---
title: A world in a tag
chapter: libraries
time: About 30 minutes
id: vibe-lab-world
lede: A 3D world you can walk around in, borrowed from a library and described in your own words.
done: `world/index.html` opens from your `vibe-coding` folder, you can walk around a 3D scene you described, and you have run the background check on its library.
demo: 
video: 
video-length: 
transcript: 
guide: check
guide-label: The background check
---

::: picture
A 3D world. Ground under your feet, sky overhead, a few things standing around,
and you walk through it with the arrow keys and look around by dragging the
mouse. Open it on a phone and you can look around by turning the phone.

You don't write the 3D part yourself! It takes thousands of lines of very hard
math. You are going to *borrow* it, from a free library called `A-Frame`,
and the AI only has to write the part that is yours: what is in the world.

Pick a world before you start. A campsite at dusk. A floating island. Your
dream room. A garden of giant mushrooms. A small planet. Anything you can
describe in a few sentences.
:::

::: ask
Start a new chat and describe your world. Make sure it says:

- Build it with the `A-Frame` library
- **What is in the world** - the ground, the sky, and at least four things
  standing in it. Colors and sizes help
- I can **walk around** with the arrow keys or WASD and **look around** by
  dragging with the mouse
- **One thing reacts** when I click on it - it spins, changes color, grows,
  anything (be sure it's obvious, or tell me when you submit the URL)
- A short written description of the world on the page, under the scene,
  for anyone who cannot see it

Then paste the specification at the end of your prompt, exactly as it is.

{{spec}}

::: aside
Notice the first bullet. Naming the library is the skill this week. You do
not need to know how A-Frame works. You need to know it exists, and how to ask for it.
:::
:::

::: run
1. Make a new folder: `vibe-coding/world`.
2. Copy the code, paste it into TXTEDTR, and Save as `index.html` inside that
   folder.
3. Double-click `index.html`. Click once inside the scene, then walk around
   with the arrow keys. Click the thing that is meant to react.

::: tree
alt: Folder diagram. The vibe-coding folder contains the app folders from earlier chapters, and a new world folder holding index.html.
caption: One file, as always. The 3D engine is not in your folder at all - it arrives from the internet every time the page opens.
vibe-coding/
├── qr-code/
├── magic-8-ball/
├── ...
└── world/
    └── index.html   [[← this lab]]
:::

Last - the background check. Open `index.html` in TXTEDTR. Near the top, a
comment lists the library the page borrows. Answer the four questions, about a minute
each:

- **Is it real?** Search for *A-Frame*. Is there a real website for it?
- **Where is it coming from?** Find the `<script src="https://` line. Is the
  address A-Frame's own site, or one of the big CDNs - `cdnjs.cloudflare.com` or
  `cdn.jsdelivr.net`?
- **Is anybody still looking after it?** On A-Frame's GitHub page, how recently
  were the files changed?
- **Is this the newest version?** A-Frame's website says what the newest version
  is. Does it match the number in your address?

Write down your findings in a few words.
:::

::: read
::: q
Find a line in your file that starts with `<a-box`, `<a-sphere` or
`<a-cylinder`. Can you guess what it does?
---
A-Frame is unusual: you build the world out of HTML tags, the same kind of
tags you met in chapter 1. `<a-box color="tomato" position="0 1 -3">` is a red
box, one step up and three steps in front of you. It is the one library in this
course you can actually read. Try a hand edit if you like - change a color,
save, refresh.
:::

::: q
Your specification says *fully accessible*. What do you think a screen
reader finds in a 3D world?
---
Almost nothing. To a screen reader the whole scene is one blank rectangle. That
is why the prompt asked for a written description under it - it is the best
fix, and it is the same idea as alt text for a picture. Walking with the arrow
keys keeps it usable without a mouse. But no amount of *telling* the AI
"make it accessible" makes a 3D world accessible on its own. Somebody has to
decide what the words should say. That somebody is YOU.
:::

::: q
Switch off your wifi and refresh the page. What happens?
---
The world is gone. Your file is still there, but the library never arrives, so
there is nothing to draw with. That is what *borrowed* means. Switch the wifi
back on.
:::
:::

::: yours
Ask for one change to the world. Same loop as always: ask, copy, paste over
everything, save, refresh.

::: prompt
Make it nighttime: a dark sky with stars, a moon, and soft light, so the things in my world glow a little. Keep everything else exactly the same.
:::

Or say it your own way - *add a ring of trees around the edge*, *make the ground
water*, *put a sign over the door with my name on it*.
:::

::: grow
Two more, one at a time:

- Look around with your phone. Try running this on your phone! On an iPhone,
  the page may need to ask permission first; if nothing moves, ask the AI to
  *"add a button that asks permission to use the phone's motion"*.
- Step inside it. If you can borrow a VR headset, open the same address in
  the headset's browser and press the goggles button in the corner.
:::

::: keep
Your world is `vibe-coding/world/index.html`.

Want it online? Drag the `world` folder onto your repository on
`github.com`, the same as every app since chapter 3, and it will live at
`your-username.github.io/vibe-coding/world/`.

Write down your background-check findings and anything that surprised you.
:::

::: trouble
**The page is blank, or it shows the written description but no world.**

The library did not arrive. Check you are online. Then open the Developer
Console and look for a red line about a script that failed to load. If the
address in that line looks wrong, copy the red line into the same chat and tell
the AI the 3D library is not loading.
---
**I can look around but I cannot walk.**

Click once inside the scene first, so the arrow keys go to the world and not to
the page. If it still does not move, tell the AI: *"I can look around but the
arrow keys do not move me."*
---
**I see sky but none of my things.**

They are probably behind you, or buried in the ground. Turn around with the
mouse first. If they are still missing, tell the AI: *"put everything in front
of where I start, standing on the ground."*
---
**Things standing on the ground are cut off, as if they are sinking into it.**

Usually the ground is a circle. Tell the AI: *"things standing on the ground are
hidden below the horizon - make the ground a big flat plane instead of a
circle."*
---
**Clicking the special thing does nothing.**

Ask the AI to *"show a small dot in the middle of the screen, and let me click
things by looking at them and clicking"*. In 3D, clicking needs something to aim
with.
---
**Looking around by turning my phone does not work.**

It only works from the online `github.io` address, because phones only share
their motion with secure web addresses. On an iPhone, the page also has to ask
permission first; see the Grow step.
:::
