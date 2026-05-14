# DISARM Framework — Database Guide

This document explains every table in `disarm_combined.sql`, what data it holds,
and how the tables are linked to each other.

---

## Quick Summary

| Table | Rows | What it is |
|-------|-----:|------------|
| `technique` | 339 | Attacker methods (Red Framework) |
| `counter` | 338 | Defensive responses (Blue Framework) |
| `incident` | 133 | Real-world disinformation cases |
| `metatechnique` | 14 | High-level counter categories |
| `tactic` | 10 | Campaign stages |
| `phase` | 3 | Campaign lifecycle groupings |
| `framework` | 3 | Framework version metadata |
| `counter_tactic` | ~221 | Links: which counters address which tactics |
| `counter_technique` | 58 | Links: which counters address which techniques |
| `incident_technique` | 0 | Links: which techniques appeared in which incidents *(empty — source data was cleared)* |
| `detection` | 0 | Detection methods *(not yet populated)* |
| `externalgroup` | 0 | Known threat actor groups *(not yet populated)* |
| `tool` | 0 | Software/platforms *(not yet populated)* |
| `resource` | 0 | Resources needed for counters *(not yet populated)* |
| `task` | 0 | Step-by-step tactic tasks *(not yet populated)* |
| `responsetype` | 0 | Counter response categories *(not yet populated)* |
| `playbook` | 0 | Ordered action sequences *(not yet populated)* |
| `example` | 0 | Usage examples *(not yet populated)* |
| `detection_technique` | 0 | Link table *(empty)* |
| `detection_tactic` | 0 | Link table *(empty)* |
| `users` | 0 | App user accounts |

---

## The Big Picture

DISARM models disinformation from two angles:

```
RED FRAMEWORK (attacker view)          BLUE FRAMEWORK (defender view)
─────────────────────────────          ─────────────────────────────
How disinformation campaigns           How to detect and counter
are structured and executed            those campaigns

phase                                  metatechnique
  └─ tactic                              └─ counter ──────── counter_tactic ──► tactic
       └─ technique ◄─────────────────────────── counter_technique
                   ◄── incident_technique ── incident
```

The two frameworks are connected through the technique ↔ counter relationship:
every counter targets one or more techniques, and every technique can be
addressed by one or more counters.

---

## Relationship Map

```
phase (3)
  │ phase_id
  ▼
tactic (10)
  │ tactic_id                  ◄──── counter_tactic (221)
  ▼                                         │
technique (339)                        counter (338) ──► metatechnique (14)
  ▲                                         │
  │                                  counter_technique (58)
  │
  └──── incident_technique (0) ──── incident (133)
```

The numbers in brackets are row counts in the current database.

---

## Table-by-Table Reference

---

### `phase` — Campaign Lifecycle Stages

The top-level grouping. There are 3 phases representing the broad stages
of a disinformation campaign.

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Unique ID, e.g. `P01` |
| `name` | text | Phase name, e.g. *"Plan"* |
| `rank` | text | Display order |
| `summary` | text | Description |

**Links to:** `tactic.phase_id`

---

### `tactic` — Campaign Tactics (10 rows)

Specific objectives within a phase. This is the backbone of the Red Framework.
Each tactic represents a distinct stage an attacker moves through.

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Unique ID, e.g. `TA01`, `TA06` |
| `phase_id` | text | Which phase this tactic belongs to → `phase.disarm_id` |
| `name` | text | Tactic name, e.g. *"Develop Narratives"* |
| `rank` | text | Display order within its phase |
| `summary` | text | Description |

**Links from:** `phase.disarm_id`  
**Links to:** `technique.tactic_id`, `counter.tactic_id`, `counter_tactic.tactic_id`

---

### `technique` — Attacker Techniques (339 rows)

The core of the Red Framework. Each row is a specific method a disinformation
actor uses. Techniques have two levels:

- **Parent techniques** — e.g. `T2001` *"Create Sockpuppet Accounts"*
- **Sub-techniques** — e.g. `T2001.001` *"Anonymous accounts"* (spotted by the `.` in the ID)

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Unique ID, e.g. `T2001`, `T2001.001` |
| `tactic_id` | text | Which tactic this technique belongs to → `tactic.disarm_id` |
| `name` | text | Technique name |
| `summary` | text | Description |

**To find sub-techniques of T2001:**
```sql
SELECT * FROM technique WHERE disarm_id LIKE 'T2001.%';
```

**To find the parent of T2001.001:**
```sql
SELECT * FROM technique WHERE disarm_id = 'T2001';
```

---

### `counter` — Countermeasures (338 rows)

The core of the Blue Framework. Each row is a defensive action that counters
one or more attacker techniques. Every counter belongs to a tactic (the stage
it addresses) and a metatechnique (its broad category).

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Unique ID, e.g. `C00006`, `C00080` |
| `tactic_id` | text | Primary tactic this counter addresses → `tactic.disarm_id` |
| `metatechnique_id` | text | Broad counter category → `metatechnique.disarm_id` |
| `name` | text | Counter name, e.g. *"Create competing narrative"* |
| `summary` | text | Description |

**To find all counters for a tactic:**
```sql
SELECT * FROM counter WHERE tactic_id = 'TA06';
```

---

### `metatechnique` — Counter Categories (14 rows)

High-level groupings for counters. Every counter belongs to one metatechnique.
Think of these as the *type* of defensive action.

| ID | Name | What it means |
|----|------|---------------|
| M001 | Resilience | Build resistance in the population |
| M002 | Diversion | Create alternative channels/messages |
| M003 | Daylight | Make disinformation visible |
| M004 | Friction | Slow down disinformation spread |
| M005 | Removal | Remove disinformation from the system |
| M006 | Scoring | Use rating/verification systems |
| M007 | Metatechnique | *(general)* |
| M008 | Data Pollution | Confound disinformation monitoring |
| M009 | Dilution | Dilute disinformation with other content |
| M010 | Countermessaging | Create and distribute alternative messages |
| M011 | Verification | Fact-checking and verification |
| M012 | Cleaning | Remove unused resources from the system |
| M013 | Targeting | Target components of a disinformation campaign |
| M014 | Reduce Resources | Reduce resources available to attackers |

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Unique ID, e.g. `M004` |
| `name` | text | Category name |
| `summary` | text | Description |

---

### `counter_technique` — Counter ↔ Technique Links (58 rows)

A many-to-many join table. One counter can address multiple techniques,
and one technique can be addressed by multiple counters.

| Column | Type | Description |
|--------|------|-------------|
| `counter_id` | text | → `counter.disarm_id` |
| `technique_id` | text | → `technique.disarm_id` |
| `summary` | text | Context note (usually `N/A`) |

**To find all counters for technique T0003:**
```sql
SELECT c.disarm_id, c.name
FROM counter c
JOIN counter_technique ct ON ct.counter_id = c.disarm_id
WHERE ct.technique_id = 'T0003';
```

**To find all techniques a counter addresses:**
```sql
SELECT t.disarm_id, t.name
FROM technique t
JOIN counter_technique ct ON ct.technique_id = t.disarm_id
WHERE ct.counter_id = 'C00080';
```

> Note: Only 58 of the 338 counters have specific technique links.
> Many counters are broad enough that they weren't linked to individual techniques.

---

### `counter_tactic` — Counter ↔ Tactic Links (~221 rows)

A many-to-many join table. A counter primarily targets one tactic
(`main_tactic = 'Y'`) but may also apply to others (`main_tactic = 'N'`).

| Column | Type | Description |
|--------|------|-------------|
| `counter_id` | text | → `counter.disarm_id` |
| `tactic_id` | text | → `tactic.disarm_id` |
| `main_tactic` | text | `'Y'` = primary tactic, `'N'` = secondary |
| `summary` | text | Context note |

**To find all counters relevant to tactic TA06 (including secondary):**
```sql
SELECT c.disarm_id, c.name, ct.main_tactic
FROM counter c
JOIN counter_tactic ct ON ct.counter_id = c.disarm_id
WHERE ct.tactic_id = 'TA06'
ORDER BY ct.main_tactic DESC;
```

---

### `incident` — Real-World Cases (133 rows)

Documented disinformation campaigns and incidents used to validate the framework.

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Unique ID, e.g. `I00001` |
| `name` | text | Incident name |
| `summary` | text | Description |
| `year_started` | text | Year the campaign began |
| `attributions_seen` | text | Who was attributed (if known) |
| `found_in_country` | text | Country/region where it was observed |
| `objecttype` | text | Type classification |

**Links to:** `incident_technique` (currently empty — see below)

---

### `incident_technique` — Incident ↔ Technique Links (0 rows — empty)

This table would link each incident to the techniques that were observed in it.
The source data for this table (the Excel `incidenttechniques` sheet) was cleared
at some point, so this table is currently empty.

When populated, it enables queries like:
*"Which techniques were used in this incident?"* and
*"Which incidents demonstrate this technique?"*

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Row ID for this specific link |
| `incident_id` | text | → `incident.disarm_id` |
| `technique_id` | text | → `technique.disarm_id` |
| `name` | text | Name of the incident-technique link |
| `summary` | text | Context note |

---

### `framework` — Framework Versions (3 rows)

Metadata about the different DISARM framework versions (Red, Blue, etc.).

| Column | Type | Description |
|--------|------|-------------|
| `disarm_id` | text | Framework ID |
| `name` | text | Framework name |
| `summary` | text | Description |

---

### Empty Tables (Defined but Not Populated)

These tables exist in the database and the PHP site supports them,
but the underlying Excel source data was not available at export time.
They are ready to receive data.

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `detection` | Detection methods (alongside counters) | `disarm_id`, `tactic_id`, `name`, `summary` |
| `detection_technique` | Links detections to techniques | `detection_id`, `technique_id` |
| `detection_tactic` | Links detections to tactics | `detection_id`, `tactic_id`, `main_tactic` |
| `externalgroup` | Known threat actor groups | `disarm_id`, `name`, `region`, `country`, `primary_role` |
| `tool` | Software/platforms used in campaigns | `disarm_id`, `name`, `category`, `platform`, `url` |
| `resource` | Resources needed to execute a counter | `disarm_id`, `name`, `resource_type` |
| `responsetype` | Categories of response (Detect/Deny/Degrade…) | `disarm_id`, `name` |
| `task` | Step-by-step tasks per tactic | `disarm_id`, `tactic_id`, `name`, `summary` |
| `playbook` | Ordered sequences of counters | `disarm_id`, `object_id`, `name`, `summary` |
| `example` | Usage examples | `disarm_id`, `object_id`, `name`, `summary` |
| `users` | App login accounts | `username`, `password` |

---

## How the PHP Site Uses These Tables

| Site page | Tables queried |
|-----------|---------------|
| `index.php` | `phase`, `tactic`, `incident` — for stats and overview |
| `tactics.php` | `tactic`, `technique` (count), `counter` (count) |
| `tactic.php` | `tactic`, `technique`, `counter`, `detection`, `task` |
| `techniques.php` | `technique`, `tactic` |
| `technique.php` | `technique`, `counter_technique` → `counter`, `detection_technique` → `detection`, `incident_technique` → `incident` |
| `counters.php` | `counter`, `tactic`, `metatechnique` |
| `counter.php` | `counter`, `counter_technique` → `technique`, `counter_tactic` → `tactic` |
| `incidents.php` | `incident` |
| `incident.php` | `incident`, `incident_technique` → `technique`, `counter_technique` → `counter` |
| `detections.php` | `detection`, `tactic` |
| `search.php` | `technique`, `counter`, `tactic`, `incident`, `detection`, `tool`, `externalgroup` |
| `groups.php` | `externalgroup` |
| `tools.php` | `tool` |
| `resources.php` | `resource` |
| `playbooks.php` | `playbook` |

---

## Useful SQL Queries

**All techniques for a specific tactic:**
```sql
SELECT disarm_id, name FROM technique WHERE tactic_id = 'TA06' ORDER BY disarm_id;
```

**All counters for a specific tactic (via join table, including secondary):**
```sql
SELECT c.disarm_id, c.name, ct.main_tactic
FROM counter c JOIN counter_tactic ct ON ct.counter_id = c.disarm_id
WHERE ct.tactic_id = 'TA06' ORDER BY ct.main_tactic DESC, c.disarm_id;
```

**Full Red Framework — all tactics with their technique counts:**
```sql
SELECT ta.disarm_id, ta.name, COUNT(tc.disarm_id) AS techniques
FROM tactic ta LEFT JOIN technique tc ON tc.tactic_id = ta.disarm_id
GROUP BY ta.disarm_id ORDER BY ta.disarm_id;
```

**Full Blue Framework — all tactics with their counter counts:**
```sql
SELECT ta.disarm_id, ta.name, COUNT(c.disarm_id) AS counters
FROM tactic ta LEFT JOIN counter c ON c.tactic_id = ta.disarm_id
GROUP BY ta.disarm_id ORDER BY ta.disarm_id;
```

**Which counters address a given technique?**
```sql
SELECT c.disarm_id, c.name, c.metatechnique_id
FROM counter c JOIN counter_technique ct ON ct.counter_id = c.disarm_id
WHERE ct.technique_id = 'T0003';
```

**Which techniques does a given counter address?**
```sql
SELECT t.disarm_id, t.name, t.tactic_id
FROM technique t JOIN counter_technique ct ON ct.technique_id = t.disarm_id
WHERE ct.counter_id = 'C00080';
```

**Counters grouped by metatechnique:**
```sql
SELECT mt.name AS category, COUNT(c.disarm_id) AS counter_count
FROM metatechnique mt LEFT JOIN counter c ON c.metatechnique_id = mt.disarm_id
GROUP BY mt.disarm_id ORDER BY counter_count DESC;
```

**Incidents by year:**
```sql
SELECT year_started, COUNT(*) AS count FROM incident
WHERE year_started != '' GROUP BY year_started ORDER BY year_started DESC;
```
