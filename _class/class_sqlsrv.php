<?PHP

#繁體简体萬國碼万国码
#-----------------------------------------------------------------------#
# PHP CLASS
# CLASS AUTHOR : LYK
# DESCRIPTION : Use the Class to Connect to Database
# DATABASE API : MSSQL - SQLSRV
# DEVOLOPMENT DATE : 2011-09-12
#-----------------------------------------------------------------------#

class SQLSRV extends SQL_Data
{
    private $DBConnect;
    private $Field_Property;

    #----------------------------------------------------------------------#
    #Session of Connect to Database
    #----------------------------------------------------------------------#

    #Make a connection and store connect id : RETURN RESOURE LINK
    public function SQLCONNECT($SQLHost = 'localhost', $SQLUser = 'root', $SQLPassword =
        '', $SQL_DefaultDB = '')
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }

        #Driver={SQL Server};Network Library=dbmssocn;Server=DEVDB01; Database=vssDB;
        if (!empty($this->DBConnect))
            $this->SQLCLOSE();
        $Connect_Property = array(
            'uid' => $SQLUser,
            'pwd' => $SQLPassword,
            'CharacterSet' => 'UTF-8',
            'ReturnDatesAsStrings' => true);
        if (!empty($SQL_DefaultDB))
            $Connect_Property['Database'] = $SQL_DefaultDB;
        $this->DBConnect = @sqlsrv_connect($SQLHost, $Connect_Property);
        return $this->DBConnect;
    }

    #----------------------------------------------------------------------#
    #Session of the Query
    #----------------------------------------------------------------------#

    #Return the Query Result ID : RETURN RESOURE LINK
    public function QUERY($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("sqlsrv") == false)
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
        return @sqlsrv_query($this->DBConnect, $DBQ, array(), array("Scrollable" =>
                SQLSRV_CURSOR_STATIC));
    }

    #Return given database the Query Result ID : RETURN RESOURE LINK
    public function DB_QUERY($DB, $SQLQuery)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @sqlsrv_query($DB, $this->Query_Format($SQLQuery, $NoChange));
    }

    #----------------------------------------------------------------------#
    #Session of the Result Field
    #----------------------------------------------------------------------#

    #Return the num of field : RETURN INTERGER
    public function NUM_FIELD($SQL_Resource)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @sqlsrv_num_fields($SQL_Resource);
    }

    #Return the field of name : RETURN STRING
    public function FIELD_NAME($SQL_Resource, $SColumn)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        $DB_Field = sqlsrv_field_metadata($SQL_Resource);
        return $DB_Field[$SColumn]['Name'];
    }

    #Return the field of length : RETURN INTERGER
    public function FIELD_LEN($SQL_Resource, $SColumn)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        $DB_Field = sqlsrv_field_metadata($SQL_Resource);
        return $DB_Field[$SColumn]['Size'];
    }

    #Return the field of type : RETURN STRING
    public function FIELD_TYPE($SQL_Resource, $SColumn)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        $Prefix_Field_Type = array(
            -155 => "datetimeoffset",
            -154 => "time",
            -152 => "xml",
            -151 => "udt",
            -11 => "uniqueidentifier",
            -10 => "ntext",
            -9 => "nvarchar",
            -8 => "nchar",
            -7 => "bit",
            -6 => "tinyint",
            -5 => "bigint",
            -4 => "image",
            -3 => "varbinary",
            -2 => "binary",
            -2 => "timestamp",
            -1 => "text",
            1 => "char",
            2 => "numeric",
            3 => "decimal",
            3 => "money",
            3 => "Smallmoney",
            4 => "int",
            5 => "smallint",
            6 => "float",
            7 => "real",
            12 => "varchar",
            91 => "date",
            93 => "datetime",
            93 => "datetime2",
            93 => "smalldatetime");
        $DB_Field = sqlsrv_field_metadata($SQL_Resource);
        return $Prefix_Field_Type[$DB_Field[$SColumn]['Type']];
    }

    #----------------------------------------------------------------------#
    #Session of the Result Data
    #----------------------------------------------------------------------#

    #Return QueryID Cell Result : RETURN STRING
    public function RESULT($SQL_Resource, $Cols, $Rows)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        if (@sqlsrv_fetch($SQL_Resource, SQLSRV_SCROLL_ABSOLUTE, $Rows) === false)
        {
            return false;
        } else
        {
            return @sqlsrv_get_field($SQL_Resource, $Cols);
        }
    }

    #Return 1 row array(column) value with the Query result: RETURN ARRAY
    public function FETCH_ROW($SQL_Resource)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @sqlsrv_fetch_array($SQL_Resource, SQLSRV_FETCH_NUMERIC);
    }

    #Return 1 row array assoc(column) value with the Query result: RETURN OBJECT
    public function FETCH_ASSOC($SQL_Resource)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @sqlsrv_fetch_array($SQL_Resource, SQLSRV_FETCH_ASSOC);
    }

    #Return 1 row object(column) value with the Query result: RETURN OBJECT
    public function FETCH_OBJECT($SQL_Resource)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @sqlsrv_fetch_object($SQL_Resource);
    }

    #Return number of  row with the Query result : RETURN INTERGR
    public function NUM_ROWS($SQL_Resource)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @sqlsrv_num_rows($SQL_Resource);
    }

    #Return 1 cell result in row 0 col 0 without query refence.
    public function CRESULT($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return $this->RESULT($this->QUERY($this->Query_Format($SQLQuery, $NoChange)), 0,
            0);
    }

    #Return 1 row result without query refence.
    public function CFETCH_ROW($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return $this->FETCH_ROW($this->QUERY($this->Query_Format($SQLQuery, $NoChange)));
    }


    #Return 1 row assoc result without query refence.
    public function CFETCH_ASSOC($SQLQuery, $NoChange = false)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return $this->FETCH_ASSOC($this->QUERY($this->Query_Format($SQLQuery, $NoChange)));
    }

    #----------------------------------------------------------------------#
    #Session of the Error handeling
    #----------------------------------------------------------------------#

    #Return Mysql Last times Error Number : RETURN INTERGER
    public function ERRORNO()
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        $Errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
        return $Errors[count($Errors)]['code'];
    }

    #Return Mysql Last times Error : RETURN STRING
    public function ERROR($Format = null)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        $Errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
        return (!empty($Format) ? $this->SqlErrFormat($Errors[count($Errors)]['code']) :
            $Errors[count($Errors)]['code']);
    }

    #----------------------------------------------------------------------#
    #Session of the Close or free result
    #----------------------------------------------------------------------#

    #Free up the Query result : RETURN BOOLIN
    public function FREE_RESULT($SQL_Resource)
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        return @odbc_free_result($SQL_Resource);
    }

    #Close the connect without Persistent Links : RETURN BOOLIN
    public function SQLCLOSE()
    {
        if (extension_loaded("sqlsrv") == false)
        {
            return false;
        }
        @sqlsrv_close($this->DBConnect);
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