## 2025-02-06 - Hardcoded UI Strings & Dynamic JS Templates
**Learning:** The admin interface (`includes/field-settings-page.php`) renders dynamic rows using inline JS strings that must mirror the PHP output. UI strings are hardcoded in Russian, matching the existing pattern.
**Action:** When modifying admin UI, always update both the PHP rendering loop and the JS string templates to ensure consistency for dynamic elements. Hardcode Russian strings for new UI elements.
