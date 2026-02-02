## 2026-02-02 - Inline Admin CSS Antipatterns
**Learning:** This WP plugin embeds CSS directly in PHP files and explicitly suppresses focus styles (`outline: none`) on interactive elements without replacement, creating severe accessibility barriers for keyboard users.
**Action:** Always check inline CSS blocks in admin pages for `:focus` suppression and restore standard WordPress focus indicators (`box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8)` or similar Modern WP styles).
