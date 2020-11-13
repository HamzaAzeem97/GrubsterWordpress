<?php
/**
 * Grid Style 2 - used for several loops
 */

$props = isset($props) ? $props : [];

Bunyad::core()->partial('content-grid', [
	'classes'        => get_post_class('grid-post grid-post-b'), 
	'show_social'    => false,
] + $props);
