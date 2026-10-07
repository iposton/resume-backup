# Resume page

## Preview locally

PHP is installed on this Mac. Open Terminal and run:

```sh
cd ~/Desktop/resume-backup
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php in your browser. Leave Terminal open; press Control+C to stop the server. If port 8000 is already in use, use 8001 and update the URL. Use a hard refresh (Command+Shift+R in Chrome) after changing files.

Use PHP rather than opening index.php directly or using a static server: the page contains PHP and the contact form posts to mailer.php.

## Changes

The layout and interactions are implemented in css/styles.css and main.js. External Material Design Lite, jQuery UI, demo CSS, icon-font and Ladda dependencies have been removed from the page. The old Ladda/spin files remain as unused originals.

The page has responsive cards, compact navigation on Resume only, accessible tabs with arrow-key navigation, animated collapsible sections, animated scrolling within Resume and reduced-motion support. Without JavaScript all sections and accordion content remain readable.

Contact fields use native required/email validation. Status messages and mailer redirects work locally and on HTTPS; mailer.php checks mail()'s result before reporting success. Sending email requires a configured PHP mail transport; the built-in preview server does not configure that for you. The avatar still loads from GitHub.

## Browser checks

- Switch About, Resume and Contact; use the arrow keys while a tab is focused.
- Expand and collapse all three About accordions.
- On Resume, select Work Experience, Education, Skills and References.
- Resize to a narrow phone width and check for overlap or horizontal scrolling.
- On Contact, submit empty fields or an invalid email and check browser validation.
- Enable reduced motion in your OS and check that transitions are suppressed.

PHP syntax checks and the JavaScript syntax check pass. The local page, stylesheet and script return successfully. Automated browser interaction was unavailable in the repair session, so visual/animation checks must be completed in your browser.

The side menu appears only on Resume. Its About link scrolls to the top of the resume content; other menu links scroll to their resume sections. Top-level tabs switch without animated scrolling. Resume scrolling respects reduced-motion preferences.

## Editing page content

Edit **content.json** to update page copy without touching HTML. PHP loads it through `content.php` on every request, so save and refresh your browser—no build or server restart is needed.

- `site`: browser title, page heading, avatar, top tab labels, email link and social links.
- `about.blocks`: paragraphs and accordion headings/items, in display order.
- `resume`: heading, introduction, side-menu labels, jobs, education, skills, references and document download.
- `contact`: field labels, submit/sending labels and success/error messages.

To add a job, copy an object in `resume.jobs` and edit its fields:

```json
{
  "heading": [
    { "text": "Web Developer " },
    { "text": "Example Company", "url": "https://example.com" }
  ],
  "dates": "2025 - Present",
  "bullets": [
    [{ "text": "Built and maintained web applications." }]
  ]
}
```

Headings and bullets that contain inline links use arrays of text parts. Each part has `text`, plus an optional `url` or `style` (`em` or `strong`). Use plain text rather than HTML; PHP escapes text automatically. Keep spaces around link parts where words should be separated.

JSON requires double quotes, commas between items, and no trailing commas or comments. Escape quotes inside text as `\"`. Missing/unreadable files, malformed JSON and missing top-level sections produce a clear error before the page renders. Keep the existing field names and value types when editing.

The HTML structure, tab/section IDs and navigation destinations remain in `index.php`; presentation stays in `css/styles.css`, and interactions stay in `main.js`. `mailer.php` still owns the recipient and mail delivery settings. The content file is public website copy, so do not put credentials or private notes in it.

Validation completed: PHP/JavaScript syntax, JSON-driven rendering, preserved resume/accordion targets, inline links, escaped text and malformed-JSON handling. Visual browser verification remains manual.
