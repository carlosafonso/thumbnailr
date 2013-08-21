<?php

	/**
	 * thumbnailr.php
	 *
	 * Utility class for dynamically generating
	 * image thumbnails.
	 *
	 * @author		Carlos Afonso
	 * @date		August 2013
	 * @version		1.1
	 *
	 */
	define('THUMBNAILR_SIZE_FIXED', 1);
	define('THUMBNAILR_SIZE_FIT_LONGEST', 2);
	define('THUMBNAILR_SIZE_FIT_SHORTEST', 3);

	class Thumbnailr {

		private $file;

		private $_src;
		private $_dst;

		public function __construct($file = NULL)
		{
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
		 * @param	int		size_transformation		A flag which specifies what kind of
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
		public function build_thumbnail($width, $height, $size_transformation = THUMBNAILR_SIZE_FIT_LONGEST)
		{
			$size = getimagesize($this->file);

			$o_width = $size[0];
			$o_height = $size[1];

			switch ($size_transformation)
			{
				case THUMBNAILR_SIZE_FIXED:

					break;

				case THUMBNAILR_SIZE_FIT_LONGEST:
				case THUMBNAILR_SIZE_FIT_SHORTEST:
					$r_height = $width * $o_height / $o_width;
					$r_width = $height * $o_width / $o_height;

					if ($size_transformation == THUMBNAILR_SIZE_FIT_LONGEST)
					{
						if ($r_height <= $height)
							$height = $r_height;
						else
							$width = $r_width;
					}
					else if ($size_transformation == THUMBNAILR_SIZE_FIT_SHORTEST)
					{
						if ($r_height > $height)
							$height = $r_height;
						else
							$width = $r_width;
					}

					break;

				default:
					throw new Exception("Invalid size transformation modifier, expecting THUMBNAILR_SIZE_FIXED, THUMBNAILR_SIZE_FIT_LONGEST or THUMBNAILR_SIZE_FIT_SHORTEST");
			}
			
			if (substr_compare(strtolower($this->file), '.png', -4, 4) === 0)
				$this->_src = imagecreatefrompng($this->file);
			else if (substr_compare(strtolower($this->file), '.jpg', -4, 4) === 0 || substr_compare(strtolower($this->file), '.jpeg', -5, 5) === 0)
				$this->_src = imagecreatefromjpeg($this->file);
			else
				throw new Exception("Unrecognized file type");

			$this->_dst = imagecreatetruecolor($width, $height);
			imagecopyresampled($this->_dst, $this->_src, 0, 0, 0, 0, $width, $height, $o_width, $o_height);
			
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
		 * @param	boolean	keep_aspect_ratio		Whether or not to keep the aspect
		 *											ratio of the original image. If set to
		 *											TRUE, either the width or height specified
		 *											as parameters will be ignored and adjusted
		 *											so that the thumbnail size keeps the original
		 *											image's aspect ratio.
		 * @return	Thumbnailr	This instance of Thumbnailr to allow function chaining.
		 * @deprecated	1.1	This function does not use the size transformation
		 *					parameter. Use build_thumbnail() instead. Calling this
		 *					function will use build_thumbnail() internally.
		 */
		public function create_thumbnail($width, $height, $keep_aspect_ratio = TRUE)
		{
			return $this->build_thumbnail($width, $height, $keep_aspect_ratio ? THUMBNAILR_SIZE_FIT_LONGEST : THUMBNAILR_SIZE_FIXED);
		}

		/**
		 * Returns a base 64 representation of the thumbnail
		 * in PNG format.
		 *
		 * @param compression_level int the compression level
		 *			to use, 0 being no compression and 9 being
		 *			maximum compression
		 *
		 */
		public function to_png_base_64($compression_level = 5)
		{
			ob_start();

			$this->to_png_file(NULL, $compression_level);
			$b64 = ob_get_contents();

			ob_end_clean();

			return base64_encode($b64);
		}

		/**
		 * Writes the thumbnail as a PNG file.
		 *
		 */
		public function to_png_file($file = NULL, $compression_level = 5)
		{
			return imagepng($this->_dst, $file, $compression_level);
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
		public function to_jpeg_base_64($quality = 75)
		{
			ob_start();

			$this->to_jpeg_file(NULL, $quality);
			$b64 = ob_get_contents();

			ob_end_clean();

			return base64_encode($b64);			
		}

		public function to_jpeg_file($file = NULL, $quality = 75)
		{
			return imagejpeg($this->_dst, $file, $quality);
		}

	}