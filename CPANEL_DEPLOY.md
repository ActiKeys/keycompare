# KeyCompare — cPanel Deployment Guide

> Complete step-by-step instructions for deploying KeyCompare on a cPanel shared hosting.

## 📋 What you need

- ✅ cPanel hosting with **PHP 8.2+** (Laravel 11 requirement)
- ✅ MySQL/MariaDB database
- ✅ File Manager or FTP access
- ✅ ~500MB disk space
- ⏱️ 15-30 minutes

## 🚀 Installation (5 steps)

### Step 1: Download the package

The `keycompare-cpanel-v1.x.x.tar.gz` package already includes:
- Full source code
- `vendor/` (PHP dependencies — pre-installed)
- `public/build/` (compiled Tailwind CSS)
- `cpanel-install.sh` (cPanel-specific setup script)

### Step 2: Upload to cPanel

1. Log in to **cPanel**
2. Open **File Manager** → navigate to `public_html/`
3. Create a new folder, e.g. `keycompare`
4. Upload `keycompare-cpanel-v1.x.x.tar.gz` to that folder
5. Right-click the file → **Extract**
6. Move all extracted files up one level (or extract directly into the folder)

### Step 3: Set PHP version

1. cPanel → **MultiPHP Manager**
2. Select the folder you just created
3. Set PHP version to **8.2 or higher**
4. Enable these extensions (if available):
   - `mbstring`, `pdo_mysql`, `openssl`, `gd`, `intl`, `bcmath`, `fileinfo`, `xml`, `tokenizer`, `ctype`, `json`

### Step 4: Create MySQL database

1. cPanel → **MySQL® Databases** (or **MariaDB Databases**)
2. Create a new database: `youruser_keycompare`
3. Create a new user: `youruser_keycompare_admin`
4. Set a strong password
5. Add user to database with **ALL PRIVILEGES**

### Step 5: Run the setup script

In cPanel → **Terminal** (or SSH if you have it):

```bash
cd ~/public_html/keycompare
bash cpanel-install.sh
```

If you don't have Terminal, you can run the steps manually:
- Copy `.env.example` to `.env` and edit it
- Set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` to your cPanel values
- Set file permissions: `chmod -R 755 storage bootstrap cache database`

### Step 6: Set document root

**Option A (recommended):** Change document root in cPanel
1. cPanel → **Domains** → click your domain
2. Change "Document Root" from `public_html` to `public_html/keycompare/public`
3. Save

**Option B (alternative):** Use a redirect `.htaccess` in your `public_html/`:

```apache
# /home/user/public_html/.htaccess
RewriteEngine On
RewriteRule ^(.*)$ keycompare/public/$1 [L]
```

### Step 7: Run the installer wizard

Open `https://yourdomain.com/` in a browser.

You'll see a 5-step wizard:
1. **Welcome** — checks PHP extensions
2. **Database** — enter your cPanel MySQL credentials (host, database, user, password). Click "Test connection" before continuing.
3. **Admin** — create your admin username, email, and password
4. **Settings** — set site name and generate an API token
5. **Done** — save the API token, click through to your site

## 🔄 Sending data to your site

After installation, you can import products via:

### Option A: Python script (recommended)
```bash
# Upload examples/push_products.py to your server, then:
python3 push_products.py data.json \
  --url https://yourdomain.com/api/import \
  --token YOUR_API_TOKEN
```

### Option B: cURL
```bash
curl -X POST https://yourdomain.com/api/import \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d @data.json
```

### Option C: Artisan CLI
```bash
cd ~/public_html/keycompare
php artisan keycompare:import /path/to/data.json
```

## 🛠️ Managing the site

- **Admin panel**: `https://yourdomain.com/admin`
- **Media library**: `https://yourdomain.com/admin/media`
- **Import logs**: `https://yourdomain.com/admin/import-logs`

## ⏰ Scheduled tasks (optional)

In cPanel → **Cron Jobs**, add:

```cron
# Update currency rates daily (if used)
0 3 * * * cd ~/public_html/keycompare && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## 🔒 Security recommendations

1. **Use a strong admin password** (12+ chars, mixed)
2. **Save your API token** somewhere safe (password manager)
3. **Backup the database** regularly (cPanel → Backup Wizard)
4. **Keep Laravel updated**: `composer update` (if you have SSH)

## 🆘 Troubleshooting

### "500 Server Error" after installation
- Check `storage/logs/laravel.log` for the actual error
- Make sure `storage/` and `bootstrap/cache/` are writable (chmod 755)
- Verify PHP version is 8.2+

### "Storage link not found" error
- The installer tries to create `public/storage` symlink
- If symlinks are disabled (some hosts), manually copy:
  ```bash
  cp -r storage/app/public/* public/storage/
  ```

### "Class not found" errors
- The vendor folder might be incomplete
- Re-upload the entire `vendor/` directory

### Images not downloading during import
- Some hosts block outbound HTTPS
- Use the Media panel to upload images manually

### "Permission denied" on storage
```bash
chmod -R 755 storage bootstrap/cache
chown -R USER:USER storage bootstrap/cache  # Replace USER with your cPanel username
```

## 📞 Need help?

- GitHub: https://github.com/ActiKeys/keycompare/issues
- Logs: `storage/logs/laravel.log`
- Test API: `curl https://yourdomain.com/api/stats`

## 🌐 Web-based setup (no SSH/Terminal needed)

Even **without cPanel Terminal access**, you can complete the entire setup from your browser:

1. **Upload** the package to `public_html/keycompare/` via File Manager
2. **Set PHP version** to 8.2+ via MultiPHP Manager
3. **Set document root** to `public_html/keycompare/public` (or use .htaccess redirect)
4. **Open your domain** in a browser — the installer wizard opens automatically!
5. **Follow the 5 steps**: Welcome → Database → Admin → Settings → Done
6. **Troubleshoot anytime** at `https://yourdomain.com/install/tools`:
   - Fix permissions (1 click)
   - Create storage symlink (1 click)
   - Clear all caches
   - Run migrations manually
   - Reset installation (re-run wizard)

### Pre-installer auto-fixes (run before Laravel boots)

When you first visit your domain, `public/index.php` automatically:
- ✅ Creates required directories (storage, bootstrap/cache, etc.)
- ✅ Copies `.env.example` to `.env` if missing
- ✅ Generates `APP_KEY` (Laravel encryption key)
- ✅ Generates `IMPORT_API_TOKEN` (API authentication)
- ✅ Creates `public/storage` symlink (or copy fallback)

You never need to run `bash cpanel-install.sh` if your web server is working — the pre-installer handles everything.


## 🌐 Web-based setup (no SSH/Terminal needed)

Even **without cPanel Terminal access**, you can complete the entire setup from your browser:

1. **Upload** the package to `public_html/keycompare/` via File Manager
2. **Set PHP version** to 8.2+ via MultiPHP Manager
3. **Set document root** to `public_html/keycompare/public` (or use .htaccess redirect)
4. **Open your domain** in a browser — the installer wizard opens automatically!
5. **Follow the 5 steps**: Welcome → Database → Admin → Settings → Done
6. **Troubleshoot anytime** at `https://yourdomain.com/install/tools`:
   - Fix permissions (1 click)
   - Create storage symlink (1 click)
   - Clear all caches
   - Run migrations manually
   - Reset installation (re-run wizard)

### Pre-installer auto-fixes (run before Laravel boots)

When you first visit your domain, `public/index.php` automatically:
- ✅ Creates required directories (storage, bootstrap/cache, etc.)
- ✅ Copies `.env.example` to `.env` if missing
- ✅ Generates `APP_KEY` (Laravel encryption key)
- ✅ Generates `IMPORT_API_TOKEN` (API authentication)
- ✅ Creates `public/storage` symlink (or copy fallback)

You never need to run `bash cpanel-install.sh` if your web server is working — the pre-installer handles everything.


## 🌐 Web-based setup (no SSH/Terminal needed)

Even **without cPanel Terminal access**, you can complete the entire setup from your browser:

1. **Upload** the package to `public_html/keycompare/` via File Manager
2. **Set PHP version** to 8.2+ via MultiPHP Manager
3. **Set document root** to `public_html/keycompare/public` (or use .htaccess redirect)
4. **Open your domain** in a browser — the installer wizard opens automatically!
5. **Follow the 5 steps**: Welcome → Database → Admin → Settings → Done
6. **Troubleshoot anytime** at `https://yourdomain.com/install/tools`:
   - Fix permissions (1 click)
   - Create storage symlink (1 click)
   - Clear all caches
   - Run migrations manually
   - Reset installation (re-run wizard)

### Pre-installer auto-fixes (run before Laravel boots)

When you first visit your domain, `public/index.php` automatically:
- ✅ Creates required directories (storage, bootstrap/cache, etc.)
- ✅ Copies `.env.example` to `.env` if missing
- ✅ Generates `APP_KEY` (Laravel encryption key)
- ✅ Generates `IMPORT_API_TOKEN` (API authentication)
- ✅ Creates `public/storage` symlink (or copy fallback)

You never need to run `bash cpanel-install.sh` if your web server is working — the pre-installer handles everything.

