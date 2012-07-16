(function($) {
	$(function() {
// ==========================================================
		var api = {
			p: function(i) {
				return $('#cws-wp-help-' + i);
			},
			bindH2Updates: function() {
				// Refresh this in case we just moved the menu
				data.menu = $( '#adminmenu a.current' );
				data.menu.text( data.h2.edit.input.val() );
				// Send h2 updates to the menu item as we type
				data.h2.edit.input.bind( 'keyup', function() {
					data.menu.text( $( this ).val() );
				});
			},
			init: function() {
				// Clicking the source API URI
				data.apiURL.click( function() {
					this.select();
				});

				// Clicking the "Save Changes" button
				data.saveButton.click( function() {
					api.saveSettings();
				});

				// Clicking the "Settings" button
				data.settingsButton.click( function(e) {
					e.preventDefault();
					api.revealSettings( true ); // true = autofocus on the h2 with no highlighting
				});

				// Doubleclick the h2
				data.h2.display.text.dblclick( function() {
					api.revealSettings();
					data.h2.edit.input.focus();
				});
				
				// Doubleclick the h3
				data.h3.display.text.dblclick( function() {
					api.revealSettings();
					data.h3.edit.input.focus();
				});

				// Monitor for "return" presses in our text inputs
				data.returnMonitor.bind( 'keydown', function(e) {
					if ( 13 == e.which ) {
						$( this ).blur();
						api.saveSettings();
					}
				});

				api.bindH2Updates();

				// Preview menu placement "live"
				data.menuLocation.change( function() {
					var newLocation = String( window.location );
					if ( data.menuLocation.val().indexOf( 'submenu' ) == -1 ) {
						newLocation = newLocation.replace( '/index.php', '/admin.php' );
					} else {
						newLocation = newLocation.replace( '/admin.php', '/index.php' );
					}
					var newLocationPreview = String( newLocation ) + '&wp-help-preview-menu-location=' + data.menuLocation.val();
					var commonScript = String( newLocation ).replace( /\/wp-admin\/.*$/, '/wp-admin/js/common.js' );
					
					$( '#adminmenu' ).load( newLocationPreview + ' #adminmenu', function() {
						if ( window.history.replaceState ) {
							window.history.replaceState( null, null, newLocation );
						}
						$.getScript( commonScript ); // Makes the menu work again
						api.bindH2Updates(); // Makes live H2 previewing work again
					});
				});
			},
			fadeOutIn: function(first, second) {
				first.fadeOut( 150, function() {
					second.fadeIn( 150 );
				});
			},
			hideShow: function(hide, show) {
				hide.hide();
				show.show();
			},
			revealSettings: function(autofocus) {
				$([ data.h2, data.h3 ]).each( function() {
					api.hideShow( this.display.wrap, this.edit.wrap );
				});
				data.actions.fadeTo( 200, 0 ).slideUp( 200 );
				api.fadeOutIn( data.doc, data.settings );
				if ( autofocus ) {
					(function(h2) {
						h2.focus().val( h2.val() );
					})(data.h2.edit.input);
				}
			},
			saveSettings: function() {
				api.clearError();
				$([ data.h2, data.h3 ]).each( function() {
					this.display.text.text( this.edit.input.val() );
				});
				$.post( ajaxurl, {
					action: 'cws_wp_help_settings',
					nonce: $('#_cws_wp_help_nonce').val(),
					h2: data.h2.edit.input.val(),
					h3: data.h3.edit.input.val(),
					menu_location: data.menuLocation.val(),
					slurp_url: data.slurp.val()
				}, function(result) {
					result = $.parseJSON( result );
					data.slurp.val( result.slurp_url );
					if ( result.error ) {
						api.error( result.error );
						data.slurp.focus();
					} else {
						api.hideSettings();
					}
				});
			},
			hideSettings: function() {
				$([ data.h2, data.h3 ]).each( function() {
					api.hideShow( this.edit.wrap, this.display.wrap );
				});
				data.actions.slideDown( 200 ).fadeTo( 200, 1 );
				api.fadeOutIn( data.settings, data.doc );
			},
			clearError: function(){
				data.slurpError.html('').hide();
			},
			error: function(msg){
				data.slurpError.html( '<p>' + msg + '</p>' ).fadeIn(150);
			}
		};

		var data = {
			h2: {
				edit: {
					input: api.p( 'h2-label' ),
					wrap: api.p( 'h2-label-wrap' )
				},
				display: {
					text: $( '.wrap h2:first' ),
					wrap: $( '.wrap h2:first' )
				}
			},
			h3: {
				edit: {
					input: api.p( 'listing-label' ),
					wrap: api.p( 'listing-labels' )
				},
				display: {
					text: api.p( 'listing h3' ),
					wrap: api.p( 'listing h3' )
				}
			},
			settingsButton: api.p( 'settings-on' ),
			menu: function() { return $( '#adminmenu a.current' ); },
			doc: api.p( 'document' ),
			actions: api.p( 'actions' ),
			settings: api.p( 'settings' ),
			apiURL: api.p( 'api-url' ),
			slurp: api.p( 'slurp-url' ),
			slurpError: api.p( 'slurp-error' ),
			saveButton: api.p( 'settings-save' ),
			menuLocation: api.p( 'menu-location' ),
			returnMonitor: $( '.wrap input[type="text"]' )
		};

		// Bootstrap everything
		api.init();
// ==========================================================
	});
})(jQuery);