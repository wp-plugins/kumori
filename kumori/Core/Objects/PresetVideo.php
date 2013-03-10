<?php

class PresetVideo{        
    private $_Codec; // must be H.264.
    private $_CodecOptions; // - (hash[string => string]) - Profile
    private $_KeyframesMaxDist; // 1 and 100000
    private $_FixedGOP; //Valid values are true and false:
    private $_BitRate; // Valid values depend on the values of Level and Profile. 
    private $_FrameRate; //
    private $_Resolution; // Valid values are auto and width x height:
    private $_AspectRatio;
        
    public function __construct($argCodec, $argCodecOptions, $argKeyframesMaxDist, 
            $argFixedGOP, $argBitRate, $argFrameRate, $argResolution, 
            $argAspectRatio){
        $this->_Codec = 'H.264';
        $this->_CodecOptions = $argCodecOptions;
        $this->_KeyframesMaxDist = $argKeyframesMaxDist;
        $this->_FixedGOP = $argFixedGOP;
        $this->_BitRate = $argBitRate;
        $this->_FrameRate = $argFrameRate;
        $this->_Resolution = $argResolution;
        $this->_AspectRatio = $argAspectRatio;
    }       
       
    public function getCodec(){return $this->_Codec;}
    public function getCodecOptions(){return $this->_CodecOptions;}
    public function getKeyframesMaxDist(){return $this->_KeyframesMaxDist;}
    public function getFixedGOP(){return $this->_FixedGOP;}        
    public function getBitRate(){return $this->_BitRate;}  
    public function getFrameRate(){return $this->_FrameRate;}  
    public function getResolution(){return $this->_Resolution;}  
    public function getAspectRatio(){return $this->_AspectRatio;}  
}
?>
