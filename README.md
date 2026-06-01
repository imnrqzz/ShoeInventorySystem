# ShoeInventorySystem

A comprehensive, web-based inventory management system built with PHP and MySQL, specifically designed for a shoe store. The application provides a full suite of tools for tracking shoe items, managing suppliers, monitoring stock levels, logging transactions, and managing user accounts.

## Features

-   **User Authentication**: Secure user registration and login with password hashing and session management.
-   **Dashboard Overview**: A central hub displaying key metrics such as total items, active suppliers, system users, transaction counts, and critical low-stock alerts.
-   **Item Management**: Full CRUD (Create, Read, Update, Delete) functionality for shoe items, including details like name, price, and associated supplier.
-   **Supplier Management**: A complete interface to add, view, edit, and delete supplier information.
-   **Stock Tracking**: A detailed view of inventory levels with progress bars and status indicators to quickly identify items that are running low based on set minimum thresholds.
-   **Transaction Logging**: Record all inventory movements, including sales, restocks, and waste, with details on the item, quantity, user, and reason.
-   **User Management**: An administrative interface to view, filter, edit, and delete user accounts and their roles.
-   **Reporting**: Generate and filter reports on transaction history, with options to print or export data to XML.

## Technology Stack

-   **Backend**: PHP
-   **Frontend**: HTML, CSS, JavaScript
-   **Database**: MySQL
-   **Web Server**: Apache (or any server supporting PHP, such as Nginx)

## Getting Started

Follow these instructions to set up a local instance of the project for development and testing.

### Prerequisites

You will need a local web server environment that supports PHP and MySQL. A stack like [XAMPP](https://www.apachefriends.org/index.html) is recommended as it includes Apache, MySQL, and phpMyAdmin.

### Installation

1.  **Clone the Repository**
    Clone the project to your local machine.
    ```sh
    git clone https://github.com/imnrqzz/ShoeInventorySystem.git
    ```

2.  **Move to Web Server Directory**
    Move the cloned project folder (e.g., `ShoeInventorySystem`) into your web server's root directory (typically `htdocs` for XAMPP or `www` for WAMP/MAMP).

3.  **Set Up the Database**
    -   Start the Apache and MySQL services from your server control panel (e.g., XAMPP Control Panel).
    -   Navigate to `http://localhost/phpmyadmin` in your web browser.
    -   Create a new database and name it `pos_inventory_system`.
    -   Select the newly created database, go to the "Import" tab, and upload the `pos_inventory_system.sql` file located in the root of the project directory. This will create all the necessary tables and populate them with sample data.

4.  **Configure the Database Connection**
    -   Open the file `backend/Classes/Database.php`.
    -   Update the following variables with your local MySQL database credentials. For a default XAMPP installation, you may only need to change `$password` if you have set one.
      ```php
      private $host = 'localhost';
      private $db_name = 'pos_inventory_system';
      private $username = 'root';
      private $password = ''; // Your MySQL password
      ```

5.  **Run the Application**
    -   Open your web browser and navigate to the project's login page. The URL will be similar to this:
    `http://localhost/ShoeInventorySystem/frontend/login.php`
    -   You can log in with a user from the sample data, for example: `username: izana`. The password is not provided in clear text, but you can register a new user.

## Project Structure

The project is organized into dedicated directories for backend logic, frontend pages, and styling.

```
.
├── backend/            # Contains all backend PHP scripts and classes.
│   ├── Classes/        # Object-Oriented classes for managing items, users, etc.
│   ├── process_login.php # Handles user login.
│   └── ...
├── css/                # Contains all CSS stylesheets.
├── frontend/           # Contains all user-facing PHP/HTML pages.
│   ├── index.php       # The main dashboard page.
│   ├── login.php       # User login page.
│   ├── item.php        # Item management page.
│   └── ...
└── pos_inventory_system.sql # The database schema and sample data.
```

## Key Modules

-   **Login & Registration** (`login.php`, `register.php`): The entry point for all users. Securely handles authentication and session creation.
-   **Dashboard** (`index.php`): The main landing page after login, providing an at-a-glance summary of the inventory status through KPI cards and summary tables.
-   **Item Management** (`item.php`): Allows users to add, edit, and delete shoe models, set prices, and link them to suppliers.
-   **Supplier Management** (`Supplier.php`): A dedicated interface for maintaining an organized list of vendors with full CRUD capabilities.
-   **Stock Management** (`stock.php`): Provides a detailed, table-based view of current stock. It uses color-coding and progress bars to visually flag items that are below their minimum threshold.
-   **Transaction Management** (`transactions.php`): A comprehensive log of all stock movements (Sales, Restocks, Waste), which can be filtered and searched.
-   **User Management** (`user.php`): An admin-level page to manage system users, their roles, and their status.
-   **Reports** (`reports.php`): A module for generating, viewing, printing, and exporting transaction data for analysis.
