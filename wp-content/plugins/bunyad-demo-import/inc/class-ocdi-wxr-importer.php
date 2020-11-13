<?php
/**
 * WXR importer class used in the One Click Demo Import plugin.
 * Needed to extend the WXR_Importer class to get/set the importer protected variables,
 * for use in the multiple AJAX calls.
 *
 * @package ocdi
 */

// Include required files, if not already present (via separate plugin).
if ( ! class_exists( 'WXR_Importer' ) ) {
	require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-wxr-importer.php';
}

class OCDI_WXR_Importer extends WXR_Importer {

	public function __construct( $options = array() ) {
		parent::__construct( $options );

		// Set current user to $mapping variable.
		// Fixes the [WARNING] Could not find the author for ... log warning messages.
		$current_user_obj = wp_get_current_user();
		$this->mapping['user_slug'][ $current_user_obj->user_login ] = $current_user_obj->ID;
	}

	/**
	 * Get all protected variables from the WXR_Importer needed for continuing the import.
	 */
	public function get_importer_data() {
		return array(
			'mapping'            => $this->mapping,
			'requires_remapping' => $this->requires_remapping,
			'exists'             => $this->exists,
			'user_slug_override' => $this->user_slug_override,
			'url_remap'          => $this->url_remap,
			'featured_images'    => $this->featured_images,
		);
	}

	/**
	 * Sets all protected variables from the WXR_Importer needed for continuing the import.
	 *
	 * @param array $data with set variables.
	 */
	public function set_importer_data( $data ) {
		$this->mapping            = empty( $data['mapping'] ) ? array() : $data['mapping'];
		$this->requires_remapping = empty( $data['requires_remapping'] ) ? array() : $data['requires_remapping'];
		$this->exists             = empty( $data['exists'] ) ? array() : $data['exists'];
		$this->user_slug_override = empty( $data['user_slug_override'] ) ? array() : $data['user_slug_override'];
		$this->url_remap          = empty( $data['url_remap'] ) ? array() : $data['url_remap'];
		$this->featured_images    = empty( $data['featured_images'] ) ? array() : $data['featured_images'];
	}
	
	// + Bunyad

	/**
	 * Fixes attachments added to the posts by better replacement than importer 
	 * 
	 * @see WXR_Importer::post_process_posts()
	 */
	protected function post_process_posts( $todo )
	{
		$url_remap = array();
		
		foreach ( $this->url_remap as $remote => $local ) {
			
			$parts = pathinfo( $remote );
			$name = $parts['filename'];
			
			// Ignore files without extensions added by aggressive search
			if (!$parts['extension']) {
				continue;
			}

			$parts_new = pathinfo( $local );
			$name_new = $parts_new['filename'];

			// Replace xyz.ext and xyz-nnnxnnn.ext 
			$url_remap['#' . preg_quote($parts['dirname'] . '/' . $name) . "(-(\d+)x(\d+)|)\.{$parts['extension']}" . '#'] 
				= $parts_new['dirname'] . '/' . $name_new . "\\1.{$parts_new['extension']}";
		}
		
		// Important as we're going to do post updates below, followed parent::post_process_posts()
		// which will get stale cache.
		wp_suspend_cache_invalidation(false);
		
		// Replaces attachment refs in post content using url remap with regex.
		// The default importer uses simple str_replace which isn't enough.
		foreach ( $todo as $post_id => $_ ) {
			
			$has_attachments = get_post_meta( $post_id, '_wxr_import_has_attachment_refs', true );
			if ( ! empty( $has_attachments ) ) {
				
				$post = get_post( $post_id );
				$content = $post->post_content;

				// Replace all the URLs we've got and update the post
				$new_content = preg_replace( array_keys($url_remap), $url_remap, $content );
				
				if ( $new_content !== $content ) {

					wp_update_post( array(
						'ID' => $post_id,
						'post_content' => $new_content
					) );
					
				}
			}
		}
		
		// Suspend it again - as in original importer
		wp_suspend_cache_invalidation(true);
		
		parent::post_process_posts($todo);
	}
	
}
