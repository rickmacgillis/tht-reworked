<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Coupons
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

        $this->navtitle  = "Coupons";
        $this->navlist[] = array("Configuration", "cog.png", "config");
        $this->navlist[] = array("Add Coupons", "add.png", "add");
        $this->navlist[] = array("Edit Coupons", "pencil.png", "edit");
        $this->navlist[] = array("Delete Coupons", "delete.png", "delete");
    
    }

    public function description(){

        return "<strong>Coupons</strong><br />
                Here is where you can create coupons to be used for your hosting plans.  This coupon system gives you a lot of control over
                how your coupons work.  Click the \"Add Coupons\" link to see all those purrty options.  =3";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        if($_POST){

            if($postvar['coupid']){

                //Edit
                main::errors(coupons::validate_admin_form($postvar['coupid']));
            
            }else{

                if($postvar['update']){

                    //Config
                    check::empty_fields();
                    if(!main::errors()){

                        if(!main::isint($postvar['graceperiod'])){

                            $error = "Please enter the number of days for the grace period.";
                        
                        }

                        if($error){

                            main::errors($error);
                        
                        }else{

                            $dbh->updateConfig("multicoupons", $postvar['multicoup']);
							$dbh->updateConfig("p2hgraceperiod", $postvar['graceperiod']);
                            main::errors("Configuration updated.");
                        
                        }

                    }

                
                }else{

                    //Add
                    main::errors(coupons::validate_admin_form("add"));
                
                }

            }

        }

        //Used on both add and edit
        $users[]     = array("Any User", "all");
        $users[]     = array("Other (Enter it in the textbox)", "newuser");
        $users_query = $dbh->select("users", 0, array("user", "ASC"));
        while($users_data = $dbh->fetch_array($users_query)){

            $pack_info = main::uidtopack($users_data['id']);
            if($pack_info['packages']['type'] != "free"){

                unset($space);
                if($pack_info['packages']['type'] == "p2h"){

                    $space = " "; //Told you I'm a perfectionist sometimes.  ;)
                
                }

                $users[] = array("[".$pack_info['packages']['type']."] ".$space.$users_data['user'], $users_data['user']);
            
            }

        }

        $order_packs[]  = array("type", "ASC");
        $order_packs[]  = array("name", "ASC");
        $packages_query = $dbh->select("packages", array("type", "!=", "free"), $order_packs, 0, 1);
        while($packages_data = $dbh->fetch_array($packages_query)){

            $additional = type::additional($packages_data['id']);
            $monthly    = $additional['monthly'];
            $signup     = $additional['signup'];
            
            unset($info);
            if($packages_data['type'] == "p2h"){

                $info = "(Init=".$signup.", monthly=".$monthly.")";
            
            }else{

                $info = "(".$monthly." ".$dbh->config("currency").")";
            
            }

            $packages[] = array("[".$packages_data['type']."] ".$packages_data['name']." ".$info, $packages_data['id']);

        }

        $area[] = array("Both Areas", "both");
        $area[] = array("Order Area Only", "orders");
        $area[] = array("Invoices Area Only", "invoices");
        
        $goodfor[] = array("Account Lifetime", "life");
        $goodfor[] = array("Current Bill", "current");
        $goodfor[] = array("Set Number Of Months", "months");
        
        $paidtype[] = array($dbh->config("currency"), "0");
        $paidtype[] = array("%", "1");
        
        $p2hinittype[] = array("Total", "0");
        $p2hinittype[] = array("% Of", "1");
        
        $p2hmonthlytype[] = array("Total", "0");
        $p2hmonthlytype[] = array("% Of", "1");
        
        switch($getvar['sub']){

            case "add":
                
                $couponsform_array['ID']               = "";
                $couponsform_array['COUPNAME']         = "";
                $couponsform_array['SHORTDESC']        = "";
                $couponsform_array['COUPCODE']         = "";
                $couponsform_array['PAID']             = "";
                $couponsform_array['INITPOSTS']        = "";
                $couponsform_array['MONTHLYPOSTS']     = "";
                $couponsform_array['NOEXPIRE']         = "";
                $couponsform_array['EXPIREDATE']       = "";
                $couponsform_array['GOODFORMONTHS']    = "";
                $couponsform_array['USERNAME']         = "";
                $couponsform_array['ALLPACKS']         = "";
                $couponsform_array['LIMITEDCOUPONS']   = "";
                $couponsform_array['UNLIMITEDCOUPONS'] = "";
                $couponsform_array['ADDEDIT']          = "Add";
                $couponsform_array['PAIDTYPE']         = main::dropDown("paidtype", $paidtype, "0", 0);
                $couponsform_array['P2HINITTYPE']      = main::dropDown("p2hinittype", $p2hinittype, "0", 0);
                $couponsform_array['P2HMONTHLYTYPE']   = main::dropDown("p2hmonthlytype", $p2hmonthlytype, "0", 0);
                $couponsform_array['USERNAMES']        = main::dropDown("userselect", $users, "all", 0);
                $couponsform_array['PACKAGES']         = main::dropDown("packages", $packages, 0, 0);
                $couponsform_array['AREA']             = main::dropDown("area", $area, "both", 0);
                $couponsform_array['GOODFOR']          = main::dropDown("goodfor", $goodfor, "life", 0);
                
                echo style::replaceVar("tpl/admin/coupons/coupons-form.tpl", $couponsform_array);
                
                break;
            
            case "edit":
                
                if(is_numeric($getvar['edit'])){

                    $coup_data = coupons::coupon_data("", $getvar['edit']);
                    
                    $couponsform_array['ID']             = $coup_data['id'];
                    $couponsform_array['COUPNAME']       = $coup_data['coupname'];
                    $couponsform_array['SHORTDESC']      = $coup_data['shortdesc'];
                    $couponsform_array['COUPCODE']       = $coup_data['coupcode'];
                    $couponsform_array['PAID']           = $coup_data['paiddisc'];
                    $couponsform_array['INITPOSTS']      = $coup_data['p2hinitdisc'];
                    $couponsform_array['MONTHLYPOSTS']   = $coup_data['p2hmonthlydisc'];
                    $couponsform_array['ADDEDIT']        = "Edit";
                    $couponsform_array['AREA']           = main::dropDown("area", $area, $coup_data['area'], 0);
                    $couponsform_array['GOODFOR']        = main::dropDown("goodfor", $goodfor, $coup_data['goodfor'], 0);
                    $couponsform_array['PAIDTYPE']       = main::dropDown("paidtype", $paidtype, $coup_data['paidtype'], 0);
                    $couponsform_array['P2HINITTYPE']    = main::dropDown("p2hinittype", $p2hinittype, $coup_data['p2hinittype'], 0);
                    $couponsform_array['P2HMONTHLYTYPE'] = main::dropDown("p2hmonthlytype", $p2hmonthlytype, $coup_data['p2hmonthlytype'], 0);
                    
                    if($coup_data['packages'] == "all"){

                        $couponsform_array['ALLPACKS'] = "checked";
                        $couponsform_array['PACKAGES'] = main::dropDown("packages", $packages, 0, 0);
                    
                    }else{

                        $coup_data['packages'] = explode(",", $coup_data['packages']);
                        $couponsform_array['ALLPACKS']     = "";
                        $couponsform_array['PACKAGES']     = main::dropDown("packages", $packages, $coup_data['packages'], 0);
                    
                    }

                    if(empty($coup_data['limited'])){

                        $couponsform_array['UNLIMITEDCOUPONS'] = "checked";
                        $couponsform_array['LIMITEDCOUPONS']   = "";
                    
                    }else{

                        $couponsform_array['UNLIMITEDCOUPONS'] = "";
                        $couponsform_array['LIMITEDCOUPONS']   = $coup_data['limited'];
                    
                    }

                    if($coup_data['expiredate'] == "99/99/9999"){

                        $couponsform_array['NOEXPIRE']   = "checked";
                        $couponsform_array['EXPIREDATE'] = "";
                    
                    }else{

                        $couponsform_array['NOEXPIRE']   = "";
                        $couponsform_array['EXPIREDATE'] = $coup_data['expiredate'];
                    
                    }

                    if($coup_data['goodfor'] == "months"){

                        $couponsform_array['GOODFORMONTHS'] = $coup_data['monthsgoodfor'];
                    
                    }else{

                        $couponsform_array['GOODFORMONTHS'] = "";
                    
                    }

                    if($coup_data['user'] != "all"){

                        $users_data = $dbh->fetch_array($users_query);
                        
                        if(@in_array($coup_data['user'], $users_data)){

                            $couponsform_array['USERNAME'] = "";
                        
                        }else{

                            $couponsform_array['USERNAME'] = $coup_data['user'];
                            $coup_data['user'] = "newuser"; //This prepares it for the drop down box selection.
                        
                        }

                    
                    }else{

                        $couponsform_array['USERNAME'] = "";
                    
                    }

                    $couponsform_array['USERNAMES'] = main::dropDown("userselect", $users, $coup_data['user'], 0);
                    
                    echo style::replaceVar("tpl/admin/coupons/coupons-form.tpl", $couponsform_array);
                    
                }else{

                    $coupons_list = $dbh->select("coupons", 0, array("coupname", "ASC"));
                    while($coupons_list_data = $dbh->fetch_array($coupons_list)){

                        $edit_coupons_list_item_array['ID']          = $coupons_list_data['id'];
                        $edit_coupons_list_item_array['NAME']        = $coupons_list_data['coupname'];
                        $edit_coupons_list_item_array['DESCRIPTION'] = "Code: ".$coupons_list_data['coupcode']."<br>".$coupons_list_data['shortdesc'];
                        echo style::replaceVar("tpl/admin/coupons/edit-coupons-list-item.tpl", $edit_coupons_list_item_array);
                        $found_coupons = "1";
                    
                    }

                    if(!$found_coupons){

                        echo "No coupons to edit, my friend.  =)";
                    
                    }

                }

                break;
            
            case "delete":
                
                if(is_numeric($getvar['delete'])){

                    if($postvar['yes']){

                        $dbh->delete("coupons", array("id", "=", $getvar['delete']), 1);
                        main::redirect("?page=coupons&sub=delete");
                    
                    }elseif($postvar['no']){

                        main::redirect("?page=coupons&sub=delete");
                    
                    }else{

                        $warning_array['HIDDEN'] = "<input type = 'hidden' name = 'confirm' value = 'confirm'>";
                        echo style::replaceVar("tpl/warning.tpl", $warning_array);
                    
                    }

                
                }else{

                    $coupons_list = $dbh->select("coupons", 0, array("coupname", "ASC"));
                    while($coupons_list_data = $dbh->fetch_array($coupons_list)){

                        $delete_coupons_list_item_array['ID']          = $coupons_list_data['id'];
                        $delete_coupons_list_item_array['NAME']        = $coupons_list_data['coupname'];
                        $delete_coupons_list_item_array['DESCRIPTION'] = "Code: ".$coupons_list_data['coupcode']."<br>".$coupons_list_data['shortdesc'];
                        echo style::replaceVar("tpl/admin/coupons/delete-coupons-list-item.tpl", $delete_coupons_list_item_array);
                        $found_coupons = "1";
                    
                    }

                    if(!$found_coupons){

                        echo "No coupons to delete, my friend.  =)";
                    
                    }

                }

                break;
            
            case "config":
                $coupopts[] = array("Yes", "1");
                $coupopts[] = array("No", "0");
                
                $coupon_options_array['MULTICOUP']   = main::dropDown("multicoup", $coupopts, $dbh->config("multicoupons"), 0);
                $coupon_options_array['GRACEPERIOD'] = $dbh->config("p2hgraceperiod");
                echo style::replaceVar("tpl/admin/coupons/coupon-options.tpl", $coupon_options_array);
                
                break;
        
        }

    }

}

?>