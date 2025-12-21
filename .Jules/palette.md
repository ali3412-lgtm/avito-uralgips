# Palette's Journal

## 2024-05-22 - Accessibility of Dynamic Tables
**Learning:** Admin interfaces with dynamic rows often miss accessibility attributes on appended elements (JS) even if the static PHP template handles them.
**Action:** Always check both PHP initial render and JS `append()` strings for matching ARIA attributes.

## 2024-05-22 - Admin Focus Styles
**Learning:** Default WordPress admin styles are often overridden with `outline: none` for "aesthetic" reasons, breaking keyboard navigation.
**Action:** Restore standard WP focus styles (`outline: 2px solid #2271b1; outline-offset: 2px;`) instead of custom focus indicators or no indicator.
