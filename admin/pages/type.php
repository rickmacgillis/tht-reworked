<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Type
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
    
    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        if(!$getvar['type'] || !$getvar['sub']){

            echo "Not all variables set!";
        
        }

        $user = $_SESSION['user'];
        if($user == 1){

            $packtype = $instance->packtypes[$getvar['type']];
			if(method_exists($packtype, "acpPage")){
			
				$packtype->acpPage();
			
			}else{
			
				echo "This page doesn't exist.  It's all in your head.";
			
			}            
        
        }else{

            echo "You don't have access to this page.";
        
        }

    }

}

?>