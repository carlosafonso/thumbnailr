thumbnailr
==========

Thumbnailr is a PHP library for dynamically generating image thumbnails in either PNG or JPEG format.

##Installation
Thumbnailr is available via Composer. Add a reference to Thumbnailr in your `composer.json` file;

```json
{
  "require": {
    "carlosafonso/thumbnailr": "2.*"
  }
}
```

Then fire up a terminal and run the following wherever your `composer.json` file is located:
```bash
$ composer install
```


##Usage example
```php
// the path to the source image
$img = 'path/to/image.png';

// the thumbnail's dimensions
$width = 200;
$height = 100;

// instantiate the library
$thumbnailr = new \Thumbnailr\Thumbnailr($img);

/*
 * Generating the thumbnail
 */
// this will not keep the aspect ratio (both calls are equivalent)
$thumbnailr->buildThumbnail($width, $height);
$thumbnailr->buildThumbnail($width, $height, self::SIZE_FIXED);

// keep the aspect ratio and fit the LONGEST side inside the thumbnail,
// (actual size will be smaller than specified)
$thumbnailr->buildThumbnail($width, $height, self::SIZE_FIT_LONGEST);

// keep the aspect ratio and fit the SHORTEST side inside the thumbnail size,
// (actual size will be larger than specified)
$thumbnailr->buildThumbnail($width, $height, self::SIZE_FIT_SHORTEST);

/*
 * Saving the thumbnail
 */
// save to a PNG file, apply standard compression
$thumbnailr->toPngFile('thumbnail.png');

// same as above specifying a compression level (higher level means smaller file size)
$thumbnailr->toPngFile('thumbnail.png', 7);

// get the thumbnail as raw binary data
$raw_png = $thumbnailr->toPngFile(NULL);

// get the PNG thumbnail as a base 64 string, apply standard compression
$png_b64 = $thumbnailr->toPngBase64();

// same as above specifying a compression level
$png_b64 = $thumbnailr->toPngBase64(4);

// save to a JPEG file, use standard quality
$thumbnailr->toJpegFile('thumbnail.jpg');

// same as above specifying a quality value (higher quality means larger file size)
$thumbnailr->toJpegFile('thumbnail.jpg', 80);

// get the JPEG thumbnail as a base 64 string, use standard quality
$jpeg_b64 = $thumbnailr->toJpegBase64();

// same as the above specifying a quality value
$jpeg_b64 = $thumbnailr->toJpegBase64(40);
```
