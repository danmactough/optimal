<?php
/*
Name: optimal_functions.php.inc
Homepage: http://www.yabfog.com/wp/optimal/
Description: A component of Optimal
Version: 0.4pre1(beta)
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
function headJS () {
	$root = 'http://'.$_SERVER['SERVER_NAME'];
	$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) ?
		str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) :
		'';
	$imgCollapsed = $root.$path."/img/imgCollapsed12x12.gif";
	$imgExpanded = $root.$path."/img/imgExpanded12x12.gif";

?>
<script type="text/javascript">
<!-- begin hiding from old browsers
	var imgCollapsed = "<?= $imgCollapsed ?>";
	var imgExpanded = "<?= $imgExpanded ?>";

	function expandAll () {
		var uls = document.getElementsByTagName('ul');
		for (var i=0;i<uls.length;i++) {
			if (uls[i].firstChild.className != 'outlineItemNodeSub') {
				uls[i].style.display = 'block';
				if (uls[i].className != 'main') {
					if (document.images["img-"+uls[i].id]) {
						document.images["img-"+uls[i].id].src=[imgExpanded];
					}
				}
			}
		}
	}

	function collapseAll () {
		var uls = document.getElementsByTagName('ul');
		for (var i=0;i<uls.length;i++) {
			if (uls[i].firstChild.className != 'outlineItemNodeSub' && uls[i].className != 'main') {
				uls[i].style.display = 'none';
				if (document.images["img-"+uls[i].id]) {
					document.images["img-"+uls[i].id].src=[imgCollapsed];
				}
			}
		}
	}

	function opmlRenderExCol(id, isNode, url) {
		if (isNode != null) {
			xmlhttpGetOpml (url, id);
		}
		if (document.getElementById) {
			var item = document.getElementById(id);
			var display = item.style.display;
			if (display == "none"){
				item.style.display = 'block';
				if (document.images["img-"+id]) {
					document.images["img-"+id].src=[imgExpanded];
				}
				return true;
			} else {
				item.style.display = 'none';
				if (document.images["img-"+id]) {
					document.images["img-"+id].src=[imgCollapsed];
				}
				return true;
			}	
		} else {
			if (document.layers) {
				if (document.id.display == "none"){
					document.id.display = 'block';
					document.images["img-"+id].src=[imgExpanded];
				} else {
					document.images["img-"+id].src=[imgCollapsed];
					document.id.display = 'none';
				}
			} else {
				if (document.all.id.style.visibility == "none"){
					document.all.id.style.display = 'block';
				} else {
					document.images["img-"+id].src=[imgCollapsed];
					document.all.id.style.display = 'none';
				}
			}
		}
	}

	var xmlhttp=false;
	/*@cc_on @*/
	/*@if (@_jscript_version >= 5)
	// JScript gives us Conditional compilation, we can cope with old IE versions.
	// and security blocked creation of the objects.
	 try {
	  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	 } catch (e) {
	  try {
	   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	  } catch (E) {
	   xmlhttp = false;
	  }
	 }
	@end @*/
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		try {
			xmlhttp = new XMLHttpRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}
	if (!xmlhttp && window.createRequest) {
		try {
			xmlhttp = window.createRequest();
		} catch (e) {
			xmlhttp=false;
		}
	}

	var arrFetchedOpml = new Array();

	Array.prototype.inArray = function (value) {
		var i;
		for (i=0; i < this.length; i++) {
			if (this[i] === value) {
				return true;
			}
		}
		return false;
	};

	function xmlhttpGetOpml (url, id) {
		if (!arrFetchedOpml.inArray(url)) {
			xmlhttp.open("GET", url, true);
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4) {
					if (xmlhttp.status==200) {
						//alert(xmlhttp.responseText)
						arrFetchedOpml.push(url);
						node_graft(id);
						} else {
						document.getElementById(id).style.display='block';
						document.getElementById(id).innerHTML="Error "+xmlhttp.status+": "+xmlhttp.statusText;
						}
					}
				}
			xmlhttp.send(null);
		}
	}
	
	function node_graft (id) {
		document.getElementById(id).style.display='block';
		document.getElementById(id).innerHTML=xmlhttp.responseText;
	}
// end hiding -->
</script>
<?php
}

function nodeTreeCSS () {
	// Pull in the css with an import
	// And prefetch the images with JavaScript
?>
<style type="text/css">
<!--
@import url(http://<?php
echo $_SERVER['SERVER_NAME'];
echo str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) ?
        str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) :
        ''; ?>/css/optimal.css);
-->
</style>
<script type="text/javascript">
<!-- begin hiding from old browsers
if (document.images)
{
<?php
	{ // Scoping brace
	$i = '1';
		foreach (glob("img/*") as $filename) {
			echo "\tpic".$i."= new Image(8,8);\n\tpic".$i.".src='".$filename."';\n";
			$i++;
		}
	}
?>
}
// end hiding -->
</script>
<?php
}

function urlProper ($url) {
    $url = html_entity_decode(urldecode($url));
	$url = str_replace( ' ', '%20', $url);
	return ($url);
}

function renderXML ($url, $flForceRefresh = FALSE, $options = array()) {
	error_reporting(0);
	
	// The options array
	$depth = $options['depth'];
	$flIsNode = $options['flIsNode'];
	$flNoHead = $options['flNoHead'];
	$linkTarget = $options['linkTarget'];
	$maxAge = $options['maxAge'] ? $options['maxAge'] : '1200' ;
	$type = $options['type'] ? $options['type'] : 'OPML' ;
	$xslfile = $options['xslfile'];

	$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) ?
		str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__FILE__)) :
		'';

	$cachefile = dirname(__FILE__).'/_cache/'.md5($url).'.'.strtolower($type).'.xml';
	
	$debuginfo = "<br/>$type URL: <a href=\"$url\">$url</a><br/>\nCache file: ";
	$debuginfo .= $cachefile ? '<a href="'.$path.'/_cache/'.basename($cachefile).'">'.basename($cachefile).'</a>' : "None";
	$debuginfo .= "<br/>\n";

	// Get file contents
	if ( (!$flForceRefresh) && file_exists($cachefile) && ( filesize($cachefile) > 0 ) && ( filemtime($cachefile) > ( time() - $maxAge ) ) ) {
		// We have a local, non-empty, recent copy, so use it
		$xml = file_get_contents($cachefile);

		if (empty($xml))  {
			return "<li class=\"outlineItem\">Error reading $type file.$debuginfo</li>";
		}

	} else {

		if (function_exists('curl_init')) {
			// We have curl
			$curl_handle=curl_init();
			curl_setopt($curl_handle,CURLOPT_URL,$url);
			curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,10);
			curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($curl_handle,CURLOPT_MAXREDIRS,2);
			$xml = curl_exec($curl_handle);
			curl_close($curl_handle);

			if (empty($xml) && file_exists($cachefile))  {
				$xml = file_get_contents($cachefile);

				if (empty($xml))  {
					return "<li class=\"outlineItem\">Error reading $type file in CURL.$debuginfo</li>";
				}
				$xml_is_cached = TRUE;
			}

		} elseif (ini_get(allow_url_fopen)) {
			// We have allow_url_fopen
			$xml = file_get_contents($url);

			if (empty($xml) && file_exists($cachefile))  {
				$xml = file_get_contents($cachefile);

				if (empty($xml))  {
					return "<li class=\"outlineItem\">Error reading $type file.$debuginfo</li>";
				}
				$xml_is_cached = TRUE;
			}

		} else {
			return "Error: Your PHP installation must have either CURL or allow_url_fopen to use this plugin.";
		}

		if (!$xml_is_cached) { // If we're not using the cachefile, write xml to the cachefile
			$handle = fopen($cachefile,'w');
			fwrite($handle,$xml);
			fclose($handle);
		}
	} // End of get file contents

	// XSL
	if ($xslfile && file_exists(dirname(__FILE__).'/xsl/'.$xslfile.'.xsl')) {
		$xsl = file_get_contents(dirname(__FILE__).'/xsl/'.$xslfile.'.xsl');

	} elseif ($_GET['xslfile'] && file_exists(dirname(__FILE__).'/xsl/'.$_GET['xslfile'].'.xsl')) {
		$xsl = file_get_contents(dirname(__FILE__).'/xsl/'.$_GET['xslfile'].'.xsl');

	} else {
		$xsl = file_get_contents(dirname(__FILE__).'/xsl/optimal.xsl');
	}

	if (empty($xsl))  {
		return "Error reading XSL file.";
	}

	if ($type == 'OPML') {
		$parameter_array = array (	'opmlLink' => "$url",
									'path' => 'http://'.$_SERVER['SERVER_NAME'].$path );
	
		if ($flIsNode) {
			$parameter_array['isNode'] = 'TRUE';
			}
		
		if ($flNoHead) {
			$parameter_array['noHead'] = 'TRUE';
			}

		if ($linkTarget) {
			$parameter_array['linkTarget'] = $linkTarget;
		}

		if ('1' > $depth) {
			$parameter_array['depth'] = '1';
		} else {
			$parameter_array['depth'] = $depth;
		}

		if ($flIsNode) {
			$parameter_array['depth'] = '0';
		}
	}

	elseif ($type == 'RSS') {
		$parameter_array = array ( 'path' => 'http://'.$_SERVER['SERVER_NAME'].$path,
								   'rssLink' => 'http://'.$_SERVER['SERVER_NAME'].$path.'/_cache/'.basename($cachefile) );
		if ($linkTarget) {
			$parameter_array['linkTarget'] = $linkTarget;
		}
	}

	// This is the magic that searches for the best available XSLT rendering
	// method and dies elegantly if none is found
	if (PHP_VERSION >= 5) {
		// Fix this section
		// To do what?! Stupid self ...
		// Error reporting, maybe...
		$xslt = new xsltProcessor;
		$xslt->importStyleSheet(DomDocument::loadXML($xsl));
		foreach ($parameter_array as $param => $value) {
			$xslt->setParameter('', $param, $value);
		}
		$xslt_result = $xslt->transformToXML(DomDocument::loadXML($xml));

	} elseif (function_exists('domxml_open_mem') && function_exists('domxml_xslt_stylesheet')) {  // PHP 4 DOM_XML support

		if (!$domXml = domxml_open_mem($xml)) {
			$result = "<li class=\"outlineItem\">Error while parsing the $type document.$debuginfo</li>";
		}
		
		$domXsltObj = domxml_xslt_stylesheet( $xsl );
		$domTranObj = $domXsltObj->process( $domXml, $parameter_array );
		$xslt_result = $domXsltObj->result_dump_mem( $domTranObj );

	} elseif (function_exists('xslt_create')) {  // PHP 4 XSLT library
		$arguments = array (
		 '/_xml' => $xml,
		 '/_xsl' => $xsl
		);

		$xslt_inst = xslt_create();	
		$xslt_result = xslt_process($xslt_inst,'arg:/_xml','arg:/_xsl', NULL, $arguments, $parameter_array);
		xslt_free($xslt_inst);

	} else {  // Nothing, no valid processor found.  Curses.
		$result = "No valid XSLT processor found";
	}
	
	if ($xslt_result) {
		$result = $xslt_result;

		if ($xml_is_cached) {
			$result .= "<!-- loaded from cache as a fallback -->\n";
		}

	} elseif (!$result) {
		$result = "<li class=\"outlineItem\">Horrific XSLT error - check logs.$debuginfo</li>";
		unlink($cachefile); //Delete the cachefile - maybe it's corrupt.
	}

	return $result;

}
?>
