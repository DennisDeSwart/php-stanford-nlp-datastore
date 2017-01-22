<?php

    /**
     * Instantiate
     */
    require_once __DIR__.'/bootstrap.php';
    
    /**
     * Init template
     * Init CoreNLP Adapter
     */
    $template   = new Template();
    $coreNLP    = new CorenlpAdapter();
    $datastore  = new Datastore($db->conn);
    
    /**
     * Init variables
     */
    $text = '';
    $search = '';
    $enterButton = '';
    $searchButton = '';
    $helpButton = '';

    /**
     * POST procedure
     */
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // clean up the post array
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // check if "clear database" checkbox is set
        if(array_key_exists("emptyDB", $_POST)){
            $db->clearAllTables();
        }
        
        // Analyze the text
        if(!empty($_POST['text'])){
            $text = $_POST['text'];
            
            // runs the CoreNLP Adapter and saves result in "$coreNLP->serverMemory"
            $coreNLP->getOutput($text);
            
            // Save result to database
            $datastore->storeNLP($coreNLP);
            
        } elseif(!empty($_POST['helpButton'])){
            $helpButton = $_POST['helpButton'];
        
        } elseif(!empty($_POST['enterButton'])){
            $enterButton = $_POST['enterButton'];
            
        } elseif(!empty($_POST['searchButton'])){
            $searchButton = $_POST['searchButton'];
        } elseif(!empty($_POST['search'])){
            $search = $_POST['search'];
        }    
    }

    // display the form
    $template->getForm($text, $searchButton, $search);

    if($helpButton){
        $template->getHelp();
        die;        
    }
    
    if(!empty($text) || !empty($search) ||$searchButton == '1' || $enterButton == '1'){
      
        
?>
    <!-- RESULTS -->
    <table>
    <th>   
        <tr>
            <td>
                <?php   
                    $oie = new OpenIE($db->conn);
                    
                    if($search){
                        $ieSearch = $oie->openieSearch($search);
                        $searchWord = 'for text containing the word "'.$search.'"';
                    } else {
                        $ieSearch = $oie->openieSearch();
                        $searchWord = 'for all words';
                    }
                    
                    $template->getTable($ieSearch, 'OpenIE', $searchWord);
                ?>
            </td>       
        </tr>
        <tr>
            <td>
                <?php
                
                    $ner = new NER($db->conn);
                
                    if($search){
                        $nerWords = $ner->searchEntities($search);
                        $searchWord = 'for text containing the word "'.$search.'"';
                    } else {
                        $nerWords = $ner->searchEntities();
                        $searchWord = 'for all words';
                    }
                
                    $template->getTable($nerWords, 'NER Entities', $searchWord);

                    /**
                     *  for the seperate tokens uncomment the two lines below:
                     */
                    if($search){
                        $nerTokens = $ner->searchTokens($search);
                        $searchWord = 'for text containing the word "'.$search.'"';
                    } else {
                        $nerTokens = $ner->searchTokens();
                        $searchWord = 'for all words';
                    }
                
                    $template->getTable($nerTokens, 'NER Tokens', $searchWord);
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php          
                    $coref = new Coreference($db->conn);
                    
                     if($search){
                        $corefs = $coref->corefSearch($search);
                        $searchWord = 'that refer to the word "'.$search.'"';
                    } else {
                        $corefs = $coref->corefSearch();
                        $searchWord = 'for all words';
                    }
                    
                    $template->getTable($corefs, 'Corefs', $searchWord);
                ?>
            </td>
        </tr>
    </table>
    <!-- END RESULTS -->
<?php
    }
    echo '</div></body></html>';
