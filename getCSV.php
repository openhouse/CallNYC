<?php
  include_once('library/dbinfo.php');
  include_once('library/opendb.php');
  include_once('functions.php');

  $url = "https://data.cityofnewyork.us/api/views/edai-dig6/rows.csv?accessType=DOWNLOAD&bom=false&query=select+*";

  $n=0;
  $valid = false;
  if (($handle = fopen($url, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

      if($n==0){
        // first row is column names
        // validate csv here as well

        $j=0;
        foreach($data as &$key){
          $keys[$j] = trim($key);
          $j++;
        }
        $keys[] = 'OPENDATE_INT';
        $keys[] = 'CLOSEDATE_INT';

        if( in_array('UNIQUE_KEY', $keys)
          && in_array('ACCOUNT', $keys)
          && in_array('OPENDATE', $keys)
          && in_array('CLOSEDATE', $keys)
          && in_array('COMPLAINT_TYPE', $keys)
          && in_array('DESCRIPTOR', $keys)
          && in_array('BOROUGH', $keys)
        ){
          $valid = true;
          // empty the table to remove deleted cases (spam etc)
          $db->query("TRUNCATE TABLE `cases`");
        }
        var_dump($keys);
      } else {
        $data[]=strtotime($data[array_search('OPENDATE', $keys)]);
        $data[]=strtotime($data[array_search('CLOSEDATE', $keys)]);

        if($valid){
          $query = "REPLACE INTO `cases` (`UNIQUE_KEY`, `ACCOUNT`, `OPENDATE`, `COMPLAINT_TYPE`,
            `DESCRIPTOR`, `ZIP`, `BOROUGH`, `CITY`, `COUNCIL_DIST`, `COMMUNITY_BOARD`, `CLOSEDATE`,
            `OPENDATE_INT`, `CLOSEDATE_INT`) VALUES
            (
              '".$data[array_search('UNIQUE_KEY', $keys)]."',
              '".$data[array_search('ACCOUNT', $keys)]."',
              '".$data[array_search('OPENDATE', $keys)]."',
              '".$data[array_search('COMPLAINT_TYPE', $keys)]."',
              '".$data[array_search('DESCRIPTOR', $keys)]."',
              '".$data[array_search('ZIP', $keys)]."',
              '".$data[array_search('BOROUGH', $keys)]."',
              '".$data[array_search('CITY', $keys)]."',
              '".$data[array_search('COUNCIL_DIST', $keys)]."',
              '".$data[array_search('COMMUNITY_BOARD', $keys)]."',
              '".$data[array_search('CLOSEDATE', $keys)]."',
              ".$data[array_search('OPENDATE_INT', $keys)].",
              ".$data[array_search('CLOSEDATE_INT', $keys)]."
            )";

          //$query = "REPLACE INTO `cases` (`".implode('`, `', $keys)."`) VALUES ('".implode("', '", $data)."')";

          $db->query($query);
          //echo "\n".$query."\n\n";
        }



        //var_dump($data);
      }

      /*
      if($n > 2){
        exit;
      }
      */

      /*process your data here*/
      $timestamp = $data[0]; //timestamp
      $orderId = $data[1];
      $productId = $data[2];
      $stars = $data[3];
      $review = $data[4];

      $n++;
    }
  }

  echo $n;
  include_once('library/closedb.php');
?>
success!
