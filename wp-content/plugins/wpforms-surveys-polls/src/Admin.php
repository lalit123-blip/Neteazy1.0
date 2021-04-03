<?php

namespace WPFormsSurveys;

use WPFormsSurveys\Reporting;

/**
 * Various admin functionality.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class Admin {

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

		// Add Survey toggle setting to select core fields.
		add_action( 'wpforms_field_options_top_advanced-options', array( $this, 'field_survey_toggle' ), 10, 2 );

		// Add results link to forms overview table.
		add_filter( 'wpforms_overview_row_actions', array( $this, 'form_list_row_actions' ), 10, 2 );

		// Register form builder settings area.
		add_filter( 'wpforms_builder_settings_sections', array( $this, 'builder_settings_register' ), 20, 2 );

		// Form builder settings content.
		add_action( 'wpforms_form_settings_panel_content', array( $this, 'builder_settings_content' ), 20, 2 );

		// Field styles for Gutenberg.
		add_action( 'enqueue_block_editor_assets', array( $this, 'gutenberg_enqueues' ) );

		// Admin form builder enqueues.
		add_action( 'wpforms_builder_enqueues_before', array( $this, 'admin_builder_enqueues' ) );
	}

	/**
	 * Enqueues for the admin form builder.
	 *
	 * @since 1.3.3
	 */
	public function admin_builder_enqueues() {

		// Localize data.
		wp_localize_script(
			'wpforms-survey-builder',
			'wpforms_surveys_polls',
			array(
				'alert_disable_entries' => esc_html__( "You've just turned off storing entry information in WordPress. Surveys and Polls addon requires entries to be stored, otherwise, it won't be able to process the data and display you the results.", 'wpforms-surveys-polls' ),
				'alert_enable_entries'  => esc_html__( 'This feature can\'t currently be used because entry storage is disabled for this form. Please go to Settings > General and uncheck the "Disable storing entry information in WordPress" option. Then, try enabling this feature again.', 'wpforms-surveys-polls' ),
			)
		);

	}

	/**
	 * Add setting to core fields to allow enabling survey tracking/reporting.
	 *
	 * This setting gets added single line text, paragraph text, dropdown,
	 * multiple choice, and checkbox fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field Field settings.
	 * @param object $instance Field base class instance.
	 */
	public function field_survey_toggle( $field, $instance ) {

		// Limit to our specific field types.
		if ( ! in_array( $field['type'], Reporting\Fields::get_survey_field_types(), true ) ) {
			return;
		}

		$builder = \WPForms_Builder::instance();

		// Create checkbox setting.
		$instance->field_element(
			'row',
			$field,
			array(
				'slug'    => 'survey',
				'content' => $instance->field_element(
					'checkbox',
					$field,
					array(
						'slug'    => 'survey',
						'value'   => isset( $field['survey'] ) ? '1' : '0',
						'desc'    => esc_html__( 'Enable Survey Reporting', 'wpforms-surveys-polls' ),
						'tooltip' => esc_html__( 'Check this option to track user input and include in survey reporting.', 'wpforms-surveys-polls' ),
					),
					false
				),
				'class'   => ! empty( $builder->form_data['settings']['survey_enable'] ) ? 'wpforms-hidden' : '',
			)
		);
	}

	/**
	 * On the forms overview table add a link to go to the survey results page.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $actions Table row actions.
	 * @param object $form Form object.
	 *
	 * @return array
	 */
	public function form_list_row_actions( $actions, $form ) {

		if ( ! Reporting\Forms::form_has_survey( $form ) ) {
			return $actions;
		}

		// Action link to view survey results.
		$action = array(
			'survey' => sprintf(
				'<a href="%s" title="%s">%s</a>',
				add_query_arg(
					array(
						'page'    => 'wpforms-entries',
						'view'    => 'survey',
						'form_id' => $form->ID,
					),
					admin_url( 'admin.php' )
				),
				esc_attr__( 'View Survey Results', 'wpforms-surveys-polls' ),
				esc_html__( 'Survey Results', 'wpforms-surveys-polls' )
			),
		);

		return wpforms_array_insert( $actions, $action, 'entries' );
	}

	/**
	 * Surveys and Polls form builder register settings area.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections  Settings area sections.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	public function builder_settings_register( $sections, $form_data ) {

		$sections['surveys_polls'] = esc_html__( 'Surveys and Polls', 'wpforms-surveys-polls' );

		return $sections;
	}

	/**
	 * Surveys and Polls form builder settings content.
	 *
	 * @since 1.0.0
	 *
	 * @param object $instance Settings panel instance.
	 */
	public function builder_settings_content( $instance ) {

		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-surveys_polls">';

			echo '<div class="wpforms-panel-content-section-title">';
				esc_html_e( 'Surveys and Polls', 'wpforms-surveys-polls' );
				echo '<a href="https://wpforms.com/docs/how-to-install-and-use-the-surveys-and-polls-addon/" target="_blank" rel="noopener noreferrer"><i class="fa fa-question-circle wpforms-help-tooltip" title="' . esc_attr__( 'Click here for documentation on Surveys and Polls addon', 'wpforms-surveys-polls' ) . '"></i></a>';
			echo '</div>';

			$survey_note = sprintf(
				wp_kses(
					/* translators: %s - WPForms.com documentation page URL. */
					__( 'Survey Reporting for all supported fields will be turned on. For more details and advanced survey options visit our <a href="%s" target="_blank" rel="noopener noreferrer">Surveys documentation</a>.', 'wpforms-surveys-polls' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'https://wpforms.com/docs/how-to-install-and-use-the-surveys-and-polls-addon/#enable-survey'
			);

			wpforms_panel_field(
				'checkbox',
				'settings',
				'survey_enable',
				$instance->form_data,
				esc_html__( 'Enable Survey Reporting', 'wpforms-surveys-polls' ),
				array(
					'after' => '<p class="note">' . $survey_note . '</p>',
				)
			);

			$poll_note = sprintf(
				wp_kses(
					/* translators: %s - WPForms.com documentation page URL. */
					__( 'Poll results for all Checkbox, Multiple Choice, and Dropdown fields will automatically display to users after they submit the form. For more details and advanced poll options visit our <a href="%s" target="_blank" rel="noopener noreferrer">Polls documentation</a>.', 'wpforms-surveys-polls' ),
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'https://wpforms.com/docs/how-to-install-and-use-the-surveys-and-polls-addon/#enable-poll'
			);

			wpforms_panel_field(
				'checkbox',
				'settings',
				'poll_enable',
				$instance->form_data,
				esc_html__( 'Enable Poll Results', 'wpforms-surveys-polls' ),
				array(
					'after' => '<p class="note">' . $poll_note . '</p>',
				)
			);

		echo '</div>';
	}

	/**
	 * Load enqueues for the Gutenberg editor.
	 *
	 * @since 1.2.0
	 */
	public function gutenberg_enqueues() {

		$min = wpforms_get_min_suffix();

		// CSS.
		wp_enqueue_style(
			'wpforms-surveys-polls',
			wpforms_surveys_polls()->url . "assets/css/wpforms-surveys-polls{$min}.css",
			array(),
			WPFORMS_SURVEYS_POLLS_VERSION
		);
	}
}
