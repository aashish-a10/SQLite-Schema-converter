<?php

/*******************************************************************************
Mysql toSQLite schema export 
Copyright (c) 2011 Deepesh Sharma
*******************************************************************************/

// Defined ingredients

define('DATABASE', 'sqlite_database_name.db');
define('VERSION', '1.0');
define('SQLITE_DATABASE_SCHEMA', 'mysql__database_schema_file.sql');


function export_sqlite_schema($hostname, $username, $password, $database, $db_tables = '*', $directory) {
	
	$connection = mysql_connect ( $hostname, $username, $password ) or die ( mysql_error () );
	mysql_select_db ( $database, $connection ) or die ( mysql_error () );
	
	if ($db_tables == '*') {
			$db_tables = array ();
			$query_result = mysql_query ( 'SHOW TABLES' ) or die (mysql_error());
			while ( $fetch_row = mysql_fetch_row ($query_result)) {
				$db_tables [] = $fetch_row [0];
			}
	} else {
		
			$db_tables = is_array( $db_tables ) ? $db_tables : explode ( ',', $db_tables );
	}
	
	foreach ( $db_tables as $table ) {

		$query_result = mysql_query('SELECT * FROM '. $table) or die (mysql_error());
		$num_fields = mysql_num_fields ($query_result) or die (mysql_error());
		
		$schema .= 'DROP TABLE IF EXISTS "' . $table . '";';
		$create_table_fetch_row = mysql_fetch_row ( mysql_query ( 'SHOW CREATE TABLE ' . $table) ) or die ( mysql_error () );
		$explode_fetch_row = explode ( 'ENGINE', $create_table_fetch_row [1] );
		$schema .= "\n\n" . $explode_fetch_row [0] . ";\n\nBEGIN;\n";
		
		for($x_index = 0; $x_index < $num_fields; $x_index ++) {
			while ( $fetch_row = mysql_fetch_row ( $query_result ) ) {
				$schema .= 'INSERT INTO "' . $table . '" VALUES(';
				for($y_index = 0; $y_index < $num_fields; $y_index ++) {
					$fetch_row [$y_index] = addslashes ( $fetch_row [$y_index] );
					$fetch_row [$y_index] = ereg_replace ( "\n", "\\n", $fetch_row [$y_index] );
					if (isset ( $fetch_row [$y_index] )) {
						$schema .= "'" . $fetch_row [$y_index] . "'";
					} else {
						$schema .= '""';
					}
					if ($y_index < ($num_fields - 1)) {
						$schema .= ',';
					}
				}
				$schema .= ");\n";
			}
		}
		$schema .= "COMMIT; \n\n\n";
	}
	$string_replace = array ("varchar" => "TEXT", "`" => '"', "AUTO_INCREMENT" => "", "ENGINE=MyISAM DEFAULT CHARSET=latin1" => "", "0)" => "0, 0)", "int" => "INTEGER", "float" => "REAL(10,1)", "unsigned " => "", "ENGINE=InnoDB DEFAULT CHARSET=latin1" => "" );
	$schema = strtr ( $schema, $string_replace );
	
	$file_name = $directory . 'turfnutritiontool.sql';
	$handle = fopen ( $file_name, 'w+' ); //'DB_'.date('d-m-Y').'.sql'
	fwrite ( $handle, $schema );
	fclose ( $handle );
// 	echo $return;
}

function create_database() {
	
	if( file_exists(DATABASE) ) {
	
	} else {
		
			try {
					$db = new PDO('sqlite:' . DATABASE);

		    } catch(PDOException $e) {

			  }
	 }		
}

function execute_database_query() {
	
	try {
		// database object
		$db = new PDO('sqlite:' . DATABASE) or die("Unable to open database");
		
	} catch(PDOException $e) {
	
	}
	
	try
	{
		// open the schema file
		$raw_queries = file_get_contents(SQLITE_DATABASE_SCHEMA) or die("Unable to open file");
		
	} catch(Exception $raw_queries) {
		echo $raw_queries->getMessage();
	}
	
	$raw_queries = explode(";", $raw_queries);
	$num_query=sizeof($raw_queries);
	
	for($index=1;$index<=$num_query;$index++) {
		
		try { 
			// Perform the query
			$db->query($raw_queries[$index]);
		}
		
		catch(Exception $e){
		
		}
	}
}

export_sqlite_schema ( "localhost", "admin", "admin", 'Mysql_database_name', '*', '' ); //backup_tables($dbhost, $dbuser, $dbpass, $dbname, '*', $dir);
create_database();
execute_database_query();

?>