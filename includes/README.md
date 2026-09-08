# `/includes` — Class File Reference

All PHP classes that power the Trek Content Manager plugin live here.

---

| File | Class | Responsibility |
|---|---|---|
| [`class-trek-cpt.php`](class-trek-cpt.php) | `Trek_CPT` | CPT registration, 11 admin metaboxes, save handler, card helpers |
| [`class-trek-renderer.php`](class-trek-renderer.php) | `Trek_Renderer` | Canonical data normaliser, schema JSON-LD, social meta tags |
| [`class-trek-rest-api.php`](class-trek-rest-api.php) | `Trek_REST_API` | REST endpoints for destinations, enquiries, and reviews |
| [`class-trek-shortcodes.php`](class-trek-shortcodes.php) | `Trek_Shortcodes` | All listing shortcodes (slider, grid, map, finder, journal) |
| [`class-trek-importer.php`](class-trek-importer.php) | `Trek_Importer` | Admin JSON importer / exporter page |
| [`class-trek-roles.php`](class-trek-roles.php) | `Trek_Roles` | Custom capabilities + WP role → Trek role mapping |

---

## Quick Meta Key Reference

### Core Identity

| Meta Key | Admin Field | Type |
|---|---|---|
| `_trek_hero_title` | Hero Big Heading | text |
| `_trek_hero_subtitle` | Hero Subtitle | textarea |
| `_trek_hero_days` | Duration | text |
| `_trek_hero_bg_img` | Hero Background Image | url |
| `_trek_card_title` | Card Title | text |
| `_trek_card_image_url` | Card Image URL | url |
| `_trek_card_tag` | Card Tag Line | text |
| `_trek_card_excerpt` | Card Excerpt | text |
| `_trek_card_meta` | Card Meta Badge | text |
| `_trek_breadcrumb_region` | Breadcrumb Region | text |
| `_trek_featured_home` | Featured on Home | checkbox `"1"`/`"0"` |

### Classification

| Meta Key | Admin Field | Notes |
|---|---|---|
| `_trek_primary_category` | Primary Category | slug |
| `_trek_difficulty` | Trek Difficulty | text + syncs `trek_difficulty` taxonomy |
| `_trek_map_region` | Map Region | text |
| `_trek_map_district` | Map District | text |

### Pricing

| Meta Key | Admin Field | Notes |
|---|---|---|
| `_trek_price` | Starting Price | `"2,850"` |
| `_trek_currency` | Currency | `USD` / `EUR` / `GBP` etc. |

### Quick Facts (Individual + Compiled)

| Meta Key | Notes |
|---|---|
| `_trek_fact_high_point` | Max altitude |
| `_trek_fact_region` | Destination/region |
| `_trek_fact_trekking` | Daily walking hours |
| `_trek_fact_accom` | Accommodation type |
| `_trek_fact_style` | Travel style |
| `_trek_fact_season` | Best season |
| `_trek_fact_group_size` | Group size |
| `_trek_quick_facts` | **Array** — compiled from all above + difficulty/price/currency |

### Repeaters (Dual-Key)

| Primary Key | Alias Key | Format |
|---|---|---|
| `_trek_itinerary_list` | `_trek_itinerary` | `[{day, stage, title, pills, desc}]` |
| `_trek_inclusions_full` | `_trek_inclusions` | `['string', ...]` |
| `_trek_exclusions_full` | `_trek_exclusions` | `['string', ...]` |
| `_trek_gallery_list` | `_trek_gallery_images` | `[{url, label}]` |
| `_trek_essential_list` | `_trek_faqs` | `[{title, content}]` |
| `_trek_curated_reviews` | `_trek_reviews_list` | `[{name, location, rating, quote}]` |

### Other Repeaters

| Meta Key | Format |
|---|---|
| `_trek_extensions_list` | `[{title, suggested, desc}]` |
| `_trek_itin_side_points` | `['string', ...]` |
| `_trek_inclusions_list` | `['string', ...]` (overview bullets) |
| `_trek_highlights` | `['string', ...]` |

### Master JSON

| Meta Key | Notes |
|---|---|
| `_trek_structured_data` | JSON string — auto-compiled on every save via `Trek_Renderer::get_full_trek_data()`. Powers REST API and JS engine. |

### Contact

| Meta Key | Description |
|---|---|
| `_trek_whatsapp_number` | WhatsApp number (international) |
| `_trek_sales_email` | Sales email |

---

## Class Load Order

```
trek-content-manager.php
    └── class-trek-cpt.php         (first — registers CPT + taxonomies)
    └── class-trek-roles.php
    └── class-trek-rest-api.php
    └── class-trek-renderer.php    (required by cpt save handler + rest api)
    └── class-trek-shortcodes.php
    └── class-trek-importer.php    (requires class-trek-renderer.php)
```

---

See the root [README.md](../README.md) for full documentation.
