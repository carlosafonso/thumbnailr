<?php

	/**
	 * Utility class for dynamically generating
	 * image thumbnails.
	 *
	 * @author Carlos Afonso
	 *
	 */
	class Thumbnailr {

		private $file;

		private $_src;
		private $_dst;

		public function __construct($file = NULL)
		{
			$this->file = $file;
		}

		public function create_thumbnail($width, $height, $keep_aspect_ratio = TRUE)
		{
			$size = getimagesize($this->file);

			if ($keep_aspect_ratio)
			{
				$r_height = $width * $size[1] / $size[0];
				$r_width = $height * $size[0] / $size[1];

				if ($r_height <= $height)
					$height = $r_height;
				else
					$width = $r_width;
			}

			if (substr_compare($this->file, '.png', -4, 4) === 0)
				$this->_src = imagecreatefrompng($this->file);
			else if (substr_compare($this->file, '.jpg', -4, 4) === 0 || substr_compare($this->file, '.jpeg', -5, 5) === 0)
				$this->_src = imagecreatefromjpeg($this->file);
			else
				die ('Unrecognized file type');

			$this->_dst = imagecreatetruecolor($width, $height);
			imagecopyresampled($this->_dst, $this->_src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
			
			// to allow method chaining
			return $this;
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