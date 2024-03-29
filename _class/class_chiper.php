<?php
define('CRYPT_RIJNDAEL_MODE_CTR', -1);
define('CRYPT_RIJNDAEL_MODE_ECB', 1);
define('CRYPT_RIJNDAEL_MODE_CBC', 2);
define('CRYPT_RIJNDAEL_MODE_CFB', 3);
define('CRYPT_RIJNDAEL_MODE_OFB', 4);
define('CRYPT_RIJNDAEL_MODE_INTERNAL', 1);
define('CRYPT_RIJNDAEL_MODE_MCRYPT', 2);
define('CRYPT_AES_MODE_CTR', -1);
define('CRYPT_AES_MODE_ECB', 1);
define('CRYPT_AES_MODE_CBC', 2);
define('CRYPT_AES_MODE_CFB', 3);
define('CRYPT_AES_MODE_OFB', 4);
define('CRYPT_AES_MODE_INTERNAL', 1);
define('CRYPT_AES_MODE_MCRYPT', 2);

class Crypt_Rijndael {
    protected  $mode;
    protected  $key = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
    protected  $iv = '';
    protected  $encryptIV = '';
    protected  $decryptIV = '';
    protected  $continuousBuffer = false;
    protected  $padding = true;
    protected  $changed = true;
    protected  $explicit_key_length = false;
    protected  $w;
    protected  $dw;
    protected  $block_size = 16;
    protected  $Nb = 4;
    protected  $key_size = 16;
    protected  $Nk = 4;
    protected  $Nr;
    protected  $c;
    protected  $t0;
    protected  $t1;
    protected  $t2;
    protected  $t3;
    protected  $dt0;
    protected  $dt1;
    protected  $dt2;
    protected  $dt3;
    protected  $paddable = false;
    protected  $enbuffer = array('encrypted' => '', 'xor' => '');
    protected  $debuffer = array('ciphertext' => '');

    protected function Crypt_Rijndael($mode = CRYPT_RIJNDAEL_MODE_CBC)
    {
        switch ($mode) {
            case CRYPT_RIJNDAEL_MODE_ECB:
            case CRYPT_RIJNDAEL_MODE_CBC:
                $this->paddable = true;
                $this->mode = $mode;
                break;
            case CRYPT_RIJNDAEL_MODE_CTR:
            case CRYPT_RIJNDAEL_MODE_CFB:
            case CRYPT_RIJNDAEL_MODE_OFB:
                $this->mode = $mode;
                break;
            default:
                $this->paddable = true;
                $this->mode = CRYPT_RIJNDAEL_MODE_CBC;
        }

        $t3 = &$this->t3;
        $t2 = &$this->t2;
        $t1 = &$this->t1;
        $t0 = &$this->t0;

        $dt3 = &$this->dt3;
        $dt2 = &$this->dt2;
        $dt1 = &$this->dt1;
        $dt0 = &$this->dt0;
        $t3 = array(
            0x6363A5C6, 0x7C7C84F8, 0x777799EE, 0x7B7B8DF6, 0xF2F20DFF, 0x6B6BBDD6, 0x6F6FB1DE, 0xC5C55491,
            0x30305060, 0x01010302, 0x6767A9CE, 0x2B2B7D56, 0xFEFE19E7, 0xD7D762B5, 0xABABE64D, 0x76769AEC,
            0xCACA458F, 0x82829D1F, 0xC9C94089, 0x7D7D87FA, 0xFAFA15EF, 0x5959EBB2, 0x4747C98E, 0xF0F00BFB,
            0xADADEC41, 0xD4D467B3, 0xA2A2FD5F, 0xAFAFEA45, 0x9C9CBF23, 0xA4A4F753, 0x727296E4, 0xC0C05B9B,
            0xB7B7C275, 0xFDFD1CE1, 0x9393AE3D, 0x26266A4C, 0x36365A6C, 0x3F3F417E, 0xF7F702F5, 0xCCCC4F83,
            0x34345C68, 0xA5A5F451, 0xE5E534D1, 0xF1F108F9, 0x717193E2, 0xD8D873AB, 0x31315362, 0x15153F2A,
            0x04040C08, 0xC7C75295, 0x23236546, 0xC3C35E9D, 0x18182830, 0x9696A137, 0x05050F0A, 0x9A9AB52F,
            0x0707090E, 0x12123624, 0x80809B1B, 0xE2E23DDF, 0xEBEB26CD, 0x2727694E, 0xB2B2CD7F, 0x75759FEA,
            0x09091B12, 0x83839E1D, 0x2C2C7458, 0x1A1A2E34, 0x1B1B2D36, 0x6E6EB2DC, 0x5A5AEEB4, 0xA0A0FB5B,
            0x5252F6A4, 0x3B3B4D76, 0xD6D661B7, 0xB3B3CE7D, 0x29297B52, 0xE3E33EDD, 0x2F2F715E, 0x84849713,
            0x5353F5A6, 0xD1D168B9, 0x00000000, 0xEDED2CC1, 0x20206040, 0xFCFC1FE3, 0xB1B1C879, 0x5B5BEDB6,
            0x6A6ABED4, 0xCBCB468D, 0xBEBED967, 0x39394B72, 0x4A4ADE94, 0x4C4CD498, 0x5858E8B0, 0xCFCF4A85,
            0xD0D06BBB, 0xEFEF2AC5, 0xAAAAE54F, 0xFBFB16ED, 0x4343C586, 0x4D4DD79A, 0x33335566, 0x85859411,
            0x4545CF8A, 0xF9F910E9, 0x02020604, 0x7F7F81FE, 0x5050F0A0, 0x3C3C4478, 0x9F9FBA25, 0xA8A8E34B,
            0x5151F3A2, 0xA3A3FE5D, 0x4040C080, 0x8F8F8A05, 0x9292AD3F, 0x9D9DBC21, 0x38384870, 0xF5F504F1,
            0xBCBCDF63, 0xB6B6C177, 0xDADA75AF, 0x21216342, 0x10103020, 0xFFFF1AE5, 0xF3F30EFD, 0xD2D26DBF,
            0xCDCD4C81, 0x0C0C1418, 0x13133526, 0xECEC2FC3, 0x5F5FE1BE, 0x9797A235, 0x4444CC88, 0x1717392E,
            0xC4C45793, 0xA7A7F255, 0x7E7E82FC, 0x3D3D477A, 0x6464ACC8, 0x5D5DE7BA, 0x19192B32, 0x737395E6,
            0x6060A0C0, 0x81819819, 0x4F4FD19E, 0xDCDC7FA3, 0x22226644, 0x2A2A7E54, 0x9090AB3B, 0x8888830B,
            0x4646CA8C, 0xEEEE29C7, 0xB8B8D36B, 0x14143C28, 0xDEDE79A7, 0x5E5EE2BC, 0x0B0B1D16, 0xDBDB76AD,
            0xE0E03BDB, 0x32325664, 0x3A3A4E74, 0x0A0A1E14, 0x4949DB92, 0x06060A0C, 0x24246C48, 0x5C5CE4B8,
            0xC2C25D9F, 0xD3D36EBD, 0xACACEF43, 0x6262A6C4, 0x9191A839, 0x9595A431, 0xE4E437D3, 0x79798BF2,
            0xE7E732D5, 0xC8C8438B, 0x3737596E, 0x6D6DB7DA, 0x8D8D8C01, 0xD5D564B1, 0x4E4ED29C, 0xA9A9E049,
            0x6C6CB4D8, 0x5656FAAC, 0xF4F407F3, 0xEAEA25CF, 0x6565AFCA, 0x7A7A8EF4, 0xAEAEE947, 0x08081810,
            0xBABAD56F, 0x787888F0, 0x25256F4A, 0x2E2E725C, 0x1C1C2438, 0xA6A6F157, 0xB4B4C773, 0xC6C65197,
            0xE8E823CB, 0xDDDD7CA1, 0x74749CE8, 0x1F1F213E, 0x4B4BDD96, 0xBDBDDC61, 0x8B8B860D, 0x8A8A850F,
            0x707090E0, 0x3E3E427C, 0xB5B5C471, 0x6666AACC, 0x4848D890, 0x03030506, 0xF6F601F7, 0x0E0E121C,
            0x6161A3C2, 0x35355F6A, 0x5757F9AE, 0xB9B9D069, 0x86869117, 0xC1C15899, 0x1D1D273A, 0x9E9EB927,
            0xE1E138D9, 0xF8F813EB, 0x9898B32B, 0x11113322, 0x6969BBD2, 0xD9D970A9, 0x8E8E8907, 0x9494A733,
            0x9B9BB62D, 0x1E1E223C, 0x87879215, 0xE9E920C9, 0xCECE4987, 0x5555FFAA, 0x28287850, 0xDFDF7AA5,
            0x8C8C8F03, 0xA1A1F859, 0x89898009, 0x0D0D171A, 0xBFBFDA65, 0xE6E631D7, 0x4242C684, 0x6868B8D0,
            0x4141C382, 0x9999B029, 0x2D2D775A, 0x0F0F111E, 0xB0B0CB7B, 0x5454FCA8, 0xBBBBD66D, 0x16163A2C
        );

        $dt3 = array(
            0xF4A75051, 0x4165537E, 0x17A4C31A, 0x275E963A, 0xAB6BCB3B, 0x9D45F11F, 0xFA58ABAC, 0xE303934B,
            0x30FA5520, 0x766DF6AD, 0xCC769188, 0x024C25F5, 0xE5D7FC4F, 0x2ACBD7C5, 0x35448026, 0x62A38FB5,
            0xB15A49DE, 0xBA1B6725, 0xEA0E9845, 0xFEC0E15D, 0x2F7502C3, 0x4CF01281, 0x4697A38D, 0xD3F9C66B,
            0x8F5FE703, 0x929C9515, 0x6D7AEBBF, 0x5259DA95, 0xBE832DD4, 0x7421D358, 0xE0692949, 0xC9C8448E,
            0xC2896A75, 0x8E7978F4, 0x583E6B99, 0xB971DD27, 0xE14FB6BE, 0x88AD17F0, 0x20AC66C9, 0xCE3AB47D,
            0xDF4A1863, 0x1A3182E5, 0x51336097, 0x537F4562, 0x6477E0B1, 0x6BAE84BB, 0x81A01CFE, 0x082B94F9,
            0x48685870, 0x45FD198F, 0xDE6C8794, 0x7BF8B752, 0x73D323AB, 0x4B02E272, 0x1F8F57E3, 0x55AB2A66,
            0xEB2807B2, 0xB5C2032F, 0xC57B9A86, 0x3708A5D3, 0x2887F230, 0xBFA5B223, 0x036ABA02, 0x16825CED,
            0xCF1C2B8A, 0x79B492A7, 0x07F2F0F3, 0x69E2A14E, 0xDAF4CD65, 0x05BED506, 0x34621FD1, 0xA6FE8AC4,
            0x2E539D34, 0xF355A0A2, 0x8AE13205, 0xF6EB75A4, 0x83EC390B, 0x60EFAA40, 0x719F065E, 0x6E1051BD,
            0x218AF93E, 0xDD063D96, 0x3E05AEDD, 0xE6BD464D, 0x548DB591, 0xC45D0571, 0x06D46F04, 0x5015FF60,
            0x98FB2419, 0xBDE997D6, 0x4043CC89, 0xD99E7767, 0xE842BDB0, 0x898B8807, 0x195B38E7, 0xC8EEDB79,
            0x7C0A47A1, 0x420FE97C, 0x841EC9F8, 0x00000000, 0x80868309, 0x2BED4832, 0x1170AC1E, 0x5A724E6C,
            0x0EFFFBFD, 0x8538560F, 0xAED51E3D, 0x2D392736, 0x0FD9640A, 0x5CA62168, 0x5B54D19B, 0x362E3A24,
            0x0A67B10C, 0x57E70F93, 0xEE96D2B4, 0x9B919E1B, 0xC0C54F80, 0xDC20A261, 0x774B695A, 0x121A161C,
            0x93BA0AE2, 0xA02AE5C0, 0x22E0433C, 0x1B171D12, 0x090D0B0E, 0x8BC7ADF2, 0xB6A8B92D, 0x1EA9C814,
            0xF1198557, 0x75074CAF, 0x99DDBBEE, 0x7F60FDA3, 0x01269FF7, 0x72F5BC5C, 0x663BC544, 0xFB7E345B,
            0x4329768B, 0x23C6DCCB, 0xEDFC68B6, 0xE4F163B8, 0x31DCCAD7, 0x63851042, 0x97224013, 0xC6112084,
            0x4A247D85, 0xBB3DF8D2, 0xF93211AE, 0x29A16DC7, 0x9E2F4B1D, 0xB230F3DC, 0x8652EC0D, 0xC1E3D077,
            0xB3166C2B, 0x70B999A9, 0x9448FA11, 0xE9642247, 0xFC8CC4A8, 0xF03F1AA0, 0x7D2CD856, 0x3390EF22,
            0x494EC787, 0x38D1C1D9, 0xCAA2FE8C, 0xD40B3698, 0xF581CFA6, 0x7ADE28A5, 0xB78E26DA, 0xADBFA43F,
            0x3A9DE42C, 0x78920D50, 0x5FCC9B6A, 0x7E466254, 0x8D13C2F6, 0xD8B8E890, 0x39F75E2E, 0xC3AFF582,
            0x5D80BE9F, 0xD0937C69, 0xD52DA96F, 0x2512B3CF, 0xAC993BC8, 0x187DA710, 0x9C636EE8, 0x3BBB7BDB,
            0x267809CD, 0x5918F46E, 0x9AB701EC, 0x4F9AA883, 0x956E65E6, 0xFFE67EAA, 0xBCCF0821, 0x15E8E6EF,
            0xE79BD9BA, 0x6F36CE4A, 0x9F09D4EA, 0xB07CD629, 0xA4B2AF31, 0x3F23312A, 0xA59430C6, 0xA266C035,
            0x4EBC3774, 0x82CAA6FC, 0x90D0B0E0, 0xA7D81533, 0x04984AF1, 0xECDAF741, 0xCD500E7F, 0x91F62F17,
            0x4DD68D76, 0xEFB04D43, 0xAA4D54CC, 0x9604DFE4, 0xD1B5E39E, 0x6A881B4C, 0x2C1FB8C1, 0x65517F46,
            0x5EEA049D, 0x8C355D01, 0x877473FA, 0x0B412EFB, 0x671D5AB3, 0xDBD25292, 0x105633E9, 0xD647136D,
            0xD7618C9A, 0xA10C7A37, 0xF8148E59, 0x133C89EB, 0xA927EECE, 0x61C935B7, 0x1CE5EDE1, 0x47B13C7A,
            0xD2DF599C, 0xF2733F55, 0x14CE7918, 0xC737BF73, 0xF7CDEA53, 0xFDAA5B5F, 0x3D6F14DF, 0x44DB8678,
            0xAFF381CA, 0x68C43EB9, 0x24342C38, 0xA3405FC2, 0x1DC37216, 0xE2250CBC, 0x3C498B28, 0x0D9541FF,
            0xA8017139, 0x0CB3DE08, 0xB4E49CD8, 0x56C19064, 0xCB84617B, 0x32B670D5, 0x6C5C7448, 0xB85742D0
        );

        for ($i = 0; $i < 256; $i++) {
            $t2[$i <<  8] = (($t3[$i] <<  8) & 0xFFFFFF00) | (($t3[$i] >> 24) & 0x000000FF);
            $t1[$i << 16] = (($t3[$i] << 16) & 0xFFFF0000) | (($t3[$i] >> 16) & 0x0000FFFF);
            $t0[$i << 24] = (($t3[$i] << 24) & 0xFF000000) | (($t3[$i] >>  8) & 0x00FFFFFF);

            $dt2[$i <<  8] = (($this->dt3[$i] <<  8) & 0xFFFFFF00) | (($dt3[$i] >> 24) & 0x000000FF);
            $dt1[$i << 16] = (($this->dt3[$i] << 16) & 0xFFFF0000) | (($dt3[$i] >> 16) & 0x0000FFFF);
            $dt0[$i << 24] = (($this->dt3[$i] << 24) & 0xFF000000) | (($dt3[$i] >>  8) & 0x00FFFFFF);
        }
    }

    protected function setKey($key)
    {
        $this->key = $key;
        $this->changed = true;
    }

    private function setIV($iv)
    {
        $this->encryptIV = $this->decryptIV = $this->iv = str_pad(substr($iv, 0, $this->block_size), $this->block_size, chr(0));
    }

    private function setKeyLength($length)
    {
        $length >>= 5;
        if ($length > 8) {
            $length = 8;
        } else if ($length < 4) {
            $length = 4;
        }
        $this->Nk = $length;
        $this->key_size = $length << 2;

        $this->explicit_key_length = true;
        $this->changed = true;
    }

    private function setBlockLength($length)
    {
        $length >>= 5;
        if ($length > 8) {
            $length = 8;
        } else if ($length < 4) {
            $length = 4;
        }
        $this->Nb = $length;
        $this->block_size = $length << 2;
        $this->changed = true;
    }

    private function _generate_xor($length, &$iv)
    {
        $xor = '';
        $block_size = $this->block_size;
        $num_blocks = floor(($length + ($block_size - 1)) / $block_size);
        for ($i = 0; $i < $num_blocks; $i++) {
            $xor.= $iv;
            for ($j = 4; $j <= $block_size; $j+=4) {
                $temp = substr($iv, -$j, 4);
                switch ($temp) {
                    case "\xFF\xFF\xFF\xFF":
                        $iv = substr_replace($iv, "\x00\x00\x00\x00", -$j, 4);
                        break;
                    case "\x7F\xFF\xFF\xFF":
                        $iv = substr_replace($iv, "\x80\x00\x00\x00", -$j, 4);
                        break 2;
                    default:
                        extract(unpack('Ncount', $temp));
                        $iv = substr_replace($iv, pack('N', $count + 1), -$j, 4);
                        break 2;
                }
            }
        }

        return $xor;
    }

    private function encrypt($plaintext)
    {
        $this->_setup();
        if ($this->paddable)
            $plaintext = $this->_pad($plaintext);
        $block_size = $this->block_size;
        $buffer = &$this->enbuffer;
        $continuousBuffer = $this->continuousBuffer;
        $ciphertext = '';
        switch ($this->mode) {
            case CRYPT_RIJNDAEL_MODE_ECB:
                for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                    $ciphertext.= $this->_encryptBlock(substr($plaintext, $i, $block_size));
                }
                break;
            case CRYPT_RIJNDAEL_MODE_CBC:
                $xor = $this->encryptIV;
                for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                    $block = substr($plaintext, $i, $block_size);
                    $block = $this->_encryptBlock($block ^ $xor);
                    $xor = $block;
                    $ciphertext.= $block;
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                }
                break;
            case CRYPT_RIJNDAEL_MODE_CTR:
                $xor = $this->encryptIV;
                if (!empty($buffer['encrypted'])) {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $block = substr($plaintext, $i, $block_size);
                        $buffer['encrypted'].= $this->_encryptBlock($this->_generate_xor($block_size, $xor));
                        $key = $this->_string_shift($buffer['encrypted'], $block_size);
                        $ciphertext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $block = substr($plaintext, $i, $block_size);
                        $key = $this->_encryptBlock($this->_generate_xor($block_size, $xor));
                        $ciphertext.= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                    if ($start = strlen($plaintext) % $block_size) {
                        $buffer['encrypted'] = substr($key, $start) . $buffer['encrypted'];
                    }
                }
                break;
            case CRYPT_RIJNDAEL_MODE_CFB:
                if (!empty($buffer['xor'])) {
                    $ciphertext = $plaintext ^ $buffer['xor'];
                    $iv = $buffer['encrypted'] . $ciphertext;
                    $start = strlen($ciphertext);
                    $buffer['encrypted'].= $ciphertext;
                    $buffer['xor'] = substr($buffer['xor'], strlen($ciphertext));
                } else {
                    $ciphertext = '';
                    $iv = $this->encryptIV;
                    $start = 0;
                }
                for ($i = $start; $i < strlen($plaintext); $i+=$block_size) {
                    $block = substr($plaintext, $i, $block_size);
                    $xor = $this->_encryptBlock($iv);
                    $iv = $block ^ $xor;
                    if ($continuousBuffer && strlen($iv) != $block_size) {
                        $buffer = array(
                            'encrypted' => $iv,
                            'xor' => substr($xor, strlen($iv))
                        );
                    }
                    $ciphertext.= $iv;
                }
                if ($this->continuousBuffer)
                    $this->encryptIV = $iv;
                break;
            case CRYPT_RIJNDAEL_MODE_OFB:
                $xor = $this->encryptIV;
                if (strlen($buffer)) {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $buffer.= $xor;
                        $key = $this->_string_shift($buffer, $block_size);
                        $ciphertext.= substr($plaintext, $i, $block_size) ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $ciphertext.= substr($plaintext, $i, $block_size) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                    if ($start = strlen($plaintext) % $block_size) {
                         $buffer = substr($key, $start) . $buffer;
                    }
                }
        }
        return $ciphertext;
    }

    private function decrypt($ciphertext)
    {
        $this->_setup();
        if ($this->paddable)
            $ciphertext = str_pad($ciphertext, strlen($ciphertext) + ($this->block_size - strlen($ciphertext) % $this->block_size) % $this->block_size, chr(0));
        $block_size = $this->block_size;
        $buffer = &$this->debuffer;
        $continuousBuffer = $this->continuousBuffer;
        $plaintext = '';
        switch ($this->mode) {
            case CRYPT_RIJNDAEL_MODE_ECB:
                for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                    $plaintext.= $this->_decryptBlock(substr($ciphertext, $i, $block_size));
                }
                break;
            case CRYPT_RIJNDAEL_MODE_CBC:
                $xor = $this->decryptIV;
                for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                    $block = substr($ciphertext, $i, $block_size);
                    $plaintext.= $this->_decryptBlock($block) ^ $xor;
                    $xor = $block;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                }
                break;
            case CRYPT_RIJNDAEL_MODE_CTR:
                $xor = $this->decryptIV;
                if (!empty($buffer['ciphertext'])) {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        $buffer['ciphertext'].= $this->_encryptBlock($this->_generate_xor($block_size, $xor));
                        $key = $this->_string_shift($buffer['ciphertext'], $block_size);
                        $plaintext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        $key = $this->_encryptBlock($this->_generate_xor($block_size, $xor));
                        $plaintext.= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                        $buffer['ciphertext'] = substr($key, $start) . $buffer['encrypted'];
                    }
                }
                break;
            case CRYPT_RIJNDAEL_MODE_CFB:
                if (!empty($buffer['ciphertext'])) {
                    $plaintext = $ciphertext ^ substr($this->decryptIV, strlen($buffer['ciphertext']));
                    $buffer['ciphertext'].= substr($ciphertext, 0, strlen($plaintext));
                    if (strlen($buffer['ciphertext']) == $block_size) {
                        $xor = $this->_encryptBlock($buffer['ciphertext']);
                        $buffer['ciphertext'] = '';
                    }
                    $start = strlen($plaintext);
                    $block = $this->decryptIV;
                } else {
                    $plaintext = '';
                    $xor = $this->_encryptBlock($this->decryptIV);
                    $start = 0;
                }

                for ($i = $start; $i < strlen($ciphertext); $i+=$block_size) {
                    $block = substr($ciphertext, $i, $block_size);
                    $plaintext.= $block ^ $xor;
                    if ($continuousBuffer && strlen($block) != $block_size) {
                        $buffer['ciphertext'].= $block;
                        $block = $xor;
                    } else if (strlen($block) == $block_size) {
                        $xor = $this->_encryptBlock($block);
                    }
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $block;
                }
                break;
            case CRYPT_RIJNDAEL_MODE_OFB:
                $xor = $this->decryptIV;
                if (strlen($buffer)) {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $buffer.= $xor;
                        $key = $this->_string_shift($buffer, $block_size);
                        $plaintext.= substr($ciphertext, $i, $block_size) ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $plaintext.= substr($ciphertext, $i, $block_size) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                         $buffer = substr($key, $start) . $buffer;
                    }
                }
        }
        return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
    }

    private function _encryptBlock($in)
    {
        $state = array();
        $words = unpack('N*word', $in);

        $w = $this->w;
        $t0 = $this->t0;
        $t1 = $this->t1;
        $t2 = $this->t2;
        $t3 = $this->t3;
        $Nb = $this->Nb;
        $Nr = $this->Nr;
        $c = $this->c;

        $i = 0;
        foreach ($words as $word)
            $state[] = $word ^ $w[0][$i++];
        $temp = array();
        for ($round = 1; $round < $Nr; $round++) {
            $i = 0;
            $j = $c[1];
            $k = $c[2];
            $l = $c[3];

            while ($i < $this->Nb) {
                $temp[$i] = $t0[$state[$i] & 0xFF000000] ^
                            $t1[$state[$j] & 0x00FF0000] ^
                            $t2[$state[$k] & 0x0000FF00] ^
                            $t3[$state[$l] & 0x000000FF] ^
                            $w[$round][$i];
                $i++;
                $j = ($j + 1) % $Nb;
                $k = ($k + 1) % $Nb;
                $l = ($l + 1) % $Nb;
            }
            for ($i = 0; $i < $Nb; $i++)
                $state[$i] = $temp[$i];
        }
        for ($i = 0; $i < $Nb; $i++)
            $state[$i] = $this->_subWord($state[$i]);
        $i = 0;
        $j = $c[1];
        $k = $c[2];
        $l = $c[3];
        while ($i < $this->Nb) {
            $temp[$i] = ($state[$i] & 0xFF000000) ^
                        ($state[$j] & 0x00FF0000) ^
                        ($state[$k] & 0x0000FF00) ^
                        ($state[$l] & 0x000000FF) ^
                         $w[$Nr][$i];
            $i++;
            $j = ($j + 1) % $Nb;
            $k = ($k + 1) % $Nb;
            $l = ($l + 1) % $Nb;
        }
        $state = $temp;
        array_unshift($state, 'N*');
        return call_user_func_array('pack', $state);
    }

    private function _decryptBlock($in)
    {
        $state = array();
        $words = unpack('N*word', $in);
        $num_states = count($state);
        $dw = $this->dw;
        $dt0 = $this->dt0;
        $dt1 = $this->dt1;
        $dt2 = $this->dt2;
        $dt3 = $this->dt3;
        $Nb = $this->Nb;
        $Nr = $this->Nr;
        $c = $this->c;
        $i = 0;
        foreach ($words as $word)
            $state[] = $word ^ $dw[$Nr][$i++];
        $temp = array();
        for ($round = $Nr - 1; $round > 0; $round--) {
            $i = 0;
            $j = $Nb - $c[1];
            $k = $Nb - $c[2];
            $l = $Nb - $c[3];
            while ($i < $Nb) {
                $temp[$i] = $dt0[$state[$i] & 0xFF000000] ^
                            $dt1[$state[$j] & 0x00FF0000] ^
                            $dt2[$state[$k] & 0x0000FF00] ^
                            $dt3[$state[$l] & 0x000000FF] ^
                            $dw[$round][$i];
                $i++;
                $j = ($j + 1) % $Nb;
                $k = ($k + 1) % $Nb;
                $l = ($l + 1) % $Nb;
            }

            for ($i = 0; $i < $Nb; $i++) {
                $state[$i] = $temp[$i];
            }
        }
        $i = 0;
        $j = $Nb - $c[1];
        $k = $Nb - $c[2];
        $l = $Nb - $c[3];

        while ($i < $Nb) {
            $temp[$i] = $dw[0][$i] ^
                        $this->_invSubWord(($state[$i] & 0xFF000000) |
                                           ($state[$j] & 0x00FF0000) |
                                           ($state[$k] & 0x0000FF00) |
                                           ($state[$l] & 0x000000FF));
            $i++;
            $j = ($j + 1) % $Nb;
            $k = ($k + 1) % $Nb;
            $l = ($l + 1) % $Nb;
        }
        $state = $temp;
        array_unshift($state, 'N*');
        return call_user_func_array('pack', $state);
    }

    private function _setup()
    {
        static $rcon = array(0,
            0x01000000, 0x02000000, 0x04000000, 0x08000000, 0x10000000,
            0x20000000, 0x40000000, 0x80000000, 0x1B000000, 0x36000000,
            0x6C000000, 0xD8000000, 0xAB000000, 0x4D000000, 0x9A000000,
            0x2F000000, 0x5E000000, 0xBC000000, 0x63000000, 0xC6000000,
            0x97000000, 0x35000000, 0x6A000000, 0xD4000000, 0xB3000000,
            0x7D000000, 0xFA000000, 0xEF000000, 0xC5000000, 0x91000000
        );

        if (!$this->changed) {
            return;
        }

        if (!$this->explicit_key_length) {
            $length = strlen($this->key) >> 2;
            if ($length > 8) {
                $length = 8;
            } else if ($length < 4) {
                $length = 4;
            }
            $this->Nk = $length;
            $this->key_size = $length << 2;
        }

        $this->key = str_pad(substr($this->key, 0, $this->key_size), $this->key_size, chr(0));
        $this->encryptIV = $this->decryptIV = $this->iv = str_pad(substr($this->iv, 0, $this->block_size), $this->block_size, chr(0));
        $this->Nr = max($this->Nk, $this->Nb) + 6;
        switch ($this->Nb) {
            case 4:
            case 5:
            case 6:
                $this->c = array(0, 1, 2, 3);
                break;
            case 7:
                $this->c = array(0, 1, 2, 4);
                break;
            case 8:
                $this->c = array(0, 1, 3, 4);
        }
        $key = $this->key;
        $w = array_values(unpack('N*words', $key));
        $length = $this->Nb * ($this->Nr + 1);
        for ($i = $this->Nk; $i < $length; $i++) {
            $temp = $w[$i - 1];
            if ($i % $this->Nk == 0) {
                $temp = (($temp << 8) & 0xFFFFFF00) | (($temp >> 24) & 0x000000FF); // rotWord
                $temp = $this->_subWord($temp) ^ $rcon[$i / $this->Nk];
            } else if ($this->Nk > 6 && $i % $this->Nk == 4) {
                $temp = $this->_subWord($temp);
            }
            $w[$i] = $w[$i - $this->Nk] ^ $temp;
        }
        $temp = array();
        for ($i = $row = $col = 0; $i < $length; $i++, $col++) {
            if ($col == $this->Nb) {
                if ($row == 0) {
                    $this->dw[0] = $this->w[0];
                } else {
                    $j = 0;
                    while ($j < $this->Nb) {
                        $dw = $this->_subWord($this->w[$row][$j]);
                        $temp[$j] = $this->dt0[$dw & 0xFF000000] ^
                                    $this->dt1[$dw & 0x00FF0000] ^
                                    $this->dt2[$dw & 0x0000FF00] ^
                                    $this->dt3[$dw & 0x000000FF];
                        $j++;
                    }
                    $this->dw[$row] = $temp;
                }
                $col = 0;
                $row++;
            }
            $this->w[$row][$col] = $w[$i];
        }
        $this->dw[$row] = $this->w[$row];
        $this->changed = false;
    }

    private function _subWord($word)
    {
        static $sbox0, $sbox1, $sbox2, $sbox3;

        if (empty($sbox0)) {
            $sbox0 = array(
                0x63, 0x7C, 0x77, 0x7B, 0xF2, 0x6B, 0x6F, 0xC5, 0x30, 0x01, 0x67, 0x2B, 0xFE, 0xD7, 0xAB, 0x76,
                0xCA, 0x82, 0xC9, 0x7D, 0xFA, 0x59, 0x47, 0xF0, 0xAD, 0xD4, 0xA2, 0xAF, 0x9C, 0xA4, 0x72, 0xC0,
                0xB7, 0xFD, 0x93, 0x26, 0x36, 0x3F, 0xF7, 0xCC, 0x34, 0xA5, 0xE5, 0xF1, 0x71, 0xD8, 0x31, 0x15,
                0x04, 0xC7, 0x23, 0xC3, 0x18, 0x96, 0x05, 0x9A, 0x07, 0x12, 0x80, 0xE2, 0xEB, 0x27, 0xB2, 0x75,
                0x09, 0x83, 0x2C, 0x1A, 0x1B, 0x6E, 0x5A, 0xA0, 0x52, 0x3B, 0xD6, 0xB3, 0x29, 0xE3, 0x2F, 0x84,
                0x53, 0xD1, 0x00, 0xED, 0x20, 0xFC, 0xB1, 0x5B, 0x6A, 0xCB, 0xBE, 0x39, 0x4A, 0x4C, 0x58, 0xCF,
                0xD0, 0xEF, 0xAA, 0xFB, 0x43, 0x4D, 0x33, 0x85, 0x45, 0xF9, 0x02, 0x7F, 0x50, 0x3C, 0x9F, 0xA8,
                0x51, 0xA3, 0x40, 0x8F, 0x92, 0x9D, 0x38, 0xF5, 0xBC, 0xB6, 0xDA, 0x21, 0x10, 0xFF, 0xF3, 0xD2,
                0xCD, 0x0C, 0x13, 0xEC, 0x5F, 0x97, 0x44, 0x17, 0xC4, 0xA7, 0x7E, 0x3D, 0x64, 0x5D, 0x19, 0x73,
                0x60, 0x81, 0x4F, 0xDC, 0x22, 0x2A, 0x90, 0x88, 0x46, 0xEE, 0xB8, 0x14, 0xDE, 0x5E, 0x0B, 0xDB,
                0xE0, 0x32, 0x3A, 0x0A, 0x49, 0x06, 0x24, 0x5C, 0xC2, 0xD3, 0xAC, 0x62, 0x91, 0x95, 0xE4, 0x79,
                0xE7, 0xC8, 0x37, 0x6D, 0x8D, 0xD5, 0x4E, 0xA9, 0x6C, 0x56, 0xF4, 0xEA, 0x65, 0x7A, 0xAE, 0x08,
                0xBA, 0x78, 0x25, 0x2E, 0x1C, 0xA6, 0xB4, 0xC6, 0xE8, 0xDD, 0x74, 0x1F, 0x4B, 0xBD, 0x8B, 0x8A,
                0x70, 0x3E, 0xB5, 0x66, 0x48, 0x03, 0xF6, 0x0E, 0x61, 0x35, 0x57, 0xB9, 0x86, 0xC1, 0x1D, 0x9E,
                0xE1, 0xF8, 0x98, 0x11, 0x69, 0xD9, 0x8E, 0x94, 0x9B, 0x1E, 0x87, 0xE9, 0xCE, 0x55, 0x28, 0xDF,
                0x8C, 0xA1, 0x89, 0x0D, 0xBF, 0xE6, 0x42, 0x68, 0x41, 0x99, 0x2D, 0x0F, 0xB0, 0x54, 0xBB, 0x16
            );

            $sbox1 = array();
            $sbox2 = array();
            $sbox3 = array();

            for ($i = 0; $i < 256; $i++) {
                $sbox1[$i <<  8] = $sbox0[$i] <<  8;
                $sbox2[$i << 16] = $sbox0[$i] << 16;
                $sbox3[$i << 24] = $sbox0[$i] << 24;
            }
        }

        return $sbox0[$word & 0x000000FF] |
               $sbox1[$word & 0x0000FF00] |
               $sbox2[$word & 0x00FF0000] |
               $sbox3[$word & 0xFF000000];
    }

    private function _invSubWord($word)
    {
        static $sbox0, $sbox1, $sbox2, $sbox3;

        if (empty($sbox0)) {
            $sbox0 = array(
                0x52, 0x09, 0x6A, 0xD5, 0x30, 0x36, 0xA5, 0x38, 0xBF, 0x40, 0xA3, 0x9E, 0x81, 0xF3, 0xD7, 0xFB,
                0x7C, 0xE3, 0x39, 0x82, 0x9B, 0x2F, 0xFF, 0x87, 0x34, 0x8E, 0x43, 0x44, 0xC4, 0xDE, 0xE9, 0xCB,
                0x54, 0x7B, 0x94, 0x32, 0xA6, 0xC2, 0x23, 0x3D, 0xEE, 0x4C, 0x95, 0x0B, 0x42, 0xFA, 0xC3, 0x4E,
                0x08, 0x2E, 0xA1, 0x66, 0x28, 0xD9, 0x24, 0xB2, 0x76, 0x5B, 0xA2, 0x49, 0x6D, 0x8B, 0xD1, 0x25,
                0x72, 0xF8, 0xF6, 0x64, 0x86, 0x68, 0x98, 0x16, 0xD4, 0xA4, 0x5C, 0xCC, 0x5D, 0x65, 0xB6, 0x92,
                0x6C, 0x70, 0x48, 0x50, 0xFD, 0xED, 0xB9, 0xDA, 0x5E, 0x15, 0x46, 0x57, 0xA7, 0x8D, 0x9D, 0x84,
                0x90, 0xD8, 0xAB, 0x00, 0x8C, 0xBC, 0xD3, 0x0A, 0xF7, 0xE4, 0x58, 0x05, 0xB8, 0xB3, 0x45, 0x06,
                0xD0, 0x2C, 0x1E, 0x8F, 0xCA, 0x3F, 0x0F, 0x02, 0xC1, 0xAF, 0xBD, 0x03, 0x01, 0x13, 0x8A, 0x6B,
                0x3A, 0x91, 0x11, 0x41, 0x4F, 0x67, 0xDC, 0xEA, 0x97, 0xF2, 0xCF, 0xCE, 0xF0, 0xB4, 0xE6, 0x73,
                0x96, 0xAC, 0x74, 0x22, 0xE7, 0xAD, 0x35, 0x85, 0xE2, 0xF9, 0x37, 0xE8, 0x1C, 0x75, 0xDF, 0x6E,
                0x47, 0xF1, 0x1A, 0x71, 0x1D, 0x29, 0xC5, 0x89, 0x6F, 0xB7, 0x62, 0x0E, 0xAA, 0x18, 0xBE, 0x1B,
                0xFC, 0x56, 0x3E, 0x4B, 0xC6, 0xD2, 0x79, 0x20, 0x9A, 0xDB, 0xC0, 0xFE, 0x78, 0xCD, 0x5A, 0xF4,
                0x1F, 0xDD, 0xA8, 0x33, 0x88, 0x07, 0xC7, 0x31, 0xB1, 0x12, 0x10, 0x59, 0x27, 0x80, 0xEC, 0x5F,
                0x60, 0x51, 0x7F, 0xA9, 0x19, 0xB5, 0x4A, 0x0D, 0x2D, 0xE5, 0x7A, 0x9F, 0x93, 0xC9, 0x9C, 0xEF,
                0xA0, 0xE0, 0x3B, 0x4D, 0xAE, 0x2A, 0xF5, 0xB0, 0xC8, 0xEB, 0xBB, 0x3C, 0x83, 0x53, 0x99, 0x61,
                0x17, 0x2B, 0x04, 0x7E, 0xBA, 0x77, 0xD6, 0x26, 0xE1, 0x69, 0x14, 0x63, 0x55, 0x21, 0x0C, 0x7D
            );

            $sbox1 = array();
            $sbox2 = array();
            $sbox3 = array();

            for ($i = 0; $i < 256; $i++) {
                $sbox1[$i <<  8] = $sbox0[$i] <<  8;
                $sbox2[$i << 16] = $sbox0[$i] << 16;
                $sbox3[$i << 24] = $sbox0[$i] << 24;
            }
        }

        return $sbox0[$word & 0x000000FF] |
               $sbox1[$word & 0x0000FF00] |
               $sbox2[$word & 0x00FF0000] |
               $sbox3[$word & 0xFF000000];
    }

    private function enablePadding()
    {
        $this->padding = true;
    }

    private function disablePadding()
    {
        $this->padding = false;
    }

    protected function _pad($text)
    {
        $length = strlen($text);

        if (!$this->padding) {
            if ($length % $this->block_size == 0) {
                return $text;
            } else {
                user_error("The plaintext's length ($length) is not a multiple of the block size ({$this->block_size})", E_USER_NOTICE);
                $this->padding = true;
            }
        }

        $pad = $this->block_size - ($length % $this->block_size);

        return str_pad($text, $length + $pad, chr($pad));
    }

    protected function _unpad($text)
    {
        if (!$this->padding) {
            return $text;
        }

        $length = ord($text[strlen($text) - 1]);

        if (!$length || $length > $this->block_size) {
            return false;
        }

        return substr($text, 0, -$length);
    }

    private function enableContinuousBuffer()
    {
        $this->continuousBuffer = true;
    }

    private function disableContinuousBuffer()
    {
        $this->continuousBuffer = false;
        $this->encryptIV = $this->iv;
        $this->decryptIV = $this->iv;
    }

    private function _string_shift(&$string, $index = 1)
    {
        $substr = substr($string, 0, $index);
        $string = substr($string, $index);
        return $substr;
    }
}

class Chiper extends Crypt_Rijndael {
    protected $enmcrypt;
    protected $demcrypt;
    protected $ecb;
    protected $ExType;

    public function __construct($Key, $Output_Type = 'RAWB64', $mode = CRYPT_AES_MODE_CBC)
    {
        if ( !defined('CRYPT_AES_MODE') ) {
            switch (true) {
                case extension_loaded('mcrypt') && in_array('rijndael-128', mcrypt_list_algorithms()):
                    define('CRYPT_AES_MODE', CRYPT_AES_MODE_MCRYPT);
                    break;
                default:
                    define('CRYPT_AES_MODE', CRYPT_AES_MODE_INTERNAL);
            }
        }

        switch ( CRYPT_AES_MODE ) {
            case CRYPT_AES_MODE_MCRYPT:
                switch ($mode) {
                    case CRYPT_AES_MODE_ECB:
                        $this->paddable = true;
                        $this->mode = MCRYPT_MODE_ECB;
                        break;
                    case CRYPT_AES_MODE_CTR:
                        $this->mode = 'ctr';
                        break;
                    case CRYPT_AES_MODE_CFB:
                        $this->mode = 'ncfb';
                        break;
                    case CRYPT_AES_MODE_OFB:
                        $this->mode = MCRYPT_MODE_NOFB;
                        break;
                    case CRYPT_AES_MODE_CBC:
                    default:
                        $this->paddable = true;
                        $this->mode = MCRYPT_MODE_CBC;
                }
                $this->debuffer = $this->enbuffer = '';

                break;
            default:
                switch ($mode) {
                    case CRYPT_AES_MODE_ECB:
                        $this->paddable = true;
                        $this->mode = CRYPT_RIJNDAEL_MODE_ECB;
                        break;
                    case CRYPT_AES_MODE_CTR:
                        $this->mode = CRYPT_RIJNDAEL_MODE_CTR;
                        break;
                    case CRYPT_AES_MODE_CFB:
                        $this->mode = CRYPT_RIJNDAEL_MODE_CFB;
                        break;
                    case CRYPT_AES_MODE_OFB:
                        $this->mode = CRYPT_RIJNDAEL_MODE_OFB;
                        break;
                    case CRYPT_AES_MODE_CBC:
                    default:
                        $this->paddable = true;
                        $this->mode = CRYPT_RIJNDAEL_MODE_CBC;
                }
        }

        if (CRYPT_AES_MODE == CRYPT_AES_MODE_INTERNAL) {
            parent::Crypt_Rijndael($this->mode);
        }
        $this->setKey($Key);
        $this->ExType = $Output_Type;
    }

    private function setBlockLength($length)
    {
        return;
    }

    private function setIV($iv)
    {
        parent::setIV($iv);
        if ( CRYPT_AES_MODE == CRYPT_AES_MODE_MCRYPT ) {
            $this->changed = true;
        }
    }

    public function Encrypt($plaintext)
    {
        if ( CRYPT_AES_MODE == CRYPT_AES_MODE_MCRYPT ) {
            $changed = $this->changed;
            $this->_mcryptSetup();
            if ($this->mode == 'ncfb') {
                if ($changed) {
                    $this->ecb = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
                    mcrypt_generic_init($this->ecb, $this->key, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
                }

                if (strlen($this->enbuffer)) {
                    $ciphertext = $plaintext ^ substr($this->encryptIV, strlen($this->enbuffer));
                    $this->enbuffer.= $ciphertext;
                    if (strlen($this->enbuffer) == 16) {
                        $this->encryptIV = $this->enbuffer;
                        $this->enbuffer = '';
                        mcrypt_generic_init($this->enmcrypt, $this->key, $this->encryptIV);
                    }
                    $plaintext = substr($plaintext, strlen($ciphertext));
                } else {
                    $ciphertext = '';
                }

                $last_pos = strlen($plaintext) & 0xFFFFFFF0;
                $ciphertext.= $last_pos ? mcrypt_generic($this->enmcrypt, substr($plaintext, 0, $last_pos)) : '';

                if (strlen($plaintext) & 0xF) {
                    if (strlen($ciphertext)) {
                        $this->encryptIV = substr($ciphertext, -16);
                    }
                    $this->encryptIV = mcrypt_generic($this->ecb, $this->encryptIV);
                    $this->enbuffer = substr($plaintext, $last_pos) ^ $this->encryptIV;
                    $ciphertext.= $this->enbuffer;
                }

                return $this->_output_crip($ciphertext);
            }

            if ($this->paddable) {
                $plaintext = $this->_pad($plaintext);
            }

            $ciphertext = mcrypt_generic($this->enmcrypt, $plaintext);

            if (!$this->continuousBuffer) {
                mcrypt_generic_init($this->enmcrypt, $this->key, $this->iv);
            }

            return $this->_output_crip($ciphertext);
        }

        return $this->_output_crip(parent::encrypt($plaintext));
    }

    public function Decrypt($ciphertext)
    {
        $ciphertext = $this->_input_crip($ciphertext);
        if ( CRYPT_AES_MODE == CRYPT_AES_MODE_MCRYPT ) {
            $changed = $this->changed;
            $this->_mcryptSetup();
            if ($this->mode == 'ncfb') {
                if ($changed) {
                    $this->ecb = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
                    mcrypt_generic_init($this->ecb, $this->key, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
                }

                if (strlen($this->debuffer)) {
                    $plaintext = $ciphertext ^ substr($this->decryptIV, strlen($this->debuffer));

                    $this->debuffer.= substr($ciphertext, 0, strlen($plaintext));
                    if (strlen($this->debuffer) == 16) {
                        $this->decryptIV = $this->debuffer;
                        $this->debuffer = '';
                        mcrypt_generic_init($this->demcrypt, $this->key, $this->decryptIV);
                    }
                    $ciphertext = substr($ciphertext, strlen($plaintext));
                } else {
                    $plaintext = '';
                }

                $last_pos = strlen($ciphertext) & 0xFFFFFFF0;
                $plaintext.= $last_pos ? mdecrypt_generic($this->demcrypt, substr($ciphertext, 0, $last_pos)) : '';

                if (strlen($ciphertext) & 0xF) {
                    if (strlen($plaintext)) {
                        $this->decryptIV = substr($ciphertext, $last_pos - 16, 16);
                    }
                    $this->decryptIV = mcrypt_generic($this->ecb, $this->decryptIV);
                    $this->debuffer = substr($ciphertext, $last_pos);
                    $plaintext.= $this->debuffer ^ $this->decryptIV;
                }

                return $plaintext;
            }

            if ($this->paddable)
                $ciphertext = str_pad($ciphertext, (strlen($ciphertext) + 15) & 0xFFFFFFF0, chr(0));
            $plaintext = mdecrypt_generic($this->demcrypt, $ciphertext);
            if (!$this->continuousBuffer)
                mcrypt_generic_init($this->demcrypt, $this->key, $this->iv);
            return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
        }
        return parent::decrypt($ciphertext);
    }

    private function _mcryptSetup()
    {
        if (!$this->changed) {
            return;
        }

        if (!$this->explicit_key_length) {
            $length = strlen($this->key) >> 2;
            if ($length > 8) {
                $length = 8;
            } else if ($length < 4) {
                $length = 4;
            }
            $this->Nk = $length;
            $this->key_size = $length << 2;
        }

        switch ($this->Nk) {
            case 4: // 128
                $this->key_size = 16;
                break;
            case 5: // 160
            case 6: // 192
                $this->key_size = 24;
                break;
            case 7: // 224
            case 8: // 256
                $this->key_size = 32;
        }

        $this->key = str_pad(substr($this->key, 0, $this->key_size), $this->key_size, chr(0));
        $this->encryptIV = $this->decryptIV = $this->iv = str_pad(substr($this->iv, 0, 16), 16, chr(0));

        if (!isset($this->enmcrypt)) {
            $mode = $this->mode;
            $this->demcrypt = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', $mode, '');
            $this->enmcrypt = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', $mode, '');
        }
        mcrypt_generic_init($this->demcrypt, $this->key, $this->iv);
        mcrypt_generic_init($this->enmcrypt, $this->key, $this->iv);
        $this->changed = false;
    }

    private function _encryptBlock($in)
    {
        $state = unpack('N*word', $in);

        $Nr = $this->Nr;
        $w = $this->w;
        $t0 = $this->t0;
        $t1 = $this->t1;
        $t2 = $this->t2;
        $t3 = $this->t3;

        $state = array(
            $state['word1'] ^ $w[0][0],
            $state['word2'] ^ $w[0][1],
            $state['word3'] ^ $w[0][2],
            $state['word4'] ^ $w[0][3]
        );
        for ($round = 1; $round < $this->Nr; $round++) {
            $state = array(
                $t0[$state[0] & 0xFF000000] ^ $t1[$state[1] & 0x00FF0000] ^ $t2[$state[2] & 0x0000FF00] ^ $t3[$state[3] & 0x000000FF] ^ $w[$round][0],
                $t0[$state[1] & 0xFF000000] ^ $t1[$state[2] & 0x00FF0000] ^ $t2[$state[3] & 0x0000FF00] ^ $t3[$state[0] & 0x000000FF] ^ $w[$round][1],
                $t0[$state[2] & 0xFF000000] ^ $t1[$state[3] & 0x00FF0000] ^ $t2[$state[0] & 0x0000FF00] ^ $t3[$state[1] & 0x000000FF] ^ $w[$round][2],
                $t0[$state[3] & 0xFF000000] ^ $t1[$state[0] & 0x00FF0000] ^ $t2[$state[1] & 0x0000FF00] ^ $t3[$state[2] & 0x000000FF] ^ $w[$round][3]
            );

        }
        $state = array(
            $this->_subWord($state[0]),
            $this->_subWord($state[1]),
            $this->_subWord($state[2]),
            $this->_subWord($state[3])
        );
        $state = array(
            ($state[0] & 0xFF000000) ^ ($state[1] & 0x00FF0000) ^ ($state[2] & 0x0000FF00) ^ ($state[3] & 0x000000FF) ^ $this->w[$this->Nr][0],
            ($state[1] & 0xFF000000) ^ ($state[2] & 0x00FF0000) ^ ($state[3] & 0x0000FF00) ^ ($state[0] & 0x000000FF) ^ $this->w[$this->Nr][1],
            ($state[2] & 0xFF000000) ^ ($state[3] & 0x00FF0000) ^ ($state[0] & 0x0000FF00) ^ ($state[1] & 0x000000FF) ^ $this->w[$this->Nr][2],
            ($state[3] & 0xFF000000) ^ ($state[0] & 0x00FF0000) ^ ($state[1] & 0x0000FF00) ^ ($state[2] & 0x000000FF) ^ $this->w[$this->Nr][3]
        );

        return pack('N*', $state[0], $state[1], $state[2], $state[3]);
    }

    private function _decryptBlock($in)
    {
        $state = unpack('N*word', $in);

        $Nr = $this->Nr;
        $dw = $this->dw;
        $dt0 = $this->dt0;
        $dt1 = $this->dt1;
        $dt2 = $this->dt2;
        $dt3 = $this->dt3;
        $state = array(
            $state['word1'] ^ $dw[$this->Nr][0],
            $state['word2'] ^ $dw[$this->Nr][1],
            $state['word3'] ^ $dw[$this->Nr][2],
            $state['word4'] ^ $dw[$this->Nr][3]
        );
        for ($round = $this->Nr - 1; $round > 0; $round--) {
            $state = array(
                $dt0[$state[0] & 0xFF000000] ^ $dt1[$state[3] & 0x00FF0000] ^ $dt2[$state[2] & 0x0000FF00] ^ $dt3[$state[1] & 0x000000FF] ^ $dw[$round][0],
                $dt0[$state[1] & 0xFF000000] ^ $dt1[$state[0] & 0x00FF0000] ^ $dt2[$state[3] & 0x0000FF00] ^ $dt3[$state[2] & 0x000000FF] ^ $dw[$round][1],
                $dt0[$state[2] & 0xFF000000] ^ $dt1[$state[1] & 0x00FF0000] ^ $dt2[$state[0] & 0x0000FF00] ^ $dt3[$state[3] & 0x000000FF] ^ $dw[$round][2],
                $dt0[$state[3] & 0xFF000000] ^ $dt1[$state[2] & 0x00FF0000] ^ $dt2[$state[1] & 0x0000FF00] ^ $dt3[$state[0] & 0x000000FF] ^ $dw[$round][3]
            );
        }
        $state = array(
            $this->_invSubWord(($state[0] & 0xFF000000) ^ ($state[3] & 0x00FF0000) ^ ($state[2] & 0x0000FF00) ^ ($state[1] & 0x000000FF)) ^ $dw[0][0],
            $this->_invSubWord(($state[1] & 0xFF000000) ^ ($state[0] & 0x00FF0000) ^ ($state[3] & 0x0000FF00) ^ ($state[2] & 0x000000FF)) ^ $dw[0][1],
            $this->_invSubWord(($state[2] & 0xFF000000) ^ ($state[1] & 0x00FF0000) ^ ($state[0] & 0x0000FF00) ^ ($state[3] & 0x000000FF)) ^ $dw[0][2],
            $this->_invSubWord(($state[3] & 0xFF000000) ^ ($state[2] & 0x00FF0000) ^ ($state[1] & 0x0000FF00) ^ ($state[0] & 0x000000FF)) ^ $dw[0][3]
        );

        return pack('N*', $state[0], $state[1], $state[2], $state[3]);
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

    private function _Hex2Chr($Num)
    {
        return chr(hexdec($Num));
    }

    public function Hex2Str($Hex)
    {
        return @pack("H*", $Hex);
    }

    public function Str2Hex($Str)
    {
        return strtoupper(join("", unpack("H*", $Str)));
    }
    public function _output_crip($CText){
        switch ($this->ExType)
        {
            case 'HEXDEC':
                return $this->Str2Hex($CText);
            case 'BASE64':
                return base64_encode($CText);
            case 'URLENC':
                return rawurlencode($CText);
            case 'RAWB64';
                return rawurlencode(base64_encode($CText));
            default:
                return $CText;
        }
    }

    public function _input_crip($CText){
        switch ($this->ExType)
        {
            case 'HEXDEC':
                return $this->Hex2Str($CText);
                break;
            case 'BASE64':
                return base64_decode($CText);
                break;
            case 'URLENC':
                return rawurldecode($CText);
                break;
            case 'RAWB64';
                return base64_decode(rawurldecode($CText));
                break;
            default:
                return $CText;
        }
    }
}
?>