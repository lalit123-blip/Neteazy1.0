<?php

namespace WPFormsSurveys\Templates;

/**
 * NPS Survey Simple template.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.2.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class NPSSurveySimple extends \WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'NPS Survey Simple Form', 'wpforms-surveys-polls' );
		$this->slug        = 'nps-survey-simple';
		$this->description = esc_html__( 'Find out if your clients or customers would recommend you to someone else with this basic Net Promoter Score survey template.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '4',
			'fields'   => array(
				1 => array(
					'id'       => '1',
					'type'     => 'net_promoter_score',
					'label'    => esc_html__( 'How likely are you to recommend us to a friend or colleague?', 'wpforms-surveys-polls' ),
					'required' => '1',
					'survey'   => '1',
					'style'    => 'modern',
					'size'     => 'large',
				),
				2 => array(
					'id'                => '2',
					'type'              => 'textarea',
					'label'             => esc_html__( 'How did we disappoint you and what can we do to make things right?', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => array(
						0 => array(
							0 => array(
								'field'    => '1',
								'operator' => '<',
								'value'    => '7',
							),
						),
					),
				),
				3 => array(
					'id'                => '3',
					'type'              => 'textarea',
					'label'             => esc_html__( 'What could we do to improve?', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => array(
						0 => array(
							0 => array(
								'field'    => '1',
								'operator' => '>',
								'value'    => '6',
							),
							1 => array(
								'field'    => '1',
								'operator' => '<',
								'value'    => '9',
							),
						),
					),
				),
			),
			'settings' => array(
				'submit_text'                 => esc_html__( 'Submit', 'wpforms-surveys-polls' ),
				'honeypot'                    => '1',
				'confirmation_message_scroll' => '1',
				'submit_text_processing'      => esc_html__( 'Sending...', 'wpforms-surveys-polls' ),
				'survey_enable'               => '1',
			),
			'meta'     => array(
				'template' => $this->slug,
			),
		);
	}
}
