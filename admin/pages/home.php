<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Home
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        $version_info         = main::latest_version();
		$current_version      = $version_info['THT'];
		$new_version_download = $version_info['THT_DL'];
        $running_version      = $dbh->config('version');
        $install_check        = $this->checkDir(INC."../install/");
        $conf_check           = $this->checkPerms(INC."/conf.inc.php");
        if($current_version == $running_version){

            $updatemsg  = "<span style='color:green'>Up-To-Date</span>";
            $upgrademsg = "";
        
        }else{

            $updatemsg  = "<span style='color:red'>Upgrade Avaliable</span>";
            $upgrademsg = "<div class='warn'><img src='../themes/icons/error.png' alt='' /> There is a new version ($current_version) avaliable! <a href = '".$new_version_download."' target = '_blank'>Please download it here</a> and upgrade!</div>";
        
        }

        unset($current_version);
        unset($running_version);
        $stats['VERSION']    = $dbh->config('version');
        $stats['THEME']      = $dbh->config('theme');
        $stats['CENABLED']   = main::cleaninteger($dbh->config('cenabled'));
        $stats['SVID']       = main::cleaninteger($dbh->config('show_version_id'));
        $stats['SENABLED']   = main::cleaninteger($dbh->config('senabled'));
        $stats['DEFAULT']    = $dbh->config('default_page');
        $stats['EMETHOD']    = $dbh->config('emailmethod');
        $stats['SIGNENABLE'] = main::cleaninteger($dbh->config('general'));
        $stats['MULTI']      = main::cleaninteger($dbh->config('multiple'));
        $stats['UPDATE']     = $updatemsg;
        $stats['UPG_BOX']    = $upgrademsg;
        $stats_box           = style::replaceVar('tpl/admin/home/stats.tpl', $stats);
        $content             = '<strong>Welcome to your Admin Dashboard!</strong><br />Welcome to the dashboard of your Admin Control Panel. In this area you can do the tasks that you need to complete such as manage servers, create packages, manage users.<br />
                Here, you can also change the look and feel of your THT Installation. If you require any help, be sure to ask at the <a href="http://thehostingtool.com/forum" title="THT Community is the official stop for THT Support, THT Modules, Developer Center and more! Visit our growing community now!" class="tooltip">THT Community</a><br /><br />'.$stats_box.'<br />'.$install_check.$conf_check.'</div></div>';
        echo $content;
        if($_POST){

            $dbh->update("admin_notes", array("notes" => $postvar['admin_notes']), array("id", "=", "1"));
            main::errors("Settings Updated!");
            main::done();
        
        }

        $notes_data = $dbh->select("admin_notes", array("id", "=", "1"));
        
        $notepad_array['NOTEPAD'] = $notes_data['notes'];
        $content_notepad  = style::replaceVar('tpl/admin/home/notepad.tpl', $notepad_array);
        echo '<br />';
        echo main::table('Admin Notepad', $content_notepad, 'auto', 'auto');
        
		$news = main::sub("<strong>Add the THT RSS Feed!</strong>", '<a href="http://thehostingtool.com/forum/syndication.php?fid=2" target="_blank" class="tooltip" title="Add the THT RSS Feed!"><img src="<URL>themes/icons/feed.png" /></a>');
        $rss_feed = @file_get_contents("http://thehostingtool.com/forum/syndication.php?fid=2&limit=3");
		if($rss_feed !== false){
		
			$xml = new SimpleXMLElement($rss_feed);
		
			foreach($xml->channel->item as $item){
			
				$newsitem_array['title']   = $item->title;
				$newsitem_array['author']  = "THT";
				$newsitem_array['link']    = $item->link;
				$newsitem_array['TIME']    = main::convertdate("n/d/Y", strtotime($item->pubDate));
				$newsitem_array['SUMMARY'] = $item->description;
				$news .= style::replaceVar('tpl/admin/home/news-item.tpl', $newsitem_array);				

			}
		
		}

        echo "<br />";
        echo main::table('THT News & Updates', $news);
    
    }
	
    private function curl_get_content($url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_REFERER, 'TheHostingTool Admin Area');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        if($html == false){

            $m = curl_error(($ch));
            error_log($m);
        
        }

        curl_close($ch);
        return $html;
    
    }

    private function rm_full_dir($dir){

        foreach(glob($dir.'/*') as $file){

            if(is_file($file)){

                unlink($file);
            
            }

        }

        if(rmdir($dir)){

            return true;
        
        }else{

            return false;
        
        }

    }

    private function remove_install(){

        $sql_dir              = "../install/includes/sql";
        $install_tpl          = "../install/includes/tpl";
        $install_includes_dir = "../install/includes";
        $install_dir          = "../install";
        
        if(is_dir($sql_dir)){

            if(!$this->rm_full_dir($sql_dir)){

                return false;
            
            }

        }

        if(is_dir($install_tpl)){

            if(!$this->rm_full_dir($install_tpl)){

                return false;
            
            }

        }

        if(is_dir($install_includes_dir)){

            if(!$this->rm_full_dir($install_includes_dir)){

                return false;
            
            }

        }

        if(is_dir($install_dir)){

            if(!$this->rm_full_dir($install_dir)){

                return false;
            
            }

        }

        return true;
        
    }

    private function checkDir($dir){

        if($this->remove_install()){

            return "";
        
        }else{

            return "<div class='warn'><img src='../themes/icons/cross.png' alt='' /> Warning: Please remove the directory /install.</div>";
        
        }

    }

    private function checkPerms($file){

        if(is_writable($file)){

            if(main::perms($file, 0444)){

                return "";
            
            }else{

                return "<div class='warn'><img src='../themes/icons/error.png' alt='' /> Warning: Configuration file (conf.inc.php) is still writable!</div>";
            
            }

        }else{

            return "";
        
        }

    }	

}

?>