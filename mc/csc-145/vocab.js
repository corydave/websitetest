/* ====================================================================
   Vibe Coding - vocabulary pop-ups
   Inlined by the build into every lab and reading, after lab.js.

   The build also inlines a VOCAB object (from shared/vocabulary.html):

     VOCAB = {
       "tag": { title: "Tag", match: ["tag", "tags"], html: "<p>...</p>" },
       ...
     }

   What this script does:
     1. Walks the text of the page and, the FIRST time a vocabulary term
        appears in each step (or each section, on a reading), wraps that
        word in a small button. Later mentions in the same step are left
        alone, so a page is not covered in links.
     2. Clicking the button opens a small pop-up (a native <dialog>) with
        the term's definition and a link to the full vocabulary page.

   It never touches code samples in <pre>, the demo <textarea>s, links,
   other buttons, headings, or the pretend-browser chrome. Inline <code>
   IS allowed, on purpose: that is where "<title>" appears in a sentence.
   ==================================================================== */
(function () {
  'use strict';
  if (typeof VOCAB !== 'object' || !VOCAB) return;
  const dialog = document.getElementById('vocab-dialog');
  if (!dialog) return;

  // ---------------------------------------------------------------
  // 1. ONE REGULAR EXPRESSION FOR EVERY SPELLING OF EVERY TERM
  // Longest spellings first, so "opening tag" wins over "tag".
  // ---------------------------------------------------------------
  const spellings = [];             // [{ text, id }]
  Object.keys(VOCAB).forEach(function (id) {
    (VOCAB[id].match || []).forEach(function (text) {
      if (text.trim()) spellings.push({ text: text.trim(), id: id });
    });
  });
  spellings.sort(function (a, b) { return b.text.length - a.text.length; });
  const byLower = {};
  spellings.forEach(function (s) { byLower[s.text.toLowerCase()] = s.id; });

  function escapeRegex(text) { return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
  const pattern = new RegExp(spellings.map(function (s) { return escapeRegex(s.text); }).join('|'), 'gi');

  // A match only counts if it is a whole word: no letter or digit directly
  // before it (when it starts with one) or after it (when it ends with one).
  // "tag" must not light up inside "tags" or "stage".
  // Dots, hyphens, slashes and underscores count as glue too, so "html" does
  // not light up inside the file name "qr-code.html".
  const GLUE = /[\w.\-\/_]/;
  function isWholeWord(text, start, end) {
    const first = text[start], last = text[end - 1];
    const before = text[start - 1] || ' ', after = text[end] || ' ';
    if (/\w/.test(first) && GLUE.test(before)) return false;
    if (/\w/.test(last) && GLUE.test(after)) return false;
    return true;
  }

  // ---------------------------------------------------------------
  // 2. WHERE NOT TO LOOK
  // ---------------------------------------------------------------
  const SKIP = 'pre, textarea, svg, a, button, label, input, select, script, style, h1, h2, dialog, .fake-chrome, .contents, .ribbon, .term-link, .tools-note, .submit-card';

  function skippable(node) {
    for (let el = node.parentElement; el; el = el.parentElement) {
      if (el.matches(SKIP)) return true;
    }
    return false;
  }

  // ---------------------------------------------------------------
  // 3. LINK THE FIRST MENTION OF EACH TERM IN EACH BLOCK
  // A "block" is a step body on a lab, or a section on a reading.
  // ---------------------------------------------------------------
  const main = document.getElementById('main');
  if (!main) return;
  let blocks = Array.from(main.querySelectorAll('.step-body, :scope > section'));
  if (!blocks.length) blocks = [main];

  blocks.forEach(function (block) {
    const seen = new Set();
    const walker = document.createTreeWalker(block, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    let node;
    while ((node = walker.nextNode())) {
      if (node.nodeValue.trim() && !skippable(node)) textNodes.push(node);
    }
    textNodes.forEach(function (textNode) { linkTerms(textNode, seen); });
  });

  function linkTerms(textNode, seen) {
    const text = textNode.nodeValue;
    pattern.lastIndex = 0;
    let match, last = 0;
    const pieces = [];
    while ((match = pattern.exec(text))) {
      const start = match.index, end = start + match[0].length;
      const id = byLower[match[0].toLowerCase()];
      if (!id || seen.has(id) || !isWholeWord(text, start, end)) continue;
      seen.add(id);
      pieces.push(document.createTextNode(text.slice(last, start)));
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'term-link';
      button.dataset.term = id;
      button.textContent = match[0];
      button.setAttribute('aria-label', match[0] + ' - show definition');
      pieces.push(button);
      last = end;
    }
    if (!pieces.length) return;
    pieces.push(document.createTextNode(text.slice(last)));
    const parent = textNode.parentNode;
    pieces.forEach(function (piece) { parent.insertBefore(piece, textNode); });
    parent.removeChild(textNode);
  }

  // ---------------------------------------------------------------
  // 4. THE POP-UP
  // A native <dialog>: Escape closes it, focus stays inside it, and the
  // page behind is dimmed. When it closes, focus goes back to the word
  // that opened it.
  // ---------------------------------------------------------------
  const titleEl = dialog.querySelector('#vocab-title');
  const bodyEl  = dialog.querySelector('#vocab-body');
  const moreEl  = dialog.querySelector('#vocab-more');
  let opener = null;

  function open(id, button) {
    const entry = VOCAB[id];
    if (!entry) return;
    opener = button;
    titleEl.innerHTML = entry.title;
    // The definition HTML comes from shared/vocabulary.html, written by the
    // course author and inlined at build time - not from anything a visitor
    // typed - so putting it in with innerHTML is safe here.
    bodyEl.innerHTML = entry.html;
    if (moreEl) moreEl.hash = '#' + id;
    if (typeof dialog.showModal === 'function') { dialog.showModal(); } else { dialog.setAttribute('open', ''); }
  }

  document.addEventListener('click', function (e) {
    const button = e.target.closest('.term-link');
    if (button) { e.preventDefault(); open(button.dataset.term, button); }
  });

  dialog.querySelector('#vocab-close').addEventListener('click', function () { dialog.close(); });
  // Clicking the dimmed backdrop (outside the pop-up's box) closes it too.
  dialog.addEventListener('click', function (e) {
    const box = dialog.getBoundingClientRect();
    const outside = e.clientX < box.left || e.clientX > box.right || e.clientY < box.top || e.clientY > box.bottom;
    if (outside) dialog.close();
  });
  dialog.addEventListener('close', function () { if (opener) { opener.focus(); opener = null; } });
})();
