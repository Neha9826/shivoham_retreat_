<?php 
// Connect to database (Pointing to admin/db.php)
// Adjust this path if your db.php is located elsewhere
if(file_exists('admin/db.php')){
    include 'admin/db.php';
} elseif (file_exists('db.php')){
    include 'db.php';
} else {
    die("Database file not found");
}

// Fetch images
$q = "SELECT * FROM gallery ORDER BY id DESC";
$gallery_result = mysqli_query($conn, $q);
?>
<!DOCTYPE html>
<html>

<head>
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
            <div class="impx-menu-wrapper style2 hp2" data-uk-sticky="top: .impx-slide-container; animation: uk-animation-slide-top">

                <?php include 'includes/moblileNav.php'; ?>
                
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
    <!-- <div class="impx-page-heading uk-position-relative gallery"> -->
		<div class="impx-page-heading uk-position-relative gallery" style="margin-top: -142px; height: 200px; padding-top: 200px;">
        <div class="impx-overlay dark"></div>
        <div class="uk-container">
            <div class="uk-width-1-1">
                <div class="uk-flex uk-flex-left">
                    <div class="uk-light uk-position-relative uk-text-left page-title">
                        <h1 class="uk-margin-remove">Gallery</h1><p class="impx-text-large uk-margin-remove">Our Story in Pictures</p></div>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-padding uk-padding-remove-horizontal">
        <div class="uk-container">

            <div data-uk-filter="target: .js-filter">
                
                <ul class="uk-subnav uk-subnav-pill uk-flex-center uk-margin-medium-bottom impx-gallery-filter uk-margin-small-top impx-margin-bottom">
                    <li class="uk-active" data-uk-filter-control=".all"><a href="#">All Images</a></li>
                </ul>
                <div class="js-filter uk-child-width-1-4@xl uk-child-width-1-4@l uk-child-width-1-4@m uk-child-width-1-3@s uk-grid-medium uk-margin-small-bottom" data-uk-grid="masonry : true" data-uk-lightbox>
                    
                    <?php 
                    if(mysqli_num_rows($gallery_result) > 0):
                        while($row = mysqli_fetch_assoc($gallery_result)): 
                            // 1. Prepare Image Path (add 'admin/' prefix)
                            $imagePath = 'admin/' . $row['image_path'];
                            
                            // 2. Prepare Description (Clean HTML tags for the data-caption attribute)
                            $cleanDesc = strip_tags($row['description']);
                    ?>
                        <div class="all">
                            <a class="uk-inline-clip uk-transition-toggle" href="<?php echo $imagePath; ?>" data-caption="<?php echo htmlspecialchars($cleanDesc); ?>">
                                <img src="<?php echo $imagePath; ?>" alt="Gallery Image">
                                
                                <div class="uk-transition-fade uk-position-cover uk-overlay uk-overlay-primary">
                                    <div class="uk-light">
                                        <?php echo $row['description']; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php 
                        endwhile; 
                    else:
                    ?>
                        <div class="uk-width-1-1 uk-text-center">
                            <p>No images found in the gallery.</p>
                        </div>
                    <?php endif; ?>

                </div>
                </div>
            
        </div>
    </div>
    <?php include 'includes/contact.php'; ?>
    <footer id="contact impx-footer" class="uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
        <?php include 'includes/footer.php'; ?>

        <a href="#top" class="to-top fa fa-long-arrow-up" data-uk-scroll></a>
        </footer>
    <script src="js/jquery.js"></script>
    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyBGb3xrNtz335X4G2KfoOXb-XuIyHAzlVo"></script>
    <script src="js/jquery.gmap.min.js"></script>
    <script src="js/jquery.parallax.min.js"></script>
    <script src="js/template-config.js"></script>
</body>

</html>