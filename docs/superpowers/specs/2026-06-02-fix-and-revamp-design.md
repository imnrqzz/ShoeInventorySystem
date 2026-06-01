# ShoeInventorySystem — Fix & UI Revamp Design

**Date:** 2026-06-02
**Approach:** Fix-in-place (Approach A) — fix broken functionality and restyle existing files without restructuring

## Context

ShoeInventorySystem is a PHP/MySQL school project for managing shoe store inventory. It runs on XAMPP (Apache + MySQL). The app has login/register, dashboard, item/supplier/stock/transaction/user management, and reports.

The codebase has broken functionality (dashboard doesn't load data, inconsistent navigation links, missing session checks) and an inconsistent UI (3 different color schemes, 3 font families, massive CSS duplication). The goal is to make it work correctly and look appealing.

## Scope

### Backend — Essential Fixes

1. **Fix broken dashboard (index.php):** The dashboard references variables (`$totalItems`, `$activeSuppliers`, etc.) and a `safe()` function that are never defined. Add the database queries and data loading so the dashboard actually shows real data.

2. **Add missing session checks:** Several backend endpoints (`itemtab.php`, `suppliertab.php`, `stock_delete.php`, `user_action.php`) have no `session_start()` or authentication check. Add session verification so unauthenticated users can't modify data. `export_xml.php` in frontend also needs this.

3. **Fix navigation link inconsistencies:** Some pages link to `Item.php` (capital I), others to `item.php`. Standardize to lowercase `item.php`. Fix logout paths that point to different locations across pages.

4. **Fix duplicate code in suppliertab.php:** Lines 11-22 and 41-52 are nearly identical duplicate POST handling. Remove the duplicate.

5. **Add basic input validation:** Add `isset()` checks on POST parameters before accessing them. Cast IDs to integers. Validate required fields are non-empty.

6. **Fix process_login.php and logout.php:** Ensure login sets proper session variables and logout destroys session correctly.

### Frontend/UI — Light & Minimal Revamp

**Design tokens (consistent across all pages):**
- Background: `#f8f9fb`
- Card/sidebar: `#ffffff`
- Border: `#e5e7eb`
- Text primary: `#111827`
- Text secondary: `#6b7280`
- Text muted: `#9ca3af`
- Primary blue: `#2563eb`
- Primary blue light: `#eff6ff`
- Danger red: `#dc2626`
- Danger red light: `#fef2f2`
- Success green: `#16a34a`
- Success green light: `#f0fdf4`
- Warning amber: `#d97706`
- Warning amber light: `#fffbeb`
- Font: `'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`
- Border radius (cards): `12px`
- Border radius (inputs/buttons): `10px`
- Border radius (badges): `99px`

**Layout pattern (all pages except login/register):**
- White sidebar (220px) with logo, nav items, user profile at bottom
- Soft gray background (`#f8f9fb`) for main content area
- Content uses white cards with `border: 1px solid #e5e7eb` and `border-radius: 12px`

**Login/Register pages:**
- Centered white card on `#f8f9fb` background
- Logo + app name at top of card
- Rounded input fields with gray background
- Blue primary button, full width
- Link to register/login at bottom

**Components to restyle:**
- KPI stat cards (dashboard)
- Data tables (all CRUD pages)
- Modal dialogs (add/edit forms)
- Search bars
- Action buttons (add, edit, delete)
- Status badges (active/inactive, low stock/ok)
- Navigation sidebar with active state highlighting
- Form inputs and labels

**Responsive basics:**
- Sidebar collapses or hides below 768px
- Tables get horizontal scroll on small screens
- Cards stack vertically on mobile

### Documentation

- Update README.md to clearly explain how the system works, with updated screenshots description and feature explanations suitable for a school project submission.

## Out of Scope

- CSRF token implementation
- Role-based access control
- Environment variables for DB credentials
- Security audit logging
- Password strength validation
- Rate limiting
- CSS preprocessors or build tools

## File Changes Summary

**CSS files (restyle all 9):**
- `login_style.css`, `register_styles.css` — login/register card design
- `dashboard_style.css` — sidebar + KPI cards + tables
- `Item.css`, `Supplierstyle.css`, `stockstyle.css`, `transactions_style.css`, `userstyle.css` — consistent sidebar + table + modal styling
- `reportanalysis.css` — reports page styling

**Backend files (fix):**
- `backend/db.php` — ensure `safe()` helper is defined
- `backend/process_login.php` — fix session handling
- `backend/logout.php` — fix session destruction
- `backend/itemtab.php` — add session check
- `backend/suppliertab.php` — add session check, remove duplicate code
- `backend/stock_delete.php` — add session check
- `backend/user_action.php` — add session check
- `backend/process_user.php` — add isset checks
- `backend/process_transaction.php` — add input validation

**Frontend files (fix + restyle HTML):**
- All 10 PHP files — update HTML structure, fix nav links, fix logout paths, consistent sidebar markup
- `frontend/index.php` — add data loading queries so dashboard works
- `frontend/export_xml.php` — add session check

**Documentation:**
- `README.md` — full rewrite with clear project explanation
