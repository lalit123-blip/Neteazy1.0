<?php

namespace WPFormsSurveys\Templates;

/**
 * NPS Survey Enhanced template.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.2.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class NPSSurveyEnhanced extends \WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'NPS Survey Enhanced Form', 'wpforms-surveys-polls' );
		$this->slug        = 'nps-survey-enhanced';
		$this->description = esc_html__( 'Measure customer loyalty and find out exactly what they are thinking with this enhanced Net Promoter Score survey template.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '8',
			'fields'   => array(
				1 => array(
					'id'       => '1',
					'type'     => 'net_promoter_score',
					'label'    => esc_html__( 'How likely are you to recommend us to a friend or colleague?', 'wpforms-surveys-polls' ),
					'required' => '1',
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
				4 => array(
					'id'                => '4',
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
				6 => array(
					'id'                => '6',
					'type'              => 'textarea',
					'label'             => esc_html__( 'What do you like most about us?', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => array(
						0 => array(
							0 => array(
								'field'    => '1',
								'operator' => '>',
								'value'    => '8',
							),
						),
					),
				),
				7 => array(
					'id'                => '7',
					'type'              => 'radio',
					'label'             => esc_html__( 'Would you be willing to provide us a testimonial?', 'wpforms-surveys-polls' ),
					'choices'           => array(
						1 => array(
							'label' => esc_html__( 'Yes', 'wpforms-surveys-polls' ),
							'value' => '',
						),
						2 => array(
							'label' => esc_html__( 'No', 'wpforms-surveys-polls' ),
							'value' => '',
						),
					),
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => array(
						0 => array(
							0 => array(
								'field'    => '1',
								'operator' => '>',
								'value'    => '8',
							),
						),
					),
				),
				5 => array(
					'id'                => '5',
					'type'              => 'email',
					'label'             => esc_html__( 'What is your email address?', 'wpforms-surveys-polls' ),
					'description'       => esc_html__( 'We\'ll use this to get in touch with you, so we can make things right - Thanks for giving us a chance 🙂', 'wpforms-surveys-polls' ),
					'size'              => 'medium',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => array(
						0 => array(
							0 => array(
								'field'    => '1',
								'operator' => '<',
								'value'    => '9',
							),
						),
						1 => array(
							0 => array(
								'field'    => '7',
								'operator' => '==',
								'value'    => '1',
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
