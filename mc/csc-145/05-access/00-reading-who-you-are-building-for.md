---
title: The field guide to who you are building for
chapter: access
kind: reading
time: About 15 minutes
id: vibe-reading-access
lede: We should be making web apps that are "fully accessible". It's time to do that with intention.
video: 
video-length: 
transcript: 
allow-github: yes
---

Every lab this week leans on *accessibility* - making your work enjoyable by everyone. This includes
people who are blind or visually impaired. People who are deaf or hard of hearing. People who are
colorblind. 

Scroll back through any chat you have had with the AI this semester. If you were using the
specifications prompt, then you had that piece right in the middle of it:

> * `Fully accessible - semantic HTML, ARIA labels, keyboard navigation`

This week you look to see if your work is accessible. 

::: raw
<div class="contents">
  <p>In this reading</p>
  <ol>
    <li><a href="#who">Who you are building for</a></li>
    <li><a href="#telling">Telling is not checking</a></li>
    <li><a href="#five">The five ingredients</a></li>
    <li><a href="#color">When color is the whole message</a></li>
    <li><a href="#toolkit">Your toolkit, all of it already installed</a></li>
    <li><a href="#listen">Hearing it for yourself</a></li>
    <li><a href="#good">What good actually looks like</a></li>
    <li><a href="#cheat">Cheat sheet</a></li>
  </ol>
</div>
:::

::: section
id: who
title: 1. Who you are building for

It is tempting to picture one person: somebody who cannot see, using a
computer that reads aloud. That person is real, but there are a number
of personas we are developing for. Best practices include programming
for *everyone*.

- Somebody whose trackpad died this morning and is working on keys alone.
- Somebody with a tremor.
- Somebody colorblind - around one man in twelve has some form of it.
- Somebody holding a baby in one arm.
- Somebody on a phone in bright sun, where low-contrast gray text vanishes.
- Somebody who is sixty-eight and whose eyes have changed.
- Somebody using a *screen reader*, who will only hear what you built.

You are not building for an edge case; you are building for most people, some
of the time, and some people, all of the time. It's called *universal design*, 

::: aside
The useful shift is from *compliance* to *craft*. Everything
in this chapter is something that makes an app better for everybody - clearer
labels, visible focus, information that does not depend on one sense. The
accessibility part is what happens when you do the normal things well. And you know
what? Your apps are just inherently better if they are user friendly for
everybody.
:::
:::

::: section
id: telling
title: 2. Telling is not checking

You may have told the AI to build something accessible in the past. It tried. It will have
used real buttons in most places, written alt text on most images, and
structured most pages sensibly. AI models are quite good at this, because the
web is full of examples of doing it properly.

And in that, it may have:

- removed a focus outline somewhere because it looked untidy
- used a `<div>` as a button because that was easier to style
- picked a gray that fails contrast on a white background
- written `alt="image"` on a photograph, which is worse than nothing
- built the entire feedback of a game out of color

*Nobody would ever have known.* The app looks right. It demos beautifully.
You would have shipped it, and the only people who would have found out are
the people who could not use it 😥.

That is a symptom of vibe coding (and non-vibe-coding to be honest),
and it is not really about accessibility. It is about
every instruction you will ever give an AI: *the output looking right is not
the same as the output being right,* and the only way to close that gap is to
go and look. This week we will use tools that go and look.
:::

::: section
id: five
title: 3. The five ingredients

Almost everything you will find this week is one of five things. You will see
all five in lab 1, in a game you build yourself and then break on purpose.

**One - can you get there with the keyboard?** Unplug your mouse and press Tab.
Everything you can click should be reachable. Anything that is not reachable
does not exist for some people.

**Two - can you see where you are?** As you Tab, something should visibly
light up. That outline is called the **focus ring**, browsers already have that
capbility wired in., and the single most common accessibility mistake on the web is code that
removes it for looking untidy. If you take it away, replace it with something
better.

**Three - is color carrying information on its own?** Red for wrong, green for
right, a yellow tile versus a green tile. If the only difference between two
things is the color, then for a chunk of your users there is no difference at
all. The fix is never to stop using color; it is to add a second signal.

**Four - does anything announce what just happened?** You press a button and a
message appears. You can see it. Somebody using a screen reader gets silence,
because the page changed and said nothing. This is the one place in the whole
course where you will need a thing called **ARIA**, and we will use some of it.

**Five - does everything have a name?** A button with an icon and no text is,
to a screen reader, "button". A text box whose only label is faded placeholder
text is "text box". Nameless controls are the most common finding in any
accessibility audit, and the fix is usually very quick and easy.

Keyboard, focus, color, announcement, names. That is the chapter.
:::

::: section
id: color
title: 4. When color is the whole message

Below are three tiles from a word game. Green means the letter is in the right
place, yellow means it is in the word but somewhere else, gray means it is not
in the word at all. Perfectly clear.

Tick the box to see the same three tiles roughly the way somebody with
deuteranopia - the most common kind of colorblindness - sees them. Then guess,
before you read on, which two tiles you think become hardest to tell apart.

You *do not need to look at the HTML*. That's only there for the hardcore nerds.

::: demo
file: color.html
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: system-ui, sans-serif; padding: 18px; }
  .tiles { display: flex; gap: 10px; margin: 14px 0; }
  .tile {
    width: 64px; height: 64px; border-radius: 6px;
    display: grid; place-items: center;
    font-size: 30px; font-weight: 700; color: #fff;
  }
  .right { background: #5aa84f; }   /* green - right place */
  .moved { background: #cbb239; }   /* yellow - wrong place */
  .absent{ background: #7b7f86; }   /* gray - not in the word */
  .mark { font-size: 13px; display: block; margin-top: -6px; }
  label { display: block; margin: 8px 0; font-size: 15px; }
  /* The filter below is an approximation of deuteranopia, the same thing
     Chrome's own DevTools can do to a whole page. */
  .sim { filter: url(#deuteranopia); }
</style>
</head>
<body>

<svg width="0" height="0" style="position:absolute">
  <filter id="deuteranopia">
    <feColorMatrix type="matrix" values="
      0.625 0.375 0     0 0
      0.700 0.300 0     0 0
      0     0.300 0.700 0 0
      0     0     0     1 0" />
  </filter>
</svg>

<div id="board" class="tiles">
  <div class="tile right">R<span class="mark" id="m1"></span></div>
  <div class="tile moved">A<span class="mark" id="m2"></span></div>
  <div class="tile absent">T<span class="mark" id="m3"></span></div>
</div>

<label><input type="checkbox" id="sim"> Show me deuteranopia</label>
<label><input type="checkbox" id="fix"> Add a second signal</label>

<script>
  // Toggle the colorblindness filter on the tiles.
  document.getElementById("sim").addEventListener("change", function (e) {
    document.getElementById("board").classList.toggle("sim", e.target.checked);
  });

  // The fix: a symbol as well as a color, so the color is not the only
  // thing carrying the message.
  document.getElementById("fix").addEventListener("change", function (e) {
    var on = e.target.checked;
    document.getElementById("m1").textContent = on ? "● in place" : "";
    document.getElementById("m2").textContent = on ? "▲ move it"  : "";
    document.getElementById("m3").textContent = on ? "× not in"   : "";
  });
</script>

</body>
</html>
:::

Watch the *green* tile in particular. Most people expect green and yellow to
be the problem; they are not, really. What happens is that *green collapses
into gray* - over three quarters of the difference between them disappears,
and the two tiles end up almost the same color.

Think about what those two tiles mean. Green is *this letter is exactly right.*
Gray is *this letter is not in the word at all.* They are the two most opposite
messages in the game, and for a player with deuteranopia your design has just
made them look the same. That is an unplayable game.

Now tick the second box as well. The colors have not changed at all - there is
simply something else to go on.

That is the whole move, every time. *Not "stop using color." Add a second
signal.* A symbol, a word, a border, a position.

::: aside
This is not a hypothetical. The real Wordle shipped with exactly this problem
and added a high-contrast mode after players pointed it out. You are in good
company, and so is the AI that is about to build you one with the same bug.
:::
:::

::: section
id: toolkit
title: 5. Your toolkit, all of it already installed

You do not have to download anything this week. Four of the five tools are
already in your browser, and most people never find them.

Open **Developer Tools** with <kbd>F12</kbd> (or <kbd>Cmd</kbd> + <kbd>Option</kbd> + <kbd>I**</kbd> on a Mac). Or, just right click anywhere in the web page and choose "Inspect.""

**Lighthouse** is its own panel along the top. Tick Accessibility, click
Analyze, and it gives you **a score out of 100** and a list of what is wrong,
in plain words, with the exact elements. It only works on a real web address,
not a file from your desktop - which is one more reason you put your work
online in chapter 3.

**The Accessibility pane** tells you what a thing is *called*. Elements panel,
pick something in the page, then the **Accessibility** tab in the sidebar on
the right. Both of these are often hidden behind a small **≫** button at the end of the tab
strip, which is why nobody finds it. A button with no name shows up there as
`button` with nothing next to it. That's bad. That's inaccessible.

**Emulate vision deficiencies** does to a whole live page what the checkbox
above did to three tiles. It is in the **Rendering** drawer.

**The Tab key** is free and it is the best tool you have. No panel, no setup.

::: aside
**Stop hunting through menus.** With Developer Tools open, press
<kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd> (<kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd> on a Mac) and type what you want - "lighthouse", "accessibility", "rendering". It finds any panel, wherever
Google moved it this year. Learn this one shortcut and you never need to
memorise where anything lives.
:::

Developer Tools has been the place you go to read red error text. This week it
becomes something you use to hunt for accessibility features.
:::

::: section
id: listen
title: 6. Hearing it for yourself

Reading about a screen reader is not the same as hearing one. There are three
ways to close that gap, and you should do at least the first.

**ListenMode.** A small tool built for this course. It hides the page, and as
you press Tab it says out loud what the keyboard has landed on - *"Submit,
button"*, or revealingly, *"button, with no name."* Install it once by dragging
a link to your bookmarks bar; after that it works on any page with one click.
The lab walks you through it.

| Key | What it does |
|---|---|
| **Tab** | move to the next thing, and hear what it is |
| **H** | jump to the next heading |
| **I** | list the pictures, and say which have no alt text |
| **R** | read the page from the top |
| **S** | stop talking |
| **Esc** | turn it off |

Note that this is a demonstration, not a screen reader. A
real one reads your whole computer, not one web page. It has a browse mode for
reading and a focus mode for filling things in, hundreds of keyboard commands,
and a far more careful way of working out what things are called. ListenMode
does one slice: it follows the keyboard and tells you what it finds. That slice
is enough to show you the holes in your own work, and it is not enough to tell
you what using a screen reader is actually like. This is kind of a simulation, of
sorts, of a screen reader. Just enough for you to whet your appetite.

**The real thing, if you are up for it.** Both big systems have one built in,
nothing to install.

| | Turn on and off |
|---|---|
| **Mac** | **Cmd + F5** (VoiceOver) |
| **Windows** | **Ctrl + Windows + Enter** (Narrator) |
| **Chromebook** | **Ctrl + Alt + Z** (ChromeVox) |

Two rules make this survivable.
1. Write the off switch down before you turn it
on - seriously. On paper. Because the moment it starts talking you will want
it to stop and you will not be able to read the screen to find out how.
2. Give yourself one small job rather than wandering. Close your eyes, find
the Start button on your Breathing Buddy, press it. Sixty seconds. Unstructured
exploration is where people panic and decide this is all too hard, which is the
opposite of the point.
:::

::: video
// [NOTE TO CLAUDE - I HAVEN'T MADE IT YET BUT I WILL SOON]
:::

::: section
id: good
title: 7. What good actually looks like

Two things that you will notice:

**Use the real thing.** A `<button>` is focusable, clickable, works with Enter
and Space, announces itself as a button, and does all of that with no effort
from you. A `<div>` styled to look like a button does none of it until you add
it all back by hand, and you will miss some. The same goes for `<a>` for links,
`<label>` for labels, and real headings in order. **Semantic HTML** is the
highest-leverage thing in this whole chapter: pick the right tag and most of
the work is already done. If you didn't understand any of this paragraph, that's
fine. It just says that sometimes things *look* like a button but aren't really
a button, and that's an accessibility issue. AI should fix that!

**ARIA is a last resort, not a first one.** ARIA is a set of extra attributes
that tell assistive technology about things plain HTML cannot express. It is
useful and it is also the most misused thing on the web, because
people use it instead of just using the right tag. The rule people
actually say out loud is: *the first rule of ARIA is don't use ARIA.* If real
HTML can do it, use real HTML. It's like Fight Club, for web design.

You will need exactly one piece of it this week. When something changes on the
page without the person doing anything - a result appears, a score updates, an
error shows up - nothing announces it, because there is no HTML tag that means
*say this out loud when it changes*. That is what a **live region** is for:

```
<p id="status" aria-live="polite"></p>
```

Put text into that paragraph and a screen reader reads it out. That is the
whole thing, and it is the only ARIA this chapter asks you to use.

:::

::: note Do. Not. Panic.
If you didn't follow the whole "aria" thing, that's fine. All this is to say that
web pages are often inaccessible. By prompting AI to be more diligent, we can mitigate
many of the issues.
:::

::: section
id: cheat
title: 8. Cheat sheet

| Thing | What it means |
|---|---|
| **semantic HTML** | using the tag that means the thing: `<button>`, `<a>`, `<label>`, `<h1>` |
| **focus** | where the keyboard currently is |
| **focus ring** | the outline showing it. Never remove it without replacing it |
| **tab order** | the order Tab visits things. It follows your HTML |
| **accessible name** | what a screen reader calls something |
| **alt text** | what a picture says. `alt=""` means "decoration, skip it", which is correct |
| **contrast ratio** | how different two colors are. 4.5:1 is the usual pass for normal text |
| **ARIA** | extra attributes for what HTML cannot say. Use sparingly |
| **live region** | `aria-live`, the bit that says "read this out when it changes" |
| **assistive technology** | screen readers, magnifiers, switches, voice control |

**The five questions, for anything you ever build:**

1. Can I get to everything with Tab?
2. Can I see where I am?
3. Is color doing a job on its own?
4. Does anything announce what just happened?
5. Does everything have a name?

**Where to start when you have no idea:** run Lighthouse. It will hand you a
list. Fix the top three, upload, run it again.

...aaaaaaandddddd ask AI to make your work accessible. That goes a long way.
:::
