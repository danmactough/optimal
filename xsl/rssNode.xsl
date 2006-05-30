<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="rdf enc rss atom atom10 dc admin l content xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                xmlns:enc="http://purl.oclc.org/net/rss_2.0/enc#"
                xmlns:rss="http://purl.org/rss/1.0/"
                xmlns:atom="http://purl.org/atom/ns#"
                xmlns:atom10="http://www.w3.org/2005/Atom"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
                xmlns:admin="http://webns.net/mvcb/"
                xmlns:l="http://purl.org/rss/1.0/modules/link/"
                xmlns:content="http://purl.org/rss/1.0/modules/content/">

	<xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <!--
	This XSL Transform is written by
	Dan MacTough - http://www.yabfog.com/ - http://blogs.opml.org/yabfog/
	
	Thanks to iconophobia and the inlineRSS plugin for some code and ideas.
	http://www.iconophobia.com/wordpress/?page_id=55
    -->
	<xsl:param name="linkTarget"/>
	<xsl:param name="path"/>

    <!-- RSS 1.0/RDF -->
	<xsl:template match="/rdf:RDF">
		<xsl:apply-templates select="rss:item" />
	</xsl:template>
	<xsl:template match="rss:item">
	    <xsl:call-template name="rssItem">
	        <xsl:with-param name="title"><xsl:value-of select="rss:title"/></xsl:with-param>
	        <xsl:with-param name="link"><xsl:value-of select="rss:link"/></xsl:with-param>
	        <xsl:with-param name="encLink"><xsl:value-of select="enc:enclosure/enc:Enclosure/enc:url"/></xsl:with-param>
	    </xsl:call-template>
	</xsl:template>

    <!-- RSS 0.9x, 2.0 -->
	<xsl:template match="/rss">
		<xsl:apply-templates select="channel" />
	</xsl:template>
	<xsl:template match="channel">	    
		<xsl:for-each select="item">
    	    <xsl:call-template name="rssItem">
    	        <xsl:with-param name="title">
    	            <!-- Only one of title or description is a required element -->
					<xsl:choose>
						<xsl:when test="title != ''">
							<xsl:value-of select="title"/>
						</xsl:when>
						<xsl:when test="description != ''">
							<xsl:value-of select="description"/>
						</xsl:when>
						<xsl:otherwise>[No title or description]</xsl:otherwise>
					</xsl:choose>
    	        </xsl:with-param>
    	        <xsl:with-param name="titleIsMarkup">
					<xsl:choose>
						<xsl:when test="title != ''">FALSE</xsl:when>
						<xsl:when test="description != ''">TRUE</xsl:when>
						<xsl:otherwise>FALSE</xsl:otherwise>
					</xsl:choose>
    	        </xsl:with-param>
    	        <xsl:with-param name="link"><xsl:value-of select="link"/></xsl:with-param>
    	        <xsl:with-param name="encLink"><xsl:value-of select="enclosure/@url"/></xsl:with-param>
    	    </xsl:call-template>
		</xsl:for-each>
	</xsl:template>

    <!-- Atom 0.3 -->
    <xsl:template match="/atom:feed">
		<xsl:apply-templates select="atom:entry"/>
    </xsl:template>
    <xsl:template match="atom:entry">
	    <xsl:call-template name="rssItem">
	        <xsl:with-param name="title"><xsl:value-of select="atom:title"/></xsl:with-param>
	        <xsl:with-param name="link"><xsl:value-of select="atom:link[@rel='alternate']/@href"/></xsl:with-param>
	        <xsl:with-param name="encLink"><xsl:value-of select="atom:link[@rel='enclosure']/@href"/></xsl:with-param>
	    </xsl:call-template>
	</xsl:template>
	
    <!-- Atom 1.0 -->
	<xsl:template match="/atom10:feed">
	    <xsl:apply-templates select="atom10:entry"/>
	</xsl:template>
	<xsl:template match="atom10:entry">
	    <xsl:call-template name="rssItem">
	        <xsl:with-param name="title"><xsl:value-of select="atom10:title"/></xsl:with-param>
	        <xsl:with-param name="link"><xsl:value-of select="atom10:link[@rel='alternate']/@href"/></xsl:with-param>
	        <xsl:with-param name="encLink"><xsl:value-of select="atom10:link[@rel='enclosure']/@href"/></xsl:with-param>
	    </xsl:call-template>
	</xsl:template>

	<xsl:template name="rssItem">
	    <xsl:param name="title"/>
	    <xsl:param name="titleIsMarkup"/>
	    <xsl:param name="link"/>
	    <xsl:param name="linkTarget"/>
	    <xsl:param name="encLink"/>
		<xsl:element name="li">
    		<xsl:attribute name="class">
    		    <xsl:text>outlineItem</xsl:text>
    		</xsl:attribute>
		    <xsl:choose>
		        <xsl:when test="$link != ''">
            		<xsl:element name="a">
            			<xsl:attribute name="href">
            			    <xsl:value-of select="$link"/>
        			    </xsl:attribute>
        				<xsl:if test="$linkTarget != ''">
        					<xsl:attribute name="target">
        						<xsl:value-of select="$linkTarget"/>
        					</xsl:attribute>
        				</xsl:if>
        				<xsl:choose>
        				    <xsl:when test="$titleIsMarkup='TRUE'">
        				        <xsl:value-of select="$title" disable-output-escaping="yes"/>
        				    </xsl:when>
        				    <xsl:otherwise>
        				        <xsl:value-of select="$title"/>
        				    </xsl:otherwise>
        				</xsl:choose>
                    </xsl:element>
		        </xsl:when>
    		    <xsl:otherwise>
        				<xsl:choose>
        				    <xsl:when test="$titleIsMarkup='TRUE'">
        				        <xsl:value-of select="$title" disable-output-escaping="yes"/>
        				    </xsl:when>
        				    <xsl:otherwise>
        				        <xsl:value-of select="$title"/>
        				    </xsl:otherwise>
        				</xsl:choose>
    		    </xsl:otherwise>
		    </xsl:choose>
			<xsl:if test="$encLink != ''">
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="$encLink"/>
					</xsl:attribute>
					<xsl:attribute name="style">padding-left: 3px; border: none; text-decoration: none;</xsl:attribute>
					<img src="{$path}/img/speaker.gif" alt="Enclosure" title="Enclosure"></img>
				</xsl:element>
			</xsl:if>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
