## 2024-05-23 - Accessibility Fixes for Admin
**Learning:** Icon-only buttons (like "×" for delete) are inaccessible to screen readers without an `aria-label`. Also, suppressing focus outline (`outline: none`) destroys keyboard navigation.
**Action:** Always add `aria-label` to icon-only buttons and ensure visible focus indicators (standard WP focus is `outline: 2px solid #2271b1`).
