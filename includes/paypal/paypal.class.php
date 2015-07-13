<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// PayPal Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class paypal_class{

    var $last_error; // holds the last error encountered    
    var $ipn_response; // holds the IPN response from paypal
    var $ipn_data = array(); // array contains the POST values for IPN
    
    var $fields = array(); // array holds the fields to submit to paypal
    
    function paypal_class(){
        
        $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        
        $this->last_error = '';        
        $this->ipn_response = '';
        
        // populate $fields array with a few default values.  See the paypal
        // documentation for a list of fields and their data types. These defaul
        // values can be overwritten by the calling script.
        
        $this->add_field('rm', '2'); // Return method = POST
        $this->add_field('cmd', '_xclick');
        
    }

    function add_field($field, $value){

        // adds a key=>value pair to the fields array, which is what will be
        // sent to paypal as POST variables.  If the value is already in the
        // array, it will be overwritten.
        
        $this->fields["$field"] = $value;
    
    }

    function submit_paypal_post(){
        global $dbh, $postvar, $getvar, $instance;
        
        echo "<form action=".$this->paypal_url." method='post' name='frm'>";
		
        foreach($this->fields as $a => $b){

            if($a == "amount"){

                if($b == "0"){

                    $user_data   = $dbh->select("users", array("id", "=", $_SESSION['cuser']));
                    $signup_date = date("m-d-Y", $user_data['signup']);
                    
                    if($signup_date == date("m-d-Y")){

                        $noemail = "1";
                    
                    }

                    invoice::set_paid($getvar['iid'], $noemail);
                    main::redirect("../client/?page=invoices");
                    exit;
                
                }

            }

            echo "<input type='hidden' name='".$a."' value='".$b."'>";
        
        }


		echo '</form>
		<script language="JavaScript">
		document.frm.submit();
		</script>';
   
        exit;
    
    }

    function validate_ipn(){
        global $dbh, $postvar, $getvar, $instance;
        
        $invoice_id = $getvar['invoiceID'];
        $url_parsed = parse_url($this->paypal_url);
        
        $post_string = '';
        foreach($postvar as $field => $value){

            $this->ipn_data["$field"] = $value;
            $post_string .= $field.'='.urlencode($value).'&';
        
        }

        $post_string .= "cmd=_notify-validate";
        
        $fp = fsockopen("ssl://".$url_parsed[host], "443", $err_num, $err_str, 30);
        if(!$fp){

            // could not open the connection.  If logging is on, the error message
            // will be in the logged.
            $this->last_error = "fsockopen error no. $err_num: $err_str";
            $this->log_ipn_results(false);
            return false;
            
        }else{

            // Post the data back to paypal
            fputs($fp, "POST /cgi-bin/webscr HTTP/1.0\r\n");
            fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-Length: ".strlen($post_string)."\r\n\r\n");
            fputs($fp, $post_string."\r\n\r\n");
            
            // loop through the response from the server and append to variable
            while(!feof($fp)){

                $this->ipn_response .= fgets($fp, 1024);
            
            }

            fclose($fp); // close connection
            
        }

        $invoice_info = $dbh->select("invoices", array("id", "=", $invoice_id));
        $due_date     = $invoice_info['due'];
        $amt_paid     = $invoice_info['amt_paid'];
        $txn          = $invoice_info['txn'];
        $datepaid     = $invoice_info['datepaid'];
        $gateway      = $invoice_info['gateway'];
        
        if($amt_paid){

            $amt_paid = $amt_paid.",".$this->ipn_data["mc_gross"];
        
        }else{

            $amt_paid = $this->ipn_data["mc_gross"];
        
        }

        if($this->ipn_data["parent_txn_id"]){

            $new_txn = $this->ipn_data["parent_txn_id"];
        
        }else{

            $new_txn = $this->ipn_data["txn_id"];
        
        }

        if($txn){

            $txn = $txn.",".$new_txn;
        
        }else{

            $txn = $new_txn;
        
        }

        if($datepaid){

            $datepaid = $datepaid.",".time();
        
        }else{

            $datepaid = time();
        
        }

        if($gateway){

            $gateway = $gateway.",PayPal";
        
        }else{

            $gateway = "PayPal";
        
        }

        if($this->ipn_data["mc_gross"] < 0){

            //As the transaction was revered or refunded, we need to set the is_paid flag to 0 and make sure they don't get terminated if this was an
            //invoice older than 30 days.
            if(is_numeric($invoice_id)){

                $total_paid = coupons::totalpaid($invoice_id) + $this->ipn_data["mc_gross"];
                
                if($invoice_info['amount'] > $total_paid){

                    $suspenddays    = intval($dbh->config('suspensiondays'));
                    $suspendseconds = $suspenddays * 24 * 60 * 60;
                    $time           = time();
                    if(($time - $suspendseconds) > intval($due_date)){

                        $due_date = $time - $suspendseconds;
                    
                    }

                    $amt_due = array(
                        "is_paid" => "0",
                        "due" => $due_date
                    );
                    
                }

                $gateway = $gateway." (Reversal)";
                
                $invoices_update = array(
                    "amt_paid" => $amt_paid,
                    "txn" => $txn,
                    "datepaid" => $datepaid,
                    "gateway" => $gateway
                );
                
                if($amt_due){

                    $invoices_update = array_merge($invoices_update, $amt_due);
                    
                }

                $dbh->update("invoices", $invoices_update, array("id", "=", $invoice_id));
                
            }

            $this->log_ipn_results(true);
        
        }

        if(eregi("VERIFIED", $this->ipn_response)){

            // Valid IPN transaction.
            $this->log_ipn_results(true);
            
            $invoices_update = array(
                "amt_paid" => $amt_paid,
                "txn" => $txn,
                "datepaid" => $datepaid,
                "gateway" => $gateway
            );
            
            $dbh->update("invoices", $invoices_update, array("id", "=", $invoice_id));
            $total_paid = coupons::totalpaid($invoice_id);
            
            if($invoice_info['amount'] > $total_paid){

                return false;
            
            }else{

                return true;
            
            }

        }else{

            // Invalid IPN transaction.  Check the log for details.
            $this->last_error = 'IPN Validation Failed.';
            $this->log_ipn_results(false);
            return false;
            
        }

    }

    function log_ipn_results($success){
        global $dbh, $postvar, $getvar, $instance;
		
        if($this->ipn_data["mc_gross"] < 0){

            $text = "REVERSAL ";
        
        }else{

            $text = "PAYMENT ";
        
        }

        // Success or failure being logged?
        if($success){

            $text .= "SUCCESS!<br>";
        
        }else{

            $text .= 'FAIL: '.$this->last_error."<br>";
        
        }

        // Log the POST variables
        $text .= "IPN POST Vars from PayPal:<br><br>";
        $text .= "<table width = '672' cellpaddin = '0' cellspacing = '0' border = '1' bordercolor = '#000000' style='border-collapse: collapse'>";
        foreach($this->ipn_data as $key => $value){

            if($color = ''){

                $color = '';
            
            }else{

                $color = " bgcolor = '#888888'";
            
            }

            $text .= "<tr><td width = '30%'".$color."><b>$key</b></td><td>$value</td></tr>";
        
        }

        $text .= "</table>";
        
        //This section prevents paypal's response headers from pulling the page out of wack.  - Silly, I know, but hey, I'm a perfectionist.  What can I say?
        //This won't split the html responses, though.  This way we an still show the page properly that comes up if something goes wrong.
        if(substr_count($paypal_response, "<") == "0"){

            $paypal_response = explode("\n", $this->ipn_response);
            $i               = 0;
            foreach($paypal_response as $paypal_response_key => $paypal_response_val){

                if(strlen($paypal_response_val) > 100){

                    $paypal_response_val = wordwrap($paypal_response_val, 100, "<br>", true);
                    $paypal_response[$i] = $paypal_response_val;
                    break;
                
                }

                $i++;
            
            }

            $paypal_response = implode("<br><br>", $paypal_response);
        
        }else{

            $paypal_response = $this->ipn_response;
        
        }

        // Log the response from the paypal server
        $text .= "<br><br>IPN Response from PayPal Server:<br>".$paypal_response;
        
        // Write to log
        $logs_insert = array(
            "uid" => "s",
            "loguser" => "PayPal",
            "logtime" => time(),
            "message" => $text
        );
            
        $dbh->insert("logs", $logs_insert);

    }

    function dump_fields(){

        // Used for debugging, this function will output all the field/value pairs
        // that are currently defined in the instance of the class using the
        // add_field() function.
        
        echo "<h3>paypal_class->dump_fields() Output:</h3>";
        echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>";
        
        ksort($this->fields);
        foreach($this->fields as $key => $value){

            echo "<tr><td>$key</td><td>".urldecode($value)." </td></tr>";
        
        }

        echo "</table><br>";
    
    }

}