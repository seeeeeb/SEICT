<?php
require_once __DIR__ . '/../auth.php';
check_auth(['student']);

$profile = current_profile_data((int) ($_SESSION['user_id'] ?? 0));
$yearLevel = strtolower((string) ($profile['year_level'] ?? ''));
$eligible = in_array($yearLevel, ['3rd year', '4th year', 'third year', 'fourth year', '3rd yr', '4th yr'], true);
$deanList = !empty($profile['dean_list']) || !empty($profile['honor']) || !empty($profile['deans_list']);
$qualified = $eligible || $deanList;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mentorship Module - SEICT</title>
  <link rel="stylesheet" href="../css/portal-styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
<header class="portal-header"><div class="header-container"><img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.style.display='none'"><div class="header-text"><h1>SEICT</h1><p>Mentorship and Guidance Module</p></div></div></header>
<div class="dashboard-container">
  <?php include __DIR__ . '/../sidebar_menu.php'; ?>
  <main class="main-viewport">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
      <div>
        <h2 class="text-maroon" style="margin:0;font-size:2rem;font-weight:800;">Mentorship Module</h2>
        <p class="text-muted" style="margin:4px 0 0;">Track mentor sessions, academic guidance, and support progress.</p>
      </div>
      <a href="student_dashboard.php" class="btn-maroon" style="text-decoration:none;padding:10px 18px;border-radius:8px;">Back to Dashboard</a>
    </div>
    <?php if (!empty($_GET['success'])): ?><div class="pending-actions-widget" style="background:rgba(47,133,90,0.08);border-color:rgba(47,133,90,0.15);margin-bottom:18px;">Your mentorship application was submitted successfully.</div><?php endif; ?>
    <div class="portal-card" style="padding:24px;display:grid;gap:18px;">
      <article class="portal-card" style="padding:18px;background:#fffaf7;">
        <h3 class="widget-title" style="margin-top:0;">Peer-to-Peer Mentoring & Help Desk</h3>
        <p class="text-muted" style="margin:0;">A dedicated mentorship board for students who want academic guidance, career support, and peer mentoring.</p>
      </article>
      <article class="portal-card" style="padding:18px;">
        <h3 class="widget-title" style="margin-top:0;">Qualification Check</h3>
        <p class="text-muted" style="margin:0 0 8px;">Mentorship is open to 3rd and 4th year students, and also to students with dean’s list or other strong academic standing.</p>
        <div class="action-badge <?php echo $qualified ? 'service-badge-online' : 'badge-review'; ?>">Status: <?php echo $qualified ? 'Qualified for mentorship' : 'Not yet qualified'; ?></div>
      </article>
      <article class="portal-card" style="padding:18px;">
        <h3 class="widget-title" style="margin-top:0;">Apply for Mentorship</h3>
        <form action="../ActionController.php" method="post" class="composer-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="apply_mentorship">
          <label class="composer-label" for="interest">Why do you want mentorship?</label>
          <textarea id="interest" name="interest" class="composer-input" rows="4" placeholder="Share your academic goals, concerns, or field of interest." required></textarea>
          <div class="composer-actions"><button type="submit" class="btn-maroon" <?php echo $qualified ? '' : 'disabled'; ?>>Submit Mentorship Application</button></div>
        </form>
      </article>
    </div>
  </main>
</div>
</body>
</html>
