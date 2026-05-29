<?php
// Start session for auth across pages
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Database connection settings
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'pos_inventory_system';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    error_log('Database connection failed: ' . $mysqli->connect_error);
    $mysqli = null;
    return;
}

$mysqli->set_charset('utf8mb4');
