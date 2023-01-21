<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'pandora/pandora.class.php');
$pandora_module = new pandora();
$pandora_module->getConfig();
$tmp = SQLSelectOne("SELECT ID FROM pandora_devices LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
$latest_check=0;
if($pandora_module->config['CYCLE_TIME'] != "") $checkEvery = $pandora_module->config['CYCLE_TIME'];
else $checkEvery = 20;
$timeUpdate = 0;
$gmp = false;
if(PHP_INT_MAX == 2147483647){
	if(extension_loaded('gmp')) $gmp = true;
	elseif(method_exists($pandora_module, 'sendnotification')) {
		$pandora_module->sendnotification('Обнаружена х86 версия PHP. Для корректной работы на Linux необходимо устаовить пакет php-gmp, на Windows включить расширение gmp в файле php.ini и перезапустить устройство. На стандартной уствновке файл находится по пути c:\_majordomo\server\config_tpl\.', 'warning');
	}
}
while (1)
{
   if(time() - $timeUpdate > 20){
     setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
	 $timeUpdate = time();
   }
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
    echo date('Y-m-d H:i:s').' Polling devices...';
    $pandora_module->processCycle();
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
