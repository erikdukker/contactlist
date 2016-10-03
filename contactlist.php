<?php
/**
 * @package Contactlist
 */
/*
  Plugin Name: Contactlist
  Plugin URI: http://eduk.nl/
  Description: Contactlist share a central contactlist, loads of useful features: update reminders, html emailer, smart selection
  Version: 1.3.2
  Author: Erik Dukker
  Author URI: http://eduk.nl/
  License: GPL 3
  Text Domain: edcl
*/
/*
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('ABSPATH')) die('No direct access allowed');

define('EDCL_DIR', dirname(__FILE__));
define('EDCL_URL', plugins_url('', __FILE__));
require_once( EDCL_DIR.'/contr/functions.php' );
//
register_activation_hook( __FILE__, 'on_activation' );
register_deactivation_hook( __FILE__, 'on_deactivation' );
register_uninstall_hook( __FILE__ , 'on_uninstall' );
/**
 * Activation hook.
 */
function aboutcontent() {
    echo '<br>';
    echo __('Version:', 'edcl') . ' ' . '1.3.2' . '<br>';
    echo __('Released:', 'edcl') . ' ' . '2016/10/03' . '<br>';
    echo __( 'Documentation on www.contactlijst.nl (dutch only sorry)', 'edcl' ).'<br>';
    echo __( 'Time limited licencing', 'edcl' ).'<br>';

}
function on_activation() {
    global $wpdb;
    if ( ! current_user_can( 'activate_plugins' ) ) return;
    //load db / template / emails
    require_once( 'contr/clinitdb.php' );
    $init = new initdb;
    $init->loadrow('lt','clcc',__( 'compact contactlist', 'edcl' ));            // contactlist template
    $init->loadrow('lt','clfc',__( 'full contactlist', 'edcl' ));            // contactlist template
    $init->loadrow('lt','clrg',__( 'register only', 'edcl' ));            // contactlist template
    $init->loadrow('st','smch',__( 'email invite', 'edcl' ));           // standard mail template
    $init->loadrow('st','smcf',__( 'confirm register', 'edcl' ));
    $init->loadrow('ut','um1c',__( 'email 1 collumn', 'edcl' ));        // user mail template
    $init->loadrow('ut','um2c',__( 'email 2 collumns', 'edcl' ));
    $init->loadrow('se','set',__( 'settings', 'edcl' ));                // settings: tags template
    $init->loadrow('wt','css',__( 'css', 'edcl' ));                     // css template
}
// maak parameter transfer mogelijk
add_filter('query_vars', 'parameter_queryvars' );
function parameter_queryvars( $qvars )
{
    $qvars[] = 'ky';
    return $qvars;
}
/**
 * Deactivation hook.
 */
function on_deactivation()
{
    if (!current_user_can('activate_plugins')) return;
    global $wpdb;
}

function on_uninstall() {

}
// emailtype from plain text to html
add_filter( 'wp_mail_content_type', function( $content_type ) {
    return 'text/html';
});
include_once(EDCL_DIR."/contr/clcommon.php");
include_once(EDCL_DIR."/pages.php");


function selectcontact( ) {
    include ABSPATH . 'wp-content/plugins/contactlist/selectcontact.php';
    return $outsel;
}
add_shortcode( 'selectcontact', 'selectcontact' );

function contactform( $atts ) {
    $atts = shortcode_atts(
        array(
            'name' => 'none',
        ), $atts );
    $params = $atts;
    include  ABSPATH .'wp-content/plugins/contactlist/contactform.php';
    return $outform;
}
add_shortcode( 'contactform', 'contactform' );


load_plugin_textdomain( 'edcl', false, 'contactlist/languages' );

if(!isset($_SESSION)) { session_start(); }

function set_from() {
    global $wpdb;
    $cl = $wpdb->prefix . 'contactlist';
    $rwcu = $wpdb->get_row("select * from " . $cl . " where tp = 'cu'", ARRAY_A);
    $arrcu = json_decode($rwcu['tx'], TRUE);
    $reply = $arrcu['settings']['reply'];
    return $reply;
}
function set_from_name() {
    $name = __( 'contactlist', 'edcl' );
    return $name;
}
add_filter('wp_mail_from', 'set_from');
add_filter('wp_mail_from_name', 'set_from_name');

?>