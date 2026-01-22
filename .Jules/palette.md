## 2024-05-23 - [Accessibility in Dynamic Admin Tables]
**Learning:** This plugin mirrors PHP HTML generation in JavaScript string concatenation for dynamic rows. Accessibility fixes (like adding `aria-label`) must be duplicated in both the PHP render loop and the JS `append` logic to ensure consistency for initial vs. newly added elements.
**Action:** When auditing admin tables, always check for a corresponding JS handler that generates new rows and apply attributes there as well.

## 2024-05-23 - [Focus Management in Custom Admin CSS]
**Learning:** Custom CSS in `field-settings-page.php` explicitly removed focus indicators (`outline: none`) for delete buttons.
**Action:** Always check for `outline: none` in admin CSS blocks and replace with standard WordPress focus styles (`outline: 2px solid #2271b1; outline-offset: 2px`) to ensure keyboard accessibility.
