<?php
	include '../engine/includes/start.php';

	if (!$user_id || $sql->query('SELECT COUNT(*) FROM `ban` WHERE `user_id` = '.$user_id.' AND `time` > '.$time)->result() == 0)
		Core::stop();
	
	$set['title'] = 'Бан';
	$ban = $sql->query('SELECT * FROM `ban` WHERE `user_id` = '.$user_id.' AND `time` > '.$time.' LIMIT 1')->fetch();

	include incDir . 'head.php';

	echo Core::user_show(Core::get_user($ban['moder_id']), array('post' => 'Вы забанены по причине: ' . $ban['prich'], 'status' => 'Бан до '.Core::time($ban['time'])));
	include incDir . 'foot.php';