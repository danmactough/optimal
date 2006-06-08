<?php
/*
Name: Optimal OPML Browser
Homepage: http://www.yabfog.com/wp/optimal/
Description: Renders valid OPML from any source in a tree-like view. Links to external OPML files as well as RSS, RDF, and Atom feeds are expanded in place.
Version: 0.4(beta)
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
// Instantiate the class
//
require_once('class.optimal.php');
$optimal = new optimal;

//
// Define some initial variables
//
//// Standard
$thisHost = $_SERVER['HTTP_HOST'];
$lastReferer = $_SERVER['HTTP_REFERER'];

//// If widget is passed via the query string, bypass the remainder of settings
if ($_GET['widget']) {
	$url = $optimal->url_decode_no_spaces($_GET['url']);
	$linkTarget = $_GET['linktarget'] ? $_GET['linktarget'] : '_blank'; // default to opening a new window
	$depth = $_GET['depth'] ? $_GET['depth'] : 0;
	$flForceJS = FALSE;
	$flForceRefresh = FALSE;
	$flIsNode = FALSE;
	$flNoHead = TRUE;
	$flStandalone = TRUE;
	$flIsWidget = TRUE;
} else {
	//// From query string
	$url = $optimal->url_decode_no_spaces($_GET['url']);
	$linkTarget = $_GET['linktarget'];
	if ('opml' == strtolower($_GET['node'])) {
		$nodeType = 'opml';
		} elseif ('rss' == strtolower($_GET['node'])) {
		$nodeType = 'rss';
	}
	$depth = $_GET['depth'];
	//
	// Set some flags
	//
	$flForceJS = ('1' == $_GET['jsinclude']) ? TRUE : FALSE;
	$flForceRefresh = ('1' == $_GET['refresh'] && strpos($lastReferer, $thisHost)) ? TRUE : FALSE;
	$flIsNode = isset($nodeType) ? TRUE : FALSE; // (strtolower($_GET['node']) == 'opml' || strtolower($_GET['node']) == 'rss') ? TRUE : FALSE;
	$flNoHead = ('1' == $_GET['nohead']) ? TRUE : FALSE;
	$flStandalone = ('1' == $_GET['standalone'])  ? TRUE : FALSE;
} // End of the widget bypass

//
// Program flow and logic
//
//// If this is a node inclusion, render it and terminate the script
if ($flIsNode && $url) {
	if ('opml' == $nodeType) {
		if ($flForceJS) {
			$optimal->printHeadJavaScript();
			$optimal->printNodeTreeCSS();
		}
		/*print renderXML($url, '', '', 'OPML', TRUE, $target);*/
		print $optimal->renderXML($url, '', array ('type' => 'OPML', 'flIsNode' => $flIsNode, 'linkTarget' => $linkTarget, 'flNoHead' => $flNoHead, 'depth' => $depth));
		exit;
	} elseif ('rss' == $nodeType) {
		/*print renderXML($url, '', 'rssNode', 'RSS', TRUE, $target);*/
		print $optimal->renderXML($url, '', array ('xslfile' => 'rssNode', 'type' => 'RSS', 'flIsNode' => $flIsNode, 'linkTarget' => $linkTarget, 'flNoHead' => $flNoHead, 'depth' => $depth));
		exit;
	}
}

//
//// Mixed HTML, CSS and logic to render the page
//
// HTTP Headers
//
header("Content-type: text/html; charset=UTF-8");

if ($flForceRefresh) {
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

<?php 

	$optimal->printHeadJavaScript();
	
	if (!$flStandalone) { ?>
	<style type="text/css">
	<!--
	* { /* Global Reset */
	font-size: 100.01%;  margin: 0; padding: 0;
	}
	
	html {
	/* Reset 1em to 13px */ /* 13px / 16px = .8125 */
	font-size: 81.25%;
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	}
	
	body {
	margin: 1ex 1em;
	}
	
	a:link, a:visited {
	text-decoration: none;
	}
	
	a:link {
	color: blue;
	}
	
	a:link:hover, a:visited:hover{
	text-decoration: underline;
	}
	
	img {
	border: none;
	text-decoration: none;
	}

	h1 { font-size: 2.0em; font-weight: bold; margin: .25ex auto; }
	
	h1 a:link, h1 a:visited, h1 a:hover, h1 a:link:hover {
	color: black;
	}
	
	h2 { font-size: 1.8em; font-weight: bold; color: #333; margin: .25ex auto; }
	
	h3 { font-size: 1.4em; font-weight: bold; color: #444; margin: .25ex auto; }
	
	h4 { font-size: 1.0em; font-weight: bold;  }
	
	table.formWrapper {
		width: 36em;
	}
	td.formElement{
		vertical-align: middle;
	}
	form.obForm {
		width: 100%;
		margin: 1ex auto;
		padding: 4px 0;
		border-bottom: 1px solid #666;
	}
	-->
	</style><?php
		echo "\n";
	}
	
	if ($flIsWidget) { ?>
	<style type="text/css">
	<!--
	* { /* Global Reset */
	font-size: 100.01%;  margin: 0; padding: 0;
	}
	
	html {
	/* Reset 1em to 11px */
	font-size: 68.75%;
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	}
	
	body {
	margin: -3ex 0.5em 0 0;
	padding-top: 2px;
	}
	
	a:link, a:visited {
	text-decoration: none;
	}
	
	a:link {
	color: blue;
	}
	
	a:link:hover, a:visited:hover{
	text-decoration: underline;
	}
	
	img {
	border: none;
	text-decoration: none;
	}
	-->
	</style><?php
		echo "\n";
	
	}

	$optimal->printNodeTreeCSS();

	?>
<title><?php echo $url ? "Optimal &raquo; ".$url : 'Optimal'; ?></title>
</head>
<body>
<?php

	if (!$flStandalone) {
	
	if (file_exists('optimal.bodyintro.inc'))
		include_once('optimal.bodyintro.inc');
	?>
	
<form class="obForm" action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="GET">
  <table class="formWrapper">
  	<tr>
    <td class="formElement" style="width: 6em;">OPML URL:</td>
    <td class="formElement" colspan="2" style="width: 24em;"><input style="border: 1px solid #999; padding-left: 1px; width: 23em;" type="text" name="url" value="<?php print htmlspecialchars($url) ?>"></td>
    <td class="formElement" style="text-align: center;"><input style="border: 1px outset #ccc; padding: 1px; background: #ddd;" type="submit" name="submit" value="Submit"></td>
	</tr>
	<tr>
	<td class="formElement" colspan="3">&nbsp;</td>
	<td class="formElement" style="text-align: center; padding-top: 1ex;"><span style="font-size: 85%;">Standalone<br /><input type="checkbox" name="standalone" value="1"/></span></td>
	</tr>
	<tr>
      <td class="formElement" colspan="2"><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?url=http://hosting.opml.org/yabfog/dailyReading.opml" title="Click here to render a sample OPML file">Render Sample OPML</a></td>
      <td class="formElement" colspan="2" style="text-align: right; height: 4ex; vertical-align: bottom;">
        <a href="http://www.scripting.com/" target="_blank"><img src="img/thanksdave.gif" alt="Thanks, Dave!" title="Thanks, Dave!"></a>
      </td>
    </tr>
	<tr>
      <td class="formElement" colspan="4"><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?url=<?php echo htmlspecialchars(urlencode($url))?>&amp;refresh=1" title="Click here to refresh the current view">Refresh</a></td>
	</tr>
  </table>
</form>

<?php
	}

	if ($url) {
		print $optimal->genAllExpandCollapse();

		if ($flForceRefresh) {
			echo "<!-- Forced rendering from remote server -->\n";
			print $optimal->renderXML($url, $flForceRefresh, array ('type' => 'OPML', 'linkTarget' => $linkTarget, 'flNoHead' => $flNoHead, 'depth' => $depth));
		} else {
			print $optimal->renderXML($url, '', array ('type' => 'OPML', 'linkTarget' => $linkTarget, 'flNoHead' => $flNoHead, 'depth' => $depth));
		}
	}
?>
</body>
</html>
