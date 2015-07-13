<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Credits
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

define("PAGE", "Credits");

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        echo style::replaceVar("tpl/admin/credits.tpl");
    
    }

}

?>