<?php
	echo '<div class="link"><a href="/pages/users.php">Пользователи</a><br />';

	$i = 0;
	$users_on_count = $sql->query('SELECT COUNT(*) from `user` WHERE `date_last` > '.($time - 300))->result();
	$limit = $users_on_count == 4 ? 4 : 3;
	$diff = $users_on_count - $limit;
	$sql->query('SELECT `id`, `nick`, `pol` from `user` WHERE `date_last` > '.($time - 300).' LIMIT '.$limit);

	if ($users_on_count > 0) {
		while($users_on = $sql->fetch()) {
			echo '<a href="/pages/user.php?id='.$users_on['id'].'">'.Core::user_icon($users_on) . '&nbsp;' . $users_on['nick'].'</a>' . (++$i != $limit && $i != $users_on_count ? ', ' : '');
		}

		if ($diff > 0) {
			echo ' и <a href="/pages/online.php"> еще ' . $diff . '</a>';
		}
	}
	else {
		echo 'Никого нет';
	}

	echo ' в онлайне</div>';