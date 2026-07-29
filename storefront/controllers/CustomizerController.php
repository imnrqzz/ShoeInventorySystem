<?php
require_once __DIR__ . '/../models/Cart.php';

class CustomizerController
{
    public function index(): void
    {
        $customerId = $_SESSION['customer']['id'] ?? null;
        $cartCount  = $customerId ? Cart::countForCustomer((int) $customerId) : 0;

        // WARNING & RISK: This customizer relies on Natalia Palacio Pastor's external deployment at 
        // https://shoe-customizer-silk.vercel.app. If this external endpoint becomes unavailable, 
        // the storefront 3D customizer feature will break.
        // Fallback: Run `npm install && npm run build` locally in `storefront/3D-shoe-customizer` and 
        // set CUSTOMIZER_URL="/ShoeInventorySystem/storefront/3D-shoe-customizer/dist/index.html" in .env.
        $customizerUrl = $_ENV['CUSTOMIZER_URL'] ?? 'https://shoe-customizer-silk.vercel.app';

        // If local path (starts with /), serve from same host
        if (str_starts_with($customizerUrl, '/')) {
            $customizerUrl = $customizerUrl;
        }

        $productId = (int)($_GET['productId'] ?? 1); // Defaults to Nike Air Max 90
        $editCartItemId = isset($_GET['editCartItemId']) ? (int)$_GET['editCartItemId'] : null;
        $savedCustomization = null;

        if ($editCartItemId && !empty($_SESSION['cart_customizations'][$editCartItemId])) {
            $savedCustomization = $_SESSION['cart_customizations'][$editCartItemId];
        }

        $params = ['productId' => $productId];
        if ($editCartItemId !== null) {
            $params['editCartItemId'] = $editCartItemId;
        }

        if (strpos($customizerUrl, '?') !== false) {
            $customizerUrl .= '&' . http_build_query($params);
        } else {
            $customizerUrl .= '?' . http_build_query($params);
        }

        $this->render('customizer', [
            'customizerUrl' => $customizerUrl,
            'cartCount'     => $cartCount,
            'year'          => date('Y'),
            'savedCustomization' => $savedCustomization,
            'editCartItemId' => $editCartItemId,
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.view.php';
    }
}
