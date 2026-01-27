## 2026-01-27 - Dynamic Admin Table Accessibility
**Learning:** The plugin generates admin table rows via both PHP (initial load) and jQuery (dynamic add), but the `delete-field` buttons were missing `aria-label` and had `outline: none` in both contexts. This created a consistent accessibility gap where keyboard users lost context and focus visibility.
**Action:** When fixing accessibility in dynamic interfaces, always synchronize attributes between the server-side render loop and the client-side injection script to ensure a consistent experience.
