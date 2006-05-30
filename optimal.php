<?php
/*
Name: Optimal OPML Browser
Homepage: http://www.yabfog.com/wp/optimal/
Description: Renders valid OPML from any source in a tree-like view. Links to external OPML files as well as RSS, RDF, and Atom feeds are expanded in place.
Version: 0.2a(beta)
Author: Dan MacTough
Author URI: http://www.yabfog.com/
License: GPL

Copyright (C) 2006 Dan MacTough

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

//
// Bring in the functions
//
require_once('optimal_functions.php.inc');
if ($_GET['url']) {
	$url = urlProper($_GET['url']);
	if (strtolower($_GET['node']) == 'opml') {
		if ($_GET['jsinclude'] == '1') {
			headJS();
		}
		renderXML($url, '', '', 'OPML', TRUE);
		exit;
	} elseif (strtolower($_GET['node']) == 'rss') {
		renderXML($url, '', 'rssNode', 'RSS');
		exit;
	}
}
//
// HTTP Headers
//
header("Content-type: text/html; charset=UTF-8");
$thisHost = $_SERVER['HTTP_HOST'];
$lastReferer = $_SERVER['HTTP_REFERER'];

if ($_GET['refresh'] == '1' && strpos($lastReferer, $thisHost)) {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}


//
// The page
//
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="author" content="Dan MacTough">
<meta name="keywords" content="OPML opml browser">

<?php headJS(); ?>

<style type="text/css">
<!--
@import url(http://<?php echo $_SERVER['SERVER_NAME'] ?>/css/optimal.css);
-->
</style>

<?php if ($_GET['standalone'] != '1') { headCSS(); } ?>

<title><?php echo $url ? "Optimal &raquo; ".$url : 'Optimal'; ?></title>
</head>
<body>
<?php
if ($_GET['standalone'] == '1' && $url) {
	if ($_GET['refresh'] == '1' && strpos($lastReferer, $thisHost)) {
		echo "<!-- Forced rendering from remote server -->\n";
    	renderXML($url, $forceRefresh = '1');
	} else {
    	renderXML($url);
	}
} else { ?>

<h1><a href="http://www.optimalbrowser.com/">Optimal</a></h1>
<h3>An OPML Browser</h3>
<!--
	By Dan MacTough - www.yabfog.com
-->
<h4><a href="http://www.yabfog.com/wp/optimal/" title="About Optimal">About Optimal</a> - <a href="http://www.yabfog.com/wp/optimal/#download" title="Download Optimal">Download Optimal</a> 
- <a href="javascript:location.href='http://www.optimalbrowser.com/?url='+location.href" alt="Open in Optimal Bookmarklet" title="Open in Optimal Bookmarklet">Open in Optimal Bookmarklet</a></h4>
<?php

	printForm();

				//
				// This is some debugging code
				//
				/*
				echo "<!-- ";
				print_r($_SERVER);
				print_r($_GET);
				echo "\n-->\n";
				*/
				//
				// End of debugging code
				//

	if ($url) {
		echo '<a href="javascript:history.back();">&#171; Go Back</a><br />'."\n";
		if ($_GET['refresh'] == '1' && strpos($lastReferer, $thisHost)) {
			echo "<!-- Forced rendering from remote server -->\n";
			renderXML($url, $forceRefresh = '1');
		} else {
			renderXML($url);
		}
	}
}
?>

</body>
</html>
