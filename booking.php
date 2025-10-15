<?php
// booking.php
session_start();
include 'db.php';

// 📌 YOU MUST UPDATE THIS PATH WITH YOUR SUBFOLDER NAME
$basePath = ''; // For example: '/my-hotel-project/' or '/hotel/'

// Get parameters from URL, with session as fallback
$roomId      = isset($_GET['room_id']) ? intval(trim($_GET['room_id'])) : 0;
$checkIn     = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
$checkOut    = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';
$noOfRooms   = isset($_GET['no_of_rooms']) ? intval(trim($_GET['no_of_rooms'])) : 1;
$guests      = isset($_GET['guests']) ? intval(trim($_GET['guests'])) : 2;
$children    = isset($_GET['children']) ? intval(trim($_GET['children'])) : 0;
$mealPlanKey = isset($_GET['meal_plan']) ? trim($_GET['meal_plan']) : 'standard';

$roomPrice        = isset($_GET['room_price']) ? (float)trim($_GET['room_price']) : 0;
$extraBedPrice    = isset($_GET['extra_bed_price']) ? (float)trim($_GET['extra_bed_price']) : 0;
$child5_12Price   = isset($_GET['child_5_12_price']) ? (float)trim($_GET['child_5_12_price']) : 0;
$childBelow5Price = isset($_GET['child_below_5_price']) ? (float)trim($_GET['child_below_5_price']) : 0;


// ✅ Pick up price values from URL (sent from room_details.php)
$roomPrice          = (float)($_GET['room_price'] ?? 0);
$extraBedPrice      = (float)($_GET['extra_bed_price'] ?? 0);
$child5to12Price    = (float)($_GET['child_5_12_price'] ?? 0);
$childBelow5Price   = (float)($_GET['child_below_5_price'] ?? 0);

// Calculate number of nights
$checkInDate  = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$numNights = $checkInDate->diff($checkOutDate)->days;
$numNights = $numNights > 0 ? $numNights : 1;

$bookingData = null;

if ($roomId > 0) {
    // Fetch room details
    $roomSql = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($roomSql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $roomDetails = $stmt->get_result()->fetch_assoc();

    if ($roomDetails) {
        // Fetch room images
        $imageSql = "SELECT image_path FROM room_images WHERE room_id = ?";
        $stmt = $conn->prepare($imageSql);
        $stmt->bind_param("i", $roomId);
        $stmt->execute();
        $imageResult = $stmt->get_result();
        $imagePaths = [];
        while ($row = $imageResult->fetch_assoc()) {
            $path = $row['image_path'];
            if (strpos($path, 'admin/') !== 0 && strpos($path, 'assets/') !== 0) {
                $path = 'admin/' . $path;
            }
            $imagePaths[] = $basePath . $path;
        }
        $roomDetails['images'] = $imagePaths;

        // ✅ Assign selected meal plan prices
        $roomDetails['base_price']           = $roomPrice;
        $roomDetails['price_with_extra_bed'] = $extraBedPrice;
        $roomDetails['price_child_5_12']     = $child5to12Price;
        $roomDetails['price_child_below_5']  = $childBelow5Price;
    }

    $bookingData = [
        'roomDetails' => $roomDetails,
        'checkIn'     => $checkIn,
        'checkOut'    => $checkOut,
        'numNights'   => $numNights,
        'noOfRooms'   => $noOfRooms,
        'guests'      => $guests,
        'children'    => $children,
        'mealPlan'    => $mealPlanKey,
    ];
}

$meal_plan_names = [
    'standard'   => 'Room Only',
    'breakfast'  => 'Room with Breakfast',
    'bf_lunch'   => 'Room with Breakfast & Lunch',
    'all_meals'  => 'Room with All Meals'
];

?>
<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <!-- Standard Meta -->
        <meta charset="utf-8">
        <meta name="format-detection" content="telephone=no" />
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Site Properties -->
        <title>Room Detail - Shivoham</title>
		<link rel="shortcut icon" href="images/Shivoham.png" type="image/x-icon">
        <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">

        <!-- Google Fonts -->
       	<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&amp;subset=cyrillic" rel="stylesheet">
       	<link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i&amp;subset=latin-ext" rel="stylesheet">

        <!-- CSS -->
        <link rel="stylesheet" href="css/uikit.min.css" />
        <link rel="stylesheet" href="css/font-awesome.min.css" />
        <link rel="stylesheet" href="css/tiny-date-picker.min.css" />
        <link rel="stylesheet" href="css/style.css?v=4.2" />
        <link rel="stylesheet" href="css/media-query.css" />
        
        <?php include 'includes/head.php'; ?>
    </head>
    
    <style>
     .booking-page-content {
          padding-top: 150px;
      }
    </style>
    <body id="impx-body">
    	
		<!-- HEADER -->
		<header id="impx-header">
			<div>
				<div class="impx-menu-wrapper style2" data-uk-sticky="top: .impx-page-heading; animation: uk-animation-slide-top">

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

		</header>
		<!-- HEADER END -->
         <!-- PAGE HEADING -->
		<div class="impx-page-heading uk-position-relative room-detail">
			<div class="impx-overlay dark"></div>
			<div class="uk-container">
				<div class="uk-width-1-1">
					<div class="uk-flex uk-flex-left">
						<div class="uk-light uk-position-relative uk-text-left page-title">
							<h1 class="uk-margin-remove">Room Detail</h1><!-- page title -->
							<p class="impx-text-large uk-margin-remove">Browse &amp; Choose Your Choice</p><!-- page subtitle -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- PAGE HEADING END -->
    
    <?php if ($bookingData && $bookingData['roomDetails']): ?>
    <div class="booking-page-content container my-5">
        <div class="row">
            <div class="col-lg-8">
                <div style="background-color: #f5f5f5;" class="card p-4 shadow-sm mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= htmlspecialchars($bookingData['roomDetails']['images'][0] ?? 'assets/img/default-room.jpg') ?>" 
                             class="rounded me-3" style="width: 150px; height: 100px; object-fit: cover;"
                             alt="<?= htmlspecialchars($bookingData['roomDetails']['room_name']) ?>">
                        <div>
                            <h4 class="mb-0"><?= htmlspecialchars($bookingData['roomDetails']['room_name']) ?></h4>
                            <p class="text-muted mb-0"><?= htmlspecialchars($meal_plan_names[$mealPlanKey]) ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="mb-2 text-primary">Capacity Details</h6>
                        <p class="mb-1"><strong>Total Room Capacity:</strong> <?= htmlspecialchars($bookingData['roomDetails']['room_capacity']) ?> persons</p>
                        <ul class="list-unstyled mb-0 ms-3">
                            <li><i class="fa fa-person me-2"></i> Base Adults: <?= htmlspecialchars($bookingData['roomDetails']['base_adults']) ?></li>
                            <li><i class="fa fa-bed me-2"></i> Max Extra Bed: <?= htmlspecialchars($bookingData['roomDetails']['max_extra_with_bed']) ?> (₹<?= number_format(htmlspecialchars($bookingData['roomDetails']['price_with_extra_bed']), 2) ?>)</li>
                            <li><i class="fa fa-child-reaching me-2"></i> Child (5-12) without Bed: <?= htmlspecialchars($bookingData['roomDetails']['max_child_without_bed_5_12']) ?> (₹<?= number_format(htmlspecialchars($bookingData['roomDetails']['price_child_5_12']), 2) ?>)</li>
                            <li><i class="fa fa-child me-2"></i> Child (<5) without Bed: <?= htmlspecialchars($bookingData['roomDetails']['max_child_without_bed_below_5']) ?>
                                <?php if ($bookingData['roomDetails']['price_child_below_5'] > 0): ?>
                                     (₹<?= number_format(htmlspecialchars($bookingData['roomDetails']['price_child_below_5']), 2) ?>)
                                <?php else: ?>
                                     (Complimentary)
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                    
                    <div style="background-color: #f5f5f5;" class="row text-center border-top pt-3">
                        <div class="col-4">
                            <strong>Check-in</strong>
                            <p class="mb-0"><span id="display_checkin"><?= date('D, M j, Y', strtotime($checkIn)) ?></span></p>
                            <p class="text-muted mb-0">12:00 PM</p>
                        </div>
                        <div class="col-4">
                            <strong>Check-out</strong>
                            <p class="mb-0"><span id="display_checkout"><?= date('D, M j, Y', strtotime($checkOut)) ?></span></p>
                            <p class="text-muted mb-0">11:00 AM</p>
                        </div>
                        <div class="col-4">
                            <strong>Guests</strong>
                            <p class="mb-0">
                                <span id="guestCount"><?= $guests ?></span> Adults, 
                                <span id="childrenCount"><?= $children ?></span> Children
                            </p>
                            <p class="text-muted mb-0">
                                <span id="numNights"><?= $numNights ?></span> Night<?= $numNights > 1 ? 's' : '' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div style="background-color: #f5f5f5;" class="card p-4 shadow-sm mb-4">
                    <form id="bookingForm" method="POST" action="submitBooking.php">
                        <input type="hidden" name="room_id" value="<?= $roomDetails['id'] ?>">
                        <input type="hidden" name="meal_plan" value="<?= htmlspecialchars($bookingData['mealPlan']) ?>">
                        <!-- ✅ Pass selected prices -->
                        <input type="hidden" name="room_price" value="<?= $roomPrice ?>">
                        <input type="hidden" name="extra_bed_price" value="<?= $extraBedPrice ?>">
                        <input type="hidden" name="child_5_12_price" value="<?= $child5to12Price ?>">
                        <input type="hidden" name="child_below_5_price" value="<?= $childBelow5Price ?>">

                        <h5 class="mb-3">Booking Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Check-in</label>
                                <input type="date" name="check_in" id="check_in" class="form-control" value="<?= htmlspecialchars($bookingData['checkIn']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Check-out</label>
                                <input type="date" name="check_out" id="check_out" class="form-control" value="<?= htmlspecialchars($bookingData['checkOut']) ?>" required>
                            </div>
                            <small class="text-muted">* If the no. of adults exceeds the "Base Adult" limit, the extra bed charges will be applied.</small>
                            <small class="text-muted">* Child above 15 age will be count as an Adult.</small>
                            <div class="col-md-4">
                                <label class="form-label">Rooms</label>
                                <input type="number" name="no_of_rooms" id="no_of_rooms" class="form-control" min="1" max="10" value="<?= htmlspecialchars($bookingData['noOfRooms']) ?>" required>
                                <small class="text-muted">only <?= htmlspecialchars($bookingData['guests']) ?> Base Adults per Room</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Adult/Child with bed</label>
                                <input type="number" name="guests" id="guests" class="form-control" min="1" max="20" value="<?= htmlspecialchars($bookingData['guests']) ?>" required>
                                <small class="text-muted">Base Adult + Extra Adult/Child with bed</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Child without Bed</label>
                                <input type="number" name="children" id="children" class="form-control" min="0" max="10" value="<?= htmlspecialchars($bookingData['children']) ?>" required>
                            </div>
                            
                        </div>
                        
                        <div id="extraBedInfo" class="mt-3"></div>
                
                        <div class="row g-3 mt-1" id="dynamicChildFields">
                        </div>
                
                        <h5 class="mb-3 mt-5">Contact Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button style="background-color: #bd8f03ff; color: #fff;" type="submit" class="btn btn-lg">Complete Booking</button>
                        </div>
                    </form>
                </div>
                </div>

            <div class="col-lg-4">
                <div style="background-color: #f5f5f5; top: 150px;" class="card p-4 shadow-sm sticky-top">
                    <h5 class="mb-3">Price Summary</h5>
                    <div id="price-summary-container">
                        <p class="text-center text-muted">Calculating price...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="booking-page-content container my-5 text-center">
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Room not found!</h4>
            <p>The selected room is not available or the link is invalid. Please go back to the <a href="room_details.php" class="alert-link">room selection page</a> to book your stay.</p>
        </div>
    </div>
    <?php endif; ?>

    
    <?php include 'includes/footer.php'; ?>

    <script src="js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="js/vendor/jquery-1.12.4.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/isotope.pkgd.min.js"></script>
    <script src="js/ajax-form.js"></script>
    <script src="js/waypoints.min.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <script src="js/imagesloaded.pkgd.min.js"></script>
    <script src="js/scrollIt.js"></script>
    <script src="js/jquery.scrollUp.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/nice-select.min.js"></script>
    <script src="js/jquery.slicknav.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/gijgo.min.js"></script>

    <script src="js/contact.js"></script>
    <script src="js/jquery.ajaxchimp.min.js"></script>
    <script src="js/jquery.form.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/mail-script.js"></script>

    <script src="js/main.js"></script>
    <script>
    function handleNumberInput(input) {
        input.addEventListener('input', function() {
            if (this.value.length > 1 && this.value.startsWith('0')) {
                this.value = parseInt(this.value, 10);
            }
        });
        input.addEventListener('blur', function() {
            if (this.value === '' || this.value === null) {
                this.value = 0;
            }
        });
    }

    function updatePriceSummary() {
        const formData = {
            room_id: <?= $roomId ?>,
            check_in: $('#check_in').val(),
            check_out: $('#check_out').val(),
            no_of_rooms: $('#no_of_rooms').val(),
            guests: $('#guests').val(),
            children: $('#children').val(),
            meal_plan: "<?= htmlspecialchars($mealPlanKey) ?>",
            room_price: "<?= $roomPrice ?>",
            extra_bed_price: "<?= $extraBedPrice ?>",
            child_5_12_price: "<?= $child5to12Price ?>",
            child_below_5_price: "<?= $childBelow5Price ?>",
            child_ages: []
        };
        $('select[name="child_ages[]"]').each(function() {
            formData.child_ages.push($(this).val());
        });

        $.ajax({
            url: 'calculateBookingPrice.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#price-summary-container').html(response);
            },
            error: function() {
                $('#price-summary-container').html('<p class="text-danger">Error calculating price. Please select valid dates and guest information.</p>');
            }
        });
    }

    function updateGuestFields() {
        const guests = parseInt($('#guests').val());
        const children = parseInt($('#children').val());
        const roomCapacity = <?= $bookingData['roomDetails']['room_capacity'] ?? 0 ?>;
        const maxExtraWithBed = <?= $bookingData['roomDetails']['max_extra_with_bed'] ?? 0 ?>;
        const baseAdults = <?= $bookingData['roomDetails']['base_adults'] ?? 0 ?>;
        const noOfRooms = parseInt($('#no_of_rooms').val());
        
        let totalAdultsCapacity = baseAdults * noOfRooms;
        let extraAdultsNeeded = Math.max(0, guests - totalAdultsCapacity);

        $('#guestCount').text(guests);
        $('#childrenCount').text(children);

        const childFieldsContainer = $('#dynamicChildFields');
        childFieldsContainer.empty();
        if (children > 0) {
            for (let i = 1; i <= children; i++) {
                childFieldsContainer.append(`
                    <div class="col-md-6 mb-2">
                        <label>Child ${i} Age</label>
                        <select name="child_ages[]" class="form-control" required>
                            <option value="" disabled selected>Select child age</option>
                            <option value="0">Below 5 years</option>
                            <option value="1">5-12 years</option>
                        </select>
                    </div>
                `);
            }
        }

        const extraBedInfoContainer = $('#extraBedInfo');
        extraBedInfoContainer.empty();
        if (extraAdultsNeeded > 0) {
            if (extraAdultsNeeded > (maxExtraWithBed * noOfRooms)) {
                extraBedInfoContainer.html(`
                    <div class="alert alert-danger py-2">
                        <i class="fa fa-exclamation-triangle"></i> The number of extra beds required for adults/child (${extraAdultsNeeded}) exceeds the room's total extra bed capacity (${maxExtraWithBed * noOfRooms}). Please reduce the number of adults or rooms.
                    </div>
                `);
            } else {
                extraBedInfoContainer.html(`
                    <div class="alert alert-info py-2">
                        <i class="fa fa-info-circle"></i> An extra bed is required for ${extraAdultsNeeded} adult/child with bed${extraAdultsNeeded > 1 ? 's' : ''}. Charges will be applied.
                    </div>
                `);
            }
        }
    }

    $(document).ready(function() {
        document.querySelectorAll('input[type="number"]').forEach(handleNumberInput);

        updateGuestFields();
        updatePriceSummary();

        $('#no_of_rooms, #guests, #children, #check_in, #check_out').on('change', function() {
            updateGuestFields();
            updatePriceSummary();
        });
        
        $('#dynamicChildFields').on('change', 'select', function() {
            updatePriceSummary();
        });

        $('#bookingForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'submitBooking.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect_url;
                    } else {
                        alert('Booking failed: ' + response.message); 
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('An error occurred while submitting the booking. Please try again.');
                    console.log(jqXHR, textStatus, errorThrown);
                }
            });
        });
    });

    // --- Clear check availability fields on hard refresh (Ctrl+Shift+R) ---
    window.addEventListener("load", function () {
        if (performance.navigation.type === 1) { // normal refresh (F5)
            document.getElementById("check_in").value = "";
            document.getElementById("check_out").value = "";
            document.querySelector('input[name="no_of_rooms"]').value = 1;
            document.querySelector('input[name="guests"]').value = 2;
            document.querySelector('input[name="num_children"]').value = 0;
        }
    });

    // --- Clear dependent fields if user clears input manually ---
    const checkInInput  = document.getElementById("check_in");
    const checkOutInput = document.getElementById("check_out");

    checkInInput.addEventListener("input", function () {
        if (this.value === "") {
            checkOutInput.value = "";
        }
    });
    checkOutInput.addEventListener("input", function () {
        if (this.value === "") {
            checkInInput.value = "";
        }
    });

    // Note: updateDateDisplays() and updateGuestFields() can be merged later 
    // into one function if needed, but keeping separate since everything works fine.

    function updateDateDisplays() {
        const checkInInput = document.getElementById('check_in');   // correct id
        const checkOutInput = document.getElementById('check_out'); // correct id
        const adultsInput = document.getElementById('guests');      // correct id
        const childrenInput = document.getElementById('children');  // correct id

        if (!checkInInput || !checkOutInput) return;

        const checkInDate = new Date(checkInInput.value);
        const checkOutDate = new Date(checkOutInput.value);

        const options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        if (!isNaN(checkInDate)) {
            document.getElementById('display_checkin').textContent = checkInDate.toLocaleDateString('en-US', options);
        }
        if (!isNaN(checkOutDate)) {
            document.getElementById('display_checkout').textContent = checkOutDate.toLocaleDateString('en-US', options);
        }

        if (!isNaN(checkInDate) && !isNaN(checkOutDate)) {
            const nights = Math.max(1, Math.round((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));
            document.getElementById('numNights').textContent = nights + (nights > 1 ? " Nights" : " Night");
        }

        if (adultsInput) document.getElementById('guestCount').textContent = adultsInput.value;
        if (childrenInput) document.getElementById('childrenCount').textContent = childrenInput.value;
    }

    // Attach listeners
    document.addEventListener('DOMContentLoaded', () => {
        ['check_in', 'check_out', 'guests', 'children'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateDateDisplays);  // triggers instantly
            }
        });

        // Run once on page load
        updateDateDisplays();
    });
    </script>
    </body>
</html>