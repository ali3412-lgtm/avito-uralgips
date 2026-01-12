## 2024-05-22 - [PHP/JS Interaction Pattern]
**Learning:** This plugin relies heavily on constructing HTML strings within JavaScript to handle dynamic row additions. This pattern duplicates HTML structure between PHP (server-side render) and JS (client-side render), making accessibility updates error-prone as they must be applied in both places.
**Action:** When updating UI elements in dynamic tables, always grep for the element class/ID to find both the PHP rendering loop and the JavaScript template string.
