<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [<!ENTITY nbsp " ">]>
<xsl:stylesheet exclude-result-prefixes="date long" extension-element-prefixes="date-converter" version="1.0" xmlns:date="http://xml.apache.org/xalan/java/java.util.Date" xmlns:date-converter="http://www.hannonhill.com/dateConverter/1.0/" xmlns:long="http://xml.apache.org/xalan/java/java.lang.Long" xmlns:xalan="http://xml.apache.org/xalan" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output indent="yes" method="xml"/>
		
	<xsl:template match="/system-index-block">
		<xsl:apply-templates select="//system-page">
			<xsl:sort select="date-converter:dateToStamp(string(dynamic-metadata[name='start-date']/value), string(dynamic-metadata[name='start-time']/value))" data-type="number" order="ascending" /> 
		</xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="system-page">
		
		<xsl:variable name="startDate">
			<xsl:choose>
				<xsl:when test="dynamic-metadata[name='start-date']/value != '' and dynamic-metadata[name='start-time']/value != '' and date-converter:dateToStamp(string(dynamic-metadata[name='start-date']/value), string(dynamic-metadata[name='start-time']/value)) != 'NaN'">
					<xsl:value-of select="date-converter:dateToStamp(string(dynamic-metadata[name='start-date']/value), string(dynamic-metadata[name='start-time']/value))" />
				</xsl:when>
				<xsl:when test="dynamic-metadata[name='start-date']/value != '' and count(dynamic-metadata[name='start-time']/value) = 0">
					<xsl:value-of select="date-converter:dateToStamp(string(dynamic-metadata[name='start-date']/value), string('12:00 AM'))" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="substring(string(created-on), 1, 10)" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<xsl:variable name="endDate">
			<xsl:choose>
				<xsl:when test="dynamic-metadata[name='end-date']/value != '' and dynamic-metadata[name='end-time']/value != '' and date-converter:dateToStamp(string(dynamic-metadata[name='end-date']/value), string(dynamic-metadata[name='end-time']/value)) != 'NaN'">
					<xsl:value-of select="date-converter:dateToStamp(string(dynamic-metadata[name='end-date']/value), string(dynamic-metadata[name='end-time']/value))" />
				</xsl:when>
				<xsl:when test="dynamic-metadata[name='end-date']/value != '' and count(dynamic-metadata[name='end-time']/value) = 0">
					<xsl:value-of select="date-converter:dateToStamp(string(dynamic-metadata[name='end-date']/value), string('11:59 PM'))" />
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		
		<xsl:variable name="isFeatured">
			<xsl:choose>
				<xsl:when test="system-data-structure/audience/homepage = 'Yes'">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<event id="{@id}" featured="{$isFeatured}">
			<title><xsl:value-of select="title" /></title>
			<createdon><xsl:value-of select="substring(string(created-on), 1, 10)" /></createdon>
			<startdate><xsl:value-of select="$startDate" /></startdate>
			<enddate><xsl:value-of select="$endDate" /></enddate>
			<shortdesc><xsl:value-of select="system-data-structure/event-descriptions/short-description" /></shortdesc>
			<fulldesc>
				<xsl:copy-of select="system-data-structure/event-descriptions/full-description/node()" />
			</fulldesc>
			<keywords><xsl:value-of select="keywords" /></keywords>
			<categories>
				<xsl:apply-templates select="system-data-structure/audience/applicable-audiences/value" />
			</categories>
			<location><xsl:value-of select="system-data-structure/event-location" /></location>
			<contact>
				<name><xsl:value-of select="system-data-structure/event-contact/name" /></name>
				<phone><xsl:value-of select="system-data-structure/event-contact/phone" /></phone>
				<email><xsl:value-of select="system-data-structure/event-contact/email" /></email>
			</contact>
			<image>
				<thumb><xsl:if test="system-data-structure/event-image/thumb/path != '/'">http://www.millersville.edu<xsl:value-of select="substring-after(system-data-structure/event-image/thumb/path, '/millersville')" /></xsl:if></thumb>
				<large><xsl:if test="system-data-structure/event-image/large/path != '/'">http://www.millersville.edu<xsl:value-of select="substring-after(system-data-structure/event-image/large/path, '/millersville')" /></xsl:if></large>
			</image>
			<relatedlinks>
				<xsl:apply-templates select="system-data-structure/related[link-name != '']" />
			</relatedlinks>
		</event>
	</xsl:template>
	
	<xsl:template match="applicable-audiences/value">
		<cat>
			<xsl:call-template name="strtolower"><xsl:with-param name="nodeValue" select="." /></xsl:call-template>
		</cat>
	</xsl:template>
	
	<xsl:template match="related">
		<link>
			<name><xsl:value-of select="link-name" /></name>
			<url>
			<xsl:choose>
				<xsl:when test="link-url != '' and link-url != 'http://'">
					<xsl:value-of select="link-url" />
				</xsl:when>
				<xsl:when test="link-page/path != '/'">
					http://www.millersville.edu<xsl:value-of select="substring-after(link-page/path, '/millersville')" />
				</xsl:when>
				<xsl:when test="link-file/path != '/'">
					http://www.millersville.edu<xsl:value-of select="substring-after(link-file/path, '/millersville')" />
				</xsl:when>
			</xsl:choose>
			</url>
		</link>
	</xsl:template>
	
	<xsl:template name="strtolower">
		<xsl:param name="nodeValue" />
		<xsl:value-of select="translate($nodeValue, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz')" />
	</xsl:template>
	
	<!-- Xalan component for date conversion from CMS date format to RSS 2.0 pubDate format -->
	<xalan:component functions="dateToStamp, convertDate" prefix="date-converter">
		<xalan:script lang="javascript">
			<!-- Returns the UTC timestamp of the date passed in format 'mm/dd/yyyy' -->
			function dateToStamp(date, time)
			{
				var timeRegExp = /[0-9]{1,2}:[0-9]{1,2}/i;
				var meridiemRegExp = /am|pm/i;
				
				var d = Date.parse(date + ' ' + time.match(timeRegExp) + ' ' + time.match(meridiemRegExp));
				return Math.round(d / 1000);
			}
		
			<!-- display the date and time in format "m/d/yyyy h:mm AM|PM" -->
			function convertDate(date)
			{
				var d = new Date(date); // Splits date into components 
				var month = d.getMonth() + 1;
				var date = d.getDate();
				var year = d.getFullYear();;
  				var monthString = "";
				if (month==1) monthString = "January";
				else if (month==2) monthString = "February";
				else if (month==3) monthString = "March";
				else if (month==4) monthString = "April";
				else if (month==5) monthString = "May";
				else if (month==6) monthString = "June";
				else if (month==7) monthString = "July";
				else if (month==8) monthString = "August";
				else if (month==9) monthString = "September";
				else if (month==10) monthString = "October";
				else if (month==11) monthString = "November";
				else if (month==12) monthString = "December";
								
  				return monthString + " " + date + ", " + year;
			}
		</xalan:script>
	</xalan:component>	
</xsl:stylesheet>		