## 2024-05-23 - Accessible Delete Buttons
**Learning:** Icon-only buttons (like '×') often lack accessible names, and custom styles frequently suppress focus indicators (`outline: none`), making them invisible to keyboard users.
**Action:** Always add `aria-label` to icon buttons and ensure focus states are visible (e.g., `outline: 2px solid #2271b1` for WP).
