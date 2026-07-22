    <!-- Image Upload Modal -->
    <div class="modal-overlay" id="imageUploadModal" style="display:none">
        <div class="modal-box" style="max-width: 420px;">
            <div class="modal-header">
                <h2>Upload Photo</h2>
                <button class="modal-close" onclick="closeImageUpload()">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 16px;">
                    Upload a photo for <strong id="uploadItemName"></strong>
                </p>

                <div class="image-upload-zone" id="uploadZone" onclick="document.getElementById('imageInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Drag & drop an image here</p>
                    <p>or <span class="browse-link">browse files</span></p>
                    <p style="font-size: 0.75rem; margin-top: 8px;">JPG, PNG, GIF, WebP — Max 5MB</p>
                </div>
                <input type="file" id="imageInput" accept="image/*" style="display:none" onchange="previewImage(this)">
                <img id="imagePreview" class="image-preview" style="display:none" alt="Preview">

                <div id="uploadStatus" style="margin-top: 12px; font-size: 0.85rem;"></div>

                <div class="modal-footer" style="margin-top: 16px;">
                    <button type="button" class="btn btn-secondary" onclick="closeImageUpload()">Cancel</button>
                    <button type="button" class="btn btn-danger" id="removeImageBtn" style="display:none" onclick="removeImage()">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                    <button type="button" class="btn btn-primary" id="uploadBtn" onclick="uploadImage()" disabled>
                        <i class="fa-solid fa-upload"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>
