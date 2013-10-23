<?php

namespace Components\Payments\Webservice;

use Bazalt\Auth\Model\User;
use \Bazalt\Rest\Response,
    \Bazalt\Session,
    \Bazalt\Data as Data;

use Components\Menu\Model\Element;
use Components\Payments\Model\Account;
use Components\Payments\Model\Transaction;

/**
 * @uri /payments/transaction
 */
class TransactionResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     * @return \Bazalt\Rest\Response
     */
    public function getForm()
    {
        if (!isset($_GET['amount']) || !($amount = (double)$_GET['amount'])) {
            return new Response(Response::BADREQUEST, ['amount' => 'Invalid value']);
        }
        $user = \Bazalt\Auth::getUser();
        if ($user->isGuest()) {
            return new Response(Response::FORBIDDEN, ['user' => 'Access denied']);
        }

        $settings = [
            'liqpay_id' => 'i1387024747',
            'liqpay_sign' => 'z04Jll43yiWubp0BzlYtAlfv07Y1F58IRiu3D08cxP1'
        ];
        $result_url = 'http://cherchelafam.ua2.biz/api/rest.php/liqpay';
        $server_url = 'http://cherchelafam.ua2.biz/api/rest.php/liqpay';

        $account = Account::getDefault($user);
        $transaction = Transaction::beginTransaction($account, Transaction::TYPE_UP, $amount, 'LiqPay');

        $orderId = $transaction->id;
        $price = $amount;
        $paymentCurrency = 'UAH';
        $desc = 'Test';
        $payway = 'card';

        $xml = '<request>
				<version>1.2</version>
				<merchant_id>'.$settings['liqpay_id'].'</merchant_id>
				<result_url>'.$result_url.'</result_url>
				<server_url>'.$server_url.'</server_url>
				<order_id>'.$orderId.'</order_id>
				<amount>'.$price.'</amount>
				<default_phone></default_phone>
				<currency>'.$paymentCurrency.'</currency>
				<description>'.$desc.'</description>
				<pay_way>'.$payway.'</pay_way>
 				</request>';
        $xml_encoded = base64_encode($xml);

        $merc_sign = $settings['liqpay_sign'];
        $sign = base64_encode(sha1($merc_sign.$xml.$merc_sign, 1));

        $result = '<form action="https://www.liqpay.com/?do=clickNbuy" method="POST">' .
                    '<input type="text" value="' . $_GET['amount'] . '" />' .
                    '<input type="hidden" name="operation_xml" value="' . $xml_encoded . '"/>' .
                    '<input type="hidden" name="signature" value="' . $sign . '"/>' .
                  '</form>';

        return new Response(200, $result);
    }

    /**
     * @method POST
     * @json
     * @return \Tonic\Response
     */
    public function checkTransaction()
    {
        $xml = $_POST['operation_xml'];
        $merc_sig =  'z04Jll43yiWubp0BzlYtAlfv07Y1F58IRiu3D08cxP1';

        $xml_decoded=base64_decode($xml);
        $sign = base64_encode(sha1($merc_sig. $xml_decoded .$merc_sig,1));
        if ($sign == $_POST['signature']) {
            $data = simplexml_load_string($xml_decoded);
            $order_id = $data->order_id;
            $amount = $data->amount;
            list($time, $id) = explode('_', $order_id);
            $user = User::getById($id);

            $sum = (double)$user->setting('account');
            $sum += (double)$amount;

            $user->setting('account', $sum);

            header('Location: /profile/liqpay/success?sum=' . $sum);
            exit;
        }
        header('Location: /profile/liqpay/failed');
    }

}
