<?php

#Create by LYK on 2011-08-12 @ 16:34
class SQL_Data extends HObj
{
    public $Own_Standard = true;

    public function DBAutoCommit($Auto = false)
    {
        if ($Auto === (boolean)false)
            $Config = '0';
        else
            $Config = '1';
        $this->Query("SET AUTOCOMMIT=0");
    }

    public function DBTranStart()
    {
        $this->Query("START TRANSACTION");
    }

    public function DBTranEnd()
    {
        #$this->Query("END TRANSACTION");
    }

    public function DBRollBack()
    {
        $this->Query("ROLLBACK");
    }

    public function DBCommit()
    {
        $this->Query("COMMIT");
    }

    private function DBGetColSQL($DBColumn, $Alias = true)
    {
        $DBFNC = SysConvVar("{DB_FNC}");
        if (is_array($DBColumn) && !empty($DBColumn))
        {
            foreach ($DBColumn as $ColAlies => $ColName)
            {
                $ColName = SysConvVar($ColName);
                $SQLCol .= (!empty($SQLCol) ? ", " : "") . ((substr($ColName, 0, strlen($DBFNC)) ===
                    $DBFNC) ? str_replace($DBFNC, "", $ColName) : "`" . $this->Escape_String($ColName) .
                    "`") . (!is_int($ColAlies) && $Alias ? " AS '" . $this->Escape_String($ColAlies) .
                    "'" : ((substr($ColName, 0, strlen($DBFNC)) !== $DBFNC) && $Alias ? " AS '" . $this->
                    Escape_String($ColName) . "'" : ""));
            }
        }
        elseif (!empty($DBColumn) && $DBColumn === "*")
            $SQLCol = "*";
        elseif (!empty($DBColumn) && is_string($DBColumn))
        {
            $SQLCol = SysConvVar($DBColumn);
            $SQLCol = ((substr($SQLCol, 0, strlen($DBFNC)) == $DBFNC) ? str_replace($DBFNC, "", $SQLCol) :
                "`" . $this->Escape_String($SQLCol) . "`");
        }
        else
            $SQLCol = "";
        return $SQLCol;
    }

    private function DBSelectSQL($DBTable, $DBColumn = "", $DBID = null, $DBContition = null, $DBOrder = null,
        $DBLimit = null, $DBGroup = null, $DBHaving = null)
    {
        if (!empty($DBTable))
        {
            $DBFNC = SysConvVar("{DB_FNC}");
            $DBTable = SysConvVar($DBTable);
            $SQLCol = $this->DBGetColSQL($DBColumn);
            $SQLGroup = $this->DBGetColSQL($DBGroup);

            $SQLOrder = '';
            if (!empty($DBOrder) && is_array($DBOrder))
            {
                foreach ($DBOrder as $Order => $OType)
                {
                    if (is_int($Order))
                    {
                        $Order = $OType;
                        $OType = "ASC";
                    }
                    else
                    {
                        $OType = strtoupper($OType);
                        if ($OType != "DESC")
                            $OType = "ASC";
                    }
                    $Order = $this->DBGetColSQL($Order);
                    $SQLOrder .= (!empty($SQLOrder) ? ", " : "") . "{$Order} {$OType}";
                }
            }
            elseif (!empty($DBOrder))
            {
                $SQLOrder = $this->DBGetColSQL($DBOrder) . " ASC";
            }

            switch (gettype($DBID))
            {
                case 'double':
                case 'integer':
                    if ($DBID < 0)
                        $DBContition = "`ID`!='" . abs($DBID) . "'";
                    else
                        $DBContition = "`ID`='{$DBID}'";
                    break;
                case "string":
                    $DBContition = "`ID`='" . $this->Escape_String($DBID) . "'";
                    break;
                default:
            }
            if (!empty($DBContition))
                $DBContition = " WHERE {$DBContition}";

            return "SELECT {$SQLCol} FROM `{$DBTable}`{$DBContition}" . (!empty($DBGroup) ?
                " GROUP BY {$DBGroup}" . (!empty($DBHaving) ? " {$DBHaving}" : "") : "") . (!empty($SQLOrder) ?
                " ORDER BY {$SQLOrder}" : "") . (!empty($DBLimit) ? " LIMIT {$DBLimit}" : "");
        }
        else
            return "";
    }

    public function DBSelect($DBTable, $DBColumn = "", $DBID = null, $DBContition = null, $DBOrder = null,
        $DBLimit = null, $DBGroup = null, $DBHaving = null)
    {
        return $this->Query($this->DBSelectSQL($DBTable, $DBColumn, $DBID, $DBContition, $DBOrder, $DBLimit,
            $DBGroup, $DBHaving));
    }

    public function DBRow($DBTable, $DBColumn = "*", $DBID = null, $DBContition = null, $DBOrder = null,
        $DBStart = 0)
    {
        if (!empty($DBTable))
        {
            $DBStart = (!empty($DBStart) ? (int)$DBStart : 0);
            return $this->Fetch_Row($this->DBSelect($DBTable, $DBColumn, $DBID, $DBContition, $DBOrder,
                "{$DBStart}, 1"));
        }
        else
            return false;
    }

    public function DBAssoc($DBTable, $DBColumn = "*", $DBID = null, $DBContition = null, $DBOrder = null)
    {
        if (!empty($DBTable))
            return $this->Fetch_Assoc($this->DBSelect($DBTable, $DBColumn, $DBID, $DBContition, $DBOrder,
                1));
        else
            return false;
    }

    public function DBObject($DBTable, $DBColumn = "*", $DBID = null, $DBContition = null, $DBOrder = null)
    {
        if (!empty($DBTable))
            return $this->Fetch_Object($this->DBSelect($DBTable, $DBColumn, $DBID, $DBContition, $DBOrder,
                1));
        else
            return false;
    }

    public function DBCol($DBTable, $DBColumn, $DBID = null, $DBContition = null, $DBOrder = null, $DBLimit = null,
        $DBGroup = null, $DBHaving = null)
    {
        if (!empty($DBTable) && !empty($DBColumn))
        {
            if (is_array($DBColumn))
                $DBColumn = reset($DBColumn);
            $QList = $this->DBSelect($DBTable, $DBColumn, $DBID, $DBContition, $DBOrder, $DBLimit, $DBGroup,
                $DBHaving);
            if (!$QList)
            {
                return false;
            }
            else
            {
                $ColData = array();
                while (list($Data) = $this->Fetch_Row($QList))
                    $ColData[] = $Data;
                $this->Free_Result($QList);
                return $ColData;
            }
        }
        else
            return false;
    }

    public function DBCell($DBTable, $DBColumn, $DBID = null, $DBContition = null, $DBOrder = null,
        $DBStart = 0)
    {
        if (!empty($DBTable) && !empty($DBColumn))
        {
            $DBStart = (!empty($DBStart) ? (int)$DBStart : 0);
            return $this->CResult($this->DBSelectSQL($DBTable, $DBColumn, $DBID, $DBContition, $DBOrder,
                "{$DBStart}, 1"));
        }
        else
            return false;
    }

    public function DBCount($DBTable, $DBContition = null, $DBID = null, $ColName = null)
    {
        if (!empty($DBTable))
        {
            $DBColumn = "{DB_FNC}COUNT(" . (!empty($ColName) ? "`{$ColName}`" : "*") . ")";
            return (int)$this->CResult($this->DBSelectSQL($DBTable, $DBColumn, $DBID, $DBContition, null,
                1));
        }
        else
            return false;
    }

    public function DBSum($DBTable, $ColName, $DBContition = null, $DBID = null)
    {
        if (!empty($DBTable))
        {
            $DBColumn = "{DB_FNC}SUM(" . (!empty($ColName) ? "`{$ColName}`" : "*") . ")";
            return (double)$this->CResult($this->DBSelectSQL($DBTable, $DBColumn, $DBID, $DBContition, null,
                1));
        }
        else
            return false;
    }

    public function DBInsert($DBTable, $DBColData)
    {
        if (!empty($DBTable) && is_array($DBColData) && !empty($DBColData))
        {
            $DBFNC = SysConvVar("{DB_FNC}");
            if ($this->Own_Standard === true)
            {
                $DBTime = SysConvVar("{DB_FNC}{DB_NOW}");
                $DBColData['ADD_TIME'] = $DBTime;
                $DBColData['CHG_TIME'] = $DBTime;
            }
            $DBTable = SysConvVar($DBTable);
            $IColName = '';
            $IColData = '';
            foreach ($DBColData as $ColName => $ColData)
            {
                $ColData = SysConvVar($ColData);
                $IColName .= (!empty($IColName) ? ", " : "") . "`{$ColName}`";
                $IColData .= (!empty($IColData) ? ", " : "") . ((substr($ColData, 0, strlen($DBFNC)) ==
                    $DBFNC) ? str_replace($DBFNC, "", $ColData) : "'" . $this->Escape_String($ColData) .
                    "'");
            }
            return $this->Query("INSERT INTO `{$DBTable}`({$IColName}) VALUES({$IColData})", false);
        }
        else
            return false;
    }

    public function DBUpdate($DBTable, $DBColData, $DBID = null, $DBContition = null)
    {
        if (!empty($DBTable) && is_array($DBColData) && !empty($DBColData))
        {
            $DBFNC = SysConvVar("{DB_FNC}");
            if ($this->Own_Standard === true)
                $DBColData['CHG_TIME'] = SysConvVar("{DB_FNC}{DB_NOW}");
            $DBTable = SysConvVar($DBTable);
            $UpdateCol = '';
            foreach ($DBColData as $ColName => $ColData)
            {
                $ColData = SysConvVar($ColData);
                $UpdateCol .= (!empty($UpdateCol) ? ", " : "") . "`{$ColName}`=" . ((substr($ColData,
                    0, strlen($DBFNC)) == $DBFNC) ? str_replace($DBFNC, "", $ColData) : "'" . $this->
                    Escape_String($ColData) . "'");
            }
            switch (gettype($DBID))
            {
                case 'double':
                case 'integer':
                    if ($DBID < 0)
                        $DBContition = "`ID`!='" . abs($DBID) . "'";
                    else
                        $DBContition = "`ID`='{$DBID}'";
                    break;
                case "string":
                    $DBContition = "`ID`='" . $this->Escape_String($DBID) . "'";
                    break;
                default:
            }
            if (!empty($DBContition))
                $DBContition = " WHERE {$DBContition}";
            return $this->Query("UPDATE `{$DBTable}` SET {$UpdateCol}{$DBContition}", false);
        }
        else
            return false;
    }

    public function DBAction($DBTable, $DBColData, $DBID = 0, $DBCheck = false)
    {
        if (!empty($DBID) && $DBCheck === (boolean)true)
            if ($this->DBCount($DBTable, "`ID`='{$DBID}'") === (int)0)
                $DBID = 0;
        if (!empty($DBID))
            return $this->DBUpdate($DBTable, $DBColData, $DBID);
        else
            return $this->DBInsert($DBTable, $DBColData);
    }

    public function DBDelete($DBTable, $DBID = null, $DBContition = null)
    {
        if (!empty($DBTable))
        {
            $DBID = (int)$DBID;
            $DBTable = $this->Escape_String(SysConvVar($DBTable));
            switch (gettype($DBID))
            {
                case 'double':
                case 'integer':
                    if ($DBID < 0)
                        $DBContition = "`ID`!='" . abs($DBID) . "'";
                    else
                        $DBContition = "`ID`='{$DBID}'";
                    break;
                case "string":
                    $DBContition = "`ID`='" . $this->Escape_String($DBID) . "'";
                    break;
                default:
            }
            if (!empty($DBContition))
                $DBContition = " WHERE {$DBContition}";
            return $this->Query("DELETE FROM `{$DBTable}`{$DBContition}");
        }
        else
            return false;
    }

    public function DBProcedure($DBPName, $DBParameter = null, $Fetch_All = false)
    {
        if (!empty($DBPName))
        {
            $DBPName = $this->Escape_String(SysConvVar($DBPName));
            if (!empty($DBParameter))
            {
                foreach ($DBParameter as $Key => $PValue)
                    $DBParameter[$Key] = $this->Escape_String(SysConvVar($PValue));
                $SP_Result = $this->Query("CALL {$DBPName}('" . implode("','", $DBParameter) . "');");
            }
            else
                $SP_Result = $this->Query("CALL {$DBPName}();");
            $Data = array();
            if ($Fetch_All)
            {
                do
                {
                    if ($Result = $this->Store_ResultSet())
                    {
                        $Data_Now = array();
                        while ($Row = $this->Fetch_Row($Result))
                            $Data_Now[] = $Row;
                        $Data[] = $Data_Now;
                        $this->Free_Result($Result);
                    }
                } while ($this->Next_ResultSet());
            }
            else
            {
                while ($Row = $this->Fetch_Row($Result))
                    $Data[] = $Row;
                $this->Clear_ResultSet();
            }
            return $Data;
        }
        else
            return false;
    }

    public function DBCopy($DBTbScr, $DBTbDes, $DBColNm, $DBID = 0, $DBContition = null, $DBOrder = null,
        $DBLimit = null, $DBGroup = null, $DBHaving = null)
    {
        if (!empty($DBTbScr) && !empty($DBTbDes) && !empty($DBColNm) && is_array($DBColNm))
        {
            if ($this->Own_Standard === true)
            {
                if (!array_key_exists("CHG_TIME", $DBColNm))
                    $DBColNm['CHG_TIME'] = "{DB_FNC}{DB_NOW}";
                if (!array_key_exists("ADD_TIME", $DBColNm))
                    $DBColNm['ADD_TIME'] = "{DB_FNC}{DB_NOW}";
            }
            $DBTbScr = SysConvVar($DBTbScr);
            $DBTbDes = SysConvVar($DBTbDes);
            $ColNmSrc = array();
            $ColNmDes = array();
            foreach ($DBColNm as $_ColNmSrc => $_ColNmDes)
            {
                $ColNmSrc[] = $_ColNmSrc;
                $ColNmDes[] = $_ColNmDes;
            }
            $DBColNmSrc = $this->DBGetColSQL($ColNmSrc, false);
            return $this->Query("INSERT INTO `{$DBTbDes}`({$DBColNmSrc}) " . $this->DBSelectSQL($DBTbScr,
                $ColNmDes, $DBID, $DBContition, $DBOrder, $DBLimit, $DBGroup, $DBHaving), false);
        }
        else
            return false;
    }

    public function DBLimit_Pagination($Pg_Count, $Total_Count, $Pg_Now)
    {
        $Pg_Count = (int)$Pg_Count;
        $Total_Count = (int)$Total_Count;
        $Pg_Now = (int)$Pg_Now;
        if ($Total_Count > $Pg_Count && !empty($Pg_Count))
        {
            $Pg_Total = (($Total_Count - ($Total_Count % $Pg_Count)) / $Pg_Count);
            $Pg_Total += (($Total_Count % $Pg_Count > 0) ? (int)1 : (int)0);
            if ($Pg_Now > $Pg_Total)
                $Pg_Now = $Pg_Total;
            $Count_Begin = $Pg_Now * $Pg_Count;
            return "{$Count_Begin}, {$Pg_Count}";
        }
    }

    public function DBCResultAll($QLst)
    {
        if (is_string($QLst))
            $QLst = $this->DBSelect($QLst, "*");
        if (!is_object($QLst))
            return false;
        $Fields = array();
        $Rows = array();
        if ($this->Num_Rows($QLst))
        {
            for ($i = 0; $i < $this->Num_Fields($QLst); $i++)
            {
                $Field = $this->Fetch_Field($i, $QLst);
                if ($Field->name === $Field->orgname)
                {
                    $FName = $this->Column_Comment($Field->orgname, $Field->orgtable);
                    $FName = !empty($FName) ? $FName : $Field->name;
                }
                else
                    $FName = $Field->name;
                $Fields[] = $FName;
            }
            while ($Row = $this->Fetch_Row($QLst))
                $Rows[] = $Row;
        }
        $this->Free_Result($QLst);
        return array("field" => $Fields, "rows" => $Rows);
    }

    #Parser global variable, and return to private variable
    function Log_Infor($Data)
    {
        global $_SERVER;
        $this->DBInsert("{STB_LOG}", array(
            'IP' => '{IP_CLIENT}',
            'Log' => $Data,
            "Source" => basename($_SERVER['SCRIPT_NAME'])));
    }

    public function GetConfig($SName, $APP = "SYSTEM")
    {
        global $__SysConfig;
        $APP = strtoupper($APP);
        $SName = strtoupper($SName);
        if (array_key_exists($APP, $__SysConfig) && array_key_exists($SName, $__SysConfig[$APP]) &&
            array_key_exists('DATA', $__SysConfig[$APP][$SName]))
            return $__SysConfig[$APP][$SName]['DATA'];
        else
            return false;
    }

    public function IsValid_Email($Email, $Chk_Domain = false)
    {
        if (!preg_match("/^[^@]*@[^@]*\.[^@]*$/", $Email))
        {
            return false;
        }
        elseif ($Chk_Domain === (boolean)true)
        {
            list($Username, $Domain) = split('@', $Email);
            if (!checkdnsrr($Domain, 'MX'))
                return false;
            else
                return true;
        }
        else
            return true;
    }

    public function IsValid_IP($IPAddress)
    {
        if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $IPAddress))
            return false;
        else
            return true;
    }

    public function IsValid_Password($Candidate, $Min_Length = 6, $Max_Length = 32, $Must_Alphabet = false,
        $Must_Numeric = false, $No_Special_Letter = true, $Must_Lower = false, $Must_Upper = false)
    {
        $Pass_Regx['Valid'] = '`[A-Za-z0-9@!#\$\^%&*()+=\-\[\]\\\';,\.\/\{\}\|\":<>\? ]`'; #All Valid Keyword
        $Pass_Regx['Special_Letter'] = '`[@!#\$\^%&*()+=\-\[\]\\\';,\.\/\{\}\|\":<>\? ]`'; #whatever you mean by 'special char'
        $Pass_Regx['Alphabet'] = '`[A-Za-z]`'; #Alphabet
        $Pass_Regx['Lower'] = '`[a-z]`'; #Lowercase
        $Pass_Regx['Upper'] = '`[A-Z]`'; #Uppercase
        $Pass_Regx['Numeric'] = '`[0-9]`'; //Numeric
        $Output = '';

        if (!preg_match($Pass_Regx['Valid'], $Candidate))
            return false;
        if ($Must_Alphabet === true && !preg_match($Pass_Regx['Alphabet'], $Candidate))
            return false;
        if ($Must_Numeric === true && !preg_match($Pass_Regx['Numeric'], $Candidate))
            return false;
        if ($No_Special_Letter === true && preg_match($Pass_Regx['Special_Letter'], $Candidate))
            return false;
        if ($Must_Lower === true && !preg_match($Pass_Regx['Lower'], $Candidate))
            return false;
        if ($Must_Upper === true && !preg_match($Pass_Regx['Upper'], $Candidate))
            return false;
        if (strlen($Candidate) < $Min_Length)
            return false;
        if (strlen($Candidate) > $Max_Length)
            return false;
        return true;
    }

    public function ExportCsv($Array, $Header = null, $Output = 'd', $FName = null)
    {
        if (count($Array) == 0)
            return;
        function RowCSV($RowAry)
        {
            $CSVStr = '';
            foreach ($RowAry as $Field)
                $CSVStr .= (!empty($CSVStr) ? "," : "") . "\"" . str_replace("\"", "\\\"", $Field) .
                    "\"";
            return "{$CSVStr}\r\n";
        }
        $Write_Header = true;
        switch ($Output)
        {
            case 'd':
                Download_Headers($FName);

            case 'o':
                $DF = fopen("php://output", 'w');
                break;

            case 'f':
                $DF = fopen($FName, 'w');
                break;

            case 'fa':
                if (file_exists($FName) === true)
                    $Write_Header = false;
                $DF = fopen($FName, 'a');
                break;

            case 'r':
                $DF = fopen('php://temp', 'r+');
                break;
        }

        if (empty($Header))
            $Header = array_keys(reset($Array));

        if (count($Header) > 0 && $Write_Header === true)
        {
            $CSV = RowCSV($Header);
            fwrite($DF, $CSV, strlen($CSV));
        }
        foreach ($Array as $Row)
        {
            $CSV = RowCSV($Row);
            fwrite($DF, $CSV, strlen($CSV));
        }
        if ($Output === 'r')
        {
            rewind($DF);
            $Data = fread($DF, 1048576);
            fclose($DF);
            return rtrim($Data, "\n");
        }
        fclose($DF);
    }

    public function DBExportCsv($QLst, $Output = 'd', $FName = null)
    {
        $Result = $this->DBCResultAll($QLst);
        $this->ExportCsv($Result['rows'], $Result['field'], $Output, $FName);
    }

    public function InArray_Like($Search, $Array)
    {
        foreach ($Array as $Ref)
            if (stripos($Search, $Ref))
                return true;
        return false;
    }
}

?>