<?php

    Class text
    {
        static function output($str, $set = array('html' => true, 'bbcode' => true, 'smiles' => true, 'br' => true))
        {
            if ($set['br'])
            {
                $str = nl2br($str);
            }
            
            if ($set['html'])
                $str = htmlspecialchars($str);

            if ($set['bbcode'])
            {
                //$tmp_str = $str;
                $str = self::bbcode($str);
            }

            if ($set['smiles'])// && $tmp_str == $str)
                $str = self::smiles($str);

           /**
            * Антиспам. Разрешается использовать только в SecWind
            */

            if (file_exists(H . 'engine/files/data/antispam.db'))
            {
                $antispam = unserialize(file_get_contents(H . 'engine/files/data/antispam.db'));
                $str = str_replace(array_keys($antispam), array_values($antispam), $str);
            }

            return $str;
        }

        static function size_data($size = 0)
        {
            $size_ed = 'б';
            if ($size >= 1024)
            {
                $size = round($size / 1024, 2);
                $size_ed = 'Кб';
            }
            
            if ($size >= 1024)
            {
                $size = round($size / 1024, 2);
                $size_ed = 'Мб';
            }

            if ($size >= 1024)
            {
                $size = round($size / 1024, 2);
                $size_ed = 'Гб';
            }
            
            return $size . ' ' . $size_ed;
        }

        static function smiles($msg)
		{
			static $cache = array();
			if (empty($cache))
			{
				global $sql;
				$query = mysqli_query($sql->db, 'SELECT * FROM `smiles` WHERE `type` = "smile"');
				if ($sql->num_rows())
				{
					while($smiles = $sql->fetch($query)){
					$cache[$smiles['symbol']] = '<img src="/style/smiles/'.$smiles['name'].'.gif"/>';
					}
				}
			}
			return strtr($msg, $cache);
		}

        static function passgen($len = 12)
        {
            $password = '';
            $small = 'abcdefghijklmnopqrstuvwxyz';
            $large = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $numbers = '1234567890';
            for ($i = 0; $i < $len; $i++)
            {
                switch (mt_rand(1, 3))
                {
                    case 3 :
                        $password .= $large [mt_rand(0, 25)];
                            break;
                    case 2 :
                        $password .= $small [mt_rand(0, 25)];
                            break;
                    case 1 :
                        $password .= $numbers [mt_rand(0, 9)];
                            break;
                }
            }
            return $password;
        }

        static function antimat($str, $user)
        {
            include_once H.'engine/functions/censure.php';
            if ($censure = censure($str))
            {
                return $censure;
            }
            else
                return false;
        }

		static function bbcode($text)
		{
			$search = array(
            '#\[b](.+?)\[/b]#i',
            '#\[i](.+?)\[/i]#i',
            '#\[u](.+?)\[/u]#i',
            '#\[del](.+?)\[/del]#i',
            '#\[color=(green|lime|red|blue|yellow|purple|gold|black|silver|gray|white)\](.+?)\[/color]#i',
            '#\[quote](.+?)\[quote]#i');

			$replace = array(
            '<span style="font-weight: bold">$1</span>',
            '<span style="font-style:italic">$1</span>',
            '<span style="text-decoration:underline">$1</span>',
            '<span style="text-decoration:line-through">$1</span>',
            '<span style="color:$1">$2</span>',
            '<div class="quote">$2</div>');

			$text = preg_replace($search, $replace, $text);
			$text = preg_replace_callback('#\[url=([-a-z0-9._~:\/?\#@!$&\'()*+,;=%]+)](.+?)\[\/url]#i', 
				create_function('$match', 'return "<a href=\'$match[1]\' title=\'".htmlspecialchars(strip_tags($match[2]))."\'>".htmlspecialchars(strip_tags($match[2]))."</a>";'), $text);

			// [img=img link]title[/img]
			//$text = preg_replace('/\[img=([-a-z0-9._~:\/?#@!$&\'()*+,;=%]+)](.+?)\[\/img\]/si', '<img src="\1" alt="\2" title="\2" />', $text);

			return $text; preg_replace($search, $replace, $text);
		}
    }