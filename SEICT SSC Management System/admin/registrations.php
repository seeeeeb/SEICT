<?php
require_once __DIR__ . '/../auth.php';
check_auth(['admin']);

$posts = load_posts();
$joins = load_event_joins();

// Build a set of event post IDs (only category = event/events)
$eventPosts = array_values(array_filter($posts, static function ($post) {
    $category = strtolower((string) ($post['category'] ?? ''));
    return in_array($category, ['event', 'events'], true);
}));

// Collect all joined user ids so we can map them to usernames/emails (if available)
$userIds = [];
foreach ($joins as $postId => $list) {
    if (!is_array($list)) continue;
    foreach ($list as $uid) {
        $uid = (int) $uid;
        if ($uid > 0) $userIds[$uid] = true;
    }
}
$userIdsList = array_keys($userIds);

// Map user_id => username (and email if present)
$userMap = [];
if (!empty($userIdsList) && isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    global $pdo;

    // Use only columns that exist
    $hasUsername = users_has_column('username');
    $hasEmail = users_has_column('email');

    $cols = [];
    if ($hasUsername) $cols[] = 'username';
    if ($hasEmail) $cols[] = 'email';

    // Always fetch id
    $selectCols = 'id' . ($cols ? ', ' . implode(', ', $cols) : '');

    $inPlaceholders = implode(',', array_fill(0, count($userIdsList), '?'));
    $sql = 'SELECT ' . $selectCols . ' FROM users WHERE id IN (' . $inPlaceholders . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($userIdsList);

    while ($row = $stmt->fetch()) {
        $uid = (int) ($row['id'] ?? 0);
        if ($uid <= 0) continue;
        $userMap[$uid] = $row;
    }
}

// Pagination is not necessary for admin view; keep simple.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registrations (Admin)</title>
    <link rel="stylesheet" href="../css/portal-styles.css">
    <style>
        .registrations-wrap { padding: 22px; }
        .page-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
        .page-title { color: var(--primary-maroon); font-weight: 900; font-size: 1.6rem; margin: 0; }
        .muted { color: var(--muted-text); font-size: 0.95rem; margin: 0; }
        .page-actions { display: flex; gap: 10px; align-items: center; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 1px solid rgba(16, 24, 40, 0.08); padding: 10px; vertical-align: top; }
        th { background: rgba(128, 0, 0, 0.06); text-align: left; font-weight: 800; color: var(--text-primary); }
        .card { background: #fff; border: 1px solid rgba(16, 24, 40, 0.08); border-radius: 12px; padding: 18px; box-shadow: 0 8px 22px rgba(16,24,40,0.04); }
        .section-title { margin: 22px 0 10px; color: #4a0000; font-size: 1.15rem; font-weight: 900; }
        .empty { padding: 18px; background: rgba(248, 250, 252, 0.9); border: 1px dashed rgba(128,0,0,0.25); border-radius: 12px; color: var(--text-primary); }
        .chip { display: inline-block; padding: 3px 10px; border-radius: 999px; background: rgba(128,0,0,0.08); color: var(--primary-maroon); border: 1px solid rgba(128,0,0,0.15); font-size: 0.9em; font-weight: 800; }
        .back-btn { text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <div class="registrations-wrap">
        <div class="page-head">
            <div>
                <h1 class="page-title">Event Registrations (Joined Events)</h1>
                <p class="muted">This shows students who joined events/posts. Data is stored in <code>data/events_joins.json</code>.</p>
            </div>
            <div class="page-actions">
                <a class="btn-maroon back-btn" href="../admin/admin_dashboard.php" role="button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>


    <?php if (empty($eventPosts)): ?>
        <div class="empty">No event posts found in the feed.</div>
    <?php else: ?>
        <?php foreach ($eventPosts as $event): ?>
            <?php
                $postId = (string) ($event['id'] ?? '');
                $title = (string) ($event['title'] ?? 'SEICT Event');
                $category = (string) ($event['category'] ?? 'event');
                $joinedUsers = isset($joins[$postId]) && is_array($joins[$postId]) ? $joins[$postId] : [];
            ?>

            <h2 style="margin: 22px 0 10px; color:#4a0000; font-size: 1.2rem;">
                <?php echo htmlspecialchars($title); ?>
                <span class="muted" style="font-weight: normal;">(<?php echo htmlspecialchars($category); ?>)</span>
            </h2>

            <?php if (empty($joinedUsers)): ?>
                <div class="empty">No one has joined this event yet.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>User ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($joinedUsers as $uid): ?>
                            <?php
                                $uidInt = (int) $uid;
                                $u = $userMap[$uidInt] ?? null;
                                $displayName = '';
                                if ($u) {
                                    $displayName = (string) ($u['username'] ?? '');
                                    if ($displayName === '' && isset($u['email'])) {
                                        $displayName = (string) $u['email'];
                                    }
                                }
                                if ($displayName === '') $displayName = 'User';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($displayName); ?></td>
                                <td><?php echo htmlspecialchars((string) $uidInt); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
