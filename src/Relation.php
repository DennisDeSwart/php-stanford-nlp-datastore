<?php


class Relation {
    
    public $conn;  
    public function __construct($conn) {
        
        $this->conn = $conn;      
    }
    
    public function create($openieId, $wordId){
       
        $this->conn->insert('relation', array(
            
            'openieId'  => $openieId,
            'wordId'    => $wordId,
        ));  
       
        return $this->conn->lastInsertId();
    }
}
