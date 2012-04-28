<?php if ( !defined( 'ABSPATH' ) ) die(); ?>

<?php $pages = $this->get_help_topics_html(); ?>
<?php if ( trim( $pages ) ) : ?>
<div id="cws-wp-help-listing">
<div id="cws-wp-help-listing-labels"><input type="text" id="cws-wp-help-listing-label" value="<?php echo esc_attr( $this->get_option( 'h3' ) ); ?>" /></div>
<h3><i><?php echo esc_html( $this->get_option( 'h3' ) ); ?></i><?php if ( current_user_can( 'publish_pages' ) ) : ?><span><a href="<?php echo admin_url( 'edit.php?post_type=wp-help' ); ?>"><?php _ex( 'Manage', 'verb. Button with limited space', 'wp-help' ); ?></a></span><?php endif; ?><?php if ( current_user_can( 'manage_options' ) ) : ?><span><a href="#" id="cws-wp-help-settings-on"><?php _ex( 'Settings', 'Button with limited space' ); ?></a></span><?php endif; ?></h3>
<ul>
<?php echo $pages; ?>
</ul>
</div>
<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<?php include( dirname( __FILE__ ) . '/settings.php' ); ?>
<?php endif; ?>

<div id="cws-wp-help-document">
<?php if ( $document_id ) : ?>
	<?php $document = new WP_Query( array( 'post_type' => 'wp-help', 'p' => $document_id ) ); ?>
	<?php if ( $document->have_posts() ) : $document->the_post(); ?>
		<h2><?php the_title(); ?></h2>
		<?php the_content(); ?>
	<?php else : ?>
	<p><?php _e( 'The requested help document could not be found', 'wp-help' ); ?>
	<?php endif; ?>
<?php endif; ?>
</div>
<?php else : ?>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<p><?php printf( __( 'No published help documents found. <a href="%s">Manage Help Documents</a>.', 'wp-help' ), admin_url( 'edit.php?post_type=wp-help' ) ); ?></p>
	<?php else : ?>
		<p><?php _e( 'No help documents found. Contact the site administrator.', 'wp-help' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
