<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Subdomains
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

        $this->navtitle  = "Subdomain Sub Menu";
        $this->navlist[] = array("Add Subdomain Domain", "add.png", "add");
        $this->navlist[] = array("Edit Subdomain Domain", "pencil.png", "edit");
        $this->navlist[] = array("Delete Subdomain Domain", "delete.png", "delete");
    
    }

    public function description(){

        return "<strong>Managing Subdomains</strong><br />
                This is where you add domains so users can make subdomains with them.<br />
                To get started, choose a link from the sidebar's SubMenu.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            default:
                if($_POST){

                    check::empty_fields();
                    if(!main::errors()){

                        $subdomains_insert = array(
                            "domain" => $postvar['domain'],
                            "server" => $postvar['server']
                        );
                        
                        $dbh->insert("subdomains", $subdomains_insert);
                        main::errors("Subdomain domain has been added!");
                    
                    }

                }

                $servers_query = $dbh->select("servers");
                if($dbh->num_rows($servers_query) == 0){

                    echo "There are no servers, you need to add a server first!";
                    return;
                
                }

                while($servers_data = $dbh->fetch_array($servers_query)){

                    $values[] = array($servers_data['name'], $servers_data['id']);
                
                }

                $add_subdomain_array['SERVER'] = main::dropDown("server", $values);
                echo style::replaceVar("tpl/admin/subdomains/add-subdomain.tpl", $add_subdomain_array);
                break;
            
            case "edit":
                if(isset($getvar['do'])){

                    $subdomains_data = $dbh->select("subdomains", array("id", "=", $getvar['do']));
                    if(!$subdomains_data['id']){

                        echo "That subdomain domain doesn't exist!";
                    
                    }else{

                        if($_POST){

                            check::empty_fields();
                            if(!main::errors()){

                                $subdomains_update = array(
                                    "domain" => $postvar['domain'],
                                    "server" => $postvar['server']
                                );
                                
                                $dbh->update("subdomains", $subdomains_update, array("id", "=", $getvar['do']));
                                
                                //Subdomain added
                                main::done();
                            
                            }

                        }

                        $edit_subdomain_array['DOMAIN']    = $subdomains_data['domain'];
                        $servers_query      = $dbh->select("servers");
                        while($servers_data = $dbh->fetch_array($servers_query)){

                            $values[] = array($servers_data['name'], $servers_data['id']);
                        
                        }

                        $edit_subdomain_array['SERVER'] = main::dropDown("server", $values, $servers_data['server']);
                        echo style::replaceVar("tpl/admin/subdomains/edit-subdomain.tpl", $edit_subdomain_array);
                    
                    }

                }else{

                    $subdomains_query = $dbh->select("subdomains");
                    if($dbh->num_rows($subdomains_query) == 0){

                        echo "There are no subdomain domains to edit!";
                    
                    }else{

                        echo "<ERRORS>";
                        while($subdomains_data = $dbh->fetch_array($subdomains_query)){

                            echo main::sub("<strong>".$subdomains_data['domain']."</strong>", '<a href="?page=sub&sub=edit&do='.$subdomains_data['id'].'"><img src="'.URL.'themes/icons/pencil.png"></a>');
                        
                        }

                    }

                }

                break;
            
            case "delete":
                if(isset($getvar['do'])){

                    $dbh->delete("subdomains", array("id", "=", $getvar['do']));
                    main::errors("Subdomain Deleted!");
                
                }

                $subdomains_query = $dbh->select("subdomains");
                if($dbh->num_rows($subdomains_query) == 0){

                    echo "There are no subdomain domains to delete!";
                
                }else{

                    echo "<ERRORS>";
                    while($subdomains_data = $dbh->fetch_array($subdomains_query)){

                        echo main::sub("<strong>".$subdomains_data['domain']."</strong>", '<a href="?page=sub&sub=delete&do='.$subdomains_data['id'].'"><img src="'.URL.'themes/icons/delete.png"></a>');
                    
                    }

                }

                break;
        
        }

    }

}

?>