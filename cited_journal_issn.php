<?php
require ("cab.php");

require ("../_class/_class_qualis.php");
$ql = new qualis;
	echo '<table width="900"><TR><TD>';
	echo '<h1>Concatenação ISSN-eISSN</h1>';	
	echo $ql -> process_issn_eissn();
	echo '</table>';
?>


