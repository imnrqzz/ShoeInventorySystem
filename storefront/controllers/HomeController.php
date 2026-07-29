<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';

class HomeController
{
    public function index(): void
    {
        $customerId = $_SESSION['customer']['id'] ?? null;
        $cartCount  = $customerId ? Cart::countForCustomer((int) $customerId) : 0;
        $products   = Product::getAll(50);

        $brands = [];
        foreach ($products as $p) {
            $brands[$p['brand']] = true;
        }
        $brandList = array_keys($brands);
        if (empty($brandList)) {
            $brandList = ['Nike', 'Adidas', 'Puma', 'Jordan'];
        }

        $this->render('home', [
            'products'   => $products,
            'brands'     => $brandList,
            'brandCount' => count($brandList),
            'cartCount'  => $cartCount,
            'year'       => date('Y'),
            'csrfToken'  => csrf_token(),
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.view.php';
    }
}
