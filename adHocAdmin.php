<?php
// adHocAdmin.php

require_once("adHocConst.php");
require_once("adHocInclude.php");

$connAdHoc=pdoConnect(cAdHocServer, cAdHocDatabase, cAdHocUsername, cAdHocPassword);

//get today's date
$pageDate=dateNow();

if(isset($_REQUEST["nextmenu"]))
  $nextMenu=$_REQUEST["nextmenu"];
else
  $nextMenu="1";
traceHide("nextmenu=".$nextMenu);

if(isset($_REQUEST["menu_num"]))
  $MenuNum=$_REQUEST["menu_num"];
else
  $MenuNum="1";
traceHide("menu_num=".$MenuNum);

if(isset($_REQUEST["line_num"]))
  $LineNum=$_REQUEST["line_num"];
else
  $LineNum="1";
traceHide("line_num=".$LineNum);

///////////////////
// start of updates

if(isset($_REQUEST['up']))
{
  $sql="call sp_movemenu('u',".$MenuNum.",".$LineNum.")";
  pdoQuery($sql,$connAdHoc);
}

if(isset($_REQUEST['down']))
{
  $sql="call sp_movemenu('d',".$MenuNum.",".$LineNum.")";
  pdoQuery($sql,$connAdHoc);
}

if(isset($_REQUEST['addm']))
{
  $sql="call sp_addmenu(".$MenuNum.")";
  pdoQuery($sql,$connAdHoc);
}

if(isset($_REQUEST['addq']))
{
  $sql="call sp_addquery(".$nextMenu.")";
  pdoQuery($sql,$connAdHoc);
}

// end of updates
/////////////////

$sql="call sp_getmenu(".$nextMenu.")";
pdoQuery($sql,$connAdHoc);

$adHocStmt = pdoQuery($sql,$connAdHoc);
$adHocRows = pdoFetch($adHocStmt);
?>
<HTML>
<HEAD>
<?echo cHeaderComment;?>
<TITLE>Ad Hoc Admin</TITLE>
<LINK REL="stylesheet" HREF=<? echo cStylesheet;?> TYPE="text/css" />
</HEAD>
<BODY BGCOLOR="#000000">
<!-- Header table -->
<TABLE BORDER="0" WIDTH="800" CELLPADDING="0" CELLSPACING="0">
<TR><TD WIDTH="3">&nbsp;</TD><TD WIDTH="597">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR>
  <TD><P CLASS="title">Ad Hoc Admin (<? echo $nextMenu;?> / <? echo pdoRowCount($adHocStmt);?>)</P></TD>
  <TD><P CLASS="date"><? echo "&nbsp;&nbsp;".$pageDate;?></P></TD>
</TR>
</TABLE>
<CENTER>
<IMG SRC="<? echo cHeaderImagePath;?>" ALT="" BORDER="0" WIDTH="299" HEIGHT="95"></A>
<BR><BR>
</CENTER>
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR><TD ALIGN="right">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
<TR>
  <TD>
    <P CLASS="navBar">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <A HREF="javascript:history.back()">Back</A>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    <A HREF="adHocAdmin.php">Ad Hoc Admin Home</A>
    &nbsp;&nbsp;&nbsp;&nbsp;
    </P>
  </TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<CENTER>
<TABLE cellpadding="3">
<TR>
  <TD class="ddheader"><B>Function</B></TD>
  <TD class="ddheader"><B>Type</B></TD>
</TR>
<?
$i=0;
foreach($adHocRows as $row)
{
  $i++;
  $itemType = ($row['sub_menu_num']=='0'? 'Q': 'M');
?>
  <TR>
  <TD class="ahMenu1">
<?
  if($itemType=='M')
  {
?>
    <A HREF="adHocAdmin.php?nextmenu=<?echo $row['sub_menu_num'];?>"><?echo $row['title'];?></A>
<?
  }
  else
    echo $row['title'];
?>
  &nbsp;&nbsp;<BR>
  </td>
  <td class="ahMenu1">
<?
  echo $itemType=='Q'? 'Query': 'Menu';
?>
  </td>
  <td></td>
  <td class="ahMenu1">
<?
  if ($i>1)
  {
?>
   <A HREF="adHocAdmin.php?menu_num=<?echo $row['menu_num'];?>&line_num=<?echo $row['line_num'];?>&up=1">Up</A>
<?
  }
?>
  </td>
  <td></td>
  <td class="ahMenu1">
<?
  if ($i<pdoRowCount($adHocStmt))
  {
?>
   <A HREF="adHocAdmin.php?menu_num=<?echo $row['menu_num'];?>&line_num=<?echo $row['line_num'];?>&down=1">Down</A>
<?
  }
?>
  </td>
  <td></td>
  <td class="ahMenu1">
<?
  if ($itemType=='Q')
  {
?>
   <A HREF="adHocAdminEditQuery.php?menu_num=<?echo $row['menu_num'];?>&line_num=<?echo $row['line_num'];?>&edit=1">Edit</A>
  </td>
<?
  }
  else
  {
?>
   <A HREF="adHocAdminEditMenu.php?menu_num=<?echo $row['menu_num'];?>&line_num=<?echo $row['line_num'];?>&edit=1">Edit</A>
  </td>
<?
  }
} 
?>
</TR>
<TR>
<td></td>
</TR>
<TR>
  <td>
  <form action="adHocAdmin.php" method="post">
  <input type="hidden" name="nextmenu" value="<?echo $nextMenu;?>">
  <input type="submit" name="addm" value="Add New Menu">
  </form>
  </td>
</TR>
<TR>
  <td>
  <form action="adHocAdmin" method="post">
  <input type="hidden" name="nextmenu" value="<?echo $nextMenu;?>">
  <input type="submit" name="addq" value="Add New Query">
  </form>
  </td>
</TR>
<?  
pdoDisconnect($connAdHoc);
?>
</TABLE>
</CENTER>
<BR />
<BR />
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR><TD ALIGN="right">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0">
<TR>
  <TD>
    <P CLASS="navBar">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <A HREF="javascript:history.back()">Back</A>
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    <A HREF="adHocAdmin.php">Ad Hoc Admin Home</A>
    &nbsp;&nbsp;&nbsp;&nbsp;
    </P>
  </TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
</TD>
</TR>
</TABLE>
<BR/>
</BODY>
</HTML>

