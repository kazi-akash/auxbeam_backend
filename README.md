# Shah E-Commerce Backend

A comprehensive Laravel-based e-commerce backend API with advanced features including product management, order processing, payment integration, analytics, and real-time notifications.

## Features

- **Product Management**: Products with variations, inventory tracking, bulk import
- **Order Processing**: Complete checkout flow with multiple payment methods
- **Payment Integration**: SSLCommerz, Stripe support
- **User Management**: Authentication with Sanctum, Google OAuth, OTP verification
- **Analytics**: Comprehensive tracking and reporting system
- **Notifications**: Real-time notifications via WebSocket (Pusher)
- **Shipping**: Multiple shipping methods and rate calculation
- **Admin Panel**: Full-featured admin API for store management
- **Media Management**: Image upload and gallery system
- **Marketing**: Coupons, promotions, flash deals, campaigns

## Documentation

- **[System Requirements](docs/SYSTEM_REQUIREMENTS.md)** - Setup and installation guide
- **[API Documentation](docs/API_DOCUMENTATION.md)** - Complete API reference
- **[Deployment Checklist](docs/DEPLOYMENT_CHECKLIST.md)** - Production deployment guide

## Quick Start

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your environment
3. Install dependencies: `composer install`
4. Generate application key: `php artisan key:generate`
5. Run migrations: `php artisan migrate`
6. Start the development server: `php artisan serve`

## Tech Stack

- **Framework**: Laravel 10
- **Authentication**: Laravel Sanctum
- **Database**: MySQL/PostgreSQL
- **Queue**: Redis/Database
- **Real-time**: Pusher
- **Payment**: SSLCommerz, Stripe
- **PDF Generation**: DomPDF
- **CSV Processing**: League CSV

## License

This project is proprietary software. All rights reserved.
