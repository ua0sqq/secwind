<?php
    
   /**
	* @author Tadochi <Tadochi@spaces.ru>
	* Основа из dcms 
	*/

    Class Page
	{
		public
            $k_post,
            $start,
            $k_page,
            $l_post,
		    $page=1;

        function __construct($k_post,$l_post)
		{
		    $this->k_post = $k_post;
		
		    $this->l_post = $l_post;
		}
        /*
        function __get($var)
        {
            return $this->$var();
        }
        */

        function limit()
        {
            return $this->start() . ', '. $this->l_post;
        }
		
		public function pager()
		{
		   $this->page = 1;

			if (isset($_GET['page']))
			{
		        if ($_GET['page'] == 'end')
				{
				    $this->page = intval(self::k_page());
				}

				elseif (is_numeric($_GET['page']))
				{
				    $this->page = intval($_GET['page']);
				}
			}
			
			if ($this->page < 1)
                $this->page = 1;
			
			return $this->page;
		}
		
		public function start()
		{
		    return $this->start = $this->l_post * self::pager() - $this->l_post; 
		}
		
		public function k_page()
		{
			if ($this->k_post)
			{
		        return $this->k_page = ceil($this->k_post/$this->l_post);
		    }
			return $this->k_post;
		}
		
		
		
		public function display($link = '?')
		{
	        if ($this->k_page() > 1)
            {
            //if ($page<1)$page=1;
			echo '<div class="post">';
			
			if ($this->pager() > 1)
			{
			    echo '<a class="page" href="'.$link.'page=1">&lt;&lt;</a> <a class="page" href="'.$link.'page='.($this->pager() - 1).'">&lt;</a>';
			}
			
			if ($this->pager() < $this->k_page())
			    echo '<a class="page" href="'.$link.'page='.($this->pager() +1).'" title="Следующая страница - '.($this->pager() +1).')">&gt;</a>';
			
			if ($this->pager() != $this->k_page())
			    echo '<a class="page" href="'.$link.'page=end" title="Последняя страница">&gt;&gt;</a>';
			
			echo '<br />';
			
			if ($this->pager() !=1)
			    echo '<a class="page" href="'.$link.'page=1" title="Страница - 1">1</a>';
			else 
		        echo '<span class="page_no">1</span>';
			
			for ($ot=-3; $ot<=3; $ot++)
			{
			    if ($this->pager() + $ot >1 && $this->pager() + $ot < $this->k_page())
				{
				    if ($ot == -3 && $this->pager() + $ot > 2)
					    echo '..';
					
					if ($ot)
					    echo ' <a class="page" href="'.$link.'page='.($this->pager() + $ot).'" title="Страница - '.($this->pager() + $ot).'">'.($this->pager() + $ot).'</a> ';
					else
					    echo '<span class="page_no">'.($this->pager() + $ot).'</span>';
					
					if ($ot==3 && $this->pager() + $ot < $this->k_page - 1)
					    echo '..';
				}
			}
			
			if ($this->pager() != $this->k_page())
			    echo ' <a class="page" href="'.$link.'page=end" title="Страница - '.$this->k_page().'">'.$this->k_page().'</a>';
			elseif ($this->k_page() > 1)
			    echo '<span class="page_no">'.$this->k_page().'</span>';
			
			echo '</div>';
            }
		}
	}