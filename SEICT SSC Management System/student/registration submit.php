<?php
require_once __DIR__ . '/../auth.php';
check_auth(['student']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$allPosts = load_posts();
$eventJoins = load_event_joins();

// Filter posts to show only events the student has joined
$joinedPosts = array_filter($allPosts, static function ($post) use ($userId, $eventJoins) {
    $postId = (string) ($post['id'] ?? '');
    $category = strtolower((string) ($post['category'] ?? ''));
    $isEvent = in_array($category, ['event', 'events'], true);
    
    if (!$isEvent || $postId === '') {
        return false;
    }
    
    // Check if user has joined this event
    $joinedUsers = isset($eventJoins[$postId]) && is_array($eventJoins[$postId]) ? $eventJoins[$postId] : [];
    return in_array((string) $userId, array_map(static fn($v) => (string) $v, $joinedUsers), true);
});

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$totalPages = max(1, (int) ceil(count($joinedPosts) / $perPage));
$posts = array_slice($joinedPosts, ($page - 1) * $perPage, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Joined Events - SEICT Portal</title>
    <link rel="stylesheet" href="../css/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .feed-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 24px; align-items: start; }
        @media (max-width: 992px) { .feed-layout { grid-template-columns: 1fr; } }
        .feed-post-card { background: #fff; border: 1px solid rgba(16, 24, 40, 0.08); border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: transform 0.16s ease, box-shadow 0.16s ease; }
        .feed-post-card:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(16, 24, 40, 0.10); }
        .post-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; min-width: 0; }
        .post-avatar { width: 42px; height: 42px; border-radius: 50%; background: var(--primary-maroon); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; flex: 0 0 42px; }
        .post-meta { min-width: 0; }
        .post-meta h4 { margin: 0; font-size: 0.98rem; font-weight: 800; color: var(--text-primary); overflow-wrap: anywhere; }
        .post-meta span { font-size: 0.8rem; color: var(--muted-text); display: block; margin-top: 2px; }
        .role-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 8px; font-size: 0.72rem; font-weight: 800; background: rgba(128,0,0,0.08); color: var(--primary-maroon); border: 1px solid rgba(128,0,0,0.15); margin-left: 4px; }
        .post-body { font-size: 0.95rem; color: #374151; line-height: 1.55; margin-bottom: 16px; overflow-wrap: anywhere; }
        .post-actions-bar { display: flex; border-top: 1px solid rgba(16, 24, 40, 0.06); padding-top: 12px; gap: 8px; }
        .feed-action-btn { flex: 1; background: none; border: none; padding: 8px; border-radius: 6px; color: #4b5563; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s ease; }
        .feed-action-btn:hover { background: #f3f4f6; color: var(--primary-maroon); }
        .right-sidebar-widget { background: #fff; border: 1px solid rgba(16, 24, 40, 0.08); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .trending-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(16, 24, 40, 0.06); }
        .trending-item:last-child { border-bottom: none; padding-bottom: 0; }
        .trending-icon { color: var(--primary-maroon); font-size: 1.1rem; margin-top: 2px; }
        .feed-image { width: 100%; max-height: 460px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(16,24,40,0.08); margin: 4px 0 16px; }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state-icon { font-size: 3rem; color: var(--primary-maroon); margin-bottom: 20px; opacity: 0.3; }
        .empty-state h3 { color: var(--text-primary); margin-bottom: 10px; font-size: 1.3rem; }
        .empty-state p { color: var(--muted-text); margin-bottom: 20px; }
        .btn-back-to-feed { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
    </style>
</head>
<body>

    <header class="portal-header">
        <div class="header-container">
            <img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>SEICT</h1>
                <p>School Of Engineering Information, Communication And Technology - Student Portal</p>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <?php include __DIR__ . '/../sidebar_menu.php'; ?>

        <main class="main-viewport">
            <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div>
                    <h2 style="color: var(--primary-maroon); margin: 0; font-weight: 800; font-size: 2rem;">
                        <i class="fas fa-calendar-check" style="margin-right: 12px;"></i>My Joined Events
                    </h2>
                    <p class="text-muted" style="margin: 4px 0 0 0;">View and manage all the events you have joined.</p>
                </div>
                <a href="student_dashboard.php" class="btn-maroon" style="text-decoration: none; padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Feed
                </a>
            </div>

            <div class="feed-layout">
                <div class="feed-main-stream">
                    <?php if (empty($posts)): ?>
                        <div class="feed-post-card empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <h3>No Joined Events</h3>
                            <p>You haven't joined any events yet. Explore events in the main feed and join the ones you're interested in!</p>
                            <a href="student_dashboard.php" class="btn-maroon btn-back-to-feed">
                                <i class="fas fa-arrow-left"></i> Browse Events
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($posts as $post): ?>
                        <?php
                            $role = ucfirst((string) ($post['author_role'] ?? 'Admin'));
                            $initials = strtoupper(substr((string) ($post['author'] ?? 'SEICT'), 0, 2));
                            $content = (string) ($post['content'] ?? '');
                            $preview = mb_strlen($content) > 420 ? mb_substr($content, 0, 420) . '...' : $content;
                            $postId = (string) ($post['id'] ?? '');
                        ?>
                        <article class="feed-post-card">
                            <div class="post-header">
                                <div class="post-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                <div class="post-meta">
                                    <h4><?php echo htmlspecialchars($post['title'] ?? 'SEICT Event'); ?></h4>
                                    <span>
                                        <i class="fas fa-globe-asia"></i>
                                        <?php echo e($post['author'] ?? 'SSC'); ?>
                                        <span class="role-badge"><?php echo e($role); ?></span>
                                        &bull; Event
                                        &bull; <?php echo htmlspecialchars(date('M d, Y h:i A', strtotime((string) ($post['created_at'] ?? 'now')))); ?>
                                        <?php if (!empty($post['deadline'])): ?>
                                            <br><strong style="color: var(--primary-maroon);">Deadline: <?php echo e(date('M d, Y', strtotime((string) ($post['deadline'])))); ?></strong>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="post-body"><?php echo nl2br(e($preview)); ?></div>

                            <?php if (!empty($post['attachment'])): ?>
                                <img src="../<?php echo e($post['attachment']); ?>" alt="Event image" class="feed-image img-fluid">
                            <?php endif; ?>

                            <div class="post-actions-bar">
                                <form method="post" action="../ActionController.php" style="margin: 0; flex: 1;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="event_unjoin">
                                    <input type="hidden" name="post_id" value="<?php echo e($postId); ?>">
                                    <button class="feed-action-btn" type="submit" style="color: var(--primary-maroon);">
                                        <i class="fas fa-calendar-check"></i> Joined - Leave Event
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php if ($totalPages > 1): ?>
                        <div style="display: flex; justify-content: center; gap: 10px; margin: 20px 0;">
                            <?php if ($page > 1): ?>
                                <a class="btn-maroon" style="text-decoration: none;" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a class="btn-maroon" style="text-decoration: none;" href="?page=<?php echo $page + 1; ?>">Load More</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="feed-sidebar-extras">
                    <div class="right-sidebar-widget">
                        <h3 class="widget-title" style="font-size: 1.1rem; margin-bottom: 16px;">
                            <i class="fas fa-info-circle" style="color: #3b82f6; margin-right: 6px;"></i> Event Stats
                        </h3>
                        <div style="padding: 12px 0; border-bottom: 1px solid rgba(16, 24, 40, 0.06);">
                            <div style="font-size: 0.85rem; color: var(--muted-text); margin-bottom: 4px;">Total Events Joined</div>
                            <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary-maroon);"><?php echo count($joinedPosts); ?></div>
                        </div>
                        <div style="padding: 12px 0;">
                            <p class="text-muted" style="font-size: 0.85rem; margin: 0;">Keep track of all your event registrations in one place. Click "Leave Event" to unregister.</p>
                        </div>
                    </div>

                    <div class="right-sidebar-widget" style="background: linear-gradient(to bottom right, #fff, #fafafa);">
                        <h3 class="widget-title" style="font-size: 1.1rem; margin-bottom: 12px;">Quick Actions</h3>
                        <a href="student_dashboard.php" class="btn-maroon" style="text-decoration: none; display: block; text-align: center; font-size: 0.85rem; padding: 10px; border-radius: 6px; margin-bottom: 8px;">
                            <i class="fas fa-newspaper"></i> Explore Feed
                        </a>
                        <a href="../ActionController.php?action=logout" class="btn-maroon" style="text-decoration: none; display: block; text-align: center; font-size: 0.85rem; padding: 10px; border-radius: 6px;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="portal-footer">
        <div class="footer-top-line">
            <span style="font-weight: 700;">SSC SEICT Management System</span>
            <div class="footer-socials">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-details" style="display: flex; justify-content: space-between; flex-wrap: wrap; font-size: 0.85rem; opacity: 0.8;">
            <p>Don Toribio Street, Tetuan, Zamboanga City, 7000 Philippines | +63 917 894 6367 | universidaddezamboanga@uz.edu.ph</p>
            <p>&copy; <?php echo date('Y'); ?> Universidad de Zamboanga. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

