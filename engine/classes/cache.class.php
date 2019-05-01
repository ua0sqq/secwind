<?php
    
   /**
	* @author Tadochi <Tadochi@spaces.ru>
	*/

	Class Cache
    {
		private $_file, $_result;

		function __construct($file)
		{
		    $this->_file = $file;
		}

		public function life($expiry = false)
		{
			if (!$expiry)
			{
				$set = parse_ini_file(H . 'engine/files/data/settings.ini');
				$expiry = isset($set['cache_time']) ? $set['cache_time'] : 3600;
			}
	        return file_exists($this->_file) && filemtime($this->_file) > (time() - $expiry);
		}
		
		public function read()
		{
		    return empty($this->_result) ? file_get_contents($this->_file) : $this->_result;
		}

		public function write($data = false)
		{
			return file_put_contents($this->_file, $data ? $data : ob_get_clean());
		}

		public function delete()
		{
			if (file_exists($this->_file))
			    unlink($this->_file);
		}

		public static function multi_delete($needly, $dir = tmpDir)
		{
			$dir_open = opendir($dir);
			while ($file = readdir($dir_open))
			{
				if ($file == '.' || $file == '..')
					continue;
				if (strstr($file, $needly))
					unlink($dir . $file);
			}
		}

        public function replace($param1, $param2 = false)
        {
            if (empty($this->_result))
                $this->_result =  file_get_contents($this->_file);

            $this->_result = str_replace(
                (is_array($param1) ? array_keys($param1) : $param1),
                (!$param2 ? array_values($param1) : $param2),
                $this->_result);

            return $this;
        }

        public function helper($array = false)
        {
            if (!$array)
                $array = array('<a href="/login.php">Войти на сайт</a>' => '<a href="/pages/menu.php">Кабинет</a> | <a href="/login.php?exit">Выход</a>');

            $this->_replace = $this->replace(!Core::$user_id ? array_flip($array) : $array);
            
            return $this;
        }
    }