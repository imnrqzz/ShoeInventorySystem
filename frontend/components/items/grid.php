            <div class="items-grid" id="itemsGrid">
                <?php if (!empty($items)): foreach ($items as $item):
                    $qty = (int)$item['quantity'];
                    $min = (int)$item['min_quantity'];
                    $isLow = $qty <= $min;
                    $isAdmin = isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
                    $imagePath = !empty($item['image']) ? '../' . $item['image'] : '';
                ?>
                <div class="flip-card" data-id="<?= (int)$item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" data-price="<?= number_format($item['price'], 2) ?>" data-supplier="<?= htmlspecialchars($item['supplier_name'] ?? '') ?>" data-qty="<?= $qty ?>" onclick="handleCardClick(event, this)">
                    <!-- Front Face -->
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="flip-card-image">
                                <?php if ($imagePath): ?>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                <div class="no-image">
                                    <i class="fa-solid fa-shoe-prints"></i>
                                    <span>No photo</span>
                                </div>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                <div class="flip-card-admin">
                                    <button type="button" title="Upload photo" onclick="event.stopPropagation(); openImageUpload(<?= (int)$item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')"><i class="fa-solid fa-camera"></i></button>
                                    <button type="button" title="Edit item" onclick="event.stopPropagation(); window.location.href='item.php?edit_id=<?= (int)$item['id'] ?>'"><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="delete-btn" title="Delete item" onclick="event.stopPropagation(); confirmDelete('Delete this item?', 'item.php?delete_id=<?= (int)$item['id'] ?>')"><i class="fa-solid fa-trash"></i></button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="flip-card-info">
                                <div class="flip-card-name" title="<?= htmlspecialchars($item['name']) ?>"><?= safe($item['name']) ?></div>
                                <div class="flip-card-meta">
                                    <span class="flip-card-price">$<?= number_format($item['price'], 2) ?></span>
                                    <span class="flip-card-stock">
                                        <span class="dot <?= $isLow ? 'low' : '' ?>"></span>
                                        <?= $qty ?> in stock
                                    </span>
                                </div>
                            </div>
                            <span class="flip-hint">Click to see QR</span>
                        </div>

                        <!-- Back Face - QR Code -->
                        <div class="flip-card-back">
                            <div class="qr-container" id="qr-back-<?= (int)$item['id'] ?>"></div>
                            <div class="item-name-back"><?= safe($item['name']) ?></div>
                            <div class="item-details">
                                ID: <?= (int)$item['id'] ?> | $<?= number_format($item['price'], 2) ?> | <?= htmlspecialchars($item['supplier_name'] ?? '—') ?>
                            </div>
                            <div class="flip-card-actions">
                                <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); downloadQR(<?= (int)$item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')">
                                    <i class="fa-solid fa-download"></i> Download QR
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="event.stopPropagation(); this.closest('.flip-card').classList.remove('flipped')">
                                    <i class="fa-solid fa-rotate-left"></i> Flip Back
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--color-text-muted);">
                    <i class="fa-solid fa-shoe-prints" style="font-size: 3rem; margin-bottom: 12px; display: block; opacity: 0.3;"></i>
                    <p>No items found<?= $search !== '' ? ' matching "' . htmlspecialchars($search) . '"' : '' ?>.</p>
                    <?php if ($isAdmin): ?>
                    <button class="btn btn-primary" style="margin-top: 12px;" onclick="document.getElementById('addItemModal').style.display='flex'">
                        <i class="fa-solid fa-plus"></i> Add First Item
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
