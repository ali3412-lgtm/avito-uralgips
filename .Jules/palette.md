## 2025-02-14 - Admin Interface Accessibility Anti-patterns
**Learning:** Found critical accessibility anti-patterns (`outline: none` on focusable elements) explicitly coded in the CSS. This suggests a need to proactively check for suppressed focus indicators in legacy admin CSS.
**Action:** Always `grep` for `outline: none` when auditing admin interface CSS and replace with standard WordPress focus styles (`outline: 2px solid #2271b1`).
