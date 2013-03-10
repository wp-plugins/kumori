<?php

class Owner{
    
    private $_Id;
    private $_DisplayName;
    
    public function __construct($argId, $argDisplayName){
        $this->_Id = $argId;
        $this->_DisplayName = $argDisplayName;
    } 
    
    public function getId(){return $this->_Id;}
    public function getDisplayName(){return $this->_DisplayName;}    

}
?>
