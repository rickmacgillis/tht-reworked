<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Client Area - Home
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
		
        unset($where);
        $where[]           = array("reply", "=", "0", "AND");
        $where[]           = array("userid", "=", $_SESSION['cuser']);
        $all_tickets_query = $dbh->select("tickets", $where, 0, 0, 1);
        $client_home_array['TICKETS']  = $dbh->num_rows($all_tickets_query);
        
        unset($where);
        $where[]              = array("reply", "=", "0", "AND");
        $where[]              = array("status", "=", "1", "AND");
        $where[]              = array("userid", "=", $_SESSION['cuser']);
        $open_tickets_query   = $dbh->select("tickets", $where, 0, 0, 1);
        $client_home_array['OPENTICKETS'] = $dbh->num_rows($open_tickets_query);
        
        unset($where);
        $where[]                = array("reply", "=", "0", "AND");
        $where[]                = array("status", "=", "3", "AND");
        $where[]                = array("userid", "=", $_SESSION['cuser']);
        $closed_tickets_query   = $dbh->select("tickets", $where, 0, 0, 1);
        $client_home_array['CLOSEDTICKETS'] = $dbh->num_rows($closed_tickets_query);
        
        unset($where);
        $where[]  = array("uid", "=", $_SESSION['cuser'], "AND");
        $where[]  = array("message", "LIKE", "Login%");
        $log_data = $dbh->select("logs", $where, array("id", "DESC"), "1");
        
        if($log_data['logtime']){

            $client_home_array['LASTDATE']  = main::convertdate("n/d/Y", $log_data['logtime']);
            $client_home_array['LASTTIME']  = main::convertdate("g:i a", $log_data['logtime']);
            $client_home_array['LASTLOGIN'] = $client_home_array['LASTDATE']." at ".$client_home_array['LASTTIME'];
        
        }else{

            $client_home_array['LASTLOGIN'] = "None";
        
        }

        $client_data      = $dbh->client($_SESSION['cuser']);
        $client_home_array['DATE']   = main::convertdate("n/d/Y", $client_data['signup']);
        $client_home_array['EMAIL']  = $client_data['email'];
        $client_home_array['ALERTS'] = $dbh->config('alerts');
        $client_home_array['UNAME']  = $client_data['user'];
        
        $packages_data     = $dbh->select("packages", array("id", "=", $client_data['pid']));
        $client_home_array['PACKAGE'] = $packages_data['name'];
        
        unset($where);
        $where[]            = array("uid", "=", $client_data['id'], "AND");
        $where[]            = array("is_paid", "=", "0");
        $invoices_query     = $dbh->select("invoices", $where, 0, 0, 1);
        $client_home_array['INVOICES'] = $dbh->num_rows($invoices_query);
        
        unset($where);
        $where[]        = array("uid", "=", $client_data['id'], "AND");
        $where[]        = array("message", "LIKE", "Suspended (%");
        $suspended_data = $dbh->select("logs", $where, array("id", "DESC"), "1");
        
        switch($client_data['status']){

            default:
                $client_home_array['STATUS'] = "Other";
                break;
            
            case "1":
                $client_home_array['STATUS'] = "Active";
                break;
            
            case "2":
                $client_home_array['STATUS']        = "Suspended";
                $suspended_message       = str_replace(")", "", $suspended_data['message']);
                $suspended_message       = str_replace("Suspended (", "", $suspended_message);
                $client_home_array['STATUS_REASON'] = "<br><br><b>Suspended for:</b> ".$suspended_message;
                break;
            
            case "4":
                $client_home_array['STATUS'] = "Awaiting Payment";
                break;
            
            case "5":
                $client_home_array['STATUS'] = "Awaiting Email Confirmation";
                break;
            
            case "9":
                $client_home_array['STATUS'] = "Cancelled";
                break;
        
        }

        if(!$client_home_array['STATUS_REASON']){

            $client_home_array['STATUS_REASON'] = "";
        
        }

        $typename      = type::packagetype($client_data['pid']);
        $type_instance = $instance->packtypes[$typename];
        if(method_exists($type_instance, "clientBox")){

            $box = $type_instance->clientBox();
            $client_home_array['BOX'] = main::sub($box[0], $box[1]);
        
        }else{

            $clienthome_array['BOX'] = "";
        
        }

        if($dbh->config('alerts')){

            $client_home_array['ALERTS'] = "<font size = '3'><b>Announcements:</b></font><br><font size = '2'>".$dbh->config('alerts')."</font><br><hr size = '1' noshade'><br>";
        
        }else{

            $client_home_array['ALERTS'] = "";
        
        }

        echo style::replaceVar("tpl/client/client-home.tpl", $client_home_array);
    
    }

}

?>