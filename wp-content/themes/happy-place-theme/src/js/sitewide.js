/**
 * Sitewide JavaScript Bundle
 * Navigation, header, footer, and universal components
 */

// Initialize sitewide functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('%c🌐 Happy Place Sitewide Loaded', 'color: #10b981; font-weight: bold;');
    
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
    
    // Sticky header
    let lastScrollTop = 0;
    const header = document.querySelector('.site-header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
            
            lastScrollTop = scrollTop;
        });
    }
    
    // Search autocomplete (basic)
    const searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Basic search functionality - can be enhanced later
            const query = this.value.trim();
            if (query.length >= 2) {
                console.log('Search query:', query);
            }
        });
    });
});