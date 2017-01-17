<?php

/**
 * Stores OpenIE, NER and Coreference data in a SQLite database
 */

class Database {
    
    public $config;
    public $conn; // this variable keeps the connection
    
    /**
     * Creates a connection
     */
    public function __construct() {
        $this->config = new \Doctrine\DBAL\Configuration();
        $this->getConnection();
    }
    
    private function getConnection(){
         
        $connectionParams = array(
            'url' => 'sqlite:/'.DB_DIR.'/'.DB_NAME,
        );
        $this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $this->config);
        
        return $this->conn;
    }
    
    /**
     * - Erases all data from tables in the database file. 
     * - This function does not change the table structure
     */
    public function clearAllTables(){
        
        // Tables need to be cleared in a specific order, because of foreign key dependencies
        
        // Subject-Relation-Object
        $this->truncate('subject');
        $this->truncate('relation');
        $this->truncate('object');
        
        // NER
        $this->truncate('ner_timex');
        $this->truncate('ner_entity');
        $this->truncate('ner_token');
        
        // OpenIE
        $this->truncate('openie');
        
        // corefs
        $this->truncate('coref_node');
        $this->truncate('coref');
        
        // Sentence && Word
        $this->truncate('sentence');
        $this->truncate('word');
    }
    

    /**
     * Function to truncate a table
     */
    private function truncate($table){
        
        $result = false;
        
        if($table){
            $query = $this->conn->prepare('DELETE FROM '.$table);
            $result = $query->execute();
        }
        
        $this->vacuum();
        
        return $result;
    }
    
    /**
     * Makes table smaller after deleting data. Mostly used as helper for truncate
     */
    private function vacuum(){
        
        $query = $this->conn->query('VACUUM');
        $result = $query->execute();
        
        return $result; 
    }
}   
