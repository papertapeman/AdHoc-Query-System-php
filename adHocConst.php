<?php
error_reporting(E_ALL|E_STRICT);
ini_set("display_errors", 1);

// adHocConst.php - constants for the adHoc Query System

define("cHeaderImagePath", "images/ahHeader.gif");
define("cStylesheet", "styles/adHoc.css");
define("cImageURL", "http://www.some-webite-with-images.com");

define("cMyDebug", true);
define("cShowRelatedQueries", true);
define("cMenuFormatGet", true);

define("cHeaderComment", "<!-- adHoc Query System -->");
define("cParamDelimiter", "++");
define("cLinkDelimiter", "~");
define("cDateSeparator", "/");

define("cAdHocServer","localhost");
define("cAdHocDatabase","adhoc");
define("cAdHocUsername", "adhocuser");
define("cAdHocPassword", "adhoc");
?>
<SCRIPT LANGUAGE="javascript" SRC="adHocFunc.js"></SCRIPT>

