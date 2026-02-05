## 2025-01-24 - Inline Admin Styles
**Learning:** The admin interface heavily relies on inline CSS and JS within PHP files (e.g., `includes/field-settings-page.php`), making global style updates difficult.
**Action:** When improving UX, check for inline styles and JS string concatenation in PHP files rather than looking for separate asset files.
