<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Cron Job
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("INC", ".");
define("CRON", 1);
include("compiler.php");
set_time_limit(0);
ob_start();

//Upgrade all the users before we start processing the type crons.
upgrade::cron();

$classes = $instance->packtypes;

foreach($classes as $key => $value){

    if(method_exists($classes[$key], "cron")){

        $classes[$key]->cron();
    
    }

}

$html_buff = ob_get_clean();
echo $html_buff;

if($html_buff != "" && $dbh->config("emailoncron") == "1" && $dbh->config("email_for_cron")){

	email::send($dbh->config("email_for_cron"), "Cron Job", $html_buff);

}

?>