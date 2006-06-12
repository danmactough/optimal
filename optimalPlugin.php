<?php

/*
Plugin Name: Optimal Plugin (formerly, OPML Renderer)
Plugin URI: http://www.yabfog.com/wp/optimal-plugin/
Description: Renders valid OPML from any source as an expandable/collapsible list. <em>Usage in code:</em> <code>OPMLRender('url','updatetime','css class','depth','flags');</code> <em>Usage in pages / posts:</em> <code>!OPMLRender : url,updatetime,css class,depth,flags</code> <em>where 'updatetime' is the number of seconds to cache a file before requesting an update, 'css class' indicates the CSS class to be applied to the &lt;div&gt; that wraps the rendered outline, 'depth' indicates how many levels to initially expand the outline (excluding inclusions), and 'flags' is the sum of the display flags you wish to set TRUE (currently, '1' = 'Print a header with links to Expand/Collapse all nodes' and '2' = 'Print a footer with a link to the source OPML file').</em>
Version: 0.4 (beta)
Author: Dan MacTough
Author URI: http://www.yabfog.com/
License: GPL

Optimal Plugin (formerly, OPML Renderer) - Renders valid OPML from any source as an expandable/collapsible list
Copyright (c) 2005-2006 Dan MacTough. All rights reserved.

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
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.


This is beta software, so please report any problems to
danmactough AT yahoo DOT com
*/

// This pulls in the Optimal functions
require_once('class.optimal.php');
$optimal_plugin_main = new optimal;

// This is a Wordpress content filter that replaces calls to OPMLContent
// Calls must be in the following form:
// !OPMLRender:url,updatetime,css class, depth
// Only the url is mandatory

function OPMLContent ($content = '') {
	global $optimal_plugin_main;
	$find[] = "%%";
	$replace[] = "";

	preg_match_all('/(?:<p>)?!OPMLRender:(.+)(?:<\/p>|\n<\/p>)?/', $content, $matches, PREG_SET_ORDER);
	// The phrase (?:<p>)? will catch and discard the <p> that gets inserted by 
	// the wpautop filter. This greatly helps us render XHTML valid hypertext
	// and should not have any unintended or adverse consequences ....
	foreach ($matches as $match) {
		$replacement = NULL;
		list($url, $maxAge, $presentation, $depth, $flags) = explode(",",strip_tags(rtrim($match[1])));
		
		if (!$maxAge) # Set a default of 14400 secods, or 4 hours
			$maxAge = 14400;
		if ('page' == $presentation) { # This is for some sort of backwards compatibility
			$presentation = 'opmlPage';
		} elseif ('sidebar' == $presentation) {
			$presentation = 'opmlSidebar';
		}
		if (NULL !== $depth) { # We've been passed a depth
			$depth = $depth;
		} else {
			$depth = 1;
		}
		if (NULL !== $flags) { # We've been passed some flags
			$flags = $flags;
		} else {
			$flags = 2;
		}
		$url = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $url);
		$url = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $url);
		$url = html_entity_decode($url);
		$find[] = "%". str_replace('?', '\?', $match[0]) ."%";
		if ($flags & 1)
			$replacement .= $optimal_plugin_main->genAllExpandCollapse();
		$replacement .= $optimal_plugin_main->renderXML($url, '', array( 'maxAge' => $maxAge, 'flNoHead' => TRUE, 'mainClass' => $presentation, 'depth' => $depth ) );
		if ($flags & 2)
			$replacement .= $optimal_plugin_main->genOutlineLink($url);
		$replace[] = $replacement;
	}

	return preg_replace($find, $replace, $content);

}

// This adds a PHP function you can call from your templates, e.g.,
// in sidebar.php you could create a sidebar item with the line
// < ?php OPMLRender(url, updatetime, presentation); ? >
// The default cache time is 14400 seconds, or 4 hours.
// The default XSL transform generates a list with all levels are collapsed.

function OPMLRender($url, $maxAge = '14400', $presentation = 'sidebar', $depth = 0, $flags = 2) {
	if ('sidebar' == $presentation) {
		$presentation = 'opmlSidebar';
	} elseif ('page' == $presentation) {
		$presentation = 'opmlPage';
	}	
	global $optimal_plugin_main;
	if ($flags & 1)
		print $optimal_plugin_main->genAllExpandCollapse();
	print $optimal_plugin_main->renderXML($url, '', array ( 'maxAge' => $maxAge, 'flNoHead' => TRUE, 'mainClass' => $presentation, 'depth' => $depth ) );
	if ($flags & 2)
		print $optimal_plugin_main->genOutlineLink($url);
}

function OPMLHead () {
	global $optimal_plugin_main;
	$optimal_plugin_main->printHeadJavaScript();
	$optimal_plugin_main->printNodeTreeCSS();
}

// This adds the filters to Wordpress.

add_filter('wp_head', 'OPMLHead');
add_filter('the_content', 'OPMLContent');

?>
