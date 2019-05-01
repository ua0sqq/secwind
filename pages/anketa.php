<?php
    include '../engine/includes/start.php';
    if (!$user_id)
        Core::stop();

	$set['title'] = 'Изменение данных';

	include H.'engine/includes/head.php';

	if (isset($_POST['edit']))
	{
	    if (!empty($_FILES['avatar']['tmp_name']))
        {
			Core::get('class_upload');
			$handle = new upload($_FILES['avatar']);
			if ($handle->uploaded)
			{
				$handle->file_new_name_body = $user_id;
				$handle->allowed = array('image/jpeg', 'image/gif', 'image/png');
				$handle->file_overwrite = true;
				$handle->image_resize = true;
				$handle->image_x = 100;
				$handle->image_ratio_y = true;
				$handle->image_convert = 'jpg';
				$handle->process(H . 'style/users/avatar/');
				if ($handle->processed) {
					echo '<div class="msg">Аватар добавлен</div>';
				}
				else
					echo '<div class="err">Проблема с загрузкой: ' . $handle->error . '</div>';
			}
        }

        # Имя
		   if ($user['ank_name'] != $_POST['name'])
		   {
			   if (preg_match('#^([A-zА-я \-]*)$#ui', $_POST['name']))
			   {
		           $name = my_esc($_POST['name'], 'true');
		           $sql->query("UPDATE `user` SET `ank_name` = '$name' WHERE `id` = '$user_id'");
			   }
			   else
			       Core::msg_show('Не верный формат имени');
		   }

           # Пол
		   if ($user['pol'] != $_POST['sex'])
		   {
		       $sex = intval($_POST['sex']);
		       $sql->query("UPDATE `user` SET `pol` = '$sex' WHERE `id` = '$user_id'");
		   }

		   # Город
		   if ($user['ank_city'] != $_POST['city'])
		    {
		       if (preg_match('#^([A-zА-я \-]*)$#ui', $_POST['city']))
			   {
		           $city = my_esc($_POST['city'], 'true');
		           $sql->query("UPDATE `user` SET `ank_city` = '$city' WHERE `id` = '$user_id'");
			   }
			   else
			       Core::msg_show('Не верный формат города');
		    }

		   # Дата рождения
		   if ($user['ank_d_r'] != $_POST['day'] || $user['ank_m_r'] != $_POST['month'] || $user['ank_g_r'] != $_POST['year'])
		    {
		       if (is_numeric($_POST['day']) && is_numeric($_POST['month']) && is_numeric($_POST['year']) && $_POST['day'] <= 31 && $_POST['month'] <= 12 && $_POST['year'] <= 2013)
			   {
		           $day = intval($_POST['day']);
			       $month = intval($_POST['month']);
			       $year = intval($_POST['year']);
		           $sql->query("UPDATE `user` SET `ank_d_r` = '$day', `ank_m_r` = '$month', `ank_g_r` = '$year' WHERE `id` = '$user_id'");
			   }
			   else
			       Core::msg_show('Не правильно введена дата рождения');
		    }

		   # Icq
		   if ($user['ank_icq'] != $_POST['icq'])
		   {
		       if (is_numeric($_POST['icq']))
			   {
		           $icq = intval($_POST['icq']);
		           $sql->query("UPDATE `user` SET `ank_icq` = '$icq' WHERE `id` = '$user_id'");
			   }
			   else
			       Core::msg_show('Не верный формат icq');
		   }

		   # E-mail
		   if ($user['ank_mail'] != $_POST['email'])
		   {
		       if (preg_match('#^[A-z0-9-\._]+@[A-z0-9]{2,}\.[A-z]{2,4}$#ui',$_POST['email']))
			   {
		           $user['ank_mail'] = my_esc($_POST['email']);
                    
					if ($sql->query("select count(*) from `user` where `ank_mail` = '".$user['ank_mail']."' and `id` <> ".$user_id)->result())
						Core::msg_show('Данный e-mail занят');
                    else
						$sql->query("UPDATE `user` SET `ank_mail` = '".$user['ank_mail']."' WHERE `id` = ".$user_id);
			   }
			   else
			       Core::msg_show('Не верный формат E-mail');
		   }

		   # Номер телефона
		   if ($user['ank_n_tel'] != $_POST['num_phone'])
		   {
		       if (is_numeric($_POST['num_phone']) && strlen($_POST['num_phone']) >= 5 && strlen($_POST['num_phone']) <= 11)
			   {
		            $user['num_phone'] = intval($_POST['num_phone']);

					if ($sql->query("select count(*) from `user` where `ank_n_tel` = '".$user['num_phone']."' and `id` <> ".$user_id)->result())
                        Core::msg_show('Данный номер телефона занят');
                    else
						$sql->query("UPDATE `user` SET `ank_n_tel` = '".$user['num_phone']."' WHERE `id` = ".$user_id);
			   }
			   else
			       Core::msg_show('Не верный формат номера телефона');
		   }

		   # О себе
		   if ($user['ank_o_sebe'] != $_POST['about'])
		   {
		       $user['about'] = my_esc($_POST['about'], true);
		       $sql->query("UPDATE `user` SET `ank_o_sebe` = '".$user['about']."' WHERE `id` = ".$user_id);
		   }
			
            $user = Core::get_user($user_id, true);
			if (file_exists(tmpDir . 'user[id='.$user_id.'].swc'))
				unlink(tmpDir . 'user[id='.$user_id.'].swc');
			//Core::get('cache.class');
			//Cache::multi_delete('user[id='.$user_id);

		    Core::msg_show('Данные успешно изменены<br /><div class="menu"><a href="anketa.php">Редактировать еще</a><br /><a href="user.php?id='.$user_id.'">Посмотреть анкету</a></div>','msg');
            include H.'engine/includes/foot.php';
	}


	?>
	    <form  method="POST" enctype="multipart/form-data">
       
        Аватар:<br />
        <?=Core::user_avatar()?><br />
        <input type="file" name="avatar"/><br />

	    Имя:<br />
	    <input type="text" name="name" value="<?=$user['ank_name']?>" /><br />
	   
	   Пол:<br />
	   <select name="sex">
	    <option value="1">Мужской</option>
		<option value="0" <?=$user['pol'] == 0 ? 'selected="selected"': ''?>>Женский</option>
	   </select><br />
	   
  	   Город:<br />
	   <input type="text" name="city" value="<?=$user['ank_city']?>" /><br />
	   
	   Дата рождения <small>(день/месяц/год)</small>:<br />
	   <input type="text" name="day" size="1" value="<?=$user['ank_d_r']?>" />&nbsp;.&nbsp;<input type="text" name="month" size="1" value="<?=$user['ank_m_r']?>" />&nbsp;.&nbsp;<input type="text" name="year" size="1" value="<?=$user['ank_g_r']?>" /><br />
	   
	   Icq:<br />
	   <input type="text" name="icq" value="<?=$user['ank_icq']?>" /><br />
	   
	   E-mail:<br />
	   <input type="text" name="email" value="<?=$user['ank_mail']?>" /><br />
	   
	   Номер телефона:<br />
	   <input type="text" name="num_phone" value="<?=$user['ank_n_tel']?>" /><br />
	   
	   О себе:<br />
	   <textarea name="about"><?=$user['ank_o_sebe']?></textarea><br />
	   
	   <input type="submit" name="edit" value="Изменить" />
	  </form>
		<a href="user.php"><div class="link">Анкета</div></a>
		<a href="menu.php"><div class="link">Кабинет</div></a>
		<a href="/"><div class="link">Главная</div></a>
	<?
	include H.'engine/includes/foot.php';