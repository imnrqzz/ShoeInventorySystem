<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Customer model — reads/writes the `customers` table (NOT admin `users`).
 */
class Customer
{
    public static function findByEmail(string $email): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByProvider(string $provider, string $providerId): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM customers WHERE provider = ? AND provider_id = ? LIMIT 1');
        $stmt->execute([$provider, $providerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function createLocal(string $name, string $email, string $plainPassword, string $address, string $verificationCode): ?int
    {
        $pdo  = Database::getConnection();
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO customers (name, email, password_hash, address, verification_code, is_verified, provider) VALUES (?, ?, ?, ?, ?, 0, ?)'
        );
        $stmt->execute([$name, $email, $hash, $address, $verificationCode, 'local']);
        return (int) $pdo->lastInsertId();
    }

    public static function verifyCode(string $email, string $code): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, verification_code FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['verification_code']) && password_verify($code, $row['verification_code'])) {
            $upd = $pdo->prepare('UPDATE customers SET is_verified = 1, verification_code = NULL WHERE id = ?');
            $upd->execute([$row['id']]);
            return true;
        }
        return false;
    }

    public static function verifyPassword(array $customer, string $plainPassword): bool
    {
        if (empty($customer['password_hash'])) {
            return false;
        }
        return password_verify($plainPassword, $customer['password_hash']);
    }

    public static function findOrCreateFromOAuth(
        string $provider,
        string $providerId,
        string $name,
        string $email,
        ?string $avatar = null
    ): ?array {
        $pdo = Database::getConnection();

        $existingByProvider = self::findByProvider($provider, $providerId);
        if ($existingByProvider) {
            return $existingByProvider;
        }

        $existingByEmail = self::findByEmail($email);
        if ($existingByEmail) {
            $stmt = $pdo->prepare(
                'UPDATE customers SET provider = ?, provider_id = ?, avatar = COALESCE(avatar, ?) WHERE id = ?'
            );
            $stmt->execute([$provider, $providerId, $avatar, $existingByEmail['id']]);
            return self::findById((int) $existingByEmail['id']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO customers (name, email, provider, provider_id, avatar) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $provider, $providerId, $avatar]);
        return self::findById((int) $pdo->lastInsertId());
    }
}
