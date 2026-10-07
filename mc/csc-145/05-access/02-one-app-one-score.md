---
title: One app, one score
chapter: access
time: About 30 minutes
id: vibe-lab-lighthouse
lede: Pick the app you're least sure about. Let Chrome tell you what's wrong with it. Then ask the AI to fix it.
done: Chances are that one of your published apps has at least one accessibility problem. We can fix it and re-upload it. Let's use Lighthouse!!!
demo: 
video: 
video-length: 
transcript: 
guide: toolkit
guide-label: Your toolkit
---

::: picture
Open your hub page and look at what's on it. A QR generator, a timer,
a countdown, a recipe card, a page of your pictures, a Magic 8-Ball, a decision
maker, Breathing Buddy, an invite, an adventure.

In the last lab you broke something on purpose and then fixed it. You knew what
was wrong before you started, because the lab told you.

This time nobody tells you! You pick one app, and Chrome tells you if it's broken.

There's a tool built into the browser you already have open that will read your
page and hand you a list of accessibility problems in about ten seconds. No
install, no account. It's always been there - and it's great (especially if
you care about accessibility and SEO). It is a great deal nicer to hear it from
software than from somebody who couldn't use your app.

Short lab. Let's go!
:::

::: ask
There *is* a prompt in this lab, but it comes later. You can't ask for a fix
until you know what's broken, so we need to find a bug.

So, three things first:

1. **Pick one app.** Pick one that you *think* might have accessibility issues.
2. **Open it at its real web address**, the `github.io` one.
3. **Open Developer Tools** - <kbd>F12</kbd>, or right-click anywhere on the
   page and choose "Inspect".

::: aside
**It has to be the web address, not the file on your computer.** Lighthouse
refuses to run on a `file://` address. It's measuring a real page the way a
real visitor gets it, which is the whole point of having put your work online.
:::
:::

::: run
**Run Lighthouse.**

1. Along the top of Developer Tools, click **Lighthouse**. It's often hiding
   behind the **&raquo;** at the end of the row.
2. Untick everything except **Accessibility**.
3. Click **Analyze page load**.
4. Wait a few seconds.

You get a score out of 100 and a list underneath it.

**Write the score down.** You're going to want it later.

**Now open one finding.** Click any item in the list. It expands and shows you
the exact piece of your page that caused it, plus a plain-English explanation
and a "Learn more" link. Click the little element it shows you and Chrome jumps
you to that spot in your code.

That's the whole tour. 

**Now fix one of them.** Pick a finding - the top one is fine - and take it
back to the AI. Open the chat where you built this app, or a new one, and give
it three things:

1. Your `index.html`, pasted in or attached
2. The finding, copied out of Lighthouse word for word (you can even take a screenshot of the feedback and paste that in).
3. This instruction:

> Fix only this one problem. Don't redesign anything, don't rename anything,
> and don't change how the app looks or behaves for someone using a mouse.
> Then tell me in one sentence what you changed and why.

Asking for one fix is deliberate. A prompt that says "make this accessible"
gets you a rewritten app you no longer recognize, and you learn nothing. A
prompt that says "fix this one thing" gets you a change you can actually use.

Then put it back online, the same way you always do:

1. Save the new `index.html` over the old one
2. Upload it to GitHub
3. Give it a minute, then hard-refresh the live page - <kbd>Ctrl</kbd> +
   <kbd>Shift</kbd> + <kbd>R</kbd>, or <kbd>Cmd</kbd> + <kbd>Shift</kbd> +
   <kbd>R</kbd> on a Mac

Run Lighthouse one more time. Write the new score next to the old one.

That's the lab. Everything after this is extra.

::: note The number may barely move, and that's fine.
Some real fixes aren't worth any points, and some points come from things
nobody would notice. You fixed something that was wrong for somebody.
That happened whether or not the scoreboard agrees.
:::
:::

::: read
Three questions about what you're holding.

::: q
**You scored 94. Is your app accessible?**
---
Nobody knows, including Lighthouse. It checks the things a machine can check:
contrast ratios, missing alt text, unlabeled fields, heading order. It can't
tell whether your alt text is *useful*, whether your tab order makes sense, or
whether your game is playable without color.

A page can score 100 and still be hard to use. The score is just a starting point.
:::

::: q
**It says "Background and foreground colors do not have a sufficient contrast ratio." In English?**
---
Your text is too close in color to what's behind it. The usual pass mark is
**4.5 to 1** for normal-sized text, and Lighthouse shows you the exact ratio it
measured.

Gray text on white is the classic. It looks elegant on a nice monitor in a dim
room, and it vanishes on a phone outdoors. But white text on black background? Very visible.
:::

::: q
**It says a button has no accessible name. Why does that matter more than it sounds?**
---
Because that's the *entire* information some people get. Not "the thing next to
the search box" - just "button." No way to find out what it does except press
it and see what happens.

An icon-only button is almost always the culprit, and the fix is one attribute.
:::
:::

::: yours
**Fix a second one.** Same app, next finding, same one-problem-at-a-time
prompt. The second is much faster than the first, because you already know
what the answer looks like.

**Then try your most complicated app.** Whichever one took the longest to
build, or has the most buttons. Run Lighthouse on it and see what it says.

You can probably guess the result, and it's worth seeing anyway: your fanciest
app is almost certainly your least accessible one. More controls, more colors,
more chances to go wrong. That's true of the whole web, not just your corner
of it.

::: aside
**You're allowed to be annoyed.** Some of these were avoidable and the AI
should have done better. Some of them you wouldn't have caught in a hundred
years without a tool. Both are fine. The thing that matters is that you looked,
which is the whole skill this chapter is teaching.
:::
:::

::: grow
**One - check something you didn't build.** Run Lighthouse on a site you use
every day. A news site, a store, your bank. A lot of very large companies score
worse than you just did, which is either reassuring or alarming depending on
your mood.

**Two - check the front door.** Run it on your hub page. It's the one page
every visitor sees first and the one page nobody ever looks at twice.
:::

::: keep
- **Both scores**, before and after, and which app they belong to.
- **What the fix actually was** - in your own words, one sentence. "The button
  only had a picture in it, so I gave it a label."
- Your app is live and slightly better than it was half an hour ago.
- **Did anything surprise you?** An app you were proud of scoring badly. A
  problem you'd walked past twenty times. How fast it got found. Write it down.
:::

::: trouble
**I can't find Lighthouse.**

Look behind the **&raquo;** at the end of the row of panel names. Still missing?
Press <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd> (<kbd>Cmd</kbd> +
<kbd>Shift</kbd> + <kbd>P</kbd> on a Mac), type "lighthouse" and pick **Show
Lighthouse**. That shortcut finds any panel, wherever Google moved it this year.
---
**Lighthouse says it can't run on this page.**

Check your address bar. You're almost certainly on a `file://` address - the
copy on your own computer rather than the one on the web. Open the `github.io`
version instead.
---
**I scored 100 and there's nothing to fix.**

It happens, especially on a simple app. Scroll to the bottom of the report:
there's a list of things Lighthouse **can't** check automatically, and it's
worth reading. Then try a different app - a more complicated one will have
something.
---
**The score didn't move.**

Three possibilities, in order of likelihood. The upload hasn't landed yet - wait
a minute and hard-refresh. The old page is cached - hard-refresh again. Or the
fix was real but unscored, which happens often. Check the finding is actually
gone from the list; that matters more than the number.
---
**The AI's fix broke my app.**

This is why you keep the old file. Put the previous `index.html` back, upload
it, and you're where you started. Then ask again with a narrower request, and
say what went wrong last time - that usually gets a much more careful answer.
---
**It gave me back a completely different app.**

You asked too broadly, or it decided to be helpful. Start over from your working
file and say, explicitly: change nothing except this one thing. Being bossy with
the AI is a skill, and this is good practice.
:::
