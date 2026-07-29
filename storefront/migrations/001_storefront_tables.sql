-- Storefront tables for pos_inventory_system
-- Run: mysql -u root pos_inventory_system < storefront/migrations/001_storefront_tables.sql

USE pos_inventory_system;

-- Customer accounts (separate from admin `users` table)
CREATE TABLE IF NOT EXISTS customers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NULL,
    provider      ENUM('local','google','facebook') NOT NULL DEFAULT 'local',
    provider_id   VARCHAR(190) NULL,
    avatar        VARCHAR(500) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uniq_customer_email (email),
    KEY idx_customer_provider (provider, provider_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shopping cart (FK to items — orphaned rows removed when admin deletes an item)
CREATE TABLE IF NOT EXISTS cart_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    item_id     INT UNSIGNED NOT NULL,
    variant_id  INT UNSIGNED NULL,
    qty         INT NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cart_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_item     FOREIGN KEY (item_id)     REFERENCES items(id)     ON DELETE CASCADE,
    CONSTRAINT fk_cart_variant  FOREIGN KEY (variant_id)  REFERENCES item_variants(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_cart_line (customer_id, item_id, variant_id),
    KEY idx_cart_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlist (same referential integrity as cart)
CREATE TABLE IF NOT EXISTS wishlist_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    item_id     INT UNSIGNED NOT NULL,
    variant_id  INT UNSIGNED NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_item     FOREIGN KEY (item_id)     REFERENCES items(id)     ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_variant  FOREIGN KEY (variant_id)  REFERENCES item_variants(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_wishlist_line (customer_id, item_id, variant_id),
    KEY idx_wishlist_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rate limiting for public login/register endpoints
CREATE TABLE IF NOT EXISTS customer_login_attempts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email        VARCHAR(190) NOT NULL,
    ip_address   VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    KEY idx_attempts_lookup (email, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
