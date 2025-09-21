/**
 * Enhanced Agent Archive JavaScript
 * Handles AJAX filtering, agent matching, contact integration, and team views
 */

class EnhancedAgentArchive {
    constructor() {
        this.isLoading = false;
        this.currentPage = 1;
        this.favoriteAgents = new Set();
        this.selectedAgents = new Set();
        this.matchingModal = null;
        this.contactModal = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadUserPreferences();
        this.initializeModals();
        this.initializeAdvancedSearch();
    }
    
    bindEvents() {
        // Filter form submission
        const filterForm = document.getElementById('filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFilterSubmit();
            });
        }
        
        // Sort dropdown change
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.handleSortChange(e.target.value);
            });
        }
        
        // Team view toggle
        const teamToggle = document.getElementById('team-view-toggle');
        if (teamToggle) {
            teamToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleTeamView();
            });
        }
        
        // Agent matching
        const matchAgentBtn = document.getElementById('match-agent-btn');
        if (matchAgentBtn) {
            matchAgentBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showMatchingModal();
            });
        }
        
        const closeMatchingModal = document.getElementById('close-matching-modal');
        if (closeMatchingModal) {
            closeMatchingModal.addEventListener('click', (e) => {
                e.preventDefault();
                this.hideMatchingModal();
            });
        }
        
        const agentMatchingForm = document.getElementById('agent-matching-form');
        if (agentMatchingForm) {
            agentMatchingForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleAgentMatching();
            });
        }
        
        // Load more button
        const loadMoreBtn = document.getElementById('load-more-agents');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadMoreAgents();
            });
        }
        
        // Contact multiple button
        const contactMultipleBtn = document.getElementById('contact-multiple-btn');
        if (contactMultipleBtn) {
            contactMultipleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleMultipleContact();
            });
        }
        
        // Dynamic event delegation for agent cards
        document.addEventListener('click', this.handleCardInteractions.bind(this));
        document.addEventListener('change', this.handleSelectionChange.bind(this));
    }
    
    initializeAdvancedSearch() {
        const advancedToggle = document.getElementById('advanced-search-toggle');
        const advancedPanel = document.getElementById('advanced-search-panel');
        
        if (advancedToggle && advancedPanel) {
            advancedToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleAdvancedSearch();
            });
        }
    }
    
    toggleAdvancedSearch() {
        const advancedPanel = document.getElementById('advanced-search-panel');
        const advancedToggle = document.getElementById('advanced-search-toggle');
        const toggleText = advancedToggle.querySelector('span');
        const toggleIcon = advancedToggle.querySelector('i');
        
        if (advancedPanel.classList.contains('hph-hidden')) {
            advancedPanel.classList.remove('hph-hidden');
            toggleText.textContent = 'Hide Advanced Search';
            toggleIcon.classList.replace('fa-cog', 'fa-times');
        } else {
            advancedPanel.classList.add('hph-hidden');
            toggleText.textContent = 'Show Advanced Search';
            toggleIcon.classList.replace('fa-times', 'fa-cog');
        }
    }
    
    toggleMultipleContact() {
        const contactBtn = document.getElementById('contact-multiple-btn');
        const isSelectionMode = document.body.classList.contains('agent-selection-mode');
        
        if (isSelectionMode) {
            // Exit selection mode
            document.body.classList.remove('agent-selection-mode');
            contactBtn.innerHTML = '<i class="fas fa-envelope hph-mr-xs"></i> Contact Multiple';
            contactBtn.classList.remove('hph-btn-danger');
            contactBtn.classList.add('hph-btn-outline');
            
            // Remove selection checkboxes
            document.querySelectorAll('.selection-checkbox').forEach(cb => cb.remove());
            this.selectedAgents.clear();
        } else {
            // Enter selection mode
            document.body.classList.add('agent-selection-mode');
            contactBtn.innerHTML = '<i class="fas fa-times hph-mr-xs"></i> Cancel Selection';
            contactBtn.classList.remove('hph-btn-outline');
            contactBtn.classList.add('hph-btn-danger');
            
            // Add selection checkboxes
            this.addSelectionCheckboxes();
        }
    }
    
    addSelectionCheckboxes() {
        document.querySelectorAll('.agent-card-enhanced').forEach(card => {
            if (!card.querySelector('.selection-checkbox')) {
                const agentId = card.dataset.agentId;
                if (agentId) {
                    const checkbox = document.createElement('div');
                    checkbox.className = 'selection-checkbox';
                    checkbox.innerHTML = '<i class="fas fa-check hph-hidden"></i>';
                    checkbox.dataset.agentId = agentId;
                    checkbox.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleAgentSelection(agentId, checkbox);
                    });
                    card.appendChild(checkbox);
                }
            }
        });
    }
    
    toggleAgentSelection(agentId, checkbox) {
        const isSelected = this.selectedAgents.has(agentId);
        
        if (isSelected) {
            this.selectedAgents.delete(agentId);
            checkbox.classList.remove('selected');
            checkbox.querySelector('i').classList.add('hph-hidden');
        } else {
            this.selectedAgents.add(agentId);
            checkbox.classList.add('selected');
            checkbox.querySelector('i').classList.remove('hph-hidden');
        }
        
        this.updateContactMultipleButton();
    }
    
    updateContactMultipleButton() {
        const contactBtn = document.getElementById('contact-multiple-btn');
        const selectedCount = this.selectedAgents.size;
        
        if (selectedCount > 0) {
            contactBtn.innerHTML = `<i class="fas fa-envelope hph-mr-xs"></i> Contact ${selectedCount} Agent${selectedCount > 1 ? 's' : ''}`;
            contactBtn.classList.remove('hph-btn-danger');
            contactBtn.classList.add('hph-btn-primary');
            
            // Change click handler to actually contact agents
            contactBtn.onclick = (e) => {
                e.preventDefault();
                this.contactSelectedAgents();
            };
        }
    }
    
    contactSelectedAgents() {
        if (this.selectedAgents.size === 0) return;
        
        const agentIds = Array.from(this.selectedAgents);
        // Here you would implement the multi-agent contact functionality
        this.showNotification(`Starting contact with ${agentIds.length} agents`, 'success');
        
        // Reset selection mode
        this.toggleMultipleContact();
    }
    
    handleCardInteractions(e) {
        // Handle favorite buttons
        if (e.target.closest('button[data-action="favorite"]')) {
            e.preventDefault();
            e.stopPropagation();
            const target = e.target.closest('button[data-action="favorite"]');
            this.toggleFavorite(target.dataset.agentId, target);
            return;
        }
        
        // Handle share buttons
        if (e.target.closest('button[data-action="share"]')) {
            e.preventDefault();
            e.stopPropagation();
            const target = e.target.closest('button[data-action="share"]');
            this.handleShare(target.dataset.url, target.dataset.title);
            return;
        }
        
        // Handle quick contact buttons
        if (e.target.closest('.quick-contact-btn')) {
            e.preventDefault();
            e.stopPropagation();
            const target = e.target.closest('.quick-contact-btn');
            this.showQuickContact(target.dataset.agentId);
            return;
        }
        
        // Handle contact overlay buttons
        if (e.target.closest('.contact-btn')) {
            const target = e.target.closest('.contact-btn');
            if (target.textContent.includes('Message')) {
                e.preventDefault();
                e.stopPropagation();
                const agentId = target.closest('[data-agent-id]')?.dataset.agentId;
                if (agentId) {
                    this.showContactForm(agentId);
                }
            }
        }
    }
    
    handleSelectionChange(e) {
        // This would handle any other selection changes if needed
        if (e.target.matches('.agent-selection-checkbox')) {
            const agentId = e.target.dataset.agentId;
            this.toggleAgentSelection(agentId, e.target.closest('.selection-checkbox'));
        }
    }
    
    handleFilterSubmit() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.currentPage = 1;
        
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams(formData);
        
        // Add AJAX flag
        params.append('action', 'hph_filter_agents');
        params.append('nonce', window.hphArchive?.nonce || '');
        params.append('page', this.currentPage);
        
        this.showLoadingState();
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateResults(data.data.html);
                this.updateURL(params);
                if (data.data.statistics) {
                    this.updateStatistics(data.data.statistics);
                }
            } else {
                this.showError(data.data?.message || 'Error loading results');
            }
        })
        .catch(error => {
            this.showError('Network error occurred');
        })
        .finally(() => {
            this.hideLoadingState();
            this.isLoading = false;
        });
    }
    
    handleSortChange(sortValue) {
        const form = document.getElementById('filter-form');
        let sortInput = form.querySelector('input[name="sort"]');
        
        if (!sortInput) {
            sortInput = this.createHiddenInput('sort');
        }
        
        sortInput.value = sortValue;
        this.handleFilterSubmit();
    }
    
    loadMoreAgents() {
        if (this.isLoading) return;
        
        const loadMoreBtn = document.getElementById('load-more-agents');
        if (!loadMoreBtn) return;
        
        const currentPage = parseInt(loadMoreBtn.dataset.page);
        const maxPages = parseInt(loadMoreBtn.dataset.maxPages);
        
        if (currentPage >= maxPages) return;
        
        this.isLoading = true;
        const nextPage = currentPage + 1;
        
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams(formData);
        params.append('action', 'hph_load_more_agents');
        params.append('nonce', window.hphArchive?.nonce || '');
        params.append('page', nextPage);
        
        // Update button state
        loadMoreBtn.classList.add('loading');
        loadMoreBtn.disabled = true;
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.appendResults(data.data.html);
                loadMoreBtn.dataset.page = nextPage;
                
                if (nextPage >= maxPages) {
                    loadMoreBtn.style.display = 'none';
                }
            } else {
                this.showError(data.data?.message || 'Error loading more agents');
            }
        })
        .catch(error => {
            this.showError('Network error occurred');
        })
        .finally(() => {
            loadMoreBtn.classList.remove('loading');
            loadMoreBtn.disabled = false;
            this.isLoading = false;
        });
    }
    
    toggleTeamView() {
        const results = document.getElementById('agent-results');
        const teamToggle = document.getElementById('team-view-toggle');
        
        if (!results || !teamToggle) return;
        
        const isTeamView = results.classList.contains('team-view');
        
        if (isTeamView) {
            results.classList.remove('team-view');
            teamToggle.classList.remove('hph-btn-primary');
            teamToggle.classList.add('hph-btn-outline');
            this.renderGridView();
        } else {
            results.classList.add('team-view');
            teamToggle.classList.remove('hph-btn-outline');
            teamToggle.classList.add('hph-btn-primary');
            this.renderTeamView();
        }
    }
    
    renderTeamView() {
        const agents = Array.from(document.querySelectorAll('[data-agent-id]'));
        const agentsByOffice = this.groupAgentsByOffice(agents);
        
        let teamHTML = '';
        Object.entries(agentsByOffice).forEach(([officeName, officeAgents]) => {
            teamHTML += this.createTeamSection(officeName, officeAgents);
        });
        
        const resultsContainer = document.getElementById('agent-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = teamHTML;
        }
    }
    
    renderGridView() {
        // Restore original grid layout by re-running the filter
        this.handleFilterSubmit();
    }
    
    groupAgentsByOffice(agents) {
        const grouped = {};
        
        agents.forEach(agentCard => {
            // Try to find office information from the card
            const officeElement = agentCard.querySelector('[data-office]') || 
                                agentCard.querySelector('.agent-office') ||
                                agentCard.querySelector('p:contains("office")');
            
            let officeName = 'Independent Agents';
            if (officeElement) {
                officeName = officeElement.dataset.office || 
                           officeElement.textContent?.trim() || 
                           'Independent Agents';
            }
            
            if (!grouped[officeName]) {
                grouped[officeName] = [];
            }
            grouped[officeName].push(agentCard);
        });
        
        return grouped;
    }
    
    createTeamSection(officeName, agents) {
        return `
            <div class="team-section">
                <div class="team-header">
                    <h3 class="hph-text-xl hph-font-bold">${officeName}</h3>
                    <p class="hph-text-gray-600">${agents.length} agent${agents.length !== 1 ? 's' : ''}</p>
                </div>
                <div class="team-members">
                    ${agents.map(agent => agent.outerHTML).join('')}
                </div>
            </div>
        `;
    }
    
    showMatchingModal() {
        const modal = document.getElementById('agent-matching-modal');
        if (modal) {
            modal.classList.remove('hph-hidden');
            modal.style.display = 'flex';
            
            // Focus first input
            const firstSelect = modal.querySelector('select');
            if (firstSelect) firstSelect.focus();
        }
    }
    
    hideMatchingModal() {
        const modal = document.getElementById('agent-matching-modal');
        if (modal) {
            modal.classList.add('hph-hidden');
            modal.style.display = 'none';
        }
    }
    
    handleAgentMatching() {
        const form = document.getElementById('agent-matching-form');
        if (!form) return;
        
        const formData = new FormData(form);
        
        // Validate required fields
        if (!formData.get('goal')) {
            this.showNotification('Please select your goal', 'warning');
            return;
        }
        
        const params = new URLSearchParams(formData);
        params.append('action', 'hph_match_agents');
        params.append('nonce', window.hphArchive?.nonce || '');
        
        this.showLoadingState();
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.hideMatchingModal();
                this.displayMatchedAgents(data.data.agents);
                this.showNotification(`Found ${data.data.agents.length} matching agents!`, 'success');
            } else {
                this.showError(data.data?.message || 'Error finding matching agents');
            }
        })
        .catch(error => {
            this.showError('Network error occurred');
        })
        .finally(() => {
            this.hideLoadingState();
        });
    }
    
    displayMatchedAgents(agents) {
        // Store preferences in session for match scoring
        const formData = new FormData(document.getElementById('agent-matching-form'));
        const preferences = {};
        for (const [key, value] of formData.entries()) {
            if (value) preferences[key] = value;
        }
        
        // Update the current results with matched agents and scores
        const agentHTML = agents.map(agent => {
            agent.matchScore = this.calculateMatchScore(agent, preferences);
            return this.renderAgentCard(agent);
        }).join('');
        
        const resultsContainer = document.getElementById('agent-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = `<div class="hph-grid hph-grid-cols-1 md:hph-grid-cols-2 lg:hph-grid-cols-3 xl:hph-grid-cols-4 hph-gap-lg">${agentHTML}</div>`;
            
            // Add matched agents indicator
            this.showMatchResultsHeader();
        }
    }
    
    showMatchResultsHeader() {
        const resultsParent = document.getElementById('agent-results').parentElement;
        const existingHeader = resultsParent.querySelector('.match-results-header');
        
        if (!existingHeader) {
            const resultsHeader = document.createElement('div');
            resultsHeader.className = 'match-results-header hph-bg-success hph-bg-opacity-10 hph-border hph-border-success hph-rounded-lg hph-p-md hph-mb-lg';
            resultsHeader.innerHTML = `
                <div class="hph-flex hph-items-center hph-gap-md">
                    <i class="fas fa-magic hph-text-success hph-text-xl"></i>
                    <div>
                        <h3 class="hph-font-semibold hph-text-success">Perfect Matches Found!</h3>
                        <p class="hph-text-sm hph-text-gray-600">These agents match your specific requirements and preferences.</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="hph-btn hph-btn-sm hph-btn-ghost hph-ml-auto">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            resultsParent.insertBefore(resultsHeader, document.getElementById('agent-results'));
        }
    }
    
    calculateMatchScore(agent, preferences) {
        let score = 0;
        let totalFactors = 0;
        
        // Goal matching
        if (preferences.goal && agent.specialties) {
            const goalSpecialtyMap = {
                'buy': ['buyers-agent', 'buyer-specialist'],
                'sell': ['listing-agent', 'seller-specialist'],
                'luxury': ['luxury-homes', 'luxury-specialist'],
                'invest': ['investment-properties', 'investment-specialist'],
                'commercial': ['commercial', 'commercial-specialist']
            };
            
            const goalSpecialties = goalSpecialtyMap[preferences.goal] || [];
            const hasMatchingSpecialty = goalSpecialties.some(specialty => 
                agent.specialties.toLowerCase().includes(specialty.toLowerCase())
            );
            
            if (hasMatchingSpecialty) {
                score += 30;
            }
            totalFactors += 30;
        }
        
        // Language matching
        if (preferences.language && agent.languages) {
            if (agent.languages.toLowerCase().includes(preferences.language.toLowerCase())) {
                score += 20;
            }
            totalFactors += 20;
        }
        
        // Experience factor
        if (agent.experience || agent.years_experience) {
            const experience = parseInt(agent.experience || agent.years_experience || 0);
            if (experience >= 15) score += 15;
            else if (experience >= 10) score += 12;
            else if (experience >= 5) score += 8;
            else score += 5;
            totalFactors += 15;
        }
        
        // Performance factors
        if (agent.salesVolume || agent.total_sales_volume) {
            const volume = parseInt(agent.salesVolume || agent.total_sales_volume || 0);
            if (volume > 10000000) score += 15;
            else if (volume > 5000000) score += 10;
            else if (volume > 1000000) score += 5;
            totalFactors += 15;
        }
        
        // First-time buyer specialty
        if (preferences.first_time === 'yes' && agent.specialties?.toLowerCase().includes('first-time')) {
            score += 10;
        }
        totalFactors += 10;
        
        // Property type matching
        if (preferences.property_type && agent.specialties) {
            if (agent.specialties.toLowerCase().includes(preferences.property_type.toLowerCase())) {
                score += 10;
            }
        }
        totalFactors += 10;
        
        return totalFactors > 0 ? Math.min(100, Math.round((score / totalFactors) * 100)) : 0;
    }
    
    renderAgentCard(agent) {
        const matchScoreClass = agent.matchScore >= 80 ? 'match-score-high' : 
                               agent.matchScore >= 60 ? 'match-score-medium' : 'match-score-low';
        
        return `
            <article class="agent-card-enhanced hph-card hph-card-elevated hph-h-full hph-flex hph-flex-col hph-transition-all hover:hph-shadow-xl" 
                     data-agent-id="${agent.id}">
                <div class="agent-match-score ${matchScoreClass}">
                    ${agent.matchScore}% Match
                </div>
                <div class="hph-p-md hph-flex-1">
                    <h3 class="hph-text-lg hph-font-semibold hph-mb-xs">${agent.name}</h3>
                    <p class="hph-text-sm hph-text-primary hph-mb-sm">${agent.title || 'Real Estate Professional'}</p>
                    <p class="hph-text-sm hph-text-gray-600 hph-mb-md">${agent.specialties || ''}</p>
                    <div class="hph-flex hph-justify-between hph-items-center hph-mt-auto">
                        <span class="hph-text-xs hph-text-gray-500">${agent.experience || 0} years exp</span>
                        <button class="hph-btn hph-btn-sm hph-btn-primary" onclick="this.closest('article').dispatchEvent(new CustomEvent('contact-agent'))">
                            Contact
                        </button>
                    </div>
                </div>
            </article>
        `;
    }
    
    showQuickContact(agentId) {
        const modal = this.createQuickContactModal(agentId);
        document.body.appendChild(modal);
        modal.style.display = 'flex';
    }
    
    showContactForm(agentId) {
        const modal = this.createContactFormModal(agentId);
        document.body.appendChild(modal);
        modal.style.display = 'flex';
    }
    
    createQuickContactModal(agentId) {
        const modal = document.createElement('div');
        modal.className = 'hph-fixed hph-inset-0 hph-bg-black hph-bg-opacity-50 hph-z-50 hph-flex hph-items-center hph-justify-center hph-p-md';
        modal.innerHTML = `
            <div class="hph-bg-white hph-rounded-xl hph-max-w-md hph-w-full hph-p-lg">
                <div class="hph-flex hph-justify-between hph-items-center hph-mb-lg">
                    <h3 class="hph-text-xl hph-font-bold">Quick Contact</h3>
                    <button class="hph-btn hph-btn-sm hph-btn-ghost close-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form class="contact-form" data-agent-id="${agentId}">
                    <div class="hph-space-y-md">
                        <div>
                            <label class="hph-block hph-mb-sm hph-font-medium">Your Name *</label>
                            <input type="text" name="name" required class="hph-w-full hph-px-md hph-py-sm hph-border hph-rounded-lg">
                        </div>
                        
                        <div>
                            <label class="hph-block hph-mb-sm hph-font-medium">Email *</label>
                            <input type="email" name="email" required class="hph-w-full hph-px-md hph-py-sm hph-border hph-rounded-lg">
                        </div>
                        
                        <div>
                            <label class="hph-block hph-mb-sm hph-font-medium">Phone</label>
                            <input type="tel" name="phone" class="hph-w-full hph-px-md hph-py-sm hph-border hph-rounded-lg">
                        </div>
                        
                        <div>
                            <label class="hph-block hph-mb-sm hph-font-medium">Message *</label>
                            <textarea name="message" required rows="4" class="hph-w-full hph-px-md hph-py-sm hph-border hph-rounded-lg" placeholder="I'm interested in..."></textarea>
                        </div>
                        
                        <button type="submit" class="hph-btn hph-btn-primary hph-w-full hph-py-md">
                            <i class="fas fa-paper-plane hph-mr-sm"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        // Bind events
        modal.querySelector('.close-modal').addEventListener('click', () => {
            modal.remove();
        });
        
        modal.querySelector('.contact-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitContactForm(e.target, modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
        
        return modal;
    }
    
    createContactFormModal(agentId) {
        // For now, use the same as quick contact but could be extended
        return this.createQuickContactModal(agentId);
    }
    
    submitContactForm(form, modal) {
        const formData = new FormData(form);
        const agentId = form.dataset.agentId;
        
        formData.append('action', 'hph_contact_agent');
        formData.append('nonce', window.hphArchive?.nonce || '');
        formData.append('agent_id', agentId);
        
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin hph-mr-sm"></i> Sending...';
        }
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Message sent successfully!', 'success');
                modal.remove();
            } else {
                this.showNotification(data.data?.message || 'Error sending message', 'error');
            }
        })
        .catch(error => {
            this.showNotification('Network error occurred', 'error');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane hph-mr-sm"></i> Send Message';
            }
        });
    }
    
    toggleFavorite(agentId, button) {
        if (!window.hphUser?.isLoggedIn) {
            this.showLoginPrompt();
            return;
        }
        
        const isFavorited = this.favoriteAgents.has(agentId);
        const action = isFavorited ? 'remove' : 'add';
        
        // Optimistic update
        this.updateFavoriteButton(button, !isFavorited);
        
        const params = new URLSearchParams({
            action: 'hph_toggle_agent_favorite',
            nonce: window.hphArchive?.nonce || '',
            agent_id: agentId,
            favorite_action: action
        });
        
        fetch(window.hphArchive.ajaxUrl, {
            method: 'POST',
            body: params
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (action === 'add') {
                    this.favoriteAgents.add(agentId);
                } else {
                    this.favoriteAgents.delete(agentId);
                }
                this.showNotification(data.data?.message || 'Favorite updated', 'success');
            } else {
                // Revert optimistic update
                this.updateFavoriteButton(button, isFavorited);
                this.showNotification(data.data?.message || 'Error updating favorite', 'error');
            }
        })
        .catch(error => {
            this.updateFavoriteButton(button, isFavorited);
            this.showNotification('Network error occurred', 'error');
        });
    }
    
    handleShare(url, title) {
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            
            });
        } else {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    this.showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    this.showNotification('Unable to copy link', 'error');
                });
            } else {
                this.showNotification('Sharing not available', 'warning');
            }
        }
    }
    
    // Helper methods
    updateResults(html) {
        const container = document.getElementById('agent-results');
        if (container) {
            container.innerHTML = html;
            
            // Add entrance animations
            document.querySelectorAll('.agent-card-enhanced').forEach((card, index) => {
                card.style.animationDelay = `${index * 100}ms`;
                card.classList.add('agent-card-enter');
            });
            
            // Re-add selection checkboxes if in selection mode
            if (document.body.classList.contains('agent-selection-mode')) {
                setTimeout(() => this.addSelectionCheckboxes(), 100);
            }
        }
    }
    
    appendResults(html) {
        const container = document.getElementById('agent-results');
        if (!container) return;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        while (tempDiv.firstChild) {
            container.appendChild(tempDiv.firstChild);
        }
        
        // Re-add selection checkboxes if in selection mode
        if (document.body.classList.contains('agent-selection-mode')) {
            this.addSelectionCheckboxes();
        }
    }
    
    updateStatistics(stats) {
        if (stats && typeof stats === 'object') {
            const statsContainer = document.querySelector('.agent-statistics');
            if (statsContainer && stats.avgExperience) {
                statsContainer.innerHTML = `
                    <span><i class="fas fa-award hph-mr-xs"></i> ${stats.avgExperience} avg years experience</span>
                    <span><i class="fas fa-globe hph-mr-xs"></i> ${stats.languages || 0} languages spoken</span>
                    <span><i class="fas fa-star hph-mr-xs"></i> Top: ${stats.topSpecialty || 'General'}</span>
                `;
            }
        }
    }
    
    updateFavoriteButton(button, isFavorited) {
        const icon = button.querySelector('i');
        
        if (isFavorited) {
            button.classList.add('favorited');
            if (icon) icon.className = 'fas fa-heart';
            button.title = 'Remove from Favorites';
        } else {
            button.classList.remove('favorited');
            if (icon) icon.className = 'far fa-heart';
            button.title = 'Add to Favorites';
        }
    }
    
    showLoadingState() {
        const results = document.getElementById('agent-results');
        if (!results) return;
        
        const existingOverlay = results.querySelector('.loading-overlay');
        if (existingOverlay) return;
        
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'loading-overlay loading-agents';
        loadingOverlay.innerHTML = '<div class="spinner"></div>';
        results.appendChild(loadingOverlay);
    }
    
    hideLoadingState() {
        document.querySelectorAll('.loading-overlay').forEach(overlay => {
            overlay.remove();
        });
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : 'var(--hph-primary)'};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    showLoginPrompt() {
        this.showNotification('Please log in to use this feature', 'warning');
    }
    
    updateURL(params) {
        const url = new URL(window.location);
        url.search = '';
        
        for (const [key, value] of params.entries()) {
            if (value && key !== 'action' && key !== 'nonce' && key !== 'page') {
                url.searchParams.set(key, value);
            }
        }
        
        window.history.replaceState({}, '', url);
    }
    
    createHiddenInput(name) {
        const form = document.getElementById('filter-form');
        if (!form) return null;
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        form.appendChild(input);
        return input;
    }
    
    loadUserPreferences() {
        if (window.hphUser?.favoriteAgents) {
            this.favoriteAgents = new Set(window.hphUser.favoriteAgents);
        }
        
        // Update UI based on loaded preferences
        this.updateFavoriteDisplay();
    }
    
    updateFavoriteDisplay() {
        document.querySelectorAll('[data-action="favorite"]').forEach(btn => {
            const agentId = btn.dataset.agentId;
            if (this.favoriteAgents.has(agentId)) {
                this.updateFavoriteButton(btn, true);
            }
        });
    }
    
    initializeModals() {
        // Close modals on outside click
        document.addEventListener('click', (e) => {
            if (e.target.matches('.hph-fixed.hph-inset-0')) {
                e.target.remove();
            }
        });
        
        // Close modals on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.hph-fixed.hph-inset-0').forEach(modal => {
                    modal.remove();
                });
            }
        });
    }
}

// Add required CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new EnhancedAgentArchive();
        testEnhancedAgentArchive();
    });
} else {
    new EnhancedAgentArchive();
    testEnhancedAgentArchive();
}

/**
 * Test function to verify all functionality is working
 */
function testEnhancedAgentArchive() {
    if (typeof window.hphArchive === 'undefined') {
        return;
    }
    
    const tests = {
        'Filter Form': () => !!document.getElementById('filter-form'),
        'Sort Select': () => !!document.getElementById('sort-select'),
        'Team Toggle': () => !!document.getElementById('team-view-toggle'),
        'Results Container': () => !!document.getElementById('agent-results'),
        'Match Agent Button': () => !!document.getElementById('match-agent-btn'),
        'Contact Multiple Button': () => !!document.getElementById('contact-multiple-btn'),
        'Advanced Search Toggle': () => !!document.getElementById('advanced-search-toggle'),
        'Advanced Search Panel': () => !!document.getElementById('advanced-search-panel'),
        'Agent Matching Modal': () => !!document.getElementById('agent-matching-modal'),
        'AJAX URL': () => !!window.hphArchive.ajaxUrl,
        'Nonce': () => !!window.hphArchive.nonce,
        'CSS Variables': () => {
            const root = getComputedStyle(document.documentElement);
            return root.getPropertyValue('--hph-primary').trim() !== '';
        },
        'View Buttons': () => document.querySelectorAll('[data-view]').length > 0,
        'Quick Filters': () => document.querySelectorAll('.quick-filters a').length > 0
    };
    
    let passed = 0;
    let failed = 0;
    
    
    for (const [testName, testFn] of Object.entries(tests)) {
        try {
            if (testFn()) {
                passed++;
            } else {
                failed++;
            }
        } catch (error) {
            failed++;
        }
    }
    
    
    if (failed === 0) {
    } else {
    }
    
    
    // Test advanced features
    
    const advancedTests = {
        'Advanced Search Toggle Function': () => {
            const toggle = document.getElementById('advanced-search-toggle');
            const panel = document.getElementById('advanced-search-panel');
            return toggle && panel && panel.classList.contains('hph-hidden');
        },
        'Agent Selection System': () => {
            return !document.body.classList.contains('agent-selection-mode');
        },
        'Modal System': () => {
            const modal = document.getElementById('agent-matching-modal');
            return modal && modal.classList.contains('hph-hidden');
        },
        'Local Storage Support': () => {
            try {
                localStorage.setItem('test', 'test');
                localStorage.removeItem('test');
                return true;
            } catch {
                return false;
            }
        },
        'Fetch API Support': () => typeof fetch !== 'undefined',
        'CSS Grid Support': () => {
            const test = document.createElement('div');
            return typeof test.style.display === 'string' && 
                   CSS.supports('display', 'grid');
        }
    };
    
    for (const [testName, testFn] of Object.entries(advancedTests)) {
        try {
            if (testFn()) {
            } else {
            }
        } catch (error) {
        }
    }
    
}
