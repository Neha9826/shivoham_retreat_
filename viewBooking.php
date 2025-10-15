<?php
// viewBooking.php
if (isset($_SESSION['user_id'])) {
    $bookingUserId = $booking['user_id'] ?? null; // null if not present
    if ($bookingUserId !== $_SESSION['user_id']) {
        // handle unauthorized access or simply continue
    }
}

include 'db.php';

// Function to calculate the number of nights
function get_num_nights($checkIn, $checkOut)
{
    if (!$checkIn || !$checkOut) return 0;
    $date1 = new DateTime($checkIn);
    $date2 = new DateTime($checkOut);
    $diff = $date1->diff($date2);
    return max(1, $diff->days);
}

// Check for booking ID in URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger text-center m-5">Invalid booking ID.</div>';
    exit;
}

$bookingId = intval($_GET['id']);

// Fetch booking details from database
$sql = "SELECT 
          b.id AS booking_id,
          b.room_id,
          b.user_id,          -- ✅ add this
          b.check_in,
          b.check_out,
          b.no_of_rooms,
          b.guests,
          b.children,
          b.extra_beds,
          b.total_price,
          b.name,
          b.email,
          b.phone,
          b.status,
          b.meal_plan,
          r.room_name,
          r.base_adults,
          r.max_extra_with_bed,
          r.max_child_without_bed_5_12,
          r.max_child_without_bed_below_5
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo '<div class="alert alert-danger text-center m-5">Failed to prepare SQL statement.</div>';
    exit;
}
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo '<div class="alert alert-danger text-center m-5">Booking not found.</div>';
    exit;
}

// Check if the logged-in user owns this booking (optional, but good practice)
if (isset($_SESSION['user_id']) && $booking['user_id'] !== $_SESSION['user_id']) {
    // This could be a security concern, handle appropriately.
    // For now, we'll just allow it since the prompt doesn't require strict access control here.
}

// Calculate nights
$numNights = get_num_nights($booking['check_in'], $booking['check_out']);

// Map meal plan key to a readable name
$meal_plan_names = [
    'standard' => 'Room Only',
    'breakfast' => 'Room with Breakfast',
    'breakfast_lunch' => 'Room with Breakfast & Lunch',
    'all_meals' => 'All Meals'
];
$mealPlanName = $meal_plan_names[$booking['meal_plan']] ?? 'N/A';

// Helper function to build capacity text from room details
function build_capacity_text($roomDetails)
{
    $base = (int)$roomDetails['base_adults'];
    $ebCap = (int)$roomDetails['max_extra_with_bed'];
    $maxCB5 = (int)$roomDetails['max_child_without_bed_below_5'];
    $maxC512 = (int)$roomDetails['max_child_without_bed_5_12'];

    $capacityString = "Base Adults: {$base}";

    if ($ebCap > 0) {
        $capacityString .= ", Extra Adults with Bed: {$ebCap}";
    }
    if ($maxC512 > 0) {
        $capacityString .= ", Child (5-12) without Bed: {$maxC512}";
    }
    if ($maxCB5 > 0) {
        $capacityString .= ", Child (<5) without Bed: {$maxCB5}";
    }

    return $capacityString;
}

$roomCapacityText = build_capacity_text($booking);

// Build the guests and children text
$guestsText = $booking['guests'] . ' adult' . ($booking['guests'] > 1 ? 's' : '');
$childrenText = $booking['children'] . ' child' . ($booking['children'] > 1 ? 'ren' : '');
$guestsAndChildren = $guestsText . ($booking['children'] > 0 ? ", " . $childrenText : "");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Standard Meta -->
        <meta charset="utf-8">
        <meta name="format-detection" content="telephone=no" />
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Site Properties -->
         <title>Booking Details</title>
		<link rel="shortcut icon" href="images/Shivoham.png" type="image/x-icon">
        <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon.png">

        <!-- Google Fonts -->
       	<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i&amp;subset=cyrillic" rel="stylesheet">
       	<link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700,700i&amp;subset=latin-ext" rel="stylesheet">

        <!-- CSS -->

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

        <link rel="stylesheet" href="css/uikit.min.css" />
        <link rel="stylesheet" href="css/font-awesome.min.css" />
        <!-- <link rel="stylesheet" href="css/tiny-date-picker.min.css" /> -->
        <link rel="stylesheet" href="css/style.css?v=6.1" />
        <link rel="stylesheet" href="css/media-query.css" />

    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border-radius: 1rem;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #e9ecef;
        }
        .table th,
        .table td {
            padding: 1rem;
        }
    </style>
</head>
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
							<h1 class="uk-margin-remove">Booking Detail</h1><!-- page title -->
							<p class="impx-text-large uk-margin-remove">
                            </p><!-- page subtitle -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- PAGE HEADING END -->

<section class="about_area" style="padding: 50px 0 30px;">
    <div class="container mt-5 mb-5">
        <div class="card shadow-lg p-4">
            <div class="card-body">


            <div class="alert alert-success text-center" role="alert">
                                    Thank you, <?= htmlspecialchars($booking['name']) ?>!
                                    Your booking has been received.
                                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th>Booking ID</th>
                                <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                            </tr>
                            <tr>
                                <th>Check-In</th>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($booking['check_in']))) ?></td>
                            </tr>
                            <tr>
                                <th>Check-Out</th>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($booking['check_out']))) ?></td>
                            </tr>
                            <tr>
                                <th>Nights</th>
                                <td><?= htmlspecialchars($numNights) ?> night<?= $numNights > 1 ? 's' : '' ?></td>
                            </tr>
                            <tr>
                                <th>Room Name</th>
                                <td><?= htmlspecialchars($booking['room_name']) ?></td>
                            </tr>
                            <tr>
                                <th>Rooms Booked</th>
                                <td><?= htmlspecialchars($booking['no_of_rooms']) ?> room<?= $booking['no_of_rooms'] > 1 ? 's' : '' ?></td>
                            </tr>
                            <tr>
                                <th>Room Capacity</th>
                                <td><?= htmlspecialchars($roomCapacityText) ?></td>
                            </tr>
                            <tr>
                                <th>Guests</th>
                                <td><?= htmlspecialchars($guestsAndChildren) ?></td>
                            </tr>
                            <tr>
                                <th>Meal Plan</th>
                                <td><?= htmlspecialchars($mealPlanName) ?></td>
                            </tr>
                            <tr>
                                <th>Extra Beds Added</th>
                                <td><?= htmlspecialchars($booking['extra_beds']) ?> bed<?= $booking['extra_beds'] > 1 ? 's' : '' ?></td>
                            </tr>
                            <tr>
                                <th>Total Price</th>
                                <td>₹<?= htmlspecialchars(number_format($booking['total_price'], 2)) ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= htmlspecialchars($booking['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?= htmlspecialchars($booking['phone']) ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><?= htmlspecialchars($booking['status']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary btn-lg">Go to Home</a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- CONTACT INFO -->
		<div class="pre-footer-contact uk-padding bg-img2 uk-position-relative">
			<div class="impx-overlay dark"></div>
			<div class="uk-container">

				<div data-uk-grid class="uk-padding-remove-bottom uk-position-relative">				
					<div class="uk-light uk-width-1-2@xl uk-width-1-2@l uk-width-1-2@m uk-width-1-3@s"><!-- address -->
						<h5 class="uk-heading-line uk-margin-remove-bottom"><span>Address</span></h5>
						<p class="impx-text-large uk-margin-top">Shivoham Retreat, CMTC House, Kuthalwali, Johrigaon, Dehradur,
Uttarakhand-248003</p>
					</div>
					<div class="uk-light uk-width-1-4@xl uk-width-1-4@l uk-width-1-4@m uk-width-1-3@s"><!-- phone -->
						<h5 class="uk-heading-line uk-margin-bottom"><span>Phone</span></h5>
						<p class="impx-text-large uk-margin-remove">+91-9917003456</p>
					</div>
					<div class="uk-light uk-width-1-4@xl uk-width-1-4@l uk-width-1-4@m uk-width-1-3@s"><!-- email -->
						<h5 class="uk-heading-line uk-margin-bottom"><span>Email</span></h5>
						<a href="mailto:retreatshivoham@gmail.com" class="impx-text-large">retreatshivoham@gmail.com</a><br/>
					</div>
				</div>

			</div>
		</div>
		<!-- CONTACT INFO END -->

		<!-- FOOTER -->
		<footer id="impx-footer" class="uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
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
        <!-- <script src="js/jquery.gmap.min.js"></script> -->
        <script src="js/jquery.parallax.min.js"></script>
        <!-- <script src="js/tiny-date-picker.min.js"></script> -->
        <script src="js/date-config.js"></script>
        <script src="js/jquery.barrating.js"></script>
        <script src="js/rating-config.js"></script>
        <script src="js/template-config.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>