# DISARM Framework — Database Structure

Full picture of how the Red (attack) and Blue (defence) frameworks connect in MySQL.

---

## Import order

```
1. disarm_red_framework.sql   — Red tables (phase, tactic, technique, task)
2. disarm_blue_framework.sql  — Blue tables + all junction tables
```

---

## Red Framework

```
phase
  └── tactic         (tactic.phase_id = phase.disarm_id)
        └── technique  (technique.tactic_id = tactic.disarm_id)
        └── task       (task.tactic_id = tactic.disarm_id)
```

### Tables

| Table | Key columns | Description |
|---|---|---|
| `phase` | `disarm_id` (P01–P04), `rank` | Top-level campaign phases: Plan → Prepare → Execute → Assess |
| `tactic` | `disarm_id` (TA01–TA18), `phase_id` → `phase.disarm_id` | 16 tactics, each belonging to a phase |
| `technique` | `disarm_id` (T0xxx or T0xxx.xxx), `tactic_id` → `tactic.disarm_id` | ~288 techniques + sub-techniques (dot notation = sub-technique) |
| `task` | `disarm_id` (TK0xxx), `tactic_id` → `tactic.disarm_id`, `framework_id = 'FW01'` | Operational tasks, scoped to the Red Framework |

### Sub-technique pattern

`T0074` is a parent technique; `T0074.001`, `T0074.002`, etc. are sub-techniques.  
Both live in the `technique` table with the same `tactic_id`. Join on `LEFT(disarm_id, 6)` to group them.

---

## Blue Framework

```
metatechnique  ─────────────────────────────────────────────────────┐
responsetype   ─────────────────────────────────────────────────────┤
                                                                     ↓
tactic (RED) ──→ counter ──→ counter_technique ──→ technique (RED)
                    ↑
actortype ──────────┘  (future: counter_actortype junction)

tactic (RED) ──→ detection

incident ──→ incident_technique ──→ technique (RED)
```

### Tables

| Table | Key columns | Description |
|---|---|---|
| `metatechnique` | `disarm_id` (M001–M014) | Strategy categories for counters (Resilience, Friction, Daylight…) |
| `responsetype` | `disarm_id` (D01–D07) | Response verb: Detect / Deny / Disrupt / Degrade / Deceive / Destroy / Deter |
| `actortype` | `disarm_id` (A001–A033), `sector_ids` | Who implements the counter (educator, platform, government…) |
| `counter` | `disarm_id` (C00xxx), `tactic_id`, `metatechnique_id`, `responsetype_id` | 140 countermeasures; each links to one tactic, one metatechnique, one response type |
| `detection` | `disarm_id` (F00xxx), `tactic_id`, `metatechnique_id`, `responsetype_id` | 94 detection methods; all have `responsetype_id = D01` (Detect) |
| `incident` | `disarm_id` (I00xxx), `objecttype`, `year_started`, `found_in_country` | 99 real-world disinformation incidents and campaigns |

### Junction tables

| Table | Columns | What it links |
|---|---|---|
| `counter_technique` | `counter_id`, `technique_id` | Which red techniques each counter specifically addresses (many-to-many, 57 mappings) |
| `incident_technique` | `incident_id`, `technique_id`, `description` | Which red techniques were observed in each incident (empty — source files contain no data) |

---

## Cross-framework links (RED ↔ BLUE)

All foreign-key style links use `disarm_id` strings (not auto-increment integers).  
This makes joins readable and portable.

```sql
-- All counters for a given red technique
SELECT c.*
FROM counter c
JOIN counter_technique ct ON ct.counter_id = c.disarm_id
WHERE ct.technique_id = 'T0010';

-- All techniques a tactic has, plus how many counters address them
SELECT t.disarm_id, t.name, COUNT(ct.counter_id) AS counter_count
FROM technique t
LEFT JOIN counter_technique ct ON ct.technique_id = t.disarm_id
WHERE t.tactic_id = 'TA15'
GROUP BY t.disarm_id, t.name;

-- All counters for a given tactic (tactic-level link, not technique-level)
SELECT c.*, m.name AS metatechnique, r.name AS response
FROM counter c
JOIN metatechnique m ON m.disarm_id = c.metatechnique_id
JOIN responsetype  r ON r.disarm_id = c.responsetype_id
WHERE c.tactic_id = 'TA06';

-- All detections for a given tactic
SELECT d.*
FROM detection d
WHERE d.tactic_id = 'TA08';

-- Full red chain: phase → tactic → technique
SELECT p.name AS phase, ta.name AS tactic, te.disarm_id, te.name
FROM phase p
JOIN tactic    ta ON ta.phase_id  = p.disarm_id
JOIN technique te ON te.tactic_id = ta.disarm_id
ORDER BY p.rank, ta.rank, te.disarm_id;
```

---

## Tactic ID reference

Used as the shared key between red and blue tables.

| disarm_id | Name | Phase |
|---|---|---|
| TA01 | Plan Strategy | P01 Plan |
| TA02 | Plan Objectives | P01 Plan |
| TA13 | Target Audience Analysis | P01 Plan |
| TA05 | Microtarget | P02 Prepare |
| TA06 | Develop Content | P02 Prepare |
| TA07 | Select Channels & Affordances | P02 Prepare |
| TA14 | Develop Narratives | P02 Prepare |
| TA15 | Establish Assets | P02 Prepare |
| TA16 | Establish Legitimacy | P02 Prepare |
| TA08 | Conduct Pump Priming | P03 Execute |
| TA09 | Deliver Content | P03 Execute |
| TA10 | Drive Offline Activity | P03 Execute |
| TA11 | Persist in Information Env. | P03 Execute |
| TA17 | Maximise Exposure | P03 Execute |
| TA18 | Drive Online Harms | P03 Execute |
| TA12 | Assess Effectiveness | P04 Assess |

---

## Record counts

| Table | Records |
|---|---|
| phase | 4 |
| tactic | 16 |
| technique | 288 |
| task | 42 |
| metatechnique | 14 |
| responsetype | 7 |
| actortype | 33 |
| counter | 140 |
| counter_technique | 57 |
| detection | 94 |
| incident | 99 |
| incident_technique | 0 (empty, ready for annotation) |

---

## Metatechnique reference (M-codes)

| disarm_id | Name | Description |
|---|---|---|
| M001 | Resilience | Build resistance to disinformation |
| M002 | Diversion | Create alternative channels or messages |
| M003 | Daylight | Make disinformation visible |
| M004 | Friction | Slow transmission or uptake |
| M005 | Removal | Remove disinformation objects |
| M006 | Scoring | Use a rating system |
| M007 | Metatechnique | Strategic/structural responses |
| M008 | Data Pollution | Confound disinformation monitoring |
| M009 | Dilution | Dilute with other content |
| M010 | Countermessaging | Distribute alternative messages |
| M011 | Verification | Verify objects, content, connections |
| M012 | Cleaning | Remove unneeded resources |
| M013 | Targeting | Target campaign components |
| M014 | Reduce Resources | Reduce resources available to creators |

---

## Response type reference (D-codes)

| disarm_id | Name | Description |
|---|---|---|
| D01 | Detect | Discover the existence of an intrusion |
| D02 | Deny | Prevent access indefinitely |
| D03 | Disrupt | Break flow for a fixed time |
| D04 | Degrade | Reduce effectiveness |
| D05 | Deceive | Cause false belief |
| D06 | Destroy | Permanently damage |
| D07 | Deter | Discourage |
