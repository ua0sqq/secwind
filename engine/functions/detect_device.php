<?php
	if (isset($_SESSION['user_device']))
		return $_SESSION['user_device'];
	else
	{
		Core::get('Mobile_Detect');
		$detect = new Mobile_Detect;
		return $_SESSION['user_device'] = $detect->isMobile() ? 'phone' : 'computer';
	}