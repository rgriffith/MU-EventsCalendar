<?
	abstract class MUCalendar
	{
		# Path and XML file for calendar.
		public $urlToCalendar = '';
		public $pathToCalendar = '';
		public $xml;
		
		# User specified variables.
		public $query = '';
		public $userDate = null;
		public $dateMode = null;
		
		# Period to display, based on userDate and dateMode.
		public $periodStart = null;
		public $periodEnd = null;
		
		# Script run time, see get_query_time().
		public $scriptBegin = 0;
		public $scriptEnd = 0;
		
		public $eventCount = 0;
		
		# Define the abstract methods needed for each calendar.
		abstract protected function getLatestEvents();
		abstract protected function getUpcomingEvents();
		abstract protected function _buildEventArray($event);
		
		public function getEventById($id)
		{
			$this->scriptBegin = microtime(true);
			
			if (!$this->xml || empty($id)) {
				$this->scriptEnd = microtime(true);
				return false;
			}
			
			$result = $this->xml->xpath('//event[@id = \''.$id.'\']');
			$this->scriptEnd = microtime(true);
			
			return !empty($result[0]) ? $this->_buildEventArray($result[0]) : false;			
		}
		
		public function getQueryTime() { return ($this->scriptEnd - $this->scriptBegin); }
		
		public function getDateRange($start, $end)
		{
			$dateArray = array();
			
			$start = $this->convertToTimestamp($start);
					
			if (empty($end)) {	
				$dateArray[] = $start;	
				return $dateArray;	
			}
			
			$end = $this->convertToTimestamp($end);
			
			for ($date = $start; $date <= $end; $date = $date+86400) {
				$dateArray[] = $date;
			}
	
			return $dateArray;
		}
				
		public function setQuery($q) 
		{ 
			if (is_array($q)) {
				$q = implode(' ', $q);
			}

			$toReplace = array("'", "/");
			$this->query = str_replace($toReplace, ' ', $q); 
		}
		
		public function setUserDate($d) { $this->userDate = $d; }
		
		public function setDateMode($str = '')
		{
			# Check to make sure the range is valid.
			# 	Else, default the range to month.
			$allowedRanges = array('year', 'month', 'week', 'day', 'index', 'feed');
			
			if (!empty($str) && in_array($str, $allowedRanges)) {
				$this->dateMode = $str;
			} else {
				$this->dateMode = 'month';
			}
		}		
		
		public function setPeriodStart() 
		{
			$date_info = getdate($this->userDate);
	
			switch ($this->dateMode) {
				case 'index':
				case 'day':
				default:
					$this->periodStart = $this->userDate;
					break;
	
				case 'week':
					$this->periodStart = $date_info['wday'] == 0 ? $this->userDate : strtotime('last Sunday', $this->userDate);
					break;
	
				case 'month':
					$this->periodStart = $date_info['mday'] == 1 ? $this->userDate : strtotime('01-' . $date_info['mon'] . '-' . $date_info['year']);
					break;
			}
	
		}
	
		public function setPeriodEnd() 
		{			
			$date_info = getdate($this->userDate);
	
			switch ($this->dateMode) {
				case 'day':
				default:
					$this->periodEnd = $this->userDate;
					break;
	
				case 'week':
					$this->periodEnd = $date_info['wday'] == 6 ? $this->userDate : $this->userDate + ((6-$date_info['wday'])*86400);
					break;
	
				case 'month':
				case 'index':
					$days_in_month = date('t', $this->userDate);
					$this->periodEnd = strtotime($days_in_month . '-' . $date_info['mon'] . '-' . $date_info['year']);
					break;
					
				case 'feed':
				case 'year':
					$this->periodEnd = ($this->userDate+31556926);
					break;
			}
		}
		
		public function printXml()
		{
			echo '<pre>';
			print_r($this->xml);
			echo '</pre>';
		}
		
		public function convertToTimestamp($date)
		{
			$time_now = getdate();
			$time_now = mktime(0, 0, 0, $time_now['mon'], $time_now['mday'], $time_now['year']);
						
			if (is_numeric($date)) {
				$temp = getdate($date);
				
				if (checkdate($temp['mon'], $temp['mday'], $temp['year'])) {
					return $date;
				} else {
					return $time_now;
				}
			} else {				
				if ($temp = strtotime($date)) {
					return $temp;
				} else {
					return $time_now;
				}
			}				
		}
		
		public function _cleanCharacterEncoding($str)
		{
			# Special Double Quotes.
			$find = array('â€œ', 'â€', 'â€¢', '“', '”');
			$str = str_replace($find, '"', $str);
			
			# Special Single Quotes.
			$find = array('â€˜', 'â€™', "’", "‘");
			$str = str_replace($find, "'", $str);
			
			# Everything else.
			$find[] = 'â€¦'; 	// elipsis
			$find[] = '...';
			$find[] = 'â€”';  	// em dash
			$find[] = '–';
			$find[] = 'â€“';  	// en dash;
			
			$replace = array("...", "...", "-", "-", "-",  ' ');
			
  			return str_replace($find, $replace, filter_var($str, FILTER_SANITIZE_SPECIAL_CHARS));
		}
		
		public function _cleanShoutedWords($string) 
		{
			return preg_replace_callback('/(\b[A-Z][A-Z]+\b)/', 
										create_function('$matches','return ucwords(strtolower($matches[0]));'), 
										$string);
		}
		
		public function generateMinical($urlToCalendar = '')
		{
			$output = '';
			
			// The following allows us to override the URL used within the mini calendar.
			if (empty($urlToCalendar)) {
				$urlToCalendar = $this->urlToCalendar;
			}
			
			# Capture the requested date, or today's date if none was supplied.						
			$date = $this->userDate ? getdate($this->userDate) : getdate();
			
			# Add some leading 0's.
			$date['mon'] = sprintf('%02d',$date['mon']);
			$date['mday'] = sprintf('%02d',$date['mday']);
			
			# Create a format of the current day for the Display method after the mini calendar.
			$formatted_date = $date['mday'].'-'.$date['mon'].'-'.$date['year'];
			
			# Get the first day of the month.
			$first_day = mktime(0,0,0,$date['mon'], 1, $date['year']); 
			
			# Get the day of the week the  first day falls on, in integer format.
			$day_of_week = date('w', $first_day);
			
			# Get the number of days in the month.
			$days_in_month = cal_days_in_month(0, $date['mon'], $date['year']); 
			
			# Get the number of days in the previous month.
			#	Used to fill in the initial blank cells, if any.
			$days_in_prevmonth = cal_days_in_month(0, ($date['mon']-1 > 0 ? $date['mon']-1 : 12), $date['year']); 
			
			# Find the previous and next month, accounting for wrapping to another year.
			$prev_month = $date['mon'] - 1 > 0 ? '01-'.sprintf('%02d',($date['mon']-1)).'-'.$date['year'] : '01-12-'.($date['year']-1);
			$next_month = $date['mon'] + 1 < 13 ? '01-'.sprintf('%02d',($date['mon']+1)).'-'.$date['year'] : '01-01-'.($date['year']+1);
	
			$output = '			
				<table class="cal-minical">
				<tbody>
					<tr class="month">
						<th><a title="View Previous Month" href="'.$urlToCalendar.'?c='.$this->category.'&date='.$prev_month.'&mode=month"><img src="http://www.millersville.edu/~rgriffith/beta/arrow-left.gif" alt="View previous month: '.$prev_month.'" /></a></th>
						<th colspan="5">'.$date['month'].' '.$date['year'].'</th>
						<th><a title="View Next Month" href="'.$urlToCalendar.'?c='.$this->category.'&date='.$next_month.'&mode=month"><img src="http://www.millersville.edu/~rgriffith/beta/arrow-right.gif" alt="View next month: '.$next_month.'" /></a></th>
					</tr>
					<tr class="header">
						<th>S</th><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th>
					</tr>
					<tr>'."\r\n";
	
			# Counter to keep track of the location within the week.
			$day_count = 1;
			
			# Fill in the beginning cells with the previous month, where appropriate.
			for ($i = 0; $i < $day_of_week; $i++) {
				$prevmonth_day = $days_in_prevmonth - ($day_of_week - $day_count);
				$prevmonth_day_stamp = $date['mon']-1 > 0 ? sprintf('%02d',$prevmonth_day).'-'.sprintf('%02d',($date['mon']-1)).'-'.$date['year'] : sprintf('%02d',$prevmonth_day).'-12-'.($date['year']-1);
				
				$output .= '
						<td><a href="'.$urlToCalendar.'?c='.$this->category.'&date='.$prevmonth_day_stamp.'&mode=day" class="alt-month">'.$prevmonth_day.'</a></td>';
				
				$day_count++;
			}
						
			# Create our loop constraint.
			$day_num = 1;
			
			# Print the calendar.
			while ($day_num <= $days_in_month) {
				$current_stamp = sprintf('%02d',$day_num).'-'.$date['mon'].'-'.$date['year'];
				
				$output .= '
						<td'.($day_num == $date['mday'] ? ' class="current"' : '').'><a href="'.$urlToCalendar.'?c='.$this->category.'&date='.$current_stamp.'&mode=day">'.$day_num.'</a></td>';
				
				$day_num++;
				$day_count++;
				
				# This will create the next row when the loop reaches the end of the week.
				if ($day_count > 7) {
					$output .=  '
					</tr>
					<tr>'."\r\n";
					
					$day_count = 1;
				}
			}
			
			# Fill in the remaining cells with the next month, where appropriate.
			$nextmonth_day = 1;
			while ($day_count > 1 && $day_count <= 7) {
				$nextmonth_day_stamp = $date['mon']+1 < 13 ? sprintf('%02d',$nextmonth_day).'-'.sprintf('%02d',($date['mon']+1)).'-'.$date['year'] : sprintf('%02d',$nextmonth_day).'-01-'.($date['year']+1);
				
				$output .= '
						<td><a href="'.$urlToCalendar.'?c='.$this->category.'&date='.$nextmonth_day_stamp.'&mode=day" class="alt-month">'.$nextmonth_day.'</a></td>';
				
				$day_count++;
				$nextmonth_day++;
			} 								
			
			$output .= '
					</tr>
				</tbody>
				</table>'."\r\n";			
			
			// Create the View by links.
			$viewByLink = $urlToCalendar.'?c='.$this->category.'&date='.$formatted_date;			
			$output .= '
				<p style="text-align: center;"><strong>View by:</strong> '
					.($this->dateMode == 'day' ? 'Day' : '<a href="'.$viewByLink.'&mode=day">Day</a>').' | '
					.($this->dateMode == 'week' ? 'Week' : '<a href="'.$viewByLink.'&mode=week">Week</a>').' | '
					.($this->dateMode == 'month' ? 'Month' : '<a href="'.$viewByLink.'&mode=month">Month</a>').
				'</p>'."\r\n";
			
			return $output;
		}
	}

?>