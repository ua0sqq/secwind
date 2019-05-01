CREATE TABLE IF NOT EXISTS `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(150) NOT NULL,
  `name` varchar(40) NOT NULL,
  `img` varchar(40) NOT NULL,
  `pos` enum('top','bottom') NOT NULL DEFAULT 'top',
  `main` enum('0','1') NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `new_line` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `forum_favourites` (
  `topic` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `topic` (`topic`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refid` int(11) NOT NULL,
  `topic` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `type` int(2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `down` int(11) NOT NULL,
  `plus` int(11) NOT NULL,
  `minus` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `tempid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `refid` (`refid`,`topic`,`type`,`user_id`),
  KEY `tempid` (`tempid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_forums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `realid` int(11) NOT NULL,
  `refid` int(11) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `last_topic` varchar(250) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `realid` (`realid`,`refid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_journal` (
  `time` int(11) NOT NULL,
  `user_id` int(10) NOT NULL,
  `text` text NOT NULL,
  `readed` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_polled` (
  `refid` int(11) NOT NULL,
  `poll` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `refid` (`refid`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refid` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `refid` (`refid`),
  KEY `count` (`count`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user` varchar(40) NOT NULL,
  `text` text NOT NULL,
  `edit` varchar(150) NOT NULL DEFAULT '',
  `files` int(1) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `refid` (`refid`,`time`,`user_id`,`files`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_posts_del` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user` varchar(40) NOT NULL,
  `text` text NOT NULL,
  `edit` varchar(150) NOT NULL DEFAULT '',
  `files` int(1) NOT NULL,
  `browser` varchar(250) NOT NULL,
  `ip` bigint(11) NOT NULL,
  `ip_via_proxy` bigint(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `plus_minus` varchar(50) NOT NULL DEFAULT '0|0',
  PRIMARY KEY (`id`),
  KEY `refid` (`refid`,`time`,`user_id`,`files`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_posts_rating` (
  `refid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `refid` (`refid`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_readed` (
  `topic` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `lastpost` int(11) NOT NULL,
  PRIMARY KEY (`topic`,`user_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forum_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `forum` varchar(150) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user` varchar(40) NOT NULL,
  `lastpost` varchar(250) NOT NULL,
  `count` int(11) NOT NULL,
  `close` tinyint(1) NOT NULL,
  `sticky` tinyint(1) NOT NULL,
  `clip` tinyint(1) NOT NULL,
  `poll_name` varchar(250) NOT NULL,
  `poll_set` text NOT NULL,
  `curator` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `refid` (`refid`,`time`,`user_id`,`sticky`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE `guest` (
  `id` int(11) NOT NULL auto_increment,
  `id_user` int(11) NOT NULL default '0',
  `time` int(11) NOT NULL,
  `msg` varchar(1024) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `mod_lib` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`refid` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`text` mediumtext NOT NULL,
`announce` varchar(255) NOT NULL,
`tags` varchar(255) NOT NULL,
`type` varchar(3) NOT NULL,
`time` int(11) NOT NULL,
`author_id` int(11) NOT NULL,
`author_name` varchar(25) NOT NULL,
`counter` int(11) NOT NULL DEFAULT '0',
`mod` tinyint(1) NOT NULL DEFAULT '0',
`count_arc` int(11) NOT NULL,
`comm_count` int(11) NOT NULL,
`views` int(11) NOT NULL,
`uni_views` int(11) NOT NULL,
`rate_plus` int(11) NOT NULL,
`rate_minus` int(11) NOT NULL,
`edit_name` varchar(25) NOT NULL,
`edit_id` int(11) NOT NULL,
`edit_time` int(11) NOT NULL,
`down_time` int(11) NOT NULL,
PRIMARY KEY (`id`),
FULLTEXT KEY `name` (`name`),
FULLTEXT KEY `announce` (`announce`),
FULLTEXT KEY `text` (`text`),
FULLTEXT KEY `tags` (`tags`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

				CREATE TABLE `mod_lib_comments` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`sub_id` int(10) unsigned NOT NULL,
`time` int(11) NOT NULL,
`user_id` int(10) unsigned NOT NULL,
`text` text NOT NULL,
`reply` text NOT NULL,
`attributes` text NOT NULL,
PRIMARY KEY (`id`),
KEY `sub_id` (`sub_id`),
KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE `mod_lib_counters` (
`uid` varchar(32) NOT NULL,
`aid` int(11) NOT NULL,
`type` tinyint(1) NOT NULL,
PRIMARY KEY (`uid`,`aid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

			    CREATE TABLE `mod_lib_files` (
`aid` int(11) NOT NULL,
`name` varchar(30) NOT NULL,
PRIMARY KEY (`aid`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

				CREATE TABLE `mod_lib_set` (
`key` varchar(5) NOT NULL,
`val` text NOT NULL,
PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
          
                INSERT INTO `mod_lib_set` SET `val`='a:5:{s:9:"main_deal";i:10;s:8:"zip_deal";i:10;s:9:"mod_close";i:0;s:5:"files";a:2:{s:10:"extensions";a:22:{i:0;s:3:"png";i:1;s:3:"jpg";i:2;s:3:"bmp";i:3;s:3:"gif";i:4;s:3:"zip";i:5;s:3:"rar";i:6;s:2:"7z";i:7;s:3:"jar";i:8;s:3:"tar";i:9;s:3:"mp3";i:10;s:3:"amr";i:11;s:3:"aac";i:12;s:3:"m4a";i:13;s:3:"wav";i:14;s:3:"mp4";i:15;s:3:"avi";i:16;s:3:"3gp";i:17;s:3:"exe";i:18;s:3:"bin";i:19;s:3:"txt";i:20;s:4:"conf";i:21;s:3:"log";}s:10:"max_number";i:10;}s:19:"tags_max_cache_time";i:86400;}', `key` = 'set';


CREATE TABLE IF NOT EXISTS `down_comms` (
  `id` int(11) NOT NULL auto_increment,
  `refid` int(11) NOT NULL,
  `text` text NOT NULL,
  `browser` text NOT NULL,
  `time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `refid` (`refid`),
  KEY `ip` (`ip`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `down_files` (
  `id` int(11) NOT NULL auto_increment,
  `refid` int(11) NOT NULL,
  `dir` text NOT NULL,
  `time` int(11) NOT NULL,
  `name` text NOT NULL,
  `type` int(2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `rus_name` text NOT NULL,
  `text` text NOT NULL,
  `field` int(11) NOT NULL default '0',
  `rate` varchar(30) NOT NULL default '0|0',
  PRIMARY KEY  (`id`),
  KEY `refid` (`refid`),
  KEY `total` (`total`),
  KEY `type` (`type`),
  KEY `user_id` (`user_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `down_more` (
  `id` int(11) NOT NULL auto_increment,
  `refid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `name` text NOT NULL,
  `rus_name` text NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `refid` (`refid`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL auto_increment,
  `nick` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `sess` varchar(32) default NULL,
  `activation` varchar(32) default NULL,
  `ban` int(11) NOT NULL default '0',
  `ban_pr` varchar(64) default NULL,
  `ip` bigint(20) NOT NULL default '0',
  `ua` varchar(100) default NULL,
  `date_reg` int(11) NOT NULL default '0',
  `date_aut` int(11) NOT NULL default '0',
  `date_last` int(11) NOT NULL default '0',
  `balls` int(11) NOT NULL default '0',
  `rating` int(11) NOT NULL default '0',
  `group_access` int(10) unsigned NOT NULL default '0',
  `pol` enum('0','1') NOT NULL default '1',
  `url` varchar(64) NOT NULL default '/',
  `show_url` enum('0','1') NOT NULL default '0',
  `ank_g_r` int(4) default NULL,
  `ank_m_r` int(2) default NULL,
  `ank_d_r` int(2) default NULL,
  `ank_city` varchar(32) default NULL,
  `ank_o_sebe` varchar(512) default NULL,
  `ank_icq` int(9) default NULL,
  `ank_mail` varchar(32) default NULL,
  `ank_n_tel` varchar(11) default NULL,
  `ank_name` varchar(32) default NULL,
  `set_timesdvig` int(11) NOT NULL default '0',
  `set_them` varchar(32) default 'default',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nick` (`nick`),
  KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(32) default NULL,
  `time` int(11) NOT NULL,
  `msg` varchar(1024) default NULL,
  PRIMARY KEY  (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `errors`(
`desc` varchar(300) NOT NULL,
`time` int(11) not null,
`ip` varchar(20) NOT NULL,
`user_agent` text NOT NULL,
`url` text NOT NULL,
`type` enum('server', 'php', 'mysql', 'loading'),
`user` varchar(40) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `modules` (
`name` VARCHAR(30) NOT NULL,
`ru_name` VARCHAR(30) DEFAULT NULL,
`desc` TEXT DEFAULT NULL,
`version` DECIMAL(3, 2),
`uninstaller` VARCHAR(60) DEFAULT NULL,
`author_name` VARCHAR(20) NOT NULL,
`author_e-mail` VARCHAR(20) DEFAULT NULL,
`author_icq` INT(9) DEFAULT NULL,
`author_wmid` INT(12) DEFAULT NULL,
PRIMARY KEY  (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



INSERT INTO `modules` (`name`, `ru_name`, `desc`, `version`, `uninstaller`, `author_name`, `author_e-mail`, `author_icq`, `author_wmid`) VALUES
('news', 'Новости', 'Простой модуль новостей. Все на минимуме. Админ может добавлять, редактировать, удалять новости. Данные кешируются', 1.00, 'pages/news.php?act=uninstall', 'DESURE', NULL, NULL, NULL),
('gBook', 'Мини-чат', 'Мини-чат - это место где ваши пользователи могут писать разные сообщения , на разные темы', 1.00, 'pages/guestbook.php?act=uninstall', 'DESURE', NULL, NULL, NULL),
('download', 'Загруз-центр', 'Создание, удаление, редактирование, перемещение вверх/вниз катерогий, возможность задать категории описание \r\nВозможность разрешить юзерам выгружать файлы в отдельную категорию \r\nВыгрузка, импорт файлов, возможность крепить несколько скриншотов к файлу, дополнительные файлы \r\nСкачка txt файлов в zip, jar, jad. Возможность открыть zip архив и скачть оттуда отдельные файлы или просмотреть код php файлов \r\nАвтоматическое созадание скришнотов для видео, тем для нокии и СЕ \r\nВытаскивае иконок из ява приложений \r\nВозможность скачать картинку в разных размерах с возможностью сохранить пропорции \r\nЗагрузка файлов по фтп. Поиск по имени и описанию \r\nВывод тегов mp3 файлов, возможность их редактировать. Онлайн прослушка mp3 (для ПК) \r\nПросмотр новых файлов (как всех, атк и по категориям), популярных, файлов юзера. Топ юзеров \r\nСортировка файлов по времени и имени (возрастание/убывание) \r\nВозможность добавить файл в закладки (для авторизованных)\r\nКомментарии к файлам, обзов комментариев.\r\nРейтинг файлов. Возможность переносить файлы по категориям', 1.00, 'download/uninstall.php', 'Flyself', NULL, NULL, NULL),
('forum', 'Форум', '- создание разноуровневых форумов ( форум -> подфорум -> тема, форум -> тема)\r\n- назначение иконки для форума (закинуть иконку 16*16 в папку files/forum/icons/Id_форума.png)\r\n- создание голосования с мультивыбором вариантов\r\n- создавший тему пользователь всегда может изменять её первый пост\r\n- мультиприкрепление файлов к сообщению (до 10 штук), редатирование ранее прикрепленных файлов\r\n- просмотр собственной активности (темы, сообщения, файлы)\r\n- избранные темы (типа закладки)\r\n- рейтинг сообщений\r\n- преход к последнему прочтенному сообщению темы\r\n- авторедирект после основных операций на форуме в 1 сек. (работает не во всех браузерах, поэтому продублирован ссылкой)\r\n- журнал форума (оповещение об перемещении, переименовании? удалении темы, ответе на сообщение)', 1.00, 'forum/uninstall.php', 'seg0ro', NULL, NULL, NULL),
('lib', 'Библиотека', 'Категории\r\n- Создание/Редактирование/Удаление/Перемещение\r\n- Возможность перемещения и удаления сразу нескольких категорий из админ панели (удаление возможно только если категории пусты)\r\n- Неограниченная вложенность категорий\r\n- Возможно хранить в категории категории и статьи вместе\r\n- Сортировка содержимого по имени, кол-ву комментариев, рейтингу и т.п.\r\nСтатьи\r\n- Создание (есть возможность добавлять статьи пользователям, если это позволяют настройки категории, при добавлении статья отправляется на модерацию)\r\n- Загрузка статьи из txt файла\r\n- Загрузка до 40 статей сразу, из zip архива\r\n- Редактирование/Удаление\r\n- Прикрепление файлов к статьям (максимум 40 файлов)\r\n- BB код для вставки изображений\r\n- Рейтинг\r\n- Комментарии (используется класс комментариев имеющийся в дистрибутиве JohnCMS 4.4.0)\r\n- Счетчики просмотров (Уникальные / Повторяющиеся)\r\n- Разбивка текста по страницам при просмотре статьи (Есть возможность настроить кол-во строк на страницу)\r\n- Метки\r\n- Возможно перемещать и удалять сразу несколько статей из админ панели\r\n- Добавление статьи в личные закладки\r\n- Список новых статей (находится на главной странице модуля)\r\nПоиск\r\n- по заголовку/анонсу (описанию)/тексту/меткам\r\nПанель управления\r\n- Список статей находящихся на модерации\r\n- Настройки\r\n- Управление статьями и категориями (Массовое перемещение и удаление) ', 1.00, 'lib/uninstall.php', 'Screamer', NULL, NULL, NULL);


CREATE TABLE `module_services` (
`name` varchar(50) not null,
`file` varchar(100) not null,
`desc` varchar(200) default null,
`belongs` varchar(20) default null,
`use_in` varchar(20) not null comment "todo: enum"
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `module_services` (`name`, `file`, `desc`, `belongs`, `use_in`) VALUES
('Автологин', 'engine/services/avtologin.php', 'Показывает автологин', 'Система', 'reg'),
('Новости', 'engine/services/last_news.php', 'Вывод последней новости', 'news', 'index_page'),
('Мини-чат', 'engine/services/guestbook.php', 'Выводит ссылку в мини-чат. Есть счетчик всех сообщений, и сообщений за сегодняшний день', 'gBook', 'index_page'),
('Форум', 'engine/services/forum_service.php', 'Выводит ссылку в форум. Есть счетчик всех сообщений, и тем', 'forum', 'index_page'),
('Загрузки', 'engine/services/download_service.php', 'Выводит ссылку в зц. Есть счетчик всех папок и файлов', 'download', 'index_page'),
('Библиотка', 'engine/services/lib_service.php', 'Выводит ссылку в библиотеку. Есть счетчик всех категорий и статей', 'lib', 'index_page');

INSERT INTO `module_services` (`name`, `file`, `desc`, `belongs`, `use_in`)
 VALUES 
('Пользователи', 'engine/services/users_service.php', 'Вывод онлайн юзеров ', 'Система', 'index_page');

INSERT INTO `module_services` (`name`, `file`, `desc`, `belongs`, `use_in`)
 VALUES
 ('Проверка нового юзера', 'engine/services/suspicious_user.php', 'Проверка юзера. Подробнее в админка/пользователи/подозрительные пользователи', 'Система', 'reg');

CREATE TABLE `ban` (
  `id` int(11) NOT NULL auto_increment,
  `time` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `moder_id` int(11) NOT NULL,
  `prich` varchar(1024) NOT NULL,
  `view` set('1','0') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`, `moder_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `suspicious_users` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 32 ) NOT NULL ,
`text` VARCHAR( 200 ) NOT NULL ,
PRIMARY KEY (  `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE  `smiles` (
`id` INT(11) NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 20 ) NOT NULL ,
`symbol` VARCHAR( 20 ) DEFAULT NULL ,
`type` ENUM(  'kat',  'smile' ) NOT NULL DEFAULT  'kat',
`parent_id` INT(11) NOT NULL ,
PRIMARY KEY (  `id` )
) ENGINE = MYISAM DEFAULT CHARSET=utf8;

INSERT INTO `smiles` (`id`, `name`, `symbol`, `type`, `parent_id`) VALUES
(1, 'Надписи', '', 'kat', 0),
(2, 'beta', '.бета.', 'smile', 1),
(3, 'Google', '.гугл.', 'smile', 1),
(4, 'help2', '.хелп.', 'smile', 1),
(5, 'krutoy', '.якрут.', 'smile', 1),
(6, 'lol', '.лол.', 'smile', 1),
(7, 'noflood', '.нефлуди.', 'smile', 1),
(8, 'owibochka', '.упс.', 'smile', 1),
(9, 'perviy_nah', '.перв.', 'smile', 1),
(10, 'vsempr', '.всемпр.', 'smile', 1),
(11, 'smile_t3', '.дану.', 'smile', 1),
(12, 'smile_t10', '.прот.', 'smile', 1),
(13, 'smile_t11', '.яза.', 'smile', 1);

CREATE TABLE IF NOT EXISTS `mail_contacts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`id_cont` int(11) NOT NULL,
`time` int(11) NOT NULL,
`unread` int NOT NULL,
`outbox` int NOT NULL,
`inbox` int NOT NULL,
PRIMARY KEY (`id`),
KEY `user_id` (`user_id`,`id_cont`),
KEY `unread` (`unread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mail` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`author` int(10) unsigned NOT NULL COMMENT 'Получатель',
`user` int(10) unsigned NOT NULL COMMENT 'Отправитель',
`time` int(10) unsigned NOT NULL COMMENT 'Время',
`text` varchar(1024) NOT NULL COMMENT 'Письмо',
PRIMARY KEY (`id`),
KEY `author` (`author`,`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Почта' AUTO_INCREMENT=1 ;
ALTER TABLE `mail` ADD `no` INT(2) NOT NULL DEFAULT "0";

DELETE FROM `module_services` WHERE `file` = 'engine/services/avtologin.php';

CREATE TABLE IF NOT EXISTS `main_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `link` varchar(150) DEFAULT NULL,
  `icon` varchar(150) DEFAULT NULL,
  `file` varchar(150) DEFAULT NULL,
  `pos` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;


INSERT INTO `main_menu` (`id`, `name`, `link`, `icon`, `file`, `pos`) VALUES
(2, 'Новости', '/pages/news.php', '/style/icons/news.png', 'engine/services/last_news.php', 1),
(3, 'Форум', 'forum', '/style/icons/forum.png', 'engine/services/forum_service.php', 3),
(12, 'Мини-чат', 'pages/guestbook.php', '/style/icons/chat.png', 'engine/services/guestbook.php', 2),
(13, 'Загрузки', '/download/', '/style/icons/download.png', 'engine/services/download_service.php', 4),
(14, 'Библиотека', '/lib/', '/style/icons/lib.png', 'engine/services/lib_service.php', 5);


CREATE TABLE IF NOT EXISTS `speed_dial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `link` varchar(300) DEFAULT NULL,
  `icon` varchar(200) DEFAULT NULL,
  `pos` int(11) NOT NULL,
  `new_line` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;


INSERT INTO `speed_dial` (`id`, `name`, `link`, `icon`, `pos`, `new_line`) VALUES
(33, 'Установка', '/admin/modules/install.php', 'icons/cms.png', 12, 0),
(12, 'Менеджер Speed Dial', '', '', 7, 0),
(13, 'Добавить пункт', '?act=add', 'icons/add.png', 8, 0),
(14, 'Редактирование пунктов', '?act=editing', 'icons/config.png', 9, 0),
(15, 'Официальный сайт SecWind', 'http://secwind.ru', '/apple-touch-icon.png', 5, 0),
(16, 'Главная', '/', 'icons/index_page.png', 1, 0),
(31, 'Модули', '', '', 10, 0),
(18, 'Последняя версия SW', 'http://secwind.ru/pages/secwind.php', 'icons/last_ver.php', 2, 0),
(35, 'Службы', '/admin/modules/services.php', 'icons/help.png', 14, 0),
(21, 'Погода. Алматы', '?', 'icons/weather.php?city=almaty', 6, 0),
(32, 'Загрузить', '/admin/modules/upload.php', 'icons/download.png', 11, 0),
(34, 'Все модули', '/admin/modules/list.php', 'icons/info.png', 13, 0),
(36, 'Настройки', '/admin/modules/settings.php', 'icons/config.png', 15, 0),
(37, 'Сообщить об ошибке', 'http://secwind.ru/forum/index.php?forum=4', 'icons/bug.png', 4, 0),
(38, 'Документация', 'http://secwind.ru/blog/?show:blog/id:8/', 'icons/book.png', 3, 0),
(49, 'Резервное копирование', '', 'http://www.google.com/s2/favicons?domain=', 16, 0),
(50, 'Список Backup', '/admin/backup/list.php', '/admin/icons/info.png', 17, 0),
(51, 'Бекап файлов', '/admin/backup/files.php', '/admin/icons/archive.png', 18, 0),
(52, 'Бекап MySQL', '/admin/backup/mysql.php', '/admin/icons/db.png', 19, 0);

CREATE TABLE IF NOT EXISTS `news_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `msg` varchar(300) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
