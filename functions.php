<?php
/**
 * Merge PDF files including password protected
 * Author Rahul <naikrahulda@gmail.com>
 * @see ilovepdf https://developer.ilovepdf.com/docs/guides/getting-started
 * @see pdftk http://www.angusj.com/pdftkb/
 */
// unlock pdf file
function unlockfile($file) {
  // Create public key in https://developer.ilovepdf.com/
  // Or use my key if files are remaining you will be able to see demo
  // project_public_c4b90e72df52e0049583830c8c25dc5d_Evizdf6d7a04e201832badd0d41823c491a5a
  $public_key = 'project_public_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
  $localfile  = $file;

  // Get Auth token
  // https://api.ilovepdf.com/v1/auth (POST), public_key
  // print_r(curlit('https://api.ilovepdf.com/v1/auth', 'POST', '', array('public_key' => $public_key)));
  // exit;
  $token = (curlit('https://api.ilovepdf.com/v1/auth', 'POST', '', array('public_key' => $public_key)))->token;

  // Get Server and task
  // https://api.ilovepdf.com/v1/start/{tool} (GET)
  $authorization = "Authorization: Bearer $token";
  $url           = 'https://api.ilovepdf.com/v1/start/unlock';
  $result        = curlit($url, 'GET', $authorization);
  $server        = $result->server;
  $task          = $result->task;

  // Upload file
  // https://{server}/v1/upload (POST), task, file
  $url           = "https://$server/v1/upload";

  $server_filename = curlit($url, 'POST', $authorization,
    array('task' => $task,
          'file' => curl_file_create($localfile, '', ''))
          )->server_filename;

  // Process file
  // https://{server}/v1/process (POST), task, tool, files
  $url = "https://$server/v1/process";
  $obj = array(new File($server_filename, $localfile));
  $downloadFile = curlit($url,
  'POST',
  $authorization,
    json_encode(array('task'  => $task,
          'tool'  => 'unlock',
          'files' => $obj)),
          'json'
        )->download_filename;

  // Download file
  // https://{server}/v1/download/{task} (GET)
  $url        = "https://$server/v1/download/$task";
  $outputFile = curlit($url, 'GET', $authorization, array(), 'download');

  // Replace algorithm
  $destination = $localfile;

  // Let's remove exsisting file with password protection
  if(file_exists($localfile))
    unlink($localfile);

  $file = fopen($destination, "w+");
  fputs($file, $outputFile);
  fclose($file);
}

function curlit($url, $type = 'GET', $authorization = '', $data = array(), $datatype = 'normal') {
    $ch = curl_init($url);

    if($authorization != '') {
      if($datatype == 'json') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json' , $authorization));
      } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data', $authorization));
      }
    }

   curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   if($type == 'POST' && !empty($data)) {
     curl_setopt($ch, CURLOPT_POST, true);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
   }

   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

   // Code for download file from server
   if($datatype == 'download') {
     curl_setopt($ch, CURLOPT_ENCODING, '');
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
     curl_setopt($ch, CURLOPT_HEADER, true);
     try {
       $response    = curl_exec($ch);
     } catch (Exception $e) {
       print_r($e->getMessage());
     }

     if(property_exists($response, 'error')) {
       echo $url . '<br><br>' . $response->error->type . '<br><br>'.
            $response->error->code . '<br><br>' . $response->error->message;
       exit;
     }
     $info        = curl_getinfo($ch);
     curl_close($ch);
     $httpCode    = $info['http_code'];
     $header_size = $info['header_size'];
     $header      = substr($response, 0, $header_size);
     return substr($response, $header_size);
   }
   try {
      $result = json_decode(curl_exec($ch));
   } catch (Exception $e) {
     print_r($e->getMessage());
   }

   curl_close($ch);
   if(property_exists($result, 'error')) {
     echo $url . '<br><br>' . $result->error->type . '<br><br>'.
          $result->error->code . '<br><br>' . $result->error->message;
     exit;
   }
   return $result;
}
