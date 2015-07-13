<?php
//////////////////////////////
// The Hosting Tool
// Package Types Class
// By Jonny H
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

//Create the class
class type {

        public $classes = array(); # All the classes here when createAll called

        # Start the functions #

        public function acpPadd($type) { # Returns the html of a custom form
                global $db, $style;

                if(!$this->classes[$type]) {
                        $type = $this->createType($type);
                }
                else {
                        $type = $this->classes[$type];
                }
                if($type->acpForm) {
                        $html .= $style->javascript();
                        $html .= '<script type="text/javascript">
                        var gi = 0;
                        $(document).ready(function(){
                            //var info = new Array();
                            var info;
                            $("#submitIt").click(function() {
                                $("input").each(function(i) {
                                    if(gi == 0) {
                                        info = this.name + "="  + $("#" + this.id).val();
                                    }
                                    else {
                                        info = info + "," + this.name + "="  + $("#" + this.id).val();
                                    }


                                    gi++;
                                });
                                $("select").each(function(i) {
                                    if(gi == 0) {
                                        info = this.name + "="  + $("#" + this.id).val();
                                    }
                                    else {
                                        info = info + "," + this.name + "="  + $("#" + this.id).val();
                                    }
                                    gi++;
                                });
                                var id = window.name.toString().split("-")[1];
                                window.opener.transfer(id, info);
                                window.close();
                            });
                        });
                        </script>';

                        foreach($type->acpForm as $key => $value) {
                                $array['NAME'] = $value[0] .":";
                                $array['FORM'] = $value[1];
                                $html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
                        }
                        return $html;
                }
        }

        public function orderForm($type) { # Returns the html of a custom form
                global $style;
                if(!$this->classes[$type]) {
                        $type = $this->createType($type);
                }
                else {
                        $type = $this->classes[$type];
                }
                if($type->orderForm) {
                        foreach($type->orderForm as $key => $value) {
                                $array['NAME'] = $value[0] .":";
                                $array['FORM'] = $value[1];
                                $html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
                        }
                        return $html;
                }
        }

        public function signupForm($type) { # Returns the html of a custom form
                global $style;
                if(!$this->classes[$type]) {
                        $type = $this->createType($type);
                }
                else {
                        $type = $this->classes[$type];
                }
                if($type->acpForm) {
                        foreach($type->acpForm as $key => $value) {
                                $array['NAME'] = $value[0] .":";
                                $array['FORM'] = $value[1];
                                $html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
                        }
                        return $html;
                }
        }

        public function createType($type) { # Creates a class and then returns it
                $file = LINK . "types/". $type .".php";
                if(!file_exists($file)) {
                        echo "Type doesn't exist!";
                }
                else {
                        include($file);
                        $type = new $type;
                        return $type;
                }
        }

        public function createAll() { # Creates all types and returns them
                global $main;
                $files = $main->folderFiles(LINK ."types/");
                foreach($files as $value) {
                        $data = explode(".", $value);
                        if($data[1] != "svn" and $data[1] == "php") {
                                $classes[$data[0]] = $this->createtype($data[0]);
                        }
                }
                $this->classes = $classes;
        }

        public function determineType($id) { # Returns type of a package
                global $db;
                global $main;
                $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist!";
                        $array['Package ID'] = $id;
                        $main->error($array);
                        return;
                }
                else {
                        $data = $db->fetch_array($query);
                        return $data['type'];
                }
        }
        public function determineServer($id) { # Returns server of a package
                global $db;
                global $main;
                $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist!";
                        $array['Package ID'] = $id;
                        $main->error($array);
                        return;
                }
                else {
                        $data = $db->fetch_array($query);
                        return $data['server'];
                }
        }
        public function determineServerType($id) { # Returns server of a package
                global $db;
                global $main;
                $query = $db->query("SELECT * FROM `<PRE>servers` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That server doesn't exist!";
                        $array['Server ID'] = $id;
                        $main->error($array);
                        return;
                }
                else {
                        $data = $db->fetch_array($query);
                        return $data['type'];
                }
        }
        public function determineBackend($id) { // Returns server of a package
                global $db;
                global $main;
                $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist!";
                        $array['Package ID'] = $id;
                        $main->error($array);
                        return;
                }
                else {
                        $data = $db->fetch_array($query);
                        return $data['backend'];
                }
        }

        public function acpPedit($type, $values, $origtype) { // Returns the type's acpForm[] content
                global $style;
                $usingtype = $type;
                if(!$this->classes[$type]) {
                        $type = $this->createType($type);
                }
                else {
                        $type = $this->classes[$type];
                }
                if($type->acpForm) {

                        $html .= $style->javascript();
                        $html .= '<script type="text/javascript">
                        var gi = 0;
                        $(document).ready(function(){
                            //var info = new Array();
                            var info;
                            $("#submitIt").click(function() {
                                $("input").each(function(i) {
                                    if(gi == 0) {
                                        info = this.name + "="  + $("#" + this.id).val();
                                    }
                                    else {
                                        info = info + "," + this.name + "="  + $("#" + this.id).val();
                                    }


                                    gi++;
                                });
                                $("select").each(function(i) {
                                    if(gi == 0) {
                                        info = this.name + "="  + $("#" + this.id).val();
                                    }
                                    else {
                                        info = info + "," + this.name + "="  + $("#" + this.id).val();
                                    }
                                    gi++;
                                });
                                var id = window.name.toString().split("-")[1];
                                window.opener.transfer(id, info);
                                window.close();
                            });
                        });
                        </script>';
                        

                                if($usingtype != $origtype){
                                foreach($type->acpForm as $key => $value) {
                                $array['NAME'] = $value[0] .":";
                                $array['FORM'] = $value[1];
                                $html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
                                }
                                }else{
                                $values = explode(",", $values);
                                foreach($values as $key => $value) {
                                 $me = explode("=", $value);
                                 $cform[$me[0]] = $me[1];
                                }
                                foreach($type->acpForm as $value) {
                                $array['NAME'] = $value[0] .":";
                                $hit = explode("/>", $value[1]); // haha... $hit
                                $default = "";
                                if(stripos($value[1], "</select>") === false) {
                                        $default = ' value="'.$cform[$value[2]].'" />';
                                }
                                $array['FORM'] = $hit[0]. $default;
                                $html .= $style->replaceVar("tpl/acptypeform.tpl", $array);
                                }
                                }
                        return $html;
                }
        }
        
        public function editbackupservers($server) { // Returns the backup server settings form for ACP
        global $db,$style;
        if($server){
                        $html .= $style->javascript();
                        $html .= '<script type="text/javascript">
                        var gi = 0;
                        $(document).ready(function(){
                            //var info = new Array();
                            var info;
                            $("#submitIt").click(function() {
                                $("input").each(function(i) {
                                    if(gi == 0) {
                                        info = this.name + "="  + $("#" + this.id).val();
                                    }
                                    else {
                                        info = info + "," + this.name + "="  + $("#" + this.id).val();
                                    }


                                    gi++;
                                });
                                $("select").each(function(i) {
                                    if(gi == 0) {
                                        info = this.name + "="  + $("#" + this.id).val();
                                    }
                                    else {
                                        info = info + "," + this.name + "="  + $("#" + this.id).val();
                                    }
                                    gi++;
                                });
                                var id = window.name.toString().split("-")[1];
                                window.opener.transfer(id, info);
                                window.close();
                            });
                        });
                        </script>';

                                $serverinfo = $db->query("SELECT * FROM <PRE>servers WHERE id = '".$server."'");
                                $serverinfo = $db->fetch_array($serverinfo);
                                
                                if(!$serverinfo['ftpport']){
                                $serverinfo['ftpport'] = "21";
                                }

                                $array['NAME'] = "FTP User:";
                                $array['NAMEID'] = "ftpuser";
                                $array['INPUTTYPE'] = "test";
                                $array['VALUE'] = $serverinfo['ftpuser'];
                                $array['TTINFO'] = "The FTP username to log into the backup server.";
                                $array['BUTTONCODE'] = "";
                                $html .= $style->replaceVar("tpl/backupserversform.tpl", $array);

                                $array['NAME'] = "FTP Pass:";
                                $array['NAMEID'] = "ftppass";
                                $array['INPUTTYPE'] = "password";
                                $array['VALUE'] = $serverinfo['ftppass'];
                                $array['TTINFO'] = "The FTP password to log into the backup server.";
                                $array['BUTTONCODE'] = "";
                                $html .= $style->replaceVar("tpl/backupserversform.tpl", $array);

                                $array['NAME'] = "FTP Port:";
                                $array['NAMEID'] = "ftpport";
                                $array['INPUTTYPE'] = "test";
                                $array['VALUE'] = $serverinfo['ftpport'];
                                $array['TTINFO'] = "The FTP port to log into the backup server on.";
                                $array['BUTTONCODE'] = "";
                                $html .= $style->replaceVar("tpl/backupserversform.tpl", $array);

                                $array['NAME'] = "FTP Host:";
                                $array['NAMEID'] = "ftphost";
                                $array['INPUTTYPE'] = "test";
                                $array['VALUE'] = $serverinfo['ftphost'];
                                $array['TTINFO'] = "The host containing the backup files.  This can be an IP or a domain, but don't add the http://.";
                                $array['BUTTONCODE'] = "";
                                $html .= $style->replaceVar("tpl/backupserversform.tpl", $array);

                                $array['NAME'] = "FTP Path:";
                                $array['NAMEID'] = "ftppath";
                                $array['INPUTTYPE'] = "test";
                                $array['VALUE'] = $serverinfo['ftppath'];
                                $array['TTINFO'] = "The path to change to after login.  This cannot be a full path and must be a path accessible from the point it logs in at.  (Ex. /%UNAME% or /backups/%UNAME% not /home/backups/%UNAME%)  To use the username of the client being backed up, enter %UNAME%.  %UNAME% may only appear at the end of the path or the connection will fail.  The directory's contents will be shown to the client performing the restore.";
                                $array['BUTTONCODE'] = '<input type="submit" name="updateserver" id="updateserver" value="Update Server" />';
                                $html .= $style->replaceVar("tpl/backupserversform.tpl", $array);

                                return $html;
        }else{
        return "";
        }
        }

        public function additional($id) { // Returns the additonal values on a package
                global $db;
                $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($id)}'");
                $data = $db->fetch_array($query);
                $content = explode(",", $data['additional']);
                foreach($content as $key => $value) {
                        $inside = explode("=", $value);
                        $values[$inside[0]] = $inside[1];
                }
                return $values;
        }

        public function userAdditional($id) { // Returns the additional info of a PID
                global $db, $main;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That user pack doesn't exist!";
                        $array['PID'] = $id;
                        $main->error($array);
                        return;
                }
                else {
                        $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
                        $data = $db->fetch_array($query);
                        $content = explode(",", $data['additional']);
                        foreach($content as $key => $value) {
                                $inside = explode("=", $value);
                                $values[$inside[0]] = $inside[1];
                        }
                        return $values;
                }
        }
}
//End Type
?>
