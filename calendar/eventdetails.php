<?php
	session_start();
    
    // If a date was specified, there is a potential it may be from another year, 
    // so we need to override the _SESSION date.
    if (isset($_GET['date'])) {
    	$time_now = getdate();
    	$time_now = mktime(0, 0, 0, $time_now['mon'], $time_now['mday'], $time_now['year']);
    	$args['date'] = preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/', $_GET['date']) ? $_GET['date'] : $time_now;
    } else {    
		// Try to grab the date that the user was looking prior to getting here.
		// This will help us search the appropriate XML file.
		preg_match('/date=([0-9]{2}-[0-9]{2}-[0-9]{4})/', $_SESSION['QUERY_STRING'], $matches);
	    $args['date'] = $matches[1];
	}
        
    require_once('lib/university_calendar.php');    
    
    $args['urlToCalendar'] = 'http://www.millersville.edu/calendar/';
    $args['pathToCalendar'] = $_SERVER['DOCUMENT_ROOT'].'/calendar/';
    

    $cal = new UniversityCalendar($args);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Millersville University - University Calendar Event Details</title>
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

		<? if (!$event = $cal->getEventById($_GET['id'])) { ?>
	        <div class="content-module">
                <h1>The requested event could not be found</h1>
                <div class="module-content">
                    <h3>What to do from here?</h3>
                    <ul>
                        <li>Use the "Search University Events" field or choose a Category to the left</li>
                        <li>Return to the <a href="index.php">Calendar Homepage</a></li>
                    </ul>
                </div>
	        </div>
		<? } else { ?>  
	        <div class="content-module">
                <h1><?=$event['title'];?></h1>
                <div class="module-content">            
                    <? $cal->displayEventSocialMediaBox($event['id']); ?>
                	<div style="clear:both;"></div>
                    <dl id="event-details">
                        <dt>When</dt>
                        <dd><?=date('D, F j, Y @ h:i A', $event['startdate']);?></dd>
                    <? if ($event['location'] != '') { ?>
                        <dt>Location</dt>
                        <dd><?=$event['location'];?></dd>
                    <? } ?>
                    <? if ($event['contact']['name'] != '') { ?>
                        <dt>Contact</dt> 
                        <dd><?=($event['contact']['email'] != '' ? ' <a href="mailto:'.$event['contact']['email'].'">'.$event['contact']['name'].'</a>' : $event['contact']['name']);?>
                        <?=($event['contact']['phone'] != '' ? '<br />'.$event['contact']['phone'] : '');?></dd>
                    <? } ?>
                    </dl>
                    <? 
                    $eventImagePath = null;
                    if ($event['image']['large'] != '') { 
                        $eventImagePath = $event['image']['large'];
                    } else if ($event['image']['thumb'] != '') {
						$eventImagePath = $event['image']['thumb'];
                    }
                    
                    if ($eventImagePath && file_exists(str_replace('http://www.millersville.edu', $_SERVER['DOCUMENT_ROOT'], $eventImagePath))) {
						?><img src="<?=$eventImagePath;?>" alt="<?=$event['title'];?>" class="img-right-align" /><?
                    }
                    ?>
                    
                    <div id="event-description">
						<?=utf8_encode(html_entity_decode($event['fulldesc']));?>
                	</div>
                
                    <? if (count($event['relatedlinks']) > 0) { ?>
                        <h4>Related Links:</h4>
                        <ul>
                        <? foreach ($event['relatedlinks'] as $link) { ?>
                            <? if(!empty($link['name'])) { ?>
                                    <li><a href="<?=$link['url'];?>"><?=$link['name'];?></a></li>
                            <? } ?>
                        <? } ?>
                        </ul>
                    <? } 
                ?></div>
	        </div>
		<? } ?>               
				
		</div>
			
	</div></div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script> 
	
	<script type="text/javascript">
			
		$(document).ready(function(){
			$('#cal-search input[name="q"]')
				.focus(function() {
					if(this.value == this.defaultValue)
						this.value = '';
					else if(this.value != '')
						$('#search-suggested').fadeIn('fast');
				})
		
				.blur(function() {
					if(this.value == '')
						this.value = 'Type search here';
					
					$('#search-suggested').fadeOut('fast');
				});
		});
			
	</script>
	
</body>
</html>