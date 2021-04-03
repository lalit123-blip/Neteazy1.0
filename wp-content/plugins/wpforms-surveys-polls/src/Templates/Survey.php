<?php

namespace WPFormsSurveys\Templates;

/**
 * Poll form template.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
class Survey extends \WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'Survey Form', 'wpforms-surveys-polls' );
		$this->slug        = 'survey';
		$this->description = esc_html__( 'Collect customer feedback, then generate survey reports to determine satisfaction and spot trends.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '8',
			'fields'   => array(
				'1' => array(
					'id'       => '1',
					'type'     => 'name',
					'label'    => esc_html__( 'Name', 'wpforms-surveys-polls' ),
					'format'   => 'first-last',
					'required' => '1',
					'size'     => 'medium',
				),
				'2' => array(
					'id'       => '2',
					'type'     => 'email',
					'label'    => esc_html__( 'Email', 'wpforms-surveys-polls' ),
					'format'   => 'first-last',
					'required' => '1',
					'size'     => 'medium',
				),
				'3' => array(
					'id'          => '3',
					'type'        => 'rating',
					'label'       => esc_html__( 'Your Experience', 'wpforms-surveys-polls' ),
					'description' => esc_html__( 'Overall, how would you rate your experience with us?', 'wpforms-surveys-polls' ),
					'scale'       => '5',
					'required'    => '1',
					'icon'        => 'star',
					'icon_size'   => 'medium',
					'icon_color'  => '#e27730',
				),
				'4' => array(
					'id'                => '4',
					'type'              => 'textarea',
					'label'             => esc_html__( 'How can we improve?', 'wpforms-surveys-polls' ),
					'description'       => esc_html__( 'We\'re sorry you did not have a good experience. Please let us know how we can do better.', 'wpforms-surveys-polls' ),
					'size'              => 'small',
					'required'          => '1',
					'conditional_logic' => '1',
					'conditional_type'  => 'show',
					'conditionals'      => array(
						'0' => array(
							'0' => array(
								'field'    => '3',
								'operator' => '==',
								'value'    => '1',
							),
						),
						'1' => array(
							'0' => array(
								'field'    => '3',
								'operator' => '==',
								'value'    => '2',
							),
						),
					),
				),
				'5' => array(
					'id'       => '5',
					'type'     => 'likert_scale',
					'label'    => esc_html__( 'How satisfied are you with', 'wpforms-surveys-polls' ),
					'required' => '1',
					'size'     => 'large',
					'rows'     => array(
						'1' => esc_html__( 'Purchase', 'wpforms-surveys-polls' ),
						'2' => esc_html__( 'Service', 'wpforms-surveys-polls' ),
						'3' => esc_html__( 'Company Overall', 'wpforms-surveys-polls' ),
					),
					'columns'  => array(
						'1' => esc_html__( 'Very Unsatisfied', 'wpforms-surveys-polls' ),
						'2' => esc_html__( 'Unsatisfied', 'wpforms-surveys-polls' ),
						'3' => esc_html__( 'Neutral', 'wpforms-surveys-polls' ),
						'4' => esc_html__( 'Satisfied', 'wpforms-surveys-polls' ),
						'5' => esc_html__( 'Very Satisfied', 'wpforms-surveys-polls' ),
					),
				),
				'6' => array(
					'id'       => '6',
					'type'     => 'likert_scale',
					'label'    => esc_html__( 'How likely are you to', 'wpforms-surveys-polls' ),
					'required' => '1',
					'size'     => 'large',
					'rows'     => array(
						'1' => esc_html__( 'Buy from us again', 'wpforms-surveys-polls' ),
						'2' => esc_html__( 'Recommend our product to others', 'wpforms-surveys-polls' ),
						'3' => esc_html__( 'Recommend our company to others', 'wpforms-surveys-polls' ),
					),
					'columns'  => array(
						'1' => esc_html__( 'Very Unlikely', 'wpforms-surveys-polls' ),
						'2' => esc_html__( 'Unlikely', 'wpforms-surveys-polls' ),
						'3' => esc_html__( 'Neutral', 'wpforms-surveys-polls' ),
						'4' => esc_html__( 'Likely', 'wpforms-surveys-polls' ),
						'5' => esc_html__( 'Very Likely', 'wpforms-surveys-polls' ),
					),
				),
				'7' => array(
					'id'    => '7',
					'type'  => 'textarea',
					'label' => esc_html__( 'Additional comments or suggestions', 'wpforms-surveys-polls' ),
					'size'  => 'medium',
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
