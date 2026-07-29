<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Cart.php';

class ProductController
{
    public function index(): void
    {
        $id         = (int) ($_GET['id'] ?? 0);
        $product    = $id > 0 ? Product::getById($id) : null;
        $customerId = $_SESSION['customer']['id'] ?? null;
        $cartCount  = $customerId ? Cart::countForCustomer((int) $customerId) : 0;

        $this->render('product', [
            'product'   => $product,
            'cartCount' => $cartCount,
            'year'      => date('Y'),
            'csrfToken' => csrf_token(),
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.view.php';
    }
}
