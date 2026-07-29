<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Wishlist.php';

class CartController
{
    public function index(): void
    {
        $customerId    = $_SESSION['customer']['id'] ?? null;
        $cartItems     = [];
        $wishlistItems = [];
        $message       = flash_get('cart_message');
        $error         = flash_get('cart_error');

        if ($customerId !== null) {
            $pdo = Database::getConnection();
            
            // Handle GET query parameter actions
            if (isset($_GET['add'])) {
                $name = trim($_GET['add']);
                $stmt = $pdo->prepare("SELECT id FROM items WHERE name = ? LIMIT 1");
                $stmt->execute([$name]);
                $item = $stmt->fetch();
                if ($item) {
                    Cart::addItem((int)$customerId, (int)$item['id'], null, 1);
                }
                header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
                exit;
            }

            if (isset($_GET['wishlist'])) {
                $name = trim($_GET['wishlist']);
                $stmt = $pdo->prepare("SELECT id FROM items WHERE name = ? LIMIT 1");
                $stmt->execute([$name]);
                $item = $stmt->fetch();
                if ($item) {
                    Wishlist::addItem((int)$customerId, (int)$item['id'], null);
                }
                header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
                exit;
            }

            if (isset($_GET['remove_wishlist'])) {
                $name = trim($_GET['remove_wishlist']);
                $stmt = $pdo->prepare("SELECT w.id FROM wishlist_items w INNER JOIN items i ON i.id = w.item_id WHERE i.name = ? AND w.customer_id = ? LIMIT 1");
                $stmt->execute([$name, $customerId]);
                $wItem = $stmt->fetch();
                if ($wItem) {
                    Wishlist::removeItem((int)$customerId, (int)$wItem['id']);
                }
                header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
                exit;
            }

            if (isset($_GET['remove_cart'])) {
                $cartItemId = (int)$_GET['remove_cart'];
                Cart::removeItem((int)$customerId, $cartItemId);
                if (isset($_SESSION['cart_customizations'][$cartItemId])) {
                    unset($_SESSION['cart_customizations'][$cartItemId]);
                }
                header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
                exit;
            }

            $cartItems     = Cart::getForCustomer((int) $customerId);
            $wishlistItems = Wishlist::getForCustomer((int) $customerId);
        }

        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $subtotal += (float) $item['price'] * (int) $item['qty'];
        }

        $this->render('cart', [
            'cartItems'     => $cartItems,
            'wishlistItems' => $wishlistItems,
            'cartCount'     => count($cartItems) ? Cart::countForCustomer((int) $customerId) : 0,
            'subtotal'      => $subtotal,
            'message'       => $message,
            'error'         => $error,
            'year'          => date('Y'),
            'csrfToken'     => csrf_token(),
        ]);
    }

    /**
     * Handle POST cart/wishlist actions with CSRF + stock validation.
     */
    public function handleAction(): void
    {
        verify_csrf();

        if (!is_customer_logged_in()) {
            if ($this->wantsJson()) {
                $this->json(['ok' => false, 'message' => 'Please sign in first.'], 401);
            }
            flash_set('cart_error', 'Please sign in to manage your cart.');
            header('Location: ' . STOREFRONT_BASE . '/index.php?page=login');
            exit;
        }

        $customerId = (int) $_SESSION['customer']['id'];
        $input      = json_decode(file_get_contents('php://input'), true) ?? [];
        $action     = $input['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'add_custom':
                $productId      = (int)($input['productId'] ?? 0);
                $editCartItemId = isset($input['editCartItemId']) ? (int)$input['editCartItemId'] : null;
                $layerColor     = $input['layerColor'] ?? null;
                $layerSize      = $input['layerSize'] ?? null;
                $shoeSize       = $input['shoeSize'] ?? 9;

                if (!$productId || !$layerColor || !$layerSize) {
                    $this->json(['success' => false, 'message' => 'Missing customization details.'], 400);
                }

                if ($editCartItemId && isset($_SESSION['cart_customizations'][$editCartItemId])) {
                    $_SESSION['cart_customizations'][$editCartItemId] = [
                        'colors'   => $layerColor,
                        'sizes'    => $layerSize,
                        'shoeSize' => $shoeSize
                    ];
                    $this->json([
                        'success'   => true,
                        'cartCount' => Cart::countForCustomer($customerId),
                        'message'   => 'Customization updated successfully!'
                    ]);
                } else {
                    $result = Cart::addItem($customerId, $productId, null, 1);
                    if ($result['ok'] && isset($result['cart_item_id'])) {
                        $_SESSION['cart_customizations'][$result['cart_item_id']] = [
                            'colors'   => $layerColor,
                            'sizes'    => $layerSize,
                            'shoeSize' => $shoeSize
                        ];
                        $this->json([
                            'success'   => true,
                            'cartCount' => Cart::countForCustomer($customerId),
                            'message'   => 'Customized shoe added to cart!'
                        ]);
                    } else {
                        $this->json(['success' => false, 'message' => $result['message'] ?? 'Error adding to cart.'], 400);
                    }
                }
                break;

            case 'add_cart':
                $itemId    = (int) ($_POST['item_id'] ?? $input['item_id'] ?? 0);
                $variantId = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : (!empty($input['variant_id']) ? (int) $input['variant_id'] : null);
                $qty       = max(1, (int) ($_POST['qty'] ?? $input['qty'] ?? 1));
                $result    = Cart::addItem($customerId, $itemId, $variantId, $qty);
                break;

            case 'add_wishlist':
                $itemId    = (int) ($_POST['item_id'] ?? $input['item_id'] ?? 0);
                $variantId = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : (!empty($input['variant_id']) ? (int) $input['variant_id'] : null);
                $result    = Wishlist::addItem($customerId, $itemId, $variantId);
                break;

            case 'remove_cart':
                Cart::removeItem($customerId, (int) ($_POST['cart_item_id'] ?? $input['cart_item_id'] ?? 0));
                $result = ['ok' => true, 'message' => 'Item removed from cart.'];
                break;

            case 'remove_wishlist':
                Wishlist::removeItem($customerId, (int) ($_POST['wishlist_item_id'] ?? $input['wishlist_item_id'] ?? 0));
                $result = ['ok' => true, 'message' => 'Item removed from wishlist.'];
                break;

            default:
                $result = ['ok' => false, 'message' => 'Unknown action.'];
        }

        if ($this->wantsJson()) {
            $this->json($result, $result['ok'] ? 200 : 400);
        }

        if ($result['ok']) {
            flash_set('cart_message', $result['message']);
        } else {
            flash_set('cart_error', $result['message']);
        }

        $redirect = $_POST['redirect'] ?? (STOREFRONT_BASE . '/index.php?page=cart');
        header('Location: ' . $redirect);
        exit;
    }

    public function checkout(): void
    {
        verify_csrf();

        if (!is_customer_logged_in()) {
            flash_set('cart_error', 'Please sign in to complete checkout.');
            header('Location: ' . STOREFRONT_BASE . '/index.php?page=login');
            exit;
        }

        $customerId = (int) $_SESSION['customer']['id'];
        $cartItems  = Cart::getForCustomer($customerId);

        if (empty($cartItems)) {
            flash_set('cart_error', 'Your cart is empty.');
            header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
            exit;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            foreach ($cartItems as $item) {
                $qty = (int) $item['qty'];
                $itemId = (int) $item['item_id'];
                $variantId = $item['variant_id'] ? (int) $item['variant_id'] : null;

                // 1. Atomically deduct stock
                if ($variantId !== null) {
                    $stmt = $pdo->prepare("UPDATE item_variants SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                    $stmt->execute([$qty, $variantId, $qty]);
                    if ($stmt->rowCount() === 0) {
                        throw new Exception("Insufficient stock for variant of item: " . $item['name']);
                    }

                    // Also deduct from main items table to keep totals aligned
                    $stmtMain = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                    $stmtMain->execute([$qty, $itemId, $qty]);
                    if ($stmtMain->rowCount() === 0) {
                        throw new Exception("Insufficient stock for item: " . $item['name']);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                    $stmt->execute([$qty, $itemId, $qty]);
                    if ($stmt->rowCount() === 0) {
                        throw new Exception("Insufficient stock for item: " . $item['name']);
                    }
                }

                // 2. Sync stock.current_qty to match items.quantity
                $stmtSync = $pdo->prepare("
                    UPDATE stock SET current_qty = (
                        SELECT quantity FROM items WHERE id = ?
                    ) WHERE item_id = ?
                ");
                $stmtSync->execute([$itemId, $itemId]);

                // 3. Log transaction
                $reason = 'Checkout';
                $customizationData = null;
                if (!empty($_SESSION['cart_customizations'][$item['id']])) {
                    $customizationData = json_encode([
                        'colors' => $_SESSION['cart_customizations'][$item['id']]['colors'] ?? null,
                        'sizes'  => $_SESSION['cart_customizations'][$item['id']]['sizes'] ?? null
                    ]);
                }

                $stmtTx = $pdo->prepare("
                    INSERT INTO transactions (item_id, user_id, customer_id, transaction_type, quantity, reason, customization_data, transaction_date)
                    VALUES (?, NULL, ?, 'Sold', ?, ?, ?, NOW())
                ");
                $stmtTx->execute([$itemId, $customerId, $qty, $reason, $customizationData]);
            }

            // Clear Cart and Session Customizations
            Cart::clearForCustomer($customerId);
            foreach ($cartItems as $item) {
                unset($_SESSION['cart_customizations'][$item['id']]);
            }

            $pdo->commit();
            flash_set('cart_message', 'Checkout completed successfully! Your order has been placed.');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash_set('cart_error', 'Checkout failed: ' . $e->getMessage());
        }

        header('Location: ' . STOREFRONT_BASE . '/index.php?page=cart');
        exit;
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xhr) === 'xmlhttprequest';
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.view.php';
    }
}
