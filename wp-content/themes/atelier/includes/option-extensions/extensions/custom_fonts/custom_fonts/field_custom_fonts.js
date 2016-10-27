/* global redux_change, wp */

(function( $ ) {
    "use strict";
    
    redux                               = redux || {};
    redux.field_objects                 = redux.field_objects || {};
    redux.field_objects.custom_fonts    = redux.field_objects.custom_fonts || {};
    
    var l10n;
    var ajaxDone = false;
    
    redux.field_objects.custom_fonts.init = function( selector ) {
      
        // If no selector is passed, grab one from the HTML
        if ( !selector ) {
            selector = $( document ).find( ".redux-group-tab:visible" ).find( '.redux-container-custom_font:visible' );
        }

        // Enum instances of our object
        $( selector ).each(
            function() {
                var el      = $( this );
                var parent  = el;

                if ( !el.hasClass( 'redux-field-container' ) ) {
                    parent = el.parents( '.redux-field-container:first' );
                }

                if ( parent.is( ":hidden" ) ) { // Skip hidden fields
                    return;
                }

                if ( parent.hasClass( 'redux-field-init' ) ) {
                    parent.removeClass( 'redux-field-init' );
                } else {
                    return;
                }

                // Init module level code
                redux.field_objects.custom_fonts.modInit(el);
            }
        );
    };
    
    redux.field_objects.custom_fonts.modInit = function(el) {
        l10n = redux_custom_fonts_l10;
        
        // Remove the image button
        el.find( '.remove-font' ).unbind( 'click' ).on(
            'click', function() {
                redux.field_objects.custom_fonts.remove_font(el, $( this ).parents( 'fieldset.redux-field:first' ) );
            }
        );

        // Upload media button
        el.find( '.media_add_font' ).unbind().on(
            'click', function( event ) {
                redux.field_objects.custom_fonts.add_font(el, event, $( this ).parents( 'fieldset.redux-field:first' ) );
            }
        );

        el.find( '.fontDelete' ).on(
            'click', function( e ) {
                e.preventDefault();
                
                var parent = $( this ).parents( 'td:first' );
                parent.find( '.spinner' ).show();
                
                var data = $( this ).data();
                data.action = "redux_custom_fonts";
                data.nonce = $( this ).parents( '.redux-container-custom_font:first' ).find( '.media_add_font' ).attr( "data-nonce" );

                $.post(
                    ajaxurl, data, function( response ) {
                        response = $.parseJSON( response );

                        if ( response.type && response.type == "success" ) {
                            var rowCount = parent.parents( 'table:first' ).find( 'tr' ).length;

                            if ( rowCount == 1 ) {
                                parent.parents( 'table:first' ).fadeOut().remove();
                            } else {
                                parent.parents( 'tr:first' ).fadeOut().remove();
                            }
                        } else {
                            alert( l10n.delete_error + ' ' + response.msg );
                            parent.find( '.spinner' ).hide();
                        }
                    }
                );
                return false;
            }
        );        
    };
    
    redux.field_objects.custom_fonts.startTimer = function(el, status){
        var cur_data;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'redux_custom_font_timer'
            },
            beforeSend: function () {

            },
            success: function(data) {
                var msg;
                
                if (ajaxDone == false) {
                    setTimeout(redux.field_objects.custom_fonts.startTimer(el, status), 500);
                    
                    msg = redux.args.please_wait + ': ' + status + '<br><br>' + data;
                } else {
                    msg = l10n.complete;
                    data = 'finished';
                }

                if (data != '') {
                    if (data != cur_data) {
                        $('.blockUI.blockMsg h2').html(msg);

                        cur_data = data;
                    }
                }
            }
        });
    };    
    
    redux.field_objects.custom_fonts.add_font = function(el, event, selector) {
        event.preventDefault();
        
        var frame;
        
        // If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }
        
        // Create the media frame.
        frame = wp.media({
            multiple: false,
            library: {
                type: ['application', 'font'] //Only allow zip files
            },
            // Set the title of the modal.
            title: 'Redux Custom Fonts:  ' + l10n.media_title,
            // Customize the submit button.
            button: {
                // Set the text of the button.
                text: l10n.media_button
                // Tell the button not to close the modal, since we're
                // going to refresh the page when the image is selected.
            }
        });
        
        frame.on(
            'click', function() {
                //console.log( 'Hello' );
            }
        );

        // When an image is selected, run a callback.
        frame.on(
            'select', function() {
                // Grab the selected attachment.
                var attachment = frame.state().get( 'selection' ).first();
                var error = selector.find( '.font-error' );

                error.slideUp();
                error.find( 'span' ).text( '' );

                frame.close();
                if ( attachment.attributes.type !== 'application' && attachment.attributes.type !== 'font' ) {
                    return;
                }

                var nonce = $( selector ).find( '.media_add_font' ).attr( "data-nonce" );
                var data = {
                    action: "redux_custom_fonts",
                    nonce: nonce,
                    attachment_id: attachment.id,
                    title: attachment.attributes.title,
                    mime: attachment.attributes.mime
                };
                
                if ( data.mime == "application/zip " ) {
                    var status = l10n.unzip;
                } else {
                    var status = l10n.convert;
                }
                
                redux.field_objects.custom_fonts.startTimer(el, status);
                $.blockUI( {message: '<h2>' + redux.args.please_wait + ': ' + status + '</h2>'} );
                
                $.post(
                    ajaxurl, data, function( response ) {
                        console.log('Redux Custom Fonts API Response (For support purposes)');
                        console.log(response);
                        
                        response = $.parseJSON( response );
                        
                        if ( response.type == "success" ) {
                            if (response.msg != '') {
                                $.unblockUI();
                                error.find( 'span' ).text( response.msg + '  ' + l10n.partial );
                                error.slideDown();
                                
                                ajaxDone = true;
                                return;
                                //redux.field_objects.custom_fonts.sleep (5000)
                            }
                            
                            window.onbeforeunload = "";
                            location.reload();
                        } else if ( response.type == "error" ) {
                            $.unblockUI();
                            error.find( 'span' ).text( response.msg );
                            error.slideDown();
                        } else {
                            $.unblockUI();
                            error.find( 'span' ).text( l10n.unknown );
                            error.slideDown();
                        }
                        
                        ajaxDone = true;
                    }
                );
            }
        );

        // Finally, open the modal.
        frame.open();
    };
    
    redux.field_objects.custom_fonts.remove_font = function (el, selector) {
        // This shouldn't have been run...
        if ( !selector.find( '.remove-image' ).addClass( 'hide' ) ) {
            return;
        }
    };
    
    redux.field_objects.custom_fonts.sleep = function (milliseconds) {
        var start = new Date().getTime();
        
        for (var i = 0; i < 1e7; i++) {
          if ((new Date().getTime() - start) > milliseconds){
            break;
          }
        }        
    };
})( jQuery );