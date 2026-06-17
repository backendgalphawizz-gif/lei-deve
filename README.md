# LEI Super Admin Portal (Laravel + MySQL)

LEI System & Registry Services के लिए Super Admin login और dashboard — screenshots के अनुसार UI।

## आवश्यकताएँ

- PHP 8.2+ (XAMPP)
- MySQL (XAMPP phpMyAdmin)
- Composer (`composer.phar` project folder में है)

## इंस्टॉलेशन (XAMPP)

### 1. MySQL database बनाएँ

phpMyAdmin में नया database:

```sql
CREATE DATABASE lei_registry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. `.env` सेट करें

`.env` file में:

```env
APP_NAME="LEI Super Admin"
APP_URL=http://localhost/LEI

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lei_registry
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migration और seed

```bash
cd c:\xampp\htdocs\LEI
php composer.phar install
php artisan key:generate
php artisan migrate:fresh --seed
```

### 4. Apache

URL: **http://localhost/LEI/** (बिना `/public` के — root `index.php` + `.htaccess` से चलता है)

Login: **http://localhost/LEI/admin/login**

## Login (डिफ़ॉल्ट)

| Field | Value |
|-------|--------|
| Admin Identifier | `admin@gmail.com` |
| Secure Token | `12345678` |

## Database structure

| Table | उपयोग |
|-------|--------|
| `users` | Admin login (`system_id`, role, tier) |
| `admin_menu_items` | Sidebar menu |
| `system_alerts` | SLA / Security banners |
| `dashboard_snapshots` | Stat cards (applications, payments…) |
| `registry_applications` | Applications (आगे के pages) |
| `pending_approvals` | Manual validation queue |
| `payment_transactions` | Payments module |
| `service_health_checks` | System Health widget |
| `application_trend_metrics` | Bar chart data |
| `admin_notifications` | Header notifications |
| `audit_logs` | Login / activity logs |

पूरा SQL reference: `database/schema/lei_registry.sql`

## Routes

- `/` → Login redirect
- `/admin/login` → Super Admin Portal
- `/admin/dashboard` → System Overview (auth required)

## अगले steps

जब आप बाकी pages के screenshots भेजेंगे, उसी structure पर modules जोड़े जाएंगे (`User Management`, `Payments`, आदि)।
