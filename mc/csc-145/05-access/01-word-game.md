---
title: The word game nobody can play
chapter: access
time: About 40 minutes
id: vibe-lab-word-game
lede: Build a word game the way we've been building. Then try playing it without a mouse. Or your eyes closed.
done: `word-game/index.html` works with the mouse *and* with the keyboard alone, the tiles say more than their color, and submitting a guess announces what happened in words.
demo: 
video: 
video-length: 
transcript: 
guide: five
guide-label: The five ingredients
---

::: picture
You know the game. Five letters, six tries, green means right place, yellow
means right letter wrong place, gray means not in the word (if you don't know,
Google "Wordle").

Today you build one, and you build it the ordinary way - a grid, a keyboard
drawn on the screen, click the letters. It will work. It will look good. You
will play a round and most likely feel pleased.

Then you will look at it through an accessibility lens, and you will
find out that you built something a lot of people cannot use at all.

**That is the point of this lab, and you are not being set up to fail.** You are
about to see exactly what happens when a perfectly reasonable person builds a
perfectly reasonable app - because this is what everybody's first version looks
like, including the professionals'. The difference is that you are going to
check.
:::

::: ask
Start a new chat and describe the game in your own words. Make sure it says:

- A five-letter word guessing game, six tries (although if you wanna jazz
that up, go for it! Don't let me yuck your yum).
- A grid of tiles that fill in as you guess
- A keyboard drawn on the screen that you click to enter letters, with
  Backspace and Enter keys
- Color the tiles after each guess: *green* for the right letter in the right
  place, *yellow* for the right letter in the wrong place, *gray* for a
  letter that is not in the word
- A *list of at least twenty five-letter words at the top of the script* that
  I can edit (so you can add your own words if you want!)
- Picks one word at random each time you load the page

Then paste the specification at the end of your prompt, exactly as it is.

{{spec}}

::: aside
**Read the fourth line of that specification again!!!** *Fully accessible -
semantic HTML, ARIA labels, keyboard navigation.* You have sent that line with
every prompt since week one, and you are about to find out, for the first time,
how much work it does.

Do not change it, and do not add anything extra about accessibility. Ask the
way you always ask. The whole lab depends on you not helping.
:::
:::

::: run
1. Inside `vibe-coding`, make a new folder called `word-game`.
2. Save the code into it as `index.html`.
3. Open it and **play a whole round with your mouse.** Click letters, press the
   on-screen Enter, see the tiles color in. Play until you win or run out.

It works. It is a real game, you made it in about ten minutes, and in September
you did not know what a file extension was!

Enjoy that for a second, because the next step is going to be annoying.
:::

::: read
Now check it. Five questions, and you are going to run all five yourself.

::: q
**One. Put your hands in your lap and press Tab a few times. Can you play?**
---
Probably not, or barely. Either nothing on the page responds to Tab at all, or
every single on-screen letter is its own stop and reaching Enter takes
twenty-eight presses. Try actually typing a letter on your real keyboard -
most likely nothing happens, because the game is only listening for clicks.
:::

::: q
**Two. As you Tab, can you see where you are?**
---
Sometimes. If you can see a glowing outline moving around, the AI left the
focus ring alone - good. If you cannot see anything move, it was removed
somewhere in the styling because it looked untidy. That is the most common
accessibility mistake on the web and you may have just found it in your own app.
:::

::: q
**Three. Open DevTools, press Ctrl+Shift+P (Cmd+Shift+P on a Mac), type "rendering", and set Emulate vision deficiencies to deuteranopia. Play a round.**
---
The green tiles and the gray tiles go nearly the same color. *Right place* and
*not in the word at all* - the two most opposite messages in the game - become
hard to tell apart. The game is not harder; it is broken. This will have
happened no matter how carefully you asked, because "use accessible colors"
and "do not make color the only signal" are different instructions and you
only gave one of them.
:::

::: q
**Four. Submit a guess. What would somebody who cannot see the screen know about what just happened?**
---
Nothing. The tiles changed color, silently. There is no text anywhere saying
*two letters right, one in the wrong place.* For a player using a screen reader
the game simply does not respond.
:::

::: q
**Five. Open DevTools, Elements panel, and click one of your on-screen letter keys. Find the Accessibility tab in the sidebar - it may be hidden behind the ≫ button. What is it called?**
---
Hopefully the letter. If the key is a real `<button>` with a letter in it, it is
called that letter and all is well. If it is a styled `<div>`, it has no name
and no role, and it does not exist as a control at all. Check Backspace and
Enter especially - if they are symbols rather than words, they may be announced
as nothing, or as "left arrow".
:::

Write down which of the five your version passed. The AI will have got some
right on its own. That is worth noticing too - this is not a story about the AI
being useless, it is a story about *nobody having looked.*
:::

::: yours
Now fix it, one ingredient at a time. One prompt, test, then the next. Do
not paste all five at once - when something breaks you want to know which one
did it.

**One - the keyboard.**

::: prompt
Let me type my guess on my real keyboard as well as clicking the on-screen keys: letters type, Backspace deletes the last letter, Enter submits the guess. Keep everything else exactly the same.
:::

Test it with your hands on the keys and your mouse untouched.

**Two - show me where I am.**

::: prompt
When I move through the page with Tab, make it obvious where I am with a clear, high-contrast outline. Do not remove the browser's focus outline anywhere in the CSS.
:::

**Three - stop making color do all the work.**

::: prompt
Right now the only difference between a green tile and a gray tile is the color, and some players cannot tell those apart. Add a second signal to every tile - a small symbol and a short word - so that color is never the only thing carrying the meaning.
:::

Turn the deuteranopia filter back on and play again. It should be playable now.

**Four - say what happened.**

::: prompt
When I submit a guess, nothing announces the result to a screen reader. Add a status line using aria-live="polite" that says in plain words how the guess went, for example: "Guess 3. Two letters in the right place, one in the wrong place."
:::

**Five - give everything a name.**

::: prompt
Make sure every on-screen key is a real button element with a clear accessible name. Backspace and Enter should be named with words, not just symbols.
:::

Then go back and run all five checks again. It just takes five minutes of asking to
get an app that works for a lot more people than it did before.

::: aside
Notice what you did not have to do: learn any of this. You described a problem
in plain words and the AI fixed it, every time. The skill this chapter is
teaching is not how to write `aria-live`. It is **knowing to look**, and knowing
what to look for.
:::
:::

::: grow
First, save a copy of the folder as `word-game-v1`.

**One - works both ways at once.** Your on-screen keyboard is still there. Make
sure clicking it *and* typing both work, and that clicking a key shows up for
somebody who is listening rather than looking. Somebody who cannot use a
keyboard and somebody who cannot use a mouse can now both play the same game.

::: prompt
Make the on-screen keys and the physical keyboard work together: pressing a real key should highlight the matching on-screen key, and clicking an on-screen key should do exactly what typing it does.
:::

**Two - a high-contrast mode**, exactly the one real Wordle added.

::: prompt
Add a high contrast toggle that switches the tile colors to a colorblind-friendly set, and remember the setting while the page is open.
:::

**Three - your own words.** Replace the word list with twenty words from
something you care about - a sport, a show, your subject. Five letters each.
:::

::: keep
- Your game is `vibe-coding/word-game/index.html`, with `word-game-v1` beside
  it - the version from before you checked - make sure it's on GitHub!
- Keep both. Next lab you are going to audit apps you built weeks ago, and
  having the before-and-after of this one on hand makes the scores mean
  something.
- Which of the five did the AI get right without being asked? Write it down.
  You are going to be asked this question again at the end of the week, about
  everything else you have built.
- Play it. Then hand it to somebody and watch them play without saying a word.
:::

::: trouble
**I typed on my keyboard and letters went into the page twice.**

Both the click handler and the key handler are firing. Tell the AI exactly
that: *"typing a letter enters it twice - the on-screen key and the real key
are both adding a letter."*
---
**Enter reloads the page and wipes my guess.**

The AI used a form. Same bug as the Decision Maker in chapter 4, same fix:
*"pressing Enter reloads the page - it should submit my guess instead."*
---
**The focus outline still is not visible.**

Look in the CSS for `outline: none` or `outline: 0` and tell the AI to remove
every one of them: *"there are rules removing the focus outline - take them all
out and give focused elements a clear visible outline instead."*
---
**My aria-live status says the same thing twice, or nothing at all.**

Live regions are fussy: the element has to already be on the page before the
text changes. If it is created fresh each time, nothing is announced. Tell the
AI: *"the status element should already exist when the page loads, and only its
text should change."*
---
**I cannot find the Accessibility tab in DevTools.**

It is in the Elements panel, in the right-hand sidebar with Styles and Computed,
and it is usually hidden behind the small **≫** button at the end of that row.
Widening the DevTools window makes it appear. Or press **Ctrl+Shift+P** and type
"accessibility".
---
**The deuteranopia filter did nothing.**

You may be in the wrong panel. Press **Ctrl+Shift+P** (**Cmd+Shift+P**), type
"rendering", open that drawer, then scroll down to **Emulate vision
deficiencies** and pick from the menu. It applies to the whole page, live.
:::
