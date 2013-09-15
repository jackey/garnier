<?php

class Graphic {

	public function __construct() {
        $this->background = dirname(__FILE__)."/background.png";
	}

	public function apply_filter($image_path, $to_path) {
        $info = pathinfo($image_path);
        $to_path = $info['dirname']."/".time()."_". $info["filename"].".png";
        $img = new Imagick($image_path);
		$img->modulateImage(120, 120, 100);
		$img->gammaImage(1.6);
		$img->contrastImage(50);
		#$this->img->despeckleImage();
		$img->writeImage($to_path);
		$img->clear();
        return $to_path;
	}

    public function mergeImages($images = array()) {
        $to_path = dirname(__FILE__)."/merged_images/merge_".time().".png";
        $background_img = new Imagick($this->background);
        $v1 = 10;
        $v2 = 20;
        $h1 = 10;
        $resize_width = ($background_img->getImageWidth() - 2*$h1);
        $resize_height = ($background_img->getImageHeight()- 2*$v1 - $v2) /2;
        $tmp_images = array();

        $pos_x = $h1;
        $pos_y = $v1;

        foreach ($images as $index => $image) {
            if ($index % 2 == 0) {
                list($tmp_image, $width, $height) = $this->recreate_to_diamond($image, $resize_width, $resize_height, 5);
            }
            else {
                list($tmp_image, $width, $height) = $this->recreate_to_diamond($image, $resize_width, $resize_height, -5);
            }
            $background_img->compositeImage(new Imagick($tmp_image), imagick::COMPOSITE_DEFAULT, $pos_x, $pos_y);
            $pos_y += $height + $v2;
        }
        $background_img->flattenImages();
        $background_img->setImageFormat("png");
        $background_img->writeImage($to_path);
        $background_img->clear();
        $background_img->destroy();

        return $to_path;
    }

    public function recreate_to_diamond($path, $width, $height, $digree = 0) {
        $left = FALSE;
        if ($digree < 0) {
            $digree = -$digree;
            $left = TRUE;
        }
        // Step 1, Resize it
        $info = pathinfo($path);
        $resize_image_path = $info["dirname"]."/img_temp_".$info["filename"].".png";
        $img = new Imagick($path);
        list($to_x, $to_y) = $this->resizeSize($img->getImageWidth(), 
                    $img->getImageHeight(),
                    $width,
                    $height);
        $img->scaleImage($to_x, $to_y);
        $img->setImageFormat("png");
        $img->writeImage($resize_image_path);
        $img->clear();
        $img->destroy();

        // Step 2, make it to diamond
        $diamond_image_path = $info["dirname"]. "/img_temp_diamon_".$info["filename"].".png";
        $img = new Imagick();
        $diamond_szie_width = $to_x;
        $diamond_szie_height = $to_y ;
        $img->newImage($diamond_szie_width, $diamond_szie_height, 'none');

        // Drawer .
        $rad = deg2rad($digree);
        $drawer = new ImagickDraw();
        $drawer->setFillColor("#ffffff");
        if ($left) {
            $p1 = array('x' => tan($rad) * $diamond_szie_height, 'y' => 0);
            $p2 = array('x' => $diamond_szie_width, 'y' => 0);
            $p3 = array('x' => $diamond_szie_width - $diamond_szie_width * tan($rad), 'y' => $diamond_szie_height);
            $p4 = array('x' => 0, 'y' => $diamond_szie_height);
        }
        else {
            $p1 = array('x' => 0, 'y' => 0);
            $p2 = array('x' => $diamond_szie_width - tan($rad)* $diamond_szie_height, 'y' => 0);
            $p3 = array('x' => $diamond_szie_width, 'y' => $diamond_szie_height);
            $p4 = array('x' => tan($rad)* $diamond_szie_height, 'y' => $diamond_szie_height);
        }
        $drawer->polygon(array($p1, $p2, $p3, $p4));
        $img->drawImage($drawer);

        $img->compositeImage(new Imagick($resize_image_path), imagick::COMPOSITE_ATOP, 0, 0);
        $img->flattenImages();

        $img->setImageFormat("png");
        $img->writeImage($diamond_image_path);
        $img->clear();
        $img->destroy();

        return array($diamond_image_path, $to_x, $to_y);
    }

    public function resizeSize($x,$y,$cx,$cy) {
        //Set the default NEW values to be the old, in case it doesn't even need scaling
        list($nx,$ny)=array($x,$y);
        
        //If image is generally smaller, don't even bother
        if ($x>=$cx || $y>=$cx) {
                
            //Work out ratios
            if ($x>0) $rx=$cx/$x;
            if ($y>0) $ry=$cy/$y;
            
            //Use the lowest ratio, to ensure we don't go over the wanted image size
            if ($rx>$ry) {
                $r=$ry;
            } else {
                $r=$rx;
            }
            
            //Calculate the new size based on the chosen ratio
            $nx=intval($x*$r);
            $ny=intval($y*$r);
        }
        
        //Return the results
        return array($nx,$ny);
    }

	public function __destruct() {
		$this->img->destroy();
	}
}