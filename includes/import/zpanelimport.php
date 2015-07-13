<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Import Tool - ZPanel
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class zpanelimport{

    public $name = "ZPanel";
    public $server = "zpanel"; //Leave empty to always show this on the import screen
    
    public function import(){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$_POST){

            $servers_query = $dbh->select("servers", array("type", "=", "zpanel"), 0, 0, 1);
            while($servers_data = $dbh->fetch_array($servers_query)){

                $values[] = array($servers_data['name'], $servers_data['id']);
            
            }

            $zpanel_array['DROPDOWN'] = main::dropdown("server", $values);
            echo style::replaceVar("tpl/admin/import/zpanel.tpl", $zpanel_array);
        
        }elseif($_POST){

            $postvar['server'] = $postvar['server']; //Hack to make sure we post the 'server' field as it doesn't post if it's empty.			
            check::empty_fields();
            if(main::errors()){

                echo "<ERRORS>";
                
            }else{

                $n = 0;
                include(INC."/servers/zpanel.php");
                $zpanel          = new zpanel($postvar['server']);
                $zpanel_accounts = $zpanel->listaccs($postvar['server']);
                foreach($zpanel_accounts as $zpanel_data){

                    $packages_data = $dbh->select("packages", array("backend", "=", $zpanel_data['package']));
                    $users_data    = $dbh->select("users", array("user", "=", $zpanel_data['user']));
                    if(!$packages_data['id']){

                        $packages_insert = array(
                            "name"        => $zpanel_data['package'],
                            "backend"     => $zpanel_data['package'],
                            "description" => "Imported from ZPanel: ".$zpanel_data['package'],
                            "type"        => "free",
                            "server"      => $postvar['server'],
                            "admin"       => "1"
                        );
                        
                        $dbh->insert("packages", $packages_insert);
                    
                    }

                    $new_packages_data = $dbh->select("packages", array("backend", "=", $zpanel_data['package']));
                    if(!$users_data['id']){

                        $salt    = crypto::salt();
                        $newpass = crypto::passhash(rand(), $salt);
                        
                        $users_insert = array(
                            "user"       => $zpanel_data['user'],
                            "zpanel_uid" => $zpanel_data['user'],
                            "email"      => $zpanel_data['user'],
                            "password"   => $zpanel_data['user'],
                            "salt"       => $zpanel_data['user'],
                            "signup"     => $zpanel_data['user'],
                            "status"     => $zpanel_data['user'],
                            "domain"     => $zpanel_data['user'],
                            "pid"        => $zpanel_data['user']
                        );
                        
                        $dbh->insert("users", $users_insert);
                        $dbh->insert("users_bak", $users_insert);
                        $n++;
                    
                    }

                }

                echo $n." Accounts have been imported";
            
            }

        }

    }

}

?>