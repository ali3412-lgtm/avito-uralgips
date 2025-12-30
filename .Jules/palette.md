## 2024-05-22 - Delete Button Accessibility
**Learning:** This plugin uses manual HTML/JS construction for admin tables. Accessibility (ARIA labels, focus states) must be manually injected into both PHP loops and JS templates. Standard WordPress focus styles (`#2271b1`) should be used over `outline: none`.
**Action:** When adding interactive elements in legacy WP admin pages, always check for matching JS templates and ensure focus indicators are preserved or enhanced.
