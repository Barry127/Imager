<?php

/**
 * Class to open, create and edit image files using different libraries
 *
 * @author 		Barry de Kleijn
 * @copyright 	Barry de Kleijn
 * @license		MIT License
 * @license 	http://opensource.org/licenses/MIT
 * 
 * @version 	0.8.0
 *
 */

namespace Barry127\Imager;

class ImagerOpenFileException extends \Exception{}
class ImagerInvalidHexException extends \Exception{}
class ImagerInvalidArgumentException extends \Exception{}
class ImagerUnsupportedTypeException extends \Exception{}
class ImagerSaveFileException extends \Exception{}

abstract class Imager
{
	/**
	 * Imagine version number
	 *
	 * @var string
	 */
	const 	VERSION 	= '0.8.0';

	/**
	 * Class constants
	 */
	const 	WIDTH 		= 0;
	const 	HEIGHT 		= 1;
	const 	TOP 		= 0;
	const 	RIGHT 		= 1;
	const 	BOTTOM 		= 2;
	const 	LEFT 		= 3;
	const 	CENTER 		= 4;

	/**
	 * Image object
	 * 
	 * @var object
	 */
	protected $image;

	/**
	 * Source file
	 *
	 * @var string
	 */
	protected $source 	= null;

	/**
	 * File mime
	 *
	 * @var string
	 */
	protected $mime		= null;

	/**
	 * Supported file formats
	 *
	 * @var array
	 */
	protected $formats 	= array();

	/**
	 * Check if string with hex color is valid if not throw ImagerInvaludHexException
	 *
	 * @param 	string 	$color 		String with hex color
	 */
	protected function checkHex($color)
	{
		if(!preg_match('_#[0-9A-Fa-f]{3,6}_', $color)) throw new ImagerInvalidHexException('\'' . $color . '\' is an invalid hex notation');
	}

	/**
	 * Check which module to use
	 *
	 * @return 	string 				Module
	 */
	private static function checkModule()
	{
		if(extension_loaded('imagick')) return 'imagick';
		return 'gd';
	}

	/**
	 * Open image form file
	 *
	 * @param 	string 	$source 	File to open
	 *
	 * @return 	Imagine 			Imagine instance
	 */
	public static function open($source)
	{
		switch(self::checkModule())
		{
			case 'imagick':
				return new ImagerImagick($source);
				break;

			default:
				return ImagerGD::init($source);
		}
	}

	/**
	 * Create new image
	 *
	 * @param 	int 	$width 		New image width
	 * @param 	int 	$height 	New image height
	 * 
	 * @return 	Imagine 			Imagine instance
	 */
	public static function create($width, $height, $color = '#FFFFFF')
	{
		return self::open(array($width, $height, $color));
	}

	/**
	 * Create image from object
	 * 
	 * @param 	object 	$object 	Image object
	 *
	 * @return 	Imagine 			Imagine instance
	 */
	public static function createFromObject($object)
	{
		return self::open($object);
	}

	/**
	 * same as open
	 *
	 * @return  self::open
	 */
	public static function createFromFile($source) {
		return self::open($source);
	}

	/**
	 * Get Imagine version
	 *
	 * @return 	string 				Imagine version
	 */
	public function getVersion()
	{
		return self::VERSION;
	}

	/**
	 * Construct Imagine Class
	 * 
	 * @param 	string 	$source 	Source file to open
	 * @param 	array 	$source 	Array with new image dimensions to create
	 */
	abstract public function __construct($source);

	/**
	 * Add border to image
	 * 
	 * @param 	int 	$width 		Border width
	 * @param 	string 	$color 		Border color (hex value), default #000000
	 */
	abstract public function addBorder($width, $color = '#000');

	/**
	 * Add text to image
	 * 
	 * @param 	string 	$text 		Text to add
	 * @param 	string 	$font 		Source for font file
	 * @param 	int 	$size 		Fontsize, default 11
	 * @param 	string 	$color 		Font color (hex value), default #000000
	 * @param 	int 	$posX 		x pos, default self::LEFT
	 * @param 	int 	$posY 		y pos, default self::TOP
	 */
	abstract public function addText($text, $font, $size = 11, $color = '#000', $posX = self::LEFT, $posY = self::TOP);

	/**
	 * Add watermark to image
	 * 
	 * @param 	string 	$source 	Source for watermark image
	 * @param 	int 	$posX 		x pos, default self::LEFT
	 * @param 	int 	$posY 		y pos, default self::TOP
	 * @param 	int 	$opacity 	Watermark opacity
	 */
	abstract public function addWatermark($source, $posX = self::LEFT, $posY = self::TOP, $opacity = 100);

	/**
	 * Convert to type
	 * 
	 * @param 	string 	$type 		type to convert to
	 */
	abstract public function convert($type);

	/**
	 * Crop image
	 * 
	 * @param 	int 	$width 		Width for cropped image
	 * @param 	int 	$height 	Height for cropped image
	 * @param 	int 	$left 		Position from left, default 0
	 * @param 	int 	$top 		Position from top, default 0
	 */
	abstract public function crop($width, $height, $left = 0, $top = 0);

	/**
	 * Get image height
	 *
	 * @return 	int 				Image height
	 */
	abstract public function getHeight();

	/**
	 * Get meta data
	 *
	 * @return 	array 				Available meta data
	 */
	abstract public function getMetadata();

	/**
	 * Get image object
	 *
	 * @return 	object 				Image object
	 */
	abstract public function getObject();

	/**
	 * Get image type
	 *
	 * @return 	string 				Image type
	 */
	abstract public function getType();

	/**
	 * Get image width
	 *
	 * @return 	int 				Image width
	 */
	abstract public function getWidth();

	/**
	 * Resize image
	 *
	 * @param 	int 	$size 		New size to resize to
	 * @param 	int 	$side 		Side to apply new size to, default self::WIDTH
	 * @param 	bool 	$ratio 		Keep aspect ratio, default true
	 */
	abstract public function resize($size, $side = self::WIDTH, $ratio = true);

	/**
	 * Rotate image
	 *
	 * @param 	int 	$degrees 	Number of degrees to rotate (clockwise)
	 */
	abstract public function rotate($degrees);

	/**
	 * Save image to file
	 *
	 * @param 	string 	$location 	Location to save image
	 */
	abstract public function save($location);

	/**
	 * Set image object
	 *
	 * @param 	object 	$object 	Image object
	 */
	abstract public function setObject($object);

	/**
	 * Show image
	 */
	abstract public function show();
}