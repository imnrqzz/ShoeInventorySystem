<?php
/**
 * components/toolbar.php — Reusable Search/Filter Toolbar
 *
 * Renders a search form with an optional dropdown filter, submit, and reset buttons.
 *
 * When a search term or non-default filter is active, the toolbar shows:
 *   - A highlighted search input (blue border)
 *   - An "active filters" indicator badge showing what's applied
 *   - A red Reset button so it's obvious filters need clearing
 *
 * Required variables (set before including):
 *   $toolbarAction      — Form action URL (e.g. 'item.php', 'stock.php')
 *   $toolbarSearch      — Current search value (for the input)
 *   $toolbarPlaceholder — Search input placeholder text
 *
 * Optional variables:
 *   $toolbarFilter — Array with 'name', 'value', 'options' for a dropdown filter
 */

// Determine if any filter/search is currently active
$hasSearch = !empty($toolbarSearch);
$hasFilter = false;
$activeFilterLabel = '';

if (!empty($toolbarFilter) && !empty($toolbarFilter['value'])) {
    // Check if the selected value is NOT the first option (which is the "all" default)
    $defaultValue = $toolbarFilter['options'][0]['value'] ?? '';
    if ($toolbarFilter['value'] !== $defaultValue) {
        $hasFilter = true;
        $activeFilterLabel = $toolbarFilter['value'];
    }
}

$isFiltered = $hasSearch || $hasFilter;
?>
            <form method="GET" action="<?= safe($toolbarAction) ?>" class="toolbar">
                <input type="text" name="search"
                       class="search-input<?= $hasSearch ? ' search-active' : '' ?>"
                       placeholder="<?= safe($toolbarPlaceholder) ?>"
                       value="<?= safe($toolbarSearch) ?>">
                <?php if (!empty($toolbarFilter)): ?>
                <select name="<?= safe($toolbarFilter['name']) ?>"
                        class="toolbar-select<?= $hasFilter ? ' filter-active' : '' ?>">
                    <?php foreach ($toolbarFilter['options'] as $opt): ?>
                    <option value="<?= safe($opt['value']) ?>" <?= ($toolbarFilter['value'] ?? '') == $opt['value'] ? 'selected' : '' ?>><?= safe($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-sm"><?= !empty($toolbarFilter) ? 'Filter' : 'Search' ?></button>
                <?php if ($isFiltered): ?>
                <!-- Best Practice: Red reset button + indicator badge make it obvious
                     that filters are applied and results are not showing everything. -->
                <a href="<?= safe($toolbarAction) ?>" class="btn btn-danger btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Reset
                </a>
                <?php else: ?>
                <a href="<?= safe($toolbarAction) ?>" class="btn btn-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </form>
            <?php if ($isFiltered): ?>
            <!-- Active filters indicator — shows what's currently applied -->
            <div class="toolbar-active-filters">
                <span class="toolbar-filter-label">Showing results for:</span>
                <?php if ($hasSearch): ?>
                <span class="toolbar-filter-tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    "<?= safe($toolbarSearch) ?>"
                </span>
                <?php endif; ?>
                <?php if ($hasFilter): ?>
                <span class="toolbar-filter-tag">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <?= safe($activeFilterLabel) ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
