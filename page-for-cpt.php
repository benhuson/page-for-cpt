<?php

/*
Plugin Name: Page for CPT
Plugin URI: https://github.com/benhuson/page-for-cpt
Description: Specify a page to use for the base URL of a custom post type via your WordPress Reading Settings admin page. May not work with post types that specify their own custom permalink structure.
Author: Ben Huson
Author URI: https://github.com/benhuson/page-for-cpt
Version: 0.3
License: GPLv2
*/

// Don't load if class already exists
if ( ! class_exists( 'Page_For_CPT' ) ) {

	add_action( 'plugins_loaded', array( 'Page_For_CPT', 'load' ) );

	/**
	 * Page for Custom Post Type Class
	 */
	class Page_For_CPT {

		/**
		 * Plugin File
		 *
		 * @var  string
		 */
		private static $file = __FILE__;

		/**
		 * Post Types
		 *
		 * @var  array
		 */
		public static $post_types = array();

		/**
		 * Load
		 */
		public static function load() {

			add_action( 'admin_init', array( get_class(), 'init_settings_field' ), 500 );
			add_filter( 'body_class', array( get_class(), 'body_class' ) );
			add_filter( 'get_the_archive_title', array( get_class(), 'get_the_archive_title' ) );
			add_filter( 'get_the_archive_description', array( get_class(), 'get_the_archive_description' ) );

			if ( self::has_wp_version( '4.4' ) ) {
				add_filter( 'register_post_type_args', array( get_class(), 'register_post_type_args' ), 10, 2 );
			} else {
				add_action( 'registered_post_type', array( get_class(), 'registered_post_type' ), 5, 2 );
			}

		}

		/**
		 * Init Settings Field
		 */
		public static function init_settings_field() {

			add_settings_field(
				'page_for_cpt',
				__( 'Pages for Post Types', 'page-for-cpt' ),
				array( 'Page_For_CPT', 'setting_field' ),
				'reading',
				'default'
			);

			register_setting( 'reading', 'page_for_cpt', array( 'Page_For_CPT', 'validate_setting_field' ) );

		}

		/**
		 * Setting Field
		 */
		public static function setting_field() {

			$page_for_cpt = self::get_page_for_cpt_option();

			$post_types = self::get_public_post_types();

			if ( count( $post_types ) > 0 ) {

				echo '<table class="page-for-cpt">';

				foreach ( $post_types as $post_type ) {

					// Skip invalid post types
					if ( ! post_type_exists( $post_type ) ) {
						continue;
					}

					$post_type = get_post_type_object( $post_type );
					$selected = self::get_page_for_post_type( $post_type->name );

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
								'show_option_none'  => sprintf( '&mdash; %s &mdash;', _x( 'Select', 'menu option', 'page-for-cpt' ) ),
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

				<span class="description"><?php _e( '(No custom post types available)', 'page-for-cpt' ); ?></span>

				<?php

			}

		}

		/**
		 * Validate Setting Field
		 *
		 * When settings are changed, flush rewrite rules.
		 *
		 * @since  0.3
		 * @internal
		 *
		 * @param   string  $value  Value.
		 * @return  string          Validated value.
		 */
		public static function validate_setting_field( $value ) {

			$page_for_cpt = self::get_page_for_cpt_option();

			if ( $value !== $page_for_cpt ) {
				flush_rewrite_rules( true );
			}

			return $value;

		}

		/**
		 * The Post
		 *
		 * @param   string   $post_type  Post type.
		 * @return  boolean
		 */
		public static function the_post( $post_type ) {

			global $post;

			if ( post_type_exists( $post_type ) ) {

				$page_id = self::get_page_for_post_type( $post_type );

				if ( $page_id > 0 ) {

					if ( 'page' == get_post_type( $page_id ) && 'publish' == get_post_status( $page_id ) ) {
						$post = get_post( $page_id );
						return setup_postdata( $post );
					}

				}

			}

			return false;

		}

		/**
		 * Body Class
		 *
		 * @param   array  $classes  Body classes.
		 * @return  array            Body classes.
		 */
		public static function body_class( $classes ) {

			if ( is_post_type_archive() ) {

				foreach ( self::$post_types as $post_type ) {
					if ( is_post_type_archive( $post_type ) ) {

						$page_id = self::get_page_for_post_type( $post_type );

						if ( $page_id > 0 && apply_filters( 'page_for_cpt_use_page_body_classes', false, $post_type ) ) {

							// Add page classes
							$classes[] = 'page';
							$classes[] = 'page-' . $page_id;
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

		/**
		 * Get The Archive Title
		 *
		 * @since  0.3
		 *
		 * @param   string  $title  Archive title.
		 * @return  string          Filtered archive title.
		 */
		public static function get_the_archive_title( $title ) {

			if ( is_post_type_archive() || is_home() ) {

				if ( is_home() ) {
					$page_id = get_option( 'page_for_posts' );
				} else {
					$page_id = self::get_page_for_post_type( get_post_type() );
				}

				if ( $page_id > 0 ) {
					$title = get_the_title( $page_id );
				}

			}

			return $title;

		}

		/**
		 * Get The Archive Description
		 *
		 * @since  0.3
		 *
		 * @param   string  $description  Archive description.
		 * @return  string                Filtered archive description.
		 */
		public static function get_the_archive_description( $description ) {

			if ( is_post_type_archive() || is_home() ) {

				if ( is_home() ) {
					$page_id = get_option( 'page_for_posts' );
				} else {
					$page_id = self::get_page_for_post_type( get_post_type() );
				}

				if ( $page_id > 0 ) {
					$page = get_post( $page_id );
					$description = apply_filters( 'the_content', $page->post_content );
				}

			}

			return $description;

		}

		/**
		 * Get Post Type for Page
		 *
		 * @return  string|false  Post type.
		 */
		public static function get_post_type_for_page() {

			if ( is_page() ) {

				$page_for_cpt = self::get_page_for_cpt_option();
				$post_type = array_search( get_queried_object_id(), $page_for_cpt );

				if ( post_type_exists( $post_type ) ) {
					return $post_type;
				}

			}

			return false;

		}

		/**
		 * Get Page for Post Type
		 *
		 * @param   string  $post_type  Post type.
		 * @return  int                 Post ID.
		 */
		public static function get_page_for_post_type( $post_type ) {

			$page_for_cpt = self::get_page_for_cpt_option();

			if ( isset( $page_for_cpt[ $post_type ] ) ) {
				return absint( $page_for_cpt[ $post_type ] );
			}

			return 0;

		}

		/**
		 * Get Page for CPT Option
		 *
		 * @return  array  Option data.
		 */
		public static function get_page_for_cpt_option() {

			return (array) get_option( 'page_for_cpt' );

		}

		/**
		 * Get Public Post Types
		 *
		 * @return  array  Post types.
		 */
		public static function get_public_post_types() {

			return array_keys( get_post_types( array(
				'public'   => true,
				'_builtin' => false
			) ) );

		}

		/**
		 * Register Post Type Args
		 *
		 * Change the slug for post types.
		 *
		 * @internal  Called by `register_post_type_args` hook.
		 *
		 * @param   array   $args       Post type args.
		 * @param   string  $post_type  Post type.
		 * @return  array               Post type args.
		 */
		public static function register_post_type_args( $args, $post_type ) {

			// Get all public custom post types.
			$post_types = self::get_public_post_types();

			if ( isset( $args['public'] ) && $args['public'] && ( ! isset( $args['_builtin'] ) || ! $args['_builtin'] ) ) {

				$page_for_cpt = self::get_page_for_post_type( $post_type );

				if ( $page_for_cpt > 0 ) {

					$uri = get_page_uri( $page_for_cpt );

					// If a page is assigned, use that for the rewrite rules.
					if ( ! empty( $uri ) ) {

						$args['has_archive'] = $uri;
						$args['rewrite'] = wp_parse_args( array(
							'slug'       => $uri,
							'with_front' => false
						), (array) $args['rewrite'] );

					}

				}

			}

			return $args;

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
			$post_types = self::get_public_post_types();

			if ( in_array( $post_type, $post_types ) ) {

				$page_for_cpt = self::get_page_for_post_type( $post_type );

				if ( $page_for_cpt > 0 ) {

					$uri = get_page_uri( $page_for_cpt );

					// If a page is assigned, use that for the rewrite rules.
					if ( ! empty( $uri ) ) {

						$args->has_archive = $uri;

						$args->rewrite = wp_parse_args( array(
							'slug'       => $uri,
							'with_front' => false
						), (array) $args->rewrite );

					}

					// Rebuild Rewrite Rules
					self::add_post_type_rewrite_rules( $post_type, $args );

					$wp_post_types[ $post_type ] = $args;

				}

			}

		}

		/**
		 * Add Post Type Rewrite Rules
		 *
		 * See the register_post_type() function in `wp-includes/post.php`
		 * https://core.trac.wordpress.org/browser/trunk/src/wp-includes/post.php#L1427
		 *
		 * @param  string  $post_type  Post type.
		 * @param  array   $args       Post type args.
		 */
		public static function add_post_type_rewrite_rules( $post_type, $args ) {

			global $wp_post_types, $wp_rewrite;

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

		}

		/**
		 * Has WordPress Version
		 *
		 * @param   string  $version  Version in format `0.0.0`
		 * @return  boolean           True if WordPress version is same or higher.
		 */
		public static function has_wp_version( $version ) {

			global $wp_version;

			return version_compare( $wp_version, $version ) >= 0;

		}

		/**
		 * Plugin Basename
		 *
		 * @return  string  Plugin basename.
		 */
		public static function basename() {

			return plugin_basename( self::$file );

		}

		/**
		 * Plugin Sub Directory
		 *
		 * @return  string  Plugin folder name.
		 */
		public static function sub_dir() {

			return '/' . str_replace( basename( self::$file ), '', self::basename() );

		}

		/**
		 * Plugin URL
		 *
		 * @return  string  Plugin directory URL.
		 */
		public static function url() {

			return plugins_url( self::sub_dir() );

		}

		/**
		 * Plugin Directory
		 * 
		 * @return  string  Plugin directory path.
		 */
		public static function dir() {

			return plugin_dir_path( self::$file );

		}

	}

}
