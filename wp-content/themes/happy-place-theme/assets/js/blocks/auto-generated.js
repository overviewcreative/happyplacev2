/**
 * Auto-generated block registrations
 */

import { registerBlockType } from '@wordpress/blocks';
import { createElement } from '@wordpress/element';
import { generateBlockControls } from './block-generator';


registerBlockType('hph/sections-content', {
    title: 'Content',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"layout":{"type":"string","default":"centered"},"background":{"type":"string","default":"white"},"padding":{"type":"string","default":"xl"},"content_width":{"type":"string","default":"normal"},"alignment":{"type":"string","default":"center"},"image":{"type":"string","default":"null"},"badge":{"type":"string","default":""},"headline":{"type":"string","default":"Content Section"},"headline_tag":{"type":"string","default":"h2"},"subheadline":{"type":"string","default":""},"content":{"type":"string","default":""},"buttons":{"type":"array","default":[]},"items":{"type":"array","default":[]},"stats":{"type":"array","default":[]},"faqs":{"type":"array","default":[]},"columns":{"type":"number","default":3},"animation":{"type":"boolean","default":false},"section_id":{"type":"string","default":""},"allow_multiple_open":{"type":"boolean","default":false},"search_enabled":{"type":"boolean","default":false},"card_style":{"type":"string","default":"default"},"form_config":{"type":"string","default":"null"},"sidebar_content":{"type":"string","default":""}},
    edit: (props) => generateBlockControls('hph/sections-content', props),
    save: () => null
});

registerBlockType('hph/sections-cta', {
    title: 'CTA (Call-to-Action)',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"layout":{"type":"string","default":"centered"},"background":{"type":"string","default":"primary"},"background_image":{"type":"string","default":""},"overlay":{"type":"boolean","default":true},"overlay_opacity":{"type":"number","default":40},"padding":{"type":"string","default":"xl"},"content_width":{"type":"string","default":"normal"},"alignment":{"type":"string","default":"center"},"badge":{"type":"string","default":""},"headline":{"type":"string","default":"Ready to Get Started?"},"subheadline":{"type":"string","default":""},"content":{"type":"string","default":""},"buttons":{"type":"array","default":[]},"url":{"type":"string","default":"#"},"style":{"type":"string","default":"white"},"size":{"type":"string","default":"xl'\n        )\n    )"},"image":{"type":"string","default":"null"},"form":{"type":"string","default":"null"},"animation":{"type":"boolean","default":false},"section_id":{"type":"string","default":""}},
    edit: (props) => generateBlockControls('hph/sections-cta', props),
    save: () => null
});

registerBlockType('hph/sections-features', {
    title: 'Features',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"layout":{"type":"string","default":"grid"},"background":{"type":"string","default":"white"},"padding":{"type":"string","default":"xl"},"content_width":{"type":"string","default":"normal"},"alignment":{"type":"string","default":"center"},"columns":{"type":"number","default":3},"badge":{"type":"string","default":""},"headline":{"type":"string","default":"Our Features"},"subheadline":{"type":"string","default":""},"content":{"type":"string","default":""},"features":{"type":"array","default":[]},"icon_style":{"type":"string","default":"default"},"icon_position":{"type":"string","default":"top"},"animation":{"type":"boolean","default":false},"section_id":{"type":"string","default":""}},
    edit: (props) => generateBlockControls('hph/sections-features', props),
    save: () => null
});

registerBlockType('hph/sections-hero', {
    title: 'Hero',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"style":{"type":"string","default":"gradient"},"height":{"type":"string","default":"lg"},"background_image":{"type":"string","default":""},"background_video":{"type":"string","default":""},"overlay":{"type":"string","default":"dark"},"overlay_opacity":{"type":"number","default":40},"alignment":{"type":"string","default":"center"},"content_width":{"type":"string","default":"normal"},"badge":{"type":"string","default":""},"badge_icon":{"type":"string","default":""},"headline":{"type":"string","default":"Hero Section"},"subheadline":{"type":"string","default":""},"content":{"type":"string","default":""},"buttons":{"type":"array","default":[]},"scroll_indicator":{"type":"boolean","default":false},"section_id":{"type":"string","default":""},"parallax":{"type":"boolean","default":false},"fade_in":{"type":"boolean","default":false},"listing_id":{"type":"number","default":0},"show_gallery":{"type":"boolean","default":false},"show_status":{"type":"boolean","default":false},"show_price":{"type":"boolean","default":false},"show_stats":{"type":"boolean","default":false}},
    edit: (props) => generateBlockControls('hph/sections-hero', props),
    save: () => null
});

registerBlockType('hph/sections-section', {
    title: 'Content Section',
    category: 'hph-sections',
    icon: 'align-left',
    attributes: {"layout":{"type":"string","default":"default"}},
    edit: (props) => generateBlockControls('hph/sections-section', props),
    save: () => null
});

registerBlockType('hph/base-accordion', {
    title: 'Accordion',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"items":{"type":"array","default":[]},"variant":{"type":"string","default":"default"},"size":{"type":"string","default":"md"},"icon_position":{"type":"string","default":"right"},"icon_type":{"type":"string","default":"chevron"},"allow_multiple":{"type":"boolean","default":false},"collapse_all":{"type":"boolean","default":true},"animate":{"type":"boolean","default":true},"initial_open":{"type":"number","default":0},"searchable":{"type":"boolean","default":false},"nested":{"type":"boolean","default":false},"keyboard_nav":{"type":"boolean","default":true},"id":{"type":"string","default":""},"class":{"type":"string","default":""},"attributes":{"type":"array","default":[]},"data":{"type":"array","default":[]}},
    edit: (props) => generateBlockControls('hph/base-accordion', props),
    save: () => null
});

registerBlockType('hph/base-breadcrumbs', {
    title: 'Breadcrumbs',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"items":{"type":"array","default":[]},"variant":{"type":"string","default":"default"},"size":{"type":"string","default":"md"},"separator":{"type":"string","default":"chevron"},"custom_separator":{"type":"string","default":""},"show_home":{"type":"boolean","default":true},"show_current":{"type":"boolean","default":true},"max_items":{"type":"number","default":0},"collapse_on_mobile":{"type":"boolean","default":true},"show_icons":{"type":"boolean","default":false},"home_icon":{"type":"string","default":"home"},"schema_markup":{"type":"boolean","default":true},"labels":{"type":"array","default":[]},"breadcrumbs":{"type":"string","default":"__('Breadcrumbs"},"current_page":{"type":"string","default":"__('Current page"}},
    edit: (props) => generateBlockControls('hph/base-breadcrumbs', props),
    save: () => null
});

registerBlockType('hph/base-carousel', {
    title: 'Carousel',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"slides":{"type":"array","default":[]},"variant":{"type":"string","default":"default"},"aspect_ratio":{"type":"string","default":"landscape"},"height":{"type":"string","default":"auto"},"size":{"type":"string","default":"md"},"show_arrows":{"type":"boolean","default":true},"show_dots":{"type":"boolean","default":true},"show_counter":{"type":"boolean","default":false},"show_thumbnails":{"type":"boolean","default":false},"autoplay":{"type":"boolean","default":false},"autoplay_speed":{"type":"number","default":5000},"pause_on_hover":{"type":"boolean","default":true},"infinite":{"type":"boolean","default":true},"slides_to_show":{"type":"number","default":1},"slides_to_scroll":{"type":"number","default":1},"responsive":{"type":"boolean","default":true},"keyboard_nav":{"type":"boolean","default":true},"touch_swipe":{"type":"boolean","default":true},"lazy_load":{"type":"boolean","default":false},"zoom":{"type":"boolean","default":false},"lightbox":{"type":"boolean","default":false},"id":{"type":"string","default":""},"class":{"type":"string","default":""},"attributes":{"type":"array","default":[]},"data":{"type":"array","default":[]}},
    edit: (props) => generateBlockControls('hph/base-carousel', props),
    save: () => null
});

registerBlockType('hph/base-pagination', {
    title: 'Pagination',
    category: 'hph-sections',
    icon: 'layout',
    attributes: {"current_page":{"type":"string","default":"__('Current page"},"total_pages":{"type":"number","default":1},"base_url":{"type":"string","default":""},"query_params":{"type":"array","default":[]},"variant":{"type":"string","default":"default"},"size":{"type":"string","default":"md"},"alignment":{"type":"string","default":"center"},"show_first_last":{"type":"boolean","default":true},"show_prev_next":{"type":"boolean","default":true},"show_page_info":{"type":"boolean","default":false},"show_total_items":{"type":"boolean","default":false},"max_visible_pages":{"type":"number","default":7},"total_items":{"type":"string","default":"__('%s total items"},"items_per_page":{"type":"number","default":10},"ajax":{"type":"boolean","default":false},"infinite_scroll":{"type":"boolean","default":false},"keyboard_nav":{"type":"boolean","default":true},"labels":{"type":"array","default":[]},"previous":{"type":"string","default":"__('Previous"},"next":{"type":"string","default":"__('Next"},"last":{"type":"string","default":"__('Last"},"page_info":{"type":"string","default":"__('Page %1$s of %2$s"},"go_to_page":{"type":"string","default":"__('Go to page %s"}},
    edit: (props) => generateBlockControls('hph/base-pagination', props),
    save: () => null
});
