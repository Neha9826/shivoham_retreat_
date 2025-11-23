<?php
// Ensure DB connection is present
if (!isset($conn)) {
    include 'admin/db.php';
}

// Fetch all slider images
$sliderQuery = mysqli_query($conn, "SELECT * FROM about_slider ORDER BY id DESC");
$allImages = [];
while ($row = mysqli_fetch_assoc($sliderQuery)) {
    $allImages[] = $row;
}

// The design requires 2 images per list item (card).
// We chunk the array into groups of 2.
$imageChunks = array_chunk($allImages, 2);
?>

<div id="gallery" class="uk-padding uk-padding-remove-horizontal uk-position-relative impx-section-rooms style2 bg2 uk-position-relative no-margin uk-background-fixed uk-background-center-center uk-height-1-1">
    <div class="impx-overlay darker"></div>

    <div class="uk-container">
        <div class="uk-margin-bottom uk-flex uk-flex-center uk-position-relative uk-position-z-index" data-uk-grid>
            <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-1-1@m uk-width-1-1@xl">
                <div class="impx-intro uk-text-center"><!-- intro -->
                    <h2 class="uk-margin-remove-bottom impx-text-white">Moments of Peace, Frames of Bliss</h2>
                    <p class="impx-text-large impx-text-lighter uk-margin-small-top uk-margin-bottom"> Experience the divine calm and Himalayan beauty captured in every frame.</p>
                </div><!-- intro end -->
            </div>
        </div>
    </div>

    <div class="uk-container">
        <div class="uk-position-relative uk-visible-toggle uk-margin-medium-bottom">
            <!-- room items -->
            <ul class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-1@s uk-grid-collapse uk-box-shadow-large uk-grid-match" data-uk-grid>
                
                <?php 
                $chunkIndex = 0;
                foreach ($imageChunks as $chunk): 
                    // Logic to alternate classes to match original design pattern:
                    // The original had 2 items with 'media-left' then 2 items with 'media-right'
                    // We swap style every 2 chunks.
                    $isRightStyle = (floor($chunkIndex / 2) % 2 == 1); 
                    $mediaClass = $isRightStyle ? 'uk-card-media-right' : 'uk-card-media-left';
                ?>
                <li><!-- room items pair -->
                    <div class="impx-room-item medium">
                        <div class="uk-card uk-card-default uk-grid-collapse uk-child-width-1-2@s" data-uk-grid>
                            
                            <!-- Image 1 in this pair -->
                            <div class="<?php echo $mediaClass; ?> uk-cover-container uk-position-relative">
                                <canvas width="290" height="290"></canvas>
                                <img src="admin/<?php echo htmlspecialchars($chunk[0]['image']); ?>" alt="Gallery Image" data-uk-cover>
                                <div class="impx-overlay light overlay-squared padding-xwide"></div>
                            </div>

                            <!-- Image 2 in this pair (check if exists) -->
                            <?php if (isset($chunk[1])): ?>
                            <div class="<?php echo $mediaClass; ?> uk-cover-container uk-position-relative">
                                <canvas width="290" height="290"></canvas>
                                <img src="admin/<?php echo htmlspecialchars($chunk[1]['image']); ?>" alt="Gallery Image" data-uk-cover>
                                <div class="impx-overlay light overlay-squared padding-xwide"></div>
                            </div>
                            <?php else: ?>
                                <!-- Placeholder if odd number of images, or leave empty -->
                                <div class="<?php echo $mediaClass; ?> uk-cover-container uk-position-relative uk-background-muted">
                                    <canvas width="290" height="290"></canvas>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </li><!-- room items pair end -->
                <?php 
                    $chunkIndex++;
                endforeach; 
                ?>

            </ul><!-- room items end -->
        </div>

        <div class="uk-flex-center uk-margin-small-bottom" data-uk-grid>
            <div class="uk-text-center uk-position-relative uk-position-z-index"><!-- room button -->
                <a href="rooms.php" class="uk-button uk-button-default impx-button impx-button-outline">Browse Our Rooms <i class="uk-icon-arrow-circle-o-right"></i></a>
            </div><!-- room button end -->
        </div>
    </div>

</div>