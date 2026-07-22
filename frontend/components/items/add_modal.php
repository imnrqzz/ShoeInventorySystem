    <!-- Add Item Modal -->
    <div class="modal-overlay" id="addItemModal" style="display:none">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Add New Item</h2>
                <a href="item.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <form method="POST" action="item.php" data-validate novalidate>
                    <input type="hidden" name="action" value="add">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label>Shoe Model Name *</label>
                            <input type="text" name="item_name" placeholder="e.g. Air Max 90" required minlength="2">
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" name="color" placeholder="e.g. Black, White">
                        </div>
                        <div class="form-group">
                            <label>Size</label>
                            <input type="text" name="size" placeholder="e.g. US 9, EU 42">
                        </div>
                        <div class="form-group">
                            <label>Price ($) *</label>
                            <input type="number" step="0.01" name="price" value="0.00" min="0" required>
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Min. Alert Threshold *</label>
                            <input type="number" step="1" name="min_quantity" value="5" min="0" required>
                            <span class="field-error"></span>
                        </div>
                        <div class="form-group full-width">
                            <label>Supplier</label>
                            <select name="supplier_id">
                                <option value="">- None -</option>
                                <?php foreach ($suppliers as $s): ?>
                                <option value="<?= (int)$s['id'] ?>"><?= safe($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="item.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>