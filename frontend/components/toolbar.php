<?php
/**
 * components/toolbar.php — Reusable Search/Filter Toolbar
 *
 * Renders a search form with an optional dropdown filter, submit, and reset buttons.
 * This pattern was duplicated across 6 pages with slight variations.
 *
 * Required variables (set before including):
 *   $toolbarAction      — Form action URL (e.g. 'item.php', 'stock.php')
 *   $toolbarSearch      — Current search value (for the input)
 *   $toolbarPlaceholder — Search input placeholder text
 *
 * Optional variables:
 *   $toolbarFilter — Array with 'name', 'value', 'options' for a dropdown filter
 *                    Example: [
 *                      'name' => 'type',
 *                      'value' => $type,
 *                      'options' => [
 *                        ['value' => 'All Types', 'label' => 'All Types'],
 *                        ['value' => 'Sale',      'label' => 'Sale'],
 *                      ]
 *                    ]
 *
 * Usage:
 *   $toolbarAction = 'item.php';
 *   $toolbarSearch = $search;
 *   $toolbarPlaceholder = 'Search shoes by name...';
 *   require __DIR__ . '/components/toolbar.php';
 */
?>
            <form method="GET" action="<?= safe($toolbarAction) ?>" class="toolbar">
                <input type="text" name="search" class="search-input" placeholder="<?= safe($toolbarPlaceholder) ?>" value="<?= safe($toolbarSearch) ?>">
                <?php if (!empty($toolbarFilter)): ?>
                <select name="<?= safe($toolbarFilter['name']) ?>" class="toolbar-select">
                    <?php foreach ($toolbarFilter['options'] as $opt): ?>
                    <option value="<?= safe($opt['value']) ?>" <?= ($toolbarFilter['value'] ?? '') == $opt['value'] ? 'selected' : '' ?>><?= safe($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-sm"><?= !empty($toolbarFilter) ? 'Filter' : 'Search' ?></button>
                <a href="<?= safe($toolbarAction) ?>" class="btn btn-secondary btn-sm">Reset</a>
            </form>
