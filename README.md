# KeyCompare — Laravel Edition

> Real-time price comparison for digital game keys, software, and subscriptions.
> Data is imported via JSON (manual upload or Python script).

## Quick Install

```bash
bash <(curl -Ls https://raw.githubusercontent.com/ActiKeys/keycompare/main/install.sh)
```

Supports Ubuntu 22.04+, Debian 12+, CentOS/RHEL 9+.

## With a domain + SSL

```bash
# 1. Point DNS A record to server IP
# 2. Run:
DOMAIN=compare.example.com EMAIL=you@example.com \
  bash <(curl -Ls https://raw.githubusercontent.com/ActiKeys/keycompare/main/install.sh)
```

## With API auth (recommended)

```bash
IMPORT_API_TOKEN=$(openssl rand -hex 32) \
  bash <(curl -Ls https://raw.githubusercontent.com/ActiKeys/keycompare/main/install.sh)
```

## Importing products

### Option A — Manual (admin panel)
Visit `/admin`, login, click "Import JSON" and upload a file.

### Option B — Python script (recommended for automation)
```bash
python examples/push_products.py products.json \
  --url https://compare.example.com/api/import \
  --token "$IMPORT_API_TOKEN"
```

### Option C — curl
```bash
curl -X POST https://compare.example.com/api/import \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $IMPORT_API_TOKEN" \
  -d @products.json
```

### Option D — Artisan CLI (from server)
```bash
cd /opt/keycompare
php artisan keycompare:import /path/to/products.json
```

## JSON format

```json
{
  "products": [
    {
      "name": "Windows 11 Pro",
      "link": "https://example.com/product-page",
      "image-link": "https://...",
      "description": "...",
      "platform": "Windows",
      "category": "Software",
      "tags": ["Windows", "License"],
      "stores": [
        {
          "name": "SomeStore",
          "price": 49.99,
          "currency": "USD",
          "region": "GLOBAL",
          "link": "https://store.example.com/offer",
          "in_stock": true
        }
      ]
    }
  ]
}
```

- `link` is the **unique identifier** for both products and offers
- Re-importing the same JSON **updates** existing records (no duplicates)

## Manage

```bash
# Service status
systemctl status keycompare

# Live logs
journalctl -u keycompare -f

# Create admin user (for /admin panel)
cd /opt/keycompare && php artisan make:filament-user

# Update to latest
cd /opt/keycompare && git pull && bash install.sh update

# Uninstall
cd /opt/keycompare && bash install.sh uninstall
```

## Architecture

- **Framework**: Laravel 11
- **Admin**: Filament v3 (auto-installed)
- **Frontend**: Blade + Tailwind CSS + Vite
- **Database**: SQLite (single file, easy backup)
- **PHP**: 8.2+

## Endpoints

| Method | Path | Purpose |
|---|---|---|
| `GET` | `/` | Homepage |
| `GET` | `/products` | Browse all products |
| `GET` | `/products?q=...` | Search |
| `GET` | `/products/{id}` | Product detail + price comparison |
| `POST` | `/api/import` | JSON import (auth optional) |
| `GET` | `/api/stats` | Counts |
| `GET` | `/api/products` | List products as JSON |
| `GET` | `/admin` | Filament admin panel |

## Environment variables

| Var | Default | Description |
|---|---|---|
| `APP_NAME` | `KeyCompare` | Display name |
| `DB_CONNECTION` | `sqlite` | Database type |
| `IMPORT_API_TOKEN` | empty | If set, requires `Authorization: Bearer` on `/api/import` |
| `TARGET_CURRENCY` | empty | Convert all prices to this currency on display |
| `EXCHANGE_RATE_API_KEY` | empty | For automatic FX rates |

## License

MIT
