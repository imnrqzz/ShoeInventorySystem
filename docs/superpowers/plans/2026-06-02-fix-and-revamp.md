# ShoeInventorySystem Fix & UI Revamp — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix all broken functionality and revamp the UI to a consistent light & minimal design across the entire ShoeInventorySystem app.

**Architecture:** Fix-in-place approach — repair broken backend logic, add missing session checks, standardize navigation, then restyle all pages using a shared CSS base file with design tokens. Each page keeps its own CSS file but imports the shared base.

**Tech Stack:** PHP 8.0, MySQL/MariaDB, vanilla CSS, Font Awesome 6.4, XAMPP

---

## File Map

**New files:**
- `css/base.css` — shared design tokens, sidebar, navbar, table, modal, form, card, badge, button styles

**Modified backend files:**
- `backend/suppliertab.php` — remove duplicate add block
- `backend/itemtab.php` — add session check
- `backend/stock_delete.php` — add session check
- `backend/user_action.php` — add session check
- `backend/process_user.php` — add session check + isset guards
- `backend/logout.php` — simplify session handling

**Modified frontend files (all 10):**
- `frontend/login.php` — new HTML structure + light design
- `frontend/register.php` — new HTML structure + light design
- `frontend/index.php` — sidebar layout + fix nav links
- `frontend/item.php` — sidebar layout + fix nav/logout links
- `frontend/Supplier.php` — sidebar layout + fix nav/logout links
- `frontend/stock.php` — sidebar layout + fix nav/logout links
- `frontend/transactions.php` — sidebar layout + fix nav/logout links
- `frontend/user.php` — sidebar layout + fix nav/logout links
- `frontend/reports.php` — sidebar layout + fix nav/logout links
- `frontend/export_xml.php` — add session check

**Modified CSS files (all 9):**
- `css/login_style.css` — full rewrite
- `css/register_styles.css` — full rewrite
- `css/dashboard_style.css` — full rewrite
- `css/Item.css` — full rewrite
- `css/Supplierstyle.css` — full rewrite
- `css/stockstyle.css` — full rewrite
- `css/transactions_style.css` — full rewrite
- `css/userstyle.css` — full rewrite
- `css/reportanalysis.css` — full rewrite

**Documentation:**
- `README.md` — full rewrite

---

### Task 1: Backend Fixes — Session Checks, Duplicate Code, Input Validation

**Files:**
- Modify: `backend/suppliertab.php`
- Modify: `backend/itemtab.php`
- Modify: `backend/stock_delete.php`
- Modify: `backend/user_action.php`
- Modify: `backend/process_user.php`
- Modify: `backend/logout.php`
- Modify: `frontend/export_xml.php`

- [ ] **Step 1: Fix `backend/suppliertab.php` — remove duplicate add block**

The file has a duplicate `if ($_SERVER['REQUEST_METHOD'] === 'POST' && ... 'add')` block at lines 41-52. Remove the entire second block (lines 41-52):

```php
// DELETE THIS ENTIRE BLOCK (it's a duplicate of the block above):
// In backend/suppliertab.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $company_name = trim($_POST['supplier_name'] ?? '');
    $contact      = trim($_POST['contact_person'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $phone_email  = trim($_POST['phone_email'] ?? '');
    $status       = isset($_POST['active']) && (int)$_POST['active'] === 1 ? 'Active' : 'Inactive';

    // Ensure your SupplierManager class has this method signature
    $supplierManager->addSupplier($company_name, $contact, $category, $phone_email, $status);
    header("Location: Supplier.php");
    exit();
}
```

- [ ] **Step 2: Add session check to `backend/itemtab.php`**

Add at the very top of the file (before existing code):

```php
<?php
// backend/itemtab.php

// Best Practice: Always verify the user is logged in before processing any data.
// session_start() initializes the session so we can check $_SESSION variables.
// If no valid session exists, we redirect to the login page to prevent unauthorized access.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

// 1. Secure Object Architecture Injections
require_once __DIR__ . '/classes/Database.php';
// ... rest of file unchanged
```

- [ ] **Step 3: Add session check to `backend/stock_delete.php`**

Add at the very top:

```php
<?php
// backend/stock_delete.php

// Best Practice: Authenticate before allowing any delete operation.
// Without this check, anyone could call this URL directly and delete inventory data.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

// 1. Initialize system-wide database structural layers
require_once __DIR__ . '/classes/Database.php';
// ... rest of file unchanged
```

- [ ] **Step 4: Add session check to `backend/user_action.php`**

Replace the entire file:

```php
<?php
// backend/user_action.php

// Best Practice: Always start a session and verify login before handling user actions.
// This prevents unauthenticated users from deleting or modifying accounts.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

require_once 'classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Best Practice: Use the null coalescing operator (??) to safely access POST values.
    // This prevents "undefined index" PHP warnings if a field is missing.
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        // Best Practice: Cast user-supplied IDs to int to ensure they're valid numbers.
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../frontend/user.php");
        exit;
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        $stmt = $db->prepare("UPDATE users SET username = ?, name = ?, email = ?, status = ? WHERE id = ?");
        $stmt->execute([$username, $name, $email, $status, $id]);
        header("Location: ../frontend/user.php");
        exit;
    }
}

// Best Practice: If this file is accessed without a valid POST, redirect back.
header("Location: ../frontend/user.php");
exit;
?>
```

- [ ] **Step 5: Add session check + isset guards to `backend/process_user.php`**

Replace the entire file:

```php
<?php
// backend/process_user.php

// Best Practice: Session check before any data modification.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: /ShoeInventorySystem/frontend/login.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/UserManager.php';

$db = new Database();
$userMgr = new UserManager($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Best Practice: Use ?? operator to provide safe defaults for all POST data.
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'User');

        // Best Practice: Validate required fields are not empty before database operations.
        if ($username !== '' && $password !== '') {
            $userMgr->addUser($username, $password, $role);
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $userMgr->deleteUser($id);
        }
    }

    header("Location: ../frontend/user.php");
    exit;
}

header("Location: ../frontend/user.php");
exit;
?>
```

- [ ] **Step 6: Simplify `backend/logout.php`**

Replace the entire file:

```php
<?php
// backend/logout.php

// Best Practice: Always check session state before calling session functions.
// session_unset() clears all session variables, session_destroy() ends the session.
// Together they ensure a clean logout with no leftover session data.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
session_unset();
session_destroy();
header('Location: /ShoeInventorySystem/frontend/login.php');
exit;
```

- [ ] **Step 7: Add session check to `frontend/export_xml.php`**

Add session check at the top:

```php
<?php
// frontend/export_xml.php

// Best Practice: Even file-download endpoints need authentication.
// Without this, anyone could visit this URL and export all transaction data.
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once '../backend/classes/Database.php';
// ... rest of file unchanged
```

- [ ] **Step 8: Commit backend fixes**

```bash
git add backend/suppliertab.php backend/itemtab.php backend/stock_delete.php backend/user_action.php backend/process_user.php backend/logout.php frontend/export_xml.php
git commit -m "Fix backend: add session checks, remove duplicate code, add input validation"
```

---

### Task 2: Create Shared CSS Base File

**Files:**
- Create: `css/base.css`

- [ ] **Step 1: Create `css/base.css` with design tokens and shared component styles**

This file contains all the shared styles: CSS variables (design tokens), sidebar, tables, modals, forms, buttons, cards, badges, and responsive breakpoints. Every page will import this file, eliminating the massive CSS duplication.

```css
/* css/base.css
 *
 * SHARED DESIGN SYSTEM for the Shoes Inventory System.
 * ----------------------------------------------------------
 * This file defines CSS custom properties (variables) so that
 * colors, fonts, and spacing are consistent across every page.
 *
 * Best Practice: Using CSS variables (custom properties) means
 * you only change a color in ONE place and it updates everywhere.
 * This is called a "design token" system.
 */

/* ── Design Tokens ─────────────────────────────────────────── */
:root {
  /* Primary colors */
  --color-primary: #2563eb;
  --color-primary-light: #eff6ff;
  --color-primary-hover: #1d4ed8;

  /* Neutral colors */
  --color-bg: #f8f9fb;
  --color-surface: #ffffff;
  --color-border: #e5e7eb;
  --color-border-light: #f3f4f6;

  /* Text colors */
  --color-text: #111827;
  --color-text-secondary: #6b7280;
  --color-text-muted: #9ca3af;

  /* Status colors */
  --color-danger: #dc2626;
  --color-danger-light: #fef2f2;
  --color-success: #16a34a;
  --color-success-light: #f0fdf4;
  --color-warning: #d97706;
  --color-warning-light: #fffbeb;

  /* Typography */
  --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --font-size-xs: 0.72rem;
  --font-size-sm: 0.82rem;
  --font-size-base: 0.9rem;
  --font-size-lg: 1.1rem;
  --font-size-xl: 1.3rem;

  /* Spacing */
  --radius-sm: 8px;
  --radius-md: 10px;
  --radius-lg: 12px;
  --radius-full: 99px;

  /* Sidebar */
  --sidebar-width: 230px;
}

/* ── Global Reset ──────────────────────────────────────────── */
*, *::before, *::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: var(--font-family);
  background: var(--color-bg);
  color: var(--color-text);
  min-height: 100vh;
}

/* Link to Google Fonts for Inter is added in HTML <head>.
 * We include a fallback stack so the page still looks good
 * even if the font CDN is unavailable. */

/* ── Page Layout ───────────────────────────────────────────── 
 * Uses CSS Grid to create a sidebar + main content layout.
 * The sidebar is fixed-width; the main content fills the rest.
 */
.page-wrapper {
  display: grid;
  grid-template-columns: var(--sidebar-width) 1fr;
  min-height: 100vh;
}

/* ── Sidebar ───────────────────────────────────────────────── */
.sidebar {
  background: var(--color-surface);
  border-right: 1px solid var(--color-border);
  padding: 20px 0;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: var(--sidebar-width);
  z-index: 100;
  overflow-y: auto;
}

.sidebar-brand {
  padding: 0 20px 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid var(--color-border-light);
  margin-bottom: 12px;
}

.sidebar-brand .brand-icon {
  width: 34px;
  height: 34px;
  background: var(--color-primary);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sidebar-brand .brand-icon img {
  width: 20px;
  height: 20px;
  filter: brightness(0) invert(1);
}

.sidebar-brand span {
  font-weight: 700;
  font-size: var(--font-size-base);
  color: var(--color-text);
}

/* Navigation links inside the sidebar */
.sidebar-nav {
  list-style: none;
  padding: 0 12px;
  flex: 1;
}

.sidebar-nav li a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  text-decoration: none;
  margin-bottom: 2px;
  transition: background 0.15s, color 0.15s;
}

.sidebar-nav li a:hover {
  background: var(--color-border-light);
  color: var(--color-text);
}

/* Best Practice: The "active" class highlights the current page in the nav.
 * This gives users a clear visual indicator of where they are. */
.sidebar-nav li a.active {
  background: var(--color-primary-light);
  color: var(--color-primary);
  font-weight: 600;
}

.sidebar-nav li a i {
  width: 18px;
  text-align: center;
  font-size: 0.85rem;
}

/* User profile section at bottom of sidebar */
.sidebar-user {
  padding: 16px 20px;
  border-top: 1px solid var(--color-border-light);
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: auto;
}

.sidebar-user .user-avatar {
  width: 32px;
  height: 32px;
  background: var(--color-primary-light);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--font-size-xs);
  font-weight: 700;
  color: var(--color-primary);
  flex-shrink: 0;
}

.sidebar-user .user-info {
  flex: 1;
  min-width: 0;
}

.sidebar-user .user-name {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--color-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-user .user-role {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
}

.sidebar-user .logout-btn {
  background: none;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 6px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s, background 0.15s;
}

.sidebar-user .logout-btn:hover {
  color: var(--color-danger);
  background: var(--color-danger-light);
}

/* ── Main Content Area ────────────────────────────────────── */
.main-content {
  margin-left: var(--sidebar-width);
  padding: 28px 32px;
  min-height: 100vh;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: var(--font-size-xl);
  font-weight: 700;
  color: var(--color-text);
  margin: 0;
}

.page-header p {
  font-size: var(--font-size-sm);
  color: var(--color-text-muted);
  margin: 4px 0 0;
}

/* ── KPI Stat Cards ───────────────────────────────────────── 
 * These are the summary cards at the top of dashboard/management pages.
 * CSS Grid auto-fills columns so they wrap on smaller screens.
 */
.stat-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}

.stat-card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  padding: 18px;
  border: 1px solid var(--color-border);
}

.stat-card .stat-label {
  font-size: var(--font-size-xs);
  color: var(--color-text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-card .stat-value {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--color-text);
  margin-top: 4px;
}

.stat-card.danger .stat-label { color: var(--color-danger); }
.stat-card.danger .stat-value { color: var(--color-danger); }

/* ── Toolbar (search + action buttons row) ────────────────── */
.toolbar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.toolbar .search-input {
  flex: 1;
  min-width: 200px;
  max-width: 360px;
  padding: 10px 14px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-family: var(--font-family);
  background: var(--color-surface);
  color: var(--color-text);
  outline: none;
  transition: border-color 0.15s;
}

.toolbar .search-input:focus {
  border-color: var(--color-primary);
}

.toolbar select {
  padding: 10px 14px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-family: var(--font-family);
  background: var(--color-surface);
  color: var(--color-text);
  outline: none;
  cursor: pointer;
}

.toolbar select:focus {
  border-color: var(--color-primary);
}

/* ── Buttons ──────────────────────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-weight: 600;
  font-family: var(--font-family);
  cursor: pointer;
  border: none;
  transition: background 0.15s, transform 0.1s;
  text-decoration: none;
}

.btn:active { transform: scale(0.97); }

.btn-primary {
  background: var(--color-primary);
  color: #fff;
}
.btn-primary:hover { background: var(--color-primary-hover); }

.btn-secondary {
  background: var(--color-border-light);
  color: var(--color-text-secondary);
}
.btn-secondary:hover { background: var(--color-border); }

.btn-danger {
  background: var(--color-danger-light);
  color: var(--color-danger);
}
.btn-danger:hover { background: #fde8e8; }

.btn-sm {
  padding: 6px 12px;
  font-size: var(--font-size-xs);
}

/* ── Data Tables ──────────────────────────────────────────── 
 * Best Practice: Wrapping tables in a card with overflow-x: auto
 * allows them to scroll horizontally on small screens instead of
 * breaking the page layout.
 */
.table-card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  overflow: hidden;
  margin-bottom: 20px;
}

.table-card-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--color-border-light);
  font-size: var(--font-size-base);
  font-weight: 700;
  color: var(--color-text);
  display: flex;
  align-items: center;
  gap: 8px;
}

.table-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--font-size-sm);
}

.data-table thead tr {
  background: #f9fafb;
}

.data-table th {
  text-align: left;
  padding: 10px 20px;
  color: var(--color-text-secondary);
  font-weight: 600;
  font-size: var(--font-size-xs);
  text-transform: uppercase;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

.data-table td {
  padding: 12px 20px;
  border-top: 1px solid var(--color-border-light);
  color: var(--color-text);
  vertical-align: middle;
}

.data-table tbody tr:hover {
  background: #fafbfc;
}

.data-table .empty-row td {
  text-align: center;
  padding: 32px 20px;
  color: var(--color-text-muted);
}

/* ── Badges ───────────────────────────────────────────────── */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: 600;
}

.badge-success {
  background: var(--color-success-light);
  color: var(--color-success);
}

.badge-danger {
  background: var(--color-danger-light);
  color: var(--color-danger);
}

.badge-warning {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.badge-neutral {
  background: var(--color-border-light);
  color: var(--color-text-secondary);
}

/* ── Modal Overlay ────────────────────────────────────────── 
 * Best Practice: Using a semi-transparent overlay behind modals
 * focuses user attention on the modal content. The "flex" centering
 * keeps it perfectly centered regardless of content height.
 */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

/* Used for hash-based modals — hidden by default, shown on :target */
.modal-overlay.hash-modal {
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
}

.modal-overlay.hash-modal:target {
  opacity: 1;
  pointer-events: auto;
}

.modal-box {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 520px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid var(--color-border-light);
}

.modal-header h2, .modal-header .modal-title {
  font-size: var(--font-size-lg);
  font-weight: 700;
  color: var(--color-text);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.4rem;
  color: var(--color-text-muted);
  cursor: pointer;
  text-decoration: none;
  padding: 4px 8px;
  border-radius: var(--radius-sm);
  line-height: 1;
}

.modal-close:hover {
  background: var(--color-border-light);
  color: var(--color-text);
}

.modal-body {
  padding: 20px 24px;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--color-border-light);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* ── Form Elements ────────────────────────────────────────── */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-grid .full-width {
  grid-column: span 2;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: var(--font-size-xs);
  font-weight: 600;
  color: var(--color-text);
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px 14px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-family: var(--font-family);
  background: var(--color-bg);
  color: var(--color-text);
  outline: none;
  transition: border-color 0.15s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--color-primary);
  background: var(--color-surface);
}

/* ── Progress Bar (for stock levels) ──────────────────────── */
.progress-bar {
  height: 6px;
  background: var(--color-border-light);
  border-radius: 3px;
  margin-top: 4px;
  overflow: hidden;
}

.progress-bar .fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.3s;
}

.progress-bar .fill.success { background: var(--color-success); }
.progress-bar .fill.danger { background: var(--color-danger); }

/* ── Utility Classes ──────────────────────────────────────── */
.text-danger { color: var(--color-danger) !important; }
.text-success { color: var(--color-success) !important; }
.text-warning { color: var(--color-warning) !important; }
.text-muted { color: var(--color-text-muted) !important; }
.font-bold { font-weight: 600 !important; }

/* ── Responsive Design ────────────────────────────────────── 
 * Best Practice: Media queries adapt the layout for smaller screens.
 * Below 768px, the sidebar collapses and content goes full-width.
 */
@media (max-width: 768px) {
  .page-wrapper {
    grid-template-columns: 1fr;
  }

  .sidebar {
    position: fixed;
    left: -260px;
    transition: left 0.3s;
    box-shadow: 2px 0 12px rgba(0,0,0,0.1);
  }

  .sidebar.open { left: 0; }

  .main-content {
    margin-left: 0;
    padding: 20px 16px;
  }

  .stat-cards {
    grid-template-columns: repeat(2, 1fr);
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-grid .full-width {
    grid-column: span 1;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .toolbar .search-input {
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .stat-cards {
    grid-template-columns: 1fr;
  }
}
```

- [ ] **Step 2: Commit base CSS**

```bash
git add css/base.css
git commit -m "Add shared CSS base file with design tokens and component styles"
```

---

### Task 3: Revamp Login & Register Pages

**Files:**
- Modify: `frontend/login.php`
- Modify: `css/login_style.css`
- Modify: `frontend/register.php`
- Modify: `css/register_styles.css`

- [ ] **Step 1: Rewrite `css/login_style.css`**

Replace the entire file with the light & minimal login styles:

```css
/* css/login_style.css
 * Light & minimal centered card design for the login page.
 * Uses the same design tokens as the rest of the app for consistency.
 */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f8f9fb;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* ── Login Card ────────────────────────────────────────────── */
.login-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
  padding: 40px 36px;
  width: 100%;
  max-width: 400px;
}

.login-brand {
  text-align: center;
  margin-bottom: 28px;
}

.login-brand .brand-icon {
  width: 48px;
  height: 48px;
  background: #2563eb;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.login-brand .brand-icon img {
  width: 26px;
  height: 26px;
  filter: brightness(0) invert(1);
}

.login-brand h1 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.login-brand p {
  font-size: 0.82rem;
  color: #9ca3af;
  margin-top: 4px;
}

/* ── Form Styles ───────────────────────────────────────────── */
.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.88rem;
  font-family: inherit;
  background: #f3f4f6;
  color: #111827;
  outline: none;
  transition: border-color 0.15s, background 0.15s;
}

.form-group input:focus {
  border-color: #2563eb;
  background: #fff;
}

/* ── Submit Button ─────────────────────────────────────────── */
.btn-login {
  width: 100%;
  padding: 11px;
  border: none;
  border-radius: 10px;
  background: #2563eb;
  color: #fff;
  font-size: 0.88rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s;
  margin-top: 4px;
}

.btn-login:hover {
  background: #1d4ed8;
}

/* ── Footer Link ───────────────────────────────────────────── */
.login-footer {
  text-align: center;
  margin-top: 22px;
  font-size: 0.82rem;
  color: #9ca3af;
}

.login-footer a {
  color: #2563eb;
  font-weight: 600;
  text-decoration: none;
}

.login-footer a:hover {
  text-decoration: underline;
}

/* ── Error Message ─────────────────────────────────────────── */
.alert-error {
  background: #fef2f2;
  color: #dc2626;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 0.82rem;
  margin-bottom: 18px;
  border: 1px solid #fecaca;
}

/* ── Success Modal ─────────────────────────────────────────── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: #fff;
  border-radius: 16px;
  padding: 28px;
  max-width: 380px;
  width: 100%;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
  text-align: center;
}

.modal h4 {
  font-size: 1rem;
  color: #111827;
  margin-bottom: 8px;
}

.modal p {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 20px;
}

.modal .btn-ok {
  padding: 8px 24px;
  border: none;
  border-radius: 10px;
  background: #2563eb;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.modal .btn-ok:hover {
  background: #1d4ed8;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 480px) {
  .login-card {
    padding: 28px 20px;
  }
}
```

- [ ] **Step 2: Rewrite `frontend/login.php` HTML**

Replace the entire file:

```php
<?php
// frontend/login.php

// Best Practice: Include db.php to start sessions and load shared utilities.
require_once __DIR__ . '/../backend/db.php';

// Check for success message from registration
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
// Check for login errors
$error = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShoeInventory</title>
    <link rel="stylesheet" href="../css/login_style.css">
</head>
<body>
    <?php if ($registered): ?>
    <!-- Success modal shown after registration -->
    <div class="modal-backdrop" id="successModal">
        <div class="modal">
            <h4>Registration Successful</h4>
            <p>Your account has been created. Please log in to continue.</p>
            <button class="btn-ok" id="modalOk">OK</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="login-card">
        <div class="login-brand">
            <div class="brand-icon">
                <img src="../images/shoes.png" alt="ShoeInventory Logo">
            </div>
            <h1>ShoeInventory</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error === 'invalid' || $error === '1'): ?>
        <!-- Best Practice: Show user-friendly error messages, never expose system details -->
        <div class="alert-error">Invalid username or password. Please try again.</div>
        <?php endif; ?>

        <form method="POST" action="../backend/process_login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <p class="login-footer">Don't have an account? <a href="register.php">Register</a></p>
    </div>

    <?php if ($registered): ?>
    <script>
        // Auto-dismiss the success modal after 4 seconds or on click
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('successModal');
            var ok = document.getElementById('modalOk');
            function hide() { if (modal) modal.style.display = 'none'; }
            if (ok) ok.addEventListener('click', hide);
            setTimeout(hide, 4000);
        });
    </script>
    <?php endif; ?>
</body>
</html>
```

- [ ] **Step 3: Rewrite `css/register_styles.css`**

Replace the entire file:

```css
/* css/register_styles.css
 * Matches the login page design — same centered card, same tokens.
 * Imports login_style.css to avoid duplicating shared styles.
 */

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f8f9fb;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.register-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
  padding: 36px 36px;
  width: 100%;
  max-width: 440px;
}

.register-brand {
  text-align: center;
  margin-bottom: 24px;
}

.register-brand .brand-icon {
  width: 48px;
  height: 48px;
  background: #2563eb;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.register-brand .brand-icon img {
  width: 26px;
  height: 26px;
  filter: brightness(0) invert(1);
}

.register-brand h1 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.register-brand p {
  font-size: 0.82rem;
  color: #9ca3af;
  margin-top: 4px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.88rem;
  font-family: inherit;
  background: #f3f4f6;
  color: #111827;
  outline: none;
  transition: border-color 0.15s, background 0.15s;
}

.form-group input:focus {
  border-color: #2563eb;
  background: #fff;
}

.btn-register {
  width: 100%;
  padding: 11px;
  border: none;
  border-radius: 10px;
  background: #2563eb;
  color: #fff;
  font-size: 0.88rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: background 0.15s;
  margin-top: 4px;
}

.btn-register:hover {
  background: #1d4ed8;
}

.register-footer {
  text-align: center;
  margin-top: 20px;
  font-size: 0.82rem;
  color: #9ca3af;
}

.register-footer a {
  color: #2563eb;
  font-weight: 600;
  text-decoration: none;
}

.register-footer a:hover {
  text-decoration: underline;
}

.alert-error {
  background: #fef2f2;
  color: #dc2626;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 0.82rem;
  margin-bottom: 16px;
  border: 1px solid #fecaca;
}

@media (max-width: 480px) {
  .register-card {
    padding: 24px 18px;
  }
}
```

- [ ] **Step 4: Rewrite `frontend/register.php` HTML**

Replace the entire file:

```php
<?php
// frontend/register.php
require_once __DIR__ . '/../backend/db.php';
$error = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShoeInventory</title>
    <link rel="stylesheet" href="../css/register_styles.css">
</head>
<body>
    <div class="register-card">
        <div class="register-brand">
            <div class="brand-icon">
                <img src="../images/shoes.png" alt="ShoeInventory Logo">
            </div>
            <h1>ShoeInventory</h1>
            <p>Create a new account</p>
        </div>

        <?php if ($error === 'exists'): ?>
        <div class="alert-error">Username or email already taken. Please try another.</div>
        <?php elseif ($error === 'invalid_input'): ?>
        <div class="alert-error">All fields are required and email must be valid.</div>
        <?php elseif ($error): ?>
        <div class="alert-error">An error occurred. Please try again.</div>
        <?php endif; ?>

        <form method="POST" action="../backend/process_register.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="repeatpassword">Confirm Password</label>
                <input type="password" id="repeatpassword" name="repeatpassword" placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <p class="register-footer">Already have an account? <a href="login.php">Sign In</a></p>
    </div>
</body>
</html>
```

- [ ] **Step 5: Commit login/register revamp**

```bash
git add frontend/login.php frontend/register.php css/login_style.css css/register_styles.css
git commit -m "Revamp login and register pages with light & minimal design"
```

---

### Task 4: Revamp Dashboard Page

**Files:**
- Modify: `frontend/index.php`
- Modify: `css/dashboard_style.css`

- [ ] **Step 1: Rewrite `css/dashboard_style.css`**

Replace entire file with dashboard-specific overrides (base.css handles the shared styles):

```css
/* css/dashboard_style.css
 * Dashboard-specific styles. Uses base.css for shared layout/components.
 * Only contains styles unique to the dashboard page.
 */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');

/* No page-specific overrides needed — base.css covers everything.
 * If dashboard needs unique styles in the future, add them here. */
```

- [ ] **Step 2: Rewrite `frontend/index.php`**

Replace entire file with the new sidebar layout:

```php
<?php
// frontend/index.php

// Best Practice: Check login status before showing any page content.
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// db.php loads Database, InventoryManager, TransactionManager
// and fetches all dashboard data ($totalItems, $lowStockItems, etc.)
require_once __DIR__ . '/../backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard_style.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- ── Sidebar Navigation ──────────────────────────────── -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div>
                <span>ShoeInventory</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= safe($_SESSION['username']) ?></div>
                    <div class="user-role">User</div>
                </div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </aside>

        <!-- ── Main Content ────────────────────────────────────── -->
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Overview of your inventory</p>
            </div>

            <!-- KPI Stat Cards -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-label">Total Items</div>
                    <div class="stat-value"><?= safe($totalItems) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Suppliers</div>
                    <div class="stat-value"><?= safe($activeSuppliers) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">System Users</div>
                    <div class="stat-value"><?= safe($systemUsers) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Transactions</div>
                    <div class="stat-value"><?= safe($transactionsCount) ?></div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Low Stock Alerts</div>
                    <div class="stat-value"><?= safe($lowStockAlerts) ?></div>
                </div>
            </div>

            <!-- Two-column table row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Low Stock Alerts -->
                <div class="table-card">
                    <div class="table-card-header">Low Stock Alerts</div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Current</th><th>Min</th><th>Supplier</th></tr></thead>
                            <tbody>
                                <?php if (!empty($lowStockItems)): foreach ($lowStockItems as $item): ?>
                                <tr>
                                    <td><strong><?= safe($item['item_name']) ?></strong></td>
                                    <td class="text-danger font-bold"><?= safe($item['quantity']) ?></td>
                                    <td><?= safe($item['min_quantity']) ?></td>
                                    <td><?= safe($item['supplier_name']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="4">No low stock items.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="table-card">
                    <div class="table-card-header">Recent Transactions</div>
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>By</th></tr></thead>
                            <tbody>
                                <?php if (!empty($recentTransactions)): foreach ($recentTransactions as $tx): ?>
                                <tr>
                                    <td><?= safe($tx['item_name']) ?></td>
                                    <td><span class="font-bold"><?= safe($tx['transaction_type']) ?></span></td>
                                    <td><?= safe($tx['quantity']) ?></td>
                                    <td><?= safe($tx['user_name']) ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr class="empty-row"><td colspan="4">No recent transactions.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inventory Master Table -->
            <div class="table-card">
                <div class="table-card-header">Inventory Overview</div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Item Name</th><th>In Stock</th><th>Min</th><th>Supplier</th><th>Price</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $it): ?>
                            <tr>
                                <td>#<?= safe($it['id']) ?></td>
                                <td><strong><?= safe($it['name']) ?></strong></td>
                                <td><?= safe($it['quantity']) ?></td>
                                <td><?= safe($it['min_quantity']) ?></td>
                                <td><?= safe($it['supplier_name']) ?></td>
                                <td class="font-bold">$<?= number_format((float)$it['price'], 2) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="6">No items in inventory.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
```

- [ ] **Step 3: Commit dashboard revamp**

```bash
git add frontend/index.php css/dashboard_style.css
git commit -m "Revamp dashboard with sidebar layout and clean stat cards"
```

---

### Task 5: Revamp Items Page

**Files:**
- Modify: `frontend/item.php`
- Modify: `css/Item.css`

- [ ] **Step 1: Rewrite `css/Item.css`**

Replace entire file — imports base.css, only adds item-page-specific modal trigger style:

```css
/* css/Item.css — Item management page styles. Imports base.css for shared components. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');
/* No page-specific overrides needed */
```

- [ ] **Step 2: Rewrite `frontend/item.php`**

Replace entire file with sidebar layout, consistent nav, fixed logout path:

```php
<?php
// frontend/item.php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/itemtab.php';

// Best Practice: Define a helper for escaping output if not already loaded.
if (!function_exists('safe')) {
    function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/Item.css">
    <?php if ($editing_item): ?>
    <!-- Best Practice: Show edit modal immediately when edit_id is in URL -->
    <style>#editItemModal { opacity: 1; pointer-events: auto; }</style>
    <?php endif; ?>
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div>
                <span>ShoeInventory</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php" class="active"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= safe($_SESSION['username']) ?></div>
                    <div class="user-role">User</div>
                </div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h1>Items Management</h1>
                    <p>Add, edit, and manage shoe inventory items</p>
                </div>
                <a href="#addItemModal" class="btn btn-primary">+ Add New Item</a>
            </div>

            <form method="GET" action="item.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search shoes by name..." value="<?= safe($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="item.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr><th>#</th><th>Shoe Model</th><th>Price</th><th>Supplier</th><th>Stock</th><th>Min</th><th style="text-align:center;">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $item):
                                $qty = (int)$item['quantity'];
                                $min = (int)$item['min_quantity'];
                            ?>
                            <tr>
                                <td><?= (int)$item['id'] ?></td>
                                <td><strong><?= safe($item['name']) ?></strong></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td><?= safe($item['supplier_name'] ?: '—') ?></td>
                                <td>
                                    <span class="badge <?= $qty <= $min ? 'badge-danger' : 'badge-success' ?>">
                                        <?= $qty ?> pairs
                                    </span>
                                </td>
                                <td><?= $min ?> pairs</td>
                                <td style="text-align:center;">
                                    <a href="item.php?edit_id=<?= (int)$item['id'] ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <button class="btn btn-danger btn-sm" onclick="if(confirm('Delete this item?')) window.location.href='item.php?delete_id=<?= (int)$item['id'] ?>';">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Item Modal -->
    <div class="modal-overlay hash-modal" id="addItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <a href="#" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" placeholder="e.g. Air Max 90" required>
                        </div>
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" value="0.00" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold</label>
                            <input type="number" name="min_quantity" value="5" min="0">
                        </div>
                        <div class="form-group full-width">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">— None —</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int)$s['id'] ?>"><?= safe($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editing_item): ?>
    <!-- Edit Item Modal -->
    <div class="modal-overlay" id="editItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Edit Item #<?= (int)$editing_item['id'] ?></h2>
                <a href="item.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)$editing_item['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" value="<?= safe($editing_item['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" name="price" value="<?= safe($editing_item['price']) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold</label>
                            <input type="number" name="min_quantity" value="<?= (int)$editing_item['min_quantity'] ?>" min="0">
                        </div>
                        <div class="form-group full-width">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">— None —</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int)$s['id'] ?>" <?= $editing_item['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= safe($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="item.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
```

- [ ] **Step 3: Commit items revamp**

```bash
git add frontend/item.php css/Item.css
git commit -m "Revamp items page with sidebar layout and consistent design"
```

---

### Task 6: Revamp Suppliers Page

**Files:**
- Modify: `frontend/Supplier.php`
- Modify: `css/Supplierstyle.css`

- [ ] **Step 1: Rewrite `css/Supplierstyle.css`**

```css
/* css/Supplierstyle.css — Supplier page styles. Imports base.css. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');
```

- [ ] **Step 2: Rewrite `frontend/Supplier.php`**

Replace entire file with sidebar layout, same nav pattern, fixed logout path. Uses same pattern as item.php but for suppliers:

```php
<?php
// frontend/Supplier.php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/suppliertab.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/Supplierstyle.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php" class="active"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
                <div><h1>Suppliers Management</h1><p>Manage your supplier contacts and details</p></div>
                <a href="#add-supplier-modal" class="btn btn-primary">+ Add Supplier</a>
            </div>

            <form method="GET" action="Supplier.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search suppliers..." value="<?= safe($search ?? '') ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="Supplier.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Company</th><th>Contact</th><th>Category</th><th>Phone/Email</th><th>Status</th><th style="text-align:center;">Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($suppliers)): foreach ($suppliers as $row): ?>
                            <tr>
                                <td><?= (int)$row['order_id'] ?></td>
                                <td><strong><?= safe($row['company_name']) ?></strong></td>
                                <td><?= safe($row['contact_person'] ?? '') ?></td>
                                <td><?= safe($row['category'] ?? '') ?></td>
                                <td><?= safe($row['phone_email'] ?? '') ?></td>
                                <td><span class="badge <?= $row['status'] === 'Active' ? 'badge-success' : 'badge-neutral' ?>"><?= safe($row['status']) ?></span></td>
                                <td style="text-align:center;">
                                    <a href="Supplier.php?edit_id=<?= (int)$row['order_id'] ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <a href="Supplier.php?delete_id=<?= (int)$row['order_id'] ?>" class="btn btn-danger btn-sm" style="text-decoration:none;" onclick="return confirm('Delete this supplier?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No suppliers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Supplier Modal -->
    <div id="add-supplier-modal" class="modal-overlay hash-modal">
        <div class="modal-box">
            <div class="modal-header"><h2>Add Supplier</h2><a href="#" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="add">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Company Name *</label><input type="text" name="supplier_name" required></div>
                        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category"></div>
                        <div class="form-group full-width"><label>Phone / Email</label><input type="text" name="phone_email"></div>
                        <div class="form-group full-width"><label>Status</label><select name="active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><a href="#" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Add Supplier</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div id="edit-supplier-modal" class="modal-overlay hash-modal">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit Supplier</h2><a href="Supplier.php" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <form method="POST" action="Supplier.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)($editing_supplier['order_id'] ?? 0) ?>">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Company Name *</label><input type="text" name="supplier_name" value="<?= safe($editing_supplier['company_name'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" value="<?= safe($editing_supplier['contact_person'] ?? '') ?>"></div>
                        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?= safe($editing_supplier['category'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Phone / Email</label><input type="text" name="phone_email" value="<?= safe($editing_supplier['phone_email'] ?? '') ?>"></div>
                        <div class="form-group full-width"><label>Status</label><select name="active"><option value="1" <?= ($editing_supplier['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option><option value="0" <?= ($editing_supplier['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><a href="Supplier.php" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editing_supplier): ?>
    <script>window.location.hash = "edit-supplier-modal";</script>
    <?php endif; ?>
</body>
</html>
```

- [ ] **Step 3: Commit suppliers revamp**

```bash
git add frontend/Supplier.php css/Supplierstyle.css
git commit -m "Revamp suppliers page with sidebar layout and consistent design"
```

---

### Task 7: Revamp Stock, Transactions, Users, Reports Pages

**Files:**
- Modify: `frontend/stock.php`, `css/stockstyle.css`
- Modify: `frontend/transactions.php`, `css/transactions_style.css`
- Modify: `frontend/user.php`, `css/userstyle.css`
- Modify: `frontend/reports.php`, `css/reportanalysis.css`

These four pages follow the exact same pattern: sidebar layout, consistent nav with correct links, fixed logout paths. Each CSS file becomes a thin import of base.css.

- [ ] **Step 1: Rewrite all four CSS files**

Each file gets the same content — just imports base.css:

**css/stockstyle.css:**
```css
/* css/stockstyle.css — Stock page styles. Imports base.css for shared components. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');
```

**css/transactions_style.css:**
```css
/* css/transactions_style.css — Transactions page styles. Imports base.css. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');
```

**css/userstyle.css:**
```css
/* css/userstyle.css — Users page styles. Imports base.css. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');
```

**css/reportanalysis.css:**
```css
/* css/reportanalysis.css — Reports page styles. Imports base.css. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('base.css');
```

- [ ] **Step 2: Rewrite `frontend/stock.php`**

Replace entire file. Keeps all existing PHP logic but uses new sidebar layout, consistent nav, and base.css classes:

```php
<?php
// frontend/stock.php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once __DIR__ . '/../backend/classes/Database.php';
require_once __DIR__ . '/../backend/classes/StockManager.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$database = new Database();
$pdo = $database->getConnection();
$stockManager = new StockManager($pdo);

$filters = array_filter(['search' => trim($_GET['search'] ?? ''), 'category' => trim($_GET['category'] ?? '')]);

// Handle stock update
if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST['action'] ?? '') === 'update_stock') {
    $success = $stockManager->updateGlobalInventorySync(
        (int)($_POST['stock_id'] ?? 0), (int)($_POST['item_id'] ?? 0), (int)($_POST['supplier_id'] ?? 0),
        trim($_POST['item_name'] ?? ''), trim($_POST['company_name'] ?? ''),
        (float)($_POST['current_qty'] ?? 0), (float)($_POST['min_threshold'] ?? 0)
    );
    if ($success) { header("Location: stock.php" . ($filters ? '?' . http_build_query($filters) : '')); exit; }
}

$totalItems = $stockManager->getTotalItemsCount();
$okStock = $stockManager->getOkStockCount();
$lowStock = $totalItems - $okStock;
$categories = $stockManager->getDistinctCategories();
$inventoryItems = $stockManager->getFilteredStock($filters);
$editItem = isset($_GET['edit_id']) ? $stockManager->getStockById($_GET['edit_id']) : null;
$cancelUrl = 'stock.php' . ($filters ? '?' . http_build_query($filters) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/stockstyle.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header"><h1>Stock Management</h1><p>Monitor stock levels and set thresholds</p></div>

            <div class="stat-cards">
                <div class="stat-card"><div class="stat-label">Total Items</div><div class="stat-value"><?= safe($totalItems) ?></div></div>
                <div class="stat-card"><div class="stat-label" style="color:var(--color-success)">OK Stock</div><div class="stat-value" style="color:var(--color-success)"><?= safe($okStock) ?></div></div>
                <div class="stat-card danger"><div class="stat-label">Low / Critical</div><div class="stat-value"><?= safe($lowStock) ?></div></div>
            </div>

            <form method="GET" action="stock.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search shoe name..." value="<?= safe($filters['search'] ?? '') ?>">
                <select name="category" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="All Categories">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= safe($cat['category']) ?>" <?= ($filters['category'] ?? '') === $cat['category'] ? 'selected' : '' ?>><?= safe($cat['category']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="stock.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Supplier</th><th>Qty</th><th>Min</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($inventoryItems)): foreach ($inventoryItems as $row):
                                $isLow = $row['current_qty'] < $row['min_threshold'];
                                $maxCap = max($row['current_qty'], $row['min_threshold'] * 2);
                                $fill = ($maxCap > 0) ? min(($row['current_qty'] / $maxCap) * 100, 100) : 0;
                            ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <td><strong><?= safe($row['item_name']) ?></strong></td>
                                <td class="text-muted"><?= safe($row['category']) ?></td>
                                <td><?= safe($row['supplier_name']) ?></td>
                                <td>
                                    <span class="<?= $isLow ? 'text-danger' : 'text-success' ?> font-bold"><?= number_format($row['current_qty'], 0) ?> <?= safe($row['unit'] ?? 'pairs') ?></span>
                                    <div class="progress-bar"><div class="fill <?= $isLow ? 'danger' : 'success' ?>" style="width:<?= $fill ?>%"></div></div>
                                </td>
                                <td><?= number_format($row['min_threshold'], 0) ?> <?= safe($row['unit'] ?? 'pairs') ?></td>
                                <td><span class="badge <?= $isLow ? 'badge-danger' : 'badge-success' ?>"><?= $isLow ? 'Low' : 'OK' ?></span></td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($row['last_updated'])) ?></td>
                                <td>
                                    <a href="stock.php?<?= http_build_query(array_merge($filters, ['edit_id' => $row['id']])) ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">Edit</a>
                                    <a href="../backend/stock_delete.php?<?= http_build_query(array_merge($filters, ['id' => $row['id']])) ?>" class="btn btn-danger btn-sm" style="text-decoration:none;" onclick="return confirm('Delete this product?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="9">No stock items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php if ($editItem): ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit Stock Level</h2><a href="<?= $cancelUrl ?>" class="modal-close">&times;</a></div>
            <div class="modal-body">
                <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin-bottom:16px;">Editing: <strong style="color:var(--color-text)"><?= safe($editItem['item_name']) ?></strong></p>
                <form method="POST" action="stock.php?<?= http_build_query($filters) ?>">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="stock_id" value="<?= safe($editItem['id']) ?>">
                    <input type="hidden" name="item_id" value="<?= safe($editItem['item_id']) ?>">
                    <input type="hidden" name="supplier_id" value="<?= safe($editItem['supplier_id'] ?? '') ?>">
                    <input type="hidden" name="item_name" value="<?= safe($editItem['item_name']) ?>">
                    <input type="hidden" name="company_name" value="<?= safe($editItem['supplier_name']) ?>">
                    <div class="form-grid">
                        <div class="form-group"><label>Current Quantity</label><input type="number" step="0.01" name="current_qty" value="<?= safe($editItem['current_qty']) ?>" required></div>
                        <div class="form-group"><label>Min Threshold</label><input type="number" step="0.01" name="min_threshold" value="<?= safe($editItem['min_threshold']) ?>" required></div>
                    </div>
                    <div class="modal-footer"><a href="<?= $cancelUrl ?>" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
```

- [ ] **Step 3: Rewrite `frontend/transactions.php`**

Replace entire file with sidebar layout:

```php
<?php
// frontend/transactions.php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
require_once __DIR__ . '/../backend/Classes/Transaction.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$db = (new Database())->getConnection();
$txHandler = new Transaction($db);
$items = $db->query("SELECT id, name FROM items")->fetchAll(PDO::FETCH_ASSOC);
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? 'All Types';
$transactions = $txHandler->getAll($search, $type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/transactions_style.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php" class="active"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
                <div><h1>Transactions</h1><p>Log and track all inventory movements</p></div>
                <button class="btn btn-primary" onclick="document.getElementById('addTxModal').style.display='flex'">+ Log Transaction</button>
            </div>

            <form method="GET" action="transactions.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search..." value="<?= safe($search) ?>">
                <select name="type" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="All Types">All Types</option>
                    <option value="Sale" <?= $type == 'Sale' ? 'selected' : '' ?>>Sale</option>
                    <option value="Restock" <?= $type == 'Restock' ? 'selected' : '' ?>>Restock</option>
                    <option value="Waste" <?= $type == 'Waste' ? 'selected' : '' ?>>Waste</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="transactions.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Date</th><th>Item</th><th>Type</th><th>Qty</th><th>By</th><th>Reason</th></tr></thead>
                        <tbody>
                            <?php if (!empty($transactions)): foreach ($transactions as $tx): ?>
                            <tr>
                                <td>#<?= safe($tx['id']) ?></td>
                                <td class="text-muted"><?= safe($tx['transaction_date']) ?></td>
                                <td><strong><?= safe($tx['item_name']) ?></strong></td>
                                <td><span class="badge <?= $tx['transaction_type'] === 'Sale' ? 'badge-warning' : ($tx['transaction_type'] === 'Restock' ? 'badge-success' : 'badge-danger') ?>"><?= safe($tx['transaction_type']) ?></span></td>
                                <td><?= safe($tx['quantity']) ?></td>
                                <td><?= safe($tx['user_name']) ?></td>
                                <td class="text-muted"><?= safe($tx['reason']) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Transaction Modal -->
    <div id="addTxModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header"><h2>Log Transaction</h2><button class="modal-close" onclick="document.getElementById('addTxModal').style.display='none'">&times;</button></div>
            <div class="modal-body">
                <form method="POST" action="../backend/process_transaction.php">
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Item *</label><select name="item_id" required><?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= safe($i['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Type *</label><select name="type"><option value="Restock">Restock</option><option value="Sale">Sale</option><option value="Waste">Waste</option></select></div>
                        <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" required min="1"></div>
                        <div class="form-group full-width"><label>Reason</label><input type="text" name="reason" placeholder="Optional note"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('addTxModal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Add Transaction</button></div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 4: Rewrite `frontend/user.php`**

Replace entire file:

```php
<?php
// frontend/user.php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
require_once '../backend/classes/UserManager.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$db = new Database();
$userManager = new UserManager($db->getConnection());
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$users = $userManager->getFilteredUsers($search, $role);
$total_users = count($users);
$admins = 0; $active_users = 0;
foreach ($users as $u) {
    if (strtolower($u['role'] ?? '') === 'admin') $admins++;
    if (strtolower($u['status'] ?? '') === 'active') $active_users++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/userstyle.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php" class="active"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header"><h1>User Management</h1><p>View and manage system user accounts</p></div>

            <div class="stat-cards">
                <div class="stat-card"><div class="stat-label">Total Users</div><div class="stat-value"><?= $total_users ?></div></div>
                <div class="stat-card"><div class="stat-label">Administrators</div><div class="stat-value"><?= $admins ?></div></div>
                <div class="stat-card"><div class="stat-label">Active Users</div><div class="stat-value"><?= $active_users ?></div></div>
            </div>

            <form method="GET" action="user.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search name or email..." value="<?= safe($search) ?>">
                <select name="role" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="">All Roles</option>
                    <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Staff" <?= $role == 'Staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="User" <?= $role == 'User' ? 'selected' : '' ?>>User</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="user.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th style="text-align:center;">Actions</th></tr></thead>
                        <tbody>
                            <?php if (!empty($users)): foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= safe($user['id']) ?></td>
                                <td><?= safe($user['username'] ?? 'N/A') ?></td>
                                <td><strong><?= safe($user['name'] ?? '') ?></strong></td>
                                <td class="text-muted"><?= safe($user['email'] ?? 'N/A') ?></td>
                                <td><?= safe(ucfirst($user['role'] ?? 'User')) ?></td>
                                <td><span class="badge <?= strtolower($user['status'] ?? '') === 'active' ? 'badge-success' : 'badge-neutral' ?>"><?= safe(ucfirst($user['status'] ?? '')) ?></span></td>
                                <td style="text-align:center;">
                                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?= (int)$user['id'] ?>, '<?= safe($user['username'] ?? '') ?>', '<?= safe($user['name'] ?? '') ?>', '<?= safe($user['email'] ?? '') ?>', '<?= safe($user['status'] ?? 'active') ?>')">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= (int)$user['id'] ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="7">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header"><h2>Edit User</h2><button class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</button></div>
            <div class="modal-body">
                <form method="POST" action="../backend/user_action.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="form-grid">
                        <div class="form-group"><label>Username</label><input type="text" name="username" id="editUsername" required></div>
                        <div class="form-group"><label>Name</label><input type="text" name="name" id="editUserName" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" id="editUserEmail" required></div>
                        <div class="form-group"><label>Status</label><select name="status" id="editUserStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(id, username, name, email, status) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editUsername').value = username;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserStatus').value = status;
        document.getElementById('editModal').style.display = 'flex';
    }
    function deleteUser(id) {
        if (!confirm('Delete this user?')) return;
        var f = document.createElement('form');
        f.method = 'POST'; f.action = '../backend/user_action.php';
        f.innerHTML = '<input name="action" value="delete"><input name="id" value="'+id+'">';
        document.body.appendChild(f); f.submit();
    }
    </script>
</body>
</html>
```

- [ ] **Step 5: Rewrite `frontend/reports.php`**

Replace entire file:

```php
<?php
// frontend/reports.php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
require_once '../backend/classes/Database.php';
if (!function_exists('safe')) { function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$db = (new Database())->getConnection();
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? 'All Types';

// Best Practice: Use LEFT JOIN instead of INNER JOIN so transactions still show
// even if a supplier is missing (e.g. item has no supplier linked).
$query = "SELECT t.transaction_date, i.name as item_name, 
                 COALESCE(s.company_name, '—') as supplier_name,
                 t.transaction_type, t.quantity, u.username as user_name
          FROM transactions t
          JOIN items i ON t.item_id = i.id
          LEFT JOIN suppliers s ON i.supplier_id = s.order_id
          JOIN users u ON t.user_id = u.id
          WHERE 1=1";

if (!empty($search)) $query .= " AND i.name LIKE :search";
if ($type !== 'All Types') $query .= " AND t.transaction_type = :type";
$query .= " ORDER BY t.transaction_date DESC";

$stmt = $db->prepare($query);
if (!empty($search)) $stmt->bindValue(':search', "%$search%");
if ($type !== 'All Types') $stmt->bindValue(':type', $type);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/reportanalysis.css">
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand"><div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div><span>ShoeInventory</span></div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php"><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php"><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php"><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php"><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <li><a href="user.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php" class="active"><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info"><div class="user-name"><?= safe($_SESSION['username']) ?></div><div class="user-role">User</div></div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="page-header"><h1>Reports</h1><p>View, filter, and export transaction reports</p></div>

            <form method="GET" action="reports.php" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="Search by item name..." value="<?= safe($search) ?>">
                <select name="type" style="padding:10px 14px;border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-family:var(--font-family);background:var(--color-surface);">
                    <option value="All Types" <?= $type == 'All Types' ? 'selected' : '' ?>>All Types</option>
                    <option value="Sale" <?= $type == 'Sale' ? 'selected' : '' ?>>Sale</option>
                    <option value="Restock" <?= $type == 'Restock' ? 'selected' : '' ?>>Restock</option>
                    <option value="Waste" <?= $type == 'Waste' ? 'selected' : '' ?>>Waste</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="reports.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <div class="table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>Date</th><th>Item</th><th>Supplier</th><th>Type</th><th>Qty</th><th>By</th></tr></thead>
                        <tbody>
                            <?php if (!empty($reports)): foreach ($reports as $row): ?>
                            <tr>
                                <td class="text-muted"><?= safe($row['transaction_date']) ?></td>
                                <td><strong><?= safe($row['item_name']) ?></strong></td>
                                <td><?= safe($row['supplier_name']) ?></td>
                                <td><span class="badge <?= $row['transaction_type'] === 'Sale' ? 'badge-warning' : ($row['transaction_type'] === 'Restock' ? 'badge-success' : 'badge-danger') ?>"><?= safe($row['transaction_type']) ?></span></td>
                                <td><?= safe($row['quantity']) ?></td>
                                <td><?= safe($row['user_name']) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr class="empty-row"><td colspan="6">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px;">
                <button class="btn btn-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                <a href="export_xml.php" class="btn btn-primary" style="text-decoration:none;"><i class="fa-solid fa-file-export"></i> Export XML</a>
            </div>
        </main>
    </div>
</body>
</html>
```

- [ ] **Step 6: Commit remaining page revamps**

```bash
git add frontend/stock.php frontend/transactions.php frontend/user.php frontend/reports.php css/stockstyle.css css/transactions_style.css css/userstyle.css css/reportanalysis.css
git commit -m "Revamp stock, transactions, users, and reports pages with consistent design"
```

---

### Task 8: Update README

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Rewrite `README.md`**

Replace entire file with clear project explanation:

```markdown
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
```

- [ ] **Step 2: Commit README update**

```bash
git add README.md
git commit -m "Update README with comprehensive project documentation"
```
