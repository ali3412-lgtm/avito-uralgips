## 2024-05-22 - Accessibility in Dynamic Admin Interfaces
**Learning:** WordPress admin interfaces often use inline JavaScript to generate dynamic rows. Accessibility attributes (like `aria-label`) must be added to BOTH the PHP render loop (for initial state) and the JS template string (for dynamic additions). Missing one creates an inconsistent experience.
**Action:** When auditing admin pages, always grep for the HTML string in JS to ensure dynamic elements match the static PHP markup.

## 2024-05-22 - Focus Styles in WP Admin
**Learning:** Custom buttons often suppress default focus styles (`outline: none`) without replacement, hurting keyboard navigability.
**Action:** Always replace `outline: none` with the standard WP admin focus pattern: `outline: 2px solid #2271b1; outline-offset: 2px;`.
