<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Invoice Management
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
        
        if(main::isint(str_replace("P2H-", "", $getvar['view']))){

            //Display the invoice
            
            if(substr_count($getvar['view'], "P2H-")){

                $p2hid    = str_replace("P2H-", "", $getvar['view']);
                $userid   = $dbh->select("users", array("id", "=", $p2hid));
                $userid   = $userid['id'];
                $userdata = coupons::admin_userdata($userid);
                
            }else{

                $invoiceid        = $getvar['view'];
                $invoice_data_top = $dbh->select("invoices", array("id", "=", $invoiceid));
                $pid              = $invoice_data_top['pid'];
                $userid           = $invoice_data_top['uid'];
                $uidtopack        = main::uidtopack($userid, $pid);
                
                if(!$pid){

                    $dbh->update("invoices", array("pid" => $uidtopack['pid']), array( "id", "=", $invoice_data_top['id']));
                    
                }

                $userdata = coupons::admin_userdata($userid);
                
            }

            if($_POST['submitaddcoupon']){

                if(!$postvar['addcoupon']){

                    main::errors("Please enter a coupon code.");
                    
                }else{

                    $coupcode      = $postvar['addcoupon'];
                    $user          = main::uname($userid);
                    $pack_data     = main::uidtopack($userid, $pid);
                    $packid        = $pack_data['packages']['id'];
                    $multi_coupons = $dbh->config("multicoupons");
                    
                    if($p2hid){

                        $monthly      = $pack_data['additional']['monthly'];
                        $monthly      = coupons::get_discount("p2hmonthly", $monthly, $userid);
                        $total_posted = coupons::totalposts($userid);
                        $amt_owed     = max(0, ($monthly - $total_posted));
                        
                    }else{

                        $invoice_info = $dbh->select("invoices", array("id", "=", $invoiceid));
                        if($invoice_info['pid'] != $pack_data['pid']){

                            $pack_data = upgrade::pidtobak($invoice_info['pid'], $invoice_info["uid"]);
                            
                        }

                        $total_paid = coupons::totalpaid($invoiceid);
                        $amt_owed   = max(0, ($invoice_info['amount'] - $total_paid));
                        
                    }

                    if($amt_owed == 0){

                        main::errors("The user's balance is already paid in full, so you can't add another coupon.");
                        
                    }else{

                        $coupon_info = coupons::coupon_data($coupcode);
                        $coupid      = $coupon_info['id'];
                        
                        $use_coupon = coupons::use_coupon($coupid, $packid, $invoiceid, $userid);
                        if(!$use_coupon){

                            if(!$multi_coupons){

                                main::errors("Coupon code entered was invalid or user is already using a coupon.  You can give them a credit instead.");
                                
                            }else{

                                main::errors("Coupon code entered was invalid or the user is already using this coupon.");
                                
                            }

                        }else{

                            main::redirect("?page=invoices&view=".$getvar['view']);
                            
                        }

                    }

                }

            }

            if($_POST['submitcredit']){

                $postvar['credit'] = str_replace(array(" ", ","), array("", "."), $postvar['credit']);
                
                if(!is_numeric($postvar['credit'])){

                    main::errors("Please enter the amount to be credited or debited.");
                    
                }else{

                    if($postvar['creditreason']){

                        $creditreason = $postvar['creditreason'];
                        $creditreason = ' <a title="'.$creditreason.'" class="tooltip"><img src="<URL>themes/icons/information.png"></a>';
                        $creditreason = str_replace(",", "", $creditreason); //Can't have commas, no way no how!  ;)  lol  We need to be able to explode(",", $invoice_info['txn']);
                        
                    }

                    if($p2hid){

                        $credit_fee = $postvar['credit'];
                        
                    }else{

                        $credit_fee = main::addzeros($postvar['credit']);
                        
                    }

                    if($credit_fee != 0){

                        if(substr_count($credit_fee, "-")){

                            $creditfee_lable = "CHARGE";
                            
                        }else{

                            $creditfee_lable = "CREDIT";
                            
                        }

                        $packinfo = main::uidtopack($userid, $pid); 
                        if(!$packinfo['user_data']['pid'] && !$p2hid){

                            $packinfo = upgrade::pidtobak($pid, $userid);
                            
                        }

                        $monthly = $packinfo['additional']['monthly'];
                        if($p2hid){

                            $amt_owed = max(0, ($monthly - coupons::totalposts($userid)));
                            
                        }else{

                            $amt_owed = max(0, ($monthly - coupons::totalpaid($invoiceid)));
                            
                        }

                        if($amt_owed == 0 && $creditfee_lable == "CREDIT"){

                            main::errors("The user's balance is already paid in full, so you can't add a credit.");
                            
                        }else{

                            if($p2hid){

                                $p2h_info = $dbh->select("coupons_p2h", array("uid", "=", $userid));
                                if($p2h_info['datepaid']){

                                    $comma = ",";
                                    
                                }

                                $datepaid = $p2h_info['datepaid'].$comma.time();
                                $txn      = $p2h_info['txn'].$comma.$creditfee_lable.$creditreason;
                                $amt_paid = $p2h_info['amt_paid'].$comma.$credit_fee;
                                $gateway  = $p2h_info['gateway'].$comma."INTERNAL";
                                
                                $update_coupons_p2h = array(
                                    "datepaid" => $datepaid,
                                    "txn"      => $txn,
                                    "amt_paid" => $amt_paid,
                                    "gateway"  => $gateway
                                );
                                $dbh->update("coupons_p2h", $update_coupons_p2h, array("uid", "=", $userid), "1");
                                
                            }else{

                                $invoice_info = $dbh->select("invoices", array("id", "=", $invoiceid));
                                if($invoice_info['pid'] != $packinfo['pid']){

                                    $pack_info = upgrade::pidtobak($invoice_info['pid'], $invoice_info["uid"]);
                                    
                                }

                                if($invoice_info['datepaid']){

                                    $comma = ",";
                                    
                                }

                                $datepaid = $invoice_info['datepaid'].$comma.time();
                                $txn      = $invoice_info['txn'].$comma.$creditfee_lable.$creditreason;
                                $amt_paid = $invoice_info['amt_paid'].$comma.$credit_fee;
                                $gateway  = $invoice_info['gateway'].$comma."INTERNAL";
                                
                                $update_invoices = array(
                                    "datepaid" => $datepaid,
                                    "txn"      => $txn,
                                    "amt_paid" => $amt_paid,
                                    "gateway"  => $gateway
                                );
                                $dbh->update("invoices", $update_invoices, array("id", "=", $invoiceid), "1");
                                
                            }

                            main::redirect("?page=invoices&view=".$getvar['view']);
                            
                        }

                    }

                }

            }

            if($_POST['submitpayarrange']){

                $invoice_info = $dbh->select("invoices", array("id", "=", $invoiceid));
                $duedate      = $invoice_info['due'];
                $days_modify  = $postvar['days'];
                $days_modify  = $days_modify * 24 * 60 * 60;
                
                if($postvar['addsub'] == "add"){

                    $new_due_date = $duedate + $days_modify;
                    
                }else{

                    $new_due_date = $duedate - $days_modify;
                    
                }

                $dbh->update("invoices", array("due" => $new_due_date), array("id", "=", $invoiceid), "1");
                main::redirect("?page=invoices&view=".$getvar['view']);
                
            }

            if($p2hid){

                $p2h_info = $dbh->select("users", array("id", "=", $p2hid));
                
            }else{

                $invoice_info = $dbh->select("invoices", array("id", "=", $invoiceid));
                
            }

            if(empty($invoice_info) && empty($p2h_info)){

                main::redirect("?page=invoices");
                exit;
                
            }

            if($getvar['deleteinv']){

                if($postvar['yes']){

                    if($p2hid){

                        $dbh->delete("coupons_p2h", array("uid", "=", $userid), "1");
                        main::redirect("?page=invoices&view=".$getvar['view']);
                        
                    }else{

                        $dbh->delete("invoices", array("id", "=", $invoiceid), "1");
                        main::redirect("?page=invoices");
                        
                    }

                }elseif($postvar['no']){

                    main::redirect("?page=invoices&view=".$getvar['view']);
                    
                }else{

                    $warning_array['HIDDEN'] = "<input type = 'hidden' name = 'confirm' value = 'confirm'>";
                    echo style::replaceVar("tpl/warning.tpl", $warning_array);
                    $warning_page = '1';
                    
                }

            }

            if($userdata['removed'] == 1){

                $upackage = $dbh->select("users_bak", array("id", "=", $userid));
                
            }else{

                $upackage = $dbh->select("users", array("id", "=", $userid));
                
            }

            if(!$p2hid){

                $package = $dbh->select("packages", array("id", "=", $invoice_info['pid']));
                
            }else{

                $package = $dbh->select("packages", array("id", "=", $upackage['pid']));
                
            }

            $monthly  = type::additional($package['id']);
            $subtotal = $monthly['monthly'];
            
            if(is_numeric($getvar['remove'])){

                $remove_id = $getvar['remove'];
                if($p2hid){

                    coupons::remove_p2h_coupon($remove_id, $userid);
                    
                }else{

                    coupons::remove_coupon($remove_id, $package['id'], $invoice_info['id'], $userid);
                    
                }

                main::redirect("?page=invoices&view=".$getvar['view']);
                exit;
                
            }

            if($p2hid){

                $due     = date("m/t/Y");
                $created = date("m/1/Y");
                
                $p2h               = $instance->packtypes["p2h"];
                $monthly_with_disc = coupons::get_discount("p2hmonthly", $subtotal, $userid);
                $total_posts       = $p2h->userposts($package['id'], $p2hid);
                $total_paid        = coupons::totalposts($userid);
                
                if(empty($total_paid)){

                    $total_paid = 0;
                    
                }

                if(empty($total_posts)){

                    $total_posts = 0;
                    
                }

                $acct_balance = max(0, ($monthly_with_disc - $total_paid));
                
                $view_invoice_array['BASEAMOUNT']          = $invoice_info['amount'] != $subtotal ? main::s($invoice_info['amount'], " Post")." (Package price: ".main::s($subtotal, " Post").")" : main::s($invoice_info['amount'], " Post");                
                $view_invoice_array['COUPONTOTAL']         = main::s(($subtotal - $monthly_with_disc), " Post");
				
				$invoice_transactions_array['TOTALAMOUNT'] = main::s($acct_balance, " Post");
                $invoice_transactions_array['TOTALPAID']   = main::s($total_paid, " Post");
                
				$admin_ops_array['TOTALAMT']               = main::s($acct_balance, " Post");
                $admin_ops_array['DELRESET']               = "Reset";
                
				$admin_ops_modify_array['CREDIT']          = $acct_balance;
                $admin_ops_modify_array['CURRSYMBOL']      = "";
                $admin_ops_modify_array['POSTS']           = " Posts";
                
            }else{

                $created     = $invoice_info['created'];
                $thirty_days = 30 * 24 * 60 * 60;
                $orig_due    = $created + $thirty_days;
                
                if($getvar['resetpayarange']){

                    $dbh->update("invoices", array("due" => $orig_due), array("id", "=", $invoiceid), "1");
                    main::redirect("?page=invoices&view=".$invoiceid);
                    
                }

                if(main::convertdate("n/d/Y", $invoice_info['due']) != (main::convertdate("n/d/Y", $created + $thirty_days))){

                    $due_text = " (Originally ".main::convertdate("n/d/Y", $orig_due).") - <a href = '?page=invoices&view=".$invoiceid."&resetpayarange=1'>Reset</a>";
                    
                }

                $due     = main::convertdate("n/d/Y", $invoice_info['due']);
                $created = main::convertdate("n/d/Y", $created);
                
                $total_paid_real = coupons::totalpaid($invoiceid);
                if($total_paid_real < 0){

                    $total_paid = "0.00";
                    
                }else{

                    $total_paid = $total_paid_real;
                    
                }

                $acct_balance = $invoice_info['amount'] - $total_paid_real;
                $acct_balance = main::addzeros($acct_balance);
                
                if($acct_balance < 0){

                    $acct_balance = "0.00";
                    
                }

                if($acct_balance == 0 && $invoice_info['is_paid'] == '0'){

                    $dbh->update("invoices", array("is_paid" => "1"), array("id",  "=", $invoice_info['id']), "1");
                    
					unset($where);
                    $where[] = array("id", "=", $invoice_info['uid'], "AND");
                    $where[] = array("status", "=", "4");
                    $dbh->update("users", array("status" => "1"), $where, "1");
                    
                    unset($where);
                    $where[] = array("id", "=", $invoice_info['uid'], "AND");
                    $where[] = array("status", "=", "4");
                    $dbh->update("users", array("status" => "1"), $where, "1");
                    
                    main::redirect("?page=invoices&view=".$invoiceid);
                    
                }

                if($acct_balance > 0 && $invoice_info['is_paid'] == '1'){

                    $dbh->update("invoices", array("is_paid" => "0"), array("id", "=", $invoice_info['id']), "1");
                    main::redirect("?page=invoices&view=".$invoiceid);
                    
                }

                $view_invoice_array['BASEAMOUNT']          = $invoice_info['amount'] != $subtotal ? main::money($invoice_info['amount'])." (Package price: ".main::money($subtotal).")" : main::money($invoice_info['amount']);
                $view_invoice_array['COUPONTOTAL']         = main::money($subtotal - coupons::get_discount("paid", $subtotal, $userid));
				
				$invoice_transactions_array['TOTALAMOUNT'] = main::money($acct_balance);
                $invoice_transactions_array['TOTALPAID']   = main::money($total_paid);
                
				$admin_ops_array['TOTALAMT']               = main::money($acct_balance);
                $admin_ops_array['DELRESET']               = "Delete";
                
				$admin_ops_modify_array['CREDIT']          = $acct_balance;
                $admin_ops_modify_array['CURRSYMBOL']      = main::money($acct_balance, "", 1)." ";
                $admin_ops_modify_array['POSTS']           = "";
                
            }

            $view_invoice_array['ID']      = $getvar['view'];
            $view_invoice_array['DUE']     = $due.$due_text;
            $view_invoice_array['PACKDUE'] = $due;
            $view_invoice_array['CREATED'] = $created;
            
            $view_invoice_array['UNAME']   = $userdata['user'];
            $view_invoice_array['FNAME']   = $userdata['firstname'];
            $view_invoice_array['LNAME']   = $userdata['lastname'];
            $view_invoice_array['ADDRESS'] = $userdata['address'];
            $view_invoice_array['CITY']    = $userdata['city'];
            $view_invoice_array['STATE']   = $userdata['state'];
            $view_invoice_array['ZIP']     = $userdata['zip'];
            $view_invoice_array['COUNTRY'] = strtoupper($userdata['country']);
            
            $view_invoice_array['DOMAIN']  = $upackage['domain'];
            $view_invoice_array['PACKAGE'] = $package['name'];
            
            $view_invoice_array['STATUS'] = $acct_balance == 0 ? "<font color = '#779500'>Paid</font>" : "<font color = '#FF7800'>Unpaid</font>";
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
                $where[] = array("user", "=", $userid, "AND");
                $where[] = array("disabled", "=", "0");
                $coupons_query   = $dbh->select("coupons_used", $where, array( "id", "ASC"), 0, 1);
				
                while($coupons_used_fetch = $dbh->fetch_array($coupons_query)){

                    $valid_coupon = coupons::check_expire($coupons_used_fetch['coupcode'], $userid);
                    if($valid_coupon){

                        if($p2hid){

                            $coupamt = main::s($coupons_used_fetch['p2hmonthlydisc'], " Post");
                            
                        }else{

                            $coupamt = main::money($coupons_used_fetch['paiddisc']);
                            
                        }

                        $coupons_list_array['COUPONAMOUNT'] = $coupamt;
                        $coupons_list_array['COUPCODE']     = $coupons_used_fetch['coupcode'];
                        $coupons_list_array['REMOVE']       = $userdata['removed'] == 1 ? "" : ('(<a href = "?page=invoices&view='.$getvar['view'].'&remove='.$coupons_used_fetch['id'].'">Remove</a>)');
                        $view_invoice_array['COUPONSLIST'] .= style::replaceVar("tpl/invoices/coupons-list.tpl", $coupons_list_array);
                        
                    }

                }

            }

            if(!$view_invoice_array['COUPONSLIST']){

                $view_invoice_array['COUPONSLIST'] = "<tr><td></td><td align = 'center'>None</td></tr>";
                
            }

            if($p2hid){

                $p2h_payments = $dbh->select("coupons_p2h", array("uid", "=", $userid));
                $package_info = main::uidtopack($userid);
                if(empty($p2h_payments)){

                    $p2h_pay_array = array(
                        "uid"      => $userid,
                        "amt_paid" => $total_posts,
                        "txn"      => $package_info['uadditional']['fuser'],
                        "datepaid" => time(),
                        "gateway"  => $package_info['additional']['forum']
                    );
                    
                    $dbh->insert("coupons_p2h", $p2h_pay_array);
                    $p2h_payments = $dbh->select("coupons_p2h", array("uid", "=", $userid));
                    
                }

                $amt_paid = $p2h_payments['amt_paid'];
                $txn      = $p2h_payments['txn'];
                $datepaid = $p2h_payments['datepaid'];
                $gateway  = $p2h_payments['gateway'];
                
            }else{

                $amt_paid = $invoice_info['amt_paid'];
                $txn      = $invoice_info['txn'];
                $datepaid = $invoice_info['datepaid'];
                $gateway  = $invoice_info['gateway'];
                
            }

            $amt_paid = explode(",", $amt_paid);
            $txn      = explode(",", $txn);
            $datepaid = explode(",", $datepaid);
            $gateway  = explode(",", $gateway);
            
            $remnum = 1;
            for($i = 0; $i < count($amt_paid); $i++){

                unset($remtxn);
                if($gateway[$i] == "INTERNAL" && !$userdata['removed']){

                    $remtxn = ' <a href = "?page=invoices&view='.$getvar['view'].'&remtxn='.$remnum.'">[Delete]</a>';
                    
                }

                if($txn[$i] == $package_info['uadditional']['fuser']){

                    if($amt_paid[$i] != $total_posts){

                        $reload = 1;
                        
                    }

                    $amt_paid[$i] = $total_posts;
                    $datepaid[$i] = time();
                    
                }

                $paid_this = $paid_this + $amt_paid[$i];
                if($p2hid){

                    $transaction_list_array['PAIDAMOUNT'] = main::s(str_replace("-", "−", $amt_paid[$i]), " Post").$remtxn;
                    
                }else{

                    $transaction_list_array['PAIDAMOUNT'] = main::money($amt_paid[$i]).$remtxn;
                    
                }

                $transaction_list_array['TXN']      = $txn[$i];
                $transaction_list_array['PAIDDATE'] = main::convertdate("n/d/Y", $datepaid[$i]);
                $transaction_list_array['GATEWAY']  = $gateway[$i];
                $invoice_transactions_array['TXNS'] .= style::replaceVar("tpl/invoices/transaction-list.tpl", $transaction_list_array);
                
                if($getvar['remtxn'] != $i + 1){

                    $paidamts    = $paidamts.",".$amt_paid[$i];
                    $paidtxn     = $paidtxn.",".$txn[$i];
                    $paiddate    = $paiddate.",".$datepaid[$i];
                    $paidgateway = $paidgateway.",".$gateway[$i];
                    
                }

                $remnum++;
                
            }

            if($p2hid){

                $paidamts    = substr($paidamts, 1, strlen($paidamts));
                $paidtxn     = substr($paidtxn, 1, strlen($paidtxn));
                $paiddate    = substr($paiddate, 1, strlen($paiddate));
                $paidgateway = substr($paidgateway, 1, strlen($paidgateway));
                
                $p2h_pay_array = array(
                    "amt_paid" => $paidamts,
                    "txn"      => $paidtxn,
                    "datepaid" => $paiddate,
                    "gateway"  => $paidgateway
                );
                
				unset($where);
                $where[] = array("uid", "=", $userid);
                $dbh->update("coupons_p2h", $p2h_pay_array, $where);
                
                if($getvar['remtxn'] || $reload){

                    main::redirect("?page=invoices&view=".$getvar['view']);
                    
                }

            }else{

                if($getvar['remtxn']){

                    $paidamts    = substr($paidamts, 1, strlen($paidamts));
                    $paidtxn     = substr($paidtxn, 1, strlen($paidtxn));
                    $paiddate    = substr($paiddate, 1, strlen($paiddate));
                    $paidgateway = substr($paidgateway, 1, strlen($paidgateway));
                    
                    $update_invoices = array(
                        "amt_paid" => $paidamts,
                        "txn"      => $paidtxn,
                        "datepaid" => $paiddate,
                        "gateway"  => $paidgateway
                    );
                    $dbh->update("invoices", $update_invoices, array( "id", "=", $invoiceid), "1");
                    main::redirect("?page=invoices&view=".$invoiceid);
                    
                }

            }

            if($invoice_info['amt_paid'] || $p2hid){

                $view_invoice_array['TRANSACTIONS'] = style::replaceVar("tpl/invoices/invoice-transactions.tpl", $invoice_transactions_array);
                
            }

            $addsub[] = array("Add", "add");
            $addsub[] = array("Subtract", "subtract");
            
            $days[] = array("1 Day", "1");			
            for($num = 2; $num < 31; $num++){

                $days[] = array($num." Days", $num);
                
            }

            $payment_arrangments_array['ADDSUB'] = main::dropDown("addsub", $addsub, "add", 0);
            $payment_arrangments_array['DAYS']   = main::dropDown("days", $days, 1, 0);
            
            if($userdata['removed'] == 1){

                $admin_ops_array['MODIFYFUNCS'] = '
                        <tr>
                         <td align="center" colspan = "2"><font color = "#FF0055"><strong>The owner of this invoice has been dismembered.  Er... I mean the member who owned this invoice has been removed.</strong></font></td>
                        </tr>';
                
            }else{

				if(!$p2hid){

					$admin_ops_modify_array['PAYARRANGE'] = style::replaceVar("tpl/admin/invoices/payment-arrangments.tpl", $payment_arrangments_array);
					
				}else{

					$admin_ops_modify_array['PAYARRANGE'] = "";
					
				}

				$admin_ops_array['MODIFYFUNCS'] = style::replaceVar("tpl/admin/invoices/admin-ops-modify.tpl", $admin_ops_modify_array);
				
				if($invoice_info['changed_plan']){

					$admin_ops_array['MODIFYFUNCS'] .= '
                        <tr>
                         <td align="center" colspan = "2"><font color = "#FF0055"><strong>The owner of this invoice has upgraded their account and this is an invoice from an old account.</strong></font></td>
                        </tr>';
                
				}

            }

            $view_invoice_array['TRANSACTIONS'] .= style::replaceVar("tpl/admin/invoices/admin-ops.tpl", $admin_ops_array);
            
            if(!$warning_page){

                echo style::replaceVar("tpl/invoices/view-invoice.tpl", $view_invoice_array);
                
            }

        }else{

            //Display the invoice list
            
            //Status search
            $showstatus = "all";
            if($postvar['submitstatus']){

                $showstatus = $postvar['status'];
                
            }

            //End ststus search
            
            //Type search
            $showtype = "all";
            if($postvar['submittype']){

                $showtype = $postvar['invtype'];
                
            }

            //End type search
            
            $users[] = array("All", "all");
            $users[] = array("Orphans", "orphans");
            
            $users_query = $dbh->select("users", 0, array("user", "ASC"));
            while($users_data = $dbh->fetch_array($users_query)){

                $users[] = array($users_data['user'], $users_data['id']);
                
            }

            //User search
            $users_default = "all";
            if($postvar['submitusers']){

                $users_default = $postvar['users'];
                
                if($users_default != "all" && $users_default != "orphans"){

                    $show_user           = array("uid", "=", $users_default);
                    $show_p2h_user_where = array("id", "=", $users_default, "AND");
                    $username            = main::uname($users_default);
                    $for_user            = " For ".$username;
                    
                }

            }

            //End user search            
            $num_invoices = 0;
            $num_paid     = 0;
            $num_unpaid   = 0;
            $total_unpaid = 0;
            if($showtype == "all" || $showtype == "p2h"){

                $p2h_query = $dbh->select("packages", array("type", "=", "p2h"), 0, 0, 1);
                while($p2h_data = $dbh->fetch_array($p2h_query)){

					$show_p2h_user_where[] = array("pid", "=", $p2h_data['id']);
                    $user_query = $dbh->select("users", $show_p2h_user_where, 0, 0, 1);
                    while($user_data = $dbh->fetch_array($user_query)){

                        unset($user_show);
                        unset($orphaned);
                        $user_show = main::uname($user_data["id"]);
                        if(!$user_show){

                            $user_show = '<font color = "FF0055">ORPHANED</font>';
                            $orphaned  = 1;
                            
                        }

                        if(($orphaned && $users_default == "orphans") || $users_default != "orphans"){

                            $pack_info         = main::uidtopack($user_data['id']);
                            $p2h               = $instance->packtypes["p2h"];
                            $monthly           = $pack_info['additional']['monthly'];
                            $monthly_with_disc = coupons::get_discount("p2hmonthly", $monthly, $user_data['id']);
                            $userposts         = coupons::totalposts($user_data['id']);
                            
                            $invoice_list_item_array['ID']        = "P2H-".$user_data['id'];
                            $invoice_list_item_array['USERFIELD'] = '<td width="100" align="center">'.$user_show.'</td>';
                            $invoice_list_item_array['DUE']       = main::convertdate("n/d/Y", mktime(date("H"), date("i"), date("s"), date("n"), date("t"), date("Y")));
                            $invoice_list_item_array['CREATED']   = main::convertdate("n/d/Y", mktime(date("H"), date("i"), date("s"), date("n"), 1, date("Y")));
                            $invoice_list_item_array['AMOUNT']    = main::s($monthly, " Post");
                            $invoice_list_item_array['AMTPAID']   = main::s($userposts, " Post");
                            
                            if($showstatus == "unpaid" || $showstatus == "all"){

                                if($monthly_with_disc - $userposts > 0){

                                    $pulled                      = 1;
                                    $invoice_list_item_array["PAID"] = "<font color = '#FF7800'>Unpaid</font>";
                                    $admin_page_array['LIST'] .= style::replaceVar("tpl/invoices/invoice-list-item.tpl", $invoice_list_item_array);
                                    
                                }

                            }

                            if($showstatus == "paid" || $showstatus == "all" && !$pulled){

                                if($monthly_with_disc - $userposts <= 0){

                                    $invoice_list_item_array["PAID"] = "<font color = '#779500'>Paid</font>";
                                    $admin_page_array['LIST'] .= style::replaceVar("tpl/invoices/invoice-list-item.tpl", $invoice_list_item_array);
                                    
                                }

                            }

                            if($monthly_with_disc - $userposts > 0){

                                $total_unpaid = $total_unpaid + 1;
                                
                            }

                            $pulled       = 0;
                            $num_invoices = $num_invoices + 1;
                            
                        }

                    }

                }

            }

            if($showtype == "all" || $showtype == "paid"){

                $invoices_query = $dbh->select("invoices", $show_user, array("id", "DESC"), 0, 1);
                
                while($invoices_data = $dbh->fetch_array($invoices_query)){

                    unset($user_show);
                    unset($orphaned);
                    unset($invoice_locked);
                    $user_show = main::uname($invoices_data["uid"]);
                    if(!$user_show){

                        $user_show = '<font color = "FF0055">ORPHANED</font>';
                        $orphaned  = 1;
                        
                    }

                    if(($orphaned && $users_default == "orphans") || $users_default != "orphans"){

                        $pack_info = main::uidtopack($invoices_data["uid"], $invoices_data['pid']);
                        
                        if(!$invoices_data['pid']){

                            $dbh->update("invoices", array("pid" => $pack_info['user_data']['pid']), array("id", "=", $invoices_data['id']));
                            $invoices_data['pid'] = $pack_info['user_data']['pid'];
                            
                        }

                        if($invoices_data['pid'] != $pack_info['user_data']['pid']){

                            $pack_info = upgrade::pidtobak($invoices_data['pid'], $invoices_data["uid"]);
                            
                        }

                        $invoice_list_item_array['ID']        = $invoices_data['id'];
                        $invoice_list_item_array['USERFIELD'] = '<td width="100" align="center">'.$user_show.'</td>';
                        $invoice_list_item_array['DUE']       = main::convertdate("n/d/Y", $invoices_data['due']);
                        $invoice_list_item_array['CREATED']   = main::convertdate("n/d/Y", $invoices_data['created']);
                        $total_paid_real                  = coupons::totalpaid($invoices_data['id']);
                        
                        $invoice_list_item_array['AMOUNT']  = main::money($invoices_data['amount']);
                        $invoice_list_item_array['AMTPAID'] = main::money($total_paid_real);
                        
                        if($showstatus == "unpaid" || $showstatus == "all"){

                            if($invoices_data["is_paid"] == 0){

                                $pulled                      = 1;
                                $invoice_list_item_array["PAID"] = "<font color = '#FF7800'>Unpaid</font>".$invoice_locked;
                                $admin_page_array['LIST'] .= style::replaceVar("tpl/invoices/invoice-list-item.tpl", $invoice_list_item_array);   
								
                            }

                        }

                        if($showstatus == "paid" || $showstatus == "all" && !$pulled){

                            if($invoices_data["is_paid"] == 1){

                                $invoice_list_item_array["PAID"] = "<font color = '#779500'>Paid</font>".$invoice_locked;
                                $admin_page_array['LIST'] .= style::replaceVar("tpl/invoices/invoice-list-item.tpl", $invoice_list_item_array);
                                
                            }

                        }

                        if($invoices_data["is_paid"] == 0){

                            $total_unpaid = $total_unpaid + 1;
                            
                        }

                        $pulled       = 0;
                        $num_invoices = $num_invoices + 1;
                        
                    }

                }

            }

            if(!$admin_page_array['LIST']){

                $admin_page_array['LIST'] = "<tr>\n<td colspan = '7' align = 'center'>There are not currently any invoices to show.</td>\n</tr>";
                
            }

            $statusopts[] = array("All", "all");
            $statusopts[] = array("Unpaid", "unpaid");
            $statusopts[] = array("Paid", "paid");
            
            $typeopts[] = array("All", "all");
            $typeopts[] = array("P2H", "p2h");
            $typeopts[] = array("Paid", "paid");
            
            $admin_page_array['USERS']      = main::dropDown("users", $users, $users_default, 0);
            $admin_page_array['TYPEOPTS']   = main::dropDown("invtype", $typeopts, $showtype, 0);
            $admin_page_array['STATUSOPTS'] = main::dropDown("status", $statusopts, $showstatus, 0);
            
            $admin_page_array['FORUSER']   = $for_user;
            $admin_page_array['NUM']       = $num_invoices;
            $admin_page_array['NUMPAID']   = $num_invoices - $total_unpaid;
            $admin_page_array['NUMUNPAID'] = $total_unpaid;

            echo style::replaceVar("tpl/admin/invoices/admin-page.tpl", $admin_page_array);
			
        }

    }

}

?>