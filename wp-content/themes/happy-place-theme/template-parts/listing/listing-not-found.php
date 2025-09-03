<?php
/**
 * Listing Not Found Template Part
 * 
 * @package HappyPlaceTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<main id="primary" class="hph-listing-not-found">
    <div class="hph-container" style="padding: 5rem 1rem; text-align: center;">
        <div style="max-width: 600px; margin: 0 auto;">
            
            <div style="margin-bottom: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🏠</div>
                <h1 class="hph-section-title" style="font-size: 2.5rem; margin-bottom: 1rem;">
                    Listing Not Found
                </h1>
                <p style="font-size: 1.125rem; color: var(--hph-gray-600); margin-bottom: 2rem; line-height: 1.6;">
                    Sorry, the listing you're looking for is no longer available or has been removed.
                </p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 1rem; align-items: center;">
                <a href="/listings" class="hph-agent-btn hph-agent-btn-primary" style="width: auto; min-width: 200px;">
                    <i class="fas fa-search"></i>
                    View All Listings
                </a>
                <a href="/" class="hph-agent-btn hph-agent-btn-outline" style="width: auto; min-width: 200px;">
                    <i class="fas fa-home"></i>
                    Return Home
                </a>
            </div>
            
        </div>
    </div>
</main>
