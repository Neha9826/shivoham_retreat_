<?php
// admin/addGallery.php
include 'session.php';
include 'db.php';

// fetch existing gallery records
$galleryRs = mysqli_query($conn, "SELECT * FROM gallery ORDER BY id DESC");

// We don't need ADMIN_BASE_URL for images if we use relative paths
// But we keep it for JS variable passing if needed
$ADMIN_BASE_URL = '/admin/';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container px-4 mt-4">
            <h2>Add / Manage Gallery</h2>

            <form id="form-gallery" method="POST" action="gallery/insert.php" enctype="multipart/form-data">
                <div class="card mb-4" id="section-gallery">
                    <div class="card-header">Gallery Images & Descriptions</div>
                    <div class="card-body" id="galleryContainer">
                        
                        <div class="gallery-item border p-3 mb-3">
                            <input type="hidden" name="gallery_id[]" class="gallery_id" value="">
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Image</label>
                                <input type="file" name="gallery_image[]" class="form-control mb-2">
                                
                                <div class="edit-image-preview" style="display:none;">
                                    <p class="text-muted small">Current Image:</p>
                                    <img src="" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="gallery_description[]" class="form-control editor" id="gallery_editor_0" rows="3"></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="button" id="addMoreGallery" class="btn btn-outline-primary">+ Add More Images</button>
                        
                        <div>
                            <button type="submit" name="save_gallery" id="btn-save-gallery" class="btn btn-primary">Save Gallery</button>
                            <button type="button" id="btn-update-gallery" class="btn btn-success" style="display:none;">Update Gallery</button>
                            <button type="button" id="btn-cancel-gallery" class="btn btn-secondary" style="display:none;">Cancel Edit</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row mb-4" id="existingGalleryList">
                <?php while ($row = mysqli_fetch_assoc($galleryRs)): ?>
                    <div class="col-md-4 mb-4" id="gallery-card-<?= $row['id'] ?>">
                        <div class="card h-100">
                            <div style="height: 200px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                <img src="<?= htmlspecialchars($row['image_path']) ?>" class="card-img-top" style="object-fit: cover; height: 100%; width: 100%;" alt="Gallery Image">
                            </div>
                            
                            <div class="card-body">
                                <div class="card-text description-content">
                                    <?= $row['description'] ?>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-white d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary btn-edit-gallery" data-id="<?= $row['id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-gallery" data-id="<?= $row['id'] ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </main>
    </div>
</div>

<script>
    // Initialize first CKEditor
    CKEDITOR.replace('gallery_editor_0');

    let galleryEditorIndex = 1;
    // Pass PHP var to JS if needed for edit mode, otherwise leave empty
    const ADMIN_BASE_URL = ''; 

    // --- ADD MORE BUTTON LOGIC ---
    document.getElementById('addMoreGallery').addEventListener('click', function(){
        const container = document.getElementById('galleryContainer');
        const id = 'gallery_editor_' + galleryEditorIndex;
        
        const div = document.createElement('div');
        div.className = 'gallery-item border p-3 mb-3 gallery-group';
        div.innerHTML = `
            <input type="hidden" name="gallery_id[]" class="gallery_id" value="">
            
            <div class="mb-3">
                <label class="form-label">Upload Image</label>
                <input type="file" name="gallery_image[]" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="gallery_description[]" class="form-control editor" id="${id}" rows="3"></textarea>
            </div>
            
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm removeGallery">Remove</button>
            </div>
        `;
        
        container.appendChild(div);
        CKEDITOR.replace(id);
        galleryEditorIndex++;
    });

    // --- REMOVE BUTTON LOGIC ---
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('removeGallery')){
            const group = e.target.closest('.gallery-group');
            const ta = group.querySelector('textarea');
            if(ta && CKEDITOR.instances[ta.id]) {
                CKEDITOR.instances[ta.id].destroy();
            }
            group.remove();
        }
    });

    // utility: fetch JSON
    async function fetchJSON(url){
        const res = await fetch(url);
        return res.json();
    }

    function escapeHtml(str){
        if(!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function updateAllEditors(){
        for(let i in CKEDITOR.instances){
            CKEDITOR.instances[i].updateElement();
        }
    }

    // ================= EDIT LOGIC =================
    document.addEventListener('click', async function(e){
        if(e.target && e.target.classList.contains('btn-edit-gallery')){
            const id = e.target.getAttribute('data-id');
            // Ensure you have gallery/get.php created
            const data = await fetchJSON('gallery/get.php?id=' + id);
            
            if(data.success){
                const container = document.getElementById('galleryContainer');
                container.innerHTML = ''; // Clear "Add More" items
                
                // Hide "Add More" button during edit
                document.getElementById('addMoreGallery').style.display = 'none';

                const idEditor = 'gallery_edit_editor_0';
                
                // Create single edit form
                const div = document.createElement('div');
                div.className = 'gallery-item border p-3 mb-3';
                
                // Determine image source
                // If the path in DB is "uploads/img.jpg", we just use that directly
                const imgSrc = data.row.image_path ? data.row.image_path : '';
                
                div.innerHTML = `
                    <input type="hidden" name="gallery_id[]" class="gallery_id" value="${data.row.id}">
                    
                    <div class="mb-3">
                        <label class="form-label">Change Image (Leave empty to keep current)</label>
                        <input type="file" name="gallery_image[]" class="form-control mb-2">
                        
                        ${imgSrc ? `
                        <div class="edit-image-preview">
                            <p class="text-muted small mb-1">Current Image:</p>
                            <img src="${imgSrc}" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                        ` : ''}
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="gallery_description[]" class="form-control editor" id="${idEditor}" rows="3">${data.row.description}</textarea>
                    </div>
                `;
                
                container.appendChild(div);
                CKEDITOR.replace(idEditor);
                CKEDITOR.instances[idEditor].setData(data.row.description || '');
                
                // Toggle Buttons
                document.getElementById('btn-save-gallery').style.display = 'none';
                document.getElementById('btn-update-gallery').style.display = 'inline-block';
                document.getElementById('btn-cancel-gallery').style.display = 'inline-block';
                
                // Scroll to form
                document.querySelector('#form-gallery').scrollIntoView({behavior:'smooth'});
            } else {
                alert('Record not found or error fetching data.');
            }
        }
    });

    // ================= UPDATE LOGIC =================
    document.getElementById('btn-update-gallery').addEventListener('click', async function(){
        updateAllEditors();
        const fd = new FormData(document.getElementById('form-gallery'));
        const res = await fetch('gallery/update.php', {method:'POST', body: fd});
        const json = await res.json();
        if(json.success) location.reload(); 
        else alert(json.error || 'Update failed');
    });

    // ================= CANCEL LOGIC =================
    document.getElementById('btn-cancel-gallery').addEventListener('click', function(){
        location.reload(); 
    });

    // ================= DELETE LOGIC =================
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('btn-delete-gallery')){
            const id = e.target.getAttribute('data-id');
            if(!confirm('Are you sure you want to delete this gallery item?')) return;
            
            fetch('gallery/delete.php?id=' + id).then(r => r.json()).then(json => {
                if(json.success) document.getElementById('gallery-card-' + id).remove();
                else alert(json.error || 'Delete failed');
            });
        }
    });

</script>
<?php include 'includes/script.php'; ?>
</body>
</html>