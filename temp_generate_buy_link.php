<?php
$secret = "AgSg9vrf5jGJCcsH*-rMKVUHS5vPw&G7Pspfc?AbPG3FjGwBC&c%DCV6EYsCzs7#";
$paramsToSign = [
    'currency' => 'USD',
    'customer-ext-ref' => 'CUSTOMER-MANUAL',
    'description' => 'Embroidery digitization service for: Custom Design',
    'order-ext-ref' => 'ORDER-MANUAL-150',
    'price' => '150.00',
    'prod' => 'Embroidery Digitize',
    'qty' => '1',
    'return-type' => 'redirect',
    'return-url' => 'https://embroiderydigitize.com/thank-you/',
    'type' => 'digital',
];
ksort($paramsToSign);
$data = '';
foreach ($paramsToSign as $v) {
    $v = mb_convert_encoding($v, 'UTF-8');
    $data .= strlen($v) . $v;
}
$signature = hash_hmac('sha256', $data, $secret);
$query = [
    'merchant' => '255036765830',
    'currency' => 'USD',
    'dynamic' => '1',
    'prod' => 'Embroidery Digitize',
    'price' => '150.00',
    'type' => 'digital',
    'qty' => '1',
    'signature' => $signature,
    'tpl' => 'default',
    'return-url' => 'https://embroiderydigitize.com/thank-you/',
    'return-type' => 'redirect',
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '',
    'order-ext-ref' => 'ORDER-MANUAL-150',
    'customer-ext-ref' => 'CUSTOMER-MANUAL',
    'src' => 'manual',
    'description' => 'Embroidery digitization service for: Custom Design',
    'tangible' => '0',
    'language' => 'en',
    'test' => '1'
];
$url = 'https://secure.2checkout.com/checkout/buy?' . http_build_query($query);
echo "DataToSign: $data\nSignature: $signature\nURL: $url\n";
