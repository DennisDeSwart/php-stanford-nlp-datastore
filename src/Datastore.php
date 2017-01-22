<?php

class Datastore {
    
    public $conn;
    public function __construct($conn) {
        
        $this->conn = $conn;      
    }
  
    /**
     * This function combines classes 
     */ 
    public function storeNLP($coreNLP){
        
        // save words && get lastInsertIds for the sentences
        $w = new Word($this->conn);
        $sentenceIds = $w->saveWordList($coreNLP);

        // save openIE relations
        $open = new OpenIE($this->conn);
        $createdSentenceIds = $open->saveOpenie($coreNLP, $sentenceIds);
                  
        // process Coreferences
        $coref = new Coreference($this->conn);
        $coref->storeCoreference($coreNLP, $createdSentenceIds);

        //Process the Named-Entity-Recognition (NER)
        $ner = new NER($this->conn);
        $ent = $ner->getNerEntities($coreNLP, $createdSentenceIds);
        $ner->storeEntities($ent);

        $tok = $ner->getNerTokens($coreNLP, $createdSentenceIds);
        $ner->storeTokens($tok);           

        return $sentenceIds;
    }
}
