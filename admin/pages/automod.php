<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - AutoMod
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public $navtitle;
    public $navlist = array();
    
    public function __construct(){

        $this->navtitle  = "AutoMod";
        $this->navlist[] = array("Added Modules", "zoom.png", "added");
        $this->navlist[] = array("Install Modules", "add.png", "install");
        $this->navlist[] = array("Uninstall Modules", "delete.png", "uninstall");
        $this->navlist[] = array("Check For Updates", "arrow_refresh.png", "updates");
    
    }

    public function description(){

        return "<strong>AutoMod</strong><br />
                Automod is a program that allows you to install and uninstall modules for THT.";
    
    }

    public function content(){
		global $dbh, $postvar, $getvar, $instance;
        
        if(!automod::checkDir(INC."/automod")){

            main::errors("Please create the /includes/automod directory and make it writable.");
        
        }

        if(!automod::checkPerms(INC."/automod")){

            main::errors("Please make the /includes/automod directory writable.  (0777)");
        
        }
        
        switch($getvar['sub']){

            case "added":
                $mods_exist = $dbh->select("automod_mods", 0, array("mod_name", "ASC"));
                $mods_exist = $dbh->num_rows($mods_exist);
                if($mods_exist > 0){

                    if(is_numeric($getvar['view'])){

                        $mod_vals = automod::module_data($getvar['view']);
                        
                        if(automod::installed_tht_is_reworked()){

                            $THT_VERS = $dbh->config("version")." Reworked";
                        
                        }else{

                            $THT_VERS = $dbh->config("version");
                        
                        }

                        $mod_vals['mod_thtversion'] = str_replace("rework3d", "Reworked", strtolower($mod_vals['mod_thtversion']));
                        $mod_vals['mod_thtversion'] = str_replace("reworked", "Reworked", strtolower($mod_vals['mod_thtversion']));
                        
                        $viewmod_array['ID']              = $mod_vals['id'];
                        $viewmod_array['NAME']            = $mod_vals['mod_name'];
                        $viewmod_array['MODVERSION']      = $mod_vals['mod_version'];
                        $viewmod_array['VERSION']         = $THT_VERS;
                        $viewmod_array['THTVERSION']      = $mod_vals['mod_thtversion'];
                        $viewmod_array['LICENSE']         = $mod_vals['mod_license'];
                        $viewmod_array['AUTHOR']          = $mod_vals['mod_author'];
                        $viewmod_array['SUPPORT']         = $mod_vals['mod_support'];
                        $viewmod_array['AUTHLINK']        = $mod_vals['mod_link'];
                        $viewmod_array['PROJWEB']         = $mod_vals['mod_projectpage'];
                        $viewmod_array['RECOMMENDATIONS'] = automod::recommendations();
                        $viewmod_array['DESCRIPTION']     = nl2br($mod_vals['mod_descrip']);
                        $viewmod_array['DIY']             = $mod_vals['mod_diy'];
                        
                        echo style::replaceVar("tpl/automod/view-module.tpl", $viewmod_array);
                        
                    }else{

                        $mod_query = $dbh->select("automod_mods", 0, array("mod_name", "ASC"));
                        while($mod_vals = $dbh->fetch_array($mod_query)){

                            $listmods_array['ID']   = $mod_vals['id'];
                            $listmods_array['NAME'] = $mod_vals['mod_name'];
                            unset($elipses);
                            if(strlen($mod_vals['mod_descrip']) > 250){

                                $elipses = " <b>...</b>";
                            
                            }

                            $listmods_array['DESCRIPTION'] = nl2br(htmlentities(substr($mod_vals['mod_descrip'], 0, 250)).$elipses);
                            echo style::replaceVar("tpl/automod/list-modules.tpl", $listmods_array);
                        
                        }

                    }

                
                }else{

                    echo "No modules installed.";
                
                }

                break;
            
            case "install":
                if($getvar['install']){
				
					//Install a module
                    if($getvar['confirm'] == '1'){

                        automod::completeinstall($getvar['install']);
                    
                    }else{

                        automod::install_mod($getvar['install']);
                    
                    }

                
                }elseif($getvar['reminstall']){
				
					//Remove a module's directory                    
                    $reminstall = $getvar['reminstall'];
                    if($postvar['confirm']){

                        if($postvar['yes']){

                            automod::rmfulldir(INC."/automod/".$reminstall);
                            main::redirect("?page=automod&sub=install");
                        
                        }else{

                            main::redirect("?page=automod&sub=install");
                        
                        }

                    }else{

                        $warning_array['HIDDEN'] = "<input type = 'hidden' name = 'confirm' value = 'confirm'>";
                        echo style::replaceVar("tpl/warning.tpl", $warning_array);
                    
                    }

                }else{

                    //Add a module to be installed
                    automod::processaddmod();
                
                }

                break;
            
            case "uninstall":
                $mods_exist = $dbh->select("automod_mods", 0, array("mod_name", "ASC"));
                $mods_exist = $dbh->num_rows($mods_exist);
                if($mods_exist > 0){

                    if(is_numeric($getvar['uninstall'])){

                        if($getvar['confirm'] == '1'){

                            if(!$postvar['remove'] && !$postvar['rename']){

                                $mode = '1';
                            
                            }

                            if($postvar['remove'] && $postvar['rename']){

                                $mode = '2';
                            
                            }

                            if($postvar['remove'] && !$postvar['rename']){

                                $mode = '3';
                            
                            }

                            if(!$postvar['remove'] && $postvar['rename']){

                                $mode = '4';
                            
                            }

                            automod::completeuninstall($getvar['uninstall'], $mode);
                        
                        }else{

                            automod::uninstall_mod($getvar['uninstall']);
                        
                        }

                    }else{

                        $mod_query = $dbh->select("automod_mods", 0, array("mod_name", "ASC"));
                        while($mod_vals = $dbh->fetch_array($mod_query)){

                            $listmods_array['ID']   = $mod_vals['id'];
                            $listmods_array['NAME'] = $mod_vals['mod_name'];
                            unset($elipses);
                            if(strlen($mod_vals['mod_descrip']) > 250){

                                $elipses = " <b>...</b>";
                            
                            }

                            $listmods_array['DESCRIPTION'] = nl2br(htmlentities(substr($mod_vals['mod_descrip'], 0, 250)).$elipses);
                            echo style::replaceVar("tpl/automod/list-modules.tpl", $listmods_array);
                        
                        }

                    }

                
                }else{

                    echo "No modules installed.";
                
                }

                break;
            
            case "updates":
                automod::updates_check();
                break;
        
        }

    }

}

?>