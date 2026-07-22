<?php
// backend/logout.php

require_once __DIR__ . '/bootstrap.php';

// session_unset() clears all session variables, session_destroy() ends the session.
// Together they ensure a clean logout with no leftover session data.
session_unset();
session_destroy();
header('Location: /ShoeInventorySystem/frontend/login.php');
exit;
