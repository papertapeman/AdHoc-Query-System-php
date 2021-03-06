<?php
//adHocParam.php

session_start();
require("adHocConst.php");
require("adHocInclude.php");

traceHide("Passed keys / value");
foreach ($_REQUEST as $key => $val)
  traceHide("key:".$key." / val:".$val);

$connAdHoc=pdoConnect(cAdHocServer, cAdHocDatabase, cAdHocUsername, cAdHocPassword);
$thisMenu=$_REQUEST["menunum"];
$thisLine=$_REQUEST["linenum"];

$sql=" SELECT title, select_stmt".
    " FROM menus".
    " JOIN queries q1 ON main_query_num = query_num".
    " WHERE menu_num = ".$thisMenu." AND line_num = ".$thisLine;

$stmt = pdoQuery($sql,$connAdHoc);

$siteNum=$_SESSION["sitenum"];
$sqlSite="SELECT site_name FROM sites WHERE site_num = ".$siteNum;
$siteStmt = pdoQuery($sqlSite,$connAdHoc);
$siteRow = pdoFirstRow($siteStmt);
$siteName=pdoData($siteRow,"site_name");

//get today's date
$pageDate=dateNow();
$row = pdoFirstRow($stmt);
?>
<HTML>
<HEAD>
<? echo cHeaderComment;?>
<TITLE><? echo pdoData($row,"title");?> adHoc Query - Parameter Entry</TITLE>
<LINK REL="stylesheet" HREF=<? echo cStylesheet;?> TYPE="text/css" />
<script language="javascript">
<!--
function doInputForm() {
  document.InputForm.submit();
}
//-->
</script>
</HEAD>
<BODY BGCOLOR="#000000">

<!-- Header table -->
<TABLE BORDER="0" WIDTH="600" CELLPADDING="0" CELLSPACING="0">
<TR><TD WIDTH="3">&nbsp;</TD><TD WIDTH="597">
<TABLE CELLPADDING="0" CELLSPACING="0" BORDER="0" WIDTH="100%"><TR>
  <TD><P CLASS="title">Ad Hoc Parameter <? echo $thisMenu;?> / <? echo $thisLine;?></P></TD>
  <TD><P CLASS="title"><?echo $siteName;?></TD>
  <TD><P CLASS="date"><? echo $pageDate;?></P></TD>
</TR></TABLE>
<CENTER>
<IMG SRC="<? echo cHeaderImagePath;?>" ALT="" BORDER="0" WIDTH="299" HEIGHT="95"></A>
<BR><BR>
<span class="header18"><? echo pdoData($row,"title");?></span>
</CENTER>
<? 
traceHide($sql);
traceHide("menunum/linenum=".$thisMenu."/".$thisLine);

$selectStmt=pdoData($row,"select_stmt");
if (queryHasParams($selectStmt))
{
  $paramStr=explode(cParamDelimiter,$selectStmt);
  //step through the parameters and create corresponding form inputs
  //these are called '_n', where n is their index within array
  //splitParamStrArray
  $formInputsStr="";
  for ($i=1; $i<count($paramStr); $i+=2)
  {
    traceHide("requestName=".$paramStr[$i]);
    $formInputsStr=$formInputsStr."<TR><TD class=\"ahParam\">".$paramStr[$i]."</TD>".
      "<TD class=\"ahParam\"><INPUT TYPE=\"text\" NAME=\"".
      $paramStr[$i]."\" VALUE=\"".requestValue($paramStr[$i])."\" class=\"text\"></TD><TD>&nbsp;</TD></TR>";
  }
} 
pdoDisconnect($connAdHoc);
?>
<FORM ACTION="adHocQuery.php" NAME="InputForm" METHOD="POST">
<CENTER>
<TABLE cellpadding="2">
<? echo $formInputsStr;?>
<TR>
  <TD>&nbsp;</TD>
  <TD>&nbsp;</TD>
  <TD class="ahParam" valign="bottom">
     &nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:doInputForm()">next...</a>
	 <!--INPUT TYPE="Submit" VALUE="Next"-->
  </TD>
</TR>
</TABLE>
</CENTER>
  <INPUT TYPE="hidden" NAME="sitenum" VALUE="<? echo $thisSite;?>">
  <INPUT TYPE="hidden" NAME="menunum" VALUE="<? echo $thisMenu;?>">
  <INPUT TYPE="hidden" NAME="linenum" VALUE="<? echo $thisLine;?>">
</FORM>
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
<SCRIPT LANGUAGE="JavaScript">
<!--
document.InputForm.elements[0].focus();
document.InputForm.elements[0].select();
// -->
</SCRIPT>
</BODY>
</HTML>

