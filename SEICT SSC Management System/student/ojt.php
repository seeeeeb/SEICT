<?php
require_once __DIR__ . '/../auth.php';
check_auth(['student']);

$profile = current_profile_data((int) ($_SESSION['user_id'] ?? 0));
$yearLevel = strtolower((string) ($profile['year_level'] ?? ''));
$eligible = in_array($yearLevel, ['3rd year', '4th year', 'third year', 'fourth year', '3rd yr', '4th yr'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OJT Module - SEICT</title>
  <link rel="stylesheet" href="../css/portal-styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
<header class="portal-header"><div class="header-container"><img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.style.display='none'"><div class="header-text"><h1>SEICT</h1><p>On-the-Job Training Module</p></div></div></header>
<div class="dashboard-container">
  <?php include __DIR__ . '/../sidebar_menu.php'; ?>
  <main class="main-viewport">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
      <div>
        <h2 class="text-maroon" style="margin:0;font-size:2rem;font-weight:800;">OJT Module</h2>
        <p class="text-muted" style="margin:4px 0 0;">Track onboarding tasks, training logs, and required documents in one place.</p>
      </div>
      <a href="student_dashboard.php" class="btn-maroon" style="text-decoration:none;padding:10px 18px;border-radius:8px;">Back to Dashboard</a>
    </div>
    <?php if (!empty($_GET['success'])): ?><div class="pending-actions-widget" style="background:rgba(47,133,90,0.08);border-color:rgba(47,133,90,0.15);margin-bottom:18px;">Your OJT application was submitted successfully.</div><?php endif; ?>
    <div class="portal-card" style="padding:24px;display:grid;gap:18px;">
      <article class="portal-card" style="padding:18px;background:#fffaf7;">
        <h3 class="widget-title" style="margin-top:0;">OJT & Internship Nexus</h3>
        <p class="text-muted" style="margin:0;">The OJT & Internship Nexus is a dedicated job board connecting SEICT students with local tech companies for internships and jobs.</p>
      </article>
      <article class="portal-card" style="padding:18px;">
        <h3 class="widget-title" style="margin-top:0;">Eligibility</h3>
        <p class="text-muted" style="margin:0 0 8px;">You are currently eligible for OJT applications if your profile shows 3rd or 4th year standing.</p>
        <div class="action-badge <?php echo $eligible ? 'service-badge-online' : 'badge-review'; ?>">Status: <?php echo $eligible ? 'Eligible' : 'Profile needs 3rd/4th year standing'; ?></div>
      </article>
      <article class="portal-card" style="padding:18px;">
        <h3 class="widget-title" style="margin-top:0;">Apply for OJT</h3>
        <form action="../ActionController.php" method="post" class="composer-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="apply_ojt">
          <label class="composer-label" for="preferred_company">Preferred Company / Partner</label>
          <input id="preferred_company" name="preferred_company" class="composer-input" placeholder="e.g. Accenture, Globe, etc." required>
          <label class="composer-label" for="skills">Relevant Skills</label>
          <textarea id="skills" name="skills" class="composer-input" rows="4" placeholder="List your technical or communication strengths." required></textarea>
          <div class="composer-actions"><button type="submit" class="btn-maroon" <?php echo $eligible ? '' : 'disabled'; ?>>Submit OJT Application</button></div>
        </form>
      </article>
    </div>
  </main>
</div>
</body>
</html>
