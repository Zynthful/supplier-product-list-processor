<?php
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

function compare($productX, $productY)
{
    // check for mismatch of properties
    for ($i = 0; $i < count($productX->properties); $i++)
    {
        // does x property NOT match y property?
        if (strcmp($productX->properties[$i], $productY->properties[$i]) != 0)
        {
            return false;
        }
    }
    // if there are no mismatch of properties, then it's an exact match, and we return true
    return true;
}
?>