<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Packages
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

        $this->navtitle  = "Packages Sub Menu";
        $this->navlist[] = array("Add Packages", "package_add.png", "add");
        $this->navlist[] = array("Edit Packages", "package_go.png", "edit");
        $this->navlist[] = array("Delete Packages", "package_delete.png", "delete");
    
    }

    public function description(){

        return "<strong>Managing Packages</strong><br />
                Welcome to the Package Management Area. Here you can add, edit and delete web hosting packages. Have fun :)<br />
                To get started, choose a link from the sidebar's SubMenu.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        switch($getvar['sub']){

            default:
                if($_POST['add']){

                    $no_check_array = array("admin", "groupid", "sendwelcome", "welcomesubject", "welcomebody");
                    check::empty_fields($no_check_array);
					
					$ZserverID = $postvar['server'];
					unset($where);
					$where[]       = array("id", "=", $ZserverID, "AND");
					$where[]       = array("type", "=", "zpanel");
					$servers_query = $dbh->select("servers", $where, 0, 0, 1);
					if($dbh->num_rows($servers_query) == 1){

						$zpanel_srv = 1;
					
					}
						
                    //Hack to make sure the Group ID isn't 0 on ZPanel
                    if($zpanel_srv && ($postvar["groupid"] == 0 || $postvar["backend"] == 0)){

                        main::errors(" ZPanel server packages must include a Group ID for the package and the a backend package ID that's greater than 0. See the info bubbles for help.");

                    }
					
					if(($postvar["monthly"] && !is_numeric($postvar["monthly"])) || ($postvar["signup"] && !is_numeric($postvar["signup"]))){
								
						main::errors("Please enter a positive number for the cost or posts fields.");
								
					}

                    if(!main::errors()){

                        $not_additional_array = array("add", "name", "backend", "description", "type", "server", "admin", "groupid", "sendwelcome", "welcomesubject", "welcomebody", $GLOBALS['csrf']['input-name']);
                        foreach($postvar as $key => $value){

                            if(!in_array($key, $not_additional_array)){								
							
                                if($n){

                                    $additional .= ",";
                                
                                }

                                $additional .= $key."=".str_replace(array(" ", ","), array("", "."), $value);
                                $n++;
                            
                            }

                        }

                        $packages_insert = array(
                            "name"          => $postvar['name'],
                            "backend"       => $postvar['backend'],
                            "description"   => $postvar['description'],
                            "type"          => $postvar['type'],
                            "server"        => $postvar['server'],
                            "admin"         => $postvar['admin'],
                            "is_hidden"     => $postvar['hidden'],
                            "is_disabled"   => $postvar['disabled'],
                            "additional"    => $additional,
                            "reseller"      => $postvar['reseller'],
                            "groupid"       => $postvar['groupid'],
                            "send_email"    => $postvar['sendwelcome'],
                            "email_subject" => $postvar['welcomesubject'],
                            "email_body"    => $postvar['welcomebody']
                        );
                        
                        $dbh->insert("packages", $packages_insert);
                        main::errors("Package has been added!<br>");
                    
                    }

                }
				
				if($_POST['packserver'] || $_POST['add']){
					$servers_data   = $dbh->select("servers", array("id", "=", $postvar['server']), 0, "1");
					$serverfile     = server::createServer(0, $servers_data['type']);
					$package_fields = $serverfile->acp_packages_form();
					
					$add_package_array['TYPE_FORM'] = type::acpPadd($postvar['type']);	
					$add_package_array['TYPE'] = $postvar['type'];		
					$add_package_array['PACKAGES_FIELDS'] = $package_fields;					
					$add_package_array['SERVER'] = $postvar['server'];
								
					echo style::replaceVar("tpl/admin/packages/add-package.tpl", $add_package_array);
					break;
				
				}

                $servers_query = $dbh->select("servers");
                if($dbh->num_rows($servers_query) == 0){

                    echo "There are no servers, you need to add a server first!";
                    return;
                
                }

                while($servers_data = $dbh->fetch_array($servers_query)){

                    $values[] = array($servers_data['name'], $servers_data['id']);
                
                }
				
				$p2h_query = $dbh->select("p2h");
				$p2h_data  = $dbh->num_rows($p2h_query);
				if($p2h_data != "0"){

					$package_server_array['P2HOPTION'] = '<option value="p2h">Post 2 Host</option>';
				
				}else{

					$package_server_array['P2HOPTION'] == "";
				
				}
				
				$package_server_array['SERVER'] = main::dropDown("server", $values);

                echo "This will only add the package to THT, not create the package on the backend for you.<br><br>";
                echo style::replaceVar("tpl/admin/packages/package-server.tpl", $package_server_array);
                break;
            
            case "edit":
                if(isset($getvar['do'])){

                    $packages_query = $dbh->select("packages", array("id", "=", $getvar['do']), 0, 0, 1);
                    if($dbh->num_rows($packages_query) == 0){

                        echo "That package doesn't exist!";
                    
                    }else{

                        if($_POST){

                            $no_check_array = array("admin", "groupid", "sendwelcome", "welcomesubject", "welcomebody");
                            check::empty_fields($no_check_array);
							
							$ZserverID = $postvar['server'];
							unset($where);
							$where[]       = array("id", "=", $ZserverID, "AND");
							$where[]       = array("type", "=", "zpanel");
							$servers_query = $dbh->select("servers", $where, 0, 0, 1);
							if($dbh->num_rows($servers_query) == 1){

								$zpanel_srv = 1;
							
							}
								
							//Hack to make sure the Group ID isn't 0 on ZPanel
							if($zpanel_srv && ($postvar["groupid"] == 0 || $postvar["backend"] == 0)){

								main::errors(" ZPanel server packages must include a Group ID for the package and the a backend package ID that's greater than 0. See the info bubbles for help.");

							}
					
							if(($postvar["monthly"] && !is_numeric($postvar["monthly"])) || ($postvar["signup"] && !is_numeric($postvar["signup"]))){
										
								main::errors("Please enter a positive number for the cost or posts fields.");
										
							}

                            if(!main::errors()){

                                $not_additional_array = array("edit", "name", "backend", "description", "type", "server", "admin", "groupid", "sendwelcome", "welcomesubject", "welcomebody", $GLOBALS['csrf']['input-name']);
                                foreach($postvar as $key => $value){

                                    if(!in_array($key, $not_additional_array)){

                                        if($n){

                                            $additional .= ",";
                                        
                                        }

                                        $additional .= $key."=".str_replace(array(" ", ","), array("", "."), $value);
                                        $n++;
                                    
                                    }

                                }

                                if($postvar['sendwelcome'] && (!$postvar['welcomesubject'] || !$postvar['welcomebody'])){

                                    $serverid = $postvar['server'];
                                    
                                    $server_type_data = $dbh->select("servers", array("id", "=", $serverid));
                                    $server_type      = $server_type_data['type'];
                                    
                                    if($server_type == "zpanel"){

                                        include(INC."/servers/".$server_type.".php");
                                        $server = new $server_type;
                                        
                                        $server_subject_def = server::email_subject;
                                        $server_body_def    = server::email_body;
                                        
                                        if(!$postvar['welcomesubject']){

                                            $postvar['welcomesubject'] = $server_subject_def;
                                        
                                        }

                                        if(!$postvar['welcomebody']){

                                            $postvar['welcomebody'] = $server_body_def;
                                        
                                        }

                                    }

                                }

                                $packages_update = array(
                                    "name"          => $postvar['name'],
                                    "backend"       => $postvar['backend'],
                                    "description"   => $postvar['description'],
                                    "server"        => $postvar['server'],
                                    "admin"         => $postvar['admin'],
                                    "additional"    => $additional,
                                    "reseller"      => $postvar['reseller'],
                                    "is_hidden"     => $postvar['hidden'],
                                    "is_disabled"   => $postvar['disabled'],
                                    "type"          => $postvar['type'],
                                    "groupid"       => $postvar['groupid'],
                                    "send_email"    => $postvar['sendwelcome'],
                                    "email_subject" => $postvar['welcomesubject'],
                                    "email_body"    => $postvar['welcomebody']
                                );
                                
                                $dbh->update("packages", $packages_update, array("id", "=", $getvar['do']));
                                
                                //Package edit complete.
                                main::done();
                            
                            }

                        }

                        $packages_data        = $dbh->fetch_array($packages_query);
                        $edit_package_array['BACKEND']     = $packages_data['backend'];
                        $edit_package_array['DESCRIPTION'] = $packages_data['description'];
                        $edit_package_array['NAME']        = $packages_data['name'];
                        $edit_package_array['URL']         = $dbh->config("url");
                        $edit_package_array['ID']          = $packages_data['id'];
						
                        if($packages_data['admin'] == 1){

                            $edit_package_array['ADMIN_CHECKED'] = 'checked="checked"';
                        
                        }else{

                            $edit_package_array['ADMIN_CHECKED'] = "";
                        
                        }

                        if($packages_data['reseller'] == 1){

                            $edit_package_array['RESELLER_CHECKED'] = 'checked="checked"';
                        
                        }else{

                            $edit_package_array['RESELLER_CHECKED'] = "";
                        
                        }

                        if($packages_data['is_hidden'] == 1){

                            $edit_package_array['HIDDEN_CHECKED'] = 'checked="checked"';
                        
                        }else{

                            $edit_package_array['HIDDEN_CHECKED'] = "";
                        
                        }

                        if($packages_data['is_disabled'] == 1){

                            $edit_package_array['DISABLED_CHECKED'] = 'checked="checked"';
                        
                        }else{

                            $edit_package_array['DISABLED_CHECKED'] = "";
                        
                        }

						$serverfile = server::createServer($getvar['do']);
						$package_fields = $serverfile->acp_packages_form($getvar['do']);
                        $edit_package_array['PACKAGES_FIELDS'] = $package_fields;
                        
                        $p2h_query = $dbh->select("p2h");
                        $p2h_data  = $dbh->num_rows($p2h_query);
                        
						$edit_package_array['TYPE'] = $packages_data['type'];

                        $additional = $packages_data['additional'];
                        $edit_package_array['TYPE_FORM'] = type::acpPedit($packages_data['type'], $additional, $packages_data['type']);
						
                        if($packages_data['type'] == "p2h" && $p2h_data == "0"){

                            $edit_package_array['TYPE_FORM'] = "";
                        
                        }

                        $servers_query      = $dbh->select("servers");
                        while($servers_data = $dbh->fetch_array($servers_query)){

                            $values[] = array($servers_data['name'], $servers_data['id']);
                        
                        }

                        $edit_package_array['SERVER'] = $packages_data['server'];
                        echo "This will only edit the package on THT, not edit the package on the backend for you.<br><br>";
                        echo style::replaceVar("tpl/admin/packages/edit-package.tpl", $edit_package_array);
                    
                    }

                }else{

                    $packages_query = $dbh->select("packages");
                    if($dbh->num_rows($packages_query) == 0){

                        echo "There are no packages to edit!";
                    
                    }else{

                        echo "<ERRORS>";
                        while($packages_data = $dbh->fetch_array($packages_query)){

                            echo main::sub("<strong>".$packages_data['name']."</strong>", '<a href="?page=packages&sub=edit&do='.$packages_data['id'].'"><img src="'.URL.'themes/icons/pencil.png"></a>');
                            $n++;
                        
                        }

                    }

                }

                break;
            
            case "delete":
                if($getvar['do']){

                    $dbh->delete("packages", array("id", "=", $getvar['do']));
                    main::errors("Package has been Deleted.<br>");
                
                }

                $packages_query = $dbh->select("packages");
                if($dbh->num_rows($packages_query) == 0){

                    echo "There are no packages to delete.";
                
                }else{

                    echo "<ERRORS>This will only delete the package on THT, not delete the package on the backend for you.<br><br>";
                    while($packages_data = $dbh->fetch_array($packages_query)){

                        echo main::sub("<strong>".$packages_data['name']."</strong>", '<a href="?page=packages&sub=delete&do='.$packages_data['id'].'"><img src="'.URL.'themes/icons/delete.png"></a>');
                        $n++;
                    
                    }

                }

                break;
        
        }

    }

}

?>