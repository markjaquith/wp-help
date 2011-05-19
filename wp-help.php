<?php
/*
Plugin Name: WP Help
Description: Administrators can create detailed, hierarchical documentation for the site's authors and editors, viewable in the WordPress admin.
Version: 0.1
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

class CWS_WP_Help_Plugin {
	public static $instance;
	const default_doc = 'cws_wp_help_default_doc';

	public function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ), 20, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_filter( 'post_type_link', array( $this, 'page_link' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'admin_init', array( $this, 'ajax_listener' ) );
		register_post_type( 'wp-help',
			array(
				'label' => __( 'Publishing Help' ),
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
					'name' => __( 'Help Documents' ),
					'singular_name' => __( 'Help Document' ),
					'add_new' => __( 'Add New' ),
					'add_new_item' => __( 'Add New Help Document' ),
					'edit' => __( 'Edit' ),
					'edit_item' => __( 'Edit Help Document' ),
					'new_item' => __( 'New Help Document' ),
					'view' => __( 'View' ),
					'view_item' => __( 'View Help Document' ),
					'search_items' => __( 'Search Documents' ),
					'not_found' => __( 'No Help Documents Found' ),
					'not_found_in_trash' => __( 'No Help Documents found in Trash' ),
					'parent' => __( 'Parent Help Document' )
				)
			)
		);
	}

	public function ajax_listener() {
		if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX || !isset( $_POST['action'] ) || 'wp-link-ajax' != $_POST['action'] )
			return;
		// It's the right kind of request
		// Now to see if it originated from our post type
		$qs = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
		wp_parse_str( $qs, $vars );
		if ( isset( $vars['post_type'] ) ) {
			$post_type = $vars['post_type'];
		} elseif ( isset( $vars['post'] ) ) {
			$post = get_post( $vars['post'] );
			$post_type = $post->post_type;
		} else {
			// Can't determine post type. Bail.
			return;
		}
		if ( 'wp-help' == $post_type ) {
			// Nice! This originated from our post type
			// Now we make our post type public, and all others non-public
			// There really should be a better way to do this. :-\
			global $wp_post_types;
			foreach ( $wp_post_types as $name => $type ) {
				$wp_post_types[$name]->publicly_queryable = false;
			}
			$wp_post_types['wp-help']->publicly_queryable = true;
		}
	}

	public function admin_menu() {
		$hook = add_dashboard_page( __( 'Publishing Help' ), __( 'Publishing Help' ), 'publish_posts', 'wp-help-documents', array( $this, 'render_listing_page' ) );
		add_action( "load-{$hook}", array( $this, 'enqueue' ) );
	}

	public function do_meta_boxes( $page, $context ) {
		if ( 'wp-help' == $page && 'side' == $context )
			add_meta_box( 'cws-wp-help-meta', __( 'WP Help Options' ), array( $this, 'meta_box' ), $page, 'side' );
	}

	public function meta_box() {
		global $post;
		wp_nonce_field( 'cws-wp-help-save', '_cws_wp_help_nonce', false, true ); ?>
		<p><input type="checkbox" name="cws_wp_help_make_default_doc" id="cws_wp_help_make_default_doc" <?php checked( $post->ID == get_option( self::default_doc ) ); ?> /> <label for="cws_wp_help_make_default_doc"><?php _e( 'Make this the default help document' ); ?></label></p>
		<?php
	}

	public function save_post( $post_id ) {
		if ( wp_verify_nonce( $_POST['_cws_wp_help_nonce'], 'cws-wp-help-save' ) ) {
			if ( isset( $_POST['cws_wp_help_make_default_doc'] ) ) {
				// Make it the default_doc
				update_option( self::default_doc, absint( $post_id ) );
			} elseif ( $post_id == get_option( self::default_doc ) ) {
				// Unset
				update_option( self::default_doc, 0 );
			}
		}
		return $post_id;
	}

	public function post_updated_messages( $messages ) {
		global $post_ID, $post;
		$messages['wp-help'] = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __('Document updated. <a href="%s">View document</a>'), esc_url( get_permalink($post_ID) ) ),
			 2 => __('Custom field updated.'),
			 3 => __('Custom field deleted.'),
			 4 => __('Document updated.'),
			 5 => isset($_GET['revision']) ? sprintf( __('Document restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __('Document published. <a href="%s">View document</a>'), esc_url( get_permalink($post_ID) ) ),
			 7 => __('Document saved.'),
			 8 => sprintf( __('Document submitted. <a target="_blank" href="%s">Preview document</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			 9 => sprintf( __('Document scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview document</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Document draft updated. <a target="_blank" href="%s">Preview document</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);
		return $messages;
	}

	public function enqueue() {
		$suffix = defined ('SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';
		wp_enqueue_style( 'cws-wp-help', plugins_url( "wp-help$suffix.css", __FILE__ ), array(), '20110518b' );
	}

	public function page_link( $link, $post ) {
		$post = get_post( $post );
		if ( 'wp-help' == $post->post_type )
			return admin_url( 'index.php?page=wp-help-documents&document=' . absint( $post->ID ) );
		else
			return $link;
	}

	private function get_help_topics_html() {
		return wp_list_pages( array( 'post_type' => 'wp-help', 'hierarchical' => true, 'echo' => false, 'title_li' => '' ) );
	}

	public function render_listing_page() {
		if ( !isset( $_GET['document'] ) && get_option( self::default_doc ) )
			$_GET['document'] = get_option( self::default_doc );
		if ( isset( $_GET['document'] ) ) : ?>
			<style>
			div#cws-wp-help-listing .page-item-<?php echo absint( $_GET['document'] ); ?> > a {
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
