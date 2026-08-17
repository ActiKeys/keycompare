#!/bin/bash
# KeyCompare Laravel — One-line installer
# Usage:
#   bash <(curl -Ls https://raw.githubusercontent.com/ActiKeys/keycompare/main/install.sh)
#
# Optional env:
#   DOMAIN=example.com        # enable SSL
#   EMAIL=you@example.com     # required for SSL
#   IMPORT_API_TOKEN=secret   # protect /api/import endpoint
#
set -e

REPO="https://github.com/ActiKeys/keycompare.git"
BRANCH="${BRANCH:-main}"
INSTALL_DIR="/opt/keycompare"
DB_FILE="$INSTALL_DIR/database/database.sqlite"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_info()  { echo -e "${BLUE}[i]${NC} $*"; }
print_ok()    { echo -e "${GREEN}[✓]${NC} $*"; }
print_warn()  { echo -e "${YELLOW}[!]${NC} $*"; }
print_err()   { echo -e "${RED}[✗]${NC} $*"; }

# -------- root check --------
[ "$EUID" -eq 0 ] || { print_err "Run as root (sudo)"; exit 1; }

# -------- detect OS --------
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS="$ID"
else
    print_err "Cannot detect OS"
    exit 1
fi
print_info "OS: $OS"

# -------- install dependencies --------
print_info "Installing system dependencies..."
case "$OS" in
    ubuntu|debian)
        apt-get update -qq
        apt-get install -y -qq curl wget git ca-certificates gnupg lsb-release \
            nginx certbot python3-certbot-nginx ufw sudo \
            php-cli php-fpm php-mbstring php-xml php-sqlite3 php-curl \
            php-zip php-tokenizer php-fileinfo php-bcmath php-intl
        ;;
    centos|rhel|rocky|almalinux)
        yum install -y epel-release
        yum install -y curl wget git nginx certbot \
            php-cli php-fpm php-mbstring php-xml php-sqlite3 php-curl \
            php-zip php-bcmath php-intl
        ;;
    *) print_err "Unsupported OS: $OS"; exit 1 ;;
esac

# -------- install Composer --------
if ! command -v composer >/dev/null 2>&1; then
    print_info "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
print_ok "Composer $(composer --version | head -1)"

# -------- clone repo --------
if [ -d "$INSTALL_DIR" ]; then
    print_info "Existing install found, updating..."
    cd "$INSTALL_DIR" && git pull origin "$BRANCH" --quiet
else
    print_info "Cloning repository..."
    git clone --depth 1 --branch "$BRANCH" "$REPO" "$INSTALL_DIR"
fi
cd "$INSTALL_DIR"

# -------- install PHP dependencies --------
print_info "Installing PHP dependencies..."
composer install --no-dev --no-interaction --optimize-autoloader 2>&1 | tail -3

# -------- install Node + build assets --------
if ! command -v node >/dev/null 2>&1; then
    print_info "Installing Node.js 22..."
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - 2>/dev/null || \
    curl -fsSL https://rpm.nodesource.com/setup_22.x | bash -
    case "$OS" in
        ubuntu|debian) apt-get install -y -qq nodejs ;;
        *) yum install -y nodejs ;;
    esac
fi
print_info "Building frontend assets..."
npm install --no-audit --no-fund
npm run build

# -------- env --------
if [ ! -f .env ]; then
    cp .env.example .env 2>/dev/null || cat > .env <<'EOF'
APP_NAME=KeyCompare
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost
DB_CONNECTION=sqlite
LOG_CHANNEL=stack
LOG_LEVEL=info
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
BROADCAST_DRIVER=log
EOF
fi
php artisan key:generate --force
[ -n "$IMPORT_API_TOKEN" ] && sed -i "s|^IMPORT_API_TOKEN=.*|IMPORT_API_TOKEN=$IMPORT_API_TOKEN|" .env || echo "IMPORT_API_TOKEN=" >> .env
APP_URL="http${DOMAIN:+s}://${DOMAIN:-$PUBLIC_IP}"
sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env

# -------- database --------
mkdir -p database
touch database/database.sqlite
php artisan migrate --force
chown -R www-data:www-data database storage bootstrap/cache 2>/dev/null || true

# -------- systemd --------
print_info "Installing systemd services..."
WWW_USER="www-data"
[ "$OS" = "centos" ] || [ "$OS" = "rhel" ] || [ "$OS" = "rocky" ] || [ "$OS" = "almalinux" ] && WWW_USER="nginx"

cat > /etc/systemd/system/keycompare.service <<EOF
[Unit]
Description=KeyCompare (Laravel)
After=network.target

[Service]
Type=simple
User=$WWW_USER
Group=$WWW_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=/usr/bin/php artisan serve --host=127.0.0.1 --port=8000
Restart=always
RestartSec=5
Environment=APP_ENV=production

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload
systemctl enable keycompare
systemctl restart keycompare
sleep 2

# -------- nginx --------
print_info "Configuring nginx..."
rm -f /etc/nginx/sites-enabled/default
HTTP_PORT="${PORT:-80}"
if [ -n "$DOMAIN" ]; then
    cat > /etc/nginx/sites-available/keycompare <<EOF
server {
    listen $HTTP_PORT;
    server_name $DOMAIN;
    root $INSTALL_DIR/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php*-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF
    ln -sf /etc/nginx/sites-available/keycompare /etc/nginx/sites-enabled/
    nginx -t && systemctl restart nginx
    if [ -n "$EMAIL" ]; then
        certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" || \
            print_warn "SSL setup failed. Run 'certbot --nginx -d $DOMAIN' manually after DNS is set."
    fi
else
    PUBLIC_IP=$(curl -s --max-time 5 https://api.ipify.org 2>/dev/null || hostname -I | awk '{print $1}')
    cat > /etc/nginx/sites-available/keycompare <<EOF
server {
    listen $HTTP_PORT default_server;
    root $INSTALL_DIR/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php*-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF
    ln -sf /etc/nginx/sites-available/keycompare /etc/nginx/sites-enabled/
    nginx -t && systemctl restart nginx
fi

# -------- firewall --------
if command -v ufw >/dev/null 2>&1; then
    ufw --force reset
    ufw default deny incoming
    ufw default allow outgoing
    ufw allow ssh
    ufw allow "${PORT:-80}/tcp"
    [ -n "$DOMAIN" ] && ufw allow "${HTTPS_PORT:-443}/tcp"
    ufw --force enable
elif command -v firewall-cmd >/dev/null 2>&1; then
    systemctl enable --now firewalld
    firewall-cmd --permanent --add-service=ssh
    firewall-cmd --permanent --add-service=http
    [ -n "$DOMAIN" ] && firewall-cmd --permanent --add-service=https
    firewall-cmd --reload
fi

# -------- summary --------
echo
echo "================================================================"
echo -e "${GREEN}  KeyCompare installed!${NC}"
echo "================================================================"
echo "  Service: $(systemctl is-active keycompare)"
echo "  DB:      $DB_FILE"
echo
if [ -n "$DOMAIN" ]; then
    echo "  URL:     https://$DOMAIN/"
else
    echo "  URL:     http://$PUBLIC_IP/"
fi
echo
echo "  API:     POST /api/import (JSON body)"
echo "  Stats:   GET  /api/stats"
echo "  Update:  cd $INSTALL_DIR && git pull && bash install.sh update"
echo "  Remove:  bash install.sh uninstall"
echo "================================================================"
