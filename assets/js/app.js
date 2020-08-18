$(".root").on("click", function() {
  $("#radio_option").val($(this).val());
  var getAttrId = $(this).attr('id');
  if (getAttrId == 'private') {
      $("#public").parent().removeClass('active');
      $("#private").parent().addClass('active');
  } else {
    $("#private").parent().removeClass('active');
    $("#public").parent().addClass('active');
  }
});

$("#back_to_main").on("click", function(){
  $("#_create_folder_section").show();
  $("#_folder").hide();
  $(".error").text('').hide();
  $("label").removeClass('active');
});

$(".error").hide();

// function childFunction(selectedRoot, getAttrId) {
  $('#create_folder').click(function() {
      var selectedRoot = $("#radio_option").val();
      var newFolder = $(".folder_name").val();
      var new_folder_name = selectedRoot.trim() + newFolder.trim() + '/';

      if (newFolder.length == 0) { $("label.error").show().text("Please enter folder name."); return false; }
      else if (selectedRoot.length == 0) { $("label.error").show().text("Please select one root folder name."); return false; }
      else {
        createFolderFn(new_folder_name, true);
      }
  });
// } 

/**
 * create folder in aws s3 function
 */
function createFolderFn(new_folder_name, skip_aws = false) {
  $.ajax({
      url: "Magic.php",
      data: {
          newfoldername: new_folder_name.trim(),
          bucket: $("#bucketName").val(),
          skip: skip_aws,
      },
      success: function( result ) {
          $(".folder_name").val('');
          $("#dynamic_folder_name").text(JSON.parse(result).folderName);
          $("#dynamic_hidden_folder_name").val(JSON.parse(result).folderPath);
          $("#_create_folder_section").hide();
          $("#_folder").show();
      }
  });
}



var drag = document.getElementById("drag");
   
drag.ondragover = function(e) {e.preventDefault()}
drag.ondrop = function(e) {
  e.preventDefault();
    var length = e.dataTransfer.items.length;    
    for (var i = 0; i < length; i++) {
      // console.log(length, e.dataTransfer.items[i].webkitGetAsEntry());
      var entry = e.dataTransfer.items[i].webkitGetAsEntry();
      var file = e.dataTransfer.files[i];
      if (entry.isFile) {
        var file = e.dataTransfer.files[i];
        s3upload(file);
      } else if (entry.isDirectory) {
        traverseFileTree(entry);
      }
    }
}

function traverseFileTree(item, path) {
  path = path || "";
  if (item.isFile) {
    // Get file
    item.file(function(file) {
      file.fullPath = path;
      s3upload(file);
    });
  } else if (item.isDirectory) {
    s3CreateFolder(item);
    // Get folder contents
    var dirReader = item.createReader();
    dirReader.readEntries(function(entries) {
      for (var i=0; i < entries.length; i++) {
        // console.log(entries[i]);
        traverseFileTree(entries[i], path + item.name + "/");
      }
    });
  }
}