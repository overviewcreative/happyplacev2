/**
 * Local Places Archive JavaScript
 * 
 * Handles load more functionality for local places archive
 */

(function($) {
    'use strict';

    class LocalPlacesArchive {
        constructor() {
            this.loadMoreBtn = $('[data-load-more-btn]');
            this.container = $('[data-load-more-container]');
            this.currentPage = 1;
            
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            this.loadMoreBtn.on('click', (e) => {
                e.preventDefault();
                this.loadMore();
            });
        }

        loadMore() {
            const btn = this.loadMoreBtn;
            const spinner = $('.hph-load-more-spinner');
            
            // Show loading state
            btn.prop('disabled', true);
            spinner.removeClass('hph-hidden');

            // Get current search parameters
            const urlParams = new URLSearchParams(window.location.search);
            const searchParams = {};
            
            // Extract all current filter parameters
            urlParams.forEach((value, key) => {
                searchParams[key] = value;
            });

            // Add pagination
            this.currentPage++;
            searchParams.page = this.currentPage;
            searchParams.action = 'load_more_local_places';
            searchParams.nonce = hph_theme.nonce; // Will be localized

            $.ajax({
                url: hph_theme.ajax_url,
                type: 'POST',
                data: searchParams,
                success: (response) => {
                    if (response.success && response.data.html) {
                        // Append new content
                        this.container.append(response.data.html);
                        
                        // Update button state
                        if (!response.data.has_more) {
                            btn.hide();
                        }
                        
                        // Update page data
                        btn.attr('data-current-page', response.data.current_page);
                    }
                },
                error: (xhr, status, error) => {
                    alert('Failed to load more places. Please try again.');
                    this.currentPage--; // Revert page increment
                },
                complete: () => {
                    // Hide loading state
                    btn.prop('disabled', false);
                    spinner.addClass('hph-hidden');
                }
            });
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new LocalPlacesArchive();
    });

})(jQuery);