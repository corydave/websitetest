<?php
/**
 * Tools listing — auto-scans tools/*.html
 *
 * Each tool page should include in <head>:
 *   <meta name="date"        content="YYYY-MM-DD">
 *   <meta name="description" content="Short description">
 *   <meta name="thumbnail"   content="path/from/site-root/image.jpg">
 */

$files = glob(__DIR__ . '/tools/*.html');
$tools = [];

foreach ((array) $files as $file) {
    $content = file_get_contents($file);

    // Skip files without a date meta tag (e.g. index.html placeholders)
    if (!preg_match('/<meta\s+name="date"\s+content="([^"]*)"/i', $content)) continue;

    // Title — strip site suffix
    preg_match('/<title>(.*?)<\/title>/is', $content, $m);
    $title = isset($m[1])
        ? trim(preg_replace('/\s*\|\s*FLX AI Hub\s*/i', '', $m[1]))
        : 'Untitled';

    // Description
    preg_match('/<meta\s+name="description"\s+content="([^"]*)"/i', $content, $m);
    $description = html_entity_decode($m[1] ?? '', ENT_QUOTES, 'UTF-8');

    // Date
    preg_match('/<meta\s+name="date"\s+content="([^"]*)"/i', $content, $m);
    $dateRaw     = $m[1] ?? date('Y-m-d', filemtime($file));
    $dateDisplay = date('F Y', strtotime($dateRaw));

    // Thumbnail
    preg_match('/<meta\s+name="thumbnail"\s+content="([^"]*)"/i', $content, $m);
    $thumbnail = $m[1] ?? null;

    $tools[] = [
        'url'         => 'tools/' . basename($file),
        'title'       => $title,
        'description' => $description,
        'dateRaw'     => $dateRaw,
        'dateDisplay' => $dateDisplay,
        'thumbnail'   => $thumbnail,
    ];
}

// Newest first
usort($tools, function ($a, $b) {
    return strtotime($b['dateRaw']) - strtotime($a['dateRaw']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools | FLX AI Hub</title>
    <link rel="icon" type="image/x-icon" href="images/HubLogo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Montserrat:wght@400;600;700;800&family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="shared.css">
</head>
<body>

    <!-- Navbar -->
    <div id="navbar-placeholder"></div>
    <script>window.BASE_PATH = ''; window.ACTIVE_PAGE = 'tools';</script>
    <script src="navbar.js"></script>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <span class="section-label">Built by the Hub</span>
            <h1>Tools</h1>
            <p class="hero-sub">Hands-on tools and interactive experiences we've built to make AI exploration practical and engaging.</p>
        </div>
    </section>

    <!-- Tools Grid -->
    <section class="page-section">
        <div class="container">
            <div class="cards-grid">
                <?php if (empty($tools)): ?>
                    <div class="empty-state">
                        <i data-lucide="wrench" width="48" style="color:var(--neutral-gray); margin:0 auto 1rem; display:block;"></i>
                        <h3 style="font-size:1.25rem;">Tools coming soon</h3>
                        <p>We&rsquo;re building some great ones &mdash; check back shortly.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tools as $tool): ?>
                        <a href="<?= htmlspecialchars($tool['url']) ?>" class="content-card">
                            <?php if ($tool['thumbnail']): ?>
                                <div class="card-thumb-wrap">
                                    <img src="<?= htmlspecialchars($tool['thumbnail']) ?>"
                                         alt="<?= htmlspecialchars($tool['title']) ?>"
                                         loading="lazy">
                                </div>
                            <?php else: ?>
                                <div class="card-thumb-placeholder">
                                    <i data-lucide="wrench" width="40"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="card-date"><?= htmlspecialchars($tool['dateDisplay']) ?></div>
                                <h2 class="card-title"><?= htmlspecialchars($tool['title']) ?></h2>
                                <p class="card-desc"><?= htmlspecialchars($tool['description']) ?></p>
                                <span class="card-read-link">Open Tool &rarr;</span>
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
            <a href="index.html" class="footer-logo">
                <img src="images/HubLogo.png" alt="FLX AI Hub Logo">
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
