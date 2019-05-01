<?php
    include '../../engine/includes/start.php';

    if (!$creator)
        Core::stop();

	$set['title'] = 'Загрузка дизайна';
    include incDir.'head.php';

    if (!empty($_FILES['file']['name']))
    {
        $pathinfo = pathinfo($_FILES['file']['name']);
		if ($pathinfo['extension'] != 'zip')
            $error = 'Только zip архивы';
        else
        {
			Core::get('zip');
			$zip = new Pclzip($_FILES['file']['tmp_name']);
			$contents = $zip->listContent();
            
			if ($contents == 0)
                $error = 'Архив поврежден или пуст';
			elseif (!array_key_exists('theme.ini', $contents))
				$error = 'theme.ini не найден';

			if (!isset($error))
			{
				$dir_install = H . 'style/themes/'.$pathinfo['filename'];
				if (is_dir($dir_install) || mkdir($dir_install))
					$zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, $dir_install);
				Core::msg_show('Тема установлена', 'menu_razd');
			}
		    unset($zip, $config);
		}

		if (isset($error))
			Core::msg_show($error);
    }


    ?>
    <form method="post" enctype="multipart/form-data">
        <div class="post">Выберите дизайн. Расширение файла должно быть "<b>zip</b>" и присутстовать файл "<b>theme.ini</b>"</div>
        <input type="file" name="file"/><br />
        <input value = "Загрузить" name="save" type="submit" />
    </form>
	<a href='/admin/?act=design'><div class="link">Дизайн</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?php
    include incDir.'foot.php';