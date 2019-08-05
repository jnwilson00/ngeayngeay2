<?php
function monsterinsights_forms_output_after_script( $options ) {
	$track_user    = monsterinsights_track_user();
	$ua            = monsterinsights_get_ua_to_output();

	if ( $track_user && $ua ) {
		ob_start();
		echo PHP_EOL;
		?>
<!-- MonsterInsights Form Tracking -->
<script type="text/javascript" data-cfasync="false">
	function monsterinsights_forms_record_impression( event ) {
		var monsterinsights_forms = document.getElementsByTagName("form");
		var monsterinsights_forms_i;
		for (monsterinsights_forms_i = 0; monsterinsights_forms_i < monsterinsights_forms.length; monsterinsights_forms_i++ ) {
			var monsterinsights_form_id = monsterinsights_forms[monsterinsights_forms_i].getAttribute("id");
			var skip_conversion = false;
			/* Check to see if it's contact form 7 if the id isn't set */
			if ( ! monsterinsights_form_id ) {
				monsterinsights_form_id = monsterinsights_forms[monsterinsights_forms_i].parentElement.getAttribute("id");
				if ( monsterinsights_form_id && monsterinsights_form_id.lastIndexOf('wpcf7-f', 0 ) === 0  ) {
					/* If so, let's grab that and set it to be the form's ID*/
					var tokens = monsterinsights_form_id.split('-').slice(0,2);
					var result = tokens.join('-');
					monsterinsights_forms[monsterinsights_forms_i].setAttribute("id", result);/* Now we can do just what we did above */
					monsterinsights_form_id = monsterinsights_forms[monsterinsights_forms_i].getAttribute("id");
				} else {
					monsterinsights_form_id = false;
				}
			}

			// Check if it's Ninja Forms & id isn't set.
			if ( ! monsterinsights_form_id && monsterinsights_forms[monsterinsights_forms_i].parentElement.className.indexOf( 'nf-form-layout' ) >= 0 ) {
				monsterinsights_form_id = monsterinsights_forms[monsterinsights_forms_i].parentElement.parentElement.parentElement.getAttribute( 'id' );
				if ( monsterinsights_form_id && 0 === monsterinsights_form_id.lastIndexOf( 'nf-form-', 0 ) ) {
					/* If so, let's grab that and set it to be the form's ID*/
					tokens = monsterinsights_form_id.split( '-' ).slice( 0, 3 );
					result = tokens.join( '-' );
					monsterinsights_forms[monsterinsights_forms_i].setAttribute( 'id', result );
					/* Now we can do just what we did above */
					monsterinsights_form_id = monsterinsights_forms[monsterinsights_forms_i].getAttribute( 'id' );
					skip_conversion = true;
				}
			}

			if ( monsterinsights_form_id && monsterinsights_form_id !== 'commentform' && monsterinsights_form_id !== 'adminbar-search' ) {
				__gaTracker( 'send', {
					hitType        : 'event',
					eventCategory  : 'form',
					eventAction    : 'impression',
					eventLabel     : monsterinsights_form_id,
					eventValue     : 1,
					nonInteraction : 1
				} );

				/* If a WPForms Form, we can use custom tracking */
				if ( monsterinsights_form_id && 0 === monsterinsights_form_id.lastIndexOf( 'wpforms-form-', 0 ) ) {
					continue;
				}

				/* Formiddable Forms, use custom tracking */
				if ( monsterinsights_forms_has_class( monsterinsights_forms[monsterinsights_forms_i], 'frm-show-form' ) ) {
					continue;
				}

				/* If a Gravity Form, we can use custom tracking */
				if ( monsterinsights_form_id && 0 === monsterinsights_form_id.lastIndexOf( 'gform_', 0 ) ) {
					continue;
				}

				/* If Ninja forms, we use custom conversion tracking */
				if ( skip_conversion ) {
					continue;
				}

				var custom_conversion_mi_forms = <?php echo apply_filters( "monsterinsights_forms_custom_conversion", "false" );?>;
				if ( custom_conversion_mi_forms ) {
					continue;
				}

				var __gaFormsTrackerWindow    = window;
				if ( __gaFormsTrackerWindow.addEventListener ) {
					document.getElementById(monsterinsights_form_id).addEventListener( "submit", monsterinsights_forms_record_conversion, false );
				} else {
					if ( __gaFormsTrackerWindow.attachEvent ) {
						document.getElementById(monsterinsights_form_id).attachEvent( "onsubmit", monsterinsights_forms_record_conversion );
					}
				}
			} else {
				continue;
			}
		}
	}

	function monsterinsights_forms_has_class(element, className) {
	    return (' ' + element.className + ' ').indexOf(' ' + className+ ' ') > -1;
	}

	function monsterinsights_forms_record_conversion( event ) {
		var monsterinsights_form_conversion_id = event.target.id;
		var monsterinsights_form_action        = event.target.getAttribute("miforms-action");
		if ( monsterinsights_form_conversion_id && ! monsterinsights_form_action ) {
			document.getElementById(monsterinsights_form_conversion_id).setAttribute("miforms-action", "submitted");
			__gaTracker( 'send', {
				hitType        : 'event',
				eventCategory  : 'form',
				eventAction    : 'conversion',
				eventLabel     : monsterinsights_form_conversion_id,
				eventValue     : 1
			} );
		}
	}

	/* Attach the events to all clicks in the document after page and GA has loaded */
	function monsterinsights_forms_load() {
		if ( typeof(__gaTracker) !== 'undefined' && __gaTracker && __gaTracker.hasOwnProperty( "loaded" ) && __gaTracker.loaded == true ) {
			var __gaFormsTrackerWindow    = window;
			if ( __gaFormsTrackerWindow.addEventListener ) {
				__gaFormsTrackerWindow.addEventListener( "load", monsterinsights_forms_record_impression, false );
			} else {
				if ( __gaFormsTrackerWindow.attachEvent ) {
					__gaFormsTrackerWindow.attachEvent("onload", monsterinsights_forms_record_impression );
				}
			}
		} else {
			setTimeout(monsterinsights_forms_load, 200);
		}
	}
	/* Custom Ninja Forms impression tracking */
	if (window.jQuery) {
		jQuery(document).on( 'nfFormReady', function( e, layoutView ) {
			var label = layoutView.el;
			label = label.substring(1, label.length);
			label = label.split('-').slice(0,3).join('-');
			__gaTracker( 'send', {
				hitType        : 'event',
				eventCategory  : 'form',
				eventAction    : 'impression',
				eventLabel     : label,
				eventValue     : 1,
				nonInteraction : 1
			} );
		});
	}
	monsterinsights_forms_load();
</script>
<!-- End MonsterInsights Form Tracking -->
<?php
		echo PHP_EOL;
		echo ob_get_clean();
	}

}
add_action( 'wp_head', 'monsterinsights_forms_output_after_script', 15 );

// Custom tracking for WPForms
function monsterinsights_forms_custom_wpforms_save( $fields, $entry, $form_id, $form_data ) {
	// Skip tracking if not a trackable user.
	if ( version_compare( MONSTERINSIGHTS_VERSION, '7.4', '>=' ) ) {
		$do_not_track = ! monsterinsights_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}
	$atts = array(
		't'     => 'event',                         // Type of hit
		'ec'    => 'form',                          // Event Category
		'ea'    => 'conversion',                  	// Event Action
		'el'    => 'wpforms-form-' . $form_id, 	// Event Label (form ID)
		'ev'	=> 1								// Event Value
	);
	if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
		$atts['uid'] = get_current_user_id(); // UserID tracking
	}
	monsterinsights_mp_track_event_call( $atts );
}
add_action( 'wpforms_process_entry_save', 'monsterinsights_forms_custom_wpforms_save', 10, 4 );

// Custom tracking for Ninja Forms
function monsterinsights_forms_custom_ninja_forms_save( $data ) {
	// Skip tracking if not a trackable user.
	if ( version_compare( MONSTERINSIGHTS_VERSION, '7.4', '>=' ) ) {
		$do_not_track = ! monsterinsights_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}
	$atts = array(
		't'     => 'event',                         		// Type of hit
		'ec'    => 'form',                          		// Event Category
		'ea'    => 'conversion',                  			// Event Action
		'el'    => 'nf-form-' . $data['form_id'], // Event Label (form ID)
		'ev'	=> 1										// Event Value
	);
	monsterinsights_mp_track_event_call( $atts );
}
add_action( 'ninja_forms_after_submission', 'monsterinsights_forms_custom_ninja_forms_save' );

function monsterinsights_forms_custom_gravity_forms_save( $entry, $form ) {
	// Skip tracking if not a trackable user.
	if ( version_compare( MONSTERINSIGHTS_VERSION, '7.4', '>=' ) ) {
		$do_not_track = ! monsterinsights_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}
	$atts = array(
		't'     => 'event',                         		// Type of hit
		'ec'    => 'form',                          		// Event Category
		'ea'    => 'conversion',                  			// Event Action
		'el'    => 'gform_' . $form["id"],				 // Event Label (form ID)
		'ev'	=> 1										// Event Value
	);
	monsterinsights_mp_track_event_call( $atts );
}
add_action( 'gform_after_submission', 'monsterinsights_forms_custom_gravity_forms_save', 10, 2 );

function monsterinsights_forms_custom_formidable_forms_save( $entry_id, $form_id ){
	// Skip tracking if not a trackable user.
	if ( version_compare( MONSTERINSIGHTS_VERSION, '7.4', '>=' ) ) {
		$do_not_track = ! monsterinsights_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}
	$form = FrmForm::getOne( $form_id );
	$atts = array(
		't'     => 'event',                         		// Type of hit
		'ec'    => 'form',                          		// Event Category
		'ea'    => 'conversion',                  			// Event Action
		'el'    => 'form_' . $form->form_key,				// Event Label (form ID)
		'ev'	=> 1										// Event Value
	);
	monsterinsights_mp_track_event_call( $atts );
}
add_action ( 'frm_after_create_entry', 'monsterinsights_forms_custom_formidable_forms_save', 30, 2 );

/**
 * Add a default id to the Elementor forms for tracking.
 *
 * @param array $instance The current form instance.
 * @param \ElementorPro\Modules\Forms\Widgets\Form $form The form object.
 */
function monsterinsights_add_elementor_form_id( $instance, $form ) {
	// If the form has an ID set exit so it is used.
	if ( ! empty( $instance['form_id'] ) ) {
		return;
	}

	if ( method_exists( $form, 'add_render_attribute' ) && method_exists( $form, 'get_id' ) ) {

		$form_id = 'elementor_post_' . get_the_ID() . '_form_';
		if ( ! empty( $instance['form_name'] ) ) {
			$form_id .= sanitize_title( $instance['form_name'] );
		} else {
			$form_id .= $form->get_id();
		}

		$form->add_render_attribute( 'form', 'id', $form_id );
	}
}

add_action( 'elementor-pro/forms/pre_render', 'monsterinsights_add_elementor_form_id', 15, 2 );

/**
 * Add a unique id to the Enfold contact form element.
 *
 * @param array $form_args
 * @param int $post_id
 *
 * @return array
 */
function monsterinsights_enfold_add_unique_id( $form_args, $post_id ) {

	// If the form has a title, use that to make the id unique.
	$form_id = ! empty( $form_args['heading'] ) ? sanitize_title( strip_tags( $form_args['heading'] ) ) : '';

	// If no heading is set, attempt to use the avia form id.
	if ( empty( $form_id ) && class_exists('avia_form') ) {
		$form_id = avia_form::$form_id;
	}

	$form_args['action'] .= '" id="avia_contact_' . $post_id . '_' . $form_id;

	return $form_args;
}

add_filter( 'avia_contact_form_args', 'monsterinsights_enfold_add_unique_id' , 10, 2 );
