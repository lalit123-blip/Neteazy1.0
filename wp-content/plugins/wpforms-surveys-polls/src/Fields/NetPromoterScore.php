<?php

namespace WPFormsSurveys\Fields;

/**
 * Net Promoter Score field.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.1.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class NetPromoterScore extends \WPForms_Field {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1.0
	 */
	public function init() {

		// Define field type information.
		$this->name  = esc_html__( 'Net Promoter Score', 'wpforms-surveys-polls' );
		$this->type  = 'net_promoter_score';
		$this->icon  = 'fa-tachometer';
		$this->order = 410;
		$this->group = 'fancy';

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues_before', array( $this, 'admin_builder_enqueues' ) );

		// Form frontend display enqueues.
		add_action( 'wpforms_frontend_css', array( $this, 'frontend_enqueues' ) );

		// Defaults when adding the field in the form builder.
		add_filter( 'wpforms_field_new_default', array( $this, 'admin_builder_defaults' ) );

		// Define additional field properties.
		add_filter( 'wpforms_field_properties_net_promoter_score', array( $this, 'field_properties' ), 5, 3 );
	}

	/**
	 * Enqueues for the admin form builder.
	 *
	 * @since 1.1.0
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
	 * Enqueues for the frontend form display.
	 *
	 * @since 1.1.0
	 *
	 * @param array $forms Forms displayed on current page.
	 */
	public function frontend_enqueues( $forms ) {

		$min = wpforms_get_min_suffix();

		if (
			wpforms()->frontend->assets_global() ||
			true === wpforms_has_field_type( 'net_promoter_score', $forms, true )
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
	 * @since 1.1.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return array
	 */
	public function admin_builder_defaults( $field ) {

		if ( 'net_promoter_score' === $field['type'] ) {

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
	 * @since 1.1.0
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

		for ( $i = 0; $i < 11; $i++ ) {
			$properties['inputs'][ $i ] = array(
				'label'    => array(
					'text' => $i,
				),
				'attr'     => array(
					'name'  => "wpforms[fields][{$field_id}]",
					'value' => $i,
				),
				'class'    => 'modern' === $field['style'] ? array( 'wpforms-screen-reader-element', 'wpforms-net-promoter-score-option' ) : array( 'wpforms-net-promoter-score-option' ),
				'data'     => array(),
				'id'       => "wpforms-{$form_id}-field_{$field_id}_{$i}",
				'required' => ! empty( $field['required'] ) ? 'required' : '',
			);
		}

		return $properties;
	}

	/**
	 * @inheritdoc
	 */
	protected function get_field_populated_single_property_value( $raw_value, $input, $properties, $field ) {

		if ( ! is_string( $raw_value ) ) {
			return $properties;
		}

		$get_value = stripslashes( sanitize_text_field( $raw_value ) );

		if ( ! empty( $properties['inputs'][ $get_value ] ) ) {
			$properties['inputs'][ $get_value ]['attr']['checked'] = true;
		}

		return $properties;
	}

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.1.0
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
				'tooltip' => esc_html__( 'Select the style for the net promoter score.', 'wpforms-surveys-polls' ),
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
	 * @since 1.1.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$style = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';

		// Label.
		$this->field_preview_option( 'label', $field );
		?>

		<table cellspacing="0" cellpadding="0" class="<?php echo esc_attr( $style ); ?>">
			<thead>
				<tr>
					<th colspan="11">
						<span class="not-likely"><?php esc_html_e( 'Not at all Likely', 'wpforms-surveys-polls' ); ?></span>
						<span class="extremely-likely"><?php esc_html_e( 'Extremely Likely', 'wpforms-surveys-polls' ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
				for ( $i = 0; $i < 11; $i++ ) {
					?>
					<td>
						<input type="radio" disabled>
						<label><?php echo absint( $i ); ?></label>
					</td>
					<?php
				}
				?>
				</tr>
			</tbody>
		</table>
		<?php

		// Description.
		$this->field_preview_option( 'description', $field );
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$inputs = $field['properties']['inputs'];
		$style  = ! empty( $field['style'] ) ? sanitize_html_class( $field['style'] ) : 'modern';
		$size   = ! empty( $field['size'] ) ? sanitize_html_class( $field['size'] ) : 'large';
		?>

		<table cellspacing="0" cellpadding="0" class="wpforms-field-<?php echo esc_attr( $size ); ?> <?php echo esc_attr( $style ); ?>">
			<thead>
				<tr>
					<th colspan="11">
						<span class="not-likely"><?php esc_html_e( 'Not at all Likely', 'wpforms-surveys-polls' ); ?></span>
						<span class="extremely-likely"><?php esc_html_e( 'Extremely Likely', 'wpforms-surveys-polls' ); ?></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
				foreach ( $inputs as $key => $input ) {
					echo '<td>';
						printf(
							'<input type="radio" %s %s>',
							wpforms_html_attributes( $input['id'], $input['class'], $input['data'], $input['attr'] ),
							$input['required']
						); // WPCS: XSS ok.
						echo '<label for="' . esc_attr( sanitize_html_class( $input['id'] ) ) . '">';
							echo esc_html( sanitize_text_field( $input['label']['text'] ) );
						echo '</label>';
					echo '</td>';
				}
				?>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Validates field on form submit.
	 *
	 * @since 1.1.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

		$form_id = absint( $form_data['id'] );

		// Basic required check - If field is marked as required, check for entry data.
		if ( ! empty( $form_data['fields'][ $field_id ]['required'] ) && empty( $field_submit ) && '0' !== $field_submit ) {
			wpforms()->process->errors[ $form_id ][ $field_id ] = wpforms_get_required_label();
		}

		// Allowed answers are 0-10.
		if ( ! empty( $field_submit ) && ( absint( $field_submit ) > 10 || absint( $field_submit ) < 0 ) ) {
			wpforms()->process->errors[ $form_id ][ $field_id ] = esc_html__( 'Invalid score; must be 0-10.', 'wpforms-surveys-polls' );
		}
	}

	/**
	 * Formats field.
	 *
	 * @since 1.1.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param array $field_submit Submitted form field value.
	 * @param array $form_data    Form data and settings.
	 */
	public function format( $field_id, $field_submit, $form_data ) {

		// Define data.
		$value = '' !== $field_submit ? absint( $field_submit ) : '';
		$name  = ! empty( $form_data['fields'][ $field_id ]['label'] ) ? $form_data['fields'][ $field_id ]['label'] : '';

		// Set final field details.
		wpforms()->process->fields[ $field_id ] = array(
			'name'  => sanitize_text_field( $name ),
			'value' => sanitize_text_field( $value ),
			'id'    => absint( $field_id ),
			'type'  => $this->type,
		);
	}
}
