<?php

class EncryptionHelper
{
    private static $encryption_key = 'your-static-encryption-key';
    private $cipher;

    public function __construct($cipher = "aes-128-gcm")
    {
        $this->cipher = $cipher;
    }

    public static function setEncryptionKey($key)
    {
        self::$encryption_key = $key;
    }

    public function encryptMessage($message)
    {
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($message, $this->cipher, self::$encryption_key, $options=0, $iv, $tag);
        return base64_encode($iv.$tag.$ciphertext);
    }

    public function decryptMessage($encrypted_message)
    {
        $c = base64_decode($encrypted_message);
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $iv = substr($c, 0, $ivlen);
        $tag = substr($c, $ivlen, 16);
        $ciphertext = substr($c, $ivlen + 16);
        return openssl_decrypt($ciphertext, $this->cipher, self::$encryption_key, $options=0, $iv, $tag);
    }
}


?>
