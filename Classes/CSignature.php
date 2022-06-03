<?php
namespace NeosRulez\Shop\GlobalPayments;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

class CSignature
{

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $privateKeyPassword;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @param string $privateKey
     * @param string $privateKeyPassword
     * @param string $publicKey
     */
    public function __construct(string $privateKey, string $privateKeyPassword, string $publicKey)
    {
        $this->privateKey = file_get_contents($privateKey);
        $this->privateKeyPassword = $privateKeyPassword;
        $this->publicKey = file_get_contents($publicKey);
    }

    /**
     * @param string $text
     * @return string
     */
    public function sign(string $text): string
    {
        $pkeyid = openssl_get_privatekey($this->privateKey, $this->privateKeyPassword);
        openssl_sign($text, $signature, $pkeyid);
        $signature = base64_encode($signature);
        openssl_free_key($pkeyid);
        return $signature;
    }

    /**
     * @param string $text
     * @param string $signature
     * @return string
     */
    public function verify(string $text, string $signature): string
    {
        $pubkeyid = openssl_get_publickey($this->publicKey);
        $signature = base64_decode($signature);
        $result = openssl_verify($text, $signature, $pubkeyid);
        openssl_free_key($pubkeyid);
        return $result==1;
    }

}
