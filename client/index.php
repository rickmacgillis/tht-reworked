<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Client Area
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Compile THT
define("INC", "../includes");
include(INC."/compiler.php");

//THT Variables
define("PAGE", "Client Area");

//Creates the client page
function client(){
    global $dbh, $postvar, $getvar, $instance;
    
    ob_start(); // Stop the output buffer
    
    if(!$getvar['page']){

        $getvar['page'] = "home";
    
    }

    $page   = $dbh->select("clientnav", array("link", "=", $getvar['page']), array("id", "ASC"));
    $header = $page['visual'];
    $link   = "pages/".$getvar['page'].".php";
    if(!file_exists($link)){

        $html = "That page doesn't exist.";
    
    }else{

        if(preg_match("/[\.*]/", $getvar['page']) == 0){

            include($link);
            $content = new page;
            // Main Side Bar HTML
            $nav     = "Sidebar";
            if(!$dbh->config("delacc")){

                $clientnav_query = $dbh->select("clientnav", array("link", "!=", "delete"), array("id", "ASC"), 0, 1);
            
            }else{

                $clientnav_query = $dbh->select("clientnav", 0, array("id", "ASC"), 0, 1);
            
            }

            while($clientnav_data = $dbh->fetch_array($clientnav_query)){

                $sidebar_link_array['IMGURL'] = $clientnav_data['icon'];
                $sidebar_link_array['LINK']   = "?page=".$clientnav_data['link'];
                $sidebar_link_array['VISUAL'] = $clientnav_data['visual'];
                $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebar_link_array);
            
            }

            // Types Navbar
            $client = $dbh->client($_SESSION['cuser']);
            $packtype = $instance->packtypes[type::packagetype($client['pid'])];
            if($packtype->clientNav){

                foreach($packtype->clientNav as $key2 => $value){

                    $sidebar_link_array['IMGURL'] = $value[2];
                    $sidebar_link_array['LINK']   = "?page=type&type=".type::packagetype($client['pid'])."&sub=".$value[1];
                    $sidebar_link_array['VISUAL'] = $value[0];
                    $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebar_link_array);
                    if($getvar['page'] == "type" && $getvar['type'] == type::packagetype($client['pid']) && $getvar['sub'] == $value[1]){

                        define("SUB", $value[3]);
                        $header                   = $value[3];
                        $getvar['myheader'] = $value[3];
                    
                    }

                }

            }
            
            $sidebar_link_array['IMGURL'] = "delete.png";
            $sidebar_link_array['LINK']   = "?page=logout";
            $sidebar_link_array['VISUAL'] = "Logout";
            $sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebar_link_array);
            $sidebar = style::replaceVar("tpl/sidebar.tpl", $sidebar_array);
            
            //Page Sidebar
            if($content->navtitle){

                $subnav = $content->navtitle;
                foreach($content->navlist as $key => $value){

                    $sidebar_link_array['IMGURL'] = $value[1];
                    $sidebar_link_array['LINK']   = "?page=".$getvar['page']."&sub=".$value[2];
                    $sidebar_link_array['VISUAL'] = $value[0];
                    $sub_sidebar_array['LINKS'] .= style::replaceVar("tpl/sidebar-link.tpl", $sidebar_link_array);
                
                }

                $subsidebar = style::replaceVar("tpl/sidebar.tpl", $sub_sidebar_array);
            
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

                    if($content->description()){

                        $html = $content->description()."<br><br>";
                    
                    }

                    $html .= "Select a sub-page from the sidebar.";
                    
                }else{

                    ob_start();
                    $content->content();
                    $html = ob_get_contents(); // Retrieve the HTML
                    ob_clean(); // Flush the HTML        
                
                }

            }

        }

    }

    if($getvar['sub'] && $getvar['page'] != "type"){

        foreach($content->navlist as $key => $value){

            if($value[2] == $getvar['sub']){

                define("SUB", $value[0]);
                $header = $value[0];
            
            }

        }

    }

    $staffuser = $dbh->client($_SESSION['cuser']);
    define("SUB", $header);
    define("INFO", '<b>Welcome back, '.$staffuser['user'].'</b><br />'.SUB);
    
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

if(!$_SESSION['clogged']){

    if($getvar['page'] == "forgotpass"){

        define("SUB", "Reset Password");
        define("INFO", SUB);
        echo style::get("header.tpl");
        
        if($_POST){

            check::empty_fields();
            if(!main::errors()){

                $user        = $postvar['user'];
                $email_reset = $postvar['email'];
                
                unset($where);
                $where[] = array("user", "=", $user, "AND");
                $where[] = array("email", "=", $email_reset);
                $client  = $dbh->select("users", $where);
                if(!$client['user']){

                    main::errors("That account doesn't exist!");
                
                }else{

                    $password = rand();
                    $cmd      = main::changeClientPassword($client['id'], $password);
                    main::errors("Password reset!");
                    $forgot_pass_array['PASS'] = $password;
                    $forgot_pass_array['LINK'] = $dbh->config("url")."/client";
                    $emaildata      = email::emailTemplate("client-password-reset");
                    email::send($email_reset, $emaildata['subject'], $emaildata['content'], $forgot_pass_array);
                
                }

            }

        }

        echo '<div align="center">'.main::table("Client Area - Reset Password", style::replaceVar("tpl/client/login/client-password-reset.tpl"), "300px").'</div>';
        
        echo style::get("footer.tpl");
    
    }else{

        define("SUB", "Login");
        define("INFO", "<b>Welcome to <NAME></b><br>".SUB);
        if($_POST){
		
            if(main::clientLogin($postvar['user'], $postvar['pass'])){

                main::redirect("?page=home");
            
            }else{

                main::errors("Incorrect username or password or account not active!");
            
            }

        }

        echo style::get("header.tpl");
        if(!$dbh->config("cenabled")){

            define("SUB", "Disabled");
            define("INFO", SUB);
            echo '<div align="center">'.main::table("Client Area - Disabled", $dbh->config("cmessage"), "300px").'</div>';
        
        }else{

            echo '<div align="center">'.main::table("Client Area - Login", style::replaceVar("tpl/client/login/client-login.tpl"), "300px").'</div>';
        
        }

        echo style::get("footer.tpl");
    
    }

    if($getvar['invoiceID']){

        require_once("../includes/paypal/paypal.class.php");
        $paypal = new paypal_class;
        if($dbh->config("paypalmode") == "sandbox"){

            $paypal->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        
        }else{

            $paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        
        }

        if($paypal->validate_ipn()){

            $user_data   = $dbh->select("users", array("id", "=", $_SESSION['cuser']));
            $signup_date = date("m-d-Y", $user_data['signup']);
            
            if($signup_date == date("m-d-Y")){

                $noemail = "1";
            
            }

            invoice::set_paid($getvar['invoiceID'], $noemail);
            main::errors("Your invoice has been paid!");
        
        }else{

            main::errors("Your invoice hasn't been paid!");
        
        }

    }

}elseif($_SESSION['clogged']){

    if(!$getvar['page']){

        $getvar['page'] = "home";
    
    }elseif($getvar['page'] == "logout"){

        session_destroy();
        main::redirect("./");
    
    }

    if(!$dbh->config("cenabled")){

        define("SUB", "Disabled");
        define("INFO", SUB);
        $content = '<div align="center">'.main::table("Client Area - Disabled", $dbh->config("cmessage"), "300px").'</div>';
    
    }else{

        $usersdb_data = $dbh->select("users", array("id", "=", $_SESSION['cuser']));
        if(empty($usersdb_data)){

            main::redirect("?page=logout");
        
        }

        $content = client();
    
    }

    echo style::get("header.tpl");
    echo $content;
    echo style::get("footer.tpl");

}

include(INC."/output.php");

?>