<?php
	
   /**
	* @author Tadochi aka Patsifist <Tadochi@spaces.ru>
	* @class Files 
	* @version 0.1
	* Класс для работы с файлами и папками
	*/

	Class files
	{
		public
			$is_dir, // true если папка
			//$action, // get_data, clean_dir, delete_dir, reanme
			$link; // Ссылка на объект
		public $size = 0; // Размер файла или папки
		public $files  = 0; // Количество файлов в папке
		public $dirs = 0;  // Количество папок в папке

		public function __construct($link, $action = false)
		{
			$this->link = $link;
			$this->action = $action;
			// $this->is_dir = is_dir($this->link);
			if (is_dir($link))
			{
				$this->link = $link . (substr($link, -1) != '/' ? '/' : null);
				$this->is_dir = true;
			}

			if (!is_file($link) && !$this->is_dir)
			{
				trigger_error('<b>Warning</b>: '.$link . ' not found', E_USER_ERROR);
			}

			if ($action)
			{
				$this->$action($link);
			}
		}

		public function description()
		{
			if ($this->is_dir)
				return
					'Информация о папке ' . basename($this->link) . ' (' . $this->link . ')' . 
					'<br />Размер в байтах: ' . $this->size . 
					'<br />Папок: ' . $this->dirs . '<br />
					Файлов: ' . $this->files ;
			else
				return
					'Информация о файле ' . basename($this->link) . ' (' . $this->link . ')'.
					'<br />Размер в байтах: ' . filesize($this->link) . 
					'<br />Расширение: '. pathinfo($this->link, PATHINFO_EXTENSION);
		}

		public function get_data($link)
		{
			if (is_dir($link))
			{
				$dir = opendir($link);
				while($file = readdir($dir))
				{
					if ($file == '.' || $file == '..')
					{
						continue;
					}

					if (is_dir($link . $file))
					{
						$this->dirs += 1;
						$this->get_data($link . $file . '/');
					}
					else
					{
						$this->files += 1;
						$this->size += filesize($link . $file);
					}
				}
			}
		}
	}