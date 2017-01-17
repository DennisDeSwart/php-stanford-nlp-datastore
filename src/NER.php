<?php

/**
 * Technical notes:
 * 
 * - There are two NER tables:
 *      1) Entity : this keeps the whole name of a person, or a place like "New York"
 *      2) Token  : this keeps the seperate words/tokens like "New" + "York"
 * 
 * - NER entries can be uniquely identified by using the "sentenceId" and the "wordIndex"
 */

class NER {
    
    public $conn;
    public function __construct($conn) {
        
        $this->conn = $conn;
    }
    
/**
 * NER ENTITY FUNCTIONS
 */
    
    public function getNerEntities($coreNLP, $createdSentenceIds){
        
        // get sentences
        $sentences = $coreNLP->serverMemory[0]['sentences'];

        // for every sentence get the entities
        $entityResult = array();
        foreach($sentences as $sentenceId => $sentence){
            
            if(array_key_exists('entitymentions', $sentence)){

                foreach($sentence['entitymentions'] as $entity){
                    
                    // we use DB sentenceIds, not CoreNLP sentenceIds
                    $entityResult[$createdSentenceIds[$sentenceId]][] = $this->getEntityArray($entity);
                }
            }
        }           
        return $entityResult;
    }
    
    private function getEntityArray($entity){
        
        $nerArray = array();

        // check for NER type
        if(array_key_exists('ner', $entity)){
            $nerArray['ner_type'] = $entity['ner'];
        } else {
            $nerArray['ner_type'] = '';
        }
        
        // check for normalized NER
        if(array_key_exists('normalizedNER', $entity)){
            $nerArray['ner_normalized_text'] = $entity['normalizedNER'];
        } else {
            $nerArray['ner_normalized_text'] = '';
        }

        // check for time/ date
        if(array_key_exists('timex', $entity)){
            $nerArray['timex'] = $entity['timex'];

            if(!array_key_exists('altValue', $entity['timex'])){
                $nerArray['timex']['altValue'] = '';
            }
        }

        // get ner text
        $nerArray['ner_text'] = $entity['text'];

        // get begin id
        $nerArray['tokenBegin'] = $entity['tokenBegin'];

        // get end id
        $nerArray['tokenEnd'] = $entity['tokenEnd'];

        return $nerArray;         
    }

    public function storeEntities($nerEntities){
    
        $insertIds = array();
        
        foreach($nerEntities as $sentenceId => $sentenceEntities){
            
            foreach($sentenceEntities as $entityId => $entity){
                
                $this->conn->insert('ner_entity', array(
                    'sentenceId' => $sentenceId,    
                    'ner_text' => $entity['ner_text'],
                    'ner_normalized_text' => $entity['ner_normalized_text'],
                    'ner_type' => $entity['ner_type'],
                    'tokenBegin' => $entity['tokenBegin'],
                    'tokenEnd' => $entity['tokenEnd'],
                ));  

                $insertIds[$sentenceId][$entityId] =  $this->conn->lastInsertId();
              
                if(array_key_exists('timex', $entity)){
                    
                    if(array_key_exists('value', $entity['timex'])){
                        $entityTimexValue = $entity['timex']['value'];
                    } else {
                        $entityTimexValue = NULL;
                    }
                    
                    if(array_key_exists('altValue', $entity['timex'])){
                        $entityTimexAltvalue = $entity['timex']['altValue'];
                    } else {
                        $entityTimexAltvalue = NULL;
                    }
                    
                    $this->conn->insert('ner_timex', array(
                        'ner_entity_id' => $insertIds[$sentenceId][$entityId],    
                        'tid' => $entity['timex']['tid'],
                        'ner_type' => $entity['timex']['type'],
                        'value' => $entityTimexValue,
                        'altValue' => $entityTimexAltvalue,
                    ));                
                }  
            }
        }
        
        return $insertIds;
    }
    
    public function searchEntities($searchTerm = false){
        
        if(!empty($searchTerm)){
            $words = $this->searchWordEntity($searchTerm);
        } else {
            $words = $this->searchAllEntities(); 
        }
        
        return $words;
    }
    
    private function searchWordEntity($searchTerm){
        
        $words = array();
        
        $query = $this->conn->createQueryBuilder();
        $query->select('ne.sentenceId as \'Sentence #\', ne.ner_text as \'NER Text\', ne.ner_normalized_text as \'NER Normalized\', ne.ner_type as \'NER Type\','
                . ' nt.tid as \'Time ID\', nt.value as \'Time Value (1)\', nt.altValue as \'Time Value (2)\'')
        ->from('ner_entity', 'ne')
        ->leftJoin('ne', 'ner_timex', 'nt', 'nt.ner_entity_id = ne.id')
        ->where('ner_text = ?')
        ->setParameter(0, $searchTerm)           
            ;
        $result = $query->execute();
        $words = $result->fetchAll(PDO::FETCH_ASSOC);
        
        return $words;
    }
    
    private function searchAllEntities(){
        
        $words = array();
        
        $query = $this->conn->createQueryBuilder();
        $query->select('ne.sentenceId as \'Sentence #\', ne.ner_text as \'NER Text\', ne.ner_normalized_text as \'NER Normalized\', ne.ner_type as \'NER Type\','
                . ' nt.tid as \'Time ID\', nt.value as \'Time Value (1)\', nt.altValue as \'Time Value (2)\'')
        ->from('ner_entity', 'ne')
        ->leftJoin('ne', 'ner_timex', 'nt', 'nt.ner_entity_id = ne.id')  
            ;
        $result = $query->execute();
        $words = $result->fetchAll(PDO::FETCH_ASSOC);
        
        return $words;
    }
  
/**
 * NER TOKEN FUNCTIONS
 */    
    
    public function getNerTokens($coreNLP, $createdSentenceIds){
        
        $tokenResult = array();
        
        // get sentences
        $sentences = $coreNLP->serverMemory[0]['sentences'];
            
        // for every sentence get the tokens
        foreach($sentences as $sentenceId => $sentence){
    
            foreach($sentence['tokens'] as $tokenId => $token){

                if(array_key_exists('ner', $token) && $token['ner'] != 'O'){
                    
                    $tokenResult[$createdSentenceIds[$sentenceId]][$token['index']] = $this->getTokenArray($sentenceId, $token);
                }
            }
        }     
        return $tokenResult;
    }
    
    private function getTokenArray($sentenceId, $token){
        
        $nerArray = array();
        $nerArray['ner_normalized_text'] = '';

        // check for NER type
        if(array_key_exists('ner', $token)){
            $nerArray['ner_type'] = $token['ner'];
        } else {
            $nerArray['ner_type'] = '';
        }
        
        // check for normalized NER
        if(array_key_exists('normalizedNER', $token)){
            $nerArray['ner_normalized_text'] = $token['normalizedNER'];
        } else {
            $nerArray['ner_normalized_text'] = '';
        }

        // check for time/ date
        if(array_key_exists('timex', $token)){
            $nerArray['timex'] = $token['timex'];

            if(!array_key_exists('value', $token['timex'])){
                $nerArray['timex']['value'] = NULL;
            }

            if(!array_key_exists('altValue', $token['timex'])){
                $nerArray['timex']['altValue'] = NULL;
            }
        }

        // get tree index
        $nerArray['index'] = $token['index'];

        // get sentenceId
        $nerArray['sentenceId'] = $sentenceId;

        // get ner text
        $nerArray['ner_text'] = $token['originalText'];
        
        return $nerArray;
    }

    public function storeTokens($nerTokens){
    
        $insertIds = array();
        
        foreach($nerTokens as $sentenceId => $sentenceTokens){
            
            foreach($sentenceTokens as $tokenId => $token){
            
                $this->conn->insert('ner_token', array(
                    'wordIndex' => $token['index'],
                    'sentenceId' => $sentenceId,
                    'ner_text' => $token['ner_text'],
                    'ner_normalized_text' => $token['ner_normalized_text'],
                    'ner_type' => $token['ner_type'],
                ));  

                $insertIds[$sentenceId][$tokenId] =  $this->conn->lastInsertId();
                
                if(array_key_exists('timex', $token)){
                    
                    $this->conn->insert('ner_timex', array(
                        'ner_token_id' => $insertIds[$sentenceId][$tokenId],    
                        'tid' => $token['timex']['tid'],
                        'ner_type' => $token['timex']['type'],
                        'value' => $token['timex']['value'],
                        'altValue' => $token['timex']['altValue'],
                    ));                
                }  
            }
        }
        
        return $insertIds;
    }
    
   
    public function searchTokens($searchTerm = false){
        
        $tokens = array();
        
        if(!empty($searchTerm)){
            $tokens = $this->searchWordToken($searchTerm);
        } else {
            $tokens = $this->searchAllTokens(); 
        }
        
        return $tokens;
    }
    
    
    public function searchWordToken($searchTerm = false){
        
        $tokens = array();
        
        $query = $this->conn->createQueryBuilder();
        $query->select('tk.sentenceId as \'Sentence #\', tk.wordIndex as \'Word #\', tk.ner_text as \'NER Text\','
                . ' tk.ner_normalized_text as \'NER Normalized\', tk.ner_type as \'NER Type\', nt.tid as \'Time ID\', '
                . 'nt.value as \'Time Value (1)\', nt.altValue as \'Time Value (2)\'')
        ->from('ner_token', 'tk')
        ->leftJoin('tk', 'ner_timex', 'nt', 'nt.ner_token_id = tk.id')
        ->where('ner_text = ?')
        ->setParameter(0, $searchTerm)        
            ;
        $result = $query->execute();
        $tokens = $result->fetchAll(PDO::FETCH_ASSOC);

        return $tokens;
    }
      
    public function searchAllTokens(){
        
        $tokens = array(); 
        
        $query = $this->conn->createQueryBuilder();
        $query->select('tk.sentenceId as \'Sentence #\', tk.wordIndex as \'Word #\', tk.ner_text as \'NER Text\','
                . ' tk.ner_normalized_text as \'NER Normalized\', tk.ner_type as \'NER Type\', nt.tid as \'Time ID\', '
                . 'nt.value as \'Time Value (1)\', nt.altValue as \'Time Value (2)\'')
        ->from('ner_token', 'tk')
        ->leftJoin('tk', 'ner_timex', 'nt', 'nt.ner_token_id = tk.id')

            ;
        $result = $query->execute();
        $tokens = $result->fetchAll(PDO::FETCH_ASSOC);

        return $tokens;
    }
}
