<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Backup Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class backup{

    public function serverhasrestore($servertype){
        global $dbh, $postvar, $getvar, $instance;
		
        $restoreserver = server::createServer(0, $servertype);

        if(method_exists($restoreserver, "restore")){

            return true;
            
        }else{

            return false;
            
        }

    }

    public function serverinfo(){
        global $dbh, $postvar, $getvar, $instance;
        
        $client        = $dbh->client($_SESSION['cuser']);
        $packages_data = $dbh->select("packages", array("id", "=", $client['pid']));
        $servers_data  = $dbh->select("servers", array("id", "=", $packages_data['server']));
        if($servers_data['ftpuser']){

            return array($servers_data['ftpuser'], $servers_data['ftppass'], $servers_data['ftpport'], $servers_data['ftppath'], $servers_data['ftphost'], $servers_data['id']);
            
        }else{

            return false;
            
        }

    }

}

?>