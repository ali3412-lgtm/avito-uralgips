## 2024-05-22 - Admin UI Accessibility Patterns
**Learning:** The admin UI relies heavily on inline styles and manual HTML construction in PHP/JS, often missing basic accessibility attributes like `aria-label` for icon-only buttons. Focus states are explicitly removed with `outline: none` in some places.
**Action:** Always check for `outline: none` in CSS blocks within PHP files and ensure icon-only buttons have descriptive labels, even if hardcoded in Russian to match the codebase language.
