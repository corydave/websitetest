/* The LMS half of the frame handshake, as a file rather than pasted script.

   Why a file: Brightspace's editor keeps <script> tags but empties them. An
   inline listener pasted into a topic arrives as <script></script> and never
   runs, which looks exactly like nothing happening. A <script src> survives,
   because there is no inline content to strip.

   It also means the listener lives in one place. Fixing it here fixes every
   topic in every section on the next page load, instead of re-pasting into
   each one.

   Zero configuration on purpose. It finds the frame, reads the origin off the
   frame's own src, and trusts nothing else - which removes the single most
   common way this goes wrong, mistyping an origin that then silently matches
   nothing. */
(function () {
  "use strict";
  var TAG = "csc150";
  var MIN = 200, MAX = 20000;

  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else { fn(); }
  }

  ready(function () {
    var frames = document.querySelectorAll(
      'iframe[id="csc150frame"], iframe[data-csc150]');
    if (!frames.length) return;

    /* One listener, however many frames are on the topic. */
    var known = [];
    Array.prototype.forEach.call(frames, function (f) {
      var origin;
      try { origin = new URL(f.src, location.href).origin; } catch (e) { return; }
      known.push({ f: f, origin: origin });
    });
    if (!known.length) return;

    function forEvent(e) {
      for (var i = 0; i < known.length; i++) {
        if (known[i].origin === e.origin &&
            known[i].f.contentWindow === e.source) return known[i];
      }
      return null;
    }

    function context() {
      /* Brightspace puts the course and topic ids in its own URL. That is the
         only context available from here.

         Do NOT reach for replace strings such as {FirstName}: they do not
         resolve inside an HTML document, they are substituted once at save
         time with whoever saved the file, and every learner would then see
         that person's name. */
      var ids = (location.pathname.match(/\/(\d{3,})\//g) || [])
                  .map(function (x) { return x.replace(/\//g, ""); });
      return {
        tag: TAG, type: "host", host: "brightspace",
        course: ids[0] || "", topic: ids[1] || "",
        hash: location.hash || "",
        chrome: true            /* the LMS already shows a title above the frame */
      };
    }

    window.addEventListener("message", function (e) {
      var rec = forEvent(e);
      if (!rec) return;                         /* not ours, or wrong origin */
      var d = e.data;
      if (!d || d.tag !== TAG) return;

      /* The page announces itself once, on load. If this listener was not
         attached yet - the file is fetched, the iframe often is not - that
         single announcement is gone and the page never learns it is embedded.
         So greet on FIRST CONTACT of any kind, not only on "ready". */
      if (!rec.greeted) {
        rec.greeted = true;
        rec.f.contentWindow.postMessage(context(), rec.origin);
      }

      if (d.type === "ready") {
        /* already greeted above */

      } else if (d.type === "height" && typeof d.height === "number") {
        /* Clamp: a buggy or hostile page should not be able to set the topic
           to four million pixels, nor collapse it to nothing. */
        rec.f.style.height = Math.min(Math.max(d.height, MIN), MAX) + "px";

      } else if (d.type === "scroll") {
        /* The page asked to scroll to something inside itself. Only this
           window can actually move, so translate the offset and do it here. */
        var top = rec.f.getBoundingClientRect().top + window.pageYOffset + d.top;
        if (d.block === "center") top -= (window.innerHeight - d.height) / 2;
        else top -= 16;
        window.scrollTo({ top: Math.max(0, top),
                          behavior: d.smooth ? "smooth" : "auto" });
      }
    });
  });
})();