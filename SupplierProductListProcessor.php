<?php

// assign arguments passed in to an array
$givenArgs = getopt("", array("file:", "unique-combinations:"));

class Product
{
    // array of properties (e.g., make, model, colour, etc.)
    public $properties;

    // constructor to assign properties
    function __construct($properties)
    {
        $this->properties = $properties;
    }
};

function parse($fileName, $uniqueCombinationsFile)
{
    $previousMemoryLimit = ini_get("memory_limit");
    ini_set("memory_limit", "256M");    // bandaid solution to not enough memory
                                        // this will need to be changed for longer files
                                        // todo: programmatically increase memory limit based on file length?

    $file = fopen($fileName, "r") or die("Unable to open file.");

    $products = array();
    $properties = explode(",", fgets($file));    // assign header information as our properties

    // loop through each line of the file and assign properties to each product
    $count = 0;
    while (!feof($file))    // repeat until the end of the file
    {
        // construct product with header information
        $products[$count] = new Product($properties);
        
        // get corresponding property info from the current line
        $_properties = explode(",", fgets($file));

        for ($i = 0; $i < count($_properties); $i++)
        {
            // assign corresponding property info to our product
            $products[$count]->properties[$i] = $_properties[$i];
        }

        $count++;
    }

    // close file after reading
    fclose($file);

    // reset memory limit
    ini_set("memory_limit", $previousMemoryLimit);

    // print product information
    for ($i = 0; $i < count($products); $i++)
    {
        echo "Product " . $i . PHP_EOL; // print product index
        for ($j = 0; $j < count($properties); $j++)
        {
            echo $properties[$j] . ": " . $products[$i]->properties[$j] . PHP_EOL;  // print product properties
        }
    }
    
    return $products;
};

//parse("D:/Projects/TBPS GitHub Test/examples/products_comma_separated.csv");

// parse using given arguments
parse($givenArgs["file"], $givenArgs["unique-combinations"]);
?>