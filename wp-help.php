<?php
/*
Plugin Name: WP Help
Description: Administrators can create detailed, hierarchical documentation for the site's authors and editors, viewable in the WordPress admin.
Version: 1.5.3
License: GPL
Plugin URI: http://txfx.net/wordpress-plugins/wp-help/
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
Text Domain: wp-help
Domain Path: /languages
*/

/**
 * Copyright (c) 2011-2016 Mark Jaquith (email : mark@jaquith.me)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'WPINC' ) or die;

include( dirname( __FILE__ ) . '/lib/requirements-check.php' );

$cws_wp_help_requirements_check = new CWS_WP_Help_Requirements_Check( array(
	'title' => 'WP Help',
	'php'   => '5.3',
	'wp'    => '4.7',
	'file'  => __FILE__,
));

if ( $cws_wp_help_requirements_check->passes() ) {
	// Pull in the plugin classes and initialize
	include( dirname( __FILE__ ) . '/lib/wp-stack-plugin.php' );
	include( dirname( __FILE__ ) . '/classes/plugin.php' );
	CWS_WP_Help_Plugin::start( __FILE__ );
}

unset( $cws_wp_help_requirements_check );
