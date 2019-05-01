<?php
	return ' ('.$sql->query("SELECT count(*) FROM `down_files` WHERE type = 1")->result().' / '.$sql->query("SELECT count(*) FROM `down_files` WHERE type = 2")->result().')';