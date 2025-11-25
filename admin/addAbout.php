<?php
// admin/addAbout.php
include 'session.php';
include 'db.php';

// fetch existing records
$aboutMainRs = mysqli_query($conn, "SELECT * FROM about_1 ORDER BY id DESC");
$aboutInfoRs = mysqli_query($conn, "SELECT * FROM about_info ORDER BY id DESC");
$aboutInfo2Rs = mysqli_query($conn, "SELECT * FROM about_info_2 ORDER BY id DESC"); // New Section
$aboutSliderRs = mysqli_query($conn, "SELECT * FROM about_slider ORDER BY id DESC");

// admin base URL used to display uploaded images
$ADMIN_BASE_URL = '/admin/';
?>
<!DOCTYPE html>
<html lang="en">
<?php include 'includes/head.php'; ?>
<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
    <?php include 'includes/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container px-4 mt-4">
            <h2>Add / Manage About Page</h2>

            <!-- ===== MAIN SECTION FORM ===== -->
            <form id="form-main" method="POST" action="about_main/insert.php">
                <div class="card mb-4" id="section-main">
                    <div class="card-header">About Main Section</div>
                    <div class="card-body">
                        <input type="hidden" name="id" id="main-id" value="">
                        
                        <div class="mb-3">
                            <label>Main Heading</label>
                            <input type="text" name="main_heading" id="main_heading" class="form-control" required>
                        </div>
                        
                        <!-- New Sub Heading Field -->
                        <div class="mb-3">
                            <label>Main Sub Heading</label>
                            <input type="text" name="main_sub_heading" id="main_sub_heading" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Main Description</label>
                            <textarea name="main_description" id="main_description" class="form-control editor" rows="5"></textarea>
                        </div>
                        
                        <!-- Image fields removed as requested -->
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <button type="submit" name="save_main" id="btn-save-main" class="btn btn-primary">Save Main Section</button>
                        <button type="button" id="btn-update-main" class="btn btn-success" style="display:none;">Update Main</button>
                        <button type="button" id="btn-cancel-main" class="btn btn-secondary" style="display:none;">Cancel Edit</button>
                    </div>
                </div>
            </form>

            <!-- show existing main records -->
            <div class="row mb-4">
                <?php while ($r = mysqli_fetch_assoc($aboutMainRs)): ?>
                    <div class="col-md-12 mb-3">
                        <div class="card p-3">
                            <h4 class="mb-1"><?= htmlspecialchars($r['main_heading']) ?></h4>
                            <h6 class="text-muted mb-2"><?= htmlspecialchars($r['main_sub_heading'] ?? '') ?></h6>
                            <div class="mb-2"><?= $r['main_description'] ?></div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary btn-edit-main" data-id="<?= $r['id'] ?>">Edit</button>
                                <!-- Delete functionality for main if needed -->
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- ===== INFO SECTION 1 ===== -->
            <form id="form-info" method="POST" action="about_info/insert.php">
                <div class="card mb-4" id="section-info">
                    <div class="card-header">About Info Section 1</div>
                    <div class="card-body" id="infoContainer">
                        <div class="info-item border p-3 mb-3">
                            <input type="hidden" name="info_id[]" class="info_id" value="">
                            <div class="mb-2">
                                <label>Title</label>
                                <input type="text" name="info_title[]" class="form-control" required>
                            </div>
                            <div>
                                <label>Description</label>
                                <textarea name="info_description[]" class="form-control editor" id="info_editor_0" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="button" id="addMoreInfo" class="btn btn-outline-primary">+ Add More</button>
                        <div>
                            <button type="submit" name="save_info" class="btn btn-primary">Save Info Section 1</button>
                            <button type="button" id="btn-update-info" class="btn btn-success" style="display:none;">Update Info 1</button>
                            <button type="button" id="btn-cancel-info" class="btn btn-secondary" style="display:none;">Cancel Edit</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- existing info 1 list -->
            <div class="row mb-4" id="existingInfoList">
                <?php while ($row = mysqli_fetch_assoc($aboutInfoRs)): ?>
                    <div class="col-md-4 mb-3" id="info-card-<?= $row['id'] ?>">
                        <div class="card p-2 h-100">
                            <h5><?= htmlspecialchars($row['info_title']) ?></h5>
                            <div><?= $row['info_description'] ?></div>
                            <div class="d-flex gap-2 mt-auto pt-2">
                                <button class="btn btn-sm btn-outline-primary btn-edit-info" data-id="<?= $row['id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-info" data-id="<?= $row['id'] ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- ===== INFO SECTION 2 (NEW) ===== -->
            <form id="form-info-2" method="POST" action="about_info_2/insert.php">
                <div class="card mb-4" id="section-info-2">
                    <div class="card-header bg-light">About Info Section 2</div>
                    <div class="card-body" id="infoContainer2">
                        <div class="info-item-2 border p-3 mb-3">
                            <input type="hidden" name="info_id[]" class="info_id" value="">
                            <div class="mb-2">
                                <label>Title</label>
                                <input type="text" name="info_title[]" class="form-control" required>
                            </div>
                            <div>
                                <label>Description</label>
                                <textarea name="info_description[]" class="form-control editor" id="info2_editor_0" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button type="button" id="addMoreInfo2" class="btn btn-outline-primary">+ Add More</button>
                        <div>
                            <button type="submit" name="save_info" class="btn btn-primary">Save Info Section 2</button>
                            <button type="button" id="btn-update-info-2" class="btn btn-success" style="display:none;">Update Info 2</button>
                            <button type="button" id="btn-cancel-info-2" class="btn btn-secondary" style="display:none;">Cancel Edit</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- existing info 2 list -->
            <div class="row mb-4" id="existingInfoList2">
                <?php while ($row2 = mysqli_fetch_assoc($aboutInfo2Rs)): ?>
                    <div class="col-md-4 mb-3" id="info2-card-<?= $row2['id'] ?>">
                        <div class="card p-2 h-100 border-info">
                            <h5><?= htmlspecialchars($row2['info_title']) ?></h5>
                            <div><?= $row2['info_description'] ?></div>
                            <div class="d-flex gap-2 mt-auto pt-2">
                                <button class="btn btn-sm btn-outline-primary btn-edit-info-2" data-id="<?= $row2['id'] ?>">Edit</button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-info-2" data-id="<?= $row2['id'] ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- ===== SLIDER SECTION ===== -->
            <form id="form-slider" method="POST" action="about_slider/insert.php" enctype="multipart/form-data">
                <div class="card mb-4" id="section-slider">
                    <div class="card-header">Slider Images</div>
                    <div class="card-body">
                        <label>Upload Images</label>
                        <input type="file" name="slider_images[]" class="form-control" multiple>
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="save_slider" class="btn btn-primary">Save Slider Images</button>
                    </div>
                </div>
            </form>
             <!-- existing slider images -->
             <div class="row" id="existingSliderList">
                <?php while ($s = mysqli_fetch_assoc($aboutSliderRs)): ?>
                    <div class="col-md-3 mb-3" id="slider-card-<?= $s['id'] ?>">
                        <div class="card p-2 text-center">
                            <img src="<?= htmlspecialchars($s['image']) ?>" class="img-fluid mb-2" style="max-height:100px;">
                            <div class="d-flex gap-2 justify-content-center">
                                <button class="btn btn-sm btn-outline-danger btn-delete-slider" data-id="<?= $s['id'] ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </main>
    </div>
</div>

<!-- JS: CKEditor + AJAX behavior -->
<script>
    // Initialize CKEditors
    CKEDITOR.replace('main_description');
    CKEDITOR.replace('info_editor_0');
    CKEDITOR.replace('info2_editor_0'); // New section editor

    // Helper for dynamic IDs
    let infoEditorIndex = 1;
    let info2EditorIndex = 1; // New index for section 2

    // --- SECTION 1 ADD MORE ---
    document.getElementById('addMoreInfo').addEventListener('click', function(){
        const container = document.getElementById('infoContainer');
        const id = 'info_editor_' + infoEditorIndex;
        const div = document.createElement('div');
        div.className = 'info-item border p-3 mb-3 info-group';
        div.innerHTML = `
            <input type="hidden" name="info_id[]" class="info_id" value="">
            <div class="mb-2"><label>Title</label><input type="text" name="info_title[]" class="form-control" required></div>
            <div><label>Description</label><textarea name="info_description[]" class="form-control editor" id="${id}" rows="3" required></textarea></div>
            <div class="mt-2"><button type="button" class="btn btn-danger btn-sm removeInfo">Remove</button></div>
        `;
        container.appendChild(div);
        CKEDITOR.replace(id);
        infoEditorIndex++;
    });

    // --- SECTION 2 ADD MORE ---
    document.getElementById('addMoreInfo2').addEventListener('click', function(){
        const container = document.getElementById('infoContainer2');
        const id = 'info2_editor_' + info2EditorIndex;
        const div = document.createElement('div');
        div.className = 'info-item-2 border p-3 mb-3 info-group-2';
        div.innerHTML = `
            <input type="hidden" name="info_id[]" class="info_id" value="">
            <div class="mb-2"><label>Title</label><input type="text" name="info_title[]" class="form-control" required></div>
            <div><label>Description</label><textarea name="info_description[]" class="form-control editor" id="${id}" rows="3" required></textarea></div>
            <div class="mt-2"><button type="button" class="btn btn-danger btn-sm removeInfo2">Remove</button></div>
        `;
        container.appendChild(div);
        CKEDITOR.replace(id);
        info2EditorIndex++;
    });

    // Remove Info handlers
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('removeInfo')){
            const group = e.target.closest('.info-group');
            const ta = group.querySelector('textarea');
            if(ta && CKEDITOR.instances[ta.id]) CKEDITOR.instances[ta.id].destroy();
            group.remove();
        }
        if(e.target && e.target.classList.contains('removeInfo2')){
            const group = e.target.closest('.info-group-2');
            const ta = group.querySelector('textarea');
            if(ta && CKEDITOR.instances[ta.id]) CKEDITOR.instances[ta.id].destroy();
            group.remove();
        }
    });

    // utility: fetch JSON
    async function fetchJSON(url){
        const res = await fetch(url);
        return res.json();
    }

    // ================= MAIN SECTION LOGIC =================
    document.addEventListener('click', async function(e){
        if(e.target && e.target.classList.contains('btn-edit-main')){
            const id = e.target.getAttribute('data-id');
            const data = await fetchJSON('about_main/get.php?id=' + id);
            if(data.success){
                document.getElementById('main-id').value = data.row.id;
                document.getElementById('main_heading').value = data.row.main_heading;
                document.getElementById('main_sub_heading').value = data.row.main_sub_heading || ''; // Populate sub heading
                CKEDITOR.instances['main_description'].setData(data.row.main_description || '');
                
                document.getElementById('btn-save-main').style.display = 'none';
                document.getElementById('btn-update-main').style.display = 'inline-block';
                document.getElementById('btn-cancel-main').style.display = 'inline-block';
                document.getElementById('section-main').scrollIntoView({behavior:'smooth'});
            } else {
                alert('Record not found');
            }
        }
    });

    document.getElementById('btn-update-main').addEventListener('click', async function(){
        const id = document.getElementById('main-id').value;
        if(!id){ alert('No main record selected'); return; }
        const fd = new FormData(document.getElementById('form-main'));
        fd.append('id', id);
        fd.set('main_description', CKEDITOR.instances['main_description'].getData());
        const res = await fetch('about_main/update.php', {method:'POST', body: fd});
        const json = await res.json();
        if(json.success) location.reload();
        else alert(json.error || 'Update failed');
    });

    document.getElementById('btn-cancel-main').addEventListener('click', function(){
        document.getElementById('form-main').reset();
        document.getElementById('main-id').value = '';
        CKEDITOR.instances['main_description'].setData('');
        document.getElementById('btn-save-main').style.display = 'inline-block';
        document.getElementById('btn-update-main').style.display = 'none';
        document.getElementById('btn-cancel-main').style.display = 'none';
    });

    // ================= INFO SECTION 1 LOGIC =================
    document.addEventListener('click', async function(e){
        if(e.target && e.target.classList.contains('btn-edit-info')){
            const id = e.target.getAttribute('data-id');
            const data = await fetchJSON('about_info/get.php?id=' + id);
            if(data.success){
                const container = document.getElementById('infoContainer');
                container.innerHTML = '';
                const idEditor = 'info_edit_editor_0';
                const div = document.createElement('div');
                div.className = 'info-item border p-3 mb-3';
                div.innerHTML = `
                    <input type="hidden" name="info_id[]" class="info_id" value="${data.row.id}">
                    <div class="mb-2"><label>Title</label><input type="text" name="info_title[]" class="form-control" value="${escapeHtml(data.row.info_title)}" required></div>
                    <div><label>Description</label><textarea name="info_description[]" class="form-control editor" id="${idEditor}" rows="3" required>${data.row.info_description}</textarea></div>
                `;
                container.appendChild(div);
                CKEDITOR.replace(idEditor);
                CKEDITOR.instances[idEditor].setData(data.row.info_description || '');
                
                document.getElementById('btn-update-info').style.display = 'inline-block';
                document.getElementById('btn-cancel-info').style.display = 'inline-block';
                document.querySelector('#form-info').scrollIntoView({behavior:'smooth'});
            }
        }
    });

    document.getElementById('btn-update-info').addEventListener('click', async function(){
        updateAllEditors();
        const fd = new FormData(document.getElementById('form-info'));
        const res = await fetch('about_info/update.php', {method:'POST', body: fd});
        const json = await res.json();
        if(json.success) location.reload(); else alert(json.error || 'Update failed');
    });

    document.getElementById('btn-cancel-info').addEventListener('click', function(){ location.reload(); });

    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('btn-delete-info')){
            const id = e.target.getAttribute('data-id');
            if(!confirm('Delete this info block?')) return;
            fetch('about_info/delete.php?id=' + id).then(r => r.json()).then(json => {
                if(json.success) document.getElementById('info-card-' + id).remove();
                else alert(json.error || 'Delete failed');
            });
        }
    });

    // ================= INFO SECTION 2 LOGIC (NEW) =================
    document.addEventListener('click', async function(e){
        if(e.target && e.target.classList.contains('btn-edit-info-2')){
            const id = e.target.getAttribute('data-id');
            // Fetch from about_info_2 folder
            const data = await fetchJSON('about_info_2/get.php?id=' + id);
            if(data.success){
                const container = document.getElementById('infoContainer2');
                container.innerHTML = '';
                const idEditor = 'info2_edit_editor_0';
                const div = document.createElement('div');
                div.className = 'info-item-2 border p-3 mb-3';
                div.innerHTML = `
                    <input type="hidden" name="info_id[]" class="info_id" value="${data.row.id}">
                    <div class="mb-2"><label>Title</label><input type="text" name="info_title[]" class="form-control" value="${escapeHtml(data.row.info_title)}" required></div>
                    <div><label>Description</label><textarea name="info_description[]" class="form-control editor" id="${idEditor}" rows="3" required>${data.row.info_description}</textarea></div>
                `;
                container.appendChild(div);
                CKEDITOR.replace(idEditor);
                CKEDITOR.instances[idEditor].setData(data.row.info_description || '');
                
                document.getElementById('btn-update-info-2').style.display = 'inline-block';
                document.getElementById('btn-cancel-info-2').style.display = 'inline-block';
                document.querySelector('#form-info-2').scrollIntoView({behavior:'smooth'});
            }
        }
    });

    // Update Info 2
    document.getElementById('btn-update-info-2').addEventListener('click', async function(){
        updateAllEditors();
        const fd = new FormData(document.getElementById('form-info-2'));
        const res = await fetch('about_info_2/update.php', {method:'POST', body: fd});
        const json = await res.json();
        if(json.success) location.reload(); else alert(json.error || 'Update failed');
    });

    document.getElementById('btn-cancel-info-2').addEventListener('click', function(){ location.reload(); });

    // Delete Info 2
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('btn-delete-info-2')){
            const id = e.target.getAttribute('data-id');
            if(!confirm('Delete this info block?')) return;
            fetch('about_info_2/delete.php?id=' + id).then(r => r.json()).then(json => {
                if(json.success) document.getElementById('info2-card-' + id).remove();
                else alert(json.error || 'Delete failed');
            });
        }
    });

    // Slider Delete Logic (unchanged)
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('btn-delete-slider')){
            const id = e.target.getAttribute('data-id');
            if(!confirm('Delete this slider image?')) return;
            fetch('about_slider/delete.php?id=' + id).then(r => r.json()).then(json => {
                if(json.success) document.getElementById('slider-card-' + id).remove();
                else alert(json.error || 'Delete failed');
            });
        }
    });

    function updateAllEditors(){
        for(let i in CKEDITOR.instances){
            CKEDITOR.instances[i].updateElement();
        }
    }

    function escapeHtml(str){
        if(!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
</script>
<?php include 'includes/script.php'; ?>
</body>
</html>