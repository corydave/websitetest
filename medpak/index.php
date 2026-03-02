<?php
// ─── Image serving endpoint ──────────────────────────────────────────────────
// ?img=SUBDIR/filename  →  serves an image file from within medpak/
// Using a query-parameter URL avoids all host/path resolution ambiguity.
if (isset($_GET['img'])) {
    $img = $_GET['img'];
    // Allow only safe characters; strip any directory traversal
    $img = preg_replace('/[^a-zA-Z0-9\/\-_\.]/', '', $img);
    $img = str_replace('..', '', $img);
    $full = __DIR__ . '/' . ltrim($img, '/');
    if (file_exists($full) && is_file($full)) {
        $mime = [
            'png'  => 'image/png',  'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg', 'gif'  => 'image/gif',
            'webp' => 'image/webp', 'svg'  => 'image/svg+xml',
        ][strtolower(pathinfo($full, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
        header("Content-Type: $mime");
        readfile($full);
    } else {
        http_response_code(404);
    }
    exit;
}

// ─── Raw markdown endpoint ───────────────────────────────────────────────────
// ?raw=SUBFOLDER  →  serves DOCS.md with image paths rewritten to ?img= URLs
// ?img= is query-string-only so it resolves to this same PHP file regardless
// of how the server URL is configured (Codespace, local, reverse proxy, etc.)
if (isset($_GET['raw'])) {
    $sub = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['raw']);
    $path = __DIR__ . "/$sub/DOCS.md";
    if (file_exists($path)) {
        header('Content-Type: text/plain; charset=utf-8');
        $md = file_get_contents($path);
        // Rewrite relative images: ![alt](file.png) → ![alt](?img=SUB/file.png)
        $md = preg_replace_callback(
            '/!\[([^\]]*)\]\((?!https?:\/\/)(?!\?)(?!\/)([^)]+)\)/',
            fn($m) => "![{$m[1]}](?img=$sub/{$m[2]})",
            $md
        );
        // Strip feature-image comments (they're only for the card thumbnail)
        $md = preg_replace('/<!--\s*feature:[^>]+-->[\r\n]*/i', '', $md);
        echo $md;
    } else {
        http_response_code(404);
    }
    exit;
}

// ─── App discovery ────────────────────────────────────────────────────────────
function parseAppDocs(string $content, string $subdir): array {
    $app = ['subdir' => $subdir, 'title' => $subdir, 'description' => '', 'image' => null];

    // Feature image via HTML comment: <!-- feature: filename.png -->
    if (preg_match('/<!--\s*feature:\s*(.+?)\s*-->/i', $content, $m)) {
        $src = trim($m[1]);
        // Store as SUBDIR/file for use in ?img= URL; pass through absolute URLs as-is
        $app['image'] = preg_match('/^https?:\/\//', $src) ? $src : "$subdir/$src";
    }

    // H1 title
    if (preg_match('/^#\s+(.+)$/m', $content, $m)) {
        $app['title'] = trim($m[1]);

        // First paragraph of text after the H1
        $after = ltrim(substr($content, strpos($content, $m[0]) + strlen($m[0])));
        $para = [];
        foreach (explode("\n", $after) as $line) {
            $line = trim($line);
            if ($line === '' || $line === '---') {
                if (!empty($para)) break;
                continue;
            }
            if (preg_match('/^#{1,6}\s/', $line)) break;
            if (preg_match('/^[|!\[]/', $line)) break;
            $para[] = $line;
        }
        $desc = implode(' ', $para);
        $desc = preg_replace('/\*\*([^*]+)\*\*/', '$1', $desc);
        $desc = preg_replace('/\*([^*]+)\*/', '$1', $desc);
        $desc = preg_replace('/`([^`]+)`/', '$1', $desc);
        $desc = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $desc);
        $app['description'] = $desc;
    }

    // Fallback: first image in doc
    if (!$app['image'] && preg_match('/!\[[^\]]*\]\(([^)]+)\)/', $content, $m)) {
        $src = $m[1];
        $app['image'] = preg_match('/^https?:\/\//', $src) ? $src : "$subdir/$src";
    }

    return $app;
}

$apps = [];
foreach (glob(__DIR__ . '/*/DOCS.md') as $docsPath) {
    $subdir = basename(dirname($docsPath));
    $apps[] = parseAppDocs(file_get_contents($docsPath), $subdir);
}

// SCRIPT_NAME is always the real script path (e.g. /medpak/index.php) regardless
// of whether the browser URL has a trailing slash or not — safe to use for links.
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedPak — Clinical Simulation Tools</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        #modal-backdrop.hidden { display: none; }
        #modal-body img { max-width: 100%; border-radius: 0.5rem; margin: 1rem 0; }
        .app-card { transition: box-shadow 0.15s ease, transform 0.15s ease; }
        .app-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.12); }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">

    <!-- ── Header ─────────────────────────────────────────────────────────── -->
    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center gap-4">
            <div class="bg-indigo-600 text-white rounded-xl px-3 py-1.5 font-black text-lg tracking-tight leading-none">
                Med<span class="text-indigo-200">Pak</span>
            </div>
            <div>
                <div class="font-semibold text-slate-900 leading-tight">Clinical Simulation Tools</div>
                <div class="text-xs text-slate-400">Browse and launch simulation apps</div>
            </div>
        </div>
    </header>

    <!-- ── App Grid ───────────────────────────────────────────────────────── -->
    <main class="flex-1 max-w-6xl mx-auto w-full px-6 py-10">

        <?php if (empty($apps)): ?>
            <div class="text-center text-slate-400 py-24">
                <p class="text-lg">No apps found yet.<br>Add a subfolder with a <code class="font-mono text-sm">DOCS.md</code> to get started.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($apps as $app): ?>
                    <?php
                    // Build the thumbnail src: use ?img= for local paths, pass through absolute URLs
                    $thumbSrc = $app['image']
                        ? (preg_match('/^https?:\/\//', $app['image'])
                            ? $app['image']
                            : '?img=' . urlencode($app['image']))
                        : null;
                    ?>
                    <button
                        class="app-card bg-white rounded-2xl shadow-md overflow-hidden text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                        data-app="<?= htmlspecialchars($app['subdir']) ?>"
                        data-title="<?= htmlspecialchars($app['title']) ?>"
                    >
                        <!-- Thumbnail -->
                        <div class="w-full aspect-video bg-slate-200 overflow-hidden">
                            <?php if ($thumbSrc): ?>
                                <img src="<?= htmlspecialchars($thumbSrc) ?>"
                                     alt="<?= htmlspecialchars($app['title']) ?>"
                                     class="w-full h-full object-cover" />
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card body -->
                        <div class="p-5">
                            <h2 class="font-bold text-slate-900 text-lg leading-snug mb-1">
                                <?= htmlspecialchars($app['title']) ?>
                            </h2>
                            <?php if ($app['description']): ?>
                                <p class="text-slate-500 text-sm leading-relaxed line-clamp-3">
                                    <?= htmlspecialchars($app['description']) ?>
                                </p>
                            <?php endif; ?>
                            <span class="mt-4 inline-block text-indigo-600 text-sm font-semibold">
                                View details →
                            </span>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- ── Modal ──────────────────────────────────────────────────────────── -->
    <div id="modal-backdrop"
         class="hidden fixed inset-0 bg-black/50 z-50 flex items-start justify-center p-4 sm:p-8 overflow-y-auto"
         role="dialog" aria-modal="true">

        <div id="modal-box"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl my-auto flex flex-col">

            <!-- Modal header -->
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-200 sticky top-0 bg-white rounded-t-2xl z-10">
                <h2 id="modal-title" class="flex-1 font-bold text-slate-900 text-lg truncate"></h2>
                <a id="modal-open-link"
                   href="#"
                   class="flex-shrink-0 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                    Open App →
                </a>
                <button id="modal-close"
                        class="flex-shrink-0 text-slate-400 hover:text-slate-700 transition-colors p-1 rounded"
                        aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal body: rendered markdown -->
            <div id="modal-body" class="prose prose-slate prose-img:rounded-lg max-w-none px-6 py-6 overflow-y-auto">
                <p class="text-slate-400 text-sm">Loading…</p>
            </div>
        </div>
    </div>

    <script>
        marked.use({ breaks: false, gfm: true });

        const backdrop = document.getElementById('modal-backdrop');
        const box      = document.getElementById('modal-box');
        const titleEl  = document.getElementById('modal-title');
        const bodyEl   = document.getElementById('modal-body');
        const openLink = document.getElementById('modal-open-link');
        const closeBtn = document.getElementById('modal-close');

        // SCRIPT_DIR comes from PHP's SCRIPT_NAME — always the real path to this
        // file (e.g. "/medpak/") regardless of trailing-slash in the browser URL.
        const SCRIPT_DIR = "<?= addslashes($scriptDir) ?>";
        const phpBase    = window.location.pathname;

        function openModal(subdir, title) {
            titleEl.textContent = title;
            openLink.href = SCRIPT_DIR + subdir + '/';
            bodyEl.innerHTML = '<p class="text-slate-400 text-sm">Loading…</p>';
            backdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            fetch(phpBase + '?raw=' + encodeURIComponent(subdir))
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(md => { bodyEl.innerHTML = marked.parse(md); })
                .catch(e => { bodyEl.innerHTML = '<p class="text-red-500">Failed to load documentation. (' + e.message + ')</p>'; });
        }

        function closeModal() {
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.app-card').forEach(card => {
            card.addEventListener('click', () => openModal(card.dataset.app, card.dataset.title));
        });
        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', e => { if (!box.contains(e.target)) closeModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    </script>

</body>
</html>
