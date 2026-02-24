<?php
/**
 * Calendar — upcoming events from two sources:
 *
 *   WEBINARS  — auto-scanned from media/webinars/*.html
 *               Each page needs: <meta name="date" content="YYYY-MM-DD"> (required)
 *               Optional: name="description", name="time", name="location",
 *                         name="register-url", name="thumbnail"
 *               Past dates are excluded automatically.
 *
 *   LIVE EVENTS — parsed from the `const events` JS array in live-events.html
 *                 Add/edit events there; past dates are excluded automatically.
 */

$today = date('Y-m-d');

// -----------------------------------------------------------------------
// Webinars — scan media/webinars/*.html for pages with a future date meta tag
// -----------------------------------------------------------------------
function scanWebinars(string $dir, string $urlPrefix, string $today): array {
    $items = [];
    if (!is_dir($dir)) return $items;
    foreach (glob($dir . '/*.html') ?: [] as $file) {
        $html = file_get_contents($file);

        if (!preg_match('/<meta\s+name="date"\s+content="([^"]*)"/i', $html, $m)) continue;
        $date = trim($m[1]);
        if ($date < $today) continue;

        $item = [
            'type'    => 'Webinar',
            'date'    => $date,
            'pageUrl' => $urlPrefix . basename($file),
        ];

        preg_match('/<title>(.*?)<\/title>/is', $html, $m);
        $item['title'] = isset($m[1])
            ? trim(preg_replace('/\s*\|\s*FLX AI Hub.*$/i', '', $m[1]))
            : pathinfo($file, PATHINFO_FILENAME);

        foreach (['description', 'time', 'location', 'thumbnail', 'register-url'] as $tag) {
            if (preg_match('/<meta\s+name="' . preg_quote($tag, '/') . '"\s+content="([^"]*)"/i', $html, $m)) {
                $item[$tag] = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
            }
        }

        $items[] = $item;
    }
    return $items;
}

// -----------------------------------------------------------------------
// Live Events — parse the `const events = [...]` JS array in live-events.html
// -----------------------------------------------------------------------
function parseLiveEvents(string $liveEventsFile, string $today): array {
    $items = [];
    if (!file_exists($liveEventsFile)) return $items;
    $src = file_get_contents($liveEventsFile);

    // Extract the array body between `const events = [` and `];`
    if (!preg_match('/const\s+events\s*=\s*\[(.*?)\];/s', $src, $m)) return $items;
    $arrayBody = $m[1];

    // Split into individual { ... } objects
    preg_match_all('/\{([^}]*)\}/s', $arrayBody, $objects);

    foreach ($objects[1] as $obj) {
        // Helper: extract a single-quoted JS string value for a given key
        // Handles \' escapes inside the value
        $get = function(string $key) use ($obj): string {
            if (preg_match('/\b' . preg_quote($key, '/') . '\s*:\s*\'((?:[^\'\\\\]|\\\\.)*)\'/s', $obj, $m)) {
                return str_replace(["\\'", '\\"'], ["'", '"'], $m[1]);
            }
            return '';
        };

        $date = $get('date');
        if ($date === '' || $date < $today) continue;

        $signupUrl   = $get('signupUrl');
        $signupLabel = $get('signupLabel');

        $item = [
            'type'        => 'Live Event',
            'date'        => $date,
            'title'       => $get('title'),
            'description' => $get('description'),
            'time'        => $get('time'),
            'location'    => $get('location'),
            'pageUrl'     => '../live-events.html',
        ];
        if ($signupUrl !== '') {
            $item['register-url']   = $signupUrl;
            $item['register-label'] = $signupLabel !== '' ? $signupLabel : 'Register Now';
        }

        $items[] = $item;
    }
    return $items;
}

$events = array_merge(
    scanWebinars(__DIR__ . '/../media/webinars', '../media/webinars/', $today),
    parseLiveEvents(__DIR__ . '/../live-events.html', $today)
);

// Sort soonest first
usort($events, fn($a, $b) => strcmp($a['date'], $b['date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | FLX AI Hub</title>
    <link rel="icon" type="image/x-icon" href="../images/HubLogo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Montserrat:wght@400;600;700;800&family=Rubik:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="../shared.css">
    <style>
        /* ---- Calendar page styles ---- */
        .cal-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            max-width: 860px;
            margin: 0 auto;
        }

        .cal-card {
            display: flex;
            gap: 0;
            background: white;
            border-radius: var(--radius);
            border: 1px solid #e8eaed;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.22s, box-shadow 0.22s, border-color 0.22s;
            text-decoration: none;
            color: inherit;
        }

        .cal-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: #c8d4ee;
        }

        /* Left date column */
        .cal-date-block {
            flex-shrink: 0;
            width: 88px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 0.5rem;
            background: linear-gradient(160deg, var(--primary-blue) 0%, #0057d3 100%);
            color: white;
            text-align: center;
        }

        .cal-date-month {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.85;
        }

        .cal-date-day {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin: 0.15rem 0;
        }

        .cal-date-year {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 600;
            opacity: 0.7;
        }

        /* Right event body */
        .cal-body {
            flex: 1;
            padding: 1.1rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.3rem;
        }

        .cal-meta-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .cal-badge {
            display: inline-block;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.2rem 0.65rem;
            border-radius: var(--radius-pill);
        }

        .cal-badge-webinar {
            background: rgba(0, 70, 173, 0.1);
            color: var(--primary-blue);
        }

        .cal-badge-live {
            background: rgba(88, 166, 24, 0.12);
            color: #3d7a10;
        }

        .cal-detail {
            font-size: 0.8rem;
            color: var(--neutral-gray);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .cal-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark-gray);
            line-height: 1.3;
        }

        .cal-desc {
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.6;
            margin-top: 0.1rem;
        }

        .cal-action {
            margin-top: 0.6rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.825rem;
            font-weight: 700;
            color: var(--primary-blue);
            transition: gap 0.18s;
        }

        .cal-card:hover .cal-action {
            gap: 0.55rem;
        }

        /* Empty state */
        .cal-empty {
            text-align: center;
            padding: 4rem 1.5rem;
            color: var(--neutral-gray);
        }

        .cal-empty h3 {
            font-size: 1.25rem;
            margin: 1rem 0 0.5rem;
            color: var(--dark-gray);
        }

        /* Responsive */
        @media (max-width: 540px) {
            .cal-date-block { width: 70px; }
            .cal-date-day   { font-size: 1.6rem; }
            .cal-body       { padding: 0.9rem 1rem; }
            .cal-title      { font-size: 0.95rem; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div id="navbar-placeholder"></div>
    <script>window.BASE_PATH = '../'; window.ACTIVE_PAGE = 'what-we-do';</script>
    <script src="../navbar.js"></script>

    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <span class="section-label">What We Do</span>
            <h1>Upcoming Events</h1>
            <p class="hero-sub">Webinars, workshops, and live events from the FLX AI Hub &mdash; register ahead and join us.</p>
        </div>
    </section>

    <!-- Events list -->
    <section class="page-section">
        <div class="container">
            <?php if (empty($events)): ?>
                <div class="cal-empty">
                    <i data-lucide="calendar-x" width="52" style="color:var(--neutral-gray); margin:0 auto; display:block;"></i>
                    <h3>No upcoming events</h3>
                    <p>Check back soon &mdash; new events are added regularly.</p>
                </div>
            <?php else: ?>
                <div class="cal-list">
                    <?php foreach ($events as $ev):
                        $ts          = strtotime($ev['date']);
                        $monthAbbr   = date('M', $ts);
                        $day         = date('j', $ts);
                        $year        = date('Y', $ts);
                        $isWebinar   = $ev['type'] === 'Webinar';
                        $badgeClass  = $isWebinar ? 'cal-badge-webinar' : 'cal-badge-live';
                        $linkUrl     = isset($ev['register-url']) && $ev['register-url'] !== ''
                                       ? $ev['register-url']
                                       : $ev['pageUrl'];
                        $isExternal  = isset($ev['register-url']) && $ev['register-url'] !== '';
                        $extAttrs    = $isExternal ? ' target="_blank" rel="noopener"' : '';
                        $actionLabel = isset($ev['register-label']) ? $ev['register-label']
                                       : ($isExternal ? 'Register Now' : 'Learn More');
                    ?>
                        <a href="<?= htmlspecialchars($linkUrl) ?>"
                           class="cal-card"<?= $extAttrs ?>>

                            <!-- Date block -->
                            <div class="cal-date-block">
                                <span class="cal-date-month"><?= $monthAbbr ?></span>
                                <span class="cal-date-day"><?= $day ?></span>
                                <span class="cal-date-year"><?= $year ?></span>
                            </div>

                            <!-- Event details -->
                            <div class="cal-body">
                                <div class="cal-meta-row">
                                    <span class="cal-badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($ev['type']) ?>
                                    </span>
                                    <?php if (!empty($ev['time'])): ?>
                                        <span class="cal-detail">
                                            <i data-lucide="clock" width="12"></i>
                                            <?= htmlspecialchars($ev['time']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($ev['location'])): ?>
                                        <span class="cal-detail">
                                            <i data-lucide="map-pin" width="12"></i>
                                            <?= htmlspecialchars($ev['location']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="cal-title"><?= htmlspecialchars($ev['title']) ?></div>

                                <?php if (!empty($ev['description'])): ?>
                                    <div class="cal-desc"><?= htmlspecialchars($ev['description']) ?></div>
                                <?php endif; ?>

                                <span class="cal-action">
                                    <?= $actionLabel ?>
                                    <i data-lucide="arrow-right" width="14"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
