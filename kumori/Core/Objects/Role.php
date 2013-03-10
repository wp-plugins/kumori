<?php

class Role{
    private $_Path;
    private $_Name;
    private $_Id;
    private $_Arn;
    private $_CreateDate;
    private $_AssumeRolePolicyDocument;
    
    public function __construct($argPath, $argName, $argId, $argArn, 
            $argCreateDate, $argAssumeRolePolicyDocument){
        $this->_Path = $argPath;
        $this->_Name = $argName;
        $this->_Id = $argId;
        $this->_Arn = $argArn;
        $this->_CreateDate = $argCreateDate;
        $this->_AssumeRolePolicyDocument = $argAssumeRolePolicyDocument;
    }       
       
    public function getPath(){return $this->_Path;}
    public function getName(){return $this->_Name;}
    public function getId(){return $this->_Id;}
    public function getArn(){return $this->_Arn;}
    public function getCreateDate(){return $this->_CreateDate;}
    public function getAssumeRolePolicyDocument(){return $this->_AssumeRolePolicyDocument;}
    
}
?>
