/* global wp, _, WPFormsAdmin, wpforms_admin, wpforms_surveys, wpforms_surveys_cache, Chart, Choices, randomColor, pdfMake, JSON */

;(function($) {

	'use strict';

	// Global settings access.
	var s;

	// Main Survey object.
	var WPFormsSurvey = {

		// Settings.
		settings : {
			fieldIDs:      JSON.parse( wpforms_surveys.field_ids ),
			fieldQueue:    '',
			fieldData:     {},
			charts:        {},
			exportSelects: {}
		},

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			// Settings shortcut.
			s = this.settings;

			// Default chart settings.
			Chart.defaults.global.defaultFontSize = 14;

			// Custom chart plugin to apply a white background to all
			// generated charts. This is necessary for generating JPG exports to
			// avoid black backgrounds.
			Chart.plugins.register({
				beforeDraw: function( chartInstance ) {
					var ctx = chartInstance.chart.ctx;
					ctx.fillStyle = 'white';
					ctx.fillRect( 0, 0, chartInstance.chart.width, chartInstance.chart.height );
				}
			});

			// Document ready.
			$( document ).ready( WPFormsSurvey.ready );

			// Element actions.
			WPFormsSurvey.buildUIActions();
		},

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			// If there are survey fields, fetch their data.
			if ( ! _.isEmpty( s.fieldIDs ) ) {

				s.fieldQueue = s.fieldIDs;

				// Use cache is available, otherwise create new report via AJAX.
				if ( ! _.isEmpty( wpforms_surveys_cache ) ) {
					s.fieldData = JSON.parse( wpforms_surveys_cache );
					WPFormsSurvey.generateReport();
				}  else {
					WPFormsSurvey.getFieldData();
				}
			}
		},

		/**
		 * Initilize export choices.
		 *
		 * @since 1.0.0
		 */
		initExportChoices: function() {

			$( '.survey-chart-export' ).each( function() {
				var $this = $( this ),
					id    = $this.data( 'id' );

				s.exportSelects['export_' + id ] = new Choices( $this[0], {
					searchEnabled : false,
					shouldSort : false
				} );
			});
		},

		/**
		 * Element binds and actions.
		 *
		 * @since 1.0.0
		 */
		buildUIActions: function() {

			// Toggle chart display types.
			$( document ).on( 'click', '#wpforms-survey-report .chart-toggle', function( event ) {

				event.preventDefault();

				var $this    = $( this ),
					type     = $this.data( 'type' ),
					current  = $this.hasClass( 'current' ),
					$btnWrap = $this.parent(),
					fieldID  = $btnWrap.data( 'field-id' );

				// If the user clicked the currently displayed chart, do nothing.
				if ( current ) {
					return;
				}

				// Toggle "current" button class.
				$btnWrap.find( '.current').removeClass( 'current' );
				$this.addClass( 'current' );

				// Destroy old charts.
				s.charts['chart_' + fieldID].destroy();
				s.charts['chart_hq_' + fieldID].destroy();

				// Generate new chart.
				WPFormsSurvey.generateChart( fieldID, type, s.fieldData['field_' + fieldID].chart.labels, s.fieldData['field_' + fieldID].chart.data );

				// Generate high quality chart for exports.
				WPFormsSurvey.generateChart( fieldID, type, s.fieldData['field_' + fieldID].chart.labels, s.fieldData['field_' + fieldID].chart.data, true );
			});

			// Toggle question displayed in the survey preview area.
			// Toggle chart display types.
			$( document ).on( 'change', '#wpforms-survey-preview-questions', function() {

				var $this   = $(this),
					fieldID = $this.val();

				// Reset field data and add new field to queue.
				s.fieldQueue = [];
				s.fieldData  = {};
				s.fieldQueue.push( fieldID );

				// Remove current field report.
				$( '#wpforms-survey-report' ).empty().prepend( wpforms_surveys.loader );

				// Generate new report.
				WPFormsSurvey.getFieldData();

				// AJAX request to save this field as the preferred survey
				// preview field.
				var data = {
					action:   'wpforms_surveys_set_preview_field',
					nonce:    wpforms_admin.nonce,
					field_id: fieldID,
					form_id:  wpforms_surveys.form_id
				};
				$.post( wpforms_admin.ajax_url, data );
			});

			// Export chart.
			$( document ).on( 'change', '.survey-chart-export', function() {

				var $this     = $(this),
					fieldID   = $this.data('id'),
					canvasImg;

				if ( 'jpg' === $this.val() ) {
					canvasImg = $(' #chart-' + fieldID + '-hq' )[0].toDataURL( 'image/jpeg', 1.0 );
					$( '#chart-' + fieldID + '-download' ).attr( 'href', canvasImg );
					// Triggering click on hidden elements with jQuery doesn't
					// appear to work so we use vanilla JS.
					document.getElementById( 'chart-' + fieldID + '-download' ).click();
				} else if ( 'pdf' === $this.val() ) {
					canvasImg = $(' #chart-' + fieldID + '-hq' )[0].toDataURL( 'image/jpeg', 1.0 );
					var docDefinition = {
						pageSize: 'LETTER',
						content: [
							{
								text: s.fieldData['field_' + fieldID].question,
								fontSize: 20,
								margin: [0, 0, 0, 35]
							},
							{
								image: canvasImg,
								width: 530
							}
						]
					};
					pdfMake.createPdf( docDefinition ).download( 'chart-field-' + fieldID + '.pdf' );
				} else if ( 'print' === $this.val() ) {
					window.open( wpforms_surveys.print + '&field_id=' + fieldID );
				}

				// Reset the select to show "Export";
				if ( $this.val() !== '1' ) {
					s.exportSelects['export_' + fieldID].setValueByChoice( '1' );
				}
			});

			// Open new window when viewing Survey print preview.
			$( document ).on( 'click', '.form-details-print-survey-report', function( event ) {

				event.preventDefault();

				window.open( $(this).attr( 'href' ) );
			});

			// Survey print preview - close window.
			$( document ).on( 'click', '#wpforms-survey-print-close', function( event ) {

				event.preventDefault();

				window.close();
			});

			// Survey print preview - trigger browser print.
			$( document ).on( 'click', '#wpforms-survey-print', function( event ) {

				event.preventDefault();

				window.print();
			});

			// Survey print preview - toggle question visiblity.
			$( document ).on( 'click', '#wpforms-survey-print-preview .question-toggle', function( event ) {

				event.preventDefault();

				var $this     = $(this),
					$question = $this.parent();

				$this.find( 'i' ).toggleClass( 'fa-chevron-down fa-chevron-right' );
				$question.toggleClass( 'no-print' ).find( '.chart-area, .table-wrap, .stats, .no-answers' ).toggle();
			});
		},

		/**
		 * Get the compiled field data information and calculations.
		 *
		 * @since 1.0.0
		 */
		getFieldData: function() {

			var fieldID = _.first( s.fieldQueue ),
				data    = {
					action:      'wpforms_surveys_field_data',
					nonce:       wpforms_admin.nonce,
					form_id:     wpforms_surveys.form_id,
					field_id:    fieldID,
					entry_count: wpforms_surveys.entry_count
				};

			// Trigger AJAX to fetch field data.
			$.post( wpforms_admin.ajax_url, data, function( res ) {

				if ( res.success ){

					WPFormsAdmin.debug( res.data );

					// Add data to fields array.
					s.fieldData['field_' + res.data.id] =  res.data;

					// Remove from the queue.
					s.fieldQueue = _.without( s.fieldQueue, fieldID );

					if ( _.isEmpty( s.fieldQueue ) ) {

						// Processing complete!
						WPFormsSurvey.cacheFieldData();

						// Proceed to generating the report with results.
						WPFormsSurvey.generateReport();

					} else {
						// Process next field.
						WPFormsSurvey.getFieldData();
					}
				}
			});
		},

		/**
		 * Makes an AJAX request with the final field data to build the cache.
		 *
		 * @since 1.0.0
		 */
		cacheFieldData: function() {

			var data = {
					action:      'wpforms_surveys_cache_fields',
					nonce:       wpforms_admin.nonce,
					field_data:  JSON.stringify( s.fieldData ),
					form_id:     wpforms_surveys.form_id,
					field_id:    wpforms_surveys.field_id,
					entry_count: wpforms_surveys.entry_count
				};

			// Trigger request.
			$.post( wpforms_admin.ajax_url, data );
		},

		/**
		 * Generate the report markup from the finalized field data.
		 *
		 * @since 1.0.0
		 */
		generateReport: function() {

			// Remove loading indicator.
			$( '#wpforms-survey-loading' ).remove();

			// Build markup from underscores template.
			var questionResults = wp.template( 'wpforms-question-results' );
			$( '#wpforms-survey-report' ).append( questionResults( s.fieldData ) );

			// Generate the chart for each question.
			$.each( s.fieldData, function( key, field ) {
				if ( ! _.isEmpty( field.chart.data ) ) {
					// Generate new chart.
					WPFormsSurvey.generateChart( field.id, field.chart.default, field.chart.labels, field.chart.data ); // jshint ignore:line

					// Generate high quality chart for exports.
					WPFormsSurvey.generateChart( field.id, field.chart.default, field.chart.labels, field.chart.data, true ); // jshint ignore:line
				}
			});

			// Sort tables and enable sorting.
			$( '.wpforms-table-sorting' ).stupidtable();

			// Initialize ChoicesJS for fancy selects.
			WPFormsSurvey.initExportChoices();

			if ( 'list' === wpforms_surveys.type ) {
				$( '#wpforms-survey-preview .btn-wrap' ).show();
			}
		},

		/**
		 * Generates a chart with Chart.JS.
		 *
		 * @since 1.0.0
		 */
		generateChart: function( fieldID, chartType, chartLabels, chartData, hq ) {

			var config = false,
				hq     = hq || false; // jshint ignore:line

			// Bar chart config.
			if ( 'bar' === chartType ) {
				config = {
					type: 'bar',
					data: {
						labels: chartLabels,
						datasets: [{
							label: 'Data',
							hoverBackgroundColor: '#2b8fd2',
							backgroundColor: 'rgba(215, 215, 215, 0.65)',
							data: chartData
						}]
					},
					options: {
						responsive: hq ? false : true,
						maintainAspectRatio: false,
						legend: {
							display: false
						},
						tooltips: {
							callbacks: {
								title: function( item, data ) {
									return data.labels[ item[0].index ];
								},
								label: function( item ) {
									return item.yLabel + '% (' + s.fieldData['field_' + fieldID ].chart.totals[item['index']] + ')';
								}
							}
						},
						scales: {
							xAxes: [{
								gridLines: {
									display: false
								},
								ticks: {
									fontSize: hq ? 20 : 14,
									callback: function( value ) {
										if ( typeof value === 'string' && value.length > 20 ) {
											return value.substring( 0,20 ) + '...';
										}
										return value;
									}
								}
							}],
							yAxes: [{
								ticks: {
									min: 0,
									max: 100,
									stepSize: 20,
									fontColor: '#999999',
									fontSize: hq ? 20 : 11,
									callback: function( value ) {
										return value + '%';
									}
								}
							}]
						}
					}
				};
			} else if ( 'bar-h' === chartType ) {
				config = {
					type: 'horizontalBar',
					data: {
						labels: chartLabels,
						datasets: [{
							label: 'Data',
							hoverBackgroundColor: '#2b8fd2',
							backgroundColor: 'rgba(215, 215, 215, 0.65)',
							data: chartData
						}]
					},
					options: {
						responsive: hq ? false : true,
						maintainAspectRatio: false,
						legend: {
							display: false
						},
						tooltips: {
							callbacks: {
								title: function( item, data ) {
									return data.labels[ item[0].index ];
								},
								label: function( item ) {
									return item.xLabel + '% (' + s.fieldData['field_' + fieldID ].chart.totals[item['index']] + ')';
								}
							}
						},
						scales: {
							xAxes: [{
								ticks: {
									min: 0,
									max: 100,
									fontColor: '#999999',
									fontSize: hq ? 20 : 11,
									callback: function( value ) {
										return value + '%';
									}
								}
							}],
							yAxes: [{
								gridLines: {
									display: false
								},
								ticks: {
									fontSize: hq ? 20 : 14,
									callback: function( value ) {
										if ( typeof value === 'string' && value.length > 20 ) {
											return value.substring( 0,20 ) + '...';
										}
										return value;
									}
								}
							}]
						}
					}
				};
			} else if ( 'pie' === chartType ) {
				config = {
					type: 'pie',
					data: {
						labels: chartLabels,
						datasets: [{
							hoverBackgroundColor: '#2b8fd2',
							data: chartData,
							backgroundColor: randomColor({ luminosity: 'light', count: chartData.length})
						}]
					},
					options: {
						responsive: hq ? false : true,
						maintainAspectRatio: false,
						legend: {
							position: 'right',
							labels : {
								fontSize: hq ? 20 : 14,
								padding: 15,
								generateLabels: function(chart) {
									var data    = chart.data;
									if (data.labels.length && data.datasets.length) {
										return data.labels.map(function(label, i) {
											var meta = chart.getDatasetMeta(0);
											var ds = data.datasets[0];
											var arc = meta.data[i];
											var custom = arc && arc.custom || {};
											var valueAtIndexOrDefault = Chart.helpers.valueAtIndexOrDefault;
											var arcOpts = chart.options.elements.arc;
											var fill = custom.backgroundColor ? custom.backgroundColor : valueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
											var stroke = custom.borderColor ? custom.borderColor : valueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
											var bw = custom.borderWidth ? custom.borderWidth : valueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);
											if ( label.length > 20 ) {
												label = label.substring( 0,20 ) + '...';
											}
											return {
												text: label,
												fillStyle: fill,
												strokeStyle: stroke,
												lineWidth: bw,
												hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
												index: i
											};
										});
									}
									return [];
								}
							}
						},
						tooltips: {
							callbacks: {
								label: function( item, data ) {
									return data['labels'][item['index']] + ' - ' + data['datasets'][0]['data'][item['index']] + '% (' + s.fieldData['field_' + fieldID ].chart.totals[item['index']] + ')';
								}
							}
						}
					}
				};
			} else if ( 'line' === chartType ) {
				config = {
					type: 'line',
					data: {
						labels: chartLabels,
						datasets: [{
							label: 'Data',
							borderColor: '#2b8fd2',
							backgroundColor: 'rgba(215, 215, 215, 0.65)',
							fill: false,
							data: chartData
						}]
					},
					options: {
						responsive: hq ? false : true,
						maintainAspectRatio: false,
						legend: {
							display: false
						},
						tooltips: {
							callbacks: {
								title: function( item, data ) {
									return data.labels[ item[0].index ];
								},
								label: function( item ) {
									return item.yLabel + '% (' + s.fieldData['field_' + fieldID ].chart.totals[item['index']] + ')';
								}
							}
						},
						scales: {
							xAxes: [{
								gridLines: {
									display: false
								},
								ticks: {
									fontSize: hq ? 20 : 14,
									callback: function( value ) {
										if ( typeof value === 'string' && value.length > 20 ) {
											return value.substring( 0,20 ) + '...';
										}
										return value;
									}
								}
							}],
							yAxes: [{
								ticks: {
									min: 0,
									max: 100,
									stepSize: 20,
									fontColor: '#999999',
									fontSize: hq ? 20 : 11,
									callback: function( value ) {
										return value + '%';
									}
								}
							}]
						}
					}
				};
			}

			if ( config ) {
				if ( hq ) {
					s.charts['chart_hq_' + fieldID] = new Chart( 'chart-' + fieldID + '-hq', config );
				} else {
					s.charts['chart_' + fieldID] = new Chart( 'chart-' + fieldID, config );
				}
			}
		}
	};

	WPFormsSurvey.init();

})( jQuery );
