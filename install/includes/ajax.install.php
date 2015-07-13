<?php
//////////////////////////////
// The Hosting Tool
// Ajax Class
// By Jonny H and Kevin M
// Released under the GNU-GPL
//////////////////////////////

define("LINK", "../../includes/");
include(LINK."/compiler.php");

class Installer {
        public function sqlcheck() {
                global $main, $style;
                if(INSTALL != 1) {
                        $host = $_GET['host'];
                        $user = $_GET['user'];
                        $pass = $_GET['pass'];
                        $db = $_GET['db'];
                        $pre = $_GET['pre'];
                        //die($_SERVER['REQUEST_URI']);
                        $con = @mysql_connect($host, $user, $pass);
                        if(!$con) {
                                echo 0;
                        }
                        else {
                                $seldb = mysql_select_db($db, $con);
                                if(!$seldb) {
                                        echo 1;
                                }
                                else {
                                        if($this->writeconfig($host, $user, $pass, $db, $pre, "false")) {
                                                echo 2;
                                        }
                                        else {
                                                echo 3;
                                        }
                                }
                        }
                }
                else {
                        echo 4;
                }
        }
        private function writeconfig($host, $user, $pass, $db, $pre, $true) {
                global $style;
                $array['HOST'] =  $host;
                $array['USER'] =  $user;
                $array['PASS'] =  $pass;
                $array['DB'] =  $db;
                $array['PRE'] =  $pre;
                $array['TRUE'] = $true;
                $tpl = $style->replaceVar("../install/includes/tpl/conftemp.tpl", $array);
                $link = LINK."conf.inc.php";
                chmod($link, 0666);
                if(is_writable($link)) {
                        file_put_contents($link, $tpl);
                        return true;
                }else {
                        return false;
                }
        }
        public function install() {
                global $style, $db, $main;
                if(INSTALL != 1) {
                        include(LINK."conf.inc.php");
                        $dbCon = mysql_connect($sql['host'], $sql['user'], $sql['pass']);
                        $dbSel = mysql_select_db($sql['db'], $dbCon);
                        if($main->getvar['type'] == "install") {
                                $errors = $this->installsql("sql/install.sql", $sql['pre'], $dbCon);
                        }
                        elseif($main->getvar['type'] == "upgrade") {
                                $errors = $this->installsql("sql/upgrade.sql", $sql['pre'], $dbCon);
                                $porders = mysql_query("SELECT * FROM `{$sql['pre']}packages`", $dbCon);
                                $n = 1;
                                while($data = mysql_fetch_array($porders)) {
                                        if($data['oid'] == "0") {
                                                mysql_query("UPDATE `{$sql['pre']}packages` SET `oid` = '{$n}' WHERE `id` = '{$data['id']}'", $dbCon);
                                                $n++;
                                        }
                                }
                                if($n > 1) {
                                        mysql_query("ALTER TABLE `{$sql['pre']}packages` ADD UNIQUE (`oid`)", $dbCon);
                                }
                        }
                        else {
                                echo "Eh? Fatal Error Debug: ". $main->getvar['type'];
                        }
                        $ver = mysql_real_escape_string($_GET['version']);
                        $query = mysql_query("UPDATE `{$sql['pre']}config` SET `value` = '{$ver}' WHERE `name` = 'version'");
                        if(!$query) {
                                echo '<div class="errors">There was a problem editing your script version!</div>';
                        }
                        if($main->getvar['type'] == "install") {
                                $query = mysql_query("UPDATE `{$sql['pre']}config` SET `value` = 'bluelust' WHERE `name` = 'theme'");
                                if(!$query) {
                                        echo '<div class="errors">There was a problem setting your default theme!</div>';
                                }
                        }
                        echo "Complete!<br /><strong>There were ".$errors['n']." errors while executing the SQL!</strong><br />";
                        if(!$this->writeconfig($sql['host'], $sql['user'], $sql['pass'], $sql['db'], $sql['pre'], "true")) {
                                echo '<div class="errors">There was a problem re-writing to the config!</div>';
                        }
                        if($main->getvar['type'] == "install") {
                                echo '<div align="center"><input type="button" name="button4" id="button4" value="Next Step" onclick="change()" /></div>';
                        }
                        elseif($main->getvar['type'] == "upgrade") {
                                echo '<div class="errors">Your upgrade is now complete! You can use the script as normal.</div>';
                        }
                        if($errors['n']) {
                                echo "<strong>SQL Queries (Broke):</strong><br /><pre>";
                                foreach($errors['errors'] as $value) {
                                        echo nl2br(htmlentities($value))."<br /><br />";
                                }
                                echo "</pre>";
                        }
                }
        }
        private function installsql($data, $pre, $con = 0) {
                global $style, $db;
                $array['PRE'] = $pre;
                $sContents = $style->replaceVar($data, $array, 0, 1);
                // replace slash quotes so they don't get in the way during parse
                // tried a replacement array for this but it didn't work
                // what's a couple extra lines of code, anyway?

                $sDoubleSlash   = '~~DOUBLE_SLASH~~';
                $sSlashQuote    = '~~SLASH_QUOTE~~';
                $sSlashSQuote   = '~~SLASH_SQUOTE~~';

                $sContents = str_replace('\\\\', $sDoubleSlash,  $sContents);         //'
                $sContents = str_replace('\"', $sSlashQuote,  $sContents);
                $sContents = str_replace("\'", $sSlashSQuote, $sContents);

                $iContents = strlen($sContents);
                $sDefaultDelimiter = ';';

                $aSql = array();
                $sSql = '';
                $bInQuote   = false;
                $sDelimiter = $sDefaultDelimiter;
                $iDelimiter = strlen($sDelimiter);
                $aQuote = array("'", '"');
                for ($i = 0;  $i < $iContents;  $i++) {
                        if ($sContents[$i] == "\n"
                        ||  $sContents[$i] == "\r") {
                                // Check for Delimiter Statement
                                if (preg_match('/delimiter\s+(.+)/i', $sSql, $aMatches)) {
                                                $sDelimiter = $aMatches[1];
                                                $iDelimiter = strlen($sDelimiter);
                                                $sSql = '';
                                                continue;
                                }
                        }

                        if (in_array($sContents[$i], $aQuote)) {
                                $bInQuote = !$bInQuote;
                                if ($bInQuote) {
                                                $aQuote = array($sContents[$i]);
                                } else {
                                                $aQuote = array("'", '"');
                                }
                        }

                        if ($bInQuote) {
                                $sSql .= $sContents[$i];
                        } else {
                                // fill a var with the potential delimiter - aka read-ahead
                                if(substr($sContents, $i, $iDelimiter) == $sDelimiter) {
                                                // Clear Comments
                                                $sSql = preg_replace("/^(-{2,}.+)/", '', $sSql);
                                                $sSql = preg_replace("/(?:\r|\n)(-{2,}.+)/", '', $sSql);

                                                // Put quotes back where you found them
                                                $sSql = str_replace($sDoubleSlash, '\\\\',  $sSql);             //'
                                                $sSql = str_replace($sSlashQuote,  '\\"',   $sSql);
                                                $sSql = str_replace($sSlashSQuote, "\\'",   $sSql);

                                                // FIXME: odd replacement issue, just fix it for now and move on
                                                $sSql = str_replace('IFEXISTS`', 'IF EXISTS `', $sSql);

                                                $aSql[] = $sSql;
                                                $sSql = '';

                                                // pass delimiter
                                                $i += $iDelimiter;
                                } else {
                                                $sSql .= $sContents[$i];
                                }
                        }
                }

                $aSql = array_map('trim', $aSql);
                $aSql = array_filter($aSql);

                $n = 0;
                foreach($aSql as $sSql) {
                        if($con) {
                                $query = mysql_query($sSql, $con);
                        }
                        else {
                                $query = $db->query($sSql);
                        }
                        if(!$query) {
                                $n++;
                                $errors[] = $sSql;
                        }
                }
                if(!$n) {
                        $n = 0;
                }
                $stuff['n'] = $n;
                $stuff['errors'] = $errors;
                return $stuff;
        }
        public function installfinal() {
                global $db, $main;
                $query = $db->query("SELECT * FROM `<PRE>staff`");
                if(!$db->num_rows($query)) {
                        foreach($main->getvar as $key => $value) {
                                if(!$value) {
                                        $n++;
                                }
                        }
                        if(!$n) {
                                $db->query("UPDATE `<PRE>config` SET `value` = '{$main->getvar['url']}' WHERE `name` = 'url'");
                                $salt = md5(rand(0,99999));
                                $password = md5(md5($main->getvar['pass']).md5($salt));
                                $db->query("INSERT INTO `<PRE>staff` (user, email, password, salt, name) VALUES(
                                                                                                                                                                  '{$main->getvar['user']}',
                                                                                                                                                                  '{$main->getvar['email']}',
                                                                                                                                                                  '{$password}',
                                                                                                                                                                  '{$salt}',
                                                                                                                                                                  '{$main->getvar['name']}')");
                                echo 1;
                        }
                        else {
                                echo 0;
                        }
                }
        }
}

if(isset($_REQUEST['function']) and $_REQUEST['function'] != "") {
        $Ajaxinstaller = new Installer();
        if(method_exists($Ajaxinstaller, $_REQUEST['function'])) {
                $Ajaxinstaller->{$_REQUEST['function']}();
                require(LINK."output.php");
        }
}

?>
