<?php
/**
 * Webinars listing
 *
 * PAST WEBINARS — auto-scanned from media/*.html
 * Each webinar page should include in <head>:
 *   <meta name="date"        content="YYYY-MM-DD">
 *   <meta name="description" content="Short description">
 *   <meta name="presenter"   content="Presenter Name">
 *   <meta name="thumbnail"   content="path/relative/to/media/image.jpg">  (optional)
 *
 * UPCOMING WEBINARS — edit the $upcomingWebinars array below.
 */

// -----------------------------------------------------------------------
// UPCOMING WEBINARS — update manually until registration pages exist
// -----------------------------------------------------------------------
$upcomingWebinars = [
    [
        'title'       => 'NotebookLM: AI-Powered Research',
        'presenter'   => 'Dave Ghidiu',
        'date'        => 'Date TBD',
        'description' => 'Discover how Google\'s NotebookLM transforms the way you interact with documents, lecture notes, and research materials — turning them into a personalized AI knowledge base.',
        'registerUrl' => '#',
    ],
    [
        'title'       => 'Chatbots for Your Organization',
        'presenter'   => 'Dave Ghidiu & Debora Ortloff',
        'date'        => 'Date TBD',
        'description' => 'From concept to deployment: learn how to build and customize AI chatbots that genuinely serve your organization\'s needs without requiring a background in coding.',
        'registerUrl' => '#',
    ],
    [
        'title'       => 'The Prompting Experience',
        'presenter'   => 'Dave Ghidiu',
        'date'        => 'Date TBD',
        'description' => 'A hands-on session designed to level up your AI prompting skills — from clear basics to advanced techniques that get dramatically better results from any AI tool.',
        'registerUrl' => '#',
    ],
];

// -----------------------------------------------------------------------
// PAST WEBINARS — auto-scanned
// -----------------------------------------------------------------------
$exclude = ['webinars.php', 'webinars.html', 'podcast.html'];
$allFiles = glob(__DIR__ . '/*.html') ?: [];

$pastWebinars = [];

foreach ($allFiles as $file) {
    if (in_array(basename($file), $exclude)) continue;

    $content = file_get_contents($file);

    // Only include files that have a date meta tag (marks them as content pages)
    if (!preg_match('/<meta\s+name="date"\s+content="([^"]*)"/i', $content, $m)) continue;
    $dateRaw     = $m[1];
    $dateDisplay = date('F Y', strtotime($dateRaw));

    // Title — strip site suffix
    preg_match('/<title>(.*?)<\/title>/is', $content, $m);
    $title = isset($m[1])
        ? trim(preg_replace('/\s*\|\s*FLX AI Hub.*$/i', '', $m[1]))
        : 'Untitled';

    // Description
    preg_match('/<meta\s+name="description"\s+content="([^"]*)"/i', $content, $m);
    $description = html_entity_decode($m[1] ?? '', ENT_QUOTES, 'UTF-8');

    // Presenter
    preg_match('/<meta\s+name="presenter"\s+content="([^"]*)"/i', $content, $m);
    $presenter = html_entity_decode($m[1] ?? '', ENT_QUOTES, 'UTF-8');

    // Thumbnail
    preg_match('/<meta\s+name="thumbnail"\s+content="([^"]*)"/i', $content, $m);
    $thumbnail = $m[1] ?? null;

    $pastWebinars[] = [
        'url'         => basename($file),
        'title'       => $title,
        'description' => $description,
        'presenter'   => $presenter,
        'dateRaw'     => $dateRaw,
        'dateDisplay' => $dateDisplay,
        'thumbnail'   => $thumbnail,
    ];
}

// Newest first
usort($pastWebinars, function ($a, $b) {
    return strtotime($b['dateRaw']) - strtotime($a['dateRaw']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webinars | FLX AI Hub</title>
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
    <script>window.BASE_PATH = '../'; window.ACTIVE_PAGE = 'media';</script>
    <script src="../navbar.js"></script>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <span class="section-label">Media / Webinars</span>
            <h1>Webinars</h1>
            <p class="hero-sub">Watch recordings from our past sessions and register for what's coming up next. Each webinar is practical, hands-on, and designed to be immediately useful.</p>
        </div>
    </section>

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

    <hr class="section-divider">

    <!-- UPCOMING WEBINARS -->
    <section class="page-section bg-light">
        <div class="container">
            <div class="section-header" style="text-align:left; margin-bottom:2rem;">
                <span class="section-label">Coming Up</span>
                <h2 class="text-green">Upcoming Webinars</h2>
                <p style="margin-top:0.5rem;">Reserve your spot &mdash; these fill up fast.</p>
            </div>
            <div class="cards-grid">
                <?php if (empty($upcomingWebinars)): ?>
                    <div class="empty-state"><p>No upcoming webinars scheduled yet &mdash; check back soon.</p></div>
                <?php else: ?>
                    <?php foreach ($upcomingWebinars as $w): ?>
                        <div class="upcoming-card">
                            <span class="upcoming-badge">Upcoming</span>
                            <h3><?= htmlspecialchars($w['title']) ?></h3>
                            <p class="upcoming-presenter">
                                <i data-lucide="user" width="13" style="display:inline;vertical-align:middle;margin-right:3px;"></i>
                                <?= htmlspecialchars($w['presenter']) ?>
                            </p>
                            <p class="card-date" style="font-size:0.8rem; color:var(--neutral-gray);"><?= htmlspecialchars($w['date']) ?></p>
                            <p class="card-desc"><?= htmlspecialchars($w['description']) ?></p>
                            <a href="<?= htmlspecialchars($w['registerUrl']) ?>" class="register-btn">
                                Register Now <i data-lucide="arrow-right" width="14"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
            <p class="footer-copy">&copy; <?= date('Y') ?> The FLX AI Hub. All rights reserved.</p>
        </div>
    </footer>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"1f4307355f984a529a180c0bd298a86a","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
