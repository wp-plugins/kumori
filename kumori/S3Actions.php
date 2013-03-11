<img src="<?php echo KUMORI_URL . 'kumori/'; ?>kumori_logo2_png24.png"/><br/>
<h2>Manage S3</h2>
<p>Here you can manage your S3 service.</p>

<?php
    require_once '/Core/Core.php';            
    // get an S3 client wrapper object            
    $s3 = AWSClientFactoryWrapper::Instance()->createSimpleStorageServiceClient();

    // let's check the POST
    try {
        if(isset($_POST['newBucket'])){
            // user wanted to create a new Bucket, so let's get to it!
            $s3->createBucket($_POST['newBucket'], true);
            // bucket must have been created, inform the user
?>
    <div class='updated settings-error'><p>    
<?php
            echo 'Bucket '.$_POST['newBucket'].' was created!';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['deleteBucket'])){
            // user wanted to delete a Bucket, so let's delete it!
            $s3->deleteBucket($_POST['deleteBucket'], true);
            // bucket must have been deleted, inform the user
?>
    <div class='updated settings-error'><p>    
<?php
            echo 'Bucket '.$_POST['deleteBucket'].' was deleted!';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['createFolderBucket'])){
            // user wanted to create a new Folder, so let's create it!
            $s3->createFolder($_POST['createFolderBucket'], $_POST['createFolder'], true);
            // folder must have been created, inform the user
?>
    <div class='updated settings-error'><p>    
<?php
            echo 'Folder '.$_POST['createFolder'].' in bucket '.$_POST['createFolderBucket'].' was created!';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['deleteFolderBucket'])){
            // user wanted to delete a Folder, so let's delete it!
            $s3->deleteFolder($_POST['deleteFolderBucket'], $_POST['deleteFolder']);
            // folder must have been deleted, inform the user
?>
    <div class='updated settings-error'><p>    
<?php
            echo 'Folder '.$_POST['deleteFolder'].' in bucket '.$_POST['deleteFolderBucket'].' was deleted!';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['deleteFileBucket'])){
            // user wanted to delete a File, so let's delete it!
            $s3->deleteFile($_POST['deleteFileBucket'], $_POST['deleteFile']);
            // file must have been deleted, inform the user
?>
    <div class='updated settings-error'><p>    
<?php
            echo 'File '.$_POST['deleteFile'].' in bucket '.$_POST['deleteFileBucket'].' was deleted!';
?>
    </p></div>
<?php               
        }
        elseif(isset($_POST['uploadFileBucket'])){
            // check if the $_FILES is set
            if(isset($_FILES['localFile'])){
                // check if there are errors
                if ($_FILES["localFile"]["error"] > 0)
                {
                    throw new Exception($_FILES["localFile"]["error"]);
                }
                // check if file already exists in server
                if (file_exists(getcwd() . "/Upload/" . $_FILES["localFile"]["name"]))
                {
                    throw new Exception($_FILES["localFile"]["name"].' already exists in server!');
                }
                // move the uploaded file to the specified folder
                move_uploaded_file($_FILES["localFile"]["tmp_name"],
                    getcwd() . "/Upload/" . $_FILES["localFile"]["name"]);
                // user wanted to upload a File, so let's get to the uploading!
                $s3->uploadFile($_POST['uploadFileBucket'], $_POST['uploadFileFolder'], 
                        getcwd() . "/Upload/" . $_FILES["localFile"]["name"], true);
                // file must have been uploaded, inform the user
?>
    <div class='updated settings-error'><p>    
<?php
                echo 'File '.$_FILES["localFile"]["name"].' was uploaded to bucket '.$_POST['uploadFileBucket'].'!';
?>
    </p></div>
<?php        
            }
        }                          
    } catch (Exception $exc) {
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

<h3>S3 Contents</h3>

<?php
    // get Buckets
    $bucketList = $s3->listBuckets();
    $cmbBuckets = '';
    $cmbFolders = '';
    $cmbFiles = '';
    echo '<ul class="code">';
    foreach($bucketList as $bucket){
        // make bucket options
        $cmbBuckets .= '<option value="'.$bucket->getName().'">'.$bucket->getName().'</option>';
        echo '<li>'.$bucket->getName().'</li>';
        // get Folders for the bucket
        $folderList = $s3->listFolders($bucket->getName(), '');
        echo '<ul>';
        foreach ($folderList as $folder) {
            $cmbFolders .= '<option value="'.$folder->getName().'">'.$folder->getName().'</option>';
            echo '<li>'.$folder->getName().'</li>';
            // get Files for the folder
            $fileList = $s3->listFiles($bucket->getName(), $folder->getName());
            echo '<ul>';
            foreach ($fileList as $file) {
                $cmbFiles .= '<option value="'.$file->getName().'">'.$file->getName().'</option>';
                echo '<li>'.$file->getName().' ['.$file->getSize().' bytes]'.'</li>';
            }
            echo '</ul>';
        }
        echo '</ul>';
    }         
    echo '</ul>';
?>        
<br/>

<h3>Create new Bucket</h3>
<form name="createBucket" action="<?php echo admin_url('admin.php?page='.KUMORI_S3_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">New Bucket</th>
            <td>
                <input type="text" name="newBucket" class="code">
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Create Bucket" value="Create Bucket" class="button button-primary">
            </td>
        </tr>
    </table>            
    </p>
</form>
<br/>

<h3>Delete Bucket</h3>
<form name="deleteBucket" action="<?php echo admin_url('admin.php?page='.KUMORI_S3_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Bucket</th>
            <td>
                <select name="deleteBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Delete Bucket" value="Delete Bucket" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>
<br/>

<h3>Create Folder</h3>
<form name="createFolder" action="<?php echo admin_url('admin.php?page='.KUMORI_S3_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Bucket</th>
            <td>
                <select name="createFolderBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Folder</th>
            <td>
                <input type="text" name="createFolder" class="code">
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Create Folder" value="Create Folder" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>
<br/>

<h3>Delete Folder</h3>
<form name="deleteFolder" action="<?php echo admin_url('admin.php?page='.KUMORI_S3_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Bucket</th>
            <td>
                <select name="deleteFolderBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Folder</th>
            <td>
                <select name="deleteFolder" class="code"><?php echo $cmbFolders; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Delete Folder" value="Delete Folder" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>
</form>
<br/>

<h3>Delete File</h3>
<form name="deleteFile" action="<?php echo admin_url('admin.php?page='.KUMORI_S3_ACTIONS_PAGE); ?>" method="post">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Bucket</th>
            <td>
                <select name="deleteFileBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">File</th>
            <td>
                <select name="deleteFile" class="code"><?php echo $cmbFiles; ?></select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Delete File" value="Delete File" class="button button-primary">
            </td>
        </tr>               
    </table>
    </p>    
</form>        
<br/>

<h3>Upload File</h3>
<form name="uploadFile" action="<?php echo admin_url('admin.php?page='.KUMORI_S3_ACTIONS_PAGE); ?>" method="post" enctype="multipart/form-data">
    <p>
    <table class="form-table">
        <tr valign="top"><th scope="row">Bucket</th>
            <td>
                <select name="uploadFileBucket" class="code"><?php echo $cmbBuckets; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Folder</th>
            <td>
                <select name="uploadFileFolder" class="code"><?php echo $cmbFolders; ?></select>
            </td>
        </tr>
        <tr valign="top"><th scope="row">Local File</th>
            <td>
                <input type="file" name="localFile" size="40" class="code">
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="Upload File" value="Upload File" class="button button-primary">
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

