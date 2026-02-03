## 2026-01-23 - Dynamic UI Accessibility in Legacy Code
**Learning:** Legacy WP plugins often generate UI via PHP loops and JS string concatenation. Both must be updated to ensure consistent accessibility (ARIA labels, focus states).
**Action:** When fixing dynamic UIs, always search for both the server-side render loop and the client-side template string to avoid "partial" accessibility fixes.
