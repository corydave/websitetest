// URL Parameter handling
function getURLParams() {
    const params = new URLSearchParams(window.location.search);
    const inTime = parseFloat(params.get('in')) || 4.0;
    const outTime = parseFloat(params.get('out')) || 6.0;
    const color = params.get('color') || '#90EE90';
    const duration = parseInt(params.get('duration')) || 20;
    return { inTime, outTime, color, duration };
}

function updateURL(inTime, outTime, color, duration) {
    const params = new URLSearchParams();
    params.set('in', inTime.toFixed(1));
    params.set('out', outTime.toFixed(1));
    params.set('color', color);
    params.set('duration', duration.toString());
    window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
    
    // Also update the share URL to match
    shareURL.value = window.location.href;
}

function updateShareURL() {
    const currentDuration = parseInt(durationInput.value) || 20;
    const currentInhale = parseFloat(inhaleInput.value) || 4.0;
    const currentExhale = parseFloat(exhaleInput.value) || 6.0;
    const currentColor = colorInput.value || '#90EE90';
    
    const params = new URLSearchParams();
    params.set('in', currentInhale.toFixed(1));
    params.set('out', currentExhale.toFixed(1));
    params.set('color', currentColor);
    params.set('duration', currentDuration.toString());
    
    // Only update the share URL input, don't change browser history
    const baseUrl = window.location.href.split('?')[0];
    shareURL.value = `${baseUrl}?${params.toString()}`;
}

// Settings storage
function saveToStorage(inTime, outTime, color, duration) {
    const settings = { inTime, outTime, color, duration };
    localStorage.setItem('breathingSettings', JSON.stringify(settings));
}

function getFromStorage() {
    const stored = localStorage.getItem('breathingSettings');
    return stored ? JSON.parse(stored) : null;
}

// Dark mode functions
function enableDarkMode() {
    document.body.classList.add('dark-mode');
    localStorage.setItem('darkMode', 'enabled');
}

function disableDarkMode() {
    document.body.classList.remove('dark-mode');
    localStorage.setItem('darkMode', 'disabled');
}

// Get initial settings from URL or storage
const urlParams = getURLParams();
const storedSettings = getFromStorage();
const darkMode = localStorage.getItem('darkMode');

// Initialize dark mode based on stored preference
if (darkMode === 'enabled') {
    enableDarkMode();
}

// URL params take precedence over stored settings
let breatheInTime = (urlParams.inTime || storedSettings?.inTime || 4.0) * 1000;
let breatheOutTime = (urlParams.outTime || storedSettings?.outTime || 6.0) * 1000;
let arcColor = urlParams.color || storedSettings?.color || '#90EE90';

// Constants
const INITIAL_TIME = (urlParams.duration || storedSettings?.duration || 20) * 60;
const TOTAL_LENGTH = 251.2; // SVG path length

// DOM Elements
const speedometerFill = document.querySelector('.speedometer-fill');
const text = document.querySelector('.text');
const timer = document.querySelector('.timer');
const breathRates = document.querySelector('.breath-rates');
const playButton = document.querySelector('.play-button');
const pauseButton = document.querySelector('.pause-button');
const refreshButton = document.querySelector('.refresh-icon');
const modalBackdrop = document.querySelector('.modal-backdrop');
const settingsModal = document.querySelector('.settings-modal');
const settingsIcon = document.querySelector('.settings-icon:not(.dark-mode-icon)');
const cancelButton = document.querySelector('.cancel-button');
const saveButton = document.querySelector('.save-button');
const inhaleInput = document.querySelector('#inhale-duration');
const exhaleInput = document.querySelector('#exhale-duration');
const colorInput = document.querySelector('#circle-color');
const durationInput = document.querySelector('#duration');
const shareURL = document.querySelector('#share-url');
const copyButton = document.querySelector('#copy-url');
const doneMessage = document.querySelector('.done');

// State variables
let timeLeft = INITIAL_TIME;
let timerInterval;
let isRunning = false;
let isPaused = false;
let currentPhase = 'in';
let startTime = null;
let totalPausedTime = 0;
let lastPauseStartTime = null;
let animationFrame;

function handleCompletion() {
    clearInterval(timerInterval);
    cancelAnimationFrame(animationFrame);
    
    const container = document.getElementById('main-container');
    const done = document.getElementById('done');
    
    // Fade out main container
    container.style.transition = 'opacity 1s ease';
    container.style.opacity = '0';
    
    setTimeout(() => {
        container.style.display = 'none';
        // Fade in completion message
        done.style.display = 'flex';
        setTimeout(() => {
            done.classList.add('visible');
        }, 50);
    }, 1000);
}

function updateTimer() {
    if (timeLeft <= 0) {
        handleCompletion();
        return;
    }
    
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    timeLeft--;
}

function updateBreathRates() {
    const inSeconds = (breatheInTime / 1000).toFixed(1);
    const outSeconds = (breatheOutTime / 1000).toFixed(1);
    breathRates.textContent = `In: ${inSeconds}s • Out: ${outSeconds}s`;
}

function updateAnimation() {
    if (!startTime) return;
    
    // Calculate total active time (excluding paused time)
    const now = performance.now();
    const totalActiveTime = now - startTime - totalPausedTime;
    const cycleTime = totalActiveTime % (breatheInTime + breatheOutTime);
    
    if (cycleTime < breatheInTime) {
        // Inhale phase
        currentPhase = 'in';
        text.textContent = 'Breathe in';
        const progress = cycleTime / breatheInTime;
        speedometerFill.style.transition = 'none';
        speedometerFill.style.strokeDashoffset = TOTAL_LENGTH * (1 - progress);
    } else {
        // Exhale phase
        currentPhase = 'out';
        text.textContent = 'Breathe out';
        const exhaleProgress = (cycleTime - breatheInTime) / breatheOutTime;
        speedometerFill.style.transition = 'none';
        speedometerFill.style.strokeDashoffset = TOTAL_LENGTH * exhaleProgress;
    }
}

function animate() {
    if (!isPaused && isRunning) {
        updateAnimation();
        animationFrame = requestAnimationFrame(animate);
    }
}

function pause() {
    isPaused = true;
    clearInterval(timerInterval);
    cancelAnimationFrame(animationFrame);
    
    // Record pause start time
    lastPauseStartTime = performance.now();
    
    // Freeze animation at current position
    const currentOffset = getComputedStyle(speedometerFill).strokeDashoffset;
    speedometerFill.style.transition = 'none';
    speedometerFill.style.strokeDashoffset = currentOffset;
    
    playButton.style.display = 'flex';
    pauseButton.style.display = 'none';
}

function resume() {
    if (!lastPauseStartTime) return;
    
    // Add the duration of this pause to total paused time
    totalPausedTime += performance.now() - lastPauseStartTime;
    lastPauseStartTime = null;
    
    isPaused = false;
    playButton.style.display = 'none';
    pauseButton.style.display = 'flex';
    
    requestAnimationFrame(animate);
    timerInterval = setInterval(updateTimer, 1000);
}

function resetApp() {
    cancelAnimationFrame(animationFrame);
    clearInterval(timerInterval);
    timeLeft = INITIAL_TIME;
    updateTimer();
    isRunning = false;
    isPaused = false;
    currentPhase = 'in';
    startTime = null;
    totalPausedTime = 0;
    lastPauseStartTime = null;
    
    playButton.style.display = 'flex';
    pauseButton.style.display = 'none';
    
    speedometerFill.style.transition = 'none';
    speedometerFill.style.strokeDashoffset = TOTAL_LENGTH;
    
    text.textContent = 'Breathe in';
    
    // Reset completion message if it was showing
    const container = document.getElementById('main-container');
    const done = document.getElementById('done');
    
    done.classList.remove('visible');
    done.style.display = 'none';
    container.style.display = 'flex';
    container.style.opacity = '1';
}

function openModal() {
    modalBackdrop.style.display = 'block';
    durationInput.value = Math.floor(timeLeft / 60);
    inhaleInput.value = (breatheInTime / 1000).toFixed(1);
    exhaleInput.value = (breatheOutTime / 1000).toFixed(1);
    colorInput.value = arcColor;
    updateShareURL(); // Update share URL when opening modal
}

function closeModal() {
    modalBackdrop.style.display = 'none';
}

function updateColors(color) {
    speedometerFill.style.stroke = color;
}

function saveSettings() {
    const newDuration = parseInt(durationInput.value);
    breatheInTime = parseFloat(inhaleInput.value) * 1000;
    breatheOutTime = parseFloat(exhaleInput.value) * 1000;
    arcColor = colorInput.value;

    // Update timer if not currently running
    if (!isRunning) {
        timeLeft = newDuration * 60;
        updateTimer();
    }

    updateColors(arcColor);
    updateBreathRates();
    updateURL(breatheInTime/1000, breatheOutTime/1000, arcColor, newDuration);
    saveToStorage(breatheInTime/1000, breatheOutTime/1000, arcColor, newDuration);
    closeModal();
}

// Initialize the start state
speedometerFill.style.strokeDasharray = TOTAL_LENGTH;
speedometerFill.style.strokeDashoffset = TOTAL_LENGTH;
speedometerFill.style.stroke = arcColor;
updateBreathRates();

// Event listeners
playButton.addEventListener('click', () => {
    if (isPaused) {
        resume();
    } else if (!isRunning) {
        isRunning = true;
        startTime = performance.now();
        totalPausedTime = 0;
        
        playButton.style.display = 'none';
        pauseButton.style.display = 'flex';
        
        speedometerFill.style.transition = 'none';
        speedometerFill.style.strokeDashoffset = TOTAL_LENGTH;
        
        requestAnimationFrame(animate);
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }
});

// Add input event listeners for dynamic URL updates
durationInput.addEventListener('input', updateShareURL);
inhaleInput.addEventListener('input', updateShareURL);
exhaleInput.addEventListener('input', updateShareURL);
colorInput.addEventListener('input', updateShareURL);

pauseButton.addEventListener('click', pause);
refreshButton.addEventListener('click', resetApp);
settingsIcon.addEventListener('click', openModal);
cancelButton.addEventListener('click', closeModal);
saveButton.addEventListener('click', saveSettings);
modalBackdrop.addEventListener('click', (e) => {
    if (e.target === modalBackdrop) closeModal();
});

// Copy URL functionality
copyButton.addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(shareURL.value);
        copyButton.textContent = 'Copied!';
        setTimeout(() => {
            copyButton.textContent = 'Copy';
        }, 2000);
    } catch (err) {
        console.error('Failed to copy URL:', err);
    }
});

// Dark mode toggle listener
const darkModeToggle = document.querySelector('.dark-mode-icon');
darkModeToggle.addEventListener('click', () => {
    const darkMode = localStorage.getItem('darkMode');
    if (darkMode !== 'enabled') {
        enableDarkMode();
    } else {
        disableDarkMode();
    }
});
