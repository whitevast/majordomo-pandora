<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='pandora_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  // step: default
  if($this->tab=='') {
  //updating 'NAME' (varchar)
   $rec['NAME']=gr('name');
  //updating 'MODEL' (varchar)
   $rec['MODEL']=gr('model');
  //updating 'PHONE' (varchar)
   $rec['PHONE']=gr('phone');
  //updating 'BALANCE' (varchar)
   $rec['BALANCE']=gr('balance');
  }
  // step: data
  if ($this->tab=='data') {
  }
  //UPDATING RECORD
   if ($ok) {
    if (isset($rec['ID'])) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  // step: data
  if ($this->tab=='data') {
   //dataset2
   $new_id=0;
   $delete_id = gr('delete_id');
   if ($delete_id) {
    SQLExec("DELETE FROM pandora_info WHERE ID='".(int)$delete_id."'");
   }
   $properties=SQLSelect("SELECT * FROM pandora_info WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['ID']==$new_id) continue;
    if ($this->mode=='update') {
	  $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      global ${'linked_method'.$properties[$i]['ID']};
      $properties[$i]['LINKED_METHOD']=trim(${'linked_method'.$properties[$i]['ID']});
      SQLUpdate('pandora_info', $properties[$i]);
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
      if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
      }
     }
   }
   $out['PROPERTIES']=$properties;   
  }
  //Вкладка команды
   if ($this->tab=='commands') {
	$commands=SQLSelect("SELECT * FROM pandora_commands WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");
	$total=count($commands);
	for($i=0;$i<$total;$i++) {
		if ($this->mode=='update') {
		$old_linked_object=$commands[$i]['LINKED_OBJECT'];
		$old_linked_property=$commands[$i]['LINKED_PROPERTY'];
		global ${'linked_object'.$commands[$i]['ID']};
		$commands[$i]['LINKED_OBJECT']=trim(${'linked_object'.$commands[$i]['ID']});
		global ${'linked_property'.$commands[$i]['ID']};
		$commands[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$commands[$i]['ID']});
		SQLUpdate('pandora_commands', $commands[$i]);
		if ($old_linked_object && $old_linked_object!=$commands[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$commands[$i]['LINKED_PROPERTY']) {
		removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
		}
		if ($commands[$i]['LINKED_OBJECT'] && $commands[$i]['LINKED_PROPERTY']) {
		addLinkedProperty($commands[$i]['LINKED_OBJECT'], $commands[$i]['LINKED_PROPERTY'], $this->name);
		}
		}
	}
	$out['COMMANDS']=$commands;   
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
