/**
 * FLX AI Hub — Shared Navbar
 *
 * Each page sets these before loading this script:
 *   window.BASE_PATH   = ''      (root pages) or '../' (one level deep)
 *   window.ACTIVE_PAGE = 'home' | 'media' | 'blog' | 'tools' | 'resources' | 'stay-informed' | 'flcc'
 *
 * Include on every page:
 *   <div id="navbar-placeholder"></div>
 *   <script>window.BASE_PATH=''; window.ACTIVE_PAGE='home';</script>
 *   <script src="navbar.js"></script>   (adjust path for subdirectories)
 */
(function () {
    var bp = (typeof window.BASE_PATH !== 'undefined') ? window.BASE_PATH : '';
    var ap = (typeof window.ACTIVE_PAGE !== 'undefined') ? window.ACTIVE_PAGE : '';

    // Fallback: auto-detect active page from URL
    if (!ap) {
        var p = window.location.pathname.toLowerCase();
        if (p.includes('/media/'))        ap = 'media';
        else if (p.includes('/blog'))     ap = 'blog';
        else if (p.includes('/tools'))    ap = 'tools';
        else if (p.includes('resources')) ap = 'resources';
        else if (p.includes('stay-informed')) ap = 'stay-informed';
        else if (p.includes('what-we-do'))  ap = 'flcc';
        else                              ap = 'home';
    }

    function cls(page) {
        return 'nav-link' + (ap === page ? ' active' : '');
    }

    // Inline SVGs so this script has zero external dependencies
    var iconExternal = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
    var iconChevron  = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
    var iconMenu     = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';

    var html = [
        '<header class="site-header">',
        '  <div class="nav-container">',

        '    <a href="' + bp + 'index.html" class="site-logo">',
        '      <img src="' + bp + 'images/HubLogo.png" alt="FLX AI Hub Logo">',
        '      <div class="logo-text">',
        '        <span class="logo-title">FLX AI Hub</span>',
        '        <span class="logo-sub">Innovate &amp; Educate</span>',
        '      </div>',
        '    </a>',

        '    <button class="mobile-menu-btn" id="flx-mobile-toggle" aria-label="Open menu">' + iconMenu + '</button>',

        '    <nav class="main-nav" id="flx-main-nav">',

        '      <a href="' + bp + 'index.html" class="' + cls('home') + '">Home</a>',

        '      <div class="nav-dropdown" id="flx-media-dd">',
        '        <span class="' + cls('media') + ' dropdown-trigger">',
        '          Media <span class="chevron">' + iconChevron + '</span>',
        '        </span>',
        '        <div class="dropdown-menu">',
        '          <a href="' + bp + 'media/webinars/">Webinars</a>',
        '          <a href="' + bp + 'media/podcast.html">Podcast</a>',
        '        </div>',
        '      </div>',

        '      <a href="' + bp + 'blog/"               class="' + cls('blog') + '">Blog</a>',
        '      <a href="' + bp + 'tools/"             class="' + cls('tools') + '">Tools</a>',
        '      <a href="' + bp + 'resources.html"     class="' + cls('resources') + '">Resources</a>',
        '      <a href="' + bp + 'stay-informed.html" class="' + cls('stay-informed') + '">Stay Informed</a>',

        '      <div class="nav-dropdown" id="flx-flcc-dd">',
        '        <span class="' + cls('flcc') + ' dropdown-trigger">',
        '          FLCC <span class="chevron">' + iconChevron + '</span>',
        '        </span>',
        '        <div class="dropdown-menu">',
        '          <a href="' + bp + 'what-we-do.html">What We\'re Doing</a>',
        '          <a href="https://www.flcc.edu/ai/" target="_blank" rel="noopener">FLX AI Hub &nbsp;' + iconExternal + '</a>',
        '        </div>',
        '      </div>',

        '    </nav>',
        '  </div>',
        '</header>'
    ].join('\n');

    // Inject into placeholder
    var placeholder = document.getElementById('navbar-placeholder');
    if (placeholder) {
        placeholder.outerHTML = html;
    }

    // ---- Interactivity ----
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('flx-mobile-toggle');
        var nav    = document.getElementById('flx-main-nav');

        // Mobile hamburger
        if (toggle && nav) {
            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                nav.classList.toggle('open');
            });
        }

        // Close nav when clicking outside
        document.addEventListener('click', function (e) {
            if (nav && nav.classList.contains('open')) {
                if (!nav.contains(e.target) && e.target !== toggle) {
                    nav.classList.remove('open');
                }
            }
        });

        // All dropdowns: click-to-toggle on mobile; hover handled by CSS on desktop
        if (nav) {
            nav.querySelectorAll('.nav-dropdown').forEach(function (dd) {
                var trigger = dd.querySelector('.dropdown-trigger');
                if (trigger) {
                    trigger.addEventListener('click', function (e) {
                        if (window.innerWidth <= 768) {
                            e.preventDefault();
                            var isOpen = dd.classList.contains('open');
                            // Close all, then open the clicked one if it was closed
                            nav.querySelectorAll('.nav-dropdown').forEach(function (other) {
                                other.classList.remove('open');
                            });
                            if (!isOpen) dd.classList.add('open');
                        }
                    });
                }
            });
        }
    });
})();
