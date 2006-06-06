<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:str="http://exslt.org/strings"
	extension-element-prefixes="str">

<xsl:output method="html" encoding="UTF-8" indent="yes" />
<!--
	This XSL Transform is written by
	Dan MacTough - http://www.yabfog.com/ - http://blogs.opml.org/yabfog/
-->

	<xsl:param name="opmlLink"/>
	<xsl:param name="path"/>
	<xsl:param name="isNode"/>
	<xsl:param name="noHead"/>
	<xsl:param name="linkTarget"/>
	<xsl:param name="nodeRender"><xsl:value-of select="$path"/>/optimal.php</xsl:param>
	<xsl:param name="depth"/>
	<xsl:param name="mainID"/>
	<xsl:param name="mainClass"/>
	<xsl:param name="bottomBorder"/>

	<xsl:variable name="imgCircle"><xsl:value-of select="$path"/>/img/indicator_arrows_circle.gif</xsl:variable>
	<xsl:variable name="imgCollapsed"><xsl:value-of select="$path"/>/img/imgCollapsed.gif</xsl:variable>
	<xsl:variable name="imgExpanded"><xsl:value-of select="$path"/>/img/imgExpanded.gif</xsl:variable>
	<xsl:variable name="imgNosubs"><xsl:value-of select="$path"/>/img/imgNosubs.gif</xsl:variable>
	<xsl:variable name="imgOPMLlogo"><xsl:value-of select="$path"/>/img/opml.gif</xsl:variable>
	<xsl:variable name="imgOPML"><xsl:value-of select="$path"/>/img/redArrow.gif</xsl:variable>
	<xsl:variable name="imgXML"><xsl:value-of select="$path"/>/img/feed-icon-12x12.gif</xsl:variable>

	<xsl:template match="/opml">
	    <xsl:choose>
	        <xsl:when test="$isNode != 1 and $noHead != 1">
    		<xsl:element name="div">
    			<xsl:attribute name="class"><xsl:value-of select="$mainClass"/></xsl:attribute>
    			<b>Title</b>: <xsl:value-of select="head/title" /><xsl:text> </xsl:text>
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="$opmlLink"/>
					</xsl:attribute>
					<xsl:if test="$linkTarget != ''">
						<xsl:attribute name="target">
							<xsl:value-of select="$linkTarget"/>
						</xsl:attribute>
					</xsl:if>
					<img src="{$imgOPMLlogo}" title="OPML" alt="OPML"></img>
				</xsl:element>
				<br />
    			<b>Author</b>: <xsl:value-of select="head/ownerName" /><br />
				<xsl:if test="head/ownerEmail != ''">
    			<b>Email</b>: 
    			<xsl:call-template name="strReplace">
    				<xsl:with-param name="string" select="head/ownerEmail"/>
    				<xsl:with-param name="search" select="string('@')"/>
    				<xsl:with-param name="replace" select="string(' at ')"/>
    			</xsl:call-template><br />
				</xsl:if>
				<xsl:if test="head/ownerId != ''">
				<b>Author Contact</b>: <a href="{head/ownerId}"><xsl:value-of select="head/ownerId"/></a><br />
				</xsl:if>
    			<b>Date</b>: <xsl:value-of select="head/dateModified"/><br />
    			<br />
    			<xsl:element name="ul">
    			    <xsl:attribute name="class">main</xsl:attribute>
    			    <xsl:attribute name="id"><xsl:value-of select="$mainID"/></xsl:attribute>
    				<xsl:apply-templates select="body"/>
    			</xsl:element>
    		</xsl:element>
	        </xsl:when>
	        <xsl:when test="$isNode != 1">
        		<xsl:element name="div">
        			<xsl:attribute name="class"><xsl:value-of select="$mainClass"/></xsl:attribute>
    				<xsl:element name="ul">
        			    <xsl:attribute name="class">main</xsl:attribute>
        			    <xsl:attribute name="id"><xsl:value-of select="$mainID"/></xsl:attribute>
        				<xsl:apply-templates select="body"/>
    				</xsl:element>
    			</xsl:element>
	        </xsl:when>
	        <xsl:otherwise>
	            <xsl:apply-templates select="body"/>
	        </xsl:otherwise>
	    </xsl:choose>
		<xsl:if test="$bottomBorder = 1">
			<xsl:element name="div">
				<xsl:attribute name="class">opmlBorder</xsl:attribute>
				<a href="{$opmlLink}"><img src="{$imgOPMLlogo}" alt="Source OPML" title="Source OPML" class="opmlBorderLogo"/></a>
			</xsl:element>
		</xsl:if>
	</xsl:template>
	<xsl:template match="outline">
		<xsl:variable name="isOPML">
			<xsl:call-template name="checkOPML"/>
		</xsl:variable>
		<xsl:variable name="uniqueID">
			<xsl:text>oi-</xsl:text>
			<xsl:value-of select="generate-id(.)"/>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="child::outline and (count(ancestor::outline) &lt; $depth) and $isOPML != 'true' and not(@xmlUrl)">
				<xsl:call-template name="lineItem">
				    <xsl:with-param name="imgExpCol" select="$imgExpanded"/>
				    <xsl:with-param name="jsCmd">opmlRenderExCol('<xsl:value-of select="$uniqueID"/>');</xsl:with-param>
				    <xsl:with-param name="uniqueID" select="$uniqueID"/>
 				</xsl:call-template>
			</xsl:when>
			<xsl:when test="child::outline and $isOPML != 'true' and not(@xmlUrl)">
				<xsl:call-template name="lineItem">
				    <xsl:with-param name="imgExpCol" select="$imgCollapsed"/>
				    <xsl:with-param name="jsCmd">opmlRenderExCol('<xsl:value-of select="$uniqueID"/>');</xsl:with-param>
				    <xsl:with-param name="uniqueID" select="$uniqueID"/>
 				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$isOPML = 'true'"> <!-- OPML Item -->
				<xsl:variable name="opmlUrl">
					<xsl:choose>
						<xsl:when test="@url != ''">
							<xsl:value-of select="@url" disable-output-escaping="yes"/>
						</xsl:when>
						<xsl:when test="@htmlUrl != ''">
							<xsl:value-of select="@htmlUrl" disable-output-escaping="yes"/>
						</xsl:when>
						<xsl:when test="@xmlUrl != ''">
							<xsl:value-of select="@xmlUrl" disable-output-escaping="yes"/>
						</xsl:when>
					</xsl:choose>
				</xsl:variable>
				<xsl:variable name="jsQueryStr">
					<xsl:text>url=</xsl:text>
					<xsl:value-of select="str:encode-uri($opmlUrl, true())"/>
					<xsl:text>&amp;node=opml</xsl:text>
					<xsl:if test="$linkTarget != ''">
						<xsl:text>&amp;linktarget=</xsl:text>
						<xsl:value-of select="$linkTarget"/>
					</xsl:if>
				</xsl:variable>
				<xsl:call-template name="lineItem">
				    <xsl:with-param name="imgExpCol" select="$imgCollapsed"/>
				    <xsl:with-param name="isOPML" select="$isOPML"/>
				    <xsl:with-param name="jsCmd">opmlRenderExCol('<xsl:value-of select="$uniqueID"/>', 'true', '<xsl:value-of select="$nodeRender"/>?<xsl:value-of select="$jsQueryStr"/>');</xsl:with-param>
				    <xsl:with-param name="opmlUrl" select="$opmlUrl"/>
				    <xsl:with-param name="uniqueID" select="$uniqueID"/>
 				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@xmlUrl != ''"> <!-- RSS Item -->
				<xsl:variable name="jsQueryStr">
					<xsl:text>url=</xsl:text>
					<xsl:value-of select="str:encode-uri(@xmlUrl, true())"/>
					<xsl:text>&amp;node=rss</xsl:text>
					<xsl:if test="$linkTarget != ''">
						<xsl:text>&amp;linktarget=</xsl:text>
						<xsl:value-of select="$linkTarget"/>
					</xsl:if>
				</xsl:variable>
				<xsl:call-template name="lineItem">
				    <xsl:with-param name="imgExpCol" select="$imgCollapsed"/>
				    <xsl:with-param name="jsCmd">opmlRenderExCol('<xsl:value-of select="$uniqueID"/>', 'true', '<xsl:value-of select="$nodeRender"/>?<xsl:value-of select="$jsQueryStr"/>');</xsl:with-param>
				    <xsl:with-param name="uniqueID" select="$uniqueID"/>
 				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="li">
					<xsl:attribute name="class">
					    <xsl:text>outlineItem</xsl:text>
					</xsl:attribute>
					<xsl:element name="img">
						<xsl:attribute name="src"><xsl:value-of select="$imgNosubs"/></xsl:attribute>
						<xsl:attribute name="style">text-decoration: none; border: none; margin-right: 3px;</xsl:attribute>
						<xsl:attribute name="alt"></xsl:attribute>
					</xsl:element>
					<xsl:call-template name="outlineItem"/>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- End template rules -->
	<!-- Begin named templates -->
	<xsl:template name="lineItem">
		<xsl:param name="imgExpCol"/>
		<xsl:param name="isOPML"/>
		<xsl:param name="jsCmd"/>
		<xsl:param name="opmlUrl"/>
		<xsl:param name="uniqueID"/>

		<xsl:element name="li">
			<xsl:if test="child::outline or $isOPML = 'true' or (@xmlUrl != '')">
				<xsl:attribute name="class">
				    <xsl:text>outlineItemNode</xsl:text>
				</xsl:attribute>
			</xsl:if>
			<xsl:element name="span">
				<xsl:attribute name="onclick"><xsl:value-of select="$jsCmd"/></xsl:attribute>
				<xsl:attribute name="style">cursor: pointer; text-decoration: none; border: none; margin-right: 3px;</xsl:attribute>
				<xsl:element name="img">
					<xsl:attribute name="name">img-<xsl:value-of select="$uniqueID"/></xsl:attribute>
					<xsl:attribute name="src"><xsl:value-of select="$imgExpCol"/></xsl:attribute>
					<xsl:attribute name="style">text-decoration: none; border: none;</xsl:attribute>
					<xsl:attribute name="alt">[+/-]</xsl:attribute>
					<xsl:attribute name="title">[+/-]</xsl:attribute>
				</xsl:element>
			</xsl:element>
			<xsl:element name="span">
				<xsl:call-template name="outlineItem">
				    <xsl:with-param name="isOPML" select="$isOPML"/>
				    <xsl:with-param name="opmlUrl" select="$opmlUrl"/>
				    <xsl:with-param name="uniqueID" select="$uniqueID"/>
			    </xsl:call-template>
			</xsl:element>
			<xsl:element name="ul">
				<xsl:attribute name="id">
					<xsl:value-of select="$uniqueID"/>
				</xsl:attribute>
			    <xsl:attribute name="class">
			        <xsl:text>outlineList </xsl:text>
			        <xsl:text>depth</xsl:text>
			        <xsl:value-of select="count(ancestor-or-self::outline)"/>
			    </xsl:attribute>
				<xsl:choose>
					<xsl:when test="$isOPML = 'true' or (@xmlUrl != '')">
						<xsl:attribute name="style">
							<xsl:text>display:none;</xsl:text>
						</xsl:attribute>
						<xsl:element name="li">
           					<xsl:attribute name="class">
        					    <xsl:text>outlineItemNodeSub</xsl:text>
        					</xsl:attribute>
							<xsl:element name="img">
								<xsl:attribute name="src"><xsl:value-of select="$imgCircle"/></xsl:attribute>
								<xsl:attribute name="style">text-decoration: none; border: none; margin-right: 3px;</xsl:attribute>
								<xsl:attribute name="alt"></xsl:attribute>
							</xsl:element>
							<xsl:text>Loading....</xsl:text>
						</xsl:element>
					</xsl:when>
					<xsl:when test="$imgExpCol = $imgExpanded">
						<xsl:attribute name="style">
							<xsl:text>display:block;</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="style">
							<xsl:text>display:none;</xsl:text>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:if test="child::outline and $isOPML != 'true' and not(@xmlUrl)">
					<xsl:apply-templates select="child::outline"/>
				</xsl:if>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	<xsl:template name="outlineItem">
	    <xsl:param name="isOPML"/>
	    <xsl:param name="opmlUrl"/>
	    <xsl:param name="uniqueID"/>
		<xsl:variable name="displayText">
			<xsl:call-template name="selectText"/>
		</xsl:variable>
		<xsl:choose>
			<!-- Begin OPML item -->
			<xsl:when test="$isOPML">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="$opmlUrl"/>
					</xsl:attribute>
					<xsl:if test="$linkTarget != ''">
						<xsl:attribute name="target">
							<xsl:value-of select="$linkTarget"/>
						</xsl:attribute>
					</xsl:if>
					<img src="{$imgOPML}" alt="Link to OPML File" title="Open OPML File" style="margin-right: 3px; text-decoration: none; border: none;"></img>
				</xsl:element>
				<xsl:value-of select="$displayText"/>
			</xsl:when>
			<!-- End OPML item -->
			<!-- Begin RSS item -->
			<!-- We try to be accomodating in detecting an RSS item -->
			<xsl:when test="@xmlUrl != ''">
				<xsl:variable name="xmlLink" select="@xmlUrl"/>
				<xsl:variable name="htmlLink">
					<xsl:choose>
						<xsl:when test="@htmlUrl != ''">
							<xsl:value-of select="@htmlUrl"/>
						</xsl:when>
						<xsl:when test="@url != ''">
							<xsl:value-of select="@url"/>
						</xsl:when>
					</xsl:choose>
				</xsl:variable>
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="$xmlLink"/>
					</xsl:attribute>
					<xsl:if test="$linkTarget != ''">
						<xsl:attribute name="target">
							<xsl:value-of select="$linkTarget"/>
						</xsl:attribute>
					</xsl:if>
					<img src="{$imgXML}" alt="XML" title="XML RSS Feed" style="margin-right: 3px; text-decoration: none; border: none;"></img>
				</xsl:element>
				<xsl:choose>
				    <xsl:when test="$htmlLink != ''">
						<xsl:element name="a">
							<xsl:attribute name="href">
								<xsl:value-of select="$htmlLink"/>
							</xsl:attribute>
							<xsl:if test="$linkTarget != ''">
								<xsl:attribute name="target">
									<xsl:value-of select="$linkTarget"/>
								</xsl:attribute>
							</xsl:if>
            				<xsl:value-of select="$displayText"/>
						</xsl:element>
				    </xsl:when>
				    <xsl:otherwise>
				        <xsl:value-of select="$displayText"/>
				    </xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<!-- End RSS item -->
			<!-- Begin link item -->
			<!-- We try to be accomodating in detecting a link item, too -->
            <xsl:when test="@url != '' or @htmlUrl != ''">
				<xsl:variable name="urlLink">
					<xsl:choose>
						<xsl:when test="(@url != '')">
							<xsl:value-of select="@url"/>
						</xsl:when>
						<xsl:when test="@htmlUrl != ''">
							<xsl:value-of select="@htmlUrl"/>
						</xsl:when>
					</xsl:choose>
				</xsl:variable>
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="$urlLink"/>
					</xsl:attribute>
					<xsl:if test="$linkTarget != ''">
						<xsl:attribute name="target">
							<xsl:value-of select="$linkTarget"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="$displayText"/>
				</xsl:element>
            </xsl:when>
			<!-- End link item -->
			<!-- Begin other item, probably a subfolder -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="child::outline">
						<xsl:value-of select="$displayText" disable-output-escaping="no"/>
					</xsl:when>
					<!--
						When no child outline, assume that raw HTML may be in
						the outline.
					-->
					<xsl:otherwise>
						<xsl:value-of select="$displayText" disable-output-escaping="yes"/>
					</xsl:otherwise>
				</xsl:choose>				
			</xsl:otherwise>
			<!-- End other item -->
		</xsl:choose>
	</xsl:template>
	<xsl:template name="checkOPML">
		<!-- 
			This template returns 'true' if:
			(1) @url ends in .opml or .OPML
			(2) @type = include
		-->
		<xsl:variable name="strLength">
			<xsl:choose>
				<xsl:when test="@url != ''">
					<xsl:value-of select="string-length(@url)"/>
				</xsl:when>
				<xsl:when test="@htmlUrl != ''">
					<xsl:value-of select="string-length(@htmlUrl)"/>
				</xsl:when>
				<xsl:when test="@xmlUrl != ''">
					<xsl:value-of select="string-length(@xmlUrl)"/>
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="subStr">
			<!-- This grabs the the last five characters, i.e., the filename extension -->
			<xsl:choose>
				<xsl:when test="@url != ''">
					<xsl:value-of select="translate(substring(@url, $strLength - 4), 'OPML', 'opml')"/>
				</xsl:when>
				<xsl:when test="@htmlUrl != ''">
					<xsl:value-of select="translate(substring(@htmlUrl, $strLength - 4), 'OPML', 'opml')"/>
				</xsl:when>
				<xsl:when test="@xmlUrl != ''">
					<xsl:value-of select="translate(substring(@xmlUrl, $strLength - 4), 'OPML', 'opml')"/>
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<xsl:value-of select="contains($subStr, '.opml') or contains(translate(@type, 'INCLUDE', 'include'), 'include')"/>
	</xsl:template>
	<xsl:template name="selectText">
		<!--
			This conditional addresses OPML that does not include the
			required text attribute
		-->
		<xsl:choose>
			<xsl:when test="@text != ''">
				<xsl:value-of select="@text"/>
			</xsl:when>
			<xsl:when test="@title != ''">
				<xsl:value-of select="@title"/>
			</xsl:when>
			<xsl:otherwise>
				<i>(Blank)</i>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template name="strReplace">
		<xsl:param name="string"/>
		<xsl:param name="search"/>
		<xsl:param name="replace"/>
		<xsl:param name="fragment"/>
		<xsl:choose>
			<xsl:when test="contains($string, $search)">
				<xsl:variable name="newString">
					<xsl:value-of select="concat(substring-before($string, $search), $replace)"/>
				</xsl:variable>
    			<xsl:call-template name="strReplace">
    				<xsl:with-param name="string" select="substring-after($string, $search)"/>
    				<xsl:with-param name="search" select="$search"/>
    				<xsl:with-param name="replace" select="$replace"/>
    				<xsl:with-param name="fragment" select="concat($fragment, $newString)"/>
    			</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="concat($fragment, $string)"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- End named templates -->
</xsl:stylesheet>
