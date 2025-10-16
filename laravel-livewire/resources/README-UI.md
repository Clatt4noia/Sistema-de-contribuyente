# UI Guidelines

This project shares a single light theme driven by the design tokens declared in `resources/css/app.css`. Use the tokens via CSS custom properties (e.g., `var(--color-primary-500)`) or the Tailwind utilities that map to them. Never hard-code hex values or dark mode variants.

## Design tokens
- **Color palette:** use `--color-*` variables for backgrounds, borders, text, and accents. Neutral shades drive surfaces, while semantic success/warning/danger colors support status messaging.
- **Radius scale:** `--radius-sm`, `--radius-md`, `--radius-lg`, `--radius-xl`, `--radius-2xl` define curved corners. Base interactive elements use `--radius-lg`.
- **Typography:** the default sans stack (Inter) is already applied on the `<body>` via Tailwind base styles.
- **Elevation:** prefer the provided shadows in `.btn`, `.surface-card`, and `.surface-muted`. Introduce new shadows only if they align with the existing intensity levels.

## Buttons (`.btn`)
- Base `.btn` delivers neutral styling with radius `--radius-lg`, subtle shadow, and the shared focus ring (`ring-primary-200` with offset 2).
- Variants: `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-ghost` tailor background, border, and hover states. Extend by following the same structure—declare background, border, hover, and focus colors using tokens.
- Sizing modifiers: `.btn-sm`, `.btn-lg`, `.btn-icon` adjust padding and layout. Compose them with a variant class when needed.
- Disabled states rely on opacity and cursor rules baked into `.btn`; avoid extra utility classes.

## Form controls
- `.form-control` and `[data-flux-control]` apply shared padding, border color, and focus handling. Keep custom inputs aligned by reusing these classes.
- For grouped fields, wrap controls with `.form-field` and `.form-group`. These utilities manage consistent vertical rhythm—avoid stacking extra margins inside.
- Validation: apply `.is-invalid` or `aria-invalid="true"` to leverage the danger palette feedback.

## Tables (`.table*` utilities)
- Use `<table class="table table-md">` with `<tr class="table-row">`, `<th class="table-header">`, and `<td class="table-cell">` for structure. Density modifiers (`.table-sm`, `.table-md`, `.table-lg`) adjust padding.
- State helpers: add `.table-row-hover` for hoverable rows, `.table-empty` for empty placeholders, and `.table-footer` to wrap pagination/toolbars.
- Keep semantic badges, chips, and Livewire components inside cells without extra layout wrappers. Rely on utility classes for alignment (`text-right`, `whitespace-nowrap`, etc.).

## Do & Don't
- **Do** reuse tokens and utilities; prefer composition over bespoke Tailwind classes.
- **Do** ensure focusable elements (buttons, links, actionable rows) keep the shared focus ring (`ring-primary-200` + offset).
- **Don't** introduce dark mode styles or custom palettes.
- **Don't** bypass `.table` utilities with ad-hoc spacing or divide classes.

## Extending the system
1. Introduce new variants by defining a class in `resources/css/app.css` within the `@layer components` block. Reference tokens for colors, spacing, and radii.
2. Document new patterns or components in this README to keep the system traceable.
3. Update `package.json` scripts or add utility scan rules when enforcing new guardrails.

## Commit conventions
- Use `feat(tables):` for functional table utility migrations.
- Use `chore(ui):` for polish or refactors that adjust shared UI tokens/styles.
- Use `docs(ui):` when updating this guide or related documentation.
- Run `npm run build` after each commit to validate the Vite/Tailwind pipeline, and `npm run ui:scan` before final sweeps to catch palette regressions.
