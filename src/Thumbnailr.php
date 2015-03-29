<?php

namespace Thumbnailr;

/**
 * Utility class for dynamically generating
 * image thumbnails.
 *
 * @author		Carlos Afonso
 * @date		March 2015
 * @version		2.0
 */
class Thumbnailr {

	const THUMBNAILR_SIZE_FIXED			= 1;
	const THUMBNAILR_SIZE_FIT_LONGEST	= 2;
	const THUMBNAILR_SIZE_FIT_SHORTEST	= 3;

	private $file;

	private $src;
	private $dst;

	public function __construct($file = NULL) {
		$this->file = $file;
	}

	/**
	 * Creates a thumbnail from the original image resource.
	 *
	 * The original image must have been provided to the
	 * constructor of this class.
	 *
	 * @param	int		width					The width of the thumbnail
	 * @param	int		height					The height of the thumbnail
	 * @param	int		sizeTransformation		A flag which specifies what kind of
	 *											transformation will be applied to the
	 *											final size of the thumbnail. Currently
	 *											3 different values are supported: THUMBNAILR_SIZE_FIXED
	 *											applies no transformation and the thumbnail will
	 *											have the exact size provided to the function;
	 *											THUMBNAILR_SIZE_FIT_LONGEST will scale the thumbnail
	 *											so that the longest side fully fits within the specified size;
	 *											THUMBNAILR_SIZE_FIT_SHORTEST will scale the thumbnail
	 *											so that the shortest side fully fits within the specified size.
	 *											Both THUMBNAILR_SIZE_FIT_LONGEST and THUMBNAILR_SIZE_FIT_SHORTEST
	 *											will keep the aspect ratio of the original image.
	 * @return	Thumbnailr	This instance of Thumbnailr to allow function chaining.
	 * @throws	Exception	If the size transformation flag contains an invalid value.
	 * @since	1.1
	 */
	public function buildThumbnail($width, $height, $sizeTransformation = self::THUMBNAILR_SIZE_FIT_LONGEST)
	{
		$size = getimagesize($this->file);

		$oWidth = $size[0];
		$oHeight = $size[1];

		switch ($sizeTransformation)
		{
			case self::THUMBNAILR_SIZE_FIXED:

				break;

			case self::THUMBNAILR_SIZE_FIT_LONGEST:
			case self::THUMBNAILR_SIZE_FIT_SHORTEST:
				$rHeight = $width * $oHeight / $oWidth;
				$rWidth = $height * $oWidth / $oHeight;

				if ($sizeTransformation == self::THUMBNAILR_SIZE_FIT_LONGEST)
				{
					if ($rHeight <= $height)
						$height = $rHeight;
					else
						$width = $rWidth;
				}
				else if ($sizeTransformation == self::THUMBNAILR_SIZE_FIT_SHORTEST)
				{
					if ($rHeight > $height)
						$height = $rHeight;
					else
						$width = $rWidth;
				}

				break;

			default:
				throw new Exception("Invalid size transformation modifier, expecting THUMBNAILR_SIZE_FIXED, THUMBNAILR_SIZE_FIT_LONGEST or THUMBNAILR_SIZE_FIT_SHORTEST");
		}
		
		if (substr_compare(strtolower($this->file), '.png', -4, 4) === 0)
			$this->src = imagecreatefrompng($this->file);
		else if (substr_compare(strtolower($this->file), '.jpg', -4, 4) === 0 || substr_compare(strtolower($this->file), '.jpeg', -5, 5) === 0)
			$this->src = imagecreatefromjpeg($this->file);
		else
			throw new Exception("Unrecognized file type");

		$this->dst = imagecreatetruecolor($width, $height);
		imagecopyresampled($this->dst, $this->src, 0, 0, 0, 0, $width, $height, $oWidth, $oHeight);
		
		// to allow method chaining
		return $this;
	}

	/**
	 * Creates a thumbnail from the original image resource.
	 *
	 * The original image must have been provided to the
	 * constructor of this class.
	 *
	 * @param	int		width					The width of the thumbnail
	 * @param	int		height					The height of the thumbnail
	 * @param	boolean	keepAspectRatio		Whether or not to keep the aspect
	 *											ratio of the original image. If set to
	 *											TRUE, either the width or height specified
	 *											as parameters will be ignored and adjusted
	 *											so that the thumbnail size keeps the original
	 *											image's aspect ratio.
	 * @return	Thumbnailr	This instance of Thumbnailr to allow function chaining.
	 * @deprecated	1.1	This function does not use the size transformation
	 *					parameter. Use buildThumbnail() instead. Calling this
	 *					function will use buildThumbnail() internally.
	 */
	public function createThumbnail($width, $height, $keepAspectRatio = TRUE)
	{
		return $this->buildThumbnail($width, $height, $keepAspectRatio ? self::THUMBNAILR_SIZE_FIT_LONGEST : self::THUMBNAILR_SIZE_FIXED);
	}

	/**
	 * Returns a base 64 representation of the thumbnail
	 * in PNG format.
	 *
	 * @param compressionLevel int the compression level
	 *			to use, 0 being no compression and 9 being
	 *			maximum compression
	 *
	 */
	public function toPngBase64($compressionLevel = 5)
	{
		ob_start();

		$this->toPngFile(NULL, $compressionLevel);
		$b64 = ob_get_contents();

		ob_end_clean();

		return base64_encode($b64);
	}

	/**
	 * Writes the thumbnail as a PNG file.
	 *
	 */
	public function toPngFile($file = NULL, $compressionLevel = 5)
	{
		return imagepng($this->dst, $file, $compressionLevel);
	}

	/**
	 * Returns a base 64 representation of the thumbnail
	 * in JPEG format.
	 *
	 * @param quality int the JPEG quality of the thumbnail,
	 *			0 being the worst quality and 100 being
	 *			the highest
	 *
	 */
	public function toJpegBase64($quality = 75)
	{
		ob_start();

		$this->toJpegFile(NULL, $quality);
		$b64 = ob_get_contents();

		ob_end_clean();

		return base64_encode($b64);			
	}

	public function toJpegFile($file = NULL, $quality = 75)
	{
		return imagejpeg($this->dst, $file, $quality);
	}

}