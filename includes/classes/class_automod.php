<?PHP
//////////////////////////////
// The Hosting Tool
// AutoMod Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class automod{
	
	//This grabs the updates info from a URL
    public function go_fetch($url){

        $versions_file = @file_get_contents($url);
        //Example: [[VERSION]][[URL]]
        
        if(!empty($versions_file)){

            if(substr_count($versions_file, "[[") == 2 && substr_count($versions_file, "]]") == 2){

                $versions_explode = explode("]][[", $versions_file);
                
                $current_vers = str_replace("[[", "", $versions_explode[0]);
                $download_url = str_replace("]]", "", $versions_explode[1]);
                 
                $suggested_array = array(
                    "VERSION" => $current_vers,
                    "MOD_DL"  => $download_url
                );
                return $suggested_array;
            
            }else{

                return false;
            
            }

        }else{

            return false;
        
        }

    }

    public function updates_check(){
        global $dbh, $postvar, $getvar, $instance;
		
        //Let's gather the intel
        
        $THT_updates = main::latest_version();
        $THT_version = $dbh->config("version");

        //Are we using the latest THT?
        if($THT_updates['THT'] != $THT_version && !empty($THT_updates)){

            $tht_updates_text = "<a href = '".$THT_updates['THT_DL']."' target = '_blank'><font color = '#FF7800'>New version available! - Download THT ".$THT_updates['THT']." now</font></a>";

        }

        if(!$tht_updates_text){

            if(empty($THT_updates)){

                $tht_updates_text = "Error while retrieving update URL";
            
            }else{

                $tht_updates_text = "<font color = '#779500'>Up to date</font>";
            
            }

        }

        $tht_updates_text = "<b>THT ".$THT_version."</b> (".$tht_updates_text.")<br><br>\n\n";
        
        $mods_query = $dbh->select("automod_mods", 0, array("mod_name", "ASC"));
        while($mods_data = $dbh->fetch_array($mods_query)){

            $mod_info          = self::module_data($mods_data['id']);
            $mod_name          = $mod_info['mod_name'];
            $installed_version = $mod_info['mod_version'];
            $fortht_version    = $mod_info['mod_thtversion'];
            $updates_url       = $mod_info['mod_updateurl'];
            $current_mod       = self::go_fetch($updates_url);
            
            if(!empty($current_mod)){

                if($installed_version == $current_mod['VERSION']){

                    $update_text = "<font color = '#779500'>Up to date</font>";
                
                }else{

                    $update_text = "<a href = '".$current_mod['MOD_DL']."' target = '_blank'><font color = '#FF7800'>Download New Version</font></a>";
                
                }

            }else{

                if(empty($updates_url)){

                    $update_text = "This module does not supply update information";
                
                }else{

                    $update_text = "Error while retrieving update URL";
                
                }

            }

            $mod_updates_text .= "<tr>
                                   <td><b>".$mod_name."</b></td>
                                   <td>".$installed_version."</td>
                                   <td>".$fortht_version."</td>
                                   <td>".$update_text."</td>
                                  </tr>";
            $pulled_a_mod = "1";
        
        }

        if(!$pulled_a_mod){

            $mod_updates_text .= "<tr>
                                   <td colspan = '4' align = 'center'>No modules added.</td>
                                  </tr>";
        
        }

        //Let's display the results.
        echo "<b><font size = '2' color = '#0E7EB6'>THT Updates:</font></b><br><br>".$tht_updates_text;
        echo "<b><font size = '2' color = '#0E7EB6'>Module Updates:</font></b><br><br>
        <table class = 'text' border = '1' bordercolor = '#888888' width = '99%' cellpadding = '3' style = 'border-collapse:collapse;'>
         <tr bgcolor = 'EEEEEE'>
          <td align = 'center'>Module</td>
          <td align = 'center'>Mod Version</td>
          <td align = 'center'>For THT version</td>
          <td align = 'center'>Updates</td>
         </tr>
         ".$mod_updates_text."
        </table>";
    
    }

    public function processaddmod(){
        
        echo "<b>Upload a new module.</b><br><br>";
        
		?>
        <form enctype="multipart/form-data" action="" method="POST">
        Upload Your Module (modulename.zip): <input name="zip" type="file" /><input type="submit" value="Upload" class="button" />
        </form>
        <?PHP
        
        $upload_dir = INC.'/automod'; //your upload directory NOTE: Make it writable
        
        if(isset($_FILES['zip'])){

            $filename = $_FILES['zip']['name']; //the filename
            
            $zip_dir = basename($filename, ".zip"); //get filename without extension for directory creation
            if(is_dir($upload_dir."/".$zip_dir)){

                echo "<br><font color = 'FF0055'>The directory for that module already exists.  If this is the same module or a different version, please uninstall the old module before installing the new one.</font><br>";
            
            }else{

                require_once(INC.'/pclzip.lib.php'); //include class
                
                @mkdir($upload_dir."/".$zip_dir);
                main::perms($upload_dir."/".$zip_dir, 0777);
                
                //move file
                if(move_uploaded_file($_FILES['zip']['tmp_name'], $upload_dir.'/'.$zip_dir.'/'.$filename))
                    echo "<br><br>Uploaded ".$filename." - ".$_FILES['zip']['size']." bytes<br />";
                else
                    die("<font color='red'>Error : Unable to upload file</font><br />");
                
                //unzip
                
                $archive = new PclZip($upload_dir."/".$zip_dir.'/'.$filename);
                
                if($archive->extract(PCLZIP_OPT_PATH, $upload_dir.'/'.$zip_dir) == 0)
                    die("<font color='red'>Error : Unable to unzip archive</font>");
                
                //show what was just extracted
                $list = $archive->listContent();
                echo "<br /><b>Files extracted from archive</b><br />";
                for($i = 0; $i < sizeof($list); $i++){

                    if(!$list[$i]['folder'])
                        $bytes = " - ".$list[$i]['size']." bytes";
                    else
                        $bytes = "";
                    
                    echo $list[$i]['filename']."$bytes<br />";
                
                }

                unlink($upload_dir.'/'.$zip_dir.'/'.$filename); //delete uploaded file
                
                if(!file_exists($upload_dir.'/'.$zip_dir."/install.xml")){

                    echo "<br><b><font size = '3'>The <font color = '#FF0055'>install.xml</font> file does not exist.  Make sure that the module archive's contents are not in a subdirectory in the archive.  The module has been removed from the system.</font></b><br>";
                    self::rmfulldir($upload_dir.'/'.$zip_dir);
                
                }else{

                    self::rmfulldir($upload_dir.'/'.$zip_dir."/contrib");
                
                }

            }

        }

        $handle = opendir($upload_dir);
        if($handle){

            while(false !== ($item = readdir($handle))){

                if($item != '.' && $item != '..'){

                    $mod_path = $upload_dir.'/'.$item;
                    if(is_dir($mod_path)){

                        if(file_exists($mod_path."/install.xml")){

                            $mod_installed = self::module_data($item);
                            if(empty($mod_installed['id'])){

                                $xml_array = self::get_mod_xml($mod_path."/install.xml");
                                if(!$xml_array){

                                    echo "<br><br><b><font size = '3'>The <font color = '#FF0055'>".$mod_path."/install.xml</font> file is not valid.  Extract the archive on your computer and review the file.  FireFox will show the errors if you use FireFox to open it up.  The module has been removed as it was not valid.</font></b>";
                                    self::rmfulldir($upload_dir.'/'.$zip_dir);
                                
                                }else{

                                    $install_modules_list_array['ID']   = urlencode($item);
                                    $install_modules_list_array['NAME'] = nl2br(htmlentities($xml_array['header']['projname']));
                                    unset($elipses);
                                    if(strlen($xml_array['header']['description']) > 250){

                                        $elipses = " <b>...</b>";
                                    
                                    }

                                    $install_modules_list_array['DESCRIPTION'] = nl2br(htmlentities(substr($xml_array['header']['description'], 0, 250)).$elipses);
                                    $output .= style::replaceVar("tpl/automod/install-modules-list.tpl", $install_modules_list_array);
                                    $show_mods = '1';
                                
                                }

                            }

                        }

                    }

                }

            }

        }

        if($show_mods != '1'){

            echo "<br><hr noshade color = '#151515'><br><b>There are no modules awaiting installation.</b>";
        
        }else{

            echo "<br><hr noshade color = '#151515'><br><b>Modules awaiting installation.</b><br><br>".$output;
        
        }

    }

    //This will take array([5] => "test1", [4] => "test2", [9] => "test3") into array([0] => "test1", [1] => "test2", [2] => "test3") so you can access it easier.
    public function normalize_array($array){

        if(is_array($array)){

            $newarray   = array();
            $array_keys = array_keys($array);
            $i          = 0;
            foreach($array_keys as $key){

                if(is_numeric($key)){

                    $newarray[$i] = $array[$key];
                    
                    $i++;
                
                }else{

                    $newarray = $array;
                
                }

            }

            return $newarray;
        
        }else{

            return $array;
        
        }

    }

    public function uninstall_mod($mod_id){
        global $dbh, $postvar, $getvar, $instance;
        
        if(is_numeric($mod_id)){

            $mod_vals = self::module_data($mod_id);
            if(!empty($mod_vals['mod_install_dir'])){

                $mod_dir_full = INC."/automod/".$mod_vals['mod_install_dir'];
                $mod_xml_file = $mod_dir_full."/install.xml";
                
                if(is_file($mod_xml_file)){

                    //Let's set some variables - First the basics.
                    $module_data  = self::get_mod_xml($mod_xml_file);
                    $header_data  = $module_data['header'];
                    $actions_data = $module_data['action-group'];
                    $author_data  = $module_data['header']['author-group']['author'];
                    
                    //Values that every module should have.
                    $mod_name        = htmlentities($header_data['projname']);
                    $mod_desc        = htmlentities($header_data['description']);
                    $mod_license     = htmlentities($header_data['license']);
                    $mod_version     = htmlentities($header_data['mod-version']);
                    $mod_author      = htmlentities($author_data['realname']);
                    $mod_homepage    = str_replace("&amp;", "&", htmlspecialchars($author_data['homepage']));
                    $mod_projectpage = str_replace("&amp;", "&", htmlspecialchars($author_data['projectpage']));
                    $mod_support     = htmlentities($author_data['support']);
                    $mod_thtversion  = htmlentities($author_data['thtversion']);
                    
                    //Values that many modules have
                    $mod_sql           = $actions_data['sql'];
                    $mod_uninstallsql  = $actions_data['uninstallsql'];
                    $mod_after_install = $actions_data['diy-instructions'];
                    $mod_edits         = $actions_data['open'];

                    $THT_version = $dbh->config("version");

                    $mod_thtversion = str_replace("reworked", "Reworked", strtolower($mod_thtversion));
                    
                    //Let the games begin!  =)
                    $warnings = array();
                    
                    //Do we have any <uninstallsql> queries?  If not, we need to show the user the <sql> queries that were made so they can remove them if they wish to if queries were made during installation.
                    if(empty($mod_uninstallsql) && !empty($mod_sql)){

                        $errors_list_array['ERRWARN']     = "WARNING";
                        $errors_list_array['ERRCOLOR']    = "FF7800";
                        $errors_list_array['TITLE']       = "No uninstall SQL";
                        $errors_list_array['DESCRIPTION'] = "<br><br><font color = '#FF7800'><b>WARNING: </b></font> The module does not contain any uninstall queries to run, but it specified queries to run during installation.  Below are the queries run during installation, so you can remove them manually if you wish to.<br><br><textarea cols = '87' rows = '10'>".$mod_sql."</textarea>";
                        $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
                    
                    }

                    //Let's see if any THT files will be overwritten by the installer so we can notify the user.
                    $any_replaced_files = self::RecursiveCopy($mod_dir_full."/root", "..", true, true);
                    if(!empty($any_replaced_files)){

                        $any_replaced_files[0] = str_replace("../", "", $any_replaced_files[0]);
                        $any_replaced_files[1] = str_replace("../", "", $any_replaced_files[1]);
                        $errors_list_array['ERRWARN']    = "NOTICE";
                        $errors_list_array['ERRCOLOR']   = "FF7800";
                        $errors_list_array['TITLE']      = "Some THT files were added/replaced during installation";
                        
                        if(!empty($any_replaced_files[0]) && !empty($any_replaced_files[1])){

                            $show_form              = '1';
                            $errors_list_array['DESCRIPTION'] = "<br><hr noshade color = '#000000'><br><font color = '#FF7800'><b>WARNING: </b></font> Some files were added or replaced during installation.  AutoMod can rename the old files from FILENAME_old.EXT back to their original filename, for your convienience.
                                                           AutoMod can also remove the files the module installed as well if you'd like.  Note that other modules you've installed might depend on the files being as they are.  Verify that no other modules are using these files before you proceed.  Check the boxes
                                                           below if you'd like to perform either of these operations.<br><br><b>Files to be removed:</b><br><br>".nl2br($any_replaced_files[1])."<br><br><font size = '3' color = '#0E7EB6'><strong>Remove the files?: </strong></font><input type = 'checkbox' value = '1' name = 'remove' id = 'remove' checked><br><hr noshade color = '#000000'><br>
                                                           <b>Files to be renamed back to their original name:</b><br><br><table border = '0' width = '100%'>".nl2br($any_replaced_files[0])."</table><br><br><font size = '3' color = '#0E7EB6'><strong>Rename the files?: </strong></font><input type = 'checkbox' value = '1' name = 'rename' id = 'rename' checked><br>";
                            
                        }elseif(!empty($any_replaced_files[0])){

                            $show_form              = '1';
                            $errors_list_array['DESCRIPTION'] = "<br><hr noshade color = '#000000'><br><font color = '#FF7800'><b>WARNING: </b></font> Some files were replaced during installation.  AutoMod can rename the old files from FILENAME_old.EXT back to their original filename, for your convienience.  Note that other modules you've installed might depend on the files being as they are.  Verify that no other modules are using these files before you proceed.  Check the box below if you'd like to perform this operation.<br><br><b>Files to be renamed back to their original name:</b><br><br><table border = '0' width = '100%'>".nl2br($any_replaced_files[0])."</table><br><br><font size = '3' color = '#0E7EB6'><strong>Rename the files?: </strong></font><input type = 'checkbox' value = '1' name = 'rename' id = 'rename' checked><br>";
                            
                        }elseif(!empty($any_replaced_files[1])){

                            $show_form              = '1';
                            $errors_list_array['DESCRIPTION'] = "<br><hr noshade color = '#000000'><br><font color = '#FF7800'><b>WARNING: </b></font> Some files were added during installation.  AutoMod can remove the files the module installed if you'd like.  Note that other modules you've installed might depend on the files being as they are.  Verify that no other modules are using these files before you proceed.  Check the box below if you'd like to perform this operation.<br><br><b>Files to be removed:</b><br><br>".nl2br($any_replaced_files[1])."<br><br><font size = '3' color = '#0E7EB6'><strong>Remove the files?: </strong></font><input type = 'checkbox' value = '1' name = 'remove' id = 'remove' checked><br>";
                            
                        }

                        $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
                    
                    }

                    $file_errors    = self::modify_files($mod_edits, true, true, $mod_vals['mod_install_dir']);
                    $err_fileerrors = $file_errors['fileerrors'];
                    
                    if($err_fileerrors){

                        $err_fileerrors    = self::normalize_array($err_fileerrors);
                        $file_errors_count = count($err_fileerrors);
                        for($i = 0; $i < $file_errors_count; $i++){

                            $file_missing = $err_fileerrors[$i]['filename'];
                            
                            if($file_missing){

                                $err_descrip .= "<br><br><hr noshade color = '#000000'><br><font color = '#FF0055'><b>Cannot find file: </b>".$file_missing."</font>";
                            
                            }

                            $err_fileerrors_error       = $err_fileerrors[$i]['errors'];
                            $err_fileerrors_error       = self::normalize_array($err_fileerrors_error);
                            $err_fileerrors_error_count = count($err_fileerrors_error);
                            
                            $theme_section = $err_fileerrors[$i]['themefile'];
                            
                            if($theme_section){

                                unset($themereplace);
                                unset($themeaddafter);
                                unset($themeaddbefore);
                                
                                $themefile      = $theme_section['file'];
                                $themefind      = $theme_section['themefind'];
                                $themereplace   = $theme_section['themereplace'];
                                $themeaddafter  = $theme_section['themeaddafter'];
                                $themeaddbefore = $theme_section['themeaddbefore'];
                                
                                if($themereplace){

                                    $base_text         = "<br><br><b>In file: </b>".$themefile."<br><b>Find: </b><br><br><textarea cols = '87' rows = '10'>".$themereplace."</textarea><br><br>";
                                    $modification_text = $base_text."<b>Replace it with: </b><br><br><textarea cols = '87' rows = '10'>".$themefind."</textarea><br><hr noshade color = '#000000'>";
                                
                                }elseif($themeaddafter){

                                    $base_text         = "<br><br><b>In file: </b>".$themefile."<br><b>Find: </b><br><br><textarea cols = '87' rows = '10'>".$themeaddafter."</textarea><br><br>";
                                    $modification_text = $base_text."<b>Remove it</b>";
                                
                                }else{

                                    $base_text         = "<br><br><b>In file: </b>".$themefile."<br><b>Find: </b><br><br><textarea cols = '87' rows = '10'>".$themeaddbefore."</textarea><br><br>";
                                    $modification_text = $base_text."<b>Remove it</b>";
                                
                                }

                            }

                            for($n = 0; $n < $err_fileerrors_error_count; $n++){

                                $err_descrip .= $err_fileerrors_error[$n];
                            
                            }

                        }

                        if($err_descrip){

                            $errors_list_array['ERRWARN']     = "ERROR";
                            $errors_list_array['ERRCOLOR']    = "FF0000";
                            $errors_list_array['TITLE']       = "File modification";
                            $errors_list_array['DESCRIPTION'] = $err_descrip."<br><br><hr noshade color = '#000000'>";
                            $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
                            
                        }

                        if($modification_text){

                            $errors_list_array['ERRWARN']     = "WARNING";
                            $errors_list_array['ERRCOLOR']    = "FF7800";
                            $errors_list_array['TITLE']       = "Theme modification";
                            $errors_list_array['DESCRIPTION'] = "<hr noshade color = '#000000'><br><font color = '#FF0055'><b>Attention: </b>This module modified a theme.  Below are the following modifications performed during the uninstall to the themes to help you manually modify any other themes you wish to use.</font>".$modification_text;
                            $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
                        
                        }

                    }

                    if(empty($output)){

                        $output = "<font color = '#779500'>No errors found!</font>";
                    
                    }

                    if($show_form){

                        $form_pre       = "<form name = 'UninstallForm' method = 'POST' action = '?page=automod&sub=uninstall&uninstall=".$mod_id."&confirm=1'>";
                        $form_post      = "</form>";
                        $uninstall_link = "javascript:document.UninstallForm.submit();";
                    
                    }else{

                        $uninstall_link = "?page=automod&sub=uninstall&uninstall=".$mod_id."&confirm=1";
                    
                    }

                    $pre_uninstall_array['NAME']               = $mod_name;
                    $pre_uninstall_array['VERSION']            = $THT_version;
                    $pre_uninstall_array['THTVERSION']         = $mod_thtversion;
                    $pre_uninstall_array['MODVERSION']         = $mod_version;
                    $pre_uninstall_array['LICENSE']            = $mod_license;
                    $pre_uninstall_array['AUTHOR']             = $mod_author;
                    $pre_uninstall_array['SUPPORT']            = $mod_support;
                    $pre_uninstall_array['AUTHLINK']           = $mod_homepage;
                    $pre_uninstall_array['PROJWEB']            = $mod_projectpage;
                    $pre_uninstall_array['DESCRIPTION']        = nl2br($mod_desc);
                    $pre_uninstall_array['DIY']                = $mod_after_install;
                    $pre_uninstall_array['ID']                 = $mod_id;
                    $pre_uninstall_array['UNINSTALLLINK']      = $uninstall_link;
                    $pre_uninstall_array['PREUNINSTALLERRORS'] = $output;
                    
                    echo $form_pre.style::replaceVar("tpl/automod/pre-uninstall.tpl", $pre_uninstall_array).$form_post;
                    
                }else{

                    echo "The module's directory does not exist.  It needs to be in place for the uninstallation to work.  You can upload the module through the installation page and then come back to this page to uninstall it if you removed it on accident.";
                
                }

            }else{

                echo "The DB does not contain the module's directory information.";
            
            }

        }

    }

    public function install_mod($mod_dir){
        global $dbh, $postvar, $getvar, $instance;
        
        if(self::is_mod_installed($mod_dir)){

            echo "This module is already installed.  Please uninstall the module first before reinstalling it.";
            return;
        
        }

        $mod_dir_full = INC."/automod/".$mod_dir;
        $mod_xml_file = $mod_dir_full."/install.xml";
        
        if(is_file($mod_xml_file)){

            //Let's set some variables - First the basics.
            $module_data  = self::get_mod_xml($mod_xml_file);
            $header_data  = $module_data['header'];
            $actions_data = $module_data['action-group'];
            $author_data  = $module_data['header']['author-group']['author'];
            
            //Values that every module should have.
            $mod_name        = htmlentities($header_data['projname']);
            $mod_desc        = htmlentities($header_data['description']);
            $mod_license     = htmlentities($header_data['license']);
            $mod_version     = htmlentities($header_data['mod-version']);
            $mod_author      = htmlentities($author_data['realname']);
            $mod_homepage    = str_replace("&amp;", "&", htmlspecialchars($author_data['homepage']));
            $mod_projectpage = str_replace("&amp;", "&", htmlspecialchars($author_data['projectpage']));
            $mod_support     = htmlentities($author_data['support']);
            $mod_thtversion  = htmlentities($author_data['thtversion']);
            
            //Values that many modules have
            $mod_sql           = $actions_data['sql'];
            $mod_uninstallsql  = $actions_data['uninstallsql']; //We should check this to see if the user can remove the database entries made by the module and notify them if the uninstall SQL info isn't available.
            $mod_after_install = $actions_data['diy-instructions'];
            $mod_edits         = $actions_data['open'];
            
            //Versions
            $latest_versions = main::latest_version();
            $THT_version     = $dbh->config("version");

            $mod_thtversion = str_replace("reworked", "Reworked", strtolower($mod_thtversion));
            
            //Let the games begin!  =)
            $warnings = array();
            
            //See if they are using a dynamic admin directory and if so, rename the admin directory before installing.
            if(ADMINDIR && ADMINDIR != 'admin'){

                @rename($mod_dir_full."/root/admin", $mod_dir_full."/root/".ADMINDIR);
            
            }

            //THT version check
            if($THT_version != $mod_thtversion){

                $mod_thtversion_array = explode(" ", $mod_thtversion);
                $mod_thtversion_vers   = $mod_thtversion_array[0];
                $standard_warn         = "<br><br>The THT version installed is different from what this module was developed for.  The installation will be further checked, but the installer cannot guarantee that the module will work properly on your installation of THT.<br><br><b>THT Version Installed: </b>".$THT_version."<br><b>THT Version Suggested: </b>".$mod_thtversion;
                $special_warning       = "<br><br>The THT version installed may or may not be the same as the one this module is made for.  The one this module was designed for is a special version of THT.<br><br><b>THT Version Installed: </b>".$THT_version."<br><b>THT Version Suggested: </b>".$mod_thtversion;
                
                if($mod_thtversion_array[1]){

                    $mod_thtversion_special = str_replace($mod_thtversion_vers." ", "", $mod_thtversion);
                    
                    if($mod_thtversion_vers == $THT_version){

                        if($mod_thtversion != "Reworked"){

                            $warnings['version'] = $special_warning;
                        
                        }

                    }else{

                        $warnings['version'] = $standard_warn;
                    
                    }

                }else{

                    $warnings['version'] = $standard_warn;
                
                }

            }

            if(empty($latest_versions['THT_DL'])){

                $latest_versions['THT_DL'] = "Download temporarily unavailable";
            
            }

            if($warnings['version'] && $mod_thtversion == "1.3 Reworked"){
				
				//The mod was made for Reworked.  Let's add a shameless plug.  ;)  If there's a newer release available, it'll show them the link to that one instead.
                $warnings['version'] .= "<br><b>THT 1.3 Reworked can be downloaded at: </b><a href = '".$latest_versions['THT_DL']."' target = '_blank'>".$latest_versions['THT_DL']."</a>.";
            
            }
        
			//Are we using the latest THT?
			if($latest_versions['THT'] != $THT_version && !empty($latest_versions)){

				$recommendations = "<font color = '#FF7800'><b>RECOMMENDATION: </b></font>It's always a good idea to have the most up to date version of THT.  The latest stable version of THT is THT ".$latest_versions['THT']." and can be downloaded at <a href = '".$latest_versions['THT_DL']."' target = '_blank'>".$latest_versions['THT_DL']."</a>.<br>";
            
			}else{

				$recommendations = "<font color = '#779500'>Everything is up to date!</font>";
        
			}
            
            //If we have SQL, do we also have Uninstall SQL?
            if(!empty($mod_sql)){

                if(empty($mod_uninstallsql)){

                    $warnings['uninstall_sql'] = "<br><br>This module runs the following queries when it installs, but it does not have a way to remove them automatically.  Please make a note of these in case you decide to uninstall this module.  You'll be shown these same installation queries when you uninstall the module if you decide to.<br><br><b>SQL:</b><br><textarea cols = '87' rows = '10'>".$mod_sql."</textarea>"; //The last part makes <PRE> show properly on the page.
                
                }

            }

            //Now we need to check if the file modifications are possible on this installation of THT.  Let's do a test run and see if we can find everything it says to find.
            $file_errors            = self::modify_files($mod_edits, true, false, $mod_dir);
            $warnings['fileerrors'] = $file_errors['fileerrors'];
            
            //The warnings are done now.  Now we print the results and offer them the ability confirm or reject the installation.
            
            //Let's see if any THT files will be overwritten by the installer so we can notify the user.
            $any_replaced_files = self::RecursiveCopy($mod_dir_full."/root", "..", true);
            if(!empty($any_replaced_files)){

                $any_replaced_files     = str_replace("../", "", $any_replaced_files);
                $errors_list_array['ERRWARN']     = "WARNING";
                $errors_list_array['ERRCOLOR']    = "FF7800";
                $errors_list_array['TITLE']       = "Some THT files will be replaced";
                $errors_list_array['DESCRIPTION'] = "<br><hr noshade color = '#000000'><br><font color = '#FF7800'><b>WARNING: </b></font> While this might be perfectly fine, you should be aware that the following files will be overwritten by this module.  Please be sure to back up your website before you proceed with the installation.  AutoMod will rename the old files to FILENAME_old.EXT for your convienience.<br><br><b>Files to be overwritten with new content:</b><br><br>".nl2br($any_replaced_files)."<br>";
                $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
            
            }

            //Selection of errors that we collected while processing this mod
            $err_version       = $warnings['version'];
            $err_uninstall_sql = $warnings['uninstall_sql'];
            $err_fileerrors    = $warnings['fileerrors'];
            
            if($err_version){

                $mod_thtversion_color   = "<font color = '#FF0055'>".$THT_version."</font>";
                $errors_list_array['ERRWARN']     = "WARNING";
                $errors_list_array['ERRCOLOR']    = "FF7800";
                $errors_list_array['TITLE']       = "Version Mismatch";
                $errors_list_array['DESCRIPTION'] = $err_version;
                $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
            
            }else{

                $mod_thtversion_color = "<font color = '#779500'>".$THT_version."</font>";
            
            }

            if($err_uninstall_sql){

                $errors_list_array['ERRWARN']     = "WARNING";
                $errors_list_array['ERRCOLOR']    = "FF7800";
                $errors_list_array['TITLE']       = "No SQL uninstaller";
                $errors_list_array['DESCRIPTION'] = $err_uninstall_sql;
                $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
            
            }

            if($err_fileerrors){

                $err_fileerrors    = self::normalize_array($err_fileerrors);
                $file_errors_count = count($err_fileerrors);
                for($i = 0; $i < $file_errors_count; $i++){

                    $file_missing = $err_fileerrors[$i]['filename'];
                    
                    if($file_missing){

                        $err_descrip .= "<br><br><hr noshade color = '#000000'><br><font color = '#FF0055'><b>Cannot find file: </b>".$file_missing."</font>";
                    
                    }

                    $err_fileerrors_error       = $err_fileerrors[$i]['errors'];
                    $err_fileerrors_error       = self::normalize_array($err_fileerrors_error);
                    $err_fileerrors_error_count = count($err_fileerrors_error);
                    
                    $theme_section = $err_fileerrors[$i]['themefile'];
                    
                    if($theme_section){

                        unset($themereplace);
                        unset($themeaddafter);
                        unset($themeaddbefore);
                        
                        $themefile      = $theme_section['file'];
                        $themefind      = $theme_section['themefind'];
                        $themereplace   = $theme_section['themereplace'];
                        $themeaddafter  = $theme_section['themeaddafter'];
                        $themeaddbefore = $theme_section['themeaddbefore'];
                        
                        $base_text = "<br><br><b>In file: </b>".$themefile."<br><b>Find: </b><br><br><textarea cols = '87' rows = '10'>".$themefind."</textarea><br><br>";
                        
                        if($themereplace){

                            $modification_text = $base_text."<b>Replace it with: </b><br><br><textarea cols = '87' rows = '10'>".$themereplace."</textarea><br><hr noshade color = '#000000'>";
                        
                        }elseif($themeaddafter){

                            $modification_text = $base_text."<b>Add after it: </b><br><br><textarea cols = '87' rows = '10'>".$themeaddafter."</textarea><br><hr noshade color = '#000000'>";
                        
                        }else{

                            $modification_text = $base_text."<b>Add before it: </b><br><br><textarea cols = '87' rows = '10'>".$themeaddbefore."</textarea><br><hr noshade color = '#000000'>";
                        
                        }

                    }

                    for($n = 0; $n < $err_fileerrors_error_count; $n++){

                        $err_descrip .= $err_fileerrors_error[$n];
                    
                    }

                }

                if($err_descrip){

                    $errors_list_array['ERRWARN']     = "ERROR";
                    $errors_list_array['ERRCOLOR']    = "FF0000";
                    $errors_list_array['TITLE']       = "File modification";
                    $errors_list_array['DESCRIPTION'] = $err_descrip."<br><br><hr noshade color = '#000000'>";
                    $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
                
                }

                if($modification_text){

                    $errors_list_array['ERRWARN']     = "WARNING";
                    $errors_list_array['ERRCOLOR']    = "FF7800";
                    $errors_list_array['TITLE']       = "Theme modification";
                    $errors_list_array['DESCRIPTION'] = "<hr noshade color = '#000000'><br><font color = '#FF0055'><b>Attention: </b>This module modifies a theme.  Below are the following modifications to the themes to help you manually modify any other themes you wish to use.</font>".$modification_text;
                    $output .= style::replaceVar("tpl/automod/errors-list.tpl", $errors_list_array);
                
                }

            }

            if(empty($output)){

                $output = "<font color = '#779500'>No errors found!</font>";
            
            }

            $pre_install_array['NAME']             = $mod_name;
            $pre_install_array['VERSION']          = $mod_thtversion_color;
            $pre_install_array['THTVERSION']       = $mod_thtversion;
            $pre_install_array['MODVERSION']       = $mod_version;
            $pre_install_array['LICENSE']          = $mod_license;
            $pre_install_array['AUTHOR']           = $mod_author;
            $pre_install_array['SUPPORT']          = $mod_support;
            $pre_install_array['AUTHLINK']         = $mod_homepage;
            $pre_install_array['PROJWEB']          = $mod_projectpage;
            $pre_install_array['RECOMMENDATIONS']  = $recommendations;
            $pre_install_array['DESCRIPTION']      = nl2br($mod_desc);
            $pre_install_array['DIY']              = $mod_after_install;
            $pre_install_array['ID']               = $mod_dir;
            $pre_install_array['PREINSTALLERRORS'] = $output;
            
            echo style::replaceVar("tpl/automod/pre-install.tpl", $pre_install_array);
            
        }else{

            echo "Cannot find the install.xml file.  Please make sure that ".$mod_xml_file." exists.";
        
        }

    }

    public function mysql_scary($sqlquery){
        global $dbh, $postvar, $getvar, $instance;
        
        include(INC."/conf.inc.php");
        $sqlquery = preg_replace("/<PRE>/si", $sql['pre'], $sqlquery);
        $sqlquery = explode(";\n", $sqlquery);
        foreach($sqlquery as $key => $val){

            $dbh->query($val);
        
        }

    }

    public function completeuninstall($mod_id, $mode){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!is_numeric($mod_id)){

            echo "Module ID is missing.";
            return;
        
        }

        $mod_db  = self::module_data($mod_id);
        $mod_dir = $mod_db['mod_install_dir'];
        if(!$mod_dir){

            echo "This module is not installed.  Please uninstall the module first before reinstalling it.";
            return;
        
        }

        $mod_dir_full = INC."/automod/".$mod_dir;
        $mod_files    = INC."/automod/".$mod_dir."/root";
        $mod_xml_file = $mod_dir_full."/install.xml";
        
        if(is_file($mod_xml_file)){

            //Let's set some variables - First the basics.
            $module_data  = self::get_mod_xml($mod_xml_file);
            $actions_data = $module_data['action-group'];
            
            //Values that many modules have
            $mod_uninstallsql = $actions_data['uninstallsql'];
            $mod_edits        = $actions_data['open'];
            
            //Remove and rename the files from the module.
            
            //$mode
            //1 = Skip remove/rename
            //2 = Remove/rename
            //3 = Remove
            //4 = Rename
            
            if($mode != '1'){

                self::RecursiveCopy($mod_files, "..", false, true, $mode);
            
            }

            //Uninstall the SQL
            if($mod_uninstallsql){

                self::mysql_scary($mod_uninstallsql);
            
            }

            //Edit the files
            self::modify_files($mod_edits, false, true, $mod_dir);
            
            //Remove the mod from the AutoMod table.
            $dbh->delete("automod_mods", array("id", "=", $mod_id), "1");
            
            //Let's clean house.  =)
            self::rmfulldir($mod_dir_full);
            
            //Redirect the user to the view mod page.
            main::redirect("?page=automod&sub=added");
            
        }else{

            echo "Cannot find the install.xml file.  Please make sure that ".$mod_xml_file." exists.";
        
        }

    }

    public function completeinstall($mod_dir){
        global $dbh, $postvar, $getvar, $instance;
        
        if($mod_dir){

            if(self::is_mod_installed($mod_dir)){

                echo "This module is already installed.  Please uninstall the module first before reinstalling it.";
                return;
            
            }

            $mod_dir_full = INC."/automod/".$mod_dir;
            $mod_files    = INC."/automod/".$mod_dir."/root";
            $mod_xml_file = $mod_dir_full."/install.xml";
            
            if(is_file($mod_xml_file)){

                //Let's set some variables - First the basics.
                $module_data  = self::get_mod_xml($mod_xml_file);
                $header_data  = $module_data['header'];
                $actions_data = $module_data['action-group'];
                $author_data  = $module_data['header']['author-group']['author'];
                
                //Values that every module should have.
                $mod_name        = $header_data['projname'];
                $mod_desc        = $header_data['description'];
                $mod_license     = $header_data['license'];
                $mod_version     = $header_data['mod-version'];
                $mod_author      = $author_data['realname'];
                $mod_homepage    = $author_data['homepage'];
                $mod_projectpage = $author_data['projectpage'];
                $mod_support     = $author_data['support'];
                $mod_thtversion  = $author_data['thtversion'];
                $mod_updateurl   = htmlentities($author_data['updateurl']);
                
                //Values that many modules have
                $mod_sql           = $actions_data['sql'];
                $mod_uninstallsql  = $actions_data['uninstallsql']; //We should check this to see if the user can remove the database entries made by the module and notify them if the uninstall SQL info isn't available.
                $mod_after_install = $actions_data['diy-instructions'];
                $mod_edits         = $actions_data['open'];
                
                //Copy the root directory to the THT file structure.
                self::RecursiveCopy($mod_files, "..");
                
                //Install the SQL
                self::mysql_scary($mod_sql);
                
                //Edit the files
                self::modify_files($mod_edits, false, false, $mod_dir);
                
                //Add the mod to the AutoMod table.
                $automod_mods_insert = array(
                    "mod_install_dir" => $mod_dir,
                    "mod_name"        => $mod_name,
                    "mod_version"     => $mod_version,
                    "mod_thtversion"  => $mod_thtversion,
                    "mod_descrip"     => $mod_desc,
                    "mod_author"      => $mod_author,
                    "mod_link"        => $mod_homepage,
                    "mod_projectpage" => $mod_projectpage,
                    "mod_support"     => $mod_support,
                    "mod_license"     => $mod_license,
                    "mod_diy"         => $mod_after_install,
                    "mod_updateurl"   => $mod_updateurl
                );
                $dbh->insert("automod_mods", $automod_mods_insert);
                
                //Redirect the user to the view mod page.
                $mod_data = self::module_data($mod_dir);
                main::redirect("?page=automod&sub=added&view=".$mod_data['id']);
                
            }else{

                echo "Cannot find the install.xml file.  Please make sure that ".$mod_xml_file." exists.";
            
            }

        }

    }

    public function is_mod_installed($mod_dir){
        global $dbh, $postvar, $getvar, $instance;
		
        $automod_mods_query = $dbh->select("automod_mods", array("mod_install_dir", "=", $mod_dir), 0, "1", 1);
        $automod_mods_rows  = $dbh->num_rows($automod_mods_query);
        if($automod_mods_rows == 0){

            return false;
        
        }else{

            return true;
        
        }

    }

    //$mode
    //2 = Remove/rename
    //3 = Remove
    //4 = Rename
    
    public function RecursiveCopy($source, $dest, $test_run = false, $reverse = false, $mode = "", $collected = ""){

        $sourceHandle = @opendir($source);
        
        while($res = @readdir($sourceHandle)){

            if($res == '.' || $res == '..')
                continue;
            
            if(is_dir($source.'/'.$res)){

                if(!is_dir($dest.'/'.$res)){

                    if(!$test_run){

                        @mkdir($dest.'/'.$res);
                    
                    }

                }

                $collected = self::RecursiveCopy($source.'/'.$res, $dest.'/'.$res, $test_run, $reverse, $mode, $collected);
            
            }else{

                if($test_run){

                    if(file_exists($dest.'/'.$res)){

                        if($reverse){

                            $file_array     = explode("/", $res);
                            $filename        = $file_array[count($file_array) - 1];
                            $file_array2    = explode(".", $filename);
                            $file_ext        = $file_array2[count($file_array2) - 1];
                            $backup_file     = str_replace(".".$file_ext, "", $filename)."_OLD.".$file_ext;
                            $backup_file_res = str_replace($filename, "", $res);
                            
                            $collectedreplaced = $collected[0];
                            $collectedadded    = $collected[1];
                            
                            if(file_exists($dest.'/'.$backup_file_res.$backup_file)){

                                $collectedreplaced .= "<tr><td width = '50%'>".$dest.'/'.$backup_file_res.$backup_file."</td><td> -> ".$dest.'/'.$res."</td></tr>";
                            
                            }else{

                                $collectedadded .= $dest.'/'.$res."\n";
                            
                            }

                            $collected = array($collectedreplaced, $collectedadded);
                        
                        }else{

                            $collected .= $dest.'/'.$res."\n";
                        
                        }

                    }

                }else{

                    if($reverse){

                        if($mode == 2 || $mode == 3){

                            $remfiles = '1';
                        
                        }

                        if($mode == 2 || $mode == 4){

                            $renamefiles = '1';
                        
                        }

                        $file_array     = explode("/", $res);
                        $filename        = $file_array[count($file_array) - 1];
                        $file_array2    = explode(".", $filename);
                        $file_ext        = $file_array2[count($file_array2) - 1];
                        $backup_file     = str_replace(".".$file_ext, "", $filename)."_OLD.".$file_ext; //Note: This is the safer way to backup the file.  By giving it the same extention instead of giving it the filename and adding _OLD to the end, we can avoid people downloading the files that were backed up by visiting them in their browser.
                        $backup_file_res = str_replace($filename, "", $res);
                        
                        if(file_exists($dest.'/'.$backup_file_res.$backup_file)){

                            if($renamefiles){

                                unlink($dest.'/'.$res);
                                rename($dest.'/'.$backup_file_res.$backup_file, $dest.'/'.$res);
                            
                            }

                        }else{

                            if($remfiles){

                                unlink($dest.'/'.$res);
                            
                            }

                        }

                    }else{

                        if(file_exists($dest.'/'.$res)){

                            $file_array     = explode("/", $res);
                            $filename        = $file_array[count($file_array) - 1];
                            $file_array2    = explode(".", $filename);
                            $file_ext        = $file_array2[count($file_array2) - 1];
                            $backup_file     = str_replace(".".$file_ext, "", $filename)."_OLD.".$file_ext; //Note: This is the safer way to backup the file.  By giving it the same extention instead of giving it the filename and adding _OLD to the end, we can avoid people downloading the files that were backed up by visiting them in their browser.
                            $backup_file_res = str_replace($filename, "", $res);
                            copy($dest.'/'.$res, $dest.'/'.$backup_file_res.$backup_file);
                        
                        }

                        copy($source.'/'.$res, $dest.'/'.$res);
                    
                    }

                }

            }

        }

        if($test_run && $collected){

            return $collected;
        
        }

    }

    public function modify_files($modifications_array, $test_run = false, $reverse = false, $mod_dir = ""){

        $files_to_edit = count($modifications_array);
        if($files_to_edit != 0){

            if($modifications_array['src']){

                $files_to_edit = '1';
            
            }else{

                $modifications_array = self::normalize_array($modifications_array);
            
            }

            for($i = 0; $i < $files_to_edit; $i++){

                $filename = $modifications_array[$i]['src'];
                if(!$filename){

                    $filename = $modifications_array['src'];
                
                }

                $filename_chk = explode("/", $filename);
                if($filename_chk[0] == "admin" && ADMINDIR && ADMINDIR != 'admin'){

                    $filename_chk[0] = ADMINDIR;
                    $filename        = implode("/", $filename_chk);
                
                }

                $modifications = $modifications_array[$i]['edit'];
                if(empty($modifications)){

                    $modifications = $modifications_array['edit'];
                
                }

                if(!file_exists("../".$filename)){

                    $warnings['fileerrors'][$i]['filename'] = $filename;
                
                }else{

                    if(substr_count($filename, "themes") != 0){

                        $warnings['fileerrors'][$i]['themefile']['file'] = $filename;
                    
                    }

                    $file_contents        = file_get_contents("../".$filename);
                    $file_contents        = str_replace("\r\n", "\n", $file_contents);
                    $modifications        = self::normalize_array($modifications);
                    $num_of_modifications = count($modifications);
                    $errnum               = 0;
                    $replace_code         = 0;
                    for($n = 0; $n < $num_of_modifications; $n++){

                        unset($find);
                        unset($replace);
                        unset($addafter);
                        unset($addbefore);
                        
                        $find      = $modifications[$n]['find'];
                        $replace   = $modifications[$n]['replace'];
                        $addafter  = $modifications[$n]['addafter'];
                        $addbefore = $modifications[$n]['addbefore'];
                        
                        if(empty($find)){

                            $num_of_modifications = 1;
                            $find                 = $modifications['find'];
                            $replace              = $modifications['replace'];
                            $addafter             = $modifications['addafter'];
                            $addbefore            = $modifications['addbefore'];
                        
                        }

                        $file_contents = str_replace("\r\n", "\n", $file_contents);
                        $find          = str_replace("\r\n", "\n", $find);
                        $replace       = str_replace("\r\n", "\n", $replace);
                        $addafter      = str_replace("\r\n", "\n", $addafter);
                        $addbefore     = str_replace("\r\n", "\n", $addbefore);
                        
                        if($replace == "REMOVE IT"){

                            $replace = "\n//REMOVED CODE FROM MODULE: ".$mod_dir." [".$replace_code."]  DO NOT REMOVE\n";
                            $replace_code++;
                        
                        }

                        //We're testing to make sure everything is legit.
                        if($test_run){

                            $base_action_text = "<br><b>[ACTION]</b>[PRE][CODE][POST]";
                            $textarea_pre     = "<br><textarea cols = '87' rows = '10'>";
                            $textarea_post    = "</textarea>";
                            
                            $find_text      = str_replace("<", "&lt;", $find);
                            $replace_text   = str_replace("<", "&lt;", $replace);
                            $addafter_text  = str_replace("<", "&lt;", $addafter);
                            $addbefore_text = str_replace("<", "&lt;", $addbefore);
                            
                            if($reverse){

                                //Since we're in reverse mode, we need to check if we can undo the changes.  (Uninstaller said to)
                                
                                if($warnings['fileerrors'][$i]['themefile']['file']){

                                    $warnings['fileerrors'][$i]['themefile']['themefind'] = $find_text;
                                    if($replace){

                                        $warnings['fileerrors'][$i]['themefile']['themereplace'] = $replace_text;
                                    
                                    }elseif($addafter){

                                        $warnings['fileerrors'][$i]['themefile']['themeaddafter'] = $addafter_text;
                                    
                                    }else{

                                        $warnings['fileerrors'][$i]['themefile']['themeaddbefore'] = $addbefore_text;
                                    
                                    }

                                }

                                if($replace){

                                    $searchfor   = $replace;
                                    $action_text = str_replace("[ACTION]", "Replace it with:", $base_action_text);
                                    $action_text = str_replace("[CODE]", $find_text, $action_text);
                                    $action_text = str_replace("[PRE]", $textarea_pre, $action_text);
                                    $action_text = str_replace("[POST]", $textarea_post, $action_text);
                                
                                }elseif($addafter){

                                    $searchfor   = $addafter;
                                    $action_text = str_replace("[ACTION]", "REMOVE IT", $base_action_text);
                                    $action_text = str_replace("[CODE]", "", $action_text);
                                    $action_text = str_replace("[PRE]", "", $action_text);
                                    $action_text = str_replace("[POST]", "", $action_text);
                                
                                }else{

                                    $searchfor   = $addbefore;
                                    $action_text = str_replace("[ACTION]", "REMOVE IT", $base_action_text);
                                    $action_text = str_replace("[CODE]", "", $action_text);
                                    $action_text = str_replace("[PRE]", "", $action_text);
                                    $action_text = str_replace("[POST]", "", $action_text);
                                
                                }

                                $searchfor_text = str_replace("<", "&lt;", $searchfor);
                                
                                if(substr_count($file_contents, $searchfor) == 0){

                                    $warnings['fileerrors'][$i]['errors'][$errnum] = "<hr noshade color = '#000000'><br><font color = '#FF0055'><b>Error:</b></font> Could not find the following code in the file.<br><br><b>File: </b>".$filename."<br><b>Code:</b><br><textarea cols = '87' rows = '10'>".$searchfor_text."</textarea><br>".$action_text;
                                    $errnum++;
                                
                                }

                            }else{

                                if($warnings['fileerrors'][$i]['themefile']['file']){

                                    $warnings['fileerrors'][$i]['themefile']['themefind'] = $find_text;
                                    if($replace){

                                        $warnings['fileerrors'][$i]['themefile']['themereplace'] = $replace_text;
                                    
                                    }elseif($addafter){

                                        $warnings['fileerrors'][$i]['themefile']['themeaddafter'] = $addafter_text;
                                    
                                    }else{

                                        $warnings['fileerrors'][$i]['themefile']['themeaddbefore'] = $addbefore_text;
                                    
                                    }

                                }

                                if($replace){

                                    $action_text = str_replace("[ACTION]", "Replace it with:", $base_action_text);
                                    $action_text = str_replace("[CODE]", $replace_text, $action_text);
                                    $action_text = str_replace("[PRE]", $textarea_pre, $action_text);
                                    $action_text = str_replace("[POST]", $textarea_post, $action_text);
                                
                                }elseif($addafter){

                                    $action_text = str_replace("[ACTION]", "Add after it:", $base_action_text);
                                    $action_text = str_replace("[CODE]", $addafter_text, $action_text);
                                    $action_text = str_replace("[PRE]", $textarea_pre, $action_text);
                                    $action_text = str_replace("[POST]", $textarea_post, $action_text);
                                
                                }else{

                                    $action_text = str_replace("[ACTION]", "Add before it:", $base_action_text);
                                    $action_text = str_replace("[CODE]", $addbefore_text, $action_text);
                                    $action_text = str_replace("[PRE]", $textarea_pre, $action_text);
                                    $action_text = str_replace("[POST]", $textarea_post, $action_text);
                                
                                }

                                if(substr_count($file_contents, $find) == 0){

                                    $warnings['fileerrors'][$i]['errors'][$errnum] = "<hr noshade color = '#000000'><br><font color = '#FF0055'><b>Error:</b></font> Could not find the following code in the file.<br><br><b>File: </b>".$filename."<br><b>Find:</b><br><textarea cols = '87' rows = '10'>".$find_text."</textarea><br>".$action_text;
                                    $errnum++;
                                
                                }

                                if(substr_count($file_contents, $find) > 1){

                                    $warnings['fileerrors'][$i]['errors'][$errnum] = "<hr noshade color = '#000000'><br><font color = '#FF7800'><b>Warning:</b></font> The following code was found in multiple places in the file.  Proceed with caution.<br><br><b>File: </b>".$filename."<br><b>Find:</b><br><textarea cols = '87' rows = '10'>".$find_text."</textarea><br>".$action_text;
                                    $errnum++;
                                
                                }

                            }

                        }else{

                            //Not the test run, so we need to modify the files.
                            
                            $file_contents = file_get_contents("../".$filename);
                            $file_contents = str_replace("\r\n", "\n", $file_contents);
                            
                            if($reverse){

                                //We need to perform the uninstall here.
                                
                                if($file_contents){

                                    if($replace){

                                        $new_contents = str_replace($replace, $find, $file_contents);
                                    
                                    }elseif($addafter){

                                        $new_contents = str_replace($addafter, "", $file_contents);
                                    
                                    }else{

                                        $new_contents = str_replace($addbefore, "", $file_contents);
                                    
                                    }

                                    file_put_contents("../".$filename, $new_contents);
                                    
                                }

                            }else{

                                if($file_contents){

                                    if($replace){

                                        $new_contents = str_replace($find, $replace, $file_contents);
                                    
                                    }elseif($addafter){

                                        $new_contents = str_replace($find, $find.$addafter, $file_contents);
                                    
                                    }else{

                                        $new_contents = str_replace($find, $addbefore.$find, $file_contents);
                                    
                                    }

                                    file_put_contents("../".$filename, $new_contents);
                                    
                                }

                            }

                        }

                    }

                }

            }

        }

        return $warnings;
    
    }

    public function module_data($id_or_dir){
        global $dbh, $postvar, $getvar, $instance;
        
        if(is_numeric($id_or_dir)){

            $mod_data = $dbh->select("automod_mods", array("id", "=", $id_or_dir), 0, "1");
        
        }else{

            $mod_data = $dbh->select("automod_mods", array("mod_install_dir", "=", $id_or_dir), 0, "1");
        
        }

        return $mod_data;
    
    }

    public function get_mod_xml($xmlfile){

        $xmlfile_contents = file_get_contents($xmlfile);
        try{

            $simplexml = @new SimpleXMLElement($xmlfile_contents);
        
        }

        catch(exception $e){

        }

        if($simplexml){

            $xml_array = self::simpleXMLToarray($simplexml);
            return $xml_array;
        
        }else{

            return false;
        
        }

    }

    //Moreon this function: http://php.net/manual/en/book.simplexml.php
    public function simpleXMLToarray(SimpleXMLElement $xml, $attributesKey = null, $childrenKey = null, $valueKey = null){

        if($attributesKey && !is_string($attributesKey)){

            $attributesKey = '@attributes';
        
        }

        if($childrenKey && !is_string($childrenKey)){

            $childrenKey = '@children';
        
        }

        if($valueKey && !is_string($valueKey)){

            $valueKey = '@values';
        
        }

        $return  = array();
        $name    = $xml->getName();
        $_value  = trim((string ) $xml);
        $_value2 = (string ) $xml;
        if(!strlen($_value)){

            $_value = null;
        
        }

        ;
        
        if($_value !== null){

            if($valueKey){

                $return[$valueKey] = $_value2;
            
            }else{

                $return = $_value2;
            
            }

        }

        $children = array();
        $first    = true;
        foreach($xml->children() as $elementName => $child){

            $value = self::simpleXMLToarray($child, $attributesKey, $childrenKey, $valueKey);
            if(isset($children[$elementName])){

                if(is_array($children[$elementName])){

                    if($first){

                        $temp = $children[$elementName];
                        unset($children[$elementName]);
                        $children[$elementName][] = $temp;
                        $first                    = false;
                    
                    }

                    $children[$elementName][] = $value;
                
                }else{

                    $children[$elementName] = array($children[$elementName], $value);
                
                }

            }else{

                $children[$elementName] = $value;
            
            }

        }

        if($children){

            if($childrenKey){

                $return[$childrenKey] = $children;
            
            }else{

                $return = array_merge($return, $children);
            
            }

        }

        $attributes = array();
        foreach($xml->attributes() as $name => $value){

            $attributes[$name] = $value;
        
        }

        if($attributes){

            if($attributesKey){

                $return[$attributesKey] = $attributes;
            
            }else{

                $return = array_merge($return, $attributes);
            
            }

        }

        return $return;
    
    }

    public function rmfulldir($directory){

        if(substr($directory, -1) == '/'){

            $directory = substr($directory, 0, -1);
        
        }

        if(!file_exists($directory) || !is_dir($directory)){

            return false;
        
        }elseif(!is_readable($directory)){

            return false;
        
        }else{

            $handle = opendir($directory);
            while(false !== ($item = readdir($handle))){

                if($item != '.' && $item != '..'){

                    $path = $directory.'/'.$item;
                    if(is_dir($path)){

                        if(!@rmdir($path)){

                            self::rmfulldir($path);
                        
                        }

                    }else{

                        unlink($path);
                    
                    }

                }

            }

            closedir($handle);
            if(!rmdir($directory)){

                return false;
            
            }

            return true;
        
        }

    }

    public function checkDir($dir){

        if(is_dir($dir)){

            return true;
        
        }else{

            mkdir($dir, 0777);
            if(is_dir($dir)){

                return true;
            
            }else{

                return false;
            
            }

        }

    }

    public function checkPerms($file){

        if(is_writable($file)){

            return true;
        
        }else{

            main::perms($file, 0777);
            if(is_writable($file)){

                return true;
            
            }else{

                main::perms($file, 0777);
                return false;
            
            }

        }

    }

}

//So we can keep module classes in their directories, we initialize all class files found in their directories now.
$folder2 = INC."/modules/".$mod_dir;
if($handle2 = opendir($folder2)){
	
    while(false !== ($file2 = readdir($handle2))){
		
        if($file2 != "." && $file2 != ".."){
			
            if(is_dir($folder2."/".$file2)){

                $mod_dir = $file2;
                if($handle3 = opendir($folder2."/".$mod_dir)){

                    while(false !== ($file3 = readdir($handle3))){

                        if($file3 != "." && $file3 != ".."){

                            $base3 = explode(".", $file3);
                            if($base3[1] == "php"){
								
                                $base4     = explode("_", $base3[0]);
                                $classname = str_replace("class_", "", $base3[0]);
                                if($base4[0] == "class"){

                                    require $folder2.$mod_dir."/".$file3; 
                                    ${$classname} = new $classname;
                                    global ${$classname};
                                
                                }

                            }

                        }

                    }

                    closedir($handle3); 
                
                }

            }

        }

    }

}

closedir($handle2); 

?>