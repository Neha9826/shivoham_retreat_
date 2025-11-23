<?php
// Ensure DB connection is present
if (!isset($conn)) {
    include 'admin/db.php';
}

// --- Helper Function to Clean Editor Content ---
// This removes the automatic <p> tags, newlines, and literal \r\n strings
function clean_editor_content($content) {
    if (empty($content)) return '';

    // 1. Decode HTML entities (fixes things like &nbsp; or &amp;)
    $content = html_entity_decode($content);
    
    // 2. Remove actual newlines and carriage returns (source code breaks)
    $content = str_replace(array("\r", "\n"), '', $content);
    
    // 3. Remove literal "\r\n" strings if they were escaped in DB (common with some SQL saves)
    $content = str_replace(array('\r', '\n'), '', $content);
    
    // 4. Strip block-level tags like <p> to prevent double padding.
    // We keep <br> so if you intentionally hit "Shift+Enter" in editor, it stays.
    $content = strip_tags($content, '<b><strong><em><i><br><span>');
    
    return trim($content);
}

// 1. Fetch Main Section Data (about_1)
$mainQuery = mysqli_query($conn, "SELECT * FROM about_1 ORDER BY id DESC LIMIT 1");
$mainData  = mysqli_fetch_assoc($mainQuery);

// Fetch Data
$mainHeading    = $mainData['main_heading'] ?? 'Why Choose Us?';
$mainSubHeading = $mainData['main_sub_heading'] ?? 'About Shivoham Retreat';
$mainDescRaw    = $mainData['main_description'] ?? 'Welcome to Shivoham Retreat...';

// --- FIX: Apply the cleaning function to Main Description ---
$mainDesc = clean_editor_content($mainDescRaw);


// 2. Fetch Info Cards Data (about_info)
$infoQuery = mysqli_query($conn, "SELECT * FROM about_info");

// Styles array
$cardStyles = [
    ['color' => 'color1', 'icon' => 'users'],
    ['color' => 'color2', 'icon' => 'bell'],
    ['color' => 'color3', 'icon' => 'star'],
    ['color' => 'color4', 'icon' => 'heart'],
    ['color' => 'color5', 'icon' => 'image'],
    ['color' => 'color6', 'icon' => 'happy'],
];
?>

<div class="uk-container">

    <div class="uk-flex-left" data-uk-grid>
        <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-1-1@m">
            <div class="impx-intro uk-text-left"><!-- Intro -->
                
                <!-- Main Heading -->
                <h2 class="uk-margin-remove-bottom"><?php echo htmlspecialchars($mainHeading); ?></h2>
                
                <!-- Main Sub Heading -->
                <p class="uk-margin-remove-top uk-text-uppercase uk-text-muted impx-text-large">
                    <?php echo htmlspecialchars($mainSubHeading); ?>
                </p>
                
                <!-- Main Description -->
                <div class="impx-text-large uk-margin-remove impx-text-aqua">
                    <?php echo $mainDesc; ?>
                </div>

            </div><!-- Intro End -->
        </div>
    </div>

    <div class="uk-width-3-4@xl uk-width-3-4@l uk-width-1-1@m uk-width-1-1@s uk-position-relative uk-margin-bottom" data-uk-grid>
        <!-- Reason Items -->
        <ul class="uk-child-width-1-3@xl uk-child-width-1-3@l uk-child-width-1-3@m uk-child-width-1-2@s impx-features-hl uk-grid-medium uk-grid-match" data-uk-grid>
            
            <?php 
            $counter = 0;
            if(mysqli_num_rows($infoQuery) > 0):
                while($info = mysqli_fetch_assoc($infoQuery)): 
                    // Calculate style index
                    $styleIndex = $counter % count($cardStyles);
                    $currentStyle = $cardStyles[$styleIndex];
                    
                    // Clean the info description
                    $cleanDescription = clean_editor_content($info['info_description']);
            ?>
                <li>
                    <!-- Reason Item -->
                    <div class="uk-card uk-card-default uk-card-body uk-box-shadow-medium <?php echo $currentStyle['color']; ?> impx-feature-item uk-position-relative">
                        <h6 class="uk-margin-remove-top uk-margin-bottom impx-text-white">
                            <?php echo htmlspecialchars($info['info_title']); ?>
                        </h6>
                        
                        <p class="uk-margin-remove impx-text-lighter">
                            <?php echo $cleanDescription; ?>
                        </p>
                        
                        <span data-uk-icon="icon: <?php echo $currentStyle['icon']; ?>; ratio: 8" class="feature-icon"></span>
                    </div>
                </li>
            <?php 
                $counter++;
                endwhile; 
            endif;
            ?>

        </ul>
        <!-- Reason Items End -->
    </div>

</div>