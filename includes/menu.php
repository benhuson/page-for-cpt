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
		add_filter( 'wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ), 10, 2 );

	}

	/**
	 * Filters the CSS class(es) applied to a menu item's list item element.
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

		return $classes;

	}

	/**
	 * Filters the sorted list of menu item objects before generating the menu's HTML.
	 *
	 * @since     WP 3.1.0
	 * @internal  Called via the `wp_nav_menu_objects` filter.
	 *
	 * @param   array   $sorted_menu_items  The menu items, sorted by each menu item's menu order.
	 * @param   object  $args               An object containing wp_nav_menu() arguments.
	 * @return  array                       Menu items.
	 */
	public function wp_nav_menu_objects( $sorted_menu_items, $args ) {

		return $sorted_menu_items;

	}

}
