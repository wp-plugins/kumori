<?php

require_once CORE_PATH . 'Objects/CommonObjects.php';

class IAMClientWrapper{
    private $IAMclient = null;
    
    public function __construct($iam){
        if($iam === null){
            throw new Exception('The iam client specified was null!');
        }
        $this->IAMclient = $iam;
    }    
    
    public function listRoles(){
        $roleList = $this->IAMclient->listRoles(array())->get('Roles');
        $roleObjectList = array();
        foreach ($roleList as $role) {
            $roleObjectList[] = new Role(
                    $role['Path'], 
                    $role['RoleName'], 
                    $role['RoleId'], 
                    $role['Arn'], 
                    $role['CreateDate'], 
                    $role['AssumeRolePolicyDocument']);
        }
        return $roleObjectList;
    }
    
    public function createRole($path, $name, $AssumeRolePolicyDocument){
        $defaultPolicyDocument = '{
	"Statement": [
		{
			"Sid": "1",
			"Effect": "Allow",
			"Principal":
				{
					"Service": "elastictranscoder.amazonaws.com"
				},
			"Action": "sts:AssumeRole"
		}
	]
}';
        if(!isset($AssumeRolePolicyDocument)){
            $AssumeRolePolicyDocument = $defaultPolicyDocument;
        }
        $this->IAMclient-> createRole(array(
            //'Path' => $path,
            'RoleName' => $name,
            'AssumeRolePolicyDocument' => $AssumeRolePolicyDocument
        ));       
    }
    
    public function deleteRole($name){
        $this->IAMclient->deleteRole(array('RoleName' => $name));        
    }
}
?>
