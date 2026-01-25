# Palette's Journal

## 2023-10-27 - [Accessible Delete Buttons]
**Learning:** Icon-only buttons (like '×') are often implemented without accessible names, making them invisible or confusing to screen reader users. Additionally, explicitly removing focus outlines (`outline: none`) for aesthetic reasons creates a severe barrier for keyboard users.
**Action:** Always verify icon-only buttons have `aria-label` and ensure focus indicators are visible and consistent with the platform (e.g., standard WordPress focus style).
