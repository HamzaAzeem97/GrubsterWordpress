<?php
/**
 * Options metabox for pages
 */

$options = array(
	array(
		'label' => esc_html_x('Layout Type', 'Admin', 'cheerup'),
		'name'  => 'layout_style', // will be _bunyad_layout_style
		'desc'  => esc_html_x('Default uses the site-wide general layout setting set in Appearance > Customize.', 'Admin', 'cheerup'),
		'type'  => 'radio',
		'options' => array(
			'' => esc_html_x('Default', 'Admin', 'cheerup'),
			'right' => esc_html_x('Right Sidebar', 'Admin', 'cheerup'),
			'full'  => esc_html_x('Full Width', 'Admin', 'cheerup')),
		'value' => '' // default
	),

	array(
		'label' => esc_html_x('Show Page Title?', 'Admin', 'cheerup'),
		'name'  => 'page_title', 
		'type'  => 'select',
		'options' => array(
			''    => esc_html_x('Default', 'Admin', 'cheerup'),
			'yes' => esc_html_x('Yes', 'Admin', 'cheerup'),
			'no' => esc_html_x('No', 'Admin', 'cheerup')
		),
		'value' => '' // default
	),
	
	array(
		'label' => esc_html_x('Featured Grid/Slider', 'Admin', 'cheerup'),
		'name'  => 'featured_slider', // will be _bunyad_featured_slider
		'desc'  => esc_html_x('For home-page, can also be set from use Appearance > Customize > Homepage > Home Slider.', 'Admin', 'cheerup'),
		'type'  => 'select',
		'options' => array(
			0 => esc_html_x('Disabled', 'Admin', 'cheerup'),
			1 => esc_html_x('Enabled', 'Admin', 'cheerup'),
		),
		'value' => 0 // default
	),

	array(
		'label' => esc_html_x('Featured Style', 'Admin', 'cheerup'),
		'name'  => 'slider_type',
		'type'  => 'select',
		'options' => array(
			'stylish' => esc_html_x('Stylish (3 images)', 'Admin', 'cheerup'),
			'default' => esc_html_x('Classic Slider (3 Images)', 'Admin', 'cheerup'),
			'beauty'  => esc_html_x('Beauty (Single Image)', 'Admin', 'cheerup'),
			'fashion' => esc_html_x('Fashion (Single Image)', 'Admin', 'cheerup'),
			'trendy'  => esc_html_x('Trendy (2 Images)', 'Admin', 'cheerup'),
			'large'   => esc_html_x('Large Full Width', 'Admin', 'cheerup'),
			'grid'    => esc_html_x('Grid (1 Large + 2 small)', 'Admin', 'cheerup'),
			'grid-tall' => esc_html_x('Tall Grid (1 Large + 2 small)', 'Admin', 'cheerup'),
			'carousel'  => esc_html_x('Carousel (3 Small Posts)', 'Admin', 'cheerup'),
		),
		'value' => '' // default
	),

	array(
		'label' => esc_html_x('Slider Post Count', 'Admin', 'cheerup'),
		'name'  => 'slider_number',
		'type'  => 'text',
		'desc'  => esc_html_x('Total number of posts for slider.', 'Admin', 'cheerup'),
		'value' => 6, // default
	),
	
	array(
		'label' => esc_html_x('Slider Posts Tag', 'Admin', 'cheerup'),
		'name'  => 'slider_tags',
		'desc'  => esc_html_x('Posts with this tag will be shown in the slider. Leaving it empty will show latest posts.', 'Admin', 'cheerup'),
		'type'  => 'text',
		'value' => 'featured' // default
	),
		
	array(
		'name' => 'slider_post_ids',
		'label'   => esc_html_x('Slider Post IDs', 'Admin', 'cheerup'),
		'value'   => '',
		'desc'    => esc_html_x('Advance Usage: Enter post ids separated by comma you wish to show in the slider, in order you wish to show them in. Example: 11, 105, 2', 'Admin', 'cheerup'),
		'type'    => 'text',
	),

);