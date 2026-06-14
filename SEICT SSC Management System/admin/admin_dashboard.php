<?php
require_once __DIR__ . '/../auth.php';
check_auth(['admin']);

$totalUsers = (int) $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$totalStudents = (int) $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'student'")->fetch()['c'];
$totalFaculty = (int) $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'faculty'")->fetch()['c'];
$recentUsersStmt = $pdo->prepare("SELECT id, username, email, contact_number, role FROM users ORDER BY id DESC LIMIT 5");
$recentUsersStmt->execute();
$recentUsers = $recentUsersStmt->fetchAll();

$search = clean_string($_GET['search'] ?? '', 120);
$roleFilter = clean_string($_GET['role'] ?? '', 20);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(username LIKE ? OR email LIKE ? OR contact_number LIKE ?)';
    $needle = '%' . $search . '%';
    array_push($params, $needle, $needle, $needle);
}
if (in_array($roleFilter, ['admin', 'faculty', 'student'], true)) {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM users' . $whereSql);
$countStmt->execute($params);
$filteredUsers = (int) $countStmt->fetch()['c'];
$totalPages = max(1, (int) ceil($filteredUsers / $perPage));

$usersStmt = $pdo->prepare('SELECT id, username, email, contact_number, role FROM users' . $whereSql . ' ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
$usersStmt->execute($params);
$users = $usersStmt->fetchAll();

$posts = load_posts();
$postCount = count($posts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SEICT Portal</title>
    <link rel="stylesheet" href="../css/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .indicator-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .indicator-dot.online { background-color: #166534; box-shadow: 0 0 8px rgba(22, 101, 52, 0.6); }
        .action-badge { font-size: 11px; font-weight: 800; padding: 5px 10px; border-radius: 6px; letter-spacing: 0.5px; display: inline-block; text-align: center; width: auto; }
        .service-badge-online { background: rgba(47, 133, 90, 0.08); color: #166534; font-size: 10px; }
        .badge-review { background: rgba(198, 40, 40, 0.08); color: #b42318; border: 1px solid rgba(198, 40, 40, 0.15); }
        .data-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .data-table th { font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #666; padding: 14px 16px; border-bottom: 2px solid rgba(16, 24, 40, 0.06); background: #fafafa; }
        .data-table td { padding: 14px 16px; font-size: 0.95rem; border-bottom: 1px solid rgba(16, 24, 40, 0.06); vertical-align: top; }
        .data-table tbody tr:hover { background-color: #fdfdfd; }
        .inline-form { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
        .compact-input { border: 1px solid rgba(16, 24, 40, 0.12); border-radius: 6px; padding: 8px 10px; min-width: 130px; }
        .mini-button { border: 1px solid rgba(128,0,0,0.25); color: var(--primary-maroon); background: rgba(128,0,0,0.06); border-radius: 6px; padding: 8px 10px; font-weight: 800; cursor: pointer; }
        .mini-button.danger { color: #b42318; border-color: rgba(180,35,24,0.25); background: rgba(180,35,24,0.06); }
    </style>
</head>
<body>

    <header class="portal-header">
        <div class="header-container">
            <img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>SEICT</h1>
                <p>School Of Engineering, Information, Communication And Technology - Admin Portal</p>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <?php require_once __DIR__ . '/../sidebar_menu.php'; ?>

        <main class="main-viewport">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 class="text-maroon" style="margin: 0; font-weight: 800; font-size: 2rem;">System Admin Dashboard</h2>
                    <p class="text-muted" style="margin: 4px 0 0 0;">Welcome, <?php echo e($_SESSION['username'] ?? 'Admin'); ?> — manage announcements, events, and users.</p>
                </div>
                <a href="../ActionController.php?action=logout" class="btn-maroon" style="text-decoration: none; padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px;">
                    <i class="fas fa-sign-out-alt"></i> Secure Logout
                </a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="pending-actions-widget" style="background: rgba(47,133,90,0.08); border-color: rgba(47,133,90,0.15);"><strong>Success:</strong> <?php echo e($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="pending-actions-widget" style="background: rgba(198,40,40,0.08); border-color: rgba(198,40,40,0.15);"><strong>Action needed:</strong> <?php echo e($_GET['error']); ?></div>
            <?php endif; ?>

            <div class="pending-actions-widget" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(47, 133, 90, 0.08), rgba(47, 133, 90, 0.02)); border: 1px solid rgba(47, 133, 90, 0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px; color: #166534;">
                    <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                    <span style="color: var(--text-primary);"><strong>Operational Infrastructure:</strong> Authentication, user registry, session controls, and feed storage are online.</span>
                </div>
                <small class="text-muted" style="font-family: monospace; font-weight: 700; font-size: 11px; text-transform: uppercase;">Checked: Just now</small>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 24px;">
                <?php foreach ([
                    ['Total Users', $totalUsers, 'fas fa-users'],
                    ['Total Students', $totalStudents, 'fas fa-user-graduate'],
                    ['Total Faculty', $totalFaculty, 'fas fa-chalkboard-teacher'],
                    ['Feed Posts', $postCount, 'fas fa-bullhorn'],
                ] as $card): ?>
                    <div class="portal-card" style="padding: 24px; display: flex; flex-direction: column; gap: 12px;">
                        <h4 class="text-muted" style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 0.05em;"><i class="<?php echo e($card[2]); ?>" style="margin-right: 8px;"></i><?php echo e($card[0]); ?></h4>
                        <span style="font-size: 2rem; font-weight: 800; font-family: monospace; color: var(--text-primary);"><?php echo e($card[1]); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
                <div class="portal-card" style="padding: 24px;" id="announcements">
                    <h3 class="widget-title" style="margin-bottom: 16px;"><i class="fas fa-bullhorn" style="margin-right: 8px;"></i> Announcements Publisher</h3>
                    <form action="../process_post.php" method="post" enctype="multipart/form-data" class="composer-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="category" value="announcement">

                        <label class="composer-label" for="post-title">Post Title</label>
                        <input id="post-title" name="title" class="composer-input" maxlength="160" placeholder="Announcement title" required>

                        <label class="composer-label" for="post-content">Post Content</label>
                        <textarea id="post-content" name="content" class="composer-input" rows="4" placeholder="Write an update for the student feed..." required></textarea>

                        <label class="composer-label" for="post-deadline">Deadline (optional)</label>
                        <input id="post-deadline" name="deadline" class="composer-input compact-input" type="date">

                        <div class="composer-actions">
                            <input type="file" name="media" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="compact-input" style="flex: 1 1 220px;">
                            <button type="submit" class="btn-maroon btn-post"><i class="fas fa-paper-plane"></i> Publish Announcement</button>
                        </div>
                    </form>
                </div>

                <div class="portal-card" style="padding: 24px;" id="events">
                    <h3 class="widget-title" style="margin-bottom: 16px;"><i class="fas fa-calendar-alt" style="margin-right: 8px;"></i> Events Publisher</h3>
                    <form action="../process_post.php" method="post" enctype="multipart/form-data" class="composer-form">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="category" value="events">

                        <label class="composer-label" for="event-title">Event Title</label>
                        <input id="event-title" name="title" class="composer-input" maxlength="160" placeholder="Event title" required>

                        <label class="composer-label" for="event-content">Event Content</label>
                        <textarea id="event-content" name="content" class="composer-input" rows="4" placeholder="Write event details for students..." required></textarea>

                        <label class="composer-label" for="event-deadline">Registration Deadline (optional)</label>
                        <input id="event-deadline" name="deadline" class="composer-input compact-input" type="date">

                        <div class="composer-actions">
                            <input type="file" name="media" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="compact-input" style="flex: 1 1 220px;">
                            <button type="submit" class="btn-maroon btn-post"><i class="fas fa-paper-plane"></i> Publish Event</button>
                        </div>
                    </form>
                </div>

                <div class="portal-card" style="padding: 24px;">
                    <h3 class="widget-title" style="margin-bottom: 16px;"><i class="fas fa-stream" style="margin-right: 8px;"></i> Recent User Registry</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($recentUsers as $user): ?>
                            <div style="padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid rgba(16, 24, 40, 0.06); background: rgba(248, 250, 252, 0.6); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                <div style="min-width: 0;">
                                    <strong style="font-size: 0.9rem;"><?php echo e($user['username']); ?></strong>
                                    <span class="text-muted" style="display: block; font-size: 0.8rem;"><?php echo e($user['email']); ?></span>
                                </div>
                                <span class="action-badge service-badge-online"><?php echo e($user['role']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="portal-card" id="users" style="overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid rgba(16, 24, 40, 0.06); background: #fff;">
                    <h3 class="widget-title" style="margin: 0;"><i class="fas fa-users-cog" style="margin-right: 8px;"></i> Account Registry & Identity Management</h3>
                </div>

                <div style="padding: 20px 24px; display: grid; grid-template-columns: 1fr; gap: 16px;">
                    <form method="get" action="admin_dashboard.php" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <input class="compact-input" type="search" name="search" value="<?php echo e($search); ?>" placeholder="Search users">
                        <select class="compact-input" name="role">
                            <option value="">All roles</option>
                            <?php foreach (['admin', 'faculty', 'student'] as $role): ?>
                                <option value="<?php echo e($role); ?>" <?php echo $roleFilter === $role ? 'selected' : ''; ?>><?php echo e(ucfirst($role)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="mini-button" type="submit">Filter</button>
                    </form>

                    <form action="../ActionController.php" method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="admin_create_user">
                        <input class="compact-input" name="username" placeholder="Username" required>
                        <input class="compact-input" name="email" type="email" placeholder="Email" required>
                        <input class="compact-input" name="contact_number" placeholder="Contact Number" required>
                        <select class="compact-input" name="role" required>
                            <option value="faculty">Faculty</option>
                            <option value="student">Student</option>
                        </select>
                        <input class="compact-input" name="password" type="password" minlength="8" placeholder="Password" required>
                        <input class="compact-input" name="confirm_password" type="password" minlength="8" placeholder="Confirm" required>
                        <button class="btn-maroon" type="submit">Create User</button>
                    </form>
                </div>

                <div style="overflow-x: auto; width: 100%;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User Principal Name</th>
                                <th>Email / Phone</th>
                                <th>Scope Privilege Profile</th>
                                <th>Status Flag</th>
                                <th style="text-align: right;">Operation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong><?php echo e($user['username']); ?></strong><br><span class="text-muted" style="font-size: 0.8rem;">USR-<?php echo e(str_pad((string) $user['id'], 4, '0', STR_PAD_LEFT)); ?></span></td>
                                    <td><?php echo e($user['email']); ?><br><span class="text-muted" style="font-size: 0.8rem;"><?php echo e($user['contact_number']); ?></span></td>
                                    <td><span style="font-weight: 700; color: var(--primary-maroon); font-size: 0.88rem;"><?php echo e(ucfirst($user['role'])); ?></span></td>
                                    <td><span class="action-badge service-badge-online">Active</span></td>
                                    <td style="text-align: right;">
                                        <form action="../ActionController.php" method="post" class="inline-form" style="margin-bottom: 8px;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="admin_update_user">
                                            <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                                            <input class="compact-input" name="username" value="<?php echo e($user['username']); ?>" required>
                                            <input class="compact-input" name="email" type="email" value="<?php echo e($user['email']); ?>" required>
                                            <input class="compact-input" name="contact_number" value="<?php echo e($user['contact_number']); ?>" required>
                                            <select class="compact-input" name="role">
                                                <?php foreach (['admin', 'faculty', 'student'] as $role): ?>
                                                    <option value="<?php echo e($role); ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>><?php echo e(ucfirst($role)); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="mini-button" type="submit">Save</button>
                                        </form>
                                        <form action="../ActionController.php" method="post" class="inline-form">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                                            <input class="compact-input" name="password" type="password" minlength="8" placeholder="New password">
                                            <button class="mini-button" name="action" value="admin_reset_password" type="submit">Reset</button>
                                            <?php if (users_has_column('is_active') || users_has_column('status')): ?>
                                                <button class="mini-button" name="action" value="admin_deactivate_user" type="submit">Deactivate</button>
                                            <?php endif; ?>
                                            <button class="mini-button danger" name="action" value="admin_delete_user" type="submit" onclick="return confirm('Delete this non-admin user?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span class="text-muted" style="font-size: 0.85rem;">Showing <?php echo e(count($users)); ?> of <?php echo e($filteredUsers); ?> users</span>
                    <div style="display: flex; gap: 8px;">
                        <?php if ($page > 1): ?>
                            <a class="mini-button" href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&page=<?php echo $page - 1; ?>#users">Previous</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a class="mini-button" href="?search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&page=<?php echo $page + 1; ?>#users">Next</a>
                        <?php endif; ?>
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
