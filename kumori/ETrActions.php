<img src="<?php echo KUMORI_URL . 'kumori/'; ?>kumori_logo2_png24.png"/><br/>
<h2>Manage Elastic Transcoder</h2>
<p>Here you can manage your Elastic Transcoder service.</p>

<?php
    require_once '/Core/Core.php';

    // get an Elastic TRanscoder client wrapper 
    $etr = AWSClientFactoryWrapper::Instance()->createElasticTranscoderClient();

    // get an IAM client wrapper
    $iam = AWSClientFactoryWrapper::Instance()->createIAMClient();

    // let's check the POST
    try {
        if(isset($_POST['newPipeline'])){
            // user wanted to create a new Pipeline, so let's get to it!
            $etr->createPipeline($_POST['newPipeline'], $_POST['createPipelineInputBucket'], 
                    $_POST['createPipelineOutputBucket'], $_POST['createPipelineRole'],
                    new PipelineNotifications('', '', '', '')
                    );                            
            // Pipeline must have been created, inform the user
?>
    <div class='updated settings-error'><p>
<?php            
            echo 'Pipeline '.$_POST['newPipeline'].' was created!<br/><br/>';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['newRole'])){
            // user wanted to create a new Role, so let's do it!
            $iam->createRole('', $_POST['newRole'], null);
            // role must have been created, inform the user
?>
    <div class='updated settings-error'><p>
<?php
            echo 'Role '.$_POST['newRole'].' was created!<br/><br/>';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['newPresetName'])){
            // user wanted to create a new preset, so let's do it!
            $etr->createPreset(
                    $_POST['newPresetName'], 
                    $_POST['newPresetDescription'], 
                    'mp4', //$_POST['newPresetContainer'], 
                    'H.264', //$_POST['newPresetVideoCodec'],  
                    $_POST['newPresetVideoProfile'],   
                    $_POST['newPresetVideoLevel'],
                    $_POST['newPresetVideoMaxReferenceFrames'],
                    '90',//$_POST['newPresetKeyframesMaxDist'], 
                    $_POST['newPresetFixedGOP'],
                    $_POST['newPresetVideoBitRate'], 
                    $_POST['newPresetFrameRate'], 
                    $_POST['newPresetVideoResolution'], 
                    $_POST['newPresetVideoAspectRatio'], 
                    'AAC',//$_POST['newPresetAudioCodec'], 
                    $_POST['newPresetSampleRate'], 
                    $_POST['newPresetAudioBitRate'], 
                    '2',//$_POST['newPresetChannels'], 
                    'png',//$_POST['newPresetThumbFormat'], 
                    '60',//$_POST['newPresetThumbInterval'], 
                    $_POST['newPresetThumbResolution'], 
                    $_POST['newPresetThumbAspectRatio']);
            // Preset must have been created, inform the user
?>
    <div class='updated settings-error'><p>
<?php            
            echo 'Preset '.$_POST['newPresetName'].' was created!<br/><br/>';                    
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['deletePipeline'])){
            // user wanted to delete a pipeline, so let's delete it!
            $etr->deletePipeline($_POST['deletePipeline']);
            // Pipeline must have been deleted, inform the user
?>
    <div class='updated settings-error'><p>
<?php
            echo 'Pipeline '.$_POST['deletePipeline'].' was deleted!<br/><br/>';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['deletePreset'])){
            // user wanted to delete a preset, so let's delete it!
            $etr->deletePreset($_POST['deletePreset']);
            // Preset must have been deleted, inform the user
?>
    <div class='updated settings-error'><p>
<?php
            echo 'Preset '.$_POST['deletePreset'].' was deleted!<br/><br/>';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['deleteRole'])){
            // user wanted to delete a role, so let's get to it!
            $iam->deleteRole($_POST['deleteRole']);
            // Role must have been deleted, inform the user
?>
    <div class='updated settings-error'><p>
<?php            
            echo 'Role '.$_POST['deleteRole'].' was deleted!<br/><br/>';
?>
    </p></div>
<?php               
        }
    }
    catch (Exception $exc) {
        // Oops... Something went wrong!
?>
    <div class='updated settings-error'><p>
<?php
        echo 'An error has occured!<br/>'.$exc->getMessage();
        if(get_option('kumori_debug_mode')){
            echo '<br/><pre>'.$exc->getTraceAsString().'</pre>';
        }        
?>
    </p></div>
<?php          
        echo '<br/>';
    }           
?>

<h3>Pipelines</h3>
<div class="code" style="height: 90px; overflow-y: auto;">
<?php    
    // get the pipelines
    $pipelineList = $etr->listPipelines();

    $cmbPipelines = '';
    $cmbPresets = '';
    $cmbRoles = '';
    $cmbRolesNames = '';
    
    foreach($pipelineList as $pipeline){
        $cmbPipelines .= '<option value="'.$pipeline->getId().'">'.$pipeline->getName().'</option>';
        echo 'Name [Id]: '.$pipeline->getName().' ['.$pipeline->getId().']<br/>'.
            'Input/Output bucket: '.$pipeline->getInputBucket().'/'.$pipeline->getOutputBucket().'<br/>'.
            'Role Arn: '.$pipeline->getRoleArn().'<br/>'.
            'Status: '.$pipeline->getStatus().'</br></br>';
    }
?>
</div>    

<h3>Presets</h3>
<div class="code" style="height: 200px; overflow-y: auto;">
<?php
    // get the presets
    $presetList = $etr->listPresets();

    // uncomment the following lines ONLY to get Netbeans "Intellisense" and make your coding life easier
    //$pVideo = new PresetVideo($argCodec, $argCodecOptions, $argKeyframesMaxDist, $argFixedGOP, $argBitRate, $argFrameRate, $argResolution, $argAspectRatio);
    //$pAudio = new PresetAudio($argCodec, $argSampleRate, $argBitRate, $argChannels);
    //$pVideoCodecOptions = new PresetCodecOptions($argProfile);
    //$pThumbnails = new PresetThumbnails($argFormat, $argInterval, $argResolution, $argAspectRatio);
    foreach ($presetList as $preset) {
        $cmbPresets .= '<option value="'.$preset->getId().'">'.$preset->getName().'</option>';
        echo 'Name [Id]: '.$preset->getName().' ['.$preset->getId().']<br/>'.
                'Description: '.$preset->getDescription().'</br>';    
        // get the video properties of the preset
        $pVideo = $preset->getVideo();                
        // get the codec options
        $pVideoCodecOptions = $pVideo->getCodecOptions();
        echo 'Video Codec/Profile/Level/MaxReferenceFrames: '.$pVideo->getCodec().'/'.$pVideoCodecOptions->getProfile().
                '/'.$pVideoCodecOptions->getLevel().'/'.$pVideoCodecOptions->getMaxReferenceFrames().'<br/>'.
                'Video Resolution/Aspect Ratio: '.$pVideo->getResolution().'/'.$pVideo->getAspectRatio().'<br/>'.
                'Video Bitrare/FrameRate: '.$pVideo->getBitRate().'/'.$pVideo->getFrameRate().'<br/>'.
                'Video Keyframes Max Distance/Fixed GOP: '.$pVideo->getKeyframesMaxDist().'/'.$pVideo->getFixedGOP().'<br/>';
        // get the audio properties of the preset
        $pAudio = $preset->getAudio();
        echo 'Audio Codec: '.$pAudio->getCodec().'<br/>'.
                'Audio Bitrate/SampleRate/Channels: '.$pAudio->getBitRate().'/'.$pAudio->getSampleRate().
                '/'.$pAudio->getChannels().'<br/>';
        // get the tumbnails properties of the preset
        $pThumbnails = $preset->getThumbnails();
        echo 'Thumbnails Format/Resolution/Interval/Aspect Ratio: '.$pThumbnails->getFormat().
                '/'.$pThumbnails->getResolution().'/'.$pThumbnails->getInterval().'/'.$pThumbnails->getAspectRatio().'<br/></br>';
    }
?>
</div>    

<h3>Roles</h3>
<div class="code" style="height: 80px; overflow-y: auto;">
<?php    
    // get the roles
    $roleList = $iam->listRoles();
    
    foreach ($roleList as $role) {
        $cmbRoles .= '<option value="'.$role->getArn().'">'.$role->getName().'</option>';    
        $cmbRolesNames .= '<option value="'.$role->getName().'">'.$role->getName().'</option>';    
        echo 'Role Name [Id]: '.$role->getName().' ['.$role->getId().']'.'<br/>'.
                'Role Arn/Path: '.$role->getArn().'/'.$role->getPath().'<br/>'.
                'Role Create Date/AssumeDocumentPolicy: '.$role->getCreateDate().'/'.urldecode($role->getAssumeRolePolicyDocument()).'<br/>';
    }            
?>
</div>    

<?php
    // get an S3 client wrapper object            
    $s3 = AWSClientFactoryWrapper::Instance()->createSimpleStorageServiceClient();
    // get the buckets
    $bucketList = $s3->listBuckets();
    $cmbBuckets = '';
    // make the combo list
    foreach($bucketList as $bucket){
        // make bucket options
        $cmbBuckets .= '<option value="'.$bucket->getName().'">'.$bucket->getName().'</option>';                
    }

    $cmbProfiles = '<option value="baseline">baseline</option>';
    $cmbProfiles .= '<option value="main">main</option>';
    $cmbProfiles .= '<option value="high">high</option>';

    $cmbTrueFalse = '<option value="true">true</option>';
    $cmbTrueFalse .= '<option value="false">false</option>';

    $cmbFrameRate = '<option value="15">15fps</option>';
    $cmbFrameRate .= '<option value="25">25fps</option>';
    $cmbFrameRate .= '<option value="29.97">29.97fps</option>';
    $cmbFrameRate .= '<option value="30">30fps</option>';

    $cmbAspectRatio = '<option value="4:3">4:3</option>';
    $cmbAspectRatio .= '<option value="16:9">16:9</option>';

    $cmbResolution = '<option value="320x240">320x240   [4:3]</option>';
    $cmbResolution .= '<option value="480x360">480x360   [4:3]</option>';
    $cmbResolution .= '<option value="640x480">640x480   [4:3]</option>';
    $cmbResolution .= '<option value="960x720">960x720   [4:3]</option>';
    $cmbResolution .= '<option value="1440x1080">1440x1080 [4:3]</option>';
    $cmbResolution .= '<option value="640x360">640x360   [16:9]</option>';
    $cmbResolution .= '<option value="858x480">858x480   [16:9]</option>';
    $cmbResolution .= '<option value="1280x720">1280x720  [16:9]</option>';
    $cmbResolution .= '<option value="1920x1080">1920x1080 [16:9]</option>';

    $cmbVideoBitrate = '<option value="300">300  [240p]</option>';
    $cmbVideoBitrate .= '<option value="600">600  [240p - 360p]</option>';
    $cmbVideoBitrate .= '<option value="900">900  [360p - 480p]</option>';
    $cmbVideoBitrate .= '<option value="1200">1200 [480p]</option>';
    $cmbVideoBitrate .= '<option value="2400">2400 [720p]</option>';
    $cmbVideoBitrate .= '<option value="5400">5400 [1080p]</option>';

    $cmbAudioSampleRate = '<option value="22050">22050</option>';
    $cmbAudioSampleRate .= '<option value="32000">32000</option>';
    $cmbAudioSampleRate .= '<option value="44100">44100</option>';
    $cmbAudioSampleRate .= '<option value="48000">48000</option>';

    $cmbAudioBitRate = '<option value="64">64</option>';
    $cmbAudioBitRate .= '<option value="128">128</option>';
    $cmbAudioBitRate .= '<option value="160">160</option>';

    $cmbThumbResolution = '<option value="192x108">192x108 [16:9]</option>';
    $cmbThumbResolution .= '<option value="192x144">192x144 [4:3]</option>';

/* Too small levels
    $cmbLevels = '<option value="1">[main] 1</option>';
    $cmbLevels .= '<option value="1b">[main] 1b</option>';
    $cmbLevels .= '<option value="1.1">[main] 1.1</option>';            
    $cmbLevels .= '<option value="1.2">[main] 1.2</option>';
    $cmbLevels .= '<option value="1.3">[main] 1.3</option>';
*/            

    $cmbLevels = '<option value="2">2  [240p]</option>';            
    $cmbLevels .= '<option value="2.1">2.1 [360p]</option>';
    $cmbLevels .= '<option value="2.2">2.2 [480p]</option>';           

    $cmbLevels .= '<option value="3">3  [480p]</option>';
    $cmbLevels .= '<option value="3.1">3.1 [720p]</option>';            
    $cmbLevels .= '<option value="3.2">3.2 [720p]</option>';

    $cmbLevels .= '<option value="4">4  [1080p]</option>';
    $cmbLevels .= '<option value="4.1">4.1 [1080p]</option>';


    $cmbMaxReferenceFrames = '<option value="0">0 [Level 2]</option>';
    $cmbMaxReferenceFrames .= '<option value="1">1 [Level 2]</option>';
    $cmbMaxReferenceFrames .= '<option value="2">2 [Level 3]</option>';
    $cmbMaxReferenceFrames .= '<option value="3">3 [Level 3]</option>';
    $cmbMaxReferenceFrames .= '<option value="4">4 [Level 4]</option>';
    $cmbMaxReferenceFrames .= '<option value="5">5 [Level 4]</option>';
?>

<br/>

<h3>Create Pipeline</h3>
<form name="createPipeline" action="<?php echo admin_url('admin.php?page='.KUMORI_ETR_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">New Pipeline</th>
            <td>
                <input type="text" name="newPipeline" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Input Bucket</th>
            <td>
                <select name="createPipelineInputBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Output Bucket</th>
            <td>
                <select name="createPipelineOutputBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Role</th>
            <td>
                <select name="createPipelineRole" class="code"><?php echo $cmbRoles; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Create Pipeline" value="Create Pipeline" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>
<br/>

<h3>Create Preset</h3>
<form name="createPreset" action="<?php echo admin_url('admin.php?page='.KUMORI_ETR_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">New Preset</th>
            <td>
                <input type="text" name="newPresetName" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Description</th>
            <td>
                <input type="text" name="newPresetDescription" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Container</th>
            <td>
                <input type="text" name="newPresetContainer" disabled="disabled" value="MP4" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Codec</th>
            <td>
                <input type="text" name="newPresetVideoCodec" disabled="disabled" value="H.264" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Profile</th>
            <td>
                <select name="newPresetVideoProfile" class="code"><?php echo $cmbProfiles; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Level</th>
            <td>
                <select name="newPresetVideoLevel" class="code"><?php echo $cmbLevels; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Max ReferenceFrames</th>
            <td>
                <select name="newPresetVideoMaxReferenceFrames" class="code"><?php echo $cmbMaxReferenceFrames; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Keyframes Max Distance</th>
            <td>
                <input type="text" name="newPresetKeyframesMaxDist" disabled="disabled" value="90" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video FixedGOP</th>
            <td>
                <select name="newPresetFixedGOP" class="code"><?php echo $cmbTrueFalse; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video BitRate</th>
            <td>
                <select name="newPresetVideoBitRate" class="code"><?php echo $cmbVideoBitrate; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video FrameRate</th>
            <td>
                <select name="newPresetFrameRate" class="code"><?php echo $cmbFrameRate; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Resolution</th>
            <td>
                <select name="newPresetVideoResolution" class="code"><?php echo $cmbResolution; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Video Aspect Ratio</th>
            <td>
                <select name="newPresetVideoAspectRatio" class="code"><?php echo $cmbAspectRatio; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Audio Codec</th>
            <td>
                <input type="text" name="newPresetAudioCodec" disabled="disabled" value="AAC" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Audio SampleRate</th>
            <td>
                <select name="newPresetSampleRate" class="code"><?php echo $cmbAudioSampleRate; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Audio BitRate</th>
            <td>
                <select name="newPresetAudioBitRate" class="code"><?php echo $cmbAudioBitRate; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Audio Channels</th>
            <td>
                <input type="text" name="newPresetChannels" disabled="disabled" value="2" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Thumbnails Format</th>
            <td>
                <input type="text" name="newPresetThumbFormat" disabled="disabled" value="png" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Thumbnails Interval</th>
            <td>
                <input type="text" name="newPresetThumbInterval" disabled="disabled" value="60" class="code">
            </td>
        </tr>
        <tr valign="top"><th scope="row">Thumbnails Resolution</th>
            <td>
                <select name="newPresetThumbResolution" class="code"><?php echo $cmbThumbResolution; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Thumbnails AspectRatio</th>
            <td>
                <select name="newPresetThumbAspectRatio" class="code"><?php echo $cmbAspectRatio; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Create Preset" value="Create Preset" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>
<br/>        

<h3>Create Role</h3>
<form name="createRole" action="<?php echo admin_url('admin.php?page='.KUMORI_ETR_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">New Role</th>
            <td>
                <input type="text" name="newRole" class="code">
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Create Role" value="Create Role" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>        
<br/>

<h3>Delete Pipeline</h3>
<form name="deletePipeline" action="<?php echo admin_url('admin.php?page='.KUMORI_ETR_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Pipeline</th>
            <td>
                <select name="deletePipeline" class="code"><?php echo $cmbPipelines; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Delete Pipeline" value="Delete Pipeline" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>        
<br/>

<h3>Delete Preset</h3>
<form name="deletePreset" action="<?php echo admin_url('admin.php?page='.KUMORI_ETR_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Preset</th>
            <td>
                <select name="deletePreset" class="code"><?php echo $cmbPresets; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Delete Preset" value="Delete Preset" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>        
<br/>

<h3>Delete Role</h3>
<form name="deleteRole" action="<?php echo admin_url('admin.php?page='.KUMORI_ETR_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Role</th>
            <td>
                <select name="deleteRole" class="code"><?php echo $cmbRolesNames; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Delete Role" value="Delete Role" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>        
<br/>

<?php
    if(get_option('kumori_debug_mode')){
?>
    <h3>Debug Info</h3>
    <div class='updated settings-error'>
        <p><pre><?php print_r($_POST) ?></pre></p>
        <p><pre><?php print_r($_FILES) ?></pre></p>
    </div>
<?php
    }
?>