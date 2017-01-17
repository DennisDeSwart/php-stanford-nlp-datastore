$(document).ready(function() {
    
        $("#helpButton").click(function(){       
            if(document.getElementById('inputText')){
                document.getElementById('inputText').value = "";
            }
            document.getElementById("inputHelpButton").value ='1';                     
            $( "#textForm" ).submit();
        });
        
        $("#enterButton").click(function(){
            if(document.getElementById('inputText')){
                document.getElementById('inputText').value = "";
            }          
            document.getElementById("inputEnterButton").value = '1';
            $( "#textForm" ).submit();
        });
        
        $("#searchButton").click(function(){
            document.getElementById("inputSearchButton").value = '1';
            document.getElementById('inputText').value = "";
            $( "#textForm" ).submit();
        });
    });
