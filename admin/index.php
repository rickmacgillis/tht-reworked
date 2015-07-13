<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Compile THT
define("INC", "../includes");
define("CRON", 0);
include(INC."/compiler.php");

//THT Variables
define("PAGE", "Admin Area");

//Main ACP Function - Creates the ACP basically
function acp(){
    global $dbh, $postvar, $getvar, $instance;
	
    ob_start();      
    if($_SESSION['clogged'] || $_SESSION['cuser']){

        session_destroy();
        main::redirect("?page=home");
    
    }

    if(!$getvar['page']){

        $getvar['page'] = "home";
    
    }

    $page = $dbh->select("acpnav", array("link", "=", $getvar['page']));
    // "Hack" to get the credits and tickets page looking nicer
    switch($getvar["page"]){

        case "credits":
            $header = "Credits";
            break;
        
        default:
            if($page['visual'] == "Tickets" && $getvar['mode'] == 'ticketsall'){

                $header = "All Tickets";
            
            }else{

                $header = $page['visual'];
            
            }

            break;
    
    }

    $link       = "pages/".$getvar['page'].".php";
    $staff_data = $dbh->select("staff", array("id", "=", $_SESSION['user']));
    $user_perms = $staff_data['perms'];
    if(substr_count($user_perms, "paid") == '1'){

        $nopaid = '1';
    
    }

    if(substr_count($user_perms, "p2h") == '1'){

        $nop2h = '1';
    
    }

    if(!file_exists($link)){

        $html = "<strong>THT Fatal Error:</strong> That page doesn't exist.";
    
    }elseif(!main::checkPerms($page['id']) && !$nopaid && !$nop2h && $user_perms){

        $html = "You don't have access to this page.";
        
    }elseif($getvar['page'] == "type" && $getvar['type'] == "paid" && $nopaid){

        $html = "You don't have access to this page.";
        
    }elseif($getvar['page'] == "type" && $getvar['type'] == "p2h" && $nop2h){

        $html = "You don't have access to this page.";
        
    }else{

            include($link);
            $content = new page;
            // Main Side Bar HTML
            $nav     = "Sidebar Menu";
            
            $sub = $dbh->select("acpnav", 0, array("id", "ASC"));
            while($row = $dbh->fetch_array($sub)){

                if(main::checkPerms($row['id'])){

                    $sidebarlink_array['IMGURL'] = $row['icon'];
                    $sidebarlink_array['LINK']   = "?page=".$row['link'];
                    $sidebarlink_array['VISUAL'] = $row['visual'];
                    $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebarlink_array);
                
                }

            }

            // Types Navbar
            /*
             * When Working on the navbar, to make a spacer use this:
             * $sidebar_array['LINKS'] .= style::replaceVar("tpl/spacer.tpl");
             */
            foreach($instance->packtypes as $key => $value){

                if(($key == "paid" && $nopaid != "1") || ($key == "p2h" && $nop2h != "1") || ($key != "paid" && $key != "p2h")){

                    if($instance->packtypes[$key]->acpNav){

                        foreach($instance->packtypes[$key]->acpNav as $key2 => $value){

                            $sidebarlink_array['IMGURL'] = $value[2];
                            $sidebarlink_array['LINK']   = "?page=type&type=".$key."&sub=".$value[1];
                            $sidebarlink_array['VISUAL'] = $value[0];
                            $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebarlink_array);
                            if($getvar['page'] == "type" && $getvar['type'] == $key && $getvar['sub'] == $value[1]){

                                define("SUB", $value[3]);
                                $header                   = $value[3];
                                $getvar['myheader'] = $value[3];
                            
                            }

                        }

                    }

                }

            }

            $sidebarlink_array['IMGURL'] = "information.png";
            $sidebarlink_array['LINK']   = "?page=credits";
            $sidebarlink_array['VISUAL'] = "Credits";
            $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebarlink_array);
            $sidebarlink_array['IMGURL'] = "delete.png";
            $sidebarlink_array['LINK']   = "?page=logout";
            $sidebarlink_array['VISUAL'] = "Logout";
            $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebarlink_array);
            $sidebar = style::replaceVar("tpl/sidebar.tpl", $sidebar_array);
            
            //Page Sidebar
            if($content->navtitle){

                $subnav = $content->navtitle;
                foreach($content->navlist as $key => $value){

                    $sub_sidebarlink_array['IMGURL'] = $value[1];
                    $sub_sidebarlink_array['LINK']   = "?page=".$getvar['page']."&sub=".$value[2];
                    $sub_sidebarlink_array['VISUAL'] = $value[0];
                    $sub_sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sub_sidebarlink_array);
                
                }

                $subsidebar = style::replaceVar("tpl/sidebar.tpl", $sub_sidebar_array);
            
            }

            if($getvar['sub'] && $getvar['page'] != "type"){

                foreach($content->navlist as $key => $value){

                    if($value[2] == $getvar['sub']){

                        if(!$value[0]){

                            define("SUB", $getvar['page']);
                            $header = $getvar['page'];
                        
                        }else{

                            define("SUB", $value[0]);
                            $header = $value[0];
                        
                        }

                    }

                }

            }

            if($getvar['sub'] == "delete" && isset($getvar['do']) && !$_POST && !$getvar['confirm']){

                foreach($postvar as $key => $value){

                    $warning_array['HIDDEN'] .= '<input name="'.$key.'" type="hidden" value="'.$value.'" />';
                
                }

                $warning_array['HIDDEN'] .= " ";
                $html = style::replaceVar("tpl/warning.tpl", $warning_array);
            
            }elseif($getvar['sub'] == "delete" && isset($getvar['do']) && $_POST && !$getvar['confirm']){

                if($postvar['yes']){

                    foreach($getvar as $key => $value){

                        if($i){

                            $i = "&";
                        
                        }else{

                            $i = "?";
                        
                        }

                        $url .= $i.$key."=".$value;
                    
                    }

                    $url .= "&confirm=1";
                    main::redirect($url);
                
                }elseif($postvar['no']){

                    main::done();
                
                }

            }else{

                if(isset($getvar['sub'])){

                    ob_start();
                    $content->content();
                    $html = ob_get_contents(); // Retrieve the HTML
                    ob_clean(); // Flush the HTML
                
                }elseif($content->navlist){

                    $html .= $content->description(); // First, we gotta get the page description.
                    $html .= "<br /><br />"; // Break it up
                    // Now we should prepend some stuff here
                    $subsidebar2 .= "<strong>Page Submenu</strong><div class='break'></div>";
                    $subsidebar2 .= $subsidebar;
                    // Done, now output it in a sub() table
                    $html .= main::sub($subsidebar2, NULL); // Initial implementation, add the SubSidebar(var) into the description, basically append it 
                
                }else{

                    ob_start();
                    $content->content();
                    $html = ob_get_contents(); // Retrieve the HTML
                    ob_clean(); // Flush the HTML
                
                }

            }

    }

    $staffuser = $dbh->staff($_SESSION['user']);
    define("SUB", $header);
    define("INFO", '<b>Welcome back, '.strip_tags($staffuser['name']).'</b><br />'.SUB);
    
    echo '<div id="left">';
    echo main::table($nav, $sidebar);
    if($content->navtitle){

        echo "<br />";
        echo main::table($subnav, $subsidebar);
    
    }

    echo '</div>';
    
    echo '<div id="right">';
    echo main::table($header, $html);
    echo '</div>';
    
    $html_buff = ob_get_contents();
    ob_clean(); 
    
    return $html_buff; 

}

if(!$_SESSION['logged']){

    if($_SESSION['clogged'] || $_SESSION['cuser']){

        session_destroy();
        main::redirect("?page=home");
    
    }

    if($getvar['page'] == "forgotpass"){

        define("SUB", "Reset Password");
        define("INFO", SUB);
        echo style::get("header.tpl");
        
        if($_POST){

            check::empty_fields();
            if(!main::errors()){

                $user = $postvar['user'];
                $user_email = $postvar['email'];
                unset($where);
                $where[]          = array("user", "=", $user, "AND");
                $where[]          = array( "email", "=", $user_email);
                $find_staff_query = $dbh->select("staff", $where, 0, 0, 1);
                if($dbh->num_rows($find_staff_query) == 0){

                    main::errors("That account doesn't exist!");
                
                }else{

                    $curstaff     = $dbh->fetch_array($find_staff_query);
                    $password     = rand(0, 999999);
                    $salt         = crypto::salt();
                    $newpass      = crypto::passhash($password, $salt);
                    $update_staff = array("password" => $newpass,
										  "salt"     => $salt);
                    $dbh->update("staff", $update_staff, array("id", "=", $curstaff['id']));
                    main::errors("Password reset!");
                    $forgotpass_email_array['PASS'] = $password;
                    $forgotpass_email_array['LINK'] = $dbh->config("url").ADMINDIR;
                    
                    $emaildata = email::emailTemplate("admin-password-reset");
                    email::send($user_email, $emaildata['subject'], $emaildata['content'], $forgotpass_email_array);
                
                }

            }

        }

        echo '<div align="center">'.main::table("Admin Area - Reset Password", style::replaceVar("tpl/admin/login/admin-password-reset.tpl"), "300px").'</div>';
        
        echo style::get("footer.tpl");
    
    }else{

        define("SUB", "Login");
        define("INFO", "<b>Welcome to <NAME></b><br>".SUB);
        if($_POST){
			// If user submitts form
            if(main::staffLogin($postvar['user'], $postvar['pass'])){

                $queryString = $_SERVER["QUERY_STRING"];
                if($queryString == ""){

                    $queryString = "page=home";
                
                }

                main::redirect(URL.ADMINDIR."/?".$queryString);
            
            }else{

                main::errors("Incorrect username or password!");
            
            }

        }

        echo style::get("header.tpl");
        echo '<div align="center">'.main::table("Admin Area - Login", style::replaceVar("tpl/admin/login/admin-login.tpl"), "300px").'</div>';
        echo style::get("footer.tpl");
    
    }

}elseif($_SESSION['logged']){

    if(!$getvar['page']){

        $getvar['page'] = "home";
    
    }elseif($getvar['page'] == "logout"){

        session_destroy();
        main::redirect("?page=home");
    
    }

    $content = acp();
    echo style::get("header.tpl");
    echo $content;
    echo style::get("footer.tpl");

}

//End the sctipt
include(INC."/output.php");

?>