    <!-- Variant Detail Modal -->
    <div id="variantModal" class="modal-overlay" style="display:none">
        <div class="modal-box" style="max-width: 480px;">
            <div class="modal-header">
                <h2 id="variantItemName">Item Details</h2>
                <button class="modal-close" onclick="closeVariantModal()">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div id="variantLoading" style="text-align:center;padding:30px;color:var(--color-text-muted);">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading variants...
                </div>
                <div id="variantContent" style="display:none;">
                    <div id="variantImage" style="text-align:center;margin-bottom:16px;"></div>
                    <div id="variantPrice" style="text-align:center;font-size:1.25rem;font-weight:700;color:var(--color-primary);margin-bottom:8px;"></div>
                    <div id="variantStockSummary" style="text-align:center;font-size:0.85rem;color:var(--color-text-muted);margin-bottom:20px;"></div>
                    <div id="variantColors"></div>
                </div>
                <div id="variantError" style="display:none;text-align:center;padding:30px;color:var(--color-danger);">
                    <i class="fa-solid fa-circle-exclamation"></i> <span id="variantErrorMsg">Failed to load variants</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        .variant-modal-img {
            max-width: 100%;
            max-height: 180px;
            border-radius: var(--radius-md);
            object-fit: cover;
        }
        .variant-color-group {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 12px;
            margin-bottom: 12px;
        }
        .variant-color-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .variant-color-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid var(--color-border);
            display: inline-block;
        }
        .variant-sizes {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .variant-size-btn {
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--color-border);
            background: var(--color-surface);
            font-size: 0.85rem;
            cursor: default;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .variant-size-btn.in-stock {
            border-color: var(--color-success);
            background: #f0fdf4;
            color: #166534;
        }
        .variant-size-btn.out-of-stock {
            opacity: 0.5;
            text-decoration: line-through;
            color: var(--color-text-muted);
        }
        .variant-qty {
            font-size: 0.75rem;
            color: var(--color-text-muted);
        }
        .variant-size-btn.in-stock .variant-qty {
            color: #166534;
        }
        .no-variants {
            text-align: center;
            padding: 20px;
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }
    </style>

    <script>
    function openVariantModal(itemId, itemName) {
        var modal = document.getElementById('variantModal');
        var loading = document.getElementById('variantLoading');
        var content = document.getElementById('variantContent');
        var error = document.getElementById('variantError');

        document.getElementById('variantItemName').textContent = itemName;
        loading.style.display = 'block';
        content.style.display = 'none';
        error.style.display = 'none';
        modal.style.display = 'flex';

        fetch('../backend/get_variants.php?item_id=' + itemId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    throw new Error(data.message);
                }

                var item = data.item;
                var colors = data.colors;
                var totalVariantQty = data.total_variant_qty || 0;

                // Show image
                var imgContainer = document.getElementById('variantImage');
                if (item.image) {
                    imgContainer.innerHTML = '<img src="../' + item.image + '" alt="' + item.name + '" class="variant-modal-img">';
                } else {
                    imgContainer.innerHTML = '<div style="width:100%;height:120px;background:linear-gradient(135deg,#f0f4f8,#e2e8f0);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);"><i class="fa-solid fa-shoe-prints" style="font-size:2rem;opacity:0.3;"></i></div>';
                }

                // Show price
                document.getElementById('variantPrice').textContent = '$' + parseFloat(item.price).toFixed(2);

                // Show stock summary
                document.getElementById('variantStockSummary').innerHTML =
                    '<i class="fa-solid fa-box"></i> ' + totalVariantQty + ' total in stock across all variants';

                // Show variants
                var colorsHtml = '';
                var colorNames = Object.keys(colors);

                if (colorNames.length === 0) {
                    colorsHtml = '<div class="no-variants"><i class="fa-solid fa-info-circle"></i> No variants configured for this item.</div>';
                } else {
                    colorNames.forEach(function(color) {
                        var sizes = colors[color];
                        var sizesHtml = '';
                        sizes.forEach(function(s) {
                            var stockClass = s.in_stock ? 'in-stock' : 'out-of-stock';
                            var stockLabel = s.in_stock ? s.quantity + ' left' : 'Out of stock';
                            sizesHtml += '<div class="variant-size-btn ' + stockClass + '">';
                            sizesHtml += '<span>' + s.size + '</span>';
                            sizesHtml += '<span class="variant-qty">' + stockLabel + '</span>';
                            sizesHtml += '</div>';
                        });

                        colorsHtml += '<div class="variant-color-group">';
                        colorsHtml += '<div class="variant-color-name"><span class="variant-color-dot"></span> ' + color + '</div>';
                        colorsHtml += '<div class="variant-sizes">' + sizesHtml + '</div>';
                        colorsHtml += '</div>';
                    });
                }

                document.getElementById('variantColors').innerHTML = colorsHtml;
                loading.style.display = 'none';
                content.style.display = 'block';
            })
            .catch(function(err) {
                loading.style.display = 'none';
                error.style.display = 'block';
                document.getElementById('variantErrorMsg').textContent = err.message || 'Failed to load';
            });
    }

    function closeVariantModal() {
        document.getElementById('variantModal').style.display = 'none';
    }

    // Close modal on overlay click
    document.getElementById('variantModal').addEventListener('click', function(e) {
        if (e.target === this) closeVariantModal();
    });

    // Close modal on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeVariantModal();
    });
    </script>
