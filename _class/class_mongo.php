<?php

#-----------------------------------------------------------------------#
# PHP CLASS
# CLASS AUTHOR : LYK
# DESCRIPTION : Use the Class to Connect to Database
# DATABASE API : MYSQL
# DEVOLOPMENT DATE : 2007-08-15
#-----------------------------------------------------------------------#

class MongoCls
{
    private $MO;
    private $DB;
    private $RS;
    private $TC = 'table.counters';

    #----------------------------------------------------------------------#
    #Session of Connect or change user
    #----------------------------------------------------------------------#

    public function __construct($Host = 'localhost', $Port = 27017, $User = 'admin', $Pass = 'admin', $Database =
        'admin')
    {
        if (extension_loaded("mongo") === false)
            return false;
        if (!empty($Host) && !empty($Port) && !empty($User) && !empty($Pass) && !empty($Database))
            return $this->DBConnect($Host, $Port, $User, $Pass, $Database);
    }

    #Make a connection and store connect id : RETURN RESOURE LINK
    public function DBConnect($Host = 'localhost', $Port = 27017, $User = 'admin', $Pass = 'admin', $Database =
        'admin')
    {
        if (extension_loaded("mongo") === false)
            return false;
        $this->MO = new MongoClient("mongodb://".$this->SmartStr($Host).":".$this->SmartStr($Port), array(
            "connect" => true,
            "db" => $this->SmartStr($Database),
            "username" => $this->SmartStr($User),
            "password" => $this->SmartStr($Pass)));
        $this->DB = $this->MO->selectDB($this->SmartStr($Database));
        return $this->MO;
    }

    #----------------------------------------------------------------------#
    #Session of Mongo Variable Object
    #----------------------------------------------------------------------#

    public function DBVarObjID($ID = null)
    {
        try
        {
            return new MongoId($ID);
        }
        catch (MongoException $ex)
        {
            return new MongoId();
        }
    }

    public function DBVarRegEx($RegEx)
    {
        try
        {
            return new MongoRegex($RegEx);
        }
        catch (MongoException $ex)
        {
            return false;
        }
    }

    public function DBVarBin($Data)
    {
        try
        {
            return new MongoBinData($Data);
        }
        catch (MongoException $ex)
        {
            return false;
        }
    }

    #----------------------------------------------------------------------#
    #Session Mongo Operator
    #----------------------------------------------------------------------#

    #Select Database by the function : RETURN BOOLIN
    public function DBWhereOr($Array)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return array('$or' => $Array);
    }

    public function DBWhereXOr($Array)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return array('$nor' => $Array);
    }

    public function DBWhereAnd($Array)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return array('$and' => $Array);
    }

    public function DBWhereIn($Array)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return array('$in' => $Array);
    }

    public function DBWhereNIn($Array)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return array('$nin' => $Array);
    }

    public function DBWhereAll($Array)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return array('$all' => $Array);
    }

    #----------------------------------------------------------------------#
    #Select Datbase or Collection
    #----------------------------------------------------------------------#

    #Select Database by the function : RETURN BOOLIN
    public function DBGetDB($DName, $MO = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $MO = (!empty($MO) ? $MO : $this->MO);
        return $this->DB = $MO->selectDB($this->SmartStr($DName));
    }

    public function DBGetTB($TName, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $DB = (!empty($DB) ? $DB : $this->DB);
        return $this->TB = $DB->selectCollection($this->SmartStr($TName));
    }

    #----------------------------------------------------------------------#
    # Insert Session
    #----------------------------------------------------------------------#

    public function DBInsert($Table, $InsertCols, $Options = array('auto_increment' => true), $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $TB = $this->DBGetTB($Table);
        $Options = (array )$Options;
        if (array_key_exists('auto_increment', $Options) && !array_key_exists('_id', $InsertCols))
            $InsertCols['_id'] = $this->DBNextSeq($Table);
        unset($Options['auto_increment']);
        try
        {
            $Result = $TB->insert($InsertCols, $Options);
            return $Result['ok'];
        }
        catch (MongoCursorException $e)
        {
            var_dump($e);
            return false;
        }
    }

    #----------------------------------------------------------------------#
    # Insert Session
    #----------------------------------------------------------------------#

    public function DBUpdate($Table, $ID, $UpdateCols, $Type = 'set', $Options = array(), $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        if ((!is_array($UpdateCols) && !is_object($UpdateCols)) || empty($UpdateCols))
            return false;
        $TB = $this->DBGetTB($Table);
        $Where = array('_id' => $ID);
        try
        {
            $Result = $TB->update($Where, array('$'.$Type => $UpdateCols), $Options);
            return $Result['n'];
        }
        catch (MongoCursorException $e)
        {
            return false;
        }
    }

    public function DBUpdates($Table, $Where, $UpdateCols, $Type = 'set', $Options = array("multiple" => true),
        $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        if ((!is_array($UpdateCols) && !is_object($UpdateCols)) || empty($UpdateCols))
            return false;
        $TB = $this->DBGetTB($Table);
        try
        {
            $Result = $TB->update($Where, array('$'.$Type => $UpdateCols), $Options);
            return $Result['n'];
        }
        catch (MongoCursorException $e)
        {
            return false;
        }
    }

    #----------------------------------------------------------------------#
    # Query Session
    #----------------------------------------------------------------------#

    public function DBQuery($Table, $Where = null, $Columns = null, $Sort = null, $Limit = null, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $TB = $this->DBGetTB($Table, $DB);
        $Where = (array )$Where;
        $Columns = (array )$Columns;
        $ShwCols = array();
        if (!empty($Columns) && is_array($Columns))
            foreach ($Columns as $Col)
                $ShwCols[$Col] = 1;
        if (!empty($QueryCols) && !array_key_exists('_ID', $QueryCols))
            $QueryCols['_ID'] = 0;
        $this->RS = $TB->find($Where, $ShwCols);

        #Sorting Handle
        if (!empty($Sort))
        {
            $Sorted = array();
            if (is_array($Sort))
            {
                foreach ($Sort as $Key => $Value)
                {
                    if (is_string($Value))
                        $Sorted[$Value] = 1;
                    else
                    {
                        if (strtolower($Value) === 'asc')
                            $Sorted[$Key] = 1;
                        elseif (strtolower($Value) === 'desc')
                            $Sorted[$Key] = -1;
                        elseif (is_int($Value))
                        {
                            if ($Value > 0)
                                $Value = 1;
                            elseif ($Value < 0)
                                $Value = -1;
                            $Sorted[$Key] = $Value;
                        }
                    }
                }
            }
            else
                $Sorted[$Sort] = 1;
            $this->RS = $this->RS->sort($Sorted);
        }

        #Limit and Pagination Handle
        if (!empty($Limit))
        {
            if (is_array($Limit))
            {
                $Start = (int)reset($Limit);
                $SLimit = (int)end($Limit);
                if (count($Limit) >= 2 && !empty($SLimit))
                {
                    $this->RS = $this->RS->limit($SLimit)->skip($Start);
                }
                elseif (!empty($Start))
                    $this->RS = $this->RS->limit($Start);
            }
            elseif (is_int($Limit))
                $this->RS = $this->RS->limit((int)$Limit);
        }
        elseif (is_int($Limit) && !empty($Limit))
            $this->RS = $this->RS->limit((int)$Limit);
        return $this->RS;
    }

    public function DBResult($Table, $ID = null, $Where = null, $Columns = null, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $TB = $this->DBGetTB($Table, $DB);
        $QueryCols = array();
        if (!empty($Columns) && is_array($Columns))
            foreach ($Columns as $Col)
                $QueryCols[$Col] = 1;
        if (!empty($QueryCols) && !array_key_exists('_ID', $QueryCols))
            $QueryCols['_ID'] = 0;
        if (!empty($ID))
            $Where = array('_id' => $ID);
        return $TB->findOne($Where, $QueryCols);
    }

    public function DBCell($Table, $Column, $ID = null, $Where = null, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        if (empty($Column))
            return false;
        $TB = $this->DBGetTB($Table, $DB);
        $Columns = array((string )$Column => 1);
        if (!empty($ID))
            $Where = array('_id' => $ID);
        $Result = $TB->findOne($Where, $Columns);
        return $Result[$Column];
    }

    #----------------------------------------------------------------------#
    # Delete Session
    #----------------------------------------------------------------------#

    public function DBDelRow($Table, $ID = null, $Where = null, $Options = array("justOne" => false), $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $TB = $this->DBGetTB($Table, $DB);
        $Options = (array )$Options;
        if (!empty($ID))
            $Where = array('_id' => $ID);
        $Result = $TB->remove($Where, $Options);
        return $Result['n'];
    }

    #----------------------------------------------------------------------#
    # Session Number of Row (Query / Table)
    #----------------------------------------------------------------------#

    public function DBCount_RS($RS = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $RS = (!empty($RS) ? $RS : $this->RS);
        return $RS->count(true);
    }

    public function DBCount_TB($Table, $Where = null, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $TB = $this->DBGetTB($Table, $DB);
        return $TB->count($Where);
    }

    #----------------------------------------------------------------------#
    # Fetch Data
    #----------------------------------------------------------------------#

    public function DBFetch_All($RS = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $RS = (!empty($RS) ? $RS : $this->RS);
        return iterator_to_array($RS);
    }

    public function DBFetch_Assoc($RS = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $RS = (!empty($RS) ? $RS : $this->RS);
        return $RS->getNext();
    }

    public function DBFetch_Obj($RS = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $RS = (!empty($RS) ? $RS : $this->RS);
        return (object)$RS->getNext();
    }

    public function DBFetch_Row($RS = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $RS = (!empty($RS) ? $RS : $this->RS);
        return array_values($RS->getNext());
    }

    #----------------------------------------------------------------------#
    # Next Sequence (Auto Increasement)
    #----------------------------------------------------------------------#

    private function DBNextSeq($Name, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $DB = (!empty($DB) ? $DB : $this->DB);
        $Result = $DB->selectCollection($this->TC)->findAndModify(array('_id' => $Name), array('$inc' =>
                array('seq' => 1)), array('_id' => 0, 'seq' => 1), array('new' => true, 'upsert' => true));
        return $Result['seq'];
    }

    public function DBGetNextSeq($Name, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        return $this->DBCell($this->TC, 'seq', $Name, null, $DB);
    }

    #----------------------------------------------------------------------#
    # Reset Table
    #----------------------------------------------------------------------#

    public function DBTruncate($Name, $DB = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $DB = (!empty($DB) ? $DB : $this->DB);
        if ($this->DBDelRow($this->TC, $Name))
        {
            $Result = $DB->selectCollection($Name)->drop();
            return (boolean)$Result['ok'];
        }
        else
            return false;
    }

    #----------------------------------------------------------------------#
    # Free Result / Cursor
    #----------------------------------------------------------------------#

    public function DBFreeRs($RS = null)
    {
        if (extension_loaded("mongo") == false)
            return false;
        $RS = (!empty($RS) ? $RS : $this->RS);
        $RS->reset();
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
                    }
                    else
                        $StrCount .= substr($ERMSG, $iPos, 1);
            return "<span color=\"#000080\">".$StrCount."</span>";
        }
        else
            return "";
    }
}

?>