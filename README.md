# ShoeInventory System

A web-based inventory management system for a shoe store, built with PHP and MySQL. Features a REST API, QR code restocking, photo-enabled item cards, and real-time stock synchronization.

## Features

### Core Modules
- **Dashboard** — KPI stat cards, low-stock alerts, recent transactions, inventory overview
- **Items** — flip card UI with photos, edit/delete, QR code on back, image upload (admin)
- **Suppliers** — directory of vendor contacts with CRUD operations
- **Stock** — real-time stock levels with threshold-based progress bars and status badges
- **Transactions** — log inventory movements (Restock, Sale, Waste) with user tracking
- **Users** — account management with role-based access (Admin/User)

### QR Code Restocking
- **Print QR codes** for each item from the QR Generator page
- **Scan with any phone** — adds +1 to stock instantly
- **Works across devices** — auto-detects server IP for network access
- **Clean landing page** — shows "Restocked!" with item name and new quantity

### REST API
- Full CRUD endpoints for items, stock, suppliers, transactions, users
- API key authentication for secure access
- JSON responses with consistent format
- Setup wizard at `/api/setup.php`

### Image Upload
- Admins can upload photos for each item
- Drag & drop support
- Photos display on flip cards
- Cross-device access via network

## How It Works

### Authentication Flow
1. User visits `frontend/login.php`
2. Submits credentials via POST to `backend/process_login.php`
3. Backend verifies with `password_verify()` against hashed password
4. On success, sets session variables and redirects to dashboard
5. Every protected page checks `$_SESSION['username']` — redirects if missing
6. Registration is disabled — admin creates accounts via User Management

### QR Code Restock Flow
1. Admin prints QR codes from `frontend/qr_generator.php`
2. QR codes contain a URL: `http://[server-ip]/ShoeInventorySystem/frontend/restock_scan.php?id=[item_id]`
3. Any device scans the QR → opens the landing page
4. Landing page calls the restock API → adds +1 to stock
5. Both `items.quantity` and `stock.current_qty` are updated atomically

### Stock Synchronization
All operations that modify inventory automatically sync both tables:
- **Item CRUD** — `ItemManager` creates/updates/deletes stock records
- **Transactions** — `TransactionManager` updates items and syncs stock
- **QR Restock** — `restock.php` uses `TransactionManager` for atomic updates
- **XML Import** — creates stock records for new items

### REST API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/token` | none | Generate API key |
| GET | `/api/items` | required | List items |
| POST | `/api/items` | required | Create item |
| PUT | `/api/items/{id}` | required | Update item |
| DELETE | `/api/items/{id}` | required | Delete item |
| GET | `/api/stock` | required | List stock |
| PUT | `/api/stock/{id}` | required | Update stock |
| GET | `/api/suppliers` | required | List suppliers |
| GET | `/api/transactions` | required | List transactions |
| POST | `/api/transactions` | required | Log transaction |
| GET | `/api/users` | admin | List users |
| GET | `/api/restock.php?id=N` | none | Quick restock (+1) |

## Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.0+)

### Installation

1. **Clone the repo:**
   ```
   git clone https://github.com/imnrqzz/ShoeInventorySystem.git
   ```

2. **Create the database:**
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create database `pos_inventory_system`
   - Import `pos_inventory_system.sql`

3. **Configure database** in `backend/config.php`:
   ```php
   return [
       'db_host'     => 'localhost',
       'db_name'     => 'pos_inventory_system',
       'db_username' => 'root',
       'db_password' => '',
   ];
   ```

4. **Run migration** (first time only):
   ```
   http://localhost/ShoeInventorySystem/backend/migrate_image_column.php
   ```

5. **Open** `http://localhost/ShoeInventorySystem/frontend/login.php`

6. **Login** with existing credentials or ask admin to create an account

### Default Users

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | admin |
| mark | (set in DB) | user |
| izana | (set in DB) | user |

**Change the admin password after first login!**

### Emergency Recovery

If you accidentally delete all admin accounts, visit:
```
http://localhost/ShoeInventorySystem/backend/recover_admin.php
```
Enter a username and password to create/restore an admin account.

## Project Structure

```
ShoeInventorySystem/
├── api/                          — REST API layer
│   ├── index.php                 — Central router + JSON helpers
│   ├── auth.php                  — API key validation middleware
│   ├── auth_endpoint.php         — Token generation endpoint
│   ├── items.php                 — Items CRUD
│   ├── stock.php                 — Stock CRUD
│   ├── suppliers.php             — Suppliers CRUD
│   ├── transactions.php          — Transactions CRUD
│   ├── users.php                 — Users CRUD (admin)
│   ├── restock.php               — Quick restock endpoint
│   ├── setup.php                 — API setup wizard
│   └── .htaccess                 — Clean URL routing
├── backend/
│   ├── Classes/
│   │   ├── Database.php          — PDO connection wrapper
│   │   ├── ItemManager.php       — Item CRUD + stock sync
│   │   ├── StockManager.php      — Stock management + auto-sync
│   │   ├── SupplierManager.php   — Supplier CRUD
│   │   ├── Transaction.php       — Transaction queries + stock sync
│   │   ├── TransactionManager.php — Transaction CRUD + stock sync
│   │   └── UserManager.php       — User CRUD
│   ├── bootstrap.php             — Session, DB, CSRF, helpers
│   ├── config.php                — Database configuration
│   ├── upload_image.php          — Image upload handler
│   ├── delete_image.php          — Image delete handler
│   ├── migrate_image_column.php  — DB migration (admin only)
│   ├── sync_items_stock.php      — Sync items/stock (admin only)
│   └── recover_admin.php         — Emergency admin recovery
├── frontend/
│   ├── components/
│   │   ├── auth.php              — Auth guard
│   │   ├── head.php              — Shared <head> tags
│   │   ├── sidebar.php           — Navigation sidebar
│   │   ├── footer.php            — Shared scripts + server IP
│   │   ├── page_header.php       — Page title + actions
│   │   ├── toolbar.php           — Search/filter bar
│   │   └── items/
│   │       ├── grid.php          — Flip card grid component
│   │       ├── add_modal.php     — Add item modal
│   │       ├── edit_modal.php    — Edit item modal
│   │       ├── image_upload_modal.php — Image upload modal
│   │       └── actions_menu.php  — Admin actions dropdown
│   ├── login.php                 — Login page
│   ├── register.php              — Disabled (redirects to login)
│   ├── index.php                 — Dashboard
│   ├── item.php                  — Items (flip cards + QR)
│   ├── Supplier.php              — Suppliers
│   ├── stock.php                 — Stock levels
│   ├── transactions.php          — Transaction log
│   ├── user.php                  — User management
│   ├── qr_generator.php          — Print QR codes
│   ├── restock_scan.php          — QR scan landing page
│   ├── export_xml.php            — XML export
│   └── 404.php                   — 404 error page
├── api/
├── css/
│   ├── base.css                  — Design system
│   ├── item_cards.css            — Flip card styles
│   └── ... (page-specific CSS)
├── js/
│   ├── qr-scanner.js             — QR scanner + restock
│   ├── confirm-modal.js          — Confirmation dialogs
│   ├── form-validation.js        — Form validation
│   └── validation-rules.js       — Validation rules
├── uploads/
│   ├── .htaccess                 — Blocks PHP execution
│   └── items/                    — Uploaded images
├── images/shoes.png              — App logo
└── pos_inventory_system.sql      — Database schema + data
```

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0 |
| Database | MySQL / MariaDB 10.4 |
| Frontend | HTML, CSS, JavaScript |
| Server | Apache (XAMPP) |
| QR Library | html5-qrcode (MIT) |
| QR Generator | qrcode-generator (MIT) |
| Icons | Font Awesome 6.4 |
| Font | Inter (Google Fonts) |

## Security Features

- **Prepared statements** — all queries use PDO to prevent SQL injection
- **Password hashing** — `password_hash()` / `password_verify()`
- **CSRF protection** — tokens on all forms, verified server-side
- **Session security** — httponly, strict_mode, SameSite flags
- **Output escaping** — `htmlspecialchars()` via `safe()` helper
- **Admin-only endpoints** — debug/migration scripts require admin role
- **Disabled registration** — accounts created by admin only
- **API key authentication** — for REST API access

## Multi-Device Testing

### From Another Laptop
1. Connect to the same WiFi
2. Find your server IP: run `ipconfig` in PowerShell
3. Open: `http://[your-ip]/ShoeInventorySystem/frontend/login.php`

### QR Code Scanning
1. Print QR codes from `frontend/qr_generator.php`
2. Open any QR scanner app on your phone
3. Scan a QR code → stock increases by 1
4. Refresh stock page to see the update
