<?
// adHocInclude.php

//===================================================================================
// Today's date, formatted for the visible page
//===================================================================================
function dateNow()
{
  return date('l jS \of F Y h:i:s A');
}

//===================================================================================
// TraceShow puts data out as visible on the page, when cMyDebug is true
//===================================================================================
function traceShow($str)
{
  if (cMyDebug)
    echo $str."<BR>";
} 

//===================================================================================
// TraceHide puts data out as a comment in the page source, when cMyDebug is true
//===================================================================================
function traceHide($str)
{
  if (cMyDebug)
    echo "\n"."<!-- ".$str." -->"."\n";
} 

//===================================================================================
// Creates a connection and associated objects
//===================================================================================
function pdoConnect($hostname, $dbname, $username, $password)
{
  try
  {
    $db = new PDO("mysql:host=$hostname;dbname=".$dbname, $username, $password);
  }
  catch(PDOException $e)
  {
    die($e->getMessage());
  }
  return $db;
}

//===================================================================================
// Closes a connection and destroys associated objects
//===================================================================================
function pdoDisconnect($db)
{
  $db=null;
  return;
}

//===================================================================================
// Return sql query
//===================================================================================
function pdoQuery($sql, $dbh)
{
  try
  {
    //$stmt = $dbh->query($sql);
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
  }
  catch(PDOException $e)
  {
    die($e->getMessage());
  }
  return $stmt;
}

//===================================================================================
// Return recordset
//===================================================================================
function pdoFetch($stmt)
{
   try
   {
     $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   catch(PDOException $e)
   {
     die($e->getMessage());
   }   
   return $rows;
}

//===================================================================================
// Close recordset
//===================================================================================
function pdoClose($stmt)
{
   try
   {
     $stmt->closeCursor();
   }
   catch(PDOException $e)
   {
     die($e->getMessage());
   }   
   return;
}
//===================================================================================
// Return first row from a recordset
//===================================================================================
function pdoFirstRow($stmt)
{
  return $stmt->fetch();
}

//===================================================================================
//Get number of rows in recordset
//===================================================================================
function pdoRowCount($stmt)
{
  return $stmt->rowCount();
}

//===================================================================================
// Gets a field from recordset object rs in a usable state (defaults null to "")
//===================================================================================
function pdoData($row, $columnName)
{
  $x=($row[$columnName]);
  if (!isset($x))  // return null as empty string
    $x="";
  else
    $x=trim($x); 
  return $x;
}

//===================================================================================
//Gets the number of fields in the row
//===================================================================================
function pdoColumnCount($stmt)
{
  try
  {
    $columnCount=$stmt->columnCount();
  }
  catch(PDOException $e)
  {
    die($e->getMessage());
  }
  return $columnCount;
}

//===================================================================================
//Returns number of fields in a row (must be a better way than this)
//===================================================================================
function pdoFieldCount($stmt)
{
  return $udbStmt->columnCount();
}

//===================================================================================
// Returns true if strQuery is found to have parameter(s)
//===================================================================================
function queryHasParams($strQuery)
{
  return (count(explode(cParamDelimiter,$strQuery))>1);
} 

//===================================================================================
// Merges the raw query with parameters to produce an executable query
//===================================================================================
function buildSQLQuery($strQuery)
{
  $split=explode(cParamDelimiter,$strQuery);
  traceHide("split=".$split[0]."-".$split[1]."-". $split[2]);

  //Iterate though all the form or querystring inputs
  //looking for parameter values
  foreach ($_REQUEST as $key => $val)
    //test for a 'parameter' input
    for ($i=0; $i<count($split); $i++)
      if (strtolower(str_replace("_"," ",$key))==strtolower($split[$i]))
      {
        //replace this parameter with the given value
        $split[$i]=trim($val);
        //break; no - continue as there may be more substitutions to make
      }

  return (implode("", $split));
} 

//===================================================================================
// Builds a title string in HTML in the form "Parameter: Value"
//===================================================================================
function buildParamTitle($strQuery)
{
//see BuildSQLQuery function for explanation of code
  $splitParamStrArray=explode(cParamDelimiter,$strQuery);
  $str="";

  foreach ($_REQUEST as $key => $val)
    for ($i=0; $i<count($splitParamStrArray); $i++)
      if ($key==$splitParamStrArray[$i])
        if ((strpos($str,$key) ? strpos($str,$key)+1 : 0)==0)
          //replace this parameter with the given value
          $str=$str."<span class=\"ahTitle\">".$splitParamStrArray[$i].": ".trim($val)."</span><br />";

  return $str;
} 

//===================================================================================
// Returns any queryString data in the form "attr1=value1&attr2=value2..."
// Note that "menunum" and "linenum" variables are ignored
//===================================================================================
function queryStringData($exclusion)
{
  $str="";

  foreach ($_REQUEST as $key => $val)
  {
    if (strlen($exclusion)>0)
      $i=(strpos($key,$exclusion) ? strpos($key,$exclusion)+1 : 0);
    else
      $i=0;

    if ($key!="menunum" && $key!="linenum" && $i==0)
      $str=$str."&".$key."=".trim($val);
  }
  return $str;
} 

//===================================================================================
// Returns any queryString names (not values) in the form "attr1"
// Note that "menunum" and "linenum" names are ignored
//===================================================================================
function getName($n)
{
  $str="";
  $i=0;
  foreach ($_REQUEST as $key => $val)
  {
    if ($key!="menunum" && $key!="linenum" && $i==$n)
      $str=$key;
    if ($i==$n)
      break;
    $i++;
  }
  return $str;
} 

//===================================================================================
// Return the value of a request name, or "" if not found
//===================================================================================
function requestValue($requestName)
{
  $s=str_replace(" ","_",$requestName);
  foreach($_REQUEST as $key => $val)
    if ($key==$s)
      return $val;

  return "";
}

//===================================================================================
// Return true if request name found
//===================================================================================
function requestPresent($requestName)
{
  $s=str_replace(" ","_",$requestName);
  foreach($_REQUEST as $key => $val)
    if ($key==$s)
      return true;

  return false;
}

//===================================================================================
// Returns true if request object has all required values to satisfy $where params
//===================================================================================
function hasAllParams($where)
{
  // elements 1, 3, 5 etc will hold the request names
  $split=explode(cParamDelimiter,$where);
  for($i=1;$i<count($split);$i+=2)
  {
    traceHide("check for requestName=".$split[$i]);
    if (!requestPresent($split[$i]))
    {
      traceHide("can't find requestName=".$split[$i]);
      return false;
    }
  }
  traceHide("has all params");
  return true;
}

//===================================================================================
// Merge subquery into master query
//===================================================================================
function mergeSQLQuery($masterQuery,$subQuery)
{
  return str_replace("subQuery",$subQuery,$masterQuery);
} 

//===================================================================================
// Load storage array with list of total fields required
//===================================================================================
function loadTotalFields($fieldList)
{
  //Replace spaces with <empty>
  $str=str_replace(" ","",$fieldList);
  traceHide("fieldList=".$str);

  //Split into an array using comma as delimiter
  $totalFieldName=explode(",",$str);
  return $totalFieldName;
} 

//===================================================================================
// Return the offset of the field name within the passed array
//===================================================================================
function registerTotalFieldName($storageArray,$offsetArray,$fieldName,$fieldNameOffset)
{
  for ($i=0; $i<count($storageArray); $i++)
  {
    traceHide("storageArray[".$i."]=".$storageArray[$i]);
    if ($storageArray[$i]==$fieldName)
    {
      $offsetArray[$i]=$fieldNameOffset;
      break;
    } 
  }

  return $offsetArray;
} 

//===================================================================================
// Increments the total value indexed through the offset array
//===================================================================================
function incrementTotalField($valueArray,$offsetArray,$iColumnOffset,$dataItem)
{
  $b=false;

  if ($dataItem!="")
    for ($i=0; $i<count($offsetArray); $i++)
      if ($offsetArray[$i]==$iColumnOffset)
      {
        $valueArray[$i]=$valueArray[$i]+$dataItem;
        break;
      } 

  return $valueArray;
} 

//===================================================================================
// Return to accumulated value for the column (a non-total column gets "-----")
//===================================================================================
function totalForColumn($valueArray,$offsetArray,$iColumnOffset)
{
  $dataItem="-----";

  for ($i=0; $i<count($offsetArray); $i++)
    if ($offsetArray[$i]==$iColumnOffset)
    {
      $dataItem=$valueArray[$i];
      break;
    } 

  return $dataItem;
} 

//===================================================================================
// Load the link field info for use in creating links to related queries
//===================================================================================
function loadLinks($dbAdHoc)
{
  $sql = "SELECT q.link_field, m.menu_num, m.line_num FROM queries q".
         " JOIN menus m ON q.query_num = m.main_query_num";
  $stmt = pdoQuery($sql,$dbAdHoc);
  return pdoFetch($stmt);
}

//===================================================================================
// Construct hyperlink for query result data item
//===================================================================================
function getLinkData($gblRows, $field, $data)
{
  foreach($gblRows as $row)
  {
    if(strtolower($row["link_field"])==strtolower($field))
    {
     $linkHTML="<A class=\"ah\" HREF=\"adHocQuery.php?menunum=".$row["menu_num"]."&linenum=".$row["line_num"].
				queryStringData($field)."&".$field."=".$data."\">".$data."</A>";
      return $linkHTML;
    }
  }

  switch($field)
  {
    case "email":
      $linkHTML="<A class=\"ah\" HREF=mailto:".$data." TARGET=_new>".$data."</A>";
      break;

    case "url":
    case "website":
      $linkHTML="<A class=\"ah\" HREF=".$data." TARGET=_new>".$data."</A>";
      break;

    default:
      $linkHTML=$data;
      break;
  }

  return $data;
}

//===================================================================================
// Convert a date from a request parameter form (yyyymmdd) to "dd/mm/yyyy"
//===================================================================================
function formatDate($yyyymmdd)
{
  return substr($yyyymmdd,6,2).cDateSeparator
        .substr($yyyymmdd,4,2).cDateSeparator
        .substr($yyyymmdd,0,4);
}

//===================================================================================
// Convert an entered date in the form dd/mm/yy or dd/mm/yyyy to a request
// parameter form (yyyymmdd). yy > 50 = 19nn, yy <= 50 = 20nn
//===================================================================================
function unformatDate($dd_mm_yyyy)
{
  $invalidDate="19000101";
  $dateParts=explode(cDateSeparator,$dd_mm_yyyy);
  if (count($dateParts) < 3)
    return $invalidDate;
  if (strlen($datePart[2])==2)
    if ($datePart[2]<=50)
      $datePart[2]+=2000;
    else
      $datePart[2]+=1900;
  return $datePart[2].$datePart[1].$datePart[0];
}

//===================================================================================
// Add new line characters to make editing a query easier
//===================================================================================
function formatSql($sql)
{
  $keyword1 = array(" from"," left join"," inner join"," join"," where"," order");
  $keyword2 = array("\nfrom","\nleft join","\ninner join","\njoin","\nwhere","\norder");
  return str_replace($keyword1, $keyword2, $sql);
}

//===================================================================================
// Remove new line characters (replacing with a space) from the edited query
//===================================================================================
function unformatSql($sql)
{
  return str_replace("\n", " ", $sql);
}

//===================================================================================
// Allow for single quotes in SQL literal
//===================================================================================
function sqlLiteral($lit)
{
  return "'".str_replace("'", "\'", $lit)."'";
}
?>

