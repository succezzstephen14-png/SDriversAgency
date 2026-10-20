# 🚀 Vehicle & Driver Recruitment System

A comprehensive Laravel-based **Vehicle & Driver Recruitment System** with real-time frontend updates, user account management, admin dashboard, and complete moderation capabilities.

## ✨ Key Features

- ✅ User authentication with role-based access (Regular User / Admin)
- ✅ Vehicle management system with full CRUD operations
- ✅ Driver profile management with rating system
- ✅ Job recruitment posting and management
- ✅ Admin dashboard with analytics and moderation tools
- ✅ Live frontend with auto-refreshing data (30-second intervals)
- ✅ RESTful API endpoints for live data
- ✅ Bootstrap 5 responsive design
- ✅ Complete user authorization and authentication

## 🚀 Quick Start

```bash
# 1. Install dependencies
composer install && npm install

# 2. Create database
touch database/database.sqlite

# 3. Run migrations
php artisan migrate

# 4. Build frontend assets
npm run build

# 5. Start development server
php artisan serve

# 6. Visit http://localhost:8000
```

## 💻 System Requirements

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 16+ with npm
- **Database**: SQLite (default) or MySQL/PostgreSQL
- **Web Server**: Apache, Nginx, or PHP built-in server

## 📦 Installation

### Step 1: Clone/Download Project
```bash
cd /path/to/project
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Setup
```bash
# Generate app key
php artisan key:generate
```

### Step 4: Create Database
```bash
# SQLite
touch database/database.sqlite

# Or MySQL - update .env first
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Build Assets
```bash
npm run build
# Or development mode
npm run dev
```

### Step 7: Start Server
```bash
php artisan serve
```

## 📚 Documentation Files

- **README.md** - This file (project overview)
- **SETUP_GUIDE.md** - Detailed setup instructions
- **QUICK_START.md** - Quick reference guide
- **IMPLEMENTATION_SUMMARY.md** - Complete technical documentation
- **VERIFICATION_CHECKLIST.md** - Pre-launch verification steps

## 🎯 Usage

### For Regular Users
1. **Register** at `/register`
2. **Login** to access dashboard
3. **Add Vehicles** at `/vehicles/create`
4. **Create Driver Profile** at `/drivers/create`
5. **Post Jobs** at `/recruitments/create`
6. Public website pages redirect signed-in regular users to their dashboard. Log out to browse public listings.

### For Admin Users
1. **Login** at `https://admin.succezzdriversagency.com/login`
2. **Access** the admin dashboard at `https://admin.succezzdriversagency.com/admin/dashboard`
3. **Manage** users, vehicles, drivers, recruitments
4. **View** analytics and statistics

The admin subdomain must be added to the Hostinger website and use the Laravel `public` directory as its document root. Add HTTPS for the subdomain after DNS is active. `ADMIN_DOMAIN` controls which host Laravel uses for admin and CMS routes.

### Email verification

New email/password accounts receive a signed verification link and must verify their address before accessing account pages. Google and Apple accounts are automatically verified only when the provider confirms the email address. Configure a working production mail transport and sender address so verification messages can be delivered; Laravel's default `MAIL_MAILER=log` only writes messages to the log. After changing mail settings, clear Laravel's cached configuration with `php artisan config:clear`.

## 🔌 API Endpoints

### Live Data (Auto-refresh every 30 seconds)
- `GET /api/vehicles/available` - Available vehicles
- `GET /api/drivers/available` - Available drivers
- `GET /api/recruitments/open` - Open positions

### Vehicle Routes
- `GET /vehicles` - List all
- `POST /vehicles` - Create new
- `PUT /vehicles/{id}` - Update
- `DELETE /vehicles/{id}` - Delete

### Driver Routes
- `GET /drivers` - List all
- `POST /drivers` - Create new
- `PUT /drivers/{id}` - Update
- `DELETE /drivers/{id}` - Delete

### Recruitment Routes
- `GET /recruitments` - List all
- `POST /recruitments` - Create new
- `PUT /recruitments/{id}` - Update
- `DELETE /recruitments/{id}` - Delete

## 📁 Project Structure

```
project-root/
├── app/
│   ├── Http/Controllers/
│   │   ├── VehicleController.php
│   │   ├── DriverController.php
│   │   ├── RecruitmentController.php
│   │   └── AdminController.php
│   └── Models/
│       ├── User.php
│       ├── Vehicle.php
│       ├── Driver.php
│       └── Recruitment.php
├── database/
│   ├── migrations/
│   │   └── (7 migration files)
│   └── database.sqlite
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── admin/dashboard.blade.php
│   │   ├── welcome.blade.php
│   │   └── (other views)
│   ├── css/ and js/
├── routes/
│   ├── web.php
│   └── api.php
└── (config, public, bootstrap, etc.)
```

## 🗄️ Database Schema

### Users
- id, name, email, password, role, phone, address, timestamps

### Vehicles
- id, user_id, make, model, year, vehicle_type, status, license_plate, timestamps

### Drivers
- id, user_id, license_number, license_class, license_expiry, experience_years, bio, rating, total_trips, timestamps

### Recruitments
- id, user_id, position_type, title, description, salary_min, salary_max, requirements, status, timestamps

## 🔐 Security Features

- CSRF protection on all forms
- Authentication required for sensitive routes
- Admin middleware for admin-only routes
- Blade templating prevents XSS
- Password hashing with bcrypt
- Role-based authorization

## 🛠️ Useful Commands

```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:reset        # Reset migrations
php artisan migrate:refresh      # Refresh migrations

# Cache
php artisan cache:clear          # Clear cache
php artisan config:cache         # Cache config
php artisan view:cache           # Cache views

# Utilities
php artisan tinker               # Interactive shell
php artisan route:list           # List routes

# Production
npm run build                    # Build for production
php artisan optimize             # Optimize
```

## 📋 Features in Detail

### 1. Vehicle Management
- Create, read, update, delete vehicles
- Updates to approved vehicle listings remain pending until an administrator reviews them
- Vehicle types: car, van, truck, motorcycle
- Status tracking: available, unavailable, maintenance
- Live search functionality
- Real-time API endpoints

### 2. Driver Management
- Complete driver profiles
- Updates to approved driver profiles remain pending until an administrator reviews them
- License tracking with expiry dates
- 5-star rating system
- Trip counter
- License class system (A-E)

### 3. Recruitment System
- Create job opportunities
- Position types: driver, vehicle_owner, both
- Salary range specifications
- Status management: open, closed, filled
- Advanced search capabilities

### 4. Admin Dashboard
- User management with role assignment
- Review and approve or reject user account, driver, and vehicle profile changes
- Generate one-time password reset links for users
- Vehicle moderation tools
- Driver profile moderation
- Recruitment post management
- Platform analytics

### 5. Live Frontend
- Auto-refreshing data every 30 seconds
- Real-time statistics
- Vehicle count and recent listings
- Driver count and profiles
- Job opening count
- Bootstrap 5 responsive design

## 🐛 Troubleshooting

### Database Connection Error
```bash
touch database/database.sqlite
php artisan migrate
```

### Routes Not Working
```bash
php artisan route:clear
php artisan cache:clear
```

### Assets Not Loading
```bash
npm run build
php artisan cache:clear
```

### Port 8000 Already in Use
```bash
php artisan serve --port=8001
```

## 🚀 Deployment

Before going live:

1. Set `APP_DEBUG=false` in `.env`
2. Set `APP_ENV=production`
3. Configure proper database (MySQL/PostgreSQL)
4. Setup HTTPS
5. Run optimization commands:
   ```bash
   php artisan optimize
   php artisan config:cache
   php artisan route:cache
   ```

### Hostinger Shared Hosting

Deploy this as a PHP application. The project root contains a small `index.php`
and `.htaccess` that forward web requests into Laravel's `public` directory when
the domain must use `public_html` as its document root. If hPanel allows it,
prefer setting the document root directly to the project's `public` directory.

Keep `.env` on the server and out of Git. Configure `APP_KEY` and the database
settings there; for a Hostinger-hosted MySQL database use the host shown for
local website connections (usually `localhost`), not the `srv*.hstgr.io` host
used for external connections. Build the frontend with `npm run build`, install
production Composer dependencies, and run migrations from Hostinger Terminal
when available. Generate `APP_KEY` once and keep the same key for the life of
the application.

Set `SYSTEM_ADMIN_EMAIL`, `SYSTEM_ADMIN_PASSWORD`, `CMS_ADMIN_EMAIL`, and
`CMS_ADMIN_PASSWORD` in the server `.env`; passwords must not be committed to
Git. A deployment does not run database seeders automatically. After deploying
code and setting these server variables, run the following from the project
root to create or update the admin and CMS accounts:

```bash
php artisan config:clear
php artisan db:seed --class=AdminAccountsSeeder --force
```

The seeder reuses the existing default admin record when changing its email.
Login requests do not re-seed or reset account passwords. Rotate seeded
passwords after the first login.

### Password reset email

The example environment uses Laravel's `log` mailer for local development, so
password reset messages are written to the application log instead of being
delivered. To send reset links from production, configure the mail settings in
the server's `.env` with your SMTP provider's host, port, encryption scheme,
username, password, and an authorized sender address (`MAIL_FROM_ADDRESS`).
Then run `php artisan config:clear` from the project root. Never commit live
SMTP credentials to Git. After requesting a reset, check the recipient's spam
folder and the server mail/application logs if the message does not arrive.

## 📞 Support

- Laravel Docs: https://laravel.com/docs
- Bootstrap Docs: https://getbootstrap.com
- PHP Docs: https://www.php.net

## 📄 License

Open source and available under the MIT License.

---

**Version**: 1.0.0  
**Last Updated**: March 14, 2026  
**Status**: Production Ready ✅

**See SETUP_GUIDE.md for detailed installation instructions.**

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
