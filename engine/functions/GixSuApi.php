<?php

	function GixSuApi($str)
	{
		return unserialize(file_get_contents('http://gix.su/api/userinfo.php?search=' . $str));
	}