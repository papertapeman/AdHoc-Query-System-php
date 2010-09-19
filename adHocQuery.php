<?php

require_once("adHocConst.php");
require_once("adHocInclude.php");

//adHocQuery.php
//===================================================================================
//This section retrieves the menu record
$connAdHoc=pdoConnect(cAdHocServer, cAdHocDatabase, cAdHocUsername, cAdHocPassword);

// load a recordset for links to create in the following query results
$gblRows=loadLinks($connAdHoc);

traceHide("Passed keys / value");
foreach ($_REQUEST as $key => $val)
  traceHide("key:".$key." / val:".$val);

$curMenu=$_REQUEST["menunum"];
$curLine=$_REQUEST["linenum"];

$sql=" SELECT q1.query_num, title, q1.heading_text AS heading, q1.total_fields,".
	"q1.link_field, q1.select_stmt AS detail_select,".
        "q1.pre_select,".
	"q2.select_stmt AS title_select,".
    " udb_name, udb_server, udb_username, udb_password".
    " FROM menus m".
    " JOIN queries q1 ON m.main_query_num = q1.query_num".
    " LEFT JOIN queries q2 ON m.title_query_num = q2.query_num".
    " LEFT JOIN userdbs AS d1 ON d1.udb_num = q1.udb_num".
    " WHERE m.menu_num = ".$curMenu." AND m.line_num = ".$curLine;

traceHide("main query=".$sql);

//===================================================================================
//Get the menu record pertaining to this query
$adhocStmt = pdoQuery($sql,$connAdHoc);
$row = pdoFirstRow($adhocStmt);

$title=pdoData($row,"title");
$heading=pdoData($row,"heading");

//Analyse totalling required
$totalFieldList=pdoData($row,"total_fields");
$totalFieldName=null;

$totalFieldName=loadTotalFields($totalFieldList);
$hasTotalFields=(strlen($totalFieldName[0])>0);
traceHide("hasTotalFields=".$hasTotalFields);
if ($hasTotalFields)
  for ($i=0; $i<count($totalFieldName); $i++)
  {
    traceHide("TotalFieldName=",$totalFieldName);
    $totalFieldOffset[$i]=-1.0;
    $totalFieldValue[$i]=0.0;
  }

$sideLabels=(strlen(pdoData($row,"link_field"))>0);

$preSelect=pdoData($row,"pre_select");
TraceHide("pre_select query=".$preSelect);

$detailSelect=pdoData($row,"detail_select");
traceHide("Raw detailSelect=".$detailSelect);

$titleSelect=pdoData($row,"title_select");
traceHide("Raw titleSelect=".$titleSelect);

//connect to the target database
$udb_server=pdoData($row,"udb_server");
$udb_name=pdoData($row,"udb_name");
$udb_username=pdoData($row,"udb_username");
$udb_password=pdoData($row,"udb_password");

$thisQuery=pdoData($row,"query_num");

//===================================================================================
//Get today's date
$pageDate=dateNow();
?>
<HTML>
<HEAD>
<? echo cHeaderComment;?>
<TITLE><? echo $title;?></TITLE>
<LINK REL="stylesheet" HREF=<? echo cStylesheet;?> TYPE="text/css" />
</HEAD>
<BODY BGCOLOR="#000000">
<? 
//===================================================================================
//Display the Menu No / Line No / Query No on the web page
?>
<!-- Header table -->
<TABLE BORDER="0" WIDTH="100%" CELLPADDING="0" CELLSPACING="0">
<TR><TD WIDTH="3">&nbsp;</TD><TD>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR>
  <TD><P CLASS="title">Ad Hoc Query <? echo $curMenu;?> / <? echo $curLine;?> / <? echo $thisQuery;?></P></TD>
  <TD><P CLASS="date"><? echo $pageDate;?></P></TD>
</TR></TABLE>
<CENTER>
<IMG SRC="<? echo cHeaderImagePath;?>" ALT="" BORDER="0" WIDTH="299" HEIGHT="95"></A>
<BR><BR>
</CENTER>
</TD>
</TR></TABLE>
<!-- Header table -->
<? 
//===================================================================================
//Identify the type of query
if ((strpos($detailSelect,"sp_") ? strpos($detailSelect,"sp_")+1 : 0)>0
 || (strpos($detailSelect,"xp_") ? strpos($detailSelect,"xp_")+1 : 0)>0)
   $isStoredProcedure=true;
else
   $isStoredProcedure=false;

//Connect to the the user database
$conn_udb=pdoConnect($udb_server, $udb_name, $udb_username, $udb_password);
$paramStr="";
$paramTitle="";
//===================================================================================
//This section constructs and runs a title query if necessary
if (queryHasParams($titleSelect))
{
  $paramStr=buildParamTitle($titleSelect);
  traceHide("paramStr=".$paramStr);

  $titleSelect=buildSQLQuery($titleSelect);
  traceHide("titleSelect=".$titleSelect);

  $udbStmt = pdoQuery($titleSelect,$conn_udb);
  $udbRows = pdoFetch($udbStmt);

  $paramTitle="<BR>";
  foreach ($udbRows as $row)
  {
    foreach($row as $fieldName=>$dataItem)
    {
      $paramTitle=$paramTitle."<span class=\"ahTitle\">".$fieldName.": ".$dataItem."</span><br />";
    }
   break;
  }
} 

//===================================================================================
//This section constructs and runs the preselect query if necessary.
//do this before building the detail query, so that query string
//variables get inserted into this as well
if (queryHasParams($preSelect))
{
  $preSelect=buildSQLQuery($preSelect);
  traceHide("preSelect=".$preSelect);

  $udbResult = pdoQuery($preSelect,$conn_udb);
  $row = pdoFirstRow($udbResult);

  $subQuery=pdoData($row,"sub_query");
  traceHide("sub_query=".$subQuery);

  $detailSelect=mergeSQLQuery($detailSelect,$subQuery);
} 

//===================================================================================
//This section constructs the detail query if necessary
if (queryHasParams($detailSelect))
{
  $paramStr=buildParamTitle($detailSelect);
  traceHide("queryParamTitle=".$paramStr);

  traceHide("before builtSQLQuery=".$detailSelect);
  $detailSelect=buildSQLQuery($detailSelect);
  traceHide("after builtSQLQuery=".$detailSelect);
}
else
  traceHide("query has no parameters");

//===================================================================================
//This section runs the constructed detail query
//Different handling for stored procedures and conventional SQL
if ($isStoredProcedure)
{
  //**FIX**
  // $aCmd is of type "ADODB.Command"
  //$aCmd_ActiveConnection=;
  //$aCmd_CommandText=$detailSelect;  
  //$aCmd_CommandType=$adCmdText;  
  //$row=  $aCmd_Execute=);;
}
else
{
  $udbStmt = pdoQuery($detailSelect,$conn_udb);
  $udbRows = pdoFetch($udbStmt);
} 

$numRecords=pdoRowCount($udbStmt);

//===================================================================================
//This section gets the original query string to pass through when linking to
//another query
$passThruData=queryStringData("");
traceHide("passThruData=".$passThruData);

//===================================================================================
// Home and Back buttons
?>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR><TD ALIGN="right">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0"><TR>
  <TD>
    <P CLASS="navBar">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <A HREF="javascript:history.back()">Back</A>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    <A HREF="adHocMenu.php">Ad Hoc Home</A>
    &nbsp;&nbsp;&nbsp;&nbsp;
    </P>
  </TD>
</TR></TABLE>
</TD></TR></TABLE>
<br/>
<br/>

<CENTER>
<span class="header18"><?echo $title;?>
<? 
//===================================================================================
//This section displays the query title and (if applicable) the record count 
if ($numRecords>-1)
{
?>
 (<? echo $numRecords;?> records)
<? 
} 
?>
</span>
</CENTER>
<BR>
<? echo $paramStr;?>
<? echo $paramTitle;?>
<BR>
<?
 
//===================================================================================
//This section constructs the heading text if necessary
if (strlen($heading)>0)
{
?>
  <TABLE>
  <TR>
    <TD WIDTH="70%"><? echo $heading;?></TD>
  </TR>
  </TABLE>
<? 
} 

//===================================================================================
// Decide whether to display with side-labels
if ($numRecords<=1 && $sideLabels)
{
//-------------------------------------------------------------------------------------
// Side labels
//-------------------------------------------------------------------------------------
?>
  <TABLE WIDTH="100%" BORDER="0">
  <?
  traceHide("Side labels");
  foreach($udbRows as $row)
  {
    foreach($row as $fieldName=>$dataItem)
    {
?>
      <TR>
      <TD class="ddheader"><?echo $fieldName;?></TD>
      <TD class="dedata"><?echo getLinkData($gblRows, $passThruData, $fieldName, $dataItem);?></TD>
      </TR>
<? 
    }
  }
}

//-----------------------------------------------------------------------------------
// Loop that displays detail rows
$titlesDone=false;
if ($numRecords>1 || !$sideLabels)
foreach($udbRows as $udbRow)
{
  if ($titlesDone==false)
  {
    traceHide("Integrated column headings");
?>
  <TABLE WIDTH="100%" BORDER="0">
  <TR>
<?
    $colCnt=0;
    foreach($udbRow as $fieldName=>$dataItem)
    {
      if ($hasTotalFields)
        $totalFieldOffset=registerTotalFieldName($totalFieldName,$totalFieldOffset,$fieldName,$colCnt);
?>
      <TD class="ddheader"><B><? echo $fieldName;?></B></TD>
<? 
     $colCnt++;
    }
    $titlesDone=true;
  }
?>
  <TR>
<?
  $colCnt=0;
  foreach($udbRow as $fieldName=>$dataItem)
  {
    if ($hasTotalFields)
      $totalFieldValue=incrementTotalField($totalFieldValue,$totalFieldOffset,$colCnt,$dataItem);

    $linkHTML=getLinkData($gblRows, $passThruData, $fieldName, $dataItem);
    $colCnt++;
?>
    <TD class="dedata"><? echo $linkHTML;?></TD>
<?
  }
?>
  </TR>
<?
} 

//Display Totals
if (($hasTotalFields)&&($numRecords>0))
{
?>
  <TR>
<? 
  for ($i=0; $i<$udbStmt->columnCount(); $i++)
  {
?>
    <TD class="detotal"><?echo totalForColumn($totalFieldValue,$totalFieldOffset,$i);?></TD>
<? 
  }
?>
  </TR>
<? 
} 
pdoDisconnect($conn_udb);
?>
</TABLE>
<? 
//-----------------------------------------------------------------------------------
if (cShowRelatedQueries)
{
  for ($i=0; $i<10; $i++)
  {
    traceHide("cShowRelatedQueries=true");
    $relatedItem=getName($i);
    traceHide("relatedItem=".$relatedItem);
    if ($relatedItem!="")
    {
      $relatedTitle="Other Queries using ".$relatedItem;

//Look for related enquiries, that is, where this user's query string variables
// can be used in other adHocQuery records

      $relatedSelect="SELECT m.menu_num, m.line_num, m.title, q.select_stmt"
          ." FROM queries q JOIN menus m ON m.main_query_num = q.query_num"
          ." WHERE q.select_stmt LIKE '%".cParamDelimiter.$relatedItem.cParamDelimiter."%'"
          ." AND m.hidden = 0"
          ." AND q.query_num <> ".$thisQuery." AND (q.pre_select IS NULL OR q.pre_select = '')"
          ." AND EXISTS( SELECT * FROM menus mm WHERE mm.main_query_num = q.query_num GROUP BY mm.main_query_num"
          ." HAVING m.menu_num = MIN(mm.menu_num))"
          ." ORDER BY m.title";

      traceHide("relatedSelect=".$relatedSelect);
      $adhocStmt = pdoQuery($relatedSelect,$connAdHoc);

      $numRecords=pdoRowCount($adhocStmt);
      traceHide($relatedSelect);
      traceHide($numRecords." records");

      if ($numRecords>0)
      {
?>
        <br/>
        <p class="ahTitleOtherLinks"><?echo $relatedTitle;?> (<?echo $numRecords;?> items)</p>
        <UL>
<? 
      } 
      foreach($adhocStmt as $row)
      {
        $nextScript=(hasAllParams(pdoData($row,"select_stmt"))? "adHocQuery.php":"adHocParam.php");
        TraceHide(pdoData($row,"menu_num")."/".pdoData($row,"line_num").", ".pdoData($row,"title"));
        $linkHTML="<A HREF=\"".$nextScript."?menunum=".pdoData($row,"menu_num")
                  ."&linenum=".pdoData($row,"line_num")
                  .$passThruData."\">".pdoData($row,"title")."</A>";
?>
        <LI class="ahOtherLinks"><? echo $linkHTML;?></LI><br />
<? 
      } 
?>
      </UL>
<? 
      pdoDisconnect($connAdHoc);
    } 
  }
} 
?>
<br />
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR><TD ALIGN="right">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0"><TR>
  <TD>
    <P CLASS="navBar">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <A HREF="javascript:history.back()">Back</A>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    <A HREF="adHocMenu.php">Ad Hoc Home</A>
    &nbsp;&nbsp;&nbsp;&nbsp;
    </P>
  </TD>
</TR></TABLE>
</TD></TR></TABLE>
</BODY>
</HTML>

