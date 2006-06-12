<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>Optimal Widget Generator</title>
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
	
	table {
		width: 36em;
	}
	form {
		width: 100%;
		margin: 1ex auto;
		padding: 4px 0;
	}
	-->
	</style>

</head>

<?php
if ($_GET['url'] && 'http://' != $_GET['url'] && (0 == $_GET['depth'] || $_GET['depth'] > 0)  && $_GET['linktarget'] && $_GET['height'] && $_GET['width']) {
	$widgetStr = '<iframe src="http://www.optimalbrowser.com/';
	$widgetStr .= '?url='.htmlspecialchars(urlencode($_GET['url']));
	$widgetStr .= '&amp;widget=1';
	$widgetStr .= '&amp;depth='.$_GET['depth'];
	$widgetStr .= '&amp;linktarget='.$_GET['linktarget'].'" ';
	$widgetStr .= 'height="'.$_GET['height'].'" ';
	$widgetStr .= 'width="'.$_GET['width'].'" ';
	$widgetStr .= 'style="border: none;"></iframe>';
	echo "<body onload=\"javascript: prompt('Copy and paste this string into your page\'s source code:','".htmlspecialchars($widgetStr)."'); return false;\">\n";
} else {
?>
<body>
<? } ?>
<h1><a href="http://www.optimalbrowser.com/">Optimal</a> <a href="http://www.optimalbrowser.com/widgetwiz.php">Widget Generator</a></h1>
<form action="http://www.optimalbrowser.com/widgetwiz.php" method="GET">
<table>
<tr>
<td style="width: 12em;">OPML URL:</td>
<td><input size="40" name="url" tabindex="1" type="text" value="<?php echo $_GET['url'] ? $_GET['url'] : 'http://' ?>"></td>
</tr>
<tr>
<td>Initial Depth:</td>
<td><input size="4" name="depth" tabindex="2" type="text" value="<?php echo $_GET['depth'] ? $_GET['depth'] : '0' ?>"></td>
</tr>
<tr>
<td>Links Will Open In...</td>
<td>
	<select size="1" name="linktarget" tabindex="3" style="width: 20em;">
		<option value="_parent"<?php echo ('_parent' == $_GET['linktarget']) ? ' selected' : '' ?>>The Main Window</option>
		<option value="_blank"<?php echo ('_blank' == $_GET['linktarget']) ? ' selected' : '' ?>>A New Window</option>
		<option value="_self"<?php echo ('_self' == $_GET['linktarget']) ? ' selected' : '' ?>>The IFRAME</option>
	</select>
</td>
</tr>
<tr>
<td>Widget Height:</td>
<td><input size="4" name="height" tabindex="4" type="text" value="<?php echo $_GET['height'] ? $_GET['height'] : '400' ?>"></td>
</tr>
<tr>
<td>Widget Width:</td>
<td><input size="4" name="width" tabindex="5" type="text" value="<?php echo $_GET['width'] ? $_GET['width'] : '200' ?>"></td>
</tr>
<tr>
<td colspan="2" style="padding-top: 2ex;"><input style="border: 1px outset #ccc; padding: 1px; background: #ddd;" type="submit" name="submit" tabindex="6" value="Generate Widget"></td>
</tr>
</table>
</form>
</body>
</html>
