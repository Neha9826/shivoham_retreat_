<?php
// Ensure DB connection is present
if (!isset($conn)) {
    include 'admin/db.php';
}

// Helper function to clean editor content (if not already declared)
if (!function_exists('clean_editor_content')) {
    function clean_editor_content($content) {
        if (empty($content)) return '';
        $content = html_entity_decode($content);
        $content = str_replace(array("\r", "\n"), '', $content);
        $content = str_replace(array('\r', '\n'), '', $content);
        $content = strip_tags($content, '<b><strong><em><i><br><span>');
        return trim($content);
    }
}

// Fetch Data from about_info_2
// We use ASC order so the first item you added gets the first icon (Cutlery), etc.
$query = mysqli_query($conn, "SELECT * FROM about_info_2 ORDER BY id ASC");

// Define the specific icons for this section
$serviceIcons = [
    'fa-cutlery',       // Item 1 (Kitchen)
    'fa-paw',           // Item 2 (Gaushala)
    'fa-asterisk',      // Item 3 (Garden)
    'fa-heart-o',       // Item 4 (Balconies)
    'fa-child',         // Item 5 (Yoga)
    'fa-hand-o-right'   // Item 6 (Parking)
];
?>

<div class="uk-container uk-container-expand">

    <div data-uk-grid class="uk-position-relative uk-position-z-index">

        <div class="uk-width-expand">
            <ul class="uk-child-width-1-6@xl uk-child-width-1-6@l uk-child-width-1-3@m uk-child-width-1-2@s data-uk-grid fac-list" data-uk-grid>
                
                <?php 
                $counter = 0;
                if (mysqli_num_rows($query) > 0):
                    while ($row = mysqli_fetch_assoc($query)):
                        // Get icon based on order (loops back to start if you add more than 6 items)
                        $iconClass = $serviceIcons[$counter % count($serviceIcons)];
                        
                        // Clean the description
                        $cleanDesc = clean_editor_content($row['info_description']);
                ?>
                    <li class="uk-text-center"><!-- Service item --> 
                        <i class="fa <?php echo $iconClass; ?> fa-2x impx-text-white"></i>
                        
                        <h6 class="uk-margin-remove-bottom uk-margin-small-top impx-text-white">
                            <?php echo htmlspecialchars($row['info_title']); ?>
                        </h6>
                        
                        <p class="uk-margin-remove-bottom uk-margin-small-top impx-text-light">
                            <?php echo $cleanDesc; ?>
                        </p>
                    </li>
                <?php 
                    $counter++;
                    endwhile;
                endif;
                ?>

            </ul>

        </div>
    
    </div>

</div>