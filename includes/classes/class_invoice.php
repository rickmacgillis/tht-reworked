<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Invoice Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class invoice{

    public function create($uid, $amount, $due, $notes){
        global $dbh, $postvar, $getvar, $instance;
        
        $client           = $dbh->client($uid);
        $emailtemp        = email::emailTemplate("new-invoice");
        $newinvoice_array['USER']    = $client['user'];
        $newinvoice_array['AMOUNT']  = main::addzeros($amount);
        $newinvoice_array['LINK']    = $dbh->config("url")."/client/?page=invoices";
        $newinvoice_array['DUE']     = main::convertdate("n/d/Y", $due, $uid);
        $is_paid          = $newinvoice_array['AMOUNT'] == "0.00" ? "1" : "0";
        email::send($client['email'], $emailtemp['subject'], $emailtemp['content'], $newinvoice_array);
        
        unset($where);
        $where[] = array("amount", "=", "0", "OR");
        $where[] = array("amount", "=", "0.00");
        $dbh->update("invoices", array("is_paid" => "1"), $where); //This way people won't see unpaid invoices for $0.
        
        $invoices_insert = array(
            "uid"     => $uid,
            "amount"  => $amount,
            "created" => time(),
            "due"     => $due,
            "notes"   => $notes,
            "pay_now" => $amount,
            "is_paid" => $is_paid
        );
        
        $response = $dbh->insert("invoices", $invoices_insert);
        return $response;
    
    }

    public function pay($iid, $returnURL = "order/index.php"){
        global $dbh, $postvar, $getvar, $instance;
        
        require_once(INC."/paypal/paypal.class.php");
        $paypal        = new paypal_class;
        $invoices_data = $dbh->select("invoices", array("id", "=", $iid));
        
        if($_SESSION['cuser'] == $invoices_data['uid']){

            if($dbh->config("paypalmode") == "sandbox"){

                $paypal->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
                $paypal->add_field('business', $dbh->config('paypalsandemail'));
            
            }else{

                $paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
                $paypal->add_field('business', $dbh->config('paypalemail'));
            
            }

            $paypal->add_field('return', $dbh->config('url')."client/index.php?page=invoices");
            $paypal->add_field('cancel_return', $dbh->config('url')."client/index.php?page=invoices");
            $paypal->add_field('notify_url', $dbh->config('url')."client/index.php?page=invoices&invoiceID=".$iid);
            $paypal->add_field('item_name', 'THT Order: '.$invoices_data['notes']);
            $paypal->add_field('amount', $invoices_data['pay_now']);
            $paypal->add_field('currency_code', $dbh->config("currency"));
            $paypal->submit_paypal_post();
        
        }else{

            echo "You don't seem to be the person who owns that invoice!";
            exit;
        
        }

    }

	// Pay the invoice by giving invoice id
    public function set_paid($iid, $noemail = 0){	
        global $dbh, $postvar, $getvar, $instance;
		
        $invoices_update = array(
            "datepaid" => time(),
            "is_paid"  => "1"
        );
        
        $response      = $dbh->update("invoices", $invoices_update, array("id", "=", $iid));
        $invoices_data = $dbh->select("invoices", array("id", "=", $iid), 0, "1");
        
        server::unsuspend($invoices_data['uid'], $noemail);
        
        return $response;
    
    }

}

?>