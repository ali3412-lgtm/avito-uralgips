## 2024-05-23 - Accessibility Patterns in Admin Tables
**Learning:** Admin tables often rely on visual context (column headers) for labels, which fails for screen readers. Icon-only buttons (like 'x' for delete) are common but inaccessible without `aria-label`.
**Action:** When working with dynamic admin tables, always ensure input fields have associated labels (or `aria-label` matching the column header) and icon-only buttons carry descriptive `aria-label`. Focus states must be preserved or enhanced, never removed.
