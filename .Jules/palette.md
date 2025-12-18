## 2024-05-23 - [Adding ARIA labels to dynamic tables]
**Learning:** When tables are used for layout of form inputs, screen readers lose context. Adding `aria-label` to inputs inside `<td>` cells is critical for accessibility.
**Action:** Always verify that inputs inside tables have either associated `<label>` elements or explicit `aria-label` attributes describing their purpose, especially for dynamic rows.
