<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Servers
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

        $this->navtitle  = "Servers Sub Menu";
        $this->navlist[] = array("Add Server", "server_add.png", "add");
        $this->navlist[] = array("View/Edit Servers", "server_go.png", "view");
        $this->navlist[] = array("Test Servers", "server_connect.png", "test");
        $this->navlist[] = array("Delete Server", "server_delete.png", "delete");
        $this->navlist[] = array("Server Status", "application_osx_terminal.png", "status");
        $this->navlist[] = array("PHP Info", "page_white_php.png", "phpinfo");
    
    }

    public function description(){

        return "<strong>Managing Hosting Servers</strong><br />
                Welcome to the Servers Management Area. Here you can add, edit, and delete servers as well as view server information.<br />
                To get started, choose a link from the sidebar's SubMenu.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            default:
                if($_POST['add']){

                    $no_check_array = array("resellerport", "welcome", "nstmp", "passtoo", "resellerid");
                    check::empty_fields($no_check_array);
                    if(!main::errors()){

                        $servers_insert = array(
                            "ip"           => $postvar['ip'],
                            "resellerport" => $postvar['resellerport'],
                            "port"         => $postvar['port'],
                            "nameservers"  => $postvar['nameservers'],
                            "name"         => $postvar['name'],
                            "host"         => $postvar['host'],
                            "user"         => $postvar['user'],
                            "accesshash"   => $postvar['hash'],
                            "type"         => $postvar['type'],
                            "dnstemplate"  => $postvar['nstmp'],
                            "welcome"      => $postvar['welcome'],
                            "pass"         => $postvar['pass'],
                            "reseller_id"  => $postvar['resellerid'],
                            "https"        => $postvar['https'],
                            "apiport"      => $postvar['apiport']
                        );
                        
                        $dbh->insert("servers", $servers_insert);
                        main::errors("Server has been added!");
                    
                    }

                }
                
				if($_POST['addtype'] || $_POST['add']){
					
					$serverfile = server::createServer(0, $postvar['type']);
					$server_fields = $serverfile->acp_form();
					
					$add_server_array['SERVER_FIELDS']    = $server_fields;
					
					$add_server_array['TYPE'] = $postvar['type'];
					
					echo style::replaceVar("tpl/admin/servers/add-server.tpl", $add_server_array);
					break;
					
				}
				
				$files = main::folderFiles(INC."/servers/");
                foreach($files as $value){

                    include(INC."/servers/".$value);
                    $fname    = explode(".", $value);
                    $stype    = new $fname[0];
                    $values[] = array($stype->name, $fname[0]);
                
                }

                $server_type_array['TYPE'] = main::dropDown("type", $values, 0);
				echo style::replaceVar("tpl/admin/servers/server-type.tpl", $server_type_array);				
                break;
            
            case "view":
                if(isset($getvar['do'])){

                    $servers_query = $dbh->select("servers", array("id", "=", $getvar['do']), 0, 0, 1);
                    if($dbh->num_rows($servers_query) == 0){

                        echo "That server doesn't exist!";
                    
                    }else{

                        if($_POST){

                            check::empty_fields();
                            if(!main::errors()){

                                $servers_update = array(
                                    "name"         => $postvar['name'],
                                    "host"         => $postvar['host'],
                                    "reseller_id"  => $postvar['resellerid'],
                                    "user"         => $postvar['user'],
                                    "pass"         => $postvar['pass'],
                                    "accesshash"   => $postvar['hash'],
                                    "port"         => $postvar['port'],
                                    "resellerport" => $postvar['resellerport'],
                                    "nameservers"  => $postvar['nameservers'],
                                    "ip"           => $postvar['ip'],
                                    "dnstemplate"  => $postvar['nstmp'],
                                    "welcome"      => $postvar['welcome'],
                                    "https"        => $postvar['https'],
                                    "apiport"      => $postvar['apiport']
                                );
                                
                                $dbh->update("servers", $servers_update, array("id", "=", $getvar['do']), 1);
                                
                                //Server edit complete
                                main::done();
                            
                            }

                        }

                        $servers_data          = $dbh->fetch_array($servers_query);
						
						$serverfile    = server::createServer(0, $servers_data['type']); 
						$server_fields = $serverfile->acp_form($getvar['do']);					
					
                        $edit_server_array['NAME']          = $servers_data['name'];
                        $edit_server_array['HOST']          = $servers_data['host'];
                        $edit_server_array['SERVERIP']      = $servers_data['ip'];
                        $edit_server_array['RESELLERPORT']  = $servers_data['resellerport'];
                        $edit_server_array['PORT']          = $servers_data['port'];
                        $edit_server_array['NAMESERVERS']   = $servers_data['nameservers'];
						$edit_server_array['SERVER_FIELDS'] = $server_fields;
						
                        echo style::replaceVar("tpl/admin/servers/edit-server.tpl", $edit_server_array);
                    
                    }

                }else{

                    $servers_query = $dbh->select("servers");
                    if($dbh->num_rows($servers_query) == 0){

                        echo "There are no servers to view!";
                    
                    }else{

                        echo "<ERRORS>";
                        while($servers_data = $dbh->fetch_array($servers_query)){

                            echo main::sub("<strong>".$servers_data['name']."</strong>", '<a href="?page=servers&sub=view&do='.$servers_data['id'].'"><img src="'.URL.'themes/icons/magnifier.png"></a>');
                            
                        }

                    }

                }

                break;
            
            case "delete":
                if($getvar['do']){

                    $dbh->delete("servers", array("id", "=", $getvar['do']));
                    main::errors("Server Deleted!");
                
                }

                $servers_query = $dbh->select("servers");
                if($dbh->num_rows($servers_query) == 0){

                    echo "There are no servers to delete!";
                
                }else{

                    echo "<ERRORS>";
                    while($servers_data = $dbh->fetch_array($servers_query)){

                        echo main::sub("<strong>".$servers_data['name']."</strong>", '<a href="?page=servers&sub=delete&do='.$servers_data['id'].'"><img src="'.URL.'themes/icons/delete.png"></a>');
                    
                    }

                }

                break;
            
            case "test":
                if(isset($getvar["do"])){

                    $result = server::testConnection($getvar["do"]);
                    if($result === true){

                        echo '<div style="text-align:center;padding-top:10px;">'.style::notice(true, "Connected to the server successfully!")."</div>";
                    
                    }else{

                        echo '<div style="text-align:center;">'.style::notice(false, "Couldn't connect to the server...")."</div>";
                        echo '<strong>Error:</strong><pre>'.(string) $result.'</pre>';
                    
                    }

                }else{

                    $servers_query = $dbh->select("servers");
                    if($dbh->num_rows($servers_query) == 0){

                        echo "There are no servers to view!";
                    
                    }else{

                        echo "Caution: Some servers are set to automatically ban the IP address of this server (".$_SERVER['SERVER_ADDR'].") after a certain number of failed logins.<br />";
                        while($servers_data = $dbh->fetch_array($servers_query)){

                            echo main::sub("<strong>".$servers_data['name']."</strong>", '<a href="?page=servers&sub=test&do='.$servers_data['id'].'"><img src="'.URL.'themes/icons/server_chart.png"></a>');
                            
                        }

                    }

                }

                break;
            
            case "status":
                $server_status_array['EXTRA'] = '';
                
                if(!main::canRun('shell_exec')){

                    $server_status_array['EXTRA'] = 'Some statistics could not be provided because shell_exec has been disabled.<br>';
                    
                }

                $server          = $_SERVER['HTTP_HOST'];
                $server_status_array['OS']     = php_uname();
                $server_status_array['DISTRO'] = '';
                if(php_uname('s') == 'Linux'){

                    $distro = main::getLinuxDistro();
                    if($distro){

                        $server_status_array['DISTRO'] = '<tr><td><strong>Linux Distro:</strong></td><td> '.$distro.' </td></tr>';
                        
                    }

                }

                $server_status_array['SOFTWARE']      = getenv('SERVER_SOFTWARE');
                $server_status_array['PHP_VERSION']   = phpversion();
                $server_status_array['MYSQL_VERSION'] = '';
                $mysqlVersion           = $dbh->version();
                
                if($mysqlVersion){

                    $server_status_array['MYSQL_VERSION'] = '<tr><td><strong>MySQL Version:</strong><br><br></td><td> '.$mysqlVersion.' <br><br></td></tr>';
                    
                }

                $server_status_array['SERVER'] = $server;
                echo style::replaceVar('tpl/admin/servers/server-status.tpl', $server_status_array);
                
                break;
            
            case "phpinfo":
			
                echo server::show_phpinfo();
                break;
        
        }

    }

}

?>