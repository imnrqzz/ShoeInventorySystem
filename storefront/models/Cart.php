<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Product.php';

/**
 * Cart model — linked to customers and inventory items via FK.
 */
class Cart
{
    public static function getForCustomer(int $customerId): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT
                c.id,
                c.customer_id,
                c.item_id,
                c.variant_id,
                c.qty,
                c.created_at,
                i.name,
                i.price,
                i.quantity AS item_stock,
                i.image,
                COALESCE(s.company_name, 'SoleHaus') AS brand,
                v.color AS variant_color,
                v.size  AS variant_size,
                v.quantity AS variant_stock
            FROM cart_items c
            INNER JOIN items i ON i.id = c.item_id
            LEFT JOIN suppliers s ON s.order_id = i.supplier_id
            LEFT JOIN item_variants v ON v.id = c.variant_id
            WHERE c.customer_id = ?
            ORDER BY c.id ASC
        ");
        $stmt->execute([$customerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            $row['image'] = item_image_url($row['image'] ?? null);
            $row['price'] = (float) $row['price'];
            $row['qty']   = (int) $row['qty'];
            return $row;
        }, $rows);
    }

    public static function countForCustomer(int $customerId): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(qty), 0) FROM cart_items WHERE customer_id = ?');
        $stmt->execute([$customerId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Add item to cart with server-side stock validation.
     * Returns ['ok' => bool, 'message' => string].
     */
    public static function addItem(int $customerId, int $itemId, ?int $variantId = null, int $qty = 1): array
    {
        if (!Product::exists($itemId)) {
            return ['ok' => false, 'message' => 'This product is no longer available.'];
        }

        $available = Product::getAvailableStock($itemId, $variantId);
        if ($available <= 0) {
            return ['ok' => false, 'message' => 'This item is out of stock.'];
        }

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT id, qty FROM cart_items WHERE customer_id = ? AND item_id = ? AND variant_id <=> ? LIMIT 1'
        );
        $stmt->execute([$customerId, $itemId, $variantId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $newQty = ($existing ? (int) $existing['qty'] : 0) + max(1, $qty);
        if ($newQty > $available) {
            return ['ok' => false, 'message' => "Only {$available} unit(s) available in stock."];
        }

        if ($existing) {
            $upd = $pdo->prepare('UPDATE cart_items SET qty = ? WHERE id = ?');
            $upd->execute([$newQty, $existing['id']]);
            $cartItemId = (int)$existing['id'];
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO cart_items (customer_id, item_id, variant_id, qty) VALUES (?, ?, ?, ?)'
            );
            $ins->execute([$customerId, $itemId, $variantId, max(1, $qty)]);
            $cartItemId = (int)$pdo->lastInsertId();
        }

        return ['ok' => true, 'message' => 'Added to cart.', 'cart_item_id' => $cartItemId];
    }

    public static function removeItem(int $customerId, int $cartItemId): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND customer_id = ?');
        $stmt->execute([$cartItemId, $customerId]);
    }

    public static function clearForCustomer(int $customerId): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE customer_id = ?');
        $stmt->execute([$customerId]);
    }
}
