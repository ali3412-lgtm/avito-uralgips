## 2024-05-23 - Missing Label Associations in WP Admin Tables
**Learning:** WordPress admin tables often rely on visual layout (table cells) for label-input association, missing explicit `for`/`id` connections. This is a common accessibility anti-pattern in older plugins.
**Action:** When working on WP Admin interfaces, always check `form-table` structures and ensure `th` labels have `for` attributes matching `input` IDs.
