<?php
    include '../engine/includes/start.php';

    $set['title']='Новости сайта';

    include H.'engine/includes/head.php';

	!$admin ? $act = null : null;
	
	function __delete_cache()
	{
		Cache::multi_delete('news', tmpDir);
		if (is_file(tmpDir . 'index_page.swc'))
			unlink(tmpDir . 'index_page.swc');
	}

    if (isset($_GET['read']))
    {
        $news_id = intval($_GET['read']);
        if ($sql->query('SELECT COUNT(*) FROM `news` WHERE `id` = '.$news_id)->result() == 0)
        {
            Core::msg_show('Новость удалена','error');
        }
        else
        {
            if ($user_id && !empty($_POST['msg']))
            {
                $sql->query("INSERT INTO `news_comments` SET 
                    `msg` = '".my_esc(mb_substr($_POST['msg'], 0, 300))."', 
                    `time` = '".$time."', 
                    `user_id`='".$user_id."', 
                    `news_id` = '".$news_id."'");
            }
            
            $news = $sql->query('SELECT `title`,`time`,`msg` FROM `news` WHERE `id` = '.$news_id)->fetch();
            echo '<div class="menu_razd">'.
                htmlspecialchars($news['title']). ' ('. Core::time($news['time']).') </div>
                <div class="post">'.
				text::output($news['msg']).'</div>';
            
            $k_post = $sql->query('SELECT COUNT(*) FROM `news_comments` WHERE `news_id` = '.$news_id)->result();
            $page = new page($k_post, $set['p_str']);
            
            if ($user_id)
            {
            ?>
            <form action="?read=<?=$news_id?>" method="post">
            <textarea name="msg"></textarea><br />
            <input type="submit"/>
            </form>
            <?php
            }
            
            $sql->query('SELECT `comment_id`, `user_id`, `msg`, `time` FROM `news_comments` WHERE `news_id` = '.$news_id .' order by `time` DESC LIMIT ' . $page->limit());
            while($comment = $sql->fetch())
            {
                echo '<div class="link">'.
                Core::user_show(Core::get_user($comment['user_id']), array('post' => text::output($comment['msg']), 'is_time' => true, 'status' => $comment['time'])).
                '</div>';
            }
            $page->display('?read='.$news_id.'&amp;');
        }
    }
    else
    {
	   switch($act)
	   {
		default:
			Core::get('cache.class');
			$cache = new cache(tmpDir . 'news[page='.$cur_page.';access='.($admin ? 1 : 0).'].swc');
			if (!$cache->life())
			{
				ob_start();
				Core::get(array('text.class', 'page.class'));
				
				if ($admin) {
				?><form><input type="hidden" name="act" value="edit"/><input name="or_add" type="submit" style="width:40%" value="Добавить новость"/></form><?php
				}

				$k_post = $sql->query('SELECT COUNT(*) FROM `news`')->result();
				$page = new page($k_post, $set['p_str']);
				$q = $sql->query('SELECT * FROM `news` ORDER BY `id` DESC LIMIT '. $page->limit());
				
				if ($k_post == 0)
					echo 'Нет новостей';
    
				while ($post = $sql->fetch())
				{
					echo '
					<div class="post">
					<span class="status">'.htmlspecialchars($post['title']). '</span> ('. Core::time($post['time']).')<br />'.
					text::output($post['msg']).'<br /><a href="?read='.$post['id'].'">[&nbsp;&nbsp;Обсудить&nbsp;&nbsp;]</a>';

					if ($admin)
					{
						echo '&nbsp;&nbsp;&nbsp;&nbsp;<a align="center" href="?act=edit&amp;id='.$post['id'].'">[&nbsp;&nbsp;Редактировать&nbsp;&nbsp;]</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="?act=delete&amp;id='.$post['id'].'">[&nbsp;&nbsp;Удалить&nbsp;&nbsp;]</a>';
					}

					echo '</div>';
				}

				$page->display('?');
				$cache->write();
				$sql->free();
			}
			echo $cache->read();
			unset($cache, $page);
		break;

		case 'delete':
			$check = isset($_GET['yes']) ? false :$sql->query('SELECT `title` from `news` where `id` = '.$id)->num_rows();
			if (!$check)
			{
				if (isset($_GET['yes']))
				{
					__delete_cache();
					$sql->query('Delete from `news` where `id` = '.$id);
					Core::msg_show('Новость удалена');
				}
				else
					Core::msg_show('Новость не найдена');
			}
			else
			{
				echo 'Вы действительно хотите удалить новость"<span class="status">'.$sql->result().'</span>" ?<br />
				<a href="?act=delete&amp;id='.$id.'&amp;yes">[&nbsp;&nbsp;Да&nbsp;&nbsp;]</a>&nbsp;&nbsp; | &nbsp;&nbsp;<a href="?">[&nbsp;&nbsp;Нет&nbsp;&nbsp;]</a>';
			}
		break;

		case 'edit':
			$adding = isset($_GET['or_add']); // Добавление или редактирование
			$words = $adding ? array('INSERT', 'добавлена', 'Добавление', 'Добавить') : array('UPDATE', ' изменена', 'Редактирование', 'Изменить');
			

			if (!$adding) // Если редактирование
			{
				if ($sql->query('SELECT * from `news` where `id` = '.$id)->num_rows() == 0)
				{
					Core::msg_show('Новость не найдена');
					echo '<a href="?"><div class="menu_razd">Новости</div></a>';
					include H.'engine/includes/foot.php';
				}
				else
					$news = $sql->fetch();
			}
			else
			{
				$news['title'] = Core::form('title');
				$news['msg'] = Core::form('msg');
			}

			echo '<div class="menu_razd">'.$words[2].' новости</div>';

			if (isset($_POST['title'], $_POST['msg']))
			{
				$TitLen = mb_strlen($_POST['title']);
				$MsgLen = mb_strlen($_POST['msg']);
				if ($TitLen > 32 || $TitLen < 3)
					Core::msg_show('Заголовок должен быть более 3х символов и меньше 32х символов');
				elseif ($MsgLen > 1024 || $MsgLen < 3)
					Core::msg_show('Текст должен быть более 3х символов и меньше 32х символов');
				else
				{
					$sql->query(
						$words[0] ." `news` SET 
						`title` = '".Core::form('title')."',
						`msg` = '".Core::form('msg')."',
						`time` = ".$time . (!$adding ? ' WHERE `id` = '.$id : ''));

					__delete_cache();

					Core::msg_show('Новость '.$words[1], 'menu_razd');
					if ($adding)
						$news['title'] = $news['msg'] = '';
					else
					{
						$news['title'] = htmlspecialchars($_POST['title']);
						$news['msg'] = htmlspecialchars($_POST['msg']);
					}
				}
			}
			?>
			<form method="post">
				Заголовок:<br />
				<input type="text" name="title" value="<?=$news['title']?>"/><br />
				Текст:<br />
				<textarea name="msg"><?=$news['msg']?></textarea><br />
				<input type="submit" value="<?=$words[3]?>"/>
			</form>
			<?php
		break;

		case 'uninstall':
		if ($creator)
		{
			if (isset($_GET['yes']))
			{
				$sql->multi("DROP TABLE `news`;DELETE FROM `modules` where `name` = 'news';DELETE FROM `module_services` where `belongs` = 'news';");
				unlink(H . 'engine/services/last_news.php');
				unlink(H . 'pages/news.php');
				echo 'Модуль "Новости" удален';
			}
			else
				echo 'Вы действительно хотите удалить модуль "Новости"?<br /><a href="?act=uninstall&amp;yes">[  Да  ]</a> | <a href="?">[  Нет  ]</a>';
		}
		break;
	   }
    }

	?>
	<a href="/"><div class="link">Главная</div></a>
	<a href="?"><div class="link">Новости сайта</div></a>
	<?php
    include H.'engine/includes/foot.php';