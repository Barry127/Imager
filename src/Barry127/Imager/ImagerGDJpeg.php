<?php

namespace Barry127\Imager;

class ImagerGDJpeg extends ImagerGD
{
	public function __construct($source)
	{
		if(gettype($source) === 'resource' && get_resource_type($source) === 'gd') {
			$this->image = $source;
		} elseif(is_array($source)) {
			$this->checkHex($source[2]);
			$this->image = imagecreatetruecolor($source[0], $source[1]);
			imagefill($this->image, 0, 0, imagecolorallocate($this->image, hexdec(substr($source[2], 1, 2)), hexdec(substr($source[2], 3, 2)), hexdec(substr($source[2], 5, 2))));
		} else {
			if(!file_exists($source)) throw new ImagerOpenFileException('Cannot open \'' . $source . '\' File doesn\'t exist');
			if(!is_readable($source)) throw new ImagerOpenFileException('Cannot read \'' . $source . '\'');

			$this->image = imagecreatefromjpeg($source);
		}
		return $this;
		$this->mime = 'image/jpeg';
	}

	public function save($location)
	{
		parent::save($location);
		imagejpeg($this->image, $location);
		return $this;
	}

	public function show()
	{
		header('Content-type: image/jpeg');
		imagejpeg($this->image);
		die();
	}
}