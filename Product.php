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

function compare($productX, $productY)
{
    // check for mismatch of properties
    for ($i = 0; $i < count($productX->properties); $i++)
    {
        // does x property NOT match y property?
        if (strcmp($productX->properties[$i]->value, $productY->properties[$i]->value) != 0)
        {
            return false;
        }
    }
    // if there are no mismatch of properties, then it's an exact match, and we return true
    return true;
}
?>