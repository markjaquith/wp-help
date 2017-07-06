<?php defined( 'ABSPATH' ) or die(); ?>

<script>
(function($){
	var a = $('.wrap:first h1:first a:first'), i = ' <a href="edit.php?post_type=<?php echo $this::POST_TYPE; ?>" class="add-new-h2"><?php echo esc_js( _x( 'Manage', 'verb. Button with limited space', 'wp-help' ) ); ?></a> ';
	if ( a.length )
		a.before(i);
	else
		$('.wrap:first h1:first').append(i);
	$('#parent_id').detach().insertAfter( '.misc-pub-section:last' ).wrap('<div class="misc-pub-section"></div>').before( '<?php echo esc_js( __( 'Parent:', 'wp-help' ) ); ?> ' ).css( 'max-width', '80%' );
	$('#pageparentdiv').hide();
	$('#screen-options-wrap #pageparentdiv-hide').parent('label').hide();
})(jQuery);
</script>
