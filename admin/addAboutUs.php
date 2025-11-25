<?php
// admin/addAboutUs.php
include 'session.php';
include 'db.php';

// Fetch Data
$sec1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_1 WHERE id=1"));
$sec2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_2 WHERE id=1"));
$sec3 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_3 WHERE id=1"));
$sec4 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_4 WHERE id=1"));

// Decode JSON data
$sec2_items = json_decode($sec2['content_json'] ?? '[]', true);
$sec3_items = json_decode($sec3['content_json'] ?? '[]', true);
$sec4_items = json_decode($sec4['features_json'] ?? '[]', true);

function showImg($path) {
    if (!empty($path) && file_exists('uploads/' . basename($path))) {
        return '<div class="mt-2"><img src="' . $path . '" style="height:80px;" class="img-thumbnail"></div>';
    }
    return '';
}
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
            <h2>Manage About Us Page</h2>

            <form method="POST" action="about_us/update.php" class="mb-5">
                <input type="hidden" name="section_type" value="1">
                <div class="card">
                    <div class="card-header bg-primary text-white">Part 1: Main Introduction</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Heading</label>
                            <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec1['heading'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label>Sub Heading</label>
                            <input type="text" name="sub_heading" class="form-control" value="<?= htmlspecialchars($sec1['sub_heading'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control editor" rows="4"><?= $sec1['description'] ?? '' ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer"><button class="btn btn-primary">Update Part 1</button></div>
                </div>
            </form>

            <form method="POST" action="about_us/update.php" class="mb-5">
                <input type="hidden" name="section_type" value="2">
                <div class="card">
                    <div class="card-header bg-success text-white">Part 2: Content Lists</div>
                    <div class="card-body">
                        <div class="mb-3" style="display:none;">
                            <label>Section Heading</label>
                            <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec2['heading'] ?? '') ?>">
                        </div>

                        <label>List Items (Title & Description)</label>
                        <div id="container-sec2">
                            <?php foreach($sec2_items as $index => $item): ?>
                            <div class="border p-3 mb-2 item-group">
                                <input type="text" name="titles[]" class="form-control mb-2" placeholder="Title" value="<?= htmlspecialchars($item['title']) ?>">
                                <textarea name="descs[]" class="form-control mb-2" placeholder="Description (Bullet points recommended)"><?= htmlspecialchars($item['desc']) ?></textarea>
                                <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addItem('container-sec2', 'titles[]', 'descs[]')">+ Add More Item</button>
                    </div>
                    <div class="card-footer"><button class="btn btn-success">Update Part 2</button></div>
                </div>
            </form>

            <form method="POST" action="about_us/update.php" class="mb-5">
                <input type="hidden" name="section_type" value="3">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Part 3: Network Cards</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Section Heading</label>
                            <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec3['heading'] ?? '') ?>">
                        </div>

                        <label>Network Locations</label>
                        <div id="container-sec3">
                            <?php foreach($sec3_items as $item): ?>
                            <div class="border p-3 mb-2 item-group">
                                <input type="text" name="titles[]" class="form-control mb-2" placeholder="Country / Title" value="<?= htmlspecialchars($item['title']) ?>">
                                <textarea name="descs[]" class="form-control mb-2 editor-mini" placeholder="Address Details"><?= htmlspecialchars($item['desc']) ?></textarea>
                                <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-warning btn-sm mt-2" onclick="addItem('container-sec3', 'titles[]', 'descs[]')">+ Add Location</button>
                    </div>
                    <div class="card-footer"><button class="btn btn-warning">Update Part 3</button></div>
                </div>
            </form>

            <form method="POST" action="about_us/update.php" enctype="multipart/form-data" class="mb-5">
                <input type="hidden" name="section_type" value="4">
                <div class="card">
                    <div class="card-header bg-info text-white">Part 4: Why Choose Us</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Main Heading</label>
                                <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec4['heading'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Side Image</label>
                                <input type="file" name="image" class="form-control">
                                <?= showImg($sec4['image_path'] ?? '') ?>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Main Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= $sec4['description'] ?? '' ?></textarea>
                            </div>
                        </div>

                        <label>Feature Items</label>
                        <div id="container-sec4">
                            <?php foreach($sec4_items as $item): ?>
                            <div class="border p-3 mb-2 item-group">
                                <input type="text" name="titles[]" class="form-control mb-2" placeholder="Feature Title" value="<?= htmlspecialchars($item['title']) ?>">
                                <textarea name="descs[]" class="form-control mb-2" placeholder="Feature Description"><?= htmlspecialchars($item['desc']) ?></textarea>
                                <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-info btn-sm mt-2" onclick="addItem('container-sec4', 'titles[]', 'descs[]')">+ Add Feature</button>

                    </div>
                    <div class="card-footer"><button class="btn btn-info text-white">Update Part 4</button></div>
                </div>
            </form>

        </main>
    </div>
</div>

<script>
    // Initialize CKEditor for Part 1
    CKEDITOR.replace('description');

    // Generic Add Item Function
    function addItem(containerId, titleName, descName) {
        const container = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'border p-3 mb-2 item-group';
        div.innerHTML = `
            <input type="text" name="${titleName}" class="form-control mb-2" placeholder="Title">
            <textarea name="${descName}" class="form-control mb-2" placeholder="Description"></textarea>
            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
        `;
        container.appendChild(div);
    }

    // Remove Item Event Delegation
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-item')){
            e.target.closest('.item-group').remove();
        }
    });
</script>
<?php include 'includes/script.php'; ?>
</body>
</html>