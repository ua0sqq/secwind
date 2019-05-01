<?php
	include '../../engine/includes/start.php';
	
	if (!$admin)
		Core::stop('/?');

	$set['title'] = 'Редактирование пользователей';
	include incDir . 'head.php';

	if ($act == 'new' && isset($_SESSION['edit_user_id']))
	{
		unset($_SESSION['edit_user_id']);
	}

	if (isset($_POST['user']) || $id)
	{
		$_SESSION['edit_user_id'] = 
			$sql->query('SELECT `id` FROM `user` WHERE `'.(Core::form('order') == 'id' || $id ? 'id' : 'nick').'` = "'.($id ? $id : Core::form('user')).'" LIMIT 1')->result();
		if ($_SESSION['edit_user_id'] == 0 || $_SESSION['edit_user_id'] == $user_id)
		{
			unset($_SESSION['edit_user_id']);
			Core::msg_show('Поиск не дал результатов', 'error');
			//Include incDir . 'foot.php';
		}
	}

	if (!isset($_SESSION['edit_user_id']))
	{
		?>
		<div class="menu_razd">Редактирование пользователя</div>
		<form method="post">
		Искать пользователя по:<br />
		<select name="order">
			<option value="id">Id</option>
			<option value="nick">Ник</option>
		</select><br />
		Данные для поиска:<br />
		<input type="text" name="user" value=""/><br />
		<input type="submit" value="Искать"/>
		</form>
		<?php
	}
	else
	{
		$editor = Core::get_user($_SESSION['edit_user_id']);
		echo '<div class="post">Редактирование пользователя '.$editor['nick'].', <a href="?act=new">изменить</a></div>';
		
		if (isset($_POST['save']))
		{
			if (isset($_POST['nick']) && $_POST['nick'] != $editor['nick'])
			{
				if ($sql->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."'")->result() == 1)
					$err = 'Ник '.$_POST['nick'].' уже занят';
				else
				{
					$nick = Core::form('nick');
					$nick_len = mb_strlen($nick);

					if(!preg_match("#^([A-zА-я0-9\-\_\ ])+$#ui", $nick))
						$err[] = 'В нике присутствуют запрещенные символы';
					if ($nick_len < 3)
						$err[] = 'Короткий ник';
					elseif ($nick_len > 32)
						$err[] = 'Длина ника превышает 32 символа';
					
					if (!isset($err))
					{
						$sql->query("UPDATE `user` SET `nick` = '$nick' WHERE `'".Core::form('order')."` = '".Core::form('user')."' LIMIT 1");
					}
				}
			}

			if (isset($_POST['ank_name']) && preg_match('#^([A-zА-я \-]*)$#ui', $_POST['ank_name']))
			{
				$editor['ank_name'] = Core::form('ank_name');
				$sql->query("UPDATE `user` SET `ank_name` = '$editor[ank_name]' WHERE `id` = '$editor[id]' LIMIT 1");
			}
			else $err='Вы ошиблись в поле имя';

			if (isset($_POST['ank_d_r']) && (is_numeric($_POST['ank_d_r']) && $_POST['ank_d_r']>0 && $_POST['ank_d_r']<=31 || $_POST['ank_d_r']==NULL))
			{
				$editor['ank_d_r']=$_POST['ank_d_r'];
				if ($editor['ank_d_r']==null)
					$editor['ank_d_r']='null';
				$sql->query("UPDATE `user` SET `ank_d_r` = $editor[ank_d_r] WHERE `id` = '$editor[id]' LIMIT 1");
				if ($editor['ank_d_r']=='null')
					$editor['ank_d_r']=NULL;
			}
			else $err='Неверный формат дня рождения';

			if (isset($_POST['ank_m_r']) && (is_numeric($_POST['ank_m_r']) && $_POST['ank_m_r']>0 && $_POST['ank_m_r']<=12 || $_POST['ank_m_r']==NULL))
			{
				$editor['ank_m_r']=$_POST['ank_m_r'];
				if ($editor['ank_m_r']==null)$editor['ank_m_r']='null';
				$sql->query("UPDATE `user` SET `ank_m_r` = $editor[ank_m_r] WHERE `id` = '$editor[id]' LIMIT 1");
				if ($editor['ank_m_r']=='null')$editor['ank_m_r']=NULL;
			}
			else $err='Неверный формат месяца рождения';

			if (isset($_POST['ank_g_r']) && (is_numeric($_POST['ank_g_r']) && $_POST['ank_g_r']>0 && $_POST['ank_g_r']<=date('Y') || $_POST['ank_g_r']==NULL))
			{
				$editor['ank_g_r']=$_POST['ank_g_r'];
				if ($editor['ank_g_r']==null)$editor['ank_g_r']='null';
				$sql->query("UPDATE `user` SET `ank_g_r` = $editor[ank_g_r] WHERE `id` = '$editor[id]' LIMIT 1");
				if ($editor['ank_g_r']=='null')$editor['ank_g_r']=NULL;
			}
			else $err='Неверный формат года рождения';

			if (isset($_POST['ank_city']) && preg_match('#^([A-zА-я \-]*)$#ui', $_POST['ank_city']))
			{
				$editor['ank_city'] = Core::form('ank_city');
				$sql->query("UPDATE `user` SET `ank_city` = '$editor[ank_city]' WHERE `id` = '$editor[id]' LIMIT 1");
			}
			else $err='Вы ошиблись в поле город';

			if (isset($_POST['ank_icq']) && (is_numeric($_POST['ank_icq']) && strlen($_POST['ank_icq'])>=5 && strlen($_POST['ank_icq'])<=9 || $_POST['ank_icq']==NULL))
			{
				$editor['ank_icq']=$_POST['ank_icq'];
				if ($editor['ank_icq']==null)$editor['ank_icq']='null';
				$sql->query("UPDATE `user` SET `ank_icq` = $editor[ank_icq] WHERE `id` = '$editor[id]' LIMIT 1");
				if ($editor['ank_icq']=='null')$editor['ank_icq']=NULL;
			}
			else $err='Неверный формат ICQ';

			if (isset($_POST['ank_n_tel']) && (is_numeric($_POST['ank_n_tel']) && strlen($_POST['ank_n_tel'])>=5 && strlen($_POST['ank_n_tel'])<=11 || $_POST['ank_n_tel']==NULL))
			{
				$editor['ank_n_tel']=$_POST['ank_n_tel'];
				$sql->query("UPDATE `user` SET `ank_n_tel` = '$editor[ank_n_tel]' WHERE `id` = '$editor[id]' LIMIT 1");
			}
			else $err='Неверный формат номера телефона';

			if (isset($_POST['ank_mail']) && ($_POST['ank_mail']==null || preg_match('#^[A-z0-9-\._]+@[A-z0-9]{2,}\.[A-z]{2,4}$#ui',$_POST['ank_mail'])))
			{
				$user['ank_mail']=$_POST['ank_mail'];
				$sql->query("UPDATE `user` SET `ank_mail` = '$user[ank_mail]' WHERE `id` = '$user[id]' LIMIT 1");
			}
			else $err='Неверный E-mail';

			if (isset($_POST['ank_o_sebe']) && preg_match('#^([A-zА-я \-]*)$#ui', $_POST['ank_o_sebe']))
			{
				$editor['ank_o_sebe'] = Core::form('ank_o_sebe');
				$sql->query("UPDATE `user` SET `ank_o_sebe` = '$editor[ank_o_sebe]' WHERE `id` = '$editor[id]' LIMIT 1");
			}
			else $err='Вы ошиблись в поле о себе';

			if (isset($_POST['new_pass']) && mb_strlen($_POST['new_pass'])>5)
			{
				$sql->query("UPDATE `user` SET `pass` = '".md5($editor['id'] . $_POST['new_pass'])."' WHERE `id` = '$editor[id]' LIMIT 1");
			}

			if (isset($_POST['group_access']) && $_POST['group_access'] < 5 && 
				$editor['group_access']!=intval($_POST['group_access']))
			{
				$editor['group_access']= $editor['group_access'] > 9 ? $editor['group_access'] : intval($_POST['group_access']);
				$sql->query("UPDATE `user` SET `group_access` = '$editor[group_access]' WHERE `id` = '$editor[id]' LIMIT 1");
			}

			if (isset($_POST['balls']) && is_numeric($_POST['balls']))
			{
				$editor['balls']=intval($_POST['balls']);
				$sql->query("UPDATE `user` SET `balls` = '$editor[balls]' WHERE `id` = '$editor[id]' LIMIT 1");
			}

			if (!isset($err))
			{
				Core::msg_show('Изменения успешно приняты', 'msg');
				Core::get('cache.class');
				$editor = Core::get_user($editor['id'], true);
				Cache::multi_delete('user[id='.$editor['id']);
			}
			else
				Core::msg_show($err, 'error');
		}
	?>

	<form method='post' action='?id=<?=$editor['id']?>'>
	Ник:<br />
	<input type='text' name='nick' value='<?=$editor['nick']?>' maxlength='32'/><br />
	Имя в реале:<br />
	<input type='text' name='ank_name' value='<?=$editor['ank_name']?>' maxlength='32' /><br />
	Дата рождения:<br />
	<input type='text' name='ank_d_r' value='<?=$editor['ank_d_r']?>' size='2' maxlength='2' />
	<input type='text' name='ank_m_r' value='<?=$editor['ank_m_r']?>' size='2' maxlength='2' />
	<input type='text' name='ank_g_r' value='<?=$editor['ank_g_r']?>' size='4' maxlength='4' /><br />
	Город:<br />
	<input type='text' name='ank_city' value='<?=$editor['ank_city']?>' maxlength='32' /><br />
	ICQ:<br />
	<input type='text' name='ank_icq' value='<?=$editor['ank_icq']?>' maxlength='9' /><br />
	E-mail:<br />
	<input type='text' name='ank_mail' value='<?=$editor['ank_mail']?>' maxlength='32' /><br />
	Номер телефона:<br />
	<input type='text' name='ank_n_tel' value='<?=$editor['ank_n_tel']?>' maxlength='11' /><br />
	О себе:<br />
	<input type='text' name='ank_o_sebe' value='<?=$editor['ank_o_sebe']?>' maxlength='512' /><br />
	Баллы:<br />
	<input type='text' name='balls' value='<?=$editor['balls']?>' /><br />
	Группа:<br />
	<select name='group_access'>
		<option value='1' <?=($editor['group_access'] == 1 ? ' selected="selected"':null)?>>Пользователь</option>
		<option value='2' <?=($editor['group_access'] == 2 ? ' selected="selected"':null)?>>Модератор</option>
		<option value='3' <?=($editor['group_access'] == 3 ? ' selected="selected"':null)?>>Админ</option>
	</select><br />
	Новый пароль:<br />
	<input type='text' name='new_pass' value='' /><br />
	<input type='submit' name='save' value='Сохранить' />
	</form>
	<?php
	}

	if ($creator)
	{
		?>
		<a href='/admin/?act=users'><div class="link">Пользователи</div></a>
		<a href='/admin/'><div class="link">Админка</div></a>
		<?php
	}
	include incDir . 'foot.php';