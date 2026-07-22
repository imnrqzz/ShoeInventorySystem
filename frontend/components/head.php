    <!-- components/head.php — Shared <head> tags for all authenticated pages.
         Loads the Inter font from Google Fonts and Font Awesome icons.
         Each page sets $pageTitle and $pageCss before including this file.
         Example: $pageTitle = 'Items'; $pageCss = 'Item.css'; -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= safe($pageTitle ?? 'ShoeInventory') ?> - ShoeInventory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/<?= $pageCss ?? 'base.css' ?>">
