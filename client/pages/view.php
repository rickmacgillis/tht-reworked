<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Client Area - View Package
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
        
        $client_data     = $dbh->client($_SESSION['cuser']);
        $packages_data   = $dbh->select("packages", array("id", "=", $client_data['pid']));
        
        $view_package_array['USER']        = $client_data['user'];
        $view_package_array['SIGNUP']      = main::convertdate("n/d/Y", $client_data['signup']);
        $view_package_array['DOMAIN']      = $client_data['domain'];
        $view_package_array['PACKAGE']     = $packages_data['name']." <a href = '?page=upgrade'>Change</a>";
        $view_package_array['DESCRIPTION'] = $packages_data['description'];
        
        if($_POST){

            if(crypto::passhash($postvar['currentpass'], $client_data['salt']) == $client_data['password']){

                if($postvar['newpass'] == $postvar['cpass']){

                    $cmd = main::changeClientPassword($client_data['id'], $postvar['newpass']);
                    if($cmd === true){

                        main::errors("Details updated!");
                    
                    }else{

                        main::errors((string) $cmd);
                    
                    }

                }else{

                    main::errors("Your passwords don't match!");
                
                }

            }else{

                main::errors("Your current password wasn't correct!");
            
            }

        }

        echo style::replaceVar("tpl/client/view-package.tpl", $view_package_array);
    
    }

}

?>