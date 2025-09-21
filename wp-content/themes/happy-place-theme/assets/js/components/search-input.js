/**
 * SEARCH INPUT ATOM - JavaScript Component
 * Handles interactive behavior for hph-search-input atomic component
 *
 * Features:
 * - Clear button show/hide based on input value
 * - Clear input on close button click
 * - Auto-focus behavior
 * - Escape key to clear
 * - Autocomplete integration
 *
 * @package HappyPlaceTheme
 * @version 4.0.0
 */

(function() {
    'use strict';

    // Wait for DOM to load
    document.addEventListener('DOMContentLoaded', function() {
        initializeSearchInputs();
    });

    /**
     * Initialize all search input components
     */
    function initializeSearchInputs() {
        const searchInputWrappers = document.querySelectorAll('.hph-search-input-wrapper');

        searchInputWrappers.forEach(wrapper => {
            const input = wrapper.querySelector('.hph-search-input');
            const closeButton = wrapper.querySelector('.hph-search-input-close');

            if (!input) return;

            // Initialize the search input instance
            new SearchInputComponent(wrapper, input, closeButton);
        });

    }

    /**
     * SearchInputComponent class
     */
    class SearchInputComponent {
        constructor(wrapper, input, closeButton = null) {
            this.wrapper = wrapper;
            this.input = input;
            this.closeButton = closeButton;
            this.container = wrapper.closest('.hph-search-input-container');

            this.init();
        }

        init() {
            this.bindEvents();
            this.updateCloseButtonVisibility();

            // Initialize autocomplete integration if container exists
            if (this.container) {
                this.initAutocompleteIntegration();
            }
        }

        bindEvents() {
            // Input events
            this.input.addEventListener('input', () => this.handleInputChange());
            this.input.addEventListener('focus', () => this.handleInputFocus());
            this.input.addEventListener('blur', () => this.handleInputBlur());
            this.input.addEventListener('keydown', (e) => this.handleInputKeydown(e));

            // Close button events
            if (this.closeButton) {
                this.closeButton.addEventListener('click', () => this.handleCloseClick());
            }
        }

        handleInputChange() {
            this.updateCloseButtonVisibility();
            this.updateWrapperState();
        }

        handleInputFocus() {
            this.wrapper.classList.add('is-active');
            if (this.container) {
                this.container.classList.add('is-active');
            }
        }

        handleInputBlur() {
            // Small delay to allow for close button clicks
            setTimeout(() => {
                this.wrapper.classList.remove('is-active');
                if (this.container) {
                    this.container.classList.remove('is-active');
                }
            }, 150);
        }

        handleInputKeydown(e) {
            switch (e.key) {
                case 'Escape':
                    if (this.input.value) {
                        e.preventDefault();
                        this.clearInput();
                    }
                    break;

                case 'Enter':
                    // Let the form handle submission unless we need special behavior
                    break;
            }
        }

        handleCloseClick() {
            this.clearInput();
            this.input.focus();
        }

        clearInput() {
            this.input.value = '';
            this.updateCloseButtonVisibility();
            this.updateWrapperState();

            // Trigger input event for any listeners
            this.input.dispatchEvent(new Event('input', { bubbles: true }));

            // Hide autocomplete if present
            if (this.container) {
                const autocomplete = this.container.querySelector('.hph-search-results');
                if (autocomplete) {
                    autocomplete.classList.remove('active');
                    autocomplete.style.display = 'none';
                }
            }
        }

        updateCloseButtonVisibility() {
            const hasValue = this.input.value.trim().length > 0;

            if (hasValue) {
                this.wrapper.classList.add('has-value');
            } else {
                this.wrapper.classList.remove('has-value');
            }
        }

        updateWrapperState() {
            const hasValue = this.input.value.trim().length > 0;

            if (hasValue) {
                this.wrapper.classList.add('has-value');
            } else {
                this.wrapper.classList.remove('has-value');
            }
        }

        initAutocompleteIntegration() {
            // Watch for autocomplete activation
            const autocomplete = this.container.querySelector('.hph-search-results');
            if (!autocomplete) return;

            // Use MutationObserver to watch for autocomplete show/hide
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' &&
                        (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {

                        const isActive = autocomplete.classList.contains('active') ||
                                        autocomplete.style.display === 'block';

                        if (isActive) {
                            this.container.classList.add('has-autocomplete-active');
                        } else {
                            this.container.classList.remove('has-autocomplete-active');
                        }
                    }
                });
            });

            observer.observe(autocomplete, {
                attributes: true,
                attributeFilter: ['style', 'class']
            });

            // Store observer reference for cleanup if needed
            this.autocompleteObserver = observer;
        }

        /**
         * Public API methods
         */
        focus() {
            this.input.focus();
        }

        clear() {
            this.clearInput();
        }

        getValue() {
            return this.input.value;
        }

        setValue(value) {
            this.input.value = value;
            this.updateCloseButtonVisibility();
            this.updateWrapperState();
        }

        destroy() {
            // Clean up event listeners and observers
            if (this.autocompleteObserver) {
                this.autocompleteObserver.disconnect();
            }
        }
    }

    // Export for external use if needed
    window.HPH = window.HPH || {};
    window.HPH.SearchInputComponent = SearchInputComponent;

})();