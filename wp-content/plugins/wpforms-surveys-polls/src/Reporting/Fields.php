<?php

namespace WPFormsSurveys\Reporting;

/**
 * Field related survey reporting methods.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class Fields {

	/**
	 * Build and return the survey data for a given field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field       Field settings.
	 * @param int   $form_id     Form ID.
	 * @param int   $entry_count Total number of entries.
	 * @param array $form_data   Form data and settings.
	 *
	 * @return array
	 */
	public static function get_survey_field_data( $field, $form_id, $entry_count = 0, $form_data = array() ) {

		// Get answers for this field.
		$answers = wpforms()->entry_fields->get_fields(
			array(
				'number'   => -1,
				'form_id'  => $form_id,
				'field_id' => $field['id'],
				'order'    => 'ASC',
			)
		);

		// Setup and define basic default data.
		$data = array(
			'id'       => $field['id'],
			'type'     => $field['type'],
			'badge'    => self::get_field_badge_markup( $field['type'] ),
			'question' => $field['label'],
			'total'    => $entry_count,
			'answered' => count( $answers ),
			'skipped'  => ! empty( $answers ) && ! empty( $entry_count ) ? $entry_count - count( $answers ) : 0,
			'answers'  => array(),
			'chart'    => array(
				'supports' => array( 'bar', 'bar-h', 'pie', 'line' ),
				'default'  => 'bar',
				'labels'   => array(),
				'totals'   => array(),
				'data'     => array(),
			),
		);

		// If there are no answers, bail to prevent calculations.
		if ( empty( $answers ) ) {
			return $data;
		}

		// Get ready to process and calculate the data.
		switch ( $field['type'] ) {
			// Radio, Select, and Checkbox fields all share the same calculations.
			case 'radio':
			case 'select':
			case 'checkbox':
			case 'rating':
				// Loop through each answer for process.
				foreach ( $answers as $answer ) {

					$exists = false;

					if ( 'checkbox' === $field['type'] ) {

						// Checkbox values are slightly different because
						// there can be multiple checkbox values in a single
						// answer. This requires adjusted logic.
						$checks = explode( "\n", $answer->value );

						// Checkbox reporting does not support the pie graph
						// so remove it from the defaults graph types supported.
						$data['chart']['supports'] = array( 'bar', 'bar-h', 'line' );

						// Process values.
						foreach ( $checks as $check_key => $check ) {

							if ( ! empty( $data['answers'] ) ) {
								foreach ( $data['answers'] as $key => $item ) {
									if ( $check === $item['value'] ) {
										$data['answers'][ $key ]['count']++;
										$exists = true;
										break;
									}
								}
							}

							if ( ! $exists ) {
								$data['answers'][ $answer->id . '_' . $check_key ] = array(
									'value' => $check,
									'count' => 1,
								);
							}
						}
					} else {

						$answer_value = $answer->value;

						// For the rating field we adjust the values to make
						// them more human readable.
						if ( 'rating' === $field['type'] ) {
							switch ( $field['icon'] ) {
								case 'star':
									/* translators: %s - number of stars. */
									$answer_value = esc_html( sprintf( _n( '%s star', '%s stars', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;
								case 'heart':
									/* translators: %s - number of hearts. */
									$answer_value = esc_html( sprintf( _n( '%s heart', '%s hearts', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;
								case 'thumb':
									/* translators: %s - number of thumbs. */
									$answer_value = esc_html( sprintf( _n( '%s thumb', '%s thumbs', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;
								case 'smiley':
									/* translators: %s - number of smileys. */
									$answer_value = esc_html( sprintf( _n( '%s smiley', '%s smileys', $answer->value, 'wpforms-surveys-polls' ), number_format_i18n( $answer->value ) ) );
									break;
							}
							// Add the scale at the end for reference.
							$answer_value .= " ({$answer->value}/{$field['scale']})";
						}

						// Process values.
						if ( ! empty( $data['answers'] ) ) {
							foreach ( $data['answers'] as $key => $item ) {
								if ( $answer_value === $item['value'] ) {
									$data['answers'][ $key ]['count']++;
									$exists = true;
									break;
								}
							}
						}

						if ( ! $exists ) {
							$data['answers'][ $answer->id ] = array(
								'value' => $answer_value,
								'count' => 1,
							);
							if ( 'rating' === $field['type'] ) {
								$data['answers'][ $answer->id ]['value_raw'] = $answer->value;
							}
						}
					}
				}

				// Rating field specific actions.
				if ( 'rating' === $field['type'] && ! empty( $data['answers'] ) ) {
					// Reorder answers by numeric value.
					usort( $data['answers'], function( $a, $b ) {
						return $a['value_raw'] - $b['value_raw'];
					} );

					// Calculate average rating.
					$total = 0;
					foreach ( $answers as $answer ) {
						$total += absint( $answer->value );
					}
					$data['average'] = round( $total / count( $answers ), 1 );
				}

				// For Radio, Checkboxes, and Select fields, reorder the answers
				// to match the order they are displayed in the survey.
				if ( in_array( $field['type'], array( 'radio', 'select', 'checkbox' ), true ) && ! empty( $data['answers'] ) ) {

					$answers_originals = $data['answers'];
					$answers_ordered   = array();

					foreach ( $field['choices'] as $choice_key => $choice ) {
						foreach ( $answers_originals as $key => $answers_original ) {
							if ( sanitize_text_field( $choice['label'] ) === $answers_original['value'] ) {
								$answers_ordered[ $key ]              = $answers_original;
								$answers_ordered[ $key ]['choice_id'] = $choice_key;
								unset( $answers_originals[ $key ] );
								break;
							}
						}
					}

					// If there are any answers remaining that means they are
					// choices that were available at some point, but have been
					// removed and no longer exist. In this case we add them to
					// the end.
					if ( ! empty( $answers_originals ) ) {
						$answers_ordered = array_replace_recursive( $answers_ordered, $answers_originals );
					}

					$data['answers'] = $answers_ordered;
				}

				// Loop through each answer and compile/format values needed for Chart JS.
				if ( ! empty( $data['answers'] ) ) {
					foreach ( $data['answers'] as $key => $item ) {
						$percent                            = round( ( $item['count'] / count( $answers ) ) * 100 );
						$data['answers'][ $key ]['percent'] = $percent;
						$data['chart']['labels'][]          = $item['value'];
						$data['chart']['totals'][]          = $item['count'];
						$data['chart']['data'][]            = $percent;
					}
				}
				break;
			case 'text':
			case 'textarea':
				// Text input and text area results.
				// Text based fields don't support charts so we disable.
				$data['chart']['supports'] = array();
				$data['chart']['default']  = false;

				// Loop through each answer.
				foreach ( $answers as $answer ) {

					$data['answers'][ $answer->id ] = array(
						'value'     => $answer->value,
						'date'      => date_i18n( sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) ), strtotime( $answer->date ) + ( get_option( 'gmt_offset' ) * 3600 ) ),
						'date_unix' => strtotime( $answer->date ),
						'entry_id'  => absint( $answer->entry_id ),
					);
				}
				break;
			// Likert Scale results.
			case 'likert_scale':
				// Get the form data to have access to field rows and columns.
				if ( empty( $form_data ) ) {
					$form_data = wpforms()->form->get(
						$form_id,
						array(
							'content_only' => true,
						)
					);
				}

				// Likert fields don't support charts so we disable.
				$data['chart']['supports'] = array();
				$data['chart']['default']  = false;

				// Basic details for rendering the results table.
				$data['table']            = array();
				$data['table']['columns'] = array();
				$data['table']['rows']    = array();
				$data['table']['single']  = ! empty( $field['single_row'] ) ? true : false;
				$data['table']['width']   = $data['table']['single'] ? round( 100 / count( $form_data['fields'][ $field['id'] ]['columns'] ), 4 ) : round( 80 / count( $form_data['fields'][ $field['id'] ]['columns'] ), 4 );

				// Prefix the field rows and column keys to preserve the order
				// when looping in our javascript template.
				foreach ( $form_data['fields'][ $field['id'] ]['rows'] as $k => $v ) {
					$data['table']['rows'][ "r{$k}" ] = $v;
				}
				foreach ( $form_data['fields'][ $field['id'] ]['columns'] as $k => $v ) {
					$data['table']['columns'][ "c{$k}" ] = $v;
				}

				// Get the row and column IDs to use to verify data.
				$row_ids    = array_map( 'absint', array_keys( $form_data['fields'][ $field['id'] ]['rows'] ) );
				$column_ids = array_map( 'absint', array_keys( $form_data['fields'][ $field['id'] ]['columns'] ) );

				// Set all the initial counts to zero.
				$counts = array();
				foreach ( $row_ids as $r ) {
					foreach ( $column_ids as $c ) {
						$counts[ $r ][ $c ] = 0;
					}
				}

				// Loop through each answer to process counts.
				foreach ( $answers as $answer ) {

					// Fetch and decode the raw values (arrays).
					$vals = json_decode( $answer->value, true );
					$vals = $vals['value_raw'];

					foreach ( $vals as $row_key => $column_keys ) {

						// If this row key is not found, that means the admin
						// has likely removed that row from the field settings,
						// so we discard it.
						if ( ! in_array( absint( $row_key ), $row_ids, true ) ) {
							continue;
						}

						$column_keys = (array) $column_keys;

						foreach ( $column_keys as $column_key ) {

							// If this column key is not found, that means the
							// admin has likely removed that column from the
							// field settings, so we discard it.
							if ( ! in_array( absint( $column_key ), $column_ids, true ) ) {
								continue;
							}

							// Increment the count.
							$counts[ $row_key ][ $column_key ]++;
						}
					}
				}

				// Compile the final answer data to return.
				foreach ( $row_ids as $r ) {
					foreach ( $column_ids as $c ) {

						$answer_key = "r{$r}_c{$c}";
						$total      = ! empty( $counts[ $r ] ) ? array_sum( $counts[ $r ] ) : 0;
						$count      = $counts[ $r ][ $c ];

						$data['answers'][ $answer_key ] = array(
							'count'   => $count,
							'percent' => ! empty( $count ) && ! empty( $total ) ? round( ( $count / $total ) * 100 ) : 0,
							'highest' => ! empty( $count ) ? max( $counts[ $r ] ) === $count : false,
						);
					}
				}
				break;
			case 'net_promoter_score':
				// Define initial values.
				$data['nps'] = array(
					'detractors' => array(
						'count'   => 0,
						'percent' => 0,
					),
					'passives'   => array(
						'count'   => 0,
						'percent' => 0,
					),
					'promoters'  => array(
						'count'   => 0,
						'percent' => 0,
					),
					'score'      => 0,
				);

				for ( $i = 0; $i < 11; $i++ ) {
					$data['answers'][ $i ] = array(
						'value'   => $i,
						'count'   => 0,
						'percent' => 0,
					);
				}

				// Loop through each answer for process.
				foreach ( $answers as $answer ) {
					$data['answers'][ $answer->value ]['count']++;
				}

				// Loop through each answer.
				if ( ! empty( $data['answers'] ) ) {
					foreach ( $data['answers'] as $key => $item ) {
						$percent                            = round( ( $item['count'] / count( $answers ) ) * 100 );
						$data['answers'][ $key ]['percent'] = $percent;

						// Compile/format values needed for Chart JS.
						$data['chart']['labels'][] = $item['value'];
						$data['chart']['totals'][] = $item['count'];
						$data['chart']['data'][]   = $percent;

						// Assign NPS category.
						if ( $item['value'] >= 9 ) {
							$data['nps']['promoters']['count'] += $item['count'];
						} elseif ( $item['value'] < 9 && $item['value'] > 6 ) {
							$data['nps']['passives']['count'] += $item['count'];
						} else {
							$data['nps']['detractors']['count'] += $item['count'];
						}
					}
				}

				// Calculate NPS category percentages.
				foreach ( $data['nps'] as $key => $nps_category ) {
					if ( 'score' === $key ) {
						continue;
					}
					$data['nps'][ $key ]['percent'] = round( ( $nps_category['count'] / count( $answers ) ) * 100 );
				}

				// Calculate raw NPS score.
				$data['nps']['score'] = round( ( ( $data['nps']['promoters']['count'] - $data['nps']['detractors']['count'] ) / count( $answers ) ) * 100, 2 );
				break;
		}

		// Return final array of data!
		return $data;
	}

	/**
	 * Returns array of fields in a form that have survey reporting enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form_data Form data and settings.
	 * @param bool  $ids       Return field IDs when true, otherwise field arrays.
	 *
	 * @return bool|array
	 */
	public static function get_survey_fields( $form_data, $ids = false ) {

		if ( empty( $form_data['fields'] ) ) {
			return false;
		}

		$fields    = $form_data['fields'];
		$field_ids = array();

		if ( ! empty( $form_data['settings']['survey_enable'] ) ) {

			foreach ( $fields as $id => $field ) {
				if ( ! in_array( $field['type'], self::get_survey_field_types(), true ) ) {
					unset( $fields[ $id ] );
				} else {
					$field_ids[] = $id;
				}
			}
		} else {
			foreach ( $fields as $id => $field ) {
				if ( ! self::field_has_survey( $field ) ) {
					unset( $fields[ $id ] );
				} else {
					$field_ids[] = $id;
				}
			}
		}

		if ( $ids ) {
			return $field_ids;
		}

		return $fields;
	}

	/**
	 * Outputs HTML markup for a badge that indicates the field type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Field type slug.
	 *
	 * @return string
	 */
	public static function get_field_badge_markup( $type ) {

		$badge = array();

		switch ( $type ) {
			case 'text':
				$badge['name'] = esc_html__( 'Single Line Text', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-text-width';
				break;
			case 'textarea':
				$badge['name'] = esc_html__( 'Paragraph Text', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-paragraph';
				break;
			case 'select':
				$badge['name'] = esc_html__( 'Dropdown', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-caret-square-o-down';
				break;
			case 'radio':
				$badge['name'] = esc_html__( 'Multiple Choice', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-list-ul';
				break;
			case 'checkbox':
				$badge['name'] = esc_html__( 'Checkboxes', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-check-square-o';
				break;
			case 'rating':
				$badge['name'] = esc_html__( 'Rating', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-star';
				break;
			case 'likert_scale':
				$badge['name'] = esc_html__( 'Likert Scale', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-ellipsis-h';
				break;
			case 'net_promoter_score':
				$badge['name'] = esc_html__( 'Net Promoter Score', 'wpforms-surveys-polls' );
				$badge['icon'] = 'fa-tachometer';
				break;
		}

		$badge = apply_filters( 'wpforms_surveys_reporting_fields_get_field_badge_markup', $badge );

		return sprintf( '<span class="badge"><i class="fa %s" aria-hidden="true"></i> %s</span>',
			sanitize_html_class( $badge['icon'] ),
			esc_html( $badge['name'] )
		);
	}

	/**
	 * Check if the provided fields from a form contain survey.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Found fields.
	 *
	 * @return bool
	 */
	public static function fields_has_survey( $fields ) {

		if ( ! empty( $fields ) && is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( self::field_has_survey( $field ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if a specific field has survey reporting enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field     Field data and settings.
	 * @param array $form_data Form data and settings.
	 *
	 * @return bool
	 */
	public static function field_has_survey( $field, $form_data = array() ) {

		if ( isset( $field['survey'] ) && '1' === $field['survey'] ) {
			return true;
		} elseif ( ! empty( $form_data['settings']['survey_enable'] ) && in_array( $field['type'], self::get_survey_field_types(), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get an array of field types that support Surveys.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_survey_field_types() {

		return apply_filters(
			'wpforms_surveys_reporting_fields_get_survey_field_types',
			array( 'text', 'textarea', 'select', 'radio', 'checkbox', 'rating', 'likert_scale', 'net_promoter_score' )
		);
	}
}
