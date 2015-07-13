<?php
//////////////////////////////
// The Hosting Tool
// Invoice Class
// By Nick (TheRaptor) + Jonny + Jimmie
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){
        die();
}
//Create the class
class invoice {
        # Start the functions #
        public function create($uid, $amount, $due, $notes) {
                global $db;
                global $email;
                global $main;
                global $sdk;
                $client = $db->client($uid);
                $emailtemp = $db->emailTemplate("newinvoice");
                $array['USER'] = $client['user'];
                $array['AMOUNT'] = $sdk->addzeros($amount);
                $array['LINK'] = $db->config("url")."/client/?page=invoices";
                $array['DUE'] = $main->convertdate("n/d/Y", $due, $uid);
                $is_paid = $array['AMOUNT'] == "0.00" ? "1" : "0";
                $email->send($client['email'], $emailtemp['subject'], $emailtemp['content'], $array);
                $db->query("UPDATE <PRE>invoices SET is_paid = '1' WHERE amount = '0' || amount = '0.00'");  //This way people won't see unpaid invoices for $0.  lol
                return $db->query("INSERT INTO `<PRE>invoices` (uid, amount, due, notes, pay_now, is_paid) VALUES('{$uid}', '{$amount}', '{$due}', '{$notes}', '{$amount}', '{$is_paid}')");
        }

        public function delete($id) { # Deletes invoice upon invoice id
                global $db;
                $query = $db->query("DELETE FROM `<PRE>invoices` WHERE `id` = '{$id}'"); //Delete the invoice
                return $query;
        }
        public function edit($iid, $uid, $amount, $due, $notes) { # Edit an invoice. Fields created can only be edited?
                global $db;
                $query = $db->query("UPDATE `<PRE>invoices` SET
                                                   `uid` = '{$uid}',
                                                   `amount` = '{$amount}',
                                                   `due` = '{$due}',
                                                   `notes` = '{$notes}',
                                                   WHERE `id` = '{$iid}'");
                return $query;
        }

        public function pay($iid, $returnURL = "order/index.php") {
                global $db, $main;
                require_once("paypal/paypal.class.php");
                $paypal = new paypal_class;
                $query = $db->query("SELECT * FROM `<PRE>invoices` WHERE `id` = '{$iid}'");
                $array = $db->fetch_array($query);
                if($_SESSION['cuser'] == $array['uid']) {
                        if($db->config("paypalmode") == "sandbox"){
                         $paypal->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
                         $paypal->add_field('business', $db->config('paypalsandemail'));
                        }else{
                         $paypal->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
                         $paypal->add_field('business', $db->config('paypalemail'));
                        }
                        $paypal->add_field('return', $db->config('url')."client/index.php?page=invoices");
                        $paypal->add_field('cancel_return', $db->config('url')."client/index.php?page=invoices");
                        $paypal->add_field('notify_url',  $db->config('url')."client/index.php?page=invoices&invoiceID=".$iid);
                        $paypal->add_field('item_name', 'THT Order: '.$array['notes']);
                        $paypal->add_field('amount', $array['pay_now']);
                        $paypal->add_field('currency_code', $db->config("currency"));
                        $paypal->submit_paypal_post(); // submit the fields to paypal
                }
                else {
                        echo "You don't seem to be the person who owns that invoice!";
                        exit;
                }
        }

        public function cron(){
                global $db, $server;
                global $type;
                global $navens_coupons, $sdk;
                $time = time();
                $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `type` = 'paid'");
                while($array = $db->fetch_array($query)){
                        $id = intval($array['id']);
                        $query2 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `pid` = '{$id}'");
                        while($array2 = $db->fetch_array($query2)){                                unset($checked_term);
                                unset($checked_sus);
                                unset($found_one);
                                $uid = intval($array2['userid']);
                                $query3 = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$uid}' ORDER BY `id`");
                                if(mysql_num_rows($query3) > 0){
                                        while($array3 = $db->fetch_array($query3)){
                                        #$userinfo = $db->client($uid);
                                        if(!$found_one){
                                            if($time > strtotime($array3['created'])+2592000){  # +30 Days
                                                $username = $sdk->uname($array2['userid']);
                                                $array3['amount'] = $navens_coupons->get_discount("paid", $array3['amount'], $username);
                                                $this->create($uid, $array3['amount'], $time, $array3['notes']); # Create Invoice
                                                $found_one = 1;
                                            }else{
                                                $found_one = 1;
                                            }
                                        }

                                        $lastmonth = $time-2592000;
                                        $suspenddays = intval($db->config('suspensiondays'));
                                        $terminationdays = $suspenddays + $db->config('terminationdays');
                                        $suspendseconds = $suspenddays*24*60*60;
                                        $terminateseconds = intval($db->config('terminationdays'))*24*60*60;
                                        if($array3['due'] < $time and $array3['is_paid'] == 0 && $array3['amount'] > 0){
                                                if(($time-$suspendseconds) > intval($array3['due']) && $array2['status'] == '1' && !$checked_sus){
                                                        $server->suspend($array2['id'], "Your account is overdue.  Please log in and pay your invoice to bring your account out of suspension.");
                                                        $checked_sus = 1;
                                                }
                                                elseif(($time-$suspendseconds-$terminateseconds) > intval($array3['due']) && !$checked_term){
                                                        $server->terminate($array2['id'], "Your account was overdue for more than ".$terminationdays." days.");
                                                        $checked_term = 1;
                                                }
                                        }
                                        }
                                }
                                else{ # User has no invoice yet
                                        $monthly = $type->additional($id);
                                        $amount = $monthly['monthly'];
                                        $amount = $navens_coupons->get_discount("paid", $amount, $array2['userid']);
                                        $this->create($uid, $amount, $time+2592000, ""); # Create Invoice +30 Days
                                        if(!$amount){ //If the bill is for $0, then we set it to paid.  lol
                                                $last_invoice = $sdk->tdata("invoices", "uid", $uid, "", 0, "ORDER BY `id` DESC LIMIT 1");
                                                $last_invoice = $last_invoice['id'];
                                                $db->query("UPDATE <PRE>invoices SET is_paid = '1' WHERE id = '".$last_invoice."' LIMIT 1");
                                        }
                                }
                        }
                }
        }

        public function set_paid($iid, $noemail = 0) { # Pay the invoice by giving invoice id
                global $db, $server;
                $time = time();
                $query = $db->query("UPDATE `<PRE>invoices` SET `is_paid` = '1' WHERE `id` = '{$iid}'");
                $query2 = $db->query("SELECT * FROM `<PRE>invoices` WHERE `id` = '{$iid}' LIMIT 1");
                $data2 = $db->fetch_array($query2);
                $query3 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$data2['uid']}'");
                $data3 = $db->fetch_array($query3);
                $server->unsuspend($data3['id'], $noemail);
                return $query;
        }

        public function set_unpaid($iid) { # UnPay the invoice by giving invoice id - Don't think this will be useful
                global $db;
                $query = $db->query("UPDATE `<PRE>invoices` SET `is_paid` = '0' WHERE `id` = '{$iid}'");
                return $query;
        }

        public function is_paid($id) { # Is the invoice paid - True = Paid / False = Not
                global $db;
                $data = $db->fetch_array($db->query("SELECT * FROM `<PRE>invoices` WHERE `id` = '{$id}'"));
                if($data['is_paid']) {
                        return true;
                }
                else {
                        return false;
                }
        }

}
//End Invoice
?>
