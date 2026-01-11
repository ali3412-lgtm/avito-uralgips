## 2024-05-22 - Admin UI Inline Styles and JS
**Learning:** This WordPress plugin constructs admin UIs using PHP to echo large blocks of HTML and inline CSS, with JavaScript strings for dynamic elements. This makes accessibility fixes require changes across PHP, CSS, and JS simultaneously.
**Action:** When fixing admin UI issues, check for dynamic JS row generation and inline CSS blocks within the main PHP file, not just static HTML.

## 2024-05-22 - Mocking Admin Pages
**Learning:** Verification of admin interface changes in this environment required creating standalone mock HTML files (e.g., `mock-admin.html`) to mimic the PHP-generated structure, as there is no running WordPress instance to render the actual page.
**Action:** For future UI tasks, expect to create temporary HTML mocks that replicate the target PHP output to verify CSS/JS behavior with Playwright.
