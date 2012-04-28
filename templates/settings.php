<?php if ( !defined( 'ABSPATH' ) ) die(); ?>

<div id="cws-wp-help-settings">

<?php wp_nonce_field( 'cws-wp-help-settings', '_cws_wp_help_nonce', false, true ); ?>

<h2><?php _e( 'WP Help Settings' ); ?></h2>

<h3><?php _e( 'Headlines' ); ?></h3>

<p><?php _e( 'The main WP Help headline and document listing headline are directly editable.', 'wp-help' ); ?></p>

<h3><?php _ex( 'Sync Source', 'noun, h3 heading about synchronization', 'wp-help' ); ?></h3>

<p><?php _e( 'To treat this install as a source, use this secret URL:', 'wp-help' ); ?><br /><input id="cws-wp-help-api-url" class="regular-text" type="text" readonly value="<?php echo esc_url( $this->api_url() ); ?>" /></p>

<h3><?php _e( 'Sync Pull', 'wp-help' ); ?></h3>

<p><?php _e( 'Pull in help documents from this WP Help secret URL:', 'wp-help' ); ?><br /><input id="cws-wp-help-slurp-url" class="regular-text" type="text" value="<?php echo esc_url( $this->get_option( 'slurp_url' ) ); ?>" /></p>

<?php submit_button( __( 'Save Changes', 'wp-help' ), 'primary', 'cws-wp-help-settings-save', true ); ?>

<div id="cws-wp-help-slurp-error"></div>

</div>