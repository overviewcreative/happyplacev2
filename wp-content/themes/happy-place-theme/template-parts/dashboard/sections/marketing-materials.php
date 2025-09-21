<?php
/**
 * Marketing Materials Dashboard Section
 * 
 * Generate and manage marketing materials for listings
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!defined('ABSPATH') || !is_user_logged_in()) {
    exit;
}

$current_user = wp_get_current_user();
$is_agent = in_array('agent', $current_user->roles) || in_array('administrator', $current_user->roles);

if (!$is_agent) {
    echo '<div class="hph-alert hph-alert-warning">Marketing materials are available for agents only.</div>';
    return;
}
?>

<div class="hph-marketing-materials">
    
    <!-- Marketing Header -->
    <div class="hph-section-header hph-flex hph-flex-row hph-justify-between hph-items-center hph-mb-lg">
        <div>
            <h2 class="hph-section-title hph-text-xl hph-font-semibold hph-text-gray-900">Marketing Materials</h2>
            <p class="hph-text-gray-600 hph-mt-1">Generate professional marketing materials for your listings</p>
        </div>
        <div class="hph-header-actions">
            <button class="hph-btn hph-btn-secondary hph-btn-sm" id="refreshMarketingBtn">
                <span class="hph-icon hph-icon-refresh hph-mr-sm"></span>
                Refresh
            </button>
        </div>
    </div>

    <!-- Marketing Generator Cards -->
    <div class="hph-grid hph-grid-cols-1 hph-lg:grid-cols-3 hph-gap-lg hph-mb-lg">
        
        <!-- PDF Flyer Generator -->
        <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg">
            <div class="hph-flex hph-items-center hph-mb-md">
                <span class="hph-text-2xl hph-mr-md">📄</span>
                <h3 class="hph-font-semibold hph-text-gray-900">PDF Flyers</h3>
            </div>
            <p class="hph-text-gray-600 hph-text-sm hph-mb-md">Create professional property flyers with listing details and agent information.</p>
            <div class="hph-flex hph-flex-col hph-gap-sm">
                <select class="hph-form-select hph-text-sm" id="pdfListingSelect">
                    <option value="">Select a listing...</option>
                    <!-- Will be populated via AJAX -->
                </select>
                <select class="hph-form-select hph-text-sm" id="pdfTemplateSelect">
                    <option value="modern">Modern Layout</option>
                    <option value="classic">Classic Style</option>
                    <option value="luxury">Luxury Theme</option>
                </select>
                <button class="hph-btn hph-btn-primary hph-btn-sm" id="generatePdfBtn" disabled>
                    <span class="hph-btn-text">Generate PDF Flyer</span>
                    <span class="hph-btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Generating...
                    </span>
                </button>
            </div>
        </div>
        
        <!-- Social Media Templates -->
        <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg">
            <div class="hph-flex hph-items-center hph-mb-md">
                <span class="hph-text-2xl hph-mr-md">📱</span>
                <h3 class="hph-font-semibold hph-text-gray-900">Social Media</h3>
            </div>
            <p class="hph-text-gray-600 hph-text-sm hph-mb-md">Generate ready-to-post social media content for Facebook, Instagram, and Twitter.</p>
            <div class="hph-flex hph-flex-col hph-gap-sm">
                <select class="hph-form-select hph-text-sm" id="socialListingSelect">
                    <option value="">Select a listing...</option>
                    <!-- Will be populated via AJAX -->
                </select>
                <select class="hph-form-select hph-text-sm" id="socialPlatformSelect">
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="twitter">Twitter</option>
                </select>
                <button class="hph-btn hph-btn-primary hph-btn-sm" id="generateSocialBtn" disabled>
                    <span class="hph-btn-text">Generate Social Post</span>
                    <span class="hph-btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Creating...
                    </span>
                </button>
            </div>
        </div>
        
        <!-- Email Marketing -->
        <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg">
            <div class="hph-flex hph-items-center hph-mb-md">
                <span class="hph-text-2xl hph-mr-md">📧</span>
                <h3 class="hph-font-semibold hph-text-gray-900">Email Marketing</h3>
            </div>
            <p class="hph-text-gray-600 hph-text-sm hph-mb-md">Create email templates for listing announcements and open house invitations.</p>
            <div class="hph-flex hph-flex-col hph-gap-sm">
                <select class="hph-form-select hph-text-sm" id="emailListingSelect">
                    <option value="">Select a listing...</option>
                    <!-- Will be populated via AJAX -->
                </select>
                <select class="hph-form-select hph-text-sm" id="emailTemplateSelect">
                    <option value="listing_announcement">New Listing</option>
                    <option value="open_house_invite">Open House Invite</option>
                    <option value="price_reduction">Price Reduction</option>
                </select>
                <button class="hph-btn hph-btn-primary hph-btn-sm" id="generateEmailBtn" disabled>
                    <span class="hph-btn-text">Generate Email</span>
                    <span class="hph-btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Creating...
                    </span>
                </button>
            </div>
        </div>
        
    </div>

    <!-- Listing Selection -->
    <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg hph-mb-lg">
        <h3 class="hph-font-semibold hph-text-gray-900 hph-mb-md">Select Listing for Marketing Materials</h3>
        
        <!-- Quick Search -->
        <div class="hph-flex hph-gap-md hph-mb-md">
            <div class="hph-flex-1">
                <input type="text" class="hph-form-control" id="listingSearchInput" placeholder="Search listings by title, address, or MLS...">
            </div>
            <button class="hph-btn hph-btn-secondary" id="searchListingsBtn">Search</button>
        </div>
        
        <!-- Listings Grid -->
        <div id="marketingListingsGrid" class="hph-grid hph-grid-cols-1 hph-md:grid-cols-2 hph-lg:grid-cols-3 hph-gap-md">
            <!-- Listings will be loaded here via AJAX -->
            <div class="hph-text-center hph-py-xl hph-text-gray-500">
                <p>Loading your listings...</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div id="marketingPagination" class="hph-flex hph-justify-center hph-mt-lg">
            <!-- Pagination will be inserted here -->
        </div>
    </div>

    <!-- Generated Content Display -->
    <div id="generatedContent" class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg" style="display: none;">
        <h3 class="hph-font-semibold hph-text-gray-900 hph-mb-md">Generated Marketing Material</h3>
        <div id="generatedContentBody">
            <!-- Generated content will appear here -->
        </div>
    </div>

    <!-- Marketing Activity Log -->
    <div class="hph-bg-white hph-border hph-border-gray-200 hph-rounded-lg hph-p-lg hph-mt-lg">
        <div class="hph-flex hph-justify-between hph-items-center hph-mb-md">
            <h3 class="hph-font-semibold hph-text-gray-900">Recent Marketing Activity</h3>
            <button class="hph-btn hph-btn-secondary hph-btn-sm" id="viewAllActivityBtn">View All</button>
        </div>
        
        <div id="marketingActivityLog" class="hph-space-y-md">
            <!-- Activity items will be loaded here -->
            <div class="hph-text-center hph-py-md hph-text-gray-500">
                <p>No marketing activity yet. Generate your first material above!</p>
            </div>
        </div>
    </div>

</div>

<!-- Marketing Material Modals -->

<!-- PDF Preview Modal -->
<div class="hph-modal" id="pdfPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="hph-modal-dialog hph-modal-lg">
        <div class="hph-modal-content">
            <div class="hph-modal-header">
                <h5 class="hph-modal-title">PDF Flyer Generated</h5>
                <button type="button" class="hph-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="hph-modal-body">
                <div class="hph-text-center hph-py-lg">
                    <div class="hph-text-6xl hph-mb-md">📄</div>
                    <h4 class="hph-font-semibold hph-text-gray-900 hph-mb-md">PDF Flyer Ready!</h4>
                    <p class="hph-text-gray-600 hph-mb-lg">Your professional property flyer has been generated successfully.</p>
                    <div class="hph-flex hph-gap-md hph-justify-center">
                        <a href="#" class="hph-btn hph-btn-primary" id="downloadPdfBtn" target="_blank">
                            <span class="hph-icon hph-icon-download hph-mr-sm"></span>
                            Download PDF
                        </a>
                        <button class="hph-btn hph-btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Media Content Modal -->
<div class="hph-modal" id="socialContentModal" tabindex="-1" aria-hidden="true">
    <div class="hph-modal-dialog hph-modal-lg">
        <div class="hph-modal-content">
            <div class="hph-modal-header">
                <h5 class="hph-modal-title">Social Media Content</h5>
                <button type="button" class="hph-btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="hph-modal-body">
                <div id="socialContentDisplay">
                    <!-- Social content will be displayed here -->
                </div>
                <div class="hph-mt-lg hph-flex hph-gap-md">
                    <button class="hph-btn hph-btn-primary" id="copySocialContentBtn">
                        <span class="hph-icon hph-icon-copy hph-mr-sm"></span>
                        Copy to Clipboard
                    </button>
                    <button class="hph-btn hph-btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

