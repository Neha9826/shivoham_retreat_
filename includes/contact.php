<?php
// Connect to database (Handle different path depths)
if(file_exists('admin/db.php')){
    include_once 'admin/db.php';
} elseif (file_exists('../admin/db.php')){
    include_once '../admin/db.php'; // In case this is included from a subfolder
} elseif (file_exists('db.php')){
    include_once 'db.php';
}

// Fetch Contact Info (Assuming ID=1 is the main record)
$contact_sql = "SELECT * FROM contact_info WHERE id=1";
$contact_res = mysqli_query($conn, $contact_sql);
$contact_data = mysqli_fetch_assoc($contact_res);

// Set Default Fallbacks if DB is empty
$address = $contact_data['address'] ?? 'Shivoham Retreat, Dehradun, Uttarakhand';
$phone   = $contact_data['phone'] ?? '+91-9917003456';
$email   = $contact_data['email'] ?? 'retreatshivoham@gmail.com';
// Default Google Map Embed URL
$map_embed = $contact_data['map_embed'] ?? 'https://www.google.com/maps/embed?pb=...'; 
?>

<div id="contact" class="impx-contact">
    
    <div class="impx-overlay aqua-darkest uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
        <div class="uk-container">

            <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-1-1@s uk-margin-medium-bottom">
                <h4 class="impx-text-white">Our Location click here <i class="fa fa-hand-o-right impx-text-white"></i> <a style="color:yellow" href="https://maps.app.goo.gl/3iaxH1aeYfHzacPh8" target="_blank">Shivoham Retreat</a></h4>
                <p class="impx-text-large impx-text-light">Shivoham Retreat is a scenic drive from Dehradun city centre and Jolly Grant Airport. Winding roads through pine groves and clear streams lead you to our door, where tranquillity, hospitality, and Himalayan beauty await.</p>
                </div>

            <div data-uk-grid class="uk-padding-remove-bottom"><div class="uk-light uk-width-1-2@xl uk-width-1-2@l uk-width-1-2@m uk-width-1-1@s">
                    <h5 class="uk-heading-line uk-margin-remove-bottom impx-text-white"><span>Address</span></h5>
                    <p class="impx-text-large uk-margin-top impx-text-light"><?= nl2br(htmlspecialchars($address)) ?></p>
                </div><div class="uk-light uk-width-1-4@xl uk-width-1-4@l uk-width-1-4@m uk-width-1-1@s"><h5 class="uk-heading-line uk-margin-bottom impx-text-white"><span>Phone</span></h5>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phone) ?>"><p class="impx-text-large uk-margin-remove impx-text-light"><?= htmlspecialchars($phone) ?></p></a>
                </div><div class="uk-light uk-width-1-4@xl uk-width-1-4@l uk-width-1-4@m uk-width-1-1@s"><h5 class="uk-heading-line uk-margin-bottom impx-text-white"><span>Email</span></h5>
                    <a href="mailto:<?= htmlspecialchars($email) ?>" class="impx-text-large impx-text-light"><?= htmlspecialchars($email) ?></a>
                </div></div>

        </div>
    </div>
</div>

<div id="impx-gmap">
    <?= $map_embed ?>  
</div>