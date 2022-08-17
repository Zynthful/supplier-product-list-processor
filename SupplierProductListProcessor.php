<?php

class Product
{
    // array of properties
    public $properties;

    function __construct($properties)
    {
        $this->properties = $properties;
    }
};

function parse($fileName)
{
    $previousMemoryLimit = ini_get("memory_limit");
    ini_set("memory_limit", "256M");    // bandaid solution to not enough memory
                                        // this will need to be changed for longer files
                                        // todo: programmatically increase memory limit based on file length?

    $file = fopen($fileName, "r") or die("Unable to open file.");

    $products = array();
    $properties = explode(",", fgets($file));

    // loop through each line of the file and assign properties to each product
    $count = 0;
    while (!feof($file))
    {
        $products[$count] = new Product($properties);
        
        $_properties = explode(",", fgets($file));

        for ($i = 0; $i < count($_properties); $i++)
        {
            $products[$count]->properties[$i] = $_properties[$i];
        }

        $count++;
    }

    // close file after reading
    fclose($file);

    ini_set("memory_limit", $previousMemoryLimit);

    // print product information
    for ($i = 0; $i < count($products); $i++)
    {
        echo "Product " . $i . PHP_EOL;
        for ($j = 0; $j < count($properties); $j++)
        {
            echo $properties[$j] . ": " . $products[$i]->properties[$j] . PHP_EOL;
        }
    }
    
    return $products;
};

// todo: allow passing in arguments
parse("D:\Projects\TBPS GitHub Test\supplier-product-list-processor\SupplierProductListProcessor.php");

?>