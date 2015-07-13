<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Types Pages
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

            echo "This page cannot be displayed.";
        
        }else{

            $instance->packtypes[$getvar['type']]->clientPage();
        
        }

    }

}

?>