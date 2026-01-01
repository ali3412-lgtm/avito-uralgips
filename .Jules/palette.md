## 2024-05-23 - Accessibility of Action Buttons
**Learning:** Icon-only buttons (like 'x' for delete) are common but often lack `aria-label`, making them inaccessible to screen readers. Also, suppressing focus outline (`outline: none`) without replacement is a critical accessibility failure.
**Action:** Always add `aria-label` to icon-only buttons and use standard WordPress focus styles (`outline: 2px solid #2271b1`) instead of removing them.
