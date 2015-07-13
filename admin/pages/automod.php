<?php
//////////////////////////////
// The Hosting Tool
// Admin Area - System Tools
// By Jonny H & Jimmie Lin
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

class page {

        public $navtitle;
        public $navlist = array();

        public function __construct() {
                $this->navtitle = "AutoMod";
                $this->navlist[] = array("Added Modules", "zoom.png", "added");
                $this->navlist[] = array("Install Modules", "add.png", "install");
                $this->navlist[] = array("Uninstall Modules", "delete.png", "uninstall");
                $this->navlist[] = array("Check For Updates", "arrow_refresh.png", "updates");
        }

        public function description() {
                return "<strong>AutoMod</strong><br />
                Automod is a program that allows you to install and uninstall modules for THT.";
        }

        public function content() { # Displays the page
                global $main;
                global $style;
                global $db;
                global $automod;

                if(!$automod->checkDir(LINK."AutoMod")){
                      $main->errors("Please create the /includes/AutoMod directory and make it writable.");
                }

                if(!$automod->checkPerms(LINK."AutoMod")){
                      $main->errors("Please make the /includes/AutoMod directory writable.  (0777)");
                }

                $automod->automod_installed();
                
                switch($main->getvar['sub']) {
                        case "added":
                                $mods_exist = $db->query("SELECT * FROM `<PRE>automod_mods` ORDER BY mod_name ASC");
                                $mods_exist = $db->num_rows($mods_exist);
                                if($mods_exist > 0){
                                 if(is_numeric($main->getvar['view'])){
                                  $mod_vals = $automod->module_data($main->getvar['view']);

                                  if($automod->installed_tht_is_reworked()){
                                  $THT_VERS = $db->config("version")." Reworked";
                                  }else{
                                  $THT_VERS = $db->config("version");
                                  }
                                  
                                  $mod_vals['mod_thtversion'] = str_replace("rework3d", "Reworked", strtolower($mod_vals['mod_thtversion']));
                                  $mod_vals['mod_thtversion'] = str_replace("reworked", "Reworked", strtolower($mod_vals['mod_thtversion']));

                                  $array['ID'] = $mod_vals['id'];
                                  $array['NAME'] = $mod_vals['mod_name'];
                                  $array['MODVERSION'] = $mod_vals['mod_version'];
                                  $array['VERSION'] = $THT_VERS;
                                  $array['THTVERSION'] = $mod_vals['mod_thtversion'];
                                  $array['LICENSE'] = $mod_vals['mod_license'];
                                  $array['AUTHOR'] = $mod_vals['mod_author'];
                                  $array['SUPPORT'] = $mod_vals['mod_support'];
                                  $array['AUTHLINK'] = $mod_vals['mod_link'];
                                  $array['PROJWEB'] = $mod_vals['mod_projectpage'];
                                  $array['RECOMMENDATIONS'] = $automod->recommendations();
                                  $array['DESCRIPTION'] = nl2br($mod_vals['mod_descrip']);
                                  $array['DIY'] = $mod_vals['mod_diy'];

                                  echo $style->replaceVar("tpl/AutoMod/viewmod.tpl", $array);

                                 }else{
                                  $mod_query = $db->query("SELECT * FROM `<PRE>automod_mods` ORDER BY `mod_name` ASC");
                                   while($mod_vals = $db->fetch_array($mod_query)){
                                    $array['ID'] = $mod_vals['id'];
                                    $array['NAME'] = $mod_vals['mod_name'];
                                    unset($elipses);
                                    if(strlen($mod_vals['mod_descrip']) > 250){
                                        $elipses = " <b>...</b>";
                                    }
                                    $array['DESCRIPTION'] = nl2br(htmlentities(substr($mod_vals['mod_descrip'], 0, 250)).$elipses);
                                    echo $style->replaceVar("tpl/AutoMod/listmods.tpl", $array);
                                  }
                                 }

                                }else{
                                echo "No modules installed.";
                                }
                        break;

                        case "install":
                                if($main->getvar['install']){         #Install a module
                                 if($main->getvar['confirm'] =='1'){
                                 $automod->completeinstall($main->getvar['install']);
                                 }else{
                                 $automod->install_mod($main->getvar['install']);
                                 }

                                }elseif($main->getvar['reminstall']){ #Remove a module's directory

                                 $reminstall = $main->getvar['reminstall'];
                                 if($main->postvar['confirm']){
                                  if($main->postvar['yes']){
                                   $automod->rmfulldir(LINK."AutoMod/".$reminstall);
                                   $main->redirect("?page=automod&sub=install");
                                  }else{
                                   $main->redirect("?page=automod&sub=install");
                                  }
                                 }else{
                                 $array['HIDDEN'] = "<input type = 'hidden' name = 'confirm' value = 'confirm'>";
                                 echo $style->replaceVar("tpl/warning.tpl", $array);
                                }
                                }else{
                                                                     #Add a module to be installed
                                 $automod->processaddmod();
                                }
                        break;

                        case "uninstall":
                                $mods_exist = $db->query("SELECT * FROM `<PRE>automod_mods` ORDER BY `mod_name` ASC");
                                $mods_exist = $db->num_rows($mods_exist);
                                if($mods_exist > 0){
                                 if(is_numeric($main->getvar['uninstall'])){
                                  if($main->getvar['confirm'] =='1'){

                                   if(!$main->postvar['remove'] && !$main->postvar['rename']){
                                    $mode = '1';
                                   }

                                   if($main->postvar['remove'] && $main->postvar['rename']){
                                    $mode = '2';
                                   }

                                   if($main->postvar['remove'] && !$main->postvar['rename']){
                                    $mode = '3';
                                   }

                                   if(!$main->postvar['remove'] && $main->postvar['rename']){
                                    $mode = '4';
                                   }
                                   
                                    $automod->completeuninstall($main->getvar['uninstall'], $mode);
                                  }else{
                                  $automod->uninstall_mod($main->getvar['uninstall']);
                                  }
                                 }else{
                                  $mod_query = $db->query("SELECT * FROM `<PRE>automod_mods` ORDER BY `mod_name` ASC");
                                   while($mod_vals = $db->fetch_array($mod_query)){
                                    $array['ID'] = $mod_vals['id'];
                                    $array['NAME'] = $mod_vals['mod_name'];
                                    unset($elipses);
                                    if(strlen($mod_vals['mod_descrip']) > 250){
                                        $elipses = " <b>...</b>";
                                    }
                                    $array['DESCRIPTION'] = nl2br(htmlentities(substr($mod_vals['mod_descrip'], 0, 250)).$elipses);
                                    echo $style->replaceVar("tpl/AutoMod/listmods.tpl", $array);
                                  }
                                 }

                                }else{
                                echo "No modules installed.";
                                }
                        break;
                        
                        case "updates":
                               $automod->updates_check();
                        break;
                }
        }
}
?>
