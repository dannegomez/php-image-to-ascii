<?php

/**
 * Class to make a ascii image of an image
 * 
 * Created by
 * Daniel Gomez-Ortega
 */
class Ascii
{
    private $image_data = [];
    private $image_filename = "";
    private $image_file = null;
    private $image_sampled_pixels = [[]];
    public $sample_size = 10;
    public $ascii_chars = [];
    private $ascii_image = "";

    /**
     * Class constructor
     * 
     * @param string $filename Path to image file
     */
    public function __construct($filename)
    {
        $this->ascii_chars = str_split(".~:;!jX0@#");
        $this->ascii_chars = array_reverse($this->ascii_chars);
        $this->sample_size = count($this->ascii_chars);

        $this->image_filename = $filename;

        //get image file
        $this->image_file = $this->get_image($this->image_filename);

        //get image size: width, height
        $this->get_image_size();
    }

    /**
     * Method to read image file and create an image object
     */
    private function get_image()
    {
        if (!file_exists($this->image_filename)) {
            throw new Exception('Image file not found.');
        }

        //return image objekt
        return imagecreatefromstring(file_get_contents($this->image_filename));
    }

    /**
     * Method to get image width and height
     * Rescale image to max 1024px in width if large
     */
    private function get_image_size()
    {
        //get image width and height
        $this->image_data['width'] = imagesx($this->image_file);
        $this->image_data['height'] = imagesy($this->image_file);

        //check image size - Avoid possible "Out of memory" situations
        if ($this->image_data['width'] > 1024) {
            $this->image_file = imagescale($this->image_file, 1024);
            //re get size
            $this->image_data['width'] = imagesx($this->image_file);
            $this->image_data['height'] = imagesy($this->image_file);
        }

        if (!array_key_exists('width', $this->image_data)) {
            throw new Exception('Could not get width of image.');
        }
        if (!array_key_exists('height', $this->image_data)) {
            throw new Exception('Could not get height of image.');
        }
        if ($this->image_data['width'] == 0 || $this->image_data['height'] == 0) {
            throw new Exception('Could not get height of image.');
        }
    }

    /**
     * Create ascii of the image
     */
    public function create_ascii_to_file()
    {
        //destory old image if called multiple times
        if (!empty($this->ascii_image)) {
            $this->ascii_image = "";
        }

        //read colors in sampled pixels
        $this->sample_pixel_colors($this->sample_size);

        //create mosaic
        $this->create_ascii_image("file");

        //save file
        $this->save_file("");
    }

    /**
     * Create ascii to html
     */
    public function create_ascii_to_html()
    {
        //destory old image if called multiple times
        if (!empty($this->ascii_image)) {
            $this->ascii_image = "";
        }

        //read colors in sampled pixels
        $this->sample_pixel_colors($this->sample_size);

        //create mosaic
        $this->create_ascii_image("html");

        //echo image
        echo $this->ascii_image;
    }

    /**
     * Save ascii to file
     * 
     * @return bool Status of save
     */
    public function save_file($save_filename = "")
    {
        if (!empty($this->ascii_image)) {
            $save_as = "ascii_" . date("ymdHis") . ".txt";
            if (!empty($save_filename)) {
                if (substr($save_filename, -4) !== ".txt") {
                    $save_filename .= ".txt";
                }
                $save_as = $save_filename;
            }
            return file_put_contents(__DIR__ . "/" . $save_as, $this->ascii_image);
        }
    }

    /**
     * Loop all pixels in image and sample color of pixel and save it
     * 
     * @param int $sample_size Sample intervall
     */
    private function sample_pixel_colors($sample_size)
    {
        $y_pixel = 0;
        for ($y = 0; $y < $this->image_data['height']; $y += $sample_size) {
            $x_pixel = 0;
            for ($x = 0; $x < $this->image_data['width']; $x += $sample_size) {
                //get color at pixel x,y
                $rgb = imagecolorat($this->image_file, $x, $y);
                $r = (($rgb >> 16) & 0xFF);
                $g = (($rgb >> 8) & 0xFF);
                $b = ($rgb & 0xFF);

                //save pixel colors
                $this->image_sampled_pixels[$y_pixel][$x_pixel] = [$r, $g, $b];

                $x_pixel++;
            }
            $y_pixel++;
        }
    }

    /**
     * Create the ascii image
     * 
     * @param string $type Output type: file, html
     */
    private function create_ascii_image($type)
    {
        //loop all sampled pixels
        $height_sampled_pixels = count($this->image_sampled_pixels);
        $width_sampled_pixels = count($this->image_sampled_pixels[0]);
        for ($y = 0; $y < $height_sampled_pixels; $y++) {
            for ($x = 0; $x < $width_sampled_pixels; $x++) {

                //calc grayscale value
                $gray = $this->grayscale_color(
                    $this->image_sampled_pixels[$y][$x][0],
                    $this->image_sampled_pixels[$y][$x][1],
                    $this->image_sampled_pixels[$y][$x][2]
                );

                if ($type == "file") {
                    //add char to file
                    $this->ascii_image .= $this->grayscale_to_char($gray) . " ";
                } else {
                    //add char to html
                    $this->ascii_image .= "<div style='width:15px; display:inline-block; text-align:center;'>" . $this->grayscale_to_char($gray) . "</div>";
                }
            }

            if ($type == "file") {
                //add char to file
                $this->ascii_image .= PHP_EOL;
            } else {
                //add char to html
                $this->ascii_image .= "<br>";
            }
        }
    }

    /**
     * Convert rgb color to grayscale
     * 
     * @return int Weighted grayscaled value
     */
    private function grayscale_color($r, $g, $b)
    {
        return (int) ((0.3 * $r) + (0.59 * $g) + (0.11 * $b));
    }

    /**
     * Convert grayscale to char
     * 
     * @return string character to use on pixel
     */
    private function grayscale_to_char($gray)
    {
        $char = (int) (($gray / 255) * (count($this->ascii_chars) - 1));
        return $this->ascii_chars[$char];
    }
}
