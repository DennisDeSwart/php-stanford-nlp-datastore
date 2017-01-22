<?php

/**
 * Log errors: Development purposes only.
 */
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('max_execution_time', 300); // 300 seconds, because some analysis can take up to 1 or 2 minutes.

   
/**
 * Use the online API?
 * 
 * IMPORTANT NOTE: For "Datastore" to work properly this needs to be off. The online API does not provide OpenIE data.
 */    
    define('ONLINE_API', FALSE);  // 
     
 /**
  * Stanford API URL configuration
  */
    define('ONLINE_URL' , 'http://nlp.stanford.edu:8080/corenlp/process?outputFormat=json&Process=Submit&input='); // add url encoded text to the end

/**
 * Guzzle is used to make HTTP calls to the CoreNLP server.
 * 
 * If true: HTTP calls are used (recommended)
 * If false: cURL command line is used
 */    
    define('USE_GUZZLE', TRUE);
       
/**
 * Java version configuration
 */
    define('CURLURL' , 'http://localhost:9000/');
    
    // used for CoreNLP version 3.7.0
    define('CURLPROPERTIES' , '%22prettyPrint%22%3A%22true%22');
    
    // if you want specific annotators, you can do it like this:
    // define('CURLPROPERTIES' , '%22annotators%22%3A%22tokenize%2Cregexner%2Cparse%2Cdepparse%2Cpos%2Clemma%2Cmention%2Copenie%2Cner%2Ccoref%2Ckbp%22%2C%22prettyPrint%22%3A%22true%22');
    
/**
 * Start composer autoloader
 */
    if(!@include_once(__DIR__.'/vendor/autoload.php')) {
        echo '<br />NLP Datastore error: could not load "Composer" files. <br /><br />'
          . '- Run "composer update" on the command line<br />'
          . '- If Composer is not installed, go to: <a href="https://getcomposer.org/">install Composer</a></p>';
        die;
    }
 
/**
 * Database directory constant
 */    
    define('DB_DIR', __DIR__);
    define('DB_NAME', 'datastore.db');

    // You can use "$db->conn" to connect to the database
    $db = new Database();
 