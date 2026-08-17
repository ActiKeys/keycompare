# 🚨 رفع فوری مشکل cPanel: installer wizard نمایش داده نمیشه

## ❌ مشکل چیه؟

وقتی فایل‌ها رو در `public_html/` اکسترکت کردی:
- ساختار درست: `public_html/public/index.php`, `public_html/app/`, ...
- ولی cPanel **document root** هنوز `public_html/` هست
- پس وقتی میای `softmium.com/` → LiteSpeed directory listing نشون میده
- وقتی میای `softmium.com/public/` → بازم directory listing (PHP اجرا نمیشه)

**LiteSpeed نمی‌تونه خودش `/public/index.php` رو پیدا کنه** چون index معمولاً در ریشه document root باید باشه.

---

## ✅ راه‌حل ۱ (بهترین): Document Root رو عوض کن

```
1. وارد cPanel شو: https://softmium.com/cpanel
2. Domains → روی softmium.com کلیک کن → "Manage"
3. در بخش "Document Root" آدرس رو عوض کن:
   ❌ /home/YOUR_USER/public_html
   ✅ /home/YOUR_USER/public_html/public
4. Save
```

⚠️ اگه گزینه "Document Root" قفل شده یا نمیبینی، از cPanel support بخواه یا از راه‌حل ۲ استفاده کن.

---

## ✅ راه‌حل ۲ (ساده‌ترین): فایل Forwarder

اگه دسترسی به Document Root نداری:

```
1. cPanel File Manager → /public_html/
2. یه فایل index.php در root بساز (یا از package کپی کن:
   public_html_index.php رو به index.php تغییر نام بده)
3. softmium.com/ رو refresh کن
```

محتوای فایل `public_html_index.php` (که در package هست):
```php
<?php
// Forward all requests to public/
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
```

---

## ✅ راه‌حل ۳: .htaccess redirect

```
1. cPanel File Manager → /public_html/
2. یه فایل .htaccess بساز با این محتوا:

   RewriteEngine On
   RewriteCond %{REQUEST_URI} !^/public/
   RewriteRule ^(.*)$ public/$1 [L]

3. softmium.com/ رو refresh کن
```

---

## 🔍 اگه هیچ‌کدوم کار نکرد: Diagnostic

یه فایل `diagnose.php` در package هست. این کار رو بکن:

```
1. cPanel File Manager → /public_html/public/
2. مطمئن شو فایل diagnose.php اونجاست
3. باز کن: https://softmium.com/diagnose.php
```

این بهت نشون میده:
- آیا PHP کار می‌کنه؟
- چه extensions نصب هستن
- فایل‌های مهم کجا هستن
- مشکل دقیق کجاست

---

## 🆘 اگه PHP اصلاً کار نمی‌کنه:

یعنی هاستت PHP-FPM یا PHP Handler روی پوشه `public/` تنظیم نکرده.

```
1. cPanel → MultiPHP Manager
2. پوشه /public_html/public/ رو پیدا کن
3. PHP version رو روی "8.2" یا بالاتر تنظیم کن
4. Apply
```

اگه پوشه `public/` در لیست نیست:
```
1. به هاستینگ تیکت بزن و بگو:
   "Please enable PHP 8.2+ for /public_html/public/ on domain softmium.com"
```

---

## 🧪 تست نهایی

بعد از هر تغییر، این URL ها رو چک کن:

```
https://softmium.com/                     → باید installer wizard نمایش بده
https://softmium.com/install/welcome      → باید Step 1 باشه
https://softmium.com/install/tools        → باید system tools باشه
https://softmium.com/diagnose.php         → باید وضعیت PHP رو نشون بده
https://softmium.com/api/stats            → باید {"products":0,"offers":0,...} باشه
```

---

## 📝 خلاصه سریع:

| مشکل | راه‌حل |
|---|---|
| Directory listing در `/` | عوض کردن Document Root به `/public` |
| Directory listing در `/public/` | فعال کردن PHP در MultiPHP Manager |
| 404 برای `index.php` | چک کردن `.htaccess` و PHP handler |
| Internal Server Error | چک کردن storage permissions |

---

اگه بازم مشکل داشتی:
- `https://softmium.com/diagnose.php` رو باز کن
- خروجی رو بهم بفرست
- می‌تونم دقیق تشخیص بدم
