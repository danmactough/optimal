<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="rdf rss atom l dc admin content xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                xmlns:rss="http://purl.org/rss/1.0/"
                xmlns:atom="http://purl.org/atom/ns#"
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

	<xsl:template match="/rdf:RDF">
		<xsl:apply-templates select="rss:item" />
	</xsl:template>

	<xsl:template match="rss:item">
		<li>
		<xsl:attribute name="class">
		    <xsl:text>outlineItem</xsl:text>
		</xsl:attribute>
			<xsl:element name="a">
				<xsl:attribute name="href">
					<xsl:value-of select="rss:link"/>
				</xsl:attribute>
				<xsl:value-of select="rss:title"/>
			</xsl:element>
		</li>
	</xsl:template>

	<xsl:template match="/rss">
		<xsl:apply-templates select="channel" />
	</xsl:template>

	<xsl:template match="channel">
		<xsl:for-each select="item">
			<li>
			<xsl:attribute name="class">
			    <xsl:text>outlineItem</xsl:text>
			</xsl:attribute>
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="link"/>
					</xsl:attribute>
					<xsl:value-of select="title"/>
				</xsl:element>
			</li>
		</xsl:for-each>
	</xsl:template>

    <xsl:template match="atom:feed">
		<xsl:apply-templates select="atom:entry"/>
    </xsl:template>
    <xsl:template match="atom:entry">
		<li>
		<xsl:attribute name="class">
		    <xsl:text>outlineItem</xsl:text>
		</xsl:attribute>
			<a href="{atom:link[substring(@rel, 1, 8)!='service.']/@href}">
				<xsl:value-of select="atom:title"/>
			</a>
		</li>
	</xsl:template>

</xsl:stylesheet>
