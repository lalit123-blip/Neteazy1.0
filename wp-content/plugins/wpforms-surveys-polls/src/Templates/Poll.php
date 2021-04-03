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
class Poll extends \WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->name        = esc_html__( 'Poll Form', 'wpforms-surveys-polls' );
		$this->slug        = 'poll';
		$this->description = esc_html__( 'Ask visitors a question and display the results after they provide an answer.', 'wpforms-surveys-polls' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = array(
			'field_id' => '2',
			'fields'   => array(
				'1' => array(
					'id'       => '1',
					'type'     => 'radio',
					'label'    => esc_html__( 'What is your favorite color?', 'wpforms-surveys-polls' ),
					'required' => '1',
					'size'     => 'medium',
					'choices'  => array(
						'1' => array(
							'label' => esc_html__( 'Red', 'wpforms-surveys-polls' ),
							'value' => '',
						),
						'2' => array(
							'label' => esc_html__( 'Green', 'wpforms-surveys-polls' ),
							'value' => '',
						),
						'3' => array(
							'label' => esc_html__( 'Blue', 'wpforms-surveys-polls' ),
							'value' => '',
						),
						'4' => array(
							'label' => esc_html__( 'Orange', 'wpforms-surveys-polls' ),
							'value' => '',
						),
						'5' => array(
							'label' => esc_html__( 'Purple', 'wpforms-surveys-polls' ),
							'value' => '',
						),
						'6' => array(
							'label' => esc_html__( 'Other', 'wpforms-surveys-polls' ),
							'value' => '',
						),
					),
				),
			),
			'settings' => array(
				'submit_text'                 => esc_html__( 'Vote', 'wpforms-surveys-polls' ),
				'honeypot'                    => '1',
				'confirmation_message_scroll' => '1',
				'confirmation_message'        => esc_html__( 'Thanks for voting! Results are below.', 'wpforms-surveys-polls' ),
				'submit_text_processing'      => esc_html__( 'Sending...', 'wpforms-surveys-polls' ),
				'survey_enable'               => '1',
				'poll_enable'                 => '1',
			),
			'meta'     => array(
				'template' => $this->slug,
			),
		);
	}
}
