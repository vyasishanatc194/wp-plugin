<?php
require_once WPS3_PLUGIN_DIR . '/classes/AwsS3WP.php';

class magicWP {

    /**
     * @param folder_name
     * @param bucket
     */
    static function createFolderCB($folder_name, $bucket, $skip) {
        
        if (!$bucket) {
            return 'Please enter bucket name';
        }
        if (!$folder_name) {
            return 'Please enter folder name';
        }
        $status = '';
        if (!$skip) {
            $status = 200;
        } else { 
            $res = AwsS3WP::createFolder($folder_name, $bucket);
            $status = $res['statusCode'];
        }
    }

    /**
     * @param bucket
     * @param userId
     */
    static function getAllFolderCB($bucket, $prefix) {
        $res = AwsS3WP::getListOfBuckets($bucket, $prefix);
        return $res;
    }
    
    static function getAllFolderDB($bucket, $prefix) {
		
		$userId = 1;
		if( is_user_logged_in() ) {
			$userId = get_current_user_id();
        }
		
		$servername = get_option('wpawss3_host');
        $username = get_option('wpawss3_username');
        $password = get_option('wpawss3_password');
        $dbname = get_option('wpawss3_db_name');
		
		$MyConnection = new mysqli($servername, $username, $password, $dbname, 3306);
		
		$par_idUser = $userId;
        $data = [];
		mysqli_multi_query($MyConnection, "CALL get_constants()");
		
	    if(mysqli_multi_query($MyConnection, "CALL CRUD_prs_folders(@CRUD_READ, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE, $par_idUser, @FOLDER_STATUS_COMPLETED  , @PAR_NONE)")) {
			
		//if(mysqli_multi_query($MyConnection, "CALL CRUD_prs_folders(@CRUD_READ, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE, $par_idUser, @FOLDER_STATUS_FILE_UPLOAD  , @PAR_NONE)")) {
		
			while (mysqli_more_results($MyConnection)) {

				   if ($results = mysqli_store_result($MyConnection)) {

						  while ($row = mysqli_fetch_assoc($results)) {
								$data[] = $row;
						  }
						  mysqli_free_result($results);
				   }
				   mysqli_next_result($MyConnection);
			}
			
			$result['data'] = $data;
		
			//wp_send_json_success( $result );
			mysqli_close($MyConnection);
		}		
	 return $result;
    }
	
	static function getAllFileDB($bucket, $prefix, $folderhas) {
		
		$userId = 1;
		if( is_user_logged_in() ) {
			$userId = get_current_user_id();
        }
		
		//$folderhas = '9a2eba77-d70a-11ea-a3ac-0ef030544d11';
		
		$servername = get_option('wpawss3_host');
        $username = get_option('wpawss3_username');
        $password = get_option('wpawss3_password');
        $dbname = get_option('wpawss3_db_name');
		
		$MyConnection = new mysqli($servername, $username, $password, $dbname, 3306);
		
		$par_idUser = $userId;
        $data = [];
		mysqli_multi_query($MyConnection, "CALL get_constants()");
		if(mysqli_multi_query($MyConnection, "CALL CRUD_prs_files(@CRUD_READ, '".$folderhas."', @PAR_NONE, @FILE_TYPE_CONVERTED, @PAR_NONE, @FILE_STATUS_COMPLETED, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE, @PAR_NONE)")) {
		
			while (mysqli_more_results($MyConnection)) {

				   if ($results = mysqli_store_result($MyConnection)) {
					
						  while ($row = mysqli_fetch_assoc($results)) {
								$data[] = $row;
						  }
						  mysqli_free_result($results);
				   }
				   mysqli_next_result($MyConnection);
			}
			
			$result['data'] = $data;
			
			//wp_send_json_success( $result );
			mysqli_close($MyConnection);
		}		
	 return $result;
    }

    /**
     * function to make an array from array
     */
    static function makeAnArr($str) {
        $resArr = [];
        $resArr1 = [
            'private' => [],
            'public' => [],
        ];
        foreach($str as $key=>$val) {
            if (!strpos($val, ".")) {
                $explode = explode('/', $val);
                if ($explode[0] == "private") {
                    if ($key >= 2 && isset($explode[2]) && trim($explode[2]) != "") {
                        $resArr1['private'][$explode[$key-1]][] =  $val;
                    } else {
                        $resArr1['private'][] = $val;
                    }                    
                } else {
                    if ($key >= 2 && isset($explode[2]) && trim($explode[2]) != "") {
                        $resArr1['public'][$explode[$key-1]][] =  $val;
                    } else {
                        $resArr1['public'][] = $val;
                    }
                }
            }         
        }
        return $resArr1;
    }
}

if (!empty($_REQUEST) && !empty($_REQUEST['newfoldername'])) {
    
    $newFolderName = $_REQUEST['newfoldername'];
    $skip = $_REQUEST['skip'];
    $bucket = $_REQUEST['bucket'];
    $response = Magic::createFolderCB($newFolderName, $bucket, $skip);
    if ($response['success']) {
        $folderNameArr = explode("/", $newFolderName);
        $response['data'] = [
            'folderName' => $folderNameArr[count($folderNameArr)-2],
            'folderPath' => $newFolderName
        ];
        $html = '';
        echo json_encode($response['data']); die;
    } else {
        echo $response['msg'];
    }
    die;
}

if (!empty($_REQUEST['getFolderList'])) {
    $bucket = $_REQUEST['bucket'];
    $desti = $_REQUEST['desti'];
    $newResposne = Magic::getAllFolderCB($bucket, $desti);
    $htmlArr = [];
    if ($newResposne) {
        foreach($newResposne as $key=>$val) {
            $explodedVal = explode("/", $val);
            $lastVal = trim($explodedVal[count($explodedVal)-1]);
            $v = ($lastVal != '') ? $lastVal : trim($explodedVal[count($explodedVal)-2]);
            $htmlArr[] = $v;
        }
        echo json_encode($htmlArr); die;
    } else {
        echo $response['msg'];
    }
}
