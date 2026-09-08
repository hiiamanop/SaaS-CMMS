---
version: alpha
name: Link
description: A clean, high-trust payments system with spacious editorial hierarchy and a vivid green accent.
colors:
  primary: "#00C767"
  secondary: "#171717"
  tertiary: "#525252"
  neutral: "#E5E5E5"
  surface: "#FFFFFF"
  on-surface: "#171717"
  background: "#FFFFFF"
  accent-strong: "#011E0F"
  border: "#E5E5E5"
  muted-surface: "#F7F7F7"
  shadow: "#17171714"
  error: "#D92D20"
typography:
  headline-display:
    fontFamily: Matter
    fontSize: 88px
    fontWeight: 500
    lineHeight: 106px
    letterSpacing: -2.4px
  headline-lg:
    fontFamily: Matter
    fontSize: 61px
    fontWeight: 500
    lineHeight: 61.44px
    letterSpacing: -1.92px
  headline-md:
    fontFamily: Matter
    fontSize: 42px
    fontWeight: 500
    lineHeight: 50px
    letterSpacing: -0.56px
  headline-sm:
    fontFamily: Matter
    fontSize: 29px
    fontWeight: 500
    lineHeight: 35px
    letterSpacing: 0px
  body-lg:
    fontFamily: Matter
    fontSize: 20px
    fontWeight: 400
    lineHeight: 30px
    letterSpacing: -0.2px
  body-md:
    fontFamily: Matter
    fontSize: 16px
    fontWeight: 400
    lineHeight: 24px
    letterSpacing: -0.12px
  body-sm:
    fontFamily: Matter
    fontSize: 14px
    fontWeight: 400
    lineHeight: 20px
    letterSpacing: 0px
  label-lg:
    fontFamily: Matter
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
    letterSpacing: 0px
  label-md:
    fontFamily: Matter
    fontSize: 12px
    fontWeight: 500
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Matter
    fontSize: 11px
    fontWeight: 500
    lineHeight: 14px
    letterSpacing: 0.04em
rounded:
  none: 0px
  sm: 8px
  md: 10px
  lg: 18px
  xl: 28px
  full: 9999px
spacing:
  xs: 6px
  sm: 16px
  md: 24px
  lg: 32px
  xl: 60px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.accent-strong}"
    typography: "{typography.label-lg}"
    rounded: "{rounded.md}"
    padding: 6px 24px
    height: 49px
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-lg}"
    rounded: "{rounded.sm}"
    padding: 6px 24px
    height: 49px
  button-tertiary:
    backgroundColor: "transparent"
    textColor: "{colors.tertiary}"
    typography: "{typography.body-sm}"
    rounded: "{rounded.none}"
    padding: 0px
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.xl}"
    padding: 20px
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.md}"
    padding: 12px 16px
  chip:
    backgroundColor: "{colors.muted-surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.full}"
    padding: 6px 12px
---

# Link

## Overview
Link feels modern, trustworthy, and deliberately spacious, with a strong editorial voice balanced by a friendly green accent. The page is optimized for a broad consumer/prosumer audience that wants fast, secure checkout without feeling technical or cluttered. Overall tone is calm and premium rather than playful, with clear hierarchy and minimal visual noise.

## Colors
- **Primary (#00C767):** A bright, energetic green used for the brand mark, the main call-to-action, and key emphasis moments. It signals action and success without feeling overly loud.
- **Secondary (#171717):** A near-black ink used for headlines, primary text, and dark icon treatments. It provides the high contrast that gives the interface its crisp, editorial feel.
- **Tertiary (#525252):** A softer graphite used for secondary links, subtle copy, and lower-emphasis text.
- **Neutral (#E5E5E5):** A light border gray used for outlines, separators, and quiet container edges.
- **Surface (#FFFFFF):** The dominant background color, reinforcing the clean, airy composition.
- **Muted-surface (#F7F7F7):** A faint warm gray for recessed or secondary panels when a little separation is needed without adding shadow.
- **Accent-strong (#011E0F):** A deep green-black used for text on the primary green button to preserve contrast while staying on-brand.
- **Shadow (#17171714):** A subtle translucent ink tint that supports depth without heavy visual weight.
- **Error (#D92D20):** A reserved alert red for validation and destructive states; it is not prominent in the current composition but should remain restrained.

## Typography
Matter is the sole voice of the system and should be treated as a modern, neutral grotesk with a premium fintech character. Headlines are medium-weight and tightly tracked, giving the hero copy a confident, condensed rhythm without appearing aggressive. Body text stays lighter and highly readable, while labels and buttons use 500 weight to feel deliberate and operational.

The display hierarchy is strong: `headline-display` and `headline-lg` are used for major marketing statements, `headline-md` and `headline-sm` for supporting page titles, and `body-lg`/`body-md` for explanatory copy. Buttons and UI labels rely on `label-lg` and `label-md`, with slightly tighter uppercase-like spacing in small utility contexts when needed. Avoid decorative type treatments; the system depends on scale, weight, and spacing rather than stylization.

## Layout & Spacing
The layout uses a fixed, generous desktop canvas with large negative space around the hero content and a right-side product vignette. Content is arranged in a two-column marketing structure: text on the left, product visualization on the right, with strong asymmetry to create focus. The spacing rhythm is simple and consistent, built around 6px, 16px, 24px, 32px, and 60px increments.

Containers and cards prefer wide breathing room, with internal padding around 20px for large surfaces and 12px–16px for compact controls. Sections should feel open, with large vertical separation between the nav, hero, and supporting CTA. Density should remain low; when in doubt, add whitespace before adding lines or borders.

## Elevation & Depth
Depth is subtle and mostly achieved through tonal layering, soft borders, and restrained shadow rather than dramatic elevation. The main product card uses a light shadow and rounded container to float above the background, while smaller UI elements often rely on neutral outlines and contrast instead of shadow. Flat surfaces are acceptable and even preferred when the goal is clarity.

Borders are thin and pale, helping define interactive regions without boxing in the layout. The visual hierarchy should come from scale, color contrast, and spacing first, then shadow as a secondary cue. Avoid stacked shadows, heavy glows, or complex layered depth effects.

## Shapes
The shape language is soft and contemporary, with rounded corners that feel approachable but not bubbly. Buttons sit around 8px–10px radius, cards use a larger 28px radius, and fully rounded chips and badges help soften utility elements. Overall, forms are friendly and polished, with no sharp architectural edges.

## Components
Buttons should be simple, high-contrast, and size-consistent. `button-primary` is the main action: bright green background, dark text, medium radius, and compact horizontal padding with a 49px target height. `button-secondary` is a light, bordered button for alternate actions like sign-in or navigation. `button-tertiary` is text-only and should remain visually quiet, used for low-emphasis actions or inline links.

Cards use white surfaces, large radius, and subtle elevation. `card` should contain grouped content with generous padding and minimal border treatment; it is ideal for product previews, checkout modules, and floating panels. Inputs should mirror the same language: white fill, soft border, medium rounding, and comfortable padding so form fields feel aligned with the button system.

Chips and small badges should use pill rounding and muted surface fills to stay lightweight. Links should be understated, dark gray, and only underline when they need explicit affordance. Icons are simple black line or filled marks, often paired with strong whitespace rather than framed in containers.

## Do's and Don'ts
- Do keep the interface airy and allow large zones of whitespace around primary messaging.
- Do use the green primary color sparingly so it stays special and action-oriented.
- Do maintain strong typographic contrast between the hero headline and supporting copy.
- Do favor soft borders and subtle shadows over heavy elevation effects.
- Don't introduce bright secondary colors that compete with the signature green.
- Don't tighten spacing to make the page feel dense or dashboard-like.
- Don't use exaggerated corner radii on primary buttons or form controls.
- Don't layer multiple visual effects on the same component when simple contrast already communicates hierarchy.