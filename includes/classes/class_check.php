<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Check Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();
    
}

class check{

    public function empty_fields($omit = array()){
        global $dbh, $postvar, $getvar, $instance;
		
        foreach($postvar as $key => $value){
            if($value == "" && !$n && !in_array($key, $omit)){

                main::errors("Please fill in all the fields!");
                $n++;
                
            }

        }

    }

    public function user($user){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$user){

            return false;
            
        }

        if(strlen($user) > 8){

            return false;
            
        }

        if(strlen($user) < 4){

            return false;
            
        }

        if(is_numeric(substr($user, 0, 1))){

            return false;
            
        }

        if(!preg_match("/^([0-9a-zA-Z])+$/", $user)){

            return false;
            
        }

        $users_query = $dbh->select("users", array("user", "=", $user));
        if(!$users_query['id']){

            return true;
            
        }

        return false;
        
    }

    public function pass($pass1, $pass2){

        if(!self::dontbreakme($pass1) || !self::dontbreakme($pass2)){

            return false;
            
        }

        if($pass1 != $pass2){

            return false;
            
        }

        return true;
        
    }

    public function email($email, $nocheckid=0, $nochecktype = "users", $format_only=0){
        global $dbh, $postvar, $getvar, $instance;
        
        $atIndex   = strrpos($email, "@");
        $domain    = substr($email, $atIndex + 1);
        $prefix    = substr($email, 0, $atIndex);
        $prefixlen = strlen($prefix);
        $domainlen = strlen($domain);
        
        if(!$email){

            return false;
            
        }

        if(!self::dontbreakme($email)){

            return false;
            
        }

        if($atIndex === false){

            return false;
            
        }

        if(!$prefixlen || $prefixlen > 64){

            return false;
            
        }

        if(!$domainlen || $domainlen > 255){

            return false;
            
        }

        //prefix can't start or end with a .
        if($prefix[0] == '.' || $prefix[$prefixlen - 1] == '.'){

            return false;
            
        }

        if(strpos($prefix, '..') !== false){

            return false;
            
        }

        if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $prefix))){ //"
            
            // Character not valid in prefix unless prefix is quoted            
            if(!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $prefix))){ //"
                
                return false;
                
            }

        }

        if(!self::domain($domain)){

            return false;
            
        }

		if(!$format_only){
		
			//The user is updating info on the same page as their own email, so it'll have to exist.
			if($nocheckid && $nochecktype = "users"){

				$users_where[] = array("id", "!=", $nocheckid, "AND");
            
			}

			//The staff member is updating info on the same page as their own email, so it'll have to exist.
			if($nocheckid && $nochecktype = "staff"){

				$staff_where[] = array("id", "!=", $nocheckid, "AND");
            
			}

			$users_where[] = array("email", "=", $email);
			$users_data    = $dbh->select("users", $users_where);
			if($users_data['id']){

				return false;
            
			}

			$staff_where[] = array("email", "=", $email);
			$staff_data    = $dbh->select("staff", $staff_where);
			if($staff_data['id']){
			
				return false;
            
			}
			
		}

        return true;
        
    }

    public function firstname($firstname){

        if(!preg_match("/^([a-zA-Z\.\'\ \-])+$/", stripslashes($firstname))){

            return false;
            
        }

        return true;
        
    }

    public function lastname($lastname){

        if(!preg_match("/^([a-zA-Z\.\'\ \-])+$/", stripslashes($lastname))){

            return false;
            
        }

        return true;
        
    }

    public function address($addr){

        if(!preg_match("/^([0-9a-zA-Z\.\ \-])+$/", $addr)){

            return false;
            
        }

        return true;
        
    }

    public function city($city){

        if(!preg_match("/^([a-zA-Z ])+$/", $city)){

            return false;
            
        }

        return true;
        
    }

    public function state($state){

        if(!preg_match("/^([a-zA-Z\.\ -])+$/", $state)){

            return false;
            
        }

        return true;
        
    }

    public function zip($zip){

        if(strlen($zip) > 10){

            return false;
            
        }

        if(!preg_match("/^([0-9a-zA-Z\ \-])+$/", $zip)){

            return false;
            
        }

        return true;
        
    }

    //Two letter country code check
    public function country($country){

        if(!self::dontbreakme($country)){

            return false;
            
        }

        if(strlen($country) != 2 || !ctype_alpha($country)){

            return false;
            
        }

        return true;
        
    }

    public function phone($phone){
        
        //Some wacky numbers get quite long.  *Thinks of a jingle*  Dial 1 87777 836 993 1234!  lol  WOW!  I wouldn't want to get stuck with that number!  =P  That's not even the full 20...
        $phone_len = strlen(str_replace("-", "", $phone));
        if($phone_len > 20 || $phone_len < 10){

            return false;
            
        }

        if(!preg_match("/^([0-9\-])+$/", $phone)){

            return false;
            
        }

        return true;
        
    }

    public function tzone($tzone){

        if(!preg_match("/^([a-zA-Z\/\_\-])+$/", $tzone)){

            return false;
            
        }

        return true;
        
    }

    public function human($human){
        
        if($human != $_SESSION["pass"]){

            return false;
            
        }

        return true;
        
    }

    public function coupon($coupon, $user, $package){
        
        if(empty($coupon)){

            return true;
            
        }

        if(type::packagetype($package) == "free"){

            return false;
            
        }

        $coupon_text = coupons::validate_coupon($coupon, "orders", $user, $package);
        if($coupon_text){

            return $coupon_text;
            
        }

        return false;
        
    }

    public function domain($domain){

        if(!$domain){

            return false;
            
        }

        if(!self::dontbreakme($domain)){

            return false;
            
        }

        if(strpos($prefix, '..') !== false){

            return false;
            
        }

        $domain_exp = explode(".", $domain);
        if(!$domain_exp[0] || !$domain_exp[1] || count($domain_exp) > 3){

            return false;
            
        }

        if(!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain_exp[0])){

            return false;
            
        }

        //For domains like domain.co.uk
        if(count($domain_exp) == 3 && !$domain_exp[2]){

            return false;
            
        }

        $pre_domain_len = strlen($domain_exp[0].".");
        
        $domain_ext = substr($domain, $pre_domain_len, strlen($domain));
        
        if(!self::iana_lookup(strtolower($domain_ext))){

            return false;
            
        }

        return true;
        
    }

    public function extra_fields(){
        global $dbh, $postvar, $getvar, $instance;
        
        foreach($postvar as $key => $value){

            if(substr($key, 0, 5) == "type_"){

                if(!self::dontbreakme($value)){

                    return false;
                    
                }

                //This is the easiest way to pass unknown variables to any type (Ex. p2h) that has a signup()
                $getvar[$key] = $value;
                
            }

        }

        return true;
        
    }

    public function IP($ip){
        global $dbh, $postvar, $getvar, $instance;
        
        $users_data = $dbh->select("users", array("ip", "=", $ip));
        if($users_data['id']){

            return false;
            
        }

        return true;
        
    }

    //lol  As kinky as this sounds, it just gets rid of unwanted characters being posted via API.  =P
    public function dontbreakme($insert_here){

        if(strpos($insert_here, "<") !== false || strpos($insert_here, ">") !== false || strpos($insert_here, "#") !== false){

            //I'm broken...
            return false;
            
        }

        return true;
        
    }

    //This returns an invoice ID for paid accounts on the order form if it's not requiring admin approval.
    function ispaid($packid, $uname){
        global $dbh, $postvar, $getvar, $instance;
        
        $packages_data = $dbh->select("packages", array("id", "=", $packid));
        if($packages_data['type'] == "paid" && $packages_data['admin'] != "1"){

            $users_data    = $dbh->select("users", array("user", "=", $uname));
            $invoices_data = $dbh->select("invoices", array("uid", "=", $users_data['id']));
            return $invoices_data['id'];
            
        }

        return false;
        
    }

    //For now this just checks valid extentions on a domain, but not the order of them.  Unfortuanately IANA's list doesn't include the position of the
    //TLD, so for now it'll allow .uk.co as well as .co.uk and the like.  This is a LOT better than allowing .coo.clux at least.  lol
    public function iana_lookup($domain_ext){
        global $dbh, $postvar, $getvar, $instance;
        
        $last_tld_update = $dbh->config("last_tld_update"); //This can be set to "never" by an admin who wishes to update the list.
        $tld_update_days = $dbh->config("tld_update_days");
        
        $tld_update_seconds = $tld_update_days * 24 * 60 * 60;
        
        if($last_tld_update == "never" || (is_numeric($last_tld_update) && $last_tld_update <= time() - $tld_update_seconds)){

            //We need to update our copy of the TLD list            
            $iana_list = "data.iana.org/TLD/tlds-alpha-by-domain.txt";
            
            $ch      = curl_init();
            $timeout = 5;
            
            if(isset($_SERVER['HTTPS'])){

                curl_setopt($ch, CURLOPT_URL, "https://".$iana_list);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
            }else{

                curl_setopt($ch, CURLOPT_URL, "http://".$iana_list);
                
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $curl_data = curl_exec($ch);
            curl_close($ch);
            
            if($curl_data){

                //To effectively lock the table so no one else updates the list while we are, we set the config value to "now"
                $dbh->updateConfig("last_tld_update", "now");
                $dbh->delete("tld", 0, 0, 1); //We could use TRUNCATE, but that means you need to be granted execute permissions on MySQL which is a pointless security risk.
                
                $lines = explode("\n", $curl_data);
                
                //Line 1 is the list update info, so we start at line 2.
                for($i = 1; $i < count($lines); $i++){

                    if($lines[$i]){

                        $tld_insert = array(
                            "tld" => $lines[$i],
                            "id"  => $i
                        ); //Setting the ID manually will make it so the integer value won't overflow.
                        $dbh->insert("tld", $tld_insert);
                        
                    }

                }

                $dbh->updateConfig("last_tld_update", time());
                
            }

        }

        //If no one has the hook then we're clear to start pulling data for checking data and there's been some imported.
        if($last_tld_update != "now" && $last_tld_update != "never"){

            $domain_ext = explode(".", $domain_ext);
            
            $tld_query = $dbh->select("tld");
            
            while($tld_data = $dbh->fetch_array($tld_query)){

                $tld = strtolower($tld_data['tld']);
                
                if($domain_ext[0] == $tld){

                    $tld1_validated = 1;
                    
                }

                if($tld1_validated && count($domain_ext) == 1){

                    //We have a single part TLD and have validated the TLD, so we can break the loop.
                    break;
                    
                }else{

                    if($domain_ext[1] == $tld){

                        $tld2_validated = 1;
                        break;
                        
                    }

                }

            }

            if($tld1_validated == 1){

                if(count($domain_ext) == 2){

                    if($tld2_validated == 1){

                        return true;
                        
                    }else{

                        return false;
                        
                    }

                }else{

                    return true;
                    
                }

            }else{

                return false;
                
            }

        }

        //If we can't contact IANA or use our DB to validate the TLD right now, so we just assume the domain ext is valid and hope the rest of the checks caught any discrepencies.
        return true;
        
    }

}

?>