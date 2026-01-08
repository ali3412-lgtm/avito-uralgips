## 2024-05-23 - Focus Indicators
**Learning:** This admin interface relies on hardcoded CSS that suppressed outline styles (`outline: none`), making it inaccessible for keyboard users.
**Action:** When working with custom admin tables, always check for `outline: none` and restore it with WordPress standard focus styles (`outline: 2px solid #2271b1`).
