<?php

class S3File{
    private $_Key;
    private $_LastModified;
    private $_ETag;
    private $_Size;
    private $_StorageClass;
    private $_Owner;
    
    public function __construct($argKey, $argLastModified, $argETag, $argSize,
            $argStorageClass, $argOwner){
        $this->_Key = $argKey;
        $this->_LastModified = $argLastModified;
        $this->_ETag = $argETag;
        $this->_Size = $argSize;
        $this->_StorageClass = $argStorageClass;
        $this->_Owner = $argOwner;
    }       
       
    public function getName(){return $this->_Key;}
    public function getLastModified(){return $this->_LastModified;}
    public function getETag(){return $this->_ETag;}
    public function getSize(){return $this->_Size;}
    public function getStorageClass(){return $this->_StorageClass;}
    public function getOwner(){return $this->_Owner;}
}
?>
