<?php

namespace WPFormsSurveys\Reporting;

/**
 * Form related survey reporting methods.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class Forms {

	/**
	 * Check if a specific form contains fields with survey reporting.
	 *
	 * @since 1.0.0
	 *
	 * @param object|array|int $form Form object, form data array, or form ID.
	 *
	 * @return bool
	 */
	public static function form_has_survey( $form ) {

		if ( is_object( $form ) && ! empty( $form->post_content ) ) {
			// Check if this is form object.
			$form = wpforms_decode( $form->post_content );
		} elseif ( is_numeric( $form ) ) {
			// Check if this is a form ID.
			$form = wpforms()->form->get(
				$form,
				array(
					'content_only' => true,
				)
			);
		}

		// Check if the form has survey reporting enabled globally and contains
		// a field that supports surveys.
		if ( is_array( $form ) && ! empty( $form['settings']['survey_enable'] ) && ! empty( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( in_array( $field['type'], Fields::get_survey_field_types(), true ) ) {
					return true;
				}
			}
		}

		// Check that form is the $form_data array we need.
		if ( is_array( $form ) && ! empty( $form['fields'] ) ) {
			return Fields::fields_has_survey( $form['fields'] );
		}

		return false;
	}
}
