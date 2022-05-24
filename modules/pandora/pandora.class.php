<?php
/**
* Pandora 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 06:12:25 [Dec 18, 2021])
*/
//
//
class pandora extends module {
/**
* pandora
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="pandora";
  $this->title="Pandora";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
  $this->getConfig();
  $this->debug = $this->config['LOG_DEBMES'] == 1 ? true : false;
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
if ((time() - gg('cycle_pandoraRun')) < 25 ) {
	$out['CYCLERUN'] = 1;
 } else {
	$out['CYCLERUN'] = 0;
 }
 $this->getConfig();
 $out['LOGIN']=$this->config['LOGIN'];
 $out['PASSWORD']=$this->config['PASSWORD'];
 $out['LOG_DEBMES']=$this->config['LOG_DEBMES'];
 $out['CYCLE_TIME']=$this->config['CYCLE_TIME'];
 if (!$out['CYCLE_TIME']) {
  $out['CYCLE_TIME']=10;
 }
 if ($this->view_mode=='update_settings') {
   global $login;
   $this->config['LOGIN']=$login;
   global $password;
   $this->config['PASSWORD']=$password;
   global $log_debmes;
   $this->config['LOG_DEBMES']=$log_debmes;
   global $cycle_time;
   if($cycle_time < 10) $cycle_time = 10;
   $this->config['CYCLE_TIME']=$cycle_time;
   $this->saveConfig();
   //Добавляем устройства//
   $cookies = $this->getdata(1, "", "", "", $login, $password);
   if(!$cookies){
		$out['ERR'] = 1;
	    $out['LOGIN']=$login;
		$out['PASSWORD']=$password;
		
   }else{
	   $this->config['COOKIES'] = $cookies;
	   $this->saveConfig();
	   $devices=$this->getdata(2, $cookies);
	   foreach($devices as $device){
		   $rec = SQLSelectOne('SELECT * FROM pandora_devices WHERE DEV_ID ="'.$device['id'].'"');
		   if($rec['DEV_ID']){
				SQLUpdate('pandora_devices', $rec);
		   }
		   else{
				$rec['DEV_ID'] = $device['id'];														
				$rec['TITLE'] = $device['name'];
				$rec['MODEL'] = $device['model'];
				$rec['PHONE'] = $device['phone'];
				$id = SQLInsert('pandora_devices', $rec);
				$info = [['online','Онлайн'],
				['car_locked','Под охраной'],
				['alarm','Tревога'],
				['engine','Двигатель заведен'],
				['balance','Баланс СИМ-карты'],
				['move','Автомобиль движется'],
				['voltage','Напряжение бортовой сети'],
				['engine_temp','Температура двигателя'],
				['out_temp','Внешняя температура'],
				['fuel','Топливо'],
				['speed','Скорость'],
				['engine_rpm','Обороты двигателя'],
				['cabin_temp','Температура в салоне'],
				['gsm_level','Уровень GSM-сигнала'],
				['key','Зажигание включено'],
				['autostart_init','Процедура АЗ активна'],
				['human_right','HandsFree постановка под охрану'],
				['human_left','HandsFree снятие с охраны'],
				['gsm','Gsm-модем включен'],
				['gps','Gps-приемник включен'],
				['tracking','Трекинг включен'],
				['immo','Двигатель заблокирован'],
				['ext_sensor_alert_zone','Откл контр доп датчика, предупр зона'],
				['ext_sensor_main_zone','Откл контр доп датчика, осн зона'],
				['sensor_alert_zone','Откл контр датчика удара, предупр зона'],
				['sensor_main_zone','Откл контр датчика удара, осн зона'],
				['autostart','Запрограммирован АЗ двигателя'],
				['sms','Разрешена отправка СМС – сообщений'],
				['call','Разрешены голосовые вызовы'],
				['light','Включены габаритные огни (фары, свет.)'],
				['sound1','Выкл. Предупредительные сигналы сирены'],
				['sound2','Выкл. Все звуковые сигналы сирены'],
				['door_front_left','Открыта передняя левая дверь'],
				['door_front_rigt','Открыта передняя правая дверь'],
				['b_door_back_left','Открыта задняя левая дверь'],
				['b_door_back_right','Открыта задняя правая дверь'],
				['trunk','Багажник'],
				['hood','Капот'],
				['handbrake','Ручной тормоз'],
				['brakes','Тормоз'],
				['temp','Предпусковой подогреватель'],
				['active_secure','Активная охрана'],
				['heat','Запрограммирован пред. подогреватель'],
				['evaq','Режим эвакуации включен'],
				['to','Режим ТО включен'],
				['stay_home','Stay home'],
				['zapret_oprosa_metok','Запрет опроса меток'],
				['zapret_snyatia_s_ohrani_bez_metki','Запр сн-я с охр при отс-вии метки в зоне']];
				$code['DEVICE_ID'] = $id;
				for ($i=0; $i<count($info); $i++){
					$code['TITLE'] = $info[$i][0];
					$code['NAME'] = $info[$i][1];
					$code['VALUE'] = 0;
					$code['UPDATED'] = date('Y-m-d H:i:s');
					SQLInsert('pandora_info', $code);
				}
				$commands = [['alarm','Вкл/откл охраны'],
				['engine','Запуск/останов двигателя'],
				['track','Вкл/откл трекинга'],
				['temp','Вкл/откл подогревателя'],
				['tk','Вкл/откл таймерного канала'],
				['act_alarm','Вкл/откл активной охраны'],
				['to','Вкл/откл режима ТО'],
				['connect','Продление/завершение связи'],
				['check','Команда CHECK'],
				['trunk','Открытие багажника'],
				['light','Включение подсветки'],
				['beep','Подача звукового сигнала'],
				['dop1','Доп. команда 1'],
				['dop2','Доп. команда 2']];
				$com['DEVICE_ID'] = $id;
				for ($i=0; $i<count($commands); $i++){
					$com['TITLE'] = $commands[$i][0];
					$com['NAME'] = $commands[$i][1];
					$com['VALUE'] = 0;
					$com['UPDATED'] = date('Y-m-d H:i:s');
					SQLInsert('pandora_commands', $com);
				}
		   }
	   }
	setGlobal('cycle_pandoraControl','restart');
    $this->redirect("?");
   }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='pandora_devices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_pandora_devices') {
   $this->search_pandora_devices($out);
  }
  if ($this->view_mode=='edit_pandora_devices') {
   $this->edit_pandora_devices($out, $this->id);
  }
  if ($this->view_mode=='edit_pandora_devices') {
   $this->edit_pandora_devices($out, $this->id);
  }
  if ($this->view_mode=='delete_pandora_devices') {
   $this->delete_pandora_devices($this->id);
   $this->redirect("?data_source=pandora_devices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='pandora_info') {
  if ($this->view_mode=='' || $this->view_mode=='search_pandora_info') {
   $this->search_pandora_info($out);
  }
  if ($this->view_mode=='edit_pandora_info') {
   $this->edit_pandora_info($out, $this->id);
  }
 }
}


/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* pandora_devices search
*
* @access public
*/
 function search_pandora_devices(&$out) {
  require(dirname(__FILE__).'/pandora_devices_search.inc.php');
 }
/**
* pandora_devices edit/add
*
* @access public
*/
 function edit_pandora_devices(&$out, $id) {
  require(dirname(__FILE__).'/pandora_devices_edit.inc.php');
 }
/**
* pandora_devices delete record
*
* @access public
*/
 function delete_pandora_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM pandora_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM pandora_devices WHERE ID='".$rec['ID']."'");
  $properties=SQLSelect("SELECT * FROM pandora_info WHERE DEVICE_ID='".$rec['ID']."' AND LINKED_OBJECT != '' AND LINKED_PROPERTY != ''");
    foreach($properties as $prop) {
		removeLinkedProperty($prop['LINKED_OBJECT'], $prop['LINKED_PROPERTY'], $this->name);
	}
  SQLExec("DELETE FROM pandora_info WHERE DEVICE_ID='".$rec['ID']."'");
  $properties=SQLSelect("SELECT * FROM pandora_commands WHERE DEVICE_ID='".$rec['ID']."' AND LINKED_OBJECT != '' AND LINKED_PROPERTY != ''");
    foreach($properties as $prop) {
		removeLinkedProperty($prop['LINKED_OBJECT'], $prop['LINKED_PROPERTY'], $this->name);
	}
  SQLExec("DELETE FROM pandora_commands WHERE DEVICE_ID='".$rec['ID']."'");
 }
/**
* pandora_info search
*
* @access public
*/
 function search_pandora_info(&$out) {
  require(dirname(__FILE__).'/pandora_info_search.inc.php');
 }
/**
* pandora_info edit/add
*
* @access public
*/
 function edit_pandora_info(&$out, $id) {
  require(dirname(__FILE__).'/pandora_info_edit.inc.php');
 }
 
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   $table='pandora_commands';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     $command = SQLSelectOne("SELECT * FROM pandora_commands WHERE ID='".(int)$properties[$i]['ID']."'");
	 $device = SQLSelectOne("SELECT * FROM pandora_devices WHERE ID='".(int)$command['DEVICE_ID']."'");
	 if($command['TITLE'] == "alarm"){
		 if($value == "0") $com = "2";
		 else if($value == "1") $com = "1";
	 }else if($command['TITLE'] == "engine"){
		 if($value == "0") $com = "8";
		 else if($value == "1") $com = "4";
	 }else if($command['TITLE'] == "track"){
		 if($value == "0") $com = "32";
		 else if($value == "1") $com = "16";
	 }else if($command['TITLE'] == "temp"){
		 if($value == "0") $com = "22";
		 else if($value == "1") $com = "21";
	 }else if($command['TITLE'] == "tk"){
		 if($value == "0") $com = "34";
		 else if($value == "1") $com = "33";
	 }else if($command['TITLE'] == "act_alarm"){
		 if($value == "0") $com = "18";
		 else if($value == "1") $com = "17";
	 }else if($command['TITLE'] == "to"){
		 if($value == "0") $com = "41";
		 else if($value == "1") $com = "40";
	 }else if($command['TITLE'] == "connect"){
		 if($value == "0") $com = "15";
		 else if($value == "1") $com = "240";
	 }else if($command['TITLE'] == "check"){
		 $com = "255";
	 }else if($command['TITLE'] == "trunk"){
		 $com = "35";
	 }else if($command['TITLE'] == "light"){
		 $com = "24";
	 }else if($command['TITLE'] == "beep"){
		 $com = "23";
	 }else if($command['TITLE'] == "dop1"){
		 $com = "64";
	 }else if($command['TITLE'] == "dop2"){
		 $com = "128";
	 }
	 if($this->getdata(4, $this->config['COOKIES'], $device['DEV_ID'], $com)){
		 $this->Writelog("Команда ".$command['NAME']." (".$value.") отправлена на ".$device['TITLE']);
	 }
	 $command['VALUE']=$value;
	 $command['UPDATED'] = date('Y-m-d H:i:s');
	 SQLUpdate('pandora_commands', $command);
    }
   }
 }
 function processCycle() {
  $this->getConfig();
  $devices = SQLSelect("SELECT * FROM pandora_devices");
  $data = $this->getdata(3, $this->config['COOKIES']);
  if(!isset($data['stats'])) return;
  foreach($devices as $device){
	  $info = SQLSelect("SELECT * FROM pandora_info WHERE DEVICE_ID='".$device['ID']."'");
	  $deviceinfo = array_merge($data['stats'][$device['DEV_ID']], $this->parsebit($data['stats'][$device['DEV_ID']]['bit_state_1']));
	  if($deviceinfo['gps']){
	    global ${'latitude'.$device['DEV_ID']};
	    global ${'longitude'.$device['DEV_ID']};
	    if(${'latitude'.$device['DEV_ID']} != $deviceinfo['x'] or ${'longitude'.$device['DEV_ID']} != $deviceinfo['y']){
			if($deviceinfo['move']){
			  ${'latitude'.$device['DEV_ID']} = $deviceinfo['x'];
			  ${'longitude'.$device['DEV_ID']} = $deviceinfo['y'];
			  $url = BASE_URL . '/gps.php?latitude=' . $deviceinfo['x']
			  . '&longitude=' .$deviceinfo['y']
			  . '&altitude=0'
			  . '&accuracy=0'
			  . '&provider=0'
			  . '&speed='     .$deviceinfo['speed'] 
			  . '&battlevel=0'
			  . '&charging=0'
			  . '&deviceid='  .$device['TITLE']
			  . '&op=';
			  getURL($url, 0);
		   }
		}
	  }
	  foreach($info as $inf){
		  if($inf['TITLE'] == 'balance'){
			 if($inf['VALUE'] != $deviceinfo['balance']['value']){
				$params['OLD_VALUE'] = $inf['VALUE'];
				$params['NEW_VALUE'] = $deviceinfo['balance']['value'];
				$this->setProperty($inf, (int)$deviceinfo[$inf['TITLE']], $params);
				$inf['VALUE'] = $deviceinfo['balance']['value'];
				$inf['UPDATED'] = date('Y-m-d H:i:s');
				SQLUpdate('pandora_info', $inf);
				$device['BALANCE'] = $deviceinfo['balance']['value'];
				SQLUpdate('pandora_devices', $device);
			 }
		  }
		  else{
			  if(isset($deviceinfo[$inf['TITLE']])){
			   if($inf['VALUE'] != $deviceinfo[$inf['TITLE']]){
				  $params['OLD_VALUE'] = $inf['VALUE'];
				  $params['NEW_VALUE'] = (int)$deviceinfo[$inf['TITLE']];
				  $this->setProperty($inf, (int)$deviceinfo[$inf['TITLE']], $params);
				  $inf['VALUE'] = $deviceinfo[$inf['TITLE']];
				  $inf['UPDATED'] = date('Y-m-d H:i:s');
				  SQLUpdate('pandora_info', $inf);
			   }
		     }
		 }
	  }
  }
 }
  //Запись в привязанное свойство
function setProperty($device, $value, $params = ''){
    if ($device['LINKED_OBJECT'] && $device['LINKED_PROPERTY']) {
		setGlobal($device['LINKED_OBJECT'] . '.' . $device['LINKED_PROPERTY'], $value, 0, 'pandora_module');
    }
	if ($device['LINKED_OBJECT'] && $device['LINKED_METHOD']) {
     $params['VALUE'] = $value;
	 callMethodSafe($device['LINKED_OBJECT'] . '.' . $device['LINKED_METHOD'], $params);
    }
}

// Глобальный поиск по модулю
 function findData($data) {
    $res = array();
	//Pandora devices
    $devices = SQLSelect("SELECT ID, TITLE, MODEL FROM pandora_devices where `TITLE` like '%" . DBSafe($data) . "%' OR `MODEL` like '%" . DBSafe($data) . "%' OR `PHONE` like '%" . DBSafe($data) . "%'  order by TITLE");
	foreach($devices as $device){
         $res[]= '&nbsp;<span class="label label-info">devices</span>&nbsp;<a href="/panel/pandora.html?md=pandora&inst=adm&data_source=&view_mode=edit_pandora_devices&id=' . $device['ID'] . '.html">' . $device['TITLE'].($device['MODEL'] ? '<small style="color: gray;padding-left: 5px;"><i class="glyphicon glyphicon-arrow-right" style="font-size: .8rem;vertical-align: text-top;color: lightgray;"></i> ' . $device['MODEL'] . '</small>' : ''). '</a>';
    }
    //Pandora info
    $infos = SQLSelect("SELECT ID, TITLE, NAME, DEVICE_ID FROM pandora_info where `TITLE` like '%" . DBSafe($data) . "%' OR `NAME` like '%" . DBSafe($data) . "%' order by TITLE");
    foreach($infos as $info){
		$alarm = SQLSelectOne('SELECT TITLE FROM pandora_devices WHERE ID="'.$info['DEVICE_ID'].'"');
		$res[]= '&nbsp;<span class="label label-info">'.$alarm['TITLE'].'</span>&nbsp;<span class="label label-primary">info</span>&nbsp;<a href="/panel/pandora.html?md=pandora&inst=adm&data_source=&view_mode=edit_pandora_devices&tab=data&id=' . $info['DEVICE_ID'] . '.html">' . $info['NAME'].'</a>';
    }
	 //Pandora commands
    $cmds = SQLSelect("SELECT ID, TITLE, NAME, DEVICE_ID FROM pandora_commands where `TITLE` like '%" . DBSafe($data) . "%' OR `NAME` like '%" . DBSafe($data) . "%' order by TITLE");
    foreach($cmds as $cmd){
		$alarm = SQLSelectOne('SELECT TITLE FROM pandora_devices WHERE ID="'.$info['DEVICE_ID'].'"');
		$res[]= '&nbsp;<span class="label label-info">'.$alarm['TITLE'].'</span>&nbsp;<span class="label label-primary">command</span>&nbsp;<a href="/panel/pandora.html?md=pandora&inst=adm&data_source=&view_mode=edit_pandora_devices&tab=commands&id=' . $cmd['DEVICE_ID'] . '.html">' . $cmd['NAME'].'</a>';
    }
    return $res;
 }
 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  $id = SQLSelect('SELECT ID FROM pandora_devices');
  for($i=0; $i<count($id); $i++){
	$this->delete_pandora_devices($id[$i]['ID']);
  }
  SQLExec('DROP TABLE IF EXISTS pandora_devices');
  SQLExec('DROP TABLE IF EXISTS pandora_info');
  SQLExec('DROP TABLE IF EXISTS pandora_commands');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
pandora_devices - 
pandora_info - 
*/
  $data = <<<EOD
 pandora_devices: ID int(10) unsigned NOT NULL auto_increment
 pandora_devices: DEV_ID varchar(100) NOT NULL DEFAULT ''
 pandora_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 pandora_devices: MODEL varchar(20) NOT NULL DEFAULT ''
 pandora_devices: PHONE varchar(20) NOT NULL DEFAULT ''
 pandora_devices: BALANCE varchar(50) NOT NULL DEFAULT ''
 pandora_info: ID int(10) unsigned NOT NULL auto_increment
 pandora_info: TITLE varchar(100) NOT NULL DEFAULT ''
 pandora_info: NAME varchar(255) NOT NULL DEFAULT ''
 pandora_info: VALUE varchar(20) NOT NULL DEFAULT ''
 pandora_info: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 pandora_info: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 pandora_info: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 pandora_info: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 pandora_info: UPDATED datetime
 pandora_commands: ID int(10) unsigned NOT NULL auto_increment
 pandora_commands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 pandora_commands: TITLE varchar(255) NOT NULL DEFAULT ''
 pandora_commands: NAME varchar(255) NOT NULL DEFAULT ''
 pandora_commands: VALUE int(10) NOT NULL DEFAULT '0'
 pandora_commands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 pandora_commands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 pandora_commands: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------

/////////////////////////My_functions//////////////////////////////////

function getdata($type, $cookies = "", $device = "", $command = "", $login = "", $password = ""){  //$type: 1-auth, 2-get devces, 3-update, 4-command
	if ($type == 1){
		$path = "/api/users/login";
		$post = '{"login":"'.$login.'","password":"'.$password.'","lang":"ru"}';
	}
	else if ($type == 2) $path = "/api/devices";
	else if ($type == 3) $path = "/api/updates?ts=0"; // время у них что-ли отстает
	else if ($type == 4){
		$path = "/api/devices/command";
		$post = '{"id":"'.$device.'","command":"'.$command.'"}';
	}
	$header=array(
		'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
		'Content-Type: application/json; charset=UTF-8',
		'Accept: application/json',
		'Accept-Encoding: gzip, deflate, br',
	);
	$ch = curl_init('https://pro.p-on.ru'.$path);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	if($type == 1 or $type == 4){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	$html = curl_exec($ch);
	curl_close($ch);
	$html = json_decode($html, true);
	if($type == 1){
		if(isset($html["session_id"])) return "sid=".$html["session_id"];
		else{
			$this->WriteLog('Неверное имя пользователя или пароль');
			return false;
		}
	}
	if(isset($html['error_text'])){ //если ошибка
		if($html['status'] == "Session is expired"){ //куки просрочились
			$this->getConfig();
			$cookies = $this->getdata(1, "", "", "", $this->config['LOGIN'],  $this->config['PASSWORD']); //получаем новые куки
			$this->config['COOKIES'] = $cookies;
			$this->saveConfig();
			return $this->getdata($type, $cookies, $command); //запускаем команду заново
		}
	}
	if($type == 4){
		if($html['action_result'][$device] == "sent") return true;
		else return false;
	}
	return $html;
}

function parsebit($dec){
	$bin = decbin($dec);
	//print $bin;
	$bin = strrev($bin);
	$a = 63-strlen($bin);
	for($i=1; $i<$a; $i++){
		$bin=$bin."0"; //если строка короче, чем нужно для парсинга, чтоб не выдавалось ошибок, добиваем нулями
	}
	$data['car_locked'] = $bin[0] == 1 ? 1 : 0;                         // под охраной;
	$data['alarm'] = $bin[1] == 1 ? 1 : 0;                              // тревога;
	$data['engine'] = $bin[2] == 1 ? 1 : 0;                             // двигатель заведен;
	$data['key'] = $bin[3] == 1 ? 1 : 0;                                // зажигание включено;
	$data['autostart_init'] = $bin[4] == 1 ? 1 : 0;                     // процедура АЗ активна;
	$data['human_right'] = $bin[5] == 1 ? 1 : 0;                        // HandsFree постановка под охрану при удалении от авто
	$data['human_left'] = $bin[6] == 1 ? 1 : 0;                         // HandsFree снятие с охраны при приближении к авто
	$data['gsm'] = $bin[7] == 1 ? 1 : 0;                                // Gsm-модем включен
	$data['gps'] = $bin[8] == 1 ? 1 : 0;                                // Gps-приемник включен
	$data['tracking'] = $bin[9] == 1 ? 1 : 0;                           // трекинг включен
	$data['immo'] = $bin[10] == 1 ? 1 : 0;                              // Двигатель заблокирован
	$data['ext_sensor_alert_zone'] = $bin[11] == 1 ? 1 : 0;             // Отключен контроль доп. датчика, предупредительная зона
	$data['ext_sensor_main_zone'] = $bin[12] == 1 ? 1 : 0;              // Отключен контроль доп. датчика, основная зона
	$data['sensor_alert_zone'] = $bin[13] == 1 ? 1 : 0;                 // Отключен контроль датчика удара, предупредительная зона
	$data['sensor_main_zone'] = $bin[14] == 1 ? 1 : 0;                  // Отключен контроль датчика удара, основная зона
	$data['autostart'] = $bin[15] == 1 ? 1 : 0;                         // Запрограммирован АЗ двигателя
	$data['sms'] = $bin[16] == 1 ? 1 : 0;                               // Разрешена отправка СМС – сообщений
	$data['call'] = $bin[17] == 1 ? 1 : 0;                              // Разрешены голосовые вызовы
	$data['light'] = $bin[18] == 1 ? 1 : 0;                             // Включены габаритные огни (фары, свет.)
	$data['sound1'] = $bin[19] == 1 ? 1 : 0;                            // Выкл. Предупредительные сигналы сирены
	$data['sound2'] = $bin[20] == 1 ? 1 : 0;                            // Выкл. Все звуковые сигналы сирены
	$data['door_front_left'] = $bin[21] == 1 ? 1 : 0;                   // Открыта передняя левая дверь
	$data['door_front_rigt'] = $bin[22] == 1 ? 1 : 0;                   // Открыта передняя правая дверь
	$data['b_door_back_left'] = $bin[23] == 1 ? 1 : 0;                  // Открыта задняя левая дверь
	$data['b_door_back_right'] = $bin[24] == 1 ? 1 : 0;                 // Открыта задняя правая дверь
	$data['trunk'] = $bin[25] == 1 ? 1 : 0;                  			// Багажник
	$data['hood'] = $bin[26] == 1 ? 1 : 0;                              // Капот
	$data['handbrake'] = $bin[27] == 1 ? 1 : 0;                         // Ручной тормоз
	$data['brakes'] = $bin[28] == 1 ? 1 : 0;                            // Тормоз
	$data['temp'] = $bin[29] == 1 ? 1 : 0;                              // Предпусковой подогреватель
	$data['active_secure'] = $bin[30] == 1 ? 1 : 0;                     // Активная охрана
	$data['heat'] = $bin[31] == 1 ? 1 : 0;                              // Запрограммирован пред. подогреватель
	$data['evaq'] = $bin[33] == 1 ? 1 : 0;                              // Режим эвакуации включен
	$data['to'] = $bin[34] == 1 ? 1 : 0;                                // Режим ТО включен
	$data['stay_home'] = $bin[35] == 1 ? 1 : 0;                         // stay home
	$data['zapret_oprosa_metok'] = $bin[60] == 1 ? 1 : 0;               // Запрет опроса меток
	$data['zapret_snyatia_s_ohrani_bez_metki'] = $bin[61] == 1 ? 1 : 0; // Запрет снятия с охраны при отсутствии метки в зоне
	return $data;
}

function WriteLog($msg){
     if ($this->debug) {
        DebMes($msg, $this->name);
     }
  }
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgRGVjIDE4LCAyMDIxIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
