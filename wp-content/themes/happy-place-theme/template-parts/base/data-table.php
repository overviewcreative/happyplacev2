<?php
/**
 * Data Table Component - Responsive data table for dashboards
 *
 * @package HappyPlaceTheme
 */

// Default attributes
$table_args = wp_parse_args($args ?? [], [
    'title' => '',
    'description' => '',
    'columns' => [],
    'data' => [],
    'actions' => [],
    'pagination' => false,
    'search' => false,
    'sortable' => false,
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'compact' => false,
    'empty_message' => 'No data available',
    'loading' => false,
    'table_id' => 'hph-data-table',
    'per_page' => 10,
    'total' => 0
]);

// Generate unique table ID if not provided
if (empty($table_args['table_id'])) {
    $table_args['table_id'] = 'hph-table-' . uniqid();
}

// Build table classes using utility-first approach
$table_classes = [
    'hph-table',
    'hph-w-full',
    'hph-border-collapse',
    'hph-text-sm'
];

if ($table_args['striped']) {
    $table_classes[] = 'hph-table-striped';
}
if ($table_args['hover']) {
    $table_classes[] = 'hph-table-hover';
}
if ($table_args['compact']) {
    $table_classes[] = 'hph-table-compact';
}

$wrapper_classes = [
    'hph-overflow-x-auto',
    'hph-border',
    'hph-border-gray-200',
    'hph-rounded-lg'
];

if ($table_args['responsive']) {
    $wrapper_classes[] = 'hph-responsive-table';
}
?>

<div class="hph-data-table-container hph-card">
    
    <?php if ($table_args['title'] || $table_args['description'] || $table_args['search'] || !empty($table_args['actions'])) : ?>
        <!-- Table Header -->
        <div class="hph-table-header hph-p-4 hph-border-b">
            <div class="hph-flex hph-flex-col md:hph-flex-row md:hph-items-center hph-justify-between hph-gap-4">
                <div class="hph-table-title-section">
                    <?php if ($table_args['title']) : ?>
                        <h3 class="hph-table-title hph-font-medium hph-mb-1">
                            <?php echo esc_html($table_args['title']); ?>
                        </h3>
                    <?php endif; ?>
                    
                    <?php if ($table_args['description']) : ?>
                        <p class="hph-table-description hph-text-sm hph-text-muted hph-mb-0">
                            <?php echo esc_html($table_args['description']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="hph-table-controls hph-flex hph-items-center hph-gap-3">
                    <?php if ($table_args['search']) : ?>
                        <div class="hph-table-search hph-relative">
                            <input type="text" 
                                   class="hph-form-control hph-form-control-sm hph-pl-8"
                                   placeholder="Search..."
                                   id="<?php echo esc_attr($table_args['table_id']); ?>-search">
                            <i class="fas fa-search hph-absolute hph-left-2 hph-top-1/2 hph-transform hph--translate-y-1/2 hph-text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($table_args['actions'])) : ?>
                        <div class="hph-table-actions hph-flex hph-gap-2">
                            <?php foreach ($table_args['actions'] as $action) : ?>
                                <?php
                                $action_defaults = [
                                    'text' => '',
                                    'url' => '#',
                                    'type' => 'button',
                                    'color' => 'primary',
                                    'size' => 'sm',
                                    'icon' => '',
                                    'onclick' => ''
                                ];
                                $action = wp_parse_args($action, $action_defaults);
                                
                                $btn_class = 'hph-btn hph-btn-' . $action['color'] . ' hph-btn-' . $action['size'];
                                ?>
                                
                                <?php if ($action['type'] === 'link') : ?>
                                    <a href="<?php echo esc_url($action['url']); ?>" class="<?php echo esc_attr($btn_class); ?>">
                                        <?php if ($action['icon']) : ?>
                                            <i class="fas <?php echo esc_attr($action['icon']); ?> hph-mr-1"></i>
                                        <?php endif; ?>
                                        <?php echo esc_html($action['text']); ?>
                                    </a>
                                <?php else : ?>
                                    <button type="button" class="<?php echo esc_attr($btn_class); ?>"
                                            <?php if ($action['onclick']) : ?>onclick="<?php echo esc_attr($action['onclick']); ?>"<?php endif; ?>>
                                        <?php if ($action['icon']) : ?>
                                            <i class="fas <?php echo esc_attr($action['icon']); ?> hph-mr-1"></i>
                                        <?php endif; ?>
                                        <?php echo esc_html($action['text']); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Table Body -->
    <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
        <?php if ($table_args['loading']) : ?>
            <div class="hph-table-loading hph-p-8 hph-text-center">
                <div class="hph-spinner hph-spinner-lg hph-text-primary hph-mb-2">
                    <i class="fas fa-circle-notch fa-spin"></i>
                </div>
                <p class="hph-text-muted">Loading data...</p>
            </div>
        <?php elseif (empty($table_args['data'])) : ?>
            <div class="hph-table-empty hph-p-8 hph-text-center">
                <div class="hph-empty-icon hph-text-4xl hph-text-muted hph-mb-2">
                    <i class="fas fa-table"></i>
                </div>
                <p class="hph-text-muted"><?php echo esc_html($table_args['empty_message']); ?></p>
            </div>
        <?php else : ?>
            <table id="<?php echo esc_attr($table_args['table_id']); ?>" class="<?php echo esc_attr(implode(' ', $table_classes)); ?>">
                
                <!-- Table Head -->
                <?php if (!empty($table_args['columns'])) : ?>
                    <thead>
                        <tr>
                            <?php foreach ($table_args['columns'] as $column) : ?>
                                <?php
                                $column_defaults = [
                                    'key' => '',
                                    'label' => '',
                                    'sortable' => false,
                                    'class' => '',
                                    'width' => ''
                                ];
                                $column = wp_parse_args($column, $column_defaults);
                                
                                $th_class = 'hph-table-header-cell';
                                if ($column['class']) $th_class .= ' ' . $column['class'];
                                if ($table_args['sortable'] && $column['sortable']) $th_class .= ' hph-sortable';
                                
                                $th_style = '';
                                if ($column['width']) $th_style = 'width: ' . $column['width'];
                                ?>
                                
                                <th class="<?php echo esc_attr($th_class); ?>"
                                    <?php if ($th_style) : ?>style="<?php echo esc_attr($th_style); ?>"<?php endif; ?>
                                    <?php if ($table_args['sortable'] && $column['sortable']) : ?>
                                        data-sort-key="<?php echo esc_attr($column['key']); ?>"
                                    <?php endif; ?>>
                                    
                                    <?php echo esc_html($column['label']); ?>
                                    
                                    <?php if ($table_args['sortable'] && $column['sortable']) : ?>
                                        <i class="fas fa-sort hph-sort-icon hph-ml-1"></i>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                <?php endif; ?>
                
                <!-- Table Body -->
                <tbody>
                    <?php foreach ($table_args['data'] as $row_index => $row) : ?>
                        <tr class="hph-table-row" data-row-index="<?php echo esc_attr($row_index); ?>">
                            <?php foreach ($table_args['columns'] as $column) : ?>
                                <?php
                                $cell_value = $row[$column['key']] ?? '';
                                $cell_class = 'hph-table-cell';
                                if (isset($column['cell_class'])) $cell_class .= ' ' . $column['cell_class'];
                                ?>
                                
                                <td class="<?php echo esc_attr($cell_class); ?>">
                                    <?php if (isset($column['render']) && is_callable($column['render'])) : ?>
                                        <?php echo call_user_func($column['render'], $cell_value, $row, $row_index); ?>
                                    <?php else : ?>
                                        <?php echo esc_html($cell_value); ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <?php if ($table_args['pagination'] && !empty($table_args['data'])) : ?>
        <!-- Table Footer with Pagination -->
        <div class="hph-table-footer hph-p-4 hph-border-t hph-flex hph-items-center hph-justify-between">
            <div class="hph-table-info hph-text-sm hph-text-muted">
                Showing <?php echo count($table_args['data']); ?> 
                <?php if ($table_args['total'] > 0) : ?>
                    of <?php echo number_format($table_args['total']); ?>
                <?php endif; ?>
                results
            </div>
            
            <div class="hph-table-pagination">
                <!-- Pagination would be implemented based on your pagination system -->
                <div class="hph-pagination hph-flex hph-gap-1">
                    <button class="hph-btn hph-btn-outline hph-btn-sm" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="hph-btn hph-btn-primary hph-btn-sm">1</button>
                    <button class="hph-btn hph-btn-outline hph-btn-sm">2</button>
                    <button class="hph-btn hph-btn-outline hph-btn-sm">3</button>
                    <button class="hph-btn hph-btn-outline hph-btn-sm">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<?php if ($table_args['search'] || $table_args['sortable']) : ?>
<script>
jQuery(document).ready(function($) {
    var $table = $('#<?php echo esc_js($table_args['table_id']); ?>');
    var $search = $('#<?php echo esc_js($table_args['table_id']); ?>-search');
    
    // Search functionality
    if ($search.length) {
        $search.on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            $table.find('tbody tr').each(function() {
                var rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchTerm) > -1);
            });
        });
    }
    
    // Sort functionality
    <?php if ($table_args['sortable']) : ?>
    $('.hph-sortable').on('click', function() {
        var $header = $(this);
        var sortKey = $header.data('sort-key');
        var columnIndex = $header.index();
        var $tbody = $table.find('tbody');
        var rows = $tbody.find('tr').get();
        
        var isAsc = !$header.hasClass('hph-sort-asc');
        
        // Remove sort classes from all headers
        $('.hph-sortable').removeClass('hph-sort-asc hph-sort-desc');
        $('.hph-sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        
        // Add sort class to current header
        $header.addClass(isAsc ? 'hph-sort-asc' : 'hph-sort-desc');
        $header.find('.hph-sort-icon').removeClass('fa-sort').addClass(isAsc ? 'fa-sort-up' : 'fa-sort-down');
        
        rows.sort(function(a, b) {
            var aVal = $(a).find('td').eq(columnIndex).text();
            var bVal = $(b).find('td').eq(columnIndex).text();
            
            // Try to parse as numbers
            var aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
            var bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAsc ? aNum - bNum : bNum - aNum;
            } else {
                return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            }
        });
        
        $.each(rows, function(index, row) {
            $tbody.append(row);
        });
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>