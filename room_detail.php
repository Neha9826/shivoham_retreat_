<?php
// room_details.php (Goibibo-style Room Selection Page with Advanced Search)
session_start();
include 'db.php';
include 'config.php';
include 'includes/helpers.php';


// 📌 YOU MUST UPDATE THIS PATH WITH YOUR SUBFOLDER NAME
$basePath = ''; // For example: '/my-hotel-project/' or '/hotel/'

// Get parameters from URL, with session as fallback
$room_id     = $_GET['room_id'] ?? null;
$check_in    = $_GET['check_in'] ?? $_SESSION['check_in'] ?? date('Y-m-d');
$check_out   = $_GET['check_out'] ?? $_SESSION['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$no_of_rooms = $_GET['no_of_rooms'] ?? $_SESSION['no_of_rooms'] ?? 1;
$guests      = $_GET['guests'] ?? $_SESSION['guests'] ?? 2;
$children    = $_GET['children'] ?? $_SESSION['num_children'] ?? 0;

// Save to session for consistency across pages
$_SESSION['check_in']     = $check_in;
$_SESSION['check_out']    = $check_out;
$_SESSION['no_of_rooms']  = $no_of_rooms;
$_SESSION['guests']       = $guests;
$_SESSION['num_children'] = $children;

// Helper function to get all room data with pricing and availability
function get_all_room_data($conn, $check_in, $check_out, $guests, $children, $no_of_rooms = 1, $preferred_room_id = null) {
    global $basePath;
    $rooms_data = [];

    // ✅ Multiply room capacity with number of rooms
    $sql = "SELECT r.*,
                   (SELECT GROUP_CONCAT(image_path) FROM room_images WHERE room_id = r.id) AS image_paths,
                   (SELECT GROUP_CONCAT(a.name, '|', a.icon_class)
                     FROM amenities a
                     JOIN room_amenities ra ON ra.amenity_id = a.id
                     WHERE ra.room_id = r.id) AS amenity_data
            FROM rooms r
            WHERE ((r.base_adults + r.max_extra_with_bed + r.max_child_without_bed_5_12) * ?) >= ?
            ORDER BY FIELD(r.id, ?) DESC, r.id DESC";

    $stmt = $conn->prepare($sql);
    $total_guests_for_search = $guests + $children; // Total guests (adults + children)
    $stmt->bind_param("iii", $no_of_rooms, $total_guests_for_search, $preferred_room_id);
    $stmt->execute();
    $roomResult = $stmt->get_result();

    if ($roomResult && $roomResult->num_rows > 0) {
        while ($room = $roomResult->fetch_assoc()) {
            $room_id = (int)$room['id'];

            $total_qty = (int)$room['total_rooms'];
            if ($check_in && $check_out) {
                // ✅ Check if room is already booked for the given dates
                // ---------- NEW SAFE CODE ----------
$booked = 0;

// Use the bookings table (summation of no_of_rooms) and ONLY count confirmed/booked reservations
// This counts the actual number of rooms booked of this type in the overlapping date range.
$conflictSql = "
    SELECT COALESCE(SUM(b.no_of_rooms), 0) AS booked_count
    FROM bookings b
    WHERE b.room_id = ? 
      AND (b.check_in < ? AND b.check_out > ?)
      AND b.status = 'booked'
";
$stmt_conf = $conn->prepare($conflictSql);
if ($stmt_conf) {
    // bind: room_id (int), check_out (string), check_in (string)
    // note: keeping same date-comparison order as earlier code: b.check_in < '$check_out' AND b.check_out > '$check_in'
    $stmt_conf->bind_param("iss", $room_id, $check_out, $check_in);
    $stmt_conf->execute();
    $res_conf = $stmt_conf->get_result();
    $booked = $res_conf ? (int)($res_conf->fetch_assoc()['booked_count'] ?? 0) : 0;
    $stmt_conf->close();
} else {
    // Fallback: if prepare() fails for some reason, use a safe cast of the old approach (defensive)
    $safeSql = "SELECT COUNT(*) AS booked_count FROM bookings WHERE room_id = " . (int)$room_id . " AND (check_in < '" . $conn->real_escape_string($check_out) . "' AND check_out > '" . $conn->real_escape_string($check_in) . "') AND status = 'booked'";
    $r = $conn->query($safeSql);
    $booked = $r ? (int)$r->fetch_assoc()['booked_count'] : 0;
}

$available = $total_qty - $booked;
$room['available_qty'] = max(0, $available);

// Keep the same behavior you had: if not enough rooms available for requested count, skip showing (or change to show Sold Out)
if ($available < $no_of_rooms) {
    continue;
}
// ---------- END REPLACEMENT ----------
            } else {
                $room['available_qty'] = null;
            }

            // ✅ Seasonal price handling
            $dayOfWeek = date('l', strtotime($check_in));
            $priceColumns = [
                'standard' => strtolower($dayOfWeek) . '_standard',
                'breakfast' => strtolower($dayOfWeek) . '_breakfast',
                'breakfast_lunch' => strtolower($dayOfWeek) . '_breakfast_lunch',
                'all_meals' => strtolower($dayOfWeek) . '_all_meals'
            ];
            $sql_prices = "SELECT " . implode(', ', $priceColumns) . " 
                            FROM room_seasonal_prices
                            WHERE room_id = ? AND ? BETWEEN start_date AND end_date
                            LIMIT 1";
            $stmt_prices = $conn->prepare($sql_prices);
            $stmt_prices->bind_param("is", $room_id, $check_in);
            $stmt_prices->execute();
            $seasonal_prices = $stmt_prices->get_result()->fetch_assoc();

            $room['meal_prices'] = [
                'standard' => $seasonal_prices[$priceColumns['standard']] ?? $room['standard_price'],
                'breakfast' => $seasonal_prices[$priceColumns['breakfast']] ?? $room['price_with_breakfast'],
                'breakfast_lunch' => $seasonal_prices[$priceColumns['breakfast_lunch']] ?? $room['price_with_breakfast_lunch'],
                'all_meals' => $seasonal_prices[$priceColumns['all_meals']] ?? $room['price_with_all_meals']
            ];
            
            // ✅ Extra bed and child prices
            $room['extra_bed_prices'] = [
                'standard' => $room['price_with_extra_bed_standard'],
                'breakfast' => $room['price_with_extra_bed_bf'],
                'breakfast_lunch' => $room['price_with_extra_bed_bf_lunch'],
                'all_meals' => $room['price_with_extra_bed_all_meals']
            ];
            $room['child_5_12_prices'] = [
                'standard' => $room['price_child_5_12_standard'],
                'breakfast' => $room['price_child_5_12_bf'],
                'breakfast_lunch' => $room['price_child_5_12_bf_lunch'],
                'all_meals' => $room['price_child_5_12_all_meals']
            ];
            $room['child_below_5_price'] = $room['price_child_below_5'];

            // ✅ Room images
            // ✅ FIXED IMAGE PATH HANDLING (for your actual DB)
$images = [];
if (!empty($room['image_paths'])) {
    $images = array_map(function($path) use ($basePath) {
        $path = trim($path);
        // If already starts with 'admin/', keep as is
        if (strpos($path, 'admin/') === 0) {
            return $basePath . $path;
        }
        // If it's a full URL
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        // Default fallback (assume admin/uploads)
        return $basePath . 'admin/uploads/' . $path;
    }, explode(',', $room['image_paths']));
}
$room['images'] = $images;



            // ✅ Amenities
            $amenityList = [];
            if (!empty($room['amenity_data'])) {
                $pairs = explode(',', $room['amenity_data']);
                foreach ($pairs as $pair) {
                    $item = explode('|', $pair);
                    if (count($item) === 2) {
                        [$name, $icon] = $item;
                        $amenityList[] = ['name' => $name, 'icon' => $icon ?: 'bi-check-circle'];
                    }
                }
            }
            $room['amenities'] = $amenityList;
            
            $rooms_data[] = $room;
        }
    }
    return $rooms_data;
}

$all_rooms = get_all_room_data(
    $conn,
    $check_in,
    $check_out,
    $guests,
    $children,
    $no_of_rooms,
    $room_id
);

// ✅ Find the matching room by ID
$room = null;
if (!empty($all_rooms)) {
    foreach ($all_rooms as $r) {
        if ($r['id'] == $room_id) {
            $room = $r;
            break;
        }
    }
}

// ✅ If no room found → show error and exit
if (!$room) {
    echo '<div class="uk-container uk-margin-large-top uk-text-center">
            <div class="uk-alert-danger uk-padding">Invalid Room ID or Room Not Found.</div>
          </div>';
    exit;
}



$meal_plan_names = [
    'standard' => 'Room Only',
    'breakfast' => 'Room with Breakfast',
    'breakfast_lunch' => 'Room with Breakfast & Lunch',
    'all_meals' => 'All Meals'
];

// --- UPDATED LOGIC FOR CANCELLATION POLICIES ---

// Fetch all cancellation policies from the database, ordered by percentage
$cancellationPolicies = [];
$cancellationPolicyRs = mysqli_query($conn, "SELECT * FROM cancellation_policy ORDER BY refundable_percentage ASC");
if ($cancellationPolicyRs && mysqli_num_rows($cancellationPolicyRs) > 0) {
    while ($policy = mysqli_fetch_assoc($cancellationPolicyRs)) {
        // Create an array of formatted strings instead of one long string
        // $cancellationPolicies[] = "If you cancel the booking within **" . htmlspecialchars($policy['time_period']) . "**, you will get **" . (int)$policy['refundable_percentage'] . "%** of the total amount refunded.";
        $cancellationPolicies[] = "Cancellation " . htmlspecialchars($policy['time_period']) . ", " . htmlspecialchars((int)$policy['refundable_percentage']) . "% refundable.";
    }
} else {
    // Default to a single line if no policies are found
    $cancellationPolicies[] = 'Please contact to know cancellation policy';
}

// Update the meal plan features with the new array of policy strings
$meal_plan_features = [
    'standard' => ['No meals included', $cancellationPolicies],
    'breakfast' => ['Complimentary Breakfast', $cancellationPolicies],
    'breakfast_lunch' => ['Complimentary Breakfast & Lunch', $cancellationPolicies],
    'all_meals' => ['All Meals included (Breakfast, Lunch & Dinner)', $cancellationPolicies]
];
// --- END OF UPDATED LOGIC ---
?>

<!DOCTYPE html>
<html>
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

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

        <link rel="stylesheet" href="css/uikit.min.css" />
        <link rel="stylesheet" href="css/font-awesome.min.css" />
        <!-- <link rel="stylesheet" href="css/tiny-date-picker.min.css" /> -->
        <link rel="stylesheet" href="css/style.css?v=6.1" />
        <link rel="stylesheet" href="css/media-query.css" />

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
							<h1 class="uk-margin-remove">Room Detail</h1><!-- page title -->
							<p class="impx-text-large uk-margin-remove">Browse &amp; Choose Your Choice</p><!-- page subtitle -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- PAGE HEADING END -->


		<!-- CONTENT -->
		<div class="uk-padding vert uk-padding-remove-horizontal">
			<div class="uk-container">
				<div data-uk-grid>

				<?php if (empty($all_rooms)): ?>
					
                <div class="alert alert-warning text-center">
                    No rooms are available for the selected dates or guest count. Availability is shown based on each room’s capacity. You can adjust the number of guests or increase the number of rooms on the booking page.
                </div>
            <?php else: ?>

					<div class="uk-width-1-1 uk-margin-medium-top">
						<!-- slider -->
						<div class="impx-room-slider">
							<div class="uk-position-relative" data-uk-slideshow="animation: fade">
							    <ul class="uk-slideshow-items">
							        <li>
							            <img src="<?= htmlspecialchars($room['images'][0] ?? 'assets/img/default-room.jpg') ?>" alt="<?= htmlspecialchars($room['room_name']) ?>"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#roomDetailsModal"
                                 data-room-id="<?= $room['id'] ?>" data-uk-cover>
							            <div class="impx-overlay"></div>
							        </li>
							        <li>
							            <img src="images/about_banner5.png" alt="" data-uk-cover>
							            <div class="impx-overlay"></div>
							        </li>
							        <li>
							            <img src="images/banner2.jpg" alt="" data-uk-cover>
							            <div class="impx-overlay"></div>
							        </li>
							        <li>
							            <img src="images/about_banner2.png" alt="" data-uk-cover>
							            <div class="impx-overlay"></div>
							        </li>
							        <li>
							            <img src="images/balcony.jpg" alt="" data-uk-cover>
							            <div class="impx-overlay"></div>
							        </li>
							    </ul>
							    <!-- slider thumb nav -->
							    <div class="uk-position-center-left uk-position-medium">
							        <ul class="uk-thumbnav uk-thumbnav-vertical">
							            <li data-uk-slideshow-item="0"><a href="#"><img src="<?= htmlspecialchars($room['images'][0] ?? 'assets/img/default-room.jpg') ?>" width="120" alt=""></a></li>
							            <li data-uk-slideshow-item="1"><a href="#"><img src="images/about_banner5.png" width="120" alt=""></a></li>
							            <li data-uk-slideshow-item="2"><a href="#"><img src="images/banner2.jpg" width="120" alt=""></a></li>
							            <li data-uk-slideshow-item="3"><a href="#"><img src="images/about_banner2.png" width="120" alt=""></a></li>
							            <li data-uk-slideshow-item="4"><a href="#"><img src="images/balcony.jpg" width="120" alt=""></a></li>
							        </ul>
							    </div>
							    <!-- slider thumb nav end -->
							</div>
						</div>
						<!-- slider end -->
					</div>
					

					<!-- MAIN CONTENT -->
					<div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-1-1@s">
						<!-- highlight -->
						<ul class="uk-child-width-1-3@xl uk-child-width-1-3@l uk-child-width-1-2@m uk-child-width-1-2@s uk-grid-medium uk-grid-match" data-uk-grid>
						    <li class="uk-text-center">
						    	<div class="uk-card uk-card-default uk-card-body impx-padding-medium"><!-- highlight item #1 -->
							    	<i class="fa fa-wifi fa-2x impx-text-aqua"></i>
							    	<h6 class="uk-margin-remove-bottom uk-margin-small-top">Free Wi-fi</h6>
							    	<p class="uk-margin-remove-bottom uk-margin-small-top">Ergo hoc quidem apparet agendum esse natos. Quam tu ponis in verbis ego</p>
						    	</div>
						    </li><!-- highlight item #1 end -->
						    <li class="uk-text-center"><!-- highlight item #2 -->
						    	<div class="uk-card uk-card-default uk-card-body impx-padding-medium">
							    	<i class="fa fa-bathtub fa-2x impx-text-aqua"></i>
							    	<h6 class="uk-margin-remove-bottom uk-margin-small-top">Bathtub</h6>
							    	<p class="uk-margin-remove-bottom uk-margin-small-top">Ergo hoc quidem apparet agendum esse natos. Quam tu ponis in verbis ego</p>
						    	</div>
						    </li><!-- highlight item #2 end -->
						    <li class="uk-text-center"><!-- highlight item #3 -->
						    	<div class="uk-card uk-card-default uk-card-body impx-padding-medium">
							    	<i class="fa fa-tv fa-2x impx-text-aqua"></i>
							    	<h6 class="uk-margin-remove-bottom uk-margin-small-top">Widescreen TV</h6>
							    	<p class="uk-margin-remove-bottom uk-margin-small-top">Ergo hoc quidem apparet agendum esse natos. Quam tu ponis in verbis ego</p>
							    </div>
						    </li><!-- highlight item #3 end -->
						    <li class="uk-text-center"><!-- highlight item #4 -->
						    	<div class="uk-card uk-card-default uk-card-body impx-padding-medium">
							    	<i class="fa fa-heart-o fa-2x impx-text-aqua"></i>
							    	<h6 class="uk-margin-remove-bottom uk-margin-small-top">Gym Studio</h6>
							    	<p class="uk-margin-remove-bottom uk-margin-small-top">Ergo hoc quidem apparet agendum esse natos. Quam tu ponis in verbis ego</p>
							    </div>
						    </li><!-- highlight item #4 end -->
						    <li class="uk-text-center"><!-- highlight item #5 -->
						    	<div class="uk-card uk-card-default uk-card-body impx-padding-medium">
							    	<i class="fa fa-child fa-2x impx-text-aqua"></i>
							    	<h6 class="uk-margin-remove-bottom uk-margin-small-top">Kids Playground</h6>
							    	<p class="uk-margin-remove-bottom uk-margin-small-top">Ergo hoc quidem apparet agendum esse natos. Quam tu ponis in verbis ego</p>
							    </div>
						    </li><!-- highlight item #5 end -->
						    <li class="uk-text-center"><!-- highlight item #6 -->
						    	<div class="uk-card uk-card-default uk-card-body impx-padding-medium">
							    	<i class="fa fa-coffee fa-2x impx-text-aqua"></i>
							    	<h6 class="uk-margin-remove-bottom uk-margin-small-top">Mini Cafe</h6>
							    	<p class="uk-margin-remove-bottom uk-margin-small-top">Ergo hoc quidem apparet agendum esse natos. Quam tu ponis in verbis ego</p>
							    </div>
						    </li><!-- highlight item #6 end -->
						</ul>
						<!-- highlight end -->

						<!-- room description -->
						 <h3><?= htmlspecialchars($room['room_name']) ?></h3>
						<h4>Room Description</h4>
						<?php if ($room['available_qty'] !== null): ?>
							<p class="mb-1">
								<?php if ($room['available_qty'] > 0): ?>
									<span class="text-success fw-bold"><?= $room['available_qty'] ?> room(s) available</span>
								<?php else: ?>
									<span class="text-danger fw-bold">Sold Out</span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<p class="impx-text-large"><?= nl2br(htmlspecialchars(substr($room['description'] ?? '', 0, 150) . '...')) ?></p>

									
						<!-- room features list -->
						<div data-uk-grid>
							<div class="uk-width-1-2@xl uk-width-1-2@l uk-width-1-2@m uk-width-1-2@s">
								<h5>Additional Description</h5>
								<p><strong>Total Room Capacity:</strong><br><?= $room['room_capacity'] ?> persons</p>
                                <ul>
                                    <li>Base Adults: <?= $room['base_adults'] ?></li>
                                    <li>Max Adult/Child with Extra Bed: <?= $room['max_extra_with_bed'] ?></li>
                                    <li>Child (5–12) without Bed: <?= $room['max_child_without_bed_5_12'] ?></li>
                                    <li>Child (<5) without Bed: <?= $room['max_child_without_bed_below_5'] ?>
                                        <?php if ($room['price_child_below_5'] == 0.00): ?>
                                            (Complimentary)
                                        <?php endif; ?>
                                    </li>
                                </ul>
							</div>
							<div class="uk-width-1-2@xl uk-width-1-2@l uk-width-1-2@m uk-width-1-2@s">
								<h5>More Facilities</h5>
								<ul class="uk-list uk-list-bullet impx-list">
									<?php foreach ($room['amenities'] as $am): ?>
									<li>
										<i class="bi <?= htmlspecialchars($am['icon']) ?> me-1"></i>
                                            <?= htmlspecialchars($am['name']) ?>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>

						

						<hr class="uk-divider-icon">

						<!-- Price details according to meal plan -->
						<div style="background-color: #f5f5f5;" class="meal-plan-list">
                            <div class="table-responsive">
                                <table  class="table table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">Meal Plan</th>
                                            <th scope="col">Extra Adult/Child with Bed</th>
                                            <th scope="col">Child (5-12) without Bed</th>
                                            <th scope="col">Price for <?= $no_of_rooms ?> Room/Night</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($room['meal_prices'] as $key => $price): ?>
                                            <?php
                                                // Map meal plan key to correct DB fields
                                                switch ($key) {
                                                    case 'standard':
                                                        $extra_bed_price   = $room['price_with_extra_bed_standard'];
                                                        $child_5_12_price  = $room['price_child_5_12_standard'];
                                                        break;
                                                    case 'breakfast':
                                                        $extra_bed_price   = $room['price_with_extra_bed_bf'];
                                                        $child_5_12_price  = $room['price_child_5_12_bf'];
                                                        break;
                                                    case 'breakfast_lunch':
                                                        $extra_bed_price   = $room['price_with_extra_bed_bf_lunch'];
                                                        $child_5_12_price  = $room['price_child_5_12_bf_lunch'];
                                                        break;
                                                    case 'all_meals':
                                                        $extra_bed_price   = $room['price_with_extra_bed_all_meals'];
                                                        $child_5_12_price  = $room['price_child_5_12_all_meals'];
                                                        break;
                                                    default:
                                                        $extra_bed_price   = 0;
                                                        $child_5_12_price  = 0;
                                                }

                                                /**
                                             * ✅ FIXED LOGIC
                                             * - Children <5 years old (without bed) are NOT counted towards room capacity
                                             * - Only adults, extra beds, and 5-12 yrs old children affect the capacity
                                             */
                                            
                                            // Step 1: Children under 5 do NOT count towards occupancy
                                            $children_5_12_needed = min($children, $room['max_child_without_bed_5_12']); 
                                            $children_below_5_needed = max(0, $children - $children_5_12_needed); // purely for pricing, NOT capacity

                                            // Step 2: Calculate extra beds needed
                                            $extra_beds_needed = max(0, $guests - $room['base_adults']); 

                                            // Step 3: Total price per room
                                            $total_price_per_room = $price;
                                            $total_price_per_room += $extra_beds_needed * $extra_bed_price;
                                            $total_price_per_room += $children_5_12_needed * $child_5_12_price;
                                            $total_price_per_room += $children_below_5_needed * $room['price_child_below_5'];

                                            // Step 4: Final total price for selected number of rooms
                                            $final_price = $total_price_per_room * $no_of_rooms;
                                        ?>
                                        <?php if ($price > 0): ?>
                                                <tr>
                                            <td class="text-start">
                                                <h6><?= htmlspecialchars($meal_plan_names[$key]) ?></h6>
                                                <small class="d-block text-muted mt-2">
                                                    <i class="bi bi-check-circle-fill text-success"></i> <?= ($meal_plan_features[$key][0]) ?><br>
                                                    
                                                </small>
                                                <div class="mt-2" style="font-size: 0.9rem;">
                                                    <?php if ($extra_beds_needed > 0): ?>
                                                        <span class="badge bg-secondary me-1"><?= $extra_beds_needed ?> Extra Bed(s)</span>
                                                    <?php endif; ?>
                                                    <?php if ($children_5_12_needed > 0): ?>
                                                        <span class="badge bg-secondary me-1"><?= $children_5_12_needed ?> Child(ren) (5-12)</span>
                                                    <?php endif; ?>
                                                    <?php if ($children_below_5_needed > 0): ?>
                                                        <span class="badge bg-secondary me-1"><?= $children_below_5_needed ?> Child(ren) (<5)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?= $extra_bed_price > 0 ? '₹'.number_format($extra_bed_price, 2) : 'NA' ?>
                                            </td>
                                            <td>
                                                <?= $child_5_12_price > 0 ? '₹'.number_format($child_5_12_price, 2) : 'NA' ?>
                                            </td>
                                            <td>
                                                <p class="lead fw-bold mb-0">₹<?= number_format($final_price, 2) ?></p>
                                                <small class="text-muted d-block">for <?= $no_of_rooms ?> room</small>
                                            </td>
                                            <td>
                                                <a style="background-color: #bd8f03ff; color: #fff;" href="booking.php?room_id=<?= $room['id'] ?>&check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>&no_of_rooms=<?= (int)$no_of_rooms ?>&guests=<?= (int)$guests ?>&children=<?= (int)$children ?>&meal_plan=<?= $key ?>&room_price=<?= $price ?>&extra_bed_price=<?= $extra_bed_price ?>&child_5_12_price=<?= $child_5_12_price ?>&child_below_5_price=<?= $room['price_child_below_5'] ?>"class="btn ">Select</a>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Price details according to meal plan -->
					</div>
					<!-- MAIN CONTENT -->

					<!-- SIDEBAR -->
					<div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-3@m uk-width-1-1@s">
						<!-- booking form -->
						<div class="bg-color-aqua uk-padding impx-padding-medium uk-margin-medium-bottom uk-box-shadow-medium">

							<div class="impx-hp-booking-form side-form uk-margin-bottom uk-margin-remove-top ">
								<h6 class="uk-heading-line uk-text-center uk-light uk-text-uppercase"><span>Check Availability</span></h6>
								<form class="" method="GET" action="room_detail.php" data-room-id="<?= htmlspecialchars($room_id) ?>">
    <!-- Hidden Room ID -->
    <input type="hidden" name="room_id" value="<?= htmlspecialchars($room_id) ?>">

    <div class="uk-margin">
        <div class="uk-form-controls">
            <div class="uk-inline">
                <label class="uk-form-label">Check-in Date</label>
                <span class="uk-form-icon" data-uk-icon="icon: calendar"></span>
                <input class="uk-input booking-arrival uk-border-rounded" 
                       type="date" name="check_in" id="check_in"
                       value="<?= htmlspecialchars($check_in) ?>" required>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <div class="uk-form-controls">
            <div class="uk-inline">
                <label class="uk-form-label">Check-out Date</label>
                <span class="uk-form-icon" data-uk-icon="icon: calendar"></span>
                <input class="uk-input booking-departure uk-border-rounded" 
                       type="date" name="check_out" id="check_out"
                       value="<?= htmlspecialchars($check_out) ?>" required>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <div class="uk-form-controls uk-position-relative">
            <label class="uk-form-label">No. of Adults</label>
            <span class="uk-form-icon select-icon" data-uk-icon="icon: users"></span>
            <input type="number" name="guests" min="1"
                   value="<?= htmlspecialchars($guests) ?>" 
                   class="uk-input uk-border-rounded" required>
        </div>
    </div>

    <div class="uk-margin">
        <div class="uk-form-controls uk-position-relative">
            <label class="uk-form-label">No. of Children</label>
            <span class="uk-form-icon select-icon" data-uk-icon="icon: users"></span>
            <input type="number" name="children" min="0"
                   value="<?= htmlspecialchars($children) ?>" 
                   class="uk-input uk-border-rounded" required>
        </div>
    </div>

    <div class="uk-margin">
        <div class="uk-form-controls uk-position-relative">
            <label class="uk-form-label">Rooms</label>
            <span class="uk-form-icon select-icon" data-uk-icon="icon: album"></span>
            <input type="number" name="no_of_rooms" min="1"
                   value="<?= htmlspecialchars($no_of_rooms) ?>" 
                   class="uk-input uk-border-rounded" required>
        </div>
    </div>

    <div>
        <label class="uk-form-label empty-label">&nbsp;</label>
        <button class="uk-button uk-width-1-1" type="submit">Check Availability</button>
    </div>
</form>

							</div>
							<!-- booking form -->
						</div>
						<!-- SIDEBAR END -->

						

					</div>
            <?php endif; ?>
				</div>
			</div>
		</div>	
	</div>					
		<!-- CONTENT END -->

		<!-- CONTACT INFO -->
		<div class="pre-footer-contact uk-padding bg-img2 uk-position-relative">
			<div class="impx-overlay dark"></div>
			<div class="uk-container">

				<div data-uk-grid class="uk-padding-remove-bottom uk-position-relative">				
					<div class="uk-light uk-width-1-2@xl uk-width-1-2@l uk-width-1-2@m uk-width-1-3@s"><!-- address -->
						<h5 class="uk-heading-line uk-margin-remove-bottom"><span>Address</span></h5>
						<p class="impx-text-large uk-margin-top">Shivoham Retreat, CMTC House, Kuthalwali, Johrigaon, Dehradun,
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



        <script>
const checkIn   = document.getElementById('check_in');
const checkOut = document.getElementById('check_out');
const today = new Date(); const todayStr = today.toISOString().split('T')[0];
checkIn.setAttribute('min', todayStr);
checkIn.addEventListener('change', () => {
    const inDate = new Date(checkIn.value);
    if (!isNaN(inDate)) {
        inDate.setDate(inDate.getDate() + 1);
        const nextDay = inDate.toISOString().split('T')[0];
        checkOut.value = nextDay;
        checkOut.setAttribute('min', nextDay);
    }
});
if (checkIn.value) {
    const inDate = new Date(new Date(checkIn.value).getTime() + (24 * 60 * 60 * 1000));
    const nextDay = inDate.toISOString().split('T')[0];
    checkOut.setAttribute('min', nextDay);
}

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

document.querySelectorAll('input[type="number"]').forEach(handleNumberInput);

const roomDetailsModal = document.getElementById('roomDetailsModal');

roomDetailsModal.addEventListener('show.bs.modal', function (event) {
    const imageElement = event.relatedTarget;
    const roomId = imageElement.getAttribute('data-room-id');

    const modalCarousel = document.getElementById('owl-modal-carousel');
    const modalRoomName = document.getElementById('modalRoomName');
    const modalRoomDescription = document.getElementById('modalRoomDescription');
    const modalAmenities = document.getElementById('modalAmenities');

    modalRoomName.innerText = 'Loading...';
    modalRoomDescription.innerText = '';
    modalAmenities.innerHTML = '';
    modalCarousel.innerHTML = `<div class="text-center p-5 text-muted">Loading images...</div>`;

    fetch(`getRoomDetailsForModal.php?room_id=${roomId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                modalRoomName.innerText = 'Error';
                modalRoomDescription.innerText = data.error;
                modalCarousel.innerHTML = `<div class="text-center p-5 text-danger">${data.error}</div>`;
                return;
            }

            modalRoomName.innerText = data.room_name;
            modalRoomDescription.innerText = data.description;
            
            modalAmenities.innerHTML = data.amenities.map(am => `
                <span class="badge bg-light text-dark border me-2 mb-2 p-2">
                    <i class="bi ${am.icon} me-1"></i>
                    ${am.name}
                </span>
            `).join('');

            if ($(modalCarousel).hasClass('owl-carousel')) {
                $(modalCarousel).owlCarousel('destroy').removeClass('owl-carousel owl-theme');
            }

            const carouselItems = data.images.map(img => `
                <div class="item">
                    <img src="${img}" class="d-block w-100" style="height: 400px; object-fit: cover;">
                </div>
            `).join('');
            
            modalCarousel.innerHTML = carouselItems || `<div class="text-center p-5 text-muted">No images available.</div>`;

            if (carouselItems) {
                $(modalCarousel).addClass('owl-carousel owl-theme').imagesLoaded(function() {
                    $(modalCarousel).owlCarousel({
                        loop: true,
                        nav: true,
                        navText: ['<i class="bi bi-chevron-left"></i>', '<i class="bi bi-chevron-right"></i>'],
                        dots: true,
                        items: 1,
                        autoplay: true,
                        autoplayTimeout: 5000,
                        autoplayHoverPause: true,
                        responsiveClass: true,
                        responsive: {
                            0: { items: 1 },
                            600: { items: 1 },
                            1000: { items: 1 }
                        }
                    });
                });
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            modalRoomName.innerText = 'Error';
            modalRoomDescription.innerText = 'An unknown error occurred. Please check the console for details.';
            modalAmenities.innerHTML = '';
            modalCarousel.innerHTML = `<div class="text-center p-5 text-danger">An unknown error occurred.</div>`;
        });
});

roomDetailsModal.addEventListener('hidden.bs.modal', function () {
    const modalCarousel = document.getElementById('owl-modal-carousel');
    if ($(modalCarousel).hasClass('owl-carousel')) {
        $(modalCarousel).owlCarousel('destroy').removeClass('owl-carousel owl-theme');
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const shareButtons = document.querySelectorAll('.share-room-btn');

    shareButtons.forEach(button => {
        const shareData = {
            title: button.dataset.title || 'Check out this page',
            text: 'Check out this room: ' + (button.dataset.title || 'A great hotel room'),
            url: window.location.origin + '/' + button.dataset.url
        };

        if (navigator.share) {
            button.addEventListener('click', async () => {
                try {
                    await navigator.share(shareData);
                    console.log('Content shared successfully');
                } catch (err) {
                    console.error('Error sharing:', err.message);
                }
            });
        } else {
            const shareContainer = button.closest('.share-container');
            const fallbackHtml = `
                <div class="d-flex align-items-center">
                    <span class="d-inline-block me-2">Share this page:</span>
                    <a href="https://wa.me/?text=${encodeURIComponent(shareData.text + ' ' + shareData.url)}" target="_blank" title="Share on WhatsApp"><i class="fa fa-whatsapp fa-2x"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareData.url)}" target="_blank" title="Share on Facebook"><i class="fa fa-facebook fa-2x"></i></a>
                    <a href="https://www.instagram.com/direct/inbox/?text=${encodeURIComponent(shareData.text + ' ' + shareData.url)}" target="_blank" title="Share on Instagram"><i class="fa fa-instagram fa-2x"></i></a>
                </div>
            `;
            if (shareContainer) {
                shareContainer.innerHTML = fallbackHtml;
            } else {
                button.parentNode.innerHTML = fallbackHtml;
            }
        }
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

document.querySelectorAll('.availability-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Stop full-page submission
        const roomId      = this.dataset.roomId;
        const checkIn     = this.querySelector('[name="check_in"]').value;
        const checkOut    = this.querySelector('[name="check_out"]').value;
        const rooms       = this.querySelector('[name="no_of_rooms"]').value || 0;
        const adults      = this.querySelector('[name="guests"]').value || 1;
        const children    = this.querySelector('[name="num_children"]').value || 0;
        const resultDiv   = document.getElementById(`availability-result-${roomId}`);

        fetch('ajaxCheckAvailability.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                room_id: roomId,  // ✅ Added
                check_in: checkIn,
                check_out: checkOut,
                no_of_rooms: rooms,
                guests: adults,
                num_children: children
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const room = data.rooms.find(r => r.id == roomId);
                if (room) {
                    resultDiv.innerHTML = room.available_qty > 0
                        ? `<span class="text-success">
                               <i class="bi bi-calendar2-check"></i>
                               ${room.available_qty} room(s) available
                           </span>`
                        : `<span class="text-danger">
                               <i class="bi bi-calendar2-x"></i>
                               Fully Booked
                           </span>`;
                } else {
                    resultDiv.innerHTML = `<span class="text-danger">Not available</span>`;
                }
            } else {
                resultDiv.innerHTML = `<span class="text-danger">${data.message}</span>`;
            }
        })
        .catch(err => {
            console.error(err);
            resultDiv.innerHTML = `<span class="text-danger">Error checking availability.</span>`;
        });
    });
});
</script>
    </body>


</html>