<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Save URL params into session (so included files can read them)
    $_SESSION['check_in'] = $_GET['check_in'] ?? '';
    $_SESSION['check_out'] = $_GET['check_out'] ?? '';
    $_SESSION['no_of_rooms'] = $_GET['no_of_rooms'] ?? 1;
    $_SESSION['guests'] = $_GET['guests'] ?? 2;
    $_SESSION['children'] = $_GET['children'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>   
	<!-- Standard Meta -->
	<meta charset="utf-8">
	<meta name="format-detection" content="telephone=no" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">


	<!-- Site Properties -->
	<title>Shivoham Yoga Retreat</title>
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
		<!-- HEADER -->
		<header id="impx-header">
			<div>
				<div class="impx-menu-wrapper style2 hp2" data-uk-sticky="top: .impx-slide-container; animation: uk-animation-slide-top">

					<!-- Mobile Nav Start -->
					<?php include 'includes/moblileNav.php'; ?>
		            <!-- Mobile Nav End -->

		            <!-- Top Header -->
					<?php include 'includes/topHeader.php'; ?>
					<!-- Top Header End -->

					<div class="uk-container uk-container-expand">
						<div data-uk-grid>
							<!-- Header Logo -->
							<div class="uk-width-auto">
								<div class="impx-logo">
									<a href="index.php"><img src="images/Shivoham.png" class="logo" alt="Logo"></a>
								</div>
							</div>
							<!-- Header Logo End-->
							
							<!-- Header Navigation -->
							<?php include 'includes/navbar.php'; ?>
							<!-- Header Navigation End -->

							<!-- Promo Ribbon -->
							<!-- <div class="uk-width-auto uk-position-relative">
								<div class="ribbon">
								  <i><span><s></s>30% <span>Off!</span><s></s></span></i>
								</div>
							</div> -->
							<!-- Promo Ribbon End -->

						</div>
					</div>
				</div>
			</div>

		</header><!-- HEADER END -->

		<!-- SLIDESHOW -->
		<?php include 'includes/banner_slider.php'; ?>
		<!-- SLIDESHOW END -->

		<!-- BOOKING FORM -->
		 <div class="slide-form-section hp2">
			<?php include 'includes/bookingForm.php'; ?>
		</div>
		<!-- BOOKING FORM END -->

		

		<!-- WHY CHOOSE US? -->
		 <div id="about" class="uk-padding uk-padding-remove-horizontal uk-position-relative uk-position-relative bg-img1">
			<?php include 'includes/about.php'; ?>
		</div>
		<!-- WHY CHOOSE US? END -->

		<!-- SERVICES -->
		 <div id="activities"  class="impx-content style3 hp2 bg-color-aqua uk-padding uk-padding-remove-horizontal pattern-1">
			<?php include 'includes/services.php'; ?>
		</div>
		
		<!-- SERVICES END -->

		<!-- ROOMS LIS Images -->
		<?php include 'includes/roomImages.php'; ?>
		<!-- ROOMS LIST END -->

		<!-- ROOMS LIST & PRICING PLANS -->
		 <div id="rooms" class="uk-padding">
			<?php include 'includes/roomList.php'; ?>
		</div>
		<!-- ROOMS LIST & PRICING PLANS END -->

		<!-- TESTIMONIALS CAROUSEL -->
		<?php include 'includes/testimonial.php'; ?>
		<!-- TESTIMONIALS CAROUSEL END -->

		<!-- CONTACT SECTION -->
		<?php include 'includes/contact.php'; ?>
		<!-- CONTACT SECTION END -->

		<!-- FOOTER -->
		<footer id="contact impx-footer" class="uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
			<?php include 'includes/footer.php'; ?>

			<!-- Scroll to Top -->
<a href="#top" class="to-top fa fa-long-arrow-up" data-uk-scroll></a>
<!-- Scroll to Top End -->
		</footer>
		<!-- FOOTER END -->

    	<!-- Javascript -->
    	<script src="js/jquery.js"></script>
        <script src="js/uikit.min.js"></script>
        <script src="js/uikit-icons.min.js"></script>
        <!-- <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyBGb3xrNtz335X4G2KfoOXb-XuIyHAzlVo"></script> -->
        <script src="js/jquery.gmap.min.js"></script>
        <!-- <script src="js/tiny-date-picker.min.js"></script> -->
        <script src="js/jquery.parallax.min.js"></script>
        <script src="js/date-config.js"></script>
        <script src="js/template-config.js"></script>
    </body>


</html>