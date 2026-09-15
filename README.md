# Trek Content Manager — Complete Technical Documentation

> **Plugin Version:** 1.0.3 | **Author:** Yeti Adventure Architecture Team  
> **Requires WordPress:** 5.8+ | **Requires PHP:** 7.4+ | **Tested up to PHP:** 8.3  
> **Text Domain:** `trek-content-manager`

---

## Table of Contents

1. [Overview & Features](#overview--features)
2. [Plugin Architecture](#plugin-architecture)
3. [File Structure](#file-structure)
4. [Complete Meta Field Reference (CPT)](#complete-meta-field-reference-cpt)
   - [Core & Taxonomies](#1-core--taxonomies)
   - [Hero Section & Card Settings](#2-hero-section--card-settings)
   - [Trip Facts Grid](#3-trip-facts-grid)
   - [Overview Narrative & Route Visual Map](#4-overview-narrative--route-visual-map)
   - [Day-by-Day Itinerary Builder](#5-day-by-day-itinerary-builder)
   - [Inclusions & Exclusions](#6-inclusions--exclusions)
   - [Visual Photo Gallery](#7-visual-photo-gallery)
   - [Essential Information & FAQs](#8-essential-information--faqs)
   - [Traveller Reviews & Moderation](#9-traveller-reviews--moderation)
   - [Fixed Departures & Contact Settings](#10-fixed-departures--contact-settings)
   - [Canonical Structured Data](#11-canonical-structured-data)
5. [Dual-Key Compatibility Map](#dual-key-compatibility-map)
6. [Shortcodes Reference](#shortcodes-reference)
   - [Granular Field Shortcode (`[trek_field]`)](#granular-field-shortcode-trek_field)
   - [Modular Section Shortcodes](#modular-section-shortcodes)
   - [Directory & Filterable Listing Shortcodes](#directory--filterable-listing-shortcodes)
   - [Interactive Map & Journey Finder](#interactive-map--journey-finder)
7. [REST API Endpoints](#rest-api-endpoints)
8. [JSON Format & Bidirectional Sync](#json-format--bidirectional-sync)
   - [Quick JSON Auto-Fill Tool](#quick-json-auto-fill-tool)
   - [1-Click JSON Export](#1-click-json-export)
   - [Canonical Schema](#canonical-schema)
9. [Developer FAQ & Best Practices](#developer-faq--best-practices)

---

## Overview & Features

**Trek Content Manager** is a full-stack, enterprise-grade WordPress plugin designed for high-altitude trekking agencies, tour operators, and adventure expedition brands.

### Key Capabilities:
- **Rich Dynamic Custom Post Type (`trek_destination`)**: 11 clean metaboxes providing WYSIWYG editors, image uploaders, multi-image gallery pickers, and dynamic repeaters.
- **Each Destination Individually Editable**: Full granular control per destination without touching code or database rows directly.
- **Bidirectional JSON Synchronization**:
  - Drag-and-drop or paste any destination JSON file directly into the CPT edit screen to instantly auto-fill all form fields, repeaters, section headings, and media.
  - On saving, the plugin automatically normalizes and serializes the complete destination into a clean, canonical JSON payload (`_trek_structured_data`).
  - 1-Click "📥 Export JSON" action available both in the post list table and directly inside the post editor.
- **Granular Shortcode Suite**: Embed full trip detail pages, individual modular sections (Hero, Itinerary, Inclusions, Reviews, Map), or specific single fields (`[trek_field key="price"]`) anywhere in Elementor, Gutenberg, or custom theme templates.
- **Automated Dual-Key Storage**: Full backwards and forwards compatibility between legacy imports and modern structured arrays.
- **Zero External Role Dependencies**: Clean, standard WordPress capability checks (`edit_posts`, `manage_options`).

---

## Plugin Architecture

```
trek-content-manager.php                  ← Main plugin entry point & asset loader
├── includes/
│   ├── class-trek-cpt.php                ← CPT registration, 11 metaboxes, auto-fill JS, export actions
│   ├── class-trek-renderer.php           ← Normalization engine → get_full_trek_data(), JSON-LD schema
│   ├── class-trek-rest-api.php           ← REST API endpoints (/destinations, /enquiries, /reviews)
│   ├── class-trek-shortcodes.php         ← Comprehensive field, section, and listing shortcodes
│   └── class-trek-importer.php           ← Bulk JSON importer & documentation guide
├── assets/
│   ├── canonical-trek-template.json     ← Master reference canonical JSON destination template
│   ├── css/trek-detail.css               ← Single destination styling
│   ├── js/trek-engine.js                 ← Interactive frontend engine
│   └── js/trek-validation.js             ← Form validation helpers
└── templates/
    ├── single-trek_destination.php      ← Full destination detail page template
    ├── single-post.php                   ← Travel journal single post template
    └── nepal-map-template.php            ← Interactive Nepal SVG tourism map
```

---

## Complete Meta Field Reference (CPT)

### 1. Core & Taxonomies

| Field Name | Post Meta / Taxonomy | Type | Description |
| :--- | :--- | :--- | :--- |
| **Title** | `post_title` | `string` | The main destination title. |
| **Slug** | `post_name` | `string` | URL rewrite slug (e.g. `everest-base-camp-experience`). |
| **Trip Categories** | `trek_category` (Tax) | `term_id[]` | Hierarchical category taxonomy. |
| **Primary Category** | `_trek_primary_category` | `string` | Primary category used for badges and kicker text. |
| **Regions** | `trek_region` (Tax) | `term_id[]` | Geographic region taxonomy (e.g. `Khumbu`, `Annapurna`). |
| **Difficulty** | `trek_difficulty` (Tax) + `_trek_difficulty` | `string` | Grade (e.g. `Easy`, `Moderate`, `Strenuous`). |
| **Map Region** | `_trek_map_region` | `string` | Filter identifier for interactive maps. |
| **Map District** | `_trek_map_district` | `string` | Administrative district (e.g. `Solukhumbu`, `Kaski`). |
| **Home Featured** | `_trek_featured_home` | `'1'`/`'0'` | Whether this trip appears on the homepage slider. |

---

### 2. Hero Section & Card Settings

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_script_title` | `_trek_script_title` | `string` | Script calligraphy overlay (e.g. *The Ultimate Trek*). |
| `trek_hero_title` | `_trek_hero_title` | `string` | Primary H1 display title. |
| `trek_hero_kicker` | `_trek_hero_kicker` | `string` | Small label above title (e.g. `Khumbu · Nepal`). |
| `trek_hero_days` | `_trek_hero_days` | `string` | Duration display string (e.g. `15 Days`). |
| `trek_hero_badge_sub` | `_trek_hero_badge_sub` | `string` | Duration badge subtitle (e.g. `Days in Nepal`). |
| `trek_breadcrumb_region` | `_trek_breadcrumb_region`| `string` | Breadcrumb country or region name. |
| `trek_hero_subtitle` | `_trek_hero_subtitle` | `string` | Hero subtitle / descriptive narrative paragraph. |
| `trek_hero_bg_img` | `_trek_hero_bg_img` | `url` | Hero background banner image URL. |
| `hero_slider_image[]` | `_trek_hero_slider_images`| `url[]` | Dedicated hero slider multi-image gallery. |
| `trek_card_title` | `_trek_card_title` | `string` | Card title used in archive grids and sliders. |
| `trek_card_meta` | `_trek_card_meta` | `string` | Card duration pill (e.g. `15 Days`). |
| `trek_card_tag` | `_trek_card_tag` | `string` | Card category subtitle (e.g. `Nepal · Signature`). |
| `trek_card_kicker` | `_trek_card_kicker` | `string` | Card top category kicker. |
| `trek_card_excerpt` | `_trek_card_excerpt` | `string` | Short teaser excerpt under 30 words. |
| `trek_card_image_url` | `_trek_card_image_url` | `url` | Card preview image. |
| `trek_card_order` | `_trek_card_order` | `int` | Custom numeric sequence order. |

---

### 3. Trip Facts Grid

| Form Input Name | Post Meta Key | Quick Facts Key | Description |
| :--- | :--- | :--- | :--- |
| `trek_fact_high_point` | `_trek_fact_high_point` | `high_point` | Peak altitude (e.g. `5,550m · Kala Patthar`). |
| `trek_fact_low_point` | `_trek_fact_low_point` | `low_point` | Starting altitude (e.g. `1,400m · Kathmandu`). |
| `trek_trip_status` | `_trek_trip_status` | `status` | Booking status: `open`, `waitlist`, `closed`. |
| `trek_fact_region` | `_trek_fact_region` | `region` | Primary region name. |
| `trek_fact_season` | `_trek_fact_season` | `season` | Ideal travel months. |
| `trek_fact_group_size` | `_trek_fact_group_size` | `group_size` | Traveller capacity (e.g. `2–12 Travellers`). |
| `trek_fact_trekking` | `_trek_fact_trekking` | `trekking` | Daily hiking pace (e.g. `5–6 hours daily`). |
| `trek_fact_accom` | `_trek_fact_accom` | `accom` | Accommodation standard. |
| `trek_fact_style` | `_trek_fact_style` | `style` | Guiding style (e.g. `Privately Guided`). |
| `trek_fact_distance` | `_trek_fact_distance` | `distance` | Total route mileage. |
| `trek_fact_start` | `_trek_fact_start` | `start` | Starting trailhead/city. |
| `trek_fact_end` | `_trek_fact_end` | `end` | Finishing point. |
| `trek_price` | `_trek_price` | `price` | Cost figure (e.g. `2850`). |
| `trek_currency` | `_trek_currency` | `currency` | Currency code (`USD`, `EUR`, `GBP`, `NPR`). |

---

### 4. Overview Narrative & Route Visual Map

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_overview_kicker` | `_trek_overview_kicker` | `string` | Top small tag (e.g. `Nepal · 15 Days`). |
| `trek_overview_script` | `_trek_overview_script` | `string` | Script calligraphy eyebrow (e.g. *The Ultimate Trek*). |
| `trek_overview_heading` | `_trek_overview_heading` | `string` | Main section heading. |
| `trek_overview_subheading` | `_trek_overview_subheading` | `string` | Subtitle / one-line summary. |
| `trek_overview_content` | `_trek_overview_content` | `html` (WYSIWYG) | Complete overview narrative article. |
| `trek_overview_highlight` | `_trek_overview_highlight` | `html` (WYSIWYG) | Callout pull quote. |
| `inc_item[]` | `_trek_inclusions_list` / `_trek_highlights` | `string[]` | Key bulleted highlights repeater. |
| `trek_overview_img` | `_trek_overview_img` | `url` | Feature photograph alongside narrative. |
| `trek_route_img` | `_trek_route_img` | `url` | Static route map image. |
| `trek_map_file_url` | `_trek_map_file_url` | `url` | KML, GPX, or GeoJSON vector file. |
| `trek_map_embed_code` | `_trek_map_embed_code` | `html` | Mapbox, Leaflet, or Google Maps iFrame code. |
| `trek_route_caption_sub` | `_trek_route_caption_sub` | `string` | Route caption tag. |
| `trek_route_caption_h3` | `_trek_route_caption_h3` | `string` | Route caption headline. |

---

### 5. Day-by-Day Itinerary Builder

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_itin_kicker` | `_trek_itin_kicker` | `string` | Section kicker (e.g. `Day by Day`). |
| `trek_itin_script` | `_trek_itin_script` | `string` | Script font subhead (e.g. *Your Journey*). |
| `trek_itin_heading` | `_trek_itin_heading` | `string` | Section main heading. |
| `trek_itin_subtext` | `_trek_itin_subtext` | `string` | Section subtext description. |
| `itin_day[]` | `_trek_itinerary_list` / `_trek_itinerary` | `string` | Day number (e.g. `01`, `02`). |
| `itin_stage[]` | `_trek_itinerary_list` / `_trek_itinerary` | `string` | Stage & elevation (e.g. `Kathmandu (1,400m)`). |
| `itin_title[]` | `_trek_itinerary_list` / `_trek_itinerary` | `string` | Main day title. |
| `itin_pills[]` | `_trek_itinerary_list` / `_trek_itinerary` | `string` | Meals, lodge, and walking duration tags. |
| `itin_desc[]` | `_trek_itinerary_list` / `_trek_itinerary` | `html` (WYSIWYG) | Rich narrative description of the day's hike. |
| `itin_gallery[]` | `_trek_itinerary_list` / `_trek_itinerary` | `url[]` | Comma-separated or array of photo URLs for that day. |

---

### 6. Inclusions & Exclusions

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_inex_kicker` | `_trek_inex_kicker` | `string` | Section kicker (e.g. `What is Covered`). |
| `trek_inex_script` | `_trek_inex_script` | `string` | Script font subhead (e.g. *Clear & Transparent*). |
| `trek_inex_heading` | `_trek_inex_heading` | `string` | Section main heading (e.g. `Inclusions & Exclusions`). |
| `trek_inex_subtext` | `_trek_inex_subtext` | `string` | Section subtext description. |
| `inc_title[]`, `inc_desc[]` | `_trek_inclusions_full` & `_trek_inclusions` | `array<{title, desc}>` | Included items with title + rich description. |
| `exc_title[]`, `exc_desc[]` | `_trek_exclusions_full` & `_trek_exclusions` | `array<{title, desc}>` | Excluded items with title + rich description. |

---

### 7. Visual Photo Gallery

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_gallery_kicker` | `_trek_gallery_kicker` | `string` | Section kicker (e.g. `Visual Moments`). |
| `trek_gallery_script` | `_trek_gallery_script` | `string` | Script font subhead (e.g. *In Pictures*). |
| `trek_gallery_heading` | `_trek_gallery_heading` | `string` | Section main heading (e.g. `Glimpses of Everest`). |
| `trek_gallery_subtext` | `_trek_gallery_subtext` | `string` | Section subtext description. |
| `gal_url[]`, `gal_label[]` | `_trek_gallery_list` & `_trek_gallery_images` | `array<{url, label}>` | Gallery photographs with optional captions. |

---

### 8. Essential Information & FAQs

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_ess_kicker` | `_trek_ess_kicker` | `string` | Section kicker (e.g. `Travel Advice`). |
| `trek_ess_script` | `_trek_ess_script` | `string` | Script font subhead (e.g. *Good to Know*). |
| `trek_ess_heading` | `_trek_ess_heading` | `string` | Section heading (e.g. `Essential Information`). |
| `trek_ess_intro` | `_trek_ess_intro` | `html` (WYSIWYG) | Rich introductory narrative. |
| `trek_ess_card_img` | `_trek_ess_card_img` | `url` | Trip dossier side card image. |
| `trek_ess_card_kicker`| `_trek_ess_card_kicker` | `string` | Dossier card tag (e.g. `Trip Dossier`). |
| `trek_ess_card_title` | `_trek_ess_card_title` | `string` | Dossier card headline. |
| `trek_ess_card_desc` | `_trek_ess_card_desc` | `string` | Dossier card description. |
| `trek_ess_card_btn` | `_trek_ess_card_btn` | `string` | Dossier card CTA button text. |
| `trek_ess_card_email`| `_trek_ess_card_email` | `string` | Enquiry email address. |
| `ess_title[]`, `ess_content[]` | `_trek_essential_list` & `_trek_faqs` | `array<{title, content}>` | Accordion items: topic question + rich answer. |

---

### 9. Traveller Reviews & Moderation

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_review_kicker` | `_trek_review_kicker` | `string` | Section kicker (e.g. `Guest Stories`). |
| `trek_review_script` | `_trek_review_script` | `string` | Script font subhead (e.g. *Traveller Feedback*). |
| `trek_review_heading`| `_trek_review_heading` | `string` | Section heading. |
| `trek_review_subtext`| `_trek_review_subtext` | `string` | Section subtext description. |
| `rev_name[]`, `rev_loc[]`, `rev_rating[]`, `rev_quote[]` | `_trek_curated_reviews` & `_trek_reviews_list` | `array<{name, location, rating, quote}>` | Curated editorial reviews saved with the trek. |

*Note: The plugin also registers a dedicated custom post type (`trek_review`) where traveller reviews submitted through the website form are stored and moderated.*

---

### 10. Fixed Departures & Contact Settings

| Form Input Name | Post Meta Key | Type | Description |
| :--- | :--- | :--- | :--- |
| `trek_dep_kicker` | `_trek_dep_kicker` | `string` | Section kicker (e.g. `Travel Together`). |
| `trek_dep_script` | `_trek_dep_script` | `string` | Script font subhead (e.g. *Choose Your Month*). |
| `trek_dep_heading` | `_trek_dep_heading` | `string` | Section heading. |
| `trek_dep_subtext` | `_trek_dep_subtext` | `string` | Subtext description. |
| `trek_dep_box_script`| `_trek_dep_box_script` | `string` | Departure box script title (e.g. *Everest Awaits*). |
| `trek_dep_box_title` | `_trek_dep_box_title` | `string` | Departure box headline. |
| `trek_dep_box_desc` | `_trek_dep_box_desc` | `string` | Departure box body description. |
| `trek_dep_box_photo` | `_trek_dep_box_photo` | `url` | Host/guide photo URL. |
| `trek_dep_box_note` | `_trek_dep_box_note` | `string` | Availability footnote. |
| `trek_whatsapp_number`| `_trek_whatsapp_number` | `string` | WhatsApp contact number (digits only). |
| `trek_sales_email` | `_trek_sales_email` | `string` | Direct booking sales email. |

---

### 11. Canonical Structured Data

| Post Meta Key | Type | Description |
| :--- | :--- | :--- |
| `_trek_structured_data` | `json_string` | Serialized output of `Trek_Renderer::get_full_trek_data($post_id)` compiled on every post save. |
| `_trek_quick_facts` | `array` | Compiled associative array of quick facts. |

---

## Dual-Key Compatibility Map

The plugin maintains dual-key synchronization on save so that both modern structured arrays and legacy importers work without data loss:

| Primary Key (Admin Form / UI) | Legacy / Dual-Saved Key | Purpose |
| :--- | :--- | :--- |
| `_trek_itinerary_list` | `_trek_itinerary` | Day-by-day itinerary array. |
| `_trek_inclusions_full` | `_trek_inclusions` | Inclusions repeater with rich descriptions. |
| `_trek_exclusions_full` | `_trek_exclusions` | Exclusions repeater with rich descriptions. |
| `_trek_gallery_list` | `_trek_gallery_images` | Gallery photos and captions. |
| `_trek_essential_list` | `_trek_faqs` | Essential info accordion items. |
| `_trek_curated_reviews` | `_trek_reviews_list` | Curated editorial reviews. |

---

## Shortcodes Reference

### Granular Field Shortcode: `[trek_field]`

Retrieve any specific field value for a destination. If `id` is omitted, it defaults to the current queried post.

```text
[trek_field id="123" field="price"]
[trek_field field="duration"]
[trek_field field="high_point"]
[trek_field field="whatsapp"]
```

#### Supported `field` parameters:
- `title` / `name`
- `hero_title`
- `script_title`
- `subtitle` / `hero_subtitle`
- `duration` / `days` / `hero_days`
- `price`
- `formatted_price` (e.g. `USD 2,850`)
- `currency`
- `difficulty`
- `high_point` / `highest_point` / `max_altitude`
- `low_point`
- `region` / `breadcrumb_region`
- `season` / `best_season`
- `group_size`
- `accommodation` / `accom`
- `trekking` / `daily_walking`
- `style`
- `status`
- `whatsapp` / `whatsapp_number`
- `email` / `sales_email`
- `overview` / `overview_content`
- `highlight` / `overview_highlight`
- `hero_image` / `image`
- `card_image`
- `route_img`
- `map_file_url`
- `map_region`
- `map_district`

---

### Modular Section Shortcodes

Embed specific styled sections anywhere on a page:

| Shortcode | Attributes | Description |
| :--- | :--- | :--- |
| `[trek_hero]` | `id="123"` | Full responsive Hero banner with cursive overlay, title, badge, and background image. |
| `[trek_facts]` / `[trek_quick_facts]` | `id="123"` | Responsive 6-item Quick Facts grid (Duration, Elevation, Difficulty, Style, etc.). |
| `[trek_overview]` | `id="123"` | Overview narrative, pull quote highlight box, and key bullet points. |
| `[trek_itinerary]` | `id="123"` | Expandable Day-by-Day journey accordion with stages, tags, and trail narratives. |
| `[trek_inclusions]` / `[trek_inex]` | `id="123"` | Two-column Included vs. Excluded grid with checkmarks, titles, and descriptions. |
| `[trek_gallery]` | `id="123"` | Responsive photo gallery grid with aspect-ratio thumbnails. |
| `[trek_essentials]` / `[trek_faqs]` | `id="123"` | Expandable Essential Information / Travel Advice accordions. |
| `[trek_reviews]` | `id="123"` | 5-Star guest testimonials with reviewer names and locations. |
| `[trek_departures]` / `[trek_contact]` | `id="123"` | Fixed Departures card with direct 1-click WhatsApp inquiry button. |
| `[trek_map]` | `id="123"` | Interactive map embed code or static route map graphic. |
| `[trek_card]` | `id="123"` | Single destination showcase card with image, tag, duration pill, and link. |

---

### Directory & Filterable Listing Shortcodes

| Shortcode | Description | Example |
| :--- | :--- | :--- |
| `[trek_all_trips_filterable]` | Complete filterable directory with search, category tabs, and difficulty filters. | `[trek_all_trips_filterable per_page="12"]` |
| `[trek_category_treks]` | Destination card grid filtered by specific taxonomy category. | `[trek_category_treks category="trekking" limit="6"]` |
| `[trek_signature_journeys]` | Showcase slider / grid for featured signature treks. | `[trek_signature_journeys count="4"]` |
| `[trek_trip_detail]` | Complete destination detail page embedded in any page. | `[trek_trip_detail id="45"]` |

---

### Interactive Map & Journey Finder

| Shortcode | Description | Example |
| :--- | :--- | :--- |
| `[trek_nepal_map]` | Interactive SVG tourism map of Nepal with clickable districts. | `[trek_nepal_map]` |
| `[trek_journey_finder]` | Interactive multi-step trip planning wizard and lead generator. | `[trek_journey_finder]` |
| `[trek_journal_posts]` | Grid of travel journal and blog articles. | `[trek_journal_posts limit="3"]` |
| `[trek_home_notes]` | Curated dispatch notes from the field. | `[trek_home_notes]` |

---

## REST API Endpoints

All endpoints are prefixed with `/wp-json/trek-manager/v1/`:

### 1. `GET /destinations/{id}`
Returns the complete canonical JSON payload for destination `{id}`. Data is fresh and reflects all current CPT fields.

### 2. `POST /destinations/{id}`
Saves updated destination fields. Requires authentication (`current_user_can('edit_post', $id)`).

### 3. `POST /enquiries`
Public endpoint for traveller booking inquiries and trip planning requests. Stores submission into the `trek_enquiry` post type.

### 4. `POST /reviews`
Public endpoint for guest review submissions. Stores submission into the `trek_review` post type for moderation.

### 5. `GET /reviews/{trek_id}`
Returns approved public reviews for a specific destination.

---

## JSON Format & Bidirectional Sync

### Quick JSON Auto-Fill Tool
Inside the WordPress editor for any Destination post, open **Metabox 0: Quick JSON Auto-Fill Destination Data**:
1. Drag and drop any `.json` destination file into the dropzone (or click **Select JSON File**, or paste raw JSON).
2. The browser parser automatically populates all form fields:
   - Post Title & Hero (Script title, kicker, days, badge, subtitle, background image)
   - Card fields (Title, tags, meta, excerpt, order)
   - Quick Facts grid (Elevation, low point, season, lodging, group size, style)
   - Overview narrative (WYSIWYG editor, highlight box, highlights bullets, route image, maps)
   - Itinerary days (Day numbers, stages, titles, tags, WYSIWYG trail narratives, day gallery)
   - Inclusions & Exclusions (Section headers + titles and rich WYSIWYG descriptions)
   - Visual Gallery (Photo URLs and captions)
   - Essential Information & FAQs (Intro, dossier card, accordion Q&As)
   - Traveller Reviews (Guest names, locations, ratings, quotes)
   - Fixed Departures & Contact (WhatsApp number, sales email, card details)
   - Pricing & Taxonomies
3. Review changes and click **Publish** or **Update**.

### 1-Click JSON Export
Every destination is exportable as a canonical JSON file in two ways:
1. **From the Destination List (`wp-admin/edit.php?post_type=trek_destination`)**: Click the **📥 Export JSON** row action on any destination row.
2. **From the Destination Edit Screen**: Scroll to **Metabox 9: Structured Live JSON Payload** and click **📥 Download .json** or **📋 Copy Full JSON**.

### Canonical Schema
The plugin includes a verified canonical destination template at:
`wp-content/plugins/Trek Content Manager/assets/canonical-trek-template.json`

---

## Developer FAQ & Best Practices

#### Q: How do I disable the frontend JS interactive engine?
The static HTML output in `single-trek_destination.php` is fully styled and responsive out of the box. `trek-engine.js` is enqueued to provide interactive client-side tab switching and lightbox interactions. If you only want server-rendered HTML, dequeue `trek-engine-js`.

#### Q: How do I programmatically get a trek's structured data?
```php
$data = Trek_Renderer::get_full_trek_data( $post_id );
```
This returns a normalized associative array with all section headers, quick facts, itinerary days, inclusions/exclusions, gallery, and contact data.

#### Q: How are inclusions and exclusions stored?
Inclusions and exclusions are stored as structured arrays of `{title, desc}` in `_trek_inclusions_full` and `_trek_exclusions_full`, while also syncing backward-compatible plain strings to `_trek_inclusions` and `_trek_exclusions`.
