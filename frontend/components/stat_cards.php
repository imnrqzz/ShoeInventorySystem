<?php
/**
 * components/stat_cards.php — Reusable Stat Card Row
 *
 * Renders a row of KPI stat cards from an array. This pattern was duplicated
 * across the dashboard, stock, and user pages.
 *
 * Required variable (set before including):
 *   $statCards — Array of cards, each with 'label', 'value', and optional 'type'
 *                Type can be 'danger' (red) or 'success' (green). Default is neutral.
 *
 * Usage:
 *   $statCards = [
 *       ['label' => 'Total Items',    'value' => $totalItems],
 *       ['label' => 'Low Stock',      'value' => $lowStock, 'type' => 'danger'],
 *       ['label' => 'OK Stock',       'value' => $okStock,  'type' => 'success'],
 *   ];
 *   require __DIR__ . '/components/stat_cards.php';
 */
?>
            <div class="stat-cards">
                <?php foreach ($statCards as $card): ?>
                <div class="stat-card<?= isset($card['type']) && $card['type'] === 'danger' ? ' danger' : '' ?>">
                    <div class="stat-label"<?php if (isset($card['type']) && $card['type'] === 'success'): ?> style="color:var(--color-success)"<?php endif; ?>><?= safe($card['label']) ?></div>
                    <div class="stat-value"<?php if (isset($card['type']) && $card['type'] === 'success'): ?> style="color:var(--color-success)"<?php endif; ?>><?= safe($card['value']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
