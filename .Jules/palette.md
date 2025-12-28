## 2024-05-23 - Accessibility of Dynamic Tables
**Learning:** Icon-only buttons (like "×" for delete) in dynamic tables often miss `aria-label` attributes, making them inaccessible to screen readers. Focus styles are also frequently suppressed (`outline: none`) without replacement.
**Action:** Always add `aria-label` to icon-only buttons (in both PHP and JS templates) and ensure a visible focus indicator (e.g., `outline: 2px solid #2271b1`) is present for keyboard users.
