<?php
// adHocMenu.php
session_start();
require_once("adHocConst.php");
require_once("adHocInclude.php");
$connAdHoc=pdoConnect(cAdHocServer, cAdHocDatabase, cAdHocUsername, cAdHocPassword);

//get today's date
$pageDate=dateNow();

//request sitenum takes precedent over session sitenum
if(isset($_REQUEST["sitenum"]))
  $siteNum=$_REQUEST["sitenum"];
else
{
  if(isset($_SESSION["sitenum"]))
    $siteNum=$_SESSION["sitenum"];
  else
    $siteNum="1";
}

$_SESSION["sitenum"]=$siteNum;
traceHide("sitenum=".$siteNum);

if(isset($_REQUEST["nextmenu"]))
  $nextMenu=$_REQUEST["nextmenu"];
else
  $nextMenu="1";
traceHide("nextmenu=".$nextMenu);

$sql=" SELECT menu_num, line_num, title, sub_menu_num, select_stmt".
    " FROM menus".
    " LEFT JOIN queries ON main_query_num = query_num".
    " WHERE menu_num = ".$nextMenu.
    " AND hidden = 0".
    " ORDER BY menu_num, line_num";

$adHocStmt = pdoQuery($sql,$connAdHoc);
$adHocRows = pdoFetch($adHocStmt);
?>
<HTML>
<HEAD>
<?echo cHeaderComment;?>
<TITLE>Ad Hoc Menu</TITLE>
<LINK REL="stylesheet" HREF=<? echo cStylesheet;?> TYPE="text/css" />
</HEAD>
<BODY BGCOLOR="#000000">
<!-- Header table -->
<TABLE BORDER="0" WIDTH="800" CELLPADDING="0" CELLSPACING="0">
<TR><TD WIDTH="3">&nbsp;</TD><TD WIDTH="597">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR>
  <TD><P CLASS="title">Ad Hoc Menu (<? echo $nextMenu;?> / <? echo pdoRowCount($adHocStmt);?>)</P></TD>
<?
if (cMultipleSites)
{
  $sqlSites="SELECT site_num, site_name FROM sites";
  $adHocSitesStmt = pdoQuery($sqlSites,$connAdHoc);
  $adHocSitesRows = pdoFetch($adHocSitesStmt);
?>
<TD>
<FORM ACTION="adHocMenu.php" METHOD="post">
<SELECT NAME="sitenum" SIZE="1" ONCHANGE="this.form.submit();">
<?
  foreach($adHocSitesRows as $row)
  {
   if ($siteNum==pdoData($row,"site_num"))
     $sel="SELECTED";
   else
     $sel="";
?>
<OPTION <?echo $sel?> VALUE="<?echo pdoData($row,"site_num");?>"><?echo pdoData($row,"site_name");?></OPTION>
<?
  }
?>
</SELECT>
</FORM>
</TD>
<?
}
?>
  <TD><P CLASS="date"><? echo "&nbsp;&nbsp;".$pageDate;?></P></TD>
</TR></TABLE>
<CENTER>
<IMG SRC="<? echo cHeaderImagePath;?>" ALT="" BORDER="0" WIDTH="299" HEIGHT="95"></A>
<BR><BR>
</CENTER>
<CENTER>
<? 
if (cMenuFormatGet)
{
?>
<TABLE cellpadding="3">
<TR>
  <TD class="ddheader"><B>Function</B></TD>
</TR>
<? 
}
else
{
?>
<TABLE>
<TR>
  <TD class="ddheader"><B>Function</B></TD>
  <TD class="ddheader"><B>Action</B></TD>
</TR>
<? 
} 
foreach($adHocRows as $row)
{
?>
<TR>
<? 
  if ($row['sub_menu_num']=="0")
  {
    //This is a query request
    //Check to see if adHocParam page required
    if (queryHasParams(pdoData($row,"select_stmt")))
    {
      //Query with arguments
      if (cMenuFormatGet)
      {
?>
<TD class="ahMenu1">
<LI><A HREF="adHocParam.php?menunum=<?echo pdoData($row,"menu_num");?>&linenum=<?echo pdoData($row,"line_num");?>"><?echo pdoData($row,"title");?></A>&nbsp;&nbsp;<BR>
</TD>
<? 
      }
        else
      {
?>
	<TD class="ahMenu2">&nbsp;&nbsp;<?echo pdoData($row,"title");?>&nbsp;&nbsp;</TD>
	<TD class="ahMenu3">
	 <FORM ACTION="adhocParam.php" METHOD="POST">
	 <INPUT TYPE="hidden" NAME="menunum" VALUE="<?echo pdoData($row,"menu_num");?>">
	 <INPUT TYPE="hidden" NAME="linenum" VALUE="<?echo pdoData($row,"line_num");?>">
	 <INPUT TYPE="submit" VALUE="Next">
	</FORM>
	</TD>
<? 
      } 
    }
    else
    {
//Query with no arguments
      if (cMenuFormatGet)
      {
?>
<TD class="ahMenu1">
<LI><A HREF="adHocQuery.php?menunum=<?echo pdoData($row,"menu_num");?>&linenum=<?echo pdoData($row,"line_num");?>"><?echo pdoData($row,"title");?></A>&nbsp;&nbsp;<BR>
</TD>
<? 
      }
      else
      {
?>
	<TD class="ahMenu2">&nbsp;&nbsp;<? echo pdoData($row,"title");?>&nbsp;&nbsp;</TD>
	<TD class="ahMenu3">
	<FORM ACTION="adhocQuery.php" METHOD="POST">
	 <INPUT TYPE="hidden" NAME="menunum" VALUE="<? echo pdoData($row,"menu_num");?>">
	 <INPUT TYPE="hidden" NAME="linenum" VALUE="<? echo pdoData($row,"line_num");?>">
	 <INPUT TYPE="submit" VALUE="Next">
	</FORM>
	</TD>
<? 
      } 
    } 
  }
  else
  {
// This is another menu page
    if (cMenuFormatGet)
    {
?>
<TD class="ahMenu1">
<LI><A HREF="adHocMenu.php?nextmenu=<?echo $row['sub_menu_num'];?>"><?echo $row['title'];?></A>&nbsp;&nbsp;<BR>
</TD>
<? 
    }
    else
    {
?>
	<TD class="ahMenu2">&nbsp;&nbsp;<?       echo pdoData($row,"title");?>&nbsp;&nbsp;</TD>
	<TD class="ahMenu3">
	<FORM ACTION="adhocMenu.php" METHOD="POST">
	 <INPUT TYPE="hidden" NAME="nextmenu" VALUE="<?echo pdoData($row,"sub_menu_num");?>">
	 <INPUT TYPE="submit" VALUE="Next">
	</FORM>
	</TD>
<? 
    } 
  } 
?>
</TR>
<? 
} 
pdoDisconnect($connAdHoc);
//-----------------------------------------------------------------------------------
?>
</TABLE>
</CENTER>
<BR />
<BR />
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
</TD>
</TR></TABLE>
<!-- Header table -->
<BR /><BR />
</BODY>
</HTML>

