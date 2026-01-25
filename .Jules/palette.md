## 2024-05-22 - Legacy Admin Accessibility Patterns
**Learning:** Legacy WordPress admin pages often mix PHP rendering and inline JS string concatenation for dynamic rows. Accessibility fixes (like `aria-label`) must be applied in *both* places to avoid inconsistent behavior. Also, many plugins hardcode `outline: none` on buttons, which must be replaced with standard WP focus styles (`outline: 2px solid #2271b1`).
**Action:** When auditing legacy admin UIs, grep for `outline: none` and check for duplicate HTML generation logic (PHP vs JS) to ensure full coverage of a11y fixes.
