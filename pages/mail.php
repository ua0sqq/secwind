<?php
	include '../engine/includes/start.php';
	
    if (!$user_id)
        Core::stop('/index.php');
	
	/*
	* By Denis27
      */

	switch ($act) 
	{
		case 'ajax':
		header('Content-Type: text/xml');
		?>
		<response>
			<new_mail><?=$sql->query('SELECT sum(`unread`) FROM `mail_contacts` WHERE `user_id` = ' . $user_id)->result()?></new_mail>
		</response>
		<?php
		exit;
		break;
		/*
		---------------------------------
		Переписка с пользователем
		---------------------------------
		*/
		case 'mail':
			if (!$id)
				Core::stop('?');
			
			Core::get(array('page.class', 'text.class'), 'classes');
			
			if ($sql->query('SELECT COUNT(*) FROM `user` WHERE `id` = '.$id)->result() > 0)
            {
				$contact = Core::get_user($id);
				$sql->query('UPDATE `mail_contacts` SET `unread` = 0 WHERE `user_id` = '.$user_id.'  AND `id_cont` = '.$id);
				
				$set['title'] = 'Переписка с ' . $contact['nick'];
				include incDir . 'head.php';
			
				/* Отправка сообщения */
				if (isset($_POST['add_mail'])) {
				
					$text = my_esc($_POST['text'], 'true');
					
					
					if (empty($_POST['text']) || mb_strlen($_POST['text'], 'UTF-8') <= 1) {
						Core::msg_show('Сообщение слишком короткое');
						
					} elseif (mb_strlen($_POST['text'], 'UTF-8') > 1024) {
						Core::msg_show('Сообщение превышает 1024 символа');
				
					} elseif ($id == $user_id) {
						Core::msg_show('Нельзя отправлять сообщения самому себе');
					} else {
						$sql->query("INSERT INTO `mail` (`author`, `user`, `time`, `text`) VALUES ('$user_id', '$id', '$time', '$text')");
						

					if ($sql->query("SELECT COUNT(*) FROM `mail_contacts` WHERE `user_id` = '$user_id' AND `id_cont` = '$id'")->result() == 0)
						$sql->query('INSERT INTO `mail_contacts` (`user_id`, `id_cont`, `outbox`) VALUES (' . $user_id . ', ' . $id . ', "1")');
					else
						$sql->query('UPDATE `mail_contacts` SET `outbox` = `outbox`+1 WHERE `user_id` = ' . $user_id . ' AND `id_cont` = '. $id);

					if ($sql->query("SELECT COUNT(*) FROM `mail_contacts` WHERE `user_id` = '". $id ."' AND `id_cont` = '" . $user_id . "'")->result() == 0)
						$sql->query('INSERT INTO `mail_contacts` (`user_id`, `id_cont`, `inbox`, `unread`) VALUES (' . $id . ', ' . $user_id . ', "1", "1")');
					else
						$sql->query('UPDATE `mail_contacts` SET `inbox` = `inbox`+1, `unread` = `unread`+1 WHERE `user_id` = ' . $id . ' AND `id_cont` = '. $user_id);

						
						
						Core::msg_show('Сообщение успешно отправлено', 'msg');
                        unset($text);
					}
				}
				
				
				?>
					<form action="?act=<?=$act . '&amp;id=' .$id?>" method="POST">
						Сообщение:<br />
						<textarea name="text"><?php echo !empty($text) ? $text : null ?></textarea><br />
						
						<input type="submit" name="add_mail"/>
					</form>
				<?php
				
				
				
				$count = $sql->query("SELECT COUNT(*) FROM `mail` WHERE `author` = '$user_id' AND `user` = '$id' OR `author` = '$id' AND `user` = '$user_id'")->result();

				if ($count > 0) {
					
					$sql->query('UPDATE `mail` SET `no` = "1" WHERE `author` = ' . $id . ' AND `user` = ' . $user_id);
					
					$page = new page($count, $set['p_str']);
					$i = 1;
					
					$sql->query("SELECT * FROM `mail` WHERE `author` = '$user_id' AND `user` = '$id' OR `author` = '$id' AND `user` = '$user_id' order by `id` DESC LIMIT " . $page->limit());
					
					while ($mail = $sql->fetch())
					{
						echo 
                        '<div class="'.($i++ % 2 ? 'p_m' : 'p_t').'">' .
                        Core::user_show($mail['author'] == $id ? $contact : $user, 
                            array('post' => text::output($mail['text']), 'status' => $mail['time'], 'is_time' => true)) . 
                        (empty($mail['no']) ?'<span style="color:#FF0000;float:right;">Не прочитанно</span>' : null) . 
						'</div>';
					}
					$page->display('?act=mail&amp;id=' . $id . '&amp;');
                    unset($text, $contact, $page, $mail, $count);
                    $sql->free();
				} else
					Core::msg_show('Переписка пуста');
				
			} else
				Core::msg_show('Пользователь не найден');
			
			echo '<a href="?"><div class="menu_razd">Список контактов</div></a>';
			break;




		/*
		---------------------------------
		Написание сообщения
		---------------------------------
		*/
		case 'add_mail':
			$set['title'] = 'Написать сообщение';
			include H.'engine/includes/head.php';
			
			
			if (isset($_POST['add'])) {
				$_SESSION['login_mail'] = my_esc($_POST['login'], 'true');
				$_SESSION['text_mail'] = my_esc($_POST['text'], 'true');
				$login_mail = my_esc($_POST['login'],'true');
				$text = my_esc($_POST['text'], 'true');
				
				if (empty($_POST['login'])) {
					Core::msg_show('Вы не ввели логин пользователя');
					
				} elseif (empty($_POST['text']) || mb_strlen($_POST['text'], 'UTF-8') <= 1) {
					Core::msg_show('Сообщение слишком короткое');
					
				} elseif (mb_strlen($_POST['text'], 'UTF-8') > 1024) {
					Core::msg_show('Сообщение превышает 1024 символа');
				
				} elseif ($_POST['login'] == $user['nick']) {
					Core::msg_show('Нельзя отправлять сообщения самому себе');
				
				} elseif ($sql->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '$login_mail'")->result() > 0) {
					$sql->query("SELECT `id` FROM `user` WHERE `nick` = '$login_mail'");
					$users = $sql->fetch();
					
					/* Отправляем сообщение */
					$sql->query("INSERT INTO `mail` (`author`, `user`, `time`, `text`) VALUES ('$user_id', '" . $users['id'] . "', '$time', '$text')");
					
					
					/* Добавляем пользователя в контакты, если его нету и обновляем счетчики */
					if ($sql->query("SELECT COUNT(*) FROM `mail_contacts` WHERE `user_id` = '$user_id' AND `id_cont` = '" . $users['id'] . "'")->result() == 0)
						$sql->query('INSERT INTO `mail_contacts` (`user_id`, `id_cont`, `outbox`) VALUES (' . $user_id . ', ' . $users['id'] .', "1")');
					else
						$sql->query('UPDATE `mail_contacts` SET `outbox` = `outbox`+1 WHERE `user_id` = ' . $user_id . ' AND `id_cont` = '. $users['id']);

					if ($sql->query("SELECT COUNT(*) FROM `mail_contacts` WHERE `user_id` = '". $users['id'] ."' AND `id_cont` = '" . $user_id . "'")->result() == 0)
						$sql->query('INSERT INTO `mail_contacts` (`user_id`, `id_cont`, `inbox`, `unread`) VALUES (' . $users['id'] . ', ' . $user_id . ', "1", "1")');
					else
						$sql->query('UPDATE `mail_contacts` SET `inbox` = `inbox`+1, `unread` = `unread`+1 WHERE `user_id` = ' . $users['id'] . ' AND `id_cont` = '. $user_id);

						
					UnSET($_SESSION['login_mail'], $_SESSION['text_mail']);
					$_SESSION['add_mail'] = true;
					Core::stop('?');
						
				
				} else
					Core::msg_show('Пользователь с логином <b>' . $login_mail . '</b> не найден');
			}
			
			?>
				<form action="" method="POST">
					Введите логин пользователя:<br />
					<input type="text" name="login" value="<?php echo empty($_SESSION['login_mail']) ? '' : $_SESSION['login_mail']; ?>" /><br />
					
					Сообщение:<br />
					<textarea name="text"><?php echo empty($_SESSION['text_mail']) ? '' : $_SESSION['text_mail']; ?></textarea><br />
					
					<input type="submit" name="add"/>
				</form>
				<a href="?"><div class="menu_razd">Список контактов</div></a>
			<?php
			break;





		/*
		---------------------------------
		Вывод контактов
		---------------------------------
		*/
		default:
		
		$set['title'] = 'Контакты';
		include H.'engine/includes/head.php';
		
		Core::get('page.class', 'classes');
		
		if (isset($_SESSION['add_mail'])) {
			Core::msg_show('Сообщение успешно отправлено', 'msg');
			UnSET($_SESSION['add_mail']);
		}
		
		
		
		echo '<div class="fmenu"><a href="?act=add_mail">Написать сообщение</a></div>';
		
		
		$count = $sql->query('SELECT COUNT(*) FROM `mail_contacts` WHERE `user_id` = ' . $user_id)->result();
		
		if ($count > 0) {
			
			$k_post = $count;
			$page = new page($k_post, $set['p_str']);
			
			$query = $sql->query('SELECT `mail_contacts`.*, `user`.`nick`,`user`.`pol`, `user`.`id` FROM `mail_contacts` LEFT JOIN `user` ON `mail_contacts`.`id_cont` = `user`.`id` WHERE `mail_contacts`.`user_id` = ' . $user_id . ' order by `mail_contacts`.`unread` DESC LIMIT ' . $page->limit());
			
			while ($cont = $sql->fetch())
			{
				echo '
				<a href="?act=mail&amp;id=' . $cont['id'] . '">
				<div class="link">'. Core::user_icon($cont) .' ' . $cont['nick'] . '
				(' . $cont['inbox'] . '/' . $cont['outbox'] . ')' .
				(empty($cont['unread']) ? '' : '<b>+' . $cont['unread'] . '</b>') .
				'</div></a>';
			}
			
		} else
			Core::msg_show('Контактов нет');
	}

	echo '
		<div class="menu_razd">См. также</div>
		<div class="link"><a href="/pages/menu.php">Кабинет</a></div>
		<div class="link"><a href="/">Главная</a></div>';
	
	include H.'engine/includes/foot.php';