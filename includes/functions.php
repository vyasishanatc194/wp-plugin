<?php

require WPS3_PLUGIN_DIR.'/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

function records_list() {
    check_ajax_referer('wpawss3', 'security');
    $result['success'] = false;
    $result['data'] = [
        'message' => 'Network error.',
    ];

    $servername = get_option('wpawss3_host');
    $username = get_option('wpawss3_username');
    $password = get_option('wpawss3_password');
    $dbname = get_option('wpawss3_db_name');

    try {
        $conn = new PDO("mysql:host=$servername;dbname=processing", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		try {
            $s3 = new S3Client([
                'version' => get_option('wpawss3_aws_version'),
				'region'  => get_option('wpawss3_aws_region'),
				'credentials' => [
					'key'    => get_option('wpawss3_aws_key'),
					'secret' => get_option('wpawss3_aws_secret_key')
				]
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL; 
        }
        // set the PDO error mode to exception
        $sql_stmt = "SELECT 
                    PF.`folderName`, PFS.`label` as `status`, PU.`label` as idUser, BIN_TO_UUID(PF.`idFolder`) as idFolder, PFI.label as isPublic 
                    FROM prs_folders PF 
                    INNER JOIN prs_folders_ispublic PFI ON PFI.id = PF.isPublic
                    INNER JOIN prs_users PU ON PU.id = PF.idUser
                    INNER JOIN prs_folders_status PFS ON PFS.id = PF.status
					WHERE PF.status = 1
                    "; 
        $stmt = $conn->prepare($sql_stmt);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach($result as $key=>$value) {
            $isPublic = ($value['isPublic'] == 'Public') ? 'public/' : 'private/';
            $userlogin = get_current_user_id().'/';
            $folder = $value['folderName'].'/';
            $path = $isPublic.$userlogin.$folder;
            $objects = $s3->listObjects([
                'Bucket' => AWS_S3_BUCKET,
                'Prefix' => $path
            ]);
            $result[$key]['process'] = 0;
            if (isset($objects['Contents'])) {
                if (count($objects['Contents']) > 0) {
                    $result[$key]['process'] = 1;
                }
            }
        }
        wp_send_json_success( $result );
    } catch (\Exception $ex) {
        $result['success'] = false;
        $result['data'] = [
            'message' => $ex->getMessage(),
        ];
        wp_send_json_error( $result );
    }
    
    wp_send_json_error( $result );
    die();
}
add_action('wp_ajax_records_list', 'records_list');
add_action('wp_ajax_nopriv_records_list', 'records_list');

function magic_funcs() {
	check_ajax_referer('wpawss3', 'security');
	$insertdata = [];
    $result = [];
	// default response
	$result['success'] = false;
	$result['data'] = [
		'message' => 'Network error.',
	];

	if (!empty($_POST) && !empty($_POST['newfoldername'])) {    
		$newFolderName = $_POST['newfoldername'];
		$skip = $_POST['skip'];
		$bucket = $_POST['bucket'];
		$response = MagicWP::createFolderCB($newFolderName, $bucket, $skip);
		if ($response['success']) {
			$folderNameArr = explode("/", $newFolderName);
			$response['data'] = [
                'success' => true,
				'folderName' => $folderNameArr[count($folderNameArr)-2],
				'folderPath' => $newFolderName
			];
			wp_send_json_success($response['data']);
		} else {
			wp_send_json_error($response['msg']);
		}
		die;
	}

    if(!empty($_POST['wpawss3_getDBFolderList'])){
        $bucket = $_POST['wpawss3_bucket'];
         $desti = $_POST['wpawss3_desti'];
         $response = MagicWP::getAllFolderDB($bucket, $desti);
         if ($response['data']) {
             
             $response['success'] = true;
             wp_send_json_success($response);
         } else {
             $response['msg'] = 'Folder not found';
             wp_send_json_error($response['msg']);
         }
         die;
     }
     
     if(!empty($_POST['wpawss3_getfileList'])){
        $bucket = $_POST['wpawss3_bucket'];
         $desti = $_POST['wpawss3_desti'];
         $folderhas = $_POST['wpawss3_folderhas'];
         $response = MagicWP::getAllFileDB($bucket, $desti, $folderhas);
         if ($response['data']) {
             
             $response['success'] = true;
             wp_send_json_success($response);
         } else {
             $response['msg'] = 'File not found';
             wp_send_json_error($response['msg']);
         }
         die;
     }
	
	if (!empty($_POST['wpawss3_getFolderList'])) {
		$bucket = $_POST['wpawss3_bucket'];
		$desti = $_POST['wpawss3_desti'];
        $response = MagicWP::getAllFolderCB($bucket, $desti);
        // print_r($response); die;
		if ($response) {
            $response['success'] = true;
			wp_send_json_success($response);
		} else {
			wp_send_json_error($response['msg']);
		}
		die;
	}
	wp_send_json_error( $result );
}

add_action('wp_ajax_magic_funcs', 'magic_funcs');
add_action('wp_ajax_nopriv_magic_funcs', 'magic_funcs');


function create_folder() {
    check_ajax_referer('wpawss3', 'security');
    $RESULTS = [];
    $result['success'] = false;
	$result['data'] = [
		'message' => 'Network error.',
    ];
    try {
		$userId = 1;
		if( is_user_logged_in() ) {
			$userId = get_current_user_id();
        }
        
        $servername = get_option('wpawss3_host');
        $username = get_option('wpawss3_username');
        $password = get_option('wpawss3_password');
        $dbname = get_option('wpawss3_db_name');
		
		$MyConnection = new mysqli($servername, $username, $password, $dbname, 3306);
		
		$par_idFolder_HEX = $_POST['formData'][0]['value'];
		$par_folderName = $_POST['formData'][0]['value'];
		$par_ispublic = $_POST['formData'][1]['value'];
        $par_idUser = $userId;
        $par_status = 1;
        
		mysqli_multi_query($MyConnection, "CALL get_constants()");
		if(mysqli_multi_query($MyConnection, "CALL CRUD_prs_folders(@CRUD_CREATE, '$par_idFolder_HEX', '$par_folderName', @PAR_NONE, $par_ispublic, $par_idUser, $par_status, @PAR_NONE)")) {
			$result['success'] = true;
			$result['data'] = [
				'message' => 'Folder Created Successfully.',
			];
			wp_send_json_success( $result );
			mysqli_close($MyConnection);
		} else {
			$result['success'] = false;
			$result['data'] = [
                'server_error' => mysqli_error($MyConnection),
				'message' =>  'Internal Server Error.',
			];
			wp_send_json_error( $result );
			exit;
		}
        
    } catch (\Exception $ex) {
        $result['success'] = false;
        $result['data'] = [
            'message' => $ex->getMessage(),
        ];
        wp_send_json_error( $result );
    }

    wp_send_json_error( $result );
    die();
}
add_action('wp_ajax_create_folder', 'create_folder');
add_action('wp_ajax_nopriv_create_folder', 'create_folder');


function get_id_app_par() {
	check_ajax_referer('wpawss3', 'security');
	
    $response = [];
	// default response
	$response['success'] = false;
	$response['data'] = [
		'message' => 'Network error.',
	];
	
	$servername = get_option('wpawss3_host');
	$username = get_option('wpawss3_username');
	$password = get_option('wpawss3_password');
	$dbname = get_option('wpawss3_db_name');

	$MyConnection = new mysqli($servername, $username, $password, $dbname, 3306);
	
	if($_POST['par_idApp'] == 2){
		
		mysqli_multi_query($MyConnection, "CALL get_constants()");
		if(mysqli_multi_query($MyConnection, "CALL CRUD_prs_app_parameters_safd(@CRUD_READ , NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)")) {
		
			while (mysqli_more_results($MyConnection)) {

				   if ($results = mysqli_store_result($MyConnection)) {
					
						  while ($row = mysqli_fetch_assoc($results)) {
								$data[] = $row;
						  }
						  mysqli_free_result($results);
				   }
				   mysqli_next_result($MyConnection);
			}
			$response['data']['message'] = '';
			$response['idAppPar'] = $data[0]['idAppParSaf'];
			
			$response['success'] = true;
			wp_send_json_success($response);
			mysqli_close($MyConnection);
		}	
	}
	if($_POST['par_idApp'] == 3){
		mysqli_multi_query($MyConnection, "CALL get_constants()");
		if(mysqli_multi_query($MyConnection, "CALL CRUD_prs_app_parameters_COMP(@CRUD_READ , NULL, NULL, NULL, NULL, NULL, NULL)")) {
		
			while (mysqli_more_results($MyConnection)) {

				   if ($results = mysqli_store_result($MyConnection)) {
					
						  while ($row = mysqli_fetch_assoc($results)) {
								$data[] = $row;
						  }
						  mysqli_free_result($results);
				   }
				   mysqli_next_result($MyConnection);
			}
			$response['data']['message'] = '';
			$response['idAppPar'] = $data[0]['idAppParCmp'];
			$response['success'] = true;
			wp_send_json_success($response);
			mysqli_close($MyConnection);
		}	
	}
	wp_send_json_error( $result );
}

add_action('wp_ajax_get_id_app_par', 'get_id_app_par');
add_action('wp_ajax_nopriv_get_id_app_par', 'get_id_app_par');

function store_process() {
	check_ajax_referer('wpawss3', 'security');
	
	$result = [];
	// default response
	$result['success'] = false;
	$result['data'] = [
		'message' => 'Network error.',
	];
	
	$folderhas = $_POST['folderhas'];
	$process_radio = $_POST['process_radio'];
	$filehas = $_POST['filehas'];
	$par_idApp = $_POST['par_idApp'];
	$idAppPar = $_POST['idAppPar'];
	$comment = $_POST['comment'];
	
	$userId = 1;
	if( is_user_logged_in() ) {
		$userId = get_current_user_id();
	}

	$servername = get_option('wpawss3_host');
	$username = get_option('wpawss3_username');
	$password = get_option('wpawss3_password');
	$dbname = get_option('wpawss3_db_name');

	$MyConnection = new mysqli($servername, $username, $password, $dbname, 3306);
	mysqli_multi_query($MyConnection, "CALL get_constants()");
	
	if($process_radio == 'process_file'){
		
		if(mysqli_multi_query($MyConnection, "CALL prs_app_file('".$filehas."', $idAppPar, $par_idApp, $userId, 'test')")) {
			$result['success'] = true;
			$result['data'] = [
				'message' => 'File processed Successfully.',
			];
			wp_send_json_success( $result );
			mysqli_close($MyConnection);
		}
		
	}
	
	if($process_radio == 'process_folder'){
		if(mysqli_multi_query($MyConnection, "CALL prs_app_folder('".$folderhas."', $idAppPar, $par_idApp, $userId, 'test')")) {
			$result['success'] = true;
			$result['data'] = [
				'message' => 'Folder processed Successfully.',
			];
			wp_send_json_success( $result );
			mysqli_close($MyConnection);
		}
		
	}
	
	wp_send_json_error( $result );
}

add_action('wp_ajax_store_process', 'store_process');
add_action('wp_ajax_nopriv_store_process', 'store_process');
