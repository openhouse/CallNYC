<?php
/*
Copyright 2016 Thick Arts LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

  include_once('functions.php');
  include_once('library/db.php');

  if (is_archived()) {
    http_response_code(403);
    echo 'Archived mode enabled.';
    exit;
  }

  $db = get_db_connection();

  $url = "https://data.cityofnewyork.us/api/views/edai-dig6/rows.csv?accessType=DOWNLOAD&bom=false&query=select+*";

  $n=0;
  $valid = false;
  $insertStatement = null;

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
          $insertStatement = $db->prepare(
            "REPLACE INTO `cases` (`UNIQUE_KEY`, `ACCOUNT`, `OPENDATE`, `COMPLAINT_TYPE`,
              `DESCRIPTOR`, `ZIP`, `BOROUGH`, `CITY`, `COUNCIL_DIST`, `COMMUNITY_BOARD`, `CLOSEDATE`,
              `OPENDATE_INT`, `CLOSEDATE_INT`) VALUES
              (:unique_key, :account, :opendate, :complaint_type, :descriptor, :zip, :borough,
              :city, :council_dist, :community_board, :closedate, :opendate_int, :closedate_int)"
          );
        }
      } else {
        $data[]=strtotime($data[array_search('OPENDATE', $keys)]);
        $data[]=strtotime($data[array_search('CLOSEDATE', $keys)]);

        if($valid && $insertStatement){
          $insertStatement->execute([
            ':unique_key' => $data[array_search('UNIQUE_KEY', $keys)],
            ':account' => $data[array_search('ACCOUNT', $keys)],
            ':opendate' => $data[array_search('OPENDATE', $keys)],
            ':complaint_type' => $data[array_search('COMPLAINT_TYPE', $keys)],
            ':descriptor' => $data[array_search('DESCRIPTOR', $keys)],
            ':zip' => $data[array_search('ZIP', $keys)],
            ':borough' => $data[array_search('BOROUGH', $keys)],
            ':city' => $data[array_search('CITY', $keys)],
            ':council_dist' => $data[array_search('COUNCIL_DIST', $keys)],
            ':community_board' => $data[array_search('COMMUNITY_BOARD', $keys)],
            ':closedate' => $data[array_search('CLOSEDATE', $keys)],
            ':opendate_int' => $data[array_search('OPENDATE_INT', $keys)],
            ':closedate_int' => $data[array_search('CLOSEDATE_INT', $keys)],
          ]);
        }
      }

      $n++;
    }
  }

  echo $n;
  include_once('library/closedb.php');
?>
success!
