<?php
/**
 * Webinars listing — fully auto-scanned from media/webinars/*.html
 *
 * Each webinar page needs in <head>:
 *   <meta name="date"         content="YYYY-MM-DD">         ← required
 *   <meta name="description"  content="Short description">  ← recommended
 *   <meta name="presenter"    content="Presenter Name">     ← recommended
 *   <meta name="time"         content="12:00 PM – 12:45 PM"> ← optional
 *   <meta name="location"     content="Online (Zoom)">      ← optional
 *   <meta name="thumbnail"    content="path/to/image.jpg">  ← optional
 *   <meta name="register-url" content="https://...">        ← optional (upcoming only)
 *
 * Upcoming vs past is determined automatically by date vs today.
 */

$today = date('Y-m-d');
$upcomingWebinars = [];
$pastWebinars     = [];

foreach (glob(__DIR__ . '/*.html') ?: [] as $file) {
    $content = file_get_contents($file);

    // Must have a date meta tag
    if (!preg_match('/<meta\s+name="date"\s+content="([^"]*)"/i', $content, $m)) continue;
    $dateRaw = trim($m[1]);

    // Helper to extract a meta tag value
    $meta = function(string $name) use ($content): string {
        if (preg_match('/<meta\s+name="' . preg_quote($name, '/') . '"\s+content="([^"]*)"/i', $content, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
        }
        return '';
    };

    preg_match('/<title>(.*?)<\/title>/is', $content, $m);
    $title = isset($m[1])
        ? trim(preg_replace('/\s*\|\s*FLX AI Hub.*$/i', '', $m[1]))
        : 'Untitled';

    $entry = [
        'url'         => basename($file),
        'title'       => $title,
        'description' => $meta('description'),
        'presenter'   => $meta('presenter'),
        'time'        => $meta('time'),
        'location'    => $meta('location'),
        'thumbnail'   => $meta('thumbnail') ?: null,
        'registerUrl' => $meta('register-url'),
        'dateRaw'     => $dateRaw,
        'dateDisplay' => date('F j, Y', strtotime($dateRaw)),
    ];

    if ($dateRaw >= $today) {
        $upcomingWebinars[] = $entry;
    } else {
        $pastWebinars[] = $entry;
    }
}

// Upcoming: soonest first; Past: newest first
usort($upcomingWebinars, fn($a, $b) => strcmp($a['dateRaw'], $b['dateRaw']));
usort($pastWebinars,     fn($a, $b) => strcmp($b['dateRaw'], $a['dateRaw']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webinars | FLX AI Hub</title>
    <link rel="icon" type="image/x-icon" href="../../images/HubLogo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Montserrat:wght@400;600;700;800&family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../../shared.css">
</head>
<body>

    <!-- Navbar -->
    <div id="navbar-placeholder"></div>
    <script>window.BASE_PATH = '../../'; window.ACTIVE_PAGE = 'media';</script>
    <script src="../../navbar.js"></script>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <span class="section-label">Media / Webinars</span>
            <h1>Webinars</h1>
            <p class="hero-sub">Watch recordings from our past sessions and register for what's coming up next. Each webinar is practical, hands-on, and designed to be immediately useful.</p>
        </div>
    </section>

    <!-- UPCOMING WEBINARS -->
    <section class="page-section bg-light">
        <div class="container">
            <div class="section-header" style="text-align:left; margin-bottom:2rem;">
                <span class="section-label">Coming Up</span>
                <h2 class="text-green">Upcoming Webinars</h2>
                <p style="margin-top:0.5rem;">Reserve your spot - these fill up fast.</p>
            </div>
            <div class="cards-grid">
                <?php if (empty($upcomingWebinars)): ?>
                    <div class="empty-state"><p>No upcoming webinars scheduled yet &mdash; check back soon.</p></div>
                <?php else: ?>
                    <?php foreach ($upcomingWebinars as $w):
                        $registerHref  = $w['registerUrl'] !== '' ? $w['registerUrl'] : $w['url'];
                        $registerAttrs = $w['registerUrl'] !== '' ? ' target="_blank" rel="noopener"' : '';
                    ?>
                        <div class="upcoming-card">
                            <span class="upcoming-badge">Upcoming</span>
                            <h3><?= htmlspecialchars($w['title']) ?></h3>
                            <?php if ($w['presenter']): ?>
                            <p class="upcoming-presenter">
                                <i data-lucide="user" width="13" style="display:inline;vertical-align:middle;margin-right:3px;"></i>
                                <?= htmlspecialchars($w['presenter']) ?>
                            </p>
                            <?php endif; ?>
                            <p class="card-date" style="font-size:0.8rem; color:var(--neutral-gray);">
                                <?= htmlspecialchars($w['dateDisplay']) ?>
                                <?php if ($w['time']): ?> &bull; <?= htmlspecialchars($w['time']) ?><?php endif; ?>
                            </p>
                            <p class="card-desc"><?= htmlspecialchars($w['description']) ?></p>
                            <a href="<?= htmlspecialchars($registerHref) ?>" class="register-btn"<?= $registerAttrs ?>>
                                Register Now <i data-lucide="arrow-right" width="14"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    <!-- PAST WEBINARS -->
    <section class="page-section">
        <div class="container">
            <div class="section-header" style="text-align:left; margin-bottom:2rem;">
                <span class="section-label">On Demand</span>
                <h2 class="text-blue">Past Webinars</h2>
            </div>
            <div class="cards-grid">
                <?php if (empty($pastWebinars)): ?>
                    <div class="empty-state"><p>Recordings coming soon.</p></div>
                <?php else: ?>
                    <?php foreach ($pastWebinars as $w): ?>
                        <a href="<?= htmlspecialchars($w['url']) ?>" class="content-card webinar-card">
                            <?php if ($w['thumbnail']): ?>
                                <div class="card-thumb-wrap" style="position:relative;">
                                    <img src="<?= htmlspecialchars($w['thumbnail']) ?>"
                                         alt="<?= htmlspecialchars($w['title']) ?>"
                                         loading="lazy">
                                    <div class="play-overlay"><i data-lucide="play-circle" width="56"></i></div>
                                </div>
                            <?php else: ?>
                                <div class="card-thumb-placeholder" style="position:relative;">
                                    <i data-lucide="play-circle" width="56" style="opacity:0.35;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="card-date">
                                    <?= htmlspecialchars($w['dateDisplay']) ?>
                                    <?php if ($w['time']): ?>
                                        &bull; <?= htmlspecialchars($w['time']) ?>
                                    <?php endif; ?>
                                    <?php if ($w['presenter']): ?>
                                        &bull; <?= htmlspecialchars($w['presenter']) ?>
                                    <?php endif; ?>
                                </div>
                                <h2 class="card-title"><?= htmlspecialchars($w['title']) ?></h2>
                                <p class="card-desc"><?= htmlspecialchars($w['description']) ?></p>
                                <span class="card-read-link">Watch Recording &rarr;</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <a href="../../index.html" class="footer-logo">
                <img src="../../images/HubLogo.png" alt="FLX AI Hub Logo">
                <div class="logo-text">
                    <span class="logo-title">FLX AI Hub</span>
                    <span class="logo-sub">Innovate &amp; Educate</span>
                </div>
            </a>
            <p class="footer-copy">&copy; <?= date('Y') ?> The FLX AI Hub. All rights reserved.</p>
        </div>
    </footer>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"1f4307355f984a529a180c0bd298a86a","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
