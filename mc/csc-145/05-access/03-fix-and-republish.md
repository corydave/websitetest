---
title: Fix and republish
chapter: access
time: About 35 minutes
id: vibe-lab-fix-republish
lede: You fixed one thing. Now take a single app and fix everything you can find in it.
done: At least three accessibility findings in one app are fixed, uploaded, and re-checked on the live site, with the before and after Lighthouse scores written down.
demo: 
video: 
video-length: 
transcript: 
report: yes
q1: Which app did you fix, and what were its scores before and after?
q2: Which of the five ingredients did the AI get right on its own, without being asked?
guide: five
guide-label: The five ingredients
---

::: picture
Last lab you ran the loop once: find one problem, fix one problem, put it back
online, check the number.

This lab you run it until you run out. **One app, every problem you can find in
it.** Three, minimum.

Doing it once proved you could. Doing it until there's nothing left is a
different and better feeling, but most
software is abandoned the day it works. That loop - change, deploy, verify -
is how everything on the internet gets maintained, and you now know it well
enough to do it without being walked through.
:::

::: ask
No single prompt this time. You will write one per finding:

> **What is wrong, where, and what should happen instead.**

| Weak | Strong |
|---|---|
| *make it accessible* | *the Stop button cannot be reached with Tab - it should be a real button that the keyboard can get to* |
| *fix the colors* | *the gray text on the white card is too faint - Lighthouse says 2.9 to 1 and it needs at least 4.5 to 1* |
| *add alt text* | *the four photos on this page have no alt text - here is what each one shows, in order* |

The right-hand column is the whole skill. You are not asking for a better app;
you are reporting a defect. That is a different kind of sentence and it gets a
different kind of answer.

::: aside
**Alt text is the one thing the AI cannot do for you.** It has never seen your
photographs. If you ask it to "add alt text" it will invent something plausible
and wrong, which is worse than leaving it blank. Describe each picture yourself
in a few words and hand it the words. This is the one place this week where
you are the only one who knows the answer.
:::
:::

::: run
You know these steps now. Here they are once, and then you repeat them.

For each of at least three findings:

1. Open the app's folder, open `index.html`, and copy the whole file into a
   fresh chat.
2. Write the problem in one sentence, the way the table above does.
3. Add: *"Change only what is needed to fix this. Keep everything else exactly
   the same."*
4. Paste the new version over your file. Save.
5. **Open it locally and check it still works.** Fixing accessibility by
   breaking the app is not a win.
6. **Upload it** to your site, the chapter 3 way.
7. **Hard refresh the live page** - <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>R</kbd>, or <kbd>Cmd</kbd>+<kbd>Shift</kbd>+<kbd>R</kbd> on a
   Mac - and check the fix is really there.
8. **Run Lighthouse again** and write the new score next to the old one.

Run out of findings before you hit three? Put the mouse down and press
<kbd>Tab</kbd> through the whole app instead. Anything you can't reach, and
anything you can reach but can't see, is a finding.

::: aside
Step 7 is not optional padding. GitHub Pages can take a minute or two to serve
the new version, and your browser will happily show you the old one until you
force it. More than one student every semester concludes a fix did not work
when it worked fine two minutes ago. Hard refresh, then judge.
:::
:::

::: read
::: q
**Your score went from 82 to 94. Is the app fixed?**
---
It is better. Whether it is *fixed* depends on things Lighthouse never looked
at. Put the mouse down and Tab through the whole thing. The score moving is
evidence that you changed something real; it is not a certificate.
:::

::: q
**You fixed the contrast and the score did not change at all. What happened?**
---
Usually one of three things. The upload did not land - check the live page's
source. The browser is showing you the old version - hard refresh. Or you fixed
an instance Lighthouse was not complaining about, and the one it meant is still
there. Click the finding in Lighthouse; it names the exact element.
:::

::: q
**The AI's fix changed things you did not ask it to change.**
---
That is why step 3 says *change only what is needed.* It will still happen
sometimes. Compare the new file against your `index-v1.html` copy, or just
ask: *"list everything you changed."* Getting into the habit of asking that
question will serve you for years.
:::
:::

::: yours
**Take it all the way.** Keep going on that same
app until Lighthouse has nothing left and you can play the whole thing with
your hands off the mouse.

Choose the one you like best. The adventure, maybe, or Breathing Buddy. The
point is to find out what *finished* feels like, once, on something that is
yours and is online and that somebody could actually use.

Then do the thing that matters most and takes the least time: send it to
somebody. The link still works, it goes to the same address it always did,
and it is better than it was when you started.

::: aside
Notice something about the `-v1` copies you have been making since chapter 1 -
and if you have not made one of this app yet, make it now, before you start.
You now have a before and an after of real work, five weeks apart, with a
number attached to the difference. That is the most convincing thing you could
put in front of anybody who asks what you learned this semester. Keep them.
:::
:::

::: grow
**One - the focus ring, properly.** Pick any app and find where the focus
outline is set. Remove it completely, save, and try to use the app with Tab.
Then put it back, and make it better than the browser's default:

::: prompt
Give focused elements a clear custom focus style - a thick outline in a color that stands out against this page's background, with a small gap between the outline and the element. Apply it everywhere, not just to buttons.
:::

**Two - reduced motion.** Some people get unwell from movement on
screen, and their computer already knows they have asked for less of it.

::: prompt
Honor prefers-reduced-motion: when someone has asked their system for reduced motion, turn off or greatly shorten the animations, but keep the app fully working.
:::

**Three - fix the hub.** It is the front door, every one of these links goes
through it, and nobody has ever audited it.
:::

::: keep
- Your apps are updated **and uploaded**. The versions on your site are now
  better than the ones that were there when you started.
- **Your scores, before and after.** That is the lab report.
- Your `-v1` folders are untouched, which means you can always show the
  difference.
- **Did anything surprise you?** A one-line fix that moved the score ten
  points. A fix that broke the app. How long alt text takes to write properly.
  **Write it down.**
- **Whatever you did not get to.** There is always something left, and that is
  normal - real projects are never finished either. Write it down and keep it;
  you will want it in chapter 13 when you build the portfolio.
:::

::: trouble
**I uploaded it and the live page has not changed.**

Hard refresh first - **Ctrl+Shift+R** or **Cmd+Shift+R**. Still old? GitHub
Pages can take a couple of minutes. Still old after that, check you replaced
the file in the right folder, and that it is still called `index.html`.
---
**The fix works locally but not on the site.**

You uploaded a different file than the one you fixed, or you uploaded it to the
wrong folder. Open the live page, view source, and look for your change. If it
is not there, it is an upload problem and not a code problem.
---
**The fix broke the app.**

Go back to your `index-v1.html` copy and start again with a narrower request. Tell the
AI what broke: *"that change stopped the Start button working - keep the
accessibility fix but get the button working again."*
---
**Lighthouse says I still have a contrast problem and I cannot see which bit.**

Click the finding. It expands and lists the exact elements, and hovering one
highlights it in the page. If it is text over a photo, that is a hard case -
the usual answer is a semi-transparent panel behind the text.
---
**I cannot work out what alt text to write.**

Ask yourself what you would say to somebody on the phone who asked what the
picture was. That is the alt text. If the picture is pure decoration and says
nothing - a divider, a background swirl - then `alt=""` is the correct answer
and it means "skip this".
---
**My score went down.**

Possible, and worth looking at rather than panicking. A fix that adds new
controls adds new things to check. Read the new findings; they are usually
small and they are usually about the thing you just added.
:::
