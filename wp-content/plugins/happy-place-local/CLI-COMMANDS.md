# Happy Place Local - CLI Commands Reference

This document outlines all available WP-CLI commands for the Happy Place Local plugin.

## Available Commands


 Available CLI Commands:

  wp hpl:cities - Full Multi-Source Import

  - ✅ Google Places API
  - ✅ Wikipedia integration (descriptions, images)
  - ✅ Census API (population data)
  - ✅ Direct city post creation
  - Command: fetch

  wp hpl:places - Simple Places Pipeline

  - ✅ Google Places text search
  - ✅ AI enhancement pipeline
  - ✅ Complete automation
  - Command: clean_run

  wp hpl:agent - Pipeline Management

  - ✅ Process individual stages
  - ✅ Pipeline status and control
  - Commands: run, status, etc.

  wp hpl:ingest - Advanced Pipeline Tools

  - ✅ Reset pipeline
  - ✅ Re-import existing posts
  - ✅ Scrub city data
  - Commands: reset, reimport, scrub

  So for cities without AI enhancement, use:
  wp hpl:cities fetch --only=cities --limit=20

### `wp hpl:cities` - Import Cities and Local Places

Import cities and local places from various data sources with optional AI enhancement.

#### Basic Usage
```bash
wp hpl:cities fetch [--options]
```

#### Available Options

| Option | Description | Default | Example |
|--------|-------------|---------|---------|
| `--source` | Data source to use | `google` | `--source=google` |
| `--limit` | Maximum number of items to import | `100` | `--limit=50` |
| `--state` | State abbreviation for location filtering | `DE` | `--state=MD` |
| `--dry-run` | Preview what would be imported without saving | `false` | `--dry-run` |
| `--ai` | Route imports through AI enhancement pipeline | `false` | `--ai` |
| `--q` | Custom search query (when not using bounds) | | `--q="restaurants in Dover"` |

#### Examples

**Import 20 Delaware restaurants with AI enhancement:**
```bash
wp hpl:cities fetch --source=google --limit=20 --ai
```

**Preview Maryland businesses without importing:**
```bash
wp hpl:cities fetch --state=MD --limit=10 --dry-run
```

**Search for specific places:**
```bash
wp hpl:cities fetch --source=text --q="coffee shops in Wilmington DE" --limit=15
```

**Import without AI (direct publishing):**
```bash
wp hpl:cities fetch --limit=50
```

#### Data Sources

- **`google`** (default): Uses Google Places bounds search for high-quality, rated places
- **`text`**: Uses Google Places text search with custom queries

#### What Gets Imported

**Cities** (detected by Google types like 'locality', 'political'):
- Wikipedia data integration with state-specific disambiguation
- Census population data
- Geographic coordinates
- Hero images from Wikipedia

**Local Places** (detected by Google types like 'establishment', 'restaurant'):
- Google Places business data
- Ratings and reviews
- Contact information
- Operating hours
- Geographic coordinates
- Business categories

---

### `wp hpl:agent` - AI Processing Pipeline

Manually trigger the AI Agent pipeline to process imported data.

#### Available Subcommands

##### `wp hpl:agent run` - Process Pipeline Items
```bash
wp hpl:agent run [--options]
```

**Available Options:**

| Option | Description | Default | Example |
|--------|-------------|---------|---------|
| `--batch-size` | Number of items to process per run | `10` | `--batch-size=5` |
| `--stage` | Process only items at specific stage | `all` | `--stage=new` |
| `--force` | Reprocess items regardless of current stage | `false` | `--force` |

**Examples:**
```bash
# Process pending items
wp hpl:agent run

# Process only new items
wp hpl:agent run --stage=new

# Reprocess 5 items with force
wp hpl:agent run --batch-size=5 --force

# Process only items in classification stage
wp hpl:agent run --stage=classified
```

##### `wp hpl:agent status` - Show Pipeline Status
```bash
wp hpl:agent status
```

**Example Output:**
```
AI Agent Pipeline Status
========================
📋 new: 5 items
✓ classified: 0 items
✓ enriched: 0 items
📋 rewritten: 3 items
✓ scored: 0 items
📋 published: 12 items

Configuration Status
===================
Agent Enabled: ✅ Yes
LLM Provider: openai
LLM Connection: ✅ Working
```

#### AI Pipeline Stages

1. **`new`** → **`classified`**: LLM analyzes and categorizes content
2. **`classified`** → **`enriched`**: Deterministic data enhancement
3. **`enriched`** → **`rewritten`**: LLM creates local-friendly descriptions  
4. **`rewritten`** → **`scored`**: Content quality evaluation
5. **`scored`** → **`published`**: Auto-publish if score ≥ threshold

---

## Configuration Requirements

### API Keys Setup

Configure required API keys in **WordPress Admin → Happy Place → Local Places**:

1. **Google Places API Key** (required)
   - Get from [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
   - Enable Places API and Geocoding API

2. **LLM Provider** (required for `--ai` flag)
   - **OpenAI**: Requires API key and model selection
   - **Anthropic**: Requires API key (Claude models)
   - **Custom**: Any OpenAI-compatible endpoint

3. **Optional APIs**:
   - **Census API**: For population data
   - **Wikipedia**: No key required

### Search Bounds Configuration

Default search area is Delaware:
- **Southwest**: `38.451013, -75.788658`
- **Northeast**: `39.839007, -74.984165`

Modify in the admin configuration or update `config/sources.local.json`.

---

## Best Practices

### Import Strategy

1. **Start Small**: Use `--limit=10 --dry-run` to test configuration
2. **Use AI Enhancement**: Add `--ai` flag for better content quality
3. **Monitor Progress**: Check `/wp-admin/edit.php?post_type=hpl_ingest` for pipeline status
4. **State-Specific**: Always use `--state` for accurate Wikipedia disambiguation

### Error Handling

**Common Issues**:
- **Missing API Key**: Configure in admin settings
- **Wrong Wikipedia Data**: Ensure state parameter is correct
- **Out of Bounds Results**: Check search bounds configuration
- **AI Processing Fails**: Verify LLM provider configuration

**Troubleshooting Commands**:
```bash
# Test API connection
wp eval "echo 'API Test: '; var_dump((new \HappyPlace\Local\Services\GooglePlacesClient())->textSearch('test', null));"

# Check AI configuration
wp option get hpl_config

# View pipeline status
wp post list --post_type=hpl_ingest --meta_key=_hpl_stage --format=table
```

### Performance Optimization

- **Rate Limiting**: Built-in 1-second delays between API calls
- **Batch Processing**: Process large imports in smaller batches
- **Off-Peak Running**: Run during low-traffic periods for AI processing
- **Monitor Quotas**: Watch Google Places API usage

---

## Output Examples

### Successful Import
```
Upserted city #123: Dover
Queued local_place #124 for AI processing: Blue Coast Burrito
Upserted local_place #125: Dogfish Head Alehouse
```

### Dry Run Output
```
Would upsert city: Newark (DE) @ 39.677,-75.750
Would upsert local_place: Iron Hill Brewery [restaurant, food] @ 39.676,-75.749
```

### AI Pipeline Processing
```
Processing ingest item #126: Blue Coast Burrito
Stage: new → classified (confidence: 0.92)
Stage: classified → enriched
Stage: enriched → rewritten (150 words generated)
Stage: rewritten → scored (score: 85)
Stage: scored → published (local_place #127 created)
```

---

## Advanced Usage

### Cron Automation

Set up automated imports:
```bash
# Add to crontab
0 2 * * 0 /usr/local/bin/wp --path=/path/to/wordpress hpl:cities fetch --ai --limit=25

# Process AI queue hourly
0 * * * * /usr/local/bin/wp --path=/to/wordpress hpl:agent run
```

### Custom Configuration

Create `wp-content/plugins/happy-place-local/config/sources.local.json`:
```json
{
  "google": {
    "api_key": "your-api-key",
    "bounds": {
      "sw": [38.451013, -75.788658],
      "ne": [39.839007, -74.984165]
    },
    "types": ["locality", "establishment"]
  },
  "defaults": {
    "state": "DE"
  }
}
```

### Integration Hooks

Available WordPress hooks for customization:
```php
// Modify auto-publish threshold
add_filter('hpl/agent/auto_publish', function($auto, $score, $post_id, $data) {
    return $score >= 90; // Require 90% confidence
}, 10, 4);

// Custom enrichment
add_action('hpl/agent/enrich_item', function($post_id, $payload) {
    // Add custom data enhancement
}, 10, 2);
```

---

For more detailed information, see the plugin's admin configuration page at **Happy Place → Local Places** in your WordPress dashboard.