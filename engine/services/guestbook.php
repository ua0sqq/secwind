<?php

	$today = $sql->query('SELECT count(*) FROM `guest` where `time` > '.mktime(0,0,0))->result();
	return ' ('.$sql->query('SELECT count(*) FROM `guest`')->result(). ($today > 0 ? '+'.$today : '').')';