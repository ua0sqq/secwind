<?php
// Author:
//   Vagharshak Tozalakyan <vagh@armdex.com>
//   This module was written by author on its leasure time.

define ( 'E_TMP_DIR',    'Incorrect temprorary directory specified.' );
define ( 'E_IP_ADDR',    'Incorrect IP address specified.' );
define ( 'E_LOG_FILE',   'Log file access error! Check permissions to write.' );
define ( 'E_CRON_FNAME', 'The name of cron file must begin with dot.' );
define ( 'E_CRON_FILE',  'Cron file access error! Check permissions to write.' );
define ( 'E_CRON_JOB',   'Unable to perform the cron job.' );

class FloodBlocker
{

  var $logs_path = tmpDir;
  var $ip_addr;
  var $rules;
  var $cron_file;
  var $cron_interval;
  var $logs_timeout;

  function __construct($ip = null)
  {

    if (!is_dir ( $this->logs_path ))
      trigger_error ( E_TMP_DIR, E_USER_ERROR );

    $logs_path = str_replace ( '\\', '/', $this->logs_path);
    if ( substr ( $logs_path, -1 ) != '/' )
      $logs_path .= '/';

    $this->logs_path = $logs_path;

    if (empty($ip))
      $ip = $_SERVER['REMOTE_ADDR'];

    $ip = ip2long ($ip);
    if ($ip == -1 || $ip === FALSE)
      trigger_error ( E_IP_ADDR, E_USER_ERROR );

    $this->ip_addr = $ip;

    $this->rules = unserialize(file_get_contents(H . 'engine/files/data/flood_config.swi'));
    $this->cron_file = '.sfb';
    $this->cron_interval = 1800;  // 30 minutes
    $this->logs_timeout = 7200;  // 2 hours

  }


  function RawCheck ( &$info )
  {

    $no_flood = TRUE;

    foreach ( $this->rules as $interval=>$limit )
    {
		if ($interval < 1)
			continue;
	
      if ( ! isset ( $info[$interval] ) )
      {
        $info[$interval]['time'] = time ( );
        $info[$interval]['count'] = 0;
      }

      $info[$interval]['count'] += 1;

      if ( time ( ) - $info[$interval]['time'] > $interval )
      {
        $info[$interval]['count'] = 1;
        $info[$interval]['time'] = time ( );
      }

      if ( $info[$interval]['count'] > $limit )
      {
        $info[$interval]['time'] = time ( );
        $no_flood = FALSE;
      }
    }  // foreach

    return $no_flood;

  }

  function CheckFlood ( )
  {
    $path = $this->logs_path . $this->ip_addr;

    if ( ! ( $f = fopen ( $path, 'a+' ) ) )
      trigger_error ( E_LOG_FILE, E_USER_ERROR);

    flock ( $f, LOCK_EX );

    $info = fread ( $f, filesize ( $path ) + 10 );
    $info = unserialize( $info );

    $result = $this->RawCheck ( $info );

    ftruncate ( $f, 0 );
    fwrite ( $f, serialize( $info ) );
    fflush ( $f );

    flock($f, LOCK_UN);

    fclose($f);

    return $result;

  }
}