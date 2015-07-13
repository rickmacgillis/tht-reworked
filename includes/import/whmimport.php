<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Import Tool - WHM
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class whmimport{

    public $name   = "Web Host Manager (WHM)";
    public $server = "whm"; //Leave empty to always show this on the import screen
    
    public function import(){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$_POST){

            $servers_query = $dbh->select("servers", array("type", "=", "whm"), 0, 0, 1);
            while($servers_data = $dbh->fetch_array($servers_query)){

                $values[] = array($servers_data['name'], $servers_data['id']);
            
            }

            $whm_array['DROPDOWN'] = main::dropdown("server", $values);
            echo style::replaceVar("tpl/admin/import/whm.tpl", $whm_array);
        
        }elseif($_POST){

            $postvar['server'] = $postvar['server']; //Hack to make sure we post the 'server' field as it doesn't post if it's empty.            
            check::empty_fields();
            if(main::errors()){

                echo "<ERRORS>";
                
            }else{

                include(INC."/servers/whm.php");
                $whm          = new whm;
                $whm_accounts = $whm->listaccs($postvar['server']);
                foreach($whm_accounts as $whm_data){

                    $packages_data = $dbh->select("packages", array("backend", "=", $whm_data['package']));
                    $users_data    = $dbh->select("users", array("user", "=", $whm_data['user']));
                    if(!$users_data['id']){

                        if(!$packages_data['id']){

                            $packages_insert = array(
                                "name"        => $whm_data['package'],
                                "backend"     => $whm_data['package'],
                                "description" => "Inported from WHM: ".$whm_data['package'],
                                "type"        => "free",
                                "server"      => $postvar['server'],
                                "admin"       => "1"
                            );
                            
                            $dbh->insert("packages", $packages_insert);
                        
                        }

                        $new_packages_data = $dbh->select("packages", array("backend", "=", $whm_data['package']));
                        $salt              = crypto::salt();
                        $newpass           = crypto::passhash(rand(), $salt);
                        
                        $users_insert = array(
                            "user"     => $whm_data['user'],
                            "email"    => $whm_data['email'],
                            "password" => $newpass,
                            "salt"     => $salt,
                            "signup"   => $whm_data['start_date'],
                            "status"   => "1",
                            "domain"   => $whm_data['domain'],
                            "pid"      => $new_packages_data['id']
                        );
                        
                        $dbh->insert("users", $users_insert);
                        $dbh->insert("users_bak", $users_insert);
                        $n++;
                    
                    }

                }

                echo $n." Accounts have been imported!";
            
            }

        }

    }

}

?>