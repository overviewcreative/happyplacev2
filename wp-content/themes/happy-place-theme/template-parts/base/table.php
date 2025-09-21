<?php
/**
 * Base Table Component
 * 
 * Pure UI data table component with sorting, filtering, and responsive options
 * 
 * @package HappyPlaceTheme
 * @subpackage Components/Base
 * @since 3.0.0
 */

$props = wp_parse_args(get_query_var('args', array()), array(
    // Table data
    'columns' => array(), // Column definitions
    'rows' => array(), // Data rows
    'footer' => array(), // Footer row data
    
    // Appearance
    'variant' => 'default', // default, bordered, striped, hover, compact, spacious
    'theme' => 'light', // light, dark
    'size' => 'md', // sm, md, lg
    'layout' => 'auto', // auto, fixed, responsive
    
    // Features
    'sortable' => false, // Enable column sorting
    'filterable' => false, // Enable column filtering
    'searchable' => false, // Enable global search
    'selectable' => false, // Row selection (single, multiple)
    'expandable' => false, // Expandable rows
    'editable' => false, // Inline editing
    'resizable' => false, // Column resizing
    'reorderable' => false, // Column reordering
    
    // Display options
    'show_header' => true,
    'show_footer' => false,
    'sticky_header' => false,
    'sticky_columns' => 0, // Number of sticky columns from left
    'highlight_row' => -1, // Index of row to highlight
    'highlight_column' => -1, // Index of column to highlight
    
    // Pagination
    'paginated' => false,
    'page_size' => 10,
    'current_page' => 1,
    'total_rows' => 0,
    
    // Responsive
    'responsive' => 'scroll', // scroll, stack, collapse, priority
    'breakpoint' => 'md', // When to trigger responsive mode
    'priority_columns' => array(), // Column priorities for responsive
    
    // Empty state
    'empty_message' => 'No data available',
    'empty_icon' => 'inbox',
    
    // Loading state
    'loading' => false,
    'loading_rows' => 5,
    
    // Actions
    'bulk_actions' => array(), // Bulk action options
    'row_actions' => array(), // Per-row action options
    
    // HTML
    'id' => '',
    'class' => '',
    'caption' => '',
    'summary' => '', // For accessibility
    'attributes' => array(),
    'data' => array()
));

// Process columns
$processed_columns = array();
foreach ($props['columns'] as $index => $column) {
    $col_defaults = array(
        'key' => 'col_' . $index,
        'label' => 'Column ' . ($index + 1),
        'type' => 'text', // text, number, date, boolean, status, actions
        'width' => '', // Fixed width
        'align' => '', // left, center, right
        'sortable' => $props['sortable'],
        'filterable' => $props['filterable'],
        'searchable' => true,
        'visible' => true,
        'priority' => $index + 1, // For responsive
        'formatter' => '', // Format function name
        'class' => '',
        'header_class' => '',
        'sticky' => false
    );
    
    $processed_columns[] = wp_parse_args($column, $col_defaults);
}

// Generate ID if needed
if (!$props['id']) {
    $props['id'] = 'hph-table-' . substr(md5(serialize($props['columns'])), 0, 8);
}

// Container classes
$container_classes = array(
    'hph-table-container',
    'hph-table-container--' . $props['variant'],
    'hph-table-container--' . $props['size'],
    'hph-table-container--' . $props['theme']
);

if ($props['responsive'] !== 'scroll') {
    $container_classes[] = 'hph-table-container--responsive';
    $container_classes[] = 'hph-table-container--' . $props['responsive'];
}

if ($props['loading']) {
    $container_classes[] = 'is-loading';
}

// Table classes
$table_classes = array(
    'hph-table'
);

if ($props['layout'] !== 'auto') {
    $table_classes[] = 'hph-table--' . $props['layout'];
}

if ($props['sticky_header']) {
    $table_classes[] = 'hph-table--sticky-header';
}

if ($props['selectable']) {
    $table_classes[] = 'hph-table--selectable';
}

if ($props['class']) {
    $table_classes[] = $props['class'];
}

?>

<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" 
     id="<?php echo esc_attr($props['id']); ?>-container">
    
    <?php if ($props['searchable']): ?>
    <div class="hph-table__search">
        <input type="search" 
               class="hph-table__search-input" 
               placeholder="Search table..."
               aria-label="Search table">
        <span class="hph-table__search-icon" data-icon="search"></span>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($props['bulk_actions']) && $props['selectable']): ?>
    <div class="hph-table__bulk-actions" style="display: none;">
        <span class="hph-table__selected-count">
            <span class="count">0</span> selected
        </span>
        <?php foreach ($props['bulk_actions'] as $action): ?>
            <?php hph_component('base/button', wp_parse_args($action, array(
                'size' => 'sm',
                'variant' => 'outline'
            ))); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="hph-table__wrapper">
        <?php if ($props['loading']): ?>
        <div class="hph-table__loader">
            <div class="hph-table__skeleton">
                <?php for ($i = 0; $i < $props['loading_rows']; $i++): ?>
                <div class="hph-skeleton-row">
                    <?php foreach ($processed_columns as $col): ?>
                    <div class="hph-skeleton-cell"></div>
                    <?php endforeach; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <table class="<?php echo esc_attr(implode(' ', $table_classes)); ?>"
               id="<?php echo esc_attr($props['id']); ?>"
               <?php if ($props['caption']): ?>
               aria-label="<?php echo esc_attr($props['caption']); ?>"
               <?php endif; ?>
               <?php if ($props['summary']): ?>
               summary="<?php echo esc_attr($props['summary']); ?>"
               <?php endif; ?>
               <?php foreach ($props['data'] as $key => $value): ?>
               data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
               <?php endforeach; ?>
               <?php foreach ($props['attributes'] as $key => $value): ?>
               <?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
               <?php endforeach; ?>>
            
            <?php if ($props['caption']): ?>
            <caption class="hph-table__caption"><?php echo esc_html($props['caption']); ?></caption>
            <?php endif; ?>
            
            <?php if ($props['show_header']): ?>
            <thead class="hph-table__head">
                <tr class="hph-table__row hph-table__row--header">
                    <?php if ($props['selectable']): ?>
                    <th class="hph-table__cell hph-table__cell--checkbox">
                        <input type="checkbox" 
                               class="hph-table__select-all"
                               aria-label="Select all rows">
                    </th>
                    <?php endif; ?>
                    
                    <?php if ($props['expandable']): ?>
                    <th class="hph-table__cell hph-table__cell--expand"></th>
                    <?php endif; ?>
                    
                    <?php foreach ($processed_columns as $col_index => $column): 
                        if (!$column['visible']) continue;
                        
                        $header_classes = array('hph-table__cell', 'hph-table__cell--header');
                        if ($column['align']) {
                            $header_classes[] = 'hph-table__cell--' . $column['align'];
                        }
                        if ($column['sortable']) {
                            $header_classes[] = 'hph-table__cell--sortable';
                        }
                        if ($column['header_class']) {
                            $header_classes[] = $column['header_class'];
                        }
                        if ($col_index < $props['sticky_columns'] || $column['sticky']) {
                            $header_classes[] = 'hph-table__cell--sticky';
                        }
                    ?>
                    <th class="<?php echo esc_attr(implode(' ', $header_classes)); ?>"
                        <?php if ($column['width']): ?>
                        style="width: <?php echo esc_attr($column['width']); ?>"
                        <?php endif; ?>
                        <?php if ($column['sortable']): ?>
                        data-sortable="true"
                        data-sort-key="<?php echo esc_attr($column['key']); ?>"
                        aria-sort="none"
                        role="columnheader"
                        tabindex="0"
                        <?php endif; ?>
                        <?php if ($props['responsive'] === 'priority'): ?>
                        data-priority="<?php echo esc_attr($column['priority']); ?>"
                        <?php endif; ?>>
                        
                        <div class="hph-table__header-content">
                            <span class="hph-table__header-label">
                                <?php echo esc_html($column['label']); ?>
                            </span>
                            
                            <?php if ($column['sortable']): ?>
                            <span class="hph-table__sort-icon">
                                <span data-icon="chevron-up"></span>
                                <span data-icon="chevron-down"></span>
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($column['filterable']): ?>
                            <button type="button" 
                                    class="hph-table__filter-btn"
                                    aria-label="Filter <?php echo esc_attr($column['label']); ?>">
                                <span data-icon="filter"></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($props['resizable']): ?>
                        <div class="hph-table__resize-handle"></div>
                        <?php endif; ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <?php endif; ?>
            
            <tbody class="hph-table__body">
                <?php if (empty($props['rows'])): ?>
                <tr class="hph-table__row hph-table__row--empty">
                    <td class="hph-table__cell hph-table__cell--empty" 
                        colspan="<?php echo count($processed_columns) + ($props['selectable'] ? 1 : 0) + ($props['expandable'] ? 1 : 0); ?>">
                        <div class="hph-table__empty">
                            <span class="hph-table__empty-icon" data-icon="<?php echo esc_attr($props['empty_icon']); ?>"></span>
                            <p class="hph-table__empty-message"><?php echo esc_html($props['empty_message']); ?></p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($props['rows'] as $row_index => $row): 
                        $row_classes = array('hph-table__row');
                        if ($row_index === $props['highlight_row']) {
                            $row_classes[] = 'hph-table__row--highlighted';
                        }
                        if (isset($row['_state'])) {
                            $row_classes[] = 'hph-table__row--' . $row['_state'];
                        }
                        if (isset($row['_class'])) {
                            $row_classes[] = $row['_class'];
                        }
                    ?>
                    <tr class="<?php echo esc_attr(implode(' ', $row_classes)); ?>"
                        <?php if (isset($row['_id'])): ?>
                        data-row-id="<?php echo esc_attr($row['_id']); ?>"
                        <?php endif; ?>>
                        
                        <?php if ($props['selectable']): ?>
                        <td class="hph-table__cell hph-table__cell--checkbox">
                            <input type="checkbox" 
                                   class="hph-table__select-row"
                                   value="<?php echo esc_attr($row['_id'] ?? $row_index); ?>"
                                   aria-label="Select row">
                        </td>
                        <?php endif; ?>
                        
                        <?php if ($props['expandable']): ?>
                        <td class="hph-table__cell hph-table__cell--expand">
                            <button type="button" 
                                    class="hph-table__expand-btn"
                                    aria-expanded="false"
                                    aria-label="Expand row">
                                <span data-icon="chevron-right"></span>
                            </button>
                        </td>
                        <?php endif; ?>
                        
                        <?php foreach ($processed_columns as $col_index => $column): 
                            if (!$column['visible']) continue;
                            
                            $cell_value = $row[$column['key']] ?? '';
                            $cell_classes = array('hph-table__cell');
                            
                            if ($column['align']) {
                                $cell_classes[] = 'hph-table__cell--' . $column['align'];
                            }
                            if ($column['type']) {
                                $cell_classes[] = 'hph-table__cell--' . $column['type'];
                            }
                            if ($column['class']) {
                                $cell_classes[] = $column['class'];
                            }
                            if ($col_index === $props['highlight_column']) {
                                $cell_classes[] = 'hph-table__cell--highlighted';
                            }
                            if ($col_index < $props['sticky_columns'] || $column['sticky']) {
                                $cell_classes[] = 'hph-table__cell--sticky';
                            }
                        ?>
                        <td class="<?php echo esc_attr(implode(' ', $cell_classes)); ?>"
                            <?php if ($props['responsive'] === 'stack'): ?>
                            data-label="<?php echo esc_attr($column['label']); ?>"
                            <?php endif; ?>>
                            
                            <?php 
                            // Format cell based on type
                            switch ($column['type']) {
                                case 'boolean':
                                    echo $cell_value ? '<span data-icon="check" class="hph-text-success"></span>' : '<span data-icon="x" class="hph-text-danger"></span>';
                                    break;
                                    
                                case 'status':
                                    $status_variant = $cell_value['variant'] ?? 'default';
                                    $status_text = $cell_value['text'] ?? $cell_value;
                                    echo '<span class="hph-badge hph-badge--' . esc_attr($status_variant) . '">' . esc_html($status_text) . '</span>';
                                    break;
                                    
                                case 'actions':
                                    if (!empty($props['row_actions'])) {
                                        echo '<div class="hph-table__actions">';
                                        foreach ($props['row_actions'] as $action) {
                                            $action['size'] = 'xs';
                                            $action['data']['row-id'] = $row['_id'] ?? $row_index;
                                            hph_component('base/button', $action);
                                        }
                                        echo '</div>';
                                    }
                                    break;
                                    
                                case 'number':
                                    echo '<span class="hph-table__number">' . esc_html(number_format($cell_value)) . '</span>';
                                    break;
                                    
                                case 'date':
                                    echo '<time datetime="' . esc_attr($cell_value) . '">' . esc_html(date('M j, Y', strtotime($cell_value))) . '</time>';
                                    break;
                                    
                                default:
                                    echo wp_kses_post($cell_value);
                                    break;
                            }
                            ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <?php if ($props['expandable'] && isset($row['_expanded_content'])): ?>
                    <tr class="hph-table__row hph-table__row--expanded" style="display: none;">
                        <td class="hph-table__cell hph-table__cell--expanded" 
                            colspan="<?php echo count($processed_columns) + ($props['selectable'] ? 1 : 0) + ($props['expandable'] ? 1 : 0); ?>">
                            <?php echo $row['_expanded_content']; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            
            <?php if ($props['show_footer'] && !empty($props['footer'])): ?>
            <tfoot class="hph-table__foot">
                <tr class="hph-table__row hph-table__row--footer">
                    <?php if ($props['selectable']): ?>
                    <td class="hph-table__cell"></td>
                    <?php endif; ?>
                    
                    <?php if ($props['expandable']): ?>
                    <td class="hph-table__cell"></td>
                    <?php endif; ?>
                    
                    <?php foreach ($processed_columns as $col_index => $column): 
                        if (!$column['visible']) continue;
                        $footer_value = $props['footer'][$column['key']] ?? '';
                    ?>
                    <td class="hph-table__cell hph-table__cell--footer">
                        <?php echo wp_kses_post($footer_value); ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
    
    <?php if ($props['paginated']): ?>
    <div class="hph-table__pagination">
        <?php hph_component('base/pagination', array(
            'total' => $props['total_rows'],
            'per_page' => $props['page_size'],
            'current' => $props['current_page'],
            'size' => 'sm'
        )); ?>
    </div>
    <?php endif; ?>
    
</div>
