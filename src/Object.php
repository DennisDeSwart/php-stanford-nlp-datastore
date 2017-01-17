<?php

class Object {
   
    public $conn;
    public function __construct($conn) {
        
        $this->conn = $conn;      
    }
    
    public function create($openieId, $wordId){
       
        $this->conn->insert('object', array(
            
            'openieId'  => $openieId,
            'wordId'    => $wordId,
        ));  
        
        return $this->conn->lastInsertId();;
    }
}
