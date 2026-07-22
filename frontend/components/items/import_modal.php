            <?php $importMsg = $_GET['import_msg'] ?? ''; $importType = $_GET['import_type'] ?? ''; ?>
            <?php if ($importMsg): ?>
            <div class="alert-<?= $importType === 'success' ? 'success' : 'error' ?>" style="margin-bottom:16px;">
                <?= safe($importMsg) ?>
            </div>
            <?php endif; ?>

            <!-- Import XML Modal -->
            <div id="importXmlModal" class="modal-overlay" style="display:none;">
                <div class="modal-box">
                    <div class="modal-header">
                        <h2>Import XML</h2>
                        <button class="modal-close" onclick="document.getElementById('importXmlModal').style.display='none'">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="import_xml.php" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <p style="font-size:var(--font-size-sm);color:var(--color-text-muted);margin-bottom:16px;">Upload an XML file with items. Existing items will be updated, new ones will be added.</p>
                            <div class="form-group full-width">
                                <label>XML File *</label>
                                <input type="file" name="xml_file" accept=".xml" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('importXmlModal').style.display='none'">Cancel</button>
                                <button type="submit" class="btn btn-primary">Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>