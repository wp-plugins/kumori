<img src="<?php echo KUMORI_URL . 'kumori/'; ?>kumori_logo2_png24.png"/><br/>
<h2>Kumori-fy Videos!</h2>
<p>You can select multiple encoding presets and create transcoded video files from your source video on-the-fly.</p>

<?php
    require_once '/Core/Core.php';
    // make sure that the script will take its time...
    set_time_limit(0);
    ini_set('max_execution_time', '0');

    // get an Elastic TRanscoder client wrapper 
    $etr = AWSClientFactoryWrapper::Instance()->createElasticTranscoderClient();

    // check the post
    try {
        if(isset($_POST['kumoriPresets'])){
            // let's check the data
            // check if presets were selected
            if(count($_POST['kumoriPresets']) == 0){
                throw new Exception("No Presets were selected!");
            }
            // check if $_FILES is set
            if(!isset($_FILES['localFile'])){
                throw new Exception("No local file was selected!");
            }
            // check if there are errors with the file
            if ($_FILES["localFile"]["error"] > 0)
            {
                throw new Exception($_FILES["localFile"]["error"]);
            }
            // get wordpress upload dir and url
            $uploads = wp_upload_dir();
            $uploadFolder = $uploads['path'].'/';
            $uploadUrl = $uploads['url'].'/';
            // check if file already exists in server
            if (file_exists($uploadFolder . $_FILES["localFile"]["name"]))
            {
                throw new Exception($_FILES["localFile"]["name"].' already exists in server!');
            }                    
            // everything seems fine, let's start the fun!                    
            // get an S3 client wrapper
            $s3 = AWSClientFactoryWrapper::Instance()->createSimpleStorageServiceClient();

            // 1. upload local file to host
            // move the uploaded file to the specified folder
            move_uploaded_file($_FILES["localFile"]["tmp_name"],
                $uploadFolder . $_FILES["localFile"]["name"]);                    

            // insert attachment to wordpress media library
            //post_title, post_content (the value for this key should be the empty string), post_status and post_mime_type
            $wp_filetype = wp_check_filetype($uploadFolder . $_FILES["localFile"]["name"], null );
            $attach_id = wp_insert_attachment( 
                    array(
                        'guid' => $uploadUrl . $_FILES["localFile"]["name"], 
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => $_FILES["localFile"]["name"],
                        'post_content' => '',
                        'post_status' => 'inherit'
                    ), 
                    $uploadFolder . $_FILES["localFile"]["name"], 
                    ''
            );
            // you must first include the image.php file for the function wp_generate_attachment_metadata() to work
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $uploadFolder . $_FILES["localFile"]["name"] );
            wp_update_attachment_metadata( $attach_id, $attach_data );            
            
            // 2. get the available pipelines
            $pipelineList = $etr->listPipelines();
            // get the user presets
            $presetIds = $_POST['kumoriPresets'];
            // set the current Pipeline Index
            $currentPipelineIdx = 0;
            // prepare an array with the output files data
            $outputFilesList = array();
            // 3. loops through the presets                    
            foreach ($presetIds as $presetId) {
                // 4. every preset should be processed to a different pipeline for parallel processing
                // 5. get details of the selected pipeline
                $currentPipeline = $pipelineList[$currentPipelineIdx];

                // get folders and select the first one
                $inputBucketFolderList = $s3->listFolders($currentPipeline->getInputBucket(), '');
                if(count($inputBucketFolderList) == 0){
                    throw new Exception('Input bucket ' . $currentPipeline->getInputBucket() . ' does not have any folders!');
                }
                $currentInputFolder = $inputBucketFolderList[0]->getName();

                $outputBucketFolderList = $s3->listFolders($currentPipeline->getOutputBucket(), '');
                if(count($outputBucketFolderList) == 0){
                    throw new Exception('Output bucket ' . $currentPipeline->getOutputBucket() . ' does not have any folders!');
                }
                $currentOutputFolder = $outputBucketFolderList[0]->getName();

                // 6. check if the input file is already in the input bucket, else put it there
                $s3->uploadFile(
                        $currentPipeline->getInputBucket(), 
                        $currentInputFolder, 
                        $uploadFolder . $_FILES["localFile"]["name"], 
                        true
                );

                // 7. make an appropriate output file name
                $remoteOutputName = $_FILES["localFile"]["name"] . '.' .$presetId . '.mp4';

                // 8. create the job
                $etr->createJob(
                        $currentPipeline->getId(), 
                        $currentInputFolder . $_FILES["localFile"]["name"], 
                        'auto', //$argFrameRate, 
                        'auto', //$argResolution, 
                        'auto', //$argAspectRatio, 
                        'auto', //$argInterlaced, 
                        'auto', //$argContainer, 
                        $currentOutputFolder . $remoteOutputName,  //$argOutputFilename, 
                        '', //$argThumbPattern, 
                        '0', //$argRotate, 
                        $presetId 
                );
                // add the data for the output files
                $outputFilesList[] = array(
                    'Bucket' => $currentPipeline->getOutputBucket(),
                    'Folder' => $currentOutputFolder,
                    'File' => $remoteOutputName
                );
                // get next pipeline
                if($currentPipelineIdx + 1 == count($pipelineList)){
                    $currentPipelineIdx = 0;
                }
                else{
                    $currentPipelineIdx++;
                }
            }

            // 9. wait until all the output files exist
            // 10. download all the output files to the host
            foreach ($outputFilesList as $outputFile) {
                $s3->waitUntilObjectExists($outputFile['Bucket'], $outputFile['Folder'].$outputFile['File']);
                $s3->getFile(
                        $outputFile['Bucket'], 
                        $outputFile['Folder'] . $outputFile['File'], 
                        $uploadFolder . $outputFile['File']
                );
                // insert attachment to wordpress media library
                //post_title, post_content (the value for this key should be the empty string), post_status and post_mime_type
                $wp_filetype = wp_check_filetype($uploadFolder . $outputFile['File'], null );
                $attach_id = wp_insert_attachment( 
                        array(
                            'guid' => $uploadUrl . $outputFile['File'], 
                            'post_mime_type' => $wp_filetype['type'],
                            'post_title' => $outputFile['File'],
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ), 
                        $uploadFolder . $outputFile['File'], 
                        ''
                );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $uploadFolder . $outputFile['File'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );                
            }
            // 11. echo the links to the happy user! :)
?>
    <div class='updated settings-error'><p>    
<?php
            echo 'Your file was Kumori-fied successfully!!!<br/>Links to your Kumori-fied files:<br/>';
            foreach ($outputFilesList as $outputFile) {
                echo '<a href="' . $uploadUrl . $outputFile['File'] . '">'.$outputFile['File'].'</a>'.'<br/>';
            }
?>
    </p></div>
<?php  
        }    
    } catch (Exception $exc) {
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

    // get the presets
    $presetList = $etr->listPresets();
    // make the combo
    $cmbPresets = '';
    foreach ($presetList as $preset) {
        $cmbPresets .= '<option value="'.$preset->getId().'">'.$preset->getName().'</option>';
    }

?>


<form name="uploadFile" action="<?php echo admin_url('admin.php?page='.KUMORI_ACTIONS_PAGE); ?>" method="post" enctype="multipart/form-data">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Presets</th>
            <td>
                <select name="kumoriPresets[]" multiple="multiple" size="10" style="width: 300px" class="code"><?php echo $cmbPresets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Local File</th>
            <td>
                <input type="file" name="localFile" size="50" class="button">
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Kumori-fy File!" value="Kumori-fy File!" class="button button-primary">
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
