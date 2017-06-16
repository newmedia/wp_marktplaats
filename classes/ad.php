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

class ad {
    public $image       = NULL;
    public $title       = NULL;
    public $description = NULL;
    public $price       = NULL;
    public $views       = 0;
    public $location    = NULL;
    public $date        = NULL;
    public $link        = NULL;
}
