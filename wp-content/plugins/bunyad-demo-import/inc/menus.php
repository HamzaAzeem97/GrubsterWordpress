<?php
/**
 * Import specified menus and the items.
 * 
 * @copyright ThemeSphere
 */
class Bunyad_Demo_Import_Menus
{
	public $items;
	public $menu_data;
	public $base_url;

	protected $random_cat_ids = [];
	protected $random_post_ids = [];
	
	public function __construct($menu_data = [], $base_url = '')
	{
		$this->menu_data = $menu_data;
		$this->base_url  = $base_url;
		
		if (!$this->base_url) {
			$this->base_url = untrailingslashit(get_bloginfo('url'));
		}
	}

	// public function map_term_ids($map)
	// {
	// 	foreach ($this->menu_data as $key => $menu) {
	// 		$this->menu_data[$key] = $this->replace_term_ids($menu, $map);
	// 	}
	// }

	// public function replace_term_ids($menu, $map) {

	// 	$items = [];
	// 	foreach ($menu['items'] as $key => $item) {

	// 		$items[$key] = $item;

	// 		if ($item['items']) {
	// 			$items[$key]['items'] = $this->replace_term_ids($item, $map);
	// 		}
	// 	}

	// 	if (count($items)) {
	// 		$menu['items'] = $items;
	// 	}

	// 	return $items;
	// }

	/**
	 * Begin importing the menu.
	 *
	 * @return void
	 */
	public function import()
	{
		// Add all the menus and their respective items.
		foreach ($this->menu_data as $menu) {
			$menu_id = $this->create_menu($menu);

			if (false !== $menu_id) {
				$this->add_to_menu($menu['items'], $menu_id);
			}
		}
	}

	/**
	 * Create a menu and add its items.
	 *
	 * @param array $menu
	 * @return void
	 */
	public function create_menu($menu) 
	{
		if (!isset($menu['label'])) {
			return;
		}

		$menu_id = wp_create_nav_menu($menu['label']);

		// Nothing to import?
		if (is_wp_error($menu_id) || !isset($menu['items'])) {
			return false;
		}

		// Add location to the menu if specified.
		if (!empty($menu['location'])) {
			$menu_locations = (array) get_theme_mod('nav_menu_locations');
			set_theme_mod('nav_menu_locations', array_replace(
				$menu_locations,
				[
					$menu['location'] => $menu_id
				]
			));
		}

		return $menu_id;
	}

	/**
	 * Add all items to a menu.
	 * 
	 * @param array   $items   Array of items to array 
	 * @param integer $menu_id Menu ID
	 * @param integer $parent  Parent Item ID.
	 * @param integer $position Position starts at 0 but continues globally recursively.
	 */
	public function add_to_menu($items = [], $menu_id, $parent = 0, &$position = 0)
	{
		if (!$items) {
			$items = $this->items;
		}

		foreach ($items as $key => $item) {
			
			// Reset parent for current loop.
			$item_id = 0;

			// If type is unknown, fallback to custom.
			$item['type'] = empty($item['type']) ? 'custom' : $item['type'];

			// Base shared args for the nav item.
			$args = [
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
				'menu-item-position'  => isset($item['order']) ? $item['order'] : $position,
				'menu-item-title'     => isset($item['title']) ? $item['title'] : ''
			];

			// Check menu type. Currently supports category, custom and post.
			switch ($item['type']) {

				/**
				 * Category item by slug or a random category (sorted post count).
				 */
				case 'category':
					if (!isset($item['slug'])) {
						continue 2;
					}

					$term = $this->get_category($item['slug']);
					if (!$term) {
						continue 2;
					}

					$item = array_replace($item, [
						'object'    => 'category',
						'object_id' => $term->term_id,
						'type'      => 'taxonomy',
					]);

					break;

				/**
				 * Add a post item using slug or a random latest post.
				 */
				case 'post':
				case 'page':

					$post = $this->get_post(
						isset($item['slug']) ? $item['slug'] : '',
						$item['type']
					);

					if (!$post) {
						continue 2;
					}

					$item = array_replace($item, [
						'object'    => $item['type'],
						'object_id' => $post->ID,
						'type'      => 'post_type',
					]);

					break;

				/**
				 * Custom item with title and url with an optional open target.
				 */
				case 'custom':

					// Title is required.
					if (!isset($item['title'])) {
						continue 2;
					}

					// Set URL to # if container or empty.
					$item_url = isset($item['url']) ? $item['url'] : '#';
					$item_url = str_replace('{base_url}', $this->base_url, $item_url);

					$args += [
						'menu-item-url' => $item_url,
						'menu-item-target' => isset($item['target']) ? $item['target'] : ''
					];

					break;

				// Unsupport type
				default:
					continue 2;
			}

			if (isset($item['object'])) {
				$args['menu-item-object'] = $item['object'];
			}

			if (isset($item['object_id'])) {
				$args['menu-item-object-id'] = $item['object_id'];
			}

			// Set the type of menu: taxonomy, custom, post_type etc.
			$args['menu-item-type'] = $item['type'];
			
			// Finally, add the menu item.
			$item_id = wp_update_nav_menu_item($menu_id, 0, $args);

			// Add menu item meta fields, such as for mega menu.
			if (!empty($item['meta'])) {
				foreach ((array) $item['meta'] as $key => $value) {
					update_post_meta($item_id, '_menu_item_' . $key, $value);
				}
			}
			
			// Increment the auto-position.
			$position++;

			// Recursively add any child items.
			if (!empty($item['items'])) {
				$this->add_to_menu($item['items'], $menu_id, $item_id, $position);
			}
		}
	}

	/**
	 * Get the specified post by slug or a random post if not found.
	 * 
	 * @return bool|WP_Post
	 */
	public function get_post($slug = '', $type = 'post')
	{
		if ($slug) {
			$posts = get_posts([
				'name' => $slug,
				'posts_per_page' => 1,
				'post_type' => $type,
			]);

			if ($posts) {
				return current($posts);
			}
		}

		/**
		 * Get a random latest post, excluding already used ones.
		 */

		// Add hello world / first post to excluded posts.
		$exclude = array_merge([1], $this->random_post_ids);

		$posts = get_posts([
			'posts_per_page' => 1,
			'exclude'   => $exclude,
			'post_type' => $type,
		]);

		if (!$posts) {
			return false;
		}

		$post = current($posts);
		$this->random_post_ids[] = $post->ID;

		return $post;
	}

	/**
	 * Get the specified category by slug or a random category if not found.
	 * 
	 * @return bool|WP_Term
	 */
	public function get_category($slug)
	{
		$term = get_term_by('slug', $slug, 'category');
		if (!$term || empty($term->term_id)) {
			$categories = get_terms('category', [
				'orderby' => 'count',
				'order'   => 'desc',
				'exclude' => $this->random_cat_ids,
			]);

			if (!$categories) {
				return false;
			}

			// Return first category with highest posts and add to exclusion list.
			$category = current($categories);
			$this->random_cat_ids[] = $category->term_id;

			return $category;
		}

		return $term;
	}
}
