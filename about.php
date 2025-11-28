<?php
// Connect to database
if(file_exists('admin/db.php')){ include 'admin/db.php'; } 
elseif (file_exists('db.php')){ include 'db.php'; }

// Fetch Data
$sec1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_1 WHERE id=1"));
$sec2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_2 WHERE id=1"));
$sec3 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_3 WHERE id=1"));
$sec4 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM about_us_section_4 WHERE id=1"));
$slide_res = mysqli_query($conn, "SELECT * FROM about_slider ORDER BY id DESC"); // Keep existing slider logic

$sec2_list = json_decode($sec2['content_json'] ?? '[]', true);
$sec3_list = json_decode($sec3['content_json'] ?? '[]', true);
$sec4_list = json_decode($sec4['features_json'] ?? '[]', true);

function getImg($path) {
    return !empty($path) ? 'admin/' . $path : 'images/about-img-2.jpg';
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
	<meta charset="utf-8">
	<meta name="format-detection" content="telephone=no" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Site Properties -->
	<title>Shivoham Yoga Retreat</title>
	<!-- <title>Shivoham Retreat | Cozy Homestay & Yoga Retreat Near Mussoorie Road, Dehradun</title> -->
	<link rel="shortcut icon" href="images/Shivoham.png" type="image/x-icon">
	<link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&amp;subset=cyrillic" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i&amp;subset=latin-ext" rel="stylesheet">

	<!-- CSS -->
	<link rel="stylesheet" href="css/uikit.min.css" />
	<link rel="stylesheet" href="css/font-awesome.min.css" />
	<link rel="stylesheet" href="css/tiny-date-picker.min.css" />
	<link rel="stylesheet" href="css/style.css?v=6.1" />
	<link rel="stylesheet" href="css/media-query.css" />
</head>
<body class="impx-body" id="top">
    
   <header id="impx-header">
    <div>
        <div class="impx-menu-wrapper style2 hp2" data-uk-sticky="top: 0; offset: 0; animation: uk-animation-slide-top">

            <?php include 'includes/moblileNav.php'; ?>
            <?php include 'includes/topHeader.php'; ?>
            <div class="uk-container uk-container-expand">
                <div data-uk-grid>
                    <div class="uk-width-auto">
                        <div class="impx-logo">
                            <a href="index.php"><img src="images/Shivoham.png" class="logo" alt="Logo"></a>
                        </div>
                    </div>
                    <?php include 'includes/navbar.php'; ?>
                </div>
            </div>
        </div>
    </div>
</header>

    <div class="impx-page-heading uk-position-relative about" style="margin-top: -142px; height: 180px; padding-top: 200px;">
        <div class="impx-overlay dark"></div>
        <div class="uk-container">
            <div class="uk-width-1-1">
                <div class="uk-flex uk-flex-left">
                    <div class="uk-light uk-position-relative uk-text-left page-title" style="margin-top: -150px;">
                        <h1 class="uk-margin-remove">About</h1>
                        <p class="impx-text-large uk-margin-remove">The Story About & Who We Are</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="uk-padding impx-page-content uk-padding-remove-horizontal">
        <div class="uk-container">
            <div class="" data-uk-grid>
                <div class="uk-width-3-5@xl uk-width-3-5@l uk-width-1-1@m">
                    
                    <h2 class="uk-margin-remove"><?= htmlspecialchars($sec1['heading']) ?></h2>
                    <p class="uk-text-muted impx-text-large uk-margin-remove"><?= htmlspecialchars($sec1['sub_heading']) ?></p>
                    <div class="impx-text-large"><?= $sec1['description'] ?></div>

                    <div class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-2@s" data-uk-grid>
                        <?php foreach($sec2_list as $item): ?>
                        <div>
                            <h6><?= htmlspecialchars($item['title']) ?></h6>
                            <div class="uk-list uk-list-bullet impx-list">
                                <p><?= nl2br(htmlspecialchars($item['desc'])) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="" data-uk-grid>
                        <div class="uk-width-1-1">
                            <h4 class="uk-heading-line uk-heading-bullet"><span><?= htmlspecialchars($sec3['heading']) ?></span></h4>
                            <div class="uk-child-width-1-3@xl uk-child-width-1-3@l uk-child-width-1-3@m uk-child-width-1-1@s uk-grid-small" data-uk-grid>
                                <?php 
                                $colors = ['color1', 'color2', 'color3', 'color4']; 
                                $c = 0;
                                foreach($sec3_list as $net): 
                                    $bg = $colors[$c % 4]; $c++;
                                ?>
                                <div>
                                    <div class="uk-card uk-card-default <?= $bg ?> uk-light">
                                        <div class="uk-card-body impx-padding-small">
                                            <h6><?= htmlspecialchars($net['title']) ?></h6>
                                            <ul class="uk-list impx-list">
                                                <li><?= nl2br(htmlspecialchars($net['desc'])) ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="uk-width-2-5@xl uk-width-2-5@l uk-width-1-1@m">
                    <div class="uk-card uk-card-default uk-card-small uk-margin-bottom">
                        <div class="uk-card-body uk-padding-remove">
                             <div class="uk-position-relative uk-visible-toggle uk-light" tabindex="-1" uk-slideshow="autoplay: true; ratio: 3:4; animation: pull">
                                <ul class="uk-slideshow-items">
                                    <?php while($slide = mysqli_fetch_assoc($slide_res)): ?>
                                    <li><img src="admin/<?= $slide['image'] ?>" alt="" uk-cover></li>
                                    <?php endwhile; ?>
                                </ul>
                                <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#" uk-slidenav-previous uk-slideshow-item="previous"></a>
                                <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#" uk-slidenav-next uk-slideshow-item="next"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="uk-padding uk-padding-remove-horizontal uk-padding-remove-bottom about-highlight">
        <div class="uk-container">
            <div class="uk-grid">
                <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-2@m uk-margin-medium-top">
                    <img src="<?= getImg($sec4['image_path']) ?>" alt="Why Choose Us" class="">
                </div>

                <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-1-2@m">
                    <h3 class="uk-margin-remove-bottom uk-margin-small-top"><?= htmlspecialchars($sec4['heading']) ?></h3>
                    <p class="impx-text-large uk-margin-medium-bottom uk-margin-small-top"><?= nl2br(htmlspecialchars($sec4['description'])) ?></p>
                    
                    <ul class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-2@s impx-features-hl uk-margin-top uk-grid-match uk-flex-center uk-margin-medium-bottom" data-uk-grid >
                        <?php 
                        $icons = ['group', 'image', 'star-o', 'smile-o']; // Default icons
                        $i = 0;
                        foreach($sec4_list as $feat): 
                            $icon = $icons[$i % 4]; $i++;
                        ?>
                        <li>
                            <div class="uk-grid-medium" data-uk-grid>
                                <div class="uk-width-auto@xl uk-width-auto@l uk-width-auto@l uk-width-auto@s">
                                    <i class="fa fa-<?= $icon ?> fa-2x impx-text-aqua"></i>
                                </div>
                                <div class="uk-width-expand@xl uk-width-expand@l uk-width-expand@l uk-width-auto@s impx-margin-top-remove">
                                    <h5 class="uk-margin-remove"><?= htmlspecialchars($feat['title']) ?></h5>
                                    <p class="uk-margin-small-top"><?= htmlspecialchars($feat['desc']) ?></p>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/contact.php'; ?>
    <footer id="impx-footer" class="uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
        <?php include 'includes/footer.php'; ?>
        <a href="#top" class="to-top fa fa-long-arrow-up" data-uk-scroll></a>
    </footer>

    <script src="js/jquery.js"></script>
    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>
    <script src="js/template-config.js"></script>
</body>
</html>