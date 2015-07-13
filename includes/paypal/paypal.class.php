<?php
/*******************************************************************************
 *                      PHP Paypal IPN Integration Class
 *******************************************************************************
 *      Author:     Micah Carrick
 *      Email:      email@micahcarrick.com
 *      Website:    http://www.micahcarrick.com
 *
 *      File:       paypal.class.php
 *      Version:    1.00
 *      Copyright:  (c) 2005 - Micah Carrick 
 *                  You are free to use, distribute, and modify this software 
 *                  under the terms of the GNU General Public License.  See the
 *                  included license.txt file.
 *      
 *******************************************************************************
 *  VERION HISTORY:
 *  
 *      v1.0.0 [04.16.2005] - Initial Version
 *
 *******************************************************************************
 *  DESCRIPTION:
 *
 *      This file provides a neat and simple method to interface with paypal and
 *      The paypal Instant Payment Notification (IPN) interface.  This file is
 *      NOT intended to make the paypal integration "plug 'n' play". It still
 *      requires the developer (that should be you) to understand the paypal
 *      process and know the variables you want/need to pass to paypal to
 *      achieve what you want.  
 *
 *      This class handles the submission of an order to paypal aswell as the
 *      processing an Instant Payment Notification.
 *  
 *      This code is based on that of the php-toolkit from paypal.  I've taken
 *      the basic principals and put it in to a class so that it is a little
 *      easier--at least for me--to use.  The php-toolkit can be downloaded from
 *      http://sourceforge.net/projects/paypal.
 *      
 *      To submit an order to paypal, have your order form POST to a file with:
 *
 *          $p = new paypal_class;
 *          $p->add_field('business', 'somebody@domain.com');
 *          $p->add_field('first_name', $_POST['first_name']);
 *          ... (add all your fields in the same manor)
 *          $p->submit_paypal_post();
 *
 *      To process an IPN, have your IPN processing file contain:
 *
 *          $p = new paypal_class;
 *          if ($p->validate_ipn()) {
 *          ... (IPN is verified.  Details are in the ipn_data() array)
 *          }
 *
 *
 *      In case you are new to paypal, here is some information to help you:
 *
 *      1. Download and read the Merchant User Manual and Integration Guide from
 *         http://www.paypal.com/en_US/pdf/integration_guide.pdf.  This gives 
 *         you all the information you need including the fields you can pass to
 *         paypal (using add_field() with this class) aswell as all the fields
 *         that are returned in an IPN post (stored in the ipn_data() array in
 *         this class).  It also diagrams the entire transaction process.
 *
 *      2. Create a "sandbox" account for a buyer and a seller.  This is just
 *         a test account(s) that allow you to test your site from both the 
 *         seller and buyer perspective.  The instructions for this is available
 *         at https://developer.paypal.com/ as well as a great forum where you
 *         can ask all your paypal integration questions.  Make sure you follow
 *         all the directions in setting up a sandbox test environment, including
 *         the addition of fake bank accounts and credit cards.
 * 
 *******************************************************************************
*/

class paypal_class {
    
   var $last_error;                 // holds the last error encountered
   
   var $ipn_log;                    // bool: log IPN results to text file?
   var $ipn_log_file;               // filename of the IPN log
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   
   var $fields = array();           // array holds the fields to submit to paypal

   
   function paypal_class() {
       
      // initialization constructor.  Called when class is created.

      $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
      
      $this->last_error = '';
      
      $this->ipn_log_file = 'ipn_log.txt';
      $this->ipn_log = true;
      $this->ipn_log_type = "db";  //file or db
      $this->ipn_response = '';
      
      // populate $fields array with a few default values.  See the paypal
      // documentation for a list of fields and their data types. These defaul
      // values can be overwritten by the calling script.

      $this->add_field('rm','2');           // Return method = POST
      $this->add_field('cmd','_xclick'); 
      
   }
   
   function add_field($field, $value) {
      
      // adds a key=>value pair to the fields array, which is what will be 
      // sent to paypal as POST variables.  If the value is already in the 
      // array, it will be overwritten.
      
      $this->fields["$field"] = $value;
   }

   function submit_paypal_post() {

   //MODDED BY JONNY, fixed by Na'ven.  ;)
      
   ?>
   <form action='<?PHP echo $this->paypal_url; ?>' method='post' name='frm'>
   <?php
   foreach ($this->fields as $a => $b) {
   echo "<input type='hidden' name='".$a."' value='".$b."'>";
   }
   ?>
   </form>
   <script language="JavaScript">
   document.frm.submit();
   </script>
   <?PHP
   exit;
   }
   
   function validate_ipn() {

      // parse the paypal URL
      $url_parsed=parse_url($this->paypal_url);

      // generate the post string from the _POST vars aswell as load the
      // _POST vars into an arry so we can play with them from the calling
      // script.
      $post_string = '';    
      foreach ($_POST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
         $post_string .= $field.'='.urlencode($value).'&'; 
      }
      $post_string.="cmd=_notify-validate"; // append ipn command

      // open the connection to paypal
      $fp = fsockopen("ssl://".$url_parsed[host],"443",$err_num,$err_str,30);
      if(!$fp) {
          
         // could not open the connection.  If loggin is on, the error message
         // will be in the log.
         $this->last_error = "fsockopen error no. $err_num: $err_str";
         $this->log_ipn_results(false);       
         return false;
         
      } else {
         // Post the data back to paypal
         fputs($fp, "POST /cgi-bin/webscr HTTP/1.0\r\n");
         fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
         fputs($fp, "Content-Length: ".strlen($post_string)."\r\n\r\n");
         fputs($fp, $post_string . "\r\n\r\n");

         // loop through the response from the server and append to variable
         while(!feof($fp)) { 
            $this->ipn_response .= fgets($fp, 1024); 
         } 

         fclose($fp); // close connection

      }
      
      if (eregi("VERIFIED",$this->ipn_response)) {
  
         // Valid IPN transaction.
         $this->log_ipn_results(true);
         return true;       
         
      } else {
  
         // Invalid IPN transaction.  Check the log for details.
         $this->last_error = 'IPN Validation Failed.';
         $this->log_ipn_results(false);   
         return false;
         
      }
      
   }
   
   function log_ipn_results($success) {
   global $db;
       
      if (!$this->ipn_log) return;  // is logging turned off?
      
      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - '; 
      
      // Success or failure being logged?
      if($success){
      $text .= "SUCCESS!<br>";
      }else{
      $text .= 'FAIL: '.$this->last_error."<br>";
      }
      
      // Log the POST variables
      $text .= "IPN POST Vars from PayPal:<br><br>";
      $text .= "<table width = '672' cellpaddin = '0' cellspacing = '0' border = '1' bordercolor = '#000000' style='border-collapse: collapse'>";
      foreach ($this->ipn_data as $key=>$value) {
      if($color = ''){
      $color = '';
      }else{
      $color = " bgcolor = '#888888'";
      }
         $text .= "<tr><td width = '30%'".$color."><b>$key</b></td><td>$value</td></tr>";
      }
      $text .= "</table>";
 
      //This section prevents paypal's response headers from pulling the page out of wack.  - Silly, I know, but hey, I'm a perfectionist.  What can I say?
      //This won't split the html responses, though.  This way we an still show the page properly that comes up if something goes wrong.
      if(substr_count($paypal_response, "<") == "0"){
       $paypal_response = explode("\n", $this->ipn_response);
       $i=0;
        foreach($paypal_response as $paypal_response_key => $paypal_response_val){
         if(strlen($paypal_response_val) > 100){
          $paypal_response_val = wordwrap($paypal_response_val, 100, "<br>", true);
          $paypal_response[$i] = $paypal_response_val;
          break;
         }
         $i++;
        }
       $paypal_response = implode("<br><br>", $paypal_response);
      }else{
       $paypal_response = $this->ipn_response;
      }
      
      // Log the response from the paypal server
      $text .= "<br><br>IPN Response from PayPal Server:<br>".$paypal_response;

      // Write to log
      if($this->ipn_log_type == "file"){
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text . "\n\n");
      fclose($fp);  // close file
      }else{
      $db->query("INSERT INTO <PRE>logs SET uid = 's', loguser = 'PayPal', logtime = '".time()."', message = '".addslashes($text)."'");
      }
   }

   function dump_fields() {
 
      // Used for debugging, this function will output all the field/value pairs
      // that are currently defined in the instance of the class using the
      // add_field() function.
      
      echo "<h3>paypal_class->dump_fields() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>"; 
      
      ksort($this->fields);
      foreach ($this->fields as $key => $value) {
         echo "<tr><td>$key</td><td>".urldecode($value)."&nbsp;</td></tr>";
      }
 
      echo "</table><br>"; 
   }
}         


 
