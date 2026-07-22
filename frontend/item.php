<?php
// frontend/item.php

require_once __DIR__ . '/components/auth.php';
require_once __DIR__ . '/../backend/itemtab.php';

$pageTitle = 'Items';
$pageCss = 'item_cards.css';
$activePage = 'items';
$isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
</head>
<body>
    <div class="page-wrapper">
        <?php require __DIR__ . '/components/sidebar.php'; ?>

        <main class="main-content">
<?php
$pageSubtitle = 'Browse and manage shoe inventory';
$headerAction = $isAdmin ? ['label' => '<i class="fa-solid fa-bars"></i> Actions', 'onclick' => 'toggleActionsMenu(event)'] : ['label' => '<i class="fa-solid fa-qrcode"></i> Scan QR / Barcode', 'onclick' => 'window.qrScanner.open()'];
require __DIR__ . '/components/page_header.php';
?>

            <?php if ($isAdmin): ?>
            <?php require __DIR__ . '/components/items/actions_menu.php'; ?>
            <?php endif; ?>

<?php
$toolbarAction = 'item.php';
$toolbarSearch = $search;
$toolbarPlaceholder = 'Search shoes by name...';
require __DIR__ . '/components/toolbar.php';
?>

            <?php require __DIR__ . '/components/items/grid.php'; ?>
            <?php require __DIR__ . '/components/items/import_modal.php'; ?>
        </main>
    </div>

    <?php if ($isAdmin): ?>
    <?php require __DIR__ . '/components/items/add_modal.php'; ?>
    <?php require __DIR__ . '/components/items/edit_modal.php'; ?>
    <?php require __DIR__ . '/components/items/image_upload_modal.php'; ?>
    <?php endif; ?>

    <script>
    // Flip card on click (unless clicking admin buttons)
    function handleCardClick(e, card) {
        if (e.target.closest('.flip-card-admin') || e.target.closest('.flip-card-actions')) return;
        card.classList.toggle('flipped');

        // Generate QR on first flip
        if (card.classList.contains('flipped')) {
            var id = card.getAttribute('data-id');
            var name = card.getAttribute('data-name');
            generateQR(id, name);
        }
    }

    // Generate QR code for card back (with restock landing page URL)
    function generateQR(id, name) {
        var container = document.getElementById('qr-back-' + id);
        if (!container || container.hasChildNodes()) return;

        var restockUrl = 'http://' + window.serverIp + '/ShoeInventorySystem/frontend/restock_scan.php?id=' + id;

        var qr = qrcode(0, 'M');
        qr.addData(restockUrl);
        qr.make();

        var img = document.createElement('img');
        img.src = qr.createDataURL(4, 0);
        img.style.width = '160px';
        img.style.height = '160px';
        img.alt = 'Restock QR for ' + name;
        container.appendChild(img);
    }

    // Download QR code as PNG
    function downloadQR(id, name) {
        var container = document.getElementById('qr-back-' + id);
        if (!container) return;

        var img = container.querySelector('img');
        if (!img) return;

        // Create a canvas with QR + label
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var padding = 20;
        var qrSize = 200;
        var labelHeight = 40;

        canvas.width = qrSize + padding * 2;
        canvas.height = qrSize + labelHeight + padding * 2;

        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Draw QR code
        ctx.drawImage(img, padding, padding, qrSize, qrSize);

        // Draw label
        ctx.fillStyle = '#333333';
        ctx.font = 'bold 14px Inter, Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(name, canvas.width / 2, qrSize + padding + 20);

        // Download
        var link = document.createElement('a');
        link.download = 'QR_' + name.replace(/[^a-zA-Z0-9]/g, '_') + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    // --- Image Upload ---
    var currentUploadItemId = null;

    function openImageUpload(itemId, itemName) {
        currentUploadItemId = itemId;
        document.getElementById('uploadItemName').textContent = itemName;
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('uploadZone').style.display = 'block';
        document.getElementById('uploadBtn').disabled = true;
        document.getElementById('removeImageBtn').style.display = 'none';
        document.getElementById('uploadStatus').innerHTML = '';
        document.getElementById('imageInput').value = '';
        document.getElementById('imageUploadModal').style.display = 'flex';
    }

    function closeImageUpload() {
        document.getElementById('imageUploadModal').style.display = 'none';
        currentUploadItemId = null;
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('uploadZone').style.display = 'none';
                document.getElementById('uploadBtn').disabled = false;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function uploadImage() {
        var input = document.getElementById('imageInput');
        if (!input.files || !input.files[0]) return;

        var formData = new FormData();
        formData.append('item_id', currentUploadItemId);
        formData.append('image', input.files[0]);
        formData.append('_csrf_token', window.csrfToken);

        document.getElementById('uploadStatus').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
        document.getElementById('uploadBtn').disabled = true;

        fetch('../backend/upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('uploadStatus').innerHTML = '<span style="color:var(--color-success)"><i class="fa-solid fa-check"></i> ' + data.message + '</span>';
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                document.getElementById('uploadStatus').innerHTML = '<span style="color:var(--color-danger)"><i class="fa-solid fa-xmark"></i> ' + data.message + '</span>';
                document.getElementById('uploadBtn').disabled = false;
            }
        })
        .catch(err => {
            document.getElementById('uploadStatus').innerHTML = '<span style="color:var(--color-danger)">Upload failed</span>';
            document.getElementById('uploadBtn').disabled = false;
        });
    }

    function removeImage() {
        if (!currentUploadItemId) return;
        if (!confirm('Remove this photo?')) return;

        var formData = new FormData();
        formData.append('item_id', currentUploadItemId);
        formData.append('_csrf_token', window.csrfToken);

        fetch('../backend/delete_image.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) window.location.reload();
        });
    }

    // Drag & drop support
    var uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        uploadZone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
        uploadZone.addEventListener('dragleave', function(e) { e.preventDefault(); this.classList.remove('dragover'); });
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            var input = document.getElementById('imageInput');
            input.files = e.dataTransfer.files;
            previewImage(input);
        });
    }

    // Generate QR codes for visible cards on load
    document.addEventListener('DOMContentLoaded', function() {
        // Pre-generate all QR codes (lightweight)
        document.querySelectorAll('.flip-card').forEach(function(card) {
            // QR will be generated on first flip for performance
        });
    });
    </script>
    <?php require __DIR__ . '/components/footer.php'; ?>
</body>
</html>
