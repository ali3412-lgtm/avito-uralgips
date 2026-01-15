## 2024-05-22 - Dynamic Row Accessibility
**Learning:** When UI rows are generated both by PHP (server-side) and JS (client-side), accessibility attributes like `aria-label` must be duplicated in both places to ensure consistency for all items.
**Action:** Always check for JS-based row generation when modifying table rows in PHP to ensure the JS templates are updated with matching attributes.
