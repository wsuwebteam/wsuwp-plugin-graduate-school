<?php
/**
 * Renders factsheet meta box field HTML (primary and secondary).
 *
 * Used by the factsheet edit screen; receives meta config and calls
 * display_*_meta_field per field. Permission checks use WSUWP_Factsheet_Access.
 *
 * @package WSUWP_Graduate_School
 */

class WSUWP_Factsheet_Fields_Display {

		/**
	 * Outputs the HTML associated with the primary and secondary meta boxes.
	 *
	 * @since 0.7.0
	 *
	 * @param $meta
	 * @param $data
	 * @param $key
	 */
	public static function output_meta_box_html( $meta, $data, $key ) {
		if ( isset( $meta['pre_html'] ) ) {
			echo $meta['pre_html']; // @codingStandardsIgnoreLine (HTML is static in code)
		}
		?>
		<div class="factsheet-primary-input factsheet-<?php echo esc_attr( $meta['type'] ); ?>">
		<?php

		if ( isset( $meta['meta_field_callback'] ) && is_callable( $meta['meta_field_callback'] ) ) {
			call_user_func( $meta['meta_field_callback'], $meta, $key, $data );
		}

		echo '</div>'; // End factsheet-primary-input

		if ( isset( $meta['post_html'] ) ) {
			echo $meta['post_html']; // @codingStandardsIgnoreLine (HTML is static in code)
		}
	}

    

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_string_meta_field( $meta, $key, $data ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<?php

		// Check if field is restricted and user is a restricted contributor
		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_restricted_contributor( wp_get_current_user()->ID, get_the_ID() ) ) {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}

		?>
		<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $data[ $key ][0] ); ?>" <?php echo $disabled; // @codingStandardsIgnoreLine (HTML is static in code) ?> />
		<?php
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as an integer.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_int_meta_field( $meta, $key, $data ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<?php

		// Check if field is restricted and user is a restricted contributor
		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_restricted_contributor( wp_get_current_user()->ID, get_the_ID() ) ) {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}

		?>
		<input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo absint( $data[ $key ][0] ); ?>" <?php echo $disabled; // @codingStandardsIgnoreLine (HTML is static in code) ?> />
		<?php
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as boolean.
	 *
	 * @since 1.3.0
	 * @since 1.4.0 Added support for restricted fields that are disabled for contributors.
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_bool_meta_field( $meta, $key, $data ) {
		// Check if field is restricted and user is a restricted contributor
		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_restricted_contributor( wp_get_current_user()->ID, get_the_ID() ) ) {
			$disabled = 'disabled';
		} else {
			$disabled = '';
		}

		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<select name="<?php echo esc_attr( $key ); ?>" <?php echo $disabled; // @codingStandardsIgnoreLine ?>>
			<option value="0" <?php selected( 0, absint( $data[ $key ][0] ) ); ?>>No</option>
			<option value="1" <?php selected( 1, absint( $data[ $key ][0] ) ); ?>>Yes</option>
		</select>
		<?php
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_textarea_meta_field( $meta, $key, $data ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $meta['description'] ); ?>:</label>
		<?php

		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_eam_user( wp_get_current_user()->ID, get_the_ID() ) ) {
			echo '<div id="' . esc_attr( $key ) . '" class="field-content">' . wp_kses_post( apply_filters( 'the_content', $data[ $key ][0] ) ) . '</div>';
			return;
		}

		$wp_editor_settings = array(
			'textarea_rows' => 10,
			'media_buttons' => false,
			'teeny' => true,
		);

		wp_editor( $data[ $key ][0], esc_attr( $key ), $wp_editor_settings );
	}

		/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_deadlines_prog_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'semester' => 'None',
			'deadline' => '',
			'international' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Program Deadlines:</strong></span>
			<?php

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][semester]">
						<option value="None" <?php selected( 'None', $field_datum['semester'] ); ?>>Not selected</option>
						<option value="Fall" <?php selected( 'Fall', $field_datum['semester'] ); ?>>Fall</option>
						<option value="Spring" <?php selected( 'Spring', $field_datum['semester'] ); ?>>Spring</option>
						<option value="Summer" <?php selected( 'Summer', $field_datum['semester'] ); ?>>Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][deadline]" value="<?php echo esc_attr( $field_datum['deadline'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][international]" value="<?php echo esc_attr( $field_datum['international'] ); ?>" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[0][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][international]" value="" />
				</span>
				<?php
			}

			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-deadlines_prog-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][international]" value="" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_deadlines_prog_form_count" id="factsheet_deadlines_prog_form_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_deadlines_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'semester' => 'None',
			'deadline' => '',
			'international' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Priority Deadlines:</strong></span>
			<?php

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[0][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][international]" value="" />
				</span>
				<?php
			}

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][semester]">
						<option value="None" <?php selected( 'None', $field_datum['semester'] ); ?>>Not selected</option>
						<option value="Fall" <?php selected( 'Fall', $field_datum['semester'] ); ?>>Fall</option>
						<option value="Spring" <?php selected( 'Spring', $field_datum['semester'] ); ?>>Spring</option>
						<option value="Summer" <?php selected( 'Summer', $field_datum['semester'] ); ?>>Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][deadline]" value="<?php echo esc_attr( $field_datum['deadline'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][international]" value="<?php echo esc_attr( $field_datum['international'] ); ?>" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}



			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-deadline-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<select name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][semester]">
						<option value="None">Not selected</option>
						<option value="Fall">Fall</option>
						<option value="Spring">Spring</option>
						<option value="Summer">Summer</option>
					</select>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][deadline]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][international]" value="" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_deadline_form_count" id="factsheet_deadline_form_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}


		/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_contacts_meta_field( $meta, $key, $data ) {
			$field_data = maybe_unserialize( $data[ $key ][0] );
	
			if ( empty( $field_data ) ) {
				$field_data = array();
			}
	
			$default_field_data = array(
				'name' => '',
				'email' => '',
			);
			$field_count = 0;
	
			?>
			<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
				<span class="factsheet-label"><strong>Contact Information: </strong></span>
				<?php
	
				foreach ( $field_data as $field_datum ) {
					$field_datum = wp_parse_args( $field_datum, $default_field_data );
	
					?>
					<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
						<label for="Name">Name: </label>
						<input type="text" id="Name" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][name]" value="<?php echo esc_attr( $field_datum['name'] ); ?>" />
						<label for="Email">Email: </label>
						<input type="text" id ="Email" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][email]" value="<?php echo esc_attr( $field_datum['email'] ); ?>" />
						<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
					</span>
					<?php
					$field_count++;
				}
	
				// If no fields have been added, provide an empty field by default.
				if ( 0 === count( $field_data ) ) {
					?>
					<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<label for="Name">Name: </label>
	
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][name]" value="" /><br>
					<label for="Email">Email: </label>

						<input type="text" name="<?php echo esc_attr( $key ); ?>[0][email]" value="" />
					</span>
					<?php
				}
	
				// @codingStandardsIgnoreStart
				?>
				<script type="text/template" id="factsheet-gscontacts-template">
					<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<label for="Name">Name: </label>
						<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][name]" value="" />
						<label for="Email">Email: </label>
						<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][email]" value="" />
						<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
					</span>
				</script>
				<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
				<input type="hidden" name="factsheet_gscontacts_count" id="factsheet_gscontacts_count" value="<?php echo esc_attr( $field_count ); ?>" />
			</div>
			<?php
			// @codingStandardsIgnoreEnd
		}
	


	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_requirements_gre_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'required' => 'None',
			'test' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Additional Program Requirements:</strong></span>
			<?php

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
				<label for="test">Test Name (GRE, GMAT, etc.): </label>

					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][test]" value="" />
					<label for="required">Required?:  </label>

					<select id="required" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][required]">
						<option value="None" <?php selected( 'None', $default_field_data['required'] ); ?>>Not selected</option>
						<option value="Optional" <?php selected( 'Optional', $default_field_data['required'] ); ?>>Optional</option>
						<option value="Yes" <?php selected( 'Yes', $default_field_data['required'] ); ?>>Yes</option>
						<option value="No" <?php selected( 'No', $default_field_data['required'] ); ?>>No</option>
					</select>
				</span>
				<?php
				}

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
				<label for="test">Test Name (GRE, GMAT, etc.): </label>
					<input type="text" id="test" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][test]" value="<?php echo esc_attr( $field_datum['test'] ); ?>" />
					<label for="required">Required?:  </label>

					<select id="required" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][required]">
						<option value="None" <?php selected( 'None', $field_datum['required'] ); ?>>Not selected</option>
						<option value="Optional" <?php selected( 'Optional', $field_datum['required'] ); ?>>Optional</option>
						<option value="Yes" <?php selected( 'Yes', $field_datum['required'] ); ?>>Yes</option>
						<option value="No" <?php selected( 'No', $field_datum['required'] ); ?>>No</option>
					</select>
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}

			

			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-requirement-gre-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
				<label for="test">Test Name (GRE, GMAT, etc.): </label>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][test]" value="" />
				<label for="required">Required?:  </label>

					<select id="required" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][required]">
						<option value="None" <?php selected( 'None', $field_datum['required'] ); ?>>Not selected</option>
						<option value="Optional" <?php selected( 'Optional', $field_datum['required'] ); ?>>Optional</option>
						<option value="Yes" <?php selected( 'Yes', $field_datum['required'] ); ?>>Yes</option>
						<option value="No" <?php selected( 'No', $field_datum['required'] ); ?>>No</option>
					</select>
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_requirement_form_gre_count" id="factsheet_requirement_form_gre_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_requirements_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'score' => '',
			'test' => '',
			'description' => '',
		);
		$field_count = 0;

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Language Test Requirements:</strong></span>
			<?php

			foreach ( $field_data as $field_datum ) {
				$field_datum = wp_parse_args( $field_datum, $default_field_data );

				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][score]" value="<?php echo esc_attr( $field_datum['score'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][test]" value="<?php echo esc_attr( $field_datum['test'] ); ?>" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $field_count ); ?>][description]" value="<?php echo esc_attr( $field_datum['description'] ); ?>" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
				<?php
				$field_count++;
			}

			// If no fields have been added, provide an empty field by default.
			if ( 0 === count( $field_data ) ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][score]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][test]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[0][description]" value="" />
				</span>
				<?php
			}

			// @codingStandardsIgnoreStart
			?>
			<script type="text/template" id="factsheet-requirement-template">
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][score]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][test]" value="" />
					<input type="text" name="<?php echo esc_attr( $key ); ?>[<%= form_count %>][description]" value="" />
					<span class="remove-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">Remove</span>
				</span>
			</script>
			<input type="button" class="add-factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field button" value="Add" />
			<input type="hidden" name="factsheet_requirement_form_count" id="factsheet_requirement_form_count" value="<?php echo esc_attr( $field_count ); ?>" />
		</div>
		<?php
		// @codingStandardsIgnoreEnd
	}
	
	/**
	 * Outputs the meta field HTML used to capture meta data stored as strings.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $meta
	 * @param string $key
	 * @param array  $data
	 */
	public static function display_locations_meta_field( $meta, $key, $data ) {
		$field_data = maybe_unserialize( $data[ $key ][0] );

		if ( empty( $field_data ) ) {
			$field_data = array();
		}

		$default_field_data = array(
			'Pullman' => 'No',
			'Spokane' => 'No',
			'Tri-Cities' => 'No',
			'Vancouver' => 'No',
			'Everett' => 'No',
			'Global Campus (online)' => 'No',

		);
		$field_data = wp_parse_args( $field_data, $default_field_data );

		if ( isset( $meta['restricted'] ) && $meta['restricted'] && WSUWP_Factsheet_Access::user_is_eam_user( wp_get_current_user()->ID, get_the_ID() ) ) {
			$restricted = true;
		} else {
			$restricted = false;
		}

		?>
		<div class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-wrapper">
			<span class="factsheet-label"><strong>Locations:</strong></span>
			<?php

			foreach ( $field_data as $location => $location_status ) {
				?>
				<span class="factsheet-<?php echo esc_attr( $meta['type'] ); ?>-field">
					<label for="location-<?php echo esc_attr( sanitize_key( $location ) ); ?>"><?php echo esc_html( $location ); ?></label>
					<?php
					if ( $restricted ) {
						echo '<span id="location-' . esc_attr( sanitize_key( $location ) ) . '" class="field-value">' . esc_attr( $location_status ) . '</span>';
					} else {
						?>
						<select id="location-<?php echo esc_attr( sanitize_key( $location ) ); ?>"
							name="<?php echo esc_attr( $key ); ?>[<?php echo esc_attr( $location ); ?>]">
							<option value="No" <?php selected( 'No', $location_status ); ?>>No</option>
							<option value="Yes" <?php selected( 'Yes', $location_status ); ?>>Yes</option>
							<option value="By Exception" <?php selected( 'By Exception', $location_status ); ?>>By Exception</option>
						</select>
						<?php
					}
					?>


				</span>
				<?php
			}
			?>
		</div>
		<?php
	}
    
}