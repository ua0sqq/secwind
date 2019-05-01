<?php
if (!defined('_BR_'))
   define('_BR_',chr(13).chr(10));
class ini {
    public $filename;
    public $arr;
    function __construct($file = false){
        if ($file)
		{
            if (!file_exists($file))
				file_put_contents($file, '');
			$this->loadFromFile($file);
		}
    }
    function initArray(){
        $this->arr = parse_ini_file($this->filename);
    }
    function loadFromFile($file){
        $result = true;
        $this->filename = $file;
        if (file_exists($file) && is_readable($file)){
            $this->initArray();
        }
        else
            $result = false;
        return $result;
    }
    function read($section, $key, $def = ''){
        if (isset($this->arr[$section][$key])){
            return $this->arr[$section][$key];
        } else
            return $def;
    }
    function write($key, $value){
        if (is_bool($value))
            $value = $value ? 1 : 0;
        $this->arr[$key] = $value;
    }
    function eraseSection($section){
        if (isset($this->arr[$section]))
            unset($this->arr[$section]);
    }
    function deleteKey($section, $key){
        if (isset($this->arr[$section][$key]))
            unset($this->arr[$section][$key]);
    }
    function readSections(&$array){
        $array = array_keys($this->arr);
        return $array;
    }
    function readKeys($section, &$array){
        if (isset($this->arr[$section])){
            $array = array_keys($this->arr[$section]);
            return $array;
        }
        return array();
    }
    function updateFile(){
        $result = '';
        foreach ($this->arr as $key=>$value){
                $result .= $key .'='.$value . PHP_EOL;
            }
            $result .= PHP_EOL;
        
            file_put_contents($this->filename, $result);
            return true;
    }
    function __destruct(){
        $this->updateFile();
    }
}