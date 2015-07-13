<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Page Variables
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

//This page is included from inside of a function.
global $dbh, $postvar, $getvar, $instance;

if(INSTALL == 1){
   
    if($dbh->config("show_page_gentime") == 1){

        $mtime            = explode(' ', microtime());
        $totaltime        = $mtime[0] + $mtime[1] - $starttime;
        $gentime          = substr($totaltime, 0, 5);
        $page_generation_time_array['PAGEGEN'] = $gentime;
        $page_generation_time_array['IP']      = getenv('REMOTE_ADDR');
        $pagegen .= style::replaceVar('tpl/page-generation-time.tpl', $page_generation_time_array);
        if($dbh->config("show_footer")){

            $server_status_array['EXTRA'] = '';
            if(!main::canRun('shell_exec')){

                $server_status_array['EXTRA'] = 'Some statistics could not be provided because shell_exec has been disabled.';
            
            }

            $server_status_array['OS']     = php_uname();
            $server_status_array['DISTRO'] = '';
            if(php_uname('s') == 'Linux'){

                $distro = main::getLinuxDistro();
                if($distro){

                    $server_status_array['DISTRO'] = '<tr><td><strong>Linux Distro:</strong></td><td> '.$distro.' </td></tr>';
                
                }

            }

            $server_status_array['SOFTWARE']      = $_SERVER["SERVER_SOFTWARE"];
            $server_status_array['PHP_VERSION']   = phpversion();
            $server_status_array['MYSQL_VERSION'] = '';
            $versionResult           = $dbh->version();
            if($versionResult){

                $server_status_array['MYSQL_VERSION'] = '<tr><td><strong>MySQL Version:</strong></td><td> '.$versionResult[0].' </td></tr>';
            
            }

            $server_status_array["SERVER"] = $_SERVER["HTTP_HOST"];
            $footer_debug_array['TITLE']   = style::replaceVar('tpl/admin/servers/server-status.tpl', $server_status_array);
            $pagegen .= style::replaceVar('tpl/footer-debug.tpl', $footer_debug_array);
        
        }

    }else{

        $pagegen = '';
    
    }

    if($dbh->config("show_version_id") == 1){

        $version = $dbh->config("version");
    
    }else{

        $version = '';
    
    }

    if(FOLDER != "install"){

        $navbar_query = $dbh->select("navbar", 0, array("sortorder", "ASC"));
        while($navbar_data = $dbh->fetch_array($navbar_query)){

			$navigation_link_array['ID']   = "nav_".$navbar_data['name'];
			$navigation_link_array['LINK'] = $navbar_data['link'];
			$navigation_link_array['ICON'] = $navbar_data['icon'];
			$navigation_link_array['NAME'] = $navbar_data['visual'];
			$navigation .= style::replaceVar("tpl/navigation-link.tpl", $navigation_link_array);

        }

    }

}

/**********************************************************************/
$page_data = preg_replace("/<THT TITLE>/si", NAME." :: ".PAGE." - ".SUB, $page_data);
$page_data = preg_replace("/<NAME>/si", NAME, $page_data);
$page_data = preg_replace("/<CSS>/si", self::css(), $page_data);
$page_data = preg_replace("/<JAVASCRIPT>/si", self::javascript(), $page_data);
$page_data = preg_replace("/<WYSIWYG_EDITOR>/si", "<URL>includes/tinymce/tinymce.min.js", $page_data);
$page_data = preg_replace("/<WYSIWYG_PLUGS>/si", "advlist autolink autoresize hr link searchreplace table", $page_data);
$page_data = preg_replace("/<WYSIWYG_LANG>/si", "", $page_data);
$page_data = preg_replace("/<MENU>/si", $navigation, $page_data);
$page_data = preg_replace("/<URL>/si", URL, $page_data);
$page_data = preg_replace("/<AJAX>/si", URL."includes/ajax.php", $page_data);
$page_data = preg_replace("/<IMG>/si", URL."themes/".THEME."/images/", $page_data);
$page_data = preg_replace("/<ICONDIR>/si", URL."themes/icons/", $page_data);
$page_data = preg_replace("/<PAGEGEN>/si", $pagegen, $page_data);

$page_data = preg_replace("/<COPYRIGHT>/si", '<div id="footer">Powered by <a href="http://thehostingtool.com/" target="_blank">TheHostingTool</a> '.$version.'</div>', $page_data);
$page_data = preg_replace("/<ERRORS>/si", '<span class="errors">'.main::errors().'</span>', $page_data);
$page_data = preg_replace("/%INFO%/si", INFO, $page_data);
$page_data = preg_replace("/-%-INFO-%-/si", "%INFO%", $page_data);
$page_data = preg_replace("/<CSRF_NAME>/si", $GLOBALS['csrf']['input-name'], $page_data);
$page_data = preg_replace("/<ADMINDIR>/si", ADMINDIR, $page_data);

?>