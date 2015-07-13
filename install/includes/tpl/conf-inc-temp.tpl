<?php
//////////////////////////////
// The Hosting Tool Reworked
// SQL Config
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

//MAIN SQL CONFIG - Change values accordingly
$sql['host'] = '%HOST%'; //The DB Host, usually default - localhost
$sql['user'] = '%USER%'; //The DB Username
$sql['pass'] = '%PASS%'; //The DB Password
$sql['db']   = '%DB%';   //The DB Database, remember to have your username prefix
$sql['pre']  = '%PRE%';  //The DB Prefix, usually default unless otherwise

//LEAVE THIS
$sql['install'] = %TRUE%;

?>
