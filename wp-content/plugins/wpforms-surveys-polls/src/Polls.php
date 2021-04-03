<?php

namespace WPFormsSurveys;

use WPFormsSurveys\Reporting\Fields;

/**
 * Poll functionality.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class Polls {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Register CSS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueues' ) );

		// Poll results shortcode.
		add_shortcode( 'wpforms_poll', array( $this, 'shortcode' ) );

		// Enable WordPress shortcodes in the confirmation message setting.
		add_filter( 'wpforms_frontend_confirmation_message', array( $this, 'confirmation_message' ), 20, 2 );
	}

	/**
	 * Register Poll CSS.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		// CSS.
		wp_register_style(
			'wpforms-polls',
			wpforms_surveys_polls()->url . "assets/css/wpforms-polls{$min}.css",
			array(),
			WPFORMS_SURVEYS_POLLS_VERSION
		);
	}

	/**
	 * Polls shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function shortcode( $atts ) {

		$atts = shortcode_atts( array(
			'label'    => false,
			'form_id'  => false,
			'field_id' => false,
			'counts'   => false,
		), $atts, 'wpforms_poll' );

		if ( empty( $atts['form_id'] ) || empty( $atts['field_id'] ) ) {
			return '';
		}

		ob_start();

		$this->display_results( $atts );

		return ob_get_clean();
	}

	/**
	 * Displays poll results for a specific field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Results arguments.
	 */
	public function display_results( $args ) {

		$form_id  = absint( $args['form_id'] );
		$field_id = absint( $args['field_id'] );

		// Get form data so we can access the field settings.
		$form_data = wpforms()->form->get(
			$form_id,
			array(
				'content_only' => true,
			)
		);

		// Confirm it's a valid form and field.
		if ( empty( $form_data ) || empty( $form_data['fields'][ $field_id ]['type'] ) ) {
			return;
		}

		// Supported poll fields types are select, radio, and checkbox.
		if ( ! in_array( $form_data['fields'][ $field_id ]['type'], array( 'select', 'radio', 'checkbox' ), true ) ) {
			return;
		}

		// Fetch the results.
		$results = Fields::get_survey_field_data( $form_data['fields'][ $field_id ], $form_id );

		// Field choices.
		$choices = $form_data['fields'][ $field_id ]['choices'];

		// Bail if there are no answers or choices, which should never happen.
		if ( empty( $results['answers'] ) || empty( $choices ) ) {
			return;
		}

		// Build the results output display.
		echo '<div class="wpforms-poll-results" id="wpforms-poll-results-' . absint( $form_id ) . '">';

			if ( $args['label'] && 'false' !== $args['label'] ) {
				echo '<div class="wpforms-poll-label">';
					echo esc_html( sanitize_text_field( $results['question'] ) );
				echo '</div>';
			}

			foreach ( $choices as $choice_key => $choice ) {

				$count   = 0;
				$percent = 0;

				// Find the choice in the answers.
				foreach ( $results['answers'] as $answer_key => $answer ) {
					if ( $answer['choice_id'] === $choice_key ) {
						$count   = $results['answers'][ $answer_key ]['count'];
						$percent = $results['answers'][ $answer_key ]['percent'];
					}
				}

				echo '<div class="wpforms-poll-answer" id="wpforms-poll-answer-' . absint( $choice_key ) . '">';

					echo '<div class="wpforms-poll-answer-label-wrap">';

						echo '<div class="wpforms-poll-answer-percent">';
							echo '<span>' . absint( $percent ) . '%</span>';
							if ( $args['counts'] && 'false' !== $args['counts'] ) {
								echo ' <div class="wpforms-poll-answer-count">';
									printf(
										/* translators: %d - votes count. */
										_n( '(%d vote)', '(%d votes)', absint( $count ), 'wpforms-surveys-polls' ),
										number_format_i18n( absint( $count ) )
									);
								echo '</div>';
							}
						echo '</div>';

						echo '<div class="wpforms-poll-answer-label">';
							echo esc_html( sanitize_text_field( $choice['label'] ) );
						echo '</div>';

					echo '</div>';

					echo '<div class="wpforms-poll-answer-bar-wrap">';
						echo '<div class="wpforms-poll-answer-bar" style="width:' . absint( $percent ) . '%;"></div>';
					echo '</div>';

				echo '</div>';
			}

			if ( $args['counts'] ) {
				echo '<div class="wpforms-poll-total">';
					printf(
						/* translators: %d - total votes. */
						esc_html__( 'Total Votes: %d', 'wpforms-surveys-polls' ),
						absint( $results['answered'] )
					);
				echo '</div>';
			}

		echo '</div>';

		// Load our poll styling.
		wp_enqueue_style( 'wpforms-polls' );
	}

	/**
	 * First, enable shortcodes in the confirmation message form setting if it contains
	 * a Poll shortcode.
	 *
	 * Second, if Poll results are enabled in the form settings, detect and
	 * automatically append the results to the confirmation message.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message   Confirmation message.
	 * @param array  $form_data Form data and settings.
	 *
	 * @return string
	 */
	public function confirmation_message( $message, $form_data ) {

		// Check if automatic poll reporting is enabled.
		if ( ! empty( $form_data['settings']['poll_enable'] ) ) {

			$args = apply_filters( 'wpforms_poll_results_confirmation_defaults', array(
				'label'  => true,
				'counts' => true,
			) );

			foreach ( $form_data['fields'] as $field ) {
				if ( in_array( $field['type'], array( 'select', 'radio', 'checkbox' ), true ) ) {

					ob_start();

					$this->display_results( array(
						'label'    => $args['label'],
						'form_id'  => $form_data['id'],
						'field_id' => $field['id'],
						'counts'   => $args['counts'],
					) );

					$message .= ob_get_clean();
				}
			}
		} elseif ( has_shortcode( $message, 'wpforms_poll' ) ) {

			// Check for shortcode in confirmation message.
			return do_shortcode( $message );
		}

		return $message;
	}
}
