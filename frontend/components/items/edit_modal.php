    <?php if ($editing_item): ?>
    <!-- Edit Item Modal -->
    <div class="modal-overlay" id="editItemModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Edit Item #<?= (int)$editing_item['id'] ?></h2>
                <a href="item.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php" data-validate novalidate>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= (int)$editing_item['id'] ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" value="<?= safe($editing_item['name']) ?>" required minlength="2">
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Price ($) *</label>
                            <input type="number" step="0.01" name="price" value="<?= number_format((float)$editing_item['price'], 2, '.', '') ?>" min="0" required>
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold *</label>
                            <input type="number" step="1" name="min_quantity" value="<?= (int)$editing_item['min_quantity'] ?>" min="0" required>
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group full-width">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">- None -</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int)$s['id'] ?>" <?= $editing_item['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= safe($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="item.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>