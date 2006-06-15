<?php
/*
Name: class.optimal.php
Homepage: http://www.yabfog.com/wp/optimal/
Description: A component of Optimal
Version: 0.4c (beta)
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

class optimal {

	var $basefilepath;
	var $baseuripath;
	var $relpath;
	var $localbasefilepath;
	var $mainID;

	var $errors = array();

	var $debuginfo;
	var $_cachefile;
	var $xml;
	
	function optimal ($basefilepath = NULL, $baseuripath = NULL, $relpath = NULL, $localbasefilepath = NULL) { // Constructor
        /* The user may pass the path parameters if for some reason the 
           autodiscovery in this function does not work (such as Aliased directories) */
		
		$this->basefilepath = $basefilepath ? $basefilepath : $_SERVER['DOCUMENT_ROOT'];
		$this->baseuripath = $baseuripath ? $baseuripath : 'http://'.$_SERVER['SERVER_NAME'];
		if ($relpath)
			$this->relpath = $relpath;
		else
			$this->relpath = str_replace($this->basefilepath, '', dirname(__FILE__)) ? str_replace($this->basefilepath, '', dirname(__FILE__)) : '';
		if ($localbasefilepath)
			$this->localbasefilepath = $localbasefilepath;
		else
			$this->localbasefilepath = $this->basefilepath;
	}
	
	function _error_messages () {
		foreach ($this->errors as $msg) {
			$errors .= '<li class="outlineItemErrors">'.$msg.'</li>';
		}
		return $errors;
	}

	function url_decode_no_spaces ($url) {
		$url = html_entity_decode(urldecode($url));
		$url = str_replace( ' ', '%20', $url);
		return ($url);
	}
	
	function _fetch_file ($url, $flIsLocal = FALSE) {
		if ($flIsLocal) {
			return file_get_contents($url);
		}
		elseif (function_exists('curl_init')) {
			// We have curl
			$curl_handle=curl_init();
			curl_setopt($curl_handle,CURLOPT_URL,$url);
			curl_setopt($curl_handle,CURLOPT_USERAGENT,"Optimal/0.4");
			curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,10);
			curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($curl_handle,CURLOPT_MAXREDIRS,2);
			$xml = curl_exec($curl_handle);
			curl_close($curl_handle);

		}
		elseif (ini_get(allow_url_fopen)) {
			// We have allow_url_fopen
			$xml = @file_get_contents($url);

		}
		else {
			$this->errors[] = "Error: Your PHP installation must have either CURL or allow_url_fopen to use Optimal";
			return false;
		}
		// Write non-local, non-cached xml to the cachefile
		$handle = fopen($this->_cachefile,'w');
		fwrite($handle,$xml);
		fclose($handle);
		return $xml;
	} // End function _fetch_file
	
	function _XSLtransform ($xml, $xsl, $parameter_array = array()) {
		// This is the magic that searches for the best available XSLT rendering
		// method and dies elegantly if none is found
		if (PHP_VERSION >= 5 && class_exists ('xsltProcessor')) {
			$xslt = new xsltProcessor;
			$xslt->importStyleSheet(DomDocument::loadXML($xsl));
			foreach ($parameter_array as $param => $value) {
				$xslt->setParameter('', $param, $value);
			}
			$xslt_result = $xslt->transformToXML(DomDocument::loadXML($xml));
		}
		elseif (function_exists('domxml_open_mem') && function_exists('domxml_xslt_stylesheet')) {  // PHP 4 DOM_XML support
	
			if (!$domXml = domxml_open_mem($xml, $errors)) {
				$this->errors[] = "Error while parsing the document.$this->debuginfo";
				return false;
			}
			$domXsltObj = domxml_xslt_stylesheet( $xsl );
			$domTranObj = $domXsltObj->process( $domXml, $parameter_array );
			$xslt_result = $domXsltObj->result_dump_mem( $domTranObj );
		}
		elseif (function_exists('xslt_create')) {  // PHP 4 XSLT library
			$arguments = array (
			 '/_xml' => $xml,
			 '/_xsl' => $xsl
			);
	
			$xslt_inst = xslt_create();	
			$xslt_result = xslt_process($xslt_inst,'arg:/_xml','arg:/_xsl', NULL, $arguments, $parameter_array);
			xslt_free($xslt_inst);
		}
		else {  // Nothing, no valid processor found.  Curses.
			$this->errors[] = "No valid XSLT processor found";
			return false;
		}
		return $xslt_result;
	} // End function _XSLtransform

	function renderXML ($url, $flForceRefresh = FALSE, $options = array()) {
		/*    This is the main method.    */
		error_reporting(0);

		/* This is used to wrap the entire outline so that the expand/collapse
		   javascript function only operates on this outline */
		if (!$this->mainID)
			$this->mainID = uniqid('optimal-');

		// The options array
		$depth = NULL !== $options['depth'] ? $options['depth'] : 1;
		$flIsNode = $options['flIsNode'];
		$flNoHead = $options['flNoHead'];
		$linkTarget = $options['linkTarget'];
		$mainClass = $options['mainClass'] ? $options['mainClass'] : 'outlineRoot';
		$flBottomBorder = $options['bottomBorder'];
		$maxAge = $options['maxAge'] ? $options['maxAge'] : '1200' ;
		$type = $options['type'] ? $options['type'] : 'OPML' ;
		$xslfile = $options['xslfile'];
		// Select and read the XSL file
		if ($xslfile && file_exists($this->basefilepath.$this->relpath.'/xsl/'.$xslfile.'.xsl')) {
			$xsl = @file_get_contents($this->basefilepath.$this->relpath.'/xsl/'.$xslfile.'.xsl');
		} elseif ($_GET['xslfile'] && file_exists($this->basefilepath.$this->relpath.'/xsl/'.$_GET['xslfile'].'.xsl')) {
			$xsl = @file_get_contents($this->basefilepath.$this->relpath.'/xsl/'.$_GET['xslfile'].'.xsl');
		} else {
			$xsl = @file_get_contents($this->basefilepath.$this->relpath.'/xsl/optimal.xsl');
		}
	
		if (empty($xsl))  {
			$this->errors[] = "Error reading XSL file";
			return $this->_error_messages();
		}
	
		// You can use locally stored files that are under your server's root
		// document folder by specifying a relative local path
		if (strpos($url, '/') === 0) { # a relative local path
			$flIsLocal = TRUE;
		} else {
			$flIsLocal = FALSE;
		}

		$this->_cachefile = $this->basefilepath.$this->relpath.'/_cache/'.md5($url).'.'.strtolower($type).'.xml';

		$this->debuginfo = "<br/>$type URL: <a href=\"$url\">$url</a><br/>\nCache file: ";
		$this->debuginfo .= $this->_cachefile ? '<a href="'.$this->baseuripath.$this->relpath.'/_cache/'.basename($this->_cachefile).'">'.basename($this->_cachefile).'</a>' : "None";
		$this->debuginfo .= "<br/>\n";

		// Get file contents
		if ($flIsLocal) {
			// We are rendering a local file
			$this->xml = $this->_fetch_file($this->localbasefilepath.$url, TRUE);
		} elseif ( (!$flForceRefresh) && file_exists($this->_cachefile) && ( filesize($this->_cachefile) > 0 ) && ( filemtime($this->_cachefile) > ( time() - $maxAge ) ) ) {
			// We have a local, non-empty, recent copy, so use it
			$this->xml = $this->_fetch_file($this->_cachefile, TRUE);
		} else {
			$this->xml = $this->_fetch_file($url);
			if (empty($this->xml) && file_exists($this->_cachefile))  { // Use a local cachefile as fallback if the remote fetch fails
				$this->xml = $this->_fetch_file($this->_cachefile, TRUE);
				$xmlIsCached = TRUE;
			}
		}

		if (empty($this->xml))  {
			$this->errors[] = "Error reading $type file.$this->debuginfo";
			return $this->_error_messages();
		}
		// End get file contents

		// Prepare XSL parameters
		if ($type == 'OPML') {
			$parameter_array = array (	'opmlLink' => "$url",
										'path' => $this->baseuripath.$this->relpath,
										'mainID' => $this->mainID,
										'depth' => $depth );
			if ($flIsNode) {
				$parameter_array['isNode'] = TRUE;
				}
			if ($flNoHead) {
				$parameter_array['noHead'] = TRUE;
				}
			if ($linkTarget) {
				$parameter_array['linkTarget'] = $linkTarget;
			}
			if ($mainClass) {
				$parameter_array['mainClass'] = $mainClass;
			}
			if ($flBottomBorder) {
				$parameter_array['bottomBorder'] = TRUE;
			}
			if ($flIsNode) {
				$parameter_array['depth'] = '0';
			}
		} elseif ($type == 'RSS') {
			$parameter_array = array ( 'path' => $this->baseuripath.$this->relpath,
									   'rssLink' => $this->baseuripath.$this->relpath.'/_cache/'.basename($this->_cachefile) );
			if ($linkTarget) {
				$parameter_array['linkTarget'] = $linkTarget;
			}
		}
		// End prepare XSL parameters

		$xslt_result = $this->_XSLtransform($this->xml, $xsl, $parameter_array);
		
		if ($type == 'RSS')
			$xslt_result = $this->fixLinkTargets ($xslt_result, $linkTarget);

		if ($xslt_result) {
			if ($xmlIsCached) {
				$xslt_result .= "\n<!-- loaded from cache as a fallback -->\n";
			}
			return $xslt_result;
		} else {
			unlink($this->_cachefile); //Delete the cachefile - maybe it's corrupt.
			$this->errors[] = "Could not apply the XSL transform to the file, probably because the file was not valid XML or because the remote server returned an unexpected error instead of the requested page.";
			return $this->_error_messages();
		}
		return false; // This should never be reached
	} // End function renderXML

	function fixLinkTargets ($str, $linkTarget) {
		$search = array('/target=.+? /ims', '/<a /ims');
		$replace = array(' ', "<a target=\"$linkTarget\" ",);
		return preg_replace($search, $replace, $str);
	}

	function printHeadJavaScript ($absuripath = NULL, $linkTarget = '_blank') {
		if (!$absuripath) {
			$absuripath = $this->baseuripath.$this->relpath;
		}
		$imgCollapsed = $absuripath.'/img/imgCollapsed.gif';
		$imgExpanded = $absuripath.'/img/imgExpanded.gif';
	?>
	<script type="text/javascript">
	<!-- begin hiding from old browsers
	var imgCollapsed = "<?php echo $imgCollapsed; ?>";
	var imgExpanded = "<?php echo $imgExpanded; ?>";

	function expandAll (id) {
		var uls = new Array();
		var nodeCollection = document.getElementById(id).childNodes;
		for (var i=0;i<nodeCollection.length;i++) {
			if (nodeCollection[i].nodeType == 1) {
				uls = uls.concat(getULsRecursive(nodeCollection[i]));
			}
		}
		for (var i=0;i<uls.length;i++) {
			if (uls[i].firstChild.className != 'outlineItemNodeSub' && uls[i].className != 'rssItem') {
				uls[i].style.display = 'block';
				if (document.images["img-"+uls[i].id]) {
					document.images["img-"+uls[i].id].src=[imgExpanded];
				}
			}
		}
	}

	function collapseAll (id) {
		var uls = new Array();
		var nodeCollection = document.getElementById(id).childNodes;
		for (var i=0;i<nodeCollection.length;i++) {
			if (nodeCollection[i].nodeType == 1) {
				uls = uls.concat(getULsRecursive(nodeCollection[i]));
			}
		}
		for (var i=0;i<uls.length;i++) {
			if (uls[i].firstChild.className != 'outlineItemNodeSub') {
				uls[i].style.display = 'none';
				if (document.images["img-"+uls[i].id]) {
					document.images["img-"+uls[i].id].src=[imgCollapsed];
				}
			}
		}
	}

	function getULsRecursive (node) {
		var uls = new Array();
		if (node.nodeName == 'UL') {
			uls.push(node);
		}
		if (node.childNodes && node.nodeName == 'LI') {
			for (var i=0;i<node.childNodes.length;i++) {
				uls = uls.concat(getULsRecursive(node.childNodes[i]));
			}
		}
		return uls;
	}

	function optimalToggleNode(id, isNode, url) {
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
	} // End function printHeadJavaScript

	function printNodeTreeCSS ($absuripath = NULL) {
		if (!$absuripath) {
			$absuripath = $this->baseuripath.$this->relpath;
		}
	?>
	<style type="text/css">
	<!--
	ul.main, ul.outlineList {
	margin-left: 15px;
	padding: 0px;
	}
	.outlineRoot img, ul.main img {
	border: none;
	text-decoration: none;
	}
	li.outlineItem {
	list-style: none outside;
	margin-left: 0px;
	text-indent: -15px;
	}
	li.outlineItemNode {
	list-style: none outside;
	margin-left: 0px;
	text-indent: -15px;
	}
	ul.rssItem {
	margin-left: 0px;
	padding: 0px;
	}
	ul.rssItem li.outlineItem {
	list-style: none outside;
	margin-left: 0px;
	text-indent: 0px;
	}
	li.outlineItemNodeSub {
	list-style: none outside;
	margin-left: 0px;
	text-indent: -15px;
	}
	li.outlineItemErrors {
	list-style: none outside;
	margin-left: 15px;
	}
	.optimalAllExpandCollapse {
	margin: 3ex 0px 0px;
	padding: 0px;
	font-size: 87%;
	}
	.optimalTarget {
	color: blue;
	cursor: pointer;
	}
	.optimalTarget:hover {
	text-decoration: underline;
	}
	.optimalSourceLink {
	margin: 0px 0px 3ex;
	padding: 0px;
	border-top: 1px solid #ddd;
	}
	.optimalSourceLink a, .optimalSourceLink img {
	border: none;
	text-decoration: none;
	}
	.optimalSourceLink img {
	float: right;
	}
	-->
	</style>
	<script type="text/javascript">
	<!-- begin hiding from old browsers
	if (document.images)
	{
	<?php
		$i = '1';
		chdir($this->basefilepath.$this->relpath);
		foreach (glob("img/*") as $filename) {
			echo "\tpic".$i."= new Image(8,8);\n\tpic".$i.".src='".$absuripath.'/'.$filename."';\n";
			$i++;
		}
	?>
	}
	// end hiding -->
	</script>
	<?php
	} // End function printNodeTreeCSS

	function genAllExpandCollapse ($class = 'optimalAllExpandCollapse') {
		$this->mainID = uniqid('optimal-');
		$str = '<div class="'.$class.'">[ <span onclick="javascript:expandAll(\''.$this->mainID.'\');" class="optimalTarget">Expand All</span> | <span onclick="javascript:collapseAll(\''.$this->mainID.'\');" class="optimalTarget">Collapse All</span> ]</div>'."\n";
		return ($str);
	}

	function genOutlineLink ($url, $class = 'optimalSourceLink') {
		$str = '<div class="'.$class.'"><a href="'.$url.'"><img src="'.$this->baseuripath.$this->relpath.'/img/opml.gif" alt="Source OPML" title="Source OPML"/></a></div>'."\n";
		return ($str);
	}
} // End class optimal
?>
