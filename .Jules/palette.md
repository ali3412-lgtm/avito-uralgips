# Palette's Journal

## 2026-01-24 - Accessibility Patterns in Admin Tables
**Learning:** Admin interfaces often use inline JS for dynamic rows, duplicating HTML logic. Accessibility fixes must be applied to both PHP (SSR) and JS (CSR) code paths to ensure consistent experience.
**Action:** When fixing dynamic forms, always search for JavaScript string concatenations that mirror the PHP HTML generation.
