<?php
require_once __DIR__ . '/../models/Cart.php';

class AboutController
{
    public function index(): void
    {
        $customerId = $_SESSION['customer']['id'] ?? null;
        $cartCount  = $customerId ? Cart::countForCustomer((int) $customerId) : 0;

        $this->render('about', [
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
