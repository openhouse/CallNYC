<?php
function slugify($text) {
  /*
  $text = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", trim($text));
  $text = str_replace(' ', '-', $text);
  $text = preg_replace('~-+~', '-', $text);
  if (empty($text))
  {
    return 'n-a';
  }
  return $text;
  */

  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;

}
?>
