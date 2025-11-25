<?php
// Connect to database
if(file_exists('admin/db.php')){
    include 'admin/db.php';
} elseif (file_exists('db.php')){
    include 'db.php';
} else {
    die("Database file not found");
}

// You can fetch dynamic contact details here if you have a settings table
// For now, I'll keep the HTML static but ready for PHP injection if needed
$contact_email = "info@shivoham-retreat.com";
$contact_phone = "+91 98765 43210";
$contact_address = "Shivoham Retreat, Dehradun, Uttarakhand, India";
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
    <div class="impx-page-heading uk-position-relative contact" style="margin-top: -142px; height: 180px; padding-top: 200px;">
        <div class="impx-overlay dark"></div>
        <div class="uk-container">
            <div class="uk-width-1-1">
                <div class="uk-flex uk-flex-left">
                    <div class="uk-light uk-position-relative uk-text-left page-title" style="margin-top: -150px;">
                        <h1 class="uk-margin-remove">Contact Us</h1><p class="impx-text-large uk-margin-remove">Get in Touch with Shivoham</p></div>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-padding uk-padding-remove-horizontal">
        <div class="uk-container">

            <div data-uk-grid>
                
                <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-2@m">
                    <h3 class="uk-heading-line uk-heading-bullet"><span>Contact Info</span></h3>
                    <p>We are always ready to help you. There are many ways to contact us. You may drop us a line, give us a call or send an email, choose what suits you most.</p>
                    
                    <ul class="uk-list impx-list">
                        <li>
                            <div class="uk-grid-medium" data-uk-grid>
                                <div class="uk-width-auto">
                                    <i class="fa fa-map-marker impx-text-aqua fa-2x"></i>
                                </div>
                                <div class="uk-width-expand">
                                    <h5 class="uk-margin-remove">Address</h5>
                                    <p class="uk-margin-small-top"><?= $contact_address ?></p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="uk-grid-medium" data-uk-grid>
                                <div class="uk-width-auto">
                                    <i class="fa fa-phone impx-text-aqua fa-2x"></i>
                                </div>
                                <div class="uk-width-expand">
                                    <h5 class="uk-margin-remove">Phone</h5>
                                    <p class="uk-margin-small-top"><?= $contact_phone ?></p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="uk-grid-medium" data-uk-grid>
                                <div class="uk-width-auto">
                                    <i class="fa fa-envelope impx-text-aqua fa-2x"></i>
                                </div>
                                <div class="uk-width-expand">
                                    <h5 class="uk-margin-remove">Email</h5>
                                    <p class="uk-margin-small-top"><a href="mailto:<?= $contact_email ?>" class="uk-link-reset"><?= $contact_email ?></a></p>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <div class="uk-margin-medium-top">
                        <h5 class="uk-heading-bullet">Follow Us</h5>
                        <a href="https://www.facebook.com/Shivohamvalleyretreat" class="uk-icon-button uk-margin-small-right" data-uk-icon="facebook"></a>
                        <a href="https://www.instagram.com/retreatshivoham/?igsh=MWd1MTg1emRqOHE3Ng%3D%3D#" class="uk-icon-button uk-margin-small-right" data-uk-icon="instagram"></a>
                    </div>
                </div>

                <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-1-2@m">
                    <h3 class="uk-heading-line uk-heading-bullet"><span>Send us a Message</span></h3>
                    
                    <div class="uk-card uk-card-default uk-card-body impx-padding-medium">
                        <form action="contact_submit.php" method="POST">
                            <fieldset class="uk-fieldset">

                                <div class="uk-grid-small" data-uk-grid>
                                    <div class="uk-width-1-2@s">
                                        <label class="uk-form-label">Name *</label>
                                        <input class="uk-input" type="text" name="name" placeholder="Your Name" required>
                                    </div>
                                    <div class="uk-width-1-2@s">
                                        <label class="uk-form-label">Email *</label>
                                        <input class="uk-input" type="email" name="email" placeholder="Your Email" required>
                                    </div>
                                </div>

                                <div class="uk-margin">
                                    <label class="uk-form-label">Phone Number</label>
                                    <input class="uk-input" type="text" name="phone" placeholder="Your Phone Number">
                                </div>

                                <div class="uk-margin">
                                    <label class="uk-form-label">Message *</label>
                                    <textarea class="uk-textarea" rows="6" name="message" placeholder="Your Message" required></textarea>
                                </div>

                                <div class="uk-margin uk-text-right">
                                    <button class="uk-button impx-button aqua" type="submit">Send Message</button>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>                      
    <div class="uk-section uk-padding-remove-vertical">
        <div id="impx-gmap" style="height: 400px; width: 100%; background: #eee;">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d110204.74637233267!2d77.9470942475727!3d30.325409792014166!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390929c356c888af%3A0x4c3562c032518799!2sDehradun%2C%20Uttarakhand!5e0!3m2!1sen!2sin!4v1698765432100!5m2!1sen!2sin" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <footer id="impx-footer" class="uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
        <?php include 'includes/footer.php'; ?>
        <a href="#top" class="to-top fa fa-long-arrow-up" data-uk-scroll></a>
    </footer>

    <script src="js/jquery.js"></script>
    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>
    <script src="js/jquery.gmap.min.js"></script>
    <script src="js/jquery.parallax.min.js"></script>
    <script src="js/template-config.js"></script>
</body>

</html>