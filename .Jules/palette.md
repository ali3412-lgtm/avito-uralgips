## 2024-05-23 - Accessibility in Dynamic Admin Tables
**Learning:** Dynamic tables in WordPress admin panels often miss accessibility attributes on action buttons (like delete), especially when rows are generated via JavaScript.
**Action:** Always ensure both PHP-rendered loops and JavaScript string templates include `aria-label` for icon-only buttons.
