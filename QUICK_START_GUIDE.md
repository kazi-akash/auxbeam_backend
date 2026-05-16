# Auxbeam E-Commerce - Quick Start Guide
## Get Up and Running in 10 Minutes

**Last Updated:** May 8, 2026  
**Laravel Version:** 11.51.0

---

## Prerequisites

- PHP 8.2 or higher
- MySQL 5.7 or higher
- Composer
- Node.js & NPM (for frontend)

---

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/kazi-akash/auxbeam_backend.git
cd auxbeam_backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auxbeam_db
DB_USERNAME=root
DB_PASSWORD=your-password
```

### 5. Run Migrations & Seeders

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE auxbeam_db"

# Run migrations
php artisan migrate

# Seed default data
php artisan db:seed --class=RBACSeeder
php artisan db:seed --class=CustomerSegmentSeeder
php artisan db:seed --class=SmsTemplateSeeder
```

### 6. Start Development Server

```bash
# Start Laravel server
php artisan serve

# Start Laravel Reverb (in another terminal)
php artisan reverb:start
```

### 7. Access Application

- **API Base URL:** http://127.0.0.1:8000/api
- **Health Check:** http://127.0.0.1:8000/up
- **Reverb WebSocket:** ws://127.0.0.1:8080

---

## Default Admin Credentials

After seeding, you can create an admin user:

```bash
php artisan tinker

# In Tinker:
$user = App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@auxbeam.com',
    'phone' => '01700000000',
    'password' => bcrypt('password123'),
    'user_type' => 'admin',
    'status' => 'active'
]);

# Assign Super Admin role
$role = App\Models\Role::where('name', 'Super Admin')->first();
$user->roles()->attach($role->id);
```

**Login Credentials:**
- Email: admin@auxbeam.com
- Password: password123

---

## Essential Commands

### Development

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# List all routes
php artisan route:list

# Check migration status
php artisan migrate:status

# Start queue worker
php artisan queue:work

# Start Reverb server
php artisan reverb:start --debug
```

### Database

```bash
# Fresh migration (WARNING: Deletes all data)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name
```

### Code Generation

```bash
# Create model with migration
php artisan make:model ModelName -m

# Create controller
php artisan make:controller ControllerName

# Create seeder
php artisan make:seeder SeederName

# Create job
php artisan make:job JobName

# Create event
php artisan make:event EventName
```

---

## API Testing

### Authentication

**Register:**
```bash
POST http://127.0.0.1:8000/api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "01700000001",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Login:**
```bash
POST http://127.0.0.1:8000/api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

### Authenticated Requests

Add token to headers:

```bash
GET http://127.0.0.1:8000/api/user/profile
Authorization: Bearer 1|abc123...
```

---

## Key API Endpoints

### Public Endpoints

```bash
# Products
GET /api/products
GET /api/products/{id}
GET /api/products/featured
GET /api/products/best-selling

# Categories
GET /api/categories
GET /api/categories/{id}/products

# Search
GET /api/search?q=keyword
```

### Customer Endpoints (Authenticated)

```bash
# Profile
GET /api/user/profile
PUT /api/user/profile

# Orders
GET /api/user/orders
GET /api/user/orders/{id}

# Wishlist
GET /api/user/wishlist
POST /api/user/wishlist
DELETE /api/user/wishlist/{id}

# Cart
GET /api/cart
POST /api/cart
PUT /api/cart/{id}
DELETE /api/cart/{id}
```

### Admin Endpoints (Authenticated + Role)

```bash
# Dashboard
GET /api/admin/dashboard

# Orders
GET /api/admin/orders
GET /api/admin/orders/{id}
PUT /api/admin/orders/{id}/status

# Products
GET /api/admin/products
POST /api/admin/products
PUT /api/admin/products/{id}
DELETE /api/admin/products/{id}

# Customers
GET /api/admin/customers
GET /api/admin/customers/{id}

# Reports
GET /api/admin/enhanced-reports/profit
GET /api/admin/enhanced-reports/cod-vs-paid
GET /api/admin/enhanced-reports/customer-growth
```

---

## Configuration

### Mail Configuration

Edit `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@auxbeam.com
MAIL_FROM_NAME="Auxbeam"
```

### SMS Configuration

Edit `.env`:

```env
# BulkSMSBD
BULKSMSBD_API_KEY=your-api-key
BULKSMSBD_SENDER_ID=your-sender-id

# OR Twilio
TWILIO_ACCOUNT_SID=your-account-sid
TWILIO_AUTH_TOKEN=your-auth-token
TWILIO_FROM_NUMBER=+1234567890
```

### Payment Gateway (SSLCommerz)

Edit `.env`:

```env
SSLCZ_STORE_ID=your-store-id
SSLCZ_STORE_PASSWORD=your-password
SSLCZ_TESTMODE=true  # Set to false for production
```

### Broadcasting (Laravel Reverb)

Edit `.env`:

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=533026
REVERB_APP_KEY=e7rrvaeu6gqi9pfaiy7l
REVERB_APP_SECRET=tkysyp8cc6rkjrxoaq0f
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

---

## Troubleshooting

### Issue: Migration Fails

**Solution:**
```bash
# Check database connection
php artisan db:show

# Clear config cache
php artisan config:clear

# Try again
php artisan migrate
```

### Issue: 500 Internal Server Error

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan optimize:clear

# Check permissions
chmod -R 775 storage bootstrap/cache
```

### Issue: CORS Error

**Solution:**

Edit `config/cors.php`:

```php
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

### Issue: Reverb Won't Start

**Solution:**
```bash
# Check if port is in use
netstat -ano | findstr :8080

# Kill process if needed
taskkill /PID <PID> /F

# Start Reverb
php artisan reverb:start
```

---

## Testing Checklist

### ✅ Basic Tests

- [ ] Application starts without errors
- [ ] Database connection works
- [ ] Migrations run successfully
- [ ] Seeders run successfully
- [ ] API endpoints respond
- [ ] Authentication works
- [ ] Authorization works (RBAC)

### ✅ Feature Tests

- [ ] User registration works
- [ ] User login works
- [ ] Product listing works
- [ ] Product search works
- [ ] Add to cart works
- [ ] Checkout works
- [ ] Order creation works
- [ ] Payment integration works
- [ ] Email notifications work
- [ ] SMS notifications work

### ✅ Admin Tests

- [ ] Admin login works
- [ ] Dashboard loads
- [ ] Order management works
- [ ] Product management works
- [ ] Customer management works
- [ ] Reports generate correctly
- [ ] RBAC permissions work

---

## Production Deployment

### 1. Server Requirements

- PHP 8.2+
- MySQL 5.7+
- Redis (recommended)
- Supervisor (for queues)
- Nginx/Apache

### 2. Environment Setup

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Set production URL
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-frontend-domain.com

# Configure production database
DB_HOST=your-production-host
DB_DATABASE=your-production-db
DB_USERNAME=your-production-user
DB_PASSWORD=your-production-password

# Set production broadcasting
BROADCAST_DRIVER=reverb
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### 3. Optimize for Production

```bash
# Install dependencies (production only)
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed default data
php artisan db:seed --class=RBACSeeder --force
php artisan db:seed --class=CustomerSegmentSeeder --force
php artisan db:seed --class=SmsTemplateSeeder --force
```

### 4. Set Up Queue Worker

Create Supervisor config: `/etc/supervisor/conf.d/auxbeam-worker.conf`

```ini
[program:auxbeam-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

### 5. Set Up Reverb Server

Create Supervisor config: `/etc/supervisor/conf.d/auxbeam-reverb.conf`

```ini
[program:auxbeam-reverb]
process_name=%(program_name)s
command=php /var/www/html/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/reverb.log
stopwaitsecs=3600
```

### 6. Start Services

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start auxbeam-worker:*
sudo supervisorctl start auxbeam-reverb
```

---

## Useful Resources

### Documentation
- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum)
- [Laravel Reverb](https://laravel.com/docs/11.x/reverb)

### Project Documentation
- `README.md` - Project overview
- `API_DOCUMENTATION.md` - API endpoints
- `SRS_COMPLIANCE_AUDIT.md` - Feature compliance
- `PROJECT_STATUS_SUMMARY.md` - Complete status
- `LARAVEL_11_UPGRADE_GUIDE.md` - Upgrade details

### Support
- **Repository:** https://github.com/kazi-akash/auxbeam_backend.git
- **Issues:** Create issue on GitHub

---

## Quick Reference

### Default Roles
1. Super Admin (full access)
2. Order Manager (order management)
3. Inventory Manager (inventory management)
4. Marketing Manager (marketing features)
5. Accountant (financial reports)

### Default Customer Segments
1. VIP (high-value customers)
2. Repeat (repeat buyers)
3. COD Risk (high cancellation rate)
4. New (new customers)
5. Inactive (no recent orders)
6. High Spender (high AOV)
7. Frequent Buyer (high order frequency)

### Default SMS Templates
1. Order Confirmation
2. Order Status Update
3. OTP Verification
4. Delivery Notification
5. Order Shipped
6. Order Delivered
7. Order Cancelled
8. Payment Reminder
9. Welcome Message
10. Flash Deal Alert

---

## Next Steps

1. ✅ Complete installation
2. ✅ Test all features
3. ✅ Configure external services (SMS, Payment)
4. ✅ Customize for your needs
5. ✅ Deploy to staging
6. ✅ Test in staging
7. ✅ Deploy to production
8. ✅ Monitor and optimize

---

**Happy Coding! 🚀**

*For detailed information, refer to the complete documentation files in the project root.*
