<?php

namespace Barry127\Imager;

class ImagerImagick extends Imager
{
	private $acceptedMimes = array(
		'image/bmp'		=> 'bmp',
		'image/gif'		=> 'gif',
		'image/jpeg'	=> 'jpg',
		'image/png'		=> 'png',
		'image/tiff'	=> 'tiff'
	);

	public function __construct($source)
	{
		if(gettype($source) === 'object' && get_class($source) === 'Imagick') {
			$this->image = $source;
			$this->image->setMime('jpg');
			$this->formats = array_map('strtolower', $this->image->queryFormats());
		} elseif (is_array($source)) {
			$this->checkHex($source[2]);
			$this->image = new \Imagick();
			$this->image->newImage($source[0], $source[1], $source[2]);
			$this->image->setImageFormat('jpg');
		} else {
			if(!file_exists($source)) throw new ImagerOpenFileException('Cannot open \'' . $source . '\' File doesn\'t exist');
			if(!is_readable($source)) throw new ImagerOpenFileException('Cannot read \'' . $source . '\'');

			$this->setMime(strtolower(pathinfo($source, PATHINFO_EXTENSION)));
			$this->source = $source;
			$this->image = new \Imagick();
			$this->formats = array_map('strtolower', $this->image->queryFormats());
			$this->image->readImage($this->source);

		}
		return $this;
	}

	public function __destruct()
	{
		$this->image->destroy();
	}

	public function addBorder($width, $color = '#000')
	{
		$this->checkHex($color);
		$this->image->borderImage($color, $width, $width);
		return $this;
	}

	public function addText($text, $font, $size = 11, $color = '#000', $posX = self::LEFT, $posY = self::TOP)
	{
		if(!file_exists($font)) throw new ImagerOpenFileException('Cannot open \'' . $font . '\' File doesn\'t exist');
		if(!is_int($size)) throw new ImagerInvalidArgumentException('Size needs to be an integer');
		$this->checkHex($color);

		$textBox = new \ImagickDraw();
		$textBox->setFont($font);
		$textBox->setFontSize($size);
		$textBox->setFillColor($color);

		if($posX == self::CENTER && $posY == self::TOP) {
			$textBox->setGravity(\Imagick::GRAVITY_NORTH);
		} elseif($posX == self::RIGHT && $posY == self::TOP) {
			$textBox->setGravity(\Imagick::GRAVITY_NORTHEAST);
		} elseif($posX == self::LEFT && $posY == self::CENTER) {
			$textBox->setGravity(\Imagick::GRAVITY_WEST);
		} elseif($posX == SELF::CENTER && $posY == self::CENTER) {
			$textBox->setGravity(\Imagick::GRAVITY_CENTER);
		} elseif($posX == self::RIGHT && $posY == self::CENTER) {
			$textBox->setGravity(\Imagick::GRAVITY_EAST);
		} elseif($posX == self::LEFT && $posY == self::BOTTOM) {
			$textBox->setGravity(\Imagick::GRAVITY_SOUTHWEST);
		} elseif($posX == self::CENTER && $posY == self::BOTTOM) {
			$textBox->setGravity(\Imagick::GRAVITY_SOUTH);
		} elseif($posX == self::RIGHT && $posY == self::BOTTOM) {
			$textBox->setGravity(\Imagick::GRAVITY_SOUTHEAST);
		} else {
			$textBox->setGravity(\Imagick::GRAVITY_NORTHWEST);
		}

		$this->image->annotateImage($textBox, 0, 0, 0, $text);

		return $this;
	}

	public function addWatermark($source, $posX = self::LEFT, $posY = self::TOP, $opacity = 100)
	{
		if(!is_int($opacity)) throw new ImagerInvalidArgumentException('Opacity needs to be an integer');
		$watermark = new ImagerImagick($source);

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

		$this->image->compositeImage($watermark->getObject(), \Imagick::COMPOSITE_OVER, $x, $y, $opacity);
		return $this;
	}

	public function convert($type)
	{
		if(!isset($this->acceptedMimes[strtolower($type)])) throw new ImagerUnsupportedTypeException('Mime \'' . $type . '\' is not supported');
		$this->image->setImageFormat(strtoupper($this->acceptedMimes[strtolower($type)]));
		return $this;
	}

	public function crop($width, $height, $left = 0, $top = 0)
	{
		if(!is_int($width)) throw new ImagerInvalidArgumentException('Width needs to be an integer');
		if(!is_int($height)) throw new ImagerInvalidArgumentException('Height needs to be an integer');
		if(!is_int($left)) throw new ImagerInvalidArgumentException('Left needs to be an integer');
		if(!is_int($top)) throw new ImagerInvalidArgumentException('Top needs to be an integer');

		$this->image->cropImage($width, $height, $left, $top);
		return $this;
	}

	public function getHeight()
	{
		return $this->image->getImageHeight();
	}

	public function getMetadata()
	{
		return $this->image->getImageProperties('*');
	}

	public function getObject()
	{
		return $this->image;
	}

	public function getType()
	{
		return $this->mime;
	}

	public function getWidth()
	{
		return $this->image->getImageWidth();
	}

	public function resize($size, $side = self::WIDTH, $ratio = true)
	{
		if(!is_int($size)) throw new ImagerInvalidArgumentException('Degrees needs to be an integer');

		if(!$ratio) {
			if($side === self::WIDTH) {
				$this->image->scaleImage($size, $this->getHeight());
			} else {
				$this->image->scaleImage($this->getWidth(), $size);
			}
		} else {
			if($side === self::WIDTH) {
				$this->image->scaleImage($size, 0);
			} else {
				$this->image->scaleImage(0, $size);
			}
		}
		return $this;
	}

	public function rotate($degrees)
	{
		if(!is_int($degrees)) throw new ImagerInvalidArgumentException('Degrees needs to be an integer');
		$this->image->rotateImage(new \ImagickPixel(), $degrees);
		return $this;
	}

	public function save($location)
	{
		if(file_exists($location)) throw new ImagerSaveFileException('Cannot save image to \'' . $location . '\' file already exists');
		if(!is_writable(realpath(pathinfo($location, PATHINFO_DIRNAME)))) throw new ImagerSaveFileException('Cannot save image to \'' . $location . '\' directory is not writable');
		$this->image->writeImage($location);
		return $this;
	}

	/**
	 * Set mime type from extension
	 * 
	 * @param 	string 	$extension 	Extension to set mime for
	 */
	private function setMime($extension)
	{
		switch ($extension) {
			case 'bmp':
			case 'bmp2':
			case 'bmp3':
				$this->mime = 'image/bmp';
				break;

			case 'gif':
			case 'gif87':
				$this->mime = 'image/gif';
				break;

			case 'jpe':
			case 'jpg':
			case 'jpeg':
				$this->mime = 'image/jpeg';
				break;

			case 'png':
			case 'png24':
			case 'png32':
			case 'png8':
				$this->mime = 'image/png';
				break;

			case 'tiff':
			case 'tiff64':
				$this->mime = 'image/tiff';
				break;
			
			default:
				$this->mime = 'image/jpeg';
				break;
		}
	}

	public function setObject($object)
	{
		if((gettype($object) !== 'object' && get_class($object) !== 'Imagick')) {
			$this->image = $object;
			$this->image->setMime('jpg');
		} else {
			throw new ImagerInvalidArgumentException('Object needs to be an instance of Imagick');
		}
		return $this;
	}

	public function show()
	{
		header('Content-type: ' . $this->getType());
		echo $this->image->getImageBlob();
		die();
	}
}