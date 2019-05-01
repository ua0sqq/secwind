<?php
    include '../engine/includes/start.php';

    if (!$creator)
        Core::stop();

    $set['title']='Админка';

    include incDir.'head.php';

	if (empty($_SESSION['entry_admin']))
	{
		$_SESSION['entry_admin'] = 1;
		$query = mysqli_query($sql->db, 'select `file` from `module_services` where `use_in`="enrty_admin"');
		while($file = $sql->result($query))
		{
			include_once H . $file;
		}
	}

    ?>
	<!-- Прошу прощения за следующие html,css - быдлокоды, я далеко не знаток html, css. Буду благодарен за подсказки на счет упрощения кода :) -->
    <style>
		.sprite {
		background: url(sprite.png) no-repeat top left;
		}
		#sprite-about{ background-position: 0 0; width: 48px; height: 48px; } 
		#sprite-database{ background-position: 0 -98px; width: 48px; height: 48px; } 
		#sprite-design{ background-position: 0 -196px; width: 48px; height: 48px; } 
		#sprite-moduls{ background-position: 0 -294px; width: 48px; height: 48px; } 
		#sprite-mysql{ background-position: 0 -392px; width: 48px; height: 48px; } 
		#sprite-security{ background-position: 0 -490px; width: 48px; height: 48px; } 
		#sprite-server{ background-position: 0 -588px; width: 48px; height: 48px; } 
		#sprite-users{ background-position: 0 -686px; width: 48px; height: 48px; } 
        .status
        {
        font-size:smaller;
        }
    </style>
    <?php

    switch($act)
    {
        default:
            ?>
            <table width="100%">

			<tr class="post">
            <td width="10%">
                <div class="sprite" id="sprite-moduls"></div>
            </td>
            <td>
                <a href="?act=modules">Расширения<br /><span class="status"> &nbsp; Расширьте функционал сайта добавив модули</span></a>
            </td>
            </tr>

			<tr class="post">
            <td width="10%">
               <div class="sprite" id="sprite-design"></div>
            </td>
            <td>
                <a href="?act=design">Персонализация<br /><span class="status"> &nbsp; Настройте сайт на свой вкус меняя темы и главное меню</span></a>
            </td>
            </tr>

            <tr class="post">
            <td width="10%">
                <div class="sprite" id="sprite-server"></div>
            </td>
            <td>
                <a href="?act=server">Сайт<br /><span class="status"> &nbsp; Информация о сайте, настройки сайта, реклама</span></a>
            </td>
            </tr>

            <tr class="post">
            <td width="10%">
                <div class="sprite" id="sprite-users"></div>
            </td>
            <td>
                <a href="?act=users">Пользователи<br /><span class="status"> &nbsp; Редактирование, удаление юзера, подозрительные юзеры</span></a>
            </td>
            </tr>

            <tr class="post">
            <td width="10%">
                <div class="sprite" id="sprite-security"></div>
            </td>
            <td>
                <a href="?act=security">Безопасность<br /><span class="status"> &nbsp; Бекап бд и файлов, ошибки сайта</span></a>
            </td>
            </tr>

			<tr class="post">
            <td width="10%">
                <div class="sprite" id="sprite-mysql"></div>
            </td>
            <td>
                <a href="?act=mysql">MySQL менеджер<br /><span class="status"> &nbsp; Работа с базой данных без <abbr title="PHPMyAdmin">PMA</abbr> прямо через админку</span></a>
            </td>
            </tr>

            <tr class="post">
            <td width="10%">
                <div class="sprite" id="sprite-about"></div>
            </td>
            <td>
                <a href="?act=about">SecWind<br /><span class="status"> &nbsp; О движке, и прочяя информация админу</span></a>
            </td>
            </tr>

            </table>
            <a href='panel.php' class="link">Экспресс панель</a>
			<a href='/pages/menu.php' class="link">Кабинет</a>
			<a href='/' class="link">Главная</a>
            <?php
        break;

		case 'ModMas':
		case 'modules':
            ?>
			<table class="post" width="100%">
                <tr><td><div class="sprite" id="sprite-moduls"></div></td><td><span style="status">
				Утилита "ModMas" позволяет с легкостью устанавливать, удалять, и проверять на обновление ваши модули</span></td></tr>
            </table>
			<table width="100%">

			<tr><td><div class="link"><a href='modules/upload.php'>Загрузка</a></div></td>
            <td><div class="link"><a href='modules/import.php'>Импорт</a></div></td></tr>

            <tr><td><div class="link"><a href='modules/install.php'>Установка</a></div></td>
			<td><div class="link"><a href='modules/list.php'>Установленные</a></div></td></tr><tr>

            <td><div class="link"><a href='modules/services.php'>Службы</a></div></td>
            <td><div class="link"><a href='modules/settings.php'>Настройки</a></div></td>
			
			</tr></table>
            <a href='/admin/'><div class="link">Админка</div></a>
            <?php
        break;

		case 'Desman':
		case 'design':
            ?>
			<table class="post" width="100%">
                <tr><td><div class="sprite" id="sprite-design"></div></td><td><span style="status">
				Утилита "DesMan" позволяет с легкостью устанавливать, удалять ваши дизайны</span></td></tr>
            </table>
			<table width="100%"><tr>
			<td><div class="link">
            <a href='design/upload.php'>Загрузка</a></div>
			</td>
            <td><div class="link">
			<a href='design/import.php'>Импорт</a></div>
			</td></tr><tr>
            <td>
			<a href='design/create.php'><div class="link">Создать тему</div></a>
			</td>
            <td><div class="link">
			<a href='design/main_menu.php'>Главное меню</a></div>
			</td>
			</tr></table>
            <a href='/admin/'><div class="link">Админка</div></a>
            <?php
        break;

		case 'iServer':
		case 'server':
			?>
			<table class="post" width="100%">
                <tr><td><div class="sprite" id="sprite-server"></div></td><td><span style="status">Утилита "iServer" позволяет отслеживать состояние сервера и сайта</span></td></tr>
			</table>
			<table width="100%">
            <tr><td><a href='server/information.php'><div class="link">Информация о сервере</div></a></td><td>
            <a href='server/settings.php'><div class="link">Настройки системы</div></a></td></tr>
            <tr><td><a href='server/seo_analyze.php'><div class="link">SEO анализ</div></a></td><td>
            <a href='server/status.php'><div class="link">Состояние SecWind</div></a></td></tr></table>
			<a href='server/ads.php'><div class="link">Реклама</div></a>
            <a href='/admin/'><div class="link">Админка</div></a>
			<?php
		break;

		case 'Sqzer':
		case 'users':
			?>
			<table class="post" width="100%">
                <tr><td><div class="sprite" id="sprite-users"></div></td><td><span style="status">Утилита "Sqzer" позволяет редактировать, удалять юзеров</span></td></tr>
			</table>
			<table width="100%">
            <tr><td><a href='users/edit.php'><div class="link">Редактирование</div></a></td><td>
            <a href='users/delete.php'><div class="link">Удаление</div></a></td></tr>
            <tr><td><a href='users/suspicious.php'><div class="link">Подозрительные юзеры</div></a></td><td>
            <a href='users/ban.php'><div class="link">Забаненные</div></a></td></tr></table>
            <a href='/admin/'><div class="link">Админка</div></a>
			<?php
		break;

		case 'sec[2]':
        case 'security':
            ?>
            <table class="post" width="100%">
                <tr><td><div class="sprite" id="sprite-security"></div></td><td><span style="status">
				Утилита "Sec[2]" позволяет максимально обезопасить ваш сайт, анализировать ошибки: сервера, php и mysql</span></td></tr>
            </table>
            <div class="menu_razd">Backup</div>
                <a href="backup/files.php" class="link">Бекап файлов</a>
                <a href="backup/mysql.php" class="link">Бекап базы данных</a>
            <div class="menu_razd">Ошибки сайта</div>
                <a href="errors.php?type=server"><div class="link">Ошибки сервера</div></a>
                <a href="errors.php?type=php"><div class="link">Ошибки php</div></a>
                <a href="errors.php?type=mysql"><div class="link">Ошибки MySQL</div></a>
            <div class="menu_razd">Hasl Staff</div>
                <a href="anti/antihack.php"><div class="link">AntiHack</div></a>
                <a href="anti/antishell.php"><div class="link">AntiShell</div></a>
                <a href="errors.php?type=loading"><div class="link">AntiLoading</div></a>
                <a href="anti/antispam.php"><div class="link">AntiSpam</div></a>
                <a href="anti/antitwink.php"><div class="link">AntiTwink</div></a>
                <a href="anti/antiflood.php"><div class="link">AntiFlood</div></a>
                <a href='/admin/'><div class="link">Админка</div></a>
            <?php
        break;

		case 'mysql':
            ?>
			<div class="menu_razd">Утилита "SMS" предназначена для упрощенной работы c MySQL</div>
			<table width="100%"><tr>
            <td><div class="link"><a href='mysql/query.php'>Сделать запрос</a></div></td>
            <td><div class="link"><a href='mysql/from_file.php'>Из файла</a></div></td></tr><tr>
			<td><div class="link"><a href='mysql/defrag.php'>Оптимизация</a></div></td>
            <td><div class="link"><a href='mysql/check.php'>Проверка таблиц</a></div></td>
			</tr></table>
            <a href='/admin/' class="link">Админка</a>
            <?php
        break;

        case 'about':
            ?>
			<div class="menu_razd">SecWind | Информация</div>
            <a href='about/secwind.php'><div class="link">Что такое SecWind?</div></a>
            <a href='about/agreement.php'><div class="link">Соглашение</div></a>
            <a href='openid.php'><div class="link">Тех. поддержка   [beta]</div></a>
            <a href='about/update.php'><div class="link">Обновление движка</div></a>
            <a href='about/help.php'><div class="link">Нужна Ваша помощь!</div></a>
            <a href='/admin/'><div class="link">Админка</div></a>
            <?php
        break;
    }

    include incDir.'foot.php';