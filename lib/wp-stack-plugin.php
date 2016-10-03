<?php
defined( 'WPINC' ) or die;

// Convenience methods
if ( !class_exists( 'WP_Stack_Plugin2' ) ) {
	class WP_Stack_Plugin2 {
		protected static $instance;
		protected $__FILE__;

		/**
		 * Blank protected constructor
		 */
		protected function __construct() {}

		/**
		 * Initializes the plugin object and returns its instance
		 *
		 * @param string $__FILE__ the main plugin file's __FILE__ value
		 * @return object the plugin object instance
		 */
		public static function start( $__FILE__ ) {
			if ( ! isset( static::$instance ) ) {
				static::$instance = new static();
				static::$instance->__FILE__ = $__FILE__;
			}
			return static::get_instance();
		}

		/**
		 * Returns the plugin's object instance
		 *
		 * @return object the plugin object instance
		 */
		public static function get_instance() {
			if ( isset( static::$instance ) ) {
				return static::$instance;
			}
		}

		/**
		 * Add a WordPress hook (action/filter)
		 *
		 * @param  mixed $hook,... first parameter is the name of the hook. If second or third parameters are included, they will be used as a priority (if an integer) or as a class method callback name (if a string)
		 */
		public function hook( $hook ) {
			$priority = 10;
			$method = $this->sanitize_method( $hook );
			$args = func_get_args();
			unset( $args[0] );
			foreach( (array) $args as $arg ) {
				if ( is_int( $arg ) )
					$priority = $arg;
				else
					$method = $arg;
			}
			return add_action( $hook, array( $this, $method ), $priority, 999 );
		}

		private function sanitize_method( $method ) {
			return str_replace( array( '.', '-' ), array( '_DOT_', '_DASH_' ), $method );
		}

		/**
		 * Includes a file (relative to the plugin base path)
		 * and optionally globalizes a named array passed in
		 *
		 * @param  string $file the file to include
		 * @param  array  $data a named array of data to globalize
		 */
		protected function include_file( $file, $data = array() ) {
			extract( $data, EXTR_SKIP );
			include( $this->get_path() . $file );
		}

		/**
		 * Returns the URL to the plugin directory
		 *
		 * @return string the URL to the plugin directory
		 */
		public function get_url() {
			return plugin_dir_url( $this->__FILE__ );
		}

		/**
		 * Returns the path to the plugin directory
		 *
		 * @return string the absolute path to the plugin directory
		 */
		public function get_path() {
			return plugin_dir_path( $this->__FILE__ );
		}

		public function load_textdomain( $name, $path ) {
				return load_plugin_textdomain( $name, false, basename( dirname( $this->__FILE__ ) ) . $path );
		}
	}
}
