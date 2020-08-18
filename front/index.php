<?php
function wpawss3_service_func() {
if (!is_admin()) :
	$userId = 1;
if( is_user_logged_in() ) {
	$userId = get_current_user_id();
?>
<script>
     if (!localStorage.getItem('folderName') || !localStorage.getItem('FirstFolderName')) { window.history.back(); }	
</script>
<?php
}
    $bucket = get_option('wpawss3_s3_bucket');
?>

<div id="myModal" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
    </div>
    <div class="modal-body">
      <p>Do you want to stop uploading process? count <span id="countOfFiles"></span></p>
    </div>
  </div>
</div>
<section class="form-folder-section">
    <div class="container">
        <div class="form-folder-div">
            <div class="form-new-folder-root" id="_folder">
                <main role="main" class="main-container w-100">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="heading-div">
                                <button type="button" class="btn btn-default" id="back_to_main">
                                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Back To Main Page
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="heading-div">
                                <h2>Upload file in S3 folder: <span id="dynamic_folder_name">DD</span></h2>
                                <input type="hidden" value="public/1/DD/" id="dynamic_hidden_folder_name" />
                            </div>
                        </div>
                    </div>
                    <div class="row" style="height: 408px;">
                        <div class="col-md-6 col-sm-12 mb-20">
                            <!-- Our markup, the important part here! -->
                            <div id="drag" class="dm-uploader p-5">
                                <h3 class="mb-5 mt-5 text-muted">Drag &amp; drop files here</h3>

                                <div class="btn btn-primary btn-block mb-5 hide-element">
                                    <span>Open the file Browser</span>
                                    <input type="file" id="fileUpload" title='Click to add Files' />
                                </div>
                            </div><!-- /uploader -->
                        </div>
                        <div class="col-md-6 col-sm-12 mb-20">
                            <div class="card h-100">
                                <div class="card-header">File List</div>
                                <ul class="list-unstyled p-2 d-flex flex-column col" id="files">
                                    <li class="text-muted text-center empty">No files uploaded.</li>
                                </ul>
                            </div>
                        </div>
						<div class="col-md-12 col-sm-12 mb-20">
                            <ul id="folder_list"></ul>
                        </div>
						
                    </div><!-- /file list -->
                </main> <!-- /container -->
            </div>
        </div>
    </div>
</section>

<!-- File item template -->
<script type="text/html" id="files-template">
    <li class="media">
        <div class="media-body mb-1">
        <p class="mb-2">
            <strong>%%filename%%</strong> - Status: <span class="text-muted">Waiting</span>
        </p>
        <div class="progress mb-2">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
            role="progressbar"
            style="width: 0%" 
            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
        </div>
        <hr class="mt-1 mb-1" />
        </div>
    </li>
</script>
<script>
	
	function closeModal() {
		document.getElementById("myModal").style.display = "none";
	}
	function openModal() {
		document.getElementById("myModal").style.display = "block";
		$(document).on("click", "span.close", function(){
			if (window.confirm("do you really want to stop uploading process?")) {
				document.getElementById("myModal").style.display = "none";
				location.reload();
			}
		});
		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == document.getElementById("myModal")) {
				if (window.confirm("do you really want to stop uploading process?")) {
					document.getElementById("myModal").style.display = "none";
					location.reload();
				}
			}
		}
	}
	
    var $destination = '';
    var albumBucketName = "<?php echo get_option('wpawss3_s3_bucket'); ?>";
    var bucketRegion = "<?php echo get_option('wpawss3_aws_region'); ?>";
    var IdentityPoolId = '<?php echo get_option('wpawss3_identity_pool_id'); ?>';

    AWS.config.update({
        region: bucketRegion,
        credentials: new AWS.CognitoIdentityCredentials({
            IdentityPoolId: IdentityPoolId
        })
    });
    var s3 = new AWS.S3({
        apiVersion: "2006-03-01",
        params: { Bucket: albumBucketName }
    });
    var baseURL = '';
	var listOfFiles = 0;
    function s3CreateFolder(files) {
        if (files) {
            console.log('folder', files);
            $destination = $("#dynamic_hidden_folder_name").val();
            if (baseURL != '') {
                $destination = baseURL;
            }
            //$("#dynamic_folder_name").text(files.name);
            $("#dynamic_hidden_folder_name").val($destination + files.fullPath.slice(1) + "/");
            files.id = Math.random().toString(36).substr(2);              
            var file = files;
            var fileName = file.name;
            var filePath = $destination.trim() + fileName.trim() + "/";
            var checkFile = checkFileNameExists(file, $destination);
            if (checkFile) {
                ui_multi_add_file(file.id, file);
                s3.putObject({
                    Key: filePath,
                    ACL: 'public-read'
                }, function (err, data) {
                    if(err) {
                        console.log(err);
                        return true;
                    }
                    createFolderFn(filePath, false);
                    ui_multi_update_file_status(file, 100);
                });
            }
        }
    }

    function s3upload(files) {
        if (files) {
            console.log('file', files);
            files.id = Math.random().toString(36).substr(2);
            $destination = $("#dynamic_hidden_folder_name").val();
            var file = files;
            var fileName = file.name;
            var filePath = $destination.trim() + fileName.trim();
            var checkFile = checkFileNameExists(file, $destination);
            
            if (file.fullPath && baseURL) {
                filePath = baseURL + file.fullPath + fileName.trim();
            }
            if (checkFile) {
                ui_multi_add_file(file.id, file);
                s3.upload({
                    Key: filePath,
                    Body: file,
                    ContentType: file.type,
                    ACL: 'public-read'
                }, function (err, data) {
                    if(err) {
                        console.log(err);
                        return true;
                    }
                    getFolderList();
                }).on('httpUploadProgress', function (progress) {
                    var uploaded = parseInt((progress.loaded * 100) / progress.total);
                    ui_multi_update_file_status(file, uploaded);
                });
            }
        }
    }

    // Creates a new file and add it to our list
    function ui_multi_add_file(id, file) {
        var template = $('#files-template').text();
        template = template.replace('%%filename%%', file.name);

        template = $(template);
        template.prop('id', 'uploaderFile' + id);
        template.data('file-id', id);

        $('#files').find('li.empty').fadeOut(); // remove the 'no files yet'
        $('#files').prepend(template);
        if (file.size < 1) {
			ui_multi_update_file_progress(file.id, 0, 'danger', false);
			$('#uploaderFile' + file.id).find('span').html('0 KB file is not allow to upload.').prop('class', 'status text-' + 'danger');
		} else {
			listOfFiles = parseInt(listOfFiles) + 1;
			if (parseInt(listOfFiles) > 0) {
				openModal();
				$("#countOfFiles").text(listOfFiles);
			}
		}
    }

    // Changes the status messages on our list
    function ui_multi_update_file_status(file, uploaded) {
        var message = 'uploading';
        var status = 'Uploading...';
        var id = file.id;
        if (uploaded > 0 && uploaded < 100) {
			ui_multi_update_file_progress(id, uploaded, '', true);
			$('#uploaderFile' + id).find('span').html(message).prop('class', 'status text-' + status);
		} else if (uploaded == 100) {
			ui_multi_update_file_progress(id, 100, 'success', true);
			$('#uploaderFile' + id).find('span').html('success').prop('class', 'status text- Upload Complete');
			listOfFiles = listOfFiles - 1;
			if (listOfFiles == 0) {
				console.log('listOfFiles', listOfFiles);
				closeModal();
			}
		}
    }

    // Updates a file progress, depending on the parameters it may animate it or change the color.
    function ui_multi_update_file_progress(id, percent, color, active) {
        color = (typeof color === 'undefined' ? false : color);
        active = (typeof active === 'undefined' ? true : active);

        var bar = $('#uploaderFile' + id).find('div.progress-bar');

        bar.width(percent + '%').attr('aria-valuenow', percent);
        bar.toggleClass('progress-bar-striped progress-bar-animated', active);

        if (percent === 0) {
            bar.html('');
        } else {
            bar.html(percent + '%');
        }
        if (color == 'success') {
            bar.removeClass('progress-bar-striped');
            bar.addClass('bg-' + color);
        }
    }        

    function checkFileNameExists(file, $destination) {
        if (file) {
            return true;
        }
        return false;
    }
    
    getFolderList(); 

    /*
     * get folder list from aws s3 
     */
    function getFolderList() {
        var folderPath = localStorage.getItem('FirstFolderName')+'<?php echo '/'.$userId.'/' ?>'+localStorage.getItem('folderName')+'/';
        jQuery("#dynamic_hidden_folder_name").val(folderPath);
        baseURL = folderPath;
        jQuery("#dynamic_folder_name").text(localStorage.getItem('folderName'));
        var destinationDir = folderPath;

        $.ajax({
            url: pw1_script_vars.ajaxurl,
            dataType: "json",
            type: "POST",
            error: function(e){},
            data: {
                action: 'magic_funcs',
                security: pw1_script_vars.security,
                wpawss3_desti: destinationDir.trim(),
                wpawss3_getFolderList: true,
                wpawss3_bucket: '<?php echo $bucket; ?>'
            },
            beforeSend: function() {},
            success: function( result, xhr ) {
				if (result.data.success) {
					$("#folder_list").html('');
					$("#folder_list").append('<h3>Existing Folders/Files in S3 Directory</h3>');
					$.each(result.data, function(i, item) {
						var $explodedVal = item.split("/");
						var $lastVal = $explodedVal[$explodedVal.length - 1].trim();
						var arr = [];
						var array3 = [];
						var array4 = [];
						var $HTML = '';
						if (result.data.success) {
							$("#folder_list").html('');
							$("#folder_list").append('<h3>Existing Folders/Files in S3 Directory</h3>');
							$.each(result.data, function(i, item) {
								var itemArray = item.split("/");
								var newArr = [];
								for(var i = 3; i <= itemArray.length; i++) {
									var forthUl = false;
									if (itemArray[i] && itemArray[i].trim() != "") {
										newArr.push(itemArray[i]);
										if(i == 3 && array3.indexOf(itemArray[i]) != 0) {
											if (forthUl) {
												$HTML += '</ul>';
												$HTML += '</li>';
											}
											$HTML += '<li><span class="caret">'+ itemArray[i] +'</span>';
											array3.push(itemArray[i]);
										}
										if(i == 4 && array4.indexOf(itemArray[i]) != 0) {
											forthUl = false;
											$HTML += '<ul class="nested">';
											$HTML += '<li>'+ itemArray[i] +'</li>';
											array4.push(itemArray[i]);
										}
									}
								}
								arr.push(newArr);
								var $explodedVal = item.split("/");
								var $lastVal = $explodedVal[$explodedVal.length - 1].trim();
								var v = ($lastVal != '') ? '<li class="file badge badge-success"><i class="fa fa-file" aria-hidden="true"></i> '+$lastVal+'</li>' : '<li class="folder badge badge-primary"><i class="fa fa-folder" aria-hidden="true"></i> '+$explodedVal[$explodedVal.length - 2].trim()+'</li>';
								$("#folder_list").append(v);
							});
						}
					});
				}
			},
            complate: function() {}
        });
        
        jQuery("#back_to_main").on("click", function(){
            window.history.back();
        });
    }
</script>
<?php 
    endif;
} 
add_shortcode('wpawss3service', 'wpawss3_service_func');

