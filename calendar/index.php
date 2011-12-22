<?php
	session_start();
	
	$_SESSION['QUERY_STRING'] = $_SERVER['QUERY_STRING'];

    require_once('lib/university_calendar.php');
    
    // Get the current date.
    $time_now = getdate();
    $time_now = mktime(0, 0, 0, $time_now['mon'], $time_now['mday'], $time_now['year']);
    
    // Define the URL for the mini calendar and the server path to the calendar.
    $args['urlToCalendar'] = 'http://www.millersville.edu/calendar/';
    $args['pathToCalendar'] = $_SERVER['DOCUMENT_ROOT'].'/fornathan/calendar/';
     
	// Grab the query and category parameters and run some sanitization (i.e. remove tags and encode special characters).
	$args['query'] = isset($_GET['q']) ? filter_var($_GET['q'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_HIGH) : '';
	$args['category'] = isset($_GET['c']) ? filter_var($_GET['c'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_HIGH) : '';
	
	// Was a date and/or mode specified?
	if (!isset($_GET['date']) && !isset($_GET['mode'])) { 
        $args['date'] = $time_now;
        $args['mode'] = 'index';
	} else {
		// If a date and/or mode were given, validate them.      
		$args['date'] = isset($_GET['date']) && preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/', $_GET['date']) ? $_GET['date'] : $time_now;
		$args['mode'] = isset($_GET['mode']) && in_array($_GET['mode'], array('day','week','month','index')) ? $_GET['mode'] : 'month';
	}
	
	// Initiate the calendar.
	$cal_data = array('query' => $args['query'], 'category' => $args['category'], 'date' => $args['date'], 'mode' => $args['mode']);    
    $cal = new UniversityCalendar($args);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Millersville University - University Calendar</title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-type"/>
	<meta content="en-us" http-equiv="Content-Language"/>

	<meta content="calendar, millersville university" name="keywords"/>
	<meta content="Events Calendar" name="description"/>

	<link href="../lib/css/core.2.1.css" media="all" rel="stylesheet" type="text/css"/>	
	
	<link href="lib/calendar.css" media="screen, print" rel="stylesheet" type="text/css"/>
</head>

<body>

	<div id="content-wrapper"><div id="content">
			
		<div id="sidebar">
	
			<a class="button-link" href="index.php?c=cultural">Cultural Events</a>				
			<a href="index.php?date=<?=date('d-m-Y').'&mode=day';?>" class="button-link">MU Today</a>
			<div class="sidebar-module">
				<div class="module-content">
					<?=$cal->generateMinical();?>
				</div>
			</div>

			<div class="sidebar-module">
				<h1>Categories</h1>
				<div class="module-content">				
					<?=$cal->generateCategoryList();?>
				</div>
			</div>
			
			<div class="sidebar-module">
				<h1>Search University Events</h1>
				<div class="module-content">
					<p>To search the Millersville University Calendar, type a keyword in the field below and click the <strong>"Go"</strong> button.</p>
					<form action="index.php" id="cal-search" method="get">
						<input name="date" type="hidden" value="<?=$args['date'];?>" />
						<input name="mode" type="hidden" value="<?=$cal->dateMode;?>" />
						<input name="c" type="hidden" value="<?=$cal->category;?>" />
						
						<input name="q" size="22" type="text" placeholder="Search calendar" value="<?=$cal->query;?>" />							
						<button type="submit">GO</button>
					</form>
				</div>
			</div>
			
			<div class="sidebar-module">
				<h1>Request to Add an Event</h1>
				<div class="module-content">
					<p>Please fill out the <a href="eventrequest.php">event request form</a> if you have an event that you would like showcased on the Millersville University calendar.</p>
				</div>
			</div>
		</div>
			
		<div id="content-main">
			
		<? if (!empty($cal->query) || (!empty($cal->category) && $cal->category != 'all')) { ?>
			<div class="form-success">
			<? if (!empty($cal->query)) { 
				?><h4>Search Results</h4><?
			} ?>
				<p>You are currently browsing events based on the <?=!empty($cal->query) ? 'keyword(s) <b>"'.$cal->query.'"</b> and' : '';?> category <b>"<?=!empty($cal->category) ? $cal->category : 'All';?>"</b> in the date range of <b><?=date('M j, Y',$cal->periodStart);?></b> to <b><?=date('M j, Y',$cal->periodEnd);?></b>. <a href="index.php">Reset calendar.</a></p>
				</div>
		<? } ?>


		<? 
		// Generate the ongoing and upcoming events arrays.
		$cal->getUpcomingEvents(); 
		?>

		<? 
		// List any ongoing events.
		if (count($cal->ongoingEvents) > 0) { ?>
			<div class="content-module">
				<h1>Ongoing Events</h1>
				<div class="module-content">
				<ul>
				<? foreach($cal->ongoingEvents as $date => $event) { ?>
					<li>
						<b><?=date('M d', $event[0]['startdate']);?><?=$event[0]['enddate'] ? ' - '.date('M d', $event[0]['enddate']) : '';?></b> <a href="eventdetails.php?id=<?=$event[0]['id'];?>"><?=$event[0]['title'];?></a>
					</li>
				<? } ?>
				</ul>
				</div>
			</div>
		<? } ?>

			<div class="content-module">
			
			<? 
			// List the upcoming events.
			if (count($cal->upcomingEvents) < 1) { ?>
				<h1>Current &amp; Upcoming Events</h1>
				<div class="module-content">
					<p>There are no events currently scheduled for this time. Please check back for updates.</p>
				</div><?
			} else { 
				$queryString = '?q='.$cal->query.'&c='.$cal->category.'&date='.date('d-m-Y',$cal->userDate).'&mode='.$cal->dateMode;
			?>
				<h1>Current &amp; Upcoming Events <span id="upcoming-events-tools"><a title="Expand all event details" href="#" class="cal-events-toggler expand">Expand All</a> | <a title="Collapse all event details" href="#" class="cal-events-toggler collapse">Collapse All</a> <a title="View RSS Feed" id="event-tools-rss" href="rss.php<?=$queryString;?>"><img class="noborder" src="lib/rss.png" alt="" /></a></span></h1>
				
				<div class="module-content">
				<? foreach ($cal->upcomingEvents as $date => $times) { ?>
					<dl class="cal-events-wrapper">
						<dt class="cal-events-date">
			        		<?=date('M', (int)$date);?><b><?=date('d', (int)$date);?></b>
						</dt>
						<dd class="cal-events-list">
							<span class="cal-date-carrot<?=$counter%2==0 ? ' zebra' : '';?>">&nbsp;</span>
						<?              
			        		ksort($times);
			            	foreach ($times as $time => $events) {                                                               
			                	foreach ($events as $event) {                                                                                                                                                                       
			                    	if (!empty($cal->query)) {
										$event['title'] = preg_replace('/'.$cal->query.'/i', '<b>$0</b>', htmlentities($event['title']));                                                                                               
										$event['shortdesc'] = preg_replace('/'.$cal->query.'/i', '<b>$0</b>', $event['shortdesc']);
									}
			            			?><dl class="cal-event<?=$counter%2==0 ? ' zebra' : '';?>">
										<dt>
											<a href="eventdetails.php?id=<?=$event['id'];?>">
			                                	<?=html_entity_decode($event['title']);?>  
			                                	<? if ($time!='am 12:00') { ?><i><?=date('h:i a', $event['startdate']);?></i> <? } ?>
			                                </a>
										</dt>
										<dd>
			
										<? 
										$eventImagePath = '';
										if ($event['image']['thumb'] != '') { 
											$eventImagePath = $event['image']['thumb'];
										} else if ($event['image']['large'] != '') {
											$eventImagePath = $event['image']['large'];
										}
										
										if ($eventImagePath) {
											$systemPath = str_replace('http://www.millersville.edu', $_SERVER['DOCUMENT_ROOT'], $eventImagePath);
											
											if (file_exists($systemPath)) {							
												?><a href="eventdetails.php?id=<?=$event['id'];?>"><img src="<?=$eventImagePath;?>" alt="<?=$event['title'];?>" class="cal-listing-image" /></a><?
											}
										}
										?>
										
											<div class="cal-listing-content">
												<p><?=$event['shortdesc'];?> ... <a class="view-more" href="eventdetails.php?id=<?=$event['id'];?>">View More</a></p>
												<ul class="cal-listing-info">
													<li>
														<b>When:</b> <?=date('l, F j, Y', $event['startdate']);?>
														<? if ($time!='am 12:00') { ?> @ <?=date('h:i a', $event['startdate']);?><? } ?>		
													</li>
														
												<? if ($event['location'] != '') { ?>
													<li><b>Where:</b> <?=$event['location'];?></li>
												<? } ?>
			                                            
												<? if ($event['contact']['name'] != '') { ?>
													<li><b>Contact:</b> <?=($event['contact']['email'] != '' ? ' <a href="mailto:'.$event['contact']['email'].'">'.$event['contact']['name'].'</a>' : $event['contact']['name']);?><?=($event['contact']['phone'] != '' ? '; <b>Phone:</b> '.$event['contact']['phone'] : '');?>
													</li>
												<? } ?>
			                                            
												<? if (count($event['relatedlinks']) > 0) { ?>
													<li><b>Related Links:</b>
														<ul>
														<? 
														foreach ($event['relatedlinks'] as $link) {
			                                            	if(!empty($link['name'])) {                                                                                                                                                                          
			                                                	echo '<li><a href="'.$link['url'].'">'.$link['name'].'</a></li>';
															}
														} 
														?>
														</ul>
													</li>
												<? } ?>
												</ul>
											</div>
										</dd>
									</dl><?
								$counter++;
							}
			        	} ?>
			        </dd>
				</dl><?
				} 
			} ?>
			</div>
		</div>
	</div></div>	

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script> 
		
	<script type="text/javascript">
				
	$(document).ready(function(){					
		$('.cal-event dt')				
			.toggle(
				function() {
					$(this).next('dd').animate({"height": "toggle", "opacity": "toggle"}, 400, 'swing');
				},
				function() {
					$(this).next('dd').animate({"height": "toggle", "opacity": "toggle"}, 400, 'swing');
				}
			)
			.children('a').removeAttr('href');
			
		$('.cal-event dd:first').animate({"height": "toggle", "opacity": "toggle"}, 400, 'swing');
		
		$('.cal-events-toggler.expand').click(function() {
			$('.cal-event dd').slideDown(400);
			return false;
		});
		
		$('.cal-events-toggler.collapse').click(function() {
			$('.cal-event dd').slideUp(400);
			return false;
		});
	});
				
	</script>
		
</body>
</html>