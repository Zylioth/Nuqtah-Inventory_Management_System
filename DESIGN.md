---
name: Nuqtah
description: The ITQSHHB IT inventory and loan system, set as the institute's own due-date card.
colors:
  manila: "#E4D6B8"
  manila-deep: "#D8C7A3"
  card-stock: "#F2E8D4"
  ink: "#1C1A17"
  ink-soft: "#4A443A"
  brand-teal: "#00796B"
  deep-teal: "#004D40"
  stamp-violet: "#4B3A6B"
  alert-rust: "#A8332A"
  white: "#FFFFFF"
  bar-mist: "#EAF3F1"
  photo-black: "#0E0C0A"
typography:
  display:
    fontFamily: "Courier Prime, ui-monospace, Courier New, monospace"
    fontSize: "clamp(2.45rem, 5.1vw, 4.35rem)"
    fontWeight: 700
    lineHeight: 0.98
    letterSpacing: "-0.015em"
  headline:
    fontFamily: "Courier Prime, ui-monospace, Courier New, monospace"
    fontSize: "clamp(1.7rem, 2.6vw, 2.35rem)"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "-0.01em"
  body:
    fontFamily: "Courier Prime, ui-monospace, Courier New, monospace"
    fontSize: "1.03rem"
    fontWeight: 400
    lineHeight: 1.6
    letterSpacing: "0em"
  label:
    fontFamily: "Archivo Black, Arial Black, sans-serif"
    fontSize: "0.72rem"
    fontWeight: 400
    lineHeight: 1
    letterSpacing: "0.1em"
rounded:
  none: "0px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "14px"
  lg: "20px"
  xl: "28px"
  section: "64px"
components:
  button-stamp:
    backgroundColor: "{colors.brand-teal}"
    textColor: "{colors.card-stock}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    padding: "17px 26px"
  button-stamp-hover:
    backgroundColor: "{colors.deep-teal}"
    textColor: "{colors.card-stock}"
  button-paper:
    backgroundColor: "{colors.card-stock}"
    textColor: "{colors.deep-teal}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    padding: "12px 18px"
  stamp:
    backgroundColor: "{colors.card-stock}"
    textColor: "{colors.stamp-violet}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    padding: "7px 11px"
---

# Design System: Nuqtah

## Overview

**Creative North Star: "The Due-Date Desk"**

Nuqtah's interface is the record a lending desk already keeps: a manila card, typed entries, a rubber date stamp, a pasted photograph, a perforated edge. The system is not a product brochure wearing paper colours; paper is the ground, the rules are its manufacturing marks, and every state is a mark made on it. It exists because the institute already ran on slips and ledgers, and the digital system should feel like the same desk, minus the paper.

The density is low and legible: one idea per band, wide margins, and rules that separate rather than decorate. Colour is committed but restrained to three inks (teal for the institution, violet for dates and states, rust for the one overdue condition) over two paper stocks, with a single photograph per viewport.

**Key Characteristics:**
- Manila paper ground with a fine grain, card-stock panels for raised sections.
- One typewriter face for every entry; a heavy grotesk used only for stamped words.
- Square corners everywhere; the only curve is the punched hole on the card's top edge.
- State is a stamp, not a colour fill: dashed boxes, ink rings, rotated rubber marks.
- Depth comes from paper layers and one soft shadow, never from hard offsets or glass.

## Colors

Two paper stocks, one ink, three stamping inks: a stationery palette that keeps the institution's teal as the only saturated field.

### Primary
- **Brand Teal** (#00796B): the top bar, the primary action, table hover wash, figure labels. A field colour, not an accent sprinkled on a neutral page.
- **Deep Teal** (#004D40): rules, headings inside card stock, button edge and hover, focus rings. Carries small text on manila where the brand teal would fall below 4.5:1.

### Secondary
- **Stamp Violet** (#4B3A6B): date stamps and step numerals. The only ink allowed to look pressed.
- **Alert Rust** (#A8332A): overdue only, never used for decoration or for an on-time due date. The landing page currently ships no overdue state, so the token is reserved.

### Neutral
- **Manila** (#E4D6B8): the page ground.
- **Manila Deep** (#D8C7A3): scrollbar track and inset bands.
- **Card Stock** (#F2E8D4): panels, labels, the specimen slip, and stamped-word backgrounds.
- **Ink** (#1C1A17): headings and primary text.
- **Ink Soft** (#4A443A): body copy and secondary text on paper.
- **Rule** (`rgba(28, 26, 23, .22)`): hairlines between entries.
- **White** (#FFFFFF): bar text and focus rings on teal.
- **Bar Mist** (#EAF3F1): the teal bar's secondary microtype.
- **Photo Black** (#0E0C0A): letterbox ground behind the video window.

### Named Rules
**The Two Inks Rule.** A surface carries the teal family or the violet stamp, never both at full strength. Violet marks time and state; teal marks the institution and its actions.

**The Field Not Accent Rule.** Teal owns whole regions (the bar, the primary button, the due band) before it is used for a small mark. Scattering it as icons and underlines breaks the world.

## Typography

**Display Font:** Courier Prime (with ui-monospace, Courier New, monospace)
**Body Font:** Courier Prime
**Label Font:** Archivo Black (with Arial Black, sans-serif)

**Character:** A typewriter does the talking and a heavy grotesk does the stamping. The pairing reads as a filled-in form: long, even, mechanical text with dense, pressed, capital marks for the few words that behave like rubber stamps.

### Hierarchy
- **Display** (700, `clamp(2.45rem, 5.1vw, 4.35rem)`, 0.98): the landing page thesis only. Sentence case, tight tracking, never all-caps.
- **Headline** (700, `clamp(1.7rem, 2.6vw, 2.35rem)`, 1): section headings, sentence case.
- **Title** (700, 1.02rem, 1.2, uppercase, 0.06em): table entry names and slip field labels.
- **Body** (400, 1.03rem, 1.6): running copy, held to 54ch; ledger descriptions at .88rem and 46ch.
- **Small print** (400, .6-.98rem, 1.45-1.6): captions, slip fields, bar microtype. Fixed small steps, never fluid below 1rem.
- **Label** (Archivo Black, 0.72rem, 0.1em, uppercase): stamps, buttons, the form number. Column headers stay typewriter.

### Named Rules
**The Typed Entry Rule.** Every piece of reading text is typewriter. Archivo Black is a stamp: it appears in capitals, in short runs, and never as a sentence.

**The No Eyebrow Rule.** Nothing small and tracked sits above a heading. The heading carries the section; the form number lives in the card's corner, not over the title.

## Layout

A single 1240px measure centred on the viewport, with page gutters of 24px above 760px and 16px below. The landing page is a vertical sequence of paper bands: cover, perforated rule, date-due panel, specimen, register, footer. Sections breathe at 64px vertical padding on desktop, 40px on phones; headings sit closer to their content than to the band above them.

The cover is a two-column card at 1.18fr / .92fr with a 64px gap, collapsing to one column below 980px with the photograph after the copy. The date-due table collapses to a two-column grid per row below 760px: the stamped numeral stays left, the entry name and its description stack in the right column. The register runs two columns above 760px and one below.

Breakpoints: 980px (cover collapses, photo window goes 16:9), 760px (nav org line and signed-in name hide, table stacks, primary action goes full width), 480px (footer logo divider hides).

## Elevation & Depth

The system is flat by default, built from paper layers: manila ground, card-stock panels, and hairline rules. Depth appears in exactly two places: the specimen slip, which sits above the card with a soft drop shadow, and the photo window, which is a mounted print with a 10px paper mat and a 1px rule.

### Shadow Vocabulary
- **Slip lift** (`box-shadow: 0 22px 34px -24px rgba(28, 26, 23, .55)`): the one raised object on a page, used for the specimen record only.
- **Footer map** (Bootstrap's `shadow` utility): inherited from the shared footer; not part of this system.

### Named Rules
**The One Lift Rule.** At most one element per view lifts. Hard offset shadows, zero-blur blocks, and coloured halos are refused: the world is printed paper, not a sticker sheet.

## Shapes

Everything is square: buttons, table cells, panels, the photo window, the specimen slip. The only rounded forms are the punched half-holes along the cover's top edge and the 50%-radius flex circles inherited from the shared footer. Borders are 1px hairlines in Rule, 2px dashed rules between bands and inside stamp boxes, and a 3px deep-teal bottom edge on the primary button, which reads as the stamp's pressure rather than a shadow.

**The Square Stock Rule.** No border radius is added to any surface. A radius is a different material; this system only knows cut paper.

## Components

### Buttons
- **Shape:** square, no radius. Primary carries a 4px bottom edge in deep teal; secondary is a flat paper chip.
- **Primary (Stamp):** teal field, card-stock text, Archivo Black at 0.84rem, uppercase, 17px x 26px. Hover darkens to deep teal and holds the edge.
- **Paper (Nav Register):** card-stock field, deep-teal text, 0.72rem, 12px x 18px. Hover inverts to a transparent field with a card-stock border.
- **Link:** no colour change; a 2px underline offset below the text thickens on hover.
- **Focus:** 2px deep-teal outline at 3px offset; white outline on the teal bar.

### Stamps
- **Style:** violet or rust ink, 3px solid current-colour border, card-stock backing, Archivo Black capitals at 0.78rem, 7px x 11px, rotated -3.5 degrees.
- **Use:** exactly one per viewport region. The cover's live-stock stamp sits in the photograph's corner; the specimen's on-loan stamp sits on the product photo.

### Cards / Containers
- **Corner Style:** square.
- **Background:** card stock on manila; never card-on-card.
- **Shadow Strategy:** flat except the specimen slip (see Elevation).
- **Border:** 1px Rule hairline; 2px dashed for perforation and stamp boxes.
- **Internal Padding:** 18-20px for slips and panels, 0 for full-bleed bands.

### Due-Date Table
- **Style:** no vertical rules; each entry separated by a 1px Rule hairline, hover wash `rgba(0, 121, 107, .07)`.
- **Numeral:** Archivo Black in a 2px dashed violet box, 8px x 10px padding, width 90px.
- **Header row:** uppercase typewriter 700 at .66rem, 0.16em tracking; no stamp face.
- **Entry name:** uppercase typewriter, 1.02rem, 190px column.
- **Description:** Ink Soft, .9rem, capped near 58ch.

### Navigation
- **Style:** a teal bar with a 3px deep-teal bottom edge, white Nuqtah logo at 30px, an uppercase org line separated by a hairline, and account actions right-aligned.
- **States:** links are white with a translucent underline that goes solid on hover; the register button is the paper variant.
- **Mobile:** the org line and signed-in name hide below 760px; the bar keeps the logo and two actions.

## Do's and Don'ts

### Do:
- **Do** set ground-level pages on manila (#E4D6B8) and put panels on card stock (#F2E8D4).
- **Do** use Courier Prime for every reading text and Archivo Black only for capitals that behave like stamps.
- **Do** make state visible as a mark: a dashed box, an ink ring, a rotated rubber stamp.
- **Do** keep the cover's photograph mounted on a 10px paper mat with a `FIG.` caption.
- **Do** hold body copy to 54ch and stamp labels to short two-word runs.

### Don't:
- **Don't** add border radius, glass, blur decoration, or gradient text.
- **Don't** use hard offset shadows, coloured halos, or more than one softened lift per view.
- **Don't** put a small tracked label above a heading.
- **Don't** scatter teal as small icons; it owns fields or it stays out.
- **Don't** use rust for anything except an overdue or due-date condition.
