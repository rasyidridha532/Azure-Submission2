<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=uploadgambar;AccountKey=BJgscBqeI4NYw+mrM/cKugWHZXE6qqQ/MUaRZcBVe+K3XmcLv3Lw8y1qtSAi8aaefSChCqawdH3qePTsSPCEhw==";
$containerName = "blobrasyidsubmission2";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

if (isset($_POST['submit'])) {
    $fileToUpload = strtolower($_FILES["fileToUpload"]["name"]);
    $content = fopen($_FILES["fileToUpload"]["tmp_name"], "r");
    // echo fread($content, filesize($fileToUpload));
    $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
    header("Location: analyze.php");
}
$listBlobsOptions = new ListBlobsOptions();
$listBlobsOptions->setPrefix("");
$result = $blobClient->listBlobs($containerName, $listBlobsOptions);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Analyze Sample</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
</head>
<body>
 
<script type="text/javascript">
    function processImage() {
        // **********************************************
        // *** Update or verify the following values. ***
        // **********************************************
 
        // Replace <Subscription Key> with your valid subscription key.
        var subscriptionKey = "f374f4ecabd843faa7b94371f98bf90b";
 
        // You must use the same Azure region in your REST API method as you used to
        // get your subscription keys. For example, if you got your subscription keys
        // from the West US region, replace "westcentralus" in the URL
        // below with "westus".
        //
        // Free trial subscription keys are generated in the "westus" region.
        // If you use a free trial subscription key, you shouldn't need to change
        // this region.
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        var sourceImageUrl = document.getElementById("inputImage").value;
        document.querySelector("#sourceImage").src = sourceImageUrl;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").val(JSON.stringify(data, null, 2));
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };
</script>
 
<h1>Analyze image:</h1>
Enter the URL to an image, then click the <strong>Analyze image</strong> button.
<br><br>
Image to analyze:
<input type="text" name="inputImage" id="inputImage"
    value="https://www.thesun.co.uk/wp-content/uploads/2017/08/kurt-cobain.jpg" />
<button onclick="processImage()">Analyze image</button>
<br><br>
<div id="wrapper" style="width:1020px; display:table;">
    <div id="jsonOutput" style="width:600px; display:table-cell;">
        Response:
        <br><br>
        <textarea id="responseTextArea" class="UIInput"
                  style="width:580px; height:400px;"></textarea>
    </div>
    <div id="imageDiv" style="width:420px; display:table-cell;">
        Source image:
        <br><br>
        <img id="sourceImage" width="400" />
    </div>
</div>
<br>
<br>
<form class="d-flex justify-content-lefr" action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" accept=".jpeg,.jpg,.png" required="">
                <input type="submit" name="submit" value="Upload">
</form>
<br>
        <br>
        <h4>Total Files : <?php echo sizeof($result->getBlobs())?></h4>
        <table class='table table-hover' border="1">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>File URL</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                do {
                    foreach ($result->getBlobs() as $blob)
                    {
                        ?>
                        <tr>
                            <td><?php echo $blob->getName() ?></td>
                            <td><?php echo $blob->getUrl() ?></td>
                            <td>
                                <form action="computervision.php" method="post">
                                    <input type="hidden" name="url" value="<?php echo $blob->getUrl()?>">
                                    <input type="submit" name="submit" value="Analyze!" class="btn btn-primary">
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                    $listBlobsOptions->setContinuationToken($result->getContinuationToken());
                } while($result->getContinuationToken());
                ?>
            </tbody>
        </table>
</body>
</html>