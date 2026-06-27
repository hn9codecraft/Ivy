# IVY - WordPress Project Rules for Claude Code

## Project Overview
- WordPress site using the **hello-elementor** theme (Elementor + child theme setup).
- Caching: Breeze plugin (breeze-config folder present — be careful editing cache, always clear cache after CSS/PHP changes).
- Main work areas: `wp-content/themes/hello-elementor/`, `wp-content/plugins/`.

## File Structure (for reference — don't recreate, follow this)
```
wp-content/themes/hello-elementor/
├── assets/
│   ├── css/        ← all custom styles go here
│   ├── images/
│   └── js/
├── includes/
├── modules/
└── template-parts/
```

## CSS Rules (IMPORTANT)
- ALWAYS check `assets/css/` first before writing any new CSS. Search existing files for similar classes before adding new ones.
- NEVER duplicate a style that already exists — reuse existing classes/utility classes.
- New global styles (colors, spacing, fonts) go in one shared file (e.g. `assets/css/theme.css` or main theme stylesheet) using CSS variables — don't scatter repeated values across files.
- Follow Elementor's existing class naming patterns where the page is built with Elementor widgets; don't fight Elementor's own CSS with overly specific overrides — use theme's `assets/css` for custom rules only.
- After any CSS edit, tell me clearly: file changed, classes reused vs. newly created.

## Accessibility (a11y)
- All `<img>` tags need meaningful `alt` text (decorative images: `alt=""`).
- Semantic HTML (`<nav>`, `<main>`, `<header>`, `<footer>`) in template-parts, not generic `<div>` where avoidable.
- All form fields (Elementor forms too) need associated `<label>`.
- Keyboard-navigable + visible focus states on all buttons/links.
- WCAG AA color contrast (4.5:1 minimum) for text.

## SEO
- Unique `<title>` + `<meta name="description">` per page/template.
- One `<h1>` per page; proper heading order (no skipped levels).
- Open Graph tags (`og:title`, `og:description`, `og:image`) on key pages.
- Images: proper `width`/`height`, compressed, `loading="lazy"` for below-the-fold.

## WordPress / Caching Specific
- After any CSS/JS/PHP change in the theme, remind me to clear Breeze cache before testing.
- Don't edit files inside `cache/` directly — it's auto-generated.
- Keep theme overrides inside `hello-elementor` child theme files only — don't touch WordPress core or plugin core files directly.

## General Workflow
- Confirm with me before structural changes (new template-parts, new modules) — don't go ahead of schedule on assumptions.
- Show a short plan first for any multi-step task before implementing.
