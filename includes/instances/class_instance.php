<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Instance Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

//So we don't have to make everything an instance and crowd the scripts with "global $main, $servers, $type..." at the top of every last function,
//we can save instance settings here.
class instance{

	//Email Class Variables
	public $method, $details = array(), $email = array();
	
	//Servers Class Variables
	public $servers = array();
	
	//Type Class Variables
	public $packtypes = array();

}