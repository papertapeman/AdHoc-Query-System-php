<?php
require_once("adHocConst.php");
require_once("adHocInclude.php");

//adHocAdminEditMenu.php
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
  $sql="call sp_updatemenu("
			.$_REQUEST["parent_menu"].','
			.$curMenu.','
			.$curLine.','
			.sqlLiteral($_REQUEST['title'])
			.")";
  traceHide($sql);
  pdoQuery($sql,$connAdHoc);
  header("Location: adHocAdmin.php?nextmenu=".$curMenu);
  exit;
}

$sql=" SELECT title".
     " FROM menus".
     " WHERE menu_num = ".$curMenu." AND line_num = ".$curLine;

traceHide("main query=".$sql);

//===================================================================================
//Get the menu record pertaining to this query
$adhocStmt = pdoQuery($sql,$connAdHoc);
$row = pdoFirstRow($adhocStmt);

$title=pdoData($row,'title');
traceHide('Title: '.$title);
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
  <TD><P CLASS="title">Ad Hoc Admin <? echo $curMenu;?> / <? echo $curLine;?></P></TD>
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
<span class="header18">Edit Menu
</span>
</CENTER>
<BR>
</TABLE>
<br />
<FORM ACTION="adHocAdminEditMenu.php" METHOD=POST>
<TABLE>
<TR>
<TD>
Parent Menu:<BR>
</TD>
<TD>
<?
$sql="call sp_menusfordropdown();";
$stmt = pdoQuery($sql,$connAdHoc);
$rows = pdoFetch($stmt);
?>
  <SELECT NAME="parent_menu" SIZE="1">
  <OPTION VALUE="1">Top Level</OPTION>
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
pdoDisconnect($connAdHoc);
?>
</SELECT>
</TD>
</TR>
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
</TD>
<TD>
<INPUT TYPE=SUBMIT VALUE="submit">
</TD
</TABLE>

<input type="hidden" name="upd" value="1">
<input type="hidden" name="menu_num" value="<?echo $curMenu;?>">
<input type="hidden" name="line_num" value="<?echo $curLine;?>">

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

