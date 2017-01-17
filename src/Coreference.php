<?php

class Coreference {
    
    public $conn;
    public function __construct($conn) {
        
        $this->conn = $conn;
    }
    
    /**
     * Stores coreferences from the given text
     */
    public function storeCoreference($coreNLP, $sentenceIds){
        
        if(array_key_exists('corefs', $coreNLP->serverMemory[0])){
                
            $corefs  = $coreNLP->serverMemory[0]['corefs'];            
            foreach ($corefs as $corenlp_id => $coref){

                $this->conn->insert('coref', array(
                    'corenlp_id' => $corenlp_id,    
                ));
                
                $lastCorefId = $this->conn->lastInsertId();
               
                foreach($coref as $node){
                   $this->insertNode($node, $lastCorefId, $sentenceIds);
                } 
            }        
        }
    }  
    
    /**
     * Helper function for storeCoreference
     */
    private function insertNode($node, $lastInsertId, $sentenceIds){
        
        // headIndex does not always exist
        $headIndex = '';
        if(array_key_exists('headIndex', $node)){
            $headIndex = $node['headIndex'];
        }
    
        $this->conn->insert('coref_node', array(
            'coref_id' => $lastInsertId,
            'text' => $node['text'],
            'type' => $node['type'],
            'number' => $node['number'],
            'gender' => $node['gender'],
            'animacy' => $node['animacy'],
            'startIndex' => $node['startIndex'],
            'endIndex' => $node['endIndex'],
             'headIndex' => $headIndex, 
            'sentenceId' => $sentenceIds[$node['sentNum']-1], // the lastInsertedId from "Sentence" table
            'position_0' => $node['position']['0'],
            'position_1' => $node['position']['1'],
            'isRepMention' => $node['isRepresentativeMention'],       
        ));  
    }
    
    /**
     * Search function for Coreference
     */
    public function corefSearch($searchTerm = ''){
        
        $result = array();
        
        if(!empty($searchTerm)){
            $result = $this->corefSearchTerm($searchTerm);
        } else {
            $result = $this->corefSearchAll();
        }
        
        return $result;
    }
    
    /**
     * Searches a coref by term
     */
    private function corefSearchTerm($searchTerm){
      
        $query = $this->conn->createQueryBuilder();
        $query->select('cn.*')
        ->from('coref_node', 'cn')
        ->where('text = ?')
        ->setParameter(0, $searchTerm)
        ;      
        $cn = $query->execute();
        $coref  = $cn->fetch();
      
        // now get the referenced values too
        $query = $this->conn->createQueryBuilder();
        $query->select('cn.*')
        ->from('coref_node', 'cn')
        ->where('coref_id = ?')
        ->setParameter(0, $coref['coref_id'])
        ;      
        $cn = $query->execute();
       
        return $cn->fetchAll(); 
    }
    
    /**
     * Searches all corefs
     */
    
    private function corefSearchAll(){ // TODO function that does a few words or all
       
        $query = $this->conn->createQueryBuilder();
        $query->select('cn.*')
        ->from('coref_node', 'cn')      
        ;
       
        $cn = $query->execute();
        return $cn->fetchAll(); 
    }
}
