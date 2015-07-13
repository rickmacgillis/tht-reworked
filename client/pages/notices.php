<?PHP
//////////////////////
// The Hosting Tool Reworked
// Client Area - Announcements
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        if($dbh->config('alerts')){

            $announcements_array['ALERTS'] = $dbh->config('alerts');
            echo style::replaceVar('tpl/client/announcements.tpl', $announcements_array);
        
        }else{

            echo 'No Announcements Available';
        
        }

    }

}

?>