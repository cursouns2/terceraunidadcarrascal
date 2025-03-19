```php
  <?php
  if (function_exists('curl_version')) {
      echo 'cURL está habilitado.';
      $curl_version = curl_version();
      echo '<pre>';
      print_r($curl_version);
      echo '</pre>';
  } else {
      echo 'cURL NO está habilitado.';
  }
  ?>
  ```
