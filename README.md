# Trek Content Manager — Developer Documentation

> **Plugin Version:** 1.0.2 | **Author:** Yeti Adventure Tech Architecture  
> **Requires WordPress:** 5.8+ | **Requires PHP:** 7.4+  
> **Text Domain:** `trek-content-manager`

---

## Table of Contents

1. [Overview](#overview)
2. [Plugin Architecture](#plugin-architecture)
3. [File Reference: `/includes`](#file-reference-includes)
   - [class-trek-cpt.php](#class-trek-cptphp)
   - [class-trek-renderer.php](#class-trek-rendererphp)
   - [class-trek-rest-api.php](#class-trek-rest-apiphp)
   - [class-trek-shortcodes.php](#class-trek-shortcodesphp)
   - [class-trek-importer.php](#class-trek-importerphp)
   - [class-trek-roles.php](#class-trek-rolesphp)
4. [Complete Meta Field Reference](#complete-meta-field-reference)
   - [Hero & Card Fields](#1-hero--card-fields)
   - [Taxonomy & Classification Fields](#2-taxonomy--classification-fields)
   - [Quick Facts Fields](#3-quick-facts-fields)
   - [Pricing Fields](#4-pricing-fields)
   - [Map & Geography Fields](#5-map--geography-fields)
   - [Overview / Content Fields](#6-overview--content-fields)
   - [Itinerary Repeater](#7-itinerary-repeater)
   - [Inclusions & Exclusions](#8-inclusions--exclusions)
   - [Gallery Repeater](#9-gallery-repeater)
   - [Extensions Repeater](#10-extensions-repeater)
   - [Essential Info / FAQ Accordion](#11-essential-info--faq-accordion)
   - [Curated Reviews Repeater](#12-curated-reviews-repeater)
   - [Itinerary Section Labels](#13-itinerary-section-labels)
   - [Gallery Section Labels](#14-gallery-section-labels)
   - [Extensions Section Labels](#15-extensions-section-labels)
   - [Essential Info Section Labels](#16-essential-info-section-labels)
   - [Reviews Section Labels](#17-reviews-section-labels)
   - [Departure / Booking Section Labels](#18-departure--booking-section-labels)
   - [Contact & CTA Fields](#19-contact--cta-fields)
   - [Master JSON Payload](#20-master-json-payload)
5. [Dual-Key Sync Map](#dual-key-sync-map)
6. [Taxonomies](#taxonomies)
7. [REST API Endpoints](#rest-api-endpoints)
8. [Shortcodes Reference](#shortcodes-reference)
9. [JavaScript Data (TREK_DATA)](#javascript-data-trek_data)
10. [Import JSON Schema](#import-json-schema)
11. [User Roles & Capabilities](#user-roles--capabilities)
12. [Data Save Flow](#data-save-flow)

---

## Overview

Trek Content Manager is a full-stack WordPress plugin for managing adventure travel destinations (treks, expeditions, tours). It provides:

- A Custom Post Type (`trek_destination`) with 11 rich admin metaboxes
- A second CPT (`trek_review`) for moderated guest reviews
- A master JSON payload (`_trek_structured_data`) auto-generated on every post save — fed to the REST API and frontend JavaScript engine
- Full dual-key backward compatibility across two save paths (admin form vs. JSON importer)
- Role-aware rendering (`public`, `staff`, `reviewer`, `editor`, `admin`)
- Shortcodes for listing pages (sliders, filterable grid, map, signature journeys, journey finder)
- REST API for retrieving destination data and submitting enquiries/reviews

---

## Plugin Architecture

```
trek-content-manager.php          ← Main entry point, bootstraps all classes
├── includes/
│   ├── class-trek-cpt.php        ← CPT registration, all 11 admin metaboxes, save handler, card helpers
│   ├── class-trek-renderer.php   ← Canonical data normaliser → get_full_trek_data(), schema JSON-LD, social meta
│   ├── class-trek-rest-api.php   ← REST endpoints: GET/POST destinations, enquiries, reviews
│   ├── class-trek-shortcodes.php ← All listing shortcodes, journey finder, map, journal posts
│   ├── class-trek-importer.php   ← Admin JSON importer page + handle_json_import()
│   └── class-trek-roles.php      ← Custom capabilities, WP role → Trek role mapping
├── assets/
│   ├── css/trek-detail.css       ← Single destination page styles
│   ├── css/single-post.css       ← Blog post styles
│   ├── js/trek-engine.js         ← Frontend renderer (reads TREK_DATA, renders sections)
│   ├── js/trek-roles.js          ← Role detection and gating
│   └── js/trek-validation.js     ← Form validation helpers
└── templates/
    ├── single-trek_destination.php  ← Full destination detail page
    └── single-post.php              ← Blog post template
```

---

## File Reference: `/includes`

---

### `class-trek-cpt.php`

**Class:** `Trek_CPT`  
**Size:** ~142 KB | ~2450 lines

#### Responsibilities
- Register `trek_destination` and `trek_review` Custom Post Types
- Register `trek_category`, `trek_region`, `trek_difficulty` taxonomies
- Register and render 11 admin metaboxes on the destination edit screen
- Save all post meta via `save_meta_box_data()`
- Provide helper methods: `get_card_data()`, `get_trek_cards()`
- Render the admin field guide page

#### Public Methods

| Method | Description |
|---|---|
| `init()` | Hook registration |
| `register_post_types()` | Registers `trek_destination` and `trek_review` CPTs |
| `register_taxonomies()` | Registers `trek_category`, `trek_region`, `trek_difficulty` |
| `add_meta_boxes()` | Attaches all 11 metaboxes to `trek_destination` edit screen |
| `render_hero_metabox($post)` | Metabox 1: Hero, titles, categories, regions, difficulty, map settings |
| `render_card_metabox($post)` | Metabox 1b: Card display fields |
| `render_facts_metabox($post)` | Metabox 2: Quick facts, pricing, season, group size |
| `render_overview_metabox($post)` | Metabox 3: Overview WYSIWYG content |
| `render_itinerary_metabox($post)` | Metabox 4: Day-by-day itinerary repeater |
| `render_inex_metabox($post)` | Metabox 5: Full inclusions & exclusions repeater |
| `render_gallery_metabox($post)` | Metabox 6: Gallery image repeater |
| `render_extensions_metabox($post)` | Metabox 7: Optional extensions repeater |
| `render_essential_metabox($post)` | Metabox 8: Essential info accordion repeater |
| `render_reviews_metabox($post)` | Metabox 9: Curated reviews repeater |
| `render_contact_metabox($post)` | Metabox 10: WhatsApp, email, departure settings |
| `render_json_metabox($post)` | Metabox 11: Live structured JSON viewer (read-only with Copy/Download) |
| `save_meta_box_data($post_id)` | Master save handler — saves all fields, syncs dual keys, compiles `_trek_structured_data` |
| `save_review_meta_data($post_id)` | Saves moderation data for `trek_review` CPT |
| `get_card_data($post_id, $index)` | Returns structured card data array for listing views |
| `get_trek_cards($args)` | WP_Query wrapper returning card data for multiple destinations |
| `render_guide_page()` | Admin field guide/reference page |

---

### `class-trek-renderer.php`

**Class:** `Trek_Renderer`  
**Size:** ~47 KB

#### Responsibilities
- Normalise all raw meta into a single canonical PHP array via `get_full_trek_data()`
- This array is: saved as `_trek_structured_data`, passed to the JS engine as `TREK_DATA`, and served via the REST API
- Render `<script type="application/ld+json">` TourActivity schema
- Render Open Graph and Twitter Card meta tags

#### Public Methods

| Method | Description |
|---|---|
| `init()` | Hook registration |
| `auto_inject_head_meta()` | Injects schema + social meta into `<head>` on singular destination pages |
| `get_full_trek_data($post_item)` | **Core method.** Returns the full normalised payload. Accepts post ID or WP_Post object |
| `render_schema_json_ld($trek_data)` | Outputs `<script type="application/ld+json">` TourActivity schema |
| `render_social_meta($trek_data)` | Outputs Open Graph + Twitter Card meta tags |

#### `get_full_trek_data()` Output Structure

```php
[
  // Identity
  'id'                    => int,
  'slug'                  => string,
  'status'                => string,
  'canonical_url'         => string,

  // Classification
  'difficulty'            => string,   // 'Strenuous'
  'difficulty_slug'       => string,   // 'strenuous'
  'map_region'            => string,
  'map_district'          => string,
  'primary_category'      => string,
  'primary_category_slug' => string,
  'categories'            => array,    // All trek_category terms
  'regions'               => array,    // All trek_region terms

  // Pricing
  'pricing' => [
    'price'        => string,
    'numeric_price'=> float,
    'currency'     => string,   // 'USD'
    'formatted'    => string,   // 'USD 2,850'
  ],

  // Hero
  'hero' => [
    'title'      => string,
    'subtitle'   => string,
    'kicker'     => string,
    'script'     => string,
    'days'       => string,
    'badge_sub'  => string,
    'bg_img'     => string,
    'breadcrumb_region' => string,
  ],

  // Card
  'card' => [
    'title'      => string,
    'meta'       => string,
    'tag'        => string,
    'kicker'     => string,
    'excerpt'    => string,
    'image_url'  => string,
    'order'      => int,
    'is_featured'=> bool,
  ],

  // Overview
  'overview' => [
    'kicker'      => string,
    'script'      => string,
    'heading'     => string,
    'subheading'  => string,
    'content'     => string,  // wp_kses_post HTML
    'highlight'   => string,  // wp_kses_post HTML
    'highlights'  => array,   // plain text bullet array
    'inclusions_list' => array,
  ],

  // Quick Facts (flat array for JS engine)
  'keyFacts' => [
    'duration'   => string,
    'high_point' => string,
    'region'     => string,
    'season'     => string,
    'group_size' => string,
    'trekking'   => string,
    'accom'      => string,
    'style'      => string,
    'difficulty' => string,
    'price'      => string,
    'currency'   => string,
  ],
  'facts' => [ ... ], // alias of keyFacts

  // Geography
  'geography' => [
    'map_region'   => string,
    'map_district' => string,
    'breadcrumb'   => string,
  ],

  // Route
  'route' => [
    'img'        => string,
    'caption_sub'=> string,
    'caption_h3' => string,
  ],

  // Itinerary (dual key)
  '_trek_itinerary_list' => array,  // [{day, stage, title, pills, desc}]
  'itinerary'            => array,  // alias

  // Inclusions / Exclusions (dual key)
  'included'    => array,   // alias of inclusions
  'inclusions'  => array,
  'excluded'    => array,   // alias of exclusions
  'exclusions'  => array,

  // Gallery (dual key)
  'gallery'      => array,  // [{url, label}]
  'gallery_images'=> array, // alias

  // Extensions
  'extensions'   => array,  // [{title, suggested, desc}]

  // Essential / FAQ (dual key)
  'essential'    => array,  // [{title, content}]
  'faqs'         => array,  // alias

  // Reviews (dual key)
  'reviews'      => array,  // [{name, location, rating, quote}]
  '_trek_curated_reviews' => array, // alias

  // Departure
  'departure' => [ ... ],

  // Contact
  'contact' => [
    'whatsapp'    => string,
    'sales_email' => string,
  ],

  // JS engine aliases
  'keyFacts'  => array,
  'geography' => array,
  'included'  => array,
  'excluded'  => array,
  'reviews'   => array,
]
```

---

### `class-trek-rest-api.php`

**Class:** `Trek_REST_API`  
**Namespace:** `trek-manager/v1`

#### Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/wp-json/trek-manager/v1/destinations/{id}` | Public | Returns `_trek_structured_data` (falls back to renderer) |
| POST | `/wp-json/trek-manager/v1/destinations/{id}` | `edit_post` cap | Updates partial destination data |
| POST | `/wp-json/trek-manager/v1/enquiries` | Public | Saves booking enquiry as `trek_enquiry` private post |
| POST | `/wp-json/trek-manager/v1/reviews` | Public | Saves review as `trek_review` post, triggers WhatsApp notification |
| GET | `/wp-json/trek-manager/v1/reviews/{trek_id}` | Public | Returns approved reviews for a destination |
| POST | `/wp-json/trek/v1/comments` | Public | Submit guest comment on journal posts |
| POST | `/wp-json/trek/v1/comments/{id}/like` | Public | Like a comment |
| POST | `/wp-json/trek/v1/posts/{id}/like` | Public | Like a journal post |

#### Enquiry Meta Fields (saved to `trek_enquiry` post)

| Meta Key | Description |
|---|---|
| `_trek_enquiry_email` | Submitter email |
| `_trek_enquiry_trek_id` | Associated destination post ID |
| `_trek_enquiry_source` | Submission source (`frontend`, etc.) |

#### Review Meta Fields (saved to `trek_review` post)

| Meta Key | Description |
|---|---|
| `_trek_id` | Associated destination post ID |
| `_trek_rating` | Rating 1–5 |
| `_trek_reviewer_email` | Reviewer email |
| `_trek_city` | Reviewer city |
| `_trek_country` | Reviewer country |
| `_trek_tour_date` | When they traveled |
| `_trek_moment` | Inspirational moment text |
| `_trek_leader` | Trek leader feedback |
| `_trek_advice` | Advice for other travelers |
| `_trek_approved` | `'1'` = approved, `'0'` = pending |

---

### `class-trek-shortcodes.php`

**Class:** `Trek_Shortcodes`

#### Shortcodes

| Shortcode | Aliases | Description |
|---|---|---|
| `[ya_country_destinations]` | `[trek_country_destinations]` | Card slider filtered by country/region |
| `[ya_all_trips_filterable]` | `[trek_all_trips_filterable]`, `[trek_listing]` | Full filterable directory with AJAX search |
| `[ya_category_treks]` | `[trek_category_treks]`, `[trek_journey_cards]` | Grid/carousel by category |
| `[ya_signature_journeys]` | `[trek_signature_journeys]`, `[trek_home]` | Featured journeys hero section |
| `[ya_journey_finder]` | `[trek_journey_finder]` | Interactive journey finder quiz |
| `[ya_nepal_map]` | `[trek_nepal_map]`, `[nepal_tourism_map]`, `[ya_tourism_map]`, `[trek_tourism_map]` | Interactive Nepal destination map |
| `[ya_journal_posts]` | `[trek_journal_posts]` | Blog/journal post grid |
| `[ya_home_notes]` | `[trek_home_notes]` | Home page notes/highlights |

#### Shortcode Attributes

**`[ya_country_destinations]`**

| Attribute | Default | Description |
|---|---|---|
| `country` | `''` | Filter by region/country name |
| `limit` | `12` | Max number of cards |
| `columns` | `3` | Grid columns |

**`[ya_all_trips_filterable]`**

| Attribute | Default | Description |
|---|---|---|
| `limit` | `24` | Max destinations |
| `category` | `''` | Pre-filter by category slug |
| `region` | `''` | Pre-filter by region slug |

**`[ya_category_treks]`**

| Attribute | Default | Description |
|---|---|---|
| `category` | `''` | Trek category slug |
| `limit` | `6` | Max posts |
| `layout` | `grid` | `grid` or `carousel` |

**`[ya_nepal_map]`**

| Attribute | Default | Description |
|---|---|---|
| `region` | `''` | Highlight specific region |
| `interactive` | `true` | Enable click interactions |

#### Public Helper Methods

| Method | Description |
|---|---|
| `get_formatted_trip_data($post_item, $index)` | Returns formatted card data for shortcode output |
| `create_guest_comment(WP_REST_Request)` | REST handler for journal guest comments |
| `like_journal_comment(WP_REST_Request)` | REST handler for liking a comment |
| `like_journal_post(WP_REST_Request)` | REST handler for liking a journal post |

---

### `class-trek-importer.php`

**Class:** `Trek_Importer`

#### Responsibilities
- Provides admin page at **Destinations → Import / Export JSON**
- Accepts a JSON array of destination objects and creates/updates `trek_destination` posts
- Supports drag-and-drop file upload or paste of raw JSON
- Updates all dual-key meta fields and compiles `_trek_structured_data` per imported post

#### Import JSON Keys (Full Schema)

```json
[
  {
    "title":            "The Ultimate Trek: Everest Base Camp",
    "slug":             "everest-basecamp-trek",
    "excerpt":          "Journey through Sherpa country...",
    "days":             15,
    "difficulty":       "Strenuous",
    "price":            "2,850",
    "currency":         "USD",
    "category":         ["Trekking", "Himalayan"],
    "primary_category": "Trekking",
    "region":           "Nepal",
    "map_region":       "Khumbu",
    "map_district":     "Solukhumbu",
    "tag":              "Trekking · Everest",
    "featured":         true,
    "hero_image":       "https://...",
    "card_image":       "https://...",
    "overview":         "<p>HTML content...</p>",
    "quick_facts": {
      "max_altitude":   "5,364m / 17,598ft",
      "destination":    "Everest Region, Nepal",
      "daily_walking":  "5 - 7 Hours",
      "accommodation":  "Mountain Lodges",
      "style":          "Privately Guided",
      "season":         "March to May",
      "group_size":     "2 - 12 Travelers",
      "difficulty":     "Strenuous",
      "price":          "2,850",
      "currency":       "USD"
    },
    "highlights":       ["Stand at EBC...", "Sunrise from Kala Patthar..."],
    "itinerary": [
      {
        "day":          "01",
        "title":        "Arrive in Kathmandu",
        "description":  "Private transfer...",
        "altitude":     "1,400m",
        "walking_hours":"Rest day",
        "stay":         "Dwarika's Hotel",
        "meals":        "Dinner"
      }
    ],
    "inclusions":       ["All transfers", "Scenic flights"],
    "exclusions":       ["International airfare", "Visa"],
    "gallery": [
      { "url": "https://...", "label": "Khumbu Icefall" }
    ],
    "faqs": [
      { "question": "Do I need a permit?", "answer": "Yes..." }
    ],
    "reviews": [
      {
        "name":     "Sarah Jenkins",
        "location": "United Kingdom",
        "rating":   "5",
        "quote":    "Unforgettable..."
      }
    ]
  }
]
```

#### Public Methods

| Method | Description |
|---|---|
| `init()` | Hook registration |
| `add_admin_menu()` | Registers importer submenu |
| `render_importer_page()` | Renders the importer admin UI |
| `handle_json_import()` | Processes POST form submission, creates/updates posts |
| `get_sample_json_string()` | Returns full sample JSON string for the help panel |

---

### `class-trek-roles.php`

**Class:** `Trek_Roles`

#### WP Role → Trek Role Mapping

| WordPress Role | Trek Role | Access |
|---|---|---|
| `administrator` | `admin` | All fields, pricing, internal notes |
| `editor` | `editor` | All public + internal fields |
| `author` | `staff` | Operational content |
| `contributor` | `reviewer` | Submit reviews |
| `subscriber` / guests | `public` | Public-facing content only |

#### Custom Capabilities

| Capability | Granted To |
|---|---|
| `manage_trek_content` | admin, editor |
| `edit_trek_destinations` | admin, editor |
| `publish_trek_destinations` | admin |
| `manage_trek_pricing` | admin |
| `review_trek_destinations` | admin, editor |
| `view_internal_trek_notes` | admin |

---

## Complete Meta Field Reference

All meta keys are prefixed with `_` in the database (WordPress convention).

---

### 1. Hero & Card Fields

| Form Field Name | Meta Key | Type | Sanitizer | Description |
|---|---|---|---|---|
| `trek_script_title` | `_trek_script_title` | text | `sanitize_text_field` | Cursive eyebrow/script above hero heading |
| `trek_hero_title` | `_trek_hero_title` | text | `sanitize_text_field` | Main hero `<h1>` heading (falls back to post title) |
| `trek_hero_kicker` | `_trek_hero_kicker` | text | `sanitize_text_field` | Hero kicker line |
| `trek_hero_subtitle` | `_trek_hero_subtitle` | textarea | `sanitize_textarea_field` | Hero subtitle paragraph |
| `trek_hero_days` | `_trek_hero_days` | text | `sanitize_text_field` | Duration display e.g. `"15 Days"` |
| `trek_hero_badge_sub` | `_trek_hero_badge_sub` | text | `sanitize_text_field` | Badge subline below day count |
| `trek_hero_bg_img` | `_trek_hero_bg_img` | url | `sanitize_text_field` | Hero background image URL |
| `trek_breadcrumb_region` | `_trek_breadcrumb_region` | text | `sanitize_text_field` | Breadcrumb region label e.g. `"Nepal"` |
| `trek_card_title` | `_trek_card_title` | text | `sanitize_text_field` | Card `<h3>` title (falls back to hero title) |
| `trek_card_meta` | `_trek_card_meta` | text | `sanitize_text_field` | Card badge/meta e.g. `"15 Days"` |
| `trek_card_tag` | `_trek_card_tag` | text | `sanitize_text_field` | Card subtitle/tag line |
| `trek_card_kicker` | `_trek_card_kicker` | text | `sanitize_text_field` | Card kicker |
| `trek_card_excerpt` | `_trek_card_excerpt` | text | `sanitize_text_field` | Short card description |
| `trek_card_image_url` | `_trek_card_image_url` | url | `sanitize_text_field` | Card thumbnail image URL |
| `trek_card_order` | `_trek_card_order` | number | `sanitize_text_field` | Sort order for card listings |
| `trek_featured_home` | `_trek_featured_home` | checkbox | n/a | `"1"` = featured on homepage, `"0"` = not featured |

---

### 2. Taxonomy & Classification Fields

| Form Field Name | Meta Key | Taxonomy | Description |
|---|---|---|---|
| `trek_category_ids[]` | (taxonomy) | `trek_category` | Multi-select checkboxes → `wp_set_object_terms()` |
| `trek_region_ids[]` | (taxonomy) | `trek_region` | Multi-select checkboxes → `wp_set_object_terms()` |
| `trek_primary_category` | `_trek_primary_category` | meta | Slug of the primary category (for renderer/JS) |
| `trek_difficulty` | `_trek_difficulty` | `trek_difficulty` + meta | Select dropdown + auto syncs taxonomy term |

**Difficulty Options:** `Easy` · `Moderate` · `Demanding` · `Strenuous` · `Challenging` · `Technical`

---

### 3. Quick Facts Fields

| Form Field Name | Meta Key | Description |
|---|---|---|
| `trek_fact_high_point` | `_trek_fact_high_point` | Max altitude e.g. `"5,364m / 17,598ft"` |
| `trek_fact_region` | `_trek_fact_region` | Fact panel region/destination label |
| `trek_fact_trekking` | `_trek_fact_trekking` | Daily walking hours |
| `trek_fact_accom` | `_trek_fact_accom` | Accommodation type |
| `trek_fact_style` | `_trek_fact_style` | Travel style e.g. `"Privately Guided"` |
| `trek_fact_season` | `_trek_fact_season` | Best season text |
| `trek_fact_group_size` | `_trek_fact_group_size` | Group size range |
| — | `_trek_quick_facts` | **Array** – compiled from all fact fields above on every save |

**`_trek_quick_facts` Array Keys:**
```php
[
  'duration'   => string,
  'high_point' => string,
  'region'     => string,
  'season'     => string,
  'group_size' => string,
  'trekking'   => string,
  'accom'      => string,
  'style'      => string,
  'difficulty' => string,
  'price'      => string,
  'currency'   => string,
]
```

---

### 4. Pricing Fields

| Form Field Name | Meta Key | Description |
|---|---|---|
| `trek_price` | `_trek_price` | Starting price string e.g. `"2,850"` |
| `trek_currency` | `_trek_currency` | Currency code: `USD` · `EUR` · `GBP` · `AUD` · `CAD` · `NPR` |

---

### 5. Map & Geography Fields

| Form Field Name | Meta Key | Description |
|---|---|---|
| `trek_map_region` | `_trek_map_region` | Map region label e.g. `"Khumbu"` |
| `trek_map_district` | `_trek_map_district` | Map district label e.g. `"Solukhumbu"` |

---

### 6. Overview / Content Fields

| Form Field Name | Meta Key | Type | Description |
|---|---|---|---|
| `trek_overview_kicker` | `_trek_overview_kicker` | text | Overview section kicker |
| `trek_overview_script` | `_trek_overview_script` | text | Overview section script/eyebrow |
| `trek_overview_heading` | `_trek_overview_heading` | text | Overview `<h2>` |
| `trek_overview_subheading` | `_trek_overview_subheading` | text | Overview subheading |
| `trek_overview_content` | `_trek_overview_content` | WYSIWYG | Main overview body HTML |
| `trek_overview_highlight` | `_trek_overview_highlight` | WYSIWYG | Highlighted pull-quote HTML |
| `trek_route_img` | `_trek_route_img` | url | Route map image URL |
| `trek_route_caption_sub` | `_trek_route_caption_sub` | text | Route map caption line 1 |
| `trek_route_caption_h3` | `_trek_route_caption_h3` | text | Route map caption headline |
| `inc_item[]` | `_trek_inclusions_list` | array | Overview bullet inclusions list |

---

### 7. Itinerary Repeater

Saved as an **array of objects** via repeater form fields.

| Form Array Name | Array Key | Sanitizer | Description |
|---|---|---|---|
| `itin_day[]` | `day` | `sanitize_text_field` | Day number e.g. `"01"` |
| `itin_stage[]` | `stage` | `sanitize_text_field` | Location/stage e.g. `"Kathmandu to Lukla"` |
| `itin_title[]` | `title` | `sanitize_text_field` | Day title |
| `itin_pills[]` | `pills` | `sanitize_text_field` | Pill badges e.g. `"1,400m · Rest Day"` |
| `itin_desc[]` | `desc` | `wp_kses_post` | Day description HTML |

**Saved to (dual keys):**

| Meta Key | Format |
|---|---|
| `_trek_itinerary_list` | `[{day, stage, title, pills, desc}]` (CPT admin format) |
| `_trek_itinerary` | Same array, saved simultaneously for legacy/importer compatibility |

**Side Card Points:**

| Form Array | Meta Key | Description |
|---|---|---|
| `itin_side_point[]` | `_trek_itin_side_points` | Bullet highlights on itinerary sidebar card |

---

### 8. Inclusions & Exclusions

| Form Array | Meta Key (Primary) | Meta Key (Alias) | Description |
|---|---|---|---|
| `inc_full_item[]` | `_trek_inclusions_full` | `_trek_inclusions` | What's included plain text array |
| `exc_full_item[]` | `_trek_exclusions_full` | `_trek_exclusions` | What's excluded plain text array |

---

### 9. Gallery Repeater

| Form Array | Array Key | Description |
|---|---|---|
| `gal_url[]` | `url` | Image URL (sanitized with `esc_url_raw`) |
| `gal_label[]` | `label` | Image caption/label |

**Saved to (dual keys):**

| Meta Key | Format |
|---|---|
| `_trek_gallery_list` | `[{url, label}]` |
| `_trek_gallery_images` | Same, saved simultaneously |

**Section Label Fields:**

| Meta Key | Description |
|---|---|
| `_trek_gallery_kicker` | Gallery kicker text |
| `_trek_gallery_script` | Gallery eyebrow script |
| `_trek_gallery_heading` | Gallery `<h2>` |
| `_trek_gallery_subtext` | Gallery subtext |

---

### 10. Extensions Repeater

| Form Array | Array Key | Description |
|---|---|---|
| `ext_title[]` | `title` | Extension name |
| `ext_suggested[]` | `suggested` | Suggested duration e.g. `"1–2 extra days"` |
| `ext_desc[]` | `desc` | Extension description |

**Meta Key:** `_trek_extensions_list`

**Extension Section Fields:**

| Meta Key | Description |
|---|---|
| `_trek_ext_photo` | Extensions section background photo |
| `_trek_ext_kicker` | Extensions kicker |
| `_trek_ext_script` | Extensions eyebrow script |
| `_trek_ext_heading` | Extensions `<h2>` |
| `_trek_ext_btn_text` | CTA button text |
| `_trek_ext_btn_url` | CTA button URL |
| `_trek_ext_intro` | Extensions intro HTML (WYSIWYG) |

---

### 11. Essential Info / FAQ Accordion

| Form Array | Array Key | Description |
|---|---|---|
| `ess_title[]` | `title` | Accordion/FAQ title (question) |
| `ess_content[]` | `content` | Accordion body HTML (answer) |

**Saved to (dual keys):**

| Meta Key | Format |
|---|---|
| `_trek_essential_list` | `[{title, content}]` |
| `_trek_faqs` | Same array — format: `[{title/question, content/answer}]` |

**Section Label Fields:**

| Meta Key | Description |
|---|---|
| `_trek_ess_kicker` | Section kicker |
| `_trek_ess_script` | Eyebrow script |
| `_trek_ess_heading` | `<h2>` heading |
| `_trek_ess_intro` | Intro paragraph (WYSIWYG) |
| `_trek_ess_card_img` | Sidebar card image |
| `_trek_ess_card_kicker` | Sidebar card kicker |
| `_trek_ess_card_title` | Sidebar card title |
| `_trek_ess_card_desc` | Sidebar card description |
| `_trek_ess_card_btn` | Sidebar card CTA button text |
| `_trek_ess_card_email` | Sidebar card contact email |

---

### 12. Curated Reviews Repeater

| Form Array | Array Key | Description |
|---|---|---|
| `rev_name[]` | `name` | Reviewer name |
| `rev_loc[]` | `location` | Reviewer location e.g. `"London, UK"` |
| `rev_rating[]` | `rating` | Star rating `"1"–"5"` |
| `rev_quote[]` | `quote` | Review text |

**Saved to (dual keys):**

| Meta Key | Format |
|---|---|
| `_trek_curated_reviews` | `[{name, location, rating, quote}]` |
| `_trek_reviews_list` | Same array — alias used by renderer and JS engine |

---

### 13. Itinerary Section Labels

| Meta Key | Description |
|---|---|
| `_trek_itin_kicker` | Section kicker |
| `_trek_itin_script` | Eyebrow script |
| `_trek_itin_heading` | `<h2>` heading |
| `_trek_itin_subtext` | Subtext paragraph |
| `_trek_itin_side_script` | Side card eyebrow |
| `_trek_itin_side_title` | Side card title |
| `_trek_itin_side_desc` | Side card description |

---

### 14. Gallery Section Labels

| Meta Key | Description |
|---|---|
| `_trek_gallery_kicker` | Section kicker |
| `_trek_gallery_script` | Eyebrow script |
| `_trek_gallery_heading` | `<h2>` |
| `_trek_gallery_subtext` | Section subtext |

---

### 15. Extensions Section Labels

See [10. Extensions Repeater](#10-extensions-repeater) for section label fields.

---

### 16. Essential Info Section Labels

See [11. Essential Info / FAQ Accordion](#11-essential-info--faq-accordion) for section label fields.

---

### 17. Reviews Section Labels

| Meta Key | Description |
|---|---|
| `_trek_review_kicker` | Section kicker |
| `_trek_review_script` | Eyebrow script |
| `_trek_review_heading` | `<h2>` |
| `_trek_review_subtext` | Section subtext |

---

### 18. Departure / Booking Section Labels

| Meta Key | Description |
|---|---|
| `_trek_dep_kicker` | Departure section kicker |
| `_trek_dep_script` | Eyebrow script |
| `_trek_dep_heading` | `<h2>` |
| `_trek_dep_subtext` | Section subtext |
| `_trek_dep_box_script` | Booking box eyebrow |
| `_trek_dep_box_title` | Booking box title |
| `_trek_dep_box_desc` | Booking box description |
| `_trek_dep_box_photo` | Booking box image URL |
| `_trek_dep_box_note` | Booking box footnote |

---

### 19. Contact & CTA Fields

| Form Field | Meta Key | Description |
|---|---|---|
| `trek_whatsapp_number` | `_trek_whatsapp_number` | WhatsApp number (international format) |
| `trek_sales_email` | `_trek_sales_email` | Sales/contact email address |

---

### 20. Master JSON Payload

| Meta Key | Type | Description |
|---|---|---|
| `_trek_structured_data` | JSON string | Auto-compiled on every post save and import. Consumed by REST API (`GET /destinations/{id}`) and injected into frontend as `TREK_DATA` via `wp_localize_script` |

**Generation:** Called automatically in `save_meta_box_data()` and `handle_json_import()`:
```php
$full_data = Trek_Renderer::get_full_trek_data( $post_id );
update_post_meta( $post_id, '_trek_structured_data',
    wp_slash( wp_json_encode( $full_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) )
);
```

---

## Dual-Key Sync Map

All repeaters write to **two meta keys simultaneously** to ensure backward compatibility between the admin form save path and the JSON importer path.

| Canonical Key (CPT Admin) | Legacy / Importer Key | Notes |
|---|---|---|
| `_trek_itinerary_list` | `_trek_itinerary` | Admin format includes `stage` and `pills` |
| `_trek_inclusions_full` | `_trek_inclusions` | Simple string array |
| `_trek_exclusions_full` | `_trek_exclusions` | Simple string array |
| `_trek_gallery_list` | `_trek_gallery_images` | Admin format includes `label`; legacy is URL-only array |
| `_trek_essential_list` | `_trek_faqs` | Admin uses `{title, content}`; importer uses `{question, answer}` |
| `_trek_curated_reviews` | `_trek_reviews_list` | Same format: `{name, location, rating, quote}` |

---

## Taxonomies

| Taxonomy | Slug | Post Type | Hierarchical | Description |
|---|---|---|---|---|
| Trek Category | `trek_category` | `trek_destination` | No | Trip type (Trekking, Culture, Wildlife, etc.) |
| Trek Region | `trek_region` | `trek_destination` | No | Geographic region (Nepal, Tibet, India, etc.) |
| Trek Difficulty | `trek_difficulty` | `trek_destination` | No | Difficulty level — auto-synced from `_trek_difficulty` meta on save |

---

## REST API Endpoints

### GET `/wp-json/trek-manager/v1/destinations/{id}`

Returns full destination payload. Reads `_trek_structured_data` first; falls back to `Trek_Renderer::get_full_trek_data()` if not yet compiled.

**Example:**
```bash
curl https://yoursite.com/wp-json/trek-manager/v1/destinations/123
```

### POST `/wp-json/trek-manager/v1/enquiries`

```json
{
  "name":    "John Smith",
  "email":   "john@example.com",
  "message": "I am interested in the EBC trek...",
  "trek_id": 123
}
```

### POST `/wp-json/trek-manager/v1/reviews`

```json
{
  "name":      "Sarah Jenkins",
  "email":     "sarah@example.com",
  "city":      "London",
  "country":   "UK",
  "rating":    5,
  "comment":   "Absolutely life-changing...",
  "trek_id":   123,
  "tour_date": "March 2025",
  "moment":    "Kala Patthar sunrise",
  "advice":    "Train hard, go slow"
}
```

---

## Shortcodes Reference

```
[ya_country_destinations country="Nepal" limit="12"]
[ya_all_trips_filterable limit="24" category="trekking"]
[ya_category_treks category="trekking" limit="6" layout="grid"]
[ya_signature_journeys]
[ya_journey_finder]
[ya_nepal_map]
[ya_journal_posts]
[ya_home_notes]
[trek_destination id="123"]
```

---

## JavaScript Data (TREK_DATA)

On every `trek_destination` singular page, the plugin calls `wp_localize_script()` to inject `TREK_DATA` into the page — the output of `Trek_Renderer::get_full_trek_data()`.

The JS engine (`trek-engine.js`) reads this to render all sections client-side, supporting role-gated content visibility.

```js
// Available globally on destination pages:
console.log(TREK_DATA.hero.title);
console.log(TREK_DATA.keyFacts.difficulty);
console.log(TREK_DATA.itinerary);
console.log(TREK_DATA.pricing.formatted);

// User context:
console.log(PAGE_CONTEXT.userRole);   // 'public' | 'staff' | 'reviewer' | 'editor' | 'admin'
console.log(PAGE_CONTEXT.restUrl);
console.log(PAGE_CONTEXT.nonce);
```

---

## Import JSON Schema

See [class-trek-importer.php](#class-trek-importerphp) → Import JSON Keys section for the complete schema with all supported fields.

**To access the sample JSON**, go to:  
`WordPress Admin → Trek Destinations → Import / Export JSON`

The importer supports:
- **Create** new posts (if slug doesn't exist)
- **Update** existing posts (matched by slug)
- **Create Terms** — new categories/regions are auto-created when found in JSON
- **Difficulty taxonomy** — auto-created and assigned from `difficulty` field

---

## User Roles & Capabilities

| WordPress Role | Trek Role | Admin Capabilities |
|---|---|---|
| Administrator | `admin` | All 6 custom caps |
| Editor | `editor` | `manage_trek_content`, `edit_trek_destinations`, `review_trek_destinations` |
| Author | `staff` | Standard WP author caps only |
| Contributor | `reviewer` | Standard WP contributor caps only |
| Subscriber / Guest | `public` | Public frontend only |

---

## Data Save Flow

```
1. Admin edits trek_destination post
        ↓
2. save_meta_box_data($post_id)
   ├─ Nonce verification
   ├─ Save all text/select fields → wp_meta
   ├─ Save trek_category terms (multi-select checkboxes)
   ├─ Save trek_region terms (multi-select checkboxes)
   ├─ Sync trek_difficulty taxonomy from select value
   ├─ Save textarea fields
   ├─ Save WYSIWYG fields (wp_kses_post)
   ├─ Save overview inclusions list
   ├─ Save side card highlight points
   ├─ Save itinerary repeater → _trek_itinerary_list + _trek_itinerary
   ├─ Save inclusions repeater → _trek_inclusions_full + _trek_inclusions
   ├─ Save exclusions repeater → _trek_exclusions_full + _trek_exclusions
   ├─ Save gallery repeater → _trek_gallery_list + _trek_gallery_images
   ├─ Save extensions repeater → _trek_extensions_list
   ├─ Save essential repeater → _trek_essential_list + _trek_faqs
   ├─ Save reviews repeater → _trek_curated_reviews + _trek_reviews_list
   ├─ Rebuild _trek_quick_facts array
   └─ Trek_Renderer::get_full_trek_data($post_id) → _trek_structured_data

3. REST API / Frontend
   └─ GET /wp-json/trek-manager/v1/destinations/{id}
       ├─ Read _trek_structured_data (JSON string)
       ├─ json_decode()
       └─ Fallback: Trek_Renderer::get_full_trek_data() if missing
```

---

*Documentation generated: September 2026 · Trek Content Manager v1.0.2*
