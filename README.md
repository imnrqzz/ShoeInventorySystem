# ShoeInventorySystem

This is a web-based inventory management system built with PHP and MySQL, designed specifically for managing a shoe store's inventory. It provides a comprehensive suite of tools for tracking items, suppliers, stock levels, and user activity.

## Features

-   **User Authentication**: Secure login, registration, and session management using password hashing.
-   **Dashboard**: A central overview of key metrics like total items, active suppliers, low stock alerts, and recent transactions.
-   **Item Management**: Add, view, delete, and search for shoe items in the inventory.
-   **Supplier Management**: Maintain a directory of suppliers with full CRUD (Create, Read, Update, Delete) functionality.
-   **Stock Tracking**: Monitor current stock levels against minimum thresholds, with visual indicators and progress bars for low-stock items.
-   **Modular Interface**: Separate, styled pages for each major function (Dashboard, Items, Suppliers, Stock) for a clear user experience.

## Technology Stack

-   **Backend**: PHP
-   **Frontend**: HTML, CSS, JavaScript
-   **Database**: MySQL
-   **Web Server**: Apache (or a similar environment like Nginx)

## Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

You need a local web server environment that supports PHP and MySQL. The most common solution is a stack like:
-   [XAMPP](https://www.apachefriends.org/index.html) (Windows, macOS, Linux)
-   WAMP (Windows)
-   MAMP (macOS)

### Installation

1.  **Clone the repository**
    ```sh
    git clone https://github.com/imnrqzz/ShoeInventorySystem.git
    ```

2.  **Place the project in your web server's root directory**
    Move the cloned folder (`imnrqzz-shoeinventorysystem`) into your server's `htdocs` (for XAMPP) or `www` directory.

3.  **Set up the database**
    -   Start Apache and MySQL from your server's control panel (e.g., XAMPP Control Panel).
    -   Open phpMyAdmin in your browser (usually at `http://localhost/phpmyadmin`).
    -   Create a new database. The project uses multiple database names across its files (`pos_inventory_system`, `shoes_inventory`, `inventory`, `db_items`). It is recommended to choose **one** name (e.g., `shoes_inventory`) and use it consistently.
    -   Create the necessary tables (e.g., `users`, `items`, `suppliers`, `stock`, `transactions`). You will need to infer the table structures from the columns used in the PHP files. For example, the `users` table requires at least an `id`, `username`, and `password_hash` column.

4.  **Configure Database Connections**
    -   Manually update the database connection details (host, username, password, and database name) in each of the following files to match your local setup:
        -   `backend/db.php`
        -   `backend/stock_delete.php`
        -   `backend/stock_edit.php`
        -   `backend/suppliertab.php`
        -   `frontend/item.php`
        -   `frontend/stock.php`

5.  **Run the application**
    -   Open your web browser and navigate to the project's login page. The URL will be similar to this:
    `http://localhost/imnrqzz-shoeinventorysystem/frontend/login.php`

## Project Structure

The project is organized into distinct directories for backend logic, frontend presentation, and styling.

```
.
├── backend/        # Contains all backend PHP scripts for database interactions and logic.
│   ├── db.php      # Main database connection handler.
│   ├── process_login.php # Handles user login authentication.
│   ├── process_register.php # Handles new user registration.
│   └── ...         # CRUD scripts for stock and suppliers.
├── css/            # Contains all CSS stylesheets for different pages.
│   ├── dashboard_style.css
│   ├── login_style.css
│   └── ...
└── frontend/       # Contains all user-facing PHP/HTML pages.
    ├── index.php   # The main dashboard.
    ├── login.php   # The login page.
    ├── item.php    # Item management page.
    ├── stock.php   # Stock management page.
    ├── Supplier.php # Supplier management page.
    └── ...
```

## Key Modules

-   **Login & Registration** (`login.php`, `register.php`): The entry point for users. The system handles user authentication and creates sessions upon successful login.
-   **Dashboard** (`index.php`): The main landing page after login. It displays key performance indicators (KPIs) in a clean, card-based layout, providing an at-a-glance summary of the inventory status.
-   **Item Management** (`item.php`): This module allows users to define and manage the shoe products, including details like category, unit price, and associated supplier.
-   **Supplier Management** (`Supplier.php`): A dedicated interface for adding, editing, searching, and deleting supplier information, helping to maintain an organized vendor list.
-   **Stock Management** (`stock.php`): Provides a detailed, table-based view of the current inventory. It uses color-coding and progress bars to visually flag items that are running low, allowing for quick stock assessments and updates.
