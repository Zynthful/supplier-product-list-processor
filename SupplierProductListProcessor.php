<?php

// assign arguments passed in to an array
// file: = the file to parse
// unique-combinations: the file to write grouped count for each unique combination i.e. make, model, etc.
$givenArgs = getopt("", array("file:", "unique-combinations:", "max-lines"));

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

// unique combination of products, grouped under the same properties
class Combination
{
    public $product;
    public $count = 0;  // number of products in this combination

    function __construct($product, $count)
    {
        $this->product = $product;
        $this->count = $count;
    }
}

function parse($fileName, $uniqueCombinationsFile, $maxLines = -1)
{
    $previousMemoryLimit = ini_get("memory_limit");
    ini_set("memory_limit", "256M");    // bandaid solution to not enough memory
                                        // this will need to be changed for longer files
                                        // todo: programmatically increase memory limit based on file length?

    $file = fopen($fileName, "r") or die("Unable to open file.");

    $products = array();
    $properties = explode(",", fgets($file));   // assign header information as our properties
                                                // todo: allow for different separators (e.g., .tsv file)

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
    
    writeCombinations($products, $uniqueCombinationsFile);

    return $products;
};

function writeCombinations($products, $fileName)
{
    //$file = fopen($fileName, "w");

    $combinations = array();
    $count = 0; // number of combinations

    for ($i = 0; $i < count($products) - 1; $i++)
    {
        echo PHP_EOL . $i;

        if (productExists($products[$i], $combinations))
        {
            echo PHP_EOL . "increment";
            // increment number of products in combination
            $combinations[$count - 1]->count++;
        }
        else
        {
            echo PHP_EOL . "new";
            // create new combination
            $combinations[$count] = new Combination($products[$i], 1);
            $count++;   // increment number of combinations
        }

        // compare this element and next element
        // if (compare($products[$i], $products[$i + 1]))
        // {
        //     echo PHP_EOL . "COMB COUNT " . count($combinations);

        //     if (count($combinations) === 0)
        //     {
        //         // create new combination
        //         $combinations[$count] = new Combination($products[$i], 1);
        //         $count++;   // increment number of combinations
        //     }
        //     else
        //     {
        //         echo PHP_EOL . "increment";
        //         // increment number of products in combination
        //         $combinations[$count]->add();
        //     }
        // }
    }

    echo PHP_EOL;
    print_r($combinations);
}

function compare($productX, $productY)
{
    // check for mismatch of properties
    for ($i = 0; $i < count($productX->properties); $i++)
    {
        // does x property NOT match y property?
        //echo PHP_EOL . "Comparing: " . $productX->properties[$i] . " against " . $productY->properties[$i] . ". Result = ". strcmp($productX->properties[$i], $productY->properties[$i]);
        if (strcmp($productX->properties[$i], $productY->properties[$i]) != 0)
        {
            //echo PHP_EOL. "no match";
            return false;
        }
    }
    //echo PHP_EOL . "match";
    // if there are no mismatch of properties, then it's an exact match, and we return true
    return true;
}

function productExists($product, $combinations)
{
    foreach ($combinations as $combination)
    {
        if (compare($product, $combination->product))
        {
            echo PHP_EOL . "match!";
            return true;
        }
    }
    echo PHP_EOL . "no match : (";
    return false;
    //return in_array($combination, $combinations, true);
}

parse("D:/Projects/TBPS GitHub Test/examples/products_comma_separated.csv", "D:\Projects\TBPS GitHub Test\examples\combination_count.csv");

// parse using given arguments
//parse($givenArgs["file"], $givenArgs["unique-combinations"], $givenArgs["max-lines"]);
?>