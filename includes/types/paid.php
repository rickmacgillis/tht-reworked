<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Paid Hosting Type
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class paid{

    public $acpForm = array(), $orderForm = array(), $acpNav = array(), $acpSubNav = array(); // The HTML Forms arrays
    public $name = "Paid";     // Human readable name of the package.
    
    public function __construct(){
        
        $this->acpNav[]  = array("Paid Configuration", "paid", "coins.png", "Paid Configuration");
        $this->acpForm[] = array("Monthly Cost", '<input name="monthly" type="text" id="monthly" size="5" />', 'monthly');
    
    }

    public function cron(){
        global $dbh, $postvar, $getvar, $instance;

        $packages_query = $dbh->select("packages", array("type" => "paid"), 0, 0, 1);		
		$packages_num_rows = $dbh->num_rows($packages_query);
		
		//Do we have paid packages?
		if($packages_num_rows){
		
			while($packages_data = $dbh->fetch_array($packages_query)){

				$i++;
				
				//Do we have multiple packages and aren't on the last one?
				if($packages_data < $i){
				
					//Did we already pull one package?  If so, we don't set the flag for the parenthesis.
					if($pulled_one){
					
						$where[] = array("pid", "=", $packages_data['id'], "OR");
						
					//We are on the first listing of the paid packages, so we set the flag for the opening parenthesis and mark it that we pulled one already.
					}else{
					
						$where[] = array("pid", "=", $packages_data['id'], "OR", 1);
						$pulled_one = 1;
						
					}
				
				}else{
				
					//Are we on the last listing of paid listings?  If so, we close the parenthesis by setting that flag.
					if($pulled_one){
					
						$where[] = array("pid", "=", $packages_data['id'], "", 1);
						
					//We only had one listing, so we don't use parenthesis and we don't use "OR."
					}else{
					
						$where[] = array("pid", "=", $packages_data['id']);
						
					}
				
				}
				
				//So we can later grab the package's information without needing to repull this data.
				$packages_info[$packages_data['id']] = $packages_data;
				
			}
			
			$time = time();
			
			//Look at every last invoice.
			$invoices_query = $dbh->select("invoices");
			while($invoices_data = $dbh->fetch_array($invoices_query)){
			
				$uid    = $invoices_data['uid'];
				$client = $dbh->client($uid);
				
				//Skip this invoice if it belongs to a user marked as a free user.
				if($client['freeuser']){
				
					continue;
				
				}

				//If the invoice is older than 30 days and we haven't issued a new invoice yet...  (This makes sure the user is still on the package
				//before issuing a new invoice for it.)
				if($time > $invoices_data['created'] + 2592000 && !in_array($uid, $invoiced_to) && $invoices_data['pid'] == $client['pid']){
				
					$pack_additional = type::additional($client['pid']);
					$amount          = coupons::get_discount("paid", $pack_additional['monthly'], $client['user']);
					invoice::create($uid, $amount, $time, "Your hosting package invoice for this billing cycle. Package: ".$packages_info[$client['pid']]['name']);
					
					$invoiced_to[] = $uid; //Track what clients have been sent a new invoice.
				
				}

				$lastmonth        = $time - 2592000;
				$suspenddays      = $dbh->config('suspensiondays');
				$terminationdays  = $suspenddays + $dbh->config('terminationdays');
				$suspendseconds   = $suspenddays * 24 * 60 * 60;
				$terminateseconds = $dbh->config('terminationdays') * 24 * 60 * 60;
				
				//If we have an unpaid bill that's greater than $0 and it's past it's due date...
				if($invoices_data['due'] < $time and $invoices_data['is_paid'] == 0 && $invoices_data['amount'] > 0){

					//If we have a bill that's overdue by $terminationdays + $suspenddays, then we terminate the account...
					if(($time - $suspendseconds - $terminateseconds) > $invoices_data['due']){

						server::terminate($uid, "Your account was overdue for more than ".$terminationdays." days.");
						$checked_term = 1;
					
					//If we have a bill that's overdue by $suspenddays and the client is active, then we suspend them...
					//Just an FYI, if I start(ed) charging for this script, check The Pirate Bay for this script as I always upload my payware stuff there since I know not everyone can afford to pay me.
					}elseif(($time - $suspendseconds) > $invoices_data['due'] && $client['status'] == '1'){

						server::suspend($uid, "Your account is overdue.  Please log in and pay your invoice to bring your account out of suspension.");
					
					}

				}

			}
			
			//If the user does not have an invoice yet and never had one, this will create one for them.  The portion above
			//handles creating NEW invoices.  (It checks for outdated ones and such.)
			$users_query = $dbh->select("users", $where, 0, 0, 1);
			while($users_data = $dbh->fetch_array($users_query)){			
			
				//Skip this user if its marked as a free user.
				if($users_data['freeuser']){
				
					continue;
				
				}
			
				$invoice_data = $dbh->select("invoices", array("pid", "=", $users_data['pid']));				
				if(!$invoice_data['id']){				
					
					$monthly = type::additional($users_data['pid']);
					$amount  = $monthly['monthly'];
					$amount  = coupons::get_discount("paid", $amount, $users_data['id']);
					invoice::create($users_data['id'], $amount, $time + (30 * 24 * 60 * 60), "Your hosting package invoice for this billing cycle. Package: ".$packages_info[$users_data['pid']]['name']); // Create Invoice +30 Days
				
				}
			
			}
		
		}

    }	
	
    public function acpPage(){
        global $dbh, $postvar, $getvar, $instance;
        
        if($_POST){

            check::empty_fields(array("password", "paypalsandemail"));
            if(!main::errors()){

                if(is_numeric($postvar['susdays']) && is_numeric($postvar['termdays'])){

                    $dbh->updateConfig("suspensiondays", $postvar['susdays']);
                    $dbh->updateConfig("terminationdays", $postvar['termdays']);
                    $dbh->updateConfig("currency", $postvar['currency']);
                    $dbh->updateConfig("currency_format", $postvar['currency_format']);
                    $dbh->updateConfig("paypalemail", $postvar['paypalemail']);
                    $dbh->updateConfig("paypalmode", $postvar['paypalmode']);
                    $dbh->updateConfig("paypalsandemail", $postvar['paypalsandemail']);
                    main::errors("Values have been updated!");
                
                }else{

                    main::errors("Please enter a valid value!");
                
                }

            }

        }

        $currency_values[] = array("Pound Sterling", "GBP");
        $currency_values[] = array("US Dollars", "USD");
        $currency_values[] = array("Australian Dollars", "AUD");
        $currency_values[] = array("Canadian Dollars", "CAD");
        $currency_values[] = array("Euros", "EUR");
        $currency_values[] = array("Yen", "JPY");
        $currency_values[] = array("New Zealand Dollar", "NZD");
        $currency_values[] = array("Swiss Franc", "CHF");
        $currency_values[] = array("Hong Kong Dollar", "HKD");
        $currency_values[] = array("Singapore Dollar", "SGD");
        $currency_values[] = array("Swedish Krona", "SEK");
        $currency_values[] = array("Danish Krone", "DKK");
        $currency_values[] = array("Polish Zloty", "PLN");
        $currency_values[] = array("Norwegian Krone", "NOK");
        $currency_values[] = array("Hungarian Forint", "HUF");
        $currency_values[] = array("Czech Koruna", "CZK");
        $currency_values[] = array("Israeli Shekel", "ILS");
        $currency_values[] = array("Mexican Peso", "MXN");
        
        $paypal_values[] = array("Live", "live");
        $paypal_values[] = array("Sandbox", "sandbox");
        
        $currency_format_values[] = array("1,000.99", ".");
        $currency_format_values[] = array("1 000,99", ",");
        
        $paid_configuration_array['CURRENCY']        = main::dropDown("currency", $currency_values, $dbh->config("currency"));
        $paid_configuration_array['CURRENCY_FORMAT'] = main::dropDown("currency_format", $currency_format_values, $dbh->config("currency_format"));
        $paid_configuration_array['PAYPALMODE']      = main::dropDown("paypalmode", $paypal_values, $dbh->config("paypalmode"));
        $paid_configuration_array['PAYPALSANDEMAIL'] = $dbh->config("paypalsandemail");
        $paid_configuration_array['SUSDAYS']         = $dbh->config("suspensiondays");
        $paid_configuration_array['TERDAYS']         = $dbh->config("terminationdays");
        $paid_configuration_array['PAYPALEMAIL']     = $dbh->config("paypalemail");
        
        echo style::replaceVar("tpl/admin/paid-configuration.tpl", $paid_configuration_array);
        
    }

}

?>