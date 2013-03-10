<?php

class S3Bucket{
    private $_Name;
    private $_CreateDate;
    
    public function __construct($argName, $argCreateDate){
        $this->_Name = $argName;
        $this->_CreateDate = $argCreateDate;
    } 
    
    public function getName(){return $this->_Name;}
    public function getCreateDate(){return $this->_CreateDate;}
}
?>
