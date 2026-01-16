# Palette's Journal

## 2024-05-23 - Accessibility Patterns in Admin Tables
**Learning:** Standard WordPress admin tables often use icon-only buttons for actions like deletion, but frequently miss `aria-label` attributes, making them inaccessible to screen readers. Additionally, custom CSS sometimes suppresses focus indicators (`outline: none`), harming keyboard navigation.
**Action:** Always check dynamic HTML generation in PHP and JavaScript for missing `aria-label` attributes on icon-only buttons and verify focus states are preserved or enhanced, not removed.
