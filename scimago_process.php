<?php
require ("cab.php");
require ("../_class/_class_scimago.php");
$sci = new scimago;

require ("../_class/_class_qualis.php");

require ("../_class/_class_cited.php");
$jl = new cited;
$jl -> updatex_journal();

$dir = 'scimago/2013/';
$arq = $sci -> buscar_arquivos_pastas($dir);
echo '<HR>PROCESS<HR>';

$id = $dd[0];

	$file = $arq[$id];
	echo '<HR><h1>'.$file.'</h1><hr>';	
	$sci -> process($dir.$file);

echo '<META http-equiv="refresh" content="5;URL='.page().'?dd0='.($id+1).'">';
?>


