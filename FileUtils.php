<?php

class FileUtils
{
	public static function get_separator_from_extension($ext)
	{
		switch ($ext)
		{
			case "csv":
				return ",";
				break;
			case "tsv":
				return "\t";
				break;
		}
	}

	public static function get_separator_from_filename($fileName)
	{   
		return FileUtils::get_separator_from_extension(pathinfo($fileName, PATHINFO_EXTENSION));
	}
}

?>