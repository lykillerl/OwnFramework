<?PHP

#繁體简体萬國碼万国码
#-----------------------------------------------------------------------#
# PHP CLASS
# CLASS AUTHOR : LYK
# DESCRIPTION : Use the Class to Connect to Database
# DATABASE API : MSSQL
# DEVOLOPMENT DATE : 2011-09-12
#-----------------------------------------------------------------------#

class MSSQL extends SQL_Data
{
    private $DBConnect;

    #----------------------------------------------------------------------#
    #Session of Connect to Database
    #----------------------------------------------------------------------#

    #Make a connection and store connect id : RETURN RESOURE LINK
    public function SQLConnect($SQLHost = 'localhost', $SQLUser = 'root', $SQLPassword =
        '', $SQL_DefaultDB = '', $PConnect = 0)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }

        #Driver={SQL Server};Network Library=dbmssocn;Server=DEVDB01; Database=vssDB;
        if (!empty($this->DBConnect))
            odbc_close_all($this->DBConnect);
        $ConnStr = "Driver={SQL Server Native Client 10.0}; Server=" . $this->
            Query_Format($SQLHost, false) . "; AutoTranslate=yes; Database=" . $this->
            Query_Format($SQL_DefaultDB, false) . ";";
        if (empty($PConnect))
        {
            $this->DBConnect = odbc_connect($ConnStr, $this->Query_Format($SQLUser, false),
                $this->Query_Format($SQLPassword, false));
        } else
        {
            $this->DBConnect = odbc_pconnect($ConnStr, $this->Query_Format($SQLUser, false),
                $this->Query_Format($SQLPassword, false));
            @odbc_autocommit($this->DBConnect, false);
        }
        return $this->DBConnect;
    }

    #----------------------------------------------------------------------#
    #Session of the Query
    #----------------------------------------------------------------------#

    #Return the Query Result ID : RETURN RESOURE LINK
    public function Query($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        if ($NoChange == false)
        {
            $DBQ = $this->Query_Format($SQLQuery, $NoChange);
        } else
        {
            $DBQ = $SQLQuery;
        }
        return @odbc_exec($this->DBConnect, $DBQ);
    }

    #Return given database the Query Result ID : RETURN RESOURE LINK
    public function DB_Query($DB, $SQLQuery)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_exec($DB, $this->query_format($SQLQuery, $NoChange));
    }

    #----------------------------------------------------------------------#
    #Session of the Result Field
    #----------------------------------------------------------------------#

    #Return the num of field : RETURN INTERGER
    public function Num_Field($SQLQueryID)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_field_num($SQLQueryID);
    }

    #Return the field of name : RETURN STRING
    public function Field_Name($SQLQueryID, $SColumn)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_field_name($SQLQueryID, $SColumn);
    }

    #Return the field of length : RETURN INTERGER
    public function Field_Len($SQLQueryID, $SColumn)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_field_len($SQLQueryID, $SColumn);
    }

    #Return the field of type : RETURN STRING
    public function Field_Type($SQLQueryID, $SColumn)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_field_type($SQLQueryID, $SColumn);
    }

    #----------------------------------------------------------------------#
    #Session of the Result Data
    #----------------------------------------------------------------------#

    #Return QueryID Cell Result : RETURN STRING
    public function Result($SQLQueryID, $Cols, $Rows)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        for ($i = 0; $i <= $Rows; $i++)
        {
            @odbc_next_result($SQLQueryID);
        }
        return @odbc_result($SQLQueryID, $Cols);
    }

    #Return all row all coloumn from query
    public function Result_All($SQLQueryID, $Str_Format)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        ob_start();
        @odbc_result_all($SQLQueryID, $Str_Format);
        $GetResult = ob_get_clean();
        return $GetResult;
    }

    #Return 1 row array(column) value with the Query result: RETURN ARRAY
    public function Fetch_Row($SQLQueryID)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @array_values(odbc_fetch_array($SQLQueryID));
    }

    #Return 1 row array assoc(column) value with the Query result: RETURN OBJECT
    public function Fetch_Assoc($SQLQueryID)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_fetch_array($SQLQueryID);
    }

    #Return 1 row object(column) value with the Query result: RETURN OBJECT
    public function Fetch_Object($SQLQueryID)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_fetch_object($SQLQueryID);
    }

    #Return number of  row with the Query result : RETURN INTERGR
    public function Num_Rows($SQLQueryID)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return odbc_num_rows($SQLQueryID);
    }

    #Return 1 cell result in row 0 col 0 without query refence.
    public function CResult($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_result(odbc_exec($this->DBConnect, $this->query_format($SQLQuery, $NoChange)),
            1);
    }

    #Return 1 row result without query refence.
    public function CFetch_Row($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @array_values(odbc_fetch_array(odbc_exec($this->DBConnect, $this->
            query_format($SQLQuery, $NoChange))));
    }


    #Return 1 row assoc result without query refence.
    public function CFetch_Assoc($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_fetch_array(odbc_exec($this->DBConnect, $this->query_format($SQLQuery,
            $NoChange)));
    }

    #----------------------------------------------------------------------#
    #Session of the Error handeling
    #----------------------------------------------------------------------#

    #Return Mysql Last times Error Number : RETURN INTERGER
    public function DB_ErrorNo()
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_error($this->DBConnect);
    }

    #Return Mysql Last times Error : RETURN STRING
    public function DB_Error($Format = null)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return (!empty($Format) ? $this->sqlerrformat(@odbc_errormsg($this->DBConnect)) :
            @odbc_errormsg($this->DBConnect));
    }

    #----------------------------------------------------------------------#
    #Session of the Close or free result
    #----------------------------------------------------------------------#

    #Free up the Query result : RETURN BOOLIN
    public function Free_Result($SQLQuery)
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        return @odbc_free_result($SQLQuery);
    }

    #Close the connect without Persistent Links : RETURN BOOLIN
    public function SQLClose()
    {
        if (extension_loaded("odbc") == false)
        {
            return false;
        }
        @odbc_close($this->DBConnect);
    }

    #Smartly String
    public function SmartStr($StrValue, $NoChange = false)
    {
        $StrValue = $this->Query_Format($StrValue, $NoChange);
        while (ereg("[ ]{2,}", $StrValue, $reg))
        {
            $StrValue = str_replace($reg[0], " ", $StrValue);
        }
        while (ereg("\r\n\r\n{1,}", $StrValue, $reg))
        {
            $StrValue = str_replace($reg[0], "\r\n", $StrValue);
        }
        $StrValue = mysql_escape_string(trim($StrValue));
        return $StrValue;
    }

    private function Query_Format($SQL_Query, $NoChange = false)
    {
        if (!$NoChange)
        {
            $Source = array("now()", "`");
            $Replace = array("getdate()", "\"");
            $SQL_Query = str_ireplace($Source, $Replace, function_exists('Str_Evmkey') ?
                Str_Evmkey($SQL_Query) : $SQL_Query);
        }
        return $SQL_Query;
    }

    private function SqlErrFormat($ERMSG)
    {
        $iCount = SUBSTR_COUNT($ERMSG, "'");
        $iCount -= $iCount % 2;
        if ($iCount > 0)
        {
            for ($iPos = 0; $iPos < STRLEN($ERMSG); $iPos++)
            {
                if (SUBSTR($ERMSG, $iPos, 1) == "'")
                {
                    if ($iCount > 0)
                    {
                        if ($iCount % 2 == 0)
                        {
                            $StrCount .= "'<font color=\"#80000000\">";
                        } else
                        {
                            $StrCount .= "</font>'";
                        }
                        $iCount--;
                    }
                } else
                {
                    $StrCount .= SUBSTR($ERMSG, $iPos, 1);
                }
            }
            return "<font color=\"#000080\">{$StrCount}</font>";
        } else
        {
            return "<font color=\"#000080\">{$ERMSG}</font>";
        }
    }
}

?>