<?php
	require_once('mucalendar.php');	
	
	class UniversityCalendar extends MUCalendar
	{
		public $category = '';				
		public $upcomingEvents = array();
		public $ongoingEvents = array();
		
		private $restrictedCats = array('dining','mu-lancaster','music', 'ware-center');		
		
		public function __construct(array $config) 
		{																	
			if (isset($config['query']) && $config['query'] != '') {
				$this->setQuery($config['query']);
				$this->setDateMode('year');
				$this->setUserDate(time());
			} else {
				if (isset($config['category'])) {
					$this->setCategory($config['category']);
				}
				
				if (isset($config['date']) && !empty($config['date'])) {
					$this->setUserDate($this->convertToTimestamp($config['date']));
				} else {
					$this->setUserDate(time());
				}
					
				if (isset($config['mode']))	{
					$this->setDateMode($config['mode']);
				}
			}
			
			if (isset($config['urlToCalendar'])) {
				// Set to user defined value, also checking for trailing slash.
				$this->urlToCalendar = sprintf('%s%s', $config['urlToCalendar'], (substr($config['urlToCalendar'], -1) != '/' ? '/' :''));
			} else {
				$this->urlToCalendar = 'http://www.millersville.edu'.dirname($_SERVER["SCRIPT_NAME"]).'/';
			}
			
			if (isset($config['pathToCalendar'])) {
				// Set to user defined value, also checking for trailing slash.
				$this->pathToCalendar = sprintf('%s%s', $config['pathToCalendar'], (substr($config['pathToCalendar'], -1) != '/' ? '/' :''));
			} else {
				$this->pathToCalendar = realpath('.').'/';
			}
			
			$this->setPeriodStart();
			$this->setPeriodEnd();	

			if ((date('Y') - date('Y', $this->userDate)) <= 0) {
				$this->xml = simplexml_load_file($this->urlToCalendar."events/events.xml");						
			} else {
				$this->xml = simplexml_load_file($this->urlToCalendar."events/".date('Y', $this->userDate)."-events.xml");		
			}			
		}
		
		public function setCategory($category) { $this->category = strtolower($category); }
		
		public function getUpcomingEvents($showFeatured = false, $sortMethod = 'ksort')
		{
			$this->scriptBegin = microtime(true);
			
			if (!$this->xml) {
				$this->scriptEnd = microtime(true);
				return false;
			} else {
				// We only want articles that have a short description.
				$xpathQuery = 'startdate <= '.($this->periodEnd+82400).' and shortdesc != \'\'';
				
				// Are we only showing featured news?
				if ($showFeatured) {
					$xpathQuery .= ' and @featured = \'true\'';
				}
			
				$xpathResults = $this->xml->xpath('//event['.$xpathQuery.']');
				
				// Parse through the events.	
				foreach ($xpathResults as $k) {	
							
					// Get the event's starting and ending timestamps.
					$start_stamp = mktime(0,0,0,date('n',(int)$k->startdate),date('j',(int)$k->startdate),date('Y',(int)$k->startdate));
					
					// We need to account for events that may not have an end date entered.
					$end_stamp = false;
					if (!empty($k->enddate)) {
						$end_stamp = mktime(0,0,0,date('n',(int)$k->enddate),date('j',(int)$k->enddate),date('Y',(int)$k->enddate));
					}
					
					// Check to see if this event is upcoming or ongoing (i.e. spans multiple dates).
					$eventType = false;					
					if ($end_stamp && $end_stamp > $start_stamp) {
						$eventType = 'ongoing';
					} else if (!$end_stamp || $end_stamp == $start_stamp) {
						$eventType = 'upcoming';
					}
					
					// If we don't have an event, there's some funky data in the start.end dates.
					if ($eventType) {
						// Loop through the event's range of dates and find it's occurances that fall between the date period.
						$dateRange = $this->getDateRange($start_stamp, $end_stamp);							
						foreach ($dateRange as $date_to_check) {			
							if ($date_to_check >= $this->periodStart && $date_to_check <= $this->periodEnd) {		
								// Get the article's categories.
								$categories = array_map('strtolower', $k->xpath('categories/cat'));
									
								// Did the user specify a category?
								// If so, make sure the event falls in the category.
								if (!empty($this->category) && $this->category != 'all') {									
									if (!in_array($this->category, $categories)) {									
										break;
									}
								} else {									
									// We don't want to show the following categories unless the user specifies them directly
									// or the event is designated as a University event.
									if (array_intersect($this->restrictedCats, $categories)) {
										// If the event is not specified as University, leave it out.
										// Else, we still want it to show up...
										if (!in_array('university', $categories)) {
											break;
										}
									}
								}
								
								// Perform a search within the content.
								// If the query is empty, the preg_match will just return true.
								if (preg_match('/'.$this->query.'/i', $k->title) 
									|| preg_match('/'.$this->query.'/i', $k->fulldesc->asXML())
									) {
									
									// Depending on the type of event, we need to structure them differently.
									// This structure is important in the front-end...
									if ($eventType == 'upcoming') {												
										$this->upcomingEvents[$date_to_check][date('a h:i',(int)$k->startdate)][] = $this->_buildEventArray($k);
									} else {
										$this->ongoingEvents[$start_stamp][] = $this->_buildEventArray($k);
									}
									
									$this->eventCount++;
								}
							}
						}
					}
				}
				
				// If we found some events, sort the results and return true.
				if ($this->eventCount > 0) {
					switch ($sortMethod) {
						case 'ksort':
						default:
							ksort($this->upcomingEvents);
							ksort($this->ongoingEvents);				
							break;
						case 'krsort':
						 	krsort($this->upcomingEvents);
						 	krsort($this->ongoingEvents);				
						 	break;
					}
						
					$this->scriptEnd = microtime(true);						
					return true;
				} else {
					$this->scriptEnd = microtime(true);			
					return false;
				}
			}
		}
		
		public function getLatestEvents($showFeatured = false, $sortMethod = 'ksort')
		{		
			$this->scriptBegin = microtime(true);
			
			if (!$this->xml) {
				$this->scriptEnd = microtime(true);			
				return false;
			} else {		
				$xpathQuery = 'startdate <= '.($this->periodEnd+82400);
				
				// Are we only showing featured news?
				if ($showFeatured == true) {
					$xpathQuery .= ' and @featured = \'true\'';
				}
				
				$xpathResults = $this->xml->xpath('//event['.$xpathQuery.']');
								
				if (!$xpathResults) {
					$this->scriptEnd = microtime(true);	
					return false;
				}
				
				foreach ($xpathResults as $event) {
					$eventFound = false;
				
					# Get the event's starting timestamp.
					$startStamp = getdate((int)$event->startdate);
					$startStamp = mktime(0, 0, 0, $startStamp['mon'], $startStamp['mday'], $startStamp['year']);
					
					if ($startStamp && $startStamp >= $this->userDate) {
						// Get the article's categories.
						$categories = array_map('strtolower', $event->xpath('categories/cat'));
	
						// Did the user specify a category?
						// If so, make sure the event falls in the category.
						if (!empty($this->category) && $this->category != 'all') {									
							if (in_array($this->category, $categories)) {
								$eventFound = true;
							}
						} else {
							// We don't want to show the following categories unless the user specifies them directly
							// or the event is designated as a University event.
							if (!array_intersect($this->restrictedCats, $categories)) {
								$eventFound = true;
							} else {
								// If the event is not specified as University, leave it out.
								// Else, we still want it to show up...
								if (in_array('university', $categories)) {
									$eventFound = true;									
								}
							}
						}
						
						if ($eventFound) {
							$this->upcomingEvents[$startStamp][] = $this->_buildEventArray($event);								
							$this->eventCount++;
						}
					}
				}
				
				if ($this->eventCount > 0) {
					switch ($sortMethod) {
						case 'ksort':
						default:
							ksort($this->upcomingEvents);				
							break;
						case 'krsort':
						 	krsort($this->upcomingEvents);				
						 	break;
					}
						
					$this->scriptEnd = microtime(true);	
					return true;
				} else {
					$this->scriptEnd = microtime(true);			
					return false;
				}
			}
		}
		
		public function displayEventSocialMediaBox($eventId = null) {
			$fbLikeUrl = $this->urlToCalendar;
			if ($eventId) {
				$fbLikeUrl .= 'eventdetails.php?id='.$eventId;	
			}
			
			echo '<ul class="social-media-share">
					<li><script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:like href="'.$fbLikeUrl.'" show_faces="false" layout="button_count" font="verdana"></fb:like></li>
					<li><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script><a href="http://www.millersville.edu" class="twitter-share-button" data-count="horizontal">Tweet</a></li>
					<li><script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script><g:plusone size="medium"></g:plusone></li>
				</ul>';
		}
		
		public function generateCategoryList()
		{
			$categoriesArray = array(
					'all' => 'All Categories',
					'cultural' => 'Cultural',
					'dining' => 'Dining',
					'music' => 'Music',
					'student' => 'Student',
					'university' => 'University',
					'ware-center' => 'Ware Center'
				);
			
			$output = '<ul>';
			
			foreach ($categoriesArray as $id => $label) {
				$output .= '<li>';
				
				if ($this->category == $id || ($this->category == '' && $id == 'all')) {
					$output .= '<strong>'.$label.'</strong>';
				} else {
					$uri = 'index.php?';
					$uri .= 'c='.$id;
					$uri .= '&date='.date('d-m-Y',$this->userDate);				
					$uri .= '&mode='.$this->dateMode;
							
					$output .= '<a href="'.$uri.'">'.$label.'</a>';
				}		
				
				$output .= '</li>';
			}
			$output .= '</ul>';
			
			return $output;
		}
		
		protected function _buildEventArray($event)
		{			
			$fullDesc = str_replace(array('<fulldesc>','</fulldesc>'), '', $event->fulldesc->asXML());
			
			$relatedLinks = array();			
			foreach ($event->relatedlinks->link as $link) {
				$relatedLinks[] = array(
					'name' => (string)$link->name, 
					'url' => (string)$link->url
				);
			}
			
			$imageThumb = preg_replace('/\r\n|\r|\n|\t/m', '', (string)$event->image->thumb);
			$imageLarge = preg_replace('/\r\n|\r|\n|\t/m', '', (string)$event->image->large);
			
			return array(
				'id' => (string)$event->attributes()->id,
				'keywords' => (string)$event->keywords,
				'title' => $this->_cleanCharacterEncoding((string)$event->title),
				'createdon' => (int)$event->createdon,
				'startdate' => (int)$event->startdate,
				'enddate' => (int)$event->enddate,
				'starttime' => date('h:i A', (int)$event->startdate),
				'endtime' => date('h:i A', (int)$event->enddate),
				'categories' => array_map('strtolower', $event->xpath('categories/cat')),
				'featured' => (string)$event->attributes()->featured,
				'shortdesc' => $this->_cleanShoutedWords($this->_cleanCharacterEncoding(utf8_decode((string)$event->shortdesc))),
				'fulldesc' => $this->_cleanCharacterEncoding(utf8_decode($fullDesc)),
				'ticketinfo' => (string)$event->ticketinfo,				
				'location' => (string)$event->location,
				'image' => array(
					'thumb' => (string)$event->image->thumb, 
					'large' => (string)$event->image->large
				),
				'contact' => array(
					'name' => (string)$event->contact->name, 
					'phone' => (string)$event->contact->phone, 
					'email' => (string)$event->contact->email
				),
				'relatedlinks' => $relatedLinks
			);
		}
	}

?>