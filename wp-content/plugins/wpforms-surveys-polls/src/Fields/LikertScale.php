<?php

namespace WPFormsSurveys\Fields;

/**
 * Likert Scale field.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class LikertScale extends \WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define field type information.
		$this->name     = esc_html__( 'Likert Scale', 'wpforms-surveys-polls' );
		$this->type     = 'likert_scale';
		$this->icon     = 'fa-ellipsis-h';
		$this->order    = 400;
		$this->group    = 'fancy';
		$this->defaults = array(
			'rows'    => array(
				1 => esc_html__( 'Item #1', 'wpforms-surveys-polls' ),
				2 => esc_html__( 'Item #2', 'wpforms-surveys-polls' ),
				3 => esc_html__( 'Item #3', 'wpforms-surveys-polls' ),
			),
			'columns' => array(
				1 => esc_html__( 'Strongly Disagree', 'wpforms-surveys-polls' ),
				2 => esc_html__( 'Disagree', 'wpforms-surveys-polls' ),
				3 => esc_html__( 'Neutral', 'wpforms-surveys-polls' ),
				4 => esc_html__( 'Agree', 'wpforms-surveys-polls' ),
				5 => esc_html__( 'Strongly Agree', 'wpforms-surveys-polls' ),
			),
		);

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues_before', array( $this, 'admin_builder_enqueues' ) );

		// Template for form builder preview.
		add_action( 'wpforms_builder_print_footer_scripts', array( $this, 'admin_builder_template' ) );

		// Form frontend display enqueues.
		add_action( 'wpforms_frontend_css', array( $this, 'frontend_enqueues' ) );

		// Defaults when adding the field in the form builder.
		add_filter( 'wpforms_field_new_default', array( $this, 'admin_builder_defaults' ) );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_likert_scale', array( $this, 'field_properties' ), 5, 3 );

		// Customize the information saved in the entry_fields database.
		add_filter( 'wpforms_entry_save_fields', array( $this, 'save_field' ), 10, 3 );
	}

	/**
	 * Enqueues for the admin form builder.
	 *
	 * @since 1.0.0
	 */
	public function admin_builder_enqueues() {

		$min = wpforms_get_min_suffix();

		// JavaScript.
		wp_enqueue_script(
			'wpforms-survey-builder',
			wpforms_surveys_polls()->url . "assets/js/admin-survey-builder{$min}.js",
			array( 'jquery', 'wpforms-builder', 'wpforms-utils' ),
			WPFORMS_SURVEYS_POLLS_VERSION
		);

		// CSS.
		wp_enqueue_style(
			'wpforms-survey-builder',
			wpforms_surveys_polls()->url . "assets/css/admin-survey-builder{$min}.css",
			array(),
			WPFORMS_SURVEYS_POLLS_VERSION
		);
	}

	/**
	 * Template for form builder preview.
	 *
	 * @since 1.0.0
	 */
	public function admin_builder_template() {

		?>
		<script type="text/html" id="tmpl-wpforms-likert-scale-preview">
			<# var rowCount = 1; #>
			<table cellspacing="0" cellpadding="0" class="{{ data.style }} {{ data.singleClass }}">
				<thead>
					<tr>
					<# if ( ! data.singleRow ) { #>
						<th style="width:20%;"></th>
					<# } #>
					<# _.each( data.columns, function( columnData, key ) {  #>
						<th style="width:{{ data.width }}%;">{{ columnData.value }}</th>
					<# }) #>
					</tr>
				</thead>
				<tbody>
					<# _.each( data.rows, function( rowData, key ) {  #>
						<# if ( ! data.singleRow || ( data.singleRow && rowCount === 1 ) ) { #>
							<tr>
							<# if ( ! data.singleRow ) { #>
								<th>{{ rowData.value }}</th>
							<# } #>
							<# _.each( data.columns, function( columnData, key ) {  #>
								<td>
									<input type="{{ data.inputType }}" disabled>
									<label></label>
								</td>
							<# }) #>
							</tr>
						<# } #>
						<# rowCount++ #>
					<# }) #>
				</tbody>
			</table>
		</script>
		<?php
	}

	/**
	 * Enqueues for the frontend form display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $forms Forms displayed on current page.
	 */
	public function frontend_enqueues( $forms ) {

		$min = wpforms_get_min_suffix();

		if (
			wpforms()->frontend->assets_global() ||
			true === wpforms_has_field_type( 'likert_scale', $forms, true )
		) {
			// CSS.
			wp_enqueue_style(
				'wpforms-surveys-polls',
				wpforms_surveys_polls()->url . "assets/css/wpforms-surveys-polls{$min}.css",
				array(),
				WPFORMS_SURVEYS_POLLS_VERSION
			);
		}
	}

	/**
	 * New field default settings in the form builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return array
	 */
	public function admin_builder_defaults( $field ) {

		if ( 'likert_scale' === $field['type'] ) {

			// Enable survey tracking.
			$field['survey'] = '1';

			// Due to the contents, this field is best rendered as large.
			$field['size'] = 'large';
		}

		return $field;
	}

	/**
	 * Define additional field properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $properties Field properties.
	 * @param array $field      Field settings.
	 * @param array $form_data  Form data and settings.
	 *
	 * @return array
	 */
	public function field_properties( $properties, $field, $form_data ) {

		// Remove primary input since this is a custom field.
		unset( $properties['inputs']['primary'] );

		// Define data.
		$form_id  = absint( $form_data['id'] );
		$field_id = absint( $field['id'] );

		// Create the inputs.
		foreach ( (array) $field['columns'] as $column_key => $column ) {
			foreach ( $field['rows'] as $row_key => $row ) {
				$properties['inputs'][ "r{$row_key}_c{$column_key}" ] = array(
					'attr'     => array(
						'name'  => ! empty( $field['multiple_responses'] ) ? "wpforms[fields][{$field_id}][{$row_key}][]" : "wpforms[fields][{$field_id}][{$row_key}]",
						'value' => $column_key,
					),
					'block'    => array(),
					'class'    => 'modern' === $field['style'] ? array( 'wpforms-screen-reader-element', 'wpforms-likert-scale-option' ) : array( 'wpforms-likert-scale-option' ),
					'data'     => array(),
					'id'       => "wpforms-{$form_id}-field_{$field_id}_{$row_key}_{$column_key}",
					'required' => ! empty( $field['required'] ) ? 'required' : '',
					'sublabel' => array(
						'hidden' => 1,
						'value'  => sanitize_text_field( "{$row} {$column}" ),
					),
				);

				// Add input error class if needed.
				if ( ! empty( $properties['error']['value'][ "r{$row_key}" ] ) ) {
					$properties['inputs'][ "r{$row_key}_c{$column_key}" ]['class'][] = 'wpforms-error';
				}

				// Add input required class if needed.
				if ( ! empty( $field['required'] ) ) {
					$properties['inputs'][ "r{$row_key}_c{$column_key}" ]['class'][] = 'wpforms-field-required';
				}
			}
		}

		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	protected function get_field_populated_single_property_value( $raw_value, $input, $properties, $field ) {
		/*
		 * $input is different depending on the source of the population.
		 * Dynamic: 'r2_c4' or similar string.
		 * Fallback: number which is a row (starting from 1), and we need to get the value (column) from original submitted source.
		 */
		preg_match( '/^r(\d+)_c(\d+)$/i', $input, $matches );

		/*
		 * During fallback and multiple responses per row we get single row but several columns as a raw value.
		 * We need to process this situation differently, and check each of that selected row/column pairs.
		 */
		$inputs = array();

		if ( empty( $matches ) || ! is_array( $matches ) ) {
			// We are in Fallback mode.
			$get_value = ! empty( $orig_value[ $input ] );

			if ( empty( $field['multiple_responses'] ) ) {
				/*
				 * Single response per row.
				 * We have this structure ($input => column):
				 * Array (
				 *     [2] => 2
				 *     [3] => 3
				 * )
				 */
				if ( ! is_numeric( $raw_value ) || empty( $_POST['wpforms']['fields'][ $field['id'] ] ) ) { // phpcs:ignore
					return $properties;
				}

				$orig_value = $_POST['wpforms']['fields'][ $field['id'] ]; // phpcs:ignore

				$inputs[] = 'r' . (int) $input . '_c' . (int) $orig_value[ $input ];
			} else {
				/*
				 * Several responses per row.
				 * We have this structure ($input => {#}=>column):
				 * Array (
				 *     [2] => Array (
				 *                0 => 2
				 *            )
				 *	   [3] => Array (
				 *                0 => 1,
				 *                1 => 4
				 *            )
				 *	)
				 */
				if ( ! is_array( $raw_value ) ) {
					return $properties;
				}

				foreach ( $raw_value as $column ) {
					$inputs[] = 'r' . (int) $input . '_c' . (int) $column;
				}
			}
		} else {
			// We are in Dynamic mode.
			if ( ! is_string( $raw_value ) ) {
				return $properties;
			}

			$inputs[]  = $input;
			$get_value = (bool) $raw_value;
		}

		if (
			$get_value &&
			! empty( $inputs )
		) {
			foreach ( $inputs as $key ) {
				if ( isset( $properties['inputs'][ $key ] ) ) {
					$properties['inputs'][ $key ]['attr']['checked'] = true;
				}
			}
		}

		return $properties;
	}

	/**
	 * Customize the information stored in the entry_field database.
	 *
	 * We need to include both the "pretty" and raw values in the database. The
	 * pretty values allow the field values to be searched and the raw values
	 * are used for survey reporting calculations.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field settings.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  Entry ID.
	 *
	 * @return array
	 */
	public function save_field( $field, $form_data, $entry_id ) {

		if ( $this->type === $field['type'] && ! empty( $field['value'] ) ) {
			$field['value'] = wp_json_encode(
				array(
					'value'     => $field['value'],
					'value_raw' => $field['value_raw'],
				)
			);
		}

		return $field;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			array(
				'markup' => 'open',
			)
		);

		// Label.
		$this->field_option( 'label', $field );

		// Rows.
		$values = ! empty( $field['rows'] ) ? $field['rows'] : $this->defaults['rows'];
		$lbl    = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'rows',
				'value'   => esc_html__( 'Rows', 'wpforms-surveys-polls' ),
				'tooltip' => esc_html__( 'Add rows to the likert scale.', 'wpforms-surveys-polls' ),
			),
			false
		);
		$fld    = sprintf(
			'<ul data-next-id="%s" class="choices-list %s" data-field-id="%d" data-field-type="%s" data-choice-type="%s">',
			max( array_keys( $values ) ) + 1,
			! empty( $field['single_row'] ) ? 'wpforms-hidden' : '',
			$field['id'],
			$this->type,
			'rows'
		);
		foreach ( $values as $key => $value ) {
			$fld .= sprintf( '<li data-key="%d">', $key );
			$fld .= '<span class="move"><i class="fa fa-bars" aria-hidden="true"></i></span>';
			$fld .= sprintf( '<input type="text" name="fields[%s][rows][%s]" value="%s" class="label">', esc_attr( $field['id'] ), $key, esc_attr( $value ) );
			$fld .= '<a class="add" href="#" title="' . esc_attr__( 'Add likert scale row', 'wpforms-surveys-polls' ) . '"><i class="fa fa-plus-circle"></i></a>';
			$fld .= '<a class="remove" href="# title="' . esc_attr__( 'Remove likert scale row', 'wpforms-surveys-polls' ) . '"><i class="fa fa-minus-circle"></i></a>';
			$fld .= '</li>';
		}
		$fld .= '</ul>';
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'rows',
				'content' => $lbl . $fld,
			)
		);

		// Single rows.
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'single_row',
				'content' => $this->field_element(
					'checkbox',
					$field,
					array(
						'slug'    => 'single_row',
						'value'   => isset( $field['single_row'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Make this a single-row rating scale', 'wpforms-surveys-polls' ),
						'tooltip' => esc_html__( 'Check this option to make this a single-row rating scale and remove the row choices.', 'wpforms-surveys-polls' ),
					),
					false
				),
			)
		);

		// Multiple row responses.
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'multiple_responses',
				'content' => $this->field_element(
					'checkbox',
					$field,
					array(
						'slug'    => 'multiple_responses',
						'value'   => isset( $field['multiple_responses'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Allow multiple responses per row', 'wpforms-surveys-polls' ),
						'tooltip' => esc_html__( 'Check this option to allow multiple responses per row (uses checkboxes).', 'wpforms-surveys-polls' ),
					),
					false
				),
			)
		);

		// Columns.
		$values = ! empty( $field['columns'] ) ? $field['columns'] : $this->defaults['columns'];
		$lbl    = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'columns',
				'value'   => esc_html__( 'Columns', 'wpforms-surveys-polls' ),
				'tooltip' => esc_html__( 'Add columns to the likert scale.', 'wpforms-surveys-polls' ),
			),
			false
		);
		$fld    = sprintf( '<ul data-next-id="%s" class="choices-list" data-field-id="%d" data-field-type="%s" data-choice-type="%s">', max( array_keys( $values ) ) + 1, $field['id'], $this->type, 'columns' );
		foreach ( $values as $key => $value ) {
			$fld .= sprintf( '<li data-key="%d">', $key );
			$fld .= '<span class="move"><i class="fa fa-bars" aria-hidden="true"></i></span>';
			$fld .= sprintf( '<input type="text" name="fields[%s][columns][%s]" value="%s">', $field['id'], $key, esc_attr( $value ) );
			$fld .= '<a class="add" href="#" title="' . esc_attr__( 'Add likert scale column', 'wpforms-surveys-polls' ) . '"><i class="fa fa-plus-circle"></i></a>';
			$fld .= '<a class="remove" href="# title="' . esc_attr__( 'Remove likert scale column', 'wpforms-surveys-polls' ) . '"><i class="fa fa-minus-circle"></i></a>';
			$fld .= '</li>';
		}
		$fld .= '</ul>';
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'columns',
				'content' => $lbl . $fld,
			)
		);

		// Description.
		$this->field_option( 'description', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			array(
				'markup' => 'close',
			)
		);

		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			array(
				'markup' => 'open',
			)
		);

		// Style (theme).
		$lbl = $this->field_element(
			'label',
			$field,
			array(
				'slug'    => 'style',
				'value'   => esc_html__( 'Style', 'wpforms-surveys-polls' ),
				'tooltip' => esc_html__( 'Select the style for the likert scale.', 'wpforms-surveys-polls' ),
			),
			false
		);
		$fld = $this->field_element(
			'select',
			$field,
			array(
				'slug'    => 'style',
				'value'   => ! empty( $field['style'] ) ? esc_attr( $field['style'] ) : 'modern',
				'options' => array(
					'modern'  => esc_html__( 'Modern', 'wpforms-surveys-polls' ),
					'classic' => esc_html__( 'Classic', 'wpforms-surveys-polls' ),
				),
			),
			false
		);
		$this->field_element(
			'row',
			$field,
			array(
				'slug'    => 'style',
				'content' => $lbl . $fld,
			)
		);

		// Size.
		$this->field_option( 'size', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			array(
				'markup' => 'close',
			)
		);
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$rows       = ! empty( $field['rows'] ) ? $field['rows'] : $this->defaults['rows'];
		$columns    = ! empty( $field['columns'] ) ? $field['columns'] : $this->defaults['columns'];
		$input_type = ! empty( $field['multiple_responses'] ) ? 'checkbox' : 'radio';
		$style      = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';
		$single     = ! empty( $field['single_row'] ) ? true : false;
		$width      = $single ? round( 100 / count( $columns ), 4 ) : round( 80 / count( $columns ), 4 );

		// Label.
		$this->field_preview_option( 'label', $field );
		?>

		<table cellspacing="0" cellpadding="0" class="<?php echo esc_attr( $style ); ?><?php echo $single ? ' single-row' : ''; ?>">
			<thead>
				<tr>
					<?php
					if ( ! $single ) {
						echo '<th style="width:20%;"></th>';
					}
					foreach ( $columns as $column_key => $column ) {
						printf(
							'<th style="width:%d%%;">%s</th>',
							esc_attr( $width ),
							esc_html( sanitize_text_field( $column ) )
						);
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $rows as $row_key => $row ) {
					echo '<tr>';
						if ( ! $single ) {
							echo '<th>' . esc_html( sanitize_text_field( $row ) ) . '</th>';
						}
						foreach ( $columns as $column_key => $column ) {
							echo '<td>';
								echo '<input type="' . esc_attr( $input_type ) . '" disabled>';
								echo '<label></label>';
							echo '</td>';
						}
					echo '</tr>';
					if ( $single ) {
						break;
					}
				}
				?>
			</tbody>
		</table>
		<?php

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$inputs     = $field['properties']['inputs'];
		$input_type = ! empty( $field['multiple_responses'] ) ? 'checkbox' : 'radio';
		$style      = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';
		$single     = ! empty( $field['single_row'] ) ? true : false;
		$size       = ! empty( $field['size'] ) ? sanitize_html_class( $field['size'] ) : 'large';
		$width      = 80;
		if ( ! empty( $field['columns'] ) ) {
			$width = $single ? round( 100 / count( $field['columns'] ), 4 ) : round( 80 / count( $field['columns'] ), 4 );
		}
		?>

		<table cellspacing="0" cellpadding="0" class="wpforms-field-<?php echo esc_attr( $size ); ?> <?php echo esc_attr( $style ); ?><?php echo $single ? ' single-row' : ''; ?>">
			<thead>
				<tr>
					<?php
					if ( ! $single ) {
						echo '<th style="width:20%;"></th>';
					}
					foreach ( $field['columns'] as $column_key => $column ) {
						printf(
							'<th style="width:%d%%;">%s</th>',
							esc_attr( $width ),
							esc_html( sanitize_text_field( $column ) )
						);
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( (array) $field['rows'] as $row_key => $row ) {
					echo '<tr>';
						if ( ! $single ) {
							echo '<th>';
								echo esc_html( sanitize_text_field( $row ) );
								$this->field_display_error( "r{$row_key}", $field );
							echo '</th>';
						}
						foreach ( $field['columns'] as $column_key => $column ) {
							$input = $inputs[ "r{$row_key}_c{$column_key}" ];
							echo '<td>';
								printf(
									'<input type="%s" %s %s>',
									esc_attr( $input_type ),
									wpforms_html_attributes( $input['id'], $input['class'], $input['data'], $input['attr'] ),
									$input['required']
								); // WPCS: XSS ok.
								echo '<label for="' . esc_attr( sanitize_html_class( $input['id'] ) ) . '">';
									echo ! empty( $input['sublabel']['hidden'] ) ? '<span class="wpforms-screen-reader-element">' : '<span>';
										echo esc_html( sanitize_text_field( $input['sublabel']['value'] ) );
									echo '</span>';
								echo '</label>';
							echo '</td>';
						}
					echo '</tr>';

					if ( $single ) {
						break;
					}
				}
				?>
			</tbody>
		</table>
		<?php
		// Display errors for single row fields after the table since we do
		// not display the row legend column.
		if ( $single ) {
			$row_keys = array_keys( $field['rows'] );
			$this->field_display_error( "r{$row_keys[0]}", $field );
		}
	}

	/**
	 * Validates field on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$form_id  = absint( $form_data['id'] );
		$required = wpforms_get_required_label();
		$row_keys = array_keys( $form_data['fields'][ $field_id ]['rows'] );
		$single   = ! empty( $form_data['fields'][ $field_id ]['single_row'] ) ? true : false;
		$x        = 1;

		// The validation logic for this field is only applicable if the field
		// is configured as required.
		if ( empty( $form_data['fields'][ $field_id ]['required'] ) ) {
			return;
		}

		foreach ( $row_keys as $row_key ) {
			if ( empty( $field_submit[ $row_key ] ) ) {
				wpforms()->process->errors[ $form_id ][ $field_id ][ "r{$row_key}" ] = $required;
			}
			if ( $single && 1 === $x ) {
				break;
			}
			$x++;
		}
	}

	/**
	 * Formats field.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		// Define data.
		$name       = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';
		$value      = '';
		$value_raw  = ! empty( $field_submit ) ? $this->sanitize_field_submit( (array) $field_submit ) : '';
		$rows       = $form_data['fields'][ $field_id ]['rows'];
		$columns    = $form_data['fields'][ $field_id ]['columns'];
		$single     = ! empty( $form_data['fields'][ $field_id ]['single_row'] ) ? true : false;
		$show_empty = apply_filters( 'wpforms_likert_scale_show_empty', false );

		// Process submitted data.
		if ( ! empty( $value_raw ) ) {

			$x = 1;

			foreach ( $rows as $row_key => $row_label ) {

				$answers  = (array) $value_raw[ $row_key ];
				$selected = array();

				foreach ( $columns as $column_id => $column_label ) {
					if ( in_array( $column_id, $answers, true ) ) {
						$selected[] = sanitize_text_field( $column_label );
					}
				}

				if ( $x > 1 && ( ! empty( $selected ) || $show_empty ) ) {
					$value .= "\n";
				}

				if ( ! empty( $selected ) ) {
					if ( $single ) {
						$value .= implode( ', ', $selected );
					} else {
						$value .= sanitize_text_field( $row_label ) . ":\n" . implode( ', ', $selected );
					}
				} else {
					if ( $show_empty ) {
						$value .= sanitize_text_field( $row_label ) . ":\n" . esc_html__( '(Empty)', 'wpforms-surveys-polls' );
					}
				}

				if ( $single ) {
					break;
				}
				$x++;
			}
		}

		// Set final field details.
		wpforms()->process->fields[ $field_id ] = array(
			'name'      => sanitize_text_field( $name ),
			'value'     => $value,
			'value_raw' => $value_raw,
			'id'        => absint( $field_id ),
			'type'      => $this->type,
		);
	}

	/**
	 * Sanitize the submitted data. All values and keys should integers.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field_submit Submitted data for Likert field.
	 *
	 * @return array
	 */
	public function sanitize_field_submit( $field_submit = array() ) {

		if ( ! is_array( $field_submit ) || ! count( $field_submit ) ) {
			return array();
		}

		foreach ( $field_submit as $key => $value ) {
			if ( is_int( $key ) ) {
				if ( is_array( $value ) ) {
					$field_submit[ $key ] = $this->sanitize_field_submit( $value );
				} else {
					$field_submit[ $key ] = absint( $value );
				}
			} else {
				unset( $field_submit[ $key ] );
			}
		}

		return $field_submit;
	}
}
