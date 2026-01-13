# Palette's Journal

## 2024-05-23 - Accessibility in Dynamic Admin Tables
**Learning:** In WordPress admin interfaces that use PHP to render initial rows and JS to append new rows, accessibility attributes (like `aria-label`) must be duplicated in both languages. A common pitfall is removing focus styles (`outline: none`) for "cleaner" UI, which breaks keyboard navigation.
**Action:** Always check both the PHP loop and the JS template string when modifying repeated elements. Restore focus styles using standard WordPress colors (`#2271b1`).
