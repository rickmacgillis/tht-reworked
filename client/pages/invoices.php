<?PHP

//////////////////////////////
// The Hosting Tool Reworked
// Client Area - Invoice Management
// By: Barrette Galt
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        if(is_numeric($getvar['view'])){

            //Show the invoice            
            unset($where);
            $where[]          = array("uid", "=", $_SESSION['cuser'], "AND");
            $where[]          = array("id", "=", $getvar['view']);
            $invoice_info_top = $dbh->select("invoices", $where);
            $pack_data_top    = main::uidtopack();
            if(!$invoice_info_top['pid']){

                $dbh->update("invoices", array("pid" => $pack_data_top['user_data']['pid']), array("id", "=", $invoice_info_top['id']));
                $invoice_info_top['pid'] = $pack_data_top['user_data']['pid'];
            
            }

            if($_POST['submitaddcoupon']){

                if(!$postvar['addcoupon']){

                    main::errors("Please enter a coupon code or click the checkout button.");
                
                }else{

                    $coupcode  = $postvar['addcoupon'];
                    $user      = main::uname($_SESSION['cuser']);
                    $pack_data = main::uidtopack();
                    if($invoice_info_top['pid'] != $pack_data['user_data']['pid']){

                        $pack_data = upgrade::pidtobak($invoice_info_top['pid']);
                    
                    }

                    $packid        = $pack_data['packages']['id'];
                    $multi_coupons = $dbh->config("multicoupons");
                    
                    $coupon_info = coupons::coupon_data($coupcode);
                    $coupid      = $coupon_info['id'];
                    
                    $use_coupon = coupons::use_coupon($coupid, $packid, $getvar['view']);
                    if(!$use_coupon){

                        if(!$multi_coupons){

                            main::errors("Coupon code entered was invalid or you're already using a coupon.");
                        
                        }else{

                            main::errors("Coupon code entered was invalid.");
                        
                        }

                    }else{

                        main::redirect("?page=invoices&view=".$getvar['view']);
                    
                    }

                }

            }

            unset($where);
            $where[]      = array("uid", "=", $_SESSION['cuser'], "AND");
            $where[]      = array("id", "=", $getvar['view']);
            $invoice_info = $dbh->select("invoices", $where);
            
            if(empty($invoice_info)){

                main::redirect("?page=invoices");
                exit;
            
            }
            
            $package  = $dbh->select("packages", array("id", "=", $invoice_info['pid']));
            $monthly  = type::additional($package['id']);
            $subtotal = $monthly['monthly'];
            
            if(is_numeric($getvar['remove'])){

                $remove_id = $getvar['remove'];
                $remove    = coupons::remove_coupon($remove_id, $package['id'], $invoice_info['id'], $_SESSION['cuser']);
                main::redirect("?page=invoices&view=".$invoice_info['id']);
                exit;
            
            }

            $total_paid_real = coupons::totalpaid($getvar['view']);
            if($total_paid_real < 0){

                $total_paid = "0.00";
            
            }else{

                $total_paid = $total_paid_real;
            
            }
		
			$acct_balance = coupons::get_discount("paid", $subtotal) - $total_paid_real;
            
            if($acct_balance < 0){

                $acct_balance = "0.00";
            
            }
			
            $acct_balance = main::addzeros($acct_balance);

            if($acct_balance == 0 && $invoice_info['is_paid'] == '0'){

                $dbh->update("invoices", array("is_paid" => "1"), array("id", "=", $invoice_info['id']), "1");
                main::redirect("?page=invoices&view=".$invoice_info['id']);
            
            }

            if($acct_balance > 0 && $invoice_info['is_paid'] == '1'){

                $dbh->update("invoices", array("is_paid" => "0"), array("id", "=", $invoice_info['id']), "1");
                main::redirect("?page=invoices&view=".$invoice_info['id']);
            
            }

            if($_POST['checkout']){

                $postvar['paythis'] = str_replace(array(" ", ","), array("", "."), $postvar['paythis']);
                
                if(!is_numeric($postvar['paythis'])){

                    main::errors("Please enter the amount you wish to pay today.");
                
                }else{

                    if($postvar['paythis'] > $acct_balance || $acct_balance <= 0){

                        main::errors("You can't pay more than you owe.  =)");
                    
                    }else{

                        $dbh->update("invoices", array("pay_now" => $postvar['paythis']), array("id", "=", $getvar['view']));
                        main::redirect("?page=invoices&iid=".$getvar['view']);
                        exit;
                    
                    }

                }

            }

            $created     = $invoice_info['created'];
            $thirty_days = 30 * 24 * 60 * 60;
            $orig_due    = $created + $thirty_days;
            
            if(main::convertdate("n/d/Y", $invoice_info['due']) != (main::convertdate("n/d/Y", $created + $thirty_days))){

                $due_text = " (Originally ".main::convertdate("n/d/Y", $orig_due).")";
            
            }

            $due = main::convertdate("n/d/Y", $invoice_info['due']);
            
			$client = $dbh->client($_SESSION['cuser']);
			
            $invoice_transactions_array['TOTALAMOUNT'] = main::money($acct_balance);
            $invoice_transactions_array['TOTALPAID']   = main::money($total_paid);
			
            $pay_invoice_array['TOTALAMT']             = main::money($acct_balance);
            $pay_invoice_array['PAYBALANCE']           = $acct_balance;
            $pay_invoice_array['CURRSYMBOL']           = main::money($acct_balance, "", 1);
            $pay_invoice_array['PACKID']               = $invoice_info['pid'];
            $pay_invoice_array['USER']                 = $client['user'];
			
            $view_invoice_array['ID']                  = $invoice_info['id'];
            $view_invoice_array['DUE']                 = $due.$due_text;
            $view_invoice_array['PACKDUE']             = $due;
            $view_invoice_array['CREATED']             = main::convertdate("n/d/Y", $created);
            $view_invoice_array['BASEAMOUNT']          = $invoice_info['amount'] != $subtotal ? main::money($invoice_info['amount'])." (Package price: ".main::money($subtotal).")" : main::money($invoice_info['amount']);
            $view_invoice_array['BALANCE']             = main::money($acct_balance);
            $view_invoice_array['COUPONTOTAL']         = main::money($subtotal - coupons::get_discount("paid", $subtotal));
            
            $view_invoice_array['UNAME']   = $client['user'];
            $view_invoice_array['FNAME']   = $client['firstname'];
            $view_invoice_array['LNAME']   = $client['lastname'];
            $view_invoice_array['ADDRESS'] = $client['address'];
            $view_invoice_array['CITY']    = $client['city'];
            $view_invoice_array['STATE']   = $client['state'];
            $view_invoice_array['ZIP']     = $client['zip'];
            $view_invoice_array['COUNTRY'] = strtoupper($client['country']);
            
            $view_invoice_array['DOMAIN']  = $client['domain'];
            $view_invoice_array['PACKAGE'] = $package['name'];
            
            $view_invoice_array['STATUS']  = $invoice_info["is_paid"] == 1 ? "<font color = '#779500'>Paid</font>" : "<font color = '#FF7800'>Unpaid</font>";
            if($invoice_info['changed_plan'] && $invoice_info['hadcoupons']){

                $coupon_list   = explode(",", $invoice_info['hadcoupons']);
                $coupon_values = explode(",", $invoice_info['couponvals']);
                if($coupon_list){

                    for($i = 0; $i < count($coupon_list); $i++){

                        $coupons_list_array['COUPONAMOUNT'] = main::money($coupon_values[$i]);
                        $coupons_list_array['COUPCODE']     = $coupon_list[$i];
                        $coupons_list_array['REMOVE']       = "";
                        $view_invoice_array['COUPONSLIST'] .= style::replaceVar("tpl/invoices/coupons-list.tpl", $coupons_list_array);
                        $coup_total = $coup_total + $coupon_values[$i];
                    
                    }

                    $view_invoice_array['COUPONTOTAL'] = main::money(min($subtotal, $coup_total));
                
                }

            }else{

				unset($where);
				$where[] = array("user", "=", $client['id'], "AND");
				$where[] = array("disabled", "=", "0");
                $coupons_query = $dbh->select("coupons_used", $where, array("id", "ASC"), 0, 1);
                while($coupons_used_fetch = $dbh->fetch_array($coupons_query)){

                    $valid_coupon = coupons::check_expire($coupons_used_fetch['coupcode'], $client['id']);
                    if($valid_coupon){

                        $coupons_list_array['COUPONAMOUNT'] = main::money($coupons_used_fetch['paiddisc']);
                        $coupons_list_array['COUPCODE']     = $coupons_used_fetch['coupcode'];
                        $coupons_list_array['REMOVE']       = $invoice_info['is_paid'] == 1 ? "" : ('(<a href = "?page=invoices&view='.$invoice_info['id'].'&remove='.$coupons_used_fetch['id'].'">Remove</a>)');
                        $view_invoice_array['COUPONSLIST'] .= style::replaceVar("tpl/invoices/coupons-list.tpl", $coupons_list_array);
                    
                    }

                }

                if(!$view_invoice_array['COUPONSLIST']){

                    $view_invoice_array['COUPONSLIST'] = "<tr><td></td><td align = 'center'>None</td></tr>";
                
                }

            }
            
            $amt_paid = $invoice_info['amt_paid'];
            $txn      = $invoice_info['txn'];
            $datepaid = $invoice_info['datepaid'];
            $gateway  = $invoice_info['gateway'];
            
            $amt_paid                      = explode(",", $amt_paid);
            $txn                           = explode(",", $txn);
            $datepaid                      = explode(",", $datepaid);
            $gateway                       = explode(",", $gateway);
            $view_invoice_array['TRANSACTIONS'] = "";
            
            for($i = 0; $i < count($amt_paid); $i++){

                $paid_this             = $paid_this + $amt_paid[$i];
                $transaction_list_array['PAIDAMOUNT'] = main::money($amt_paid[$i]);
                $transaction_list_array['TXN']        = $txn[$i];
                $transaction_list_array['PAIDDATE']   = main::convertdate("n/d/Y", $datepaid[$i]);
                $transaction_list_array['GATEWAY']    = $gateway[$i];
                $invoice_transactions_array['TXNS'] .= style::replaceVar("tpl/invoices/transaction-list.tpl", $transaction_list_array);
            
            }

            if($invoice_info["is_paid"]){

                if(!$invoice_info['amt_paid']){

                    $invoice_transactions_array['TXNS'] = "<tr><td colspan = '4' align = 'center'><b>--- None ---</b></td></tr>";
                
                }

                $view_invoice_array['TRANSACTIONS'] = style::replaceVar("tpl/invoices/invoice-transactions.tpl", $invoice_transactions_array);
            
            }else{

                if($invoice_info['amt_paid']){

                    $view_invoice_array['TRANSACTIONS'] = style::replaceVar("tpl/invoices/invoice-transactions.tpl", $invoice_transactions_array);
                
                }

                $view_invoice_array['TRANSACTIONS'] .= style::replaceVar("tpl/client/invoices/pay-invoice.tpl", $pay_invoice_array);
            
            }

            echo style::replaceVar("tpl/invoices/view-invoice.tpl", $view_invoice_array);
            
        }else{

            //Show the list of invoices
            $pack_info = main::uidtopack();
            
            $invoices_query = $dbh->select("invoices", array("uid", "=", $_SESSION['cuser']), array("id", "DESC"), 0, 1);
            
            $client_page_array['LIST'] = "";
            while($invoices_data = $dbh->fetch_array($invoices_query)){

                if(!$invoices_data['pid']){

                    $dbh->update("invoices", array("pid" => $pack_info['user_data']['pid']), array("id", "=", $invoices_data['id']));
                    $invoices_data['pid'] = $pack_info['user_data']['pid'];
                
                }

                if($invoices_data['pid'] != $pack_info['user_data']['pid']){

                    $pack_info = upgrade::pidtobak($invoices_data['pid']);
                
                }

                $monthly             = $pack_info['additional']['monthly'];
                $invoice_list_item_array['ID']        = $invoices_data['id'];
                $invoice_list_item_array['USERFIELD'] = "";
                $invoice_list_item_array['DUE']       = main::convertdate("n/d/Y", $invoices_data['due']);
                $invoice_list_item_array['CREATED']   = main::convertdate("n/d/Y", $invoices_data['created']);
                $invoice_list_item_array["PAID"]      = $invoices_data["is_paid"] == 1 ? "<font color = '#779500'>Paid</font>" : "<font color = '#FF7800'>Unpaid</font>";
                $invoice_list_item_array['AMOUNT']    = main::money($invoices_data['amount']);
                $invoice_list_item_array['AMTPAID']   = main::money(coupons::totalpaid($invoices_data['id']));
                $client_page_array['LIST'] .= style::replaceVar("tpl/invoices/invoice-list-item.tpl", $invoice_list_item_array);
            
            }

            $client_page_array['NUM'] = $dbh->num_rows($invoices_query);
            
            if($client_page_array['NUM'] == 0){

                $client_page_array['LIST'] = "<tr>\n<td colspan = '6' align = 'center'>You currently do not have any invoices.</td>\n</tr>";
            
            }

            echo style::replaceVar("tpl/client/invoices/client-page.tpl", $client_page_array);
        
        }

    }

}

?>