<?php
/**
 * Quick Actions Modal
 * Universal modal for various dashboard quick actions
 * 
 * @package HappyPlaceTheme
 */

// Security check
if (!is_user_logged_in()) {
    return;
}

$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_agent = in_array('agent', $user_roles) || in_array('administrator', $user_roles);
?>

<div id="quickActionsModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    align-items: center;
    justify-content: center;
">
    <div id="quickActionsOverlay" style="
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    "></div>
    
    <div style="
        position: relative;
        background: var(--hph-white);
        border-radius: var(--hph-border-radius-lg);
        box-shadow: var(--hph-shadow-xl);
        max-width: 500px;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        width: 90vw;
    ">
        <div class="hph-modal-header">
            <h3 class="hph-modal-title" id="quickActionsTitle">
                <?php _e('Quick Actions', 'happy-place-theme'); ?>
            </h3>
            <button class="hph-modal-close" id="closeQuickActions" aria-label="<?php _e('Close', 'happy-place-theme'); ?>">
                <span class="hph-icon-close">&times;</span>
            </button>
        </div>
        
        <div class="hph-modal-content">
            
            <!-- Contact Agent Form -->
            <div class="hph-quick-action hph-contact-agent-form" id="contactAgentForm" style="display: none;">
                <form class="hph-form" id="agentContactForm">
                    <input type="hidden" id="contactListingId" name="listing_id" value="">
                    <input type="hidden" id="contactAgentId" name="agent_id" value="">
                    <input type="hidden" name="action" value="contact_agent">
                    <?php wp_nonce_field('hph_contact_agent', 'contact_nonce'); ?>
                    
                    <div class="hph-form-group">
                        <label for="contactName" class="hph-form-label">
                            <?php _e('Your Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input type="text" id="contactName" name="contact_name" class="hph-form-input" required 
                               value="<?php echo esc_attr($current_user->display_name); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="contactEmail" class="hph-form-label">
                            <?php _e('Email Address', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input type="email" id="contactEmail" name="contact_email" class="hph-form-input" required 
                               value="<?php echo esc_attr($current_user->user_email); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="contactPhone" class="hph-form-label"><?php _e('Phone Number', 'happy-place-theme'); ?></label>
                        <input type="tel" id="contactPhone" name="contact_phone" class="hph-form-input" 
                               placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="contactMessage" class="hph-form-label">
                            <?php _e('Message', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <textarea id="contactMessage" name="contact_message" class="hph-form-textarea" rows="4" required 
                                  placeholder="<?php _e('I am interested in this property and would like more information...', 'happy-place-theme'); ?>"></textarea>
                    </div>
                    
                    <div class="hph-form-actions">
                        <button type="button" class="hph-btn hph-btn-outline-primary" id="cancelContactAgent">
                            <?php _e('Cancel', 'happy-place-theme'); ?>
                        </button>
                        <button type="submit" class="hph-btn hph-btn-primary">
                            <span class="hph-btn-icon hph-icon-send"></span>
                            <?php _e('Send Message', 'happy-place-theme'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Schedule Tour Form -->
            <div class="hph-quick-action hph-schedule-tour-form" id="scheduleTourForm" style="display: none;">
                <form class="hph-form" id="tourScheduleForm">
                    <input type="hidden" id="tourListingId" name="listing_id" value="">
                    <input type="hidden" name="action" value="schedule_tour">
                    <?php wp_nonce_field('hph_schedule_tour', 'tour_nonce'); ?>
                    
                    <div class="hph-form-group">
                        <label for="tourName" class="hph-form-label">
                            <?php _e('Your Name', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input type="text" id="tourName" name="tour_name" class="hph-form-input" required 
                               value="<?php echo esc_attr($current_user->display_name); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="tourEmail" class="hph-form-label">
                            <?php _e('Email Address', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input type="email" id="tourEmail" name="tour_email" class="hph-form-input" required 
                               value="<?php echo esc_attr($current_user->user_email); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="tourPhone" class="hph-form-label">
                            <?php _e('Phone Number', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input type="tel" id="tourPhone" name="tour_phone" class="hph-form-input" required 
                               placeholder="<?php _e('(555) 123-4567', 'happy-place-theme'); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="tourDate" class="hph-form-label">
                            <?php _e('Preferred Date', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <input type="date" id="tourDate" name="tour_date" class="hph-form-input" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="tourTime" class="hph-form-label">
                            <?php _e('Preferred Time', 'happy-place-theme'); ?>
                            <span class="hph-required">*</span>
                        </label>
                        <select id="tourTime" name="tour_time" class="hph-form-select" required>
                            <option value=""><?php _e('Select Time', 'happy-place-theme'); ?></option>
                            <option value="09:00"><?php _e('9:00 AM', 'happy-place-theme'); ?></option>
                            <option value="10:00"><?php _e('10:00 AM', 'happy-place-theme'); ?></option>
                            <option value="11:00"><?php _e('11:00 AM', 'happy-place-theme'); ?></option>
                            <option value="12:00"><?php _e('12:00 PM', 'happy-place-theme'); ?></option>
                            <option value="13:00"><?php _e('1:00 PM', 'happy-place-theme'); ?></option>
                            <option value="14:00"><?php _e('2:00 PM', 'happy-place-theme'); ?></option>
                            <option value="15:00"><?php _e('3:00 PM', 'happy-place-theme'); ?></option>
                            <option value="16:00"><?php _e('4:00 PM', 'happy-place-theme'); ?></option>
                            <option value="17:00"><?php _e('5:00 PM', 'happy-place-theme'); ?></option>
                        </select>
                    </div>
                    
                    <div class="hph-form-group">
                        <label for="tourMessage" class="hph-form-label"><?php _e('Additional Notes', 'happy-place-theme'); ?></label>
                        <textarea id="tourMessage" name="tour_message" class="hph-form-textarea" rows="3" 
                                  placeholder="<?php _e('Any specific requests or questions...', 'happy-place-theme'); ?>"></textarea>
                    </div>
                    
                    <div class="hph-form-actions">
                        <button type="button" class="hph-btn hph-btn-outline-primary" id="cancelScheduleTour">
                            <?php _e('Cancel', 'happy-place-theme'); ?>
                        </button>
                        <button type="submit" class="hph-btn hph-btn-primary">
                            <span class="hph-btn-icon hph-icon-calendar"></span>
                            <?php _e('Schedule Tour', 'happy-place-theme'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($is_agent): ?>
                <!-- Add Note Form (For Leads) -->
                <div class="hph-quick-action hph-add-note-form" id="addNoteForm" style="display: none;">
                    <form class="hph-form" id="leadNoteForm">
                        <input type="hidden" id="noteLeadId" name="lead_id" value="">
                        <input type="hidden" name="action" value="add_lead_note">
                        <?php wp_nonce_field('hph_add_note', 'note_nonce'); ?>
                        
                        <div class="hph-form-group">
                            <label for="noteType" class="hph-form-label"><?php _e('Note Type', 'happy-place-theme'); ?></label>
                            <select id="noteType" name="note_type" class="hph-form-select">
                                <option value="general"><?php _e('General Note', 'happy-place-theme'); ?></option>
                                <option value="phone_call"><?php _e('Phone Call', 'happy-place-theme'); ?></option>
                                <option value="email"><?php _e('Email', 'happy-place-theme'); ?></option>
                                <option value="meeting"><?php _e('Meeting', 'happy-place-theme'); ?></option>
                                <option value="showing"><?php _e('Property Showing', 'happy-place-theme'); ?></option>
                                <option value="follow_up"><?php _e('Follow-up', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        
                        <div class="hph-form-group">
                            <label for="noteContent" class="hph-form-label">
                                <?php _e('Note Content', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <textarea id="noteContent" name="note_content" class="hph-form-textarea" rows="4" required 
                                      placeholder="<?php _e('Enter your note here...', 'happy-place-theme'); ?>"></textarea>
                        </div>
                        
                        <div class="hph-form-group">
                            <label for="followUpDate" class="hph-form-label"><?php _e('Schedule Follow-up', 'happy-place-theme'); ?></label>
                            <input type="date" id="followUpDate" name="follow_up_date" class="hph-form-input" 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="hph-form-actions">
                            <button type="button" class="hph-btn hph-btn-outline-primary" id="cancelAddNote">
                                <?php _e('Cancel', 'happy-place-theme'); ?>
                            </button>
                            <button type="submit" class="hph-btn hph-btn-primary">
                                <span class="hph-btn-icon hph-icon-file-plus"></span>
                                <?php _e('Add Note', 'happy-place-theme'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lead Status Update Form -->
                <div class="hph-quick-action hph-update-status-form" id="updateStatusForm" style="display: none;">
                    <form class="hph-form" id="leadStatusForm">
                        <input type="hidden" id="statusLeadId" name="lead_id" value="">
                        <input type="hidden" name="action" value="update_lead_status">
                        <?php wp_nonce_field('hph_update_status', 'status_nonce'); ?>
                        
                        <div class="hph-form-group">
                            <label for="newStatus" class="hph-form-label">
                                <?php _e('Lead Status', 'happy-place-theme'); ?>
                                <span class="hph-required">*</span>
                            </label>
                            <select id="newStatus" name="new_status" class="hph-form-select" required>
                                <option value="cold"><?php _e('Cold', 'happy-place-theme'); ?></option>
                                <option value="warm"><?php _e('Warm', 'happy-place-theme'); ?></option>
                                <option value="hot"><?php _e('Hot', 'happy-place-theme'); ?></option>
                                <option value="converted"><?php _e('Converted', 'happy-place-theme'); ?></option>
                                <option value="lost"><?php _e('Lost', 'happy-place-theme'); ?></option>
                            </select>
                        </div>
                        
                        <div class="hph-form-group">
                            <label for="statusReason" class="hph-form-label"><?php _e('Reason for Change', 'happy-place-theme'); ?></label>
                            <textarea id="statusReason" name="status_reason" class="hph-form-textarea" rows="3" 
                                      placeholder="<?php _e('Optional: Why are you changing the status?', 'happy-place-theme'); ?>"></textarea>
                        </div>
                        
                        <div class="hph-form-actions">
                            <button type="button" class="hph-btn hph-btn-outline-primary" id="cancelUpdateStatus">
                                <?php _e('Cancel', 'happy-place-theme'); ?>
                            </button>
                            <button type="submit" class="hph-btn hph-btn-primary">
                                <span class="hph-btn-icon hph-icon-check"></span>
                                <?php _e('Update Status', 'happy-place-theme'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Share Listing Form -->
            <div class="hph-quick-action hph-share-listing-form" id="shareListingForm" style="display: none;">
                <div class="hph-share-content">
                    <h5 class="hph-share-title"><?php _e('Share This Property', 'happy-place-theme'); ?></h5>
                    
                    <!-- Share URL -->
                    <div class="hph-form-group">
                        <label for="shareUrl" class="hph-form-label"><?php _e('Property URL', 'happy-place-theme'); ?></label>
                        <div class="hph-input-group">
                            <input type="text" id="shareUrl" class="hph-form-input" readonly>
                            <button type="button" class="hph-btn hph-btn-outline-primary" id="copyUrlBtn">
                                <span class="hph-icon-copy"></span>
                                <?php _e('Copy', 'happy-place-theme'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Social Share Buttons -->
                    <div class="hph-social-share">
                        <h6 class="hph-share-subtitle"><?php _e('Share on Social Media', 'happy-place-theme'); ?></h6>
                        <div class="hph-social-buttons">
                            <a href="#" class="hph-social-btn hph-social-facebook" id="shareFacebook" target="_blank">
                                <span class="hph-icon-facebook"></span>
                                <span><?php _e('Facebook', 'happy-place-theme'); ?></span>
                            </a>
                            <a href="#" class="hph-social-btn hph-social-twitter" id="shareTwitter" target="_blank">
                                <span class="hph-icon-twitter"></span>
                                <span><?php _e('Twitter', 'happy-place-theme'); ?></span>
                            </a>
                            <a href="#" class="hph-social-btn hph-social-email" id="shareEmail">
                                <span class="hph-icon-mail"></span>
                                <span><?php _e('Email', 'happy-place-theme'); ?></span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="hph-form-actions">
                        <button type="button" class="hph-btn hph-btn-outline-primary" id="cancelShare">
                            <?php _e('Close', 'happy-place-theme'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="hph-quick-action hph-success-message" id="successMessage" style="display: none;">
                <div class="hph-success-content">
                    <div class="hph-success-icon">
                        <span class="hph-icon-check-circle"></span>
                    </div>
                    <h5 class="hph-success-title" id="successTitle">
                        <?php _e('Success!', 'happy-place-theme'); ?>
                    </h5>
                    <p class="hph-success-text" id="successText">
                        <?php _e('Your action was completed successfully.', 'happy-place-theme'); ?>
                    </p>
                    <div class="hph-form-actions">
                        <button type="button" class="hph-btn hph-btn-primary" id="closeSuccess">
                            <?php _e('Close', 'happy-place-theme'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="hph-quick-action hph-error-message" id="errorMessage" style="display: none;">
                <div class="hph-error-content">
                    <div class="hph-error-icon">
                        <span class="hph-icon-alert-circle"></span>
                    </div>
                    <h5 class="hph-error-title"><?php _e('Error', 'happy-place-theme'); ?></h5>
                    <p class="hph-error-text" id="errorText">
                        <?php _e('Something went wrong. Please try again.', 'happy-place-theme'); ?>
                    </p>
                    <div class="hph-form-actions">
                        <button type="button" class="hph-btn hph-btn-outline-primary" id="closeError">
                            <?php _e('Close', 'happy-place-theme'); ?>
                        </button>
                        <button type="button" class="hph-btn hph-btn-primary" id="retryAction">
                            <?php _e('Try Again', 'happy-place-theme'); ?>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
