<?php
// Determine current file and role to assign active states dynamically.
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

$menus = [
    'admin' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'href' => 'admin_dashboard.php', 'page' => 'admin_dashboard.php'],
        ['label' => 'Users', 'icon' => 'fas fa-users-cog', 'href' => '#users'],
        ['label' => 'Departments', 'icon' => 'fas fa-building', 'href' => '#departments'],
        ['label' => 'Announcements', 'icon' => 'fas fa-bullhorn', 'href' => '#announcements'],
        ['label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'href' => '#events'],
        ['label' => 'Registrations', 'icon' => 'fas fa-clipboard-list', 'href' => '../admin/registrations.php', 'page' => 'registrations.php'],
        ['label' => 'Settings', 'icon' => 'fas fa-cog', 'href' => '#settings'],
    ],
    'faculty' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'href' => 'faculty_dashboard.php', 'page' => 'faculty_dashboard.php'],
        ['label' => 'Announcements', 'icon' => 'fas fa-bullhorn', 'href' => '#announcements'],
        ['label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'href' => '#events'],
        ['label' => 'Attendance', 'icon' => 'fas fa-user-check', 'href' => '#attendance'],
        ['label' => 'Profile', 'icon' => 'fas fa-user-circle', 'href' => '#profile'],
    ],
    'student' => [
        ['label' => 'Dashboard', 'icon' => 'fas fa-home', 'href' => 'student_dashboard.php', 'page' => 'student_dashboard.php'],
        ['label' => 'OJT', 'icon' => 'fas fa-briefcase', 'href' => '#ojt', 'page' => 'ojt.php'],
        ['label' => 'MENTORSHIP', 'icon' => 'fas fa-user-tie', 'href' => '#mentorship', 'page' => 'mentorship.php'],
        ['label' => 'Announcements', 'icon' => 'fas fa-bullhorn', 'href' => '#announcements'],
        ['label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'href' => '#events'],
        ['label' => 'My Registrations', 'icon' => 'fas fa-clipboard-list', 'href' => 'registration%20submit.php', 'page' => 'registration submit.php'],
        ['label' => 'Profile', 'icon' => 'fas fa-user-circle', 'href' => '#profile'],
        ['label' => 'Settings', 'icon' => 'fas fa-cog', 'href' => '#settings'],
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
