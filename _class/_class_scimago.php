<?php
class scimago {

	var $journal;
	var $journal_codigo;
	
	var $tabela = 'cited_journals';
	
	function busca_scimago_issn($issn)
		{
			$sx = "http://www.scimagojr.com/journalsearch.php?q=$issn&tip=iss";
		}

	function buscar_arquivos_pastas($dir) {
		$d = dir($dir);
		echo "Lista de Arquivos do diretório '<strong>" . $dir . "</strong>':<br />";
		$arq = array();
		while ($fl = $d -> read()) {
			array_push($arq, $fl);
		}
		$d -> close();
		return ($arq);
	}

	function updatex() {
		global $base;
		$c = 'cj';
		$c1 = 'id_' . $c;
		$c2 = $c . '_codigo';
		$c3 = 7;
		$sql = "update " . $this -> tabela . " set $c2 = lpad($c1,$c3,0) where $c2='' ";
		if ($base == 'pgsql') { $sql = "update " . $this -> tabela . " set $c2 = trim(to_char(id_" . $c . ",'" . strzero(0, $c3) . "')) where $c2='' ";
		}
		$rlt = db_query($sql);
	}

	function search_journal($issn, $name = '', $country = '') {
		if (substr($issn, 4, 1) == '-') {
			$sql = "select * from cited_journals where cj_issn = '" . $issn . "' ";
			$rlt = db_query($sql);
			if ($line = db_read($rlt)) {
				$cod = trim($line['cj_codigo']);
				$this -> journal_codigo = $cod;
				$this -> journal_codigo_issn = $line['cj_issn'];
				return (1);
			} else {
				return (-1);
			}
		}
	}

	/* Processar Arquivo */
	function process($filename) {
		
		$this->updatex();
		$s = $this -> read_file($filename);
		echo '<HR>'.$filename.'<HR>';
		if ($s != -1) {
			$s = $this -> process_i($s);
			$s = $this -> process_ii($s);
			echo '<HR>PROCESSO III<HR>';
			$sa = $this -> process_iii($s);
			echo '<HR>PROCESSO IV<HR>';
			$this -> process_iv($sa);
			echo '<HR>PROCESSO V<HR>';
			$this -> process_v($sa);
		} else {
			Echo 'Erro na abertura de arquivo';
			exit ;
		}
	}

	/* Identificacao */
	function process_iv($sa) {
		for ($r = 0; $r < count($sa); $r++) {
			$sx = $sa[$r] . ';';
			$ss = troca($ss,';;','; ;');
			$ss = splitx(';', $sx);
			$journal = $ss[1];
			$q = $ss[4];
			if (substr($q,0,1) != 'Q')
				{
					print_r($ss);
					echo '=========>'.$q;
				}
			
			$issn = trim(substr($ss[3], 0, 4) . '-' . substr($ss[3], 4, 4));
			if (strlen($issn) <> 9)
				{
					$issn = trim(substr($ss[4], 0, 4) . '-' . substr($ss[4], 4, 4));
					$journal = $ss[1];
					$q = $ss[5];
							
				}
			$journal = $ss[1];
			$q = $ss[4];
			
			$country = $ss[14];
			echo '<BR>[[' . $issn . ']] - <font color="blue">' . $journal . '('.strlen($issn).')</font> ';
			
			if ($this -> search_journal($issn) > 0) {
				if (substr($q,0,1)=='Q')
				{
					$q1 = round(substr($this->scimago,1,1));
					$q2 = '';
					$sql = "update cited_journals set cj_scimago = '$q' where cj_codigo = '" . $this -> journal_codigo . "' ";
					//echo '<BR><font color="blue">'.$sql.'</font>';
					$rrr = db_query($sql);
				}
			} else {
				echo '<BR><font color="red">Publicação não localizada ' . $issn . ' - ' . $this -> journal . '</font>';
				$qualis = new qualis;
				$qualis -> cited_journal_insert($issn, $journal);
			}

		}
	}

	/* Process Grava Dados */

	function process_v($sa) {
		/* Identificacao */
		}

	/* Processa corta */
	function process_i($s) {
		$pos = strpos($s, '<tbody>');
		if ($pos > 0) {
			$s = substr($s, $pos + 7, strlen($s));
			$pos = strpos($s, '</tbody>');
			$s = substr($s, 0, $pos);
		}
		return ($s);
	}

	/* Processa arquivo */
	function process_ii($s) {
		$s = troca($s, '<', '[');
		$s = troca($s, '>', ']');
		$s = troca($s, "'", "´");
		$s = troca($s, '[tr]', chr(13) . chr(10));
		$s = troca($s, '[td]', '');
		$s = troca($s, '[td ]', '');
		$s = troca($s, '="', '');
		$s = troca($s, '"', '');
		$s = troca($s, '[/td]', ';');
		return ($s);
	}

	/* SplitX */
	function process_iii($s) {
		$sa = splitx(chr(13) . chr(10), $s);
		return ($sa);
	}

	/* Ler arquivo */
	function read_file($filename) {
		if (file_exists($filename)) {
			$rlt = fopen($filename, 'r');
			$s = '';
			while (!(feof($rlt))) {
				$s .= fread($rlt, 512);
			}
			fclose($rlt);
			return ($s);
		} else {
			return (-1);
		}

	}

}
?>
