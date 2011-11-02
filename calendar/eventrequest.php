<?
	session_start();
    require_once('lib/university_calendar.php');    
    
    // Initiate the calendar.
    $cal = new UniversityCalendar(array());
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Millersville University - Events Calendar</title>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-type"/>
	<meta content="en-us" http-equiv="Content-Language"/>

	<meta content="calendar, millersville university" name="keywords"/>
	<meta content="Events Calendar" name="description"/>

	<link href="../lib/css/core.2.1.css" media="all" rel="stylesheet" type="text/css"/>
	<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/themes/redmond/jquery-ui.css" media="screen" rel="stylesheet" type="text/css"/>
	
	<link href="lib/calendar.css" media="screen, print" rel="stylesheet" type="text/css"/>
	
	<style media="screen, print" type="text/css">
		#event-request-form label { overflow: visible; }

		.tooltip {		
			background-color: #005f9b;
			border-radius: 3px; 
			-moz-border-radius: 3px; 
			-webkit-border-radius: 3px; 
			color: #fff;
			cursor: help;
			font: bold 12px/15px Arial, Helvetica, sans-serif;
			text-align: center;
			clear: none;
			display: inline-block;
			*display: inline; /* IE7 Fix */
			margin: 0 5px;
			padding: 2px 6px;
			position: relative;
			z-index: 1;
			height: 14px;
			width: 8px;			
		}
			.tooltip:hover,
			.tooltip:focus {
				color: #fff;
			}
				.tooltip span,
				.tooltip div {
					margin-left: -999em;
					position: absolute;					
					padding:5px;
					background: #555; 
					border:1px solid #888;
					color: #fff;
					font: normal 12px/18px Arial, Helvetica, sans-serif;
					text-align: left;
					position: absolute; 
					width: 260px;
					z-index: 99;
				}
					.tooltip p,
					.tooltip li {
						color: #fff;
					}
				.tooltip:hover span,
				.tooltip:focus span,
				.tooltip:hover div,
				.tooltip:focus div {
					border-radius: 5px; 
					-moz-border-radius: 5px; 
					-webkit-border-radius: 5px; 
					box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.1); 
					-webkit-box-shadow: 5px 5px rgba(0, 0, 0, 0.1); 
					-moz-box-shadow: 5px 5px rgba(0, 0, 0, 0.1);
					margin-left: 0; 
				}
					.tooltip:hover .tipright,
					.tooltip:focus .tipright {
						left: 130%; 
						top: -5px; 
					}
					
					.tooltip:hover .tipright:before,
					.tooltip:focus .tipright:before {
					   content:"";
					   display:block;
					   z-index:100;
					   position:absolute;
					   top:6px;
					   left:-8px;
					   width:0;
					   border-width:8px 8px 8px 0;
					   border-style:solid;
					   border-color:transparent #888;
					}
					
					.tooltip:hover .tipright:after,
					.tooltip:focus .tipright:after {
					   content:"";
					   display:block;
					   z-index:100;
					   position:absolute;
					   top:7px;
					   left:-7px;
					   width:0;
					   border-width:7px 7px 7px 0;
					   border-style:solid;
					   border-color:transparent #555;
					}
	</style>
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
				
			<div class="content-module">
				<h1><a name="EventRequestForm"></a>Event Request Form</h1>
				<div class="module-content">
					<p>Please fill out the form below to submit an event to the calendar. Fill out separate forms for each event or for each component of your event.</p>
					<?      
					function outputTimeSelection($field)
					{
					    # Set up the Hours
					    $output = '<select name="'.$field.'[hour]">';
					    $output .= '<option value=""'. (isset($_SESSION[$field]['hour']) && $_SESSION[$field]['hour'] == '' ? ' selected="selected"' : '') .'></option>';
					    for ($i = 1; $i < 13; $i++) {
					        $timeVal = sprintf('%02d',$i);
					        
					        $output .= '<option value="'.$timeVal.'"'. (isset($_SESSION[$field]['hour']) && $_SESSION[$field]['hour'] == $timeVal ? ' selected="selected"' : '') .'>'.$timeVal.'</option>';
					    }
					    $output .= '</select> ';
					    
					    # Set up the Minutes
					    $output .= '<select name="'.$field.'[min]">';
					    $output .= '<option value=""'. (isset($_SESSION[$field]['min']) && $_SESSION[$field]['min'] == '' ? ' selected="selected"' : '') .'></option>';
					    for ($i = 0; $i < 60; $i=$i+5) {
					        $timeVal = sprintf('%02d',$i);
					        
					        $output .= '<option value="'.$timeVal.'"'. (isset($_SESSION[$field]['min']) && $_SESSION[$field]['min'] == $timeVal ? ' selected="selected"' : '') .'>'.$timeVal.'</option>';
					    }
					    $output .= '</select> ';
					    
					    # Set up the Meridiem
					    $output .= '<select name="'.$field.'[meridiem]">';
					    
					    $output .= '<option value=""'. (isset($_SESSION[$field]['meridiem']) && $_SESSION[$field]['meridiem'] == '' ? ' selected="selected"' : '') .'></option>';
					    $output .= '<option value="AM"'. (isset($_SESSION[$field]['meridiem']) && $_SESSION[$field]['meridiem'] == 'AM' ? ' selected="selected"' : '') .'>AM</option>';
					    $output .= '<option value="PM"'. (isset($_SESSION[$field]['meridiem']) && $_SESSION[$field]['meridiem'] == 'PM' ? ' selected="selected"' : '') .'>PM</option>';
					                                            
					    $output .= '</select>';
					    
					    return $output;
					}
					        
					if (isset($_SESSION['errors'])) { 
					        ?><div class="form-error">
					        <ul>
					                <? foreach ($_SESSION['errors'] as $error) { ?>
					                        <li><?=$error;?></li>
					                <? } ?>                
					                </ul>
					        </div><? 
					} 
					?>
					<p style="color: #666666;"><span class="required">*</span> -  denotes a required field </p>
					<form id="event-request-form" action="process_eventrequest.php" enctype="multipart/form-data" method="post">
					        
					    <fieldset>
					        <legend>Event Information</legend>
					
					        <label for="title"><span class="required">*</span>Title:
					        	<span class="tooltip">?
					        		<span class="tipright">e.g. Name/Title of Lecture with Speaker's Name<br />
					            		e.g. Film Screening with Name of Film<br />
					            		e.g. Concert with Performer's Name</span>
					        	</span></label> 
					        	<input type="text" id="title" name="title" size="33" maxlength="32" value="<?=$_SESSION['title'];?>" /><br />
					        
					        
					        <label for="s_date">Event Start Date: <span class="required">*</span></label> <input type="text" id="s_date" name="s_date" size="15" value="<?=$_SESSION['s_date'];?>" class="dateInput" /> at <?= outputTimeSelection('s_time'); ?><br />
					        <label for="e_date">Event End Date: <span class="required">*</span></label> <input type="text" id="e_date" name="e_date" size="15" value="<?=$_SESSION['e_date'];?>" class="dateInput" /> at <?= outputTimeSelection('e_time'); ?><br />
					        <p><b>Please Note:</b> Fill out separate forms for each event or for each component of your event.</p><br />
					        
					        <fieldset>
					                <legend>Applicable Calendars</legend>
					                
					                <label class="field-label-nostyle" style="padding-right: 20px;"><input name="audienceCal[]" value="University" type="checkbox" <?= isset($_SESSION['audienceCal']) && in_array('University', $_SESSION['audienceCal'])  ? 'checked="checked"' : '';?> /> University</label>
					                
					                <label class="field-label-nostyle" style="padding-right: 20px;"><input name="audienceCal[]" value="Cultural" type="checkbox" <?= isset($_SESSION['audienceCal']) && in_array('Cultural', $_SESSION['audienceCal'])  ? 'checked="checked"' : '';?> /> Cultural</label>
					                
					                <label class="field-label-nostyle" style="padding-right: 20px;"><input name="audienceCal[]" value="Music" type="checkbox" <?= isset($_SESSION['audienceCal']) && in_array('Music', $_SESSION['audienceCal'])  ? 'checked="checked"' : '';?>  /> Music</label> 
					                
					                <label class="field-label-nostyle" style="padding-right: 20px;"><input name="audienceCal[]" value="MU-Lancaster" type="checkbox" <?= isset($_SESSION['audienceCal']) && in_array('MU-Lancaster', $_SESSION['audienceCal'])  ? 'checked="checked"' : '';?>  /> MU Lancaster</label> 
					                                                                                        
					                <label class="field-label-nostyle"><input name="audienceCal[]" value="Student" type="checkbox" <?= isset($_SESSION['audienceCal']) && in_array('Student', $_SESSION['audienceCal'])  ? 'checked="checked"' : '';?>  /> Student</label>                                  
					                                                                                        
					        </fieldset><br />
					        
					        <label for="short_desc"><span class="required">*</span>Short Description:<span class="tooltip">?<span class="tipright">Describe in 2-4 lines what the event is and who will be featured.</span></span></label><br />
					        	<textarea id="short_desc" name="short_desc" cols="60" rows="4" class="mceNoEditor"><?=$_SESSION['short_desc'];?></textarea><br /><br />
					        
					        <label for="full_desc" class="field-label-nostyle"><span class="required">*</span><strong>Full Description:</strong></label>    		
					        	<div class="tooltip">?<div class="tipright"><p>Please include the following components:</p>
					                	<p>Description:</p>
					                		<ul>
					                			<li>type of event</li> 
					                			<li>who will be featured</li>
					                			<li>date, time, and location</li>
					                		</ul>
					                	<p>Brief biography (if applicable): </p>
					                		<ul>
					                			<li>featured guest/artist</li>
					                		</ul>
					        
					                	<p>Ticket information: </p>
					                		<ul>
					                			<li>Please indicate if your event is open to the public.</li>
					        		        	<li>If your event requires tickets, include cost.</li>
					        		        	<li>If your event does not require tickets, include that it is free.</li>
					        		        </ul>
					        	</div></div>
					        	<textarea id="full_desc" name="full_desc" rows="15" cols="60"><?=$_SESSION['full_desc'];?></textarea><br />
					        
					        <label for="location"><span class="required">*</span>Event Location:
					        	<span class="tooltip">?
					        		<span class="tipright">e.g. Room Name, Building</span>
					        	</span></label> 
					        	<input type="text" id="location" name="location" size="25" value="<?=$_SESSION['location'];?>" /><br />
					        	
					        <label for="image">Event Image:<span class="tooltip">?<span class="tipright">Please submit one image in one of the following formats: .gif, .jpg, or .jpeg.</span></span></label>
					        	<input id="image" name="image" type="file"></input><br /><br /><br />
					    </fieldset>
					    
					    <fieldset>
					        <legend>Event Contact Information</legend>                      
					        <label for="contact_name">Name: <span class="required">*</span></label> <input type="text" id="contact_name" name="contact_name" size="25" value="<?=$_SESSION['contact_name'];?>" /><br />
					        <label for="contact_phone">Phone: <span class="required">*</span></label> <input type="text" id="contact_phone" name="contact_phone" size="15" value="<?=$_SESSION['contact_phone'];?>" /><br />
					        <label for="contact_email">Email: <span class="required">*</span></label> <input type="text" id="contact_email" name="contact_email" size="25" value="<?=$_SESSION['contact_email'];?>" /><br />
					    </fieldset>
					    
					    <fieldset>
					        <legend>Related Links</legend>
					        
					        <label for="link1">Label / URL:</label> 
					        	<input id="link1" name="links[labels][0]" size="18" type="text" value="<?=$_SESSION['links']['labels'][0];?>" /> / 
					        	<input name="links[urls][0]" size="18" type="text" value="<?=$_SESSION['links']['urls'][0];?>" /><br />
					        
					        <label for="link2">Label / URL:</label> 
					        	<input id="link2" name="links[labels][1]" size="18" type="text" value="<?=$_SESSION['links']['labels'][1];?>" /> / 
					        	<input name="links[urls][1]" size="18" type="text" value="<?=$_SESSION['links']['urls'][1];?>" /><br />
					        
					        <label for="link3">Label / URL:</label> 
					        	<input id="link3" name="links[labels][2]" size="18" type="text" value="<?=$_SESSION['links']['labels'][2];?>" /> / 
					        	<input name="links[urls][2]" size="18" type="text" value="<?=$_SESSION['links']['urls'][2];?>" /><br />
					
						</fieldset>
					                
					       <p><button type="submit">Submit Request</button></p>
					</form>
					<?
					session_destroy();
					?>
				</div>
			</div>				
		</div>
			
	</div></div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script> 	
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js" type="text/javascript"></script>

	<script type="text/javascript">			
		$(document).ready(function(){	
			$('.dateInput').datepicker();
		});			
	</script>
	
</body>
</html>