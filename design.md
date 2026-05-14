# Design System — Global Reference

Ein minimalistisches, editoriales Design-System mit europäischem Charakter. Inspiriert von Schweizer Typografie, Editorial-Design und High-End-Consultancy-Websites. Das System funktioniert für jedes Thema, das Seriosität, Expertise und Zurückhaltung kommunizieren soll.

---

## 1. Philosophie

Das Design kommuniziert durch Weglassen. Keine Dekorationen, keine Shadows, keine Border-Radii, keine Gradients auf Buttons. Stattdessen: viel Whitespace, präzise Typografie, dünne Linien (1px) als einziges strukturelles Element.

**Kernprinzipien:**
- Alles passiert auf zwei Hintergründen: off-white und fast-schwarz
- Typografie und Spacing tragen das gesamte visuelle Gewicht
- Farbe kommt ausschließlich durch den Kontrast der beiden Hintergrundfarben
- Hover-States sind subtil (opacity, color-shift) — niemals Größenänderungen oder Shadows
- Kein Border-Radius irgendwo — alles ist hart und eckig

---

## 2. Farben

```css
:root {
    --bg:      #F5F1EB;   /* off-white, warm — Haupthintergrund */
    --dark:    #0D0D0D;   /* fast-schwarz — Sektionshintergrund & Buttons */
    --text:    #0D0D0D;   /* Haupttextfarbe */
    --muted:   #7A7469;   /* Labels, Captions, sekundärer Text */
    --white:   #F5F1EB;   /* Text auf dunklem Hintergrund */
    --border:  rgba(13,13,13,0.11);        /* Trennlinien auf hell */
    --bd-dark: rgba(245,241,235,0.11);     /* Trennlinien auf dunkel */
}
```

**Verwendungsregel:**
- `--bg` und `--dark` wechseln sich als Sektionshintergrund ab — das erzeugt Rhythmus ohne neue Farben
- `--muted` ausschließlich für Metainfo, Labels, Beschreibungstexte — nie für Headings
- Borders sind immer transparent/rgba — niemals solid grau — damit sie mit beiden Hintergründen harmonieren

---

## 3. Typografie

**Font:** `Inter` (Google Fonts), mit System-Fallbacks: `-apple-system, BlinkMacSystemFont, sans-serif`

```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
font-size: 16px;
line-height: 1.6;
font-weight: 400;
-webkit-font-smoothing: antialiased;
```

### Gewichts-Hierarchie

| Gewicht | Verwendung |
|---------|-----------|
| 300 (Light) | Fließtext, Beschreibungen, Hero-Text, lange Absätze |
| 400 (Regular) | UI-Elemente, Metadaten, Zahlen |
| 500 (Medium) | Service-Titel, Buttons, Nav-CTA |
| 600 (SemiBold) | Labels (Uppercase), Footer-Logo |

**Grundregel:** Je größer der Text, desto leichter das Gewicht. H1 immer 300. Nur Labels (klein, uppercase) sind 600.

### Typografische Skala

```css
/* Hero H1 */
font-size: clamp(38px, min(6.5vw, 9vh), 100px);
font-weight: 300;
letter-spacing: -0.035em;
line-height: 1.01;

/* Section H2 */
font-size: clamp(30px, 4vw, 56px);
font-weight: 300;
letter-spacing: -0.025em;
line-height: 1.12;

/* Große Zahlen / Stats */
font-size: clamp(40px, 5.5vw, 84px);
font-weight: 300;
letter-spacing: -0.04em;

/* Service-Titel / Sub-Headings */
font-size: clamp(16px, 1.6vw, 20px);
font-weight: 500;
letter-spacing: -0.01em;

/* Body-Text (beschreibend) */
font-size: clamp(14px, 1.3vw, 17px);
font-weight: 300;
line-height: 1.75;

/* Body-Text (kompakt) */
font-size: clamp(13px, 1.1vw, 15px);
font-weight: 300;
line-height: 1.75;

/* Labels / Eyebrows */
font-size: 10px;
font-weight: 600;
letter-spacing: 0.2em;
text-transform: uppercase;
color: var(--muted);

/* Nav-Links */
font-size: 11px;
font-weight: 400;
letter-spacing: 0.06em;

/* Kleinste UI-Elemente */
font-size: 10px–12px;
```

**Negative Letter-Spacing bei großen Texten:** Je größer der Text, desto mehr negatives Letter-Spacing (`-0.025em` bis `-0.04em`). Das verhindert das optisch auseinanderfallende Aussehen großer serifenloser Schrift.

**`clamp()` immer für responsive Typografie** — nie feste px-Werte für Headings.

---

## 4. Layout & Spacing

### Container

```css
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 clamp(20px, 5vw, 80px);  /* responsives Seitenpadding */
}
```

### Sektions-Padding

Alle Hauptsektionen verwenden dasselbe vertikale Rhythmus-Muster:

```css
padding: clamp(80px, 10vw, 140px) 0;
```

Das ergibt auf kleinen Screens ~80px, auf großen ~140px — konsistenter Rhythmus über alle Breakpoints.

### Spacing-Prinzip

Kein fixes Spacing-System (keine 4px/8px-Skala). Stattdessen: `clamp()` für alles, was sich mit dem Viewport ändern soll. Fixe `px`-Werte nur für kleine, unveränderliche Abstände (z.B. Gap innerhalb eines Buttons).

---

## 5. Grid-Systeme

Das Design verwendet kein globales Grid-Framework — jede Sektion hat ihr eigenes Grid, das zum Inhalt passt.

### Zweispaltig (häufigste Variante)

```css
display: grid;
grid-template-columns: 1fr 1fr;
gap: clamp(60px, 8vw, 120px);
```

### Asymmetrisch (Sticky-Sidebar-Effekt)

```css
display: grid;
grid-template-columns: 5fr 7fr;
gap: clamp(48px, 7vw, 110px);
```

Die linke Spalte enthält eine Sticky-Headline, die rechts scrollt Inhalt vorbei — erzeugt Tiefe ohne JS.

### Listen / Tabellen-Grid (z.B. Services)

```css
display: grid;
grid-template-columns: 72px 1fr 1fr;  /* Nummer / Titel / Beschreibung */
gap: 32px;
```

### Stats-Grid (3-spaltig mit Borders)

```css
display: grid;
grid-template-columns: repeat(3, 1fr);
border-top: 1px solid var(--bd-dark);
/* Jede Zelle hat border-right und border-bottom */
```

---

## 6. Trennlinien & Struktur

Das einzige dekorative Element sind 1px-Linien. Sie übernehmen alle strukturellen Aufgaben:

```css
/* Horizontale Trennung zwischen Sektionen */
border-bottom: 1px solid var(--border);

/* Interne Trennung (innerhalb einer Sektion) */
border-top: 1px solid var(--border);

/* Grid-Struktur (Stats) */
border-right: 1px solid var(--bd-dark);
border-bottom: 1px solid var(--bd-dark);
```

**Auf dunklem Hintergrund immer `--bd-dark` verwenden**, auf hellem immer `--border` — beide sind rgba und haben dieselbe optische Stärke.

---

## 7. Komponenten

### Navigation (Fixed)

```
[LOGO (uppercase, 10px, 600)]    [Link Link Link]    [CTA Button]
```

- Fixiert oben, `z-index: 100`
- Hintergrund: `rgba(245,241,235,0.88)` + `backdrop-filter: blur(14px)` — Glasmorphismus-Effekt, sehr dezent
- `border-bottom: 1px solid var(--border)`
- Höhe: 60px fix
- Logo: 10px, 600, `letter-spacing: 0.22em`, uppercase — identisch mit Footer-Logo
- Nav-Links: 11px, 400, muted, hover → text-color (0.2s transition)
- CTA: dunkler Button, 11px, 500, uppercase

### Label / Eyebrow

Ein wiederkehrendes Element, das Sektionen einleitet:

```css
.label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 56px;
}
.label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}
```

Der `::after`-Pseudoelement zieht eine Linie bis zum rechten Rand — verbindet Label visuell mit dem Raum.

### Buttons

```css
/* Primär / Dark */
.btn-dark {
    background: var(--dark);
    color: var(--bg);
    padding: 13px 26px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}
.btn-dark:hover { opacity: 0.72; }

/* Sekundär / Ghost */
.btn-ghost {
    background: transparent;
    color: var(--text);
    border: 1px solid var(--border);
}
.btn-ghost:hover { border-color: var(--text); }
```

Kein Border-Radius. Hover ist immer opacity-basiert (dark) oder border-intensivierung (ghost) — nie Größe, Shadow oder Farbwechsel.

### Hero-Sektion

- Vollbild (`min-height: 100svh`)
- Hintergrundbild mit `background-size: 145%` (leicht überzoomt) und präziser `background-position`
- Gradient-Overlay von oben (transparent) nach unten (solid `--bg`) — das Bild „verschwindet" nach unten in den Hintergrund
- Content am unteren Ende der Sektion (`justify-content: flex-end`, `padding-bottom: 30vh`)
- Zweispaltiger unterer Bereich: links Beschreibungstext, rechts Buttons
- Hero-Animationen: `fadeUp` mit gestaffelten Delays (0.1s / 0.28s / 0.5s)

```css
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: none; }
}
```

### Dark-Sektion (Stats / Why)

```css
background: var(--dark);
color: var(--white);
padding: clamp(80px, 10vw, 140px) 0;
```

Der Wechsel zwischen hell und dunkel passiert abrupt — kein Fade, kein Gradient zwischen Sektionen. Das erzeugt Energie und Klarheit.

### Listen-Rows (Services)

Jeder Eintrag ist eine horizontale Zeile mit `border-top`:

```css
.service {
    display: grid;
    grid-template-columns: 72px 1fr 1fr;
    padding: clamp(24px, 2.5vw, 36px) 0;
    border-top: 1px solid var(--border);
}
.service:last-child { border-bottom: 1px solid var(--border); }
```

Muster: Nummer (muted, tabular-nums) / Titel (500) / Beschreibung (300, muted)

### Formular

Sehr reduziertes Formular-Design:

```css
/* Inputs haben nur einen border-bottom, keinen border rundherum */
.form-group input,
.form-group textarea {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--border);
    padding: 12px 0;
    font-size: 15px;
    font-weight: 300;
}
/* Focus: border-bottom wird dunkler */
:focus { border-bottom-color: var(--text); }
```

Labels: 10px, 600, uppercase, muted — identisch mit den globalen Labels.

### Footer

```
[LOGO]    [Copyright · Location · Tagline]    [Link Link Link Link]
```

- `border-top: 1px solid var(--border)`, `padding: 36px 0`
- Logo identisch mit Nav-Logo (10px, 600, uppercase, letter-spacing: 0.22em)
- Copyright: 12px, 300, muted
- Links: 12px, 400, muted → text-color on hover

---

## 8. Scroll Reveal Animation

```css
.reveal {
    opacity: 0;
    transform: translateY(22px);
    transition: opacity 0.75s ease, transform 0.75s ease;
}
.reveal.in {
    opacity: 1;
    transform: none;
}
```

Die `.in`-Klasse wird per IntersectionObserver gesetzt. Alle Content-Blöcke bekommen `.reveal`. Kein Bounce, kein Overshoot — pures `ease`.

---

## 9. Bild-Behandlung

```css
/* Einzelbild als atmosphärischer Break */
.img-break {
    width: 100%;
    height: clamp(260px, 35vw, 500px);
    overflow: hidden;
}
.img-break img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.82);  /* leicht entsättigt */
}

/* 3-spaltiges Bild-Mosaik */
.img-mosaic {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    height: clamp(200px, 25vw, 340px);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}
```

Alle Bilder bekommen `filter: saturate(0.82)` — leicht entsättigt, damit sie nicht zu fotografisch-bunt wirken. Keine weiteren Filter (kein Schwarzweiß, kein starkes Desaturate).

---

## 10. Responsive Breakpoints

```css
/* Tablet-Landscape und kleiner */
@media (max-width: 960px) {
    .nav-links { display: none; }           /* keine mobile Nav / kein Hamburger */
    /* 2-spaltige Grids → 1-spaltig */
    /* Sticky-Headline → nicht mehr sticky */
}

/* Mobile */
@media (max-width: 600px) {
    /* Stats: 3-spaltig → 1-spaltig */
    /* Footer: horizontal → vertikal */
    /* Hero: Hintergrundbild ausblenden (zu klein) */
    .hero { background-image: none; }
}
```

**Kein Hamburger-Menü** — die Nav-Links werden auf kleinen Screens einfach ausgeblendet. Der CTA-Button bleibt sichtbar. Das ist eine bewusste Entscheidung: die Zielgruppe nutzt Desktop.

---

## 11. Technische Basis

```html
<!-- Reset: box-sizing border-box, margin/padding 0 -->
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

<!-- Smooth Scroll -->
html { scroll-behavior: smooth; }

<!-- Schrift-Rendering -->
body { -webkit-font-smoothing: antialiased; overflow-x: hidden; }
```

- **Kein CSS-Framework** (kein Bootstrap, kein Tailwind)
- **Kein JavaScript-Framework** — alles natives PHP + vanilla JS
- **Google Fonts:** `Inter` mit `display=swap`, preconnect für Performance
- **`svh` statt `vh`** im Hero (`min-height: 100svh`) — korrekte Höhe auf Mobile (kein iOS-Adressleisten-Bug)

---

## 12. Design-Tokens auf einen Blick

| Token | Wert | Verwendung |
|-------|------|-----------|
| `--bg` | `#F5F1EB` | Haupthintergrund (warm off-white) |
| `--dark` | `#0D0D0D` | Dark-Sektionen, Buttons |
| `--muted` | `#7A7469` | Sekundärtext, Labels |
| `--border` | `rgba(13,13,13,0.11)` | Alle Trennlinien auf hell |
| `--bd-dark` | `rgba(245,241,235,0.11)` | Alle Trennlinien auf dunkel |
| `--max` | `1200px` | Container max-width |
| `--pad` | `clamp(20px, 5vw, 80px)` | Container seitliches Padding |
| Section-Padding | `clamp(80px, 10vw, 140px)` | Vertikaler Rhythmus |
| Transition | `0.2s` | Hover-States |
| Reveal-Transition | `0.75s ease` | Scroll-Animationen |
| Hero fadeUp | `0.7s–0.8s ease` | Initiale Animationen |

---

## 13. Was dieses Design nicht tut

Diese Liste ist genauso wichtig wie das, was das Design tut:

- **Kein Border-Radius** — nicht einmal 2px
- **Keine Box-Shadows** — weder auf Karten noch auf Buttons
- **Keine Farb-Akzente** — kein Blau, kein Grün, kein Rot außer Fehler-Rot im Formular
- **Keine Icons** — Struktur entsteht durch Nummern und Typografie
- **Keine Karten** — Inhalte werden durch Trennlinien gegliedert, nicht durch Boxen
- **Keine Hover-Animationen mit Größenänderung** — alles ist opacity oder color
- **Keine Hintergrundfarben auf einzelnen Elementen** — nur auf ganzen Sektionen
- **Kein Hamburger-Menü**
- **Kein Sticky-Sidebar-JavaScript** — nur CSS `position: sticky`
