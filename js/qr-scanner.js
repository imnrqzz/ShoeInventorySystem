// js/qr-scanner.js - QR/Barcode scanner for restocking items

class QRScanner {
    constructor(options = {}) {
        this.onScan = options.onScan || function() {};
        this.modalId = options.modalId || 'qr-scanner-modal';
        this.scannerId = options.scannerId || 'qr-reader';
        this.html5QrCode = null;
        this.isScanning = false;
    }

    // Create the scanner modal HTML
    createModal() {
        if (document.getElementById(this.modalId)) return;

        const modal = document.createElement('div');
        modal.id = this.modalId;
        modal.className = 'modal-overlay qr-scanner-overlay';
        modal.style.display = 'none';
        modal.innerHTML = `
            <div class="modal-box qr-scanner-box">
                <div class="modal-header">
                    <h2><i class="fa-solid fa-qrcode"></i> Scan to Restock</h2>
                    <button class="modal-close" onclick="window.qrScanner.stop()">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="${this.scannerId}" class="qr-reader-container"></div>
                    <div class="qr-scan-input-group">
                        <input type="number" id="qr-manual-input" class="form-input" placeholder="Or enter item ID manually..." min="1">
                        <button class="btn btn-primary" onclick="window.qrScanner.submitManual()">
                            <i class="fa-solid fa-plus"></i> Restock
                        </button>
                    </div>
                    <p class="qr-scan-hint">Scan a QR code to add 1 unit to stock</p>
                    <div id="qr-scan-results" class="qr-scan-results"></div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.stop();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                this.stop();
            }
        });
    }

    // Open scanner modal
    async open() {
        this.createModal();
        const modal = document.getElementById(this.modalId);
        modal.style.display = 'flex';
        document.getElementById('qr-scan-results').innerHTML = '';
        document.getElementById('qr-manual-input').value = '';

        try {
            if (typeof Html5Qrcode === 'undefined') {
                await this._loadScript('https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js');
            }

            this.html5QrCode = new Html5Qrcode(this.scannerId);
            this.isScanning = true;

            await this.html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                (decodedText) => this._onScanSuccess(decodedText),
                () => {}
            );
        } catch (err) {
            console.error('Camera start failed:', err);
            document.getElementById('qr-scan-results').innerHTML =
                `<div class="qr-scan-error">
                    <i class="fa-solid fa-video-slash"></i>
                    <p>Camera unavailable. Use manual input below.</p>
                </div>`;
        }
    }

    // Stop scanning
    async stop() {
        const modal = document.getElementById(this.modalId);
        if (this.html5QrCode && this.isScanning) {
            try { await this.html5QrCode.stop(); } catch (e) {}
            this.isScanning = false;
        }
        if (modal) modal.style.display = 'none';
        const reader = document.getElementById(this.scannerId);
        if (reader) reader.innerHTML = '';
    }

    // Handle successful scan
    _onScanSuccess(decodedText) {
        if (this.html5QrCode && this.isScanning) {
            this.html5QrCode.pause();
        }

        // Check if it's a restock URL
        if (decodedText.includes('/api/restock.php?id=')) {
            const url = new URL(decodedText);
            const itemId = url.searchParams.get('id');
            if (itemId) {
                this._restockItem(itemId, decodedText);
                return;
            }
        }

        // Check if it's just an item ID (number)
        if (/^\d+$/.test(decodedText.trim())) {
            this._restockItem(decodedText.trim());
            return;
        }

        // It's a plain text (item name) — try to find item ID by name
        this._findAndRestock(decodedText);
    }

    // Handle manual input (item ID)
    submitManual() {
        const input = document.getElementById('qr-manual-input');
        const value = input.value.trim();
        if (value === '' || isNaN(value)) {
            document.getElementById('qr-scan-results').innerHTML =
                `<div class="qr-scan-error"><p>Please enter a valid item ID (number)</p></div>`;
            return;
        }
        this._restockItem(value);
    }

    // Restock an item by ID
    async _restockItem(itemId, scannedUrl) {
        const resultsDiv = document.getElementById('qr-scan-results');
        resultsDiv.innerHTML =
            `<div class="qr-scan-match">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Restocking item #${itemId}...</span>
            </div>`;

        try {
            // Use server IP for restock URL (works from any device on network)
            var serverIp = window.serverIp || window.location.hostname;
            const restockUrl = `http://${serverIp}/ShoeInventorySystem/api/restock.php?id=${itemId}`;

            const response = await fetch(restockUrl);
            const data = await response.json();

            if (data.success) {
                resultsDiv.innerHTML =
                    `<div class="qr-scan-match" style="background:#f0fdf4; border-color:#bbf7d0;">
                        <i class="fa-solid fa-check-circle" style="color:#16a34a;"></i>
                        <div>
                            <strong style="color:#166534;">${data.message}</strong><br>
                            <span style="font-size:0.85rem; color:#166534;">
                                Stock: ${data.previous_qty} → <strong>${data.new_qty}</strong>
                            </span>
                        </div>
                    </div>
                    <div class="qr-scan-actions">
                        <button class="btn btn-primary btn-sm" onclick="window.qrScanner.resumeScan()">
                            <i class="fa-solid fa-camera"></i> Scan Another
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="window.qrScanner.stop()">
                            <i class="fa-solid fa-xmark"></i> Close
                        </button>
                    </div>`;

                // Update stock page table if on stock page
                if (typeof updateStockRow === 'function') {
                    updateStockRow(data.item_id, data.new_qty);
                }
            } else {
                resultsDiv.innerHTML =
                    `<div class="qr-scan-error">
                        <i class="fa-solid fa-xmark-circle"></i>
                        <p>${data.message}</p>
                        <button class="btn btn-primary btn-sm" onclick="window.qrScanner.resumeScan()">
                            <i class="fa-solid fa-camera"></i> Try Again
                        </button>
                    </div>`;
            }
        } catch (err) {
            resultsDiv.innerHTML =
                `<div class="qr-scan-error">
                    <i class="fa-solid fa-wifi-slash"></i>
                    <p>Network error. Make sure you're connected.</p>
                    <button class="btn btn-primary btn-sm" onclick="window.qrScanner.resumeScan()">
                        <i class="fa-solid fa-camera"></i> Try Again
                    </button>
                </div>`;
        }
    }

    // Find item by name, then restock
    async _findAndRestock(name) {
        const resultsDiv = document.getElementById('qr-scan-results');
        resultsDiv.innerHTML =
            `<div class="qr-scan-match">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Looking up: <strong>${this._escapeHtml(name)}</strong></span>
            </div>`;

        try {
            const response = await fetch(`../api/index.php/items?search=${encodeURIComponent(name)}`, {
                headers: { 'X-API-Key': window.apiKey || '' }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.data && data.data.length > 0) {
                    // Show items and let user pick
                    let html = '<div class="qr-results-list">';
                    data.data.forEach(item => {
                        html += `
                            <div class="qr-result-item" onclick="window.qrScanner._restockItem(${item.id})">
                                <div class="qr-result-info">
                                    <strong>${this._escapeHtml(item.name)}</strong>
                                    <span class="text-muted">ID: ${item.id} | Current stock: ${item.quantity}</span>
                                </div>
                                <i class="fa-solid fa-arrow-up" style="color:var(--color-success);"></i>
                            </div>`;
                    });
                    html += '</div>';
                    html += `<div class="qr-scan-actions">
                        <button class="btn btn-primary btn-sm" onclick="window.qrScanner.resumeScan()">
                            <i class="fa-solid fa-camera"></i> Scan Again
                        </button>
                    </div>`;
                    resultsDiv.innerHTML += html;
                    return;
                }
            }
        } catch (e) {}

        resultsDiv.innerHTML =
            `<div class="qr-scan-error">
                <p>Item not found. Try scanning the QR code again.</p>
                <button class="btn btn-primary btn-sm" onclick="window.qrScanner.resumeScan()">
                    <i class="fa-solid fa-camera"></i> Try Again
                </button>
            </div>`;
    }

    // Resume scanning
    async resumeScan() {
        document.getElementById('qr-scan-results').innerHTML = '';
        document.getElementById('qr-manual-input').value = '';
        if (this.html5QrCode) {
            try { await this.html5QrCode.resume(); } catch (e) {
                this.stop();
                this.open();
            }
        }
    }

    _loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    _escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

// Initialize global scanner
window.qrScanner = new QRScanner();
