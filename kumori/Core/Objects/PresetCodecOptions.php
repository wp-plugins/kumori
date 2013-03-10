<?php

class PresetCodecOptions{
    private $_Profile;
    private $_Level;
    private $_MaxReferenceFrames;
    
    public function __construct($argProfile, $argLevel, $argMaxReferenceFrames){
        $this->_Profile = $argProfile;
        $this->_Level = $argLevel;
        $this->_MaxReferenceFrames = $argMaxReferenceFrames;
    }       
       
    public function getProfile(){return $this->_Profile;}
    public function getLevel(){return $this->_Level;}
    public function getMaxReferenceFrames(){return $this->_MaxReferenceFrames;}
}
?>
