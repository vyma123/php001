<?php
// Include the database configuration file 
include_once 'db.php';
require_once 'functions.php';

if (isset($_POST['add_property'])) {
    $fileNames = array_filter($_FILES['gallery']['name']);
    $categories = $tags = $messempty = "";

    // File upload configuration 
    $targetDir = "uploads/";
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');

    $statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = '';
    if (!empty($fileNames)) {
        foreach ($_FILES['gallery']['name'] as $key => $val) {
            // File upload path 
            $fileName = basename($_FILES['gallery']['name'][$key]);
            $targetFilePath = $targetDir . $fileName;

            // Check whether file type is valid 
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            if (in_array($fileType, $allowTypes)) {
                // Upload file to server 
                if (move_uploaded_file($_FILES["gallery"]["tmp_name"][$key], $targetFilePath)) {
                    // Image db insert sql 
                    $insertValuesSQL .= "('" . $fileName . "', 'gallery'),";
                } else {
                    $errorUpload .= $_FILES['gallery']['name'][$key] . ' | ';
                }
            } else {
                $errorUploadType .= $_FILES['gallery']['name'][$key] . ' | ';
            }
        }

        // Error message 
        $errorUpload = !empty($errorUpload) ? 'Upload Error: ' . trim($errorUpload, ' | ') : '';
        $errorUploadType = !empty($errorUploadType) ? 'File Type Error: ' . trim($errorUploadType, ' | ') : '';
        $errorMsg = !empty($errorUpload) ? '<br/>' . $errorUpload . '<br/>' . $errorUploadType : '<br/>' . $errorUploadType;

        if (!empty($insertValuesSQL)) {
            $insertValuesSQL = trim($insertValuesSQL, ',');
            // Insert image file name into database 
            try {
                $insert = $conn->query("INSERT INTO property (name_, type_) VALUES $insertValuesSQL");
                if ($insert) {
                    $statusMsg = "Files are uploaded successfully." . $errorMsg;
                }
            } catch (mysqli_sql_exception $e) {
                if ($conn->errno == 1062) { // Error code for duplicate entry
                    echo "Error: Gallery name already exists.<br>";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        } else {
            $statusMsg = "Upload failed! " . $errorMsg;
        }
    } else {
        $statusMsg = 'Please select a file to upload.';
    }


    
  

    // add categories 
    if (!empty($_POST['categories'])) {
        $categories = test_input($_POST['categories']);

        $u = "SELECT name_ from property where name_ = '$categories'";
        $uu = mysqli_query($conn, $u);


        if (mysqli_num_rows($uu) > 0) {
            $result_cate = "<h5 class='warning'> This category already exists or duplicate with tag</h5>";
        } else{
            
        $sql = "INSERT INTO property (type_, name_) VALUES ('category', '$categories')";
        try {
            if ($conn->query($sql) === TRUE) {
                $result_cate = "New category created successfully";
            }
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno == 1062) { // Error code for duplicate entry
                echo "Error: Category name already exists.<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
    }

    // add tags 
    if (!empty($_POST['tags'])) {
        $tags = test_input($_POST['tags']);
        $u = "SELECT name_ from property where name_ = '$tags'";
        $uu = mysqli_query($conn, $u);


        if (mysqli_num_rows($uu) > 0) {
            $result_tag = "<h5 class='warning'> This tag already exists or duplicate with category</h5>";
        } else {
        $sql = "INSERT INTO property (type_, name_) VALUES ('tag', '$tags')";
        try {
            if ($conn->query($sql) === TRUE) {
                $result_tag = "New tag created successfully";
            }
        } catch (mysqli_sql_exception $e) {
            if ($conn->errno == 1062) { // Error code for duplicate entry
                echo "Error: Tag name already exists<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
    }

    if (empty($fileNames) && empty($categories) && empty($tags)) {
        echo 'No properties have been added yet';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property</title>
    <!-- semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Add Property</h1>
    <form enctype="multipart/form-data" class="ui form" method="post">
        <div class="field">
            <label>Gallery</label>
            <input type="file" name="gallery[]" accept=".jpg, .png, .jpeg" multiple>
            <div>
                <?php
                if (!empty($fileNames)) {
                    echo $statusMsg;
                }
                ?>
            </div>
        </div>
        <div class="field">
            <label>Categories</label>
            <input type="text" name="categories" placeholder="Categories">
            <div><?php
                    if (isset($categories) && $categories !== "") {
                        echo $result_cate;
                    }
                    ?></div>
        </div>
        <div class="field">
            <label>Tags</label>
            <input type="text" name="tags" placeholder="Tags">
            <div><?php
                    if (isset($tags) && $tags !== "") {
                        echo $result_tag;
                    }
                    ?></div>
        </div>
        <div class="footer_property">
            <a class="ui button" href="index.php">Back</a>
            <button name="add_property" class="ui button" type="submit">Add</button>
        </div>
    </form>
    <script src="script.js"></script>
</body>

</html>