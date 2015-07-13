<?php

//////////////////////////////
// The Hosting Tool - Coupons
// Admin Area - Coupons
// By Na'ven Enigma
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1) {
    die();
}

class page {

    public $navtitle;
    public $navlist = array();

    public function __construct() {
        $this->navtitle = "Coupons";
        $this->navlist[] = array("Configuration", "cog.png", "config");
        $this->navlist[] = array("Add Coupons", "add.png", "add");
        $this->navlist[] = array("Edit Coupons", "pencil.png", "edit");
        $this->navlist[] = array("Delete Coupons", "delete.png", "delete");
    }

    public function description() {
        return "<strong>Coupons</strong><br />
                Here is where you can create coupons to be used for your hosting plans.  This coupon system gives you a lot of control over
                how your coupons work.  Click the \"Add Coupons\" link to see all those purrty options.  =3";
    }

    public function content() { # Displays the page
        global $main;
        global $db;
        global $type;
        global $style;
        global $navens_coupons;
        global $sdk;

        if($_POST) {
            if($main->postvar['coupid']) {
                //Edit
                $main->errors($navens_coupons->validate_admin_form($main->postvar['coupid']));
            }
            else {
                if($main->postvar['update']) {
                    //Config
                    foreach($main->postvar as $key => $value) {
                        if($value == "" && !$n) {
                            $main->errors("Please fill in all the required fields!");
                            $n++;
                        }
                    }
                    if(!$n) {
                        if(!$sdk->isint($main->postvar['graceperiod'])) {
                            $error = "Please enter the number of days for the grace period.";
                        }

                        if($error) {
                            $main->errors($error);
                        }
                        else {
                            $db->query("UPDATE <PRE>mod_navens_coupons_config SET configvalue = '{$main->postvar['multicoup']}' WHERE configname = 'multicoupons' LIMIT 1");
                            $db->query("UPDATE <PRE>mod_navens_coupons_config SET configvalue = '{$main->postvar['graceperiod']}' WHERE configname = 'p2hgraceperiod' LIMIT 1");
                            $main->errors("Configuration updated.");
                        }
                    }

                }
                else {
                    //Add
                    $main->errors($navens_coupons->validate_admin_form("add"));
                }
            }
        }

        //Used on both add and edit
        $users[] = array("Any User", "all");
        $users[] = array("Other (Enter it in the textbox)", "newuser");
        $users_query = $db->query("SELECT * FROM <PRE>users ORDER BY `user` ASC");
        while($users_data = $db->fetch_array($users_query)) {
            $pack_info = $sdk->uidtopack($users_data['id']);
            if($pack_info['packages']['type'] != "free") {
                unset($space);
                if($pack_info['packages']['type'] == "p2h") {
                    $space = "&nbsp;"; //Told you I'm a perfectionist sometimes.  ;)
                }
                $users[] = array("[".$pack_info['packages']['type']."] ".$space.$users_data['user'], $users_data['user']);
            }
        }

        $packages_query = $db->query("SELECT * FROM <PRE>packages WHERE type != 'free' ORDER BY `type` ASC, `name` ASC");
        while($packages_data = $db->fetch_array($packages_query)) {
            $additional = $type->additional($packages_data['id']);
            $monthly = $additional['monthly'];
            $signup = $additional['signup'];

            unset($info);
            if($packages_data['type'] == "p2h") {
                $info = "(Init=".$signup.", monthly=".$monthly.")";
            }
            else {
                $info = "(".$monthly." ".$db->config("currency").")";
            }

            $packages[] = array("[".$packages_data['type']."] ".$packages_data['name']." ".$info, $packages_data['id']);
        }

        $area[] = array("Both Areas", "both");
        $area[] = array("Order Area Only", "orders");
        $area[] = array("Invoices Area Only", "invoices");

        $goodfor[] = array("Account Lifetime", "life");
        $goodfor[] = array("Current Bill", "current");
        $goodfor[] = array("Set Nuumber Of Months", "months");

        $paidtype[] = array($db->config("currency"), "0");
        $paidtype[] = array("%", "1");

        $p2hinittype[] = array("Total", "0");
        $p2hinittype[] = array("% Of", "1");

        $p2hmonthlytype[] = array("Total", "0");
        $p2hmonthlytype[] = array("% Of", "1");

        switch($main->getvar['sub']) {
            case "add":

                $array['ID'] = "";
                $array['COUPNAME'] = "";
                $array['SHORTDESC'] = "";
                $array['COUPCODE'] = "";
                $array['PAID'] = "";
                $array['INITPOSTS'] = "";
                $array['MONTHLYPOSTS'] = "";
                $array['NOEXPIRE'] = "";
                $array['EXPIREDATE'] = "";
                $array['GOODFORMONTHS'] = "";
                $array['USERNAME'] = "";
                $array['ALLPACKS'] = "";
                $array['LIMITEDCOUPONS'] = "";
                $array['UNLIMITEDCOUPONS'] = "";
                $array['ADDEDIT'] = "Add";
                $array['PAIDTYPE'] = $sdk->dropDown("paidtype", $paidtype, "0", 0);
                $array['P2HINITTYPE'] = $sdk->dropDown("p2hinittype", $p2hinittype, "0", 0);
                $array['P2HMONTHLYTYPE'] = $sdk->dropDown("p2hmonthlytype", $p2hmonthlytype, "0", 0);
                $array['USERNAMES'] = $sdk->dropDown("userselect", $users, "all", 0);
                $array['PACKAGES'] = $sdk->dropDown("packages", $packages, 0, 0);
                $array['AREA'] = $sdk->dropDown("area", $area, "both", 0);
                $array['GOODFOR'] = $sdk->dropDown("goodfor", $goodfor, "life", 0);

                echo $navens_coupons->tpl("admin/couponsform.tpl", $array);

                break;

            case "edit":

                if(is_numeric($main->getvar['edit'])) {
                    $coup_data = $navens_coupons->coupon_data("", $main->getvar['edit']);

                    $array['ID'] = $coup_data['id'];
                    $array['COUPNAME'] = $coup_data['coupname'];
                    $array['SHORTDESC'] = $coup_data['shortdesc'];
                    $array['COUPCODE'] = $coup_data['coupcode'];
                    $array['PAID'] = $coup_data['paiddisc'];
                    $array['INITPOSTS'] = $coup_data['p2hinitdisc'];
                    $array['MONTHLYPOSTS'] = $coup_data['p2hmonthlydisc'];
                    $array['ADDEDIT'] = "Edit";
                    $array['AREA'] = $sdk->dropDown("area", $area, $coup_data['area'], 0);
                    $array['GOODFOR'] = $sdk->dropDown("goodfor", $goodfor, $coup_data['goodfor'], 0);
                    $array['PAIDTYPE'] = $sdk->dropDown("paidtype", $paidtype, $coup_data['paidtype'], 0);
                    $array['P2HINITTYPE'] = $sdk->dropDown("p2hinittype", $p2hinittype, $coup_data['p2hinittype'], 0);
                    $array['P2HMONTHLYTYPE'] = $sdk->dropDown("p2hmonthlytype", $p2hmonthlytype, $coup_data['p2hmonthlytype'], 0);

                    if($coup_data['packages'] == "all") {
                        $array['ALLPACKS'] = "checked";
                        $array['PACKAGES'] = $sdk->dropDown("packages", $packages, 0, 0);
                    }
                    else {
                        $coup_data['packages'] = explode(",", $coup_data['packages']);
                        $array['ALLPACKS'] = "";
                        $array['PACKAGES'] = $sdk->dropDown("packages", $packages, $coup_data['packages'], 0);
                    }

                    if(empty($coup_data['limited'])) {
                        $array['UNLIMITEDCOUPONS'] = "checked";
                        $array['LIMITEDCOUPONS'] = "";
                    }
                    else {
                        $array['UNLIMITEDCOUPONS'] = "";
                        $array['LIMITEDCOUPONS'] = $coup_data['limited'];
                    }

                    if($coup_data['expiredate'] == "99/99/9999") {
                        $array['NOEXPIRE'] = "checked";
                        $array['EXPIREDATE'] = "";
                    }
                    else {
                        $array['NOEXPIRE'] = "";
                        $array['EXPIREDATE'] = $coup_data['expiredate'];
                    }

                    if($coup_data['goodfor'] == "months") {
                        $array['GOODFORMONTHS'] = $coup_data['monthsgoodfor'];
                    }
                    else {
                        $array['GOODFORMONTHS'] = "";
                    }

                    if($coup_data['user'] != "all") {
                        $users_data = $db->fetch_array($users_query);

                        if(@in_array($coup_data['user'], $users_data)) {
                            $array['USERNAME'] = "";
                        }
                        else {
                            $array['USERNAME'] = $coup_data['user'];
                            $coup_data['user'] = "newuser"; //This prepares it for the drop down box selection.
                        }

                    }
                    else {
                        $array['USERNAME'] = "";
                    }
                    $array['USERNAMES'] = $sdk->dropDown("userselect", $users, $coup_data['user'], 0);

                    echo $navens_coupons->tpl("admin/couponsform.tpl", $array);

                }
                else {
                    $coupons_list = $db->query("SELECT * FROM <PRE>mod_navens_coupons ORDER BY coupname ASC");
                    while($coupons_list_data = $db->fetch_array($coupons_list)) {
                        $array['ID'] = $coupons_list_data['id'];
                        $array['NAME'] = $coupons_list_data['coupname'];
                        $array['DESCRIPTION'] = "Code: ".$coupons_list_data['coupcode']."<br>".$coupons_list_data['shortdesc'];
                        echo $navens_coupons->tpl("admin/listeditcoupons.tpl", $array);
                        $found_coupons = "1";
                    }

                    if(!$found_coupons) {
                        echo "No coupons to edit, my friend.  =)";
                    }
                }

                break;

            case "delete":

                if(is_numeric($main->getvar['delete'])) {

                    if($main->postvar['yes']) {
                        $db->query("DELETE FROM <PRE>mod_navens_coupons WHERE id = '".$main->getvar['delete']."' LIMIT 1");
                        $main->redirect("?page=navens_coupons&sub=delete");
                    }
                    elseif($main->postvar['no']) {
                        $main->redirect("?page=navens_coupons&sub=delete");
                    }
                    else {
                        $array['HIDDEN'] = "<input type = 'hidden' name = 'confirm' value = 'confirm'>";
                        echo $style->replaceVar("tpl/warning.tpl", $array);
                    }

                }
                else {
                    $coupons_list = $db->query("SELECT * FROM <PRE>mod_navens_coupons ORDER BY coupname ASC");
                    while($coupons_list_data = $db->fetch_array($coupons_list)) {
                        $array['ID'] = $coupons_list_data['id'];
                        $array['NAME'] = $coupons_list_data['coupname'];
                        $array['DESCRIPTION'] = "Code: ".$coupons_list_data['coupcode']."<br>".$coupons_list_data['shortdesc'];
                        echo $navens_coupons->tpl("admin/listdeletecoupons.tpl", $array);
                        $found_coupons = "1";
                    }

                    if(!$found_coupons) {
                        echo "No coupons to delete, my friend.  =)";
                    }
                }

                break;

            case "config":
                $coupopts[] = array("Yes", "1");
                $coupopts[] = array("No", "0");

                $array['MULTICOUP'] = $sdk->dropDown("multicoup", $coupopts, $navens_coupons->coupconfig("multicoupons"), 0);
                $array['GRACEPERIOD'] = $navens_coupons->coupconfig("p2hgraceperiod");
                echo $navens_coupons->tpl("admin/coupon_options.tpl", $array);


                break;
        }
    }
}

?>
