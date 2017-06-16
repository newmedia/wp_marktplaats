<?php
/*
    Plugin Name: WP Marktplaats
    Author URI: http://www.newmedia.nl
    Plugin URI: http://projects.newmedia.nl/projects/wpmarktplaats/wiki
    Description: Show marktplaats adverts in sidebar or pages using widget or tag
    Author: Stefan Verstege
    Version: 0.6.0

    CHANGELOG
    See readme.txt

    Copyright 2009  Stefan Verstege  (email : stefan.verstege@newmedia.nl)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('classes/marktplaats.php');
require_once('classes/ad.php');
require_once('classes/curl.php');


/**
 * Get wordpress options for this widgets/plugin
 *
 * @param string $widgetname
 * @return array
 */
function getOptions($widgetname) {
    // Fetch options for my widget
    $options = get_option($widgetname);

    // Check if options are filled
    // If not, return default settings
    if ( !is_array( $options ) ) {
        $options = array(
                'title' => 'Marktplaats advertenties',
                'amount' => 5,
                'userid' => ''
            );
    }

    return $options;
}


/**
 * Start the widget
 *
 * @param array $args
 */
function start_widget_marktplaats($args) {
    // Needed for the $before & $after tags
    extract($args);

    // Fetch the widget options
    $options = getOptions("wp_marktplaats");

    // Display the widget
    echo $before_widget;
    echo $before_title;
    echo $options['title'];
    echo $after_title;

    // Display the contents
    try {
        $mp = new Markplaats((int) $options['userid'],
                             (int) $options['amount']);
        $mp->run();
        $mp->show();
    } catch ( Exception $e ) {
        echo '<b>Error:</b>' . $e->getMessage();
    }

    // Close the widget
    echo $after_widget;
}


/**
 * Show widget control window
 */
function widget_control_marktplaats() {
    // Fetch the widget options
    $options = getOptions("wp_marktplaats");

    // Post?
    if ( isset($_POST['widget-Submit']) ) {
        $options['title'] = htmlspecialchars($_POST['widget-Title']);
        $options['amount'] = htmlspecialchars($_POST['widget-Amount']);
        $options['userid'] = htmlspecialchars($_POST['widget-Userid']);
        update_option("wp_marktplaats", $options);
    }

    // Show the control screen
    echo '<input type="hidden" id="widget-Submit" name="widget-Submit" value="1" />
          <p>
            <label for="widget-Title">Widget titel: </label><br />
            <input type="text" id="widget-Title" name="widget-Title" value="'.$options['title'].'" /></p>
          <p>
            <label for="widget-Userid">Gebruikerscode: </label>
            <input type="text" id="widget-Userid" name="widget-Userid" value="'.$options['userid'].'" size="8" maxlength="8" /></p>
          <p>
            <label for="widget-Amount">Aantal advertenties: </label>
            <input type="text" id="widget-Amount" name="widget-Amount" value="'.$options['amount'].'" size="2" maxlength="2" /></p>';
}


// The real deal!!

// Hook wp_head to add css
add_action('wp_head', 'widget_head_marktplaats');
function widget_head_marktplaats() {
    $dirs = explode('/', dirname(__FILE__));
    $path = $dirs[count($dirs)-1];
    $pluginpath = WP_PLUGIN_URL.'/'.$path.'/';
    echo '<link rel="stylesheet" type="text/css" media="screen" href="' . $pluginpath . 'style/marktplaats.css" />'."\n";
}


/**
 * Initialize the widget
 */
add_action('plugins_loaded', 'init_widget');
function init_widget(){
    register_sidebar_widget('wp_marktplaats', 'start_widget_marktplaats');
    register_widget_control('wp_marktplaats', 'widget_control_marktplaats', 200, 200 );
}


