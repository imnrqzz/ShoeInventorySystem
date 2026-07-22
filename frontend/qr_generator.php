<?php
// frontend/qr_generator.php - Generate printable QR codes for all items

require_once __DIR__ . '/components/auth.php';

$search = $_GET['search'] ?? '';
$size = (int)($_GET['size'] ?? 3);
$sizes = [1 => 120, 2 => 160, 3 => 200, 4 => 250];
$qrSize = $sizes[$size] ?? 200;

// Fetch items
$sql = "SELECT i.id, i.name, i.price, COALESCE(s.company_name, '—') AS supplier_name
        FROM items i
        LEFT JOIN suppliers s ON i.supplier_id = s.order_id";

$params = [];
if ($search !== '') {
    $sql .= " WHERE i.name LIKE ?";
    $params[] = "%" . trim($search) . "%";
}
$sql .= " ORDER BY i.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'QR Codes';
$pageCss = 'qr_generator.css';
$activePage = 'items';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        .qr-controls { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 20px; }
        .qr-controls .form-input { max-width: 250px; }
        .qr-size-btns { display: flex; gap: 4px; }
        .qr-size-btns button { padding: 6px 12px; border: 1px solid var(--color-border); background: var(--color-surface); border-radius: var(--radius-sm); cursor: pointer; font-size: 0.8rem; }
        .qr-size-btns button.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
        .qr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .qr-card { background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 16px; text-align: center; page-break-inside: avoid; }
        .qr-card .qr-image { margin: 0 auto 12px; }
        .qr-card .qr-image img { border-radius: var(--radius-sm); }
        .qr-card .qr-label { font-weight: 600; font-size: 0.9rem; margin-bottom: 4px; }
        .qr-card .qr-meta { font-size: 0.8rem; color: var(--color-text-muted); }
        .qr-card .qr-meta span { display: inline-block; margin: 0 6px; }
        .print-actions { display: flex; gap: 10px; margin-bottom: 20px; }
        @media print {
            .sidebar, .mobile-topbar, .sidebar-overlay, .page-header, .qr-controls, .print-actions, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 10px !important; }
            .page-wrapper { display: block !important; }
            body { background: #fff; margin: 0; }
            .qr-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .qr-card { border: 1px solid #ccc; padding: 10px; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

        <main class="main-content">
<?php $pageSubtitle = 'Print QR codes — scan to restock (+1 per scan)'; require __DIR__ . '/components/page_header.php'; ?>

            <div class="qr-controls no-print">
                <form method="GET" style="display:flex;gap:8px;align-items:center;">
                    <input type="text" name="search" class="form-input" placeholder="Filter by item name..." value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="size" value="<?= $size ?>">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filter</button>
                    <?php if ($search !== ''): ?>
                    <a href="qr_generator.php?size=<?= $size ?>" class="btn btn-secondary btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
                <div class="qr-size-btns">
                    <span style="font-size:0.8rem;color:var(--color-text-muted);margin-right:4px;">Size:</span>
                    <a href="?search=<?= urlencode($search) ?>&size=1" class="<?= $size === 1 ? 'active' : '' ?>" style="text-decoration:none;padding:6px 10px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.8rem;<?= $size === 1 ? 'background:var(--color-primary);color:white;border-color:var(--color-primary);' : 'background:var(--color-surface);' ?>">S</a>
                    <a href="?search=<?= urlencode($search) ?>&size=2" class="<?= $size === 2 ? 'active' : '' ?>" style="text-decoration:none;padding:6px 10px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.8rem;<?= $size === 2 ? 'background:var(--color-primary);color:white;border-color:var(--color-primary);' : 'background:var(--color-surface);' ?>">M</a>
                    <a href="?search=<?= urlencode($search) ?>&size=3" class="<?= $size === 3 ? 'active' : '' ?>" style="text-decoration:none;padding:6px 10px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.8rem;<?= $size === 3 ? 'background:var(--color-primary);color:white;border-color:var(--color-primary);' : 'background:var(--color-surface);' ?>">L</a>
                    <a href="?search=<?= urlencode($search) ?>&size=4" class="<?= $size === 4 ? 'active' : '' ?>" style="text-decoration:none;padding:6px 10px;border:1px solid var(--color-border);border-radius:var(--radius-sm);font-size:0.8rem;<?= $size === 4 ? 'background:var(--color-primary);color:white;border-color:var(--color-primary);' : 'background:var(--color-surface);' ?>">XL</a>
                </div>
            </div>

            <div class="print-actions no-print">
                <button class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print All QR Codes</button>
                <span style="font-size:0.85rem;color:var(--color-text-muted);line-height:36px;"><?= count($items) ?> item(s)</span>
            </div>

            <?php if (!empty($items)): ?>
            <div class="qr-grid" id="qrGrid">
                <?php foreach ($items as $item): ?>
                <?php
                // Auto-detect the server IP for phone access
                $ip = gethostbyname(gethostname());
                if ($ip === '127.0.0.1' || $ip === gethostname()) {
                    $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);
                    if ($sock) {
                        @socket_connect($sock, '8.8.8.8', 80);
                        @socket_getsockname($sock, $ip);
                        @socket_close($sock);
                    }
                }
                // QR points to the clean landing page (not the API directly)
                $restockUrl = 'http://' . $ip . '/ShoeInventorySystem/frontend/restock_scan.php?id=' . $item['id'];
                ?>
                <div class="qr-card" data-item-id="<?= $item['id'] ?>" data-item-name="<?= htmlspecialchars($item['name']) ?>" data-restock-url="<?= htmlspecialchars($restockUrl) ?>">
                    <div class="qr-image" id="qr-<?= $item['id'] ?>"></div>
                    <div class="qr-label"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="qr-meta">
                        <span>ID: <?= $item['id'] ?></span>
                        <span>$<?= number_format($item['price'], 2) ?></span>
                        <span><?= htmlspecialchars($item['supplier_name']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:60px 20px;color:var(--color-text-muted);">
                <i class="fa-solid fa-qrcode" style="font-size:3rem;margin-bottom:12px;display:block;opacity:0.3;"></i>
                <p>No items found<?= $search !== '' ? ' matching "' . htmlspecialchars($search) . '"' : '' ?>.</p>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    // Generate QR codes for each item (with restock URL)
    document.addEventListener('DOMContentLoaded', function() {
        var cards = document.querySelectorAll('.qr-card');
        var qrSize = <?= $qrSize ?>;
        var typeNumber = 0;
        var errorCorrectionLevel = 'M';

        cards.forEach(function(card) {
            var itemId = card.getAttribute('data-item-id');
            var itemName = card.getAttribute('data-item-name');
            var restockUrl = card.getAttribute('data-restock-url');
            var container = document.getElementById('qr-' + itemId);

            if (!container) return;

            // QR code contains the restock URL — scan it to add 1 to stock
            var qr = qrcode(typeNumber, errorCorrectionLevel);
            qr.addData(restockUrl);
            qr.make();

            var img = document.createElement('img');
            img.src = qr.createDataURL(4, 0);
            img.style.width = qrSize + 'px';
            img.style.height = qrSize + 'px';
            img.alt = 'Restock QR for ' + itemName;
            container.appendChild(img);
        });
    });
    </script>
    <?php require __DIR__ . '/components/footer.php'; ?>
</body>
</html>
