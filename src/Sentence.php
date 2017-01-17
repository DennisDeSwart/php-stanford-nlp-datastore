<?php

class Sentence {
    
    public $conn;
    public $structure;
    public $purpose;
           
    public function __construct($conn) {
        
        $this->conn = $conn;
    }
    
   /**
    * Create sentence
    */
    public function create($structure = '', $purpose = ''){
       
        $this->conn->insert('sentence', array(
            
            'structure' => $structure,
            'purpose'   => $purpose
        ));  
        
        return $this->conn->lastInsertId();
    }
}
