# Palette's Journal

## 2024-05-22 - Accessibility in Dynamic Tables
**Learning:** Dynamic rows in admin tables need accessibility attributes injected via PHP (initial) and JS (appended).
**Action:** Ensure both PHP render and JS templates include `aria-label` and focus management.

## 2024-05-22 - Delete Button Conventions
**Learning:** Admin interface uses '×' char for delete buttons.
**Action:** Use `aria-label="Удалить поле"` for these icon-only buttons.
