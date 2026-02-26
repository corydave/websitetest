<?php
/**
 * AI Policy Generator — Anonymous Analytics Collector
 *
 * Accepts POST requests with a JSON body:
 *   { "useCases": [...], "documentation": [...] }
 *
 * Stores each event in an SQLite database at ../data/analytics.db
 * (one directory above this file).  The data/ directory is created
 * automatically on first use, along with an .htaccess that blocks
 * direct web access to the database file.
 */


/**
 *
 * TO MODIFY THE RATE LIMIT:
 * Line 15: define('RATE_LIMIT', 20);   // max submissions per IP per hour
 * Line 77: $since = date('Y-m-d H:i:s', strtotime('-1 hour'));
 * 
 * 
 * Change '-1 hour' to any value strtotime accepts, e.g.:
 *   '-30 minutes'
 *   '-6 hours'
 *   '-1 day'
 * 
 */

define('DB_PATH',    __DIR__ . '/../data/analytics.db');
define('RATE_LIMIT', 100000);   // max submissions per IP per hour

// ── CORS / method checks ──────────────────────────────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); exit; }

// ── Whitelist of valid option values ─────────────────────────────────────────
$VALID_USE_CASES = ['prohibited','encouraged','brainstorming','research',
                    'outline','content','critique','analysis','other'];
$VALID_DOCS      = ['none','tools','chatsummary','prompts','citation',
                    'reflection','factcheck','multitools','other'];

// ── Parse & validate input ────────────────────────────────────────────────────
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) { http_response_code(400); exit; }

$useCases = array_values(array_filter(
    (array)($data['useCases']     ?? []),
    fn($v) => in_array($v, $VALID_USE_CASES, true)
));
$docs = array_values(array_filter(
    (array)($data['documentation'] ?? []),
    fn($v) => in_array($v, $VALID_DOCS, true)
));

// Require at least one selection
if (empty($useCases) && empty($docs)) { http_response_code(400); exit; }

// ── Database ──────────────────────────────────────────────────────────────────
try {
    $dir = dirname(DB_PATH);

    // Create data/ directory and protect it on first run
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n");
    }

    $db = new PDO('sqlite:' . DB_PATH, '', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $db->exec("
        CREATE TABLE IF NOT EXISTS events (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            ts            TEXT    NOT NULL DEFAULT (datetime('now')),
            ip_hash       TEXT    NOT NULL,
            use_cases     TEXT    NOT NULL DEFAULT '[]',
            documentation TEXT    NOT NULL DEFAULT '[]',
            event_type    TEXT    NOT NULL DEFAULT 'raw'
        )
    ");

    // Migrate existing databases that predate the event_type column
    try {
        $db->exec("ALTER TABLE events ADD COLUMN event_type TEXT NOT NULL DEFAULT 'raw'");
    } catch (Exception $e) {
        // Column already exists — safe to ignore
    }

    // Validate event_type from request ('raw' or 'sentiment'; default 'raw')
    $eventType = (($data['eventType'] ?? '') === 'sentiment') ? 'sentiment' : 'raw';

    // Rate-limit: reject if this IP has sent >= RATE_LIMIT events in the last hour
    $ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $since   = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $check   = $db->prepare("SELECT COUNT(*) FROM events WHERE ip_hash = ? AND ts > ?");
    $check->execute([$ip_hash, $since]);
    if ((int)$check->fetchColumn() >= RATE_LIMIT) { http_response_code(429); exit; }

    $stmt = $db->prepare(
        "INSERT INTO events (ip_hash, use_cases, documentation, event_type) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$ip_hash, json_encode($useCases), json_encode($docs), $eventType]);

    http_response_code(204);

} catch (Exception $e) {
    error_log('Analytics DB error: ' . $e->getMessage());
    http_response_code(500);
}
