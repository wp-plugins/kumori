<?php

class PresetAudio{        
    private $_Codec;
    private $_SampleRate;
    private $_BitRate; // between 64 and 320
    private $_Channels;

    public function __construct($argCodec, $argSampleRate, $argBitRate, $argChannels){
        $this->_Codec = 'AAC';
        $this->_SampleRate = $argSampleRate;
        $this->_BitRate = $argBitRate;
        $this->_Channels = $argChannels;
    }       
       
    public function getCodec(){return $this->_Codec;}
    public function getSampleRate(){return $this->_SampleRate;}
    public function getBitRate(){return $this->_BitRate;}
    public function getChannels(){return $this->_Channels;}            
}
?>
