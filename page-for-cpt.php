<?php

/*
Plugin Name: Page for CPT
Plugin URI: https://github.com/benhuson/page-for-cpt
Description: Specify a page to use for the base URL of a custom post type via your WordPress Reading Settings admin page. CPT must support this plugin using the 'page_for_cpt_slug' filter.
Author: Ben Huson
Author URI: https://github.com/benhuson/page-for-cpt
Version: 0.1
License: GPLv2
*/

// Plugin directory and url paths.
define( 'PAGE_FOR_CPT_BASENAME', plugin_basename( __FILE__ ) );
define( 'PAGE_FOR_CPT_SUBDIR', '/' . str_replace( basename( __FILE__ ), '', PAGE_FOR_CPT_BASENAME ) );
define( 'PAGE_FOR_CPT_URL', plugins_url( PAGE_FOR_CPT_SUBDIR ) );
define( 'PAGE_FOR_CPT_DIR', plugin_dir_path( __FILE__ ) );

// i18n
define( 'PAGE_FOR_CPT_TEXTDOMAIN', 'page-for-cpt' );

// Don't load if class already exists
if ( ! class_exists( 'Page_For_CPT' ) ) {

	/**
	 * Filter custom post type slugs
	 *
	 * Apply the 'page_for_cpt_slug' filter on the rewrite argument when registering a post type,
	 * specifying the default slug to use (if a page is not set for the post type) and the post type:
	 * apply_filters( 'page_for_cpt_slug', {$default_slug}, {$post_type} )
	 *
	 *    $default_slug : Suggest setting to a URL friendly version of the post type.
	 *    $post_type    : Post type.
	 *
	 * 'rewrite' => array(
	 *    'slug'       => apply_filters( 'page_for_cpt_slug', 'my-post-type', 'my_post_type' ),
	 *    'with_front' => false,
	 *    'feeds'      => true,
	 *    'pages'      => true,
	 * )
	 *
	 * You should also set the post type's 'has_archive' to true.
	 */
	add_filter( 'page_for_cpt_slug', array( 'Page_For_CPT', 'page_for_cpt_slug' ), 10, 2 );

	add_action( 'admin_init', array( 'Page_For_CPT', 'init_settings_field' ), 500 );

	add_filter( 'body_class', array( 'Page_For_CPT', 'body_class' ) );

	/**
	 * Page for Custom Post Type Class
	 */
	class Page_For_CPT {

		/**
		 * Post Types
		 *
		 * @var  array
		 */
		public static $post_types = array();

		/**
		 * Handle CPT Slug
		 *
		 * @param   string  $slug       Default slug.
		 * @param   string  $post_type  Post type.
		 * @return  string              Page-based slug.
		 */
		public static function page_for_cpt_slug( $slug, $post_type ) {

			// Add post type
			if ( ! in_array( $post_type, Page_For_CPT::$post_types ) ) {
				Page_For_CPT::$post_types[] = $post_type;
			}

			// Get page slug
			$page_for_cpt = (array) get_option( 'page_for_cpt' );
			if ( isset( $page_for_cpt[ $post_type ] ) ) {
				$uri = get_page_uri( $page_for_cpt[ $post_type ] );
				if ( ! empty( $uri ) ) {
					$slug = $uri;
				}
			}

			return $slug;

		}

		public static function init_settings_field() {

			add_settings_field(
				'page_for_cpt',
				__( 'Pages for Post Types', 'page-for-cpt' ),
				array( 'Page_For_CPT', 'setting_field' ),
				'reading',
				'default'
			);

			register_setting( 'reading', 'page_for_cpt' );

		}

		public static function setting_field() {

			$page_for_cpt = (array) get_option( 'page_for_cpt' );

			if ( count( Page_For_CPT::$post_types ) > 0 ) {
				echo '<ul>';
				foreach ( Page_For_CPT::$post_types as $post_type ) {

					// Skip invalid post types
					if ( ! post_type_exists( $post_type ) ) {
						continue;
					}

					$post_type = get_post_type_object( $post_type );
					$selected = isset( $page_for_cpt[ $post_type->name ] ) ? $page_for_cpt[ $post_type->name ] : '';
					$id = 'page_for_cpt_' . sanitize_key( $post_type->name );

					?>

					<li>
						<label for="<?php echo $id; ?>">
							<?php
							printf( __( '%s: %s' ), $post_type->label, wp_dropdown_pages( array(
								'id'                => $id,
								'name'              => 'page_for_cpt[' . $post_type->name . ']',
								'echo'              => 0,
								'show_option_none'  => __( '&mdash; Select &mdash;', 'page-for-cpt' ),
								'option_none_value' => '0',
								'selected'          => $selected
							) ) );
							?>
						</label>
					</li>

					<?php
				}
				echo '</ul>';
			} else {
				?>
				<span class="description"><?php _e( '(No custom post types available)', 'page-for-cpt' ); ?></span>
				<?php
			}

		}

		public static function the_post( $post_type ) {

			global $post;

			if ( post_type_exists( $post_type ) ) {

				$page_for_cpt = (array) get_option( 'page_for_cpt' );

				if ( array_key_exists( $post_type, $page_for_cpt ) ) {

					$page_id = $page_for_cpt[ $post_type ];
					if ( 'page' == get_post_type( $page_id ) && 'publish' == get_post_status( $page_id ) ) {
						$post = get_post( $page_id );
						return setup_postdata( $post );
					}
				}

			}

			return false;

		}

		public static function body_class( $classes ) {

			if ( is_post_type_archive() ) {

				foreach ( Page_For_CPT::$post_types as $post_type ) {
					if ( is_post_type_archive( $post_type ) ) {

						$page_for_cpt = (array) get_option( 'page_for_cpt' );
						if ( isset( $page_for_cpt[ $post_type ] ) && apply_filters( 'page_for_cpt_use_page_body_classes', false, $post_type ) ) {

							// Add page classes
							$classes[] = 'page';
							$classes[] = 'page-' . $page_for_cpt[ $post_type ];
							$classes[] = 'page-template-default';

							// Remove general archive classes
							$classes = array_diff( $classes, array( 'archive', 'post-type-archive' ) );
						}

						break;
					}
				}

			}

			return $classes;

		}

	}

}
