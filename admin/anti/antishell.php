<?php
    include '../../engine/includes/start.php';
    if (!$creator)
        Core::stop();
    $set['title'] = 'AntiShell | Сканер файлов';
    require incDir.'head.php';

    class file_search
    {
        public
            $key,
            $arr,
            $m = 0,
            $method,
            $kolvo = 0;

        public function file_search($key, $method)
        {
            $key = trim(str_replace('  ',' ',str_replace("\r\n",' ',$key)));
            $this->method=$method;
            if ($method == 1)
                $key=strtolower($key);
            $this->key = explode(' ',$key);
            return;
        }

        public function go_skan($dir)
        {
            $this->open_dir($dir);
            //krsort($this->arr);
            return $this->arr;
        }

        public function open_dir($dir)
        {
            if ($is = opendir($dir))
            {
                while (false !== ($file = readdir($is)))
                {
                    if ($file!='.' && $file!='..' )
                    {
                        if (is_file($dir.'/'.$file))
                        {
                            $size = filesize($dir . '/' . $file);
                            if ($size>=1)
                            {
                                $this->kolvo++;
                                $fopen = fopen($dir.'/'.$file,'r');
                                $list = fread($fopen,$size);
                                fclose($fopen);
                                $wiev = '';
                                $m = 0;
                                $this->m++;
                                if ($this->method==1)
                                    $list = strtolower($list);
                                foreach ($this->key as $key)
                                {
                                    if (strstr($list,trim($key)))
                                    {
                                        $m++;
                                        $wiev.='=|+|+|='.$key;
                                    }
                                }
                                if (!empty($wiev))
                                    $this->arr[$m.'.'.$this->m] = $dir.'/'.$file.$wiev;
                            }
                        }
                        else
                            $this->open_dir($dir.'/'.$file);
                    }
                }
            }
            return;
        }
    }


    if (isset($_POST['search']) && isset($_POST['dir']))
    {
        if (isset($_POST['method']))
            $method = $_POST['method'];
        else
            $method = 2;
        $class = new file_search($_POST['search'], $method);
        $wieF  = $class->go_skan($_POST['dir']);
        $kolvo = $class->kolvo;
        unset($class);

        if (is_array($wieF))
        {
            echo '<div class="post">Просканированно: '.$kolvo.' файл.<br />По ключевым словам найдено: '.count($wieF).' файлов.</div>';
            foreach($wieF as $list)
            {
                $list = explode('=|+|+|=', $list);
                $count=count($list);
                echo '<div class="post">B Файле: <a href="'.$list[0].'">'.$list[0].'</a> найдено: '.($count - 1).' совпадений согласно запросу!<br />Найдены ключевые слова:<br /><div style="background-color: #aaaaff">';
                for($i=1; $i < $count; $i++)
                {
                    echo '<span class="status">'.$list[$i].'</span>, '; 
                }
                echo  '<br /></div></div>';
            }
        }
        else
            echo 'По вашему запросу в данной директории не найдено не одного файла!<br />';
    }
    else
        ?><div class="post">Не рекомендуется проводить поиск сразу по всему серверу если у вас много файлов храниться на хосте, сканируйте каждую папку по очереди. 
                В противном случае скрипт либо не выполнится до конца либо повесит сервер. Старайтесь не применять поиск с "Без учета регистра" это увеличивает нагрузку на сервер! <br />
                Также ищите такие файлы как access.log, error_log, thumbs.db и тд и тп</div>
            <form method="post">Директория (без слэша в конце):<br />
            <input type="text" name="dir" value="../.." /><br />
            Ключевые слова (через пробел):<br />
            <textarea rows="4" cols="15" name="search">rmdir unlink chmod eval mkdir &lt;iframe&gt;</textarea><br />
            <input type="checkbox" name="method" value="1" /> Без учета регистра<br /><br />
            <input type="submit" value="Искать" /></form>
            <div class="post">Информация по функциям:<br />
            <span class="status">rmdir</span>(); - Удаляет директорию (если она пуста)<br />
            <span class="status">unlink</span>(); - Удаляет файл<br />
            <span class="status">chmod</span>(); - Выставляет/изменяет права доступа<br />
            <span class="status">eval</span>(); - Выполняет php код<br />
            <span class="status">mkdir</span>(); - Создает директорию</div>
            <a href='/admin/'><div class="menu_razd">Админка</div></a>
    <?php
    include incDir.'foot.php';