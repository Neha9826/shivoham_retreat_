<?php
// room_details.php (Goibibo-style Room Selection Page with Advanced Search)
session_start();
include 'db.php';

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
            $images = [];
            if (!empty($room['image_paths'])) {
                $images = array_map(function($path) use ($basePath) {
                    if (strpos($path, 'admin/') !== 0 && strpos($path, 'assets/') !== 0) {
                        return $basePath . 'admin/' . $path;
                    }
                    return $basePath . $path;
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

// --- Open Graph meta tag logic starts here ---
$og_title = "Available Rooms";
$og_description = "Check out our available rooms and book your stay!";
$og_image = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . "/assets/img/default-room.jpg";
$og_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (!empty($all_rooms) && $room_id) {
    $preferred_room = null;
    foreach ($all_rooms as $room) {
        if ($room['id'] == $room_id) {
            $preferred_room = $room;
            break;
        }
    }
    if ($preferred_room) {
        $og_title = "Check out the " . htmlspecialchars($preferred_room['room_name']);
        $og_description = htmlspecialchars($preferred_room['description'] ?? 'A comfortable room with great amenities.');
        $first_image = !empty($preferred_room['images'][0]) ? $preferred_room['images'][0] : 'assets/img/default-room.jpg';
        $og_image = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $first_image;
    }
}
// --- Open Graph meta tag logic ends here ---
?>
<!doctype html>
<html class="no-js" lang="zxx">
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
	<link rel="stylesheet" href="css/style.css?v=4.2" />
	<link rel="stylesheet" href="css/media-query.css" />
    <style>
      .room-section {
          border: 1px solid #f5f5f5;
          border-radius: 8px;
          margin-bottom: 30px;
          overflow: hidden;
      }
      .room-header {
          padding: 20px;
          display: flex;
          align-items: flex-start;
      }
      .room-image {
          width: 250px;
          height: 180px;
          object-fit: cover;
          border-radius: 8px;
          flex-shrink: 0;
          cursor: pointer;
      }
      .room-info {
          padding-left: 20px;
          flex-grow: 1;
      }
      .meal-plan-item {
          border-top: 1px solid #f5f5f5;
          padding: 15px 20px;
          display: flex;
          justify-content: space-between;
          align-items: center;
      }
      .capacity-details {
        font-size: 0.9em;
        line-height: 1.2;
        margin-bottom: 10px;
      }
      
      @media (max-width: 768px) {
        .room-header {
            flex-direction: column;
            align-items: center;
        }
        .room-image {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
        .room-info {
            padding-left: 0;
            text-align: center;
        }
        .room-info ul {
            text-align: left;
            margin: 0 auto;
            max-width: 80%;
        }
        .meal-plan-item {
            flex-direction: column;
            text-align: center;
        }
        .meal-plan-item h5 {
            margin-bottom: 10px;
        }
        .meal-plan-item .text-end {
            flex-direction: column;
            align-items: center;
        }
        .meal-plan-item .btn-primary {
            margin-top: 10px;
        }
      }

    .owl-theme .owl-nav [class*='owl-'] {
        background: transparent !important;
        color: transparent !important;
        border: none !important;
        padding: 0 !important;
        transition: opacity 0.2s ease, background 0.2s ease;
        box-shadow: none !important;
        opacity: 0;
    }

    .owl-theme .owl-nav [class*='owl-']:hover {
        opacity: 1;
        background: transparent !important;
    }

    .owl-theme .owl-nav .owl-prev,
    .owl-theme .owl-nav .owl-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 100%;
    }

    .owl-theme .owl-nav .owl-prev {
        left: 0;
    }

    .owl-theme .owl-nav .owl-next {
        right: 0;
    }

    .owl-theme:hover .owl-nav .owl-prev,
    .owl-theme:hover .owl-nav .owl-next {
        color: #fff !important;
        opacity: 1 !important;
    }
    .owl-theme .owl-nav .owl-prev,
    .owl-theme .owl-nav .owl-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 60px;
        z-index: 10;
    }
    </style>
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

		

<div class="container my-5">
    <div class="row">
        <div style="background-color: #E8E8E8;" class="col-lg-12">
            <form style="background-color: #f5f5f5;" method="GET" action="room_details.php" class="card p-4 mb-5 shadow-sm">
                <h4 class="mb-3">Check Availability</h4>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label>Check-in Date:</label>
                        <input type="date" name="check_in" id="check_in"
                               value="<?= htmlspecialchars($check_in) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Check-out Date:</label>
                        <input type="date" name="check_out" id="check_out"
                               value="<?= htmlspecialchars($check_out) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. of Rooms:</label>
                        <input type="number" name="no_of_rooms" min="1"
                               value="<?= htmlspecialchars($no_of_rooms) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. of Adults:</label>
                        <input type="number" name="guests" min="1"
                               value="<?= htmlspecialchars($guests) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>No. of Children:</label>
                        <input type="number" name="children" min="0"
                               value="<?= htmlspecialchars($children) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button style="background-color: #bd8f03ff; color: #fff;" type="submit" class="btn mt-2 w-100">Update</button>
                    </div>
                </div>
            </form>
            
            <?php if (empty($all_rooms)): ?>
                <div class="alert alert-warning text-center">
                    No rooms are available for the selected dates or guest count. Availability is shown based on each room’s capacity. You can adjust the number of guests or increase the number of rooms on the booking page.
                </div>
            <?php else: ?>
                <?php foreach ($all_rooms as $room): ?>
                    <div class="room-section mb-5 shadow-sm">
                        <div class="room-header">
                            <img src="<?= htmlspecialchars($room['images'][0] ?? 'assets/img/default-room.jpg') ?>" 
                                 class="room-image" 
                                 alt="<?= htmlspecialchars($room['room_name']) ?>"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#roomDetailsModal"
                                 data-room-id="<?= $room['id'] ?>">
                            <div class="room-info">
                                <h3><?= htmlspecialchars($room['room_name']) ?></h3>
                                <div class="share-container mb-3">
                                     <button style="background-color: #bd8f03ff; color: #fff;" class="btn share-room-btn" 
                                            data-url="<?= htmlspecialchars("ShivohamRetreat/room_details.php?room_id={$room['id']}&check_in=$check_in&check_out=$check_out&no_of_rooms=$no_of_rooms&guests=$guests&children=$children") ?>"
                                            data-title="<?= htmlspecialchars($room['room_name']) ?>">
                                        <i class="fa fa-share-alt"></i> Share this Room
                                    </button>
                                </div>
                                <?php if ($room['available_qty'] !== null): ?>
                                    <p class="mb-1">
                                        <?php if ($room['available_qty'] > 0): ?>
                                            <span class="text-success fw-bold"><?= $room['available_qty'] ?> room(s) available</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-bold">Sold Out</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
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
                                <p><?= nl2br(htmlspecialchars(substr($room['description'] ?? '', 0, 150) . '...')) ?></p>
                                <div class="d-flex flex-wrap mb-2">
                                    <?php foreach ($room['amenities'] as $am): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            <i class="bi <?= htmlspecialchars($am['icon']) ?> me-1"></i>
                                            <?= htmlspecialchars($am['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

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
                                                <h5><?= htmlspecialchars($meal_plan_names[$key]) ?></h5>
                                                <small class="d-block text-muted mt-2">
                                                    <i class="bi bi-check-circle-fill text-success"></i> <?= ($meal_plan_features[$key][0]) ?><br>
                                                    <?php foreach ($meal_plan_features[$key][1] as $policy_line): ?>
                                                        <i class="bi bi-check-circle-fill text-success"></i> <?= $policy_line ?><br>
                                                    <?php endforeach; ?>
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
                                                <a style="background-color: #bd8f03ff; color: #fff;" href="booking.php?room_id=<?= $room['id'] ?>
                                                    &check_in=<?= urlencode($check_in) ?>
                                                    &check_out=<?= urlencode($check_out) ?>
                                                    &no_of_rooms=<?= (int)$no_of_rooms ?>
                                                    &guests=<?= (int)$guests ?>
                                                    &children=<?= (int)$children ?>
                                                    &meal_plan=<?= $key ?>
                                                    &room_price=<?= $price ?>
                                                    &extra_bed_price=<?= $extra_bed_price ?>
                                                    &child_5_12_price=<?= $child_5_12_price ?>
                                                    &child_below_5_price=<?= $room['price_child_below_5'] ?>"
                                                class="btn ">Select</a>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- <?php include 'includes/forQuery.php'; ?>
<?php include 'includes/insta_area.php'; ?> -->
<?php include 'includes/footer.php'; ?>
<!-- <?php include 'includes/form.php'; ?> -->

<div class="modal fade" id="roomDetailsModal" tabindex="-1" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRoomName"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="owl-modal-carousel" class="owl-carousel owl-theme">
          <div class="text-center p-5 text-muted">Loading images...</div>
        </div>
        <div class="mt-4">
          <h5 class="mb-2">Description</h5>
          <p id="modalRoomDescription" class="text-muted"></p>
          <h5 class="mt-4 mb-2">Amenities</h5>
          <div id="modalAmenities" class="d-flex flex-wrap">
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>