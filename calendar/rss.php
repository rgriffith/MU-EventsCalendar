<?
	require_once('lib/university_calendar.php');	
	
	$today = getdate();
	
	// Initialize the calendar class' settings.
	$calendarArgs = array(
		'query' => '',
		'category' => '',
		'date' => $today['mday'].'-'.$today['mon'].'-'.$today['year'],
		'mode' => 'feed',
		'pathToCalendar' => 'http://www.millersville.edu/calendar/'
	);
	
	// Initialize the feed's settings.
	$feedArgs = array(
		'feedLink' => $calendarArgs['pathToCalendar'],
		'limitStart' => 0,
		'limitEnd' => 50,
		'showFeatured' => false,
		'showFullDesc' => false,
		'eventId' => false
	);
	
	// Did the user specify a query?
	if (isset($_GET['q'])) {
		$calendarArgs['query'] = filter_var($_GET['q'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_HIGH);
	}
	
	// Did the user specify a category?
	if (isset($_GET['c'])) {
		$calendarArgs['category'] = filter_var($_GET['c'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_HIGH);
	}
	
	// If a date and/or mode were given, validate them.      
	if (isset($_GET['date']) && preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/', $_GET['date'])) {
		$calendarArgs['date'] = $_GET['date'];
	}
	
	if (isset($_GET['mode']) && in_array($_GET['mode'], array('day','week','month','index'))) {
		$calendarArgs['mode'] = $_GET['mode'];
	}
	
	// Initialize the calendar class.
	$cal = new UniversityCalendar($calendarArgs);
	
	// Did the user specify the bounds?
	if (isset($_GET['s']) 
		&& filter_var($_GET['s'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)))
		) {
		$feedArgs['limitStart'] = $_GET['s'];
	}
	
	if (isset($_GET['e']) 
		&& filter_var($_GET['e'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))
		) {
		$feedArgs['limitEnd'] = $_GET['e'];
	}
	
	// Only show featured events?
	if (isset($_GET['featured'])) {
		$feedArgs['showFeatured'] = filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}
	
	// Should we display full descriptions?
	if (isset($_GET['fulldesc'])) {
		$feedArgs['showFullDesc'] = filter_var($_GET['fulldesc'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}
	
	// Was a specific event supplied?
	// Used mainly for movbile web.
	if (isset($_GET['id'])) {
		$feedArgs['eventId'] = filter_var($_GET['id'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_HIGH);
	}
	
	// Generate the feed content.
	$itemsRss = '';
	
	$rssOutput = '<?xml version="1.0"?> 
		<rss version="2.0" xmlns:ev="http://purl.org/rss/1.0/modules/event/"> 
			<channel> 
				<title>Millersville University Calendar</title> 
				<link>'.$feedArgs['feedLink'].'</link> 
				<description>Upcoming events at Millersville University.</description> 
				<language>en-us</language> 
				<image> 
					<title>Millersville University</title> 
					<url>http://www.millersville.edu/</url> 
					<link>http://www.millersville.edu/lib/img/MUlogo.gif</link> 
					<width>144</width> 
					<height>22</height> 
				</image>';
	
	if ($feedArgs['eventId']) {
		if ($event = $cal->getEventById($feedArgs['eventId'])) {
			$itemsRss .= '
				<item>
					<title>'.$event['title'].'</title>
					<link>'.$feedArgs['feedLink'].'eventdetails.php?id='.$event['id'].'</link>
					<description>'.($feedArgs['showFullDesc'] ? utf8_encode($event['fulldesc']) : $event['shortdesc']).'</description>
					<pubDate>'.date('D, d M Y H:i:s', $event['startdate']).' EST</pubDate>
					<guid isPermaLink="false">'.$event['id'].'</guid>
				</item>';
		}
	} else {
		$cal->getLatestEvents($feedArgs['showFeatured'], 'ksort');
		if (!empty($cal->upcomingEvents)) {	
			$count = 0;
			
			foreach ($cal->upcomingEvents as $date => $events) {
				foreach ($events as $event) {
					if ($count >= $feedArgs['limitStart'] && $count < $feedArgs['limitEnd']) {
						$itemsRss .= '
							<item>
								<title>'.$event['title'].'</title>
								<link>'.$feedArgs['feedLink'].'eventdetails.php?id='.$event['id'].'</link>
								<description>'.($feedArgs['showFullDesc'] ? utf8_encode($event['fulldesc']) : $event['shortdesc']).'</description>
								<pubDate>'.date('D, d M Y H:i:s', $event['startdate']).' EST</pubDate>
								<guid isPermaLink="false">'.$event['id'].'</guid>
							</item>';
							
						$count++;					
						if ($count == $feedArgs['limitEnd']) { break; }
					}
					
					
				}
			}
		}	
	}
	
	if (empty($itemsRss)) {
		$itemsRss .= '
			<item>
				<title>No events were found</title>
				<link>'.$feedArgs['feedLink'].'</link>
				<description>There are currently no events to report. Please check back again.</description>
				<pubDate>'.date('D, d M Y H:i:s').' EST</pubDate>
			</item>';
	}
	
	$rssOutput .= $itemsRss.'
		</channel>
	</rss>';
		
	// Output the feed.		
	header("Content-Type: application/xml; charset=ISO-8859-1");
	echo $rssOutput;
?>