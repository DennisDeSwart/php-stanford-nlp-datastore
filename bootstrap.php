<?php

/**
 * Log errors: Development purposes only.
 */
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('max_execution_time', 300); // 300 seconds

   
/**
 * Use the online API?
 */    
    define('ONLINE_API', FALSE);  // As far as I know, only the Java version offers OpenIE
     
 /**
  * Stanford API URL configuration
  */
    define('ONLINE_URL' , 'http://nlp.stanford.edu:8080/corenlp/process?outputFormat=json&Process=Submit&input='); // add url encoded text to the end
        
/**
 * Java version configuration
 */
    define('CURLURL' , 'http://localhost:9000/');
    define('CURLPROPERTIES' , '%22prettyPrint%22%3A%22true%22');
    
/**
 * Start composer autoloader
 */
    require __DIR__.'/vendor/autoload.php';
 
/**
 * Database directory constant
 */    
    define('DB_DIR', __DIR__);
    define('DB_NAME', 'datastore.db');

    // You can use "$db->conn" to connect to the database
    $db = new Database();
 