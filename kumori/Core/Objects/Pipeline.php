<?php

class Pipeline{
    private $_Id;
    private $_Name;
    private $_Status;
    private $_InputBucket;
    private $_OutputBucket;
    private $_RoleArn;
    private $_Notifications;
           
    public function __construct($argId, $argName, $argStatus, $argInputBucket, 
            $argOutputBucket, $argRoleArn, $argNotifications){
        $this->_Id = $argId;
        $this->_Name = $argName;
        $this->_Status = $argStatus;
        $this->_InputBucket = $argInputBucket;
        $this->_OutputBucket = $argOutputBucket;
        $this->_RoleArn = $argRoleArn;
        $this->_Notifications = $argNotifications;
    }   
    
    public function getId(){return $this->_Id;}
    public function getName(){return $this->_Name;}
    public function getStatus(){return $this->_Status;}
    public function getInputBucket(){return $this->_InputBucket;}
    public function getOutputBucket(){return $this->_OutputBucket;}
    public function getRoleArn(){return $this->_RoleArn;}
    public function getNotifications(){return $this->_Notifications;}
}
?>
