<?php
require_once __DIR__ . '/../auth.php';
$showRegister = (($_GET['view'] ?? '') === 'register');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Login - SEICT</title>
    <link rel="stylesheet" href="../css/portal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="min-height: 100vh; display: flex; flex-direction: column; background: #fcfcfc;">

    <header class="portal-header">
        <div class="header-container">
            <img src="../images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>SEICT</h1>
                <p>School Of Engineering Information, Communication And Technology - Student Portal</p>
            </div>
        </div>
    </header>

    <main style="flex: 1 0 auto; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div class="portal-card" style="width: 100%; max-width: 480px; padding: 40px 35px; background: #fff; text-align: center;">
            <h2 style="color: var(--primary-maroon); font-weight: 900; font-size: 1.8rem; margin: 0 0 28px 0; letter-spacing: -0.02em;"><?php echo $showRegister ? 'Student Registration' : 'Student Portal Login'; ?></h2>

            <?php if (!$showRegister): ?>
                <form action="../auth.php?type=student" method="POST" style="text-align: left; display: flex; flex-direction: column; gap: 20px;">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Username, Email, or Phone</label>
                        <input type="text" name="username" required class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="Enter student identification">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Password</label>
                        <input type="password" name="password" required class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="Password">
                    </div>

                    <button type="submit" class="btn-maroon" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: 8px; margin-top: 10px;">
                        Login
                    </button>
                </form>
            <?php else: ?>
            <form action="../ActionController.php" method="POST" style="text-align: left; display: flex; flex-direction: column; gap: 16px;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="student_register">


                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Username</label>
                        <input type="text" name="username" required class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="Choose a username">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Email</label>
                        <input type="email" name="email" required class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="student@example.com">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Phone Number</label>
                        <input type="tel" name="phone_number" required class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="Phone number">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Password</label>
                        <input type="password" name="password" required minlength="8" class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="At least 8 characters">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; color: #374151;">Confirm Password</label>
                        <input type="password" name="confirm_password" required minlength="8" class="composer-input" style="border-radius: 8px; padding: 12px 14px; background: #edf2f7; border: 1px solid rgba(16, 24, 40, 0.08);" placeholder="Confirm password">
                    </div>

                    <button type="submit" class="btn-maroon" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: 8px; margin-top: 10px;">
                        Create Student Account
                    </button>
                </form>
            <?php endif; ?>

            <div style="margin-top: 24px; display: flex; justify-content: center; gap: 10px; font-size: 0.88rem; color: var(--muted-text); font-weight: 600;">
                <a href="#" style="color: var(--primary-maroon); text-decoration: none;">Forgot Password?</a>
                <span>|</span>
                <a href="<?php echo $showRegister ? 'student login.php' : 'student login.php?view=register'; ?>" style="color: var(--primary-maroon); text-decoration: none;"><?php echo $showRegister ? 'Back to Login' : 'Create New Account'; ?></a>
            </div>

            <div style="margin-top: 16px;">
                <a href="../index.php" style="color: var(--muted-text); text-decoration: none; font-size: 0.88rem; font-weight: 600;">Back to Main Portal</a>
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
        <div class="footer-details" style="display: flex; justify-content: space-between; flex-wrap: wrap; font-size: 0.85rem; opacity: 0.8;">
            <p>Don Toribio Street, Tetuan, Zamboanga City, 7000 Philippines | +63 917 894 6367 | universidaddezamboanga@uz.edu.ph</p>
            <p>&copy; 2026 Universidad de Zamboanga. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
