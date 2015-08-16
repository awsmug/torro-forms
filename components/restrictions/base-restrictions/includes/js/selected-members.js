(function ($) {
    "use strict";
    $( function () {

        /**
         * Initializing adding participiants option
         */
        $( "#questions-add-participiants-option" ).change( function(){
            questions_add_participiants_show_hide_boxes();
        });

        var questions_add_participiants_show_hide_boxes = function(){
            var questions_add_participiants_option = $( "#questions-add-participiants-option" ).val(); // Getting selected box

            $( ".questions-add-participiants-content" ).hide(); // Hiding all boxes
            $( "#questions-add-participiants-content-" +  questions_add_participiants_option ).show(); // Showing selected box
        }

        questions_add_participiants_show_hide_boxes();

        /**
         * Setup "Not found"
         */
        var questions_setup_not_found_message = function() {
            var count_participiants = parseInt($("#questions-participiants-count").val());

            if( count_participiants > 0 ){
                $( ".no-users-found" ).hide();
            }else{
                $( ".no-users-found" ).show();
            }
        }

        questions_setup_not_found_message();

        /**
         * Members - Adding Participiants
         */
        $.questions_add_participiants = function( response ){
            var questions_participiants = $( "#questions-participiants" ).val();
            questions_participiants = questions_participiants.split( ',' );

            var count_added_participiants = 0;

            $.each( response, function( i, object ) {
                var found = false;

                if( in_array( object.id, questions_participiants ) ){
                    found = true;
                }

                // If there where found participiants
                if( false == found ){

                    // Adding participiants
                    if( '' == questions_participiants ){
                        questions_participiants =  object.id;
                    }else{
                        questions_participiants = questions_participiants + ',' + object.id;
                    }
                    $( "#questions-participiants-list tbody" ).append( '<tr class="participiant participiant-user-' + object.id + ' just-added"><td>' + object.id + '</td><td>' + object.user_nicename + '</td><td>' + object.display_name + '</td><td>' + object.user_email + '</td><td>' + translation_sm.just_added + '</td><td><a class="button questions-delete-participiant" rel="' + object.id +  '">' + translation_sm.delete + '</a></td></tr>' );
                    count_added_participiants++;
                }
            });

            var count_participiants = parseInt( $( "#questions-participiants-count" ).val() ) + count_added_participiants;

            $( "#questions-participiants" ).val( questions_participiants );
            $.questions_participiants_counter( count_participiants );

            $( "#questions-participiants-list" ).show();
            $.questions_delete_participiant();

            questions_setup_not_found_message();
        }

        $( "#questions-add-participiants-allmembers-button" ).click( function(){

            var data = {
                action: 'questions_add_participiants_allmembers'
            };

            var button = $( this );
            button.addClass( 'button-loading' );

            $.post( ajaxurl, data, function( response ) {
                response = jQuery.parseJSON( response );
                $.questions_add_participiants( response );
                button.removeClass( 'button-loading' );
            });
        });

        /**
         * Counting participiants
         */
        $.questions_participiants_counter = function( number ){
            var text = number + ' ' + translation_sm.added_participiants;
            $( "#questions-participiants-status p").html( text );
            $( "#questions-participiants-count" ).val( number );
        }

        /**
         * Removing participiant from list
         */
        $.questions_delete_participiant = function(){
            $( ".questions-delete-participiant" ).click( function(){
                var delete_user_id = $( this ).attr( 'rel' );

                var questions_participiants_new = '';

                var questions_participiants = $( "#questions-participiants" ).val();
                questions_participiants = questions_participiants.split( "," );

                $.each( questions_participiants, function( key, value ) {
                    if( value != delete_user_id ){
                        if( '' == questions_participiants_new ){
                            questions_participiants_new = value;
                        }else{
                            questions_participiants_new = questions_participiants_new + ',' + value;
                        }
                    }
                });

                $( "#questions-participiants" ).val( questions_participiants_new );
                $.questions_participiants_counter( $( "#questions-participiants-count" ).val() - 1 );
                $( ".participiant-user-" + delete_user_id ).remove();

                questions_setup_not_found_message();
            });
        }
        $.questions_delete_participiant();

        /**
         * Removing all Participiants from list
         */
        $( ".questions-remove-all-participiants" ).click( function(){
            $( "#questions-participiants" ).val( '' );
            $( "#questions-participiants-count" ).val( 0 );
            $.questions_participiants_counter( 0 );

            $( "#questions-participiants-list tbody .participiant" ).remove();

            questions_setup_not_found_message();
        });

        /**
         * Invite participiants
         */
        $( '#questions-invite-button' ).click( function(){

            var button = $( this )

            if( button.hasClass( 'button-primary' ) ){
                var data = {
                    action: 'questions_invite_participiants',
                    invitation_type: 'invite',
                    form_id: $( '#post_ID' ).val(),
                    subject_template: $( '#questions-invite-subject' ).val(),
                    text_template: $( '#questions-invite-text' ).val()
                };

                button.addClass( 'button-loading' );

                $.post( ajaxurl, data, function( response ) {
                    response = jQuery.parseJSON( response );
                    if( response.sent ){
                        $( '#questions-invite-subject' ).fadeOut( 200 );
                        $( '#questions-invite-text' ).fadeOut( 200 );
                        $( '#questions-invite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.invitations_sent_successfully + '</p>' );
                    }else{
                        $( '#questions-invite-subject' ).fadeOut( 200 );
                        $( '#questions-invite-text' ).fadeOut( 200 );
                        $( '#questions-invite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.invitations_sent_not_successfully + '</p>' );
                    }
                    button.removeClass( 'button-loading' );

                    $( '.form-reinvitations-sent' ).fadeOut( 4000 );
                    $( '#questions-invite-button' ).removeClass( 'button-primary' );
                    $( '#questions-invite-text' ).fadeOut( 200 );
                    $( '#questions-invite-button-cancel' ).fadeOut( 200 );
                });

            }else{
                button.addClass( 'button-primary' );
                $( '#questions-invite-subject' ).fadeIn( 200 );
                $( '#questions-invite-text' ).fadeIn( 200 );
                $( '#questions-invite-button-cancel' ).fadeIn( 200 );
            }
        });

        $( '#questions-invite-button-cancel' ).click( function(){
            $( '#questions-invite-button' ).removeClass( 'button-primary' );
            $( '#questions-invite-subject' ).fadeOut( 200 );
            $( '#questions-invite-text' ).fadeOut( 200 );
            $( '#questions-invite-button-cancel' ).fadeOut( 200 );
        });

        $( '#questions-reinvite-button' ).click( function(){
            var button = $( this )

            if( button.hasClass( 'button-primary' ) ){
                var data = {
                    action: 'questions_invite_participiants',
                    invitation_type: 'reinvite',
                    form_id: $( '#post_ID' ).val(),
                    subject_template: $( '#questions-reinvite-subject' ).val(),
                    text_template: $( '#questions-reinvite-text' ).val()
                };

                button.addClass( 'button-loading' );

                $.post( ajaxurl, data, function( response ) {
                    response = jQuery.parseJSON( response );
                    if( response.sent ){
                        $( '#questions-reinvite-subject' ).fadeOut( 200 );
                        $( '#questions-reinvite-text' ).fadeOut( 200 );
                        $( '#questions-reinvite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.reinvitations_sent_successfully + '</p>' );
                        button.removeClass( 'button-loading' );
                        $( '.form-reinvitations-sent' ).fadeOut( 4000 );
                    }else{
                        $( '#questions-reinvite-subject' ).fadeOut( 200 );
                        $( '#questions-reinvite-text' ).fadeOut( 200 );
                        $( '#questions-reinvite-text' ).after( '<p class="form-reinvitations-sent">' + translation_sm.reinvitations_sent_not_successfully + '</p>' );

                    }
                    button.removeClass( 'button-loading' );
                    $( '.form-reinvitations-sent' ).fadeOut( 4000 );
                    $( '#questions-reinvite-button' ).removeClass( 'button-primary' );
                    $( '#questions-reinvite-text' ).fadeOut( 200 );
                    $( '#questions-reinvite-button-cancel' ).fadeOut( 200 );
                });

            }else{
                button.addClass( 'button-primary' );
                $( '#questions-reinvite-subject' ).fadeIn( 200 );
                $( '#questions-reinvite-text' ).fadeIn( 200 )
                $( '#questions-reinvite-button-cancel' ).fadeIn( 200 )
            }
        });

        $( '#questions-reinvite-button-cancel' ).click( function(){
            $( '#questions-reinvite-button' ).removeClass( 'button-primary' );
            $( '#questions-reinvite-subject' ).fadeOut( 200 );
            $( '#questions-reinvite-text' ).fadeOut( 200 );
            $( '#questions-reinvite-button-cancel' ).fadeOut( 200 );
        });

        /**
         * Helper function - Getting a random number
         */
        function questions_rand(){
            var now = new Date();
            var random = Math.floor(Math.random() * ( 10000 - 10 + 1)) + 10;
            random = random * now.getTime();
            random = random.toString().substring( 0, 5 );
            console.log( random );
            return random;
        }

        /**
         * Helper function - JS recreating of PHP in_array function
         */
        function in_array( needle, haystack ) {
            var length = haystack.length;
            for(var i = 0; i < length; i++) {
                if(haystack[i] == needle) return true;
            }
            return false;
        }

    });
}(jQuery));