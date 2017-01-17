<?php


class Subject {
  
    public $conn;   
    public function __construct($conn) {
        
        $this->conn = $conn;      
    }
    
    public function create($openieId, $wordId){
       
        $this->conn->insert('subject', array(
            
            'openieId'  => $openieId,
            'wordId'    => $wordId,
        ));  
        
        return $this->conn->lastInsertId();;
    }
}
