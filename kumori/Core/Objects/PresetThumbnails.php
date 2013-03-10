<?php

class PresetThumbnails{      
    private $_Format;
    private $_Interval;
    private $_Resolution;
    private $_AspectRatio;
    
    public function __construct($argFormat, $argInterval, $argResolution, 
            $argAspectRatio){
        $this->_Format = $argFormat;
        $this->_Interval = $argInterval;
        $this->_Resolution = $argResolution;
        $this->_AspectRatio = $argAspectRatio;
    }       
       
    public function getFormat(){return $this->_Format;}
    public function getInterval(){return $this->_Interval;}
    public function getResolution(){return $this->_Resolution;}
    public function getAspectRatio(){return $this->_AspectRatio;}
}
?>
