<?php

require_once '/../AWS/aws.phar';

use Aws\Common\Enum\Region;
use Aws\Common\Aws;

/**
 * A singleton factory class that is used to construct client classes
 * for accessing AWS
 */
class AWSClientFactoryWrapper
{
    private $awsAccessId;
    private $awsSecretKey;
    private $serviceRegion;
    private $certificatePath;
    // the static instance of the singleton class
    private static $inst = null;
    
    /**
     * Call this method to get singleton instance
     *
     * @return AWSClientFactoryWrapper
     */
    public static function Instance()
    {
        if (self::$inst === null) {
            self::$inst = new self();
        }
        return self::$inst;
    }

    /**
     * The private constructor
     */
    final private function __construct()
    {
        // Set the private variables
        // get AWS Access Key
        //$this->awsAccessId = 'AKIAISYUUKRZL3HMB23A';
        $this->awsAccessId = get_option('kumori_aws_access_id');
        
        // get AWS Secret Key
        //$this->awsSecretKey = '3PfMw1E1AfdNEGFhuLsy2wkcZwP3iq4pQvFfec7b';
        $this->awsSecretKey = get_option('kumori_aws_secret_key');
        
        // get region
        //$this->serviceRegion = Region::IRELAND;
        $this->serviceRegion = get_option('kumori_aws_region');
        
        // get certificate path
        $this->certificatePath = KUMORI_PATH . 'kumori/Core/Wrappers/cacert.pem';
    }
    
    /**
     * Creates a Simple Storage Service client wrapper
     * 
     * @return S3Client an AWS Simple Storage Service client wrapper
     */
    public function createSimpleStorageServiceClient(){
        return new SimpleStorageServiceClientWrapper($this->createAWSClient('s3'));
    }
    
    /**
     * Creates an Elastic Transcoder client wrapper
     * 
     * @return ElasticTranscoderClientWrapper an AWS Elastic Transcoder client wrapper
     */
    public function createElasticTranscoderClient(){
        return new ElasticTranscoderClientWrapper($this->createAWSClient('elastictranscoder'));
    }
    
    /**
     * Creates an IAM client wrapper
     * 
     * @return IAMClientWrapper an AWS IAM client wrapper
     */
    public function createIAMClient(){
        return new IAMClientWrapper($this->createAWSClient('iam'));
    }

    /**
     * Returns the region
     * @return RegionEnum
     */
    public function getRegion(){
        return $this->serviceRegion;
    }
    
    /**
     * Creates an AWS Client according to the service
     * @param String $serviceType
     * @return AbstractClient
     */
    private function createAWSClient($serviceType){
        // Create a service builder
        $aws = Aws::factory(array(
            'key'    => $this->awsAccessId,
            'secret' => $this->awsSecretKey,
            'region' => $this->serviceRegion,
            'ssl.certificate_authority' => $this->certificatePath
        ));
        
        return $aws->get($serviceType);
    }
}
?>
