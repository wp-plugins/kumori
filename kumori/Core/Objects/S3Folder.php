<?php

class S3Folder{

    private $_Key;
    private $_LastModified;
    private $_ETag;
    private $_StorageClass;
    private $_Owner;
    
    public function __construct($argKey, $argLastModified, $argETag, 
            $argStorageClass, $argOwner){
        $this->_Key = $argKey;
        $this->_LastModified = $argLastModified;
        $this->_ETag = $argETag;
        $this->_StorageClass = $argStorageClass;
        $this->_Owner = $argOwner;
    }       
       
    public function getName(){return $this->_Key;}
    public function getLastModified(){return $this->_LastModified;}
    public function getETag(){return $this->_ETag;}
    public function getStorageClass(){return $this->_StorageClass;}
    public function getOwner(){return $this->_Owner;}
}
?>
