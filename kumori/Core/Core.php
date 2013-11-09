<?php
define( 'CORE_PATH', realpath(dirname(__FILE__)) . '/' );
require_once CORE_PATH . 'Objects/AllObjects.php';
require_once CORE_PATH . 'Wrappers/AWSClientFactoryWrapper.php';
require_once CORE_PATH . 'Wrappers/ElasticTranscoderClientWrapper.php';
require_once CORE_PATH . 'Wrappers/IAMClientWrapper.php';
require_once CORE_PATH . 'Wrappers/SimpleStorageServiceClientWrapper.php';
?>
