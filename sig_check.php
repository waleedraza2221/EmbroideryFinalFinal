<?php
$secret='AgSg9vrf5jGJCcsH*-rMKVUHS5vPw&G7Pspfc?AbPG3FjGwBC&c%DCV6EYsCzs7#';
$currency='USD';
$price='150'; // note: no decimals to mirror test URL
$prod='Embroidery Digitizing';
$qty='1';
$type='digital';
$params=[ 'currency'=>$currency,'price'=>$price,'prod'=>$prod,'qty'=>$qty,'type'=>$type ];
ksort($params);
$data='';
foreach($params as $v){ $v=mb_convert_encoding($v,'UTF-8'); $data.=strlen($v).$v; }
$sig=hash_hmac('sha256',$data,$secret);
echo "Data: $data\nSignature: $sig\n";
