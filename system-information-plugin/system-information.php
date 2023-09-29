<?php
/**
 * Plugin Name: Your system setup information
 * Plugin URI: https://github.com/perffectolt/wordpress-system-information
 * Description: A plugin to display system information.
 * Version: 1.0
 * Author: PERFFECTO.LT
 * Author URI: https://www.perffecto.lt
 * License: GPL2v2
 */

defined('ABSPATH') or die('Direct script access disallowed.');

define('SI_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once(SI_PLUGIN_PATH . 'inc/class-system-information.php');
