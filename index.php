<?php
/**
 * Merge PDF files including password protected
 * Author Rahul <naikrahulda@gmail.com>
 * @see ilovepdf https://developer.ilovepdf.com/docs/guides/getting-started
 * @see pdftk http://www.angusj.com/pdftkb/
 */

 require ('classes.php');
 require ('functions.php');

try {

  // Let's check all pdf files in directory
  foreach (glob('*.pdf') as $filename) {
    if(file_exists($filename) && filesize($filename) > 0) {
      $handle   = fopen($filename, "r");
      $contents = fread($handle, filesize($filename));
      fclose($handle);
      if (stristr($contents, "/Encrypt"))
      {
        unlockfile($filename);
      }
    }
    else
    {
      echo "Please check file - $filename";
      exit;
    }
  }

  if(file_exists('bigmergedfile.pdf'))
    unlink('bigmergedfile.pdf');
    
  // Merge all pdf files (using pdftk)
  // NOTE - Change Installation path
  exec('/usr/local/bin/pdftk *.pdf cat output bigmergedfile.pdf');
  echo ' Done. ';
} catch (Exception $e) {
  print_r($e->getMessage());
}
