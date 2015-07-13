<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Ajax Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("INC", ".");
include("compiler.php");

class Ajax{

    public function status(){
        global $dbh, $postvar, $getvar, $instance;
		
        if($_SESSION['logged'] || $_SESSION['cuser']){

            $userid   = $_SESSION['cuser'];
            $id       = $getvar['id'];
            $status   = $getvar['status'];
			
			unset($where);			
			if($userid){
				
				$where[]  = array("id", "=", $id, "AND");
				$where[]  = array("userid", "=", $userid);
				
			}else{
			
				$where[]  = array("id", "=", $id);
			
			}
			
            $response = $dbh->update("tickets", array("status" => $status), $where);
            if($response){

                echo "<img src=".URL."themes/icons/accept.png>";
            
            }else{

                echo "<img src=".URL."themes/icons/cross.png>";
            
            }

        }

    }

    function navbar(){
        global $dbh, $postvar, $getvar, $instance;
		
        if($_SESSION['logged']){

            if($postvar['action']){

                $action = $postvar['action'];
                $id     = $postvar['id'];
                $name   = $postvar['name'];
                $icon   = $postvar['icon'];
                $link   = $postvar['link'];
                switch($action){

                    case "add":
                        if($postvar['name'] && $postvar['icon'] && $postvar['link']){

                            $navbar_insert = array(
                                "visual" => $name,
                                "icon"   => $icon,
                                "link"   => $link
                            );
                            
                            $dbh->insert("navbar", $navbar_insert);
                        
                        }

                        break;
                    case "edit":
                        if($postvar['id'] && $postvar['name'] && $postvar['icon'] && $postvar['link']){

                            $navbar_update = array(
                                "visual" => $name,
                                "icon"   => $icon,
                                "link"   => $link
                            );
                            
                            $dbh->update("navbar", $navbar_update, array("id", "=", $id));
                        
                        }

                        break;
                    case "delete":
                        if($postvar['id']){

                            $dbh->delete("navbar", array("id", "=", $postvar['id']));
                        
                        }

                        break;
                    case "order":
                        if($postvar['order']){

                            $ids = explode("-", $postvar['order']);
                            $i   = 0;
                            foreach($ids as $id){

                                $dbh->update("navbar", array("sortorder" => $i), array("id", "=", $id));
                                $i++;
                            
                            }

                        }

                        break;
                
                }

            }

        }

    }
	
    public function search(){
        global $dbh, $postvar, $getvar, $instance;
		
        if($_SESSION['logged']){

            $type  = $getvar['type'];
            $value = $getvar['value'];
            if($getvar['num']){

                $show = $getvar['num'];
            
            }else{

                $show = 10;
            
            }

            if($getvar['page'] != 1){

                $lower = $getvar['page'] * $show;
                $lower = $lower - $show;
                $upper = $lower + $show;
            
            }else{

                $lower = 0;
                $upper = $show;
            
            }

            $users_query = $dbh->select("users", array($type, "LIKE", "%".$value."%"), array($type, "ASC"), $lower.", ".$upper, 1);
            if($dbh->num_rows($users_query) == 0){

                echo "No clients found!";
            
            }else{

                while($users_data = $dbh->fetch_array($users_query)){

                    if($n != $show){

                        $client          = $dbh->client($users_data['id']);
                        $client_search_box_array['ID']     = $client['id'];
                        $client_search_box_array['USER']   = $client['user'];
                        $client_search_box_array['DOMAIN'] = $client['domain'];
                        $client_search_box_array['URL']    = URL;
						
						switch($client['status']){
						
							case"1":

								$client_search_box_array['TEXT'] = "Suspend";
								$client_search_box_array['FUNC'] = "sus";
								$client_search_box_array['IMG']  = "exclamation.png";							
								break;
						
							case"2":

								$client_search_box_array['TEXT'] = "Unsuspend";
								$client_search_box_array['FUNC'] = "unsus";
								$client_search_box_array['IMG']  = "accept.png";							
								break;
						
							case"3":

								$client_search_box_array['TEXT'] = "Validate";
								$client_search_box_array['FUNC'] = "none";
								$client_search_box_array['IMG']  = "user_suit.png";							
								break;
						
							case"4":

								$client_search_box_array['TEXT'] = "Awaiting Payment";
								$client_search_box_array['FUNC'] = "none";
								$client_search_box_array['IMG']  = "money.png";							
								break;
						
							case"5":

								$client_search_box_array['TEXT'] = "Awaiting Email Confirmation";
								$client_search_box_array['FUNC'] = "none";
								$client_search_box_array['IMG']  = "email.png";							
								break;
						
							default:

								$client_search_box_array['TEXT'] = "Other Status";
								$client_search_box_array['FUNC'] = "none";
								$client_search_box_array['IMG']  = "help.png";							
								break;
						
						}

                        echo style::replaceVar("tpl/admin/clients/client-search-box.tpl", $client_search_box_array);
                        $n++;
                    
                    }

                }

                echo '<div class="break"></div>';
                echo '<div align="center">';
                $num   = $dbh->num_rows($users_query);
                $pages = ceil($num / $show);
                echo "Page";
                for($i; $i != $pages + 1; $i += 1){

                    echo ' <a href="Javascript: page(\''.$i.'\')">'.$i.'</a>';
                
                }

                echo '</div>';
            
            }

        }

    }
	
	public function couponcheck() {
        global $dbh, $postvar, $getvar, $instance;
		
		if(empty($getvar['coupon'])) {
		
			echo 1;
			return;
			
		}else{
		
			$package_type = type::packagetype($getvar['package']);
			if($package_type == "free"){
				
				echo 0;
				return;
				
			}

		    $coupon_text = coupons::validate_coupon($getvar['coupon'], $getvar['location'], $getvar['username'], $getvar['package']);
		    if($coupon_text){
			
				echo $coupon_text;
				return;
			
		    }else{
			
				echo 0;
				return;
			
		    }
			   
		}
	}

	//BELOW ARE FUNCTIONS STILL IN THE CODE, BUT ARE NOT IN USE DO TO THEIR FEATURES BEING DISABLED.
	
    // Sets the order of the rows in a table.
    // $postvar["order"] should be a comma separated list of IDs for $postvar["table"]
    // $postvar["table"] should NOT include the table prefix.
	
	/*
    function setOrderOfRows(){
        global $dbh, $postvar, $getvar, $instance;

        if($_SESSION['logged'] && isset($postvar["table"]) && isset($postvar["order"])){

            $i = 0;
            foreach(explode(',', $postvar["order"]) as $id){

                $response = $dbh->update("orderfields", array("sortorder" => $i), array("id", "=", $id));
                if(!$response){

                    echo '0';
                    return;
                
                }

                $i++;
            
            }

            echo '1';
        
        }

    }
	*/

}

if(isset($getvar['function']) and $getvar['function'] != ""){

    $Ajax = new Ajax();
    if(method_exists($Ajax, $getvar['function'])){

        $Ajax->{$getvar['function']}();
        require(INC."/output.php");
    
    }

}

?>