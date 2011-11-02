<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">



<head>
	<title>Millersville University - Public Lecture: "Power and Gender Inside the Italian Slow Food Movement"</title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-type"/>
	<meta content="en-us" http-equiv="Content-Language"/>

	
	

	

	<link href="../../../../../lib/v2/css/core.css" media="screen" rel="stylesheet" type="text/css"/>
<link href="../../../../../lib/v2/css/content.css" media="screen, print" rel="stylesheet" type="text/css"/>

	<link href="../../../../../lib/v2/css/subpage.css" media="screen, print" rel="stylesheet" type="text/css"/>

	

	<link href="../../../../../lib/v2/css/print.css" media="print" rel="stylesheet" type="text/css"/>
	<!--[if lt IE 7]>
		<link href="http://www.millersville.edu/lib/v2/css/ie6.css" media="screen" rel="stylesheet" type="text/css" />
	<![endif]-->	
</head>

<body>
	
	<div id="header">
<div id="banner">
	
		<?
			$seasons = array(
				'summer' => array(6, 7, 8),
				'autumn' => array(9, 10, 11),
				'winter' => array(12, 1, 2),
				'spring' => array(3, 4, 5)
			);
			
			$current_date = getdate();
			
			foreach($seasons as $season => $months)
			{
				if(in_array($current_date['mon'], $months))
					$logo_season = $season;
			}
		?>		
	
		<a href="http://www.millersville.edu/" id="branding-logo" title="Millersville Home"><img alt="Millersville University: Seize the Opportunity" src="/lib/v2/img/common/logo_<?=$logo_season;?>.jpg" title="Millersville University: Seize the Opportunity" /></a>
	
<div id="banner-helper">
			
<ul id="nav-banner">
<li><a href="../../../../../index.php">MU Home</a> | </li>
<li><a href="../../../../../siteindex.php" id="topSiteIndexLink">Site Index</a> | </li>
<li><a href="../../../../../contact.php">Contact</a> | </li>
<li><a href="../../../../../directory/index.php">People Finder</a></li>
</ul>
<div id="banner-siteindex">
<p>Click on a letter below to browse the Millersville website index or view the <a href="../../../../../siteindex.php">full site index</a>.</p>

				
<ul id="nav-siteindex">
<?
					foreach(range('A','Z') as $letter)
					{
						echo '
<li><a href="http://www.millersville.edu/siteindex.php?l='.strtolower($letter).'" title="Site Index: '.$letter.'">'.$letter.'</a></li>
'."\r\n";	
					}
				?>
				
</ul>

			</div>
<form action="http://www.millersville.edu/searchresults.php" id="search-mu" method="get" name="search">
<div id="search-mu-keywordwrapper">
					<input id="search-mu-keyword" name="q" type="text" value="Type search here"/>
					<input id="search-mu-submit" type="submit" value="go"/>
				</div>
<div id="search-mu-selector">
					<label><input checked="checked" class="search-mu-radio" id="search-mu-radio-pages" name="type" type="radio" value="pages"/> Pages </label>
					<label><input class="search-mu-radio" id="search-mu-radio-people" name="type" type="radio" value="people"/> People</label>	
				</div>
<div id="search-suggested">
<div id="search-suggested-shadow"></div>
<div id="search-suggested-output"></div>
</div>
</form>				
		</div>
<ul id="nav-utility">

			<?
				require_once('/var/apache2/millersville/lib/v2/inc/weatherClass.php');
				$weather = new MUWeather();
			?>
<li><? $weather->printWeatherHeading(); ?></li>

<li class="noBorder"><a href="javascript: window.print();" id="printpage">Print this page&#160;<img alt="Print this Page" src="../../../../../lib/img/icons_bullets/icon_print.gif"/></a></li>
</ul>
</div>
</div>
	
	<div id="navigation">
<div>
		
<ul id="nav-main">
<li class="first"><a href="../../../../../admissions/index.php" id="admissions">Admissions</a></li>
<li><a href="../../../../../academics/index.php" id="academics">Academics</a></li>
<li><a href="../../../../../currentstudents/index.php" id="campus">Current Students<br/></a></li>
<li><a href="../../../../../facultyandstaff/index.php" id="facstaff">Faculty &amp; Staff</a></li>
<li><a href="../../../../../parents/index.php" id="parents">Parents</a></li>
<li><a href="../../../../../visitors/index.php" id="visitors">Visitors &amp; Community</a></li>
</ul>
</div>
</div>
	
	<div id="content-wrapper">
		<div id="content">
		
		<? require_once('/var/apache2/millersville/lib/inc/alert.php'); ?>
		
		
			<h1 id="page-title">Public Lecture: "Power and Gender Inside the Italian Slow Food Movement"</h1>
			
			
			
			<div id="sidebar">
				<ul id="nav-section"><li><a href="../../../../index.php" title="University Calendar">University Calendar</a></li><li><a href="http://www.millersville.edu/registrar/academic-calendar/" title="Academic Calendar">Academic Calendar</a></li></ul>
				
				
<a href="?date=<?=date('d-m-Y', $time_now).'&mode=day';?>" class="button-link">MU Today</a>
<div class="sidebar-module">
<div class="module-content">
		<?=$cal->generate_minical();?>
	</div>
</div>

				
				
				
			</div>
			
			<div id="content-main">
				
				<div class="content-module"><h1>Public Lecture: "Power and Gender Inside the Italian Slow Food Movement"</h1><div class="module-content"><img align="right" alt="Public Lecture: &quot;Power and Gender Inside the Italian Slow Food Movement&quot;" src="valeria_siniscalchi.JPG" style="margin-left: 10px;"/><p><strong>When:</strong>11/15/2010at 04:00 PM</p><p><strong>Where:</strong>&#160;Roddy Hall, Room 261</p><p>Description: On Monday, November 15, from 4-5:30 p.m., Dr. Valeria Siniscalchi, cultural anthropologist and Ma&#238;tre de Conf&#233;rences, Centre Norbert Elias, l'&#201;cole des Hautes &#201;tudes en Sciences Sociales (EHESS), Marseille, will present a public lecture on the topic: "Power and Gender Inside the Italian Slow Food Movement." The lecture will take place on campus in Roddy Hall, room 261.</p>
<p>This lecture will be based on Siniscalchi's ongoing ethnographic research on the Slow Food organization centered in Bra, Italy. Slow Food is an international organization and movement devoted to promoting "good, clean and fair food." It has more than 100,000 dues-paying members in 130 countries around the globe. Founded by Carlo Petrini and associates in 1989, it has grown to have an international influence due to its membership, powerful media presence and enthusiasm of thousands of volunteers who work to establish school gardens and farmers' markets, promote local culinary culture, hold wine and food tastings, and carry out myriad other activities. It has a strong U.S. presence through Slow Food USA and is on the vanguard of global food activism.</p><h3>Contact Info:</h3><ul><li>Dr. Carole Counihan</li><li><strong>Email:</strong>&#160;<a href="mailto:Carole.Counihan@millersville.edu">Carole.Counihan@millersville.edu</a></li><li><strong>Tel:</strong>&#160;717-872-3575</li></ul><p style="margin-top:30px;">Return to <a href="../../../../index.php">University Calendar</a></p></div></div>				
			</div>
			
		</div>
	</div>
	
	<div id="footer-wrapper">
		<div id="footer">
<div id="footer-legal">
<ul id="nav-supp">
<li><a href="http://www.millersville.edu/pfru.php">Policy for Responsible Use</a> |</li>
<li><a href="http://www.millersville.edu/righttoknow.php" title="Right To Know Policy">Right-to-Know Policy</a> |</li>
<li><a href="http://www.millersville.edu/heoa.php">Student Consumer Information</a> |</li>
<li><a href="../../../../../services/hr/employment/index.php" title="Employment Opportunities at Millersville">Employment</a></li>
</ul>
<p>Millersville University is an Affirmative Action/Equal Opportunity institution;<br/>
 a member of the Pennsylvania State System of Higher Education</p>

<p>&#169; <?=date('Y');?> Millersville University. All Rights Reserved.</p>

</div>
<div id="footer-contact">
<address>Millersville University<br/>
 PO Box 1002<br/>
 1 South George St.<br/>
 Millersville, PA 17551</address>
<p class="phone"><img alt="Phone Number" src="../../../../../lib/v2/img/common/contact-phone.gif"/>(717) 872-3011</p>
<p class="contact"><img alt="Contact" src="../../../../../lib/v2/img/common/contact-envelope.gif"/><a href="http://www.millersville.edu/contact.php">Contact Us</a></p>
</div>
<div id="footer-portals">
<ul>
<li><a href="https://muhp5.millersville.edu/pls/prod/twbkwbis.P_WWWLogin" title="MAX: Banner Portal">MAX</a></li>
<li><a href="http://myville.millersville.edu/" title="my'Ville: Campus Portal">my'VILLE</a></li>
<li><a href="http://wiki.millersville.edu/display/d2ldocs" title="Desire2Learn: Student Course Management System">Desire2Learn</a></li>
<li><a href="http://m.millersville.edu/" title="Mobile Web">Mobile Web</a></li>
</ul>
</div>
</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js" type="text/javascript"></script> 
<script src="../../../../../lib/v2/js/jquery.millersville.js" type="text/javascript"></script>
	
	
	
	
<? include_once('/var/apache2/millersville/lib/inc/master_google_analytics.php'); ?>

	
</body>
</html>