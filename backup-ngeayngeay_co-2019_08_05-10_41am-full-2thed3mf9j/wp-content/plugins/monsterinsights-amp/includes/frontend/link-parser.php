<?php
if ( ! defined( 'AMP__DIR__' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
require_once ( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

class MonsterInsights_AMP_Link_Parser extends AMP_Base_Sanitizer {
	/**
	 * The actual sanitization function
	 */
	public function sanitize() {
		// Affiliate Links
		$inbound_paths = monsterinsights_get_option( 'affiliate_links', array() );
		if ( ! is_array( $inbound_paths ) ) {
			$inbound_paths = array();
		} else {
			foreach( $inbound_paths as $index => $pair ) {
				// if empty pair, unset and continue
				if ( empty( $pair['path'] ) ) {
					unset( $inbound_paths[$index] );
					continue;
				}

				// if path does not start with a /, start it with that
				$path                           = ! empty( $pair['path'] ) ? $pair['path'] : 'aff';
				$inbound_paths[$index]['path']  = trim( $path );

				// js escape the link label
				$label                          = ! empty( $pair['label'] ) ? $pair['label'] : 'aff';
				$inbound_paths[$index]['label'] = 'outbound-link-' . esc_js( trim( $label ) );
			}
		}

		// Get download extensions to track
		$download_extensions = monsterinsights_get_option( 'extensions_of_files', '' );
		$download_extensions = explode( ',', str_replace( '.', '', $download_extensions ) );
		if ( ! is_array( $download_extensions ) ) {
			$download_extensions = array( $download_extensions );
		}
		$i = 0;
		foreach( $download_extensions as $extension ){
			$download_extensions[ $i ] = esc_js( trim( $extension ) );
			$i++;
		}

		$this->options = array(
			'extensions'      => $download_extensions,
			'affiliate_links' => $inbound_paths,
		);

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
		if (  $node->nodeName === 'a' ) {
			$node_name = $node->nodeName;
			$this->parse_href( $node );
		}
		foreach ( $node->childNodes as $child_node ) {
			$this->parse_nodes_recursive( $child_node );
		}
	}

	/**
	 * Sanitizes anchor attributes
	 *
	 * @param DOMNode $node
	 * @param object $attribute
	 */
	private function parse_href( $node ) {
		$href  = $node->getAttribute('href');
		$hrefe = esc_attr( $href );

		// if has Javascript in link
		if ( substr( $href, 0, strlen( 'javascript:' ) ) === 'javascript:' ) {
			return;
		}

		$title = ! empty( $node->getAttribute( 'title' ) ) ? esc_attr( $node->getAttribute('title') ) : '';
		$class = ! empty( $node->getAttribute( 'class' ) ) ? esc_attr( $node->getAttribute('class') ) : '';
		if ( ! empty( $class ) ) {
			$class = $class . ' ';
		}

		if ( empty( $title ) ) {
			$title = ! empty( $node->nodeValue ) ? esc_attr( $node->nodeValue ) : '';
		}


		$category = ! empty( $node->getAttribute('data-vars-ga-category') ) ? esc_attr( $node->getAttribute('data-vars-ga-category') ) : '';
		$action   = ! empty( $node->getAttribute('data-vars-ga-action') )   ? esc_attr( $node->getAttribute('data-vars-ga-action') )   : $hrefe;
		$label    = ! empty( $node->getAttribute('data-vars-ga-label') )    ? esc_attr( $node->getAttribute('data-vars-ga-label') )    : $title;

		if ( ! empty( $category ) ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-link' );
			$node->setAttribute( 'data-vars-category', $category ); // type of link
			$action = $action ? $action : 'click';
			$node->setAttribute( 'data-vars-action', $action );  // href
			$label = $label ? $label : $title;
			$node->setAttribute( 'data-vars-label', $label ); // Link text
			return;
		}

		// if tel
		if ( substr( $href, 0, strlen( 'tel:' ) ) === 'tel:' ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-tel' );
			$node->setAttribute( 'data-vars-category', 'tel' ); // type of link
			$node->setAttribute( 'data-vars-action', $action );  // href
			$node->setAttribute( 'data-vars-label', $label ); // Link text
			return;
		}

		// if mailto
		if ( substr( $href, 0, strlen( 'mailto:' ) ) === 'mailto:' ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-mailto' );
			$node->setAttribute( 'data-vars-category', 'mailto' ); // type of link
			$node->setAttribute( 'data-vars-action', $action );  // href
			$node->setAttribute( 'data-vars-label', $label ); // Link text
			return;
		}

		$url  = wp_parse_url( $href );

		// if download
		if ( ! empty( $url['path'] ) && ! empty( $this->options['extensions'] ) ) {
			foreach ( $this->options['extensions'] as $extension ) {
				if ( monsterinsights_string_ends_with( $url['path'], $extension ) ) {
					$node->setAttribute( 'class', $class . 'monsterinsights-download' );
					$node->setAttribute( 'data-vars-category', 'download' ); // type of link
					$node->setAttribute( 'data-vars-action', $action );  // href
					$node->setAttribute( 'data-vars-label', $label ); // Link text
					return;
				}
			}
		}


		// if internal as outbound
		if ( ! empty( $url['path'] ) && ! empty( $this->options['affiliate_links'] ) && is_array( $this->options['affiliate_links'] ) ) {
			foreach ( $this->options['affiliate_links'] as $link ) {
				if ( ! is_array( $link ) || empty( $link['path'] ) ) {
					return;
				}
				if ( monsterinsights_string_starts_with( $url['path'], $link['path'] ) ) {
					$node->setAttribute( 'class', $class . 'monsterinsights-internal-as-outbound' );
					$node->setAttribute( 'data-vars-category', $link['label'] ); // type of link
					$node->setAttribute( 'data-vars-action', $action );  // href
					$node->setAttribute( 'data-vars-label', $label ); // Link text
					return;
				}
			}
		}

		// if external
		$current_url = home_url();
		if ( ! empty( $url['host'] ) && ! monsterinsights_string_ends_with( $current_url, $url['host'] ) ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-outbound-link' );
			$node->setAttribute( 'data-vars-category', 'outbound-link' ); // type of link
			$node->setAttribute( 'data-vars-action', $action );  // href
			$node->setAttribute( 'data-vars-label', $label ); // Link text
			return;
		}
	}
}
