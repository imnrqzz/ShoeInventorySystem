<?php
// frontend/item.php

require_once __DIR__ . '/components/auth.php';
require_once __DIR__ . '/../backend/itemtab.php';

$pageTitle = 'Items';
$pageCss = 'Item.css';
$activePage = 'items';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
    <?php if ($editing_item): ?>
    <style>#editItemModal { opacity: 1; pointer-events: auto; }</style>
    <?php endif; ?>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

        <main class="main-content">
<?php
$pageSubtitle = 'Add, edit, and manage shoe inventory items';
$headerAction = ['label' => '<i class="fa-solid fa-bars"></i> Actions', 'onclick' => 'toggleActionsMenu(event)'];
require __DIR__ . '/components/page_header.php';
?>

            <?php require __DIR__ . '/components/items/actions_menu.php'; ?>

<?php
$toolbarAction = 'item.php';
$toolbarSearch = $search;
$toolbarPlaceholder = 'Search shoes by name...';
require __DIR__ . '/components/toolbar.php';
?>

            <?php require __DIR__ . '/components/items/table.php'; ?>
            <?php require __DIR__ . '/components/items/import_modal.php'; ?>
        </main>
    </div>

    <?php require __DIR__ . '/components/items/add_modal.php'; ?>
    <?php require __DIR__ . '/components/items/edit_modal.php'; ?>
    <?php require __DIR__ . '/components/footer.php'; ?>