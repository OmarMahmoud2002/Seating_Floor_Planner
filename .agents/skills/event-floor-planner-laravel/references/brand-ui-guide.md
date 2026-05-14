# Brand UI Guide

Use this guide when designing or implementing any UI for the Arabic Event Floor Planner project.

## Identity summary

The UI should feel:

- Modern
- Clean
- Calm
- Professional
- Arabic-first
- Smooth and easy for non-technical staff
- Suitable for an internal company tool, not an overloaded SaaS dashboard

Use the logo identity colors from the supplied brand reference.

## Colors

### Primary colors

| Token | Hex | Usage |
|---|---:|---|
| `--brand-teal` | `#4D9B97` | Main identity color, positive states, active sidebar items |
| `--brand-blue` | `#4596CF` | Primary actions, links, focus state |
| `--brand-gradient` | `linear-gradient(135deg, #4D9B97 0%, #4596CF 100%)` | Main CTA buttons, hero cards, selected editor objects |

### Secondary colors

| Token | Hex | Usage |
|---|---:|---|
| `--brand-deep-blue` | `#31719D` | Headers, strong icons, analytics cards |
| `--brand-deep-teal` | `#317C77` | Confirmed/seated states |
| `--brand-gold` | `#E7C539` | VIP, highlights, warnings |
| `--brand-gray` | `#A19F9E` | Muted labels, borders, disabled states |

### Neutral colors

Use these neutrals unless the project already has a design system:

```css
:root {
  --brand-teal: #4D9B97;
  --brand-blue: #4596CF;
  --brand-deep-blue: #31719D;
  --brand-deep-teal: #317C77;
  --brand-gold: #E7C539;
  --brand-gray: #A19F9E;

  --brand-gradient: linear-gradient(135deg, #4D9B97 0%, #4596CF 100%);

  --color-bg: #F7FAFC;
  --color-surface: #FFFFFF;
  --color-surface-soft: #F3F8FA;
  --color-border: #E4E7EC;
  --color-border-strong: #CBD5E1;
  --color-text: #1F2937;
  --color-text-muted: #667085;
  --color-danger: #E5484D;
  --color-success: #317C77;
  --color-warning: #E7C539;
}
```

## Font

The original font in the screenshot cannot be confirmed from the image alone. For the application UI, standardize on:

```css
font-family: "Cairo", "Tajawal", "IBM Plex Sans Arabic", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

Use `Cairo` as the first choice because it is readable for Arabic dashboards, forms, tables, and dense editor sidebars.

Rules:

- Do not mix many fonts.
- Use one primary Arabic UI font everywhere.
- Use font weights 400, 500, 600, and 700 only.
- Body text: 14px to 16px.
- Small helper text: 12px to 13px.
- Page titles: 24px to 32px.
- Section titles: 18px to 22px.

## Layout direction

- All pages must use `dir="rtl"` and `lang="ar"`.
- Sidebar navigation should appear on the right in normal dashboard pages if the design uses an admin sidebar.
- For the floor planner editor, use this structure:
  - Top toolbar
  - Left library panel for adding elements
  - Center canvas
  - Right guest list panel

The editor may keep the library on the left and guests on the right because this matches the reference workflow, but all text, controls, and labels must remain RTL.

## Spacing

Use an 8px spacing system:

- 4px for tiny icon gaps
- 8px for compact spacing
- 12px for form item internal spacing
- 16px for card spacing
- 24px for section spacing
- 32px for page spacing

## Border radius

Recommended values:

- Inputs: 10px to 12px
- Buttons: 10px to 12px
- Cards: 16px
- Modals: 20px
- Badges/chips: 999px
- Editor seats: circular or rounded according to shape

## Shadows

Keep shadows soft and subtle:

```css
--shadow-card: 0 8px 24px rgba(15, 23, 42, 0.06);
--shadow-floating: 0 16px 40px rgba(15, 23, 42, 0.12);
```

Avoid heavy dark shadows.

## Buttons

### Primary button

Use for the main action on a screen.

Style:

- Background: brand gradient
- Text: white
- Height: 42px to 46px
- Border radius: 12px
- Font weight: 600
- Hover: slight lift or darken
- Focus: visible brand outline

Arabic examples:

- إنشاء حدث
- حفظ المخطط
- تصدير PDF
- استيراد الضيوف

### Secondary button

Use for alternative actions.

Style:

- White background
- Border: `#E4E7EC`
- Text: `#31719D` or `#1F2937`
- Hover background: `#F3F8FA`

Arabic examples:

- إلغاء
- عرض التفاصيل
- إضافة نوع ضيف

### Danger button

Use for deletion/destructive actions only.

Style:

- Red text/background depending on importance
- Confirmation modal before deleting important records

Arabic examples:

- حذف
- إزالة التجليس

## Forms

Every form should include:

- Clear Arabic label
- Optional helper text when needed
- Arabic validation errors
- Required field indicator when needed
- Consistent input height
- Clear focus state

Input styling:

```css
.form-control {
  min-height: 44px;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  background: #fff;
  color: var(--color-text);
}

.form-control:focus {
  border-color: var(--brand-blue);
  box-shadow: 0 0 0 4px rgba(69, 150, 207, 0.14);
}
```

## Cards

Use cards for dashboard stats, event summaries, floor plan summaries, guest cards, and import previews.

Card rules:

- White background
- 1px soft border
- 16px radius
- 16px to 24px padding
- Subtle shadow only when card is important or floating
- Clear title and supporting metadata

## Badges and guest types

Use color-coded chips:

| Guest type | Suggested color |
|---|---|
| عادي | Gray |
| VIP | Gold |
| عائلة | Teal |
| أصدقاء | Blue |
| موظف | Deep blue |
| إعلام | Purple/blue variant |
| راعي | Gold/teal |
| ذوي احتياجات خاصة | Deep teal |

Badges should include short Arabic labels and optional icons.

## Tables and lists

For dashboard and CRUD pages:

- Use readable row height.
- Keep actions grouped.
- Use Arabic empty states.
- Use search and filters where lists can grow.
- Avoid overly dense tables.

Arabic empty state examples:

- لا توجد أحداث بعد
- لم يتم إضافة ضيوف لهذا الحدث
- لا توجد مخططات محفوظة

## Modals

Use modals for:

- Add/edit table settings
- Add guest
- Confirm delete
- Import preview
- Export options

Modal rules:

- Arabic title
- Clear description
- Primary action on the visual left or according to the project's RTL button convention, but keep it consistent
- Cancel action always visible
- Do not make modals too wide unless showing import preview

## Toasts and status messages

Use short Arabic messages:

- تم حفظ المخطط بنجاح
- حدث خطأ أثناء الحفظ
- تم استيراد الضيوف
- تم تجليس الضيف
- هذا المقعد محجوز بالفعل

## Floor planner editor UI

### Top toolbar

Include:

- اسم المخطط
- Zoom controls
- Undo / Redo
- Save status
- عداد التجليس مثل: `4 / 14 تم تجليسهم`
- حفظ
- مخططاتي
- تصدير قائمة الضيوف
- تصدير PDF

### Left library panel

Sections:

- طاولات
- كراسي
- مسرح
- حوائط
- أبواب
- ممرات
- مناطق VIP
- إضاءة وصوت
- عناصر أخرى

Each draggable item should have:

- Icon
- Arabic name
- Short size/capacity description if useful

### Canvas

Canvas rules:

- Light grid background
- Optional background image opacity control
- Clear selected state using brand blue/teal
- Resize handles visible but not ugly
- Rotate handle where practical
- Snap-to-grid where practical
- Zoom and pan should feel smooth

### Right guest panel

Guest cards should show:

- Icon based on guest type
- Guest name
- Type badge
- Assigned table
- Assigned seat
- Remove assignment action if seated

## Accessibility

- Color should not be the only way to communicate state.
- Use readable contrast.
- Buttons must have text or accessible labels.
- Focus states must be visible.
- Icons should support the text, not replace it entirely.

## Things to avoid

- Random colors outside the brand palette.
- Heavy gradients everywhere.
- Small unreadable Arabic text.
- Mixed LTR/RTL controls without care.
- Overloaded tables with too many actions.
- Complex SaaS-style menus that the internal company does not need.
