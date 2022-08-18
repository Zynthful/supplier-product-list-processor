<?php

// assign arguments passed in to an array
// file: = the file to parse
// unique-combinations: the file to write grouped count for each unique combination i.e. make, model, etc.
//                      will be created if not found.
$givenArgs = getopt("", array("file:", "unique-combinations:", "max-lines"));

class Headers
{
    public static $headers;
}

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

function parse($fileName, $maxLines = -1)
{
    $previousMemoryLimit = ini_get("memory_limit");
    ini_set("memory_limit", "256M");    // bandaid solution to not enough memory
                                        // this will need to be changed for longer files
                                        // todo: programmatically increase memory limit based on file length?

    $file = fopen($fileName, "r") or die("Failed to open file.");

    $products = array();
    Headers::$headers = explode(",", fgets($file));     // assign header information as our properties
                                                        // todo: allow for different separators (e.g., .tsv file)

    // for some reason, the above explode returns a new line for the last element
    // so we remove this last element here
    unset(Headers::$headers[count(Headers::$headers) - 1]);

    // loop through each line of the file and assign properties to each product
    $count = 0;
    while (!feof($file) && $count <= 100)    // repeat until the end of the file
    {
        // construct product with header information
        $products[$count] = new Product(Headers::$headers);
        
        // get corresponding property info from the current line
        $_properties = explode(",", fgets($file));

        // remove last element since it's a new line for some reason?
        unset($_properties[count($_properties) - 1]);

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
        echo PHP_EOL . "Product " . $i . PHP_EOL; // print product index
        for ($j = 0; $j < count(Headers::$headers); $j++)
        {
            echo Headers::$headers[$j] . ": " . $products[$i]->properties[$j] . PHP_EOL;  // print product properties
        }
    }

    return $products;
};

function findCombinations($products)
{
    $combinations = array();
    $count = 0; // number of combinations

    for ($i = 0; $i < count($products) - 1; $i++)
    {
        echo PHP_EOL . $i;

        if (productExists($products[$i], $combinations))
        {
            //echo PHP_EOL . "increment";
            // increment number of products in combination
            $combinations[$count - 1]->count++;
        }
        else
        {
            //echo PHP_EOL . "new";
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

    return $combinations;
}

function writeCombinations($fileName, $combinations)
{
    $file = fopen($fileName, "w") or die("Failed to open file.");

    // write headers, plus count
    // todo: get properties as a global variable
    //$currentLine = __LINE__;

    fwrite($file, ArrayUtils::wrapImplode(Headers::$headers, '', ',count', ','));
    fwrite($file, PHP_EOL);
    //FileUtils::appendToLineByName($fileName, $currentLine, "count", ",");

    // write data
    for ($i = 0; $i < count($combinations); $i++)
    {
        fwrite($file, ArrayUtils::wrapImplode($combinations[$i]->product->properties, '', ",{$combinations[$i]->count}", ','));
        fwrite($file, PHP_EOL);
    }
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
            //echo PHP_EOL . "match!";
            return true;
        }
    }
    //echo PHP_EOL . "no match : (";
    return false;
    //return in_array($combination, $combinations, true);
}

class FileUtils
{
    public static function appendToLineByName($fileName, $lineIndex, $text, $separator = "")
    {
        FileUtils::appendToLineByFile(file($fileName), $lineIndex, $text, $separator);
    }
    
    public static function appendToLineByFile($file, $lineIndex, $text, $separator = "")
    {
        $file[$lineIndex] = $file[$lineIndex] + $separator + $text;
    }
}

class ArrayUtils
{
    public static function wrapImplode( $array, $before = '', $after = '', $separator = '' )
    {
        if(!$array)
            return '';
        
        return $before . implode($separator, $array ) . $after;
    }

    public static function fullyWrapImplode( $array, $before = '', $after = '', $separator = '' )
    {
        if(!$array)
            return '';
        
        return $before . implode("{$before}{$separator}{$after}", $array ) . $after;
    } 
}

$products = parse("D:/Projects/TBPS GitHub Test/examples/products_comma_separated.csv");

//$products = parse($givenArgs["file"], $givenArgs["max-lines"]);
$combinations = findCombinations($products);
//writeCombinations($givenArgs["unique-combinations"], $combinations);

writeCombinations("D:/Projects/TBPS GitHub Test/examples/combination_count.csv", $combinations);
?>