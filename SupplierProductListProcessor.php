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

    if (!$file = fopen($fileName, "r")){
        throw new FileOpenException("Failed to open file. Make sure that you typed the file name correctly, and that this has write access to it (is the file already open?).");
        return;
    }

    $separator = FileUtils::get_separator_from_filename($fileName);

    $products = array();
    Headers::$headers = explode($separator, fgets($file));      // assign header information as our properties
                                                                // todo: allow for different separators (e.g., .tsv file)

    ArrayUtils::unset_empty_lines(Headers::$headers);

    // remove last line in the array since it is a newline
    unset(Headers::$headers[count(Headers::$headers) - 1]);

    // loop through each line of the file and assign properties to each product
    $count = 0;
    while (!feof($file))    // repeat until the end of the file
    {
        if ($maxLines >= 0 && $count >= $maxLines)
        {
            break;
        }

        // construct product
        $products[$count] = new Product();
        
        // get corresponding property info from the current line
        $values = explode($separator, fgets($file));

        // remove last line in the array since it is a newline
        unset($values[count($values) - 1]);

        ArrayUtils::unset_empty_lines($values);

        for ($i = 0; $i < count($values); $i++)
        {
            // construct property field for this product
            $products[$count]->properties[$i] = new PropertyField(Headers::$headers[$i], $values[$i]);

            // check if field is required by looking for '*' character in property name
            if (str_contains($products[$count]->properties[$i]->name, "*"))
            {
                if (empty($products[$count]->properties[$i]->value))
                {
                    throw new MissingFieldException("Required field {$products[$count]->properties[$i]->name} on file {$fileName} line index {$count} is empty. Make sure all required fields on the file are filled in.");
                }

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
    for ($i = 0; $i < count($products); $i++)
    {
        echo PHP_EOL . "Product {$i}" . PHP_EOL; // print product index
        for ($j = 0; $j < count($products[$i]->properties); $j++)
        {
            echo "{$products[$i]->properties[$j]->name}: {$products[$i]->properties[$j]->value}" . PHP_EOL;  // print product properties
        }
    }

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

// check if a product exists in a combination
// returns: true if a product exists, otherwise false
function product_exists($product, $combinations)
{
    // compare this product against all combinations' products
    foreach ($combinations as $combination)
    {
        if (compare($product, $combination->product))
        {
            return true;
        }
    }
    return false;
}

$products = parse("D:/Projects/TBPS GitHub Test/examples/products_comma_separated.csv", 1000);

//$products = parse($givenArgs["file"], $givenArgs["max-lines"]);
$combinations = find_combinations($products);
//writeCombinations($givenArgs["unique-combinations"], $combinations);

write_combinations("D:/Projects/TBPS GitHub Test/examples/combination_count.csv", $combinations);
?>