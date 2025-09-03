<?php
/**
 * Search Autocomplete Component
 * 
 * Provides intelligent search suggestions as users type
 * Supports multiple post types and recent searches
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 */

$config = $args ?? [];
$input_id = $config['input_id'] ?? 'search_query';
$container_id = $config['container_id'] ?? 'search-autocomplete';
$post_types = $config['post_types'] ?? ['listing', 'agent', 'city', 'community'];
$max_suggestions = $config['max_suggestions'] ?? 8;
$enable_recent = $config['enable_recent'] ?? true;
$enable_popular = $config['enable_popular'] ?? true;
?>

<div 
    class="hph-search-autocomplete" 
    id="<?php echo esc_attr($container_id); ?>"
    data-input-id="<?php echo esc_attr($input_id); ?>"
    data-max-suggestions="<?php echo esc_attr($max_suggestions); ?>"
    style="display: none;"
>
    <ul class="autocomplete-list" role="listbox" id="<?php echo esc_attr($container_id); ?>-list">
        <!-- Suggestions will be populated by JavaScript -->
    </ul>
    
    <!-- Loading indicator -->
    <div class="autocomplete-loading" style="display: none;">
        <div class="loading-item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="spin">
                <path d="M8 0a8 8 0 1 1 0 16v-2a6 6 0 1 0 0-12V0z"/>
            </svg>
            <span><?php _e('Searching...', 'happy-place-theme'); ?></span>
        </div>
    </div>
    
    <!-- No results message -->
    <div class="autocomplete-no-results" style="display: none;">
        <div class="no-results-item">
            <span><?php _e('No suggestions found', 'happy-place-theme'); ?></span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const autocompleteContainer = document.getElementById('<?php echo esc_js($container_id); ?>');
    const searchInput = document.getElementById('<?php echo esc_js($input_id); ?>');
    const suggestionsList = autocompleteContainer.querySelector('.autocomplete-list');
    const loadingIndicator = autocompleteContainer.querySelector('.autocomplete-loading');
    const noResultsMessage = autocompleteContainer.querySelector('.autocomplete-no-results');
    
    let currentQuery = '';
    let searchTimeout;
    let selectedIndex = -1;
    let suggestions = [];
    
    // Configuration
    const config = {
        maxSuggestions: parseInt(autocompleteContainer.dataset.maxSuggestions),
        minQueryLength: 2,
        debounceDelay: 300,
        enableRecent: <?php echo json_encode($enable_recent); ?>,
        enablePopular: <?php echo json_encode($enable_popular); ?>,
        postTypes: <?php echo json_encode($post_types); ?>
    };
    
    // Initialize autocomplete
    if (searchInput && autocompleteContainer) {
        searchInput.addEventListener('input', handleInput);
        searchInput.addEventListener('keydown', handleKeydown);
        searchInput.addEventListener('focus', handleFocus);
        searchInput.addEventListener('blur', handleBlur);
        document.addEventListener('click', handleDocumentClick);
    }
    
    function handleInput(e) {
        const query = e.target.value.trim();
        
        if (query === currentQuery) return;
        currentQuery = query;
        
        clearTimeout(searchTimeout);
        selectedIndex = -1;
        
        if (query.length < config.minQueryLength) {
            hideAutocomplete();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchSuggestions(query);
        }, config.debounceDelay);
    }
    
    function handleKeydown(e) {
        if (!autocompleteContainer.style.display || autocompleteContainer.style.display === 'none') {
            return;
        }
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, suggestions.length - 1);
                updateSelection();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && suggestions[selectedIndex]) {
                    selectSuggestion(suggestions[selectedIndex]);
                } else {
                    // Submit the form with current input value
                    const form = searchInput.closest('form');
                    if (form) form.submit();
                }
                break;
                
            case 'Escape':
                hideAutocomplete();
                searchInput.blur();
                break;
        }
    }
    
    function handleFocus() {
        if (currentQuery.length >= config.minQueryLength && suggestions.length > 0) {
            showAutocomplete();
        }
    }
    
    function handleBlur(e) {
        // Small delay to allow click events on suggestions
        setTimeout(() => {
            hideAutocomplete();
        }, 150);
    }
    
    function handleDocumentClick(e) {
        if (!autocompleteContainer.contains(e.target) && e.target !== searchInput) {
            hideAutocomplete();
        }
    }
    
    async function fetchSuggestions(query) {
        showLoading();
        
        try {
            // Create request data
            const requestData = {
                action: 'hpt_search_autocomplete',
                query: query,
                post_types: config.postTypes,
                max_results: config.maxSuggestions,
                nonce: '<?php echo wp_create_nonce('search_autocomplete_nonce'); ?>'
            };
            
            // Make AJAX request
            const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(requestData)
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.success) {
                suggestions = data.data.suggestions || [];
                displaySuggestions(suggestions);
            } else {
                console.error('Autocomplete error:', data.data?.message || 'Unknown error');
                showNoResults();
            }
        } catch (error) {
            console.error('Autocomplete request failed:', error);
            
            // Fallback to static suggestions
            const fallbackSuggestions = generateFallbackSuggestions(query);
            suggestions = fallbackSuggestions;
            displaySuggestions(fallbackSuggestions);
        }
        
        hideLoading();
    }
    
    function displaySuggestions(suggestionList) {
        suggestionsList.innerHTML = '';
        
        if (suggestionList.length === 0) {
            showNoResults();
            return;
        }
        
        suggestionList.forEach((suggestion, index) => {
            const li = document.createElement('li');
            li.className = 'autocomplete-item';
            li.setAttribute('role', 'option');
            li.setAttribute('data-index', index);
            
            // Build suggestion HTML
            let iconHtml = '';
            switch (suggestion.type) {
                case 'listing':
                    iconHtml = '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/></svg>';
                    break;
                case 'agent':
                    iconHtml = '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 1a6 6 0 0 0-6 6v1h12v-1a6 6 0 0 0-6-6z"/></svg>';
                    break;
                case 'city':
                    iconHtml = '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"/><path d="M3 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3v-3.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V16h3a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1H3z"/></svg>';
                    break;
                case 'community':
                    iconHtml = '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/><path d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/></svg>';
                    break;
                default:
                    iconHtml = '<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>';
            }
            
            li.innerHTML = `
                <div class="suggestion-icon">${iconHtml}</div>
                <div class="suggestion-content">
                    <div class="suggestion-title">${highlightMatch(suggestion.title, currentQuery)}</div>
                    ${suggestion.subtitle ? `<div class="suggestion-subtitle">${suggestion.subtitle}</div>` : ''}
                </div>
                <div class="suggestion-type">${suggestion.type_label || suggestion.type}</div>
            `;
            
            li.addEventListener('click', () => selectSuggestion(suggestion));
            li.addEventListener('mouseenter', () => {
                selectedIndex = index;
                updateSelection();
            });
            
            suggestionsList.appendChild(li);
        });
        
        showAutocomplete();
    }
    
    function generateFallbackSuggestions(query) {
        // Generate basic suggestions based on query
        const suggestions = [];
        const queryLower = query.toLowerCase();
        
        // Property type suggestions
        const propertyTypes = [
            { title: 'Single Family Homes', type: 'listing', query: query + ' single family' },
            { title: 'Condos', type: 'listing', query: query + ' condo' },
            { title: 'Townhouses', type: 'listing', query: query + ' townhouse' }
        ];
        
        // Location-based suggestions
        if (queryLower.length >= 2) {
            const cities = ['Rehoboth Beach', 'Bethany Beach', 'Lewes', 'Millsboro'];
            cities.forEach(city => {
                if (city.toLowerCase().includes(queryLower)) {
                    suggestions.push({
                        title: city,
                        subtitle: 'Delaware',
                        type: 'city',
                        type_label: 'City',
                        query: city
                    });
                }
            });
        }
        
        return suggestions.slice(0, config.maxSuggestions);
    }
    
    function selectSuggestion(suggestion) {
        searchInput.value = suggestion.query || suggestion.title;
        hideAutocomplete();
        
        // Submit the form or trigger search
        const form = searchInput.closest('form');
        if (form) {
            form.submit();
        }
        
        // Save to recent searches if enabled
        if (config.enableRecent) {
            saveRecentSearch(suggestion);
        }
    }
    
    function updateSelection() {
        const items = suggestionsList.querySelectorAll('.autocomplete-item');
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === selectedIndex);
        });
    }
    
    function showAutocomplete() {
        autocompleteContainer.style.display = 'block';
        noResultsMessage.style.display = 'none';
    }
    
    function hideAutocomplete() {
        autocompleteContainer.style.display = 'none';
        selectedIndex = -1;
    }
    
    function showLoading() {
        loadingIndicator.style.display = 'block';
        noResultsMessage.style.display = 'none';
        suggestionsList.innerHTML = '';
        showAutocomplete();
    }
    
    function hideLoading() {
        loadingIndicator.style.display = 'none';
    }
    
    function showNoResults() {
        suggestionsList.innerHTML = '';
        noResultsMessage.style.display = 'block';
        showAutocomplete();
    }
    
    function highlightMatch(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    function saveRecentSearch(suggestion) {
        try {
            const recentSearches = JSON.parse(localStorage.getItem('hpt_recent_searches') || '[]');
            const newSearch = {
                title: suggestion.title,
                type: suggestion.type,
                query: suggestion.query || suggestion.title,
                timestamp: Date.now()
            };
            
            // Remove duplicates and add to beginning
            const filtered = recentSearches.filter(search => search.query !== newSearch.query);
            filtered.unshift(newSearch);
            
            // Keep only last 10 searches
            const limited = filtered.slice(0, 10);
            
            localStorage.setItem('hpt_recent_searches', JSON.stringify(limited));
        } catch (error) {
            console.error('Failed to save recent search:', error);
        }
    }
});
</script>

<style>
.hph-search-autocomplete {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-height: 400px;
    overflow-y: auto;
}

.autocomplete-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.autocomplete-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #f1f5f9;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item.selected {
    background-color: #f8fafc;
}

.suggestion-icon {
    flex-shrink: 0;
    width: 16px;
    height: 16px;
    margin-right: 12px;
    opacity: 0.7;
}

.suggestion-content {
    flex: 1;
    min-width: 0;
}

.suggestion-title {
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 2px;
}

.suggestion-title strong {
    background-color: #fef3c7;
    color: #92400e;
    padding: 0 2px;
    border-radius: 2px;
}

.suggestion-subtitle {
    font-size: 14px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.suggestion-type {
    flex-shrink: 0;
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 12px;
}

.autocomplete-loading,
.autocomplete-no-results {
    padding: 16px;
    text-align: center;
    color: #64748b;
}

.loading-item,
.no-results-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>