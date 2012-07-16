<?php if ( !defined( 'ABSPATH' ) ) die(); ?>

<div id="cws-wp-help-settings">

<?php wp_nonce_field( 'cws-wp-help-settings', '_cws_wp_help_nonce', false, true ); ?>

<h2><?php _e( 'WP Help Settings', 'wp-help' ); ?></h2>

<h3><?php _e( 'Headlines', 'wp-help' ); ?></h3>

<p><?php _e( 'The main WP Help headline and document listing headline are directly editable.', 'wp-help' ); ?></p>

<h3><?php _e( 'Menu Location', 'wp-help' ); ?></h3>

<p><?php _e( 'Display the help documents menu item:', 'wp-help' ); ?> 
	<select id="cws-wp-help-menu-location">
		<option value="dashboard-submenu" <?php selected( 'dashboard-submenu', $this->get_option( 'menu_location' ) ); ?>><?php _e( 'as a Dashboard submenu' ); ?></option>
		<option value="above-dashboard" <?php selected( 'above-dashboard', $this->get_option( 'menu_location' ) ); ?>><?php _e( 'above the Dashboard menu' ); ?></option>
		<option value="below-dashboard" <?php selected( 'below-dashboard', $this->get_option( 'menu_location' ) ); ?>><?php _e( 'below the Dashboard menu' ); ?></option>
		<option value="bottom" <?php selected( 'bottom', $this->get_option( 'menu_location' ) ); ?>><?php _e( 'at the bottom' ); ?></option>
	</select></p>

<h3><?php _ex( 'Sync Source', 'noun, h3 heading about synchronization', 'wp-help' ); ?></h3>

<p><?php _e( 'To treat this install as a source, use this secret URL:', 'wp-help' ); ?><br /><input id="cws-wp-help-api-url" class="regular-text" type="text" readonly value="<?php echo esc_url( $this->api_url() ); ?>" /></p>

<h3><?php _e( 'Sync Pull', 'wp-help' ); ?></h3>

<p><?php _e( 'Pull in help documents from this WP Help secret URL:', 'wp-help' ); ?><br /><input id="cws-wp-help-slurp-url" class="regular-text" type="text" value="<?php echo esc_url( $this->get_option( 'slurp_url' ) ); ?>" /></p>

<p>Note:</p>
<ul>
	<li>Sync pull refreshes automatically once a day.</li>
	<li>You can manually refresh by saving the setting again.</li>
	<li>Synced documents cannot be modified locally while syncing is still enabled.</li>
</ul>

<p class="submit"><?php submit_button( __( 'Save Changes', 'wp-help' ), 'primary', 'cws-wp-help-settings-save', false ); ?> <a href="#" id="cws-wp-help-settings-cancel">Cancel</a></p>

<div id="cws-wp-help-slurp-error"></div>

</div>