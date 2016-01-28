<?php

/**
 * @package     Page for CPT
 * @subpackage  Admin
 */

add_action( 'admin_init', array( 'Page_For_CPT_Admin', 'init_settings_field' ), 500 );

class Page_For_CPT_Admin {

	/**
	 * Init Settings Field
	 *
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

}
