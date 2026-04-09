<?php
session_start();
$action = $_GET['action'] ?? '';
$view = $_GET['view'] ?? '';
$id = $_GET['id'] ?? '';

// Helper for temporary file storage
function getFilePath($id) {
    return sys_get_temp_dir() . '/telemetry_sim_' . preg_replace('/[^a-zA-Z0-9]/', '', $id) . '.json';
}

// API: Start a new session
if ($action === 'start') {
    $id = substr(md5(uniqid()), 0, 8);
    $defaultState = [
        'rhythm' => 'sinus', 'saNodeRate' => 80, 'ventricularRate' => 150,
        'flutterRatio' => '1:2', 'svtRate' => 180, 'vtRate' => 160,
        'torsadesRate' => 220, 'escapeRate' => 40, 'prProlongation' => 0.32,
        'blockSeverity' => 3, 'spo2' => 98, 'rr' => 16, 'sysBp' => 120, 'diaBp' => 80,
        'joules' => 150, 'shockThreshold' => 100, 'shockTrigger' => 0,
        'wideQRS' => false, 'qtc' => 0.40, 'syncMode' => false,
        'pacingOn' => false, 'pacingRate' => 80, 'pacingOutput' => 80,
        'pacingCaptureEnabled' => true, 'pacingCaptureThreshold' => 80
    ];
    file_put_contents(getFilePath($id), json_encode($defaultState));
    
    $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $base = explode('?', $actual_link)[0];
    $simUrl = $base . "?view=sim&id=" . $id;
    $ctrlUrl = $base . "?view=ctrl&id=" . $id;
} 
// API: Poll state
elseif ($action === 'poll' && $id) {
    $file = getFilePath($id);
    if (file_exists($file)) {
        header('Content-Type: application/json');
        echo file_get_contents($file);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
} 
// API: Update state
elseif ($action === 'update' && $id) {
    $file = getFilePath($id);
    if (file_exists($file)) {
        $current = json_decode(file_get_contents($file), true);
        $input = json_decode(file_get_contents('php://input'), true);
        $new = array_merge($current, $input);
        file_put_contents($file, json_encode($new));
        echo json_encode(['status' => 'ok']);
    }
    exit;
}

$isSim = ($view === 'sim');
$isCtrl = ($view === 'ctrl');
$isHome = (!$isSim && !$isCtrl && $action !== 'start');
$isLinks = ($action === 'start');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemetry Monitor System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ctrl: sticky wrapper (must NOT have overflow:hidden — that's why it's a separate div from the monitor) */
        @media screen and (max-width: 1023px) {
            #sim-view-wrapper .waves-area { flex: none; }
            .ctrl-sticky-wrap { position: sticky; top: 0; z-index: 50; background: #020617; }
            .ctrl-sticky-wrap .waves-area { flex: none; height: 100px !important; }
            .ctrl-sticky-wrap #main-monitor { margin-bottom: 0; border-radius: 0; border-left: none; border-right: none; }
            .ctrl-sticky-wrap .vitals-sidebar { display: none; }
            .ctrl-sticky-wrap .ctrl-trace-hide { display: none; }
        }
        /* Sim landscape: fill the screen */
        @media screen and (orientation: landscape) and (max-width: 1023px) {
            #sim-view-wrapper #main-monitor { max-width: 100%; border-radius: 0; margin-bottom: 0; }
            #sim-view-wrapper .waves-area { height: calc(100vh - 2.5rem); height: calc(100svh - 2.5rem); }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 font-sans min-h-screen flex flex-col items-center">

    <?php if ($isHome): ?>
        <!-- LANDING PAGE -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 text-center w-full max-w-2xl">
            <svg class="w-24 h-24 text-indigo-500 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-white">Telemetry Simulator</h1>
            <p class="text-slate-400 text-lg mb-10">Initialize a cloud synchronized session to generate independent URLs for the simulation viewer and the instructor control panel.</p>
            <a href="?action=start" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 px-10 rounded-xl shadow-[0_0_20px_rgba(79,70,229,0.4)] text-xl transition-all hover:scale-105">
                Start New Session
            </a>
        </div>

    <?php elseif ($isLinks): ?>
        <!-- LINKS PAGE -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 w-full max-w-4xl">
            <div class="bg-slate-800 border-2 border-slate-700 p-8 rounded-2xl w-full shadow-2xl">
                <h2 class="text-3xl font-bold mb-6 text-white flex items-center gap-3">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Session Established
                </h2>
                
                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-400 mb-2 uppercase tracking-wider">Simulation Viewer URL (Patient Display)</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="<?= htmlspecialchars($simUrl) ?>" class="flex-1 bg-slate-900 text-cyan-400 font-mono border border-slate-600 rounded-lg p-3 outline-none" />
                        <a href="<?= htmlspecialchars($simUrl) ?>" target="_blank" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-3 px-6 rounded-lg transition-colors">Open</a>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-2 uppercase tracking-wider">Instructor Controller URL (Admin Panel)</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="<?= htmlspecialchars($ctrlUrl) ?>" class="flex-1 bg-slate-900 text-indigo-400 font-mono border border-slate-600 rounded-lg p-3 outline-none" />
                        <a href="<?= htmlspecialchars($ctrlUrl) ?>" target="_blank" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg transition-colors">Open</a>
                    </div>
                    <div class="mt-4">
                        <div class="bg-white p-2 rounded-lg inline-block">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($ctrlUrl) ?>"
                                 class="w-44 h-44 block" alt="QR Code for Instructor Controls" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- MAIN SIMULATION APP -->
        <div <?= $isSim ? 'id="sim-view-wrapper"' : '' ?> class="w-full p-2 md:p-6 flex flex-col items-center">
            <!-- MONITOR UI -->
            <?php if ($isCtrl): ?><div class="ctrl-sticky-wrap w-full max-w-6xl"><?php endif; ?>
            <div id="main-monitor" class="w-full max-w-6xl bg-black border-4 border-slate-700 rounded-2xl overflow-hidden shadow-2xl flex flex-col mb-8">
                
                <!-- Monitor Header -->
                <div class="bg-slate-950 px-4 py-2 flex justify-between items-center border-b border-slate-800 text-sm md:text-base">
                    <div class="flex gap-4 items-center">
                        <span class="font-bold text-white tracking-widest">SIM-PAT-01</span>
                        <span class="text-slate-400">SESSION: <?= htmlspecialchars($id) ?></span>
                    </div>
                    <div class="text-slate-400 flex gap-4">
                        <span id="time-display">--:--:--</span>
                    </div>
                </div>

                <!-- Monitor Body -->
                <div class="flex flex-col lg:flex-row relative">
                    
                    <!-- Waves Area -->
                    <div class="waves-area flex-1 relative h-[300px] lg:h-[450px] overflow-hidden">
                        <canvas id="bgCanvas" width="1200" height="450" class="absolute top-0 left-0 w-full h-full object-fill"></canvas>
                        <canvas id="mainCanvas" width="1200" height="450" class="absolute top-0 left-0 w-full h-full object-fill z-10"></canvas>
                        
                        <!-- Trace Labels -->
                        <div class="absolute top-4 left-4 z-20 text-green-500 font-bold text-sm">II <span class="text-xs ml-2">1 mV</span></div>
                        <div class="ctrl-trace-hide absolute top-[180px] left-4 z-20 text-cyan-500 font-bold text-sm">SpO2</div>
                        <div class="ctrl-trace-hide absolute top-[310px] left-4 z-20 text-yellow-500 font-bold text-sm">RESP</div>
                    </div>

                    <!-- Vitals Sidebar -->
                    <div class="vitals-sidebar w-full lg:w-72 bg-slate-950 border-t lg:border-t-0 lg:border-l border-slate-800 p-3 grid grid-cols-2 lg:flex lg:flex-col gap-3 lg:gap-0">

                        <!-- HR Box -->
                        <div class="bg-slate-900 lg:bg-transparent rounded-lg lg:rounded-none p-3 lg:p-4 lg:border-b border-slate-800 flex flex-col items-end text-green-500">
                            <div class="w-full flex justify-between items-start text-xs font-bold mb-1">
                                <span>HR</span>
                                <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <div id="val-hr" class="text-4xl lg:text-7xl font-bold font-mono tracking-tighter">---</div>
                            <div class="w-full flex justify-end text-xs opacity-70 gap-2 mt-1">
                                <span>120</span>
                                <span>50</span>
                            </div>
                        </div>

                        <!-- SpO2 Box -->
                        <div class="bg-slate-900 lg:bg-transparent rounded-lg lg:rounded-none p-3 lg:p-4 lg:border-b border-slate-800 flex flex-col items-end text-cyan-500">
                            <div class="w-full flex justify-between items-start text-xs font-bold mb-1">
                                <span>SpO2 %</span>
                                <span>PR <span id="val-pr">-</span></span>
                            </div>
                            <div id="val-spo2" class="text-3xl lg:text-5xl font-bold font-mono tracking-tighter">---</div>
                        </div>

                        <!-- NIBP Box -->
                        <div class="bg-slate-900 lg:bg-transparent rounded-lg lg:rounded-none p-3 lg:p-4 lg:border-b border-slate-800 flex flex-col items-end text-white">
                            <div class="w-full flex justify-between items-start text-xs font-bold mb-1">
                                <span>NIBP mmHg</span>
                                <span class="opacity-70">Auto</span>
                            </div>
                            <div class="text-xl lg:text-3xl font-bold font-mono">
                                <span id="val-sysBp">---</span>/<span id="val-diaBp">---</span>
                            </div>
                            <div class="text-sm opacity-70 font-mono mt-1">
                                (<span id="val-map">---</span>)
                            </div>
                        </div>

                        <!-- RESP Box -->
                        <div class="bg-slate-900 lg:bg-transparent rounded-lg lg:rounded-none p-3 lg:p-4 flex flex-col items-end text-yellow-500">
                            <div class="w-full flex justify-between items-start text-xs font-bold mb-1">
                                <span>RESP rpm</span>
                            </div>
                            <div id="val-rr" class="text-3xl lg:text-4xl font-bold font-mono tracking-tighter">16</div>
                        </div>

                    </div>
                </div>
            </div>
            <?php if ($isCtrl): ?>
                <!-- Compact vitals strip — ctrl mobile only (hidden lg+) -->
                <div class="flex lg:hidden w-full bg-slate-950 border-t-2 border-slate-800 divide-x divide-slate-800">
                    <div class="flex-1 flex flex-col items-center py-2 text-green-500">
                        <span class="text-[10px] font-bold uppercase opacity-60 tracking-wider">HR</span>
                        <span id="ctrl-val-hr" class="text-2xl font-bold font-mono leading-none mt-0.5">---</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center py-2 text-cyan-500">
                        <span class="text-[10px] font-bold uppercase opacity-60 tracking-wider">SpO2%</span>
                        <span id="ctrl-val-spo2" class="text-2xl font-bold font-mono leading-none mt-0.5">---</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center py-2 text-white">
                        <span class="text-[10px] font-bold uppercase opacity-60 tracking-wider">NIBP</span>
                        <span id="ctrl-val-bp" class="text-lg font-bold font-mono leading-none mt-0.5">---/---</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center py-2 text-yellow-500">
                        <span class="text-[10px] font-bold uppercase opacity-60 tracking-wider">RESP</span>
                        <span id="ctrl-val-rr" class="text-2xl font-bold font-mono leading-none mt-0.5">---</span>
                    </div>
                </div>
            </div><?php endif; ?>

            <?php if ($isSim): ?>
            <!-- LEARNER DEFIB + PACING PANELS (side-by-side on wider screens) -->
            <div class="mt-4 flex flex-col md:flex-row gap-4 w-full max-w-6xl">

            <!-- LEARNER DEFIBRILLATOR PANEL -->
            <div class="flex-1 bg-red-950/30 border-2 border-red-800 rounded-xl p-6 shadow-xl flex flex-col sm:flex-row items-center gap-6">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-bold text-red-400 mb-2 uppercase tracking-wider">
                        Shock Energy: <span id="lbl-learnerJoules">150</span> J
                    </label>
                    <input type="range" id="input-learnerJoules" min="0" max="8" step="1" value="5" class="w-full max-w-[500px] accent-red-500 cursor-pointer" />
                </div>
                <div class="flex flex-col gap-2 w-full sm:w-auto flex-shrink-0">
                    <button id="btn-learner-sync" class="w-full bg-[#CF9C00] text-black font-bold rounded-xl py-2 px-8 border-2 border-[#CF9C00] text-sm uppercase tracking-widest transition-all">SYNC</button>
                    <button id="btn-learner-shock" class="w-full bg-red-600 hover:bg-red-500 active:bg-red-700 text-white font-black rounded-xl py-4 px-8 shadow-lg shadow-red-900/40 border-2 border-red-400 flex items-center justify-center gap-2 text-xl uppercase transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        SHOCK
                    </button>
                </div>
            </div>

            <!-- LEARNER PACING PANEL -->
            <div class="flex-1 bg-blue-950/30 border-2 border-blue-800 rounded-xl p-6 shadow-xl flex flex-col sm:flex-row items-center gap-6">
                <button id="btn-pacing" class="w-full sm:w-auto flex-shrink-0 bg-slate-700 text-slate-300 font-black rounded-xl py-4 px-8 border-2 border-slate-600 text-xl uppercase tracking-wide transition-all">
                    PACING OFF
                </button>
                <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-blue-400 mb-2 uppercase tracking-wider">Rate: <span id="lbl-pacingRate">80</span> ppm</label>
                        <input type="range" id="input-pacingRate" min="40" max="140" step="10" value="80" class="w-full accent-blue-500 cursor-pointer" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-blue-400 mb-2 uppercase tracking-wider">Output: <span id="lbl-pacingOutput">80</span> <span class="normal-case">mA</span></label>
                        <input type="range" id="input-pacingOutput" min="10" max="200" step="10" value="80" class="w-full accent-blue-500 cursor-pointer" />
                    </div>
                </div>
            </div>

            </div><!-- end defib+pacing row -->

            <?php endif; ?>

            <!-- ADMIN CONTROLS (Hidden if on SIM view) -->
            <div id="admin-panel" class="w-full max-w-6xl bg-slate-800 rounded-xl p-6 shadow-xl border border-slate-700" <?= $isSim ? 'style="display: none;"' : '' ?>>
                <div class="flex items-center gap-3 mb-6 border-b border-slate-700 pb-4">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <h2 class="text-xl font-semibold text-white">Simulation Admin Controls</h2>
                    <span class="ml-auto text-xs bg-indigo-900 text-indigo-200 px-3 py-1 rounded-full border border-indigo-700">LIVE SYNC ACTIVE</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                    <!-- Rhythm Selection -->
                    <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Cardiac Rhythm</label>
                        <select id="input-rhythm" class="w-full bg-slate-800 text-white border border-slate-600 rounded-lg p-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <optgroup label="Sinus / Atrial">
                                <option value="sinus">Normal Sinus Rhythm</option>
                                <option value="afib">Atrial Fibrillation</option>
                                <option value="aflutter">Atrial Flutter</option>
                                <option value="svt">Supraventricular Tachycardia</option>
                            </optgroup>
                            <optgroup label="Ventricular">
                                <option value="vtach">Monomorphic V-Tach</option>
                                <option value="torsades">Torsades de Pointes</option>
                            </optgroup>
                            <optgroup label="AV Blocks">
                                <option value="1st_degree">1st Degree AV Block</option>
                                <option value="2nd_degree_type1">2nd Degree Block Type I (Wenckebach)</option>
                                <option value="2nd_degree_type2">2nd Degree Block Type II</option>
                                <option value="3rd_degree">3rd Degree AV Block</option>
                            </optgroup>
                            <optgroup label="Lethal">
                                <option value="vfib">Ventricular Fibrillation</option>
                                <option value="asystole">Asystole</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Contextual Rhythm Controls -->
                    <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700 md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6 relative">
                        
                        <div id="ctrl-saNodeRate" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">SA Node Rate: <span id="lbl-saNodeRate">80</span> bpm</label>
                            <input type="range" id="input-saNodeRate" min="20" max="220" value="80" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-ventricularRate" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Ventricular Response Rate: <span id="lbl-ventricularRate">150</span> bpm</label>
                            <input type="range" id="input-ventricularRate" min="20" max="220" value="150" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-flutterRatio" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Flutter Ratio</label>
                            <select id="input-flutterRatio" class="w-full bg-slate-800 border border-slate-600 rounded p-2 text-white">
                                <option value="1:1">1:1 (300 bpm)</option>
                                <option value="1:2" selected>1:2 (150 bpm)</option>
                                <option value="1:3">1:3 (100 bpm)</option>
                                <option value="1:4">1:4 (75 bpm)</option>
                            </select>
                        </div>

                        <div id="ctrl-svtRate" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">SVT Rate: <span id="lbl-svtRate">180</span> bpm</label>
                            <input type="range" id="input-svtRate" min="150" max="250" value="180" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-vtRate" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">V-Tach Rate: <span id="lbl-vtRate">160</span> bpm</label>
                            <input type="range" id="input-vtRate" min="100" max="250" value="160" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-torsadesRate" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Torsades Rate: <span id="lbl-torsadesRate">220</span> bpm</label>
                            <input type="range" id="input-torsadesRate" min="150" max="300" value="220" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-prProlongation" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">PR Prolongation: <span id="lbl-prProlongation">0.32</span> s</label>
                            <input type="range" id="input-prProlongation" min="0.2" max="0.4" step="0.02" value="0.32" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-blockSeverity" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Block Ratio (P:QRS): <span id="lbl-blockSeverity" class="text-white font-semibold">3:2</span></label>
                            <input type="range" id="input-blockSeverity" min="0" max="5" step="1" value="3" class="w-full accent-indigo-500" />
                            <div class="grid grid-cols-6 text-center mt-1 select-none" style="font-size:0.68rem">
                                <span class="text-red-400">3:1↑</span>
                                <span class="text-slate-300">2:1</span>
                                <span class="text-slate-500">·</span>
                                <span class="text-slate-300">3:2</span>
                                <span class="text-slate-500">·</span>
                                <span class="text-slate-300">4:3</span>
                            </div>
                        </div>

                        <div id="ctrl-escapeRate" class="admin-ctrl hidden">
                            <label class="block text-sm font-medium text-slate-400 mb-2">Ventricular Escape Rate: <span id="lbl-escapeRate">40</span> bpm</label>
                            <input type="range" id="input-escapeRate" min="20" max="60" value="40" class="w-full accent-indigo-500" />
                        </div>

                        <div id="ctrl-lethal" class="admin-ctrl hidden col-span-full">
                            <div class="flex items-center justify-center text-red-400 font-semibold p-4 bg-red-950/20 rounded-lg border border-red-900/50 w-full">
                                Lethal Rhythm Active. No structural controls available.
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Pacing Capture Controls -->
                <div class="mt-8 bg-blue-950/20 p-4 rounded-lg border border-blue-900/50 grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                    <div>
                        <label class="block text-sm font-medium text-blue-400 mb-3">Pacing Capture</label>
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" id="input-pacingCaptureEnabled" class="sr-only peer" checked />
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-blue-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                            <span class="text-sm text-slate-300">Capture Possible</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-400 mb-2">Capture Threshold: <span id="lbl-pacingCaptureThreshold">80</span> mA</label>
                        <input type="range" id="input-pacingCaptureThreshold" min="10" max="200" step="10" value="80" class="w-full accent-blue-400" />
                    </div>
                </div>

                <!-- Vitals Accordion -->
                <div class="mt-8 border border-slate-700 rounded-lg overflow-hidden">
                    <button id="btn-vitals-toggle" onclick="document.getElementById('vitals-panel').classList.toggle('hidden'); document.getElementById('vitals-chevron').classList.toggle('rotate-180')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-900/50 hover:bg-slate-900/80 text-left transition-colors">
                        <span class="text-sm font-semibold text-slate-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            Vitals
                        </span>
                        <svg id="vitals-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="vitals-panel" class="hidden border-t border-slate-700 bg-slate-900/30 p-4 grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">SpO2: <span id="lbl-spo2">98</span>%</label>
                            <input type="range" id="input-spo2" min="50" max="100" value="98" class="w-full accent-cyan-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">BP: <span id="lbl-bp">120/80 mmHg</span></label>
                            <input type="range" id="input-bp" min="0" max="22" step="1" value="8" class="w-full accent-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">RESP: <span id="lbl-rr">16</span> rpm</label>
                            <input type="range" id="input-rr" min="0" max="50" value="16" class="w-full accent-yellow-500" />
                        </div>
                    </div>
                </div>

                <!-- Advanced EKG Morphology Accordion -->
                <div class="mt-4 border border-slate-700 rounded-lg overflow-hidden">
                    <button id="btn-advanced-toggle" onclick="document.getElementById('advanced-panel').classList.toggle('hidden'); document.getElementById('advanced-chevron').classList.toggle('rotate-180')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-900/50 hover:bg-slate-900/80 text-left transition-colors">
                        <span class="text-sm font-semibold text-slate-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                            Advanced EKG Morphology
                        </span>
                        <svg id="advanced-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="advanced-panel" class="hidden border-t border-slate-700 bg-slate-900/30 p-4 grid grid-cols-1 sm:grid-cols-2 gap-6">

                        <!-- Wide QRS Toggle -->
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" id="input-wideQRS" class="sr-only peer" />
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-purple-600 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                            <div>
                                <div class="text-sm font-medium text-slate-200">Wide QRS</div>
                                <div class="text-xs text-slate-500">Forces 180 ms QRS duration</div>
                            </div>
                        </div>

                        <!-- QTc -->
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-2">QTc: <span id="lbl-qtc">400</span> ms</label>
                            <input type="range" id="input-qtc" min="380" max="600" step="10" value="400" class="w-full accent-purple-500" />
                        </div>

                    </div>
                </div>

                <!-- Defib Accordion -->
                <div class="mt-4 border border-slate-700 rounded-lg overflow-hidden">
                    <button onclick="document.getElementById('defib-panel').classList.toggle('hidden'); document.getElementById('defib-chevron').classList.toggle('rotate-180')" class="w-full flex items-center justify-between px-4 py-3 bg-slate-900/50 hover:bg-slate-900/80 text-left transition-colors">
                        <span class="text-sm font-semibold text-slate-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Defib
                        </span>
                        <svg id="defib-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div id="defib-panel" class="hidden border-t border-slate-700 bg-red-950/10 p-4 grid grid-cols-1 sm:grid-cols-3 gap-6 items-end">
                        <div>
                            <label class="block text-sm font-medium text-red-400 mb-2">Shock Energy: <span id="lbl-shockEnergy">150</span> J</label>
                            <input type="range" id="input-shockEnergy" min="0" max="8" step="1" value="5" class="w-full accent-red-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-red-400 mb-2">Conversion Threshold: <span id="lbl-shockThreshold">100</span> J</label>
                            <input type="range" id="input-shockThreshold" min="25" max="300" step="1" value="100" class="w-full accent-orange-500" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <button id="btn-sync" class="w-full bg-[#CF9C00] text-black font-bold rounded-lg p-2 transition-all border border-[#CF9C00] text-sm uppercase tracking-widest">SYNC</button>
                            <button id="btn-shock" class="w-full bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg p-2.5 transition-colors shadow-lg shadow-red-900/20 border border-red-500 flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                DELIVER SHOCK
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sessionId = "<?= htmlspecialchars($id) ?>";
                const isController = <?= $isCtrl ? 'true' : 'false' ?>;
                let lastShockTrigger = 0;
                let pendingTrigger = 0;

                // --- STATE ---
                const state = {
                    rhythm: 'sinus', saNodeRate: 80, ventricularRate: 150, flutterRatio: '1:2',
                    svtRate: 180, vtRate: 160, torsadesRate: 220, escapeRate: 40,
                    prProlongation: 0.32, blockSeverity: 3, spo2: 98, rr: 16,
                    sysBp: 120, diaBp: 80, liveHR: 0, baseHR: 0, joules: 150, shockThreshold: 100,
                    shockTrigger: 0, wideQRS: false, qtc: 0.40, syncMode: false,
                    pacingOn: false, pacingRate: 80, pacingOutput: 80,
                    pacingCaptureEnabled: true, pacingCaptureThreshold: 80
                };

                const simState = { rhythm: 'sinus', nextP: 0, nextQRS: 0, wenckebach_beat: 0, mobitz_beat: 0, mobitz_cycle_size: 2, time: 0, nextPace: 0, pacingWasOn: false };
                const events = [];
                const ctrlMobile = isController && window.innerWidth < 1024;
                const drawState = { lastX: 0, drawTime: 0, lastTimeReal: performance.now() / 1000, ekgY: ctrlMobile ? 225 : 90, plethY: 220, respY: 340 };

                let syncShockPending = false;
                let doShock = null;
                let lastKnownSyncMode = false;

                const markExistingQRSSynced = () => {
                    events.forEach(e => { if (e.type === 'QRS') e.syncMarked = true; });
                };

                const setSyncUI = () => {
                    ['btn-sync', 'btn-learner-sync'].forEach(id => {
                        const btn = document.getElementById(id);
                        if (!btn) return;
                        btn.classList.toggle('bg-yellow-400', !!state.syncMode);
                        btn.classList.toggle('border-yellow-300', !!state.syncMode);
                        btn.classList.toggle('bg-[#CF9C00]', !state.syncMode);
                        btn.classList.toggle('border-[#CF9C00]', !state.syncMode);
                    });
                };

                // Setup Clocks
                setInterval(() => { document.getElementById('time-display').innerText = new Date().toLocaleTimeString(); }, 1000);
                document.getElementById('time-display').innerText = new Date().toLocaleTimeString();

                // --- UI BINDINGS & UPDATES ---
                const updateUI = () => {
                    const rhythm = state.rhythm;
                    
                    if (isController) {
                        // Hide all admin controls
                        document.querySelectorAll('.admin-ctrl').forEach(el => el.classList.add('hidden'));

                        // Show relevant ones
                        if (['sinus', '1st_degree', '2nd_degree_type1', '2nd_degree_type2', '3rd_degree'].includes(rhythm)) {
                            document.getElementById('ctrl-saNodeRate')?.classList.remove('hidden');
                        }
                        if (rhythm === 'afib') document.getElementById('ctrl-ventricularRate')?.classList.remove('hidden');
                        if (rhythm === 'aflutter') document.getElementById('ctrl-flutterRatio')?.classList.remove('hidden');
                        if (rhythm === 'svt') document.getElementById('ctrl-svtRate')?.classList.remove('hidden');
                        if (rhythm === 'vtach') document.getElementById('ctrl-vtRate')?.classList.remove('hidden');
                        if (rhythm === 'torsades') document.getElementById('ctrl-torsadesRate')?.classList.remove('hidden');
                        if (rhythm === '1st_degree') document.getElementById('ctrl-prProlongation')?.classList.remove('hidden');
                        if (rhythm === '2nd_degree_type2') document.getElementById('ctrl-blockSeverity')?.classList.remove('hidden');
                        if (rhythm === '3rd_degree') document.getElementById('ctrl-escapeRate')?.classList.remove('hidden');
                        if (['vfib', 'asystole'].includes(rhythm)) document.getElementById('ctrl-lethal')?.classList.remove('hidden');

                        // Update Input Labels
                        const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
                        setTxt('lbl-saNodeRate', state.saNodeRate);
                        setTxt('lbl-ventricularRate', state.ventricularRate);
                        setTxt('lbl-svtRate', state.svtRate);
                        setTxt('lbl-vtRate', state.vtRate);
                        setTxt('lbl-torsadesRate', state.torsadesRate);
                        setTxt('lbl-prProlongation', state.prProlongation);
                        setTxt('lbl-escapeRate', state.escapeRate);
                        setTxt('lbl-spo2', state.spo2);
                        const bpStep = BP_STEPS[bpToIndex(state.sysBp)];
                        setTxt('lbl-bp', bpStep.sys === 0 ? 'Undetectable' : bpStep.sys + '/' + bpStep.dia + ' mmHg');
                        setTxt('lbl-rr', state.rr);
                        setTxt('lbl-shockEnergy', state.joules);
                        setTxt('lbl-shockThreshold', state.shockThreshold);
                        setTxt('lbl-qtc', Math.round((state.qtc || 0.40) * 1000));
                        setTxt('lbl-pacingCaptureThreshold', state.pacingCaptureThreshold);

                        // Sync Inputs
                        const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
                        setVal('input-rhythm', state.rhythm);
                        setVal('input-saNodeRate', state.saNodeRate);
                        setVal('input-spo2', state.spo2);
                        setVal('input-bp', bpToIndex(state.sysBp));
                        setVal('input-rr', state.rr);
                        setVal('input-shockEnergy', Math.max(0, JOULES_STEPS.indexOf(state.joules)));
                        setVal('input-shockThreshold', state.shockThreshold);
                        setVal('input-qtc', Math.round((state.qtc || 0.40) * 1000));
                        const wideQRSEl = document.getElementById('input-wideQRS');
                        if (wideQRSEl) wideQRSEl.checked = !!state.wideQRS;
                        setVal('input-pacingCaptureThreshold', state.pacingCaptureThreshold);
                        const pacCapEl = document.getElementById('input-pacingCaptureEnabled');
                        if (pacCapEl) {
                            pacCapEl.checked = !!state.pacingCaptureEnabled;
                            const isVfib = rhythm === 'vfib';
                            pacCapEl.disabled = isVfib;
                            const capContainer = pacCapEl.closest('.flex');
                            if (capContainer) {
                                capContainer.classList.toggle('opacity-40', isVfib);
                                capContainer.classList.toggle('pointer-events-none', isVfib);
                            }
                        }
                    }

                    // Pacing button + labels (both views)
                    const pacingBtn = document.getElementById('btn-pacing');
                    if (pacingBtn) {
                        pacingBtn.textContent = state.pacingOn ? 'PACING ON' : 'PACING OFF';
                        pacingBtn.classList.toggle('bg-blue-600', !!state.pacingOn);
                        pacingBtn.classList.toggle('border-blue-400', !!state.pacingOn);
                        pacingBtn.classList.toggle('text-white', !!state.pacingOn);
                        pacingBtn.classList.toggle('bg-slate-700', !state.pacingOn);
                        pacingBtn.classList.toggle('border-slate-600', !state.pacingOn);
                        pacingBtn.classList.toggle('text-slate-300', !state.pacingOn);
                    }
                    const lblPacRate = document.getElementById('lbl-pacingRate');
                    if (lblPacRate) lblPacRate.innerText = state.pacingRate;
                    const lblPacOut = document.getElementById('lbl-pacingOutput');
                    if (lblPacOut) lblPacOut.innerText = state.pacingOutput;
                    const inPacRate = document.getElementById('input-pacingRate');
                    if (inPacRate) inPacRate.value = state.pacingRate;
                    const inPacOut = document.getElementById('input-pacingOutput');
                    if (inPacOut) inPacOut.value = state.pacingOutput;

                    // Update Vitals Display (Runs for both Controller and Viewer)
                    const isLethal = ['vfib', 'asystole'].includes(rhythm);
                    document.getElementById('val-spo2').innerText = isLethal ? '---' : state.spo2;
                    const hideBP = isLethal || state.sysBp === 0;
                    document.getElementById('val-sysBp').innerText = hideBP ? '---' : state.sysBp;
                    document.getElementById('val-diaBp').innerText = hideBP ? '---' : state.diaBp;
                    document.getElementById('val-map').innerText = hideBP ? '---' : Math.round(state.diaBp + (state.sysBp - state.diaBp) / 3);
                    document.getElementById('val-rr').innerText = isLethal ? '---' : state.rr;

                    // Compact ctrl mobile vitals strip
                    const ctrlSpo2 = document.getElementById('ctrl-val-spo2');
                    if (ctrlSpo2) ctrlSpo2.innerText = isLethal ? '---' : state.spo2;
                    const ctrlBp = document.getElementById('ctrl-val-bp');
                    if (ctrlBp) ctrlBp.innerText = hideBP ? '---/---' : state.sysBp + '/' + state.diaBp;
                    const ctrlRr = document.getElementById('ctrl-val-rr');
                    if (ctrlRr) ctrlRr.innerText = isLethal ? '---' : state.rr;

                    // Calculate Base Display HR
                    switch (rhythm) {
                        case 'sinus': case '1st_degree': state.baseHR = state.saNodeRate; break;
                        case '2nd_degree_type1': state.baseHR = Math.round(state.saNodeRate * 0.8); break; 
                        case '2nd_degree_type2': {
                            const bs = state.blockSeverity;
                            if (bs === 10) state.baseHR = Math.round(state.saNodeRate / 3);           // 3:1 HG
                            else if (bs === 20) state.baseHR = Math.round(state.saNodeRate * 7 / 12); // ~avg 2:1 & 3:2
                            else if (bs === 30) state.baseHR = Math.round(state.saNodeRate * 17 / 24);// ~avg 3:2 & 4:3
                            else state.baseHR = Math.round(state.saNodeRate * (bs - 1) / bs);         // 2:1, 3:2, 4:3
                        } break;
                        case '3rd_degree': state.baseHR = state.escapeRate; break;
                        case 'afib': state.baseHR = state.ventricularRate; break;
                        case 'aflutter': state.baseHR = Math.round(300 / parseInt(state.flutterRatio.split(':')[1])); break;
                        case 'svt': state.baseHR = state.svtRate; break;
                        case 'vtach': state.baseHR = state.vtRate; break;
                        case 'torsades': state.baseHR = state.torsadesRate; break;
                        case 'vfib': case 'asystole': state.baseHR = 0; break;
                        default: state.baseHR = state.saNodeRate;
                    }
                    setSyncUI();
                };

                const updateServerState = (changes) => {
                    Object.assign(state, changes);
                    updateUI();
                    if (isController) {
                        fetch('?action=update&id=' + sessionId, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(changes)
                        });
                    }
                };

                const JOULES_STEPS = [10, 20, 30, 50, 100, 150, 200, 250, 300];

                // BP slider steps: index 0 = undetectable, 1 = 50/30, 2..22 = 60..260 by 10
                const BP_STEPS = [{ sys: 0, dia: 0 }, { sys: 50, dia: 30 }];
                for (let s = 60; s <= 260; s += 10) {
                    BP_STEPS.push({ sys: s, dia: Math.round((s * 2 / 3) / 5) * 5 });
                }
                const bpToIndex = (sys) => {
                    if (sys <= 0) return 0;
                    if (sys === 50) return 1;
                    return Math.min(BP_STEPS.length - 1, Math.round((sys - 60) / 10) + 2);
                };

                if (isController) {
                    const bindInput = (id, key, parser = String) => {
                        const el = document.getElementById(id);
                        if (el) el.addEventListener('input', (e) => updateServerState({ [key]: parser(e.target.value) }));
                    };

                    document.getElementById('input-rhythm')?.addEventListener('input', (e) => {
                        const rhythm = e.target.value;
                        const changes = { rhythm };
                        if (rhythm === 'vtach')      { changes.sysBp = 100; changes.diaBp = 65; changes.spo2 = 92; }
                        else if (rhythm === 'torsades')   { changes.sysBp = 70;  changes.diaBp = 45; changes.spo2 = 85; }
                        else if (rhythm === '3rd_degree') { changes.sysBp = 90;  changes.diaBp = 60; changes.spo2 = 90; }
                        else if (rhythm === 'vfib')       { changes.pacingCaptureEnabled = false; }
                        updateServerState(changes);
                    });
                    bindInput('input-saNodeRate', 'saNodeRate', Number);
                    bindInput('input-ventricularRate', 'ventricularRate', Number);
                    bindInput('input-flutterRatio', 'flutterRatio');
                    bindInput('input-svtRate', 'svtRate', Number);
                    bindInput('input-vtRate', 'vtRate', Number);
                    bindInput('input-torsadesRate', 'torsadesRate', Number);
                    bindInput('input-prProlongation', 'prProlongation', Number);
                    const BLOCK_RATIO_MAP = [10, 2, 20, 3, 30, 4];
                    const BLOCK_RATIO_LABELS = ['3:1 (high grade)', '2:1', '~2:1/3:2', '3:2', '~3:2/4:3', '4:3'];
                    document.getElementById('input-blockSeverity')?.addEventListener('input', (e) => {
                        const pos = Number(e.target.value);
                        document.getElementById('lbl-blockSeverity').textContent = BLOCK_RATIO_LABELS[pos];
                        updateServerState({ blockSeverity: BLOCK_RATIO_MAP[pos] });
                    });
                    bindInput('input-escapeRate', 'escapeRate', Number);
                    bindInput('input-spo2', 'spo2', Number);
                    document.getElementById('input-bp')?.addEventListener('input', (e) => {
                        const bp = BP_STEPS[Number(e.target.value)];
                        updateServerState({ sysBp: bp.sys, diaBp: bp.dia });
                    });
                    bindInput('input-rr', 'rr', Number);
                    const shockEnergyEl = document.getElementById('input-shockEnergy');
                    if (shockEnergyEl) shockEnergyEl.addEventListener('input', (e) => updateServerState({ joules: JOULES_STEPS[Number(e.target.value)] }));
                    bindInput('input-shockThreshold', 'shockThreshold', Number);
                    document.getElementById('input-qtc')?.addEventListener('input', (e) => updateServerState({ qtc: Number(e.target.value) / 1000 }));
                    document.getElementById('input-wideQRS')?.addEventListener('change', (e) => updateServerState({ wideQRS: e.target.checked }));
                    document.getElementById('input-pacingCaptureThreshold')?.addEventListener('input', (e) => updateServerState({ pacingCaptureThreshold: Number(e.target.value) }));
                    document.getElementById('input-pacingCaptureEnabled')?.addEventListener('change', (e) => updateServerState({ pacingCaptureEnabled: e.target.checked }));

                    doShock = () => {
                        const triggerTime = Date.now();
                        events.push({ type: 'SHOCK', start: drawState.drawTime });
                        let changes = { shockTrigger: triggerTime };
                        const shockableRhythms = ['svt', 'vfib', 'vtach', 'afib', 'aflutter'];
                        if (state.joules >= state.shockThreshold && shockableRhythms.includes(state.rhythm)) {
                            changes.rhythm = 'sinus';
                            changes.saNodeRate = 80;
                            changes.spo2 = 98;
                            changes.sysBp = 120;
                            changes.diaBp = 80;
                            changes.rr = 16;
                        }
                        updateServerState(changes);
                    };

                    document.getElementById('btn-sync')?.addEventListener('click', () => {
                        const newSync = !state.syncMode;
                        if (newSync) markExistingQRSSynced(); else syncShockPending = false;
                        lastKnownSyncMode = newSync;
                        updateServerState({ syncMode: newSync });
                    });

                    document.getElementById('btn-shock')?.addEventListener('click', () => {
                        state.syncMode ? (syncShockPending = true) : doShock();
                    });
                }

                // Learner shock panel (sim view only)
                if (!isController) {
                    let learnerJoules = 150;

                    document.getElementById('input-learnerJoules')?.addEventListener('input', (e) => {
                        learnerJoules = JOULES_STEPS[Number(e.target.value)];
                        const lbl = document.getElementById('lbl-learnerJoules');
                        if (lbl) lbl.innerText = learnerJoules;
                    });

                    doShock = () => {
                        const triggerTime = Date.now();
                        pendingTrigger = triggerTime;
                        events.push({ type: 'SHOCK', start: drawState.drawTime });
                        let changes = { shockTrigger: triggerTime };
                        const shockableRhythms = ['svt', 'vfib', 'vtach', 'afib', 'aflutter'];
                        if (learnerJoules >= state.shockThreshold && shockableRhythms.includes(state.rhythm)) {
                            changes.rhythm = 'sinus';
                            changes.saNodeRate = 80;
                            changes.spo2 = 98;
                            changes.sysBp = 120;
                            changes.diaBp = 80;
                            changes.rr = 16;
                        }
                        Object.assign(state, changes);
                        updateUI();
                        fetch('?action=update&id=' + sessionId, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(changes)
                        });
                    };

                    document.getElementById('btn-learner-sync')?.addEventListener('click', () => {
                        const newSync = !state.syncMode;
                        if (newSync) markExistingQRSSynced(); else syncShockPending = false;
                        lastKnownSyncMode = newSync;
                        state.syncMode = newSync;
                        setSyncUI();
                        fetch('?action=update&id=' + sessionId, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ syncMode: newSync })
                        });
                    });

                    document.getElementById('btn-learner-shock')?.addEventListener('click', () => {
                        state.syncMode ? (syncShockPending = true) : doShock();
                    });

                    // Pacing controls
                    const learnerPush = (changes) => {
                        Object.assign(state, changes);
                        updateUI();
                        fetch('?action=update&id=' + sessionId, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(changes) });
                    };
                    document.getElementById('btn-pacing')?.addEventListener('click', () => learnerPush({ pacingOn: !state.pacingOn }));
                    document.getElementById('input-pacingRate')?.addEventListener('input', (e) => learnerPush({ pacingRate: Number(e.target.value) }));
                    document.getElementById('input-pacingOutput')?.addEventListener('input', (e) => learnerPush({ pacingOutput: Number(e.target.value) }));

                }

                // Initial fetch and start polling
                if (sessionId) {
                    fetch('?action=poll&id=' + sessionId)
                        .then(r => r.json())
                        .then(data => {
                            Object.assign(state, data);
                            lastShockTrigger = data.shockTrigger || 0;
                            updateUI();
                        }).catch(e => console.error(e));

                    setInterval(async () => {
                        try {
                            const res = await fetch('?action=poll&id=' + sessionId);
                            if (!res.ok) return;
                            const data = await res.json();

                            if (data.shockTrigger && data.shockTrigger !== lastShockTrigger) {
                                lastShockTrigger = data.shockTrigger;
                                if (!isController && data.shockTrigger !== pendingTrigger) {
                                    events.push({ type: 'SHOCK', start: drawState.drawTime });
                                }
                                pendingTrigger = 0;
                            }
                            if (!lastKnownSyncMode && data.syncMode) markExistingQRSSynced();
                            if (lastKnownSyncMode && !data.syncMode) syncShockPending = false;
                            lastKnownSyncMode = !!data.syncMode;
                            Object.assign(state, data);
                            updateUI();
                        } catch (e) {}
                    }, isController ? 500 : 150);
                }

                setInterval(() => {
                    const pacingCapture = state.pacingOn && state.pacingOutput >= state.pacingCaptureThreshold && state.pacingCaptureEnabled;
                    if (pacingCapture) {
                        state.liveHR = state.pacingRate;
                    } else if (state.baseHR > 0 && ['sinus', 'afib', 'aflutter'].includes(state.rhythm)) {
                        state.liveHR = state.baseHR + Math.floor(Math.random() * 3) - 1;
                    } else {
                        state.liveHR = state.baseHR;
                    }
                    const displayStr = state.liveHR === 0 ? '---' : state.liveHR;
                    const prStr = state.liveHR === 0 ? '-' : state.liveHR;
                    document.getElementById('val-hr').innerText = displayStr;
                    document.getElementById('val-pr').innerText = prStr;
                    const ctrlHr = document.getElementById('ctrl-val-hr');
                    if (ctrlHr) ctrlHr.innerText = displayStr;
                }, 2000);

                // --- EKG MATH ---
                const evaluateEKGEvent = (event, t) => {
                    const dt = t - event.start;
                    if (dt < 0) return 0;
                    let v = 0;
                    if (event.type === 'PACE' && dt < 0.012) {
                        // Narrow vertical pacer spike
                        v += 2.2 * Math.exp(-Math.pow((dt - 0.004) / 0.0018, 2));
                    } else if (event.type === 'SHOCK') {
                        if (dt < 2.0) {
                            if (dt < 0.1) v += Math.sin(dt * 300) * 12;
                            else v -= 3 * Math.exp(-dt * 2) * Math.cos(dt * 8);
                        }
                    } else if (event.type === 'P' && dt < 0.2) {
                        v += 0.12 * Math.exp(-Math.pow((dt - 0.05) / 0.015, 2));
                    } else if (event.type === 'QRS') {
                        if (event.wide && dt < 0.3) {
                            v += 1.0 * Math.exp(-Math.pow((dt - 0.06) / 0.03, 2));
                            v -= 0.6 * Math.exp(-Math.pow((dt - 0.14) / 0.04, 2));
                        } else if (!event.wide && dt < 0.15) {
                            v -= 0.15 * Math.exp(-Math.pow((dt - 0.02) / 0.005, 2));
                            v += 1.5 * Math.exp(-Math.pow((dt - 0.045) / 0.012, 2));
                            v -= 0.25 * Math.exp(-Math.pow((dt - 0.07) / 0.012, 2));
                        }
                    } else if (event.type === 'T') {
                        const hr = event.hr || 80;
                        const rr = 60 / Math.max(hr, 20);
                        // Bazett's formula: QT = QTc * sqrt(RR)
                        const qt = (event.qtc || 0.40) * Math.sqrt(rr);
                        const tDur = Math.max(0.08, qt - (event.qrsDur || 0.12));
                        if (dt >= 0 && dt < tDur) {
                            const width = tDur * 0.28;
                            const peak = tDur * 0.42;
                            v += (event.inverted ? -1 : 1) * 0.25 * Math.exp(-Math.pow((dt - peak) / width, 2));
                        }
                    }
                    return v;
                };

                const evaluatePlethEvent = (event, t) => {
                    const dt = t - event.start;
                    if (dt < 0 || dt > 0.8) return 0;
                    if (event.type === 'SHOCK') return 5.0 * Math.exp(-Math.pow((dt - 0.05) / 0.05, 2));
                    let v = 1.0 * Math.exp(-Math.pow((dt - 0.15) / 0.08, 2)); 
                    v += 0.15 * Math.exp(-Math.pow((dt - 0.35) / 0.1, 2)); 
                    return v;
                };

                const getBaselines = (t, params) => {
                    let ekg = 0;
                    ekg += Math.sin(t * 0.5) * 0.02 + Math.sin(t * 1.2) * 0.01;

                    if (params.rhythm === 'afib') {
                        ekg += (Math.random() - 0.5) * 0.1;
                        ekg += Math.sin(t * 15) * 0.03;
                    } else if (params.rhythm === 'aflutter') {
                        let phase = (t * 5) % 1;
                        ekg += (phase < 0.5 ? phase * 2 : 2 - phase * 2) * 0.15 - 0.075; 
                        ekg += Math.sin(t * 5 * Math.PI * 2) * 0.1; 
                    } else if (params.rhythm === 'vfib') {
                        ekg += Math.sin(t * 22.0) * 0.3;
                        ekg += Math.cos(t * 15.3) * 0.25;
                        ekg += Math.sin(t * 34.1) * 0.15;
                        ekg += Math.cos(t * 7.2) * 0.2;
                    } else if (params.rhythm === 'torsades') {
                        let fastF = params.torsadesRate / 60;
                        let slowF = 0.25; 
                        let envelope = Math.sin(t * slowF * Math.PI * 2);
                        ekg += Math.sin(t * fastF * Math.PI * 2) * envelope * 0.8;
                        ekg += Math.cos(t * (fastF * 1.1) * Math.PI * 2) * (1 - Math.abs(envelope)) * 0.3;
                    } else if (params.rhythm === 'asystole') {
                        ekg = (Math.random() - 0.5) * 0.01; 
                    }

                    const isLethalRhythm = params.rhythm === 'vfib' || params.rhythm === 'asystole';
                    let resp = isLethalRhythm ? (Math.random() - 0.5) * 0.05 : -Math.sin(t * (params.rr / 60) * Math.PI * 2) * 0.6;
                    let plethWander = isLethalRhythm ? 0 : -Math.sin(t * (params.rr / 60) * Math.PI * 2) * 0.1;

                    return [ekg, plethWander, resp];
                };

                // --- SCHEDULER ---
                const scheduleEvents = (t, params, simSt, evts) => {
                    while (evts.length > 0 && evts[0].start + 2.0 < t) { evts.shift(); }
                    const rhythm = params.rhythm;
                    if (simSt.rhythm !== rhythm) {
                        simSt.rhythm = rhythm; simSt.nextP = t + 0.1; simSt.nextQRS = t + 0.1;
                        simSt.wenckebach_beat = 0; simSt.mobitz_beat = 0;
                    }

                    const pushQRS = (time, wide = false, hr = 80, invertedT = false) => {
                        const forceWide = params.wideQRS && !wide;
                        const isWide = wide || forceWide;
                        const qrsDuration = isWide ? 0.18 : 0.12;
                        evts.push({ type: 'QRS', start: time, wide: isWide });
                        evts.push({ type: 'T', start: time + qrsDuration, hr, inverted: invertedT, qtc: params.qtc || 0.40, qrsDur: qrsDuration });
                        if (!['vfib', 'asystole'].includes(rhythm)) {
                            evts.push({ type: 'PLETH', start: time + 0.2 });
                        }
                    };

                    // --- PACING (evaluated first so capture can inhibit native beats this tick) ---
                    if (params.pacingOn && !simSt.pacingWasOn) {
                        simSt.pacingWasOn = true;
                        simSt.nextPace = t + 0.1;
                    } else if (!params.pacingOn) {
                        simSt.pacingWasOn = false;
                    }
                    let nativeInhibited = false;
                    if (params.pacingOn && t >= simSt.nextPace) {
                        evts.push({ type: 'PACE', start: simSt.nextPace });
                        const capture = params.pacingCaptureEnabled && params.pacingOutput >= params.pacingCaptureThreshold;
                        if (capture) {
                            nativeInhibited = true;
                            // Paced beat: wide QRS + inverted T, 20 ms after spike
                            const paceQRSDur = 0.18;
                            evts.push({ type: 'QRS', start: simSt.nextPace + 0.02, wide: true });
                            evts.push({ type: 'T', start: simSt.nextPace + 0.02 + paceQRSDur, hr: params.pacingRate, inverted: true, qtc: params.qtc || 0.40, qrsDur: paceQRSDur });
                            evts.push({ type: 'PLETH', start: simSt.nextPace + 0.22 });
                            // Advance native clocks by one native beat interval so beats resume
                            // on their own schedule (not the pacing interval) — allows native beats
                            // between paced beats when native rate > pacing rate.
                            const nativePInterval = 60 / (params.saNodeRate || 80);
                            const nativeQRSInterval = (() => {
                                switch(rhythm) {
                                    case '3rd_degree': return 60 / params.escapeRate;
                                    case 'afib':      return 60 / params.ventricularRate;
                                    case 'aflutter':  return 0.2 * (parseInt(params.flutterRatio.split(':')[1]) || 2);
                                    case 'svt':       return 60 / params.svtRate;
                                    case 'vtach':     return 60 / params.vtRate;
                                    default:          return nativePInterval;
                                }
                            })();
                            simSt.nextP   = t + nativePInterval   + 0.001;
                            simSt.nextQRS = t + nativeQRSInterval + 0.001;
                        }
                        simSt.nextPace += 60 / params.pacingRate;
                    }

                    // --- NATIVE BEATS (skipped entirely when pacing capture is active this tick) ---
                    if (!nativeInhibited) {
                        if (['sinus', '1st_degree', '2nd_degree_type1', '2nd_degree_type2', '3rd_degree'].includes(rhythm)) {
                            if (t >= simSt.nextP) {
                                evts.push({ type: 'P', start: t });
                                if (rhythm === 'sinus') { pushQRS(t + 0.16, false, params.saNodeRate); }
                                else if (rhythm === '1st_degree') { pushQRS(t + params.prProlongation, false, params.saNodeRate); }
                                else if (rhythm === '2nd_degree_type1') {
                                    if (simSt.wenckebach_beat < 3) {
                                        pushQRS(t + 0.16 + (simSt.wenckebach_beat * 0.06), false, params.saNodeRate);
                                        simSt.wenckebach_beat++;
                                    } else { simSt.wenckebach_beat = 0; }
                                } else if (rhythm === '2nd_degree_type2') {
                                    const bs = params.blockSeverity;
                                    let cycleSize, conductedCount;
                                    if (bs === 10) { cycleSize = 3; conductedCount = 1; }  // 3:1 HG
                                    else {
                                        if (simSt.mobitz_beat === 0) {
                                            if (bs === 20) simSt.mobitz_cycle_size = Math.random() < 0.5 ? 2 : 3;
                                            else if (bs === 30) simSt.mobitz_cycle_size = Math.random() < 0.5 ? 3 : 4;
                                            else simSt.mobitz_cycle_size = bs;
                                        }
                                        cycleSize = simSt.mobitz_cycle_size;
                                        conductedCount = cycleSize - 1;
                                    }
                                    if (simSt.mobitz_beat < conductedCount) {
                                        pushQRS(t + 0.16, false, params.saNodeRate);
                                    }
                                    simSt.mobitz_beat++;
                                    if (simSt.mobitz_beat >= cycleSize) simSt.mobitz_beat = 0;
                                }
                                const baseInterval = 60 / params.saNodeRate;
                                simSt.nextP = t + baseInterval + (Math.random() * (baseInterval*0.05) - (baseInterval*0.025));
                            }
                        }

                        if (rhythm === '3rd_degree' && t >= simSt.nextQRS) {
                            pushQRS(t, true, params.escapeRate, true);
                            simSt.nextQRS = t + (60 / params.escapeRate);
                        }
                        if (rhythm === 'afib' && t >= simSt.nextQRS) {
                            pushQRS(t, false, params.ventricularRate);
                            simSt.nextQRS = t + Math.max(0.25, (60 / params.ventricularRate) + (Math.random() * 0.4 - 0.2));
                        }
                        if (rhythm === 'aflutter' && t >= simSt.nextQRS) {
                            pushQRS(t, false, 150);
                            simSt.nextQRS = t + (0.2 * (parseInt(params.flutterRatio.split(':')[1]) || 2));
                        }
                        if (rhythm === 'svt' && t >= simSt.nextQRS) {
                            pushQRS(t, false, params.svtRate);
                            simSt.nextQRS = t + (60 / params.svtRate);
                        }
                        if (rhythm === 'vtach' && t >= simSt.nextQRS) {
                            pushQRS(t, true, params.vtRate, true);
                            simSt.nextQRS = t + (60 / params.vtRate);
                        }
                    }
                };

                // --- CANVAS SETUP & RENDER LOOP ---
                const mainCanvas = document.getElementById('mainCanvas');
                const bgCanvas = document.getElementById('bgCanvas');
                const ctx = mainCanvas.getContext('2d');
                const bgCtx = bgCanvas.getContext('2d');
                const width = mainCanvas.width;
                const height = mainCanvas.height;

                bgCtx.fillStyle = '#020617'; bgCtx.fillRect(0, 0, width, height);
                for (let x = 0; x <= width; x += 6) {
                    bgCtx.beginPath(); bgCtx.moveTo(x, 0); bgCtx.lineTo(x, height);
                    bgCtx.strokeStyle = x % 30 === 0 ? '#064e3b' : '#022c22';
                    bgCtx.lineWidth = x % 30 === 0 ? 1 : 0.5; bgCtx.stroke();
                }
                for (let y = 0; y <= height; y += 6) {
                    bgCtx.beginPath(); bgCtx.moveTo(0, y); bgCtx.lineTo(width, y);
                    bgCtx.strokeStyle = y % 30 === 0 ? '#064e3b' : '#022c22';
                    bgCtx.lineWidth = y % 30 === 0 ? 1 : 0.5; bgCtx.stroke();
                }

                const animate = (time) => {
                    const t_real = time / 1000;
                    const dt_real = t_real - drawState.lastTimeReal;
                    drawState.lastTimeReal = t_real;

                    while (simState.time < drawState.drawTime + 0.5) {
                        scheduleEvents(simState.time, state, simState, events);
                        simState.time += 0.005; 
                    }

                    const pxPerSecond = 180; 
                    let pixelsToDraw = dt_real * pxPerSecond;
                    if (pixelsToDraw > 100) pixelsToDraw = 100; 

                    for (let i = 0; i < pixelsToDraw; i += 1) {
                        drawState.drawTime += 1 / pxPerSecond;
                        let currentX = drawState.lastX + 1;
                        let wrapped = false;
                        if (currentX >= width) { currentX = 0; wrapped = true; }

                        const baselines = getBaselines(drawState.drawTime, state);
                        let ekgVal = baselines[0], plethVal = baselines[1], respVal = baselines[2];

                        events.forEach(e => {
                            if (e.type === 'P' || e.type === 'QRS' || e.type === 'T' || e.type === 'SHOCK' || e.type === 'PACE') {
                                ekgVal += evaluateEKGEvent(e, drawState.drawTime);
                            } 
                            if (e.type === 'PLETH' || e.type === 'SHOCK') {
                                plethVal += evaluatePlethEvent(e, drawState.drawTime);
                            }
                            if (e.type === 'SHOCK') {
                                const dt = drawState.drawTime - e.start;
                                if (dt > 0 && dt < 0.8) respVal += 3.0 * Math.exp(-Math.pow((dt - 0.05) / 0.05, 2));
                            }
                        });

                        const newEkgY = (isController && window.innerWidth < 1024 ? 225 : 100) - ekgVal * 50;
                        const newPlethY = 240 - plethVal * 45;
                        const newRespY = 380 - respVal * 35;

                        ctx.clearRect(currentX, 0, 15, height);
                        
                        if (!wrapped) {
                            ctx.beginPath(); ctx.moveTo(drawState.lastX, drawState.ekgY); ctx.lineTo(currentX, newEkgY);
                            ctx.strokeStyle = '#22c55e'; ctx.lineWidth = 1.5; ctx.stroke();

                            if (!(isController && window.innerWidth < 1024)) {
                                ctx.beginPath(); ctx.moveTo(drawState.lastX, drawState.plethY); ctx.lineTo(currentX, newPlethY);
                                ctx.strokeStyle = '#06b6d4'; ctx.lineWidth = 1.5; ctx.stroke();

                                ctx.beginPath(); ctx.moveTo(drawState.lastX, drawState.respY); ctx.lineTo(currentX, newRespY);
                                ctx.strokeStyle = '#eab308'; ctx.lineWidth = 1.5; ctx.stroke();
                            }
                        }

                        // SYNC: triangle markers and delayed shock
                        for (const e of events) {
                            if (e.type !== 'QRS') continue;
                            const peakOffset = e.wide ? 0.06 : 0.045;
                            const peakTime = e.start + peakOffset;
                            if (state.syncMode && !e.syncMarked && drawState.drawTime >= peakTime) {
                                e.syncMarked = true;
                                ctx.fillStyle = '#facc15';
                                ctx.beginPath();
                                ctx.moveTo(currentX, 22);      // apex (bottom)
                                ctx.lineTo(currentX - 5, 11);  // top-left
                                ctx.lineTo(currentX + 5, 11);  // top-right
                                ctx.closePath();
                                ctx.fill();
                            }
                            if (syncShockPending && !e.syncShockFired && drawState.drawTime >= peakTime + 0.005) {
                                e.syncShockFired = true;
                                syncShockPending = false;
                                doShock?.();
                            }
                        }

                        drawState.lastX = currentX; drawState.ekgY = newEkgY; 
                        drawState.plethY = newPlethY; drawState.respY = newRespY;
                    }
                    requestAnimationFrame(animate);
                };

                drawState.lastTimeReal = performance.now() / 1000;
                requestAnimationFrame(animate);
            });
        </script>
    <?php endif; ?>
</body>
</html>
