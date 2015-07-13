<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// VPS Server Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class vps{

    public $name = "VPS";
    public $canupgrade = true;
    public $subdomains = false; //Can you register an account with a subdomain as well as a domain?
    
    public function __construct($serverId = null){
	
	}
	
	public function acp_packages_form($packId = null){
		
		return;
	
	}
	
	public function acp_form($serverId = null){
	
		return;
		
	}

    public function signup($server, $reseller, $user, $email, $pass, $domain, $server_pack, $extra = array(), $domsub){
	
		return true;

    }

    public function suspend($user, $server, $reason = false){

        return true;
    
    }

    public function unsuspend($user, $server){

        return true;
    
    }

    public function terminate($user, $server){

        return true;
    
    }

    public function testConnection($serverId = null){

        return true;

    }

    public function changePwd($acct, $newpwd, $server){

        return true;
        
    }	
	
    public function do_upgrade($server, $pkg, $user){

        return true;

    }

}

?>