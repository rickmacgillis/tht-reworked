<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Index Page
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("INC", "includes");
include(INC."/compiler.php");

$page = $dbh->config('default_page');
if($page != ""){

    main::redirect($page);

}

?>