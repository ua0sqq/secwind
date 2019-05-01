<?php
    
   /**
	 * @author Patsifist
     */


   /**
	 * @const START для измерения генерации
     * @const H объявляем корень
     */

    define('START', microtime(1));
	define('H', $_SERVER['DOCUMENT_ROOT'].'/');
	define('CPU', memory_get_usage());
	define('incDir', H.'engine/includes/');
    define('tmpDir', H.'engine/files/tmp/');
	define('MAIN', $_SERVER['PHP_SELF'] == '/index.php');


	if (is_file(H.'engine/files/data/settings.ini'))
	{
    	$set = parse_ini_file(H.'engine/files/data/settings.ini');
	}
	else
	    exit('<a href="/install/">Установить</a>');


	if (is_file(H . 'engine/files/data/flood_config.swi'))
	{
		Core::get('floodblocker', 'classes');
		$flood = new FloodBlocker;
		if (!$flood->CheckFlood())
		{
			die('Слишком много запросов. Попробуйте позже');
		}
		unset($flood);
	}


    /**
	 * @var $time время в timestamp
     */

    $time = $_SERVER['REQUEST_TIME'];


   /**
	 * @var $id , $act чтобы 100 раз не проверять
     */

    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
	$act = isset($_GET['act']) ? $_GET['act'] : false;
	$cur_page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
	$sql = include H.'engine/classes/mysqli.class.php';
	
    
	/**
	* Open Graph. Подробнее тут http://help.yandex.ru/webmaster/video/open-graph-markup.xml
	*/
	
	$meta_og = array(
		'site_name' => 'SecWind',
		'url' => htmlspecialchars('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']),
		'image' => 'http://' . $_SERVER['SERVER_NAME'] . '/style/sw.png', 
		'locale' => 'ru_RU',
		'title' => $set['title'],
		'description' => $set['meta_description']);
	

	session_name('Secwind');
	session_start();

	//if (is_callable('ini_set')) {
	@set_time_limit(6);
	@ini_set('date.timezone', 'Europe/Moscow');
	@ini_set('html_errors', 1);

    set_error_handler('error_catch');
    spl_autoload_register('autoloader');
    
    function autoloader($class)
    {
        if (is_file(H . 'engine/classes/'. strtolower($class) . '.class.php'))
        {
            include H . 'engine/classes/' . strtolower($class) . '.class.php';
        }
        elseif (is_file(H . 'engine/classes/'. $class . '.class.php'))
        {
            include H . 'engine/classes/' . $class . '.class.php';
        }
        elseif (is_file(H . 'engine/classes/'. strtolower($class) . '.php'))
        {
            include H . 'engine/classes/' . strtolower($class) . '.php';
        }
        elseif (is_file(H . 'engine/classes/'. $class . '.php'))
        {
            include H . 'engine/classes/' . $class . '.php';
        }
        elseif ($class == 'Pclzip')
        {
            include H . 'engine/classes/zip.php';
        }
    }

	/**
     * @class Core эдакий контейнер функций
	 */

	Class Core
	{
        static $user_id = 0;

        function __construct()
        { 
            self::$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // self::$user_id = isset($_SESSION['user_id']);
        }


        /**
		 * Кеширование данных пользователей
		 * @param integer $id id юзера, данные которого нужно получить
		 * @param boolen $update если указать true, данные обновятся
		 * @return array 
		 */

		static function get_user($id, $update = false)
		{
		    if (
			        $update || 
				    !file_exists(H.'engine/files/users/cache/'.$id.'.db') || 
				    filemtime(H.'engine/files/users/cache/'.$id.'.db') > ($_SERVER['REQUEST_TIME'] + 3600)
				)
			{
			    global $sql;
				$user = $sql->query('select * from `user` where `id` = "' . $id . '" limit 1 ')->fetch();
				
				if (!$user)
				    return
                        array('id' => 0, 'nick' => 'Гость', 'pol' => 1);
					
				file_put_contents(H.'engine/files/users/cache/'.$id.'.db', serialize($user));
				unset($sql, $user);
			}
			return
			    unserialize(file_get_contents(H.'engine/files/users/cache/'.$id.'.db'));
		}


		/**
		 * Перенаправление
		 * @param string $stop
		 */
		
		static function stop($page = '/')
		{
		    header('location: '.$page); 
            exit;
		}


        /**
         * Сохранение настроек
         */

        static function save_settings($set, $file = 'engine/files/data/settings.ini')
	    {
            include_once H.'engine/classes/ini.class.php';
            $ini = new ini(H . $file);
		    foreach($set as $key => $value)
            {
                $ini->write($key, '"'.$value.'"');
            }
            return $ini->updateFile();
	    }
		
		/**
		 * Вывод даты
		 * @param integer $time
		 */
		
		static function time($time)
		{
		    $year = date('Y', $time);
			return strtr(date('H:i, j F', $time), array (
				"January" => "января",
				"February" => "февраля",
				"March" => "марта",
				"April" => "апреля",
				"May" => "мая",
				"June" => "июня",
				"July" => "июля",
				"August" => "августа",
				"September" => "сентября",
				"October" => "октября",
				"November" => "ноября",
				"December" => "декабря")) .
				($year < date('Y') ? ' ' . $year . ' года' : null);
		}
		
		/**
		 * Рекурсивное подключение файлов
		 * @param mixed $file принимает массив или строку
         * @param $path (string) папка файла
		 */
		
		static function get($file, $path = 'classes')
		{
	        if (is_array($file))
			{
			    foreach($file as $file)
				{
				    self::get($file, $path);
				}
			}
			elseif (file_exists(H.'engine/'.$path.'/'.$file.'.php'))
		    {
		        include_once H.'engine/'.$path.'/'.$file.'.php';
		    }//else die('file engine/'.$path.'/'.$file.'.php not found');
		}
		

        /**
         * Вывод сообщений. Вместо msg() и err() из Dcms 
         * @param $msg (mixed) выводимое сообщение. Может быть массивом или строкой
         * @param $div (string) класс div'а. 
         */

        static function msg_show($msg, $div = 'error')
        {
            if (is_array($msg))
            {
                foreach($msg as $msg)
                {
                    echo '<div class="'.$div.'">'.$msg.'</div>';
                }
            }
            else
                echo '<div class="'.$div.'">'.$msg.'</div>';
        }

        
        /**
         * Вывод пользователя. Сюда и пихаем код медалек
         * @param $user (array) массив юзера
         * @param $data (array) $data['post'] текст который выводится снизу. $data['status'] текст который выводится рядом с ником. $data['is_time'] должна true, если $data['status'] - timestamp
         */

        static function user_show($user, $data = array())
        {
            return
                self::user_icon($user) .
                ' <a href="/pages/user.php?id='.$user['id'].'"> ' . 
                $user['nick'] . ' </a>'. 
				(!empty($user['date_last']) && $user['date_last'] > ($_SERVER['REQUEST_TIME'] - 300) ? ' (on) ' : ' (off) ')   .
                (isset($data['status']) ? '<span class="status">(' . (isset($data['is_time']) ? Core::time($data['status']) : $data['status']) . ')</span>' : null) . 
                '<br />'. (!empty($data['post']) ? $data['post'] : null);
        }

		
		// вывод иконки юзера

        static function user_icon($user)
        {
            return '<img src="/style/users/icons/'.$user['pol'].'.png" alt=""/>';
        }


		// вывод аватара юзера

        static function user_avatar($ava_id = 0, $return = 'image')
        {
            if (!$ava_id)
                $ava_id = Core::$user_id;
            $id = file_exists(H . 'style/users/avatar/'.$ava_id.'.jpg') ? $ava_id : 0;
            return $return == 'image' ? '<img src="/style/users/avatar/'.$id.'.jpg"/>' : '/style/users/avatar/'.$id.'.jpg';
        }


		// Получение данных о SW

		static function Secwind($key = false)
		{
			$inf = unserialize(file_get_contents(H . 'engine/files/data/secwind.db'));
			return $key ? $inf[$key] : var_export($inf);
		}

		
		// Получение фильтрированных данных из $_GET

		static function Request($key)
		{
			return 
				isset($_GET[$key]) ? 
					is_array($_GET[$key]) ? 
						$_GET[$key] : 
						my_esc($_GET[$key], true) :
					false;
		}

		static function form($key)
		{
			return 
				isset($_POST[$key]) ? 
					is_array($_POST[$key]) ? 
						$_POST[$key] : 
						my_esc($_POST[$key], true) :
					false;
		}
	}


	/**
	 * функция для фильтрации строки от xss, sql inj
     * @todo удалить метод!
	 */
	
	function my_esc($str, $html = false)
	{
	    global $sql;
        if ($html)
            $str = htmlspecialchars($str);
		return mysqli_real_escape_string($sql->db, $str);
	}
	

    /**
     * Перехват ошибок
     */

    function error_catch($errno, $errstr, $errfile, $errline, $desc, $type = 'php')
    {
		if ( 0 == error_reporting ())
		{
			return;
		}
		
        global $sql, $creator;

        if ($type == 'php')
            $desc = $errstr .  ' | Файл ' . $errfile . '  | Линия - ' . $errline;

		if ($creator && $type == 'php')
			echo $desc . '<br />';

        $sql->query("
            INSERT INTO `errors`
            (`user` , `time`, `user_agent`, `ip`, `desc`, `type`, `url`) VALUES 
            ('".Core::$user_id."', '".time()."', '".my_esc($_SERVER['HTTP_USER_AGENT'], true)."', '".my_esc($_SERVER['REMOTE_ADDR'], true)."', '".my_esc($desc)."', '".$type."', '".my_esc($_SERVER['REQUEST_URI'], true)."');");
    }



  /**
	* Авторизация
	*/
	
	if (isset($_SESSION['user_id']) && $sql->query('SELECT COUNT(*) FROM `user` WHERE `id` = '.$_SESSION['user_id'].' LIMIT 1')->result() == 1)
	{
	    $user = Core::get_user($_SESSION['user_id']);
		$user_id = $user['id'];
		if (mt_rand(1, 2) == 1)
		    $sql->query('UPDATE `user` SET `date_last` = '.$time.' WHERE `id` = '.$user['id'].' LIMIT 1');
	}
	else
	    $user_id = 0;

	if (!$user_id && 
		isset($_COOKIE['user_id'], $_COOKIE['pass']) && 
		$sql->query("SELECT COUNT(*) FROM `user` WHERE `id` = ".intval($_COOKIE['user_id'])." AND `pass` = '".my_esc($_COOKIE['pass'])."' LIMIT 1")->result() == 1)
	{
	    $user = Core::get_user($_COOKIE['user_id']);
		$user_id = $_SESSION['user_id'] = $user['id'];
		$sql->query('select `file` from `module_services` where `use_in` = "auth"');
		$_SESSION['user_authed'] = 'COOKIE';
	}


   /**
     * @var $user_id (boolen) вернет true если юзер авторизован
     * @var $moder (boolen) вернет true для модератора
     * @var $admin (boolen) вернет true для админа
     * @var $creator (boolen) вернет true для создателя
     * @var $author (boolen) вернет true если юзер - автор 
     */

    $user_id = isset($_SESSION['user_id']) ? $user['id'] : 0;
    $moder = $user_id && $user['group_access'] > 1;
    $admin = $user_id && $user['group_access'] > 2;
    $creator = $user_id && $user['group_access'] > 9;
    //$author = $user_id == $id;



  /**
	* По дефолту - отключен
	* Рекурсивно фильтрует все.
	* Даже массивы в массивах :-D
	*/

	function helper_awr(&$value, $key)
    {
        $key = my_esc($value, false);
    }

	function helper_hsc(&$value, $key)
    {
        $key = htmlspecialchars($value);
    }

    if ($set['filtr_get'])
    {
        array_walk_recursive($_GET, 'helper_awr');
    }

    if ($set['filtr_post'])
    {
        array_walk_recursive($_POST, 'helper_awr');
    }

    if ($set['antixss'])
    {

	   /*
		* Защита/предупреждение XSS-атак
		*/

		if (isset($_SERVER['HTTP_REFERER']))
		{
			$ref = parse_url($_SERVER['HTTP_REFERER']);
			if (!substr_count($ref['host'], $_SERVER['HTTP_HOST']))
			{
				if ($_POST) die('<b>Achtung! XSS attack!</b>');
				if ($_GET)  die('<b>Achtung! XSS attack?></b><br />Подтвердите переход: <a href="' 
						. htmlspecialchars($_SERVER['REQUEST_URI']) . '">' 
						. htmlspecialchars($_SERVER['REQUEST_URI']) . '</a>');
			}
		}

		array_walk_recursive($_GET, 'helper_hsc');
        array_walk_recursive($_POST, 'helper_hsc');
    }



    /*
	* SecWind?
	* Вообще не торгать. 
	*/

	if (isset($_GET['you_secwind?']))
	{
		exit(Core::Secwind('version'));
	}

	if ($user_id && $_SERVER['PHP_SELF'] != '/pages/ban.php' && $sql->query('SELECT COUNT(*) FROM `ban` WHERE `user_id` = '.$user_id.' AND `time` > '.$time .' LIMIT 1')->result() == 1)
	{
		Core::stop('/pages/ban.php');
	}

	new Core;
	ob_start();