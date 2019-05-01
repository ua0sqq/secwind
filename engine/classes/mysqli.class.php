<?php

   /**
	* @author Tadochi
	*/

    class sql
	{
		public $queries = 0;
		public $db;
		public $error;
		public $result = null; // Результат последнего запроса
        public $timer;
		public $operation;
		
		function __construct($set)
		{
            $time_before = microtime(1);
            $this->db = mysqli_connect($set['mysql_host'], $set['mysql_user'], $set['mysql_pass'], $set['mysql_db_name']);
			mysqli_set_charset($this->db, 'utf8');//mysqli_query($this->db, 'SET NAMES utf8');
            $this->timer += microtime(1) - $time_before;
			return $this->db;
		}

        function __destruct()
        {
            $this->free();
			mysqli_close($this->db);
        }
		
		public function table_exists($table)
		{
			return $this->query("SHOW TABLES LIKE '$table'")->result();
		}
		
        public function free($multi = false)
        {
			if ($multi)
			{
				do
				{
					if (!mysqli_more_results($this->db))
					break;

					if ($result = mysqli_store_result($this->db))
					{
						mysqli_free_result($result);
					}
				}
				while (mysqli_next_result($this->db));
			}

            if (!empty($this->result) && !is_bool($this->result))
                mysqli_free_result($this->result);
			$this->result = null;
        }

        public function num_rows($query = false)
        {
            return mysqli_affected_rows($this->db);
        }

        public function result($query = false)
		{
			if (!$query)
                $query = $this->result;
            $result = mysqli_fetch_row($query);
			return $result[0];
		}
		
		public function fetch($query = false)
		{
			if (!$query)
                $query = $this->result;
            return mysqli_fetch_assoc($query);
		}

        public function from_file($file)
        {
            if (file_exists($file))
                return $this->multi(file_get_contents($file));
        }

        public function multi($query)
        {
            $time_before = microtime(1);
            $this->result = mysqli_multi_query($this->db, $query);
            $this->queries += substr_count($query, ';');
            $this->timer += microtime(1) - $time_before;
			$this->error = mysqli_error($this->db);
			return $this->result;
        }
		
		public function query($query, $free = false)
		{global $arr; $arr[$this->queries]=$query;
			$time_before = microtime(1);
            $result = mysqli_query($this->db, $query);
            $this->timer += microtime(1) - $time_before;
            $this->error = mysqli_error($this->db);

            if ($free)
                mysqli_free_result($result);
            else
                $this->result = $result;

            if (!empty($this->error))
            {
                mysqli_query($this->db, "
                    INSERT INTO `errors`
                    (`user`, `time`, `user_agent`, `ip`, `desc`, `type`, `url`) VALUES 
                    ('".Core::$user_id."', '".time()."', '".my_esc($_SERVER['HTTP_USER_AGENT'], true)."', '".my_esc($_SERVER['REMOTE_ADDR'], true)."', '".my_esc($this->error)."', 'mysql', '".my_esc($_SERVER['REQUEST_URI'], true)."');");
                $this->queries++;
				$this->error = null;
			}
            else
			$this->queries++;

			return $this;
		}
	}
	
	return new sql($set);