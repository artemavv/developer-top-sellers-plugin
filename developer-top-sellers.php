<?php

/*
Plugin Name: Top Selling Developers
Description: Gathers statistics about top selling developers and provides shortcode for developer list
Author: Artem Avvakumov
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Version: 0.1.2
*/

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once 'includes.php';


define( 'DTS_VERSION', '0.1.2' );
define( 'DTS_TEXT_DOMAIN', 'developer-top-sellers' );

$plugin_root = __FILE__;

register_activation_hook( $plugin_root, array('Dts_Plugin', 'install' ) );
register_deactivation_hook( $plugin_root, array('Dts_Plugin', 'uninstall' ) );

/**** Initialise Plugin ****/

$dts_plugin = new Dts_Plugin( $plugin_root );
