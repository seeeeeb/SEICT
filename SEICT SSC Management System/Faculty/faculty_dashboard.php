<?php
require_once __DIR__ . '/../auth.php';
check_auth(['faculty']);

$posts = load_posts();
$recent_posts = array_slice($posts, 0, 5);
$facultyPosts = array_values(array_filter($posts, static fn ($post) => ($post['author_role'] ?? '') === 'faculty'));
$adminPosts = array_values(array_filter($posts, static fn ($post) => ($post['author_role'] ?? '') === 'admin'));
$eventPosts = array_values(array_filter($posts, static fn ($post) => in_array(strtolower((string) ($post['category'] ?? '')), ['event', 'events'], true)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal Dashboard - SEICT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/portal-styles.css">
    <style>
        .text-maroon { color: var(--primary-maroon); }
        .bg-maroon-light { background-color: var(--primary-maroon-light); }
        .portal-card { border: 1px solid rgba(16, 24, 40, 0.08); border-radius: var(--radius-md); box-shadow: var(--card-shadow); background: #fff; }
        .feed-card { border: 1px solid rgba(16, 24, 40, 0.08); border-radius: var(--radius-md); background: #fff; padding: 24px; margin-bottom: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .feed-card:hover { transform: translateY(-2px); box-shadow: var(--card-shadow); }
        .course-item, .pending-action-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; background: #fafafa; border-radius: var(--radius-sm); border: 1px solid rgba(16, 24, 40, 0.04); margin-bottom: 12px; }
        .course-badge, .action-badge { font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase; white-space: nowrap; }
        .course-badge { background: var(--primary-maroon-light); color: var(--primary-maroon); border: 1px solid rgba(128,0,0,0.15); }
        .badge-due { background: rgba(198, 40, 40, 0.08); color: #b42318; border: 1px solid rgba(198, 40, 40, 0.15); }
        .badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-review { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .post-media { max-height: 400px; width: 100%; object-fit: cover; border-radius: var(--radius-sm); margin-top: 15px; border: 1px solid rgba(0,0,0,0.05); }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <header>
        <div class="header-container">
            <img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo rounded" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>S E I C T</h1>
                <p>SCHOOL OF ENGINEERING INFORMATION, COMMUNICATION & TECHNOLOGY - FACULTY PORTAL</p>
            </div>
        </div>
    </header>

    <main class="container-fluid flex-grow-1 py-4 px-md-4">
        <div class="row g-4">
            <nav class="col-12 col-xl-3">
                <div class="portal-card p-3 h-100">
                    <?php require_once __DIR__ . '/../sidebar_menu.php'; ?>
                </div>
            </nav>

            <div class="col-12 col-xl-9">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                    <div>
                        <h2 class="text-maroon fw-extrabold mb-1 h3">Welcome, Prof. <?php echo e($_SESSION['username'] ?? 'Faculty Member'); ?></h2>
                        <p class="text-muted small mb-0">Coordinate academic rosters, deploy stream feeds, and access course communication tracks.</p>
                    </div>
                    <a href="../ActionController.php?action=logout" class="back-link d-inline-flex align-items-center gap-2 border border-danger border-opacity-15 px-4 py-2 rounded-3 bg-white text-decoration-none">
                        <i class="fas fa-sign-out-alt"></i> Secure Logout
                    </a>
                </div>

                <?php if (isset($_GET['post_success'])): ?>
                    <div class="status-msg success-msg shadow-sm d-flex align-items-center gap-2 mb-4 p-3 border-0">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span><strong>Broadcast Complete:</strong> Your update was synchronized directly into the student terminal matrix feed layout.</span>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <?php foreach ([
                        ['Teaching Broadcasts', count($facultyPosts), 'fas fa-bullhorn'],
                        ['Events Involved', count($eventPosts), 'fas fa-calendar-alt'],
                        ['Active Departments', 3, 'fas fa-building'],
                        ['Latest Activity', count($recent_posts), 'fas fa-chart-line'],
                    ] as $metric): ?>
                        <div class="col-12 col-md-6 col-xxl-3">
                            <div class="portal-card p-4 h-100">
                                <h3 class="h6 text-maroon fw-bold mb-2"><i class="<?php echo e($metric[2]); ?> me-2"></i><?php echo e($metric[0]); ?></h3>
                                <div style="font-size: 2rem; font-weight: 800; font-family: monospace;"><?php echo e($metric[1]); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="portal-card p-4 h-100">
                            <h3 class="h5 text-maroon fw-bold mb-3"><i class="fas fa-graduation-cap me-2"></i>Course Summary Array</h3>
                            <div class="course-item"><div><strong class="text-dark d-block small">Active Class Assignments</strong><span class="text-muted" style="font-size: 0.8rem;">Current academic terms</span></div><span class="course-badge">4 Sections</span></div>
                            <div class="course-item"><div><strong class="text-dark d-block small">Student Engagement Placeholder</strong><span class="text-muted" style="font-size: 0.8rem;">Ready for attendance and response tracking</span></div><span class="course-badge">Queued</span></div>
                            <div class="course-item mb-0"><div><strong class="text-dark d-block small">File Access Module</strong><span class="text-muted" style="font-size: 0.8rem;">Templates, documents, and class resources</span></div><span class="course-badge">Online</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="portal-card p-4 h-100">
                            <h3 class="h5 text-maroon fw-bold mb-3"><i class="fas fa-bell me-2"></i>Notification Center</h3>
                            <div class="pending-action-item"><div class="min-w-0"><strong class="text-dark d-block small text-truncate">Announcement Categories</strong><span class="text-muted" style="font-size: 0.8rem;">Announcements, events, competitions, and reminders</span></div><span class="action-badge badge-review">Ready</span></div>
                            <div class="pending-action-item"><div class="min-w-0"><strong class="text-dark d-block small text-truncate">Pinned Posts</strong><span class="text-muted" style="font-size: 0.8rem;">Mark priority updates when composing</span></div><span class="action-badge badge-pending">Available</span></div>
                            <div class="pending-action-item mb-0"><div class="min-w-0"><strong class="text-dark d-block small text-truncate">Analytics Cards</strong><span class="text-muted" style="font-size: 0.8rem;">Feed counts update without extra database calls</span></div><span class="action-badge badge-due">Live</span></div>
                        </div>
                    </div>
                </div>

                <div class="portal-card p-4 mb-4" id="announcements">
                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="h5 text-maroon fw-bold mb-1"><i class="fas fa-bullhorn me-2"></i>Faculty Broadcast Composer</h3>
                            <p class="text-muted small mb-0">Post announcements and academic updates to the SEICT feed.</p>
                        </div>
                    </div>

                    <form action="../process_post.php" method="post" enctype="multipart/form-data" class="composer-form">
                        <?php echo csrf_field(); ?>
                        <label class="composer-label" for="faculty-post-title">Title</label>
                        <input id="faculty-post-title" name="title" class="composer-input" maxlength="160" placeholder="Announcement title" required>
                        <label class="composer-label" for="faculty-post-category">Category</label>
                        <select id="faculty-post-category" name="category" class="composer-input">
                            <option value="announcement">Announcement</option>
                            <option value="event">Event</option>
                            <option value="competition">Competition</option>
                            <option value="reminder">Reminder</option>
                        </select>
                        <label class="composer-label" for="faculty-post-content">Announcement Content</label>
                        <textarea id="faculty-post-content" name="content" class="composer-input" rows="4" placeholder="Write a faculty announcement..." required></textarea>
                        <div class="composer-actions">
                            <input type="file" name="media" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="form-control">
                            <label class="d-inline-flex align-items-center gap-2 small fw-bold text-muted"><input type="checkbox" name="pinned" value="1"> Pinned</label>
                            <button type="submit" class="btn-maroon btn-post"><i class="fas fa-paper-plane"></i> Publish Update</button>
                        </div>
                    </form>
                </div>

                <div class="row g-4 mb-4" id="events">
                    <?php foreach ([
                        ['Upcoming Events', 'Registration windows and department activities queued for publication.'],
                        ['Ongoing Events', 'Active participation and event monitoring placeholder.'],
                        ['Completed Events', 'Post-event reports and engagement summaries placeholder.'],
                    ] as $eventCard): ?>
                        <div class="col-12 col-lg-4">
                            <div class="portal-card p-4 h-100">
                                <h3 class="h6 text-maroon fw-bold mb-2"><?php echo e($eventCard[0]); ?></h3>
                                <p class="text-muted small mb-0"><?php echo e($eventCard[1]); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="portal-card p-4">
                    <h3 class="h5 text-maroon fw-bold mb-3"><i class="fas fa-stream me-2"></i>Recent Portal Feed</h3>

                    <?php if (empty($recent_posts)): ?>
                        <div class="text-muted small">No recent posts are available yet.</div>
                    <?php endif; ?>

                    <?php foreach ($recent_posts as $post): ?>
                        <article class="feed-card">
                            <div class="feed-card-header">
                                <div class="min-w-0">
                                    <span class="feed-type"><?php echo e(ucfirst((string) ($post['category'] ?? 'Announcement'))); ?><?php echo !empty($post['pinned']) ? ' | Pinned' : ''; ?></span>
                                    <strong class="d-block text-truncate"><?php echo e($post['title'] ?? 'SEICT Update'); ?></strong>
                                    <span class="text-muted small"><?php echo e($post['author'] ?? 'SEICT'); ?> - <?php echo e(ucfirst((string) ($post['author_role'] ?? 'Admin'))); ?></span>
                                    <?php if (!empty($post['deadline'])): ?>
                                        <div class="text-muted small" style="margin-top: 4px;">
                                            <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i>
                                            Deadline: <?php echo e(date('M d, Y', strtotime((string) ($post['deadline'])))); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="feed-date"><?php echo e(date('M d, Y', strtotime((string) ($post['created_at'] ?? 'now')))); ?></span>
                            </div>
                            <p class="feed-content"><?php echo nl2br(e($post['content'] ?? '')); ?></p>
                            <?php if (!empty($post['attachment'])): ?>
                                <img src="../<?php echo e($post['attachment']); ?>" alt="Post media" class="post-media">
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

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
        <div class="footer-details d-flex justify-content-between flex-wrap" style="font-size: 0.85rem; opacity: 0.8;">
            <p>Don Toribio Street, Tetuan, Zamboanga City, 7000 Philippines | +63 917 894 6367 | universidaddezamboanga@uz.edu.ph</p>
            <p>&copy; <?php echo date('Y'); ?> Universidad de Zamboanga. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
