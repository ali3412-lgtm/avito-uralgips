
## 2026-01-31 - Dual-Rendering Anti-Pattern in Admin Tables
**Learning:** Admin tables are rendered twice: once via PHP for initial load, and again via JS string concatenation for dynamic rows. Any accessibility fix (like `aria-label`) must be applied in both places to avoid inconsistent state.
**Action:** Always grep for JS templates when modifying PHP loop markup in admin pages.
