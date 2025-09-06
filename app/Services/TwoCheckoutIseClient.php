<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TwoCheckoutIseClient
{
    protected string $merchant;
    protected string $secretKey;
    protected string $baseLink;
    protected string $algo;

    public function __construct(?string $merchant = null, ?string $secretKey = null, ?string $baseLink = null, string $algo = 'sha256')
    {
        $this->merchant = $merchant ?: config('services.twocheckout.account_number');
        $this->secretKey = $secretKey ?: config('services.twocheckout.secret_key');
        $this->baseLink = $baseLink ?: 'https://secure.2checkout.com/action/ise.php';
        $this->algo = $algo; // sha256 or sha3-256
    }

    /**
     * Fetch a single order snapshot using ISE filtering by REFNO or REFNOEXT.
     * This uses a narrow date window (today +/- 1 day) to minimize export size.
     *
     * @param array $filters ['REFNO'=>..., 'REFNOEXT'=>...]
     * @return array|null
     */
    public function fetch(array $filters): ?array
    {
        if(!$this->merchant || !$this->secretKey){
            return null;
        }

        $params = [
            'MERCHANT' => $this->merchant,
            'STARTDATE' => now()->subDays(2)->format('Y-m-d'),
            'ENDDATE' => now()->addDay()->format('Y-m-d'),
            'ORDERSTATUS' => 'ALL',
            'REQ_DATE' => now()->utc()->format('YmdHis'),
            'FILTER_FIELD' => '',
            'FILTER_STRING' => '',
            'SIGNATURE_ALG' => $this->algo,
            'EXPORT_FORMAT' => 'XML',
            'EXPORT_TIMEZONE_REGION' => 'UTC',
        ];

        // Map supported filters
        if(isset($filters['REFNO'])){
            $params['FILTER_FIELD'] = 'REFNO';
            $params['FILTER_STRING'] = $filters['REFNO'];
        } elseif(isset($filters['REFNOEXT'])) {
            $params['FILTER_FIELD'] = 'REFNOEXT';
            $params['FILTER_STRING'] = $filters['REFNOEXT'];
        }

        $params['HASH'] = $this->computeHash($params);

        $query = http_build_query($params, '', '&');

        try {
            $ch = curl_init($this->baseLink.'?'.$query);
            curl_setopt($ch, CURLOPT_POSTFIELDS, null);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $verify = config('services.twocheckout.verify_ssl', true);
            $ca = config('services.twocheckout.ca_bundle');
            if(!$verify){
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            } elseif($ca){
                curl_setopt($ch, CURLOPT_CAINFO, $ca);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            }
            $responseData = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if($err = curl_error($ch)){
                Log::warning('ISE curl error', ['error'=>$err]);
            }
            curl_close($ch);
            if($code !== 200){
                Log::warning('ISE non-200', ['code'=>$code,'body'=>$responseData]);
                return null;
            }
            if(stripos($contentType,'xml') === false){
                Log::warning('ISE unexpected content type', ['contentType'=>$contentType]);
                return null;
            }
            $xml = @simplexml_load_string($responseData);
            if(!$xml){
                return null;
            }
            // Convert XML to array (simple flatten for first level children)
            $json = json_decode(json_encode($xml), true);
            return $json;
        } catch(\Throwable $e){
            Log::error('ISE fetch failed', ['e'=>$e->getMessage()]);
            return null;
        }
    }

    protected function computeHash(array $params): string
    {
        $notInHash = [
            'HASH','INCLUDE_DELIVERED_CODES','INCLUDE_FINANCIAL_DETAILS','INCLUDE_EXCHANGE_RATES',
            'INCLUDE_PRICING_OPTIONS','EXPORT_FORMAT','EXPORT_TIMEZONE_REGION','SIGNATURE_ALG'
        ];
        $result = '';
        foreach($params as $key=>$val){
            if(in_array($key,$notInHash)) continue;
            $val = is_array($val) ? $this->arrayExpand($val) : $val;
            $val = stripslashes((string)$val);
            $result .= strlen($val).$val;
        }
        return hash_hmac($this->algo, $result, $this->secretKey);
    }

    protected function arrayExpand(array $array): string
    {
        $out = '';
        foreach($array as $value){
            if(is_array($value)){
                $out .= $this->arrayExpand($value);
            } else {
                $value = stripslashes((string)$value);
                $out .= strlen($value).$value;
            }
        }
        return $out;
    }
}
