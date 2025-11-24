<?php
// admin/addActivities.php
include 'session.php';
include 'db.php';

// Fetch Data for all 4 sections (Assuming ID=1 for single page content)
$sec1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_1 WHERE id=1"));
$sec2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_2 WHERE id=1"));
$sec3 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_3 WHERE id=1"));
$sec4 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_4 WHERE id=1"));

// Helper to display existing images
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
            <h2>Manage Activities Page</h2>

            <form method="POST" action="activities/update.php" enctype="multipart/form-data" class="mb-5">
                <input type="hidden" name="section_type" value="1">
                <div class="card">
                    <div class="card-header bg-primary text-white">Part 1: Ex- Family Fun</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Heading</label>
                                <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec1['heading'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Sub Heading</label>
                                <input type="text" name="sub_heading" class="form-control" value="<?= htmlspecialchars($sec1['sub_heading'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Image 1</label>
                                <input type="file" name="image_1" class="form-control">
                                <?= showImg($sec1['image_1'] ?? '') ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Image 2</label>
                                <input type="file" name="image_2" class="form-control">
                                <?= showImg($sec1['image_2'] ?? '') ?>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" id="editor1" class="form-control" rows="4"><?= $sec1['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Part-1</button>
                    </div>
                </div>
            </form>

            <form method="POST" action="activities/update.php" enctype="multipart/form-data" class="mb-5">
                <input type="hidden" name="section_type" value="2">
                <div class="card">
                    <div class="card-header bg-success text-white">Part 2: Ex- Kids Playground</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Heading</label>
                                <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec2['heading'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Sub Heading</label>
                                <input type="text" name="sub_heading" class="form-control" value="<?= htmlspecialchars($sec2['sub_heading'] ?? '') ?>">
                            </div>
                            <?php for($i=1; $i<=4; $i++): ?>
                            <div class="col-md-3 mb-3">
                                <label>Image <?= $i ?></label>
                                <input type="file" name="image_<?= $i ?>" class="form-control">
                                <?= showImg($sec2['image_'.$i] ?? '') ?>
                            </div>
                            <?php endfor; ?>
                            
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" id="editor2" class="form-control" rows="4"><?= $sec2['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">Update Part-2</button>
                    </div>
                </div>
            </form>

            <form method="POST" action="activities/update.php" enctype="multipart/form-data" class="mb-5">
                <input type="hidden" name="section_type" value="3">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Part 3: Ex- Outdoor</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label>Heading</label>
                                <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec3['heading'] ?? '') ?>">
                            </div>
                             <?php for($i=1; $i<=3; $i++): ?>
                            <div class="col-md-4 mb-3">
                                <label>Image <?= $i ?></label>
                                <input type="file" name="image_<?= $i ?>" class="form-control">
                                <?= showImg($sec3['image_'.$i] ?? '') ?>
                            </div>
                            <?php endfor; ?>

                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" id="editor3" class="form-control" rows="4"><?= $sec3['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Update Part-3</button>
                    </div>
                </div>
            </form>

            <form method="POST" action="activities/update.php" enctype="multipart/form-data" class="mb-5">
                <input type="hidden" name="section_type" value="4">
                <div class="card">
                    <div class="card-header bg-info text-white">Part 4: Ex- Yoga Center</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Heading</label>
                                <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($sec4['heading'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Sub Heading</label>
                                <input type="text" name="sub_heading" class="form-control" value="<?= htmlspecialchars($sec4['sub_heading'] ?? '') ?>">
                            </div>
                             <?php for($i=1; $i<=4; $i++): ?>
                            <div class="col-md-3 mb-3">
                                <label>Image <?= $i ?></label>
                                <input type="file" name="image_<?= $i ?>" class="form-control">
                                <?= showImg($sec4['image_'.$i] ?? '') ?>
                            </div>
                            <?php endfor; ?>

                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" id="editor4" class="form-control" rows="4"><?= $sec4['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info text-white">Update Part-4</button>
                    </div>
                </div>
            </form>

        </main>
    </div>
</div>

<script>
    // Initialize all 4 editors
    CKEDITOR.replace('editor1');
    CKEDITOR.replace('editor2');
    CKEDITOR.replace('editor3');
    CKEDITOR.replace('editor4');
</script>
<?php include 'includes/script.php'; ?>
</body>
</html>