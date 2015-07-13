<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Change Password
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        if($_POST){

            check::empty_fields();
            if(!main::errors()){

                $user = $dbh->staff($_SESSION['user']);
                if(!$user['password']){

                    main::errors("Wrong username!?");
                
                }else{

                    if(crypto::passhash($postvar['old'], $user['salt']) == $user['password']){

                        if($postvar['new'] != $postvar['confirm']){

                            main::errors("Your passwords don't match!");
                        
                        }else{

                            $salt    = crypto::salt();
                            $newpass = crypto::passhash($postvar['new'], $salt);
                            
                            $update_staff = array(
                                "password" => $newpass,
                                "salt"     => $salt
                            );
                            $dbh->update("staff", $update_staff, array("id", "=", $_SESSION['user']));
                            main::errors("Password changed!");
                        
                        }

                    }else{

                        main::errors("Your old password was wrong!");
                    
                    }

                }

            }

        }

        echo style::replaceVar("tpl/admin/change-admin-password.tpl");
    
    }

}

?>