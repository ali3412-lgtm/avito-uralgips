## 2024-05-22 - Admin Interface Accessibility
**Learning:** Legacy WordPress admin interfaces often rely on inline CSS and HTML strings in PHP/JS, leading to missing accessibility attributes on dynamic elements. The `outline: none` pattern on focus states is prevalent and hurts keyboard navigation.
**Action:** Always check dynamically generated HTML strings (in JS) for missing `aria-label` attributes and ensure focus states are explicitly defined when default outlines are removed.
