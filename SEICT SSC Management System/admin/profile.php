<?php
require_once __DIR__ . '/../auth.php';
check_auth(['admin']);

$userId = (int) ($_SESSION['user_id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, username, email, contact_number, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();
$profile = current_profile_data($userId);
$myPosts = user_posts_for_current_user($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Profile - SEICT</title>
  <link rel="stylesheet" href="../css/portal-styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .profile-grid{display:grid;grid-template-columns:1.1fr 0.9fr;gap:24px;align-items:start}.profile-card{background:#fff;border:1px solid rgba(16,24,40,0.08);border-radius:14px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,0.05)}.avatar{width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 8px 18px rgba(0,0,0,0.12)}.chip{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:999px;background:rgba(128,0,0,0.08);color:var(--primary-maroon);font-size:0.85rem;font-weight:700}.composer-input{width:100%;border:1px solid rgba(16,24,40,0.12);border-radius:10px;padding:10px 12px;background:#fff}.composer-label{display:block;font-size:0.9rem;font-weight:700;color:#374151;margin-bottom:6px}.composer-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:flex-end}.mini-btn{border:1px solid rgba(128,0,0,0.25);background:rgba(128,0,0,0.06);color:var(--primary-maroon);border-radius:8px;padding:8px 12px;font-weight:800;cursor:pointer;text-decoration:none}.mini-btn.danger{color:#b42318;border-color:rgba(180,35,24,0.25);background:rgba(180,35,24,0.06)}
  </style>
</head>
<body>
<header class="portal-header"><div class="header-container"><img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.style.display='none'"><div class="header-text"><h1>SEICT</h1><p>Admin Profile & Community Updates</p></div></div></header>
<div class="dashboard-container">
  <?php require_once __DIR__ . '/../sidebar_menu.php'; ?>
  <main class="main-viewport">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
      <div>
        <h2 class="text-maroon" style="margin:0;font-size:2rem;font-weight:800;">Admin Profile</h2>
        <p class="text-muted" style="margin:4px 0 0;">Update your profile image, contact details, and the posts you published.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="admin_dashboard.php" class="btn-maroon" style="text-decoration:none;padding:10px 18px;border-radius:8px;">Back to Dashboard</a>
        <a href="../ActionController.php?action=logout" class="btn-maroon" style="text-decoration:none;padding:10px 18px;border-radius:8px;">Secure Logout</a>
      </div>
    </div>
    <?php if (isset($_GET['profile_saved'])): ?><div class="pending-actions-widget" style="background:rgba(47,133,90,0.08);border-color:rgba(47,133,90,0.15);margin-bottom:16px;">Profile updated successfully.</div><?php endif; ?>
    <div class="profile-grid">
      <section class="profile-card">
        <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
          <img src="../<?php echo e($profile['image'] ?? 'images/default-avatar.png'); ?>" alt="Profile image" class="avatar" onerror="this.src='../images/default-avatar.png'" />
          <div>
            <div class="chip"><i class="fas fa-user-shield"></i> <?php echo e($user['role'] ?? 'admin'); ?></div>
            <h3 style="margin:8px 0 4px;font-size:1.4rem;"><?php echo e($profile['full_name'] ?? $user['username'] ?? 'Admin'); ?></h3>
            <p class="text-muted" style="margin:0;"><?php echo e($profile['course'] ?? 'Administrator'); ?> • <?php echo e($profile['year_level'] ?? 'SEICT'); ?></p>
          </div>
        </div>
        <hr />
        <form action="../ActionController.php" method="post" enctype="multipart/form-data" class="composer-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="save_profile" />
          <label class="composer-label" for="full_name">Full Name</label>
          <input id="full_name" class="composer-input" name="full_name" value="<?php echo e($profile['full_name'] ?? $user['username'] ?? ''); ?>" required />
          <label class="composer-label" for="course">Course / Department</label>
          <input id="course" class="composer-input" name="course" value="<?php echo e($profile['course'] ?? 'Administrator'); ?>" />
          <label class="composer-label" for="year_level">Year Level / Position</label>
          <input id="year_level" class="composer-input" name="year_level" value="<?php echo e($profile['year_level'] ?? 'SEICT'); ?>" />
          <label class="composer-label" for="bio">Short Bio</label>
          <textarea id="bio" class="composer-input" name="bio" rows="4" placeholder="Describe your role and updates."><?php echo e($profile['bio'] ?? ''); ?></textarea>
          <label class="composer-label" for="profile_image">Upload Profile Image</label>
          <input id="profile_image" class="composer-input" type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" />
          <div class="composer-actions" style="margin-top:12px;"><button class="btn-maroon" type="submit">Save Profile</button></div>
        </form>
      </section>
      <aside class="profile-card">
        <h3 class="widget-title" style="margin-top:0;">My Community Posts</h3>
        <?php if (empty($myPosts)): ?><p class="text-muted">You have not posted any community updates yet.</p><?php endif; ?>
        <?php foreach ($myPosts as $post): ?>
          <article style="border:1px solid rgba(16,24,40,0.08);border-radius:12px;padding:14px;background:#fafafa;margin-bottom:12px;">
            <strong><?php echo e($post['title'] ?? 'SEICT Update'); ?></strong>
            <p class="text-muted small" style="margin:6px 0;"><?php echo e($post['content'] ?? ''); ?></p>
            <form action="../ActionController.php" method="post" class="composer-form" style="display:grid;gap:8px;">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="edit_post" />
              <input type="hidden" name="post_id" value="<?php echo e($post['id'] ?? ''); ?>" />
              <input class="composer-input" name="title" value="<?php echo e($post['title'] ?? ''); ?>" />
              <textarea class="composer-input" name="content" rows="3"><?php echo e($post['content'] ?? ''); ?></textarea>
              <input class="composer-input" name="category" value="<?php echo e($post['category'] ?? 'announcement'); ?>" />
              <input class="composer-input" type="date" name="deadline" value="<?php echo e($post['deadline'] ?? ''); ?>" />
              <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button class="mini-btn" type="submit">Update</button>
              </div>
            </form>
            <form action="../ActionController.php" method="post" style="display:flex;justify-content:flex-end;margin-top:8px;">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete_post" />
              <input type="hidden" name="post_id" value="<?php echo e($post['id'] ?? ''); ?>" />
              <button class="mini-btn danger" type="submit" onclick="return confirm('Delete this post?');">Delete</button>
            </form>
          </article>
        <?php endforeach; ?>
      </aside>
    </div>
  </main>
</div>
</body></html>
