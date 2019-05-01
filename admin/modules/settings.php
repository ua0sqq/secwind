<?php
    include '../../engine/includes/start.php';
    
	if (!$creator)
		Core::stop();

    $set['title'] = 'Параметры установки модулей';
    include incDir.'head.php';
        
	$set_mod = parse_ini_file(H . 'engine/files/data/modules.ini');

    if (isset($_POST['save']))
    {
        $set_mod['from_others'] = isset($_POST['from_others']);
        $set_mod['req_un_file'] = isset($_POST['req_un_file']);
        $set_mod['check_author'] = isset($_POST['check_author']);
        $set_mod['ftp_host'] = Core::form('ftp_host');
        $set_mod['ftp_user'] = Core::form('ftp_user');
        $set_mod['ftp_pass'] = Core::form('ftp_pass');
        Core::save_settings($set_mod, 'engine/files/data/modules.ini');
        Core::msg_show('Сохранено');
    }

    ?>
    <form method = "post">
        <label><input type='checkbox' <?=(!empty($set_mod['from_others']) ? 'checked="checked"':null)?> name='from_others' value='1' /> Устанавливать модули не из официального сайта</label><br />
        <label><input type='checkbox' <?=(!empty($set_mod['req_un_file']) ? 'checked="checked"':null)?> name='req_un_file' value='1' /> Файл удаления (uninstall.php) обязателен для модулей</label><br />
        <label><input type='checkbox' <?=(!empty($set_mod['check_author']) ? 'checked="checked"':null)?> name='check_author' value='1' /> Проверять авторов модулей через Banbase.ru и Gix.su</label><br />
        <div class="menu_razd">ftp данные</div>
        Хост:<br />
        <input type="text" name="ftp_host" value="<?=(!empty($set_mod['ftp_host']) ? $set_mod['ftp_host'] : $_SERVER['SERVER_NAME'])?>"/><br />
        Пользователь:<br />
        <input type="text" name="ftp_user" value="<?=(!empty($set_mod['ftp_user']) ? $set_mod['ftp_user'] : $_SERVER['SERVER_NAME'])?>"/><br />
        Пароль:<br />
        <input type="text" name="ftp_pass" value="<?=(!empty($set_mod['ftp_pass']) ? $set_mod['ftp_pass'] :null)?>"/><br />
        <input value = "Изменить" name="save" type="submit" />
    </form>
	<a href="/admin/?act=modules"><div class="link">Модули</div></a>
    <a href='/admin/'><div class="link">Админка</div></a>
    <?php
    include incDir.'foot.php';