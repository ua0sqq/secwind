<?php
    include '../engine/includes/start.php';
    $set['title']='Гостевая книга'; // заголовок страницы
    include H.'engine/includes/head.php';
    Core::get(array('page.class', 'text.class', 'cache.class'), 'classes');
    
	if (!$admin)$act = null;

	switch($act)
	{
		case 'clean':

		if (isset($_POST['write'], $_POST['write2']))
		{
			$timeclear1 = 0;
			if ($_POST['write2']=='sut')
				$timeclear1 = time() - intval($_POST['write']) * 60 * 60 * 24;
			elseif ($_POST['write2']=='mes')
				$timeclear1 = time() - intval($_POST['write']) * 60 * 60 * 24 * 30;
			$deletes = $sql->query('DELETE FROM `guest` WHERE `time` > '.$timeclear1)->num_rows();
			$sql->query('OPTIMIZE TABLE `guest`');
			Core::msg_show( ' Удалено постов: '.$deletes);
			Cache::multi_delete('guestbook', tmpDir);
		}
		else
		?>
		<form method = "post" class='post' action="?act=clean">
		Будут удалены посты, написаные ... тому назад<br />
		<input name = "write" value="12" type="text" size='3' />
		<select name = "write2">
			<option value="">-------</option>
			<option value="mes">Месяцев</option>
			<option value="sut">Суток</option>
		</select><br />
		<input value="Очистить" type="submit" /><br />
		<a href="?">Отмена</a><br />
		</form>
		<?php
		include H.'engine/includes/foot.php';
		break;


		case 'del':
		
		if ($sql->query("SELECT COUNT(*) FROM `guest` WHERE `id` = '".$id."'")->result() == 1)
		{
			$sql->query("DELETE FROM `guest` WHERE `id` = '$id' limit 1");
			Core::msg_show('Сообщение удалено');
			Cache::multi_delete('guestbook', tmpDir);
		}
		break;
	

		case 'uninstall':
		
		if ($creator)
		{
			if (isset($_GET['yes']))
			{
				$sql->multi("DROP TABLE `guest`;DELETE FROM `modules` where `name` = 'gBook';DELETE FROM `module_services` where `belongs` = 'gBook';");
				unlink(H . 'engine/services/guestbook.php');
				unlink(H . 'pages/guestbook.php');
				Cache::multi_delete('guestbook', tmpDir);
				echo 'Мини-чат удален';
			}
			else
				echo 'Вы действительно хотите удалить модуль "Мини-чат"?<br /><a href="?act=uninstall&amp;yes">[  Да  ]</a> | <a href="?">[  Нет  ]</a>';
			include H.'engine/includes/foot.php';
		}
		break;
	}

    if (isset($_POST['msg']) && $user_id)
    {
        $msg   = my_esc($_POST['msg']);
        $mat    = text::antimat($msg, $user);
        $length = mb_Strlen($msg);

        if ($mat)
            $err = 'В тексте сообщения обнаружен мат: '.$mat;

        elseif ($length > 1024)
            $err = 'Сообщение слишком длинное';
        
        elseif ($length < 2)
            $err = 'Короткое сообщение';

        elseif ($sql->query("SELECT COUNT(*) FROM `guest` WHERE `id_user` = '".$user['id']."' AND `msg` = '".$msg."' LIMIT 1")->result())
            $err = 'Ваше сообщение повторяет предыдущее';

        elseif (!isset($err))
        {
            $sql->multi("INSERT INTO `guest` (`id_user`,  `time`, `msg`) values('".$user['id']."', '".time()."', '".$msg."');UPDATE `user` SET `balls` = `balls` + 1 WHERE `id` = '".$user['id']."' LIMIT 1;");
			$sql->free(true);
            Core::msg_show('Сообщение успешно добавлено<br /><a href="?">Вернутся</a>', 'msg');
            Cache::multi_delete('guestbook', tmpDir);
            include H.'engine/includes/foot.php';
        }
		unset($msg, $mat, $length);
    }

    if (isset($err))
        echo Core::msg_show($err);

    $cache = new cache(H.'engine/files/tmp/guestbook[page='.(isset($_GET['page']) ? intval($_GET['page']) : 1).';moder='.($moder ? 1 : 0).';user='.($user_id ? 1 : 0).'].swc');

    if (!$cache->life())
    {

    ob_start();

    $k_post = $sql->query("SELECT COUNT(*) FROM `guest`")->result();
    $page = new page($k_post, $set['p_str']);
    $i = 1;

    if (!$k_post)
        Core::msg_show('Нет сообщений', 'post');

    $sql->query('SELECT * from `guest` order by `id` DESC LIMIT ' . $page->limit());
    
    while ($post = $sql->fetch())
    {
		echo '<div class="'.($i++ % 2 ? 'p_m' : 'p_t').'">'.Core::user_show(Core::get_user($post['id_user']), array('post' => text::output($post['msg']), 'status' => $post['time'], 'is_time' => true));

		if ($user_id)
            echo '<br /><a href="?act=reply&amp;id='.$post['id_user'].'&amp;sid='.mt_rand(99,999).'#message">Ответить</a>';

        if ($moder)
            echo '<a style="float:right" href="?id='.$post['id'].'&amp;act=del">Удалить</a>';
        echo '</div>';
    }
   
    $page->display('?');
    $cache->write();
    }
    echo $cache->read();

    if ($user_id)
    {
		$area = null;
        if ($act == 'reply')
		{
			$for = Core::get_user($id);
			$area = '[b]' . $for['nick'] . '[/b], ';
		}
		?>
			<a name="message"></a>
			<form method = "post">
			Сообщение:<br />
			<textarea name="msg"><?=$area?></textarea><br />
			<input value="Отправить" type="submit"/>
			</form>
		<?php
	}

    if ($admin)
    {
        ?>
            <div class="fmenu">
                <a href="?act=clean">Очистить гостевую</a>
            </div>
        <?php
    }

	unset($cache, $page, $post, $data, $i);
	echo '<a href="/"><div class="menu_razd">Главная</div></a>';

    include H.'engine/includes/foot.php';