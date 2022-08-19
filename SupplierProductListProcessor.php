<?php

require 'Exceptions.php';
require 'ArrayUtils.php';
require 'FileUtils.php';
require 'Product.php';

// assign arguments passed in to an array
// file: the file to parse
// unique-combinations: the file to write grouped count for each unique combination i.e. make, model, etc.
//                      will be created if not found.
$givenArgs = getopt("", array("file:", "unique-combinations:", "max-lines"));

class Headers
{
    public static $headers;
}

function parse($fileName, $maxLines = -1)
{
    $previousMemoryLimit = ini_get("memory_limit");
    ini_set("memory_limit", "256M");    // bandaid solution to not enough memory
                                        // this will need to be changed for longer files
                                        // todo: programmatically increase memory limit based on file length?

    // open file for reading and throw exception if we can't open it
    if (!$file = fopen($fileName, "r")){
        throw new FileOpenException("Failed to open file. Make sure that you typed the file name correctly, and that this has write access to it (is the file already open?).");
        return;
    }

    // get separator (for parsing) from reading file extension
    // todo: allow support for different formats (json, xml, etc.)
    $separator = FileUtils::get_separator_from_filename($fileName);

    // assign header information as our properties
    // todo: allow for different separators (e.g., .tsv file)
    Headers::$headers = explode($separator, fgets($file));

    ArrayUtils::unset_empty_lines(Headers::$headers);

    // remove last line in the array since it is a newline
    unset(Headers::$headers[count(Headers::$headers) - 1]);

    $products = array();

    // loop through each line of the file
    // read property values and assign these to the property fields for each product
    $count = 0;
    while (!feof($file))
    {
        // if we've provided a limit to how many lines to iterate through, check if we've met/exceeded this limit
        if ($maxLines >= 0 && $count >= $maxLines){
            break;
        }

        // construct product
        $products[$count] = new Product();
        
        // get property values from the current line as an array
        $values = explode($separator, fgets($file));

        // remove last line in the array since it is a newline
        unset($values[count($values) - 1]);

        // remove any empty lines
        ArrayUtils::unset_empty_lines($values);

        // loop through property values and assign them as properties to each product
        for ($i = 0; $i < count($values); $i++)
        {
            // construct property field for this product
            $products[$count]->properties[$i] = new PropertyField(Headers::$headers[$i], $values[$i]);

            // check if field is required by looking for '*' character in property name
            if (str_contains($products[$count]->properties[$i]->name, "*"))
            {
                // if the required field value is empty, throw an exception
                if (empty($products[$count]->properties[$i]->value))
                {
                    throw new MissingFieldException("Required field {$products[$count]->properties[$i]->name} on file {$fileName} line index {$count} is empty. Make sure all required fields on the file are filled in.");
                }

                // set this property as required then remove * from the property name
                $products[$count]->properties[$i]->required = true;
                $products[$count]->properties[$i]->name = str_replace("*", "", $products[$count]->properties[$i]->name);
            }
        }

        $count++;
    }

    fclose($file);

    // reset memory limit
    ini_set("memory_limit", $previousMemoryLimit);

    // print product information
    // for ($i = 0; $i < count($products); $i++)
    // {
    //     // product index
    //     echo PHP_EOL . "Product {$i}" . PHP_EOL;
    //     for ($j = 0; $j < count($products[$i]->properties); $j++)
    //     {
    //         // PropertyName: PropertyValue
    //         echo "{$products[$i]->properties[$j]->name}: {$products[$i]->properties[$j]->value}" . PHP_EOL;  // print product properties
    //     }
    // }

    return $products;
};

// writes the given combinations to the given file
function write_combinations($fileName, $combinations)
{
    if (!$file = fopen($fileName, "w"))
    {
        throw new FileCreateException("Failed to create file. Make sure you wrote a valid name, and that you have write permissions to this directory.");
        return;
    }

    $separator = FileUtils::get_separator_from_filename($fileName);

    // write headers
    fwrite($file, ArrayUtils::wrap_implode(Headers::$headers, '', "{$separator}count", $separator));
    fwrite($file, PHP_EOL);

    // write data
    for ($i = 0; $i < count($combinations); $i++)
    {
        fwrite($file, ArrayUtils::wrap_implode($combinations[$i]->product->get_values(), '', "{$separator}{$combinations[$i]->count}", $separator));
        fwrite($file, PHP_EOL);
    }

    fclose($file);
}

// for ($i = 0; $i < 10; $i++)
// {
//     $lines = ($i + 1) * 1000;
//     $products = parse("D:/Projects/TBPS GitHub Test/examples/products_tab_separated.tsv", $lines);

//     //$products = parse($givenArgs["file"], $givenArgs["max-lines"]);
//     $combinations = find_combinations($products);
//     //writeCombinations($givenArgs["unique-combinations"], $combinations);
    
//     write_combinations("D:/Projects/TBPS GitHub Test/examples/combination_count.tsv", $combinations);

//     echo PHP_EOL . "{$lines}: Memory Peak (bytes): " . memory_get_peak_usage();
// }

$products = parse("D:/Projects/TBPS GitHub Test/examples/products_tab_separated.tsv", 40000);

//$products = parse($givenArgs["file"], $givenArgs["max-lines"]);
$combinations = find_combinations($products);
//writeCombinations($givenArgs["unique-combinations"], $combinations);

write_combinations("D:/Projects/TBPS GitHub Test/examples/combination_count.tsv", $combinations);

echo PHP_EOL . "Memory Peak (MB): " . memory_get_peak_usage() * 0.000001;


?>