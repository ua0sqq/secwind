<?php

$access = FALSE;
$do = $do == 'edit' ? 'edit' : 'add';

if ($id === FALSE)
{
    $id = 0;
}

if ($admin)
{
    /* Открыть доступ для модераторов и администраторов */
    $access = TRUE;
}

if ($id !== 0 && $do == 'add' && $user_id)
{

    $cat = $sql->query("SELECT `mod` FROM `mod_lib` WHERE `id` = " . $id . " AND `type` = 'cat'")->fetch();

    if ($cat !== FALSE)
    {
        if ($cat['mod'] == 1)
        {
            /* Открыть доступ для пользователей (для добавления статьи) */
            $access = TRUE;
        }
    }
    else
    {
        $error = 'Категория не найдена. <br /><a href="index.php">В библиотеку</a>';
    }

}

if ($access === TRUE && $user_id && !isset($error))
{
    if ($do == 'edit')
    {
        /* Получить данные статьи для редактировоания */
        $arc = $sql->query("SELECT `name`,`announce`,`text`,`tags` FROM `mod_lib` WHERE `id` = '" . $id . "' AND `type` = 'arc'")->fetch();
        if ($arc === FALSE)
        {
            $error = 'Статья не найдена<br /><a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a>';
        }
        elseif (empty($_POST))
        {

            $title = htmlentities($arc['name'], ENT_QUOTES, 'UTF-8');
            $text = htmlentities($arc['text'], ENT_QUOTES, 'UTF-8');
            $announce = htmlentities($arc['announce'], ENT_QUOTES, 'UTF-8');
            $tags = htmlentities($arc['tags'], ENT_QUOTES, 'UTF-8');

        }

    }
    if (!empty($_POST) || $do == 'add')
    {

        $title = isset($_POST['title']) ? htmlentities($_POST['title'], ENT_QUOTES, 'UTF-8') : '';
        $announce = isset($_POST['announce']) ? htmlentities($_POST['announce'], ENT_QUOTES, 'UTF-8') : '';
        $text = isset($_POST['text']) ? htmlentities($_POST['text'], ENT_QUOTES, 'UTF-8') : '';
        $tags = isset($_POST['tags']) ? htmlentities($_POST['tags'], ENT_QUOTES, 'UTF-8') : '';

    }

    if (!isset($error))
    {

        /* Форма добавления|редактирования статьи */
        echo '<div class="fmenu">' . 'Библиотека' . ' | ' . ($do == 'add' ? 'Добавить статью' : 'Редактировать статью') . '</div>' .
             '<div class="post">Обязательные поля помечены звездочкой<span class="red">*</span>.</div>' .
             '<form action="?act=articles&amp;mod=save&amp;id=' . $id . '&amp;do=' . $do . '" method="post" name="form" enctype="multipart/form-data">' .
             '<div class="msg">' .
             /* Title */
             '<p><span class="red">*</span>&#160;<b>Заголовок: </b> (1 - 120)<br /><input type="text" name="title" value="' . $title . '" /></p>' .
             /* Announce */
             '<p><span class="red">*</span>&#160;<b>Анонс:</b> (1 - 200)<br /><input type="text" name="announce" value="' . $announce . '" /></p>' .
             /* Text */
             '<p><span class="red">*</span>&#160;<b>Текст</b>:<br />' .
             '<textarea name="text" rows="10">' . $text . '</textarea></p>' .
             /* Tags */
             '<p><b>Метки:</b> (1-200)&#160;через запятую<br /><input type="text" name="tags" value="' . $tags . '" /></p>' .
             /* Submit */
             '<p><input type="submit" name="submit" value="' . ($do == 'add' ? 'Добавить' : 'Сохранить') . '" /></p></div>';

        /* Список прикрепленных файлов */

        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();

        if ($do == 'edit')
        {

            $sql->query("SELECT `name` FROM `mod_lib_files` WHERE `aid` = '" . $id . "'");
            while($temp = $sql->fetch())
            {

                $ext = explode('.', $temp['name']);
                $files[$temp['name']] = $ext[1];

            }

        }

        if (!empty($files))
        {

            echo '<div class="msg"><p><b>Прикрепленные файлы:</b>';

            foreach ($files as $name => $type)
            {

                $name = htmlentities($name, ENT_QUOTES, 'UTF-8');

                echo '<div style="padding: 4px 0px 2px 0px">';
                echo '<a href="files/attach/' . $name . '">' . $name . '</a>';
                echo '&#160;<input type="submit" name="delfile_' . $name . '" value="Удалить" />';
                echo isImage($type) ? '<br /><input style="margin-top: 4px;" type="text" value="[img]' . $name .
                     '[/img]" />' : '';
                echo '</div>';

            }

            echo '</p></div>';

        }

        /* Attach file */
        echo '<div class="post"><p><b>Прикрепить файл:</b><br /><input type="file" name="file" /></p>' .
             '<input type="submit" name="addfile" value="Прикрепить" /></div></form>' .
             '<div class="fmenu">' .
             ($do == 'add'
              ? '<a href="?act=category&amp;mod=view&amp;id=' . $id . '">Назад</a>'
              : '<a href="?act=articles&amp;mod=view&amp;id=' . $id . '">Назад</a>'
             ) . '</div>';

    }

}
else
{

    $error = 'Доступ запрещен';

}