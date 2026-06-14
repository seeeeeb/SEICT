student <?php
require_once __DIR__ . '/../auth.php';
check_auth(['student']);

$allPosts = load_posts();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$totalPages = max(1, (int) ceil(count($allPosts) / $perPage));
$posts = array_slice($allPosts, ($page - 1) * $perPage, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SEICT Portal</title>
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
                    <h2 style="color: var(--primary-maroon); margin: 0; font-weight: 800; font-size: 2rem;">Student Academic Workspace</h2>
                    <p class="text-muted" style="margin: 4px 0 0 0;">Access your grades, manage course tracking submissions, and review peer updates.</p>
                </div>
                <a href="../ActionController.php?action=logout" class="btn-maroon" style="text-decoration: none; padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px;">
                    <i class="fas fa-sign-out-alt"></i> Secure Logout
                </a>
            </div>

            <div class="feed-layout">
                <div class="feed-main-stream">
                    <div class="widget-frame" style="margin-bottom: 24px; padding: 20px;">
                        <label class="composer-label" style="margin-bottom: 12px; font-weight: 700;">SEICT Community Feed</label>
                        <div class="composer-form">
                            <textarea class="composer-input" rows="3" style="resize: none; padding: 12px; border-radius: 8px; background: #f8fafc;" placeholder="Student posting is reserved for a future moderation workflow." disabled></textarea>
                            <div class="composer-actions" style="justify-content: flex-end; margin-top: 12px;">
                                <button class="btn-maroon btn-post" style="padding: 10px 24px; border-radius: 8px; font-weight: 600;" disabled>
                                    <i class="fas fa-paper-plane"></i> Publish
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($posts)): ?>
                        <div class="feed-post-card">
                            <div class="post-header">
                                <div class="post-avatar">SC</div>
                                <div class="post-meta">
                                    <h4>SEICT Student Council</h4>
                                    <span><i class="fas fa-globe-asia"></i> Feed ready <span class="role-badge">System</span></span>
                                </div>
                            </div>
                            <div class="post-body">No announcements, events, or competitions have been published yet.</div>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($posts as $post): ?>
                        <?php
                            $role = ucfirst((string) ($post['author_role'] ?? 'Admin'));
                            $initials = strtoupper(substr((string) ($post['author'] ?? 'SEICT'), 0, 2));
                            $content = (string) ($post['content'] ?? '');
                            $preview = mb_strlen($content) > 420 ? mb_substr($content, 0, 420) . '...' : $content;

                            $categoryRaw = (string) ($post['category'] ?? '');

                            // Be tolerant to category variants (e.g., "Event", "Events", "Campus Events").
                            $category = (string) ($post['category'] ?? '');
                            $categoryNormalized = preg_replace('/\s+/', '', $category);
                            $isEvent = stripos($categoryNormalized, 'event') !== false;
                            $postId = $post['id'] ?? ''; // Ensure $postId is defined
                            $joined = $isEvent && $postId !== '' && user_is_joined_event($postId, (int) ($_SESSION['user_id'] ?? 0));
                        ?>
                        <article class="feed-post-card">
                            <div class="post-header">
                                <div class="post-avatar"><?php echo e($initials); ?></div>
                                <div class="post-meta">
                                    <h4><?php echo e($post['title'] ?? 'SEICT Update'); ?></h4>
                                    <span>
                                        <i class="fas fa-globe-asia"></i>
                                        <?php echo e($post['author'] ?? 'SSC'); ?>
                                        <span class="role-badge"><?php echo e($role); ?></span>
                                        &bull; <?php echo e(ucfirst((string) ($post['category'] ?? 'Announcement'))); ?>
                                        &bull; <?php echo e(date('M d, Y h:i A', strtotime((string) ($post['created_at'] ?? 'now')))); ?>
                                        <?php echo !empty($post['pinned']) ? ' &bull; Pinned' : ''; ?>
                                        <?php if (!empty($post['deadline'])): ?>
                                            &bull; Deadline: <?php echo e(date('M d, Y', strtotime((string) ($post['deadline'])))); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="post-body"><?php echo nl2br(e($preview)); ?></div>

                            <?php if (!empty($post['attachment'])): ?>
                                <img src="../<?php echo e($post['attachment']); ?>" alt="Post attachment" class="feed-image img-fluid">
                            <?php endif; ?>
                                <?php $isEvent = isset($isEvent) ? $isEvent : false; ?>
                                <?php $isEvent = $isEvent ?? false; ?>
                            <div class="post-actions-bar">
                                <?php if ($isEvent): ?>
                                    <?php if ($joined): ?>
                                        <form method="post" action="../ActionController.php" style="margin: 0; flex: 1;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="event_unjoin">
                                            <input type="hidden" name="post_id" value="<?php echo e($postId); ?>">
                                            <button class="feed-action-btn" type="submit" style="color: var(--primary-maroon);">
                                                <i class="fas fa-calendar-check"></i> Joined
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="../ActionController.php" style="margin: 0; flex: 1;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="event_join">
                                            <input type="hidden" name="post_id" value="<?php echo e($postId); ?>">
                                            <button class="feed-action-btn" type="submit" style="color: var(--primary-maroon);">
                                                <i class="fas fa-calendar-plus"></i> Join Event
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="feed-action-btn" type="button"><i class="far fa-thumbs-up"></i> Like</button>
                                    <button class="feed-action-btn" type="button"><i class="far fa-comment"></i> Comment</button>
                                    <button class="feed-action-btn" type="button"><i class="fas fa-share"></i> Share</button>
                                <?php endif; ?>
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
                            <i class="fas fa-bolt" style="color: #eab308; margin-right: 6px;"></i> Trending Updates
                        </h3>
                        <?php foreach (['#SEICTAnnouncements', '#CampusEvents', '#Competitions'] as $tag): ?>
                            <div class="trending-item">
                                <i class="fas fa-hashtag trending-icon"></i>
                                <div>
                                    <a href="#" style="text-decoration: none; color: var(--text-primary); font-weight: 700; font-size: 0.9rem; display: block;"><?php echo e($tag); ?></a>
                                    <span class="text-muted" style="font-size: 0.8rem;">Latest department feed category</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="right-sidebar-widget" style="background: linear-gradient(to bottom right, #fff, #fafafa);">
                        <h3 class="widget-title" style="font-size: 1.1rem; margin-bottom: 12px;">Upcoming Events</h3>
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 14px; line-height: 1.4;">Announcements, events, and competitions are merged into the main feed and sorted by newest first.</p>
                        <a href="#events" class="btn-maroon" style="text-decoration: none; display: block; text-align: center; font-size: 0.85rem; padding: 10px; border-radius: 6px;">View Feed</a>
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