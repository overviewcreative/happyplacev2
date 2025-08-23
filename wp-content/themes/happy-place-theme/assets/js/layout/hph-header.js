/**
 * Header Component JavaScript
 * Handles search, mobile menu, user dropdown, and sticky behavior
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ================================================================
    // HEADER COMPENSATION
    // ================================================================
    // Add class for browsers that don't support :has() pseudo-selector
    const stickyHeader = document.querySelector('.hph-sticky-header');
    if (stickyHeader) {
        document.body.classList.add('hph-header-compensated');
    }
    
    // ================================================================
    // SEARCH FUNCTIONALITY
    // ================================================================
    const searchToggle = document.querySelector('.hph-search-toggle');
    const searchBar = document.querySelector('.hph-search-bar');
    const searchClose = document.querySelector('.hph-search-close');
    const searchInput = document.querySelector('.hph-search-input');
    
    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', function() {
            searchBar.classList.toggle('active');
            if (searchBar.classList.contains('active') && searchInput) {
                setTimeout(() => searchInput.focus(), 300);
            }
        });
        
        if (searchClose) {
            searchClose.addEventListener('click', function() {
                searchBar.classList.remove('active');
            });
        }
        
        // Close search on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchBar.classList.contains('active')) {
                searchBar.classList.remove('active');
            }
        });
    }
    
    // ================================================================
    // USER DROPDOWN FUNCTIONALITY
    // ================================================================
    const userDropdown = document.querySelector('.hph-user-dropdown');
    const userToggle = document.querySelector('.hph-user-toggle');
    const userMenu = document.querySelector('.hph-dropdown-menu');
    
    if (userToggle && userMenu) {
        // Toggle on click
        userToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            userMenu.classList.toggle('active');
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userToggle.setAttribute('aria-expanded', 'false');
                userMenu.classList.remove('active');
            }
        });
        
        // Keyboard navigation
        userToggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    }
    
    // ================================================================
    // MOBILE MENU FUNCTIONALITY
    // ================================================================
    const mobileToggle = document.querySelector('.hph-mobile-toggle');
    const mobileMenu = document.querySelector('.hph-mobile-menu');
    const mobileOverlay = document.querySelector('.hph-mobile-overlay');
    const mobileClose = document.querySelector('.hph-mobile-close');
    
    if (mobileToggle && mobileMenu && mobileOverlay) {
        function openMobileMenu() {
            mobileToggle.classList.add('active');
            mobileToggle.setAttribute('aria-expanded', 'true');
            mobileMenu.classList.add('active');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenu() {
            mobileToggle.classList.remove('active');
            mobileToggle.setAttribute('aria-expanded', 'false');
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        mobileToggle.addEventListener('click', function() {
            if (this.classList.contains('active')) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
        
        // Close menu handlers
        [mobileClose, mobileOverlay].forEach(element => {
            if (element) {
                element.addEventListener('click', closeMobileMenu);
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }
    
    // ================================================================
    // STICKY HEADER WITH SCROLL BEHAVIOR
    // ================================================================
    const header = document.querySelector('.hph-sticky-header');
    const topbar = document.querySelector('.hph-topbar');
    
    if (header) {
        let lastScroll = 0;
        let ticking = false;
        
        function updateHeader() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                // Add scrolled class for compact header
                header.classList.add('scrolled');
                if (topbar) {
                    topbar.classList.add('scrolled');
                }
                
                if (currentScroll > lastScroll && currentScroll > 200) {
                    // Scrolling down - hide header
                    header.classList.add('hidden');
                } else {
                    // Scrolling up - show header
                    header.classList.remove('hidden');
                }
            } else {
                // At top - remove all scroll classes
                header.classList.remove('scrolled', 'hidden');
                if (topbar) {
                    topbar.classList.remove('scrolled');
                }
            }
            
            lastScroll = currentScroll;
            ticking = false;
        }
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestTick);
        
        // Initialize on page load
        updateHeader();
    }
    
    // ================================================================
    // SEARCH SUGGESTIONS (Basic implementation)
    // ================================================================
    if (searchInput) {
        const suggestionsContainer = document.querySelector('.hph-search-suggestions');
        let searchTimeout;
        
        if (suggestionsContainer) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        // Basic suggestions - you can enhance this with AJAX
                        const suggestions = [
                            'Wilmington, DE',
                            'Newark, DE',
                            'Dover, DE',
                            'Rehoboth Beach, DE',
                            'Bethany Beach, DE'
                        ].filter(item => 
                            item.toLowerCase().includes(query.toLowerCase())
                        );
                        
                        if (suggestions.length > 0) {
                            suggestionsContainer.innerHTML = suggestions
                                .map(suggestion => 
                                    `<div class="suggestion-item" data-value="${suggestion}">${suggestion}</div>`
                                ).join('');
                            suggestionsContainer.style.display = 'block';
                        } else {
                            suggestionsContainer.style.display = 'none';
                        }
                    }, 300);
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            });
            
            // Handle suggestion clicks
            suggestionsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('suggestion-item')) {
                    searchInput.value = e.target.dataset.value;
                    this.style.display = 'none';
                }
            });
        }
    }
    
    // ================================================================
    // ACCESSIBILITY ENHANCEMENTS
    // ================================================================
    
    // Skip to content link
    const skipLink = document.querySelector('.skip-link');
    if (skipLink) {
        skipLink.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.focus();
                target.scrollIntoView();
            }
        });
    }
    
    // Focus management for dropdowns
    document.querySelectorAll('.sub-menu').forEach(submenu => {
        const parentLink = submenu.previousElementSibling;
        if (parentLink) {
            parentLink.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const firstLink = submenu.querySelector('a');
                    if (firstLink) firstLink.focus();
                }
            });
        }
    });
});

// ================================================================
// CSS FOR ADDITIONAL STATES (Add to CSS if needed)
// ================================================================
const additionalStyles = `
    .hph-dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .hph-sticky-header.scrolled {
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    }
    
    .hph-sticky-header.hidden {
        transform: translateY(-100%);
    }
    
    .hph-search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--hph-gray-200);
        border-radius: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid var(--hph-gray-100);
    }
    
    .suggestion-item:hover {
        background: var(--hph-gray-50);
    }
    
    .suggestion-item:last-child {
        border-bottom: none;
    }
`;

// Inject additional styles if they don't exist
if (!document.querySelector('#hph-header-dynamic-styles')) {
    const style = document.createElement('style');
    style.id = 'hph-header-dynamic-styles';
    style.textContent = additionalStyles;
    document.head.appendChild(style);
}
