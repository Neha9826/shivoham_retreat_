<?php
// rooms.php (final consolidated version - restored to original working behavior)
// NOTE: this file expects a mysqli connection $conn. It will include db.php if $conn not defined.
// Make a backup before replacing your original.

// if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($conn) || !($conn instanceof mysqli)) {
    include 'db.php';
}

$basePath = ''; // update if your project lives in a subfolder e.g. '/ShivohamRetreat/'

// Prefer values from URL (so new tab / fresh load doesn't get stale session values)
$check_in = (isset($_GET['check_in']) && $_GET['check_in'] !== '') 
            ? trim($_GET['check_in']) 
            : (isset($_SESSION['check_in']) ? trim($_SESSION['check_in']) : '');

$check_out = (isset($_GET['check_out']) && $_GET['check_out'] !== '') 
             ? trim($_GET['check_out']) 
             : (isset($_SESSION['check_out']) ? trim($_SESSION['check_out']) : '');

// numeric fields: prefer URL then session fallback
$no_of_rooms = isset($_GET['no_of_rooms']) ? intval($_GET['no_of_rooms']) : (isset($_SESSION['no_of_rooms']) ? intval($_SESSION['no_of_rooms']) : 1);
$guests      = isset($_GET['guests'])      ? intval($_GET['guests'])      : (isset($_SESSION['guests']) ? intval($_SESSION['guests']) : 2);
$children    = isset($_GET['children'])    ? intval($_GET['children'])    : (isset($_SESSION['num_children']) ? intval($_SESSION['num_children']) : 0);

// ⬇️ ADD THIS RIGHT HERE
if (!isset($_GET['check_in']) && isset($_SESSION['check_in'])) {
    unset(
        $_SESSION['check_in'],
        $_SESSION['check_out'],
        $_SESSION['no_of_rooms'],
        $_SESSION['guests'],
        $_SESSION['num_children']
    );
}
$check_in = $_GET['check_in'] ?? ($_SESSION['check_in'] ?? '');
$check_out = $_GET['check_out'] ?? ($_SESSION['check_out'] ?? '');
$no_of_rooms = $_GET['no_of_rooms'] ?? ($_SESSION['no_of_rooms'] ?? 1);
$guests = $_GET['guests'] ?? ($_SESSION['guests'] ?? 2);
$children = $_GET['children'] ?? ($_SESSION['children'] ?? 0);


// === Helpers ===
function build_capacity_text($base, $ebCap, $maxEA, $maxEC) {
    $base = (int)$base; $ebCap = (int)$ebCap; $maxEA = (int)$maxEA; $maxEC = (int)$maxEC;
    if ($ebCap <= 0 || ($maxEA <= 0 && $maxEC <= 0)) {
        return "Base {$base}";
    }
    if ($maxEA > 0 && $maxEC > 0) return "Base {$base} + up to {$maxEA} adult(s) and {$maxEC} child(ren)";
    if ($maxEA > 0) return "Base {$base} + up to {$maxEA} adult(s)";
    if ($maxEC > 0) return "Base {$base} + up to {$maxEC} child(ren)";
    return "Base {$base}";
}

function get_standard_room_price($conn, $room_id, $date) {
    if (!$date) return null;
    $dayOfWeek = date('l', strtotime($date));
    $priceColumn = strtolower($dayOfWeek) . '_standard';
    $sql = "SELECT {$priceColumn} FROM room_seasonal_prices
             WHERE room_id = ?
             AND ? BETWEEN start_date AND end_date
             LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("is", $room_id, $date);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    return $row && isset($row[$priceColumn]) ? (float)$row[$priceColumn] : null;
}

/**
 * compute_available_qty
 * - Returns integer >= 0 when check_in/check_out are provided
 * - Returns null when dates are missing (so UI can show neutral placeholder)
 *
 * @return int|null
 */
function compute_available_qty(mysqli $conn, int $room_id, string $check_in, string $check_out, int $total_qty): ?int {
    // if no dates provided, signal "unknown" availability to the UI
    if (!$check_in || !$check_out) return null;

    // 1) Find the maximum number of booked rooms on any single night in the date range
    $sql = "
        SELECT MAX(daily_total) AS max_booked
        FROM (
            SELECT `date`, COALESCE(SUM(booked_rooms),0) AS daily_total
            FROM room_availability
            WHERE room_id = ?
              AND date >= ? AND date < ?
            GROUP BY `date`
        ) AS daily
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // defensive fallback: return full stock if prepare fails
        return $total_qty;
    }
    $stmt->bind_param("iss", $room_id, $check_in, $check_out);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $maxBooked = (int)($res['max_booked'] ?? 0);

    // 2) Compute available quantity: stock minus the busiest night’s usage
    $available = $total_qty - $maxBooked;

    // Don’t allow negative values
    return max(0, $available);
}


// === Fetch rooms from DB ===
$sql = "SELECT r.*,
                (SELECT image_path FROM room_images WHERE room_id = r.id LIMIT 1) AS main_image,
                (SELECT GROUP_CONCAT(a.name, '|', a.icon_class)
                   FROM amenities a
                   JOIN room_amenities ra ON ra.amenity_id = a.id
                  WHERE ra.room_id = r.id) AS amenity_data
        FROM rooms r
        ORDER BY r.id DESC";
$roomResult = $conn->query($sql);

// Process all rooms (we compute server-side availability if dates were provided).
$rooms = [];
if ($roomResult && $roomResult->num_rows > 0) {
    while ($room = $roomResult->fetch_assoc()) {
        $room_id  = (int)$room['id'];
        $total_qty = (int)$room['total_rooms'];

        // Compute availability using helper (server-side initial state).
        $room['available_qty'] = compute_available_qty($conn, $room_id, $check_in, $check_out, $total_qty);

        // Capacity logic (unchanged)
        $adults = (int)$guests;
        $children_5_12 = min((int)$children, (int)$room['max_child_without_bed_5_12']);
        $children_below_5 = max(0, $children - $children_5_12); // not counted in capacity

        $per_room_capacity = (int)$room['base_adults'] + (int)$room['max_extra_with_bed'] + (int)$room['max_child_without_bed_5_12'];
        $total_capacity_allowed = $per_room_capacity * $no_of_rooms;
        $group_size = $adults + $children_5_12;
        $room['is_match'] = ($group_size <= $total_capacity_allowed);

        // Image path fix
        $imagePath = $room['main_image'] ?? 'assets/img/default-room.jpg';
        if (strpos($imagePath, 'admin/') !== 0 && strpos($imagePath, 'assets/') !== 0) {
            $imagePath = 'admin/' . $imagePath;
        }
        $room['main_image'] = $basePath . $imagePath;

        // Amenities
        $amenityList = [];
        if (!empty($room['amenity_data'])) {
            $pairs = explode(',', $room['amenity_data']);
            foreach ($pairs as $pair) {
                $parts = explode('|', $pair);
                $name = $parts[0] ?? '';
                $icon = $parts[1] ?? 'bi-check-circle';
                if ($name) $amenityList[] = ['name' => $name, 'icon' => $icon];
            }
        }
        $room['amenities'] = $amenityList;

        // Price
        $currentDate = date('Y-m-d');
        $price = get_standard_room_price($conn, $room_id, $currentDate);
        $room['price_display'] = $price ?? (float)$room['standard_price'];

        $rooms[] = $room;
    }
}

$roomDataJson = json_encode($rooms);
?>


			<div class="uk-container">
				<div class="uk-flex uk-flex-center uk-margin-medium-bottom impx-rooms-intro">
					<div class="uk-width-2-3@xl uk-width-2-3@l uk-width-1-1@m uk-width-1-1@s uk-text-center"><!-- intro -->
						<h2 class="uk-margin-remove-top uk-margin-small-bottom">Our Special Rooms</h2>
						<p class="impx-text-large uk-margin-remove-top">At Shivoham, every room is more than just a stay — it’s an experience of peace, simplicity, and Himalayan charm. Thoughtfully designed spaces blend comfort with calm, allowing you to wake up to mountain mist, birdsong, and breathtaking views.</p>
					</div><!-- intro end -->
				</div>

				<div class="uk-flex uk-flex-center uk-margin-bottom">
					<div class="uk-width-1-1">
						<!-- pricing items -->
						<div class="uk-child-width-1-3@xl uk-child-width-1-3@l uk-child-width-1-3@m uk-child-width-1-3@s uk-grid-collapse" data-uk-grid>
							<div><!-- pricing item #1 -->
								<div class="impx-promo-pricing uk-padding uk-box-shadow-medium uk-light bg-color-white">
									<div class="uk-position-relative">
										<img src="images/about_banner10.png" alt="">
										<div class="impx-overlay light"></div>
									</div>
									<div class=" uk-position-relative bg-color-gold uk-padding">
							            <h4 class="uk-heading-line"><span>Luxury Valley Facing Room</span></h4>
							            <span class="uk-label uk-label-success impx-text-gold uk-text-bold">>₹3500/Night(2pax only)</span>
										<ul class="uk-list">
											<li><i class="fa fa-bed"></i> Cozy mountain-facing room with balcony</li>
											<li><i class="fa fa-coffee"></i> Ideal for couples seeking calm & comfort</li>
											<li><i class="fa fa-group"></i> Scenic sunrise and valley views</li>
										</ul>
										<a href="room_detail.php?room_id=8 &check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>&no_of_rooms=<?= (int)$no_of_rooms ?>&guests=<?= (int)$guests ?>&children=<?= (int)$children ?>"
   class="uk-button impx-button small impx-button-outline small-border">
   View Detail <i class="fa fa-arrow-right"></i>
</a>

									</div>
						        </div>
							</div><!-- pricing item #1 end -->
							<div class="uk-position-relative uk-position-z-index"><!-- pricing item #2 -->
								<div class="impx-promo-pricing uk-padding uk-box-shadow-large uk-light featured bg-color-white">
									<div class="uk-position-relative">
										<img src="images/about_banner8.png" alt="">
										<div class="impx-overlay light"></div>
									</div>
									<div class=" uk-position-relative bg-color-aqua uk-padding">
							            <h3 class="uk-heading-line"><span>Shivoham Harmony House</span></h3>
							            <span class="uk-label uk-label-success impx-text-gold uk-text-bold">₹6000/Night(2 Rooms, 4 Adults)</span>
										<ul class="uk-list uk-list-large">
											<li><i class="fa fa-bed"></i> Entire retreat exclusively yours</li>
											<li><i class="fa fa-coffee"></i> Great for families & friends</li>
											<li><i class="fa fa-group"></i> Private spaces, shared laughter, pure peace</li>
										</ul>
										<a href="room_detail.php?room_id=10 &check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>&no_of_rooms=<?= (int)$no_of_rooms ?>&guests=<?= (int)$guests ?>&children=<?= (int)$children ?>"
   class="uk-button impx-button small impx-button-outline small-border">
   View Detail <i class="fa fa-arrow-right"></i></a>
									</div>
						        </div>
							</div><!-- pricing item #2 end -->
							<div><!-- pricing item #3 -->
								<div class="impx-promo-pricing uk-padding uk-box-shadow-medium uk-light bg-color-white">
									<div class="uk-position-relative">
										<img src="images/about_banner13.png" alt="">
										<div class="impx-overlay light"></div>
									</div>
									<div class=" uk-position-relative bg-color-gold uk-padding">
							            <h4 class="uk-heading-line"><span>Luxury Studio Room</span></h4>
							            <span class="uk-label uk-label-success impx-text-gold uk-text-bold">₹4000/Night(2pax only)</span>
										<ul class="uk-list">
											<li><i class="fa fa-bed"></i> Spacious studio with modern interiors</li>
											<li><i class="fa fa-coffee"></i>Warm lighting & peaceful surroundings</li>
											<li><i class="fa fa-group"></i> Perfect for a serene Himalayan stay</li>
										</ul>
										<a href="room_detail.php?room_id= 9 &check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>&no_of_rooms=<?= (int)$no_of_rooms ?>&guests=<?= (int)$guests ?>&children=<?= (int)$children ?>"
   class="uk-button impx-button small impx-button-outline small-border">
   View Detail <i class="fa fa-arrow-right"></i></a>
									</div>
						        </div>
							</div><!-- pricing item #3 end -->
						</div>
						<!-- pricing items end -->
					</div>
				</div>
			</div>
		