<?php

class PipelineNotifications{   
    private $_Progressing;
    private $_Completed;
    private $_Warning;
    private $_Error;
    
    public function __construct($argProgressing, $argCompleted, $argWarning, 
            $argError){
        $this->_Progressing = $argProgressing;
        $this->_Completed = $argCompleted;
        $this->_Warning = $argWarning;
        $this->_Error = $argError;
    }       
       
    public function getProgressing(){return $this->_Progressing;}
    public function getCompleted(){return $this->_Completed;}
    public function getWarning(){return $this->_Warning;}
    public function getError(){return $this->_Error;}
}
?>
