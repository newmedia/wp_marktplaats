<?php
/*
    Plugin Name: WP Marktplaats
    Author URI: http://www.newmedia.nl
    Plugin URI: http://projects.newmedia.nl/projects/wpmarktplaats/wiki
    Description: Show marktplaats adverts in sidebar or pages using widget or tag
    Author: Stefan Verstege

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

class curl {
    private $curl = NULL;
    private $url  = NULL;
    
    /**
     * Constructor
     * 
     * @param string $url 
     */
    public function  __construct($url) {
        // Check if curl extension is loaded
        if ( !function_exists('curl_init') ) {
            throw new Exception('Curl extension is needed', 99);
        }
        
        // Check if url is valid
        $this->url = $url;
        
        // Setup curl
        $curl = curl_init();        
        curl_setopt($curl, CURLOPT_URL,            $url);
        curl_setopt($curl, CURLOPT_HTTP_VERSION,   '1.0');
        curl_setopt($curl, CURLOPT_USERAGENT,      'WP Markplaats Transporter');
        curl_setopt($curl, CURLOPT_HTTPHEADER,     array('Content-type: text/html'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT,        60);        
        
        // Remember the curl object
        $this->curl = $curl;
    }
    
    
    /**
     * Fetch the page
     * 
     */
    public function fetch() {
        // Fetch page
        $result = curl_exec($this->curl);

        // Check results
        if ( empty($result) ) {
            throw new Exception('Unable to get page from marktplaats');
        }

        return $result;
    }
}

