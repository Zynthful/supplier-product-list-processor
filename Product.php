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

// compare two products, x and y, to see if they match
// returns: true if they are an exact match, false if they are different
function compare($productX, $productY)
{
    // check for mismatch of property values
    for ($i = 0; $i < count($productX->properties); $i++)
    {
        // does x property value NOT match y property value?
        if (strcmp($productX->properties[$i]->value, $productY->properties[$i]->value) != 0)
        {
            return false;
        }
    }
    // if there are no mismatch of property values, then it's an exact match, and we return true
    return true;
}

// when given an array of products, it returns an array of unique combinations of products
// optionally pass in an array of combinations to add the products to
function find_combinations($products, &$combinations = array())
{
    $count = count($combinations); // number of combinations

    // loop through all products (slow)
    for ($i = 0; $i < count($products); $i++)
    {
        if (product_exists($products[$i], $combinations))
        {
            // increment number of products in combination
            $combinations[$count - 1]->count++;
        }
        else
        {
            // create new combination
            $combinations[$count] = new Combination($products[$i], 1);
            $count++;   // increment number of combinations
        }
    }
    return $combinations;
}
?>