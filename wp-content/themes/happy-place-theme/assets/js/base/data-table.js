/**
 * Data Table Component JavaScript
 * Enhanced responsive tables with search, sort, and filter functionality
 * 
 * @package HappyPlaceTheme
 */

(function($) {
    'use strict';
    
    // Ensure HPH global exists
    if (typeof window.HPH === 'undefined') {
        window.HPH = {};
    }
    
    // Data Table namespace
    HPH.DataTable = {
        
        /**
         * Initialize data tables
         */
        init: function() {
            $('.hph-data-table').each(function() {
                HPH.DataTable.initTable($(this));
            });
        },
        
        /**
         * Initialize individual table
         */
        initTable: function($table) {
            var instance = {
                $table: $table,
                $searchInput: $table.find('.hph-data-table__search-input'),
                $filters: $table.find('.hph-data-table__filter'),
                $sortHeaders: $table.find('.hph-data-table__header-sortable'),
                $tbody: $table.find('tbody'),
                $rows: $table.find('tbody tr'),
                currentSort: { column: null, direction: 'asc' },
                activeFilters: {}
            };
            
            // Store instance on table element
            $table.data('dataTable', instance);
            
            // Initialize features
            HPH.DataTable.initSearch(instance);
            HPH.DataTable.initSorting(instance);
            HPH.DataTable.initFilters(instance);
            HPH.DataTable.initPagination(instance);
            HPH.DataTable.initActions(instance);
        },
        
        /**
         * Initialize search functionality
         */
        initSearch: function(instance) {
            if (instance.$searchInput.length) {
                instance.$searchInput.on('input', HPH.debounce(function() {
                    var searchTerm = $(this).val().toLowerCase();
                    HPH.DataTable.filterRows(instance, searchTerm);
                }, 300));
            }
        },
        
        /**
         * Initialize sorting functionality
         */
        initSorting: function(instance) {
            instance.$sortHeaders.on('click', function() {
                var $header = $(this);
                var column = $header.data('sort-column');
                var dataType = $header.data('sort-type') || 'string';
                
                // Determine sort direction
                var direction = 'asc';
                if (instance.currentSort.column === column && instance.currentSort.direction === 'asc') {
                    direction = 'desc';
                }
                
                // Update header classes
                instance.$sortHeaders.removeClass('hph-data-table__header-sortable--asc hph-data-table__header-sortable--desc');
                $header.addClass('hph-data-table__header-sortable--' + direction);
                
                // Sort rows
                HPH.DataTable.sortRows(instance, column, direction, dataType);
                
                // Update current sort
                instance.currentSort = { column: column, direction: direction };
            });
        },
        
        /**
         * Initialize filters
         */
        initFilters: function(instance) {
            instance.$filters.on('click', function() {
                var $filter = $(this);
                var filterType = $filter.data('filter-type');
                var filterValue = $filter.data('filter-value');
                
                // Toggle filter
                $filter.toggleClass('hph-data-table__filter--active');
                
                if ($filter.hasClass('hph-data-table__filter--active')) {
                    instance.activeFilters[filterType] = filterValue;
                } else {
                    delete instance.activeFilters[filterType];
                }
                
                // Apply filters
                HPH.DataTable.applyFilters(instance);
            });
        },
        
        /**
         * Initialize pagination
         */
        initPagination: function(instance) {
            var $pagination = instance.$table.find('.hph-data-table__pagination');
            
            if ($pagination.length) {
                $pagination.on('click', '.hph-data-table__page-btn', function(e) {
                    e.preventDefault();
                    
                    var $btn = $(this);
                    if ($btn.hasClass('hph-data-table__page-btn--active') || $btn.is(':disabled')) {
                        return;
                    }
                    
                    var page = parseInt($btn.data('page'));
                    HPH.DataTable.goToPage(instance, page);
                });
            }
        },
        
        /**
         * Initialize table actions
         */
        initActions: function(instance) {
            // Row actions
            instance.$table.on('click', '.hph-data-table__action', function(e) {
                e.preventDefault();
                
                var $action = $(this);
                var actionType = $action.data('action');
                var rowId = $action.closest('tr').data('id');
                
                switch (actionType) {
                    case 'edit':
                        HPH.DataTable.editRow(instance, rowId);
                        break;
                    case 'delete':
                        HPH.DataTable.confirmDelete(instance, rowId);
                        break;
                    case 'view':
                        HPH.DataTable.viewRow(instance, rowId);
                        break;
                    default:
                        // Custom action
                        HPH.DataTable.customAction(instance, actionType, rowId);
                }
            });
            
            // Bulk actions
            var $bulkSelect = instance.$table.find('.bulk-select-all');
            if ($bulkSelect.length) {
                $bulkSelect.on('change', function() {
                    var isChecked = $(this).prop('checked');
                    instance.$table.find('.bulk-select').prop('checked', isChecked);
                    HPH.DataTable.updateBulkActions(instance);
                });
                
                instance.$table.on('change', '.bulk-select', function() {
                    HPH.DataTable.updateBulkActions(instance);
                });
            }
        },
        
        /**
         * Filter rows based on search term
         */
        filterRows: function(instance, searchTerm) {
            instance.$rows.each(function() {
                var $row = $(this);
                var text = $row.text().toLowerCase();
                
                if (searchTerm === '' || text.indexOf(searchTerm) > -1) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
            
            HPH.DataTable.updateInfo(instance);
        },
        
        /**
         * Sort table rows
         */
        sortRows: function(instance, column, direction, dataType) {
            var $rows = instance.$tbody.find('tr');
            
            $rows.sort(function(a, b) {
                var aVal = $(a).find(`[data-sort="${column}"]`).data('sort') || $(a).find(`[data-sort="${column}"]`).text();
                var bVal = $(b).find(`[data-sort="${column}"]`).data('sort') || $(b).find(`[data-sort="${column}"]`).text();
                
                // Handle different data types
                switch (dataType) {
                    case 'number':
                        aVal = parseFloat(aVal) || 0;
                        bVal = parseFloat(bVal) || 0;
                        break;
                    case 'date':
                        aVal = new Date(aVal);
                        bVal = new Date(bVal);
                        break;
                    case 'currency':
                        aVal = parseFloat(aVal.replace(/[$,]/g, '')) || 0;
                        bVal = parseFloat(bVal.replace(/[$,]/g, '')) || 0;
                        break;
                    default:
                        aVal = aVal.toString().toLowerCase();
                        bVal = bVal.toString().toLowerCase();
                }
                
                var result = 0;
                if (aVal < bVal) result = -1;
                if (aVal > bVal) result = 1;
                
                return direction === 'desc' ? result * -1 : result;
            });
            
            // Reorder rows in DOM
            instance.$tbody.append($rows);
        },
        
        /**
         * Apply active filters
         */
        applyFilters: function(instance) {
            instance.$rows.each(function() {
                var $row = $(this);
                var show = true;
                
                // Check each active filter
                Object.keys(instance.activeFilters).forEach(function(filterType) {
                    var filterValue = instance.activeFilters[filterType];
                    var rowValue = $row.find(`[data-filter="${filterType}"]`).data('filter') || 
                                  $row.find(`[data-filter="${filterType}"]`).text();
                    
                    if (rowValue != filterValue) {
                        show = false;
                    }
                });
                
                $row.toggle(show);
            });
            
            HPH.DataTable.updateInfo(instance);
        },
        
        /**
         * Go to specific page
         */
        goToPage: function(instance, page) {
            var itemsPerPage = parseInt(instance.$table.data('per-page')) || 10;
            var $visibleRows = instance.$rows.filter(':visible');
            var totalPages = Math.ceil($visibleRows.length / itemsPerPage);
            
            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;
            
            var startIndex = (page - 1) * itemsPerPage;
            var endIndex = startIndex + itemsPerPage;
            
            // Hide all rows, then show the ones for this page
            instance.$rows.hide();
            $visibleRows.slice(startIndex, endIndex).show();
            
            // Update pagination buttons
            var $pagination = instance.$table.find('.hph-data-table__pagination');
            $pagination.find('.hph-data-table__page-btn').removeClass('hph-data-table__page-btn--active');
            $pagination.find(`[data-page="${page}"]`).addClass('hph-data-table__page-btn--active');
            
            // Update prev/next buttons
            $pagination.find('[data-page="prev"]').prop('disabled', page === 1);
            $pagination.find('[data-page="next"]').prop('disabled', page === totalPages);
            
            HPH.DataTable.updateInfo(instance);
        },
        
        /**
         * Update table info
         */
        updateInfo: function(instance) {
            var $info = instance.$table.find('.hph-data-table__info');
            if ($info.length) {
                var total = instance.$rows.length;
                var visible = instance.$rows.filter(':visible').length;
                
                if (visible === total) {
                    $info.text(`Showing ${total} entries`);
                } else {
                    $info.text(`Showing ${visible} of ${total} entries`);
                }
            }
        },
        
        /**
         * Edit row
         */
        editRow: function(instance, rowId) {
            // Dispatch custom event for external handling
            instance.$table.trigger('dataTable:editRow', [rowId]);
        },
        
        /**
         * Confirm delete action
         */
        confirmDelete: function(instance, rowId) {
            if (confirm(hphContext.strings.confirmDelete || 'Are you sure you want to delete this item?')) {
                HPH.DataTable.deleteRow(instance, rowId);
            }
        },
        
        /**
         * Delete row
         */
        deleteRow: function(instance, rowId) {
            // Show loading state
            instance.$table.addClass('hph-data-table--loading');
            
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_delete_table_row',
                    nonce: hphContext.nonce,
                    row_id: rowId,
                    table_id: instance.$table.data('table-id')
                },
                success: function(response) {
                    if (response.success) {
                        // Remove row from table
                        instance.$table.find(`tr[data-id="${rowId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            HPH.DataTable.updateInfo(instance);
                        });
                        
                        HPH.showAlert(response.data.message || 'Item deleted successfully', 'success');
                    } else {
                        HPH.showAlert(response.data.message || 'Failed to delete item', 'error');
                    }
                },
                error: function() {
                    HPH.showAlert(hphContext.strings.error, 'error');
                },
                complete: function() {
                    instance.$table.removeClass('hph-data-table--loading');
                }
            });
        },
        
        /**
         * View row
         */
        viewRow: function(instance, rowId) {
            // Dispatch custom event for external handling
            instance.$table.trigger('dataTable:viewRow', [rowId]);
        },
        
        /**
         * Handle custom actions
         */
        customAction: function(instance, actionType, rowId) {
            // Dispatch custom event for external handling
            instance.$table.trigger('dataTable:customAction', [actionType, rowId]);
        },
        
        /**
         * Update bulk actions
         */
        updateBulkActions: function(instance) {
            var $selected = instance.$table.find('.bulk-select:checked');
            var $bulkActions = instance.$table.find('.bulk-actions');
            
            if ($selected.length > 0) {
                $bulkActions.show();
                $bulkActions.find('.selected-count').text($selected.length);
            } else {
                $bulkActions.hide();
            }
        },
        
        /**
         * Refresh table data
         */
        refresh: function($table) {
            var instance = $table.data('dataTable');
            if (!instance) return;
            
            // Show loading state
            $table.addClass('hph-data-table--loading');
            
            $.ajax({
                url: hphContext.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hph_refresh_data_table',
                    nonce: hphContext.nonce,
                    table_id: $table.data('table-id')
                },
                success: function(response) {
                    if (response.success) {
                        // Replace table content
                        $table.find('.hph-data-table__container').html(response.data.html);
                        
                        // Reinitialize
                        HPH.DataTable.initTable($table);
                    } else {
                        HPH.showAlert(response.data.message || 'Failed to refresh table', 'error');
                    }
                },
                error: function() {
                    HPH.showAlert(hphContext.strings.error, 'error');
                },
                complete: function() {
                    $table.removeClass('hph-data-table--loading');
                }
            });
        }
    };
    
    // Initialize data tables when DOM is ready
    $(document).ready(function() {
        HPH.DataTable.init();
    });
    
    // Expose refresh method globally
    window.hphRefreshDataTable = function(tableId) {
        var $table = $(`[data-table-id="${tableId}"]`);
        if ($table.length) {
            HPH.DataTable.refresh($table);
        }
    };
    
})(jQuery);
