<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Wc_Rabbitmq
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// <div class="wr-address">
// 	<div class="wr-add-contents add-new-addr">
// 		<button id="addnew-addr-btn">
// 			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" fill="#157abd" width="50px" height="50px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000" xml:space="preserve">
// 			<metadata> Svg Vector Icons : http://www.onlinewebfonts.com/icon </metadata>
// 			<g><g><path d="M500,10C229.4,10,10,229.4,10,500c0,270.6,219.4,490,490,490c270.6,0,490-219.4,490-490C990,229.4,770.6,10,500,10z M500,867.5c-202.7,0-367.5-164.8-367.5-367.5S297.3,132.5,500,132.5S867.5,297.3,867.5,500S702.7,867.5,500,867.5z"/><path d="M684.3,438.8H561.3V315.7c0-33.5-27.2-60.7-60.7-60.7h-1.2c-33.5,0-60.7,27.2-60.7,60.7v123.1H315.7c-33.5,0-60.7,27.2-60.7,60.7v1.2c0,33.5,27.2,60.7,60.7,60.7h123.1v123.1c0,33.5,27.2,60.7,60.7,60.7h1.2c33.5,0,60.7-27.2,60.7-60.7V561.2h123.1c33.5,0,60.7-27.2,60.7-60.7v-1.2C745,465.9,717.8,438.8,684.3,438.8L684.3,438.8z"/></g></g>
// 			</svg>
// 		</button>
// 	</div>
// </div>