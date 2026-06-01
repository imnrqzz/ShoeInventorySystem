        <?php
        /**
         * components/page_header.php — Reusable Page Header
         *
         * Renders the page title, subtitle, and an optional action button.
         * This pattern was duplicated across 7 pages with slight variations.
         *
         * Required variables (set before including):
         *   $pageTitle   — The main heading text (e.g. "Items Management")
         *   $pageSubtitle — Description below the heading
         *
         * Optional variables:
         *   $headerAction — Array with 'label' and either 'href' or 'onclick'
         *                   Example: ['label' => '+ Add Item', 'href' => '#addModal']
         *                   Example: ['label' => '+ Log Transaction', 'onclick' => "document.getElementById('modal').style.display='flex'"]
         *
         * Usage:
         *   $pageSubtitle = 'Add, edit, and manage items';
         *   $headerAction = ['label' => '+ Add New Item', 'href' => '#addItemModal'];
         *   require __DIR__ . '/components/page_header.php';
         */
        ?>
            <div class="page-header"<?php if (!empty($headerAction)): ?> style="display:flex;align-items:center;justify-content:space-between;"<?php endif; ?>>
                <div>
                    <h1><?= safe($pageTitle ?? '') ?></h1>
                    <p><?= safe($pageSubtitle ?? '') ?></p>
                </div>
                <?php if (!empty($headerAction)): ?>
                    <?php if (isset($headerAction['href'])): ?>
                    <a href="<?= $headerAction['href'] ?>" class="btn btn-primary"><?= safe($headerAction['label']) ?></a>
                    <?php elseif (isset($headerAction['onclick'])): ?>
                    <button class="btn btn-primary" onclick="<?= $headerAction['onclick'] ?>"><?= safe($headerAction['label']) ?></button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
