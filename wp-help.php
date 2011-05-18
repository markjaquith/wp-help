<?php
/*
Plugin Name: WP Help
Description: Administrators can created detailed, hierarchical documentation for the site's authors and editors, viewable in the WordPress admin.
Version: 0.1
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

class CWS_WP_Help_Plugin {
	public static $instance;

	public function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		register_post_type( 'wp-help',
			array(
				'label' => 'Help',
//				'description' => '',
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'hierarchical' => true,
				'supports' => array( 'title', 'editor', 'revisions', 'page-attributes' ),
				'capabilities' => array(
					'publish_posts' => 'manage_options',
					'edit_posts' => 'manage_options',
					'edit_others_posts' => 'manage_options',
					'delete_posts' => 'manage_options',
					'read_private_posts' => 'manage_options',
					'edit_post' => 'manage_options',
					'delete_post' => 'manage_options',
					'read_post' => 'read'
				),
				'labels' => array (
					'name' => 'Help Documents',
					'singular_name' => 'Help Document',
					'add_new' => 'Add New',
					'add_new_item' => 'Add New Help Document',
					'edit' => 'Edit',
					'edit_item' => 'Edit Help Document',
					'new_item' => 'New Help Document',
					'view' => 'View',
					'view_item' => 'View Help Document',
					'search_items' => 'Search Help Documents',
					'not_found' => 'No Help Documents Found',
					'not_found_in_trash' => 'No Help Documents found in Trash',
					'parent' => 'Parent Help Document'
				)
			)
		);
	}

	public function admin_menu() {
		$hook = add_dashboard_page( __( 'Publishing Help' ), __( 'Publishing Help' ), 'publish_posts', 'wp-help-documents', array( $this, 'render_listing_page' ) );
		add_action( "load-{$hook}", array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		$suffix = defined ('SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';
		wp_enqueue_style( 'cws-wp-help', plugins_url( "wp-help$suffix.css", __FILE__ ), array(), '20110518' );
	}

	private function enable_link_filter() {
		$this->disable_link_filter();
		add_filter( 'post_type_link', array( $this, 'page_link' ), 10, 2 );
	}

	private function disable_link_filter() {
		remove_filter( 'post_type_link', array( $this, 'page_link' ), 10, 2 );
	}

	public function page_link( $link, $post ) {
		$post = get_post( $post );
		return admin_url( 'index.php?page=wp-help-documents&document=' . absint( $post->ID ) );
	}

	private function get_help_topics_html() {
		$this->enable_link_filter();
		$pages = wp_list_pages( array( 'post_type' => 'wp-help', 'hierarchical' => true, 'echo' => false, 'title_li' => '' ) );
		$this->disable_link_filter();
		return $pages;
	}

	public function render_listing_page() {
		if ( isset( $_GET['document'] ) ) : ?>
			<style>
			div#cws-wp-help-listing .page-item-<?php echo absint( $_GET['document'] ); ?> a {
				font-weight: bold;
			}
			</style>
		<?php endif; ?>
<div class="wrap">
	<?php screen_icon(); ?><h2><?php _e( 'Publishing Help' ); ?></h2>
<?php $pages = $this->get_help_topics_html(); ?>
<?php if ( trim( $pages ) ) : ?>
<div id="cws-wp-help-listing">
<h3><?php _e( 'Help Topics' ); ?><?php if ( current_user_can( 'publish_pages' ) ) : ?><span><a href="<?php echo admin_url( 'edit.php?post_type=wp-help' ); ?>">Manage</a></span><?php endif; ?></h3>
<ul>
<?php echo $pages; ?>
</ul>
</div>
<div id="cws-wp-help-document">
<?php if ( $_GET['document'] ) : ?>
	<?php $document = new WP_Query( array( 'post_type' => 'wp-help', 'p' => absint( $_GET['document'] ) ) ); ?>
	<?php if ( $document->have_posts() ) : $document->the_post(); ?>
		<h2><?php the_title(); ?></h2>
		<?php the_content(); ?>
	<?php else : ?>
	<p><?php _e( 'The requested help document could not be found' ); ?>
	<?php endif; ?>
<?php endif; ?>
</div>
<?php else : ?>
	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<p><?php printf( __( 'No published help documents found. <a href="%s">Manage Help Documents</a>.' ), admin_url( 'edit.php?post_type=wp-help' ) ); ?></p>
	<?php else : ?>
		<p><?php _e( 'No help documents found. Contact the site administrator.' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
</div>
<?php
	}
}

new CWS_WP_Help_Plugin;
