<?php
require WPS3_PLUGIN_DIR.'/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class AwsS3WP {
    static function connect()
    {
        // Instantiate the client.
        return new S3Client([
            'version' => get_option('wpawss3_aws_version'),
            'region'  => get_option('wpawss3_aws_region'),
            'credentials' => [
                'key'    => get_option('wpawss3_aws_key'),
                'secret' => get_option('wpawss3_aws_secret_key')
            ]
        ]);
    }

    /**
     * @param bucket
     */
    static function getListOfBuckets($bucket = null, $prefix = '')
    {
        // Use the plain API (returns ONLY up to 1000 of your objects).
        if (!$bucket) {
            return 'Please enter bucket name';
        }
        $s3 = AwsS3WP::connect();
        $res = [];
        try {            
            $objects = $s3->listObjects([
                'Bucket' => $bucket,
                'Prefix' => $prefix
            ]);
            if (isset($objects['Contents'])) {
                foreach ($objects['Contents']  as $object) {
                    $res[] = $object['Key'] . PHP_EOL;
                }
            }

            if (empty($res)) {
                AwsS3WP::createFolder($prefix, $bucket, true);
            }

            return $res;
        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @param ref
     * @param dirs
     */
    static function nest_dir( $ref, $dirs ) {
        $dirs = array_filter( $dirs );
        foreach( $dirs as $index => $dir ) {
            $parent = @$dirs[ $index - 1 ];
    
            if( $parent && isset( $ref[ $parent ] ) ) {
                $ref[ $parent ][ $dir ] = nest_dir( [], array_slice( $dirs, $index + 1 ) );
                continue;
            }
            if( !$parent || ( $parent && array_search( $parent, $dirs ) === 0 ) )
                $ref[ $dir ] = [];
        }
        return $ref;
    }

    /**
     * @param file_name
     * @param folder_name
     * @param bucket
     */
    static function uploadFile($file_name = null, $folder_name = null, $bucket = null)
    {
        // Use the plain API (returns ONLY up to 1000 of your objects).
        if (!$file_name) {
            return 'Please enter file name with path';
        }
        if (!$bucket) {
            return 'Please enter bucket name';
        }
        if (!$folder_name) {
            return 'Please enter folder name';
        }
        $s3 = AwsS3WP::connect();
        
        try {        
            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $folder_name.$file_name['name'],
                'Body' => fopen($file_name['tmp_name'], 'r+')
            ]);

            if ($result) {
                $res = $result['@metadata'];
                $res = $res['statusCode'];
                if ($res == 200) {
                    return [
                        'statusCode' => $res,
                        'msg'=>'File Created Successfully',
                        'ObjectURL' => $result['ObjectURL']
                    ];
                }
                return 'Error while uploading file';
            }
        
            // Wait for the file to be uploaded and accessible :
            $s3->waitUntil('ObjectExists', array(
              'Bucket' => 'testdomain',
              'Key'    => 'pocket/'.$file_name
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        } 
    }

    /**
     * @param folder_name
     * @param bucket
     */
    static function createFolder($folder_name = null, $bucket = null, $flag = false)
    {
        // Use the plain API (returns ONLY up to 1000 of your objects).
        if (!$bucket) {
            return 'Please enter bucket name';
        }
        if (!$folder_name) {
            return 'Please enter folder name';
        }
        $s3 = AwsS3WP::connect();
        try {
            if ($flag) { }
        
            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $folder_name,
                'Body'   => "",
                'ACL'    => 'public-read' // Defines Permission to that folder
            ]);

            if ($result) {
                $res = $result['@metadata'];
                $res = $res['statusCode'];
                if ($res == 200) {
                    if ($flag) {
                        AwsS3WP::getListOfBuckets($bucket, $folder_name);
                    }
                    return [
                        'statusCode' => $res,
                        'msg'=>'Folder Created Successfully',
                        'ObjectURL' => $result['ObjectURL']
                    ];
                }
                return 'Error while creating folder';
            }
        
            // Wait for the file to be uploaded and accessible :
            $s3->waitUntil('ObjectExists', array(
              'Bucket' => $bucket,
              'Key'    => $folder_name.$file_name,
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @param file_name
     * @param folder_name
     * @param bucket
     */
    static function deleteFile($file_name = null, $folder_name = null, $bucket = null)
    {
        // Use the plain API (returns ONLY up to 1000 of your objects).
        if (!$file_name) {
            return 'Please enter file name with path';
        }
        if (!$bucket) {
            return 'Please enter bucket name';
        }
        if (!$folder_name) {
            return 'Please enter folder name';
        }
        $s3 = AwsS3WP::connect();
        try {
            // $file_name = 'testFile.txt';

            $result = $s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $folder_name.$file_name,
            ]);

            if ($result) {
                $res = $result['@metadata'];
                $res = $res['statusCode'];
                if ($res == 204) {
                    return 'File '.$file_name.' deleted successfully';
                }
                return 'Error while deleting file';
            }

            // Wait for the file to be uploaded and accessible :
            $s3->waitUntil('ObjectExists', array(
            'Bucket' => $bucket,
            'Key'    => $folder_name.$file_name,
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}