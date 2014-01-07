<?php

#繁體简体萬國碼万国码
#################################################
#	Class    : Email Send & Recive Class
#	Author   : LYK
#	Date	 : 2009-07-13
#	Time	 : 17:36:00
#	Function : Send Mail IMAP Recive
#################################################

class Email
{
    private $Socket = 0;
    private $Connected = false;
    private $Log = array();
    private $Default_Mail_From = 'root@localhost.com';
    private $Mail_From = '';
    private $Mail_Reply = '';
    private $Mail_To = array();
    private $Mail_Bcc = array();
    private $Mail_Cc = array();
    private $Mail_Header = array();
    private $Mail_Subject = '';
    private $Mail_Content = array();
    private $Mail_Message_ID = "";
    private $Mail_Attachment = array();
    private $Mail_Priority = 3;
    private $EOL = "\n";
    private $XMailer = "PHP";
    private $Error = '';

    function __construct($Mail_Server = null, $Mail_Port = 25, $Timeout = 15)
    {
        global $_SERVER;
        if (!empty($Mail_Server) && !empty($Mail_Port))
            return $this->Email_Connect($Mail_Server, $Mail_Port, $Timeout);
    }

    public function __destruct()
    {
        $this->Email_Disconnect();
    }

    private function Log_Respone($Call)
    {
        if ($this->Connected) {
            $IsEnd = false;
            $Count = 0;
            $Line_Log = array();
            if (!empty($Call))
                $Line_Log['Call'] = $Call;
            $Line_Log['Data'] = array();
            while (!$IsEnd) {
                $Respone = fgets($this->Socket, 4095);
                flush();
                $status = socket_get_status($this->Socket);
                if (strpos($Respone, "\0") || $Respone === false)
                    $IsEnd = true;
                $Respone = trim($Respone);
                if (!empty($Respone)) {
                    $Code = (int)substr($Respone, 0, 3);
                    $Data = substr($Respone, 4);
                }
                if ($Code > 0) {
                    $SubLog = array();
                    $SubLog['Code'] = $Code;
                    $SubLog['Msg'] = $Data;
                    $Line_Log['Data'][] = $SubLog;
                }
            }
            $this->Log[] = $Line_Log;
        }
    }

    public function Email_Disconnect()
    {
        if ($this->Connected) {
            @fwrite($this->Socket, "QUIT" . $this->EOL);
            $this->Log_Respone("QUIT");
            usleep(300000);
            @fclose($this->Socket);
            $this->Connected = (boolean)false;
        } else {
            return false;
        }
    }

    public function Email_Connect($Mail_Server, $Mail_Port = 25, $Timeout = 5)
    {
        if ($this->Connected)
            $this->__destruct();
        $ErrNo = 0;
        $this->Socket = @fsockopen($Mail_Server, $Mail_Port, $ErrNo, $this->Error, $Timeout);
        stream_set_timeout($this->Socket, 0, 300000);
        $this->Log_Respone("CONNECT");
        if (empty($this->Socket)) {
            $this->Connected = (boolean)false;
            return false;
        } else {
            $this->Connected = (boolean)true;

            #Say hello to server for confirm connection successful.
            @fwrite($this->Socket, "EHLO {$Mail_Server}" . $this->EOL);
            $this->Log_Respone("EHLO");
            return true;
        }
    }

    public function Email_Wait_Respone($Wait)
    {
        if ($this->Connected) {
            stream_set_blocking($this->Socket, ($Wait === (boolean)true) ? 1 : 0);
        } else {
            return false;
        }
    }

    public function EMail_Login($Email_User, $Email_Password)
    {
        if ($this->Connected === (boolean)true) {
            @fwrite($this->Socket, "AUTH LOGIN" . $this->EOL);
            $this->Log_Respone("AUTH LOGIN");

            //send the username
            @fwrite($this->Socket, base64_encode($Email_User) . $this->EOL);
            $this->Log_Respone("AUTH USER");

            //send the password
            @fwrite($this->Socket, base64_encode($Email_Password) . $this->EOL);
            $this->Log_Respone("AUTH PASS");
        }
    }

    public function EMail_Addr_Check($Email_Addr)
    {
        if (@preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",
            $Email_Addr)) {
            return true;
        } else {
            return false;
        }
    }

    public function EMail_Header($HName, $Content)
    {
        $this->EMail_Header[] = "{$HName} : {$Content}{$this->EOL}";
    }

    public function Email_Message_ID($IDContent)
    {
        $this->Mail_Message_ID = (!empty($IDContent) ? $IDContent : $this->
            Mail_Message_ID);
    }

    public function EMail_From($Email_Addr, $Email_Name = null)
    {
        if ($this->Connected === (boolean)true) {
            if ($this->EMail_Addr_Check($Email_Addr) && empty($this->Mail_From)) {
                @fwrite($this->Socket, "MAIL FROM: {$Email_Addr}" . $this->EOL);
                $this->Log_Respone("MAIL FROM");
                $this->Mail_From = (!empty($Email_Name) ? "\"{$Email_Name}\" <{$Email_Addr}>" :
                    $Email_Addr);
                return true;
            } else {
                return false;
            }
        }
    }

    public function Email_Reply($Email_Addr, $Email_Name = null)
    {
        if ($this->Connected === (boolean)true) {
            if ($this->EMail_Addr_Check($Email_Addr) && empty($this->Mail_Reply))
                $this->Mail_Reply = (!empty($Email_Name) ? "\"{$Email_Name}\" <{$Email_Addr}>" :
                    $Email_Addr);
        }
    }

    public function EMail_To($Email_Addr, $Email_Name = null)
    {
        if ($this->Connected == (boolean)true) {
            if ($this->EMail_Addr_Check($Email_Addr)) {
                @fwrite($this->Socket, "RCPT TO: {$Email_Addr}" . $this->EOL);
                $this->Log_Respone("RCPT TO");
                $this->Mail_To[] = (!empty($Email_Name) ? "\"{$Email_Name}\" <{$Email_Addr}>" :
                    $Email_Addr);
                return true;
            } else {
                return false;
            }
        }
    }

    public function EMail_Cc($Email_Addr, $Email_Name = null)
    {
        if ($this->Connected === (boolean)true) {
            if ($this->EMail_Addr_Check($Email_Addr)) {
                @fwrite($this->Socket, "RCPT TO: {$Email_Addr}" . $this->EOL);
                $this->Log_Respone("RCPT TO");
                $this->Mail_Cc[] = (!empty($Email_Name) ? "\"{$Email_Name}\" <{$Email_Addr}>" :
                    $Email_Addr);
                return true;
            } else {
                return false;
            }
        }
    }

    public function EMail_Bcc($Email_Addr, $Email_Name = null)
    {
        if ($this->Connected === (boolean)true) {
            if ($this->EMail_Addr_Check($Email_Addr)) {
                @fwrite($this->Socket, "RCPT TO: {$Email_Addr}" . $this->EOL);
                $this->Log_Respone("RCPT TO");
                $this->Mail_Bcc[] = (!empty($Email_Name) ? "\"{$Email_Name}\" <{$Email_Addr}>" :
                    $Email_Addr);
                return true;
            } else {
                return false;
            }
        }
    }

    public function EMail_Subjct($Str_Value)
    {
        if ($this->Connected === (boolean)true)
            $this->Mail_Subject = $Str_Value;
    }

    public function EMail_Body_Text($Str_Value)
    {
        if ($this->Connected === (boolean)true)
            $this->Mail_Content['Text'] = $Str_Value;
    }

    public function EMail_Body_HTML($Str_Value)
    {
        if ($this->Connected === (boolean)true)
            $this->Mail_Content['HTML'] = $Str_Value;
    }

    public function EMail_Attachment($FName, $SName = '')
    {
        if ($this->Connected === (boolean)true) {
            if (file_exists($FName)) {
                if (!empty($SName)) {
                    $FBName = $SName;
                } else {
                    $FBName = @basename($FName);
                }
                $Email_Attachment = array(
                    'Name' => $FBName,
                    'MIME' => mime_content_type($FName),
                    'Content' => chunk_split(base64_encode(file_get_contents($FName)), 76, $this->
                        EOL));
                $this->Mail_Attachment[] = $Email_Attachment;
            } else {
                $this->Log[] = "file no found : {$FName}";
            }
        } else {
            $this->Log[] = "SMTP no connected";
        }
    }

    public function EMail_Send()
    {
        if ($this->Connected === (boolean)true) {
            #Tell Server Next message is the email contain.
            @fwrite($this->Socket, "DATA" . $this->EOL);
            $this->Log_Respone("DATA");

            #Setup Mail Boundary
            $Mix_Boundary = @sha1(md5($this->Mail_Subject . "-MIXED-" . microtime(true)));
            $Text_Boundary = @sha1(md5($this->Mail_Subject . "-TEXT-" . microtime(true)));

            #Setup Intial Email Header
            $this->EMail_Header("MIME-Version", "1.0");
            if (empty($this->Mail_From))
                $this->Mail_From = $this->Default_Mail_From;
            $this->EMail_Header("From", $this->Mail_From);
            if (!empty($this->Mail_Reply)) {
                $this->EMail_Header("Reply-to", $this->Mail_Reply);
                $this->EMail_Header("Return-Path", $this->Mail_Reply);
            }
            if (count($this->Mail_To) === 0 && count($this->Mail_Cc) === 0 && count($this->
                Mail_Bcc) === 0)
                return false;
            if (count($this->Mail_To) > 0)
                $this->EMail_Header("To", implode(", ", $this->Mail_To));
            if (count($this->Mail_Cc) > 0)
                $this->EMail_Header("Cc", implode(", ", $this->Mail_Cc));
            if (count($this->Mail_Bcc) > 0)
                $this->EMail_Header("Bcc", implode(", ", $this->Mail_Bcc));
            $this->EMail_Header("Date", Date("r"));
            $this->EMail_Header("Subject", "=?UTF-8?B?" . base64_encode($this->Mail_Subject) .
                "?=");
            if ($this->Mail_Priority != 3) {
                $this->EMail_Header("X-Priority", $this->Mail_Priority);
                if ($this->Priority >= 4) {
                    $this->EMail_Header("Priority", "Low");
                    $this->EMail_Header("Importance", "low");
                } elseif ($this->Priority >= 3) {
                    $this->EMail_Header("Priority", "Medium");
                    $this->EMail_Header("Importance", "normal");
                } else {
                    $this->EMail_Header("Priority", "Urgent");
                    $this->EMail_Header("Importance", "high");
                }
            }
            $this->EMail_Header("Message-ID", $this->Mail_Message_ID);

            #Ensure text and html body both have content
            if (empty($this->Mail_Content['Text']) && !empty($this->Mail_Content['HTML'])) {
                $this->Mail_Content['Text'] = strip_tags($this->Mail_Content['HTML']);
            } elseif (!empty($this->Mail_Content['Text']) && empty($this->Mail_Content['HTML'])) {
                $this->Mail_Content['HTML'] = "<pre>{$this->Mail_Content['Text']}</pre>";
            }

            #Start Body Message
            if (count($this->Mail_Attachment) > 0) {
                $this->EMail_Header("Content-Type", "multipart/mixed; boundary=\"{$Mix_Boundary}\"");
                $this->Mail_Content['Body'] .= "--{$Mix_Boundary}{$this->EOL}";
                $this->Mail_Content['Body'] .= "Content-Type: multipart/alternative; boundary=\"{$Text_Boundary}\"{$this->EOL}{$this->EOL}";
            } else {
                $this->EMail_Header("Content-Type", "multipart/alternative; boundary=\"{$Text_Boundary}\"");
            }

            #Start the body content
            if (!empty($this->Mail_Content['Text'])) {
                $this->Mail_Content['Body'] .= "--{$Text_Boundary}{$this->EOL}Content-Type: text/plain; charset=UTF-8;{$this->EOL}";
                $this->Mail_Content['Body'] .= "Content-Transfer-Encoding: quoted-printable;{$this->EOL}{$this->EOL}";
                $this->Mail_Content['Body'] .= $this->QP_Encode($this->Mail_Content['Text'], true) . $this->
                    EOL;
                #$this->Mail_Content['Body'] .= "Content-Transfer-Encoding: base64;{$this->EOL}{$this->EOL}";
                #$this->Mail_Content['Body'] .= chunk_split(base64_encode($this->Mail_Content['Text']), 76, $this->EOL) . $this->EOL;
            }
            if (!empty($this->Mail_Content['HTML'])) {
                $this->Mail_Content['Body'] .= "--{$Text_Boundary}{$this->EOL}Content-Type: text/html; charset=UTF-8;{$this->EOL}";
                $this->Mail_Content['Body'] .= "Content-Transfer-Encoding: quoted-printable;{$this->EOL}{$this->EOL}";
                $this->Mail_Content['Body'] .= $this->QP_Encode($this->Mail_Content['HTML'], true) . $this->
                    EOL;
                #$this->Mail_Content['Body'] .= "Content-Transfer-Encoding: base64;{$this->EOL}{$this->EOL}";
                #$this->Mail_Content['Body'] .= chunk_split(base64_encode($this->Mail_Content['HTML']), 76, $this->EOL) . $this->EOL;
            }

            $this->Mail_Content['Body'] .= "--{$Text_Boundary}--{$this->EOL}";

            #Start the body attachment
            if (count($this->Mail_Attachment) > 0) {
                $Count = (int)0;
                foreach ($this->Mail_Attachment as $Email_Attachment) {
                    $Count++;
                    $this->Mail_Content['Body'] .= "--{$Mix_Boundary}{$this->EOL}";
                    $this->Mail_Content['Body'] .= "Content-Type: {$Email_Attachment['MIME']}; name=\"{$Email_Attachment['Name']}\"{$this->EOL}";
                    $this->Mail_Content['Body'] .= "Content-Disposition: attachment; filename=\"{$Email_Attachment['Name']}\"{$this->EOL}";
                    $this->Mail_Content['Body'] .= "Content-Transfer-Encoding: base64{$this->EOL}{$this->EOL}";
                    $this->Mail_Content['Body'] .= "{$Email_Attachment['Content']}{$this->EOL}{$this->EOL}";
                }
            }
            if (count($this->Mail_Attachment) > 0)
                $this->Mail_Content['Body'] .= "--{$Mix_Boundary}--{$this->EOL}";

            #Prepare Email Content
            $this->Mail_Content['Data'] = '';
            #Prepare Mail Header
            foreach ($this->EMail_Header as $Mail_Header)
                $this->Mail_Content['Data'] .= "{$Mail_Header}";
            $this->Mail_Content['Data'] .= "{$this->EOL}{$this->Mail_Content['Body']}.{$this->EOL}";

            //Send Email Contain to Server
            stream_set_timeout($this->Socket, 5);
            @fwrite($this->Socket, $this->Mail_Content['Data']);
            stream_set_timeout($this->Socket, 0, 300000);
            $this->Log_Respone("SEND");
        }
    }

    public function QP_Encode($sText, $bEmulate_imap_8bit = true)
    {
        $aLines = explode(chr(13) . chr(10), $sText);
        for ($i = 0; $i < count($aLines); $i++) {
            $sLine = &$aLines[$i];
            if (strlen($sLine) === 0)
                continue;
            $sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';
            if ($bEmulate_imap_8bit)
                $sRegExp = '/[^\x20\x21-\x3C\x3E-\x7E]/e';
            $sReplmt = 'sprintf( "=%02X", ord ( "$0" ) ) ;';
            $sLine = preg_replace($sRegExp, $sReplmt, $sLine);
            $iLength = strlen($sLine);
            $iLastChar = ord($sLine{$iLength - 1});
            if (!($bEmulate_imap_8bit && ($i == count($aLines) - 1)))
                if (($iLastChar == 0x09) || ($iLastChar == 0x20)) {
                    $sLine{$iLength - 1} = '=';
                    $sLine .= ($iLastChar == 0x09) ? '09' : '20';
                }
            if ($bEmulate_imap_8bit) {
                $sLine = str_replace(' =0D', '=20=0D', $sLine);
            }
            preg_match_all('/.{1,73}([^=]{0,2})?/', $sLine, $aMatch);
            $sLine = implode('=' . chr(13) . chr(10), $aMatch[0]); // add soft crlf's
        }
        return implode(chr(13) . chr(10), $aLines);
    }

    public function Email_Content()
    {
        return $this->Mail_Content;
    }

    public function Email_Log()
    {
        return $this->Log;
    }
}

?>