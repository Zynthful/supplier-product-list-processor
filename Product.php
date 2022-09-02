<?php
class Product
{
    // array of PropertyFields
    public $properties = array();

    // returns properties' values' as an array
    public function get_values()
    {
        $values = array();
        for ($i = 0; $i < count($this->properties); $i++)
        {
            $values[$i] = $this->properties[$i]->value;
        }
        return $values;
    }

    // compare this product to another
    // returns: true if they are an exact match, false if they are different
    public function compare($product)
    {
        // check for mismatch of properties
        for ($i = 0; $i < count($product->properties); $i++)
        {
            // if they don't match, return false
            if (!$this->properties[$i]->compare($product->properties[$i]))
            {
                return false;
            }
        }
        // if there are no mismatch of properties, then it's an exact match, and we return true
        return true;
    }
}

class PropertyField
{
    public $name;
    public $value;
    public $required = false;

    function __construct($name, $value, $required = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->required = $required;
    }

    // compares this property field's value to another
    // returns: true if both property field values match, false otherwise
    public function compare($property)
    {       
        return strcmp($this->value, $property->value) == 0;
    }
}

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

// check if a product exists in an array of combinations
// if it exists, increase the counter
// otherwise, push a new combination
// this is probably really inefficient, and should be optimised for memory usage
// todo: optimise
function product_exists($product, &$combinations)
{
    // compare this product against all combinations' products
    foreach ($combinations as &$combination)
    {
        if ($product->compare($combination->product))
        {
            $combination->count++;
            return true;
        }
    }
    // add new combination to end of array
    array_push($combinations, new Combination($product, 1));
    return null;
}

// when given an array of products, it returns an array of unique combinations of products
// optionally pass in an array of combinations to add the products to
function find_combinations($products, &$combinations = array())
{
    // loop through all products to check if it exists in any existing combinations
    for ($i = 0; $i < count($products); $i++)
    {
        product_exists($products[$i], $combinations);
    }
    return $combinations;
}
?>