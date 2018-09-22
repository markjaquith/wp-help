<?php defined( 'ABSPATH' ) or die(); ?>

<?php
global $post;
wp_nonce_field( 'cws-wp-help-save_' . $post->ID, '_cws_wp_help_nonce', false, true );
?>

<div class="misc-pub-section"><input type="checkbox" name="cws_wp_help_make_default_doc" id="cws_wp_help_make_default_doc" <?php checked( $post->ID == get_option( $this::default_doc ) ); ?> /> &nbsp;<label for="cws_wp_help_make_default_doc"><?php _e( 'Set as default help document', 'wp-help' ); ?></label></div>
