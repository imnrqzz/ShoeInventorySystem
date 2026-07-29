<?php
/**
 * Front controller for the customer-facing storefront.
 * URL: http://localhost/ShoeInventorySystem/storefront/
 */
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CustomizerController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/AboutController.php';
require_once __DIR__ . '/controllers/BrandsController.php';

$requestPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
$scriptDir   = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($scriptDir !== '' && str_starts_with($requestPath, $scriptDir)) {
    $requestPath = trim(substr($requestPath, strlen($scriptDir)), '/');
}

$page = $_GET['page'] ?? 'home';

if ($requestPath === 'auth-google-callback') {
    $page = 'auth-google-callback';
} elseif ($requestPath === 'auth-facebook-callback') {
    $page = 'auth-facebook-callback';
} elseif ($requestPath === 'auth-google') {
    $page = 'auth-google';
} elseif ($requestPath === 'auth-facebook') {
    $page = 'auth-facebook';
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

switch ($page) {
    case 'about':
        (new AboutController())->index();
        break;

    case 'brands':
        (new BrandsController())->index();
        break;

    case 'product':
        (new ProductController())->index();
        break;

    case 'customizer':
        (new CustomizerController())->index();
        break;

    case 'login':
        $isPost ? (new AuthController())->login() : (new AuthController())->showLogin();
        break;

    case 'register':
        $isPost ? (new AuthController())->register() : (new AuthController())->showRegister();
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'cart':
        (new CartController())->index();
        break;

    case 'cart-action':
        if ($isPost) {
            (new CartController())->handleAction();
        } else {
            header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
        }
        break;

    case 'checkout':
        if ($isPost) {
            (new CartController())->checkout();
        } else {
            header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
        }
        break;

    case 'verify-code':
        $isPost ? (new AuthController())->verifyCode() : (new AuthController())->showVerifyCode();
        break;

    case 'resend-code':
        if ($isPost) {
            (new AuthController())->resendCode();
        } else {
            header('Location: ' . STOREFRONT_BASE . '/index.php?page=verify-code');
        }
        break;

    case 'auth-google':
        (new AuthController())->redirectToGoogle();
        break;

    case 'auth-google-callback':
        (new AuthController())->googleCallback();
        break;

    case 'auth-facebook':
        (new AuthController())->redirectToFacebook();
        break;

    case 'auth-facebook-callback':
        (new AuthController())->facebookCallback();
        break;

    case 'home':
    default:
        (new HomeController())->index();
        break;
}
