<?php

/**
 * Instagram filters with PHP and ImageMagick
 *
 * @package    Instagraph
 * @author     Webarto <dejan.marjanovic@gmail.com>
 * @copyright  NetTuts+
 * @license    http://creativecommons.org/licenses/by-nc/3.0/ CC BY-NC
 */
class Instagraph
{
 
    public $_image = NULL;
    public $_output = NULL;
    public $_prefix = 'IMG';
    private $_width = NULL;
    private $_height = NULL;
    private $_tmp = NULL;
 
    public static function factory($image, $output)
    {
        return new Instagraph($image, $output);
    }
 
    public function __construct($image, $output)
    {
        if(file_exists($image))
        {
            $this->_image = $image;
            list($this->_width, $this->_height) = getimagesize($image);
            $this->_output = $output;
        }
        else
        {
            throw new Exception('File not found. Aborting.');
        }
    }
 
    public function tempfile()
    {
        # copy original file and assign temporary name
        $this->_tmp = $this->_prefix.rand();
        copy($this->_image, $this->_tmp);
    }
 
    public function output()
    {
        # rename working temporary file to output filename
        rename($this->_tmp, $this->_output);
    }
 
    public function execute($command)
    {
        # remove newlines and convert single quotes to double to prevent errors
        $command = str_replace(array("\n", "'"), array('', '"'), $command);
        $command = escapeshellcmd($command);
        # execute convert program
        exec($command);
    }
 
    /** ACTIONS */
 
    public function colortone($input, $color, $level, $type = 0)
    {
        $args[0] = $level;
        $args[1] = 100 - $level;
        $negate = $type == 0? '-negate': '';
 
        $this->execute("convert
        {$input}
        ( -clone 0 -fill '$color' -colorize 100% )
        ( -clone 0 -colorspace gray $negate )
        -compose blend -define compose:args=$args[0],$args[1] -composite
        {$input}");
    }
 
    public function border($input, $color = 'black', $width = 20)
    {
        $this->execute("convert $input -bordercolor $color -border {$width}x{$width} $input");
    }
 
    public function frame($input, $frame)
    {
        $this->execute("convert $input ( '$frame' -resize {$this->_width}x{$this->_height}! -unsharp 1.5×1.0+1.5+0.02 ) -flatten $input");
    }
 
    public function vignette($input, $color_1 = 'none', $color_2 = 'black', $crop_factor = 1.5)
    {
        $crop_x = floor($this->_width * $crop_factor);
        $crop_y = floor($this->_height * $crop_factor);
 
        $this->execute("convert
        ( {$input} )
        ( -size {$crop_x}x{$crop_y}
        radial-gradient:$color_1-$color_2
        -gravity center -crop {$this->_width}x{$this->_height}+0+0 +repage )
        -compose multiply -flatten
        {$input}");
    }

	public function kelvin() {
	    $this->tempfile();
	 
	    $this->execute("convert
	    ( $this->_tmp -auto-gamma -modulate 120,50,100 )
	    ( -size {$this->_width}x{$this->_height} -fill 'rgba(255,153,0,0.5)' -draw 'rectangle 0,0 {$this->_width},{$this->_height}' )
	    -compose multiply
	    $this->_tmp");
	    $this->frame($this->_tmp, __FUNCTION__);
	    $this->output();
	}

	public function nashville()  
	{  
	    $this->tempfile();  
	   
	    $this->colortone($this->_tmp, '#222b6d', 100, 0);  
	    $this->colortone($this->_tmp, '#f7daae', 100, 1);  
	   
	    $this->execute("convert $this->_tmp -contrast -modulate 100,150,100 -auto-gamma $this->_tmp");  
	    $this->frame($this->_tmp, __FUNCTION__);  
	   
	    $this->output();  
	}  
}

class Graphic {

	public function __construct($image_path, $to_path) {
		$this->to_path = $to_path;
		$this->img = new Imagick($image_path);
	}

	public function apply_filter() {
		$this->img->modulateImage(120, 120, 100);
		$this->img->gammaImage(1.6);
		$this->img->contrastImage(50);
		#$this->img->despeckleImage();
		$this->img->writeImage($this->to_path);
		$this->img->clear();
	}

    public function recreate_to_diamond($path, $width, $height, $digree = 0) {
        $rad = deg2rad($digree);
        // 1. Create transparant image with size ($width, $height)
        $this->img->newImage($width, $height, 'none');
        $this->img->setImageFormat("png");
        $drawer = new ImagickDraw();
        $p1 = array('x' => 0, 'y' => 0);
        $p2 = array('x' => $width - tan($rad)* $height, 'y' => 0);
        $p3 = array('x' => $width, 'y' => $height);
        $p4 = array('x' => tan($rad)* $height, 'y' => $height);
        $drawer->polygon(array($p1, $p2, $p3, $p4));
        $drawer->setStrokeColor(new ImagickPixel("none"));
        $this->img->drawImage($drawer);
        $this->img->writeImage($path);
        $this->img->clear();
    }

	public function __destruct() {
		$this->img->destroy();
	}
}