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
    class="hph-search-results" 
    id="<?php echo esc_attr($container_id); ?>"
    data-input-id="<?php echo esc_attr($input_id); ?>"
    data-max-suggestions="<?php echo esc_attr($max_suggestions); ?>"
    style="display: none;"
>
    <div class="hph-search-results-content" role="listbox" id="<?php echo esc_attr($container_id); ?>-list">
        <!-- Suggestions will be populated by JavaScript -->
    </div>
    
    <!-- Loading indicator -->
    <div class="hph-search-loading" style="display: none;">
        <span><?php _e('Searching...', 'happy-place-theme'); ?></span>
    </div>
    
    <!-- No results message -->
    <div class="hph-search-no-results" style="display: none;">
        <span><?php _e('No suggestions found', 'happy-place-theme'); ?></span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const autocompleteContainer = document.getElementById('<?php echo esc_js($container_id); ?>');
    const searchInput = document.getElementById('<?php echo esc_js($input_id); ?>');
    const suggestionsList = autocompleteContainer.querySelector('.hph-search-results-content');
    const loadingIndicator = autocompleteContainer.querySelector('.hph-search-loading');
    const noResultsMessage = autocompleteContainer.querySelector('.hph-search-no-results');
    
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

        // Always show fallback suggestions first for immediate feedback
        const fallbackSuggestions = generateFallbackSuggestions(query);
        suggestions = fallbackSuggestions;
        displaySuggestions(fallbackSuggestions);

        // Then try to load enhanced suggestions via AJAX
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

                if (suggestions.length > 0) {
                    displaySuggestions(suggestions);
                } else {
                    // If no real suggestions found, generate fallbacks
                    const fallbackSuggestions = generateFallbackSuggestions(query);
                    suggestions = fallbackSuggestions;
                    displaySuggestions(fallbackSuggestions);
                }
            } else {

                // Even on error, show fallback suggestions
                const fallbackSuggestions = generateFallbackSuggestions(query);
                suggestions = fallbackSuggestions;
                displaySuggestions(fallbackSuggestions);
            }
        } catch (error) {
            
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
            const li = document.createElement('a');
            li.className = 'hph-search-result-item listing-result';
            li.setAttribute('role', 'option');
            li.setAttribute('data-index', index);
            li.setAttribute('href', suggestion.url || '#');
            
            // Build suggestion HTML using new CSS structure
            let iconHtml = '<i class="fas fa-home"></i>';
            switch (suggestion.type) {
                case 'listing':
                    iconHtml = '<i class="fas fa-home"></i>';
                    break;
                case 'agent':
                    iconHtml = '<i class="fas fa-user"></i>';
                    break;
                case 'city':
                    iconHtml = '<i class="fas fa-map-marker-alt"></i>';
                    break;
                case 'community':
                    iconHtml = '<i class="fas fa-building"></i>';
                    break;
                default:
                    iconHtml = '<i class="fas fa-search"></i>';
            }
            
            // Use the new CSS structure that matches the screenshot
            if (suggestion.type === 'listing') {
                li.innerHTML = `
                    <div class="hph-search-result-icon">${iconHtml}</div>
                    <div class="hph-search-result-content">
                        <div class="hph-search-result-price">${suggestion.price || ''}</div>
                        <div class="hph-search-result-address">${highlightMatch(suggestion.title, currentQuery)}</div>
                        <div class="hph-search-result-details">${suggestion.subtitle || ''}</div>
                    </div>
                `;
            } else {
                li.innerHTML = `
                    <div class="hph-search-result-icon">${iconHtml}</div>
                    <div class="hph-search-result-content">
                        <div class="hph-search-result-title">${highlightMatch(suggestion.title, currentQuery)}</div>
                        <div class="hph-search-result-meta">${suggestion.subtitle || ''}</div>
                    </div>
                `;
            }
            
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

        // Get the listing archive URL dynamically
        const listingArchiveUrl = '<?php echo esc_js(home_url("/listings/")); ?>';

        // Primary search suggestion - always include this
        suggestions.push({
            title: `Search "${query}" in Listings`,
            subtitle: 'Find all matching properties',
            type: 'listing',
            type_label: 'Search',
            query: query,
            price: '',
            url: `${listingArchiveUrl}?s=${encodeURIComponent(query)}&post_type=listing`
        });

        // Add "Browse All Listings" if query is short
        if (query.length <= 3) {
            suggestions.push({
                title: 'Browse All Listings',
                subtitle: 'View all available properties',
                type: 'listing',
                type_label: 'Browse',
                query: '',
                price: '',
                url: listingArchiveUrl
            });
        }

        // Location-based suggestions (prioritized)
        if (queryLower.length >= 2) {
            const cities = ['Rehoboth Beach', 'Bethany Beach', 'Lewes', 'Millsboro', 'Ocean City', 'Fenwick Island', 'Selbyville'];
            cities.forEach(city => {
                if (city.toLowerCase().includes(queryLower)) {
                    suggestions.push({
                        title: `Properties in ${city}`,
                        subtitle: 'Browse all listings',
                        type: 'city',
                        type_label: 'Location',
                        query: city,
                        url: `${listingArchiveUrl}?s=${encodeURIComponent(city)}&post_type=listing`
                    });
                }
            });
        }

        // Property type suggestions - only if query could be property related
        const propertyKeywords = ['house', 'home', 'condo', 'townhouse', 'apartment', 'property', 'listing'];
        const hasPropertyKeyword = propertyKeywords.some(keyword => queryLower.includes(keyword) || keyword.includes(queryLower));

        if (hasPropertyKeyword || queryLower.length <= 3) {
            const propertyTypes = [
                { title: `${query} Single Family Homes`, subtitle: 'Houses and single-family properties', type: 'listing', query: query + ' single family' },
                { title: `${query} Condos`, subtitle: 'Condominium properties', type: 'listing', query: query + ' condo' },
                { title: `${query} Townhouses`, subtitle: 'Townhome properties', type: 'listing', query: query + ' townhouse' }
            ];

            propertyTypes.forEach(propType => {
                if (suggestions.length < config.maxSuggestions) {
                    suggestions.push({
                        ...propType,
                        url: `${listingArchiveUrl}?s=${encodeURIComponent(propType.query)}&post_type=listing`
                    });
                }
            });
        }

        // Price range suggestions for numeric queries
        if (/^\d+/.test(query)) {
            const basePrice = parseInt(query);
            if (basePrice >= 100000) {
                suggestions.push({
                    title: `Properties under $${Math.ceil(basePrice/1000)}K`,
                    subtitle: 'Browse by price range',
                    type: 'listing',
                    type_label: 'Price Range',
                    query: query,
                    url: `${listingArchiveUrl}?max_price=${basePrice}&post_type=listing`
                });
            }
        }

        // Limit to max suggestions
        return suggestions.slice(0, config.maxSuggestions);
    }
    
    function selectSuggestion(suggestion) {
        searchInput.value = suggestion.query || suggestion.title;
        hideAutocomplete();
        
        // Submit the form or navigate to URL
        if (suggestion.url) {
            window.location.href = suggestion.url;
        } else {
            const form = searchInput.closest('form');
            if (form) {
                form.submit();
            }
        }
        
        // Save to recent searches if enabled
        if (config.enableRecent) {
            saveRecentSearch(suggestion);
        }
    }
    
    function updateSelection() {
        const items = suggestionsList.querySelectorAll('.hph-search-result-item');
        items.forEach((item, index) => {
            item.classList.toggle('highlighted', index === selectedIndex);
        });
    }
    
    function showAutocomplete() {
        autocompleteContainer.classList.add('active');
        autocompleteContainer.style.display = 'block';
        noResultsMessage.style.display = 'none';
    }
    
    function hideAutocomplete() {
        autocompleteContainer.classList.remove('active');
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
            // Failed to save recent search
        }
    }
});
</script>

<!-- Old styles removed - now using HPH CSS framework search styles -->
