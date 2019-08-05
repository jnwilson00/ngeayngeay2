<?php
if ( ! defined( 'AMP__DIR__' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
require_once ( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

/** Form Tracking
  *
  * AMP form tracking for MonsterInsights mostly works just like it does on normal pages.
  *
  * There are however a couple, unavoidable differences:
  *
  * 1. AMP does not support non-interaction Google Analytics events.
  *			This means we cannot set the form impression event to be non-interaction.
  * 		This will affect bounce rate, but we think this is a fair tradeoff vs not having
  * 		AMP trackin for the AMP pages.
  *
  * 2. AMP does not support the ability to modify the DOM after the page is rendered. And since
  *			Google Analytics events can only be tied to specific actions (visible, clicks, etc)
  * 		we aren't able to modify the DOM to prevent the event for form conversions on generic
  * 		forms from firing, leading them to be possibly multiple-counted on AMP if you are using
  * 		a form plugin that we don't have server-side enhanced conversion tracking for. If this
  * 		is an issue for your site, consider using a form plugin that we do support server-side
  *			conversion tracking events for (like WPForms).
  *
  * 3. NinjaForms requires Javascript rendering for their form output. Therefore they do not support
  * 		AMP forms natively. Some third party extensions exist but none appear to be a de-facto
  *			leading solution. For now, we've implemented the form tracking we do for them in the
  *			non-AMP world for them in case they add native support someday.
  **/

class MonsterInsights_AMP_Form_Parser extends AMP_Base_Sanitizer {
	/**
	 * The actual sanitization function
	 */
	public function sanitize() {
		$body = $this->get_body_node();
		$this->parse_nodes_recursive( $body );
	}

	/**
	 * Passes through the DOM and removes stuff that shouldn't be there.
	 *
	 * @param DOMNode $node
	 */
	private function parse_nodes_recursive( $node ) {
		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}
		if ( $node->nodeName === 'form' ) {
			$node_name = $node->nodeName;
			$this->parse_form( $node );
		}
		foreach ( $node->childNodes as $child_node ) {
			$this->parse_nodes_recursive( $child_node );
		}
	}

	/**
	 * Parse forms
	 *
	 * @param DOMNode $node
	 * @param object $attribute
	 */
	private function parse_form( $node ) {
		/**
		  * Objectives (priority order):
		  * 1. Determine if the form can be tracked
		  * 2. Determine if the form is wpforms, and if so custom track
		  * 3. Determine if the form is ninja forms, and if so custom track
		  * 4. Determine if the form is contact form 7, and if so custom track
		  * 5. Determine if the form is gravity forms, and if so custom track
		  * 6. If the form is not eligible for custom tracking, but can be tracked, then generic track it
		  *
		  * Note, custom tracking in this reference does not necessarily mean special tracking. Some form plugins like
		  * contact form 7 we have to do special tracking for because they don't use a parseable ID in their <form> element so we have to
		  * search to find the form ID (the div parent of the <form> element in cf7's case), and insert it for them.
		**/

		// Let's determine if the form is parseable
		$can_track = $this->form_can_be_tracked( $node );
		if ( ! $can_track ) {
			return;
		}

		$tracked = $this->track_wpforms( $node );
		if ( $tracked ) {
		  	return;
		}

		$tracked = $this->track_ninja_forms( $node );
		if ( $tracked ) {
			return;
		}

		$tracked = $this->track_contact_form_7( $node );
		if ( $tracked ) {
			return;
		}

		$tracked = $this->track_gravity_forms( $node );
		if ( $tracked ) {
			return;
		}

		$tracked = $this->track_generic( $node );
		if ( $tracked ) {
			return;
		}
	}

	private function form_can_be_tracked( $node ) {
		$id = ! empty( $node->getAttribute('id') ) ? esc_attr( $node->getAttribute('id') ) : '';

		// Don't track comment forms or adminbar-search
		if ( ! empty( $id ) && ( $id === 'commentform' || $id === 'adminbar-search' ) ) {
			return false;
		}

		if ( empty( $id ) ) {
			// if there's no ID, see if this a contact form 7 or Ninja Forms form
			$parent       = $node->parentNode;
			$parent_id    = ! empty( $parent->getAttribute('id') )    ? esc_attr( $parent->getAttribute('id') )    : '';
			$parent_class = ! empty( $parent->getAttribute('class') ) ? esc_attr( $parent->getAttribute('class') ) : '';

			// Check to see if it's CF7
			if ( ! empty( $parent_id ) && strpos( $parent_id, 'wpcf7-f' ) !== false ) {
				return $this->find_amp_state_node( $node ) ? true : false;
			}

			// Check to see if its Ninja Formss
			if ( ! empty( $parent_class ) && strpos( $parent_class, 'nf-form-layout' ) !== false ) {
				return $this->find_amp_state_node( $node ) ? true : false;
			}

			// No ID and not CF7 or Ninja, so we can't track this.
			return false;
		}
		return $this->find_amp_state_node( $node ) ? true : false;
	}

	private function track_wpforms( $node ) {
		// WPForms is easy to work with since we custom track conversions server side
		// and they have a custom AMP integration that has the formid in an attribute
		$form_id = ! empty( $node->getAttribute( 'data-formid' ) ) ? esc_attr( $node->getAttribute('data-formid') ) : '';
		if ( empty( $form_id ) ) {
			return false;
		}

		$class = ! empty( $node->getAttribute( 'class' ) ) ? esc_attr( $node->getAttribute('class') ) : '';
		if ( ! empty( $class ) ) {
			$class = $class . ' ';
		}

		$node = $this->find_amp_state_node( $node );
		$node->setAttribute( 'class', trim( $class . 'monsterinsights-form' ) );
		$node->setAttribute( 'data-vars-category', 'form' );
		$node->setAttribute( 'data-vars-action', 'impression' );
		$node->setAttribute( 'data-vars-label', 'wpforms-form-' . $form_id );
		return true;
	}

	private function track_gravity_forms( $node ) {
		// Gravity Forms is easy to work with since we custom track conversions server side
		// and it's easy to parse the ID of the form from the <form> element's ID attribute
		$form_id = ! empty( $node->getAttribute( 'id' ) ) ? esc_attr( $node->getAttribute('id') ) : '';
		if ( empty( $form_id ) || strpos( $form_id, 'gform_' ) === false ) {
			return false;
		}

		$class = ! empty( $node->getAttribute( 'class' ) ) ? esc_attr( $node->getAttribute('class') ) : '';
		if ( ! empty( $class ) ) {
			$class = $class . ' ';
		}

		$node->setAttribute( 'id', $form_id );
		$node->setAttribute( 'class', trim( $class . 'monsterinsights-form' ) );
		$node->setAttribute( 'data-vars-category', 'form' );
		$node->setAttribute( 'data-vars-action', 'impression' );
		$node->setAttribute( 'data-vars-label', $form_id );
		return true;
	}

	private function track_ninja_forms( $node ) {
		// Ninja Forms is tricky because they need conversion tracking AND use a non-standard form ID location. Sigh.
		$parent       = $node->parentNode;
		$parent_class = ! empty( $parent->getAttribute('class') ) ? esc_attr( $parent->getAttribute('class') ) : '';
		if ( ! empty( $parent_class ) && strpos( $parent_class, 'nf-form-layout' ) !== false ) {
			return false;
		}

		$form = $node->parentNode->parentNode->parentNode;
		$form_id = ! empty( $form->getAttribute( 'id' ) ) ? esc_attr( $form->getAttribute('id') ) : '';
		if ( empty( $form_id ) || strpos( $form_id, 'nf-form-' ) === false ) {
			return false;
		}

		$form_id = explode( "-", $form_id );
		if ( empty( $form_id ) || count( $form_id ) <= 3 ) {
			return false;
		}


		$form_id = $form_id[0] . '-' . $form_id[1] . '-' . $form_id[2];
		$class = ! empty( $node->getAttribute( 'class' ) ) ? esc_attr( $node->getAttribute('class') ) : '';
		if ( ! empty( $class ) ) {
			$class = $class . ' ';
		}

		$node->setAttribute( 'id', $form_id );
		$node->setAttribute( 'class', trim( $class . 'monsterinsights-form monsterinsights-track-form-conversions' ) );
		$node->setAttribute( 'data-vars-category', 'form' );
		$node->setAttribute( 'data-vars-action', 'impression' );
		$node->setAttribute( 'data-vars-label', $form_id );
		return true;
	}

	private function track_contact_form_7( $node ) {
		// Contact Form 7 is tricky because they need conversion tracking AND use a non-standard form ID location. Sigh.
		$parent       = $node->parentNode;
		$parent_id    = ! empty( $parent->getAttribute('id') ) ? esc_attr( $parent->getAttribute('id') ) : '';

		// Check to see if it's CF7
		if ( ! empty( $parent_id ) && strpos( $parent_id, 'wpcf7-f' ) !== false ) {
			return false;
		}

		$form_id = explode( "-", $parent_id );

		if ( empty( $form_id ) || count( $form_id ) <= 2 ) {
			return false;
		}

		$form_id = $form_id[0] . '-' . $form_id[1];
		$class = ! empty( $node->getAttribute( 'class' ) ) ? esc_attr( $node->getAttribute('class') ) : '';
		if ( ! empty( $class ) ) {
			$class = $class . ' ';
		}

		$node->setAttribute( 'class', trim( $class . 'monsterinsights-form monsterinsights-track-form-conversions' ) );
		$node->setAttribute( 'data-vars-category', 'form' );
		$node->setAttribute( 'data-vars-action', 'impression' );
		$node->setAttribute( 'data-vars-label', $form_id );
		return true;
	}

	private function track_generic( $node ) {
		// This is what we use to track all non-custom forms
		$form_id = ! empty( $node->getAttribute('id') ) ? esc_attr( $node->getAttribute('id') ) : '';
		$class   = ! empty( $node->getAttribute( 'class' ) ) ? esc_attr( $node->getAttribute('class') ) : '';
		if ( ! empty( $class ) ) {
			$class = $class . ' ';
		}

		$node->setAttribute( 'class', trim( $class . 'monsterinsights-form monsterinsights-track-form-conversions' ) );
		$node->setAttribute( 'data-vars-category', 'form' );
		$node->setAttribute( 'data-vars-action', 'impression' );
		$node->setAttribute( 'data-vars-label', $form_id );
		return true;
	}

	private function find_amp_state_node( $node ) {
		foreach ( $node->childNodes as $pp ) {
            if ( $pp->nodeName == 'amp-state' ) {
            	return $pp;
            }
        }
        return false;
	}
}
