<?php

/**
 * @package     Page for CPT
 * @subpackage  Admin
 */

add_action( 'admin_init', array( 'Page_For_CPT_Admin', 'init_settings_field' ), 500 );
add_action( 'edit_form_after_title', array( 'Page_For_CPT_Admin', 'fix_no_editor_on_posts_page' ), 0 );
add_filter( 'display_post_states', array( 'Page_For_CPT_Admin', 'display_post_states' ), 8, 2 );

class Page_For_CPT_Admin {

	/**
	 * Init Settings Field
	 *
	 * @since     0.4
	 * @internal  Called by the `admin_init` hook.
	 */
	public static function init_settings_field() {

		add_settings_field(
			'page_for_cpt',
			__( 'Pages for Post Types', 'page-for-cpt' ),
			array( get_class(), 'setting_field' ),
			'reading',
			'default'
		);

		register_setting( 'reading', 'page_for_cpt', array( get_class(), 'validate_setting_field' ) );

	}

	/**
	 * Setting Field
	 *
	 * @since     0.4
	 * @internal  Called by the `init_settings_field()`.
	 */
	public static function setting_field() {

		$page_for_cpt = Page_For_CPT::get_page_for_cpt_option();

		$post_types = Page_For_CPT::get_public_post_types();

		if ( count( $post_types ) > 0 ) {

			echo '<table class="page-for-cpt">';

			foreach ( $post_types as $post_type ) {

				// Skip invalid post types
				if ( ! post_type_exists( $post_type ) ) {
					continue;
				}

				$post_type = get_post_type_object( $post_type );
				$selected = Page_For_CPT::get_page_for_post_type( $post_type->name );

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
	 * @since     0.4
	 * @internal  Called by the `init_settings_field()`.
	 *
	 * @param   string  $value  Value.
	 * @return  string          Validated value.
	 */
	public static function validate_setting_field( $value ) {

		$page_for_cpt = Page_For_CPT::get_page_for_cpt_option();

		if ( $value !== $page_for_cpt ) {
			flush_rewrite_rules( true );
		}

		return $value;

	}

	/**
	 * Display Post States
	 *
	 * @param   array    $post_states  Post states.
	 * @param   WP_Post  $post         Post object.
	 * @return  array                  States.
	 */
	public static function display_post_states( $post_states, $post ) {

		$page_for_cpt = Page_For_CPT::get_page_for_cpt_option();
		$post_type = array_search( $post->ID, $page_for_cpt );

		// Is post type page
		if ( ! empty( $post_type ) ) {
			$post_type_object = get_post_type_object( $post_type );
			$post_states[ 'page_for_cpt' ] = sprintf( esc_html__( '%s Page', 'page-for-cpt' ), esc_html( $post_type_object->labels->name ) );
		}

		return $post_states;

	}

	/**
	 * Fix No Editor On Posts Page
	 * 
	 * Add the wp-editor back into WordPress after it was removed in 4.2.2.
	 *
	 * @since     0.5
	 * @internal  Private. Called via the `edit_form_after_title` action.
	 *
	 * @see  /wp-admin/edit-form-advanced.php
	 *
	 * @param  WP_Post  $post  Post object.
	 */
	public static function fix_no_editor_on_posts_page( $post ) {

		if ( $post->ID != get_option( 'page_for_posts' ) ) {
			return;
		}

		remove_action( 'edit_form_after_title', '_wp_posts_page_notice' );
		add_post_type_support( 'page', 'editor' );

	}

}
