<?php
    if (preg_match('#([0-9]*)([a-z]*)#i', ini_get('upload_max_filesize'), $varrs))
    {
        if ($varrs[2] == 'M')
            return $varrs[1] * 1048576;
        elseif ($varrs[2] == 'K')
            return $varrs[1] * 1024;
        elseif ($varrs[2] == 'G')
            return $varrs[1]*1024*1048576;
    }