<?php


class Template {
    
    function __construct() {
        
       $this->getHeader();
       
    }
    
    public function getHeader(){
        echo '
        <!-- HEADER -->
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <title>PHP Stanford NLP Datastore</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="index.js"></script>
        </head>
        <body>
        <div class="container">

        <!-- END HEADER -->';
    }
    
    public function getForm($text = '', $searchButton = '', $search = ''){
        
        echo '<!-- START FORM -->        
            <h2>PHP Stanford NLP Datastore</h2>
            <br><form id="textForm" class="form-horizontal" method="post" action=\'\'>  ';
        
        if(empty($searchButton) && empty($search)){ 
            echo '
            
                <div class="form-group">
                  <label class="col-sm-2 control-label">Enter a text:</label>
                  <div class="col-sm-6">
                      <textarea class="form-control" id="inputText" name="text">'.$text.'</textarea>
                  </div>
                </div>
                <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                    <div class="checkbox col-sm-6">
                        <label><input type="checkbox" name="emptyDB" id="emptyDB"><b>Empty database before submit?</b></label>
                    </div>
                </div>
            ';
        } else {
            
            echo '<div class="form-group">
                <label class="col-sm-2 control-label">Search term:</label>
                <div class="col-sm-6">
                    <input class="form-control" id="search" name="search" type="text" value="">
                </div>
                </div>
            ';
        }
        
        echo '
            <br />
            <button id="helpButton" type="button" class="btn btn-info btn-lg">Help</button>
            <button style="margin-left: 20px" id="enterButton" type="button" class="btn btn-info btn-lg">Results</button>
            <button style="margin-left: 20px" id="searchButton" type="button" class="btn btn-info btn-lg">Search</button>
            <input style="margin-left: 20px" type="submit" value="Submit" class="btn btn-primary btn-lg">

            <!-- HIDDEN INPUTS --> 
            <input type="hidden" id="inputEnterButton" name="enterButton" value="">
            <input type="hidden" id="inputHelpButton" name="helpButton" value="">
            <input type="hidden" id="inputSearchButton" name="searchButton" value="">
            <br /><br />
            </form> 
            <!-- END FORM -->
        ';        
    }
    
    public function getHelp(){
        echo '
        <!-- START HELP -->
    <h3>What do the buttons do?</h3>
     <ul >
        <li>Press <b>Help</b> for this help screen</li>
        <li>Press <b>Results</b> for the CoreNLP results</li>
        <li>Press <b>Search</b> to search the results for a word</li>
        <li>Press <b>Submit</b> to submit a text</li>
        <li>The <b>Empty database checkbox:</b> The results are stored in a SQLite file database<br />
        This file is called "datastore.db". Each time you submit a text, it adds the results to the database file.<br /> 
        The results can be become very long. If you check the box, the database is emptied.</li>
    </ul>
    <h3>How to use the form</h3>
    <div> 
        <ul>
            <li>Try typing this text in the textbox: "John meets Mary at the restaurant at 7pm. He orders a drink for her."</li>   
            <li>Now press "Submit"</li>
            <li>See the results on the screen</li>
            <li>Now click the Search button. Try typing "Mary" in the search box</li>
            <li>You now see the results for the word "Mary"</li>
        </ul
    </div>
    <h3>"Datastore" looks for three sets of data:</h3>
    <div> 
        <ul>
            <li>OpenIE: it finds Subject-Relation-Object triplets. This is similar to Subject-Verb-Object triplets. <br />
            <a href="http://nlp.stanford.edu/software/openie.html">For information about OpenIE, click here.</a></li>
            <li>Coreference: reference to a word in the same or another sentence.</li>
            <li>Named-Entity-Recognition (NER): words that have a specific purpose, like Locations or Dates</li>
        </ul
    </div>
    
    <h3>Please be patient when submitting</h3>
    <div> 
        <ul>
            <li>The first run can take 1 or 2 minutes because CoreNLP server needs to start up</li>
            <li>Normal speed for this program is about 5 to 30 sentences a second. It is recommended to do 1 paragraph or 1 page per submit.</li>
        </ul
    </div>
    <h3>You can view the data with a SQLite browser</h3>
    <div> 
        <ul>
            <li>All results are stored in the "datastore.db" file</li>
            <li>You can use a SQLite browser like <a href="https://github.com/sqlitebrowser/sqlitebrowser">this SQLite browser</a> to view the data</li>
        </ul
    </div>

        <!-- END HELP -->
        ';
    }
          
    public function getTable(array $data, $title, $searchText = ''){
        
        echo '<div class="container">';
        echo '<h3>'.$title.' ';
        if($searchText){
            echo $searchText.' ';
        }
        echo '</h3>';
        
        if (count($data) > 0){

            echo '<table class="table table-bordered">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>';
            echo implode('</th><th>', array_keys(current($data)));
            echo '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($data as $row){
                echo'<tr>';
                echo'<td>';
                echo implode('</td><td>', $row);
                echo'</td>';
                echo'</tr>';
            }

            echo '</tbody>';
            echo '</table>';   

        } else {
            echo 'No results';
        }
        echo '</div>';
    }
    
    // credits: http://stackoverflow.com/questions/4746079/how-to-create-a-html-table-from-a-php-array
}
