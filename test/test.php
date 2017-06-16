<?php

// If you want to test, uncomment the following lines
require_once('../classes/marktplaats.php');
require_once('../classes/ad.php');
require_once('../classes/curl.php');
require_once('../classes/rss.php');

// Two test cases
$options[0] = array(
                'title' => 'Marktplaats advertenties',
                'amount' => 5,
                'userid' => '991318'
            );
$options[1] = array(
                'title' => 'Marktplaats advertenties',
                'amount' => 5,
                'userid' => '9693987'
            );

// Display the contents
try {
    foreach ( $options as $option ) {
        $mp = new Markplaats((int) $option['userid'],
                             (int) $option['amount']);
        $mp->run();
        $mp->show();
    }
} catch ( Exception $e ) {
    echo '<b>Error:</b>' . $e->getMessage();
}