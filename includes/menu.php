<?php

/**
 * @package     Page for CPT
 * @subpackage  Menu
 */

class Page_For_CPT_Menu {

	/**
	 * Set up menu filters.
	 */
	public function __construct() {

		add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_css_class' ), 10, 4 );

	}

	/**
	 * Filters the CSS class(es) applied to a menu item's list item element.
	 *
	 * This filter adds `parent` and `ancestor` classes to any page menu item
	 * that acts as an archive for the current single post type page.
	 *
	 * We treat the archive page as a `parent` if the current post page does
	 * not have a parent post, and an as `ancestor` if the post is the child
	 * of another post (hierarchical).
	 *
	 * @since     WP 3.0.0
	 * @since     WP 4.1.0  The `$depth` parameter was added.
	 * @internal  Called via the `nav_menu_css_class` filter.
	 *
	 * @param   array   $classes  The CSS classes that are applied to the menu item's `<li>` element.
	 * @param   object  $item     The current menu item.
	 * @param   array   $args     An array of wp_nav_menu() arguments.
	 * @param   int     $depth    Depth of menu item. Used for padding.
	 * @return  array             CSS classes.
	 */
	public function nav_menu_css_class( $classes, $item, $args, $depth ) {

		// Only check page menu items
		if ( is_singular() && $this->is_page_menu_item( $item ) ) {

			$post_obj = get_queried_object();

			$post_type = $this->get_current_post_type();
			$page_id = Page_For_CPT::get_page_for_post_type( $post_type );
			$page_ancestor_ids = get_ancestors( $page_id, 'page' );

			// If the page menu item is an archive page, add classes
			if ( $page_id > 0 && $item->object_id == $page_id ) {

				$classes = $this->add_archive_page_menu_item_classes( $classes, $post_type, $post_obj->post_parent );

			} elseif ( ! empty( $page_ancestor_ids ) && in_array( $item->object_id, $page_ancestor_ids ) ) {

				$classes = $this->add_archive_ancestor_page_menu_item_classes( $classes );

			}

		}

		if ( is_post_type_archive() && $this->is_page_menu_item( $item ) ) {

			$post_type_obj = get_queried_object();

			$post_type = $this->get_current_post_type();
			$page_id = Page_For_CPT::get_page_for_post_type( $post_type );
			$page_ancestor_ids = get_ancestors( $page_id, 'page' );

			// If the page menu item is an archive page, add classes
			if ( $page_id > 0 && $item->object_id == $page_id ) {

				$classes = $this->add_page_menu_item_classes( $classes );

			} elseif ( ! empty( $page_ancestor_ids ) && in_array( $item->object_id, $page_ancestor_ids ) ) {

				$classes = $this->add_archive_ancestor_page_menu_item_classes( $classes );

			}

		}

		return $classes;

	}

	/**
	 * Menu Item Is Page
	 *
	 * @param   WP_Post  $item  Menu item object.
	 * @return  boolean
	 */
	private function is_page_menu_item( $item ) {

		return 'post_type' == $item->type && 'page' == $item->object;

	}

	/**
	 * Get Current Post Type
	 *
	 * @return  string  Post type.
	 */
	private function get_current_post_type() {

		$qo = get_queried_object();

		if ( is_post_type_archive() ) {

			return $qo->name;

		} elseif ( is_singular() ) {

			return get_post_type( $qo );

		}

		return '';

	}

	/**
	 * Add Page Menu Item Classes
	 *
	 * @param   array    $classes  Menu item classes.
	 * @param   WP_Post  $post     Post object.
	 * @return  array              Classes.
	 */
	private function add_page_menu_item_classes( $classes ) {

		$classes[] = 'current-menu-item';

		return array_unique( $classes );

	}

	/**
	 * Add Archive Page Menu Item Classes
	 *
	 * @param   array           $classes  Menu item classes.
	 * @param   WP_Post|string  $post     Post object.
	 * @param   integer         $parent   Post parent.
	 * @return  array                     Classes.
	 */
	private function add_archive_page_menu_item_classes( $classes, $post_type = '', $parent = 0 ) {

		// Parent or ancestor?
		$relative = $parent > 0 ? 'ancestor' : 'parent';

		// Add `parent` or `ancestor` menu classes
		$classes[] = 'current-menu-' . $relative;
		$classes[] = 'current-post_type-' . $relative;
		$classes[] = 'current-' . sanitize_html_class( $post_type ) . '-' . $relative;

		return array_unique( $classes );

	}

	/**
	 * Add Archive Ancestor Page Menu Item Classes
	 *
	 * @param   array  $classes  Menu item classes.
	 * @return  array            Classes.
	 */
	private function add_archive_ancestor_page_menu_item_classes( $classes ) {

		// Add page `ancestor` menu classes
		$classes[] = 'current-menu-ancestor';
		$classes[] = 'current-page-ancestor';

		return array_unique( $classes );

	}

}
