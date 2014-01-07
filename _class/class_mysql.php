<?PHP

#-----------------------------------------------------------------------#
# PHP CLASS
# CLASS AUTHOR : LYK
# DESCRIPTION : Use the Class to Connect to Database
# DATABASE API : MYSQL
# DEVOLOPMENT DATE : 2007-08-15
#-----------------------------------------------------------------------#

class MYSQL extends SQL_Data
{
    private $Res_Link;
    private $Res_Result;
    #----------------------------------------------------------------------#
    #Session of Connect or change user
    #----------------------------------------------------------------------#

    #Make a connection and store connect id : RETURN RESOURE LINK
    public function SQLConnect($SQLHost = 'localhost', $SQLPort = 3306, $SQLUser = '', $SQLPassword =
        '')
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $this->Res_Link = mysqli_connect($this->SmartStr($SQLHost), $this->SmartStr($SQLUser), $this->
            SmartStr($SQLPassword), "", $SQLPort);
        if (!$this->Res_Link)
            Sys_Log("{$SQL_Query} " . $this->DB_Error());
        return $this->Res_Link;
    }

    #----------------------------------------------------------------------#
    #Select or Listing from Mysql
    #----------------------------------------------------------------------#

    #Select Database by the function : RETURN BOOLIN
    public function SelectDB($DBName, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return mysqli_select_db($Res_Link, $this->SmartStr($DBName));
    }

    #----------------------------------------------------------------------#
    #Session of Query Handle
    #----------------------------------------------------------------------#

    #Return escaped string with the mysql
    public function Escape_String($SQL_Query, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return @mysqli_real_escape_string($Res_Link, $SQL_Query);
    }

    #Return the Query Result ID : RETURN RESOURE LINK
    public function Query($SQL_Query, $Escape = true, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        if (is_resource($SQL_Query))
            return $SQL_Query;
        if ($Escape === (boolean)true)
            $SQL_Query = $this->SmartStr($SQL_Query);
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        $this->Res_Result = @mysqli_query($Res_Link, $SQL_Query);
        if (!$this->Res_Result)
            Sys_Log("{$SQL_Query} " . $this->DB_Error());
        return $this->Res_Result;
    }

    #Return the Query Result ID : RETURN RESOURE LINK
    public function MultiQuery($SQL_Query, $Escape = true, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        if (is_resource($SQL_Query))
            return $SQL_Query;
        if ($Escape === (boolean)true)
            $SQL_Query = $this->SmartStr($SQL_Query);
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        $this->Res_Result = @mysqli_multi_query($this->Res_Link, $SQL_Query);
        if (!$this->Res_Result)
            Sys_Log("{$SQL_Query} " . $this->DB_Error());
        return $this->Res_Result;
    }

    #Return Affected row of the Last Query result : RETURN INTERGER
    public function Affected_Rows($Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return @mysqli_affected_rows($Res_Link);
    }

    #----------------------------------------------------------------------#
    #Session of ResultSet Handle.
    #----------------------------------------------------------------------#

    public function Next_ResultSet($Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return mysqli_next_result($Res_Link);
    }

    public function More_ResultSet($Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return mysqli_more_results($Res_Link);
    }

    public function Store_ResultSet($Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return mysqli_store_result($Res_Link);
    }

    public function Clear_ResultSet($Res_Link)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        while ($this->Next_ResultSet($Res_Link))
            if ($Res_Result = $this->Store_ResultSet($Res_Link))
                $this->Free_Result($Res_Result);
    }
    #----------------------------------------------------------------------#
    #Session of the Result Field
    #----------------------------------------------------------------------#

    #Return the num of field : RETURN INTERGER
    public function Num_Fields($Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_num_fields($Res_Result);
    }

    #Return an Object of Column information with the Result : RETURN OBJECT
    public function Fetch_Field($SColumn, $Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        mysqli_field_seek($Res_Result, $SColumn);
        $FInfo = @mysqli_fetch_field($Res_Result);
        return $FInfo;
    }

    #----------------------------------------------------------------------#
    #Session of the Result Data
    #----------------------------------------------------------------------#

    #Return QueryID Cell Result : RETURN STRING
    public function Result($Cols, $Rows, $Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        mysqli_data_seek($Res_Result, $Rows);
        $Result = mysqli_fetch_row($Res_Result);
        return $Result[$Cols];
    }

    #Return 1 row array(column) value with the Query result: RETURN ARRAY
    public function Fetch_Row($Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_fetch_row($Res_Result);
    }

    #Return 1 row array assoc(column) value with the Query result: RETURN OBJECT
    public function Fetch_Assoc($Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_fetch_assoc($Res_Result);
    }

    #Return 1 row object(column) value with the Query result: RETURN OBJECT
    public function Fetch_Object($StrClassNm, $Paramters, $Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_fetch_object($Res_Result, $StrClassNm, $Paramters);
    }

    #Return 1 row array(column) length with the Query result: RETURN ARRAY
    public function Fetch_Lengths($Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_fetch_lengths($Res_Result);
    }

    #Return number of row with the Query result : RETURN INTERGR
    public function Num_Rows($Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_num_rows($Res_Result);
    }

    #Seek the Query Result positon : RETURN BOOLIN
    public function DB_Seek($Row, $Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Result = (!empty($Res_Result) ? $Res_Result : $this->Res_Result);
        return @mysqli_data_seek($Res_Result, $Row);
    }

    #Return 1 cell result in row 0 col 0 without query refence.
    public function CResult($SQL_Query, $Escape = true, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        return $this->Result(0, 0, $this->Query($SQL_Query, $Escape, $Res_Link));
    }

    #Return 1 row result without query refence.
    public function CFetch_Row($SQL_Query, $Escape = true)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        return $this->Fetch_Row($this->Query($SQL_Query, $Escape));
    }

    #Return 1 row assoc result without query refence.
    public function CFetch_Assoc($SQL_Query, $Escape = true)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        return $this->Fetch_Assoc($this->Query($SQL_Query, $Escape));
    }

    #----------------------------------------------------------------------#
    # Mysql Special Function
    #----------------------------------------------------------------------#

    public function Next_Insert_ID($Table, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Table_Infor = $this->Fetch_Assoc($this->Query("SHOW TABLE STATUS LIKE '{$Table}'", true, $Res_Link));
        return $Table_Infor['Auto_increment'];
    }

    public function Column_Comment($SColumn, $STable, $SSchema = null, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $SSchema = $this->Escape_String($SSchema);
        $STable = $this->Escape_String($STable);
        $SColumn = $this->Escape_String($SColumn);
        $DBTable = (!empty($SSchema) ? "{$SSchema}`.`" : "") . "{$STable}";
        $Result = $this->CFetch_Assoc("SHOW FULL COLUMNS FROM `{$DBTable}` WHERE `FIELD`='{$SColumn}'", true, $Res_Link);
        return $Result['Comment'];
    }

    #----------------------------------------------------------------------#
    #Session of the Error handeling
    #----------------------------------------------------------------------#

    #Return Mysql Last times Error Number : RETURN INTERGER
    public function DB_ErrorNo($Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return @mysqli_errno($Res_Link);
    }

    #Return Mysql Last times Error : RETURN STRING
    public function DB_Error($Format = null, $Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        $Res_Link = (!empty($Res_Link) ? $Res_Link : $this->Res_Link);
        return (!empty($Format) ? $this->SqlErrFormat(@mysqli_error($Res_Link)) : @mysqli_error($Res_Link));
    }

    #----------------------------------------------------------------------#
    #Session of the Close or free result
    #----------------------------------------------------------------------#

    #Free up the Query result : RETURN BOOLIN
    public function Free_Result($Res_Result = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        if (empty($Res_Result))
        {
            $Res_Result = $this->Res_Result;
            $this->Res_Result = null;
        }
        return @mysqli_free_result($Res_Result);
    }

    #Close the connect without Persistent Links : RETURN BOOLIN
    public function SQLClose($Res_Link = null)
    {
        if (extension_loaded("mysqli") == false)
            return false;
        if (!empty($Res_Link))
            @mysqli_close($Res_Link);
        else
        {
            @mysqli_close($this->Res_Link);
            $this->Res_Link = null;
        }
    }

    #Smartly String
    public function SmartStr($StrValue)
    {
        $StrValue = (function_exists('SysConvVar') ? SysConvVar($StrValue) : $StrValue);
        return $StrValue;
    }

    private function SqlErrFormat($ERMSG)
    {
        $iCount = substr_count($ERMSG, "'");
        $iCount -= $iCount % 2;
        if ($iCount > 0)
        {
            for ($iPos = 0; $iPos < strlen($ERMSG); $iPos++)
                if (substr($ERMSG, $iPos, 1) == "'")
                    if ($iCount > 0)
                    {
                        if ($iCount % 2 == 0)
                            $StrCount .= "'<span color=\"#80000000\">";
                        else
                            $StrCount .= "</span>'";
                        $iCount--;
                    } else
                        $StrCount .= substr($ERMSG, $iPos, 1);
            return "<span color=\"#000080\">" . $StrCount . "</span>";
        } else
            return "";
    }
}

?>