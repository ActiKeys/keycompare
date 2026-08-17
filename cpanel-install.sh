#!/bin/bash
# KeyCompare cPanel Setup Script
#
# USAGE:
#   1. Upload the keycompare-v1.x.x.tar.gz to your cPanel File Manager
#   2. Extract it INSIDE public_html (or a subfolder like public_html/keycompare/)
#   3. cd into the extracted folder
#   4. Run: bash cpanel-install.sh
#
# This script will:
#   - Set proper file permissions
#   - Create .env from .env.example
#   - Generate APP_KEY
#   - Create storage directories
#   - Run database migrations (if env is configured)
#   - Set up the storage symlink (or copy)
#   - Tell you what to do next
#
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_info()  { echo -e "${BLUE}[i]${NC} $*"; }
print_ok()    { echo -e "${GREEN}[✓]${NC} $*"; }
print_warn()  { echo -e "${YELLOW}[!]${NC} $*"; }

echo
echo "================================================================"
echo "  KeyCompare cPanel Setup"
echo "================================================================"
echo

# Check PHP version
PHP_VER=$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo "0")
if [ "$(echo "$PHP_VER" | cut -d. -f1)" -lt 8 ] || [ "$(echo "$PHP_VER | cut -d. -f2)" -lt 2 ] 2>/dev/null; then
    print_warn "PHP 8.2+ recommended. You have: $PHP_VER"
    if [ "$(echo "$PHP_VER" | cut -d. -f1)" -lt 8 ]; then
        echo "  Please use cPanel → MultiPHP Manager → select PHP 8.2 or higher"
        exit 1
    fi
else
    print_ok "PHP $PHP_VER"
fi

# Check required extensions
print_info "Checking required PHP extensions..."
MISSING=()
for ext in mbstring pdo pdo_mysql openssl ctype json bcmath fileinfo tokenizer xml gd intl; do
    if ! php -r "exit(extension_loaded('$ext') ? 0 : 1);" 2>/dev/null; then
        MISSING+=("$ext")
    fi
done
if [ ${#MISSING[@]} -gt 0 ]; then
    print_warn "Missing PHP extensions: ${MISSING[*]}"
    echo "  Enable them in cPanel → MultiPHP Manager → Extensions"
    echo "  Or ask your hosting provider to enable them"
else
    print_ok "All required PHP extensions present"
fi

# .env
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        print_ok "Created .env from .env.example"
    else
        print_warn ".env.example not found"
    fi
else
    print_ok ".env already exists"
fi

# Generate APP_KEY if empty
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    print_info "Generating APP_KEY..."
    KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" .env
    print_ok "APP_KEY set"
fi

# Generate import token if empty
if ! grep -q "^IMPORT_API_TOKEN=.\+" .env 2>/dev/null; then
    TOKEN=$(php -r "echo bin2hex(random_bytes(32));")
    sed -i "s|^IMPORT_API_TOKEN=.*|IMPORT_API_TOKEN=$TOKEN|" .env
    print_ok "IMPORT_API_TOKEN generated"
fi

# Storage directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p bootstrap/cache
mkdir -p database

# Set permissions (cPanel-friendly)
chmod -R 755 storage bootstrap/cache database
chmod 644 .env
echo "Permissions set"

# Storage symlink (may not work on shared hosting)
if [ ! -L public/storage ]; then
    if ln -sf "$PWD/storage/app/public" public/storage 2>/dev/null; then
        print_ok "Storage symlink created"
    else
        # Fallback: copy storage
        print_warn "Symlink not allowed, using copy fallback"
        rm -rf public/storage
        cp -r storage/app/public public/storage
    fi
fi

# Database setup
if [ -f .env ]; then
    DB_CONN=$(grep "^DB_CONNECTION=" .env | cut -d= -f2)
    if [ "$DB_CONN" = "mysql" ]; then
        print_info "Database: MySQL"
        DB_DB=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
        if [ -n "$DB_DB" ] && [ "$DB_DB" != "" ]; then
            print_ok "  Database: $DB_DB"
            print_info "  You can either:"
            echo "    1. Run the installer: open your site in browser"
            echo "    2. Or run migrations now: php artisan migrate --force"
        else
            print_warn "  Set DB_DATABASE in .env first"
        fi
    fi
fi

echo
echo "================================================================"
echo -e "${GREEN}  Setup complete!${NC}"
echo "================================================================"
echo
echo "NEXT STEPS:"
echo
echo "  1. Edit .env and set your MySQL credentials:"
echo "     DB_HOST=localhost"
echo "     DB_DATABASE=youruser_keycompare"
echo "     DB_USERNAME=youruser_admin"
echo "     DB_PASSWORD=your_db_password"
echo
echo "  2. Set your document root in cPanel to:"
echo "     /home/USER/public_html/$(basename $PWD)/public"
echo "     (or use the .htaccess redirect method if you can't change docroot)"
echo
echo "  3. Open your domain in a browser to start the installer:"
echo "     https://yourdomain.com/"
echo
echo "  4. Save the IMPORT_API_TOKEN from the installer's 'Done' page"
echo
echo "================================================================"
