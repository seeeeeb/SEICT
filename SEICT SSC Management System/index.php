
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSC SEICT - Portal Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ==========================================================================
           GLOBAL DESIGN TOKENS
           ========================================================================== */
        :root {
            --primary-maroon: #800000;
            --primary-maroon-dark: #4a0000;
            --primary-maroon-light: rgba(128, 0, 0, 0.10);
            --navy-sidebar: #0a192f;
            --light-grey-bg: #f4f5f7;
            --dark-footer-bg: #0d131f;

            --text-light: #ffffff;
            --muted-text-light: #e2e8f0;

            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            --focus-ring: 0 0 0 3px rgba(128, 0, 0, 0.4);

            --radius-sm: 10px;
            --radius-md: 12px;

            --header-height: 80px;
            --footer-height: 95px; /* Fixed height tracking for footer gap prevention */
        }

        * { box-sizing: border-box; }
        
        html, body { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
            overflow-x: hidden;
            background: var(--light-grey-bg);
        }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
        }

        a { color: inherit; text-decoration: none; }
        a:focus, button:focus { outline: none; box-shadow: var(--focus-ring); }

        /* ==========================================================================
           HEADER STYLING (Fixed to Top - No Scroll Gaps)
           ========================================================================== */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: var(--header-height);
            z-index: 1050; 
            background: var(--primary-maroon);
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.2);
        }

        .header-container {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-logo {
            height: 52px;
            width: auto;
            object-fit: contain;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .header-text h1 {
            margin: 0;
            font-weight: 800;
            font-size: 1.6rem;
            line-height: 1.15;
            letter-spacing: 1px;
        }

        .header-text p {
            margin: 2px 0 0 0;
            opacity: 0.9;
            font-size: 0.88rem;
            line-height: 1.3;
            text-transform: capitalize;
        }

        /* ==========================================================================
           MAIN CONTENT Area (Fills exact viewport remainder safely)
           ========================================================================== */
        main {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Padding values match header and footer heights precisely to avoid card clipping */
            padding-top: calc(var(--header-height) + 40px);
            padding-bottom: calc(var(--footer-height) + 40px);
            padding-left: 16px;
            padding-right: 16px;
            background: var(--light-grey-bg);
            min-height: 100vh;
        }

        .portal-container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
        }

        .portal-heading-group {
            margin-bottom: 45px;
            text-align: center;
        }

        .portal-title {
            font-weight: 900;
            letter-spacing: 0.02em;
            color: var(--primary-maroon);
            font-size: clamp(1.8rem, 4.5vw, 2.5rem);
            margin-bottom: 10px;
        }

        .portal-subtitle {
            color: #4b5563;
            font-size: clamp(0.95rem, 1.8vw, 1.1rem);
            margin: 0;
        }

        .portal-grid {
            display: grid;
            gap: 28px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .portal-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 992px) {
            .portal-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        /* The UZ Portal Card Look: Dark shadow background, clear text overlay */
        .portal-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: var(--radius-md);
            padding: 30px 25px;
            min-height: 260px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: var(--card-shadow);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
        }

        /* Image base layer visible at full opacity */
        .portal-card-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -2;
            transition: transform 0.4s ease;
        }

        /* Dark shadow overlay covering the entire card image for contrast */
        .portal-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.8) 100%);
            z-index: -1;
            transition: background 0.25s ease;
        }

        /* Hover Transitions */
        .portal-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .portal-card:hover .portal-card-bg {
            transform: scale(1.05);
        }

        /* Deepens shadow on hover */
        .portal-card:hover::after {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.9) 100%);
        }

        /* White typography and icons to balance the dark image card background */
        .portal-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-sm);
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .portal-card h3 {
            margin: 0 0 8px 0;
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--text-light);
        }

        .portal-card p {
            margin: 0;
            color: var(--muted-text-light);
            line-height: 1.5;
            font-size: 0.92rem;
            opacity: 0.9;
        }

        .portal-cta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            color: var(--text-light);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .portal-cta .arrow {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.22s ease, background 0.22s ease;
        }

        .portal-card:hover .portal-cta .arrow {
            transform: translateX(6px);
            background: #ffffff;
        }

        .portal-card:hover .portal-cta .arrow svg path {
            stroke: #000000; 
        }

        /* ==========================================================================
           FOOTER STYLING (Fixed to Bottom - Absolute Grid Boundary Control)
           ========================================================================== */
        .portal-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: var(--footer-height);
            z-index: 1050;
            background: var(--dark-footer-bg);
            color: rgba(255,255,255,0.7);
            padding: 15px 30px;
            font-size: 0.9rem;
            box-shadow: 0 -3px 15px rgba(0,0,0,0.2);
        }

        .footer-top-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }

        .footer-link {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-socials {
            display: flex;
            gap: 16px;
        }

        .footer-socials a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.2s;
        }
        
        .footer-socials a:hover {
            color: #fff;
        }

        .footer-details p {
            margin: 0;
            font-size: 0.85rem;
        }

        /* Mobile Screen Constraints */
        @media (max-width: 600px) {
            header { padding: 0 16px; }
            .header-logo { height: 44px; }
            .header-text h1 { font-size: 1.35rem; }
            .header-text p { font-size: 0.78rem; }
            main { 
                padding-top: calc(var(--header-height) + 20px); 
                padding-bottom: calc(var(--footer-height) + 20px); 
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-container">
            <img src="images/seict-logo.jpg" alt="SEICT Logo" class="header-logo" onerror="this.src='https://placehold.co/100x100'">
            <div class="header-text">
                <h1><b>SEICT</b></h1>
                <p>school of engineering, information, communication and technology</p>
            </div>
        </div>
    </header>

    <main>
        <div class="portal-container">
            <div class="portal-heading-group">
                <div class="portal-title">Select Gateway Portal</div>
                <div class="portal-subtitle">Access your specialized administrative dashboard and learning records repository</div>
            </div>

            <div class="portal-grid" aria-label="Portal Selection System Matrix">
                
                <a href="admin/SSC login.php" class="portal-card" aria-label="Open Admin access panel">
                    <div class="portal-card-bg" style="background-image: url('images/admin-bg.jpg');"></div>
                    <div>
                        <div class="portal-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2l8 4v6c0 5-3.5 9.7-8 10-4.5-.3-8-5-8-10V6l8-4z" stroke="#ffffff" stroke-width="2"/>
                                <path d="M9.2 12.2l1.8 1.8 3.9-4.2" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Admin</h3>
                        <p>Platform Analytics, security parameters configuration, and resource audits engine.</p>
                    </div>
                    <div class="portal-cta">
                        <span>Enter Gateway</span>
                        <span class="arrow" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12h12" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M13 6l6 6-6 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </a>

                <a href="Faculty/Faculty login.php" class="portal-card" aria-label="Open Faculty access panel">
                    <div class="portal-card-bg" style="background-image: url('images/faculty-bg.jpg');"></div>
                    <div>
                        <div class="portal-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 8l9-5 9 5-9 5-9-5z" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M7 10v6c0 1.5 2.7 3 5 3s5-1.5 5-3v-6" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h3>Faculty</h3>
                        <p>Course curriculum generation, student grades entries, and academic submission controls.</p>
                    </div>
                    <div class="portal-cta">
                        <span>Enter Gateway</span>
                        <span class="arrow" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12h12" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M13 6l6 6-6 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </a>

                <a href="student/student login.php" class="portal-card" aria-label="Open Student access panel">
                    <div class="portal-card-bg" style="background-image: url('images/students-bg.jpg');"></div>
                    <div>
                        <div class="portal-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21a8 8 0 0 0-16 0" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                                <path d="M12 13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z" stroke="#ffffff" stroke-width="2"/>
                            </svg>
                        </div>
                        <h3>Student</h3>
                        <p>Access your personalized community feed, enrollment forms, and digital resource vaults.</p>
                    </div>
                    <div class="portal-cta">
                        <span>Enter Gateway</span>
                        <span class="arrow" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 12h12" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/>
                                <path d="M13 6l6 6-6 6" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </a>

            </div>
        </div>
    </main>

    <footer class="portal-footer">
        <div class="footer-top-line">
            <a href="#" class="footer-link">SSC SEICT Management System</a>
            <div class="footer-socials">
                <a href="#" aria-label="Facebook Link">🌐</a>
                <a href="#" aria-label="Twitter Link">📢</a>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,0.15); margin: 8px 0;">
        <div class="footer-details">
            <p>&copy; <?php echo date("Y"); ?> SSC SEICT. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>

```