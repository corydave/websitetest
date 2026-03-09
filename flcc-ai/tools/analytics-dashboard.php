<?php
/**
 * AI Policy Generator — Analytics Dashboard
 *
 * Password-protected view of which policy options are selected most often.
 *
 * ⚠️  CHANGE THE PASSWORD BELOW before making this file publicly accessible.
 */

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
define('DASHBOARD_PASSWORD', 'flcc-ai-analytics');   // ← set your own password here
define('DB_PATH', __DIR__ . '/../data/analytics.db');
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Handle login
$loginError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === DASHBOARD_PASSWORD) {
        $_SESSION['analytics_authed'] = true;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
    $loginError = true;
}

// Login gate
if (empty($_SESSION['analytics_authed'])) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics — Sign In</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
             background:#f0f4f8;display:flex;justify-content:center;align-items:center;min-height:100vh}
        .card{background:#fff;border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,.1);
              padding:2.5rem 2rem;width:100%;max-width:360px}
        h1{color:#0046ad;font-size:1.25rem;margin-bottom:1.75rem}
        label{display:block;font-size:.85rem;color:#555;margin-bottom:.35rem;font-weight:500}
        input[type=password]{width:100%;padding:.55rem .75rem;border:1px solid #ddd;
                             border-radius:6px;font-size:1rem;margin-bottom:1rem}
        input[type=password]:focus{outline:none;border-color:#0046ad;
                                   box-shadow:0 0 0 3px rgba(0,70,173,.15)}
        button{width:100%;padding:.65rem;background:#0046ad;color:#fff;border:none;
               border-radius:6px;font-size:1rem;cursor:pointer;font-weight:500}
        button:hover{background:#003a90}
        .error{background:#fff0f0;border:1px solid #f5c6cb;color:#721c24;
               border-radius:6px;padding:.65rem .75rem;font-size:.875rem;margin-bottom:1rem}
        .warn{background:#fff8e1;border:1px solid #ffe082;color:#5d4037;
              border-radius:6px;padding:.65rem .75rem;font-size:.8rem;margin-top:1.25rem}
    </style>
</head>
<body>
<div class="card">
    <h1>📊 Policy Analytics</h1>
    <?php if ($loginError): ?>
        <p class="error">Incorrect password — please try again.</p>
    <?php endif; ?>
    <form method="post">
        <label for="pwd">Password</label>
        <input type="password" id="pwd" name="password" autofocus autocomplete="current-password">
        <button type="submit">Sign In</button>
    </form>
    <?php if (DASHBOARD_PASSWORD === 'changeme'): ?>
        <p class="warn">⚠️ Default password is still in use. Edit <code>analytics-dashboard.php</code> and change <code>DASHBOARD_PASSWORD</code>.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php exit; }

// ── Label maps ────────────────────────────────────────────────────────────────
$UC_LABELS = [
    'prohibited'    => '🚫 No AI Use',
    'encouraged'    => '✅ Open Use',
    'brainstorming' => '💡 Brainstorming',
    'research'      => '🔍 Research & Summarization',
    'outline'       => '🗒️ Outline & Thesis',
    'content'       => '📝 Content Generation',
    'critique'      => '🗨️ Critique & Peer Review',
    'analysis'      => '📊 Data Analysis',
    'other'         => '➕ Other Use Cases',
];
$DOC_LABELS = [
    'none'       => '❌ No Disclosure Required',
    'tools'      => '📝 Document Platforms',
    'chatsummary'=> '💬 Submit Chat Summary',
    'prompts'    => '📤 Submit Prompts & Outputs',
    'citation'   => '🧾 Formal Citation Required',
    'reflection' => '🧠 Additional Reflection',
    'factcheck'  => '🔎 Fact Check',
    'multitools' => '🔄 Compare Multiple Tools',
    'other'      => '➕ Other Requirements',
];

// ── Load statistics ───────────────────────────────────────────────────────────
// sentiment          = Copy / Share URL clicks  (deliberate final selection)
// raw                = every checkbox click     (exploratory behaviour)
// no_analytics_click = clicks on "no analytics" version link
$sentiment        = ['total' => 0, 'use_cases' => [], 'documentation' => []];
$raw              = ['total' => 0, 'use_cases' => [], 'documentation' => []];
$noAnalyticsClicks = 0;
$totals           = ['total' => 0, 'today' => 0, 'last7' => 0];
$daily            = [];
$dbError          = null;

if (file_exists(DB_PATH)) {
    try {
        $db = new PDO('sqlite:' . DB_PATH, '', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Ensure the event_type column exists (migrate older databases)
        try { $db->exec("ALTER TABLE events ADD COLUMN event_type TEXT NOT NULL DEFAULT 'raw'"); }
        catch (Exception $e) { /* already exists */ }

        $totals['total'] = (int)$db->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $totals['today'] = (int)$db->query("SELECT COUNT(*) FROM events WHERE date(ts) = date('now')")->fetchColumn();
        $totals['last7'] = (int)$db->query("SELECT COUNT(*) FROM events WHERE ts >= datetime('now','-7 days')")->fetchColumn();

        // Count no_analytics_click events separately
        $noAnalyticsClicks = (int)$db->query(
            "SELECT COUNT(*) FROM events WHERE event_type = 'no_analytics_click'"
        )->fetchColumn();

        // Aggregate each event type separately (skip no_analytics_click rows)
        foreach ($db->query("SELECT use_cases, documentation, COALESCE(event_type,'raw') AS event_type FROM events WHERE event_type != 'no_analytics_click'") as $row) {
            $bucket = ($row['event_type'] === 'sentiment') ? $sentiment : $raw;
            $bucket['total']++;
            foreach (json_decode($row['use_cases'],     true) ?? [] as $v)
                $bucket['use_cases'][$v]     = ($bucket['use_cases'][$v]     ?? 0) + 1;
            foreach (json_decode($row['documentation'], true) ?? [] as $v)
                $bucket['documentation'][$v] = ($bucket['documentation'][$v] ?? 0) + 1;
            // PHP arrays are value types — write back
            if ($row['event_type'] === 'sentiment') $sentiment = $bucket;
            else $raw = $bucket;
        }
        arsort($sentiment['use_cases']);  arsort($sentiment['documentation']);
        arsort($raw['use_cases']);        arsort($raw['documentation']);

        // Daily totals for sparkline (both types combined)
        $rows = $db->query(
            "SELECT date(ts) AS day, COUNT(*) AS cnt
             FROM events WHERE ts >= datetime('now','-29 days')
             GROUP BY day ORDER BY day"
        );
        foreach ($rows as $r) $daily[$r['day']] = (int)$r['cnt'];

    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
} else {
    $dbError = 'No data yet — the database will be created when the first policy is generated.';
}

$maxDay = max(array_values($daily) ?: [1]);

// Helper: colour a bar based on rank (top = solid, lower = faded)
function barColour(int $rank, int $total, string $hue): string {
    $opacity = 1 - ($rank / max($total, 1)) * 0.55;
    return $hue === 'blue'
        ? "rgba(0,70,173,{$opacity})"
        : "rgba(88,166,24,{$opacity})";
}

// Helper: renders one bar chart for a given $bucketArray and $labels
function renderBars(array $bucketArr, array $labels, string $hue): void {
    if (empty($bucketArr)) { echo '<p class="empty">No data yet.</p>'; return; }
    $max  = max(array_values($bucketArr));
    $rank = 0;
    foreach ($bucketArr as $key => $count) {
        $pct   = $max > 0 ? round($count / $max * 100) : 0;
        $color = barColour($rank, count($bucketArr), $hue);
        $label = htmlspecialchars($labels[$key] ?? $key);
        echo "<div class='bar-row'>"
           . "<div class='bar-label'>{$label}</div>"
           . "<div class='bar-track'><div class='bar-fill' style='width:{$pct}%;background:{$color}'></div></div>"
           . "<div class='bar-count'>" . number_format($count) . "</div>"
           . "</div>";
        $rank++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Policy Generator — Analytics</title>
    <style>
        :root {
            --blue:  #0046ad;
            --green: #58a618;
            --bg:    #f0f4f8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: #333;
            min-height: 100vh;
        }

        /* ── Header ── */
        header {
            background: var(--blue);
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        header h1 { font-size: 1.15rem; font-weight: 600; }
        header a {
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: .85rem;
            white-space: nowrap;
        }
        header a:hover { color: #fff; }

        /* ── Layout ── */
        main {
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 1.5rem 4rem;
        }

        /* ── Stat cards ── */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 1.25rem 1rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.08);
            text-align: center;
        }
        .stat-card .num {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--blue);
            line-height: 1;
        }
        .stat-card .lbl {
            font-size: .75rem;
            color: #888;
            margin-top: .3rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Activity sparkline ── */
        .sparkline-section {
            background: #fff;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.08);
            margin-bottom: 1.5rem;
        }
        .sparkline-section h2 { font-size: .75rem; text-transform: uppercase; letter-spacing: .06em; color: #888; margin-bottom: 1rem; }
        .sparkline {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 60px;
        }
        .spark-bar {
            flex: 1;
            background: var(--blue);
            border-radius: 2px 2px 0 0;
            opacity: .7;
            min-height: 2px;
            position: relative;
        }
        .spark-bar:hover { opacity: 1; }
        .spark-bar:hover::after {
            content: attr(data-tip);
            position: absolute;
            bottom: calc(100% + 4px);
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            font-size: .7rem;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
            pointer-events: none;
        }
        .spark-labels {
            display: flex;
            justify-content: space-between;
            font-size: .65rem;
            color: #bbb;
            margin-top: .35rem;
        }

        /* ── Chart sections ── */
        .chart-section {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.08);
            margin-bottom: 1.5rem;
        }
        .chart-section h2 {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #888;
            margin-bottom: 1.25rem;
        }

        /* Bar rows */
        .bar-row {
            display: grid;
            grid-template-columns: 240px 1fr 44px;
            align-items: center;
            gap: .75rem;
            margin-bottom: .6rem;
        }
        .bar-label {
            font-size: .875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bar-track {
            background: #eef1f5;
            border-radius: 4px;
            height: 26px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width .4s ease;
        }
        .bar-count {
            font-size: .85rem;
            font-weight: 700;
            color: #555;
            text-align: right;
        }

        /* ── Empty / error states ── */
        .empty {
            color: #999;
            font-size: .9rem;
            padding: .5rem 0;
        }
        .error-box {
            background: #fff8f8;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            color: #721c24;
            font-size: .9rem;
            margin-bottom: 1.5rem;
        }
        .warn-box {
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-radius: 8px;
            padding: .75rem 1.25rem;
            color: #5d4037;
            font-size: .8rem;
            margin-bottom: 1.5rem;
        }

        /* ── Section divider label ── */
        .section-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #fff;
            padding: .3rem .75rem;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .section-label.sentiment { background: var(--blue); }
        .section-label.raw       { background: #6c757d; }

        .section-note {
            font-size: .8rem;
            color: #888;
            margin-bottom: 1.25rem;
            line-height: 1.5;
        }

        @media (max-width: 640px) {
            .bar-row { grid-template-columns: 1fr 60px; }
            .bar-label { grid-column: 1 / -1; }
            header { padding: 1rem; }
        }
    </style>
</head>
<body>

<header>
    <h1>📊 AI Policy Generator — Analytics</h1>
    <a href="?logout=1">Sign out</a>
</header>

<main>

    <?php if (DASHBOARD_PASSWORD === 'changeme'): ?>
    <div class="warn-box">
        ⚠️ <strong>Default password still set.</strong>
        Open <code>analytics-dashboard.php</code> and change <code>DASHBOARD_PASSWORD</code> before sharing this URL.
    </div>
    <?php endif; ?>

    <?php if ($dbError): ?>
    <div class="error-box"><?= htmlspecialchars($dbError) ?></div>
    <?php endif; ?>

    <!-- Summary cards -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="num"><?= number_format($totals['total']) ?></div>
            <div class="lbl">Total Events</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= number_format($sentiment['total']) ?></div>
            <div class="lbl">Sentiment (Copy/Share)</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= number_format($raw['total']) ?></div>
            <div class="lbl">Raw (Clicks)</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= number_format($totals['last7']) ?></div>
            <div class="lbl">Last 7 Days</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= number_format($totals['today']) ?></div>
            <div class="lbl">Today</div>
        </div>
        <div class="stat-card">
            <div class="num"><?= number_format($noAnalyticsClicks) ?></div>
            <div class="lbl">No-Analytics Clicks</div>
        </div>
    </div>

    <!-- 30-day sparkline (all events combined) -->
    <?php if (!empty($daily)): ?>
    <div class="sparkline-section">
        <h2>Activity — Last 30 Days (all events)</h2>
        <div class="sparkline">
            <?php
            $period  = new DatePeriod(new DateTime('-29 days'), new DateInterval('P1D'), new DateTime('tomorrow'));
            $allDays = [];
            foreach ($period as $d) $allDays[$d->format('Y-m-d')] = $daily[$d->format('Y-m-d')] ?? 0;
            foreach ($allDays as $day => $cnt):
                $pct = $maxDay > 0 ? round($cnt / $maxDay * 100) : 0;
            ?>
            <div class="spark-bar"
                 style="height:<?= max($pct, 2) ?>%"
                 data-tip="<?= htmlspecialchars($day . ': ' . $cnt) ?>"></div>
            <?php endforeach; ?>
        </div>
        <div class="spark-labels">
            <span><?= htmlspecialchars((new DateTime('-29 days'))->format('M j')) ?></span>
            <span>Today</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════════
         SENTIMENT ANALYSIS  (Copy / Share events)
         Represents deliberate, final policy selections.
    ═══════════════════════════════════════════════════════════════════ -->

    <div class="chart-section">
        <span class="section-label sentiment">Sentiment Analysis</span>
        <p class="section-note">
            Recorded when an instructor clicks <strong>Copy</strong> or <strong>Share URL</strong> —
            their deliberate, finalised policy selection. <em><?= number_format($sentiment['total']) ?> event<?= $sentiment['total'] !== 1 ? 's' : '' ?>.</em>
        </p>
        <h2>How may learners use AI?</h2>
        <?php renderBars($sentiment['use_cases'], $UC_LABELS, 'blue'); ?>
    </div>

    <div class="chart-section">
        <span class="section-label sentiment">Sentiment Analysis</span>
        <p class="section-note">
            Recorded when an instructor clicks <strong>Copy</strong> or <strong>Share URL</strong>.
        </p>
        <h2>What should learners include when submitting?</h2>
        <?php renderBars($sentiment['documentation'], $DOC_LABELS, 'green'); ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         RAW DATA  (every checkbox click)
         Captures exploratory behaviour — all clicks, including mid-session
         changes that the instructor may not have committed to.
    ═══════════════════════════════════════════════════════════════════ -->

    <div class="chart-section">
        <span class="section-label raw">Raw Data</span>
        <p class="section-note">
            Recorded on every checkbox click, including mid-session changes.
            Captures exploratory interest, not necessarily final intent. <em><?= number_format($raw['total']) ?> event<?= $raw['total'] !== 1 ? 's' : '' ?>.</em>
        </p>
        <h2>How may learners use AI?</h2>
        <?php renderBars($raw['use_cases'], $UC_LABELS, 'blue'); ?>
    </div>

    <div class="chart-section">
        <span class="section-label raw">Raw Data</span>
        <p class="section-note">
            Recorded on every checkbox click, including mid-session changes.
        </p>
        <h2>What should learners include when submitting?</h2>
        <?php renderBars($raw['documentation'], $DOC_LABELS, 'green'); ?>
    </div>

</main>
</body>
</html>
