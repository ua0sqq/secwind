<?php

if ($admin)
{

    echo '<div class="fmenu">' . 'Библиотека' . ' | Сборщик мусора' .
         '</div><div class="msg">Эта утилита проверяет наличие неиспользуемых файлов и удаляет их.<br /> Советую периодически запускать его чтобы не засорять место на сервере.</div>';


    $query = $sql->query("SELECT `aid`, `name` FROM `mod_lib_files`");
    $files = array();
    $all_files = scandir(FILESDIR . 'attach');
    if ($sql->num_rows())
    {
        while ($file = $sql->fetch())
        {

            if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $file['name']))
            {
                $files[] = $file['name'];
            }

            $preview = explode('.', $file['name']);
            $ext = $preview[1];
            $preview = $preview[0] . '_preview.png';

            if (isImage($ext) && file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $preview))
            {
                $files[] = $preview;
            }

        }

    }

    $del_files = array_diff($all_files, $files);

    if (!empty($del_files))
    {

        $i = 0;
        foreach($del_files as $name)
        {

            if ($name != '.' && $name != '..')
            {

                if (file_exists(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name))
                {

                    unlink(FILESDIR . 'attach' . DIRECTORY_SEPARATOR . $name);
                    $i++;

                }


            }

        }

    }

    echo '<div class="menu">Удалено файлов: ' . $i .
         '</div><div class="fmenu"><a href="?act=panel&amp;mod=view">Назад</a></div>';

}
else
{

    $error = 'Доступ запрещен';

}