function MiWidgetPro() {

	var self = this,
		$ = window.jQuery,
		mi_data = window.monsterinsights_dashboard_widget,
		$widget_element = $( document.getElementById( 'monsterinsights_reports_widget' ) ),
		$widget_title = $widget_element.find( '.hndle' ),
		$widget_controls = $widget_element.find( '.mi-dw-controls' ),
		$welcome_panel = $( document.getElementById( 'welcome-panel' ) ),
		$normal_sortables = $( document.getElementById( 'normal-sortables' ) ),
		$report_options_element = $widget_element.find( '.mi-dw-reports-options' ),
		$reports_content = $widget_element.find( '.mi-dw-reports-content' ),
		$report_template = $( document.getElementById( 'mi-dw-report-template' ) ).html(),
		$error_template = $( document.getElementById( 'mi-dw-error-template' ) ).html();

	this.init = function () {
		// Stop loading early if MI is not authenticated.
		if ( ! this.is_authed() ) {
			return false;
		}

		this.add_widget_toggle();
		this.styled_toggle();
		this.dropdown_toggle();
		this.add_events();
		this.render_options();
		this.styled_checkboxes();
		this.tabs();
		this.tooltips();
		this.btn_group();
		this.apply_widget_settings();
		this.load_default();
	};

	this.state = mi_data.widget_state;
	this.active_report = '';

	this.set = function ( property, value ) {
		if ( 'undefined' === typeof this.state[property] ) {
			console.log( 'Unknown property to set' );
			return;
		}

		// No need to push if value hasn't changed.
		if ( 'string' === typeof value && this.state[property] === value ) {
			return false;
		}

		if ( 'object' === typeof value && JSON.stringify( this.state[property] ) === JSON.stringify( value ) ) {
			return false;
		}

		this.state[property] = value;
		this.save_state();
	};

	this.get = function ( property ) {
		if ( 'undefined' === typeof this.state[property] ) {
			console.log( 'Unknown property to get: ' + property );
			return false;
		}
		var value = this.state[property];
		if ( 'object' === typeof value ) {
			value = $.extend( true, {}, value );
		}

		return value;
	};

	this.save_state = function () {
		if ( this.state_call ) {
			this.state_call.abort();
		}

		this.state_call = $.ajax( {
			url: ajaxurl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'monsterinsights_save_widget_state',
				security: mi_data.options_nonce,
				widget_state: self.state
			}
		} );
	};

	this.add_events = function () {
		this.$widget_toggle.on( 'change', function () {
			self.toggle_widget_size( $( this ) );
		} );

		$report_options_element.on( 'change', 'input', function () {
			self.trigger_report_change( $( this ) );
		} );

		$reports_content.on( 'click', '.mi-dw-report-toggle', function ( e ) {
			e.preventDefault();
			self.toggle_reports_accordion( $( this ) );
		} );

		$widget_element.on( 'mi-btn-group-change', function ( event, data ) {
			if ( data.type && 'datepicker' === data.type ) {
				self.handle_interval_change( data.value );
			}
		} );

		$widget_element.find( '.mi-dw-reports-options' ).on( 'click', function ( e ) {
			e.stopPropagation();
		} );
	};

	this.apply_widget_settings = function () {
		if ( 'full' === this.get( 'width' ) ) {
			this.$widget_toggle.prop( 'checked', true ).trigger( 'change' );
		}
	};

	this.add_widget_toggle = function () {
		this.$widget_toggle = $widget_element.find( '.mi-dw-widget-width-toggle' );
	};

	this.toggle_widget_size = function ( el ) {
		if ( el.is( ':checked' ) ) {
			$widget_element.insertAfter( $welcome_panel ).addClass( 'mi-dw-full-width-widget' );
			this.set( 'width', 'full' );
			self.load_all_visible_reports();
		} else {
			$widget_element.prependTo( $normal_sortables ).removeClass( 'mi-dw-full-width-widget' );
			this.set( 'width', 'regular' );
		}
	};

	this.load_all_visible_reports = function () {
		var visible_reports = $widget_element.find( '.mi-dw-report-toggle.visible:not(.mi-dw-active)' );
		$.each( visible_reports, function () {
			var $this = $( this );
			// Only load report data if it's not already loaded.
			if ( '' === $this.next().html() ) {
				self.load_report_data( $this.data( 'report-type' ), $this.data( 'report-component' ), $this.next() );
			}
		} );
	};

	this.render_options = function () {
		var reports = self.get( 'reports' );
		self.render_report( 'overview', 'overview_top', true );

		$.each( reports, function ( report_type, report_components ) {
			var report_el = $( '<li />' );
			if ( mi_data['reports_names'][report_type] ) {
				report_el.append( '<span>' + mi_data['reports_names'][report_type] + '</span>' );
			}

			if ( ! $.isEmptyObject( report_components ) ) {
				var components_el = $( '<ul />' );
				$.each( report_components, function ( report_component, enabled ) {
					var report_li = $( '<li />' ),
						report_label = $( '<label class="mi-dw-styled-checkboxes" />' ),
						report_input = $( '<input type="checkbox" data-report="' + report_type + '" data-component="' + report_component + '" />' ),
						show_report = 'true' === enabled;
					report_input.prop( 'checked', show_report );
					report_label.append( report_input );
					if ( mi_data['reports_names'][report_component] ) {
						report_label.append( mi_data['reports_names'][report_component] );
					}
					report_li.append( report_label );
					components_el.append( report_li );
					self.render_report( report_type, report_component, show_report );
				} );
				report_el.append( components_el );
			}

			$report_options_element.append( report_el );
		} );

		$widget_controls.appendTo( $widget_title );
		$widget_element.addClass( 'mi-loaded' );

	};

	this.trigger_report_change = function ( $el ) {
		var report = $el.data( 'report' ),
			component = $el.data( 'component' ),
			reports = this.get( 'reports' ),
			show = $el.is( ':checked' ),
			accordion_element = $( '[data-report-component="' + component + '"]' );
		if ( show ) {
			accordion_element.addClass( 'visible' );
			if ( '' === accordion_element.next().html() ) {
				self.load_report_data( accordion_element.data( 'report-type' ), component, accordion_element.next() );
			}
			if ( $el.hasClass( 'monsterinsights-not-available' ) ) {
				return $widget_element.find( '.monsterinsights-not-available' ).not( $el ).prop( 'checked', false ).trigger( 'change' );
			}
		} else {
			accordion_element.removeClass( 'visible' ).removeClass( 'mi-dw-active' ).next().removeClass( 'visible' );
		}

		if ( 0 === $widget_element.find( '.monsterinsights-not-available:checked' ).length ) {
			$reports_content.removeClass( 'mi-dw-upsell-shown' );
		} else {
			$reports_content.addClass( 'mi-dw-upsell-shown' );
		}

		reports[report][component] = show;
		this.set( 'reports', reports );
	};

	this.render_report = function ( report_type, report_component, show ) {
		var report_template = $( $report_template );
		var report_toggle = report_template.first();
		var report_tooltip = report_toggle.find( '.monsterinsights-reports-uright-tooltip' );
		if ( mi_data['reports_names'][report_component] ) {
			report_template.find( '.mi-dw-report-title' ).text( mi_data['reports_names'][report_component] );
		}
		report_toggle.attr( 'data-report-type', report_type ).attr( 'data-report-component', report_component ).addClass( 'monsterinsights-report-' + report_type ).addClass( 'monsterinsights-report-component-' + report_component );
		report_toggle.next().addClass( 'monsterinsights-report-component-' + report_component );
		if ( show ) {
			report_toggle.addClass( 'visible' );
		}
		if ( mi_data['reports_tooltips'][report_component] ) {
			report_tooltip.attr( 'data-tooltip-title', mi_data['reports_names'][report_component] ).attr( 'data-tooltip-description', mi_data['reports_tooltips'][report_component] );
		} else {
			report_tooltip.hide();
		}
		$reports_content.append( report_template );
	};

	this.toggle_reports_accordion = function ( el ) {
		$reports_content.find( '.mi-dw-report-content' ).removeClass( 'visible' );

		if ( el.hasClass( 'mi-dw-active' ) ) {
			el.removeClass( 'mi-dw-active' );
		} else {
			$reports_content.find( '.mi-dw-report-toggle' ).removeClass( 'mi-dw-active' );
			var content_el = el.next();
			if ( '' === content_el.html() ) {
				this.load_report_data( el.data( 'report-type' ), el.data( 'report-component' ), content_el );
			}
			self.active_report = el.data( 'report-component' );
			content_el.addClass( 'visible' );
			el.addClass( 'mi-dw-active' );
			this.maybe_scroll_into_view( el );
		}
	};

	this.maybe_scroll_into_view = function ( el ) {
		var toggle_offset = el.offset();
		var target = toggle_offset.top - 50;
		var window_scroll = window.pageYOffset || document.documentElement.scrollTop;
		// Only scroll elemnent into view if it starts above the fold.
		if ( window_scroll > target ) {
			window.scrollTo( 0, target );
		}
	};

	this.load_report_data = function ( report_type, report_component, load_here ) {
		this.showLoader( load_here );
		$.ajax( {
			url: ajaxurl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'monsterinsights_get_dashboard_widget_report',
				security: mi_data.options_nonce,
				report: report_type,
				interval: self.get( 'interval' ),
				component: report_component
			}
		} ).done( function ( response ) {
			if ( response.success && response.data.html ) {
				load_here.html( response.data.html );
				self.report_loaded_actions( report_component, response );
			} else {
				var text = response.data.message ? response.data.message : monsterinsights_dashboard_widget.error_default;
				var footer = response.data.data.footer ? response.data.data.footer : false;
				load_here.html( self.error_markup( monsterinsights_dashboard_widget.error_text, text, footer ) );
			}
		} );
	};

	this.handle_interval_change = function ( value ) {
		self.set( 'interval', value );
		$reports_content.find( '.mi-dw-report-content' ).empty();
		if ( 'full' === this.get( 'width' ) ) {
			self.load_all_visible_reports();
		}
		var active_report = $( '[data-report-component="' + self.active_report + '"]' );
		if ( active_report ) {
			self.load_report_data( active_report.data( 'report-type' ), active_report.data( 'report-component' ), active_report.next() );
		}
	};

	this.styled_toggle = function () {

		var toggles = $widget_element.find( '.mi-dw-styled-toggle' );

		toggles.on( 'click', function ( e ) {
			e.stopPropagation();
			$( document ).trigger( 'click.monsterinsights' ); // Trigger event to close any open dropdowns without propagating click.
			var $this = $( this );
			var el = $this.find( 'input' );
			el.is( ':checked' ) ? $this.addClass( 'mi-dw-styled-toggle-checked' ) : $this.removeClass( 'mi-dw-styled-toggle-checked' );
		} );

		toggles.on( 'change', 'input', function ( e ) {
			var $this = $( this );
			var label = $this.closest( '.mi-dw-styled-toggle' );
			$this.is( ':checked' ) ? label.addClass( 'mi-dw-styled-toggle-checked' ) : label.removeClass( 'mi-dw-styled-toggle-checked' );
		} );
	};

	this.styled_checkboxes = function () {

		var checkboxes = $widget_element.find( '.mi-dw-styled-checkboxes' );

		checkboxes.on( 'change', 'input', function ( e ) {
			var $this = $( this );
			var label = $this.closest( '.mi-dw-styled-checkboxes' );
			$this.is( ':checked' ) ? label.addClass( 'mi-dw-styled-checkbox-checked' ) : label.removeClass( 'mi-dw-styled-checkbox-checked' );
		} );

		$.each( checkboxes, function () {
			var $this = $( this );
			var checkbox = $( this ).find( '[type="checkbox"]' );
			checkbox.is( ':checked' ) ? $this.addClass( 'mi-dw-styled-checkbox-checked' ) : $this.removeClass( 'mi-dw-styled-checkbox-checked' );
		} );
	};

	this.dropdown_toggle = function () {

		var toggles = $widget_element.find( '.mi-dw-dropdown-toggle' );

		toggles.on( 'click', function ( e ) {
			e.stopPropagation();
			var parent = $( this ).parent();
			if ( parent.hasClass( 'mi-dw-open' ) ) {
				$( document ).off( 'click.monsterinsights' );
				return parent.removeClass( 'mi-dw-open' );
			}
			parent.addClass( 'mi-dw-open' );
			self.dropdown_close( parent, parent.find( 'ul' ).first() );
		} );
	};

	this.dropdown_close = function ( element, container ) {
		$( document ).on( 'click.monsterinsights', function ( e ) {
			if ( ! container.is( e.target ) ) {
				element.removeClass( 'mi-dw-open' );
				$( document ).off( 'click.monsterinsights' );
			}
		} );
	};

	this.load_default = function () {
		self.toggle_reports_accordion( $( document.querySelector( '.mi-dw-report-toggle' ) ) );
	};

	this.tabs = function () {
		$widget_element.on( 'click', '.monsterinsights-tabbed-nav-tab-title a', function ( e ) {
			e.preventDefault();
			var parent = $( this ).parent();
			var target = parent.data( 'tab' );
			if ( parent.hasClass( 'active' ) ) {
				return false;
			}
			var current_tab_nav = $( this ).closest( '.monsterinsights-tabbed-nav' );
			var current_tab_nav_parent = current_tab_nav.parent();
			current_tab_nav.find( '.active' ).removeClass( 'active' );
			parent.addClass( 'active' );

			current_tab_nav_parent.find( '.monsterinsights-tabbed-nav-panel' ).hide();
			current_tab_nav_parent.find( '.' + target ).show();

		} );
	};

	this.tooltips = function () {
		$( 'body' ).tooltip( {
			selector: '.monsterinsights-reports-uright-tooltip',
			items: '[data-tooltip-title], [data-tooltip-description]',
			content: function () {
				return '<div class="monsterinsights-reports-tooltip-title">' + jQuery( this ).data( 'tooltip-title' ) + '</div>' +
				       '<div class="monsterinsights-reports-tooltip-content">' + jQuery( this ).data( 'tooltip-description' ) + '</div>';
			},
			tooltipClass: 'monsterinsights-reports-ui-tooltip',
			position: {my: 'right-10 top', at: 'left top', collision: 'flipfit'},
			hide: {duration: 200},
			show: {duration: 200},
		} );
		$( '.mi-dw-styled-toggle' ).tooltip( {
			tooltipClass: 'mi-dw-ui-tooltip',
			position: {my: 'center bottom-12', at: 'center top', collision: 'flipfit'},
		} );
	};

	this.showLoader = function ( el ) {
		el.html( '<div class="mi-dw-loading"></div>' );
	};

	this.btn_group = function () {
		$widget_controls.on( 'click', '.mi-dw-btn-group-label', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			var parent = $( this ).closest( '.mi-dw-btn-group' );
			if ( parent.hasClass( 'mi-dw-open' ) ) {
				parent.removeClass( 'mi-dw-open' );
				$( document ).off( 'click.monsterinsights' );
				return false;
			} else {
				parent.addClass( 'mi-dw-open' );
			}
			self.dropdown_close( parent, parent.find( '.mi-dw-btn-list' ).first() );
		} );

		$widget_controls.on( 'click', '.mi-dw-btn-group .mi-dw-btn', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			var $this = $( this );
			// Don't trigger event if the clicked element is currently active.
			if ( $this.hasClass( 'selected' ) ) {
				return false;
			}
			var btn_group = $this.closest( '.mi-dw-btn-group' );
			var label = btn_group.find( '.mi-dw-btn-group-label' );
			btn_group.removeClass( 'mi-dw-open' );
			btn_group.find( '.mi-dw-btn' ).removeClass( 'selected' );
			$this.addClass( 'selected' );
			label.text( $this.text() );
			$widget_element.trigger( 'mi-btn-group-change', {
				type: btn_group.data( 'type' ),
				value: $this.data( 'value' )
			} );
		} );
	};

	this.report_loaded_actions = function ( report_component, response ) {
		if ( 'overview_top' === report_component ) {
			if ( '' !== mi_data.default_tab ) {
				$widget_element.find( '[data-tab="' + mi_data.default_tab + '"] a' ).click();
			}
		}
		if ( 'overview_top' === report_component || 'infobox' === report_component || 'addremove' === report_component ) {
			$widget_element.find( '.monsterinsights-reports-infobox-title' ).each( function () {
				$( this ).text( $.trim( $( this ).text() ) );
			} );
		}
		if ( response.data.upsell ) {
			var $checkbox = $widget_element.find( '[data-component="' + report_component + '"]' ),
				$content = $widget_element.find( '.monsterinsights-report-component-' + report_component );
			$checkbox.addClass( 'monsterinsights-not-available' );
			$widget_element.find( '.monsterinsights-not-available' ).not( $checkbox ).prop( 'checked', false ).trigger( 'change' );
			$content.addClass( 'mi-dw-upsell-loaded' );
			$reports_content.addClass( 'mi-dw-upsell-shown' );
		}
	};

	this.error_markup = function ( title, text, footer ) {
		var error_template = $( $error_template );

		error_template.find( '.mi-dw-error-title' ).html( title );
		error_template.find( '.mi-dw-error-content' ).html( text );

		if ( footer ) {
			error_template.find( '.mi-dw-error-footer' ).html( footer ).addClass( 'visible' );
		}

		return error_template;

	};

	this.is_authed = function () {
		return ! (
			$widget_element.find( '.mi-dw-not-authed' ).length > 0
		);
	};
	window.uorigindetected = 'no';

	this.init();
}

new MiWidgetPro();
