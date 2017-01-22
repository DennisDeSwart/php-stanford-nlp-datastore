
# PHP Stanford NLP Datastore

Stores NLP data from Stanford CoreNLP server.



## What does it do?
It analyses a text using Stanford CoreNLP server, then stores the result.



## Which data gets stored?
- OpenIE: these are "Subject-Relation-Object" triples. The concept is similar to "Subject-Verb-Object" triples.
```
http://stanfordnlp.github.io/CoreNLP/openie.html
```
- Named-Entities: if a word is a "Named Entity", like a Location, Name or Time, it will store this data
```
http://stanfordnlp.github.io/CoreNLP/ner.html
```
- Coreference: if there is a reference to a word in another sentence.
```
http://stanfordnlp.github.io/CoreNLP/coref.html
```



## How does it work?

- You submit a text.
- The text is analyzed by the Stanford CoreNLP server
- Results are stored in a SQLite file based database. The database file is called "datastore.db"
```
https://sqlite.org/
https://github.com/sqlitebrowser
```
- The results are displayed on screen
- There is also a search form to find data



## This package depends on Stanford CoreNLP Server

```
http://stanfordnlp.github.io/CoreNLP/index.html#download
```


## This package also depends on PHP-Stanford-CoreNLP-Adapter

```
https://github.com/DennisDeSwart/php-stanford-corenlp-adapter
```

Note: since this package contains a full version of the CoreNLP Adapter, you can use all of it's features with this package.



## Installation

This package depends on these packages:

```
http://stanfordnlp.github.io/CoreNLP/index.html#download
https://github.com/DennisDeSwart/php-stanford-corenlp-adapter
https://github.com/doctrine/dbal
https://github.com/guzzle/guzzle
```



## Install procedure using the ZIP files

- Install Stanford CoreNLP Server. Check the "php-stanford-corenlp-adapter" package for an installation walkthrough
- Download and unpack the files from this package.
- Copy the files to your to your webserver directory. Usually "htdocs" or "var/www".
- Run a Composer update to install the dependencies



## Install as part of another project

- Install Stanford CoreNLP Server. Check the "php-stanford-corenlp-adapter" package for an installation walkthrough
- Add the following lines to your main project's "composer.json" require section:

```
    {
        "require": {
            "dennis-de-swart/php-stanford-nlp-datastore": "*"
        }
    }
```

- Run a Composer update to install the dependencies
``` 
Copy these files from "/vendor/dennis-de-swart/php-stanford-nlp-datastore" to your webserver directory. Usually "htdocs" or "var/www".
- datastore.db
- bootstrap.php
```

- Example code for your main project:
```
    // instantiate constants and the database
    require_once __DIR__.'/bootstrap.php';

    // startup Corenlp Adapter
    $coreNLP = new CorenlpAdapter();
    $coreNLP->getOutput($yourText);
    print_r($coreNLP->serverMemory); // result from CoreNLP Adapter

    // Save result to database
    $datastore = new Datastore($db->conn);
    $datastore->storeNLP($coreNLP);
```



## Requirements
- PHP 5.3 or higher: it also works on PHP 7
- Java SE Runtime Enviroment, version 1.8
- Stanford CoreNLP Server 3.7.0
- Windows or Linux/Unix 64-bit OS, 8Gb or more memory recommended.
- Composer for PHP
```
    https://getcomposer.org/
```


## SQLite Browser

If you need a SQLite browser check here:
```
http://sqlitebrowser.org/
```



## Important notes

- Starting the CoreNLP server for the first time, takes some time because it will load a large amount of data.
- After the first startup, the server will be much faster.
- In my experience the Stanford CoreNLP server runs best with 8Gb of memory or more. Start the server with "-mx8g" instead of "-mx4g". 
- Also use version 3.7.0 of the server, this gives you the best and quickest results.



## Example output

See 
- "datastore_result_a.PNG"
- "datastore_result_b.PNG"
- "datastore_result_search.PNG"

and "example.db", this is how a filled database looks like



## Any questions?

Let me know. You can create an issue on GitHub. Any bugs will be fixed ASAP.


