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
function getCategoryTree($db) {
  // Make Category Tree
  $query = "SELECT `COMPLAINT_TYPE`, COUNT(*) AS count FROM `cases` WHERE `BOROUGH` != '' GROUP BY `COMPLAINT_TYPE` ORDER BY count DESC";
  foreach( $db->query($query) as $row ) {
    $complaintType = (string)($row["COMPLAINT_TYPE"] ?? '');
    $category['name'] = trim($complaintType, ' /');
    $category['COMPLAINT_TYPE'] = $complaintType;
    $category['slug'] = slugify($category['name']);
    $category['count'] = $row["count"];
    if( $category['slug'] != 'n-a'  && $category['slug'] != 'select-one' && $category['slug'] != 'other' ){
      $topCategories[$category['slug']]=$category;
    }
  }

  $query = "SELECT `COMPLAINT_TYPE`, `DESCRIPTOR`, COUNT(*) AS count FROM `cases` WHERE `BOROUGH` != '' GROUP BY `COMPLAINT_TYPE`, `DESCRIPTOR` ORDER BY count DESC";
  foreach( $db->query($query) as $row ) {
    $descriptor = (string)($row["DESCRIPTOR"] ?? '');
    $subCategory['name'] = trim($descriptor, ' /');
    $subCategory['DESCRIPTOR'] = $descriptor;
    $subCategory['slug'] = slugify($subCategory['name']);
    $subCategory['count'] = $row["count"];
    if(
      trim((string)($row['COMPLAINT_TYPE'] ?? '')) != ''
      && $subCategory['slug'] != 'n-a'
      && $subCategory['slug'] != 'select'
    ){
      $topCategories[slugify($row['COMPLAINT_TYPE'])]['subCategories'][$subCategory['slug']]=$subCategory;
    }
  }

  return $topCategories;
}


?>
