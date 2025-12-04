<?php
// Connect to database
if(file_exists('admin/db.php')){
    include 'admin/db.php';
} elseif (file_exists('db.php')){
    include 'db.php';
} else {
    die("Database file not found");
}

// 1. GET POST ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: blog.php");
    exit;
}

$id = intval($_GET['id']);

// 2. FETCH MAIN POST
$sql = "SELECT * FROM blogs WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "Post not found.";
    exit;
}

$post = mysqli_fetch_assoc($result);

// Prepare variables
$title = htmlspecialchars($post['title']);
$content = $post['content']; // WYSIWYG content usually has HTML, so we don't escape it
$date = date('d F Y', strtotime($post['created_at']));
$author = htmlspecialchars($post['author'] ?? 'Admin');
$category = htmlspecialchars($post['category'] ?? 'General');

// Image Helper
function getBlogImg($path) {
    if (!empty($path)) {
        return 'admin/' . $path; 
    }
    return 'images/slideshow/slide-1.jpg'; // Fallback
}

$main_image = getBlogImg($post['featured_image']);

// 3. FETCH RELATED POSTS (Random 2 excluding current)
$related_sql = "SELECT * FROM blogs WHERE id != $id ORDER BY RAND() LIMIT 2";
$related_result = mysqli_query($conn, $related_sql);

// 4. SIDEBAR QUERIES
$recent_sidebar_sql = "SELECT * FROM blogs ORDER BY created_at DESC LIMIT 4";
$recent_sidebar_result = mysqli_query($conn, $recent_sidebar_sql);

$cat_sql = "SELECT DISTINCT category FROM blogs WHERE category IS NOT NULL AND category != '' LIMIT 10";
$cat_result = mysqli_query($conn, $cat_sql);

// 1. Fetch Comments for this post
$comment_sql = "SELECT * FROM blog_comments WHERE blog_id = $id ORDER BY created_at DESC";
$comment_result = mysqli_query($conn, $comment_sql);
$comment_count = mysqli_num_rows($comment_result);

$cnt_sql = "SELECT COUNT(*) as total FROM blog_comments WHERE blog_id = $id";
    $cnt_res = mysqli_query($conn, $cnt_sql);
    $cnt_row = mysqli_fetch_assoc($cnt_res);
    $total_comments = $cnt_row['total'];
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
    
    <!-- HEADER -->
    <header id="impx-header">
        <div>
            <div class="impx-menu-wrapper style2 hp2" data-uk-sticky="top: 0; animation: uk-animation-slide-top">

                <!-- Mobile Nav Start -->
                <?php include 'includes/moblileNav.php'; ?>
                <!-- Mobile Nav End -->

                <!-- Top Header -->
                
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
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- HEADER END -->

    <!-- PAGE HEADING -->
    <!-- Applied transparent header fix -->
    <div class="impx-page-heading uk-position-relative blog" style="margin-top: -142px; height: 180px; padding-top: 200px;">
        <div class="impx-overlay dark"></div>
        <div class="uk-container">
            <div class="uk-width-1-1">
                <div class="uk-flex uk-flex-left">
                    <div class="uk-light uk-position-relative uk-text-left page-title" style="margin-top: -150px;">
                        <h1 class="uk-margin-remove">Blog</h1><!-- page title -->
                        <p class="impx-text-large uk-margin-remove">Our Latest News</p><!-- page subtitle -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- PAGE HEADING END -->

    <!-- CONTENT -->
    <div class="uk-padding uk-padding-remove-horizontal">
        <div class="uk-container">
            <div data-uk-grid>

                <!-- MAIN CONTENT -->
                <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-1-1@s uk-margin-top  impx-margin-top-small">

                    <div class="uk-card uk-card-default uk-margin-medium-bottom impx-article">
                        <div class="uk-card-body impx-padding-medium impx-article-item">
                            <article class="uk-article">
                                <!-- Title -->
                                <h3 class="uk-article-title"><?= $title ?></h3>

                                <!-- Meta -->
                                <p class="uk-article-meta">
                                    <i class="fa fa-user"></i> <a href="#" class="impx-text-aqua"><?= $author ?></a> | 
                                    <i class="fa fa-calendar"></i> <?= $date ?>. | 
                                    <i class="fa fa-tags"></i> <a href="#" class="impx-text-aqua"><?= $category ?></a> |
                                    <i class="fa fa-commenting"></i> <a href="#impx-comments" class="impx-text-aqua" data-uk-scroll><?= $total_comments ?> Comments</a>
                                </p>

                                <!-- Featured Image -->
                                <img src="<?= $main_image ?>" class="uk-margin-small-bottom" alt="<?= $title ?>" style="width:100%;">

                                <!-- Content -->
                                <div class="uk-dropcap mt-3">
                                    <?= $content ?>
                                </div>

                            </article><!-- article end -->
                        </div>
                    </div>

                    <!-- RELATED POSTS -->
                    <div class="uk-margin-medium-bottom impx-related-posts">
                        <h4 class="uk-margin-bottom uk-heading-line uk-heading-bullet"><span>Related Posts</span></h4>
                        <ul class="uk-child-width-1-2@xl uk-child-width-1-2@l uk-child-width-1-2@m uk-child-width-1-2@s impx-related-news" data-uk-grid>
                            
                            <?php while($rel = mysqli_fetch_assoc($related_result)): ?>
                            <li>
                                <div class="uk-grid-small" data-uk-grid>
                                    <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-3@m uk-width-1-4@s">
                                        <div class="impx-popular-thumb">
                                            <a href="single-post.php?id=<?= $rel['id'] ?>">
                                                <img src="<?= getBlogImg($rel['featured_image']) ?>" alt="Thumbnail" style="height:60px; object-fit:cover;" />
                                            </a>
                                        </div>
                                    </div>
                                    <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-3-4@s">
                                        <h6 class="uk-margin-remove-bottom">
                                            <a href="single-post.php?id=<?= $rel['id'] ?>" class="uk-link-reset"><?= htmlspecialchars($rel['title']) ?></a>
                                        </h6>
                                        <ul class="impx-post-meta">
                                            <li><i class="fa fa-calendar-o"></i> <?= date('M d, Y', strtotime($rel['created_at'])) ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <?php endwhile; ?>

                        </ul>
                    </div>
                    <!-- related posts end -->

                    <!-- COMMENTS (Static Placeholder for now) -->
                    
                    <div class="impx-comments-section" id="impx-comments">
                        <div class="uk-card uk-card-default uk-margin-medium-bottom impx-article">
                            <div class="uk-card-body impx-padding-medium impx-article-item">

                                <h4 class="uk-margin-medium-bottom uk-heading-line uk-heading-bullet">
                                    <span>Comments (<?= $comment_count ?>)</span>
                                </h4>

                                <?php if ($comment_count > 0): ?>
                                    <ul class="uk-comment-list">
                                        <?php while ($comment = mysqli_fetch_assoc($comment_result)): 
                                            $c_name = htmlspecialchars($comment['name']);
                                            $c_date = date('d M Y, h:i A', strtotime($comment['created_at']));
                                            $c_body = htmlspecialchars($comment['comment']);
                                            // Generate a dynamic avatar using the name (optional trick)
                                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($c_name) . "&background=random&color=fff";
                                        ?>
                                        <li>
                                            <article class="uk-comment">
                                                <header class="uk-comment-header uk-grid-medium uk-flex-middle" data-uk-grid>
                                                    <div class="uk-width-auto">
                                                        <img class="uk-comment-avatar" src="<?= $avatar_url ?>" width="80" height="80" alt="<?= $c_name ?>">
                                                    </div>
                                                    <div class="uk-width-expand">
                                                        <h4 class="uk-comment-title uk-margin-remove">
                                                            <a class="uk-link-reset" href="#"><?= $c_name ?></a>
                                                        </h4>
                                                        <ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">
                                                            <li><a href="#"><?= $c_date ?></a></li>
                                                        </ul>
                                                    </div>
                                                </header>
                                                <div class="uk-comment-body">
                                                    <p><?= $c_body ?></p>
                                                </div>
                                            </article>
                                        </li>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No comments yet. Be the first to leave a reply!</p>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <!-- comments end -->

                    <!-- COMMENT FORM (Static Placeholder) -->
                    <div class="uk-card uk-card-default uk-margin-bottom impx-comment-form impx-article">
                        <div class="uk-card-body impx-padding-medium impx-article-item">
                            <h4 class="uk-margin-medium-bottom uk-heading-line uk-heading-bullet"><span>Add Your Comment</span></h4>
                            
                            <form action="blog_comment_submit.php" method="POST">
                                <fieldset class="uk-fieldset">
                                    
                                    <input type="hidden" name="blog_id" value="<?= $id ?>">

                                    <div class="uk-margin">
                                        <input class="uk-input" type="text" name="name" placeholder="Name" required>
                                    </div>

                                    <div class="uk-margin">
                                        <input class="uk-input" type="email" name="email" placeholder="Email" required>
                                    </div>

                                    <div class="uk-margin">
                                        <textarea class="uk-textarea" rows="5" name="comment" placeholder="Comment" required></textarea>
                                    </div>

                                    <div class="uk-margin">
                                        <button class="uk-button impx-button aqua" type="submit">Submit</button>
                                    </div>

                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <!-- comment form end -->

                </div>
                <!-- MAIN CONTENT END -->

                <!-- SIDEBAR -->
                <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-3@m uk-width-1-1@s uk-margin-top">
                    
                    <!-- Search Widget -->
                    <div class="impx-sidebar uk-margin-medium-bottom">
                        <form class="uk-search uk-search-default" action="blog.php" method="GET">
                            <a href="#" class="uk-search-icon-flip" data-uk-search-icon></a>
                            <input class="uk-search-input" type="search" name="search" placeholder="Search...">
                        </form>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div class="impx-sidebar uk-margin-large-bottom">
                        <h4 class="uk-heading-line uk-heading-bullet"><span>Recent Posts</span></h4>
                        <ul class="impx-popular-news uk-list uk-list-divider uk-list-large">
                            <?php while($sidebar_post = mysqli_fetch_assoc($recent_sidebar_result)): ?>
                            <li>
                                <div class="uk-grid-small" data-uk-grid>
                                    <div class="uk-width-1-3@xl uk-width-1-3@l uk-width-1-3@m uk-width-1-6@s">
                                        <div class="impx-popular-thumb">
                                            <a href="single-post.php?id=<?= $sidebar_post['id'] ?>">
                                                <img src="<?= getBlogImg($sidebar_post['featured_image']) ?>" alt="Thumbnail" style="height:60px; object-fit:cover;" />
                                            </a>
                                        </div>
                                    </div>
                                    <div class="uk-width-2-3@xl uk-width-2-3@l uk-width-2-3@m uk-width-5-6@s">
                                        <h6 class="uk-margin-small-bottom">
                                            <a href="single-post.php?id=<?= $sidebar_post['id'] ?>" class="uk-link-reset"><?= htmlspecialchars($sidebar_post['title']) ?></a>
                                        </h6>
                                        <ul class="impx-post-meta">
                                            <li><i class="fa fa-calendar-o"></i> <?= date('M d, Y', strtotime($sidebar_post['created_at'])) ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- Categories Widget -->
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
                <!-- SIDEBAR END -->

            </div>
        </div>
    </div>                      
    <!-- CONTENT END -->

    <!-- CONTACT INFO -->
    <?php include 'includes/contact.php'; ?>
    <!-- CONTACT INFO END -->

    <!-- FOOTER -->
    <footer id="impx-footer" class="uk-padding uk-padding-remove-bottom uk-padding-remove-horizontal">
        <?php include 'includes/footer.php'; ?>

        <!-- Scroll to Top -->
        <a href="#top" class="to-top fa fa-long-arrow-up" data-uk-scroll></a>
    </footer>
    <!-- FOOTER END -->

    <!-- Javascript -->
    <script src="js/jquery.js"></script>
    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyBGb3xrNtz335X4G2KfoOXb-XuIyHAzlVo"></script>
    <script src="js/jquery.gmap.min.js"></script>
    <script src="js/jquery.parallax.min.js"></script>
    <script src="js/template-config.js"></script>
</body>

</html>