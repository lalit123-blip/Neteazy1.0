/* global wpforms_builder, wp, wpforms_surveys_polls */

( function( $ ) {

	'use strict';

	// Global settings access.
	var s;

	// Main Survey admin builder object.
	var WPFormsSurveyBuilder = {

		// Settings.
		settings: {
			surveyFields: [ 'text', 'textarea', 'select', 'radio', 'checkbox', 'rating', 'likert_scale', 'net_promoter_score' ],
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			// Settings shortcut.
			s = this.settings;

			// Document ready.
			$( document ).ready( WPFormsSurveyBuilder.ready );

			// Element actions.
			WPFormsSurveyBuilder.buildUIActions();
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			// Make Likert row/column settings sortable.
			WPFormsSurveyBuilder.likertSortable();
		},

		/**
		 * Element binds and actions.
		 *
		 * @since 1.0.0
		 */
		buildUIActions: function() {

			// Clicking `Disable storing entry information in WordPress` checkbox in Builder > Settings
			// or `Enable Survey Reporting` or `Enable Poll Results` in Builder > Surveys and Polls.
			$( document ).on( 'click', '#wpforms-panel-field-settings-disable_entries, #wpforms-panel-field-settings-survey_enable, #wpforms-panel-field-settings-poll_enable', WPFormsSurveyBuilder.alertEnableEntries );

			// Toggle individual field survey reporting settings depending on
			// the state of the global form setting.
			$( document ).on( 'change', '#wpforms-panel-field-settings-survey_enable', function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wpforms-field-option-row-survey' ).addClass( 'wpforms-hidden' );
				} else {
					$( '.wpforms-field-option-row-survey' ).removeClass( 'wpforms-hidden' );
				}
			} );

			// When a new field is added that supports survey reporting, if
			// survey reporting is enabled globally, hide the individual field
			// setting.
			$( document ).on( 'wpformsFieldAdd', function( event, id, type ) {
				if ( $.inArray( type, s.surveyFields ) > -1 && $( '#wpforms-panel-field-settings-survey_enable' ).is( ':checked' ) ) {
					$( '#wpforms-field-option-row-' + id + '-survey' ).addClass( 'wpforms-hidden' );
				}
			} );

			// Make new Likert fields sortable.
			$( document ).on( 'wpformsFieldAdd', function( event, id, type ) {
				if ( 'likert_scale' === type ) {
					WPFormsSurveyBuilder.likertSortable( '#wpforms-field-option-' + id + ' .choices-list' );
				}
			} );

			// Likert field add new row or column.
			$( document ).on( 'click', '.wpforms-field-option-likert_scale .choices-list .add', function( event ) {
				WPFormsSurveyBuilder.likertChoiceAdd( event, $( this ) );
			} );

			// Likert field remove row or column.
			$( document ).on( 'click', '.wpforms-field-option-likert_scale .choices-list .remove', function( event ) {
				WPFormsSurveyBuilder.likertChoiceDelete( event, $( this ) );
			} );

			// Likert field update row/column labels.
			$( document ).on( 'focusout', '.wpforms-field-option-likert_scale .choices-list input', function() {
				var $list   = $( this ).closest( '.choices-list' ),
					fieldID = $list.data( 'field-id' );
				WPFormsSurveyBuilder.likertChoiceUpdate( fieldID );
			} );

			// Likert field toggle single row display.
			$( document ).on( 'change', '.wpforms-field-option-likert_scale .wpforms-field-option-row-single_row input', function() {
				$( this ).closest( '.wpforms-field-option-group-inner' ).find( '.wpforms-field-option-row-rows .choices-list' ).toggleClass( 'wpforms-hidden' );
				WPFormsSurveyBuilder.likertChoiceUpdate( $( this ).parent().data( 'field-id' ) );
			} );

			// Likert field toggle multiple responses (radio/checkbox).
			$( document ).on( 'change', '.wpforms-field-option-likert_scale .wpforms-field-option-row-multiple_responses input', function() {
				WPFormsSurveyBuilder.likertChoiceUpdate( $( this ).parent().data( 'field-id' ) );
			} );

			// Likert field style (theme).
			$( document ).on( 'change', '.wpforms-field-option-likert_scale .wpforms-field-option-row-style select', function() {
				var $this   = $( this ),
					value   = $this.val(),
					fieldID = $this.parent().data( 'field-id' );
				$( '#wpforms-field-' + fieldID ).find( 'table' ).removeClass( 'classic modern' ).addClass( value );
			} );

			// Net Promoter Score field style (theme).
			$( document ).on( 'change', '.wpforms-field-option-net_promoter_score .wpforms-field-option-row-style select', function() {
				var $this   = $( this ),
					value   = $this.val(),
					fieldID = $this.parent().data( 'field-id' );
				$( '#wpforms-field-' + fieldID ).find( 'table' ).removeClass( 'classic modern' ).addClass( value );
			} );
		},

		/**
		 * Display alert if Entries disabled.
		 *
		 * @since 1.5.3
		 */
		alertEnableEntries: function() {

			var $t              = $( this ),
				$disableEntries = $( '#wpforms-panel-field-settings-disable_entries' ),
				$surveyEnable   = $( '#wpforms-panel-field-settings-survey_enable' ),
				$pollEnable     = $( '#wpforms-panel-field-settings-poll_enable' ),
				alertText       = '';

			if ( ! $t.is( ':checked' ) ) {
				return;
			}

			if ( $t.is( $disableEntries ) ) {
				if ( ! ( $surveyEnable.is( ':checked' ) || $pollEnable.is( ':checked' ) ) ) {
					return;
				}
				alertText = wpforms_surveys_polls.alert_disable_entries;
			} else {
				if ( ! $disableEntries.is( ':checked' ) ) {
					return;
				}
				alertText = wpforms_surveys_polls.alert_enable_entries;
				$t.prop( 'checked', false );
			}

			$.alert( {
				title: wpforms_builder.heads_up,
				content: alertText,
				icon: 'fa fa-exclamation-circle',
				type: 'orange',
				buttons: {
					confirm: {
						text: wpforms_builder.ok,
						btnClass: 'btn-confirm',
						keys: [ 'enter' ],
					},
				},
			} );
		},

		/**
		 * Make the Likert field row and column options sortable.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} selector Selector.
		 */
		likertSortable: function( selector ) {

			selector = selector || '.wpforms-field-option-likert_scale .choices-list';

			$( selector ).sortable( {
				items:   'li',
				axis:    'y',
				delay:   100,
				opacity: 0.6,
				handle:  '.move',
				stop:    function( e, ui ) {
					var id = ui.item.parent().data( 'field-id' );
					WPFormsSurveyBuilder.likertChoiceUpdate( id );
				},
				update:  function() {},
			} );
		},

		/**
		 * Likert field add new row or column choice.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} event Event object.
		 * @param {object} el    DOM element.
		 */
		likertChoiceAdd: function( event, el ) {

			event.preventDefault();

			var $this      = $( el ),
				$parent    = $this.parent(),
				$list      = $this.closest( '.choices-list' ),
				fieldID    = $list.data( 'field-id' ),
				id         = $list.attr( 'data-next-id' ),
				choiceType = $list.data( 'choice-type' ),
				$newChoice = $parent.clone().insertAfter( $parent );

			$newChoice.attr( 'data-key', id );
			$newChoice.find( 'input' ).val( '' ).attr( 'name', 'fields[' + fieldID + '][' + choiceType + '][' + id + ']' );

			id++;
			$list.attr( 'data-next-id', id );

			WPFormsSurveyBuilder.likertChoiceUpdate( fieldID );
		},

		/**
		 * Likert field remove row or column choice.
		 *
		 * @since 1.0.0
		 *
		 * @param {object} event Event object.
		 * @param {object} el    DOM element.
		 */
		likertChoiceDelete: function( event, el ) {

			event.preventDefault();

			var $this = $( el ),
				$list = $this.closest( '.choices-list' ),
				total = $list.find( 'li' ).length;

			if ( 1 === total ) {
				$.alert( {
					title:   false,
					content: wpforms_builder.error_choice,
					icon:   'fa fa-info-circle',
					type:   'blue',
					buttons: {
						confirm: {
							text:     wpforms_builder.ok,
							btnClass: 'btn-confirm',
							keys:     [ 'enter' ],
						},
					},
				} );
			} else {
				$this.parent().remove();
				WPFormsSurveyBuilder.likertChoiceUpdate( $list.data( 'field-id' ) );
			}
		},

		/**
		 * Likert field re-render the table output in the preview area.
		 *
		 * @since 1.0.0
		 *
		 * @param {numeric} fieldID Field ID.
		 */
		likertChoiceUpdate: function( fieldID ) {

			var data = {
					rows:        {},
					columns:     {},
					colCount:    0,
					singleRow:   $( '#wpforms-field-option-' + fieldID + '-single_row' ).is( ':checked ' ),
					singleClass: $( '#wpforms-field-option-' + fieldID + '-single_row' ).is( ':checked ' ) ? 'single-row' : '',
					inputType:   $( '#wpforms-field-option-' + fieldID + '-multiple_responses' ).is( ':checked ' ) ? 'checkbox' : 'radio',
					style:       $( '#wpforms-field-option-' + fieldID + '-style' ).val(),
					width:       0,
				};

			// Get columns.
			$( '#wpforms-field-option-row-' + fieldID + '-columns .choices-list li' ).each( function() {
				var $this = $( this ),
					key   = $this.data( 'key' ),
					value = $this.find( 'input' ).val();

				data.columns['c' + key] = {
					key:   key,
					value: value,
				};

				data.colCount++;
			} );

			// Get rows.
			$( '#wpforms-field-option-row-' + fieldID + '-rows .choices-list li' ).each( function() {
				var $this = $( this ),
					key   = $this.data( 'key' ),
					value = $this.find( 'input' ).val();

				data.rows['r' + key] = {
					key:   key,
					value: value,
				};
			} );

			// Calculate width of columns.
			data.width = data.singleRow ? 100 / data.colCount : 80 / data.colCount;

			// Generate new table from template and replace current.
			var likertPreview = wp.template( 'wpforms-likert-scale-preview' );
			$( '#wpforms-field-' + fieldID ).find( 'table' ).replaceWith( likertPreview( data ) );
		},
	};

	WPFormsSurveyBuilder.init();

} )( jQuery );
