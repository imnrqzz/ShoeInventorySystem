<?php
require_once __DIR__ . '/db.php';
// destroy session and redirect
session_unset();
session_destroy();
header('Location: /PosInventorySystem/frontend/login.php');
exit;
