## 2024-05-23 - Accessibility of Dynamic Tables
**Learning:** Admin interfaces often use inline JS to generate rows. Accessibility fixes must be applied to *both* PHP (initial render) and JS (dynamic rows) templates.
**Action:** When auditing tables, grep for the row class in both PHP and JS sections of the file.

## 2024-05-23 - Focus Styles in WP Admin
**Learning:** Standard WP admin focus style is `outline: 2px solid #2271b1; outline-offset: 2px;`. Many plugins incorrectly suppress this with `outline: none`.
**Action:** Always check `:focus` states in CSS blocks and replace `outline: none` with the standard accessible pattern.
