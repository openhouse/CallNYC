<?php
  //Connect to a MySql database
  /*
  $connection = @mysql_connect($sqlserver,$sqlusername,$sqlpassword) or die ("Could not connect!");
  $db = @mysql_select_db($dbname, $connection) or die("Could not select a valid database.");
  */
  $dsn = 'mysql:host='.$sqlserver.';dbname='.$dbname;
  $username = $sqlusername;
  $password = $sqlpassword;
  $options = array(
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
  );

  $db = new PDO($dsn, $username, $password);


?>
