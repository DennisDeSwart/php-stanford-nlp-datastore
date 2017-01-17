<?php
// TODO: rewite //

class OpenIE {
    
    public $conn;
    public function __construct($conn) {
        
        $this->conn = $conn;
    }
  
    public function create($sentenceId = ''){
       
        $this->conn->insert('openie', array(
            
            'sentenceId' => $sentenceId,
        ));  
        
        return $this->conn->lastInsertId();;
    }
    
    
    public function openieSearch($searchTerm = ''){
        
        $result = array();
        
        // Get word and openIE lists from database
        $word = new Word($this->conn);
        $wordList = $word->getWordList();
        $openIE_list = $this->openIE_list($wordList);
             
        if(!empty($searchTerm)){
            $result = $this->openieSearchTerm($openIE_list, $searchTerm);
        } else {
            $result = $this->openieSearchAll($openIE_list);
        }
        
        return $result;
    }
    
    /**
     * Searches a term
     * 
     * Note: this is an internal function use "openieSearch" for searching openIE  
     */
    private function openieSearchTerm($openIE_list, $searchTerm){
        
        $result = array();
        $word = new Word($this->conn);
        $wordList = $word->getWordList();
        $wordMatches = $word->matchSearchTerm($searchTerm, $wordList);      
        foreach($wordMatches as $openieId => $word){
            
            $result[$openieId]['openIE #'] = '';
            $result[$openieId]['sentence #'] = '';
            $result[$openieId]['subjects'] ='';
            $result[$openieId]['relations'] ='';
            $result[$openieId]['objects'] = '';

            foreach($openIE_list[$openieId] as $node){
                
                 // always display sentenceId / wordIndex
                $result[$openieId]['openIE #'] = $openieId;
                $result[$openieId]['sentence #'] = $node['sentenceId'];               
                
                if($node['role'] == 'subjects'){
                    $result[$openieId]['subjects'] .= ' '.$node['value'];
                }

                if($node['role'] == 'relations'){
                    $result[$openieId]['relations'] .= ' '.$node['value'];
                }

                if($node['role'] == 'objects'){
                    $result[$openieId]['objects'] .= ' '.$node['value'];
                }
            }
        }     
        return $result;
    }
    
    /**
     * Searches all openIE
     * 
     * Note: this is an internal function use "openieSearch" for searching openIE  
     */
    
    private function openieSearchAll($openIE_list){ // TODO function that does a few words or all
        
        $result = array();
        
        foreach($openIE_list as $openieId => $listNode){
            
            $result[$openieId]['openIE #'] = '';
            $result[$openieId]['sentence #'] = '';
            $result[$openieId]['subjects'] ='';
            $result[$openieId]['relations'] ='';
            $result[$openieId]['objects'] = '';           
            
            foreach($listNode as $node){
                
                // always display sentenceId / wordIndex
                $result[$openieId]['openIE #'] = $openieId;
                $result[$openieId]['sentence #'] = $node['sentenceId'];               
                
                if($node['role'] == 'subjects'){
                    $result[$openieId]['subjects'] .= ' '.$node['value'];
                }

                if($node['role'] == 'relations'){
                    $result[$openieId]['relations'] .= ' '.$node['value'];
                }

                if($node['role'] == 'objects'){
                    $result[$openieId]['objects'] .= ' '.$node['value'];
                }
            }
        }
        return $result;
    }
    
    public function displaySearch($searchResult){
         
        echo '<br />OpenIE:<br />'; 

        if(is_array($searchResult)){
            foreach($searchResult as $openieId => $role){
                echo implode(' ', $role['subjects']) . ' '.implode(' ', $role['relations']).' '. implode(' ', $role['objects']);
                echo '<br />';
            }
        } else {
            echo 'No valid openIE results';
        }
    }
    
    
    public function saveOpenie($coreNLP, $sentenceIds){
        
         // get the sentences data from $serverMemory
        $sentences      = $coreNLP->serverMemory[0]['sentences'];
        $sentenceTrees  = $coreNLP->trees;
        $word           = new Word($this->conn);
        $s_r_o          = array(); // sentence-relation-object array
        $corenlp_stn_id = array(); // array that matches coreNLP sentenceId to the SQLite sentenceId
  
        // for every sentence, go through OpenIE entries
        foreach($sentenceTrees as $treeId => $sentenceTree){
            
            // get the DB sentenceId
            $dbSentenceId = $sentenceIds[$treeId];
            
            // array that matches coreNLP sentenceId to the SQLite sentenceId
            $corenlp_stn_id[$treeId] = $dbSentenceId;
            
            // count the openIE entries for this sentence
            $openIE_count = count($sentences[$treeId]['openie']);
            for ($i = 0; $i < $openIE_count; $i++) {

                // create an openIE entry in the DB
                $openieId = $this->create($dbSentenceId);

                 /**
                  *  Search trees for openIE entries
                  */
                foreach($sentenceTree as $key => $node){
                 
                    $tag_role = '';
                    if(array_key_exists('openIE', $node)){
                         $openIE = $node['openIE'];

                        if(array_key_exists($i, $openIE)){
                            $tag_role = $node['openIE'][$i];
                            $index = $node['index'];      
                            
                            $w = $word->getWord($dbSentenceId, $index);
                            $s_r_o[$openieId][$w['id']] = $tag_role;
                        }                 
                    }
                } 
                     
            } // end OpenIE loop
   
        } // end sentence loop    
        
        /**
         * Create S-R-O entries
         */
        foreach($s_r_o as $openieId => $wordId_role){
        
            foreach ($wordId_role as $wordId => $role){

                if($role == 'subject'){
                    // create subject
                    $sub = new Subject($this->conn);
                    $sub->create($openieId, $wordId);  
                }

                if($role == 'relation' ){
                      // create subject
                    $rel = new Relation($this->conn);
                    $rel->create($openieId, $wordId);  
                }

                if($role == 'object'){
                      // create subject
                    $obj = new Object($this->conn);
                    $obj->create($openieId, $wordId);  
                }
            }
        } // end S-R-O entries      
        
        return $corenlp_stn_id;
    }
    
    
    /**
     * Creates OpenIE list from a Word list
     */
    public function openIE_list($wordList){
        
        $openIE_list = array();
     
        foreach($wordList as $role => $nodes){
            foreach($nodes as $node){

                $openIE_list[$node['openieId']][]= array(
                    'sentenceId' => $node['sentenceId'],
                    'wordIndex' => $node['wordIndex'],
                    'role' => $role,
                    'wordId' => $node['wordId'],
                    'value' => $node['value'],

                );
            }
        }       
        return $openIE_list;
    }
}
