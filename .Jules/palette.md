## 2026-01-21 - Admin Button Accessibility
**Learning:** WordPress admin interfaces often rely on visual cues (like icons) without corresponding accessible labels, and custom CSS can inadvertently strip standard focus indicators (`outline: none`), harming keyboard navigation.
**Action:** Always ensure icon-only buttons have `aria-label` and `title`, and replace `outline: none` with the standard WP focus style (`outline: 2px solid #2271b1; outline-offset: 2px`).
