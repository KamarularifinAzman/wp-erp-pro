(function($) {

    // we create a copy of the WP inline edit post function
    var $wp_inline_edit = inlineEditPost.edit;

    // and then we overwrite the function with our own code
    inlineEditPost.edit = function( id ) {


        $wp_inline_edit.apply( this, arguments );

        // get the post ID
        var $post_id = 0;
        if ( typeof( id ) == 'object' ) {
            $post_id = parseInt( this.getId( id ) );
        }

        if ( $post_id > 0 ) {
            // define the edit row
            var $edit_row = $( '#edit-' + $post_id );
            var $post_row = $( '#post-' + $post_id );

            // get the data
            var $training_subject = $( '.column-training_subject', $post_row ).text();
            var $description = $( '.column-description', $post_row ).text();
            var $duration = $( '.column-duration', $post_row ).text();
            var $employee = $( '#get_employee' ).val();
            var $auo_assign = !! $('.column-auto_assigned>*', $post_row ).prop('checked');
            var $training_type  =   $( '#get_training_type' ).val();

            // populate the data
            $( ':input[name="training_subject"]', $edit_row ).val( $training_subject );
            $( ':input[name="description"]', $edit_row ).val( $description );
            $( ':input[name="training_frequency"]', $edit_row ).val( $duration );
            $( ':input[name="employees"]', $edit_row ).val( $employee );
            $( ':input[name="training_type"]', $edit_row ).val( $training_type );
            $( ':input[name="auto_assigned"]', $edit_row ).prop('checked', $auo_assign );
        }
    };

})(jQuery);