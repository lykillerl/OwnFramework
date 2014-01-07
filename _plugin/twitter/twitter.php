<?php #Create By LYK for Twitter REST API 1.1 on 2013 Jun 13 13:20

class Twitter
{
    protected $OAuth;

    public function __construct($CONSUMER_KEY, $CONSUMER_SECRET, $ACCESS_TOKEN, $ACCESS_SECRET){
        $this->OAuth = array(
            'CONSUMER_KEY' => $CONSUMER_KEY,
            'CONSUMER_SECRET' => $CONSUMER_SECRET,
            'ACCESS_TOKEN' => $ACCESS_TOKEN,
            'ACCESS_SECRET' => $ACCESS_SECRET);
    }

    private function UrlBaseStr($baseURI, $Method, $params)
    {
        $r = array();
        ksort($params);
        foreach ($params as $key => $value) {
            $r[] = "{$key}=" . rawurlencode($value);
        }
        return $Method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&',
            $r));
    }

    private function OAuthHeader($OAuth_Data)
    {
        $Result = 'Authorization: OAuth ';
        $Headers = array();
        foreach ($OAuth_Data as $Key => $Value)
            $Headers[] = "$Key=\"" . rawurlencode($Value) . "\"";
        $Result .= implode(', ', $Headers);
        return $Result;
    }

    function Twitt($Url, $Method, $Fields)
    {
        $Fields_Str = '';
        if ((is_array($Fields) && !empty($Fields))) {
            foreach ($Fields as $Key => $Value)
                $Fields_Str .= (!empty($Fields_Str)? "&" : "") . rawurlencode($Key) . "=" . rawurlencode($Value);
            rtrim($Fields_Str, '&');
        }
        $TWAuth = array(
            'oauth_consumer_key' => $this->OAuth['CONSUMER_KEY'],
            'oauth_nonce' => md5(time()),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->OAuth['ACCESS_TOKEN'],
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0');
        $BParam = array_merge($Fields, $TWAuth);
        $this->OAuth['SIGNATURE'] = base64_encode(hash_hmac('sha1', $this->UrlBaseStr($Url, $Method,
            $BParam), rawurlencode($this->OAuth['CONSUMER_SECRET']) . '&' . rawurlencode($this->OAuth['ACCESS_SECRET']), true));
        $TWAuth['oauth_signature'] = $this->OAuth['SIGNATURE'];
        if ($Method === 'GET')
            $Url .= "?{$Fields_Str}";
        $Options = array(
            CURLOPT_HTTPHEADER => array($this->OAuthHeader($TWAuth), 'Expect:'),
            CURLOPT_HEADER => false,
            CURLOPT_URL => $Url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false);
        if ($Method === 'POST' && !empty($Fields_Str)) {
            $Options[CURLOPT_POST] = count($Fields);
            $Options[CURLOPT_POSTFIELDS] = $Fields_Str;
        }
        $CH = curl_init();
        curl_setopt_array($CH, $Options);
        $Result = curl_exec($CH);
        curl_close($CH);
        return $Result;
    }
}

?>