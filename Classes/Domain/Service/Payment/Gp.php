<?php
namespace NeosRulez\Shop\GlobalPayments\Domain\Service\Payment;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use NeosRulez\Shop\GlobalPayments\CSignature;

/**
 * Class Gp
 *
 * @Flow\Scope("singleton")
 */
class Gp
{

    /**
     * @param array $payment
     * @param array $args
     * @param string $success_uri
     * @param string $failure_uri
     * @return string
     */
    public function execute(array $payment, array $args, string $success_uri, string $failure_uri):string
    {

        $stringToSign = $payment['args']['merchantnumber'] . '|CREATE_ORDER|' . $args['order_number'] . '|' . ((int) (((float) $args['summary']['total']) * 100)) . '|978|0|' . $success_uri;

        $privateKeyPathAndFileName = constant('FLOW_PATH_ROOT') . $payment['args']['privateKeyFile'];
        $publicKeyPathAndFileName = constant('FLOW_PATH_ROOT') . $payment['args']['publicKeyFile'];
        $password = $payment['args']['password'];

        $signature = new CSignature($privateKeyPathAndFileName, $password, $publicKeyPathAndFileName);
        $digest = $signature->sign($stringToSign);
        $redirectUri = 'https://3dsecure.gpwebpay.com/pgw/order.do?MERCHANTNUMBER=' . $payment['args']['merchantnumber'] . '&ORDERNUMBER=' . $args['order_number'] . '&AMOUNT=' . ((int) (((float) $args['summary']['total']) * 100)) . '&CURRENCY=978&OPERATION=CREATE_ORDER&DEPOSITFLAG=0&URL=' . urlencode($success_uri) . '&DIGEST=' . urlencode($digest);

        return $redirectUri;
    }

}
