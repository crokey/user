

<style>
  .modal-lg {
    max-width: 1200px;
    max-height: 900px;
  }
  .modal-xllg {
    max-width: 1200px;
    max-height: 900px;
    height: 800px;
    width: 1200px;
  }
  
  .modal-md {
    max-width: 60%;
  }
  
  .modal-sm {
    max-width: 30%;
  }
  .modal-body {
    padding: 1.5rem 1rem;
    height: 800px;

}
  .modal-body-large {
    padding: 1.5rem 1rem;
    height: 800px;
    width: 1200px;
}
  .modal-body-audio {
    padding: 1.5rem 1rem;
   
  }
  
  .zoom-controls button {
    padding: 5px;
    margin: 5px;
    font-size: 16px;
}
</style>


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 defined('BASEPATH') or exit('No direct script access allowed');
include 'aws_s3_config.php'; // Include AWS S3 config

$bucketName = 'mainadminbucket';
$mainFolder = 'merchants/';

// Check if the client object is set and get the client ID
$clientId = isset($client) ? $client->userid : 0; // Replace 0 with a default ID or logic as required

// Define the subfolder name
$subFolderName = 'view/';

// If the folder parameter is set in the URL, decode it
$folderQuery = isset($_GET['folder']) ? urldecode($_GET['folder']) : $mainFolder . (isset($client->company) ? $client->company . '/' . $subFolderName : 'defaultFolderName/'.$subFolderName);

// Ensure the folderQuery starts from the 'view/' subfolder
$companyFolder = $mainFolder . (isset($client->company) ? $client->company . '/' : 'defaultFolderName/'.$subFolderName);
$companyViewFolder = $companyFolder . $subFolderName; // Company specific 'view/' folder

// Reset folderQuery if it doesn't start with the companyViewFolder path
if (strpos($folderQuery, $companyViewFolder) !== 0) {
    $folderQuery = $companyViewFolder;
}

$currentUrl = "https://user.bearlapay.com/IMS/admin/clients/client/{$clientId}?group=attachments";

// Normalize the folder query to ensure consistent comparison
$normalizedFolderQuery = rtrim($folderQuery, '/').'/';

// Function to get parent folder path
function getParentFolder($folder, $mainFolder) {
    $folder = rtrim($folder, '/');
    if (strrpos($folder, '/') !== false && $folder != $mainFolder) {
        return substr($folder, 0, strrpos($folder, '/') + 1);
    }
    return '';
}

// Define the company's main folder path for comparison
$companyMainFolder = $mainFolder . (isset($client->company) ? $client->company . '/' : 'defaultFolderName/'.$subFolderName);

// Use normalized paths for comparison to decide if back button should be shown
$parentFolder = getParentFolder($normalizedFolderQuery, $mainFolder);
$parentUrl = $currentUrl . '&folder=' . urlencode($parentFolder);

// Current full folder path
$currentFolderPath = $folderQuery;

// Extract the current folder name from the folder path
$currentFolder = basename(rtrim($currentFolderPath, '/'));


// Only show the back button if not in the company's main folder
$showBackButton = $normalizedFolderQuery != $companyMainFolder;


$_SESSION['currentFolderPath'] = $currentFolderPath; // Set this to your dynamic path




?>

<input type="hidden" name="currentFolderPath" value="<?php echo htmlspecialchars($currentFolderPath); ?>">


<!-- Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" role="dialog" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-size modal-lg custom-modal-height"> <!-- Adding custom-modal-height class for increased height -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Content will be loaded here based on the file type -->
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="filePreviewModalLarge" tabindex="-1" role="dialog" aria-labelledby="filePreviewModalLabelLarge" aria-hidden="true">
  <div class="modal-dialog custom-modal-size modal-xllg custom-modal-height"> <!-- Use modal-xllg and custom-modal-height classes for xlsx files -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filePreviewModalLabelLarge">File Preview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body-large"> <!-- Use modal-body-large class for xlsx files -->
        <!-- Content will be loaded here based on the file type -->
      </div>
    </div>
  </div>
</div>

<!-- Modal for Video Files -->
<div class="modal fade" id="videoPreviewModal" tabindex="-1" role="dialog" aria-labelledby="videoPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-size modal-lg custom-modal-height"> <!-- Adding custom-modal-height class for increased height -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="videoPreviewModalLabel">Video Preview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Video content will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Modal for Audio Files -->
<div class="modal fade" id="audioPreviewModal" tabindex="-1" role="dialog" aria-labelledby="audioPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-size modal-md custom-modal-height"> <!-- Adding custom-modal-height class for increased height -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="audioPreviewModalLabel">Audio Preview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body-audio">
        <!-- Video content will be loaded here -->
      </div>
    </div>
  </div>
</div>





<!--<h4 class="customer-profile-group-heading"><?php //echo _l('client_attachments'); ?></h4>
<p class="text-info"><?php //echo _l('client_files_info_message'); ?></p>-->

<?php if (isset($client)) { ?>


<?php //echo form_open_multipart('admin/banks/import', ['class' => 'dropzone', 'id' => 'client-attachments-upload']); ?>

    
    <!--<input type="hidden" name="currentFolderPath" value="<?php echo htmlspecialchars($currentFolderPath); ?>">
    <input type="file" name="files[]" multiple>-->
<?php //echo form_close(); ?>




    
    <div class="tw-flex tw-justify-end tw-items-center tw-space-x-2 mtop15">
        <!-- Google Picker and Dropbox Chooser -->
    </div>
    <div class="attachments">
    <div class="mtop25">
        

        <table class="table dt-table" data-order-col="2" data-order-type="desc">
            <thead>
                    <tr>
                        <th width="30%"><?php echo _l('customer_attachments_file'); ?></th>
                        <th><?php echo _l('Last Modified'); ?></th>
                        <th><?php echo _l('file_date_uploaded'); ?></th>
                        <th><?php echo _l('Size'); ?></th>
                        <th><?php echo _l('options'); ?></th>
                        <th><?php echo _l('Type'); ?></th>

                    </tr>
                </thead>
          

                <tbody>
                  
                  <?php
// Define the company's main folder path
$companyMainFolder = $mainFolder . (isset($client->company) ? $client->company . '/' : 'defaultFolderName/') . $subFolderName;

// Check if the current folder is the company's main 'view/' folder
$isInCompanyMainViewFolder = ($folderQuery === $companyMainFolder);

// Generate the URL for the parent folder if not in the company's main 'view/' folder
if (!$isInCompanyMainViewFolder) {
    $parentFolder = getParentFolder($folderQuery, $mainFolder);
    // Ensure parentFolder is within the companyMainFolder
    if (strpos($parentFolder, $companyMainFolder) !== 0) {
        $parentFolder = $companyMainFolder; // Reset to companyMainFolder if outside bounds
    }
    $parentUrl = $currentUrl . '&folder=' . urlencode($parentFolder);
} else {
    $parentFolder = '';
    $parentUrl = '#'; // or set to a default URL if needed
}

// Check if the current folder is not the company's main folder
if ($folderQuery != $companyMainFolder) { ?>
    <!-- Back Button -->
    <div>
        <a href="<?php echo $parentUrl; ?>">
    <i class="fa-solid fa-backward-step" style="font-size: 24px;"></i>
          
</a>

    </div>
<?php } ?>
                    <?php
                    try {
                        $objects = $s3Client->listObjects([
                            'Bucket' => $bucketName,
                            'Prefix' => $folderQuery,
                            'Delimiter' => '/'
                        ]);

                        if (isset($objects['CommonPrefixes'])) {
                            foreach ($objects['CommonPrefixes'] as $commonPrefix) {
                                $folderName = trim(str_replace($folderQuery, '', $commonPrefix['Prefix']), '/');
                                $folderUrl = $currentUrl . '&folder=' . urlencode($commonPrefix['Prefix']);
                                echo "<tr>
        <td><i class='fa-solid fa-folder'></i> <a href='{$folderUrl}'>" . htmlspecialchars($folderName) . "</a></td>
        <td></td> <!-- Empty cell for 'Last Modified' -->
        <td></td> <!-- Empty cell for 'File Date Uploaded' -->
        <td></td> <!-- Empty cell for 'Size' -->
        <td></td> <!-- Empty cell for 'Options' -->
        <td></td> <!-- Empty cell for 'Type' -->
      </tr>";

                            }
                        }
                      
                      


                      // Function to format file size
function human_filesize($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $size = max($size, 0);
    $i = floor(log($size, 1024));
    $size = round($size / (1024 ** $i), $precision);

    return $size . ' ' . $units[$i];
}
                      
                      

if (isset($objects['Contents']) && !empty($objects['Contents'])) {
    // Existing foreach loop to list files
                        foreach ($objects['Contents'] as $object) {
    if (!str_ends_with($object['Key'], '/')) { // Make sure it's a file, not a folder
        $fileKey = $object['Key'];
      
      // Extract the file type (extension)
        $fileType = strtolower(pathinfo($fileKey, PATHINFO_EXTENSION));

        // Determine the preview type based on the file extension
        $isPdf = $fileType === 'pdf';
        $isImage = in_array($fileType, ['jpg', 'png', 'jpeg', 'gif']);
        $isVideo = in_array($fileType, ['mp4', 'mov']);
        $isAudio = $fileType === 'mp3';
        
        // Generate a pre-signed URL for downloading the file
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key'    => $fileKey
        ]);
      
     

        $request = $s3Client->createPresignedRequest($cmd, '+20 minutes'); // URL expires in 20 minutes
        $presignedUrl = (string) $request->getUri();
        $lastModified = date('Y-m-d H:i:s', strtotime($object['LastModified']));
        $dateUploaded = date('Y-m-d H:i:s', strtotime($object['LastModified']));
        $size = human_filesize($object['Size']); // Define a function to format the file size as needed
      
      // Dynamically set the preview link based on file type
$uniqueIdentifier = uniqid(); // Generate a unique identifier for each image
$previewLink = $isPdf ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'pdf')\"" : 
              ($isImage ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'image')\"" : 
              ($fileType === 'xlsx' ? "onclick=\"openFileInModalLarge('".htmlspecialchars($presignedUrl)."', 'xlsx')\"" : 
              ($fileType === 'docx' ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'docx')\"" : 
              ($fileType === 'ppt' ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'ppt')\"" : 
              ($fileType === 'txt' ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'txt')\"" : 
              ($fileType === 'csv' ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'csv')\"" :
               ($isVideo ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'mp4')\"" :
                ($isAudio ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'mp3')\"" :
              ($fileType === 'rtf' ? "onclick=\"openFileInModal('".htmlspecialchars($presignedUrl)."', 'rtf')\"" : "")))))))));






      // Add a file icon and display the information
        echo "<tr>
    <td><i class='fa-solid fa-file'></i> &nbsp;<a href='#'>" . htmlspecialchars(basename($fileKey)) . "</a <i class='fa-solid fa-folder'></i></td>
    <td>".htmlspecialchars($lastModified)."</td>
    <td>".htmlspecialchars($dateUploaded)."</td>
    <td>".htmlspecialchars($size)."</td>
    <td> <a href='#' ".$previewLink.">Preview</a></td>
    <td>".strtoupper(htmlspecialchars($fileType))."</td> <!-- Display the file type here -->
   
</tr>";

        



    	}
	}
}

                    } catch (AwsException $e) {
                        echo "Error fetching files from S3: " . $e->getMessage();
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>
<!-- Include js-image-zoom from a CDN just once -->



<!-- Add this script tag to include Viewer.js from a CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.8.0/viewer.min.js"></script>

<!-- Also, include the CSS for Viewer.js for styling -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.8.0/viewer.min.css">

<!-- Rest of your HTML code -->



<script>
  

  





function openFileInModal(fileUrl, fileType) {
    console.log('openFileInModal called with fileType:', fileType); // Check if the function is called

    if (fileType === 'pdf') {
        // Use the modal for PDF files
        var contentHtml = `<iframe src="${fileUrl}" frameborder="0" style="width:100%;min-height:100%"></iframe>`;
        $('#filePreviewModal .modal-body').html(contentHtml);
        $('#filePreviewModal').modal('show');
    } else if (fileType === 'image') {
        // Open the image in Lightbox2 for image files
        $('#filePreviewModal .modal-body').html(`<img src="${fileUrl}" style="max-width: 100%; max-height: 100%;">`);
        $('#filePreviewModal').modal('show');
    } else if (fileType === 'docx' || fileType === 'ppt' || fileType === 'csv') {
        // Use an iframe to display XLSX, DOCX, PPT, and CSV files using Office Online Viewer
        var officeOnlineViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
        var contentHtml = `<iframe src="${officeOnlineViewerUrl}" width="100%" height="100%" frameborder="0">This is an embedded <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by <a target="_blank" href="http://office.com/webapps">Office Online</a>.</iframe>`;
        $('#filePreviewModal .modal-body').html(contentHtml);
        $('#filePreviewModal').modal('show');
    } else if (fileType === 'txt') {
        // Use an iframe to display the text content of the .txt file
        var contentHtml = `<iframe src="${fileUrl}" frameborder="0" style="width:100%;min-height:100%;"></iframe>`;
        $('#filePreviewModal .modal-body').html(contentHtml);
        $('#filePreviewModal').modal('show');
    } else if ( fileType === 'mp4' || fileType === 'mov') {
        // Open the video in the new modal for video files
        var videoHtml = `<video id="videoPreview" width="100%" height="100%" controls>
                            <source src="${fileUrl}" type="video/${fileType}">
                            Your browser does not support the video tag.
                         </video>`;
        $('#videoPreviewModal .modal-body').html(videoHtml);
        $('#videoPreviewModal').modal('show');

        // Play the video when the modal is shown
        $('#videoPreviewModal').on('shown.bs.modal', function () {
            var videoElement = document.getElementById('videoPreview');
            if (videoElement) {
                videoElement.play();
            }
        });
      
      // Attach an event listener to the modal close button
$('#videoPreviewModal').on('hidden.bs.modal', function () {
  stopVideoPlayback();
});
    } else if (fileType === 'mp3') {
        // Open the video in the new modal for video files
        var audioHtml = `<audio id="audioPreview" controls>
                            <source src="${fileUrl}" type="audio/${fileType}">
                            Your browser does not support the audio tag.
                         </audio>`;
        $('#audioPreviewModal .modal-body-audio').html(audioHtml);
        $('#audioPreviewModal').modal('show');
      
      // Attach an event listener to the modal close button
$('#audioPreviewModal').on('hidden.bs.modal', function () {
  stopAudioPlayback();
});
    } else {
        console.log('Unsupported file type for preview.'); // Handle unsupported file types
    }
}


  
  function openFileInModalLarge(fileUrl, fileType) {
    console.log('openFileInModal called with fileType:', fileType); // Check if the function is called
    
    if (fileType === 'xlsx') {
        // Use an iframe to display XLSX, DOCX, PPT, and CSV files using Office Online Viewer
        var officeOnlineViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
        var contentHtml = `<iframe src="${officeOnlineViewerUrl}" width="100%" height="100%" frameborder="0">This is an embedded <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by <a target="_blank" href="http://office.com/webapps">Office Online</a>.</iframe>`;
        $('#filePreviewModalLarge .modal-body-large').html(contentHtml);
        $('#filePreviewModalLarge').modal('show');
    }  else {
        console.log('Unsupported file type for preview.'); // Handle unsupported file types
    }
}
  
  // Function to stop video playback
function stopVideoPlayback() {
  var videoElement = document.getElementById('videoPreview');
  if (videoElement) {
    videoElement.pause();
    
  }
}

   // Function to stop video playback
function stopAudioPlayback() {
  var audioElement = document.getElementById('audioPreview');
  if (audioElement) {
    audioElement.pause();
    
  }
}



</script>




<?php $this->load->view('admin/clients/modals/send_file_modal'); ?>