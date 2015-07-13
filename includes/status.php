<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Server Status Image
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

list($addr, $port) = explode(':', str_replace("::", ":", $_GET['link']));
if(empty($port)){

    $port = 80;

}

//Test the server connection
if(!@fsockopen(server($addr), $port, $errno, $errstr, 5)){

    header("Location: ../themes/icons/lightbulb_off.png");

}else{

    header("Location: ../themes/icons/lightbulb.png");

}

function server($addr){

    if(strstr($addr, "/")){

        $addr = substr($addr, 0, strpos($addr, "/"));
    
    }

    return $addr;

}

?>