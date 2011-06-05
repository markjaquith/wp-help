<?php
/*
Plugin Name: WP Help
Description: Administrators can create detailed, hierarchical documentation for the site's authors and editors, viewable in the WordPress admin.
Version: 0.2
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
Text Domain: wp-help
*/

class CWS_WP_Help_Plugin {
	public static $instance;
	const default_doc = 'cws_wp_help_default_doc';

	public function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Translations
		load_plugin_textdomain( 'wp-help', false, basename( dirname( __FILE__ ) ) . '/i18n' );

		// Actions and filters
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'do_meta_boxes', array( $this, 'do_meta_boxes' ), 20, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_filter( 'post_type_link', array( $this, 'page_link' ), 10, 2 );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'admin_init', array( $this, 'ajax_listener' ) );

		// Register the wp-help post type
		register_post_type( 'wp-help',
			array(
				'label' => _x( 'Publishing Help', 'post type label', 'wp-help' ),
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
					'name' => __( 'Help Documents', 'wp-help' ),
					'singular_name' => __( 'Help Document', 'wp-help' ),
					'add_new' => _x( 'Add New', 'i.e. Add new Help Document', 'wp-help' ),
					'add_new_item' => __( 'Add New Help Document', 'wp-help' ),
					'edit' => _x( 'Edit', 'i.e. Edit Help Document', 'wp-help' ),
					'edit_item' => __( 'Edit Help Document', 'wp-help' ),
					'new_item' => __( 'New Help Document', 'wp-help' ),
					'view' => _x( 'View', 'i.e. View Help Document', 'wp-help' ),
					'view_item' => __( 'View Help Document', 'wp-help' ),
					'search_items' => __( 'Search Documents', 'wp-help' ),
					'not_found' => __( 'No Help Documents Found', 'wp-help' ),
					'not_found_in_trash' => __( 'No Help Documents found in Trash', 'wp-help' ),
					'parent' => __( 'Parent Help Document', 'wp-help' )
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
			// Now we make our post type public, and initiate a query filter
			// There really should be a better way to do this. :-\
			add_filter( 'pre_get_posts', array( $this, 'only_query_help_docs' ) );
			global $wp_post_types;
			$wp_post_types['wp-help']->publicly_queryable = $wp_post_types['wp-help']->public = true;
		}
	}

	public function only_query_help_docs( $q ) {
		$q->set( 'post_type', 'wp-help' );
	}

	public function admin_menu() {
		$hook = add_dashboard_page( _x( 'Publishing Help', 'page title', 'wp-help' ), _x( 'Publishing Help', 'menu title', 'wp-help' ), 'publish_posts', 'wp-help-documents', array( $this, 'render_listing_page' ) );
		add_action( "load-{$hook}", array( $this, 'enqueue' ) );
	}

	public function do_meta_boxes( $page, $context ) {
		if ( 'wp-help' == $page && 'side' == $context )
			add_meta_box( 'cws-wp-help-meta', _x( 'WP Help Options', 'meta box title', 'wp-help' ), array( $this, 'meta_box' ), $page, 'side' );
	}

	public function meta_box() {
		global $post;
		wp_nonce_field( 'cws-wp-help-save', '_cws_wp_help_nonce', false, true ); ?>
		<p><input type="checkbox" name="cws_wp_help_make_default_doc" id="cws_wp_help_make_default_doc" <?php checked( $post->ID == get_option( self::default_doc ) ); ?> /> <label for="cws_wp_help_make_default_doc"><?php _e( 'Make this the default help document', 'wp-help' ); ?></label></p>
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
		$permalink = get_permalink( $post_ID );
		$messages['wp-help'] = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => sprintf( __( 'Document updated. <a href="%s">View document</a>', 'wp-help' ), esc_url( $permalink ) ),
			 2 => __( 'Custom field updated.', 'wp-help' ),
			 3 => __( 'Custom field deleted.', 'wp-help' ),
			 4 => __( 'Document updated.', 'wp-help' ),
			 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Document restored to revision from %s', 'wp-help' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => sprintf( __( 'Document published. <a href="%s">View document</a>', 'wp-help' ), esc_url( $permalink ) ),
			 7 => __( 'Document saved.', 'wp-help' ),
			 8 => sprintf( __( 'Document submitted. <a target="_blank" href="%s">Preview document</a>', 'wp-help' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
			 9 => sprintf( __( 'Document scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview document</a>', 'wp-help' ), date_i18n( __( 'M j, Y @ G:i', 'wp-help' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
			10 => sprintf( __('Document draft updated. <a target="_blank" href="%s">Preview document</a>', 'wp-help' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		);
		return $messages;
	}

	public function enqueue() {
		$suffix = defined ('SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '';
		wp_enqueue_style( 'cws-wp-help', plugins_url( "css/wp-help$suffix.css", __FILE__ ), array(), '20110518b' );
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
		$document_id = absint( isset( $_GET['document'] ) ? $_GET['document'] : get_option( self::default_doc ) );
		if ( $document_id ) : ?>
			<style>
			div#cws-wp-help-listing .page-item-<?php echo $document_id; ?> > a {
				font-weight: bold;
			}
			</style>
		<?php endif; ?>
<div class="wrap">
	<?php screen_icon(); ?><h2><?php _ex( 'Publishing Help', 'h2 title', 'wp-help' ); ?></h2>
<?php $pages = $this->get_help_topics_html(); ?>
<?php if ( trim( $pages ) ) : ?>
<div id="cws-wp-help-listing">
<h3><?php _e( 'Help Topics', 'wp-help' ); ?><?php if ( current_user_can( 'publish_pages' ) ) : ?><span><a href="<?php echo admin_url( 'edit.php?post_type=wp-help' ); ?>"><?php _ex( 'Manage', 'verb. Button with limited space', 'wp-help' ); ?></a></span><?php endif; ?></h3>
<ul>
<?php echo $pages; ?>
</ul>
</div>
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
</div>
<?php
	}
}

new CWS_WP_Help_Plugin;
