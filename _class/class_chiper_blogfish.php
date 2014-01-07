<?php

class Chiper
{
    private $Hash_Key;
    private $Hash_Len;
    private $Out_Type;
    private $Salt = 'a3997a51b58e6de40b334fe663761df92ad60ac4'; #sha1(md5('LS_FRAMEWORK'));
    private $Out_Tank;

    public function __construct($Key, $Out_Type = 3)
    {
        $this->Out_Tank = array();
        $this->Out_Tank['HEXDEC'] = (int)0;
        $this->Out_Tank['BASE64'] = (int)1;
        $this->Out_Tank['URLENC'] = (int)2;
        $this->Out_Tank['RAWB64'] = (int)3;
        $this->Out_Tank['ORIGIN'] = (int)4;
        $this->Out_Type = (int)$Out_Type;
        $this->Hash_Key = $this->_Hash($Key);
        $this->Hash_Len = strlen($this->Hash_Key);
    }

    public function Encrypt($String)
    {
        $IV = $this->_Generate_IV();
        $Out = '';
        for ($i = 0; $i < $this->Hash_Len; $i++)
            $Out .= chr(ord($IV[$i]) ^ ord($this->Hash_Key[$i]));
        $Key = $IV;
        $i = 0;

        while ($i < strlen($String))
        {
            if (($i != 0) and ($i % $this->Hash_Len == 0))
                $Key = $this->_Hash($Key . substr($String, $i - $this->Hash_Len, $this->
                    Hash_Len));
            $Out .= chr(ord($Key[$i % $this->Hash_Len]) ^ ord($String[$i]));
            $i++;
        }
        switch ($this->Out_Type)
        {
            case $this->Out_Tank['HEXDEC']:
                return $this->Str2Hex($Out);
            case $this->Out_Tank['BASE64']:
                return base64_encode($Out);
            case $this->Out_Tank['URLENC']:
                return rawurlencode($Out);
            case $this->Out_Tank['RAWB64'];
                return rawurlencode(base64_encode($Out));
            case $this->Out_Tank['ORIGIN']:
                return $Out;
        }
    }

    public function Decrypt($String)
    {
        switch ($this->Out_Type)
        {
            case $this->Out_Tank['HEXDEC']:
                $String = $this->Hex2Str($String);
                break;
            case $this->Out_Tank['BASE64']:
                $String = base64_decode($String);
                break;
            case $this->Out_Tank['URLENC']:
                $String = rawurldecode($String);
                break;
            case $this->Out_Tank['RAWB64'];
                $String = base64_decode(rawurldecode($String));
                break;
        }
        if ($this->Hex_Out)
            $String = $this->Hex2Str($String);
        $TMP_IV = substr($String, 0, $this->Hash_Len);
        $String = substr($String, $this->Hash_Len, strlen($String) - $this->Hash_Len);
        $IV = $Out = '';
        for ($i = 0; $i < $this->Hash_Len; $i++)
            $IV .= chr(ord($TMP_IV[$i]) ^ ord($this->Hash_Key[$i]));
        $Key = $IV;
        $i = 0;
        while ($i < strlen($String))
        {
            if (($i != 0) and ($i % $this->Hash_Len == 0))
                $Key = $this->_Hash($Key . substr($Out, $i - $this->Hash_Len, $this->Hash_Len));
            $Out .= chr(ord($Key[$i % $this->Hash_Len]) ^ ord($String[$i]));
            $i++;
        }
        return $Out;
    }

    public function EncryptVar($Variable)
    {
        $Serialize = serialize($Variable);
        return $this->Encrypt($Serialize);
    }

    public function DecryptVar($String)
    {
        $Serialize = $this->Decrypt($String);
        return unserialize($Serialize);
    }

    private function _Hash($String)
    {
        if (function_exists('sha1'))
        {
            $Hash = sha1($String);
        } else
        {
            $Hash = md5($String);
        }
        $Out = '';
        for ($i = 0; $i < strlen($Hash); $i += 2)
        {
            $Out .= $this->_Hex2Chr($Hash[$i] . $Hash[$i + 1]);
        }
        return $Out;
    }

    private function _Generate_IV()
    {
        srand((double)microtime() * 1000000);
        $IV = $this->Salt;
        $IV .= rand(0, getrandmax());
        $IV .= serialize($GLOBALS);
        return $this->_Hash($IV);
    }

    private function _Hex2Chr($Num)
    {
        return chr(hexdec($Num));
    }

    private function Hex2Str($Hex)
    {
        return @pack("H*", $Hex);
    }

    private function Str2Hex($Str)
    {
        return strtoupper(join("", unpack("H*", $Str)));
    }
}

?>