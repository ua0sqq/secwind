<?php
    
    include '../engine/includes/start.php';

    Core::get('cache.class');

    $id = !$id ? $user_id : $id;
    $cache = new cache(H . 'engine/files/tmp/user[id=' . $id . '].swc');

	if ($sql->query('SELECT COUNT(*) FROM `user` WHERE `id` = '.$id)->result() == 0)
    {
		Core::stop();
	}
    
	$ank = Core::get_user($id);
    $set['title'] = 'Личная страница '.$ank['nick'];
    
    include H.'engine/includes/head.php';

    if (!$cache->life())
    {
        ob_start();

		echo Core::user_avatar($ank['id']).'<br />';

		switch ($ank['group_access'])
		{
			case 2:
				echo '<b>Модератор</b>';
					break;
			
			case 3:
				echo '<b>Администратор</b>';
					break;
			
			case 10:
				echo '<b>Создатель</b>';
					break;
		}
		
		echo '<br />'.
			(empty($ank['ank_name'])
                   ? null : 'Имя: ' . $ank['ank_name'] . '<br />') .
               'Пол: ' . ($ank['pol'] == 1
			    ? 'Мужской' : 'Женский') . '<br />' .
			(empty($ank['ank_city'])
				? null : 'Город: '.$ank['ank_city'] . '<br />');

		if ($ank['ank_d_r'] != NULL && 
               $ank['ank_m_r'] != NULL && 
               $ank['ank_g_r'] != NULL)
			{
                   switch ($ank['ank_m_r'])
                   {
                       case 1:
                            $ank['mes'] = 'Января';
                                break;

                        case 2:
                            $ank['mes'] = 'Февраля';
                                break;

                        case 3:
                            $ank['mes'] = 'Марта';
                                break;

                        case 4:
                            $ank['mes'] = 'Апреля';
                                break;

                        case 5:
                            $ank['mes'] = 'Мая';
                                break; 

                        case 6:
                            $ank['mes'] = 'Июня';
                                break;

                        case 7:
                            $ank['mes'] = 'Июля';
                                break;

                        case 8:
                            $ank['mes'] = 'Августа';
                                break;

                        case 9:
                            $ank['mes'] = 'Сентября';
                                break;

                        case 10:
                            $ank['mes'] = 'Октября';
                                break;

                        case 11:
                            $ank['mes']='Ноября';
                                break;

                        default:
                            $ank['mes']='Декабря';
                                break;
                    }
					
					$ank['ank_age'] = date('Y') - $ank['ank_g_r'];
                    if (date('n') < $ank['ank_m_r'])
                    {
                        $ank['ank_age'] = $ank['ank_age'] - 1;
                    }
                    elseif (date('n') == $ank['ank_m_r'] && date('j') < $ank['ank_d_r'])
                    {
                        $ank['ank_age'] = $ank['ank_age'] - 1;
                    }
                    echo 'Дата рождения: ' . $ank['ank_d_r'] . ' - ' . $ank['mes'] . '  ' . $ank['ank_g_r'] . 'г.<br />' .
                            'Возраст: ' . $ank['ank_age'] .' <br />';
				}

            echo 
				(empty($ank['ank_icq'])
                    ? null: 'Icq: '. $ank['ank_icq']. '<br />' ).
				(empty($ank['ank_mail'])
                    ? null : 'E-mail: '.$ank['ank_mail']. '<br />') .
				(empty($ank['ank_n_tel'])
					? null : 'Телефон: '.$ank['ank_n_tel'].  '<br />') .
				(empty($ank['ank_o_sebe'])
					? null : 'О себе: '.$ank['ank_o_sebe']. '<br />') .
				'Баллы: '.$ank['balls'].'<br />
				Дата регистрации: '.Core::time($ank['date_reg']).'<br />
				Последнее посещение: '.Core::time($ank['date_last']).'<br />';
			$res = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in` ="anketa"');
			while($file = $sql->result($res))
			{
				include_once H . $file;
			}
        $cache->write();
	}

    echo $cache->read();

	if ($user_id && $ank['id'] != $user_id)
	{
		echo '<a href="/pages/mail.php?act=mail&amp;id='.$id.'"><div class="link">Написать</div></a>';
	}
	
    if ($user_id == $id)
    {
        echo '<a href="anketa.php"><div class="menu_razd">Редактировать</div></a>';
    }
	elseif ($moder)
	{
		if ($ank['group_access'] < $user['group_access'])
		{
			echo 
				'<a href="/admin/users/ban.php?act=ban&amp;id='.$id.'"><div class="link">Бан</div></a>'. ($admin ? 
				'<a href="/admin/users/edit.php?id='.$id.'"><div class="link">Редактировать</div></a>'.
				'<a href="/admin/users/delete.php?id='.$id.'"><div class="link">Удалить пользователя</div></a>' : null);
		}
	}

	echo '<a href="/"><div class="link">Главная</div></a>';

	include H.'engine/includes/foot.php';