<?php
	return ' ('.$sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'cat'")->result() .' / '. $sql->query("SELECT COUNT(*) FROM `mod_lib` WHERE `type` = 'arc'")->result().')';