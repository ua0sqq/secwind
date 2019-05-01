<?php
    include '../engine/includes/start.php';
	$set['title'] = 'Смайлы';

	include H . 'engine/includes/head.php';

	$i = 0;

	/*
	* пабличный скрипт. автор неизвестен
	*/


	/*
	* Глобальный фикс =)
	*/

	if (!$admin && ($act != 'kat' || empty($act)))
	{
		$act = null;
	}



	switch($act)
	{
		default:
			$sql->query("SELECT `id`, `name` FROM `smiles` WHERE `type` = 'kat'");

			if ($sql->num_rows())
			{
				while($kat = $sql->fetch())
				{
					echo '<a href="?act=kat&amp;id='.$kat['id'].'"><div class="'.($i++ % 2 ? 'p_m' : 'p_t').'">'.$kat['name'].'</div></a>';
				}
			}
			else
			{
				echo 'Пока смайлов нет';
			}

			if ($admin)
			{
				echo '<a class="link" href="?act=add_kat">Добавить категорию</a>';
			}
		break;
		
		case 'kat':
			$sql->query("SELECT * FROM `smiles` WHERE `type` = 'kat'  AND `id` = $id");

			if (!$sql->num_rows())
			{
				echo 'not found';
				include incDir . 'foot.php';
			}

			$kat = $sql->fetch();

			echo '<div class="menu_razd"><b>Директория</b>: '.$kat['name'].'</div>';
			if ($admin)
			{
				echo '<a href="?act=new&amp;id='.$id.'" class="link">&raquo; Добавить смайл</a>';
			}

			Core::get('page.class');

			$total = $sql->query("SELECT COUNT(*) FROM `smiles` WHERE `parent_id` = '$id'")->result();
			$page = new page($total, $set['p_str']);

			if ($total == 0)
			{
				echo '<div class="err">Нет смайлов</div>';
			}

			$sql->query("SELECT * FROM `smiles` WHERE `parent_id` = '$id' ORDER BY `id` ASC LIMIT ".$page->limit());
			while($post = $sql->fetch())
			{
				echo '<img src="/style/smiles/'.$post['name'].'.gif" alt="'.$post['name'].'"/> '.$post['symbol'].' <br />';

				if ($admin)
				{
					echo '<a href="?act=del&amp;id='.$post['id'].'">удалить</a>'.
						' | <a href="?act=edit&amp;id='.$post['id'].'">редактировать</a><br />';
				}
				echo '<br />';
			}

			$page->display('?act=kat&amp;id='.$id.'&amp;');

			echo '<a href="?act" class="link">Все категории</a>';
			if ($admin)
			{
				echo 
				'<a href="?act=edit_kat&amp;id='.$id.'" class="link">Редактировать</a>'.
				'<a href="?act=del_kat&amp;id='.$id.'" class="link">Удалить категорию</a>';
			}
		break;

		case 'new':
		if ($admin)
		{
			if (isset($_FILES['file']))
			{
				$type = $_FILES['file']['type'];
				if ($type !== 'image/jpeg' && $type!=='image/jpg' && $type!=='image/gif' && $type!=='image/png')
				$err='Это не картинка.';
				$name = Core::form('name');
				$name_len = mb_strlen($name);
	
				if ($name_len < 1)
					$err='Слишком короткое название';
				if ($name_len > 32)
					$err='Слишком днинное название';

				if ($sql->query("SELECT COUNT(*) FROM `smiles` WHERE `name` = '$name' and `type` ='smile'")->result() == 1)
					$err='Такой смайл уже есть';

				if (!isset($err))
				{
					$namef = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
					$tmp = $_FILES['file']['tmp_name'];
					$sql->query("INSERT INTO `smiles` (`name`, `symbol`, `parent_id`, `type`) values('$namef', '$name', '$id', 'smile')");

					move_uploaded_file($tmp, H.'style/smiles/'.$namef.'.gif');

					echo '<div class="msg">Смайл успешно добавлен</div>';
				}
				else
					echo Core::msg_show($err);
			}
			else
			{
				?>
				<form method="post" action="?act=new&amp;id=<?=$id?>" enctype='multipart/form-data'>
					Название:<br />
					<input name="name" type="text" maxlength='32' value='' /><br />
					Прикрепить картинку:<br />
					<input type="file" name="file"/><br />
					<input value='Добавить' type='submit' name='ok' /><br />
				</form>
				<?php
			}
			echo '<a href="?act=kat&amp;id='.$id.'" class="link">&larr; Назад</a>';
		}
		break;

	case 'edit':
		if ($admin)
		{
			if ($sql->query('SELECT COUNT(*) FROM `smiles` WHERE `id` = '.$id.' and `type` = "smile" LIMIT 1')->result() == 0)
				echo 'Смайл не найден!';
			else
			{
				$bm = $sql->query('SELECT * FROM `smiles` WHERE `id` =  '.$id.' LIMIT 1')->fetch();
				if (isset($_POST['ok']))
				{
					$name = Core::form('name');
					$name_len = mb_strlen($name);

					if ($name_len < 1)
						echo 'Название слишком короткое';
					elseif ($name_len > 32)
						echo 'Название слишком длинное';
					else
					{
						$sql->query("UPDATE `smiles` SET `symbol` = '$name' WHERE `id` = '$bm[id]' LIMIT 1");
						echo '<div class="msg">Смайл успешно отредактирован</div>';
					}
				}
				else
				{
					?>
					<form method='post' action='?act=edit&amp;id=<?=$id?>'>
					<b>Название</b><br /><input type='text' name='name' value='<?=$bm['symbol']?>'/> <br />
					<input type='submit' value='Сохранить' name='ok' /><br />
					</form>
					<?php
				}
				echo '<a href="?act=kat&amp;id='.$bm['parent_id'].'">Назад</a>';
			}
		}
	break;

	case 'del':
	if ($admin)
	{
		if ($sql->query("SELECT COUNT(*) FROM `smiles` WHERE `id` =  '$id' and `type`='smile' LIMIT 1")->result()==0)
			echo 'Смайл не найден!';
		else
		{
			$bm = $sql->query("SELECT * FROM `smiles` WHERE `id` = '$id' LIMIT 1")->fetch();
			if (isset($_POST['ok']))
			{
				$sql->query("DELETE FROM `smiles` WHERE `id` = '$id'");
				unlink(H.'style/smiles/'.$bm['name'].'.gif');
				echo '<div class="msg">Смайл успешно удален</div>';
			}
			else
			{
				?>
				Вы уверены, что хотите удалить этoт смайл?<br />
				<form method='post' action='?act=del&amp;id=<?=$id?>'>
				<input type='submit' name='ok' value='Да' />
				</form>
				<a href='?act=kat&amp;id=<?=$bm['parent_id']?>' class="link">Нет</a>
				<?php
			}
		}
	}
	break;

	case 'add_kat':
		if ($moder)
		{
			if (isset($_POST['name']))
			{
				$name = Core::form('name');
				$name_len = mb_strlen($name);

				if ($name_len < 0)
					echo 'Название слишком короткое';
				elseif ($name_len > 50)
					echo 'Название слишком длинное';
				elseif ($sql->query("SELECT COUNT(*) FROM `smiles` WHERE `name` = '$name'  and `type` = 'kat' LIMIT 1")->result() != 0)
					echo 'Такая категория уже есть!';
				else
				{
					$sql->query("INSERT INTO `smiles` (`name`, `type`, `parent_id`) values ('$name',  'kat',  '0')");
					echo '<div class="msg">Категория успешно добавлена</div>';
				}
			}
			else
			{
				?>
				<form method='post' action='?act=add_kat'>
				Название:<br />
				<input type='text' name='name' value=''/><br />
				<input type='submit' value='Создать'/>
				</form>
				<?php
			}
			echo '<a href="?act" class="link">Назад</a>';
		}
	break;

	case 'edit_kat':
		if ($admin)
		{
			if ($sql->query("SELECT COUNT(*) FROM `smiles` WHERE `id` = '$id' and `type` = 'kat' LIMIT 1")->result()==0)
				echo 'Категория не найдена!';
			else
			{
				$bm = $sql->query("SELECT * FROM `smiles` WHERE `id` = '$id' LIMIT 1")->fetch();
				if (isset($_POST['name']))
				{
					$name = Core::form('name');
					$name_len = mb_strlen($name);

					if ($name_len < 1)
						echo 'Название слишком короткое';
					elseif ($name_len > 50)
						echo 'Название слишком длинное';
					else
					{
						$sql->query("UPDATE `smiles` SET `name` = '$name' WHERE `id` = '$bm[id]' LIMIT 1");
						echo '<div class="msg">Категория успешно отредактирована</div>';
					}
				}
				else
				{
					?>
					<form method='post' action='?act=edit_kat&amp;id=<?=$bm['id']?>'>
					<b>Название</b><br />
					<input type='text' name='name' value='<?=$bm['name']?>'/> <br />
					<input type='submit' value='Сохранить'/><br />
					</form>
					<?php
				}
			}
			echo '<a href="?act" class="link">Назад</a>';
		}
	break;

	case 'del_kat':
		if ($admin)
		{
			if ($sql->query("SELECT COUNT(*) FROM `smiles` WHERE `id` = '$id' and `type` = 'kat' LIMIT 1")->result()==0)
				echo 'Категория не найдена!';
			elseif ($sql->query("SELECT COUNT(*) FROM `smiles` WHERE `parent_id` = '$id'")->result() > 0)
				echo 'Категория не пуста';
			else
			{
				$bm = $sql->query("SELECT * FROM `smiles` WHERE `id` = '$id' LIMIT 1")->fetch();
				if (isset($_POST['ok']))
				{
					$sql->query("SELECT `name` FROM `smiles` WHERE `parent_id` = '$bm[id]'");
					while ($f = $sql->result())
					{
						unlink(H.'style/smiles/'.$f['name'].'.gif');
					}

					$sql->query("DELETE FROM `smiles` WHERE `parent_id` = '$bm[id]'");
					$sql->query("DELETE FROM `smiles` WHERE `id` = '$bm[id]'");
					echo '<div class="msg">Категория успешно удалена</div>';
				}
				else
				{
					?>Вы уверены, что хотите удалить эту категорию?<br />
					<form method='post' action='?act=del_kat&amp;id=<?=$bm['id']?>'>
					<input type='submit' name='ok' value='Да' />
					</form>
					<?php
				}
				echo '<a href="?act" class="link">Назад</a>';
			}
		}
		break;
	}
	
	echo 
		'<a href="?" class="link">Смайлы</a>'.
		'<a href="bbcodes.php" class="link">Бб коды</a>'.
		'<a href="/" class="link">Главная</a>';

	include '../engine/includes/foot.php';