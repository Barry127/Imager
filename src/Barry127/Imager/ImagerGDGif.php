<?php

namespace Barry127\Imager;

class ImagerGDGif extends ImagerGD
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

			$this->image = imagecreatefromgif($source);
		}
		$this->mime = 'image/gif';
		return $this;
	}

	public function save($location)
	{
		parent::save($location);
		imagegif($this->image, $location);
		return $this;
	}

	public function show()
	{
		header('Content-type: image/gif');
		imagegif($this->image);
		die();
	}
}