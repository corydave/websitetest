<?php
/**
 * Blog listing — auto-scans blog/*.html
 *
 * Each post page should include in <head>:
 *   <meta name="date"        content="YYYY-MM-DD">
 *   <meta name="description" content="Short excerpt">
 *   <meta name="thumbnail"   content="path/from/site-root/image.jpg">
 */

$files = glob(__DIR__ . '/blog/*.html');
$posts = [];

foreach ((array) $files as $file) {
    $content = file_get_contents($file);

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
    $dateDisplay = date('F j, Y', strtotime($dateRaw));

    // Thumbnail
    preg_match('/<meta\s+name="thumbnail"\s+content="([^"]*)"/i', $content, $m);
    $thumbnail = $m[1] ?? null;

    $posts[] = [
        'url'         => 'blog/' . basename($file),
        'title'       => $title,
        'description' => $description,
        'dateRaw'     => $dateRaw,
        'dateDisplay' => $dateDisplay,
        'thumbnail'   => $thumbnail,
    ];
}

// Newest first
usort($posts, function ($a, $b) {
    return strtotime($b['dateRaw']) - strtotime($a['dateRaw']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | FLX AI Hub</title>
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
    <script>window.BASE_PATH = ''; window.ACTIVE_PAGE = 'blog';</script>
    <script src="navbar.js"></script>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <span class="section-label">From the Hub</span>
            <h1>Blog</h1>
            <p class="hero-sub">Behind-the-scenes stories, project spotlights, and practical insights on AI in the real world.</p>
        </div>
    </section>

    <!-- Blog Posts Grid -->
    <section class="page-section">
        <div class="container">
            <div class="cards-grid">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <i data-lucide="file-text" width="48" style="color:var(--neutral-gray); margin:0 auto 1rem; display:block;"></i>
                        <h3 style="font-size:1.25rem;">No posts yet</h3>
                        <p>Check back soon &mdash; we&rsquo;re always writing!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <a href="<?= htmlspecialchars($post['url']) ?>" class="content-card">
                            <?php if ($post['thumbnail']): ?>
                                <div class="card-thumb-wrap">
                                    <img src="<?= htmlspecialchars($post['thumbnail']) ?>"
                                         alt="<?= htmlspecialchars($post['title']) ?>"
                                         loading="lazy">
                                </div>
                            <?php else: ?>
                                <div class="card-thumb-placeholder">
                                    <i data-lucide="image" width="40"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="card-date"><?= htmlspecialchars($post['dateDisplay']) ?></div>
                                <h2 class="card-title"><?= htmlspecialchars($post['title']) ?></h2>
                                <p class="card-desc"><?= htmlspecialchars($post['description']) ?></p>
                                <span class="card-read-link">Read Article &rarr;</span>
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
