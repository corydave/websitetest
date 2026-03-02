<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI & Accessibility | FLX AI Hub</title>
    <link rel="icon" type="image/x-icon" href="../images/HubLogo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Montserrat:wght@400;600;700;800&family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../shared.css">
</head>
<body>

    <!-- Navbar -->
    <div id="navbar-placeholder"></div>
    <script>window.BASE_PATH = '../'; window.ACTIVE_PAGE = 'what-we-do';</script>
    <script src="../navbar.js"></script>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <span class="section-label">Initiative</span>
            <h1>AI &amp; Accessibility</h1>
            <p class="hero-sub">Exploring how artificial intelligence can remove barriers and create more inclusive experiences for everyone in the Finger Lakes region.</p>
        </div>
    </section>

    <!-- Placeholder content -->
    <section class="page-section">
        <div class="container" style="max-width: 760px; text-align: center; padding: 5rem 1.5rem;">
            <i data-lucide="accessibility" width="56" style="color: var(--primary-blue); opacity: 0.25; margin-bottom: 1.25rem;"></i>
            <h2 style="font-size: 1.4rem; color: var(--dark-gray); margin-bottom: 0.75rem;">Coming Soon</h2>
            <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.7;">This page is under construction. Check back soon for more details about the AI &amp; Accessibility initiative.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <a href="../index.html" class="footer-logo">
                <img src="../images/HubLogo.png" alt="FLX AI Hub Logo">
                <div class="logo-text">
                    <span class="logo-title">FLX AI Hub</span>
                    <span class="logo-sub">Innovate &amp; Educate</span>
                </div>
            </a>
            <p class="footer-copy">&copy; <span id="footer-year"></span> The FLX AI Hub. All rights reserved.</p>
        </div>
    </footer>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"1f4307355f984a529a180c0bd298a86a","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
    <script>
        lucide.createIcons();
        document.getElementById('footer-year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
