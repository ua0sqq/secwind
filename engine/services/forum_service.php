<?php

	return ' ('.$sql->query("SELECT COUNT(*) FROM `forum_topics` ")->result() .' / '.$sql->query("SELECT COUNT(*) FROM `forum_posts` ")->result().')';