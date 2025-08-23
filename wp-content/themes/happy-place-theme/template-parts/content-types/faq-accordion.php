<?php
/**
 * FAQ Accordion Content Type
 * 
 * Displays frequently asked questions in an accordion format
 * 
 * @package HappyPlaceTheme
 * @since 3.0.0
 * 
 * Args:
 * - faqs: array of FAQ objects with 'question', 'answer', 'category'
 * - allow_multiple_open: boolean
 * - search_enabled: boolean
 * - headline: string (optional)
 * - subheadline: string (optional)
 * - content: string (optional)
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

$faqs = $args['faqs'] ?? array();
$allow_multiple_open = $args['allow_multiple_open'] ?? false;
$search_enabled = $args['search_enabled'] ?? false;
$headline = $args['headline'] ?? '';
$subheadline = $args['subheadline'] ?? '';
$content = $args['content'] ?? '';

if (empty($faqs)) {
    return;
}

// Generate unique ID for this FAQ section
$faq_id = 'faq-' . uniqid();
?>

<div class="hph-faq-container" id="<?php echo esc_attr($faq_id); ?>">
    
    <?php if ($headline || $subheadline || $content): ?>
    <!-- FAQ Header -->
    <div class="hph-faq-header hph-text-center hph-mb-2xl">
        <?php if ($headline): ?>
        <h2 class="hph-faq-headline hph-text-3xl hph-font-bold hph-text-primary hph-mb-lg">
            <?php echo esc_html($headline); ?>
        </h2>
        <?php endif; ?>
        
        <?php if ($subheadline): ?>
        <h3 class="hph-faq-subheadline hph-text-lg hph-text-gray-600 hph-mb-lg">
            <?php echo esc_html($subheadline); ?>
        </h3>
        <?php endif; ?>
        
        <?php if ($content): ?>
        <div class="hph-faq-intro hph-text-base hph-text-gray-700 hph-max-w-3xl hph-mx-auto">
            <?php echo wp_kses_post($content); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($search_enabled): ?>
    <!-- FAQ Search -->
    <div class="hph-faq-search hph-mb-xl">
        <div class="hph-search-wrapper hph-relative hph-max-w-md hph-mx-auto">
            <input type="text" 
                   id="<?php echo esc_attr($faq_id); ?>-search"
                   class="hph-search-input hph-w-full hph-px-lg hph-py-md hph-border hph-border-gray-300 hph-rounded-lg hph-focus:border-primary hph-focus:ring-2 hph-focus:ring-primary-100"
                   placeholder="Search FAQs...">
            <div class="hph-search-icon hph-absolute hph-right-md hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-gray-400">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="hph-search-results hph-mt-sm hph-text-center hph-text-sm hph-text-gray-500" style="display: none;">
            <span class="hph-results-count">0</span> results found
        </div>
    </div>
    <?php endif; ?>
    
    <!-- FAQ Accordion -->
    <div class="hph-faq-accordion" 
         data-allow-multiple="<?php echo $allow_multiple_open ? 'true' : 'false'; ?>">
        
        <?php foreach ($faqs as $index => $faq): 
            $question = $faq['question'] ?? '';
            $answer = $faq['answer'] ?? '';
            $category = $faq['category'] ?? '';
            $faq_item_id = $faq_id . '-item-' . $index;
        ?>
        
        <div class="hph-faq-item" 
             data-category="<?php echo esc_attr($category); ?>"
             data-question="<?php echo esc_attr(strtolower($question)); ?>"
             data-answer="<?php echo esc_attr(strtolower(strip_tags($answer))); ?>">
            
            <button class="hph-faq-question" 
                    type="button"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr($faq_item_id); ?>"
                    data-faq-toggle>
                
                <span class="hph-question-text">
                    <?php echo esc_html($question); ?>
                </span>
                
                <span class="hph-question-icon">
                    <i class="fas fa-plus hph-icon-plus"></i>
                    <i class="fas fa-minus hph-icon-minus"></i>
                </span>
            </button>
            
            <div class="hph-faq-answer" 
                 id="<?php echo esc_attr($faq_item_id); ?>"
                 aria-hidden="true">
                <div class="hph-answer-content">
                    <?php echo wp_kses_post($answer); ?>
                </div>
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>
    
    <?php if ($search_enabled): ?>
    <!-- No Results Message -->
    <div class="hph-no-results hph-text-center hph-py-2xl" style="display: none;">
        <i class="fas fa-search hph-text-4xl hph-text-gray-300 hph-mb-lg"></i>
        <h3 class="hph-text-xl hph-font-semibold hph-text-gray-600 hph-mb-md">No results found</h3>
        <p class="hph-text-gray-500">Try searching with different keywords or <button class="hph-clear-search hph-text-primary hph-underline">clear your search</button>.</p>
    </div>
    <?php endif; ?>
    
</div>

<style>
/* FAQ Accordion Styles */
.hph-faq-container {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
}

.hph-faq-accordion {
    background: var(--hph-white);
    border-radius: var(--hph-radius-lg);
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.hph-faq-item {
    border-bottom: 1px solid var(--hph-gray-100);
}

.hph-faq-item:last-child {
    border-bottom: none;
}

.hph-faq-item.hidden {
    display: none;
}

.hph-faq-question {
    width: 100%;
    padding: var(--hph-space-lg) var(--hph-space-xl);
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
}

.hph-faq-question:hover {
    background: var(--hph-gray-50);
}

.hph-faq-question:focus {
    outline: 2px solid var(--hph-primary);
    outline-offset: -2px;
}

.hph-faq-question[aria-expanded="true"] {
    background: var(--hph-primary-50);
    color: var(--hph-primary-700);
}

.hph-question-text {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--hph-gray-900);
    line-height: 1.4;
    padding-right: var(--hph-space-md);
}

.hph-faq-question[aria-expanded="true"] .hph-question-text {
    color: var(--hph-primary-700);
}

.hph-question-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--hph-primary-100);
    color: var(--hph-primary);
    transition: all 0.3s ease;
    position: relative;
}

.hph-faq-question[aria-expanded="true"] .hph-question-icon {
    background: var(--hph-primary);
    color: var(--hph-white);
    transform: rotate(180deg);
}

.hph-icon-plus,
.hph-icon-minus {
    position: absolute;
    transition: all 0.3s ease;
}

.hph-icon-minus {
    opacity: 0;
    transform: rotate(90deg);
}

.hph-faq-question[aria-expanded="true"] .hph-icon-plus {
    opacity: 0;
    transform: rotate(90deg);
}

.hph-faq-question[aria-expanded="true"] .hph-icon-minus {
    opacity: 1;
    transform: rotate(0deg);
}

.hph-faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

.hph-faq-answer.open {
    max-height: 500px;
    padding: 0 var(--hph-space-xl) var(--hph-space-lg);
}

.hph-answer-content {
    color: var(--hph-gray-700);
    line-height: 1.6;
    font-size: 1rem;
}

.hph-answer-content p {
    margin-bottom: var(--hph-space-md);
}

.hph-answer-content p:last-child {
    margin-bottom: 0;
}

.hph-answer-content ul,
.hph-answer-content ol {
    margin: var(--hph-space-md) 0;
    padding-left: var(--hph-space-lg);
}

.hph-answer-content li {
    margin-bottom: var(--hph-space-sm);
}

/* Search Styles */
.hph-search-wrapper {
    position: relative;
}

.hph-search-input {
    padding-right: 3rem;
}

.hph-search-input:focus {
    outline: none;
    border-color: var(--hph-primary);
    box-shadow: 0 0 0 3px var(--hph-primary-100);
}

.hph-search-icon {
    pointer-events: none;
}

.hph-clear-search {
    background: none;
    border: none;
    cursor: pointer;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hph-faq-question {
        padding: var(--hph-space-md);
    }
    
    .hph-question-text {
        font-size: 1rem;
    }
    
    .hph-faq-answer.open {
        padding: 0 var(--hph-space-md) var(--hph-space-md);
    }
    
    .hph-faq-header {
        text-align: left;
    }
}

/* Animation for search highlighting */
.hph-faq-item.highlighted {
    animation: highlight 0.6s ease;
}

@keyframes highlight {
    0% { background: var(--hph-primary-100); }
    100% { background: transparent; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqContainer = document.getElementById('<?php echo esc_js($faq_id); ?>');
    if (!faqContainer) return;
    
    const accordion = faqContainer.querySelector('.hph-faq-accordion');
    const allowMultiple = accordion.dataset.allowMultiple === 'true';
    const searchInput = faqContainer.querySelector('#<?php echo esc_js($faq_id); ?>-search');
    const faqItems = faqContainer.querySelectorAll('.hph-faq-item');
    const noResults = faqContainer.querySelector('.hph-no-results');
    const resultsCount = faqContainer.querySelector('.hph-results-count');
    const searchResults = faqContainer.querySelector('.hph-search-results');
    const clearSearchBtn = faqContainer.querySelector('.hph-clear-search');
    
    // Accordion functionality
    accordion.addEventListener('click', function(e) {
        const toggle = e.target.closest('[data-faq-toggle]');
        if (!toggle) return;
        
        e.preventDefault();
        
        const item = toggle.closest('.hph-faq-item');
        const answer = item.querySelector('.hph-faq-answer');
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        
        if (!allowMultiple) {
            // Close all other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    const otherToggle = otherItem.querySelector('[data-faq-toggle]');
                    const otherAnswer = otherItem.querySelector('.hph-faq-answer');
                    
                    otherToggle.setAttribute('aria-expanded', 'false');
                    otherAnswer.setAttribute('aria-hidden', 'true');
                    otherAnswer.classList.remove('open');
                }
            });
        }
        
        // Toggle current item
        if (isOpen) {
            toggle.setAttribute('aria-expanded', 'false');
            answer.setAttribute('aria-hidden', 'true');
            answer.classList.remove('open');
        } else {
            toggle.setAttribute('aria-expanded', 'true');
            answer.setAttribute('aria-hidden', 'false');
            answer.classList.add('open');
        }
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query === '') {
                showAllFAQs();
                return;
            }
            
            let visibleCount = 0;
            
            faqItems.forEach(item => {
                const question = item.dataset.question || '';
                const answer = item.dataset.answer || '';
                
                if (question.includes(query) || answer.includes(query)) {
                    item.classList.remove('hidden');
                    item.classList.add('highlighted');
                    setTimeout(() => item.classList.remove('highlighted'), 600);
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            
            updateSearchResults(visibleCount, query);
        });
        
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                showAllFAQs();
            });
        }
    }
    
    function showAllFAQs() {
        faqItems.forEach(item => {
            item.classList.remove('hidden', 'highlighted');
        });
        
        if (noResults) noResults.style.display = 'none';
        if (searchResults) searchResults.style.display = 'none';
    }
    
    function updateSearchResults(count, query) {
        if (resultsCount) resultsCount.textContent = count;
        
        if (count === 0) {
            if (noResults) noResults.style.display = 'block';
            if (searchResults) searchResults.style.display = 'none';
        } else {
            if (noResults) noResults.style.display = 'none';
            if (searchResults) searchResults.style.display = 'block';
        }
    }
    
    // Keyboard accessibility
    accordion.addEventListener('keydown', function(e) {
        const toggle = e.target.closest('[data-faq-toggle]');
        if (!toggle) return;
        
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggle.click();
        }
    });
});
</script>
