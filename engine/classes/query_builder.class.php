<?php
	
   /**
	* @author Tadochi aka Patsifist <Tadochi@spaces.ru>
	* @version 0.1
	* Класс Query builder. Наверное и так понятно для чего она
	* Пример / Example
	* Core::get('query_builder');
	* $builder = new Query_builder;
	* $builder->
	*	   select('id', 'nick')->
	*      from('user')->
	*      where('id', '=', '1')->
	*      or_where('id', '=', '2')->
	*      and_where('nick', 'in', '1,2,3,4,5');
	* Чтобы сделать запрос
	* $sql->query($builder->query);
	*/


	Class Query_builder
	{

	   /**
		* $var $operation string Содержит название операции
		*/

		public $operation;

		
	   /**
		* $var operands array все допустимые операнды
		*/
		
		public $operands = array('=', '>', '<', '<>', '!=', '<=', '>=', 'LIKE', 'IN', '<>');

	   /**
		* $var query содержит сам запрос
		*/

		public $query;



	   /**
		* Хелпер для установки типа операции
		*/

		public function operation($operation)
		{
			if (empty($this->operation))
				$this->operation = $operation;
			else
				exit('Тип операции уже определен');
		}


	   /**
		* Хелпер - конструктор строки с полями
		*/

		public function fields_maker(array $fields, $set = false)
		{
			$i = 0;
			$count = count($fields);
			$string = null;

			if ($set)
			{
				foreach ($fields as $field => $value)
				{
					$string .= '`' . $field . "` = '".$value."'" . (++$i != $count ? ', ' : null);
				}
			}
			else
			{
				foreach ($fields as $field)
				{
					$string .= '`' . $field . '`' . (++$i != $count ? ', ' : null);
				}
			}
			//$this->query = ' SET '.$string;
			return  $string;
		}

	   /*
		* Хелпер констуктор условий
		*/

		public function cond_maker($cond1, $operand, $cond2)
		{
			if (in_array($operand, $this->operands))
			{
				if ($operand == 'IN') 
					return "`".$cond1."` in('".$cond2."') ";
				else
					return "`".$cond1."` ".$operand." '".$cond2."' ";
			}
			else
				exit('Неизвестный операнд '. $operand . PHP_EOL . '<br />Список операндов: '. implode(',', $this->operands));
		}


	   /**
		* Хелпер для сброса запроса
		*/

		public function reset()
		{
			$this->query = $this->operation = null;
			return $this;
		}

		// Operations

		public function select($fields)
		{
			$this->operation('SELECT');
			$args = func_get_args();
			$this->query = 'SELECT ' . (is_array($args[0]) ? $this->fields_maker($args[0]) : $this->fields_maker($args)) . ' ';
			return $this;
		}

		public function delete($table)
		{
			$this->operation('DELETE');
			$this->query = 'DELETE FROM `'.$table.'` ';
			return $this;
		}

		public function update($table)
		{
			$this->operation('UPDATE');
			$this->query = 'UPDATE `'.$table.'` ';
			return $this;
		}

		public function insert($table)
		{
			$this->operation('INSERT');
			$this->query = 'INSERT INTO `'.$table.'` ';
			return $this;
		}



		
		public function from($table)
		{
			strtoupper($this->operation) == 'SELECT' ? $this->query .= ' FROM `' . $table . '` ' : null;
			return $this;
		}

		public function order($field, $type)
		{
			if (strtoupper($this->operation) == 'SELECT')
				$this->query .= 'ORDER BY `'.$field.'` ' . (strtoupper($type) == 'DESC' ? 'DESC' : 'ASC') . ' ';
			return $this;
		}

		public function limit($limit)
		{
			$this->query .= $this->operation != 'INSERT' ? 'LIMIT ' . $limit : null;
			return $this;
		}
		
		public function set($args)
		{
			$set = $this->operation == 'INSERT' || $this->operation = 'UPDATE';
			$this->query .= ' SET ' . $this->fields_maker($args, $set);
			return $this;
		}

		public function where($cond1, $operand, $cond2)
		{
			$this->query .= $this->operation != 'INSERT' ? 'WHERE ' . $this->cond_maker($cond1, strtoupper($operand), $cond2) : null;
			return $this;
		}

		public function or_where($cond1, $operand, $cond2)
		{
			$this->query .= $this->operation != 'INSERT' ? 'OR ' . $this->cond_maker($cond1, strtoupper($operand), $cond2) : null;
			return $this;
		}

		public function and_where($cond1, $operand, $cond2)
		{
			$this->query .= $this->operation != 'INSERT' ? 'AND ' . $this->cond_maker($cond1, strtoupper($operand), $cond2) : null;
			return $this;
		}
	}