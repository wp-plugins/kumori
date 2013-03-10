<?php

require_once '/../Objects/S3Objects.php';
require_once '/../Objects/CommonObjects.php';

/**
 * 
 */
class SimpleStorageServiceClientWrapper{   
    private $s3client = null;
    
    public function __construct($s3){
        if($s3 === null){
            throw new Exception('The s3 client specified was null!');
        }
        $this->s3client = $s3;
    }
    
    private function isFolderName($folderName){
        return (substr($folderName, -1) == '/');
    }
    
    private function isFilename($filename){
        return (substr($filename, -1) != '/');
    }
    
    private function ensureFolderName($folderName){
        if(substr($folderName, -1) != '/') {
            $folderName = $folderName . '/';           
        }        
        return $folderName;
    }

    private function ensureFilename($filename){
        if(substr($filename, -1) == '/') {
            $filename = substr($filename, 0, -1);
        }        
        return $filename;
    }    
    
    public function listBuckets (){
        $bucketList = $this->s3client->listBuckets(array())->get('Buckets');
        $bucketObjectList = array();
        foreach ($bucketList as $bucket) {
            $bucketObjectList[] = new S3Bucket(
                    $bucket['Name'], 
                    $bucket['CreationDate']
            );
        }
        return $bucketObjectList;
    }
    
    public function listFolders ($bucketName, $folder){        
        $objectList = $this->s3client->listObjects(array('Bucket' => $bucketName, 'Prefix' => $folder));
        $folderList = array();        
        foreach($objectList->get('Contents') as $object)
        {
            if($this->isFolderName($object['Key'])){                
                $folderList[] = new S3Folder(
                        $object['Key'], 
                        $object['LastModified'], 
                        $object['ETag'], 
                        $object['StorageClass'], 
                        new Owner(
                                $object['Owner']['ID'], 
                                $object['Owner']['DisplayName']
                        )                        
                 );
            }
        }
        return $folderList;
    }
    
    public function listFiles ($bucketName, $folder){
        $objectList = $this->s3client->listObjects(array('Bucket' => $bucketName, 'Prefix' => $folder));
        $fileList = array();        
        foreach($objectList->get('Contents') as $object)
        {
            if($this->isFilename($object['Key'])){
                $fileList[] = new S3File(
                        $object['Key'], 
                        $object['LastModified'], 
                        $object['ETag'], 
                        $object['Size'], 
                        $object['StorageClass'], 
                        new Owner(
                                $object['Owner']['ID'], 
                                $object['Owner']['DisplayName']
                        )                        
                 );                 
            }
        }
        return $fileList;        
    }

    public function createBucket ($newBucketName, $wait){
        // check if the new bucket name is valid 
        if(!$this->s3client->isValidBucketName($newBucketName)){
            throw new Exception('The bucket name '.$newBucketName.' is not valid!');
        }
        // if the name is valid, create the bucket!
        $this->s3client->createBucket(array('Bucket' => $newBucketName, 
            'LocationConstraint' => AWSClientFactoryWrapper::Instance()->getRegion()));
        // check if user wants to wait or not
        if(isset($wait)){
            if($wait == true){
                // now wait for it...
                $this->s3client-> waitUntilBucketExists(array('Bucket' => $newBucketName, 'waiter.max_attempts' => 3));
            }
        }
    }
    
    public function createFolder ($bucketName, $newFolder, $wait){
        // check if bucket exists
        if(!$this->s3client->doesBucketExist($bucketName, false, array())) {
            throw new Exception('The bucket '.$bucketName.' does not exist!');
        }
        // folder name MUST ends with '/' character
        $newFolder = $this->ensureFolderName($newFolder);
        // check if folder exists
        if($this->s3client->doesObjectExist($bucketName, $newFolder, array())){
            throw new Exception('The folder '.$newFolder.' already exists!');
        }        
        // if bucket exists, create the new folder!
        $this->s3client->putObject(array('Bucket' => $bucketName, 'Key' => $newFolder));
        // check if user wants to wait or not
        if(isset($wait)){
            if($wait == true){
                // now wait for it...
                $this->s3client->waitUntilObjectExists(array('Bucket' => $bucketName, 'Key' => $newFolder, 'waiter.max_attempts' => 3));
            }
        }
    }

    public function deleteBucket ($bucketName, $wait){
        // check if bucket exists
        if(!$this->s3client->doesBucketExist($bucketName, false, array())) {
            throw new Exception('The bucket '.$bucketName.' does not exist!');
        }
        // if bucket exists, delete it!
        $this->s3client->deleteBucket(array('Bucket' => $bucketName));
        // check if user wants to wait or not
        if(isset($wait)){
            if($wait == true){
                // now wait for it to be deleted...
                $this->s3client->waitUntilBucketNotExists(array('Bucket' => $bucketName, 'waiter.max_attempts' => 3));
            }
        }
    }    
    
    public function deleteFolder ($bucketName, $folderName){
        // check if bucket exists
        if(!$this->s3client->doesBucketExist($bucketName, false, array())) {
            throw new Exception('The bucket '.$bucketName.' does not exist!');
        }
        // folder name MUST ends with '/' character
        $folderName = $this->ensureFolderName($folderName);    
        // check if folder exists
        if(!$this->s3client->doesObjectExist($bucketName, $folderName, array())){
            throw new Exception('The folder '.$folderName.' does not exist!');
        }
        // if the folder exists, delete it!
        $this->s3client->deleteObject(array('Bucket' => $bucketName, 'Key' => $folderName));
    }
    
    public function deleteFile ($bucketName, $filename){
        // check if bucket exists
        if(!$this->s3client->doesBucketExist($bucketName, false, array())) {
            throw new Exception('The bucket ' . $bucketName . ' does not exist!');
        }
        // filename MUST NOT ends with '/' character
        $filename = $this->ensureFilename($filename);
        // check if file exists
        if(!$this->s3client->doesObjectExist($bucketName, $filename, array())){
            throw new Exception('The file ' . $filename . ' does not exist!');
        }
        // if the file exists, delete it!
        $this->s3client->deleteObject(array('Bucket' => $bucketName, 'Key' => $filename));
    }
    
    public function doesBucketExist($bucketName){
         // check if bucket exists
        return $this->s3client->doesBucketExist($bucketName, false, array());
    }

    public function doesObjectExists($bucketName, $objectName){
        // check if object exists
        return $this->s3client->doesObjectExist($bucketName, $objectName, array());
    }
    
    public function uploadFile ($bucketName, $remoteFolder, $localFile, $wait){
        // check if bucket exists
        if(!$this->s3client->doesBucketExist($bucketName, false, array())) {
            throw new Exception('The bucket ' . $bucketName . ' does not exist!');
        }
        // folder name MUST ends with '/' character
        $remoteFolder = $this->ensureFolderName($remoteFolder);    
        // check if folder exists
        if(!$this->s3client->doesObjectExist($bucketName, $remoteFolder, array())){
            throw new Exception('The folder ' . $remoteFolder . ' does not exist!');
        }       
        // check if local file exists
        if(!file_exists($localFile)){
            throw new Exception('The file ' . $localFile . ' does not exist!');
        }
        // get the local filename WITHOUT the path
        $localFileNameOnly = basename($localFile);
        // create the remote file name using remote folder and local filename
        $remoteFile = $remoteFolder . $localFileNameOnly;        
        // open the local file for reading
        $localFileHandle = fopen($localFile, 'r');
        // upload the local file!!!
        $this->s3client-> putObject(array(
            'Bucket' => $bucketName, 
            'Key' => $remoteFile,
            'Body' => $localFileHandle
        ));        
        // close the local file handle
        fclose($localFileHandle);
        // check if user wants to wait or not
        if(isset($wait)){
            if($wait == true){
                // now wait for it...
                $this->s3client->waitUntilObjectExists(array('Bucket' => $bucketName, 'Key' => $remoteFile, 'waiter.max_attempts' => 3));
            }
        }        
    }
    
    public function getFile($argBucket, $argFilename, $argLocalFile){
        // open the local file for writing
        $fp = fopen($argLocalFile, 'w');
        // get the file
        $this->s3client-> getObject(array(
            'Bucket' => $argBucket,
            'Key' => $argFilename,
            'SaveAs' => $fp
        ));
        // close the local file handle
        fclose($fp);
    }
    
    public function waitUntilObjectExists($argBucket, $argObjectName){
        $this->s3client->waitUntilObjectExists(array('Bucket' => $argBucket, 'Key' => $argObjectName, 'waiter.max_attempts' => 5));
    }
}

?>
