<?php

namespace Barry127\Imager;

class ImagerGDPng extends ImagerGD
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

			$this->image = imagecreatefrompng($source);
		}
		imagealphablending($this->image, false);
		imagefill($this->image,0,0,imagecolorallocatealpha($this->image, 0, 0, 0, 127));
		imagesavealpha($this->image,true);
		$this->mime = 'image/png';
		return $this;
	}

	public function resize($size, $side = SELF::WIDTH, $ratio = true)
	{
		if(!is_int($size)) throw new ImagerInvalidArgumentException('Degrees needs to be an integer');

		if(!$ratio) {
			if($side === self::WIDTH) {
				$width = $size;
				$height = $this->getHeight();
			} else {
				$width = $this->getWidth();
				$height = $size;
			}
		} else {
			if($side === self::WIDTH) {
				$width = $size;
				$height = ceil($this->getHeight() / ($this->getWidth() / $size));
			} else {
				$width = ceil($this->getWidth() / ($this->getHeight() / $size));
				$height = $size;
			}
		}
		$image = imagecreatetruecolor($width, $height);
		imagealphablending($image, false);
		imagefill($image,0,0,imagecolorallocatealpha($image, 0, 0, 0, 127));
		imagesavealpha($image,true);
		imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $image;
		return $this;
	}

	public function save($location)
	{
		parent::save($location);
		imagepng($this->image, $location);
		return $this;
	}

	public function show()
	{
		header('Content-type: image/png');
		imagepng($this->image);
		die();
	}
}