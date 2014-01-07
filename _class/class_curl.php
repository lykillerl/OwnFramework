<?php

# Create by LYK on 2012-01-09 @ 12:12PM #繁體简体萬國碼万国码
class CURL
{
    private $Socket;
    private $HTTP = array();
    private $EOL = PHP_EOL;
    private $Error;
    private $NC;
    private $ID;
    private $Log;
    private $Auto_Header;
    private $Security_Send = false;

    public function __construct($Timeout = 10)
    {
        $this->ID = 1;
        $this->NC = str_pad($this->ID, 8, 0, STR_PAD_LEFT);
        $this->CURL_Create_Socket($Timeout);
    }

    public function __destruct()
    {
        $this->CURL_Disconnect();
    }

    public function CURL_OUPUT_HEADER()
    {
        if (array_key_exists("RECEIVE", $this->HTTP) && array_key_exists("HEADERS", $this->HTTP['RECEIVE']))
        {
            foreach ($this->HTTP['RECEIVE']['HEADERS'] as $Header_line)
            {
                header($Header_line);
            }
        }
    }

    public function CURL_AUTO_HEADER($Auto = false)
    {
        $this->Auto_Header = $Auto;
    }

    public function CURL_URL($Url, $Port = 80)
    {
        $Url = (strpos($Url, "://") === false ? "http://{$Url}" : $Url);
        if (is_resource($this->Socket))
        {
            $Component = parse_url($Url);
            if (empty($Component['port']) && !empty($Port))
                $Component['port'] = (int)$Port;
            if ($Component['scheme'] == "https")
            {
                if (empty($Component['port']))
                    $Component['port'] = (int)443;
            } else
            {
                if (empty($Component['port']))
                    $Component['port'] = (int)80;
            }
            #$Component['host'] = gethostbyname($Component['host']);
            if (empty($Component['scheme']))
                $Component['scheme'] = 'http';
            if (empty($Component['path']))
                $Component['path'] = "/";
            if (!array_key_exists('user', $Component))
                $Component['user'] = null;
            if (!array_key_exists('pass', $Component))
                $Component['pass'] = null;
            $Component['port'] = (int)$Component['port'];
            if (@socket_connect($this->Socket, $Component['host'], $Component['port']))
            {
                if (!array_key_exists('URL', $this->HTTP))
                    $this->HTTP['URL'] = array();
                $this->HTTP['URL']['FULL'] = $Url;
                $this->HTTP['URL']['HOST'] = $Component['host'];
                $this->HTTP['URL']['PORT'] = $Component['port'];
                $this->HTTP['URL']['PATH'] = $Component['path'];
                $this->HTTP['URL']['USER'] = (!array_key_exists('USER', $this->HTTP['URL']) ? $Component['user'] :
                    $this->HTTP['URL']['USER']);
                $this->HTTP['URL']['PASS'] = (!array_key_exists('PASS', $this->HTTP['URL']) ? $Component['pass'] :
                    $this->HTTP['URL']['PASS']);
                $this->HTTP['URL']['METHOD'] = "GET";
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    public function CURL_SET_TIMEOUT($Timeout)
    {
        if (is_resource($this->Socket))
        {
            $this->HTTP['TIME_OUT'] = (int)$Timeout;
            socket_setopt($this->Socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => $this->HTTP['TIME_OUT'],
                    "usec" => 0));
            $this->Socket_Log_Error();
            socket_setopt($this->Socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $this->HTTP['TIME_OUT'],
                    "usec" => 0));
            $this->Socket_Log_Error();
            return true;
        } else
        {
            return false;
        }
    }

    public function CURL_HTTP_AUTH($Method = null, $User = null, $Password = null)
    {
        if (!array_key_exists('AUTH', $this->HTTP))
            $this->HTTP['AUTH'] = array();
        $this->HTTP['AUTH']['METHOD'] = strtoupper($Method);
        if (!empty($User))
            $this->HTTP['AUTH']['USER'] = $User;
        if (!empty($Password))
            $this->HTTP['AUTH']['PASS'] = $Password;
    }

    public function CURL_HTTP_POST($Data)
    {
        if (is_array($Data))
        {
            $this->HTTP['URL']['METHOD'] = "POST";
            foreach ($Data as $Key => $Value)
                $this->HTTP['POST_DATA'][rawurlencode($Key)] = rawurlencode($Value);
        }
    }

    public function CURL_HTTP_GET($Data)
    {
        if (is_array($Data))
            foreach ($Data as $Key => $Value)
                $this->HTTP['GET_DATA'][rawurlencode($Key)] .= rawurlencode($Value);
    }

    public function CURL_FILE_UPLOAD($Name, $FName)
    {
        if (file_exists($FName))
        {
            if (!array_key_exists('FILE', $this->HTTP))
                $this->HTTP['FILE'] = array();
            $File = array();
            $File['NAME'] = $Name;
            $File['CONTENT'] = file_get_contents($FName);
            $File['TYPE'] = $this->Get_Mime_Type($FName);
            $File['FILE'] = basename($FName);
            $File['SIZE'] = strlen($File['CONTENT']);
            if (empty($File['CONTENT']))
            {
                $this->New_Log("Upload file is empty.");
                return false;
            } else
            {
                $this->HTTP['FILE'][] = $File;
            }
            return true;
        } else
        {
            $this->New_Log("Upload file is not found.");
            return false;
        }
    }

    public function CURL_HEADER($Header_Name, $Header_Data)
    {
        if (!array_key_exists('HTTP_SEND_HEADER', $this->HTTP))
            $this->HTTP['HTTP_SEND_HEADER'] = '';
        $this->HTTP['HTTP_SEND_HEADER'] .= "{$Header_Name}: {$Header_Data}{$this->EOL}";
    }

    public function CURL_REQUEST()
    {
        if (!empty($this->HTTP['URL']) && is_resource($this->Socket))
        {
            $Http_Get_Data = '';
            if (array_key_exists('GET_DATA', $this->HTTP) && !empty($this->HTTP['GET_DATA']))
            {
                foreach ($this->HTTP['GET_DATA'] as $Key => $Value)
                    $Http_Get_Data .= (!empty($Http_Get_Data) ? "&" : "") . "{$Key}={$Value}";
                $Http_Get_Data = "?{$Http_Get_Data}";
            }
            $this->HTTP['HTTP_SEND_START'] = "{$this->HTTP['URL']['METHOD']} {$this->HTTP['URL']['PATH']}{$Http_Get_Data} HTTP/1.1{$this->EOL}";
            $this->CURL_HEADER("User-Agent", $this->HTTP['USER_AGENT']);
            $this->CURL_HEADER("HOST", $this->HTTP['URL']['HOST']);
            $this->CURL_HEADER("Date", date("D, d M Y H:i:s e"));
            $this->CURL_HEADER("ETag", md5(microtime(true)));
            $this->CURL_HEADER("Vary", "*");
            #$this->CURL_HEADER("Accept-Encoding", "gzip, deflate");
            $this->CURL_HEADER("Accept-Language", "zh-TW,zh;q=0.8,en-US;q=0.6,en;q=0.4");
            $this->CURL_HEADER("Accept-Charset", "UTF-8,*;q=0.5");
            $this->CURL_HEADER("Cache-Control", "max-age=0");
            if (array_key_exists('AUTH', $this->HTTP) && $this->HTTP['AUTH']['METHOD'] == 'BASIC')
                $this->CURL_HEADER("Authorization", "Basic " . base64_encode("{$this->HTTP['URL']['USER']}:{$this->HTTP['URL']['PASS']}"));
            $this->HTTP['HTTP_SEND_BODY'] = '';
            if (!array_key_exists('AUTH', $this->HTTP) || stripos($this->HTTP['HTTP_SEND_HEADER'],
                "Authorization") !== false)
            {
                if (array_key_exists('FILE', $this->HTTP))
                {
                    $this->HTTP['BOUNDARY'] = "HTTP_BOUNDARY_" . strtoupper(md5("HTTP_BOUNDARY" .
                        microtime(true)));
                    $this->CURL_HEADER("Content-Type", "multipart/form-data; boundary={$this->HTTP['BOUNDARY']}");
                    foreach ($this->HTTP['FILE'] as $File)
                    {
                        $this->HTTP['HTTP_SEND_BODY'] .= "--{$this->HTTP['BOUNDARY']}{$this->EOL}";
                        $this->HTTP['HTTP_SEND_BODY'] .= "Content-Disposition: form-data; name=\"{$File['NAME']}\"; filename=\"{$File['FILE']}\"{$this->EOL}";
                        $this->HTTP['HTTP_SEND_BODY'] .= "Content-Length: {$File['SIZE']}{$this->EOL}";
                        $this->HTTP['HTTP_SEND_BODY'] .= "Content-Type: {$File['TYPE']}{$this->EOL}";
                        $this->HTTP['HTTP_SEND_BODY'] .= "{$this->EOL}{$File['CONTENT']}{$this->EOL}";
                    }
                    if (array_key_exists('POST_DATA', $this->HTTP))
                    {
                        foreach ($this->HTTP['POST_DATA'] as $PName => $PValue)
                        {
                            $this->HTTP['HTTP_SEND_BODY'] .= "--{$this->HTTP['BOUNDARY']}{$this->EOL}";
                            $this->HTTP['HTTP_SEND_BODY'] .= "Content-Disposition: form-data; name=\"{$PName}\"{$this->EOL}";
                            $this->HTTP['HTTP_SEND_BODY'] .= "{$this->EOL}{$PValue}{$this->EOL}";
                        }
                    }
                    $this->HTTP['HTTP_SEND_BODY'] .= "--{$this->HTTP['BOUNDARY']}--";
                    $this->HTTP['HTTP_SEND_BODY'] .= $this->EOL;
                } elseif ($this->HTTP['URL']['METHOD'] == "POST")
                {
                    if (array_key_exists('POST_DATA', $this->HTTP))
                    {
                        $Http_Post_Data = '';
                        foreach ($this->HTTP['POST_DATA'] as $PName => $PValue)
                        {
                            $Http_Post_Data .= (!empty($Http_Post_Data) ? "&" : "") . "{$PName}={$PValue}";
                        }
                        $this->CURL_HEADER("Content-Type",
                            "application/x-www-form-urlencoded; charset=utf-8");
                        $this->HTTP['HTTP_SEND_BODY'] .= $Http_Post_Data;
                    }
                }
            }
            if (!empty($this->HTTP['HTTP_SEND_BODY']))
                $this->CURL_HEADER("Content-Length", strlen($this->HTTP['HTTP_SEND_BODY']));
            $this->CURL_HEADER("Connection", "Keep-Alive");
            if (!$this->Send_Data("{$this->HTTP['HTTP_SEND_START']}{$this->HTTP['HTTP_SEND_HEADER']}{$this->EOL}{$this->HTTP['HTTP_SEND_BODY']}"))
            {
                $this->New_Log("HTTP 401 Unauthorized");
                return false;
            }
            $this->CURL_Receive();
            $this->HTTP['HEADER']['CODE'] = (int)$this->HTTP['HEADER']['CODE'];
            switch ($this->HTTP['HEADER']['CODE'])
            {

                case 401:
                    if (stripos($this->HTTP['HTTP_SEND_HEADER'], "Authorization") !== false)
                        return false;
                    if (!array_key_exists('AUTH', $this->HTTP))
                        $this->HTTP['AUTH'] = array();
                    list($this->HTTP['AUTH']['METHOD'], $this->HTTP['AUTH']['DATA']) = explode(" ",
                        $this->HTTP['HEADER']['WWW-AUTHENTICATE'], 2);
                    $this->ValueReader();
                    switch (strtoupper($this->HTTP['AUTH']['METHOD']))
                    {

                        case "DIGEST":
                            $Authorization = $this->CURL_Auth_Digest_Respone(true);
                            $this->CURL_RECONNECT();
                            $this->HTTP['HTTP_SEND_HEADER'] = '';
                            $this->CURL_HEADER("Authorization", $Authorization);
                            return $this->CURL_REQUEST();
                            break;

                        case "BASIC":
                            $this->New_Log("HTTP 401 Unauthorized");
                            return false;
                    }
                    break;

                case 301:
                case 302:
                    if ($this->ID > 50)
                        return false;
                    $Location = $this->HTTP['HEADER']['LOCATION'];
                    $this->CURL_Reset();
                    $this->CURL_URL($Location);
                    return $this->CURL_REQUEST();
                    break;
            }
            if ($this->Auto_Header === true)
                $this->CURL_Ouput_Header();
            if (array_key_exists('CONTENT-ENCODING', $this->HTTP['HEADER']) && array_key_exists('CONTENT-LENGTH',
                $this->HTTP['HEADER']))
            {
                switch (strtoupper($this->HTTP['HEADER']['CONTENT-ENCODING']))
                {
                    case "GZIP":
                        return @gzinflate(substr($this->HTTP['RECEIVE']['BODY'], 10, -8));
                    case "DEFLATE":
                        return @gzinflate($this->HTTP['RECEIVE']['BODY'], strlen($this->HTTP['RECEIVE']['BODY']));
                    default:
                        $this->New_Log("Unsupport content charset encoding.");
                        return false;
                }
            } else
            {
                return $this->HTTP['RECEIVE']['BODY'];
            }
        } else
        {
            $this->New_Log("No socket or no url.");
            return false;
        }
    }

    public function CURL_HTTP_CODE()
    {
        if (array_key_exists('HEADER', $this->HTTP) && array_key_exists('CODE', $this->HTTP['HEADER']))
        {
            return (int)$this->HTTP['HEADER']['CODE'];
        } else
        {
            $this->New_Log("No receive header to retrive code");
            return false;
        }
    }

    public function CURL_HTTP_DESP()
    {
        if (array_key_exists('HEADER', $this->HTTP) && array_key_exists('DESP', $this->HTTP['HEADER']))
        {
            return (int)$this->HTTP['HEADER']['DESP'];
        } else
        {
            $this->New_Log("No receive header to retrive description");
            return false;
        }
    }

    public function CURL_RESET($Reset_All = true)
    {
        $Reset_All = (int)$Reset_All;
        $Timeout = (int)$this->HTTP['TIME_OUT'];
        $this->New_Log("CURL Reset");
        $this->CURL_Disconnect();
        $this->HTTP = array();
        switch ($Reset_All)
        {
            case false:
                $this->HTTP = array();
                $this->ID++;
                break;

            default:
                $this->HTTP = array();
                $this->ID = (int)1;
                break;
        }
        $this->CURL_Create_Socket($Timeout);
        $this->Error = '';
        $this->NC = str_pad($this->ID, 8, 0, STR_PAD_LEFT);
    }

    public function CURL_HTTP()
    {
        return $this->HTTP;
    }

    public function CURL_ERROR()
    {
        return $this->Error;
    }

    public function CURL_RECONNECT()
    {
        $Timeout = (int)$this->HTTP['TIME_OUT'];
        $this->New_Log("CURL Reconnect");
        $this->CURL_Disconnect();
        usleep(10000);
        $this->CURL_Create_Socket($Timeout);
        $this->CURL_URL($this->HTTP['URL']['FULL'], $this->HTTP['URL']['PORT']);
        $this->HTTP['HTTP_SEND_START'] = '';
        $this->HTTP['HTTP_SEND_HEADER'] = '';
        $this->HTTP['HTTP_SEND_BODY'] = '';
    }

    private function CURL_Create_Socket($Timeout)
    {
        $this->Socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->Socket_Log_Error();
        $this->HTTP['USER_AGENT'] =
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.1) Gecko/20060111 Firefox/1.5.0.1";
        $this->HTTP['TIME_OUT'] = (int)$Timeout;
        socket_set_block($this->Socket);
        $this->Socket_Log_Error();
        socket_setopt($this->Socket, SOL_SOCKET, SO_REUSEADDR, true);
        $this->Socket_Log_Error();
        socket_setopt($this->Socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 0, "usec" => 1000));
        socket_setopt($this->Socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 1000));
        $this->Socket_Log_Error();
    }

    private function CURL_Disconnect()
    {
        if (is_resource($this->Socket))
        {
            socket_close($this->Socket);
            $this->Socket_Log_Error();
            $this->Socket = null;
        }
    }

    private function CURL_Receive()
    {
        if (is_resource($this->Socket))
        {
            $this->HTTP['RECEIVE'] = array(
                'CONTENT' => '',
                'HEADER' => '',
                'BODY' => '');
            $Spliter = "{$this->EOL}{$this->EOL}";
            $this->HTTP['RECEIVE']['CONTENT'] = $this->Read_Data();
            if (!empty($this->HTTP['RECEIVE']['CONTENT']))
            {
                if (strpos($this->HTTP['RECEIVE']['CONTENT'], $Spliter) === false)
                {
                    $this->HTTP['RECEIVE']['HEADER'] = $this->HTTP['RECEIVE']['CONTENT'];
                } else
                {
                    list($this->HTTP['RECEIVE']['HEADER'], $this->HTTP['RECEIVE']['BODY']) = explode($Spliter,
                        $this->HTTP['RECEIVE']['CONTENT'], 2);
                }
                $this->HTTP_Header_Parser();
                if (array_key_exists("TRANSFER-ENCODING", $this->HTTP['HEADER']) && strtolower($this->
                    HTTP['HEADER']['TRANSFER-ENCODING']) === 'chunked')
                    $this->HTTP['RECEIVE']['BODY'] = $this->Chunked_Decode($this->HTTP['RECEIVE']['BODY']);
            }
        }
    }

    private function Chunked_Decode($chunk)
    {
        $pos = 0;
        $len = strlen($chunk);
        $dechunk = null;

        while (($pos < $len) && ($chunkLenHex = substr($chunk, $pos, ($newlineAt = strpos($chunk, "\n",
            $pos + 1)) - $pos)))
        {
            if (!$this->Is_Hex($chunkLenHex))
                return $chunk;
            $pos = $newlineAt + 1;
            $chunkLen = hexdec(rtrim($chunkLenHex, "\r\n"));
            $dechunk .= substr($chunk, $pos, $chunkLen);
            $pos = strpos($chunk, "\n", $pos + $chunkLen) + 1;
        }
        return $dechunk;
    }

    private function Is_Hex($hex)
    {
        // regex is for weenies
        $hex = strtolower(trim(ltrim($hex, "0")));
        if (empty($hex))
            $hex = 0;
        $dec = hexdec($hex);
        return ($hex == dechex($dec));
    }

    private function Read_Data($MTU = 1400)
    {
        if (is_resource($this->Socket))
        {
            $Received = "";
            $Buffer = '';
            while (true)
            {
                @socket_recv($this->Socket, $Buffer, (int)$MTU, MSG_WAITALL);
                $this->Socket_Log_Error();
                if (!empty($Buffer))
                    $Received .= $Buffer;
                else
                    break;
            }
            return trim($Received);
        } else
        {
            $this->New_Log("Socket unable to receive data.");
            return false;
        }
    }

    private function Send_Data($Data)
    {
        if (is_resource($this->Socket))
        {
            $Length = strlen($Data);
            while (true)
            {
                $Sent = @socket_write($this->Socket, $Data, $Length);
                $this->Socket_Log_Error();
                if ($Sent === false)
                    return false;
                if ($Sent < $Length)
                {
                    $Data = substr($Data, $Sent);
                    $Length -= $Sent;
                } else
                {
                    return true;
                }
            }
            return false;
        } else
        {
            $this->New_Log("Socket unable to send data.");
            return false;
        }
    }

    private function Socket_Log_Error()
    {
        if (is_resource($this->Socket))
        {
            $Err_No = socket_last_error($this->Socket);
            if (!empty($Err_No))
                $this->Error .= date("Y-m-d H:i:s") . " {$Err_No} " . socket_strerror($Err_No) . $this->
                    EOL;
        }
    }

    private function CURL_Auth_Digest_Respone($Set_NC = false)
    {
        $Auth_Data = '';
        if (array_key_exists('AUTH', $this->HTTP))
        {
            $this->HTTP['AUTH']['H1_WORD'] = "{$this->HTTP['AUTH']['USER']}:{$this->HTTP['AUTH']['REALM']}:{$this->HTTP['AUTH']['PASS']}";
            $this->HTTP['AUTH']['HA1'] = md5("{$this->HTTP['AUTH']['USER']}:{$this->HTTP['AUTH']['REALM']}:{$this->HTTP['AUTH']['PASS']}");
            if (stripos($this->HTTP['AUTH']['QOP'], "auth-int") !== false)
            {
                $this->HTTP['AUTH']['H2_WORD'] = "{$this->HTTP['URL']['METHOD']}:{$this->HTTP['URL']['PATH']}:" .
                    md5($this->HTTP['RECEIVE']['BODY']);
                $this->HTTP['AUTH']['HA2'] = md5($this->HTTP['AUTH']['H2_WORD']);
            } else
            {
                $this->HTTP['AUTH']['H2_WORD'] = "{$this->HTTP['URL']['METHOD']}:{$this->HTTP['URL']['PATH']}";
                $this->HTTP['AUTH']['HA2'] = md5("{$this->HTTP['URL']['METHOD']}:{$this->HTTP['URL']['PATH']}");
            }
            $this->HTTP['AUTH']['CNONCE'] = substr(md5("{$this->HTTP['AUTH']['HA1']}:{$this->HTTP['AUTH']['USER']}:{$this->HTTP['AUTH']['PASS']}:{$this->HTTP['AUTH']['HA1']}"),
                0, 16);
            $this->HTTP['AUTH']['RESPONE'] = md5("{$this->HTTP['AUTH']['HA1']}:{$this->HTTP['AUTH']['NONCE']}:{$this->NC}:{$this->HTTP['AUTH']['CNONCE']}:{$this->HTTP['AUTH']['QOP']}:{$this->HTTP['AUTH']['HA2']}");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['USER']) ? (!empty($Auth_Data) ? ", " : " ") .
                "username=\"{$this->HTTP['AUTH']['USER']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['REALM']) ? (!empty($Auth_Data) ? ", " : " ") .
                "realm=\"{$this->HTTP['AUTH']['REALM']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['URL']['PATH']) ? (!empty($Auth_Data) ? ", " : " ") .
                "uri=\"{$this->HTTP['URL']['PATH']}\"" : "");

            $Auth_Data .= (!empty($this->NC) && $Set_NC === true ? (!empty($Auth_Data) ? ", " : " ") .
                "nc=\"{$this->NC}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['NONCE']) ? (!empty($Auth_Data) ? ", " : " ") .
                "nonce=\"{$this->HTTP['AUTH']['NONCE']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['RESPONE']) ? (!empty($Auth_Data) ? ", " : " ") .
                "response=\"{$this->HTTP['AUTH']['RESPONE']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['QOP']) ? (!empty($Auth_Data) ? ", " : " ") .
                "qop=\"{$this->HTTP['AUTH']['QOP']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['OPAQUE']) ? (!empty($Auth_Data) ? ", " : " ") .
                "opaque=\"{$this->HTTP['AUTH']['OPAQUE']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['CNONCE']) ? (!empty($Auth_Data) ? ", " : " ") .
                "cnonce=\"{$this->HTTP['AUTH']['CNONCE']}\"" : "");

            $Auth_Data .= (!empty($this->HTTP['AUTH']['ALGORITHM']) ? (!empty($Auth_Data) ? ", " :
                " ") . "algorithm={$this->HTTP['AUTH']['ALGORITHM']}" : "");

            return "Digest{$Auth_Data}";
        } else
        {
            $this->New_Log("Digest doesn't auth data.");
            return false;
        }
    }

    private function ValueReader()
    {
        if (!empty($this->HTTP['AUTH']['DATA']))
        {
            $Auth_Data = explode(",", $this->HTTP['AUTH']['DATA']);
            foreach ($Auth_Data as $Data_Line)
            {
                $Data_Line = trim($Data_Line);
                list($VName, $VData) = explode("=", $Data_Line);
                $VName = strtoupper(trim($VName));
                $this->HTTP['AUTH'][$VName] = trim(str_replace("\"", "", $VData));
            }
        } else
        {
            return false;
        }
    }

    private function HTTP_Header_Parser()
    {
        if (!empty($this->HTTP['RECEIVE']['HEADER']))
        {
            $this->HTTP['RECEIVE']['HEADERS'] = array();
            $this->HTTP['RECEIVE']['HEADERS'] = explode($this->EOL, $this->HTTP['RECEIVE']['HEADER']);
            $this->HTTP['HEADER'] = array();
            foreach ($this->HTTP['RECEIVE']['HEADERS'] as $Header_Line)
            {
                if (strpos($Header_Line, ":") === false)
                {
                    list($this->HTTP['HEADER']['VER'], $this->HTTP['HEADER']['CODE'], $this->HTTP['HEADER']['DESP']) =
                        explode(" ", $Header_Line);
                } else
                {
                    list($ValName, $Data) = explode(":", $Header_Line, 2);
                    $ValName = trim(strtoupper($ValName));
                    $Data = trim($Data);
                    $this->HTTP['HEADER'][$ValName] = $Data;
                }
            }
        } else
        {
            return false;
        }
    }

    private function Get_Mime_Type($FName)
    {

        $Mime_Bank = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv', #images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml', #archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed', #audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            #adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript', #ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            #open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );

        $File_Ext = strtolower(array_pop(explode('.', $FName)));
        if (array_key_exists($File_Ext, $Mime_Bank))
        {
            return $Mime_Bank[$File_Ext];
        } elseif (function_exists('finfo_open'))
        {
            $FInfo = finfo_open(FILEINFO_MIME);
            $MimeType = finfo_file($FInfo, $FName);
            finfo_close($FInfo);
            return $MimeType;
        } else
        {
            return "application/octet-stream";
        }
    }

    private function New_Log($Data)
    {
        $this->Log .= date("Y-m-d H:i:s") . " {$Data}{$this->EOL}";
    }

    public function CURL_LOG()
    {
        return $this->Log;
    }
}

?>