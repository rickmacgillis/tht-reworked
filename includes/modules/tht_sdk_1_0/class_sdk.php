<?php
//////////////////////////////
// The Hosting Tool - THT SDK
// SDK Class
// By NA'ven Enigma
// Released under the GNU-GPLv3
//////////////////////////////

//Check if called by script
if(THT != 1){die();}


//List of functions in the SDK:
//
//tdata($tablename, $column = "", $find = "", $where_clause = "", $return_query = 0, $orderclause = "", $like = 0)
//insert($table, $array)
//update($table, $array, $column = "", $searchfor = "", $whereclause = "", $like = 0)
//money($amount, $code = "", $noamount = 0, $local = "en-US")
//uname($id, $table = "users")
//userid($uname, $table = "users")
//uidtopack($userid = "")
//thtlog($message, $uid = "", $user = "", $usertable = "users", $time = "")
//addzeros($number)
//tpl($mod_dir, $tpl_file, $array = 0)
//isint($mixed_var)
//dropDown($name, $values, $default = 0, $top = 1, $class = "", $custom = "")
//s($number, $prefix = "")




//This is an SDK built to simplify programming for THT.  THT is already very simplistic in how you use it, but this SDK
//makes it even easier to program for.  I've noticed some repetition in the THT code like you use query() and then you
//run it through fetch_array() or num_rows(), but sometimes its simpler to just specify the tablename, column name, and
//field you wish to return when you just want one row.  So, that's why I built tdata().  Its short and sweet and to the
//point.  So, look through the class and read about what each function does and I'm sure you'll find these functions to
//be very useful for you.  I tried to keep all functions names and the class name short as well so you don't have to type
//as much.
//
//Enjoy!
//
//Na'ven

//Create the class
class sdk{

        //tdata() Usage:
        //
        //If like is 1 then we use like instead of = and $find will be searched for in that fashion.
        //Order clause is the order clause and you can set $return_query to 1 if you want multiple
        //rows returned. If $column and $find or $where_clause is not specified, we will always return
        //the query as we will always pull all the rows.  (Ex. tdata("tablename") will pull all the rows
        //in that table.  This cuts down on typing in extra parameters as well.
        public function tdata($tablename, $column = "", $find = "", $where_clause = "", $return_query = 0, $orderclause = "", $like = 0){
         global $db;

         if($column && $find || $where_clause){
          if($like){
            $operand = "LIKE";
          }else{
            $operand = "=";
          }

          if(empty($where_clause)){
          $where_clause = "WHERE ".$column." ".$operand." '".$find."'";
          }
         }else{
          $return_query = 1;
         }

         $table_info_query = $db->query(trim("SELECT * FROM <PRE>".$tablename." ".$where_clause." ".$orderclause));

         if($return_query){
           return $table_info_query;
         }else{
           $table_info_data = $db->fetch_array($table_info_query);
           return $table_info_data;
         }

        }
        


        //This takes in a table name and an array and inserts the specified data into the specified table in the DB.
        //The array's keys must be the same as the column names and the array's values must be the values for those columns.
        //The main focus of this is to make lengthy queries easier to read and maintain.
        public function insert($table, $array){
                global $db;

                foreach($array as $key => $val){
                        $insert_query .= ", ".$key." = '".$val."'";
                }

                $insert_query = substr($insert_query, 2, strlen($insert_query));
                $db->query("INSERT INTO <PRE>".$table." SET ".$insert_query);
        }
        
        
        

        //This takes in a table name, array, and where clause elements and updates the specified data into the specified table in the DB.
        //The array's keys must be the same as the column names and the array's values must be the values for those columns.
        //The main focus of this is to make lengthy queries easier to read and maintain.
        //
        //If you only need to specify one column name and value for the where clause, then you can use:
        //$column - The column for the where clause
        //$searchfor - The value of the column for the where clause
        //$like - If $like = 1 then we use LIKE instead of = and you can add the % into the $searchfor value.
        //
        //$whereclause - Use this instead of the above values in order to specify a custom where clause.
        public function update($table, $array, $column = "", $searchfor = "", $whereclause = "", $like = 0){
                global $db;

                if($like){
                        $operand = "LIKE";
                }else{
                        $operand = "=";
                }

                if(empty($whereclause)){
                        $whereclause = "WHERE ".$column." ".$operand." '".$searchfor."'";
                }



                foreach($array as $key => $val){
                        $insert_query .= ", ".$key." = '".$val."'";
                }

                $insert_query = substr($insert_query, 2, strlen($insert_query));
                $db->query("UPDATE <PRE>".$table." SET ".$insert_query." ".$whereclause);
        }
        
        
        

        //This takes the currency codes and figures out how to format the money amount given.  This will use the default
        //currency code set in the pay PayPal settings in THT if you don't specify the currency code.  It will try to format
        //the currency via the PayPal class settings and the arrays of HTML codes below.  Failing that, it will simply print
        //the money amount given with the code next to it.
        //
        //$noamount - If you don't want the amount returned, but just want the symbol or currency code, set this to 1.
        public function money($amount, $code = "", $noamount = 0, $local = "en-US"){
        global $db;

        if(empty($code)){
                $code = $db->config("currency");
        }

        $currency_symbols1 = array("GBP" => "&#163;",
                                   "USD" => "&#36;",
                                   "AUD" => "&#36;",
                                   "CAD" => "&#36;",
                                   "EUR" => "&#x80;",
                                   "JPY" => "&#165;",
                                   "NZD" => "&#36;",
                                   "CHF" => "&#8355;",
                                   "HKD" => "&#36;",
                                   "SGD" => "&#36;",
                                   "MXN" => "&#36;");

        $currency_symbols2 = array("SEK" => " kr",
                                   "DKK" => " kr",
                                   "PLN" => " z&#322;",
                                   "NOK" => " kr",
                                   "HUF" => " Ft",
                                   "CZK" => " K&#269;",
                                   "ILS" => " &#8362;");

         if(substr_count($amount, "-")){
                $amount = str_replace("-", "", $amount);
                $negative = "&#8722;";
         }

         $amount = $this->addzeros($amount);

         if($currency_symbols1[$code]){

                $amount = $currency_symbols1[$code].$amount;

                if($noamount){
                        return $currency_symbols1[$code];
                }else{
                        return $negative.$amount;
                }

         }elseif($currency_symbols2[$code]){

                $amount = $amount.$currency_symbols2[$code];

                if($noamount){
                        return $currency_symbols2[$code];
                }else{
                        return $negative.$amount;
                }

         }else{

                if($noamount){
                        return $code;
                }else{
                        return $negative.$amount." ".$code;
                }
         }


        }
        
        
        //This simply takes in the ID of a user and spits out their username.  You can set the table to "staff" if you need a staff member's info.
        public function uname($id, $table = "users"){
         $user_data = $this->tdata($table, "id", $id);
         return $user_data['user'];
        }




        //This is the oposite of the uname() above.  It takes in the username and gives you the ID.  You can set the table to "staff" if you need a staff member's info.
        public function userid($uname, $table = "users"){
         $user_data = $this->tdata($table, "user", $uname);
         return $user_data['id'];
        }
        
        
        
        
        
        //This takes in the user ID and spits out the packageinfo for both the package and user_packs tables.
        //If $userid is not set, then $SESSION['cuser'] will be used.  This also spits out the additional attributes as well.
        //This pulls from the user_packs_bak table if it can't find the pack in the existing users table.
        //
        //$package_data['user_packs']  - Array of info from the user_packs table
        //$package_data['packages']    - Array of info from the packages table
        //$package_data['additional']  - Array of info from the packages table's additional attributes.
        //$package_data['uadditional'] - Array of info from the user_packs/user_packs_bak table's additional attributes.  (Ex. Forum info)
        //$package_data['removed']     - If the function pulled from the backup table, then this will be set to 1 to show that the user no longer exists.
        public function uidtopack($userid = ""){
                global $db, $type;

                if(!$userid){
                $userid = $_SESSION['cuser'];
                }

                $userpackage = $this->tdata("user_packs", "userid", $userid);
                if(empty($userpackage)){
                        $backup = "_bak";
                        $userpackage = $this->tdata("user_packs_bak", "userid", $userid);
                        $package_data['removed'] = 1;
                }else{
                        $package_data['removed'] = 0;
                }

                $packageinfo = $this->tdata("packages", "id", $userpackage['pid']);
                $additional =  $type->additional($userpackage['pid']);
                $uadditional = $this->userAdditional($userpackage['id'], $backup);

                $package_data['user_packs'] = $userpackage;
                $package_data['packages'] =   $packageinfo;
                $package_data['additional'] = $additional;
                $package_data['uadditional'] = $uadditional;

                return $package_data;
        }
        
        
        
        //This takes in the message and a username or user ID and logs it to the database.
        //
        //$usertable - You can set this to users or staff.  It defaults to users in userid() and uname(), so it does that as well here.
        //$time - If you don't want to use time() for when it was logged, set this to the appropriate time.
        public function thtlog($message, $uid = "", $user = "", $usertable = "users", $time = ""){

        if(!$time){
                $time = time();
        }

        //Helps to keep things synced.
        if(!$uid){
                $uid = $this->userid($user, $usertable);
        }else{
                $user = $this->uname($uid, $usertable);
        }

        $array = array("uid" => $uid,
                       "loguser" => $user,
                       "logtime" => $time,
                       "message" => $message);

        $this->insert("logs", $array);

        }
        
        
        //This takes in a number and makes sure its in the following format 1.00.  This is very useful for money.
        //
        //1   Will turn into 1.00
        //1.0 will become 1.00
        //1.9 will become 1.90
        public function addzeros($number){
                if(!substr_count($number, ".")){
                                $number = $number.".00";
                }else{
                        $number_check = explode(".", $number);
                        if(strlen($number_check[1]) == 1){
                                $number = $number."0";
                        }
                }
                return $number;
        }
        
        
        //As modules that place templates in their proper directory can have their path get pretty lengthy, this
        //function will cut down on repetitive typing for you.  It'll also send it through the $style->replacevar()
        //as well so you can just call this function instead.  Its probably best to take this a step further and create
        //a wrapper function for it so you can just call your wrapper function without your module's directory name.
        //If you have your template files in subdirectories in your module's directory, you can add the extra directories
        //to the template's filename.
        //
        //$mod_dir - Ex. "module_dir" for the directory /includes/tpl/modules/module_dir
        //$tpl_file - In the directory above, where is your .tpl file located and what is it called?  (Ex. my_template.tpl or /subdirectory/my_template.tpl)
        //$array - The array you wish to have used as the replacements in the $style->replacevar() function.
        public function tpl($mod_dir, $tpl_file, $array = 0){
                global $style;

                $tpl_file = "tpl/modules/".$mod_dir."/".$tpl_file;
                return $style->replaceVar($tpl_file, $array);
        }
        
        
        //This takes in a mixed variable and checks if its an integer.  A string will be seen as an integer if its numeric.
        //I have no idea why PHP's is_int() doesn't work on string value integers as an option since an integer in math is any whole
        //number (usually) greater than 0, but in programming its a type.  (Ex. mixed, integer, boolean, etc.)  This makes it anoying
        //to not get a valid response from string based integers, so I wrote my own function for it.
        //
        //Speedy thing goes in -> speedy thing comes out.  lol  Just kidding.  =P
        //$mixed_var -> If its anything greater than zero and isn't a decimal, then it will return true.  Otherwise it returns false.
        public function isint($mixed_var){
                if(is_numeric($mixed_var) && !substr_count($mixed_var, ".") && !substr_count($mixed_var, "-")){
                        return true;
                }else{
                        return false;
                }
        }
        
        


        //This function works exactly like $main->dropdown(), but it handles multiple selection boxes as well.
        //
        //$name - The name of the dropdown box
        //$values - The array of values to add to the select box.
        //$default - Either a string stating the default or an array of the default values.  (Ex. array("selected1", selected2");)
        //$top - Should the function create the <select></select> code?  Press 1 for yes, or 0 for no.  =)
        //$class - If you pressed 1 for yes ($top = 1) then this will add the class name for the select box.
        //$custom - If you need to add more info to the <select> tag then you can use this value.
        public function dropDown($name, $values, $default = 0, $top = 1, $class = "", $custom = "") {
                if($top) {
                        $html .= '<select name="'.$name.'" id="'.$name.'" class="'.$class.'" '.$custom.'>';
                }
                if($values) {
                        foreach($values as $key => $value) {
                                $html .= '<option value="'.$value[1].'"';
                                if(is_array($default)){
                                 if(in_array($value[1], $default)){
                                  $html .= 'selected="selected"';
                                 }
                                }else{
                                if($default == $value[1]) {
                                $html .= 'selected="selected"';
                                }
                                }
                                $html .= '>'.$value[0].'</option>';
                        }
                }
                if($top) {
                        $html .= '</select>';
                }
                return $html;
        }
        
        
        
        //I love how this function is just a letter.  lol  This function will take in a number and an optional prefix and spit out
        //the properly formated verbiage.  For example, s(1, " post"); would output 1 post.  s(2, " post"); would spit out 2 posts.
        //s(1); spits out nothing and s(2); spits out s.
        //
        //$number - The number you wish to quantitize.
        //$prefix - Whatever you want to be between the number and the s if there is one.  Be sure to include the space in the prefix
        //          if you want a space after the number.
        public function s($number, $prefix = ""){

                if($number != 1){
                        $s = "s";
                }
                
                if($prefix){
                        return $number.$prefix.$s;
                }else{
                        return $s;
                }
        }
        
        
        
        //This is like $type->userAdditional(), but with the ability to grope the user_packs_bak table if you tell it to.
        //It also will not return an error if it fails, but it will be empty.  This helps to get rid of error messages everywhere
        //and you just need to check if its empty or not.
        //
        //$id - The ID of the user_packs or user_packs_bak table.
        //$backup - If you want to check the backup table instead, set this to _bak.
        public function userAdditional($id, $backup = "") {
                $data = $this->tdata("user_packs".$backup, "id", $id);
                $content = explode(",", $data['additional']);
                foreach($content as $key => $value) {
                        $inside = explode("=", $value);
                        $values[$inside[0]] = $inside[1];
                }
                return $values;
        }
        
        
        

}

?>
