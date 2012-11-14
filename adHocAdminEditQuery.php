<?php
require_once("adHocConst.php");
require_once("adHocInclude.php");

//adHocAdminEditQuery.php
//===================================================================================
//This section retrieves the menu record
$connAdHoc=pdoConnect(cAdHocServer, cAdHocDatabase, cAdHocUsername, cAdHocPassword);

traceHide("Passed keys / value");
foreach ($_REQUEST as $key => $val)
  traceHide("key:".$key." / val:".$val);

$curMenu=$_REQUEST["menu_num"];
$curLine=$_REQUEST["line_num"];

if(isset($_REQUEST['upd']))
{
  $query = unformatSql($_REQUEST['query']);
  $sql="call sp_updatequery("
			.$curMenu.','
			.$curLine.','
			.$_REQUEST['sub_menu_num'].','
			.sqlLiteral($_REQUEST['title']).','
			.$_REQUEST['query_num'].','
			.sqlLiteral($query).','
			.sqlLiteral($_REQUEST['heading']).','
			.sqlLiteral($_REQUEST['pre_select']).','
			.sqlLiteral($_REQUEST['total_fields']).','
			.sqlLiteral($_REQUEST['link_field']).','
			.$_REQUEST['udb_num']
			.")";
  traceHide($sql);
  pdoQuery($sql,$connAdHoc);
  header("Location: adHocAdmin.php?nextmenu=".$_REQUEST['sub_menu_num']);
  exit;
}

$sql=" SELECT q.query_num, title, q.heading_text AS heading, q.total_fields,".
     " q.link_field, q.select_stmt AS detail_select, q.pre_select, q.udb_num".
     " FROM menus m".
     " JOIN queries q ON m.main_query_num = q.query_num".
     " WHERE m.menu_num = ".$curMenu." AND m.line_num = ".$curLine;

traceHide("main query=".$sql);

//===================================================================================
//Get the menu record pertaining to this query
$stmt = pdoQuery($sql,$connAdHoc);
$row = pdoFirstRow($stmt);

$title=pdoData($row,'title');
$heading=pdoData($row,'heading');
$preselect=pdoData($row,'pre_select');
$linkfield=pdoData($row,'link_field');
$totalfields=pdoData($row,'total_fields');
$udbnum = pdoData($row,'udb_num');
$detailSelect=pdoData($row,'detail_select');
traceHide("Raw detailSelect=".$detailSelect);

$curQuery=pdoData($row,"query_num");
pdoClose($stmt);
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
  <TD><P CLASS="title">Ad Hoc Admin <? echo $curMenu;?> / <? echo $curLine;?> / <? echo $curQuery;?></P></TD>
  <TD><P CLASS="date"><? echo $pageDate;?></P></TD>
</TR></TABLE>
<CENTER>
<IMG SRC="<? echo cHeaderImagePath;?>" ALT="" BORDER="0" WIDTH="299" HEIGHT="95"></A>
<BR><BR>
</CENTER>
</TD>
</TR></TABLE>
<? 

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
    <A HREF="adHocAdmin.php">Ad Hoc Admin Home</A>
    &nbsp;&nbsp;&nbsp;&nbsp;
    </P>
  </TD>
</TR></TABLE>
</TD></TR></TABLE>
<br/>
<br/>

<CENTER>
<span class="header18">Edit Query
</span>
</CENTER>
<BR>
</TABLE>
<br />
<FORM ACTION="adHocAdminEditQuery.php" METHOD=POST>
<TABLE>
<TR>
<TD>
Database:<BR>
</TD>
<TD>
<?
  $sql="call sp_dbsfordropdown();";
  $stmt = pdoQuery($sql,$connAdHoc);
  $rows = pdoFetch($stmt);
?>
  <SELECT NAME="udb_num" SIZE="1">
<?
  foreach($rows as $row)
  {
   if ($udbnum==pdoData($row,"udb_num"))
     $sel="SELECTED";
   else
     $sel="";
?>
   <OPTION <?echo $sel?> VALUE="<?echo pdoData($row,'udb_num');?>"><?echo pdoData($row,'udb_name');?></OPTION>
<?
  }
  pdoClose($stmt);
?>
</SELECT>
</TD>
</TR>
<TR>
<TD>
Menu:<BR>
</TD>
<TD>
<?
  $sql="call sp_menusfordropdown();";
  $stmt = pdoQuery($sql,$connAdHoc);
  $rows = pdoFetch($stmt);
?>
  <SELECT NAME="sub_menu_num" SIZE="1">
  <OPTION VALUE="0">Top Level</OPTION>
<?
  foreach($rows as $row)
  {
   if ($curMenu==pdoData($row,"sub_menu_num"))
     $sel="SELECTED";
   else
     $sel="";
?>
   <OPTION <?echo $sel?> VALUE="<?echo pdoData($row,"sub_menu_num");?>"><?echo pdoData($row,"title");?></OPTION>
<?
  }
  pdoClose($stmt);
?>
</SELECT>
</TD>
</TR>
<? 
//-----------------------------------------------------------------------------------
pdoDisconnect($connAdHoc);
?>
<TR>
<TD>
Title:<BR>
</TD>
<TD>
<TEXTAREA NAME="title" COLS=89 ROWS=1><? echo $title;?></TEXTAREA>
</TD>
</TR>
<TR>
<TD>
Heading:<BR>
</TD>
<TD>
<TEXTAREA NAME="heading" COLS=89 ROWS=1><? echo $heading;?></TEXTAREA>
</TD>
</TR>
<TR>
<TD>
Pre-select:<BR>
</TD>
<TD>
<TEXTAREA NAME="pre_select" COLS=89 ROWS=1><? echo $preselect;?></TEXTAREA>
</TD>
</TR>
<TR>
<TD>
Total Fields:<BR>
</TD>
<TD>
<TEXTAREA NAME="total_fields" COLS=89 ROWS=1><? echo $totalfields;?></TEXTAREA>
</TD>
</TR>
<TR>
<TD>
Link Field:<BR>
</TD>
<TD>
<TEXTAREA NAME="link_field" COLS=30 ROWS=1><? echo $linkfield;?></TEXTAREA>
</TD>
</TR>
</TABLE>
<TEXTAREA NAME="query" COLS=120 ROWS=15 WRAP="soft"><? echo formatSql($detailSelect);?></TEXTAREA>

<input type="hidden" name="upd" value="1">
<input type="hidden" name="menu_num" value="<?echo $curMenu;?>">
<input type="hidden" name="line_num" value="<?echo $curLine;?>">
<input type="hidden" name="query_num" value="<?echo $curQuery;?>">

<P><INPUT TYPE=SUBMIT VALUE="submit">
</FORM>
<br />
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR><TD ALIGN="right">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0"><TR>
  <TD>
    <P CLASS="navBar">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <A HREF="javascript:history.back()">Back</A>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    <A HREF="adHocAdmin.php">Ad Hoc Admin Home</A>
    &nbsp;&nbsp;&nbsp;&nbsp;
    </P>
  </TD>
</TR></TABLE>
</TD></TR></TABLE>
</BODY>
</HTML>

