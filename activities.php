<?php
// Connect to database
if(file_exists('admin/db.php')){
    include 'admin/db.php';
} elseif (file_exists('db.php')){
    include 'db.php';
} else {
    die("Database file not found");
}

// FETCH ALL SECTIONS (Assuming ID=1 for all)
$sec1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_1 WHERE id=1"));
$sec2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_2 WHERE id=1"));
$sec3 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_3 WHERE id=1"));
$sec4 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM activity_section_4 WHERE id=1"));

// Helper to get image path (Dynamic or Fallback)
function getImg($dbPath, $fallback) {
    if (!empty($dbPath)) {
        return 'admin/' . $dbPath;
    }
    return $fallback;
}
?>
<!DOCTYPE html>
<html>

<head>
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
            <div class="impx-menu-wrapper style2 hp2" data-uk-sticky="top: 0; animation: uk-animation-slide-top">

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
    <div class="impx-page-heading uk-position-relative act" style="margin-top: -142px; height: 180px; padding-top: 200px;">
        <div class="impx-overlay dark"></div>
        <div class="uk-container">
            <div class="uk-width-1-1">
                <div class="uk-flex uk-flex-left">
                    <div class="uk-light uk-position-relative uk-text-left page-title" style="margin-top: -150px;">
                        <h1 class="uk-margin-remove">Activities</h1><p class="impx-text-large uk-margin-remove">Our Hotel & Resort</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-padding">
        <div class="uk-container">
            <div data-uk-grid>
                
                <div class="uk-width-3-5@xl uk-width-3-5@l uk-width-1-1@m">
                    <div class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-2@s uk-grid-medium" data-uk-grid>
                        <div>
                            <div class="uk-position-relative">
                                <img src="<?= getImg($sec1['image_1'], 'images/activities/family-fun-3.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-wide"></div>
                            </div>
                        </div>
                        <div>
                            <div class="uk-position-relative">
                                <img src="<?= getImg($sec1['image_2'], 'images/activities/family-fun-1.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-wide"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-width-2-5@xl uk-width-2-5@l uk-width-1-1@m uk-width-1-1@s">
                    <div class="uk-margin-medium-top uk-position-relative uk-position-z-index impx-margin-top-remove impx-margin-top-remove-m">
                        <h2 class="uk-margin-remove"><?= htmlspecialchars($sec1['heading']) ?></h2>
                        <span class="uk-label small color1 impx-label"><?= htmlspecialchars($sec1['sub_heading']) ?></span>
                        
                        <div class="impx-text-large">
                            <?= $sec1['description'] ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>                      
    <div class="color4 uk-padding">
        <div class="uk-container">
            <div data-uk-grid>
                
                <div class="uk-width-2-5@xl uk-width-2-5@l uk-width-1-1@m  uk-width-1-1@s">
                    <div class="uk-margin-medium-top uk-position-relative uk-position-z-index uk-text-right  impx-margin-top-remove impx-margin-top-remove-m">
                        <h2 class="uk-margin-remove impx-text-white"><?= htmlspecialchars($sec2['heading']) ?></h2>
                        <span class="uk-label small impx-text-light bg-none impx-label"><?= htmlspecialchars($sec2['sub_heading']) ?></span>
                        
                        <div class="impx-text-light">
                             <?= $sec2['description'] ?>
                        </div>
                    </div>
                </div>

                <div class="uk-width-3-5@xl uk-width-3-5@l uk-width-1-1@m  uk-width-1-1@s">
                    <div class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-2@s uk-grid-medium" data-uk-grid>
                        <div>
                            <div class="uk-position-relative uk-margin-medium-bottom">
                                <img src="<?= getImg($sec2['image_1'], 'images/activities/kids-play-1.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                            <div class="uk-position-relative">
                                <img src="<?= getImg($sec2['image_2'], 'images/activities/kids-play-2.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                        </div>
                        <div>
                            <div class="uk-position-relative uk-margin-medium-bottom">
                                <img src="<?= getImg($sec2['image_3'], 'images/activities/kids-play-3.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                            <div class="uk-position-relative">
                                <img src="<?= getImg($sec2['image_4'], 'images/activities/kids-play-4.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>                      
    <div class="uk-padding">
        <div class="uk-container">

            <div class="uk-flex uk-flex-center" data-uk-grid>
                <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-1-1@m  uk-width-1-1@s uk-text-center">
                    <h2 class="uk-margin-remove"><?= htmlspecialchars($sec3['heading']) ?></h2>
                    <div class="uk-margin-small">
                         <?= $sec3['description'] ?>
                    </div>
                </div>
            </div>

            <div class="uk-child-width-1-3@xl uk-child-width-1-3@l uk-child-width-1-3@m uk-child-width-1-3@s uk-grid-medium" data-uk-grid>
                <div>
                    <div class="uk-position-relative uk-margin-bottom impx-margin-bottom-small">
                        <img src="<?= getImg($sec3['image_1'], 'images/activities/outdoor-img-3.jpg') ?>" alt="" class="">
                        <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                    </div>
                </div>
                <div>
                    <div class="uk-position-relative uk-margin-bottom impx-margin-bottom-small">
                        <img src="<?= getImg($sec3['image_2'], 'images/activities/outdoor-img-1.jpg') ?>" alt="" class="">
                        <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                    </div>
                </div>
                <div>
                    <div class="uk-position-relative">
                        <img src="<?= getImg($sec3['image_3'], 'images/activities/outdoor-img-4.jpg') ?>" alt="" class="">
                        <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>                      
    <div class="color6 uk-padding">
        <div class="uk-container">

            <div data-uk-grid>
                
                <div class="uk-width-3-5@xl uk-width-3-5@l uk-width-1-1@m uk-width-1-1@s uk-margin-small-bottom">
                    <div class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-2@s uk-grid-medium" data-uk-grid>
                        <div>
                            <div class="uk-position-relative uk-margin-medium-bottom">
                                <img src="<?= getImg($sec4['image_1'], 'images/activities/gym-img-1.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                            <div class="uk-position-relative impx-margin-bottom-small">
                                <img src="<?= getImg($sec4['image_2'], 'images/activities/gym-img-2.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                        </div>
                        <div>
                            <div class="uk-position-relative uk-margin-medium-bottom">
                                <img src="<?= getImg($sec4['image_3'], 'images/activities/gym-img-3.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                            <div class="uk-position-relative">
                                <img src="<?= getImg($sec4['image_4'], 'images/activities/gym-img-4.jpg') ?>" alt="" class="">
                                <div class="impx-overlay light overlay-squared padding-xxxlarge"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-width-2-5@xl uk-width-2-5@l uk-width-1-1@m">
                    <div class="uk-margin-medium-top uk-position-relative uk-position-z-index impx-margin-top-remove impx-margin-top-remove-m">
                        <h2 class="uk-margin-remove impx-text-white"><?= htmlspecialchars($sec4['heading']) ?></h2>
                        <span class="uk-label small bg-color-white impx-text-gold impx-label"><?= htmlspecialchars($sec4['sub_heading']) ?></span>
                        
                        <div class="impx-text-lighter">
                            <?= $sec4['description'] ?>
                        </div>
                    </div>
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
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyBGb3xrNtz335X4G2KfoOXb-XuIyHAzlVo"></script>
    <script src="js/jquery.gmap.min.js"></script>
    <script src="js/tiny-date-picker.min.js"></script>
    <script src="js/jquery.parallax.min.js"></script>
    <script src="js/date-config.js"></script>
    <script src="js/template-config.js"></script>
</body>

</html>