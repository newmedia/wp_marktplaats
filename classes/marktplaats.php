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

define('RSS', 1);
define('HTML', 0);

class Markplaats {
    private $userid     = 0;
    private $amount     = 5;
    private $parsetype  = RSS; // 0 = html scraping, 1 = rss parsing
    private $url        = "http://verkopers.marktplaats.nl/";
    private $rssurl     = "http://rss.marktplaats.nl/opensearch.php?ui=";
    private $link       = "http://link.marktplaats.nl/";
    private $webpage    = NULL;
    private $adlist     = NULL;
    private $regexp     = array();
    private $ads        = array();

    /**
     * Constructor
     *
     * @param integer $userid
     * @param integer $amount
     */
    public function  __construct($userid, $amount) {
        // Check!
        if ( !is_int($userid) ) {
            throw new Exception('Userid must be an integer');
        }
        if ( !is_int($amount) ) {
            throw new Exception('Amount must be an integer');
        }

        //Setup
        $this->userid = $userid;
        $this->amount = $amount;

        // Setup regexp array for the complete application;
        if ( $this->parsetype == HTML) {
            $this->regexp[0]['adlist']          = '/Header ad list(.+?)Footer ad list/is';
            $this->regexp[1]['odd']             = '/\<tr id="AD\\d+" class="rowOdd">(.*?)\<(tr id=|\/table>.?<script lan)/ism';
            $this->regexp[1]['even']            = '/\<tr id="AD\\d+" class="rowEven">(.*?)\<(tr id=|\/table>.?<script lan)/ism';
            $this->regexp[2]['title'][0]        = '/\<(span|b)>(.*?)\<\/(span>\<\/a>\<\/h3>|b>.?\<script)/is';
            $this->regexp[2]['title'][1]        = 2;
            $this->regexp[2]['image'][0]        = '/\<img class="thumbnail" src="(.*?)" align="top"/is';
            $this->regexp[2]['image'][1]        = 1;
            $this->regexp[2]['description'][0]  = '/\<small>(.*?)\<\/small>\<\/span>/is';
            $this->regexp[2]['description'][1]  = 1;
            $this->regexp[2]['description'][2]  = 'html_entity_decode';
            $this->regexp[2]['price'][0]        = '/align="right">&nbsp;(.*?)\<\/div>/is';
            $this->regexp[2]['price'][1]        = 1;
            $this->regexp[2]['price'][2]        = 'html_entity_decode';
            $this->regexp[2]['date'][0]         = '/\<td width="76".*?">(.*?)\<\/div>/is';
            $this->regexp[2]['date'][1]         = 1;
            $this->regexp[2]['views'][0]        = '/\<td width="43".*?center">(.*?)\<\/div>/is';
            $this->regexp[2]['views'][1]        = 1;
            $this->regexp[2]['location'][0]     = '/\<td width="155".*?">(.*?)\<\/div>/is';
            $this->regexp[2]['location'][1]     = 1;
            $this->regexp[2]['link'][0]         = '/id="ada_(.*)"(><span>| onclick=)/is';
            $this->regexp[2]['link'][1]         = 1;
        }
    }


    /**
     *  Fetch the webpage
     *
     */
    private function fetch() {
        // Construct the URL
        if ( $this->parsetype == RSS) {
            $url = $this->rssurl . $this->userid;
        } else {
            $url = $this->url . $this->userid;
        }
        $curl = new curl($url);

        // Store the results
        $this->webpage = $curl->fetch();
    }


    /**
     * Parse the webpage for the adlist
     */
    private function parseWebpage() {
        $result = preg_match($this->regexp[0]['adlist'], $this->webpage, $adlist);

        // Check the results
        if ( $result === FALSE || empty($adlist[1]) ) {
            throw new Exception('Unable to parse advert list from markplaats
                                 page for userid ' . $this->userid);
        }

        // Remeber first parse
        $adlist = $adlist[1];

        // Find the ads
        $ads = array();
        foreach( $this->regexp[1] as $name=>$regexp ) {
            $result = preg_match_all($regexp, $adlist, $found);
            $ads    = array_merge($ads, $found[0]);
        }

        // Remember!
        $this->adlist = $ads;
    }


    /**
     * Generate ads from the marktplaats ads
     *
     */
    private function parseAds() {
        $ads    = array();
        $adlist = $this->adlist;

        // Loop over each ad
        foreach( $adlist as $ad ) {
            // Create new ad object
            $ad_object = new Ad();

            // Run all regexp
            foreach( $this->regexp[2] as $name=>$item) {
                $regexp      = $item[0];
                $fieldnumber = $item[1];
                $result = preg_match($regexp, $ad, $found);

                // Remove some cruft
                $result = $found[$fieldnumber];
                $result = str_replace('€', '&euro;', $result);
                $result = str_replace('�', '&euro;', $result);

                if ( array_key_exists(2, $item) ) {
                    $ad_object->$name = $item[2]($result);
                } else {
                    $ad_object->$name = $result;
                }
            }

            // Remember!
            $this->ads[] = $ad_object;
        }
    }


    /**
     * Generate ads from the marktplaats RSS feed
     *
     */
    private function parseRssToAds() {
        $ads     = array();
        $rssfeed = simplexml_load_string($this->webpage, $xmlobject, LIBXML_NOCDATA);

        // Loop over each ad
        foreach( $rssfeed->channel->item as $ad ) {
            // Some work todo!
            $title  = str_replace('Aangeboden: ', '', $ad->title);
            $result = preg_match('/\<img src="(.*?)" align="absmiddle"/is', $ad->description, $found);
            $image  = $found[1];
            $result = preg_match('/\- Prijs: (.*?) \-/is', $ad->description, $found);
            $price  = $found[1];

            // Create new ad object
            $ad_object = new Ad();
            $ad_object->date = $ad->pubDate;
            $ad_object->title = $title;
            $ad_object->image = $image;
            $ad_object->link = $ad->link;
            $ad_object->price = $price;
            $ad_object->description = $ad->description;

            // Remember!
            $this->ads[] = $ad_object;
        }
    }


    /**
     * Run everything we need to do to get an ad list from marktplaats
     *
     * @return array
     */
    public function run() {
        try {
            $this->fetch();

            if ( $this->parsetype == HTML ) {
                $this->parseWebpage();
                $this->parseAds();
            } else {
                $this->parseRssToAds();
            }
        } catch ( Exception $e ) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }


    private function generateAdList() {
        // Show the ads
        $i       = 0;
	$adblock = NULL;
        do {
            $ad = $this->ads[$i];

	    // Construct the correct link
            if ( $this->parsetype == HTML ) {
                $link = $this->link . $ad->link;
            } else {
                $link = $ad->link;
            }

	    // Construct a nicer title
	    $title = $ad->title;
 	    if ( strlen($title) > 100 ) {
		$title = substr($title,0, 100) . '...';
	    }

            $adblock .= 
               '<div id="ad-'.$i.'" class="wp_advert" onclick="window.open(\''.$link.'\')">
                <span class="image"><img src="'.$ad->image.'" alt="'.$title.'" width="50" /></span>
                <span class="title">'.$title.'</span>
                <span class="price">'.$ad->price.'</span>
                </div>';
            $i++;
        } while ( $i < $this->amount && $i < count($this->ads) );

	return $adblock;
    }


    /**
     * Show the adlist from cache or generate a new cache item
     */
    public function show() {
	// Check the wp_cache files
        if ( function_exists(wp_cache_get) ) {
            $adblock = wp_cache_get('wp_marktplaats');
        }

	// Not found ?
	if ( $adblock == false ) {
		// Generate new one
		$adblock = $this->generateAdList();

		// And put it in the cache!
                if ( function_exists(wp_cache_set) ) {
                    wp_cache_set('wp_marktplaats', $adblock);
                }
	}

	// Display adblock (from cache or generated)
	echo $adblock;
    }
}
