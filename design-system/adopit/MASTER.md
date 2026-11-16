# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** AdopIT!
**Generated:** 2026-11-16 14:52:00
**Category:** Veterinary Clinic
**Token files:** `tokens.json` (W3C Design Tokens Community Group JSON).
**CSS variables:** `tokens.css` (`:root` custom properties).
Token files are master-level only; `--page` overrides do not emit tokens.
**Design Dials:** Variance 7/10 (Balanced / Modern) | Motion 6/10 (Standard) | Density 6/10 (Standard)

---

## Global Rules

### Color Palette

| Role | Hex | CSS Variable |
|------|-----|--------------|
| Primary | `#0D9488` | `--color-primary` |
| On Primary | `#000000` | `--color-on-primary` |
| Secondary | `#14B8A6` | `--color-secondary` |
| On Secondary | `#0F172A` | `--color-on-secondary` |
| Accent/CTA | `#EA580C` | `--color-accent` |
| On Accent/CTA | `#000000` | `--color-on-accent` |
| Background | `#F0FDFA` | `--color-background` |
| Foreground | `#134E4A` | `--color-foreground` |
| Card | `#FFFFFF` | `--color-card` |
| Card Foreground | `#134E4A` | `--color-card-foreground` |
| Muted | `#E8F1F4` | `--color-muted` |
| Muted Foreground | `#475569` | `--color-muted-foreground` |
| Border | `#99F6E4` | `--color-border` |
| Destructive | `#DC2626` | `--color-destructive` |
| On Destructive | `#FFFFFF` | `--color-on-destructive` |
| Ring | `#0D9488` | `--color-ring` |

**Color Notes:** Caring teal + warm orange [Accent adjusted from #F97316]

### Typography

- **Heading Font:** Varela Round
- **Body Font:** Nunito Sans
- **Mood:** soft, rounded, friendly, approachable, warm, gentle
- **Google Fonts:** [Varela Round + Nunito Sans](https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700&family=Varela+Round&display=swap)

**CSS Import:**
```css
@import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700&family=Varela+Round&display=swap');
```

### Spacing Variables

*Density: 6/10 — Standard*

| Token | Value | Usage |
|-------|-------|-------|
| `--space-xs` | `4px` / `0.25rem` | Tight gaps |
| `--space-sm` | `8px` / `0.5rem` | Icon gaps, inline spacing |
| `--space-md` | `16px` / `1rem` | Standard padding |
| `--space-lg` | `24px` / `1.5rem` | Section padding |
| `--space-xl` | `32px` / `2rem` | Large gaps |
| `--space-2xl` | `48px` / `3rem` | Section margins |
| `--space-3xl` | `64px` / `4rem` | Hero padding |

### Shadow Depths

| Level | Value | Usage |
|-------|-------|-------|
| `--shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | Subtle lift |
| `--shadow-md` | `0 4px 6px rgba(0,0,0,0.1)` | Cards, buttons |
| `--shadow-lg` | `0 10px 15px rgba(0,0,0,0.1)` | Modals, dropdowns |
| `--shadow-xl` | `0 20px 25px rgba(0,0,0,0.15)` | Hero images, featured cards |

---

## Component Specs (AdopIT! Claymorphism Soft-3D + Bento)

### Claymorphic Surfaces & Cards
- **Radius:** `20px–24px` (`--radius-clay: 22px`, pills `999px`)
- **Border:** `3px solid` pastel-tinted border (`#99F6E4`, `#FED7AA`, `#DDD6FE`, `#FBCFE8`)
- **Double Shadow (Inner + Outer):**
  - `--clay-shadow: inset -3px -3px 8px rgba(19, 78, 74, 0.08), inset 3px 3px 8px rgba(255, 255, 255, 0.9), 6px 8px 18px rgba(13, 148, 136, 0.12);`
  - Hover on interactive bento tiles/cards: `transform: translateY(-3px);` with `cubic-bezier(0.34, 1.56, 0.64, 1)` (250ms), disabled under `prefers-reduced-motion: reduce`.

### Buttons (44px+ Touch Targets, WCAG 4.5:1 Verified)
- **Primary CTA (`+ Register Pet` / Submit):** `#EA580C` background with `#0F172A` or `#C2410C` background with `#FFFFFF` text (5.1:1+), `border: 3px solid #FFEDD5`, `border-radius: 16px`, chunky clay inner highlight + outer shadow, soft press `translateY(1px)` on `:active`.
- **Secondary Action:** `#FFFFFF` surface, `#134E4A` text, `border: 3px solid #99F6E4`, `border-radius: 16px`.
- **Icon / Table Row Buttons:** Minimum `40px–44px` hit target, explicit `aria-label`, visible `3px solid #0D9488` focus ring.

### Pastel Status Pills (No Color-Only Meaning: Pair Icon/Dot + Text)
- **Available:** Mint `#D1FAE5` bg / `#065F46` text / `#6EE7B7` border
- **Pending Adoption:** Peach-Amber `#FEF3C7` bg / `#92400E` text / `#FCD34D` border
- **Under Vet Care:** Lavender `#EDE9FE` bg / `#5B21B6` text / `#C4B5FD` border
- **Adopted:** Soft Coral `#FFE4E6` bg / `#9F1239` text / `#FDA4AF` border
- **Vaccination Up to Date / Partial / Pending:** Distinct pastel chips with crisp SVG check/clock/alert glyphs.

### Icons & Pet Avatars (Zero Emoji Rule)
- **UI Icons:** Consistent `20px` / `24px` stroke `2` inline Lucide-style SVGs (`aria-hidden="true"` when decorative).
- **Pet Species Avatars:** Custom geometric inline SVG illustrations (`Dog`, `Cat`, `Rabbit`, `Bird`, `Other`) inside `48×48px` (table) and `84×84px` (Spotlight Bento Card) pastel clay circles—guaranteed zero broken images offline.

---

## Layout & Navigation Architecture

### Top Pill Navigation Bar (Max 4 Destinations + 1 Primary CTA)
1. **Brand:** `AdopIT!` custom SVG paw-heart emblem
2. **Welcome & Pets** (`index.php`)
3. **Vet Care Logs** (`vet_care.php`)
4. **Adoption Inquiries** (`adoptions.php`)
5. **Primary CTA:** `+ Register Pet` (`create.php`)

### Welcome Page (`index.php`) — Asymmetric Bento Hero + Live DB Table
1. **Row 1 — Asymmetric Bento Grid (12-col CSS Grid / Bootstrap Grid):**
   - **Left (`col-lg-5`):** Welcome Hero Clay Card — Greeting, shelter mission tagline, quick links (`Log Vet Care`, `New Inquiry`), and live DB sync indicator.
   - **Center (`col-lg-4`):** **Spotlight "Pet of the Day" Clay Card** — Dynamically pulls the featured `Available` pet from MySQL with large SVG avatar, pet code pill, breed/age/vaccination chips, and direct `View Profile` / `Inquire` CTA.
   - **Right (`col-lg-3`):** **4 Staggered 2×2 Pastel Bento Stat Tiles** — Total Pets, Ready to Adopt, Vet Visits Logged, Pending Inquiries (live MySQL counts).
2. **Row 2 — Live Filter & Search Clay Bar:**
   - Instant JS search input (code, name, breed), Species dropdown, Adoption Status dropdown, and live visible record counter.
3. **Row 3 — Live Database Records Table:**
   - Clean, calm data table inside a clay container (no overshoot bounce on table rows per UX Pro Max rule), horizontal scroll wrapper only on narrow mobile (`table-responsive`), clear `View`, `Edit`, `Delete` actions.

---

## Motion

**Stagger List** (Standard) — Trigger: load or scroll | Duration: 300-450ms | Easing: `back.out(1.4)`

```js
gsap.from('.grid-item', { opacity: 0, scale: 0.92, y: 16, duration: 0.4, stagger: { each: 0.06, from: 'start', grid: 'auto' }, ease: 'back.out(1.4)' });
```

**Framework notes:** grid: 'auto' lets GSAP infer rows/columns from a CSS grid layout for a natural wave stagger; Use matchMedia('(prefers-reduced-motion: reduce)') to skip non-essential motion and render the final state immediately

- ✅ Combine with from: 'center' for a bento-grid layout to draw the eye inward first
- ❌ Don't use back.out on dense data tables; the overshoot reads as sloppy on informational UI
- ⚡ Group DOM writes; avoid interleaving layout reads (getBoundingClientRect) between staggered tweens

---

## Anti-Patterns (Do NOT Use)

- ❌ Generic design
- ❌ Hidden services

### Additional Forbidden Patterns

- ❌ **Emojis as icons** — Use SVG icons (Heroicons, Lucide, Simple Icons)
- ❌ **Missing cursor:pointer** — All clickable elements must have cursor:pointer
- ❌ **Layout-shifting hovers** — Avoid scale transforms that shift layout
- ❌ **Low contrast text** — Maintain 4.5:1 minimum contrast ratio
- ❌ **Instant state changes** — Always use transitions (150-300ms)
- ❌ **Invisible focus states** — Focus states must be visible for a11y

---

## Pre-Delivery Checklist

Before delivering any UI code, verify:

- [ ] No emojis used as icons (use SVG instead)
- [ ] All icons from consistent icon set (Heroicons/Lucide)
- [ ] `cursor-pointer` on all clickable elements
- [ ] Hover states with smooth transitions (150-300ms)
- [ ] Light mode: text contrast 4.5:1 minimum
- [ ] Focus states visible for keyboard navigation
- [ ] `prefers-reduced-motion` respected
- [ ] Responsive: 375px, 768px, 1024px, 1440px
- [ ] No content hidden behind fixed navbars
- [ ] No horizontal scroll on mobile
