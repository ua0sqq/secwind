<?php

if ($admin)
{

    echo '<div class="fmenu"><a href="index.php">' . 'Библиотека' . '</a> | <a href="?act=panel&amp;mod=view">Панель управления</a></div>';

    if (!empty($_POST))
    {

        if (isset($_POST['default']))
        {

            $settings = array(
                'main_deal' => 10, // Кол-во статей на стартовой странице (Не более 10)
                'zip_deal' => 10, // Кол-во файлов в zip архиве
                'mod_close' => 0,
                'files' => array(
                    'extensions' => array(
                        'png', 'jpg', 'bmp', 'gif',
                        'zip', 'rar', '7z', 'jar', 'tar',
                        'mp3', 'amr', 'aac', 'm4a', 'wav',
                        'mp4', 'avi', '3gp',
                        'exe', 'bin',
                        'txt', 'conf', 'log'
                    ),
                    /* Максимальное кол-во файлов для статьи */
                    'max_number' => 10
                ),
                'tags_max_cache_time' => 86400

            );

        }
        else
        {

            $err = array();
            $settings = array();

            /* Проверка параметров */

            /* Контроль доступа */
            $settings['mod_close'] = !empty($_POST['mod_close']) ? abs(intval($_POST['mod_close'])) : 0;
            if ($settings['mod_close'] < 0 || $settings['mod_close'] > 2)
                $err[] = lng('sett_mod_close_err');

            /* Кол-во статей на главной */
            $settings['main_deal'] = !empty($_POST['main_deal']) ? abs(intval($_POST['main_deal'])) : 0;
            if ($settings['main_deal'] < 2 || $settings['main_deal'] > 10)
                $err[] = lng('sett_main_deal_err');

            /* Кол-во файлов в zip */
            $settings['zip_deal'] = !empty($_POST['zip_deal']) ? abs(intval($_POST['zip_deal'])) : 0;
            if ($settings['zip_deal'] < 1 || $settings['zip_deal'] > 40)
                $err[] = lng('sett_zip_deal_err');

            /* Расширения файлов */
            if (!empty($_POST['files']['extensions']))
            {

                $settings['files']['extensions'] = $_POST['files']['extensions'];
                if (preg_match("/[^\da-z,]+/", $settings['files']['extensions']))
                    $err[] = lng('sett_files_exts_symbols');
                else
                    $settings['files']['extensions'] = explode(',', $settings['files']['extensions']);

            }
            else
                $err[] = lng('sett_files_exts_empty');

            /* Кол-во файлов для статьи */
            $settings['files']['max_number'] = !empty($_POST['files']['max_number']) ? abs(intval($_POST['files']['max_number'])) : 0;
            if ($settings['files']['max_number'] > 40)
                $err[] = lng('sett_files_number_err');

            /* Время хранения кэша списка тегов */
            $settings['tags_max_cache_time'] = !empty($_POST['tags_max_cache_time']) ? abs(intval($_POST['tags_max_cache_time'])) : 0;
            if ($settings['tags_max_cache_time'] < 1 || $settings['tags_max_cache_time']  > 100)
                $err[] = lng('sett_tags_max_cache_time_err');
            $settings['tags_max_cache_time'] = $settings['tags_max_cache_time'] * 3600;

        }

        if (empty($err))
        {

            $sql->query("UPDATE `mod_lib_set` SET `val` = '" . serialize($settings) . "' WHERE `key` = 'set'");
            $message = 'Настройки сохранены';

        }
        else
        {

            $message = implode('<br />', $err);

        }

        echo '<div class="post">Настройки ' . $message .
             '<br /><a href="?act=panel&amp;mod=settings">Продолжить</a> | <a href="index.php">В библиотеку</a></div>';

    }
    else
    {

        echo '<form action="?act=panel&amp;mod=settings" method="post">' .
             '<div class="post"><h3>Настройки' . 
             '</h3><p><input type="text" maxlength="2" name="main_deal" value="' . intval($libSet['main_deal']) . '" size="2" />&#160;' .
             'Кол-во статей на главной странице (2-10)</p>' .
             '<p><input type="text" maxlength="2" name="zip_deal" value="' . intval($libSet['zip_deal']) . '" size="2" />&#160;' .
             'Кол-во файлов в zip архиве для загрузки статей (1-40)</p><p>' .
             'Список допустимых расширений файлов для прикрепления к статьям:<br />' .
             '<textarea rows="6" name="files[extensions]">' . implode(',', $libSet['files']['extensions']) . '</textarea></p>' .
             '<p><input type="text" maxlength="2" name="files[max_number]" value="' . intval($libSet['files']['max_number']) . '" size="2" />&#160;' .
             'Максимальное кол-во прикрепленных файлов для одной статьи (0-40, 0 - Запретить прикреплять файлы)' .
             '</p><p><input type="text" maxlength="3" name="tags_max_cache_time" value="' . (intval($libSet['tags_max_cache_time']) / 3600) . '" size="2" />&#160;' .
             'Время хранения кэша списка последних меток в часах (1-100)</p>' .
             '<p>Доступ к модулю:<br />' .
             '<input type="radio" name="mod_close" value="0"' . ($libSet['mod_close'] == 0 ? ' checked="checked"' : '') .
             ' />&#160;Открыт для всех<br />' .
             '<input type="radio" name="mod_close" value="1"' . ($libSet['mod_close'] == 1 ? ' checked="checked"' : '') .
             ' />&#160;Только для зарегистрированных<br />' .
             '<input type="radio" name="mod_close" value="2"' . ($libSet['mod_close'] == 2 ? ' checked="checked"' : '') .
             ' />&#160;Закрыт для всех</p><p>' .
             '<input type="submit" name="submit" value="Сохранить" /> <input type="submit" name="default" value="Установить по умолчанию" /></p></div></form>';

    }

    echo '<div class="fmenu"><a href="?act=panel&amp;mod=view">Назад</a></div>';

}
else
{

    $error = 'Доступ запрещен';

}