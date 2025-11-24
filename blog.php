<?php
// Connect to database
if(file_exists('admin/db.php')){
    include 'admin/db.php';
} elseif (file_exists('db.php')){
    include 'db.php';
} else {
    die("Database file not found");
}

// --- PAGINATION SETUP ---
$limit = 3; // Number of posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch Total Posts for Pagination Calculation
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM blogs");
$total_posts = mysqli_fetch_assoc($count_query)['total'];
$pages = ceil($total_posts / $limit);

// --- MAIN BLOG QUERY ---

// 1. Base Query
$sql_base = "SELECT * FROM blogs WHERE 1=1"; // 1=1 allows us to append AND conditions easily

// 2. Filter by Category (if clicked)
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $cat_filter = mysqli_real_escape_string($conn, $_GET['category']);
    $sql_base .= " AND category = '$cat_filter'";
}

// 3. Filter by Search (if typed)
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $sql_base .= " AND (title LIKE '%$search_term%' OR content LIKE '%$search_term%')";
}

// 4. Add Order and Pagination
$sql_final = $sql_base . " ORDER BY created_at DESC LIMIT $start, $limit";
$result = mysqli_query($conn, $sql_final);

// 5. Update Total Count for Pagination (Must filter count too!)
// We reuse the $sql_base but replace SELECT * with SELECT COUNT(*)
$count_sql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql_base);
$count_query = mysqli_query($conn, $count_sql);
$total_posts = mysqli_fetch_assoc($count_query)['total'];
$pages = ceil($total_posts / $limit);

// --- SIDEBAR QUERIES ---
// 1. Recent/Popular Posts (Just taking latest 4 for now)
$recent_sql = "SELECT * FROM blogs ORDER BY created_at DESC LIMIT 4";
$recent_result = mysqli_query($conn, $recent_sql);

// 2. Categories (Get distinct categories used in DB)
$cat_sql = "SELECT DISTINCT category FROM blogs WHERE category IS NOT NULL AND category != '' LIMIT 10";
$cat_result = mysqli_query($conn, $cat_sql);

// Helper function for images
function getBlogImg($path) {
    if (!empty($path)) {
        // Adjust this path if your admin folder is named differently
        return 'admin/' . $path; 
    }
    return 'images/slideshow/slide-1.jpg'; // Fallback image
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
    <div class="impx-page-heading uk-position-relative blog" style="margin-top: -142px; height: 180px; padding-top: 200px;">
        <div class="impx-overlay dark"></div>
        <div class="uk-container">
            <div class="uk-width-1-1">
                <div class="uk-flex uk-flex-left">
                    <div class="uk-light uk-position-relative uk-text-left page-title" style="margin-top: -150px;">
                        <h1 class="uk-margin-remove">Blog</h1><p class="impx-text-large uk-margin-remove">Our Latest News</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-padding uk-padding-remove-horizontal">
        <div class="uk-container">
            <div data-uk-grid>

                <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-1-1@s uk-margin-top impx-margin-top-small">

                    <?php 
                    if(mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $id = $row['id'];

                            $cmt_sql = "SELECT COUNT(*) as total FROM blog_comments WHERE blog_id = $id";
                            $cmt_res = mysqli_query($conn, $cmt_sql);
                            $cmt_data = mysqli_fetch_assoc($cmt_res);
                            $comment_count = $cmt_data['total'];

                            $title = htmlspecialchars($row['title']);
                            $date = date('d F Y', strtotime($row['created_at']));
                            $author = htmlspecialchars($row['author'] ?? 'Admin');
                            $category = htmlspecialchars($row['category'] ?? 'General');
                            $image = getBlogImg($row['featured_image']);
                            $excerpt = $row['excerpt'];
                            
                            // If excerpt is empty, cut the content
                            if(empty($excerpt)){
                                $excerpt = substr(strip_tags($row['content']), 0, 150) . '...';
                            }
                    ?>

                    <div class="uk-card uk-card-default uk-margin-medium-bottom impx-article">
                        <div class="uk-card-body impx-padding-medium">
                            <article class="uk-article impx-article-item">
                                <h3 class="uk-article-title">
                                    <a class="uk-link-reset" href="single-post.php?id=<?= $id ?>"><?= $title ?></a>
                                </h3>

                                <p class="uk-article-meta uk-margin-remove-top">
                                    <i class="fa fa-user"></i> <a href="#" class="impx-text-aqua"><?= $author ?></a> | 
                                    <i class="fa fa-calendar"></i> <?= $date ?> | 
                                    <i class="fa fa-tags"></i> <a href="#" class="impx-text-aqua"><?= $category ?></a> |
                                    <i class="fa fa-commenting"></i> <a href="single-post.php?id=<?= $id ?>#impx-comments" class="impx-text-aqua"><?= $comment_count ?> Comments</a>
                                </p>

                                <a class="uk-link-reset" href="single-post.php?id=<?= $id ?>">
                                    <img src="<?= $image ?>" class="uk-margin-small-bottom" alt="<?= $title ?>" style="width:100%; max-height: 400px; object-fit:cover;">
                                </a>

                                <p class="uk-dropcap">
                                    <?= $excerpt ?> 
                                    <a class="uk-button uk-button-text impx-text-aqua" href="single-post.php?id=<?= $id ?>">...Read more »</a>
                                </p>

                            </article>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <div class="uk-alert-warning" uk-alert>
                            <p>No blog posts found.</p>
                        </div>
                    <?php endif; ?>

                    <?php 
                        // Build URL query string to keep filters active during pagination
                        $query_params = $_GET; // Copy current URL params (category=Food, etc.)
                        unset($query_params['page']); // Remove old page number
                        $base_url = '?' . http_build_query($query_params) . '&page='; // Rebuild URL: ?category=Food&page=
                    ?>

                    <?php if($pages > 1): ?>
                    <ul class="uk-pagination uk-flex-center impx-pagination" data-uk-margin>
                        
                        <?php if($page > 1): ?>
                            <li><a href="<?= $base_url . ($page - 1) ?>"><span data-uk-pagination-previous></span></a></li>
                        <?php endif; ?>

                        <?php for($i=1; $i<=$pages; $i++): ?>
                            <li class="<?= ($i == $page) ? 'uk-active' : '' ?>">
                                <?php if($i == $page): ?>
                                    <span><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= $base_url . $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <?php if($page < $pages): ?>
                            <li><a href="<?= $base_url . ($page + 1) ?>"><span data-uk-pagination-next></span></a></li>
                        <?php endif; ?>
                    </ul>
                    <?php endif; ?>
                    </div>
                <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-3@m uk-width-1-1@s  uk-margin-top">
                    
                    <div class="impx-sidebar uk-margin-medium-bottom">
                        <form class="uk-search uk-search-default" action="blog.php" method="GET">
                            <a href="#" class="uk-search-icon-flip" data-uk-search-icon></a>
                            <input class="uk-search-input" type="search" name="search" placeholder="Search...">
                        </form>
                    </div>
                    <div class="impx-sidebar uk-margin-large-bottom">
                        <h4 class="uk-heading-line uk-heading-bullet"><span>Recent Posts</span></h4>
                        <ul class="impx-popular-news uk-list uk-list-divider uk-list-large">
                            <?php while($r_post = mysqli_fetch_assoc($recent_result)): ?>
                            <li>
                                <div class="uk-grid-small" data-uk-grid>
                                    <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-3@m uk-width-1-6@s">
                                        <div class="impx-popular-thumb">
                                            <a href="single-post.php?id=<?= $r_post['id'] ?>">
                                                <img src="<?= getBlogImg($r_post['featured_image']) ?>" alt="Thumbnail" style="height: 60px; width: 100%; object-fit: cover;"/>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-5-6@s">
                                        <h6 class="uk-margin-small-bottom">
                                            <a href="single-post.php?id=<?= $r_post['id'] ?>" class="uk-link-reset"><?= htmlspecialchars($r_post['title']) ?></a>
                                        </h6>
                                        <ul class="impx-post-meta">
                                            <li><i class="fa fa-calendar-o"></i> <?= date('M d, Y', strtotime($r_post['created_at'])) ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <div class="impx-sidebar uk-margin-large-bottom">
                        <h4 class="uk-heading-line uk-heading-bullet"><span>Categories</span></h4>
                        <ul class="uk-list uk-list-divider impx-cat-list">
                            <li><a href="blog.php">All Categories</a></li>
                            <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                                <li>
                                    <a href="blog.php?category=<?= urlencode($cat['category']) ?>">
                                        <?= htmlspecialchars($cat['category']) ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
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
    <script src="js/jquery.parallax.min.js"></script>
    <script src="js/template-config.js"></script>
</body>

</html>