<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Cryptography Class (Let's learn cryptography!  lol)
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();
    
}

class crypto{

    public function passhash($password, $salt = 0){

        if(!$salt){

            $salt = self::salt();
            
        }

        $pbkdf2_hash = self::hashit("Whirlpool", $password, $salt, "5000", "128");
        $pass_hash   = hash("Whirlpool", $pbkdf2_hash);
        return $pass_hash;
        
    }

    public function salt(){

        $td   = mcrypt_module_open(MCRYPT_RIJNDAEL_256, "", MCRYPT_MODE_CBC, "");
        $iv   = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
        $hash = hash("Whirlpool", $iv);
        return $hash;
        
    }

    public function hashit($algorithm, $password, $salt, $count, $key_length){

        $algorithm = strtolower($algorithm);
        if(!in_array($algorithm, hash_algos(), true)){

            die('PBKDF2 ERROR: Invalid hash algorithm.');
            
        }

        if($count <= 0 || $key_length <= 0){

            die('PBKDF2 ERROR: Invalid parameters.');
            
        }

        // number of blocks = ceil(key length / hash length)
        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = $key_length / $hash_length;
        if($key_length % $hash_length != 0)
            $block_count += 1;
        
        $output = "";
        for($i = 1; $i <= $block_count; $i++){

            $output .= self::pbkdf2_f($password, $salt, $count, $i, $algorithm, $hash_length);
            
        }

        return substr($output, 0, $key_length);
        
    }

    public function pbkdf2_f($password, $salt, $count, $i, $algorithm, $hash_length){

        //$i encoded as 4 bytes, big endian.
        $last   = $salt.chr(($i >> 24) % 256).chr(($i >> 16) % 256).chr(($i >> 8) % 256).chr($i % 256);
        $xorsum = "";
        for($r = 0; $r < $count; $r++){

            $u    = hash_hmac($algorithm, $last, $password, true);
            $last = $u;
            if(empty($xorsum))
                $xorsum = $u;
            else{

                for($c = 0; $c < $hash_length; $c++){

                    $xorsum[$c] = chr(ord(substr($xorsum, $c, 1)) ^ ord(substr($u, $c, 1)));
                    
                }

            }

        }

        return $xorsum;
        
    }

}

?>