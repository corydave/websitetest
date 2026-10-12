---
title: The field guide to code other people wrote
chapter: libraries
kind: reading
time: Reference - dip in when you are curious or stuck
id: vibe-reading-libraries
lede: This reading is here for the moment you wonder where the superpower came from, or whether you should trust it. If you are curious about where your code came from...
video: 
video-length: 
transcript: 
---

You do not have to read this. Just know that every lab has a button at the
top that jumps to the one section it relates to.

This week you build a 3D world, a 3D game, a face tracker, a PDF toolkit and a
dashboard. Almost nobody could write any of those from scratch in an afternoon.
You will build all five! Because each one relies on code
that somebody else spent years on and gave away - for free. The whole week fits in a
sentence: 

> Only one line of code in your program borrows a superpower, and cool things are
possible, but there's something to be aware of...

::: section
id: borrowed
title: 1. You have been doing this since chapter 1

A **library** is code somebody else wrote so that you don't have to. Drawing a
QR code, drawing a chart, building a 3D world, reading an Excel file: each of
those is a problem that someone solved once, carefully, and then put
online for anyone to use. In their own repo!

Your page borrows a library with one line near the top, something like this:

```
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
```

*That line is probably already in your code.* The QR generator you built in
chapter 1 almost certainly borrows a library to draw the QR code, and chapter 1
promised you would find out why the page needed the internet. This is why. If
you are curious, open your `qr-code/index.html` in TXTEDTR and look near the top
for a line that starts `<script src="https://`. Only if you want; you do not have to.

Try it below! The button uses a library that draws confetti. The library is the
first line; everything that makes it go is one more line. Press the button, then
**delete the `<script src` line** in the code and press it again.

::: demo
file: party.html
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<!-- LIBRARY: canvas-confetti 1.9.4, from jsDelivr - draws confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.4/dist/confetti.browser.js"></script>
<style>
  body { font-family: system-ui, sans-serif; padding: 24px; text-align: center; }
  button { font-size: 20px; padding: 12px 24px; border: 0; border-radius: 10px;
           background: #264653; color: #fff; cursor: pointer; }
  #note { margin-top: 18px; min-height: 1.5em; }
</style>
</head>
<body>
<button id="go">Celebrate</button>
<p id="note" aria-live="polite"></p>
<script>
  // When the button is pressed: if the library arrived, use it.
  // If it did not, say so, instead of failing silently.
  document.getElementById('go').addEventListener('click', function () {
    if (typeof confetti === 'function') {
      confetti();
      document.getElementById('note').textContent = '';
    } else {
      document.getElementById('note').textContent = 'Nothing - the library is not here any more.';
    }
  });
</script>
</body>
</html>
:::

Without that one line, there is no confetti. Your app *depends* on it, which is
why a library your app needs is also called a **dependency**. In this case, the *dependency** is `confetti.browser.js`.
:::

::: section
id: cdn
title: 2. Where the library lives

The address in that line points at a **CDN** - a *content delivery network*. It
is a company that keeps copies of popular libraries on computers all over the
world, so your page can fetch one quickly wherever it is opened. The big two are
free: **cdnjs** and **jsDelivr**.

Read the address like a label on a box:

```
https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js
        |__________________|           |______| |___| |___________|
                who                      what   which     file
```

- **Who** is handing it to you - here, cdnjs.
- **What** library it is - `qrcodejs`.
- **Which version** - `1.0.0`.
- **The file** - `.min.js` means *minified*: squashed onto one line with no
  spaces, so it downloads faster. It is not meant for people to read. I mean, you 
  *can* read it, but it's just a whole lotta letters and numbers crammed next to
  each other.

Two consequences you have already experienced:

- **The page needs the internet**, even when it is a file on your own computer.
  Every time it opens, it fetches the library from that address. Offline, the
  library can't be reached and the app goes quiet.
- **If the address is wrong, nothing tells you on the page!** The library just
  does not show up. The Developer Console will have a red line about a script
  that failed to load. That is the first place to look whenever a borrowed
  superpower is missing.
:::

::: section
id: check
title: 3. The background check

When you add that line, **a stranger's code runs inside your page** with exactly
the same powers as your own. Almost always, that is a gift. If you are building apps
of consequence, there are a few questions you should seek out if you are relying
on someone elses' code:

**1. Is it real?** Search the library's name. A real one has its own website or
a page on GitHub, and lots of people talking about it. An AI can invent a
library that sounds right and does not exist!!!

**2. Where is it coming from?** The address should start with the library's own
site, or one of the big CDNs: `cdnjs.cloudflare.com` or `cdn.jsdelivr.net`. A
stranger's address you have never heard of is a reason to stop and ask. Stranger Danger!

**3. Is anybody still looking after it?** On its GitHub page, look for when the
files were last changed. A library somebody still works on is **maintained**.
One nobody has touched in years is not, which means if a problem is found,
nobody is coming to fix it. Old libraries can be insecure, too, as web technology
progresses and those libraries don't keep up.

**4. Is this the newest version?** The library's own website will say what the
newest version is. Compare it with the number in your address. AI may not know about
the latest versions!

Then make a call. *Old is not the same as bad.* A QR code works exactly the way
it did ten years ago, so a QR library nobody has touched in ten years is fine.
What deserves a second thought is *old and doing something risky* - handling
files or apps that require security or privacy.

::: aside
Your specification asks the AI to list every library in a comment at the top of
the file - its name, version, where it comes from and what it does. That comment
is your checklist. You never have to hunt through the code.
:::
:::

::: section
id: versions
title: 4. Version numbers, and pinning one

Most libraries number their versions with three numbers: `4.5.1`.

- The **first** number changes when the library changes in a way that might
  break apps built with the old one.
- The **second** changes when something new is added.
- The **third** changes for small fixes.

If you start making more sophisticated apps, just tell your AI you want it to
do track the versioning, and it will!

An address with an exact number in it is **pinned**: it will fetch that exact
version forever, and your app will behave tomorrow exactly the way it behaved
today. Some addresses say `latest` instead, or leave the number out. Those
fetch whatever the newest version is on the day the page opens, which
sounds helpful until the day the library changes and your app breaks with no
change from you.

**Pin the version.** Your specification asks for it. When you want a newer one,
you change the number on purpose, and test.
:::

::: section
id: trust
title: 5. When borrowing goes wrong

Three true stories, each one a reason for the background check.

### The address changed hands

A library called `polyfill.io` was used by more
than a hundred thousand websites, all loading it from the same address. In 2024
that address was sold. The new owner started sending different code to some
visitors: code that redirected them to scam sites. Nothing changed on any of
those hundred thousand websites. Their owners had trusted an *address*, and the
address had a new owner. This kind of attack, where the problem comes in through
something you borrowed, is called a **supply chain attack**.

### The AI made up a name

AI tools sometimes suggest a library that does not
exist, with a name that sounds perfect - and they tend to invent the *same* names
again and again. A security researcher noticed one invented name that kept
coming up, and published an empty library under it to see what would happen.
Tens of thousands of people downloaded it within a few months, because an AI had
told them to. His was harmless. An attacker's would not be. Question 1 of the
background check - *is it real?* - is the whole defense, and it takes thirty
seconds.

### The right library, the wrong copy

SheetJS reads Excel files, and it is
excellent. But the copies on the big CDNs stopped updating years ago at an old
version with a known security problem. The fixed versions are only on SheetJS's
own site. An AI, having seen the old address thousands of times, will very often
use it. Lab 6.5 is where you try to catch this behavior.

The pattern in all three: *the app looked fine*. You find these by checking,
not by looking. You have heard that before - *telling is not checking*.
:::

::: section
id: privacy
title: 6. Your files stay on your computer

The googly eyes use your camera. The PDF toolkit and the dashboard open your
files. Here is the part that surprises people. *None of it leaves your
computer* - not your face, not your PDFs, not your spreadsheets. The library came down from the internet *to* your browser, and
all the work happens right there. Nothing goes back up. Now, that's not always the case,
but when I design labs and software, I try to make them as private as I can.

For the googly eyes, that includes the *model* - the part that has learned what
a face looks like. It is a file that comes down from Google once, and then
does its work on your machine. Your camera picture never goes up.

Compare that with the free websites that merge PDFs or turn spreadsheets into
charts. To work, they need your file *on their computer*. Your lease, your tax
form, your company's sales numbers, sitting on a stranger's server, under
whatever rules that stranger decided.

You can prove yours is different. Open your app, wait for it to load, then
*switch off your wifi*. Drop a file in, or wave at the camera. It still works,
because it never needed to send anything anywhere.

*The one thing a CDN does learn.* When your page fetches a library, the CDN
can see that *somebody* opened a page that uses it, and roughly where they are.
It never sees your files or what you type. That is the trade, and for the big
CDNs it is a fair one. It is also one more reason to load libraries only from
places you trust.
:::
