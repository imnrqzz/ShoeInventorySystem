# ShoeInventory System

A web-based inventory management system for a shoe store, built with PHP and MySQL. Designed as a school project to demonstrate CRUD operations, session-based authentication, reusable component architecture, and responsive UI design.

## What It Does

This system helps a shoe store manage its day-to-day inventory operations:

- **Dashboard** — KPI stat cards (total items, suppliers, users, transactions, low-stock alerts), low-stock alerts table, recent transactions table, and inventory overview table
- **Items** — add, edit, and delete shoe models with prices, suppliers, and minimum stock thresholds
- **Suppliers** — maintain a directory of supplier contacts with company name, contact person, category, and phone/email
- **Stock** — monitor real-time stock levels with a threshold-based progress bar, color-coded status badges (OK, Near Low, Low), and inline editing
- **Transactions** — log every inventory movement (Restock, Sale, Waste) with item, quantity, user, and reason
- **Users** — manage user accounts (username, name, email, role, active/inactive status)
- **Reports** — filter and view transaction history, print reports, or export to XML

## How It Works

### Authentication Flow
1. User visits `frontend/login.php`
2. Submits username + password via POST to `backend/process_login.php`
3. Backend verifies credentials using `password_verify()` against the hashed password in the database
4. On success, sets `$_SESSION['user_id']` and `$_SESSION['username']`, then redirects to the dashboard
5. Every protected page includes `components/auth.php` which checks `$_SESSION['username']` — if missing, redirects to login
6. All authenticated pages send `Cache-Control: no-store` headers to prevent the browser from showing cached pages after logout (fixes the back-button issue)
7. Logout (`backend/logout.php`) destroys the session and redirects to login

### Form Validation
The app uses a **two-layer validation** approach:

- **Client-side** (`js/form-validation.js`) — reads HTML5 attributes (`required`, `minlength`, `min`, `max`, `step`, `type="email"`, `pattern`) from form inputs and shows inline error messages below each field on blur. Forms with the `data-validate` attribute are automatically validated. This provides instant feedback.
- **Server-side** (PHP backend files) — re-validates all inputs before database operations. Client-side validation is a UX convenience; server-side is the actual security layer since JavaScript can be bypassed.

Validation rules:
- **Login** — username (min 3 chars) and password (min 6 chars) required
- **Register** — name (min 2), username (min 3, alphanumeric + underscores), valid email, password (min 6) with strength meter, password confirmation match
- **Items** — name (min 2 chars), price (min 0, step 0.01 for cents), threshold (min 0, whole numbers)
- **Suppliers** — company name (min 2 chars) required
- **Stock** — quantity and threshold (min 0, whole numbers only)
- **Transactions** — quantity (min 1, whole numbers)
- **Users** — username (min 3), name (min 2), valid email

### Confirmation Modals
All destructive actions (delete item, delete supplier, delete stock, delete user, logout) show a styled confirmation modal (`js/confirm-modal.js`) instead of the browser's native `confirm()` dialog. Delete actions show a red "Delete Confirmation" modal; logout shows a blue "Confirm Action" modal. Modals can be dismissed via Cancel, X button, overlay click, or Escape key.

### Page Architecture
Each authenticated page follows the same pattern:

```
<?php
require_once __DIR__ . '/components/auth.php';     // 1. Auth guard
// ... page-specific data loading ...
$pageTitle = 'Items';                                // 2. Set component vars
$pageCss = 'Item.css';
$activePage = 'items';
?>
<!DOCTYPE html>
<html>
<head>
    <?php require __DIR__ . '/components/head.php'; ?>      <!-- 3. Shared head -->
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>  <!-- 4. Sidebar nav -->
        <main class="main-content">
            <?php require __DIR__ . '/components/page_header.php'; ?>  <!-- 5. Title + action btn -->
            <?php require __DIR__ . '/components/toolbar.php'; ?>      <!-- 6. Search/filter -->
            <!-- page-specific content (tables, modals, etc.) -->
        </main>
    </div>
    <?php require __DIR__ . '/components/footer.php'; ?>     <!-- 7. Scripts + close -->
```

### Shared Components (`frontend/components/`)

| Component | Used By | Purpose |
|-----------|---------|---------|
| `auth.php` | All 7 pages | Session start, no-cache headers, login redirect, `safe()` helper |
| `head.php` | All 7 pages | `<meta>` tags, Google Fonts, Font Awesome, page-specific CSS via `$pageCss` |
| `sidebar.php` | All 7 pages | Sidebar navigation with active-page highlighting via `$activePage`. On mobile (<768px), replaced by a top bar with hamburger menu |
| `footer.php` | All 7 pages | Loads `confirm-modal.js` and `form-validation.js`, closes `</body></html>` |
| `page_header.php` | All 7 pages | Page title (`$pageTitle`), subtitle (`$pageSubtitle`), optional action button (`$headerAction`) |
| `stat_cards.php` | 3 pages | KPI stat card row from `$statCards` array, supports `'type' => 'danger'` (red) and `'success'` (green) |
| `toolbar.php` | 6 pages | Search input + optional dropdown filter + submit/reset buttons. Auto-detects active filters and shows blue indicator tags + red Reset button |

### JavaScript Modules (`js/`)

| File | Purpose |
|------|---------|
| `confirm-modal.js` | Styled confirmation modals. Provides `confirmAction()`, `confirmLogout()`, `confirmDelete()`, `confirmDeletePost()` |
| `form-validation.js` | Automatic form validation. Reads HTML5 attributes, shows inline errors on blur, prevents invalid form submission |

### Stock Level Progress Bar
The stock page uses a threshold-based progress bar to visualize inventory health:
- **100% of the bar** = 2x the minimum threshold ("comfortable" level)
- **50% mark** (gray line) = the minimum threshold boundary
- **Color**: red (below minimum) → amber (near minimum) → green (healthy)

This lets you see at a glance how far each item is from its danger line.

### Database
The system uses a MySQL database called `pos_inventory_system` with these tables:
- `items` — shoe products (name, price, supplier_id, quantity, min_quantity)
- `suppliers` — vendor companies (company_name, contact_person, category, phone_email, status)
- `stock` — detailed stock tracking (item_id, supplier_id, current_qty, min_threshold, unit, category)
- `transactions` — inventory movement log (item_id, user_id, transaction_type, quantity, reason, transaction_date)
- `users` — system accounts (username, password_hash, name, email, role, status)

### Design System (`css/base.css`)
All pages import a shared `base.css` file that defines:
- **CSS custom properties** (design tokens) for colors, fonts, spacing, border-radius
- **Layout** — fixed sidebar (desktop) + mobile top bar with hamburger menu
- **Reusable components** — tables, stat cards, badges, buttons, modals, forms, toolbars, progress bars
- **Confirmation modal styles** for `confirm-modal.js`
- **Form validation styles** (`.input-error`, `.field-error`) for `form-validation.js`
- **Active filter indicators** — blue tags and red reset button when search/filter is applied
- **Responsive breakpoints** at 768px (tablet) and 480px (phone):
  - Sidebar collapses into a slide-out drawer with dark overlay
  - Mobile top bar with hamburger, app name, and user avatar
  - Stat cards go 2-per-row (tablet) → 1-per-row (phone)
  - Tables maintain min-width and scroll horizontally
  - Modals expand to near-full-width
  - Toolbar stacks vertically

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
│   │   ├── Database.php           — PDO database connection wrapper
│   │   ├── InventoryManager.php   — dashboard aggregate queries
│   │   ├── ItemManager.php        — item CRUD operations
│   │   ├── StockManager.php       — stock level management + multi-table sync
│   │   ├── SupplierManager.php    — supplier CRUD operations
│   │   ├── Transaction.php        — transaction queries + stock adjustment
│   │   ├── TransactionManager.php — transaction CRUD with inventory sync
│   │   └── UserManager.php        — user CRUD + filtered search
│   ├── db.php                     — shared bootstrap (session, DB connection, dashboard data)
│   ├── itemtab.php                — item form handler (add, edit, delete, search)
│   ├── suppliertab.php            — supplier form handler (add, edit, delete, search)
│   ├── process_login.php          — login authentication with PDO
│   ├── process_register.php       — user registration with server-side validation
│   ├── process_transaction.php    — transaction logging + stock update
│   ├── process_user.php           — user add/delete with input validation
│   ├── user_action.php            — user update/delete handler
│   ├── stock_delete.php           — stock + item cascade deletion
│   └── logout.php                 — session destruction + redirect
├── frontend/
│   ├── components/                — shared PHP components (DRY principle)
│   │   ├── auth.php               — session guard + no-cache headers + safe() helper
│   │   ├── head.php               — <meta>, fonts, icons, page CSS
│   │   ├── sidebar.php            — sidebar nav + mobile top bar + hamburger menu
│   │   ├── footer.php             — loads JS scripts + closes HTML
│   │   ├── page_header.php        — page title + subtitle + optional action button
│   │   ├── stat_cards.php         — KPI stat card row (configurable via array)
│   │   └── toolbar.php            — search/filter form with active-state indicators
│   ├── login.php                  — login page with client-side validation
│   ├── register.php               — registration with password strength meter
│   ├── index.php                  — dashboard (stat cards + 3 data tables)
│   ├── item.php                   — item management (CRUD + modals)
│   ├── Supplier.php               — supplier management (CRUD + modals)
│   ├── stock.php                  — stock monitoring (progress bars + inline edit)
│   ├── transactions.php           — transaction log (filter by type + add modal)
│   ├── user.php                   — user management (edit modal + delete)
│   ├── reports.php                — report viewer (filter + print + XML export)
│   └── export_xml.php             — XML export endpoint (authenticated)
├── css/
│   ├── base.css                   — shared design system (tokens, layout, components, responsive)
│   ├── login_style.css            — login page styles + validation styles
│   ├── register_styles.css        — register page styles + password strength bar
│   ├── dashboard_style.css        — imports base.css (no overrides needed)
│   ├── Item.css                   — imports base.css
│   ├── Supplierstyle.css          — imports base.css
│   ├── stockstyle.css             — imports base.css
│   ├── transactions_style.css     — imports base.css
│   ├── userstyle.css              — imports base.css
│   └── reportanalysis.css         — imports base.css
├── js/
│   ├── confirm-modal.js           — styled confirmation modals for delete/logout
│   └── form-validation.js         — automatic form validation from HTML5 attributes
├── images/
│   └── shoes.png                  — app logo
└── pos_inventory_system.sql       — database schema + sample data
```

## Best Practices Demonstrated

- **Prepared statements** — all database queries use PDO prepared statements to prevent SQL injection
- **Password hashing** — `password_hash()` and `password_verify()` for secure credential storage
- **Session management** — `components/auth.php` centralizes session checks + no-cache headers on every protected page
- **Output escaping** — `htmlspecialchars()` via `safe()` helper used on all user-facing data to prevent XSS
- **Input validation** — two-layer approach: client-side (JS + HTML5 attributes) for UX, server-side (PHP) for security
- **Component architecture** — 7 shared PHP components eliminate code duplication (DRY principle)
- **CSS design tokens** — centralized color/font/spacing variables in `base.css` for consistent UI
- **Responsive design** — mobile-first with hamburger menu, horizontal-scroll tables, and stacking layouts
- **Confirmation modals** — styled modals for all destructive actions instead of browser `confirm()` dialogs
- **Separation of concerns** — backend logic, frontend templates, shared components, styles, and scripts in dedicated directories
