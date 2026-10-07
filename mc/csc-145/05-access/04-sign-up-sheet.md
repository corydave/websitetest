---
title: The sign-up sheet
chapter: access
time: About 30 minutes
id: vibe-lab-sign-up
lede: A form anybody can fill in, that hands you back a link with the answers already in it.
done: `sign-up/index.html` is live on your site, can be completed start to finish with the keyboard alone, and the link it produces reopens the form filled in.
demo: 
video: 
video-length: 
transcript: 
guide: good
guide-label: What good looks like
---

::: picture
Everything you have done this week has been checking and fixing. This one is
building. And URL parameters. And accessibility.

**Forms.** A form is where accessibility stops being theoretical, because a
form is a conversation: it asks you things, you answer, and it tells you when
you got something wrong. Every one of those steps is a potential for an
accessibility nightmare.

You are going to make a sign-up sheet - for a study group, a shift rota, a
potluck, a club. Name, email, which session, what you are bringing, any notes.
Fill it in, press the button, and get a *link with your answers in it* that
you can send to whoever is organizing. Yeah, I know it would be slick if we could
actually have it save the reply somewhere (like an online database or a 
spreadsheet or something). But that's coming.

No server, nothing to sign up for. The link does the work, the same trick as
the invite in chapter 4, except this time a form fills it in for you.
:::

::: ask
Start a new chat.

- A sign-up form with: a **name** box, an **email** box, a **date**, a
  group of **radio buttons** to pick one session from three, **checkboxes** for
  things you can bring, a **dropdown**, and a **notes** box
- Every field has a real `<label>` (that's part of a form, so your AI draft
  should do that automatically), visible on the page. Placeholder text
  is not a label (that's the text that is sometimes in a field when you get
  to a form - it might say like "*John Doe*" or something to help the user
  understand how to use the box).
- Mark which fields are required, in words as well as with a color or a star
- When someone submits with something missing, show the errors in text,
  announce them with `aria-live`, and move the focus to the first problem (this
  is called "validation", and it's common in forms - like making sure an email
  address is formatted properly).
- When it is valid, build a link containing the answers and show it with a
  *Copy* button
- If the page is opened with that link, fill the form back in from it
- It must be completable with the keyboard alone, start to finish

Then paste the specification at the end of your prompt, exactly as it is.

{{spec}}
:::

::: run
1. New folder inside `vibe-coding` - `sign-up`.
2. Save the code into it as `index.html`.
3. Open it and fill it in with the mouse. Check the link comes out.
4. Refresh, and *do the whole thing again with the keyboard only.* Tab, arrow
   keys for the radio buttons, Space for the checkboxes, Enter to submit. Hands
   off the mouse the entire time.
5. *Submit it empty on purpose* and see what happens.
6. Upload it to your site.

::: aside
Step 4 is the main intent of this lab! If you get stuck partway down with no 
way forward, you have found the bug, and the rest of the lab is about fixing it.
Arrow keys are the right way to move between radio buttons.
:::
:::

::: read
::: q
**Why is placeholder text not a label?**
---
Three reasons. It disappears the moment you start typing, so
anybody who loses their place has nothing to go back to. It is usually faint
gray, which fails contrast. And a screen reader may not announce it at all - so
the field is just "text box". A visible label sits there permanently, is
properly announced, and makes the tap target bigger, because clicking a label
focuses its field.
:::

::: q
**Your error message appears in red text. What is wrong with that on its own?**
---
Color is doing a job alone again. That's bad. Red
text with no words saying what is wrong, and no symbol, is invisible to
somebody who cannot see the red. The error needs to *say* what is wrong, in
words, next to the field it belongs to.
:::

::: q
**Why does the focus move to the first broken field on submit?**
---
Because otherwise the person is left at the bottom of the page with an error
message somewhere above them that they may not even know appeared. Moving
focus takes them straight to the problem. This is one of the few times you
should move somebody's focus for them, and it is a good example of why
the rule has exceptions.
:::

::: q
**Open DevTools, Elements, pick your radio button group, and look at the Accessibility tab. Is the group itself named?**
---
Often not. The individual buttons have labels but the group does not, so a
person hears "Morning, radio button, one of three" with no idea what question
they are answering. The fix is a `<fieldset>` with a `<legend>`, which is the
oldest and most forgotten piece of HTML in this entire chapter.
:::
:::

::: yours
Make it a sheet you would actually send.

Change the sessions, the checkbox items and the dropdown to something real -
your study group's times, the dishes for an actual potluck, the shifts for a
real rota. A generic form teaches the same lesson and then gets deleted.

Then the test that matters: give it to somebody and watch. Do not explain
it, do not hover, do not say "you just click there". Watch and see if they hesitate.

Then do it once more yourself, keyboard only, with your eyes closed as far as
you can manage.

::: aside
**You have now built the same thing twice in this chapter.** The word game
started out working and ended up working for more people. This one worked for
more people from the very first prompt, because you knew what to ask for before
you asked.
:::
:::

::: grow
First, save a copy of the folder as `sign-up-v1`.

**One - a summary before you send.**

::: prompt
Before producing the link, show a summary of what was filled in and an Edit button to go back and change it.
:::

**Two - remember me.**

::: prompt
Remember the name and email in this browser so the next person filling it in on the same computer does not have to retype them, with a clear button to forget them again.
:::

**Three - a QR code.** Chapter 1 comes back around.

::: prompt
Show a QR code of the finished link as well, so I can hold up my phone and send it that way.
:::
:::

::: keep
- Your form is `vibe-coding/sign-up/index.html`, live on your site, with
  `sign-up-v1` beside it.
- **Use it for something real this week.** See if you have a real reason
  to use it!
- **Run Lighthouse on it** and write the score down beside the others. It
  should be your best one yet, and if it is not, fix it.
- **Did anything surprise you?** Something that failed keyboard-only. A
  `<fieldset>` you had never heard of fixing something real. How much longer
  the prompt was than usual. Write it down.
- Add it to your hub!
:::

::: trouble
**I cannot get to the submit button with Tab.**

Something above it is swallowing focus, or the button is not a real button.
Check it in the Accessibility tab first, then tell the AI: *"I cannot reach the
submit button with the keyboard - every control should be reachable with Tab in
a sensible order."*
---
**The radio buttons will not move with Tab.**

Working correctly. Tab moves *to* a radio group and then the **arrow keys**
move between the options inside it. That is the standard behavior everywhere
on the web, and if you did not know it, that is worth knowing.
---
**My error message appears but a screen reader says nothing.**

The live region was probably created at the same moment the error appeared.
Tell the AI: *"the error area should already be on the page when it loads,
empty, and only its text should change."*
---
**The link is enormous.**

It carries every answer, so it grows with the form. If it is unwieldy, shorten
the parameter names: `?n=` instead of `?name=`. And remember - **anyone with
that link can read everything in it.** Never put anything private in a form
like this, which is a point chapter 9 comes back to properly.
---
**Opening my own link does not fill the form back in.**

Check the address actually has the answers in it after the `?`. If it does,
tell the AI: *"when the page opens with those values in the address, fill the
form in from them."* If it does not, the problem is at the other end, when the
link is built.
---
**Lighthouse flags my date field.**

Date inputs are fussy and the usual complaint is a missing label. Make sure it
has a real `<label for>` and not just placeholder text or a nearby heading.
:::
