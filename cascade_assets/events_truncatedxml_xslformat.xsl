<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [<!ENTITY nbsp " ">]>
<xsl:stylesheet exclude-result-prefixes="date long" extension-element-prefixes="date-converter" version="1.0" xmlns:date="http://xml.apache.org/xalan/java/java.util.Date" xmlns:date-converter="http://www.hannonhill.com/dateConverter/1.0/" xmlns:long="http://xml.apache.org/xalan/java/java.lang.Long" xmlns:xalan="http://xml.apache.org/xalan" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output indent="yes" method="xml"/>
	<xsl:strip-space elements="uml package" />
	
	<!-- Global Variables -->
	<xsl:variable name="maxEvents" select="10" /> <!-- Define the number of events to show for each category -->
	
	<xsl:template match="/system-index-block">
	
		<xsl:variable name="eventCount" select="count(system-folder/system-folder/system-page[system-data-structure/audience/homepage = 'Yes'][system-data-structure/audience/applicable-audiences/value != 'Dining'][date-converter:startDateToStamp(string(dynamic-metadata[name='start-date']/value)) &gt;= date-converter:currentDate()][system-data-structure/event-descriptions/short-description != ''])" />
		
		<xsl:if test="$eventCount &gt; 0">

			<xsl:for-each select="system-folder/system-folder/system-page[system-data-structure/audience/homepage = 'Yes'][date-converter:startDateToStamp(string(dynamic-metadata[name='start-date']/value)) &gt;= date-converter:currentDate()][system-data-structure/event-descriptions/short-description != '']">
				
				<xsl:sort select="date-converter:startDateToStamp(string(dynamic-metadata[name='start-date']/value))" data-type="number" order="ascending" /> 
				
				<xsl:if test="position() &lt;= $maxEvents">
				
					<xsl:variable name="timeStamp" select="date-converter:startDateToStamp(string(dynamic-metadata[name='start-date']/value))" />

					<event>
						<event-id value="{@id}" />
						<start-time value="{date-converter:convertDate(number($timeStamp))}" />
						<title value="{title}" />
						<short-description value="{system-data-structure/event-descriptions/short-description}" />
						<full-description>
							<xsl:copy-of select="system-data-structure/event-descriptions/full-description/node()" />
						</full-description>
						<path value="http://www.millersville.edu/calendar/eventdetails.php?id={@id}&amp;date={date-converter:dateToDDMMYYY(number($timeStamp))}" />
					</event>

				</xsl:if>
				
			</xsl:for-each>

		</xsl:if>
		
	</xsl:template>

	
	<!-- Xalan component for date conversion from CMS date format to RSS 2.0 pubDate format -->
	<xalan:component functions="startDateToStamp, currentDate, convertDate, dateToDDMMYYY" prefix="date-converter">
		<xalan:script lang="javascript">
			<!-- Returns the UTC timestamp of the date passed in format 'mm/dd/yyyy' -->
			function startDateToStamp(startDate)
			{
				var d = new Date(); //New Empty Date Object
				d.setYear(startDate.substr(6, 4)); //Extract the year
				d.setMonth(startDate.substr(0, 2) - 1, startDate.substr(3, 2)); //Extract the month and date
				
				return d.getTime();
			}

			<!-- Returns the UTC timestamp of the current date -->
			function currentDate()
			{			
				var c = new Date(); //New Empty Date Object
				c.setYear(c.getFullYear()); //Extract the year
				c.setMonth(c.getMonth(), c.getDate()); //Extract the month and date
				
				return c.getTime();
			}
		
			<!-- display the date and time in format "m/d/yyyy h:mm AM|PM" -->
			function convertDate(date)
			{
				var d = new Date(date); // Splits date into components 
				var month = d.getMonth() + 1;
				var date = d.getDate();
				var year = d.getFullYear();
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
			
			<!-- convert the date to format "dd-mm-yyyy" -->
			function dateToDDMMYYY(date)
			{
				var d = new Date(date); // Splits date into components 
				var month = "0" + (d.getMonth() + 1);
				var date = "0" + d.getDate();
				var year = d.getFullYear();
								
				return date.substr(date.length-2) + "-" + month.substr(month.length-2) + "-" + year;
			}
		</xalan:script>
	</xalan:component>	
</xsl:stylesheet>		