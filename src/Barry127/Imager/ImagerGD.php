<?php

namespace Barry127\Imager;

abstract class ImagerGD extends Imager
{
	public static function init($source)
	{
		if(gettype($source) === 'resource' && get_resource_type($source) === 'gd') {
			return new ImagerGDJpeg($source);
		} elseif(is_array($source)) {
			return new ImagerGDJpeg($source);
		} else {
			$extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
			switch ($extension) {
				case 'gif':
					return new ImagerGDGif($source);	
					break;

				case 'jpe':
				case 'jpg':
				case 'jpeg':
					$r = new ImagerGDJpeg($source);
					return $r;

				case 'png':
				case 'png24':
				case 'png32':
				case 'png8':
					return new ImagerGDPng($source);
					break;
				
				default:
					return new ImagerGDJpeg($source);
					break;
			}
		}
	}

	public function addBorder($width, $color = '#000')
	{
		$this->checkHex($color);
		$tmpColor = imagecolorallocate($this->image, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));
		$x1 = 0;
		$y1 = 0;
		$x2 = $this->getWidth() - 1;
		$y2 = $this->getHeight() - 1;

		for($i = 0; $i < $width; $i++) {
			imagerectangle($this->image, $x1++, $y1++, $x2--, $y2--, $tmpColor);
		}
		return $this;
	}

	public function addText($text, $font, $size = 11, $color = '#000', $posX = self::LEFT, $posY = self::TOP)
	{
		if(!file_exists($font)) throw new ImagerOpenFileException('Cannot open \'' . $font . '\' File doesn\'t exist');
		if(!is_int($size)) throw new ImagerInvalidArgumentException('Size needs to be an integer');
		$this->checkHex($color);

		$textBox = imagettfbbox($size, 0, realpath($font), $text);

		switch ($posX) {
			case self::RIGHT:
				$x = ($this->getWidth() - $textBox[4]) - 1;
				break;

			case self::CENTER:
				$x = ($this->getWidth() / 2) - ($textBox[4] / 2);
				break;
			
			default:
				$x = 1;
				break;
		}

		switch ($posY) {
			case self::BOTTOM:
				$y = ($this->getHeight() - $textBox[3]) - 1;
				break;

			case self::CENTER:
				$y = ($this->getHeight() / 2) + (abs($textBox[5] + $textBox[3]) / 2);
			
			default:
				$y = 1 + abs($textBox[5]);
				break;
		}

		imagettftext($this->image, $size, 0, $x, $y, imagecolorallocate($this->image, hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2))), realpath($font), $text);
		unset($textBox);
		return $this;
	}

	public function addWatermark($source, $posX = self::LEFT, $posY = self::TOP, $opacity = 100)
	{
		if(!is_int($opacity)) throw new ImagerInvalidArgumentException('Opacity needs to be an integer');
		$watermark = ImagerGD::init($source);

		switch ($posX) {
			case self::RIGHT:
				$x = ($this->getWidth() - $watermark->getWidth()) - 1;
				break;

			case self::CENTER:
				$x = ($this->getWidth() / 2) - ($watermark->getWidth() / 2);
				break;
			
			default:
				$x = 1;
				break;
		}

		switch ($posY) {
			case self::BOTTOM:
				$y = ($this->getHeight() - $watermark->getHeight()) - 1;
				break;

			case self::CENTER:
				$y = ($this->getHeight() / 2) - ($watermark->getHeight() / 2);
				break;
			
			default:
				$y = 1;
				break;
		}

		if($opacity < 0 || $opacity > 100) {
			$opacity = 100;
		}

		imagecopymerge($this->image, $watermark->getObject(), $x, $y, 0, 0, $watermark->getWidth(), $watermark->getHeight(), $opacity);
		return $this;
	}

	public function convert($type)
	{
		switch (strtolower($type)) {
			case 'image/gif':
				$image = new ImagerGDGif(array(1, 1, '#000'));
				break;

			case 'image/jpeg':
				$image = new ImagerGDJpeg(array(1, 1, '#000'));

			case 'image/png':
				$image = new ImagerGDPng(array(1, 1, '#000'));
			
			default:
				$image = 'test';
				throw new ImagerUnsupportedTypeException('Mime \'' . $type . '\' is not supported');
				break;
		}
		$image->setObject($this->image);
		return $image;
	}

	public function crop($width, $height, $left = 0, $top = 0)
	{
		if(!is_int($width)) throw new ImagerInvalidArgumentException('Width needs to be an integer');
		if(!is_int($height)) throw new ImagerInvalidArgumentException('Height needs to be an integer');
		if(!is_int($left)) throw new ImagerInvalidArgumentException('Left needs to be an integer');
		if(!is_int($top)) throw new ImagerInvalidArgumentException('Top needs to be an integer');

		$crop = imagecreatetruecolor($width, $height);
		imagecopy($crop, $this->image, 0, 0, $left, $top, $width, $height);
		imagedestroy($this->image);
		$this->image = $crop;

		return $this;
	}

	public function getHeight()
	{
		return imagesy($this->image);
	}

	public function getMetadata()
	{
		return array(
			'width'		=> $this->getWidth(),
			'height'	=> $this->getHeight()
		);
	}

	public function getObject()
	{
		return $this->image;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getWidth()
	{
		return imagesx($this->image);
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

		imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $image;
		return $this;
	}

	public function rotate($degrees)
	{
		if(!is_int($degrees)) throw new ImagerInvalidArgumentException('Degrees needs to be an integer');
		$this->image = imagerotate($this->image, -($degrees), imagecolorallocate($this->image, 0, 0, 0));
		return $this;
	}

	public function save($location)
	{
		if(file_exists($location)) throw new ImagerSaveFileException('Cannot save image to \'' . $location . '\' file already exists');
		if(!is_writable(realpath(pathinfo($location, PATHINFO_DIRNAME)))) throw new ImagerSaveFileException('Cannot save image to \'' . $location . '\' directory is not writable');
		// sub class implemts save action
	}

	public function setObject($object)
	{
		if(gettype($object) === 'resource' && get_resource_type($object) === 'gd') {
			$this->image = $object;
		} else {
			throw new ImagerInvalidArgumentException('Object needs to be a resource of gd');
		}
		return $this;
	}
}