# ShoeInventory System

A web-based inventory management system for a shoe store, built with PHP and MySQL. Designed as a school project to demonstrate CRUD operations, session-based authentication, and a clean UI.

## What It Does

This system helps a shoe store manage its day-to-day inventory operations:

- **Dashboard** — see total items, active suppliers, user count, transaction count, and low-stock alerts at a glance
- **Items** — add, edit, and delete shoe models with prices, suppliers, and minimum stock thresholds
- **Suppliers** — maintain a directory of supplier contacts with company name, contact person, category, and phone/email
- **Stock** — monitor real-time stock levels with progress bars and color-coded status (OK vs Low)
- **Transactions** — log every inventory movement (Restock, Sale, Waste) with the item, quantity, user, and reason
- **Users** — manage user accounts (username, name, email, role, status)
- **Reports** — filter and view transaction history, print reports, or export to XML

## How It Works

### Authentication Flow
1. User visits `frontend/login.php`
2. Submits username + password via POST to `backend/process_login.php`
3. Backend verifies credentials using `password_verify()` against the hashed password in the database
4. On success, sets `$_SESSION['user_id']` and `$_SESSION['username']`, then redirects to the dashboard
5. Every page checks for `$_SESSION['username']` — if missing, redirects back to login
6. Logout clears the session and redirects to login

### Page Architecture
Each page follows the same pattern:
- **Frontend file** (`frontend/*.php`) — contains the HTML/PHP template with the sidebar, content area, and any modals
- **Backend handler** (`backend/*.php`) — processes form submissions (add, edit, delete) and redirects back
- **Class file** (`backend/Classes/*.php`) — contains the database logic using PDO prepared statements
- **CSS file** (`css/*.css`) — imports `css/base.css` for shared design tokens, then adds page-specific styles if needed

### Database
The system uses a MySQL database called `pos_inventory_system` with these tables:
- `items` — shoe products (name, price, supplier, quantity, min threshold)
- `suppliers` — vendor companies (name, contact, category, phone/email, status)
- `stock` — detailed stock tracking linked to items and suppliers
- `transactions` — inventory movement log (type, quantity, user, reason, date)
- `users` — system accounts (username, hashed password, role, status)

### Design System
All pages use a shared `css/base.css` file that defines:
- **CSS custom properties** (variables) for colors, fonts, spacing, and border radius
- **Sidebar layout** — fixed left sidebar with navigation links
- **Reusable components** — tables, cards, badges, buttons, modals, forms
- **Responsive breakpoints** — sidebar collapses on screens below 768px

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0 |
| Database | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript |
| Server | Apache (XAMPP) |
| Icons | Font Awesome 6.4 |
| Font | Inter (Google Fonts) |

## Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + MySQL + PHP stack)

### Steps

1. **Clone** the repo into your web server's `htdocs` directory:
   ```
   git clone https://github.com/imnrqzz/ShoeInventorySystem.git
   ```

2. **Create the database:**
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create a new database named `pos_inventory_system`
   - Import the `pos_inventory_system.sql` file from the project root

3. **Configure database credentials** in `backend/Classes/Database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'pos_inventory_system';
   private $username = 'root';
   private $password = '';  // your MySQL password
   ```

4. **Open** `http://localhost/ShoeInventorySystem/frontend/login.php` in your browser

5. **Register** a new account or use an existing one from the sample data

## Project Structure

```
ShoeInventorySystem/
├── backend/
│   ├── Classes/
│   │   ├── Database.php          — PDO database connection
│   │   ├── InventoryManager.php  — dashboard data queries
│   │   ├── ItemManager.php       — item CRUD operations
│   │   ├── StockManager.php      — stock level management
│   │   ├── SupplierManager.php   — supplier CRUD operations
│   │   ├── Transaction.php       — transaction queries + logging
│   │   ├── TransactionManager.php — transaction CRUD
│   │   └── UserManager.php       — user CRUD operations
│   ├── db.php                    — shared bootstrap (session, DB, data)
│   ├── itemtab.php               — item form handler
│   ├── suppliertab.php           — supplier form handler
│   ├── process_login.php         — login authentication
│   ├── process_register.php      — user registration
│   ├── process_transaction.php   — transaction logging
│   ├── process_user.php          — user add/delete handler
│   ├── user_action.php           — user update/delete handler
│   ├── stock_delete.php          — stock deletion handler
│   └── logout.php                — session destruction
├── frontend/
│   ├── login.php                 — login page
│   ├── register.php              — registration page
│   ├── index.php                 — dashboard
│   ├── item.php                  — item management
│   ├── Supplier.php              — supplier management
│   ├── stock.php                 — stock monitoring
│   ├── transactions.php          — transaction log
│   ├── user.php                  — user management
│   ├── reports.php               — report viewer
│   └── export_xml.php            — XML export endpoint
├── css/
│   ├── base.css                  — shared design system
│   ├── login_style.css           — login page styles
│   ├── register_styles.css       — register page styles
│   ├── dashboard_style.css       — dashboard styles
│   ├── Item.css                  — items page styles
│   ├── Supplierstyle.css         — suppliers page styles
│   ├── stockstyle.css            — stock page styles
│   ├── transactions_style.css    — transactions page styles
│   ├── userstyle.css             — users page styles
│   └── reportanalysis.css        — reports page styles
├── images/
│   └── shoes.png                 — app logo
└── pos_inventory_system.sql      — database schema + sample data
```

## Best Practices Demonstrated

- **Prepared statements** — all database queries use PDO prepared statements to prevent SQL injection
- **Password hashing** — `password_hash()` and `password_verify()` for secure credential storage
- **Session management** — every protected page checks for valid session before rendering
- **Output escaping** — `htmlspecialchars()` used on all user-facing data to prevent XSS
- **CSS design tokens** — centralized color/font/spacing variables for consistent UI
- **Separation of concerns** — backend logic, frontend templates, and styles in separate directories
