<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - System Tools
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

        $this->navtitle  = "System Tools Sub Menu";
        $this->navlist[] = array("System Health", "bug.png", "health");
        $this->navlist[] = array("Useful Tools", "wrench.png", "tools");
    
    }

    public function description(){

        return "<strong>System Tools</strong><br />
                Welcome to the system tools area. Here you can solve the most common problems and check your system health.<br />
                To start, choose a link from the submenu.";
    
    }

    public function checkDir($dir, $friendly){

        if(is_dir($dir)){

            return "<div class='warn'><img src='../themes/icons/cross.png' alt='' /> Warning: $friendly still exists, please remove it.</div>";
        
        }else{

            return "<div class='noupg'><img src='../themes/icons/accept.png' alt='' /> Check Passed: $friendly does not exist</div>";
        
        }

    }

    public function checkPerms($file, $friendly){

        if(is_writable($file)){

            return "<div class='warn'><img src='../themes/icons/error.png' alt='' /> Warning: $friendly is world writable!</div>";
        
        }else{

            return "<div class='noupg'><img src='../themes/icons/accept.png' alt='' /> Check Passed: $friendly is not writable!</div>";
        
        }

    }

    public function health(){
		
        $system_health_array['CHECK_INSTALL'] = $this->checkDir(INC."../install/", "Install Directory");
        $system_health_array['CHECK_CONF']    = $this->checkPerms(INC."/conf.inc.php", "Configuration File");
        echo style::replaceVar('tpl/admin/system/system-health.tpl', $system_health_array);
    
    }

    public function tools(){
		
        echo style::replaceVar('tpl/admin/system/perms-tools.tpl');
    
    }

    public function conf_perms(){
		
        if(is_writable(INC."/conf.inc.php")){

            if(main::perms(INC."/conf.inc.php", 0444)){

                main::errors("Configuration File made unwritable.");
            
            }else{

                main::errors("Failed to make the configuration file unwritable.");
            
            }

        }else{

            main::errors("Configuration File is already unwritable.");
        
        }
            
		echo style::replaceVar('tpl/admin/system/perms-tools.tpl');

    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            default:
                $this->health();
                break;
            
            case "health":
                $this->health();
                break;
            
            case "tools":
                $this->tools();
                break;
            
            case "tool_confperms":
                $this->conf_perms();
                break;
        
        }

    }

}

?>