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
