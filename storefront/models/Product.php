<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Product model — reads live from the shared `items` table.
 * No demo fallback, no separate storefront catalog.
 */
class Product
{
    private static function baseQuery(): string
    {
        return "
            SELECT
                i.id,
                i.name,
                i.category,
                i.quantity,
                i.min_quantity,
                i.price,
                i.color,
                i.size,
                i.image,
                i.created_at,
                COALESCE(s.company_name, 'SoleHaus') AS brand
            FROM items i
            LEFT JOIN suppliers s ON s.order_id = i.supplier_id
        ";
    }

    public static function getAll(int $limit = 50, int $offset = 0): array
    {
        $pdo  = Database::getConnection();
        $sql  = self::baseQuery() . ' ORDER BY i.created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return array_map([self::class, 'normalize'], $rows);
    }

    public static function getById(int $id): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(self::baseQuery() . ' WHERE i.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $product = self::normalize($row);
        $product['variants'] = self::getVariants($id);
        return $product;
    }

    public static function getVariants(int $itemId): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT id, color, size, quantity FROM item_variants WHERE item_id = ? ORDER BY color, size'
        );
        $stmt->execute([$itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check available stock for an item (optionally a specific variant).
     * Returns available quantity (0 if item not found).
     */
    public static function getAvailableStock(int $itemId, ?int $variantId = null): int
    {
        $pdo = Database::getConnection();

        if ($variantId !== null) {
            $stmt = $pdo->prepare(
                'SELECT v.quantity FROM item_variants v
                 INNER JOIN items i ON i.id = v.item_id
                 WHERE v.id = ? AND v.item_id = ? LIMIT 1'
            );
            $stmt->execute([$variantId, $itemId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? max(0, (int) $row['quantity']) : 0;
        }

        $stmt = $pdo->prepare('SELECT quantity FROM items WHERE id = ? LIMIT 1');
        $stmt->execute([$itemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? max(0, (int) $row['quantity']) : 0;
    }

    public static function exists(int $itemId): bool
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM items WHERE id = ? LIMIT 1');
        $stmt->execute([$itemId]);
        return (bool) $stmt->fetchColumn();
    }

    private static function normalize(array $p): array
    {
        $stock = (int) ($p['quantity'] ?? 0);
        $category = self::normalizeCategory((string) ($p['category'] ?? 'lifestyle'));

        return [
            'id'          => (int) $p['id'],
            'name'        => $p['name'],
            'brand'       => $p['brand'] ?? 'SoleHaus',
            'price'       => (float) $p['price'],
            'category'    => $category,
            'image'       => item_image_url($p['image'] ?? null),
            'stock'       => $stock,
            'stockLabel'  => $stock <= 0 ? 'Out of Stock' : ($stock <= 5 ? 'Low Stock' : 'In Stock'),
            'stockClass'  => $stock <= 0 ? 'out' : ($stock <= 5 ? 'low' : 'in'),
            'color'       => $p['color'] ?? null,
            'size'        => $p['size'] ?? null,
        ];
    }

    private static function normalizeCategory(string $category): string
    {
        $value = strtolower(trim($category));

        if (in_array($value, ['sport', 'sports', 'running', 'training'], true)) {
            return 'sport';
        }
        if (in_array($value, ['classic', 'classics', 'retro', 'casual', 'skate', 'vintage'], true)) {
            return 'classic';
        }
        return 'lifestyle';
    }
}
