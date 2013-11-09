<?php

require_once CORE_PATH . 'Objects/ElasticTranscoderObjects.php';
require_once 'AWSClientFactoryWrapper.php';

class ElasticTranscoderClientWrapper{

    private $etrClient = null;
    
    public function __construct($etr){
        if($etr === null){
            throw new Exception('The elastic transcoder client specified was null!');
        }
        $this->etrClient = $etr;
    }   
    
    public function listPipelines(){ 
       $pipelineList = $this->etrClient->listPipelines(array())->get('Pipelines');
       $pipelineObjectList = array();
       foreach($pipelineList as $pipeline){
           $pNotifications = $pipeline['Notifications'];
           $pipelineObjectList[] = new Pipeline(
                   $pipeline['Id'], 
                   $pipeline['Name'], 
                   $pipeline['Status'], 
                   $pipeline['InputBucket'], 
                   $pipeline['OutputBucket'], 
                   $pipeline['Role'], 
                   new PipelineNotifications(
                           $pNotifications['Progressing'], 
                           $pNotifications['Completed'], 
                           $pNotifications['Warning'], 
                           $pNotifications['Error']
                   )
            );
       }
       return $pipelineObjectList;
    }
    
    public function listPresets(){
        $presetList = $this->etrClient->listPresets(array())->get('Presets');
        $presetListObjectList = array();
        foreach ($presetList as $preset) {
            $pVideo = $preset['Video'];
            $pAudio = $preset['Audio'];
            $pThumbnails = $preset['Thumbnails'];
            $presetListObjectList[] = new Preset(
                    $preset['Id'], 
                    $preset['Name'],
                    $preset['Description'], 
                    '', 
                    new PresetVideo(
                            $pVideo['Codec'], 
                            new PresetCodecOptions(
                                    $pVideo['CodecOptions']['Profile'],
                                    $pVideo['CodecOptions']['Level'],
                                    $pVideo['CodecOptions']['MaxReferenceFrames']
                            ), 
                            $pVideo['KeyframesMaxDist'], 
                            $pVideo['FixedGOP'], 
                            $pVideo['BitRate'], 
                            $pVideo['FrameRate'], 
                            $pVideo['Resolution'], 
                            $pVideo['AspectRatio']
                    ),
                    new PresetAudio(
                            $pAudio['Codec'], 
                            $pAudio['SampleRate'], 
                            $pAudio['BitRate'], 
                            $pAudio['Channels']
                    ),
                    new PresetThumbnails(
                            $pThumbnails['Format'], 
                            $pThumbnails['Interval'], 
                            $pThumbnails['Resolution'], 
                            $pThumbnails['AspectRatio']
               ));
            //echo '<pre>'.print_r($pVideo['CodecOptions']).'</pre>';
        }
        return $presetListObjectList;
    }
    
    public function listJobs($pipelineId){        
        return $this->etrClient->listJobsByPipeline(array('PipelineId' => $pipelineId))->get('Jobs');
    }
    
    public function deletePipeline($pipelineId){
        $this->etrClient->deletePipeline(array('Id' => $pipelineId));
    }

    public function deletePreset($presetId){
        $this->etrClient->deletePreset(array('Id' => $presetId));
    }
    
    public function createPipeline($argName, $argInputBucket, $argOutputBucket, $argRoleArn, $argNotifications){
        // get an S3 client wrapper object            
        $s3 = AWSClientFactoryWrapper::Instance()->createSimpleStorageServiceClient();
        
        // check if input bucket exist       
        if(!$s3->doesBucketExist($argInputBucket)) {
            throw new Exception('The bucket '.$argInputBucket.' does not exist!');
        }       
        // check if output bucket exists
        if(!$s3->doesBucketExist($argOutputBucket)) {
            throw new Exception('The bucket '.$argOutputBucket.' does not exist!');
        }           
        //$argNotifications = new PipelineNotifications($argProgressing, $argCompleted, $argWarning, $argError);
        // create the new pipeline
        $this->etrClient->createPipeline(array(
            'Name' => $argName,
            'InputBucket' => $argInputBucket,
            'OutputBucket' => $argOutputBucket,
            'Role' => $argRoleArn,
            'Notifications' => array(
                'Progressing' => $argNotifications->getProgressing(),
                'Completed' => $argNotifications->getCompleted(),
                'Warning' => $argNotifications->getWarning(),
                'Error' => $argNotifications->getError()
            )
        ));
    }
    
    public function createPreset($argName, $argDescription, $argContainer, 
            $argVideoCodec, $argVideoProfile, $argVideoLevel, $argVideoMaxReferenceFrames, 
            $argKeyframesMaxDist, $argFixedGOP, $argVideoBitRate, $argFrameRate, 
            $argVideoResolution, $argVideoAspectRatio, $argAudioCodec, $argSampleRate, 
            $argAudioBitRate, $argChannels, $argThumbFormat, $argInterval, 
            $argThumbResolution, $argThumbAspectRatio){       
        // let's create this preset...
        $this->etrClient->createPreset(array(
            'Name' => $argName,
            'Description' => $argDescription,
            'Container' => $argContainer, // MP4
            'Video' => array(
                'Codec' => $argVideoCodec, // H.264
                'CodecOptions' => array(
                    'Profile' => $argVideoProfile,
                    'Level' => $argVideoLevel,
                    'MaxReferenceFrames' => $argVideoMaxReferenceFrames
                ),
                'KeyframesMaxDist' => $argKeyframesMaxDist,
                'FixedGOP' => $argFixedGOP,
                'BitRate' => $argVideoBitRate,
                'FrameRate' => $argFrameRate,
                'Resolution' => $argVideoResolution,
                'AspectRatio' => $argVideoAspectRatio
            ),
            'Audio' => array(
                'Codec' => $argAudioCodec, // AAC
                'SampleRate' => $argSampleRate,
                'BitRate' => $argAudioBitRate,
                'Channels' => $argChannels
            ),
            'Thumbnails' => array(
                'Format' => $argThumbFormat,
                'Interval' => $argInterval,
                'Resolution' => $argThumbResolution,
                'AspectRatio' => $argThumbAspectRatio
            )
        ));
    }
    
    public function createJob($argPipelineId, $argInputFilename, $argFrameRate, 
            $argResolution, $argAspectRatio, $argInterlaced, $argContainer, 
            $argOutputFilename, $argThumbPattern, $argRotate, $argPresetId){
        // let's create the job
        $this->etrClient->createJob(array(
            'PipelineId' => $argPipelineId,
            'Input' => array(
                'Key' => $argInputFilename,
                'FrameRate' => $argFrameRate, // auto
                'Resolution' => $argResolution, // auto
                'AspectRatio' => $argAspectRatio, // auto
                'Interlaced' => $argInterlaced, // auto
                'Container' => $argContainer // auto
            ),
            'Output' => array(
                'Key' => $argOutputFilename,
                'ThumbnailPattern' => $argThumbPattern,
                'Rotate' => $argRotate,
                'PresetId' => $argPresetId
            )
        ));
    }
    
    public function readPipeline($argPipelineId){
        $res = $this->etrClient->readPipeline(array('Id' => $argPipelineId));
        $resPipeline = $res->get('Pipeline');
        $resPipelineNotifications = $resPipeline['Notifications'];
        return new Pipeline(
                $resPipeline['Id'], 
                $resPipeline['Name'], 
                $resPipeline['Status'], 
                $resPipeline['InputBucket'], 
                $resPipeline['OutputBucket'], 
                $resPipeline['Role'], 
                new PipelineNotifications(
                        $resPipelineNotifications['Progressing'], 
                        $resPipelineNotifications['Completed'], 
                        $resPipelineNotifications['Warning'], 
                        $resPipelineNotifications['Error']
                )
         );
    }
    
    public function readPreset($argPresetId){
        $res = $this->etrClient->readPreset(array('Id' => $argPresetId));
        $resPreset = $res->get('Preset');
        $resVideo = $resPreset['Video'];
        $resAudio = $resPreset['Audio'];
        $resThumb = $resPreset['Thumbnails'];
     
        return new Preset(
                $resPreset['Id'], 
                $resPreset['Name'], 
                $resPreset['Description'], 
                $resPreset['Container'], 
                new PresetVideo(
                        $resVideo['Codec'], 
                        new PresetCodecOptions(
                                $resVideo['CodecOptions']['Profile'], 
                                $resVideo['CodecOptions']['Level'], 
                                $resVideo['CodecOptions']['MaxReferenceFrames']
                        ), 
                        $resVideo['KeyframesMaxDist'], 
                        $resVideo['FixedGOP'], 
                        $resVideo['BitRate'], 
                        $resVideo['FrameRate'], 
                        $resVideo['Resolution'], 
                        $resVideo['AspectRatio']), 
                new PresetAudio(
                        $resAudio['Codec'], 
                        $resAudio['SampleRate'], 
                        $resAudio['BitRate'], 
                        $resAudio['Channels']
                ), 
                new PresetThumbnails(
                        $resThumb['Format'], 
                        $resThumb['Interval'], 
                        $resThumb['Resolution'], 
                        $resThumb['AspectRatio']
                )
         );
    }
}

?>
