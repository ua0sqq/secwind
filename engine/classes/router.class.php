<?php

   /**
	* @author Tadochi <Tadochi@spaces.ru>
	* @version 0.4 beta
	* @copyright SecWind IWC
	* Точно не понял каким должен быть "router" класс, но мне так нрав.. ))
	* Задача класса - верикация маршрута
	*/

    Class Router
    {
	   /**
		* @var (array) $query URI, разбитый на сегменты
		*
		* URI - http://ru.wikipedia.org/wiki/URI
		* То что идет после ? в адресной строке. Как я понял
		*
		* Свойство содержит результат explode('/', $uri)
		* То есть, если $uri = 'test:test1/var:text';
		* $query будет содержать:
		* array ([test] => 'test1', [var] => 'text')
		*/

		private $query;

		

	   /**
		* @var (string) $uri URI с которым будет работать
		*/

        public function __construct($uri = null)
		{
			$this->parse($uri);
        }



	   /**
		* Парсинг URI
		* Если URI примерно таков: news/id:2/page:4
		* То в $this->query будет массив с такими ключами: news, id, page. 
		* А значения соответственно: false, 2, 4
		*
		* $uri (string) описан в комментарии к  __construct
		* Если $uri = null, будет использована $_SERVER['QUERY_STRING']
		*/

		public function parse($uri)
		{
			$queries = explode('/', isset($uri) ? $uri : $_SERVER['QUERY_STRING']);
			foreach($queries as $query)
			{
				$arr = explode(':', $query);
				!empty($arr[0]) ? // Если передан такой URI: test:sometext/wt:f/// 
				(
					$this->query[$arr[0]] = !empty($arr[1]) ? $arr[1] : false
				)
				: null;
			}
			return $this;
		}


	   /**
		* @param $var (string) Для лаконичного обращения
		* $this->var то же что $this->query['var']
		*/

		public function __get($var)
		{
			return isset($this->query[$var]) ? $this->query[$var] : false;
		}



	   /**
		* Проверка сегментов
		*
		* @param $params (array) массив параметров
		* Ключ (key) - Сегмент URI
		* Значение (value) - Массив для "проверки". Подробнее ниже
		*
		*
		* Сегмент URI можно проверить следующими способами:
		*
		* require - сегмент обязателен, иначе будет "выброшена" ошибка
		*
		* numeric - сегмент должен быть числовым, иначе будет выброшена ошибка
		* В качестве значения, писать название функции, которое вызовится. Например: round, abs, intval и тд
		* По дефолту intval
		*
		* maybe_values - Сегмент должен состоять из допустимых значений, указанных через запятую.
		* Например array('maybe_values' => 'create,read,update,delete')
		* Если сегмента URI нет в допустимых значениях, будет выброшена ошибка
		*
		* call_function - Сегмент будет проверятся пользовательской функцией, при возврате false, работа класса остановится
		*/

		public function check($params)
		{
			foreach($params as $param => $options)
			{
				if (empty($this->query[$param]))
				{
					if (!empty($options['require']) && !isset($options['default']))
						trigger_error('<b>Warning</b>: Свойство Router::'.$param . ' не определено', E_USER_ERROR);
					else
						$this->query[$param] = $options['default'];
				}
				
				if (!empty($options['numeric']))
				{
					if (!is_numeric($this->query[$param]))
					{
						trigger_error('<b>Warning</b>: Свойство Router::'.$param . ' не является числом', E_USER_ERROR);
					}
					elseif (is_callable($options['numeric']))
					{
						$this->query[$param] = $options['numeric']($this->query[$param]);
					}
				}
		

				if (!empty($options['maybe_values']))
				{
					if (!in_array($this->query[$param], explode(',', $options['maybe_values'])))
					{
						trigger_error('
							<b>Warning</b>: Свойство Router::'.$param . ' имеет недопустимое значение.
							Допустимые значения: '. $options['maybe_values'], E_USER_ERROR);
					}
				}

				if (!empty($options['call_function']))
				{
					if (!call_user_func($options['call_function'], $this->query[$param]))
					{
						trigger_error('
							<b>Warning</b>: Работа приостановлена функцией '.$options['call_function'] . ', 
							при обработке Router::'. $param, E_USER_ERROR);
					}
				}
			}
		}


	   /**
		* Сброс параметров
		* Чтобы не создавать кучу объектов
		* $route = new router;
		* $route->check($array);
		* $route->reset()->parse($new_uri)->check($array)->reset();
		* Наверное выпилю!
		*/

		public function reset()
		{
			$this->query = array();
			return $this;
		}
    }