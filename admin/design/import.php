<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();

	$set['title'] = 'Импорт дизайна';
    include incDir.'head.php';

	empty($_POST['url']) && !empty($_GET['url']) ? $_POST['url'] = $_GET['url'] : null;

    if (!empty($_POST['url']))
    {
		if ($_POST['url'] == 'http://')
		{
			$error = 'Не введен адрес';
		}
		else
		{
			$headers = @get_headers($_POST['url'], 1);

			if (!$headers)
				$error = 'Не удалось получить заголовки по указанному адресу';
		
			elseif (empty($_GET['url']) && !empty($headers['Content-Length']) && $headers['Content-Length'] >= 2097152)
				$error = 'Размер архива превышает 2 мб, <a href="?url='.htmlspecialchars($_POST['url']).'">продолжить?</a>';

			elseif ($headers['Content-Type'] != 'application/zip')
				$error = 'Только zip архивы';

			else
			{
				$design = pathinfo($_POST['url'], PATHINFO_FILENAME);

				if (is_dir(H . 'style/themes/' . $design))
					$error = 'Такая тема уже установлена';
				else
				{
					file_put_contents(tmpDir . $design, file_get_contents($_POST['url']));
					Core::get('zip');
					$zip = new Pclzip(tmpDir . $design);
					$contents = $zip->listContent();
            
					if ($contents == 0)
						$error = 'Архив поврежден или пуст';
					elseif (!array_key_exists('theme.ini', $contents))
						$error = 'theme.ini не найден';

					if (!isset($error))
					{
						$dir_install = H . 'style/themes/'.$design;
						if (is_dir($dir_install) || mkdir($dir_install))
							$zip->extract(PCLZIP_OPT_SUBSTITUE_FILE, $dir_install);
						unlink(tmpDir . $design);
						Core::msg_show('Тема установлена', 'menu_razd');
					}
					unset($zip, $config, $headers);
				}
			}
		}
		if (isset($error))
			Core::msg_show($error);
	}

    ?>
    <form method = "post">
        Путь к zip архиву:<br />
        <input type="text" name="url" value="http://"/><br />
        <input value = "Импорт" type="submit" />
    </form>
	<a href='/admin/?act=design'><div class="link">Дизайн</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?php
    include incDir.'foot.php';