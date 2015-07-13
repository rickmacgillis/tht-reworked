<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Look and Feel
// By: Barrette Galt
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

        $this->navtitle  = "Look and Feel Sub Menu";
        $this->navlist[] = array("Theme Chooser", "layout.png", "tchoose");
        $this->navlist[] = array("Theme Uploader", "layout_add.png", "tupload");
        $this->navlist[] = array("jQuery UI Theme", "palette.png", "ui-theme");
        $this->navlist[] = array("CSS Editor", "css.png", "cssedit");
        $this->navlist[] = array("TPL Editor", "xhtml.png", "tpledit");
        $this->navlist[] = array("NavBar Editor", "link_edit.png", "navedit");
    
    }

    public function description(){

        return "<strong>Look and Feel Administration</strong><br />
                Welcome to the Look and Feel Administration Area. This is where you really make your THT installation your very own. You can change and edit your theme, upload a new one, and even customize your navbar.<br />
                To get started, choose a link from the sidebar's SubMenu.<br />";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;

        switch($getvar['sub']){

            case "tchoose":
		
				if($_POST){

					check::empty_fields();
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);
						
						}

						main::errors("Settings Updated!");
					
					}

				}
			
                $folder = "../themes/";
                if($handle = opendir($folder)){
				
                    while(false !== ($file = readdir($handle))){
					
                        if($file != "." && $file != ".." && $file != ".svn" && $file != "icons" && $file != "index.html" && $file != "flags"){
						
                            $values[] = array($file, $file);
                        
                        }

                    }

                }

                closedir($handle);
                $theme_settings_array['THEME'] = main::dropDown("theme", $values, $dbh->config("theme"));
                echo style::replaceVar("tpl/admin/lof/theme-settings.tpl", $theme_settings_array);
                break;
            
            case "tupload": // Theme Uploader
                
                echo "Here you can upload a theme of your choice to the installer. Please be sure that the theme is in .zip format.<br><br>";
                
                if($_POST){

                    $response = main::upload_theme();
                    echo $response."<br><br>";
                    
                }

                echo style::replaceVar('tpl/admin/lof/theme-upload.tpl');
                
                break;
            
            case "cssedit":
			
				echo $this->EditTemplate("style", "css");
                break;
            
            case "tpledit":
			
                echo style::replaceVar('tpl/admin/lof/template-editor.tpl');
                break;
				
            case "navedit";
			
                echo style::replaceVar("tpl/admin/lof/navedit/top.tpl");
                $navbar_query = $dbh->select("navbar", 0, array("sortorder", "ASC"));
                while($navbar_data = $dbh->fetch_array($navbar_query)){

                    $link_box_array['ID']   = $navbar_data['id'];
                    $link_box_array['NAME'] = $navbar_data['visual'];
                    $link_box_array['ICON'] = $navbar_data['icon'];
                    $link_box_array['LINK'] = $navbar_data['link'];
                    $links_array['LINKS'] .= style::replaceVar("tpl/admin/lof/navedit/link-box.tpl", $link_box_array);
                
                }

                echo style::replaceVar("tpl/admin/lof/navedit/links.tpl", $links_array);
                echo style::replaceVar("tpl/admin/lof/navedit/bottom.tpl");
                break;
            
            case "editheader":
			
				echo $this->EditTemplate("header", "tpl");
                break;
            
            case "editfooter":
			
				echo $this->EditTemplate("footer", "tpl");
                break;
            
            case "ui-theme":	
		
				if($_POST){

					check::empty_fields();
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);
						
						}

						main::errors("Settings Updated!");
					
					}

				}
			
                $folder = INC."/css/";
                foreach(main::folderFiles($folder) as $file){

                    $files[] = array($file, $file);
                
                }

                $jquery_theme_changer_array['THEME']  = main::dropDown("ui-theme", $files, $dbh->config("ui-theme"));
                echo style::replaceVar('tpl/admin/lof/jquery-theme-changer.tpl', $jquery_theme_changer_array);
                break;
        
        }

    }
	
	private function EditTemplate($tpl, $ext){
        global $dbh, $postvar, $getvar, $instance;
		
		$filetochange = INC."/../themes/".$dbh->config('theme')."/".$tpl.".".$ext;
		
		if($_POST){
			
			file_put_contents($filetochange, stripslashes(str_replace(array("&lt;IMG>", "-%-INFO-%-"), array("<IMG>", "%INFO%"), $postvar['contents'])));
			main::errors($tpl.'.'.$ext.' Modified.');
		
		}
	
		$tpl_editor_array['CONTENT'] = str_replace(array("<IMG>", "%INFO%"), array("&lt;IMG>", "-%-INFO-%-"), htmlentities(file_get_contents($filetochange)));
		
		if(is_writable($filetochange)){

			$tpl_editor_array['NOTICE'] = '';
		
		}else{

			$tpl_editor_array['NOTICE'] = style::notice(false, "In order to make changes to this file, please make it writable.");
		
		}
		
		return style::replaceVar('tpl/admin/lof/'.$tpl.'-editor.tpl', $tpl_editor_array);	
	
	}

}

?>