<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Output
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(!defined("THT")){

    die();

}

$html_buff = ob_get_contents();
ob_end_clean();
echo style::prepare($html_buff);

?>