---
title: Roll the ball
chapter: libraries
time: About 30 minutes
id: vibe-lab-roll-the-ball
lede: A tiny 3D game - a ball, a floor, three spinning coins - built on the library that half the 3D apps use.
done: `roll-the-ball/index.html` opens from your `vibe-coding` folder, you can roll the ball with the keyboard and collect all three coins, and you have checked which version of Three.js it uses.
demo: 
video: 
video-length: 
transcript: 
guide: versions
guide-label: Version numbers
---

::: picture
A ball sitting on a floor. You roll it around with the arrow keys. Three gold
coins spin in the air, and when you roll into one, it vanishes and your score
goes up. Get all three and you win.

That is a game. It's a very small one, but it's a game. Every 3D game has the same bones: a world,
a thing you control, things to collect, and a score.

This one is built on **Three.js**, the most popular 3D library on the web.
Remember the 3D world from last lab? A-Frame is built on top of Three.js. Today
you skip the friendly layer and borrow the engine underneath it.
:::

::: ask
Start a new chat and describe the game. Make sure it says:

- Build it with the **Three.js** library
- A big flat **floor**, seen from above at an angle, so I can see the whole thing
- A **ball** I roll with the **arrow keys or WASD**. It should not be able to
  roll off the edge
- **Three gold coins** floating and spinning in different places. Rolling into
  one **collects** it
- A **score** on the screen, and a **"You got them all!"** message with a
  **Play again** button at the end
- Every time the score changes, say it out loud for screen readers

Then paste the specification at the end of your prompt, exactly as it is.

{{spec}}

::: aside
Want it to be yours from the start? Change the nouns. A cat collecting fish. A
snowball collecting snowflakes. A spaceship collecting stars. The shapes stay
simple; the story is up to you.
:::
:::

::: run
1. Make a new folder: `vibe-coding/roll-the-ball`.
2. Copy the code, paste it into TXTEDTR, and **Save as** `index.html` inside that
   folder.
3. Double-click `index.html`. Roll into all three coins. Press **Play again**.

::: tree
alt: Folder diagram. The vibe-coding folder contains the app folders from earlier chapters, the world folder from the last lab, and a new roll-the-ball folder holding index.html.
caption: Same shape as every app. The 3D engine arrives from the internet when the page opens.
vibe-coding/
├── ...
├── world/
└── roll-the-ball/
    └── index.html   [[← this lab]]
:::

**Last: the background check.** Run all four questions on the Three.js line, but
spend your time on the last one: **is this the newest version?**

- Find the version number in your file's library comment, or in the address. It
  may look like `0.160.0`, or like `r128`. Three.js counts its releases, so `r128`
  means release 128, and `0.160.0` means release 160.
- Go to **threejs.org**. The number of the newest release is near the top of the
  page.
- Compare the two. **How many releases behind is yours?**

Write down your verdict in a few words. There is a good chance your AI handed you
an old one. Read it, below, explains why, and whether that matters.
:::

::: read
::: q
**Your Three.js is many releases old, and the game works fine.** Should you
change it?
---
Not necessarily. Last lab's question was *is it real?* This lab's is *how old is
my copy?*, and the answer depends on what the code is doing. An old copy of a
3D engine drawing a ball and three coins on your own computer is not a danger to
anybody. **An old copy with a known security problem is different** - you will
meet one of those in the last lab of the chapter, and that one you will fix.
The Grow step below shows how to move to the newest version if you want to.
:::

::: q
**Why would the AI give you an old version in the first place?**
---
Because the AI learned from years of code written by other people, and for a
long time one particular old version was what almost everybody used. It is the
answer the AI has seen most often, not the newest one. The AI cannot look at
threejs.org. You can.
:::

::: q
**A-Frame, last lab, is built on Three.js.** What does that mean for the
background check you ran on A-Frame?
---
That A-Frame borrowed Three.js, so when you borrowed A-Frame, you borrowed Three.js
too, without ever seeing its name. Libraries have dependencies of their own. That
chain is called the **supply chain**, and it is why trusting a library really
means trusting everybody *it* trusts. The field guide has a story about what
happens when one link goes bad.
:::
:::

::: yours
Ask for **one change**. Same loop as always: ask, copy, paste over everything,
save, refresh.

::: prompt
Make the camera follow the ball smoothly as it rolls, so the floor can be bigger than the screen. Keep everything else exactly the same.
:::

Or say it your own way - *add a timer and show my best time*, *make the coins
bounce up and down as they spin*, *play a little sound when I collect one*.
:::

::: grow
One at a time:

- **Move to the newest Three.js.** First make a version copy: save your working
  file as `index-v1.html`, so you can always go back. Then copy the newest release number from
  threejs.org, then ask: *"Use Three.js version [the number] from jsDelivr, with
  the exact version in the address. Keep everything else exactly the same."*
  Newer versions of Three.js load in a different way, so the AI may change more
  than one line. **Test everything afterward** - that is what changing a version
  on purpose means.
- **Add a danger.** A red block that slides back and forth. Touch it and the
  game restarts.
- **More levels.** When you collect all three coins, a new floor appears with
  five coins and the score keeps going.
:::

::: keep
Your game is `vibe-coding/roll-the-ball/index.html`.

**Want it online?** Drag the `roll-the-ball` folder onto your repository on
`github.com`, and send `your-username.github.io/vibe-coding/roll-the-ball/` to
someone who will try to beat your time.

**Write down** your background-check verdict - which version you got, and how
far behind it was. Hang on to this game; the course comes back to games later.
:::

::: trouble
**The page is blank, or there is a score but no floor or ball.**

The library did not arrive. Check you are online, then open the Developer
Console and look for a red line about a script or module that failed to load.
Copy the red line into the same chat.
---
**The arrow keys scroll the page instead of rolling the ball.**

Tell the AI: *"the arrow keys scroll the page - they should only move the ball."*
---
**I roll straight through the coins and nothing happens.**

The game is checking for a collision in the wrong way, or the coins are too high
up. Tell the AI: *"rolling into a coin does not collect it - collect it when the
ball gets close to it."*
---
**The ball rolls off the edge and falls forever.**

Tell the AI: *"the ball can roll off the floor - stop it at the edges."*
---
**After I asked for the newest version, everything broke.**

That is the risk of changing a version, and it is why you made a version copy first.
Copy the red line from the Developer Console into the same chat and say *"this
broke after moving to the newer Three.js."* If it will not come right, go back
to your old copy - it worked, and that is allowed.
:::
