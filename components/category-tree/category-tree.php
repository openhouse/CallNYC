<?php
function getCategoryTree($db) {
  // Make Category Tree
  $query = "SELECT `COMPLAINT_TYPE`, COUNT(*) FROM `cases` WHERE `BOROUGH` != '' GROUP BY `COMPLAINT_TYPE` ORDER BY COUNT(*) DESC";
  foreach( $db->query($query) as $row ) {
    $category['name'] = trim($row["COMPLAINT_TYPE"], ' /');
    $category['COMPLAINT_TYPE'] = $row["COMPLAINT_TYPE"];
    $category['slug'] = slugify($category['name']);
    $category['count'] = $row["COUNT(*)"];
    if( $category['slug'] != 'n-a'  && $category['slug'] != 'select-one' && $category['slug'] != 'other' ){
      $topCategories[$category['slug']]=$category;
    }
  }

  $query = "SELECT `COMPLAINT_TYPE`, `DESCRIPTOR`, COUNT(*) FROM `cases` WHERE `BOROUGH` != '' GROUP BY `COMPLAINT_TYPE`, `DESCRIPTOR` ORDER BY COUNT(*) DESC";
  foreach( $db->query($query) as $row ) {
    $subCategory['name'] = trim($row["DESCRIPTOR"], ' /');
    $subCategory['DESCRIPTOR'] = $row["DESCRIPTOR"];
    $subCategory['slug'] = slugify($subCategory['name']);
    $subCategory['count'] = $row["COUNT(*)"];
    if(
      trim($row['COMPLAINT_TYPE']) != ''
      && $subCategory['slug'] != 'n-a'
      && $subCategory['slug'] != 'select'
    ){
      $topCategories[slugify($row['COMPLAINT_TYPE'])]['subCategories'][$subCategory['slug']]=$subCategory;
    }
  }

  return $topCategories;
}


?>
