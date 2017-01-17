<?php

class Word {
    
    public function __construct($conn) {
        
        $this->conn = $conn;    
    }
    
    /**
     * Gets word from DB with sentenceId and wordIndex
     */
    public function getWord($sentenceId, $wordIndex){
        
        $query = $this->conn->createQueryBuilder();
        $query->select('w.*')
        ->from('word', 'w')
        ->where('w.sentenceId = '.$sentenceId)
        ->andWhere('w.wordIndex = '.$wordIndex)
        ;
       
        $w = $query->execute();
        return $w->fetch(); // there can be only 1 result here, so do not do "fetchAll"
    }
   
    /**
     * Saves all words in a sentenceTree to the "word" database table
     * 
     * @returns array that matches the CoreNLP "sentenceIDs" to the new database "sentenceIDs"
     */
    public function saveWordList($coreNLP){
       
        $corenlp_sentence_id = array();
        
        // get the sentences data
        $serverData = $coreNLP->serverMemory[0]['sentences'];
        $sentenceClass = new Sentence($this->conn);
    
        // go through all the trees
        foreach($coreNLP->trees as $treeId => $sentenceTree){
            
            // create a new sentenceId in the DB
            $sentenceId = $sentenceClass->create();

            // this array matches the CoreNLP sentenceId to the Database sentenceId
            $corenlp_sentence_id[$treeId] = $sentenceId;
            
            foreach($sentenceTree as $key => $node){
            
                if(array_key_exists('index', $node)){
                    // create word entry in DB
                    $this->create($node, $sentenceId);
                }        
            }
        }       
        return $corenlp_sentence_id;
    }
    
     private function create($word, $sentenceId){
     
        $this->conn->insert('word', array(
            'sentenceId' => $sentenceId,
            'wordIndex' => $word['index'],
            'value' => $word['word'],
            'lemma' => $word['lemma'],
            'tag'   => $word['pennTreebankTag'],
            'priority' => 0, // this is not used in current version
            'time'  => date("Y-m-d H:i:s")   
        ));  
        
        return $this->conn->lastInsertId();
    }
    
    public function getWordList() {
        
        $query = $this->conn->createQueryBuilder();
        $query->select('w.*, s.*')
        ->from('word', 'w')
        ->innerJoin('w', 'subject', 's', 'w.id = s.wordId')

            ;
        $sub = $query->execute();
        $subjects = $sub->fetchAll(PDO::FETCH_ASSOC);


        $query = $this->conn->createQueryBuilder();
        $query->select('w.*, r.*')
                ->from('word', 'w')
                ->innerJoin('w', 'relation', 'r', 'w.id = r.wordId')
             ;
        $rel = $query->execute();
        $relations = $rel->fetchAll(PDO::FETCH_ASSOC);


        $query = $this->conn->createQueryBuilder();
        $query->select('w.*, o.*')
                ->from('word', 'w')
                ->innerJoin('w', 'object', 'o', 'w.id = o.wordId')
             ;
        $obj = $query->execute();
        $objects = $obj->fetchAll(PDO::FETCH_ASSOC);

        $words = array(
            'subjects' => $subjects,
            'relations' => $relations,  
            'objects' => $objects,
        );
        
        return $words;
    }
    
    /**
     * Matches a search word in the wordlist
     * Creates a list of matches that is used by OpenIE to find relations
     */
    public function matchSearchTerm($searchTerm, $wordList){
    
        $matches = array();
        
        foreach($wordList as $type => $nodes){
            
            foreach($nodes as $node){

                if($node['value'] == $searchTerm){
                    $matches[$node['openieId']] = $type;
                }
            }
        }
        
        return $matches;
    }
}
