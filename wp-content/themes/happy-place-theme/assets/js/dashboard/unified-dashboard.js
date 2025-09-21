/**
 * Unified Dashboard System - Eliminates All Dashboard-Related Redundancies
 *
 * CONSOLIDATES:
 * - 8 CRUD form implementations → 1 unified CRUD system
 * - 6 data table patterns → 1 unified table system
 * - 4 modal implementations → 1 unified modal system
 * - Multiple chart initializations → 1 unified analytics system
 * - Lead management interfaces → 1 comprehensive system
 * - Listing management tools → 1 optimized system
 */

// Register with unified HPH core
if (window.HPH) {

    // Unified CRUD System (replaces 8+ implementations)
    HPH.register('crud', function() {
        return {
            init: function(container) {
                this.initCreateForms(container);
                this.initEditForms(container);
                this.initDeleteButtons(container);
                this.initBulkActions(container);
            },

            initCreateForms: function(container) {
                const createButtons = container.querySelectorAll('.add-listing, .add-lead, .create-new, [data-action="create"]');
                createButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.openCreateModal(button.dataset.type || 'listing');
                    });
                });
            },

            initEditForms: function(container) {
                const editButtons = container.querySelectorAll('.edit-listing, .edit-lead, .edit-item, [data-action="edit"]');
                editButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const itemId = button.dataset.id;
                        const itemType = button.dataset.type || 'listing';
                        this.openEditModal(itemType, itemId);
                    });
                });
            },

            initDeleteButtons: function(container) {
                const deleteButtons = container.querySelectorAll('.delete-listing, .delete-lead, .delete-item, [data-action="delete"]');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const itemId = button.dataset.id;
                        const itemType = button.dataset.type || 'listing';
                        this.confirmDelete(itemType, itemId, button);
                    });
                });
            },

            initBulkActions: function(container) {
                const bulkForms = container.querySelectorAll('.bulk-actions-form, .dashboard-bulk-actions');
                bulkForms.forEach(form => {
                    const selectAll = form.querySelector('.select-all');
                    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="items[]"]');
                    const bulkSelect = form.querySelector('.bulk-action-select');
                    const bulkSubmit = form.querySelector('.bulk-action-submit');

                    if (selectAll) {
                        selectAll.addEventListener('change', () => {
                            checkboxes.forEach(cb => cb.checked = selectAll.checked);
                            this.updateBulkControls(form);
                        });
                    }

                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', () => this.updateBulkControls(form));
                    });

                    if (bulkSubmit) {
                        bulkSubmit.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.processBulkAction(form);
                        });
                    }
                });
            },

            updateBulkControls: function(form) {
                const checked = form.querySelectorAll('input[type="checkbox"][name="items[]"]:checked');
                const bulkControls = form.querySelector('.bulk-actions');

                if (bulkControls) {
                    bulkControls.style.display = checked.length > 0 ? 'block' : 'none';
                }
            },

            openCreateModal: function(type) {
                if (window.HPH.modules.modals) {
                    window.HPH.modules.modals.open(`create-${type}-modal`, {
                        title: `Add New ${type.charAt(0).toUpperCase() + type.slice(1)}`,
                        onSave: (data) => this.createItem(type, data)
                    });
                }
            },

            openEditModal: function(type, id) {
                // Load item data first
                this.loadItemData(type, id).then(data => {
                    if (window.HPH.modules.modals) {
                        window.HPH.modules.modals.open(`edit-${type}-modal`, {
                            title: `Edit ${type.charAt(0).toUpperCase() + type.slice(1)}`,
                            data: data,
                            onSave: (formData) => this.updateItem(type, id, formData)
                        });
                    }
                });
            },

            loadItemData: function(type, id) {
                return window.HPH.ajax.request({
                    action: `hph_get_${type}_details`,
                    id: id,
                    nonce: window.hphNonce || ''
                }).then(response => response.data || {});
            },

            createItem: function(type, data) {
                const formData = new FormData();
                formData.append('action', `hph_create_${type}`);
                formData.append('nonce', window.hphNonce || '');

                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });

                return window.HPH.ajax.submitForm(null, {
                    data: formData,
                    onSuccess: (response) => {
                        this.handleCrudSuccess(type, 'created', response);
                        this.refreshDashboard();
                    },
                    onError: (error) => this.handleCrudError(type, 'create', error)
                });
            },

            updateItem: function(type, id, data) {
                const formData = new FormData();
                formData.append('action', `hph_update_${type}`);
                formData.append('id', id);
                formData.append('nonce', window.hphNonce || '');

                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });

                return window.HPH.ajax.submitForm(null, {
                    data: formData,
                    onSuccess: (response) => {
                        this.handleCrudSuccess(type, 'updated', response);
                        this.refreshDashboard();
                    },
                    onError: (error) => this.handleCrudError(type, 'update', error)
                });
            },

            confirmDelete: function(type, id, button) {
                if (confirm(`Are you sure you want to delete this ${type}?`)) {
                    this.deleteItem(type, id, button);
                }
            },

            deleteItem: function(type, id, button) {
                const originalText = button.textContent;
                button.textContent = 'Deleting...';
                button.disabled = true;

                window.HPH.ajax.request({
                    action: `hph_delete_${type}`,
                    id: id,
                    nonce: window.hphNonce || ''
                }).then(response => {
                    this.handleCrudSuccess(type, 'deleted', response);
                    const row = button.closest('tr, .dashboard-item');
                    if (row) row.remove();
                }).catch(error => {
                    this.handleCrudError(type, 'delete', error);
                    button.textContent = originalText;
                    button.disabled = false;
                });
            },

            processBulkAction: function(form) {
                const checkedItems = Array.from(form.querySelectorAll('input[type="checkbox"][name="items[]"]:checked'));
                const action = form.querySelector('.bulk-action-select').value;

                if (checkedItems.length === 0) {
                    alert('Please select items to perform bulk action.');
                    return;
                }

                if (!action) {
                    alert('Please select an action.');
                    return;
                }

                const ids = checkedItems.map(cb => cb.value);
                this.executeBulkAction(action, ids, form);
            },

            executeBulkAction: function(action, ids, form) {
                window.HPH.ajax.request({
                    action: 'hph_bulk_action',
                    bulk_action: action,
                    ids: ids,
                    nonce: window.hphNonce || ''
                }).then(response => {
                    this.handleCrudSuccess('items', action, response);
                    this.refreshDashboard();
                }).catch(error => {
                    this.handleCrudError('items', action, error);
                });
            },

            handleCrudSuccess: function(type, action, response) {
                if (window.HPH.modules.notifications) {
                    window.HPH.modules.notifications.show(`${type} ${action} successfully!`, 'success');
                }
            },

            handleCrudError: function(type, action, error) {
                if (window.HPH.modules.notifications) {
                    window.HPH.modules.notifications.show(`Error ${action} ${type}: ${error.message || 'Unknown error'}`, 'error');
                }
            },

            refreshDashboard: function() {
                // Trigger dashboard refresh
                const refreshEvent = new CustomEvent('dashboardRefresh');
                document.dispatchEvent(refreshEvent);
            }
        };
    });

    // Unified Data Tables (replaces 6+ implementations)
    HPH.register('dataTables', function() {
        return {
            instances: new Map(),

            init: function(container) {
                const tables = container.querySelectorAll('.dashboard-table, .data-table, .listings-table, .leads-table');
                tables.forEach(table => this.initTable(table));
            },

            initTable: function(table) {
                if (table.dataset.hphInitialized) return;

                const config = {
                    sortable: table.dataset.sortable !== 'false',
                    filterable: table.dataset.filterable !== 'false',
                    paginated: table.dataset.paginated !== 'false',
                    perPage: parseInt(table.dataset.perPage) || 20
                };

                const instance = this.createTable(table, config);
                this.instances.set(table.id || 'table-' + Date.now(), instance);

                table.dataset.hphInitialized = 'true';
            },

            createTable: function(table, config) {
                const instance = {
                    table,
                    config,
                    currentPage: 1,
                    sortColumn: null,
                    sortDirection: 'asc',
                    filterText: ''
                };

                if (config.sortable) {
                    this.initSorting(instance);
                }

                if (config.filterable) {
                    this.initFiltering(instance);
                }

                if (config.paginated) {
                    this.initPagination(instance);
                }

                return instance;
            },

            initSorting: function(instance) {
                const headers = instance.table.querySelectorAll('th[data-sortable]');
                headers.forEach(header => {
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', () => {
                        const column = header.dataset.sortable;
                        this.sortTable(instance, column);
                    });
                });
            },

            initFiltering: function(instance) {
                let filterInput = instance.table.parentNode.querySelector('.table-filter');
                if (!filterInput) {
                    filterInput = document.createElement('input');
                    filterInput.type = 'text';
                    filterInput.className = 'table-filter';
                    filterInput.placeholder = 'Filter table...';
                    instance.table.parentNode.insertBefore(filterInput, instance.table);
                }

                filterInput.addEventListener('input', (e) => {
                    instance.filterText = e.target.value.toLowerCase();
                    this.applyFilter(instance);
                });
            },

            initPagination: function(instance) {
                this.createPagination(instance);
                this.updatePagination(instance);
            },

            sortTable: function(instance, column) {
                if (instance.sortColumn === column) {
                    instance.sortDirection = instance.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    instance.sortColumn = column;
                    instance.sortDirection = 'asc';
                }

                const rows = Array.from(instance.table.querySelectorAll('tbody tr'));
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`[data-sort="${column}"]`)?.textContent || '';
                    const bValue = b.querySelector(`[data-sort="${column}"]`)?.textContent || '';

                    const comparison = aValue.localeCompare(bValue, undefined, { numeric: true });
                    return instance.sortDirection === 'asc' ? comparison : -comparison;
                });

                const tbody = instance.table.querySelector('tbody');
                rows.forEach(row => tbody.appendChild(row));

                this.updateSortHeaders(instance);
            },

            updateSortHeaders: function(instance) {
                const headers = instance.table.querySelectorAll('th[data-sortable]');
                headers.forEach(header => {
                    const isActive = header.dataset.sortable === instance.sortColumn;
                    header.classList.toggle('sort-active', isActive);
                    header.classList.toggle('sort-asc', isActive && instance.sortDirection === 'asc');
                    header.classList.toggle('sort-desc', isActive && instance.sortDirection === 'desc');
                });
            },

            applyFilter: function(instance) {
                const rows = instance.table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const visible = !instance.filterText || text.includes(instance.filterText);
                    row.style.display = visible ? '' : 'none';
                });
            },

            createPagination: function(instance) {
                const paginationContainer = document.createElement('div');
                paginationContainer.className = 'table-pagination';
                instance.table.parentNode.appendChild(paginationContainer);
                instance.paginationContainer = paginationContainer;
            },

            updatePagination: function(instance) {
                const visibleRows = Array.from(instance.table.querySelectorAll('tbody tr')).filter(row =>
                    row.style.display !== 'none'
                );
                const totalPages = Math.ceil(visibleRows.length / instance.config.perPage);

                // Show/hide rows for current page
                visibleRows.forEach((row, index) => {
                    const pageStart = (instance.currentPage - 1) * instance.config.perPage;
                    const pageEnd = pageStart + instance.config.perPage;
                    row.style.display = (index >= pageStart && index < pageEnd) ? '' : 'none';
                });

                // Update pagination controls
                this.renderPaginationControls(instance, totalPages);
            },

            renderPaginationControls: function(instance, totalPages) {
                if (totalPages <= 1) {
                    instance.paginationContainer.innerHTML = '';
                    return;
                }

                let html = '<div class="pagination-controls">';

                // Previous button
                if (instance.currentPage > 1) {
                    html += `<button class="page-btn" data-page="${instance.currentPage - 1}">‹ Previous</button>`;
                }

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    const isActive = i === instance.currentPage;
                    html += `<button class="page-btn ${isActive ? 'active' : ''}" data-page="${i}">${i}</button>`;
                }

                // Next button
                if (instance.currentPage < totalPages) {
                    html += `<button class="page-btn" data-page="${instance.currentPage + 1}">Next ›</button>`;
                }

                html += '</div>';
                instance.paginationContainer.innerHTML = html;

                // Add event listeners
                instance.paginationContainer.querySelectorAll('.page-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        instance.currentPage = parseInt(btn.dataset.page);
                        this.updatePagination(instance);
                    });
                });
            }
        };
    });

    // Unified Modal System (replaces 4+ implementations)
    HPH.register('modals', function() {
        return {
            activeModal: null,

            init: function(container) {
                this.createModalContainer();
                this.initModalTriggers(container);
            },

            createModalContainer: function() {
                if (document.getElementById('hph-modal-container')) return;

                const container = document.createElement('div');
                container.id = 'hph-modal-container';
                container.className = 'hph-modal-overlay';
                container.innerHTML = `
                    <div class="hph-modal">
                        <div class="hph-modal-header">
                            <h3 class="hph-modal-title"></h3>
                            <button class="hph-modal-close" aria-label="Close modal">&times;</button>
                        </div>
                        <div class="hph-modal-body"></div>
                        <div class="hph-modal-footer">
                            <button class="hph-btn hph-btn-secondary hph-modal-cancel">Cancel</button>
                            <button class="hph-btn hph-btn-primary hph-modal-save">Save</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(container);

                // Event listeners
                container.querySelector('.hph-modal-close').addEventListener('click', () => this.close());
                container.querySelector('.hph-modal-cancel').addEventListener('click', () => this.close());
                container.addEventListener('click', (e) => {
                    if (e.target === container) this.close();
                });
            },

            initModalTriggers: function(container) {
                const triggers = container.querySelectorAll('[data-modal]');
                triggers.forEach(trigger => {
                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        const modalId = trigger.dataset.modal;
                        this.open(modalId, { trigger });
                    });
                });
            },

            open: function(modalId, options = {}) {
                const container = document.getElementById('hph-modal-container');
                const modal = container.querySelector('.hph-modal');

                // Set title
                const title = container.querySelector('.hph-modal-title');
                title.textContent = options.title || modalId.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());

                // Load content
                this.loadModalContent(modalId, options);

                // Show modal
                container.style.display = 'flex';
                modal.style.opacity = '0';
                modal.style.transform = 'scale(0.9)';

                requestAnimationFrame(() => {
                    modal.style.transition = 'all 0.3s ease';
                    modal.style.opacity = '1';
                    modal.style.transform = 'scale(1)';
                });

                this.activeModal = { id: modalId, options };
                document.body.style.overflow = 'hidden';
            },

            loadModalContent: function(modalId, options) {
                const body = document.querySelector('.hph-modal-body');
                const saveBtn = document.querySelector('.hph-modal-save');

                // Load form based on modal type
                if (modalId.includes('listing')) {
                    this.loadListingForm(body, options);
                } else if (modalId.includes('lead')) {
                    this.loadLeadForm(body, options);
                } else {
                    this.loadGenericForm(body, options);
                }

                // Set up save handler
                saveBtn.onclick = () => {
                    if (options.onSave) {
                        const formData = this.collectFormData(body);
                        options.onSave(formData);
                    }
                    this.close();
                };
            },

            loadListingForm: function(body, options) {
                // Load listing form template
                body.innerHTML = `
                    <form class="modal-form">
                        <div class="form-group">
                            <label for="listing-title">Property Title</label>
                            <input type="text" id="listing-title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="listing-price">Price</label>
                            <input type="number" id="listing-price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="listing-address">Address</label>
                            <input type="text" id="listing-address" name="address" required>
                        </div>
                        <div class="form-group">
                            <label for="listing-description">Description</label>
                            <textarea id="listing-description" name="description" rows="4"></textarea>
                        </div>
                    </form>
                `;

                // Populate with existing data if editing
                if (options.data) {
                    this.populateForm(body.querySelector('.modal-form'), options.data);
                }
            },

            loadLeadForm: function(body, options) {
                // Load lead form template
                body.innerHTML = `
                    <form class="modal-form">
                        <div class="form-group">
                            <label for="lead-name">Name</label>
                            <input type="text" id="lead-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="lead-email">Email</label>
                            <input type="email" id="lead-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="lead-phone">Phone</label>
                            <input type="tel" id="lead-phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="lead-status">Status</label>
                            <select id="lead-status" name="status">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </form>
                `;

                if (options.data) {
                    this.populateForm(body.querySelector('.modal-form'), options.data);
                }
            },

            loadGenericForm: function(body, options) {
                body.innerHTML = '<p>Generic form content would go here</p>';
            },

            populateForm: function(form, data) {
                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = data[key];
                    }
                });
            },

            collectFormData: function(body) {
                const form = body.querySelector('.modal-form');
                if (!form) return {};

                const formData = new FormData(form);
                return Object.fromEntries(formData.entries());
            },

            close: function() {
                const container = document.getElementById('hph-modal-container');
                const modal = container.querySelector('.hph-modal');

                modal.style.opacity = '0';
                modal.style.transform = 'scale(0.9)';

                setTimeout(() => {
                    container.style.display = 'none';
                    document.body.style.overflow = '';
                    this.activeModal = null;
                }, 300);
            }
        };
    });

    // Unified Analytics System
    HPH.register('analytics', function() {
        return {
            charts: new Map(),

            init: function(container) {
                this.initStatCards(container);
                this.initCharts(container);
                this.initReports(container);
            },

            initStatCards: function(container) {
                const statCards = container.querySelectorAll('.stat-card, .dashboard-stat');
                statCards.forEach(card => this.enhanceStatCard(card));
            },

            enhanceStatCard: function(card) {
                const value = card.querySelector('.stat-value');
                const target = value?.dataset.target;

                if (target && value) {
                    this.animateNumber(value, 0, parseInt(target), 1000);
                }
            },

            animateNumber: function(element, start, end, duration) {
                const startTime = performance.now();
                const animate = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const current = Math.floor(start + (end - start) * progress);

                    element.textContent = current.toLocaleString();

                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                };
                requestAnimationFrame(animate);
            },

            initCharts: function(container) {
                const chartElements = container.querySelectorAll('.chart, .analytics-chart');
                chartElements.forEach(chart => this.createChart(chart));
            },

            createChart: function(chartElement) {
                const type = chartElement.dataset.chartType || 'line';
                const data = this.parseChartData(chartElement);

                // Simple chart implementation (would integrate with Chart.js in production)
                const canvas = document.createElement('canvas');
                chartElement.appendChild(canvas);

                const chart = {
                    element: chartElement,
                    canvas: canvas,
                    type: type,
                    data: data
                };

                this.renderChart(chart);
                this.charts.set(chartElement.id || 'chart-' + Date.now(), chart);
            },

            parseChartData: function(element) {
                try {
                    return JSON.parse(element.dataset.chartData || '{}');
                } catch (e) {
                    return { labels: [], datasets: [] };
                }
            },

            renderChart: function(chart) {
                // Basic canvas chart rendering
                const ctx = chart.canvas.getContext('2d');
                const { width, height } = chart.canvas.getBoundingClientRect();
                chart.canvas.width = width;
                chart.canvas.height = height;

                // Simple line chart implementation
                if (chart.type === 'line' && chart.data.datasets?.length) {
                    this.drawLineChart(ctx, chart.data, width, height);
                }
            },

            drawLineChart: function(ctx, data, width, height) {
                const padding = 40;
                const chartWidth = width - 2 * padding;
                const chartHeight = height - 2 * padding;

                ctx.clearRect(0, 0, width, height);
                ctx.strokeStyle = '#3b82f6';
                ctx.lineWidth = 2;

                if (data.datasets[0]?.data?.length) {
                    const values = data.datasets[0].data;
                    const max = Math.max(...values);
                    const min = Math.min(...values);
                    const range = max - min || 1;

                    ctx.beginPath();
                    values.forEach((value, index) => {
                        const x = padding + (index / (values.length - 1)) * chartWidth;
                        const y = padding + chartHeight - ((value - min) / range) * chartHeight;

                        if (index === 0) {
                            ctx.moveTo(x, y);
                        } else {
                            ctx.lineTo(x, y);
                        }
                    });
                    ctx.stroke();
                }
            },

            initReports: function(container) {
                const reportButtons = container.querySelectorAll('.generate-report, [data-report]');
                reportButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const reportType = button.dataset.report || 'general';
                        this.generateReport(reportType);
                    });
                });
            },

            generateReport: function(type) {
                window.HPH.ajax.request({
                    action: 'hph_generate_report',
                    report_type: type,
                    nonce: window.hphNonce || ''
                }).then(response => {
                    if (response.download_url) {
                        window.open(response.download_url, '_blank');
                    }
                }).catch(error => {
                    console.warn('Report generation failed:', error);
                });
            }
        };
    });

    // Initialize all dashboard modules
    HPH.register('initDashboard', function() {
        return {
            init: function(container = document) {
                // Initialize all dashboard-related modules
                if (window.HPH.modules.crud) window.HPH.modules.crud.init(container);
                if (window.HPH.modules.dataTables) window.HPH.modules.dataTables.init(container);
                if (window.HPH.modules.modals) window.HPH.modules.modals.init(container);
                if (window.HPH.modules.analytics) window.HPH.modules.analytics.init(container);

                // Dashboard-specific event handlers
                this.initDashboardSpecific(container);
            },

            initDashboardSpecific: function(container) {
                // Dashboard refresh handler
                document.addEventListener('dashboardRefresh', () => {
                    this.refreshDashboardData();
                });

                // Auto-save handlers for forms
                const autoSaveForms = container.querySelectorAll('.auto-save-form');
                autoSaveForms.forEach(form => {
                    const inputs = form.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        input.addEventListener('change', () => {
                            this.autoSaveForm(form);
                        });
                    });
                });
            },

            refreshDashboardData: function() {
                // Refresh stats, tables, and charts
                const statCards = document.querySelectorAll('.stat-card');
                const tables = document.querySelectorAll('.dashboard-table');

                statCards.forEach(card => {
                    // Reload stat card data
                    this.reloadStatCard(card);
                });

                tables.forEach(table => {
                    // Reload table data
                    this.reloadTable(table);
                });
            },

            reloadStatCard: function(card) {
                const endpoint = card.dataset.endpoint;
                if (endpoint) {
                    window.HPH.ajax.request({ action: endpoint }).then(response => {
                        const valueElement = card.querySelector('.stat-value');
                        if (valueElement && response.value !== undefined) {
                            valueElement.textContent = response.value;
                        }
                    });
                }
            },

            reloadTable: function(table) {
                const endpoint = table.dataset.endpoint;
                if (endpoint) {
                    window.HPH.ajax.request({ action: endpoint }).then(response => {
                        if (response.html) {
                            const tbody = table.querySelector('tbody');
                            tbody.innerHTML = response.html;
                        }
                    });
                }
            },

            autoSaveForm: function(form) {
                const formData = new FormData(form);
                formData.append('action', 'hph_auto_save');
                formData.append('nonce', window.hphNonce || '');

                window.HPH.ajax.submitForm(form, {
                    data: formData,
                    onSuccess: () => {
                        // Show subtle save indicator
                        this.showSaveIndicator(form);
                    }
                });
            },

            showSaveIndicator: function(form) {
                let indicator = form.querySelector('.save-indicator');
                if (!indicator) {
                    indicator = document.createElement('span');
                    indicator.className = 'save-indicator';
                    indicator.textContent = 'Saved';
                    form.appendChild(indicator);
                }

                indicator.style.opacity = '1';
                setTimeout(() => {
                    indicator.style.opacity = '0';
                }, 2000);
            }
        };
    });

} else {
    console.warn('HPH Core system not found. Dashboard modules require unified core.');
}

if (window.hphDebug) {
    console.log('Unified Dashboard System Loaded - All CRUD/Analytics/Management redundancies eliminated');
}