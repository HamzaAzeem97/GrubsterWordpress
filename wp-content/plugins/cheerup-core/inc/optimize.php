<?php
/**
 * Performance optimizations and relevant plugins compatibility.
 * 
 * Only for the adventurous - raw code ahead!
 */
class CheerUp_Optimize
{
	private $_defer_fonts = array();
	
	public function __construct()
	{
		// Hooking at wp as we need is_amp_endpoint() available to disable for amp
		add_action('wp', array($this, 'init'));
	}

	public function init()
	{
		// AMP? Stop.
		if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
			return;
		}

		/**
		 * Autoptimize plugin "defer" mode requires several changes to how things work
		 */
		if (defined('AUTOPTIMIZE_PLUGIN_DIR')) {
			
			$css_optimize = get_option('autoptimize_css');
			$css_defer    = get_option('autoptimize_css_defer');
			
			// CSS defer enabled?
			if ($css_optimize && $css_defer) {
			
				add_action('wp_head', array($this, 'aop_defer_visibility'), -1);
				add_action('wp_head', array($this, 'aop_defer_ios_fix'), 100);
				add_action('bunyad_begin_body', array($this, 'aop_defer_loader_tag'));
		
				add_filter('autoptimize_filter_css_exclude', array($this, 'aop_exclude_merge'));
				add_filter('autoptimize_filter_css_preload_onload', array($this, 'aop_improve_preload'));

				add_filter('autoptimize_css_preload_polyfill', array($this, 'get_loadcss_polyfill'));
			}

			// Just the CSS optimize
			if ($css_optimize) {
				add_action('wp_enqueue_scripts', array($this, 'dequeue_fonts'), 11);
				add_action('wp_head', array($this, 'defer_fonts'));
				
				// CSS defer, when enabled, will add the polyfill on its own
				if (!$css_defer) {
					add_action('wp_footer', array($this, 'loadcss_polyfill'), 999);
				}

				// Add preload for VC styles.
				add_action('wp_print_styles', function() {

					// VC
					global $wp_styles;
					
					if (
						!is_object($wp_styles) 
						|| !property_exists($wp_styles, 'registered') 
						|| !array_key_exists('js_composer_front', $wp_styles->registered)
					) {
						return;
					}
		
					if (!is_object($wp_styles->registered['js_composer_front'])) {
						return;
					}
		
					$source = $wp_styles->registered['js_composer_front']->src;
					wp_deregister_style('js_composer_front');
		
					$this->the_preload_tag($source);
		
				}, 999);

			}

			// Jetpack bug: https://github.com/Automattic/jetpack/issues/14281
			remove_action('wp_enqueue_scripts', 'jetpack_woocommerce_lazy_images_compat', 11);
		}

		// Autoptimize merges scripts even with nomodule tag. We register the iepoylfills
		// with nomodule so it needs to be excluded.
		add_filter('autoptimize_filter_js_exclude', function($exclude) {
			return $exclude . ',ie-polyfills.js';
		});
		
		// Fix jetpack devicepx script to be deferred
		add_filter('script_loader_tag', array($this, 'jetpack_defer'), 10, 2);
	}
	
	/**
	 * Remove google font queue in the header
	 */
	public function dequeue_fonts()
	{
		$prefix = Bunyad::options()->get_config('theme_prefix');

		// Google fonts as default
		if (wp_style_is($prefix . '-fonts', 'enqueued')) {
			
			// Set flag
			$this->_defer_fonts[] = 'google';
			
			// Dequeue it for now
			wp_dequeue_style($prefix . '-fonts');
		}

		// TypeKit active?
		if (wp_style_is($prefix . '-typekit', 'enqueued')) {
			$this->_defer_fonts[] = 'typekit';

			// Dequeue it
			wp_dequeue_style($prefix . '-typekit');
		}
	}
	
	/**
	 * Add preload
	 */
	public function defer_fonts()
	{
		$theme_obj = Bunyad::get('theme');
		if (in_array('google', $this->_defer_fonts) && method_exists($theme_obj, 'get_fonts_enqueue')) {
				
			$this->the_preload_tag($theme_obj->get_fonts_enqueue());
		}

		//
		// To use Preload tag instead for TypeKit:
		// 	    $this->the_preload_tag('https://use.typekit.net/'. Bunyad::options()->typekit_id .'.css');
		//

		if (in_array('typekit', $this->_defer_fonts)) {
			?>
			<script>
			  (function(d) {
			    var config = {
			      kitId: '<?php echo esc_js(Bunyad::options()->typekit_id); ?>',
			      scriptTimeout: 3000,
			      async: true
			    },
			    h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
			  })(document);
			</script>
			<?php

		}
	}

	/**
	 * Output the preload tag
	 * 
	 * @param string $url   The script/style url
	 * @param string $type  style or script
	 * @param string $apply Whether to apply the style immediately
	 */
	public function the_preload_tag($url, $type = 'style', $apply = true)
	{
		if ($type !== 'style' && $apply) {
			$apply = false;
		}
		
		$link = '<link rel="preload" as="'. esc_attr($type) .'" media="all" href="' . esc_url($url) . '"' 
		      . ($apply ? ' onload="this.onload=null;this.rel=\'stylesheet\'"' : '') 
		      . ' />';
		
		echo $link;
	}
	
	/**
	 * Exclude some CSS from autoptimize merge
	 * 
	 * @param string $exclude
	 */
	public function aop_exclude_merge($exclude)
	{
		$prefix   = Bunyad::options()->get_config('theme_prefix');
		$exclude .= ",{$prefix}-skin-inline-css,{$prefix}-child-inline-css,ts-ld";
		
		return $exclude;
	}
	
	/**
	 * Add a loader tag for AOP deferred
	 */
	public function aop_defer_loader_tag()
	{
		echo '<div class="ts_ld"></div>';
	}
	
	/**
	 * Deferred stylesheet requires temporarily hiding
	 */
	public function aop_defer_visibility()
	{
		
		/**
		 * Unique loading method by ThemeSphere
		 * 
		 * 1. CSS to keep things hidden while styles load async.
		 * 2. Enhance load if rel=preload is supported in the browser.
		 *    - And thus preload 'onload' calls ts_ld().
		 *    - Add .ld class and remove it after 32-120ms of stylesheet being applied
		 */
		$script = "
		var ts_ld, ld_done, ld_skip;
		(function(w,d) {
			
			ld_skip = /iPad|iPhone|iPod/.test(navigator.userAgent);
			if (ld_skip) return;
			
			d.head.innerHTML += '<style>.ld .ts_ld { z-index: 99999; background: #fff; position: fixed; top: 0; left: 0; right: 0; bottom: 0; }a { color: #fff; }</style>';

			var h = d.documentElement;
			
			ld_done = function() {
				var f = function() {
					h.className = h.className.replace(/\bld\b/, '');
				};

				('requestIdleCallback' in w) 
					?  requestIdleCallback(f, {timeout: 120})
					:  setTimeout(f, 64);
			};

			ts_ld = function() {
				setTimeout(ld_done, 32);
			};
			
			h.className += ' ld';
			setTimeout(ld_done, 4500);
			
		})(window, document);
		";
		
		echo '<script>' . $this->_quick_minify($script) . '</script>';	
	}
	
	/**
	 * iOS doesn't play well with due to reflows
	 */
	public function aop_defer_ios_fix()
	{
		$script = "
			if (window.ld_skip) {
				var l = document.querySelectorAll('link[as=style]'), i = 0;
				for (; i < l.length; i++) {
					l[i].rel='stylesheet';
					l[i].as='';
					l[i].onload=null;
				}
			}
		";
		
		echo '<script>' . $this->_quick_minify($script) . '</script>';
	}
	
	/**
	 * Rewrite preload assets from Autoptimize to improve
	 */
	public function aop_improve_preload($content)
	{
		return "this.onload=null;this.rel='stylesheet';ts_ld()";
	}
	
	/**
	 * Quick CSS / JS minify that doesn't do much at all 
	 */
	public function _quick_minify($text)
	{
		return str_replace(array("\r", "\n", "\t"), '', $text);
	}
	
	/**
	 * Fix Jetpack not using deferred JS for devicepx 
	 */
	public function jetpack_defer($tag, $handle)
	{		
		if ($handle == 'devicepx') {
			$tag = str_replace('src=', 'defer src=', $tag);
		}
		
		return $tag;
	}

	/**
	 * Add loadCSS polyfill 
	 */
	public function get_loadcss_polyfill($existing = '')
	{
		// First few lines modified to hook into DOMContentLoaded
		$preloadPolyfill = '
		<script data-cfasync=\'false\'>
		var t = window;
		document.addEventListener("DOMContentLoaded", 
			function(){
				t.loadCSS||(t.loadCSS=function(){});var e=loadCSS.relpreload={};if(e.support=function(){var e;try{e=t.document.createElement("link").relList.supports("preload")}catch(t){e=!1}return function(){return e}}(),e.bindMediaToggle=function(t){function e(){t.media=a}var a=t.media||"all";t.addEventListener?t.addEventListener("load",e):t.attachEvent&&t.attachEvent("onload",e),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(e,3e3)},e.poly=function(){if(!e.support())for(var a=t.document.getElementsByTagName("link"),n=0;n<a.length;n++){var o=a[n];"preload"!==o.rel||"style"!==o.getAttribute("as")||o.getAttribute("data-loadcss")||(o.setAttribute("data-loadcss",!0),e.bindMediaToggle(o))}},!e.support()){e.poly();var a=t.setInterval(e.poly,500);t.addEventListener?t.addEventListener("load",function(){e.poly(),t.clearInterval(a)}):t.attachEvent&&t.attachEvent("onload",function(){e.poly(),t.clearInterval(a)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:t.loadCSS=loadCSS
			}
		);
		</script>';

		return $preloadPolyfill;
	}

	/**
	 * Load CSS polyfill in footer
	 */
	public function loadcss_polyfill($content) {
		echo $this->get_loadcss_polyfill();
	}
}


// init and make available in Bunyad::get('cheerup_optimize')
Bunyad::register('cheerup_optimize', array(
	'class' => 'CheerUp_Optimize',
	'init' => true
));