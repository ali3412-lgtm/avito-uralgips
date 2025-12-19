## 2024-05-23 - Accessibility of Dynamic Tables
**Learning:** Custom "X" delete buttons often lack accessible names and focus indicators, making them invisible to screen readers and keyboard users.
**Action:** Always verify custom interactive elements have `aria-label` and visible `:focus` states, especially when using `outline: none`.
