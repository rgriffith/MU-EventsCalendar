<?
	session_start();
	
	require('lib/WECalendarEvent.php');

	$_SESSION = $_POST;
	$_SESSION += $_FILES;
	
	if($_SESSION['title'] == '' || strlen($_SESSION['title']) < 2)
	{
		$_SESSION['errors'][] = 'A valid title was not entered.';
	}

	$startStamp = strtotime($_SESSION['s_date']);
	$endStamp = strtotime($_SESSION['e_date']);
	
	if($_SESSION['s_date'] == '' || !$startStamp)
	{
		$_SESSION['errors'][] = 'A valid start date was not entered.';
	}
	
	if($_SESSION['e_date'] != '' && !$endStamp)
	{
		$_SESSION['errors'][] = 'A valid end date was not entered.';
	}	
	
	if($endStamp && $startStamp > $endStamp)
	{
		$_SESSION['errors'][] = 'The End Date must be later than or equal to the Start Date.';
	}
	
	# Descode the descriptions because the Spam Check script encodes them.
	$_SESSION['short_desc'] = html_entity_decode($_SESSION['short_desc']);
	$_SESSION['full_desc'] = html_entity_decode($_SESSION['full_desc']);

	if($_SESSION['short_desc'] == '' || strlen($_SESSION['short_desc']) < 2)
	{
		$_SESSION['errors'][] = 'A valid short description was not entered.';
	}

	if($_SESSION['full_desc'] == '' || strlen($_SESSION['full_desc']) < 2)
	{
		$_SESSION['errors'][] = 'A valid full description was not entered.';
	}

	# if image, check it. An image is NOT required though
	if($_SESSION['image']['tmp_name'] != '')
	{
		$imageinfo = getimagesize($_SESSION['image']['tmp_name']);
	
		# make sure the file is actually an image
		if($imageinfo === FALSE)
		{
			$_SESSION['errors'][] = 'The submitted file is not an image.';		
		}
	
		# make sure its a jpeg or gif
		if($imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/gif')
		{
			$_SESSION['errors'][] = 'The submitted image must be either a gif or jpeg.';
		}
	}
	
	if($_SESSION['contact_name'] == '' || $_SESSION['contact_phone'] == '' || $_SESSION['contact_email'] == '')
	{
		$_SESSION['errors'][] = 'All event contact information must be entered.';
	}

	if(isset($_SESSION['errors']))
	{		
		header('Location: eventrequest.php');
		die();
	}
	else
	{
		$_SESSION['s_date'] = date('m/d/Y', $startStamp);
		
		if($_SESSION['s_time']['hour'] != '' && $_SESSION['s_time']['min'] != '' && $_SESSION['s_time']['meridiem'] != '')
			$_SESSION['s_time'] = $_SESSION['s_time']['hour'] . ':' . $_SESSION['s_time']['min'] . ' ' . $_SESSION['s_time']['meridiem'];
		else
			$_SESSION['s_time'] = '';
		
		if(isset($_SESSION['e_date']) && $_SESSION['e_date'] != '')
		{
			$_SESSION['e_date'] = date('m/d/Y', $endStamp);
			
			if($_SESSION['e_time']['hour'] != '' && $_SESSION['e_time']['min'] != '' && $_SESSION['e_time']['meridiem'] != '')
				$_SESSION['e_time'] = $_SESSION['e_time']['hour'] . ':' . $_SESSION['e_time']['min'] . ' ' . $_SESSION['e_time']['meridiem'];
			else
				$_SESSION['e_time'] = '';
		}
		else
		{
			$_SESSION['e_date'] = null;
			$_SESSION['e_time'] = null;
		}
		
		$webEvent = new WECalendarEvent();	
				
		$system_name = substr($webEvent->cleanAssetName($_SESSION['title']), 0, 32);
		$placement_path = 'millersville/calendar/pending';
		
		# Does the event exist?
		if($webEvent->isAssetAlreadyCreated($placement_path.'/'.$system_name, 'page'))
		{
			$_SESSION['errors'][] = 'An event with this title already exists and will be reviewed shortly. Please enter a different title.';
			header('Location: eventrequest.php');
			die();
		}
		
		$result = $webEvent->createCalendarEvent($_SESSION);
		
		# Check if the request was added successfully.
		if(!$result)
		{
			# Log the webevent error(s).
			$to	=	'EMAIL@DOMAIN';
			$subject = 'Logged Error: Event Request Form';
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: Event Request Form <noreturn@DOMAIN>' . "\r\n";
	
			$message  = '
						<h1>Event Request Submission</h1>
						<p><strong>Title:</strong> ' . $_SESSION['title'] . '</p>
						<p><strong>Start Date:</strong> ' . $_SESSION['s_date'] . ($_SESSION['s_time'] != '' ? ' at ' . $_SESSION['s_time'] : '') . '</p>
						<p><strong>End Date:</strong> ' . ($_SESSION['e_date'] != '' ? $_SESSION['e_date'] : '') . ($_SESSION['e_time'] != '' ? ' at ' . $_SESSION['e_time'] : '') . '</p>
						<p><strong>Applicable Calendars:</strong></p>
						<ul>';
	
			if(count($_SESSION['audienceCal']) > 0)
			{
				foreach($_SESSION['audienceCal'] as $v)
					$message .= '<li>' . $v . '</li>';
			}
			else 
			{
				$message .= '<li>There were no calendars selected.</li>';
			}
			
			$message .= '
						</ul>
						<p><strong>Short Description:</strong></p>
						<p>' . $webEvent->cleanWordCharacters($_SESSION['short_desc']) . '</p>
						<p><strong>Full Description:</strong></p>
						' . $webEvent->cleanWordCharacters($_SESSION['full_desc']) . '
						<p><strong>Event Location:</strong> ' . $_SESSION['location'] . '</p>
						<p><strong>Event Image:</strong> ' . $_SESSION['image']['name'] . '</p>
						<p><strong>Contact Name:</strong> ' .$_SESSION['contact_name'] . '</p>
						<p><strong>Contact Phone:</strong> ' .$_SESSION['contact_phone'] . '</p>
						<p><strong>Contact Email:</strong> ' .$_SESSION['contact_email'] . '</p>
						<p><strong>Related Links:</strong></p>
						<ul>
						';
						
			for($i = 0; $i < count($_SESSION['links']['labels']); $i++)
			{
				if(!empty($_SESSION['links']['labels'][$i]) && $_SESSION['links']['urls'][$i] != 'http://')
					$message .= '<li><a href="'.$_SESSION['links']['urls'][$i].'">'.$_SESSION['links']['labels'][$i].'</a></li>'."\r\n";
			}
			
			$message .= '			
						</ul>
						<br />----------------------------------------------------------------------<br />
						<h1>WebEvent Log</h1>
						';
			
			foreach($webEvent->getLog() as $msg)
				$message .= $msg."\r\n";

			
			mail($to, $subject, $message, $headers);
				
			$_SESSION['errors'][] = 'There was a problem processing your submission, the site administrator has been notified. Please try submitting this form again.  
				If this problem persists, contact <a href="#">Help Desk</a> if this problem persists.';
				
			header('Location: eventrequest.php');
			die();			
		}
		
		# If the event was created successfully, build and send out the notification email.
		$to	=	'EMAIL@DOMAIN';

		$subject = 'Event Request Form';

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: Event Request Form <noreturn@DOMAIN>' . "\r\n";

		$message  = '<h1>Event Request Form</h1>';
		$message .= '<p><strong>Title:</strong> ' . $_SESSION['title'] . '</p>
					<p><strong>Start Date:</strong> ' . $_SESSION['s_date'] . ($_SESSION['s_time'] != '' ? ' at ' . $_SESSION['s_time'] : '') . '</p>
					<p><strong>End Date:</strong> ' . ($_SESSION['e_date'] != '' ? $_SESSION['e_date'] : '') . ($_SESSION['e_time'] != '' ? ' at ' . $_SESSION['e_time'] : '') . '</p>
					<p><strong>Applicable Calendars:</strong></p>
					<ul>';

		if(count($_SESSION['audienceCal']) > 0)
		{
			foreach($_SESSION['audienceCal'] as $v)
				$message .= '<li>' . $v . '</li>';
		}
		else 
		{
			$message .= '<li>There were no calendars selected.</li>';
		}
		
		$message .= '</ul>';

		$message .= '<p><strong>Short Description:</strong><br />
					' . $webEvent->cleanWordCharacters($_SESSION['short_desc']) . '</p>
					<p><strong>Full Description:</strong><br />
					' . $webEvent->cleanWordCharacters($_SESSION['full_desc']) . '</p>
					<p><strong>Event Location:</strong> ' . $_SESSION['location'] . '</p>
					<p><strong>Event Image:</strong> ' . $_SESSION['image']['name'] . '</p>
					<p><strong>Contact Name:</strong> ' .$_SESSION['contact_name'] . '</p>
					<p><strong>Contact Phone:</strong> ' .$_SESSION['contact_phone'] . '</p>
					<p><strong>Contact Email:</strong> ' .$_SESSION['contact_email'] . '</p>
					<p><strong>Related Links:</strong></p>
					<ul>
				';
						
		for($i = 0; $i < count($_SESSION['links']['labels']); $i++)
		{
			if(!empty($_SESSION['links']['labels'][$i]) && $_SESSION['links']['urls'][$i] != 'http://')
				$message .= '<li><a href="'.$_SESSION['links']['urls'][$i].'">'.$_SESSION['links']['labels'][$i].'</a></li>'."\r\n";
		}
		
		$message .= '			
					</ul>
				';
		
		# Ensure the notification email is sent, this should rarely fail.
		if(mail($to, $subject, $message, $headers))
		{
			header('Location: thankyou.php');
			die();
		}
		else
		{			
			$_SESSION['errors'][] = 'There was a problem processing your submission, the site administrator has been notified. Please try submitting this form again.  
				If this problem persists, contact <a href="#">Help Desk</a> if this problem persists.';
				
			header('Location: eventrequest.php');
			die();
		}
	}
?>