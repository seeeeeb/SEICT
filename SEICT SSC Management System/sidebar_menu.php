<?php
// Determine current file and role to assign active states dynamically.
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

$menus = [
    'admin' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'href' => 'admin_dashboard.php', 'page' => 'admin_dashboard.php'],
        ['label' => 'Profile', 'icon' => 'fas fa-user-circle', 'href' => 'profile.php', 'page' => 'profile.php'],
        ['label' => 'Users', 'icon' => 'fas fa-users-cog', 'href' => 'admin_dashboard.php#users'],
        ['label' => 'Departments', 'icon' => 'fas fa-building', 'href' => 'admin_dashboard.php#departments'],
        ['label' => 'Announcements', 'icon' => 'fas fa-bullhorn', 'href' => 'admin_dashboard.php#announcements'],
        ['label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'href' => 'admin_dashboard.php#events'],
        ['label' => 'Registrations', 'icon' => 'fas fa-clipboard-list', 'href' => '../admin/registrations.php', 'page' => 'registrations.php'],
        ['label' => 'Settings', 'icon' => 'fas fa-cog', 'href' => 'profile.php'],
    ],
    'faculty' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'href' => 'faculty_dashboard.php', 'page' => 'faculty_dashboard.php'],
        ['label' => 'Profile', 'icon' => 'fas fa-user-circle', 'href' => 'profile.php', 'page' => 'profile.php'],
        ['label' => 'Announcements', 'icon' => 'fas fa-bullhorn', 'href' => 'faculty_dashboard.php#announcements'],
        ['label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'href' => 'faculty_dashboard.php#events'],
        ['label' => 'Attendance', 'icon' => 'fas fa-user-check', 'href' => 'faculty_dashboard.php#attendance'],
        ['label' => 'Settings', 'icon' => 'fas fa-cog', 'href' => 'profile.php'],
    ],
    'student' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'href' => 'student_dashboard.php', 'page' => 'student_dashboard.php'],
        ['label' => 'Profile', 'icon' => 'fas fa-user-circle', 'href' => 'profile.php', 'page' => 'profile.php'],
        ['label' => 'OJT', 'icon' => 'fas fa-briefcase', 'href' => 'ojt.php', 'page' => 'ojt.php'],
        ['label' => 'MENTORSHIP', 'icon' => 'fas fa-user-tie', 'href' => 'mentorship.php', 'page' => 'mentorship.php'],
        ['label' => 'Announcements', 'icon' => 'fas fa-bullhorn', 'href' => 'student_dashboard.php#announcements'],
        ['label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'href' => 'student_dashboard.php#events'],
        ['label' => 'My Registrations', 'icon' => 'fas fa-clipboard-list', 'href' => 'registration%20submit.php', 'page' => 'registration submit.php'],
        ['label' => 'Settings', 'icon' => 'fas fa-cog', 'href' => 'profile.php'],
    ],
];

$items = $menus[$role] ?? $menus['student'];
?>
<aside class="sidebar">
    <div class="sidebar-nav">
        <div class="nav-section-title">Navigation Modules</div>

        <?php foreach ($items as $item): ?>
            <?php
                $page = $item['page'] ?? '';
                // Normalize comparison for URLs that include spaces like "registration submit.php".
                $normalizedCurrent = $current_page;
                $normalizedPage = str_replace('%20', ' ', $page);
                $normalizedCurrent = str_replace('%20', ' ', $normalizedCurrent);

                $is_active = $normalizedPage !== '' && $normalizedPage === $normalizedCurrent;
            ?>
            <a href="<?php echo htmlspecialchars($item['href']); ?>" class="nav-item <?php echo $is_active ? 'active' : ''; ?>" title="<?php echo htmlspecialchars($item['label']); ?>">
                <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</aside>
