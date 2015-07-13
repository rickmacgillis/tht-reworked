<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Staff
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

        $this->navtitle  = "Staff Accounts Sub Menu";
        $this->navlist[] = array("Add Staff Account", "user_add.png", "add");
        $this->navlist[] = array("Edit Staff Account", "user_edit.png", "edit");
        $this->navlist[] = array("Delete Staff Account", "user_delete.png", "delete");
    
    }

    public function description(){

        return "<strong>Managing Staff Accounts</strong><br />
                This is where you add/edit/delete staff accounts. <b>Be careful, don't delete yourself!</b><br />
                To get started, just choose a link from the sidebar's SubMenu.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            default:
                if($_POST){

                    check::empty_fields();
                    foreach($postvar as $key => $value){

                        $broke = explode("_", $key);
                        if($broke[0] == "pages"){

                            $postvar['perms'][$broke[1]] = $value;
                        
                        }

                    }

                    if(!main::errors()){

                        $staff_query = $dbh->select("staff", array("user", "=", $postvar['user']), 0, "1", 1);
                        if(!check::email($postvar['email'])){

                            main::errors("Your email is the wrong format or is already in use by another staff member or client.");
                        
                        }elseif($postvar['pass'] != $postvar['conpass']){

                            main::errors("Passwords don't match!");
                        
                        }elseif($dbh->num_rows($staff_query) >= 1){

                            main::errors("That account already exists!");
                        
                        }else{

                            if($postvar['perms']){

                                foreach($postvar['perms'] as $key => $value){

                                    if($n){

                                        $perms .= ",";
                                    
                                    }

                                    if($value == "1"){

                                        $perms .= $key;
                                    
                                    }

                                    $n++;
                                
                                }

                            }

                            $salt     = crypto::salt();
                            $password = crypto::passhash($postvar['pass'], $salt);
                            
                            $staff_insert = array(
                                "user"     => $postvar['user'],
                                "name"     => $postvar['name'],
                                "email"    => $postvar['email'],
                                "password" => $password,
                                "salt"     => $salt,
                                "perms"    => $perms,
                                "tzadjust" => $postvar['tzones']
                            );
                            
                            $dbh->insert("staff", $staff_insert);
                            main::errors("Account added!");
                        
                        }

                    }

                }

                $acpnav_query   = $dbh->select("acpnav", array("link", "!=", "home"), array("id", "ASC"), 0, 1);
                $add_staff_member_array['PAGES'] = '<table width="100%" border="0" cellspacing="0" cellpadding="1">';
                while($acpnav_data = $dbh->fetch_array($acpnav_query)){

                    $add_staff_member_array['PAGES'] .= '<tr><td width="30%" align="left">'.$acpnav_data['visual'].':</td><td><input name="pages_'.$acpnav_data['id'].'" id="pages_'.$acpnav_data['id'].'" type="checkbox" value="1" /></td></tr>';
                
                }

                $add_staff_member_array['PAGES'] .= '<tr><td width="30%" align="left">Paid Configuration:</td><td><input name="pages_paid" id="pages_paid" type="checkbox" value="1" /></td></tr>';
                $add_staff_member_array['PAGES'] .= '<tr><td width="30%" align="left">P2H Forums:</td><td><input name="pages_p2h" id="pages_p2h" type="checkbox" value="1" /></td></tr>';
                $add_staff_member_array['PAGES'] .= "</table>";
                $add_staff_member_array['TZADJUST'] = main::tzlist();
                echo style::replaceVar("tpl/admin/staff/add-staff-member.tpl", $add_staff_member_array);
                break;
            
            case "edit":
                if(isset($getvar['do'])){

                    $staff_data = $dbh->select("staff", array("id", "=", $getvar['do']));
                    if(!$staff_data["user"]){

                        echo "That account doesn't exist!";
                    
                    }else{

                        if($_POST){

                            check::empty_fields();
                            foreach($postvar as $key => $value){

                                $broke = explode("_", $key);
                                if($broke[0] == "pages"){

                                    $postvar['perms'][$broke[1]] = $value;
                                
                                }

                            }

                            if(!main::errors()){

                                if(!check::email($postvar['email'], $getvar['do'], "staff")){

                                    main::errors("Your email is the wrong format or is already in use by another staff member or client.");
                                
                                }else{

									if($postvar['perms']){
									
										foreach($postvar['perms'] as $key => $value){

											if($n){

												$perms .= ",";
											
											}

											if($value == "1"){

												$perms .= $key;
											
											}

											$n++;
										
										}
									
									}

                                    $staff_update = array(
                                        "email"    => $postvar['email'],
                                        "name"     => $postvar['name'],
                                        "perms"    => $perms,
                                        "tzadjust" => $postvar['tzones'],
                                        "user"     => $postvar['user']
                                    );
                                    
                                    $dbh->update("staff", $staff_update, array("id", "=", $getvar['do']));
                                    
                                    //Staff account edit complete
                                    main::done();
                                
                                }

                            }

                        }

                        $edit_staff_member_array['USER']     = $staff_data['user'];
                        $edit_staff_member_array['EMAIL']    = $staff_data['email'];
                        $edit_staff_member_array['NAME']     = $staff_data['name'];
                        $edit_staff_member_array['TZADJUST'] = main::tzlist($staff_data['tzadjust']);
                        $acpnav_query      = $dbh->select("acpnav", array("link", "!=", "home"), array("id", "ASC"), 0, 1);
                        $edit_staff_member_array['PAGES']    = '<table width="100%" border="0" cellspacing="0" cellpadding="1">';
                        while($acpnav_data = $dbh->fetch_array($acpnav_query)){

                            if(!main::checkPerms($acpnav_data['id'], $staff_data['id'])){

                                $checked = 'checked="checked"';
                            
                            }

                            $edit_staff_member_array['PAGES'] .= '<tr><td width="30%" align="left">'.$acpnav_data['visual'].':</td><td><input name="pages_'.$acpnav_data['id'].'" id="pages_'.$acpnav_data['id'].'" type="checkbox" value="1" '.$checked.'/></td></tr>'."\n";
                            $checked = NULL;
                        
                        }

                        if(substr_count($staff_data['perms'], "paid") == '1'){

                            $paid_check = 'checked="checked"';
                        
                        }

                        if(substr_count($staff_data['perms'], "p2h") == '1'){

                            $p2h_check = 'checked="checked"';
                        
                        }

                        $edit_staff_member_array['PAGES'] .= '<tr><td width="30%" align="left">Paid Configuration:</td><td><input name="pages_paid" id="pages_paid" type="checkbox" value="1" '.$paid_check.'/></td></tr>'."\n";
                        $edit_staff_member_array['PAGES'] .= '<tr><td width="30%" align="left">P2H Forums:</td><td><input name="pages_p2h" id="pages_p2h" type="checkbox" value="1" '.$p2h_check.'/></td></tr>'."\n";
                        $edit_staff_member_array['PAGES'] .= "</table>";
                        echo style::replaceVar("tpl/admin/staff/edit-staff-member.tpl", $edit_staff_member_array);
                    
                    }

                }else{

                    $staff_query = $dbh->select("staff");
                    if($dbh->num_rows($staff_query) == 0){

                        echo "There are no staff accounts to edit!";
                    
                    }else{

                        echo "<ERRORS>";
                        while($staff_data = $dbh->fetch_array($staff_query)){

                            echo main::sub("<strong>".$staff_data['user']."</strong>", '<a href="?page=staff&sub=edit&do='.$staff_data['id'].'"><img src="'.URL.'themes/icons/pencil.png"></a>');
                        
                        }

                    }

                }

                break;
            
            case "delete":
                $staff_query = $dbh->select("staff");
                if($getvar['do'] && $dbh->num_rows($staff_query) > 1){

                    $dbh->delete("staff", array("id", "=", $getvar['do']));
                    main::errors("Staff Account Deleted!");
                
                }elseif($getvar['do']){

                    main::errors("Theres only one staff account!");
                
                }

                if($dbh->num_rows($staff_query) == 0){

                    echo "There are no staff accounts to edit!";
                
                }else{

                    $staff_query = $dbh->select("staff"); //This pulls the current staff list after deletion.
                    echo "<ERRORS>";
                    while($staff_data = $dbh->fetch_array($staff_query)){

                        echo main::sub("<strong>".$staff_data['user']."</strong>", '<a href="?page=staff&sub=delete&do='.$staff_data['id'].'"><img src="'.URL.'themes/icons/delete.png"></a>');
                    
                    }

                }

                break;
        
        }

    }

}

?>