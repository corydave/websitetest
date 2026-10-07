/* ====================================================================
   ListenMode - v1.0
   A DEMONSTRATION of one slice of what a screen reader does.

   IT IS NOT A SCREEN READER. A real screen reader (NVDA, JAWS,
   VoiceOver) reads the whole computer, not just a web page; it has a
   browse mode and a focus mode, hundreds of keyboard commands, and a
   far more careful way of working out what to call things. This is a
   teaching toy that does one thing: it hides the page and tells you
   what the keyboard is landing on.

   Built for CSC 145 (Vibe Coding) at Finger Lakes Community College.

   HOW IT IS USED
     A bookmarklet loads this file into whatever page is open. Press
     Escape to leave.

   KEYS
     Tab / Shift+Tab   move to the next or previous thing, and hear it
     H / Shift+H       jump to the next or previous heading
     I                 list the pictures, and say which have no alt text
     R                 read the whole page from the top
     S                 stop talking
     Escape            turn ListenMode off
   ==================================================================== */
(function () {
  'use strict';

  // If it is already running, a second click turns it off again.
  if (window.__listenMode) { window.__listenMode.stop(); return; }

  // ------------------------------------------------------------------
  // 1. THE VOICE
  // The browser can speak. speechSynthesis is built into Chrome, Edge
  // and Safari - no account, no key, nothing to install.
  // ------------------------------------------------------------------
  var synth = window.speechSynthesis;

  function say(text, interrupt) {
    if (!text) { return; }
    showCaption(text);
    if (!synth) { return; }               // no voice available - captions still work
    if (interrupt) { synth.cancel(); }
    var utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 1.1;
    synth.speak(utterance);
  }

  // ------------------------------------------------------------------
  // 2. WHAT IS THIS THING CALLED?
  // A screen reader does not read your HTML. It reads the "accessible
  // name" the browser works out for each thing. This is a SIMPLIFIED
  // version of that - the real rules are much longer - but it covers
  // the cases that matter, and it finds the same bugs.
  // ------------------------------------------------------------------
  function accessibleName(el) {
    // 1. An aria-label wins over everything.
    if (el.getAttribute('aria-label')) { return el.getAttribute('aria-label').trim(); }

    // 2. aria-labelledby points at another element whose text is the name.
    var labeledBy = el.getAttribute('aria-labelledby');
    if (labeledBy) {
      var source = document.getElementById(labeledBy);
      if (source) { return source.textContent.trim(); }
    }

    // 3. A <label for="..."> that points at this element's id.
    if (el.id) {
      var label = document.querySelector('label[for="' + CSS.escape(el.id) + '"]');
      if (label) { return label.textContent.trim(); }
    }

    // 4. A <label> wrapped around this element.
    var wrapping = el.closest('label');
    if (wrapping) { return wrapping.textContent.trim(); }

    // 5. An image uses its alt text.
    if (el.tagName === 'IMG') { return (el.getAttribute('alt') || '').trim(); }

    // 6. Otherwise, the words inside it.
    var text = (el.textContent || '').trim().replace(/\s+/g, ' ');
    if (text) { return text.slice(0, 120); }

    // 7. Last resorts, and both are warning signs rather than good names.
    if (el.value) { return String(el.value).trim(); }
    if (el.getAttribute('title')) { return el.getAttribute('title').trim(); }
    return '';
  }

  // What KIND of thing is it? Again, simplified.
  function roleOf(el) {
    if (el.getAttribute('role')) { return el.getAttribute('role'); }
    var tag = el.tagName.toLowerCase();
    if (tag === 'a') { return el.hasAttribute('href') ? 'link' : 'text'; }
    if (tag === 'button') { return 'button'; }
    if (tag === 'img') { return 'image'; }
    if (tag === 'select') { return 'menu'; }
    if (tag === 'textarea') { return 'text box'; }
    if (/^h[1-6]$/.test(tag)) { return 'heading level ' + tag[1]; }
    if (tag === 'input') {
      var type = (el.getAttribute('type') || 'text').toLowerCase();
      if (type === 'checkbox') { return 'checkbox'; }
      if (type === 'radio') { return 'radio button'; }
      if (type === 'submit' || type === 'button') { return 'button'; }
      return type + ' box';
    }
    return tag;
  }

  // Put the two together the way a screen reader announces them, and say
  // so plainly when there is no name - because that is the bug.
  function describe(el) {
    var name = accessibleName(el);
    var role = roleOf(el);
    if (!name) { return role + ', with no name - nobody can tell what this does'; }
    return name + ', ' + role;
  }

  // ------------------------------------------------------------------
  // 3. HIDING THE PAGE
  // A sheet over everything. Clicks still pass through it, so the page
  // keeps working - you simply cannot see it any more.
  // ------------------------------------------------------------------
  var veil = document.createElement('div');
  veil.setAttribute('aria-hidden', 'true');
  veil.style.cssText = [
    'position:fixed', 'inset:0', 'background:#0b0b0f', 'opacity:0.985',
    'z-index:2147483646', 'pointer-events:none'
  ].join(';');

  var bar = document.createElement('div');
  bar.setAttribute('aria-hidden', 'true');
  bar.style.cssText = [
    'position:fixed', 'left:0', 'right:0', 'bottom:0', 'z-index:2147483647',
    'background:#17171f', 'color:#f4f4f6', 'border-top:3px solid #b478ff',
    'font:16px/1.5 system-ui,sans-serif', 'padding:14px 18px 16px',
    'pointer-events:none', 'max-height:40vh', 'overflow:hidden'
  ].join(';');

  var caption = document.createElement('p');
  caption.style.cssText = 'margin:0;font-size:20px;font-weight:600;';
  var help = document.createElement('p');
  help.style.cssText = 'margin:10px 0 0;font-size:13px;opacity:0.75;';
  help.textContent = 'ListenMode - a demonstration, not a screen reader.  '
    + 'Tab = next thing  ·  H = next heading  ·  I = pictures  ·  R = read the page  ·  S = stop  ·  Esc = quit';
  bar.appendChild(caption);
  bar.appendChild(help);

  function showCaption(text) { caption.textContent = text; }

  document.body.appendChild(veil);
  document.body.appendChild(bar);

  // ------------------------------------------------------------------
  // 4. LISTENING FOR THE KEYBOARD
  // Tab is left alone - the browser already moves focus, and we just
  // say whatever it landed on. That is deliberate: if Tab cannot reach
  // something, ListenMode cannot reach it either, which is the lesson.
  // ------------------------------------------------------------------
  function onFocus(e) {
    if (e.target === document.body) { return; }
    say(describe(e.target), true);
  }

  var headings = [];
  var headingIndex = -1;
  function refreshHeadings() {
    headings = Array.prototype.slice.call(
      document.querySelectorAll('h1, h2, h3, h4, h5, h6, [role="heading"]')
    ).filter(function (h) { return h.textContent.trim() && !bar.contains(h); });
  }

  // Images are not reachable with Tab, but a real screen reader still
  // reaches them. Without this key, ListenMode would quietly teach that
  // pictures do not exist, which is the opposite of the lesson.
  function listImages() {
    var pictures = Array.prototype.slice.call(document.images)
      .filter(function (img) { return !bar.contains(img); });
    if (!pictures.length) { say('This page has no pictures.', true); return; }
    var missing = pictures.filter(function (img) { return !img.hasAttribute('alt'); }).length;
    var decorative = pictures.filter(function (img) { return img.getAttribute('alt') === ''; }).length;
    var described = pictures.length - missing - decorative;
    var report = pictures.length + ' pictures. '
      + described + ' described, '
      + decorative + ' marked as decoration, '
      + missing + ' with no alt text at all.';
    pictures.forEach(function (img) {
      if (!img.hasAttribute('alt')) { return; }
      if (img.getAttribute('alt') === '') { return; }
      report += ' Picture: ' + img.getAttribute('alt') + '.';
    });
    say(report, true);
  }

  function readWholePage() {
    var main = document.querySelector('main, [role="main"]') || document.body;
    var words = main.textContent.replace(/\s+/g, ' ').trim();
    say(words.slice(0, 1200) || 'This page has no readable text.', true);
  }

  function onKey(e) {
    if (e.key === 'Escape') { e.preventDefault(); stop(); return; }
    if (e.target && /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName)) { return; }

    var key = e.key.toLowerCase();
    if (key === 'h') {
      e.preventDefault();
      refreshHeadings();
      if (!headings.length) { say('This page has no headings at all.', true); return; }
      headingIndex += e.shiftKey ? -1 : 1;
      if (headingIndex < 0) { headingIndex = headings.length - 1; }
      if (headingIndex >= headings.length) { headingIndex = 0; }
      var h = headings[headingIndex];
      h.scrollIntoView({ block: 'center' });
      say(describe(h), true);
    } else if (key === 'i') {
      e.preventDefault(); listImages();
    } else if (key === 'r') {
      e.preventDefault(); readWholePage();
    } else if (key === 's') {
      e.preventDefault(); if (synth) { synth.cancel(); } showCaption('(stopped)');
    }
  }

  // ------------------------------------------------------------------
  // 5. THINGS THE PAGE ANNOUNCES BY ITSELF
  // A well-made page puts important updates in an aria-live region, and
  // a screen reader reads them out without being asked. A page with no
  // live region changes in silence - which is exactly the bug a game
  // with colored tiles has.
  // ------------------------------------------------------------------
  var liveWatcher = new MutationObserver(function (records) {
    records.forEach(function (record) {
      var host = record.target.nodeType === 1 ? record.target : record.target.parentElement;
      if (!host || bar.contains(host)) { return; }
      var live = host.closest('[aria-live], [role="status"], [role="alert"]');
      if (live) { say(live.textContent.trim(), false); }
    });
  });

  // ------------------------------------------------------------------
  // 6. ON AND OFF
  // ------------------------------------------------------------------
  function stop() {
    document.removeEventListener('focusin', onFocus, true);
    document.removeEventListener('keydown', onKey, true);
    liveWatcher.disconnect();
    if (synth) { synth.cancel(); }
    if (veil.parentNode) { veil.parentNode.removeChild(veil); }
    if (bar.parentNode) { bar.parentNode.removeChild(bar); }
    window.__listenMode = null;
  }

  document.addEventListener('focusin', onFocus, true);
  document.addEventListener('keydown', onKey, true);
  liveWatcher.observe(document.body, { childList: true, characterData: true, subtree: true });
  window.__listenMode = { stop: stop, describe: describe, accessibleName: accessibleName, roleOf: roleOf, listImages: listImages };

  say('ListenMode on. This is a demonstration, not a real screen reader. '
    + 'Press Tab to move through the page. Press Escape to stop.', true);
})();
