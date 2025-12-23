## 2024-05-23 - Accessibility in Dynamic PHP/JS Forms
**Learning:** In legacy WordPress plugins where UI is built with raw PHP and JS string concatenation (like `includes/field-settings-page.php`), accessibility fixes must be applied in two places: the initial PHP render loop AND the JavaScript that handles dynamic row addition. Failing to update the JS string results in new rows being inaccessible despite the static page looking correct.
**Action:** When auditing legacy forms, always search for the JS `append` or string concatenation logic corresponding to the PHP loop to ensure feature parity for dynamic elements.

## 2024-05-23 - Focus Styles in Embedded CSS
**Learning:** Some plugins embed CSS directly in PHP files and explicitly remove outlines (`outline: none`) without replacement, likely for aesthetic reasons. This breaks keyboard navigation.
**Action:** Standardize on WordPress admin focus styles: `outline: 2px solid #2271b1; outline-offset: 2px;`. This is robust, high-contrast, and native to the platform.
