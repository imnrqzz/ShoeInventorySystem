# ShoeInventory System

A web-based inventory management system for a shoe store, built with PHP and MySQL. Features a REST API, QR code restocking, item variants (colors/sizes), photo-enabled flip cards, and real-time stock synchronization.

## Features

### Core Modules
- **Dashboard** — KPI stat cards, low-stock alerts, recent transactions, best/least sellers charts
- **Items** — Flip card UI with photos, QR code on back, "See Details" shows color/size variants
- **Suppliers** — Directory of vendor contacts with CRUD operations (admin only)
- **Stock** — Real-time stock levels with threshold progress bars, inline variant editing
- **Transactions** — Log inventory movements (Restock, Sold) with detailed invoice printing
- **Users** — Account management with role-based access (Admin/Staff)

### Item Variants
- Each item can have multiple **colors** and **sizes**
- Variants track individual stock quantities
- Click "See Details" on any item to view all available variants
- Stock totals auto-sync across items, stock, and variants tables
- Edit variants directly from the Stock page

### Stock Management
- **Single-form editing** — Update stock settings and all variants at once
- **Live validation** — Shows if variant quantities match total stock
- **Auto-transaction logging** — Creates Restock/Sold transactions when stock changes
- **Visual stock bars** — Color-coded progress indicators (green/amber/red)

### QR Code Restocking
- **Print QR codes** for each item from the QR Generator page
- **Scan with any phone** — adds +1 to stock instantly
- **Works across devices** — auto-detects server IP for network access

### Invoice Printing
- **Detailed breakdown** — Shows sold vs restocked items separately
- **Per-item summary** — Lists each shoe with quantity and percentage
- **Filterable** — Print reports for specific date ranges or transaction types

### REST API
- Full CRUD endpoints for items, stock, suppliers, transactions, users
- API key authentication for secure access
- JSON responses with consistent format

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

3. **Configure database** in `backend/utils/config.php`:
   ```php
   return [
       'db_host'     => 'localhost',
       'db_name'     => 'pos_inventory_system',
       'db_username' => 'root',
       'db_password' => '',
   ];
   ```

4. **Open** `http://localhost/ShoeInventorySystem/frontend/login.php`

5. **Login** with existing credentials or ask admin to create an account

### Default Users

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| kram123 | (set in DB) | Admin |
| mark | (set in DB) | Staff |

**Change the admin password after first login!**

### Emergency Recovery

If you accidentally delete all admin accounts, visit:
```
http://localhost/ShoeInventorySystem/backend/utils/recover_admin.php
```

## Project Structure

```
ShoeInventorySystem/
├── api/                            — REST API layer
│   ├── auth.php                    — Shared auth + DB connection
│   ├── index.php                   — Central router
│   ├── items.php                   — Items CRUD
│   ├── stock.php                   — Stock CRUD
│   ├── suppliers.php               — Suppliers CRUD
│   ├── transactions.php            — Transactions
│   └── users.php                   — Users (admin only)
├── backend/
│   ├── Classes/                    — OOP managers
│   │   ├── Database.php            — PDO connection
│   │   ├── ItemManager.php         — Item CRUD + stock sync
│   │   ├── StockManager.php        — Stock + variants management
│   │   ├── SupplierManager.php     — Supplier CRUD
│   │   ├── Transaction.php         — Transaction queries
│   │   ├── TransactionManager.php  — Transaction CRUD
│   │   └── UserManager.php         — User CRUD
│   ├── handlers/                   — Form processors
│   │   ├── process_login.php       — Login handler
│   │   ├── process_register.php    — Registration handler
│   │   ├── process_transaction.php — Transaction handler
│   │   ├── process_user.php        — User add handler
│   │   └── user_action.php         — User enable/disable/update
│   ├── utils/                      — Helpers & config
│   │   ├── config.php              — Database configuration
│   │   ├── helpers.php             — Shared functions (getServerIp)
│   │   ├── validate.php            — Form validation
│   │   └── validation_rules.php    — Validation rules
│   ├── bootstrap.php               — Session, DB, CSRF init
│   ├── db.php                      — Dashboard data queries
│   ├── get_variants.php            — Variants API endpoint
│   ├── itemtab.php                 — Item form handler
│   ├── suppliertab.php             — Supplier form handler
│   ├── upload_image.php            — Image upload handler
│   └── logout.php                  — Session destroy
├── frontend/
│   ├── components/
│   │   ├── auth.php                — Auth guard
│   │   ├── head.php                — Shared <head>
│   │   ├── sidebar.php             — Navigation
│   │   ├── footer.php              — Scripts + server IP
│   │   └── items/                  — Item-specific components
│   │       ├── grid.php            — Flip card grid
│   │       ├── add_modal.php       — Add item modal
│   │       ├── edit_modal.php      — Edit item modal
│   │       ├── variant_modal.php   — Variant details modal
│   │       └── image_upload_modal.php
│   ├── login.php                   — Login page
│   ├── index.php                   — Dashboard
│   ├── item.php                    — Items (flip cards + QR)
│   ├── stock.php                   — Stock levels + variants
│   ├── Supplier.php                — Suppliers
│   ├── transactions.php            — Transaction log + invoice
│   ├── reports.php                 — Reports + export
│   ├── user.php                    — User management
│   └── qr_generator.php            — Print QR codes
├── css/                            — Stylesheets (lowercase-hyphen)
│   ├── base.css                    — Design system
│   ├── items.css                   — Flip card styles
│   ├── stock.css                   — Stock page styles
│   └── ... (page-specific CSS)
├── js/
│   ├── confirm-modal.js            — Confirmation dialogs
│   ├── form-validation.js          — Form validation
│   ├── qr-scanner.js               — QR scanner
│   └── validation-rules.js         — Validation rules
├── uploads/
│   └── items/                      — Shoe images
│       ├── airmax90.png
│       ├── onitsuka.jpg
│       ├── airjordan1.jpg
│       ├── suede-classic.png
│       ├── airforce1.jpg
│       └── pegasus.png
└── pos_inventory_system.sql        — Database schema + sample data
```

## Database Schema

### Tables
| Table | Description |
|-------|-------------|
| `items` | Shoe inventory (name, price, quantity, image) |
| `stock` | Stock levels synced with items |
| `item_variants` | Color/size variants per item |
| `suppliers` | Vendor contacts |
| `transactions` | Restock/Sold history |
| `users` | System accounts |
| `api_keys` | API authentication |

### Key Relationships
- `items` → `stock` (1:1 via item_id)
- `items` → `item_variants` (1:many via item_id)
- `items` → `suppliers` (many:1 via supplier_id)
- `transactions` → `items` (many:1 via item_id)
- `transactions` → `users` (many:1 via user_id)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.0 |
| Database | MySQL / MariaDB 10.4 |
| Frontend | HTML, CSS, JavaScript |
| Server | Apache (XAMPP) |
| Icons | Font Awesome 6.4 |
| Font | Inter (Google Fonts) |

## Security Features

- **Prepared statements** — All queries use PDO
- **Password hashing** — `password_hash()` / `password_verify()`
- **CSRF protection** — Tokens on all forms
- **Session security** — httponly, strict_mode, SameSite
- **Session regeneration** — After login (prevents session fixation)
- **Output escaping** — `htmlspecialchars()` via `safe()` helper
- **Admin-only actions** — Stock/item editing restricted to admins
- **API key authentication** — For REST API access
- **No delete operations** — Data cannot be deleted (admin protection)

## Multi-Device Testing

### From Another Device
1. Connect to the same WiFi
2. Find your server IP: run `ipconfig` in PowerShell
3. Open: `http://[your-ip]/ShoeInventorySystem/frontend/login.php`

### QR Code Scanning
1. Print QR codes from `frontend/qr_generator.php`
2. Open any QR scanner app on your phone
3. Scan a QR code → stock increases by 1
