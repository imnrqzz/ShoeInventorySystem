        <?php
        /**
         * components/page_header.php — Reusable Page Header
         *
         * Renders the page title, subtitle, and an optional action button.
         *
         * Required variables (set before including):
         *   $pageTitle    — The main heading text
         *   $pageSubtitle — Description below the heading
         *
         * Optional variables:
         *   $headerAction — Array with 'label' and either 'href' or 'onclick'
         */
        ?>
            <div class="page-header<?= !empty($headerAction) ? ' page-header-with-action' : '' ?>">
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
