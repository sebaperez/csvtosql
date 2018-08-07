<?php

	class CSV2MySQL {

		private $conex;

		public function __construct($options = array(
				"host" => "localhost",
				"user" => "root",
				"pass" => "",
				"db" => "test"
			)) {
			$this->conex = mysql_connect($options["host"], $options["user"], $options["pass"]);
			if (! $this->conex) {
				die("Unable to connect: " . mysql_error());
			}
			mysql_select_db($options["db"]);
		}

		public function createTableFromCSV($file = null, $tableName = null, $cols = array(), $options = array(
				"separator" => ";",
				"ignoreFirstRow" => true
			)) {
			if ($file && $tableName && count($cols)) {
				$rows = array();
				$fl = file($file);
				$colsKeys = array_keys($cols);
				for ($i = ($options["ignoreFirstRow"] ? 1 : 0); $i < count($fl); $i++) {
					$line = $fl[$i];
					$parsedLine = explode($options["separator"], $line);
					$_row = array();
					for ($o = 0; $o < count($parsedLine); $o++) {
						$_row[$colsKeys[$o]] = $parsedLine[$o];
					}
					array_push($rows, $_row);
				}
			}

			$this->createTempTable($tableName, $cols);
			$this->fillTable($tableName, $rows);
		}

		private function createTempTable($tableName, $cols) {
			$query = "CREATE TEMPORARY TABLE $tableName (";
			$_cols = array();
			foreach ($cols as $col => $type) {
				array_push($_cols, "$col $type");
			}
			$query .= implode(", ", $_cols);
			$query .= ")";
			mysql_query($query, $this->conex) or die ("Sql error: " . mysql_error());
		}

		private function fillTable($tableName, $values) {
			for ($i = 0; $i < count($values); $i++) {
				$_values = $values[$i];
				$query = "insert into $tableName (" . implode(",", array_keys($_values)) . ") values (" . implode(",", array_values($_values)) . ")";
				mysql_query($query) or die ("Sql error: " . mysql_error());
			}
		}	

		public function getQuery($query) {
			$r = mysql_query($query, $this->conex);
			$rows = array();
			if (!$r) {
				die("Sql error: " . mysql_error());
			}

			while ($row = mysql_fetch_assoc($r)) {
				array_push($rows, $row);
			}

			mysql_free_result($r);

			return $rows;
		}

		public function saveToCSV($rows, $fileName, $options = array(
				"separator" => ";"
			)) {
			$fh = fopen($fileName, "a+");
			fputs($fh, implode($options["separator"], array_keys($rows[0])) . "\n");
			for ($i = 0; $i < count($rows); $i++) {
				fputs($fh, implode($options["separator"], array_values($rows[$i])) . "\n");
			}
		}

		public function showResult($row) {
			var_dump($row);
		}

	}


?>
