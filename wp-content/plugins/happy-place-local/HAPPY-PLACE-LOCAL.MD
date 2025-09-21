📦 Claude Code Prompt — “Happy Place Local” Setup

You are an expert WordPress and PHP engineer. Generate production-ready code to set up and wire the “Happy Place Local” add-on inside wp-content/plugins/happy-place-local/. Follow every requirement precisely.

🎯 Goals

Ensure CPTs (local_place, local_event) + City CPT fields are wired via ACF JSON (Local JSON).

Add ACF JSON for City, Place, and Event (with relationships).

Add Cities ingest system (Google/Wikipedia/Census) + wp hpl:cities CLI command.

Add an AI Agent pipeline (Classify → Enrich → Rewrite → Score → Publish) with filter-based LLM adapters.

Keep everything namespaced under HappyPlace\Local\… and compatible with our PSR-4 autoloader.

Deliver a single cohesive output: a file tree, code blocks for each file, and a final README and test script.

✅ Environment & Constraints

WordPress 6.x, PHP 8.1+ (compatible with 7.4 where easy).

Use PSR-4 autoload convention already present in happy-place-local.php:

Namespace prefix: HappyPlace\Local\

File path: includes/<NamespacePath>.php

No external composer packages. Use WordPress HTTP APIs only.

Security: sanitize all input, escape output, use nonces in admin UIs.

Performance: no heavy queries; add small sleeps for Google pagination; cache where trivial.

Licensing/ToS: add comments re: attribution for Wikipedia/Wikidata and ToS note for Google Places.

📁 File Tree to Produce (exact paths)
happy-place-local/
├─ happy-place-local.php                      (keep our PSR-4 autoloader; call Agent bootstrap)
├─ config/
│  └─ sources.local.example.json              (keys + bounds template)
├─ acf-json/
│  ├─ hpl-city-fields.json
│  ├─ hpl-place-fields.json
│  └─ hpl-event-fields.json
├─ includes/
│  ├─ LocalServiceProvider.php                (wire CPTs, fields JSON paths, templates, shortcodes)
│  ├─ CPT/
│  │  ├─ LocalPlace.php
│  │  └─ Event.php
│  ├─ Admin/
│  │  ├─ Columns.php
│  │  ├─ EventMeta.php                        (fallback meta boxes if ACF missing)
│  │  └─ PlaceMeta.php                        (fallback meta boxes if ACF missing)
│  ├─ Relations/
│  │  └─ BindCity.php
│  ├─ Frontend/
│  │  ├─ Assets.php
│  │  ├─ Templates.php
│  │  └─ ThisWeekendShortcode.php
│  ├─ Services/
│  │  ├─ GooglePlacesClient.php
│  │  ├─ WikiClient.php
│  │  ├─ CensusClient.php
│  │  └─ CityWriter.php
│  ├─ CLI/
│  │  └─ CitiesFetch.php                       (registers WP-CLI command: wp hpl:cities)
│  └─ Agent/
│     ├─ Bootstrap.php                         (register CPT hpl_ingest, cron, CLI)
│     ├─ Runner.php
│     ├─ CLI.php
│     ├─ LLM.php                               (filter-based adapters)
│     ├─ IngestStore.php
│     └─ Tasks/
│        ├─ Classify.php
│        ├─ Enrich.php
│        ├─ Rewrite.php
│        ├─ Score.php
│        └─ Publish.php
├─ assets/
│  └─ css/frontend.css
├─ templates/
│  ├─ archive-local_event.php
│  └─ parts/card-event.php
└─ README-HAPPY-PLACE-LOCAL.md

🧩 Coding Requirements (apply everywhere)

Namespaces: <?php namespace HappyPlace\Local\…;

CPTs: public, show_in_rest => true, supports = title/editor/excerpt/thumbnail/custom-fields.

Meta (Events): register REST-visible meta keys: start_datetime, end_datetime, price, source_url, hpl_city_slug, hpl_city_id.

Shortcode: [hpl_this_weekend city="georgetown" limit="6"] (Fri 00:00 → Sun 23:59 in site TZ).

ACF JSON:

City: state (required), county, population, lat, lng, tagline, description (WYSIWYG), hero image, gallery, external links (repeater), plus relationships: related_places, featured_places (to local_place), upcoming_events (to local_event), featured_stories (to local_story), featured_deals (to local_deal).

Place: primary_city (post_object→city), related_places (relationship→local_place[]), address, website, phone, price_range ($–$$$$), is_family_friendly, accessibility (checkboxes), lat/lng, hours_json, source_url, attribution.

Event: primary_city (post_object→city), related_places (relationship→local_place[]), start/end (datetime → ISO8601), is_free, price, age_min, tickets_url, source_url, organizer_name, venue_name, venue_address, lat/lng, categories (checkboxes), attribution.

Relations/Denorm: on save, if ACF primary_city exists, write-through hpl_city_slug + hpl_city_id.

Cities Ingest:

GooglePlacesClient: nearbyByBounds(sw, ne, types[]), textSearch(query), details(place_id), pager-aware.

WikiClient: summary(title) (extract, image, wikidata id), wikidataPopulation(id).

CensusClient: rough placePopulation(name, stateAbbr) using ACS 5-year (B01003_001E) with a state→FIPS map.

CityWriter: upsert(payload); set ACF/meta; sideload hero; build external links rows.

CLI command wp hpl:cities with options: --source=google|query, --q=, --state=DE, --limit=100, --dry-run.

Config file template at config/sources.local.example.json with placeholder keys and bounds.

Agent:

Register hpl_ingest CPT (private, show_ui true under Tools).

Cron schedule every 10 minutes: action hpl/agent/tick.

WP-CLI: wp hpl:agent triggers one loop.

Pipeline:

Classify → LLM::json_call() returns {city_slug, places[], tags[], is_free, confidence}.

Enrich fires do_action('hpl/agent/enrich_item', $post_id, $payload) for deterministic enrichers.

Rewrite → LLM::text_call() (<=120 words, markdown, calm tone).

Score computes a simple score with filter hpl/agent/score.

Publish if score≥80 (filterable) → create local_event draft with key meta.

LLM adapters are not vendor-locked; expose two filters:

hpl/llm/json_call → return PHP array.

hpl/llm/text_call → return string.

Frontend:

Minimal card CSS in assets/css/frontend.css.

Archive template for local_event uses the shortcode.

Admin:

Columns for Events: City / Start / Price. Places: Lat/Lng.

Fallback meta boxes if ACF is absent (Event & Place).

Docs: A README with setup, commands, and test checklist.

🧪 Acceptance Tests (Claude must include)

After plugin activation:

Visiting /events/ renders an events archive (grid).

[hpl_this_weekend] lists only events within this Fri–Sun.

ACF JSON is detected in “Custom Fields → Field Groups,” with City/Place/Event groups present and fields matching spec.

wp hpl:cities --source=google --limit=3 --dry-run logs intended upserts; without --dry-run, creates City posts with description and optional hero.

wp hpl:agent creates an hpl_ingest item (from a sample fetch hook), moves it through stages, and produces a local_event draft (when scoring passes).

LLM filter stubs are present and documented with examples for OpenAI and Anthropic.

📤 Output Format (very important)

Start with a brief plan (bulleted).

Then present each file with a heading (path) and a fenced code block with its full contents.

Include the config example JSON.

Include the ACF JSON files as raw JSON blocks.

End with:

“README-HAPPY-PLACE-LOCAL.md” full content,

a “QUICK TEST SCRIPT” section with copy-pastable WP-CLI commands and expected outcomes.

📝 Notes to Observe

Don’t invent plugin headers we didn’t ask for; use the existing plugin name/comment style.

Where you reference API keys, use placeholders and comments.

Keep the Google requests polite (sleep(1) paging, sleep(2) for next_page_token).

Escape/nonce admin meta boxes, sanitize all inputs, and keep REST meta readable.

Zero external dependencies.

When you’re ready, generate the full set exactly as requested.