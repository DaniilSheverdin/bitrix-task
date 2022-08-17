<<<<<<< HEAD
<?
define('BP_KEY', "MyLYWLBi/siVwashq1fWA6agr/UY00mt");
define('BP_CIPHER', "aes-128-gcm");


$GLOBALS['deccc'] = function($text){
     $result = NULL;
     try{
          $text = base64_decode($text);
          $text = substr($text, 5);
          if(mb_strlen($text) < 3) throw new Exception("err");
          
          $text = substr($text, 0, mb_strlen($text)-3);
          $text = unserialize(base64_decode($text));
          if(empty($text) || count($text) !== 3) throw new Exception("err");
          $tag = base64_decode($text[0]);
          $iv = base64_decode($text[1]);
          $ciphertext = base64_decode($text[2]);
          $text = openssl_decrypt($ciphertext, BP_CIPHER, base64_decode(BP_KEY), 0, $iv, $tag);
          $text = base64_decode($text);

          $text = substr($text, 8);
          $text = substr($text, 0, mb_strlen($text)-5);

          $result = trim($text);
     }catch(Exception $exc){}
     return $result;
};
$GLOBALS['enccc'] = function($text){
     $text = base64_encode(randString(8).$text.randString(5));
     $ivlen = openssl_cipher_iv_length(BP_CIPHER);
     $iv = openssl_random_pseudo_bytes($ivlen);
     $text = openssl_encrypt($text, BP_CIPHER, base64_decode(BP_KEY), 0, $iv, $tag);
     $text = base64_encode(serialize([base64_encode($tag), base64_encode($iv), base64_encode($text)]));
     $text = base64_encode(randString(5).$text.randString(3));
     return $text;
=======
<?
define('BP_KEY', "MyLYWLBi/siVwashq1fWA6agr/UY00mt");
define('BP_CIPHER', "aes-128-gcm");


$GLOBALS['deccc'] = function($text){
     $result = NULL;
     try{
          $text = base64_decode($text);
          $text = substr($text, 5);
          if(mb_strlen($text) < 3) throw new Exception("err");
          
          $text = substr($text, 0, mb_strlen($text)-3);
          $text = unserialize(base64_decode($text));
          if(empty($text) || count($text) !== 3) throw new Exception("err");
          $tag = base64_decode($text[0]);
          $iv = base64_decode($text[1]);
          $ciphertext = base64_decode($text[2]);
          $text = openssl_decrypt($ciphertext, BP_CIPHER, base64_decode(BP_KEY), 0, $iv, $tag);
          $text = base64_decode($text);

          $text = substr($text, 8);
          $text = substr($text, 0, mb_strlen($text)-5);

          $result = trim($text);
     }catch(Exception $exc){}
     return $result;
};
$GLOBALS['enccc'] = function($text){
     $text = base64_encode(randString(8).$text.randString(5));
     $ivlen = openssl_cipher_iv_length(BP_CIPHER);
     $iv = openssl_random_pseudo_bytes($ivlen);
     $text = openssl_encrypt($text, BP_CIPHER, base64_decode(BP_KEY), 0, $iv, $tag);
     $text = base64_encode(serialize([base64_encode($tag), base64_encode($iv), base64_encode($text)]));
     $text = base64_encode(randString(5).$text.randString(3));
     return $text;
>>>>>>> e0a0eba79 (init)
};