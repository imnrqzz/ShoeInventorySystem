# ShoeInventory System

ShoeInventory System is a full-stack shoe store management platform that combines an inventory dashboard, a storefront, and a 3D shoe customizer in one project. The admin area is built with PHP, MySQL, and classic server-rendered pages, while the storefront includes a modern product experience and a React/Vite-based customizer.

## What’s included

### Admin inventory system
- Dashboard with sales and stock insights
- Item and variant management with photo support
- Supplier management
- Stock updates with transaction logging
- User management with role-based access
- QR code generation for quick stock restocking
- REST API endpoints for inventory operations

### Storefront
- Product catalog and product detail pages
- Shopping cart and wishlist flow
- Authentication and account registration
- Customizer integration for a branded shopping experience

### 3D shoe customizer
- React + Vite frontend
- Three.js-based shoe preview experience
- Color, size, and customization controls
- Integration with the storefront experience

## Tech stack

| Layer | Technology |
|-------|-----------|
| Admin backend | PHP 8.x |
| Database | MySQL / MariaDB |
| Admin frontend | HTML, CSS, JavaScript |
| Storefront | PHP + vanilla JS |
| 3D customizer | React, Vite, Three.js |

## Setup

### Prerequisites
- XAMPP with Apache, MySQL, and PHP
- Composer (optional for the storefront PHP dependencies)
- Node.js and npm (for the 3D customizer)

### 1. Database
1. Open phpMyAdmin at http://localhost/phpmyadmin
2. Create a database named pos_inventory_system
3. Import the SQL file from pos_inventory_system.sql

### 2. Configure the backend
Update the database settings in backend/utils/config.php:

```php
return [
    'db_host'     => 'localhost',
    'db_name'     => 'pos_inventory_system',
    'db_username' => 'root',
    'db_password' => '',
];
```

### 3. Open the admin app
Visit:

```text
http://localhost/ShoeInventorySystem/frontend/login.php
```

### 4. Run the storefront customizer
From the project root, open the customizer folder and install dependencies:

```bash
cd storefront/3D-shoe-customizer
npm install
npm run dev
```

## Project structure

```text
ShoeInventorySystem/
├── api/                  # REST API endpoints
├── backend/              # PHP backend logic and managers
├── frontend/             # Admin UI pages
├── css/                  # Admin stylesheets
├── js/                   # Admin JavaScript
├── storefront/           # Storefront app and controllers
│   └── 3D-shoe-customizer/  # React + Vite customizer
├── uploads/              # Product images
└── pos_inventory_system.sql
```

## Notes
- The admin app uses session-based authentication and CSRF protection.
- The storefront and customizer are optional extensions of the same inventory system.
- If you lose all admin accounts, you can recover them through the recovery utility in backend/utils/recover_admin.php.

## Repository
- GitHub: https://github.com/imnrqzz/ShoeInventorySystem
