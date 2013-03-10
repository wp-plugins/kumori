<?php

class Preset{
    private $_Id;
    private $_Name;
    private $_Description;
    private $_Container; // This value must be mp4.
    private $_Video;
    private $_Audio; 
    private $_Thumbnails;
    
    public function __construct($argId, $argName, $argDescription, $argContainer, 
            $argVideo, $argAudio, $argThumbnails){
        $this->_Id = $argId;
        $this->_Name = $argName;
        $this->_Description = $argDescription;
        $this->_Container = 'mp4';
        $this->_Video = $argVideo;
        $this->_Audio = $argAudio;
        $this->_Thumbnails = $argThumbnails;
    }       
    
    public function getId(){return $this->_Id;}
    public function getName(){return $this->_Name;}
    public function getDescription(){return $this->_Description;}
    public function getContainer(){return $this->_Container;}
    public function getVideo(){return $this->_Video;}
    public function getAudio(){return $this->_Audio;}
    public function getThumbnails(){return $this->_Thumbnails;}   
    
}

?>
