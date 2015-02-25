<?php

/*
Plugin Name: Page for CPT
Plugin URI: https://github.com/benhuson/page-for-cpt
Description: Specify a page to use for the base URL of a custom post type via your WordPress Reading Settings admin page. May not work with post types that specify their own custom permalink structure.
Author: Ben Huson
Author URI: https://github.com/benhuson/page-for-cpt
Version: 0.2
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

	add_action( 'admin_init', array( 'Page_For_CPT', 'init_settings_field' ), 500 );

	add_filter( 'body_class', array( 'Page_For_CPT', 'body_class' ) );

	add_action( 'registered_post_type', array( 'Page_For_CPT', 'registered_post_type' ), 5, 2 );

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

		public static function init_settings_field() {

			add_settings_field(
				'page_for_cpt',
				__( 'Pages for Post Types', PAGE_FOR_CPT_TEXTDOMAIN ),
				array( 'Page_For_CPT', 'setting_field' ),
				'reading',
				'default'
			);

			register_setting( 'reading', 'page_for_cpt' );

		}

		public static function setting_field() {

			$page_for_cpt = (array) get_option( 'page_for_cpt' );

			$post_types = array_keys( get_post_types( array(
				'public'   => true,
				'_builtin' => false
			) ) );

			if ( count( $post_types ) > 0 ) {
				echo '<table class="page-for-cpt">';
				foreach ( $post_types as $post_type ) {

					// Skip invalid post types
					if ( ! post_type_exists( $post_type ) ) {
						continue;
					}

					$post_type = get_post_type_object( $post_type );
					$selected = isset( $page_for_cpt[ $post_type->name ] ) ? $page_for_cpt[ $post_type->name ] : '';
					$id = 'page_for_cpt_' . sanitize_key( $post_type->name );

					?>

					<tr>
						<td>
							<label for="<?php echo $id; ?>"><?php echo $post_type->label; ?>:</label>
						</td>
						<td>
							<?php

							wp_dropdown_pages( array(
								'id'                => $id,
								'name'              => 'page_for_cpt[' . $post_type->name . ']',
								'show_option_none'  => sprintf( '&mdash; %s &mdash;', _x( 'Not Set', 'menu option', PAGE_FOR_CPT_TEXTDOMAIN ) ),
								'option_none_value' => '0',
								'selected'          => $selected
							) );

							?>
						</td>
					</tr>

					<?php
				}
				echo '</table>';
				echo '<style>table.page-for-cpt td { padding: 0 20px 0.5em 0; }</style>';
			} else {
				?>
				<span class="description"><?php _e( '(No custom post types available)', PAGE_FOR_CPT_TEXTDOMAIN ); ?></span>
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

		public static function get_post_type_for_page() {

			if ( is_page() ) {

				$page_for_cpt = (array) get_option( 'page_for_cpt' );
				$post_type = array_search( get_queried_object_id(), $page_for_cpt );

				if ( post_type_exists( $post_type ) ) {
					return $post_type;
				}

			}

			return false;

		}

		public static function get_page_for_post_type( $post_type ) {

			$page_for_cpt = (array) get_option( 'page_for_cpt' );

			if ( isset( $page_for_cpt[ $post_type ] ) ) {
				return $page_for_cpt[ $post_type ];
			}

			return 0;

		}

		/**
		 * Registered Post Type
		 *
		 * Called by the `registered_post_type` hook when a new post type is registered.
		 *
		 * @since  0.3
		 * @internal
		 *
		 * @param  string  $post_type  Post type.
		 * @param  array   $args       Post type arguments.
		 */
		public static function registered_post_type( $post_type, $args ) {

			global $wp_post_types;

			// Get all public custom post types.
			$post_types = get_post_types( array(
				'public'   => true,
				'_builtin' => false
			) );

			if ( in_array( $post_type, $post_types ) ) {

				$page_for_cpt = (array) get_option( 'page_for_cpt' );

				if ( isset( $page_for_cpt[ $post_type ] ) ) {

					$page_id = absint( $page_for_cpt[ $post_type ] );
					$uri = get_page_uri( $page_id );

					// If a page is assigned, use that for the rewrite rules.
					if ( ! empty( $uri ) ) {

						$args->has_archive = $uri;

						$args->rewrite = wp_parse_args( array(
							'slug'       => $uri,
							'with_front' => false
						), (array) $args->rewrite );

					}

					// Rebuild Rewrite Rules
					// See the register_post_type() function in `wp-includes/post.php`
					// https://core.trac.wordpress.org/browser/trunk/src/wp-includes/post.php#L1427
					if ( is_admin() || '' != get_option( 'permalink_structure' ) ) {

						if ( $args->has_archive ) {

							$archive_slug = $args->has_archive === true ? $args->rewrite['slug'] : $args->has_archive;

							if ( $args->rewrite['with_front'] ) {
								$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
							} else {
								$archive_slug = $wp_rewrite->root . $archive_slug;
							}

							add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );

							if ( $args->rewrite['feeds'] && $wp_rewrite->feeds ) {
								$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
								add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
								add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
							}

							if ( $args->rewrite['pages'] ) {
								add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );
							}

						}

						$permastruct_args = $args->rewrite;
						$permastruct_args['feed'] = $permastruct_args['feeds'];
						add_permastruct( $post_type, "{$args->rewrite['slug']}/%$post_type%", $permastruct_args );

					}

					$wp_post_types[ $post_type ] = $args;

				}

			}

		}

	}

}
