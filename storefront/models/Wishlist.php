<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Product.php';

/**
 * Wishlist model — linked to customers and inventory items via FK.
 */
class Wishlist
{
    public static function getForCustomer(int $customerId): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT
                w.id,
                w.customer_id,
                w.item_id,
                w.variant_id,
                w.created_at,
                i.name,
                i.price,
                i.quantity AS item_stock,
                i.image,
                COALESCE(s.company_name, 'SoleHaus') AS brand,
                v.color AS variant_color,
                v.size  AS variant_size
            FROM wishlist_items w
            INNER JOIN items i ON i.id = w.item_id
            LEFT JOIN suppliers s ON s.order_id = i.supplier_id
            LEFT JOIN item_variants v ON v.id = w.variant_id
            WHERE w.customer_id = ?
            ORDER BY w.id ASC
        ");
        $stmt->execute([$customerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $row): array {
            $row['image'] = item_image_url($row['image'] ?? null);
            $row['price'] = (float) $row['price'];
            return $row;
        }, $rows);
    }

    public static function countForCustomer(int $customerId): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist_items WHERE customer_id = ?');
        $stmt->execute([$customerId]);
        return (int) $stmt->fetchColumn();
    }

    public static function addItem(int $customerId, int $itemId, ?int $variantId = null): array
    {
        if (!Product::exists($itemId)) {
            return ['ok' => false, 'message' => 'This product is no longer available.'];
        }

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT id FROM wishlist_items WHERE customer_id = ? AND item_id = ? AND variant_id <=> ? LIMIT 1'
        );
        $stmt->execute([$customerId, $itemId, $variantId]);
        if ($stmt->fetch()) {
            return ['ok' => true, 'message' => 'Already in wishlist.'];
        }

        $ins = $pdo->prepare(
            'INSERT INTO wishlist_items (customer_id, item_id, variant_id) VALUES (?, ?, ?)'
        );
        $ins->execute([$customerId, $itemId, $variantId]);

        return ['ok' => true, 'message' => 'Saved to wishlist.'];
    }

    public static function removeItem(int $customerId, int $wishlistItemId): void
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM wishlist_items WHERE id = ? AND customer_id = ?');
        $stmt->execute([$wishlistItemId, $customerId]);
    }
}
