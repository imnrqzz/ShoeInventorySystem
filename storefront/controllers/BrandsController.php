<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';

class BrandsController
{
    public function index(): void
    {
        $customerId = $_SESSION['customer']['id'] ?? null;
        $cartCount  = $customerId ? Cart::countForCustomer((int) $customerId) : 0;
        $products   = Product::getAll(100);

        // Group products by brand
        $brands = [];
        foreach ($products as $p) {
            $brand = $p['brand'] ?? 'Other';
            if (!isset($brands[$brand])) {
                $brands[$brand] = [];
            }
            $brands[$brand][] = $p;
        }

        $this->render('brands', [
            'brands'     => $brands,
            'cartCount'  => $cartCount,
            'year'       => date('Y'),
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.view.php';
    }
}
