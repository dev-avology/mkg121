<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, access-control-allow-methods, Access-Control-Allow-Headers, X-Requested-With, Authorization");
if(isset($_GET['mode']) && $_GET['mode']=="logoutUser"){
    /*echo "<pre>";
    print_r($_COOKIE);
    echo "<pre>";*/
    foreach($_COOKIE as $key=>$value){
        setcookie($key, '', time()-1000);
        setcookie($key, '', time()-1000, '/');
    }
   /* echo "<pre>";
    print_r($_COOKIE);
    echo "<pre>";*/
    ?>
    <form id="TheForm" method="post" action="https://mktg.booostr.co/customer/index.php/guest/index" target="TheNewWindow">
        <input type="hidden" id="customerlogin" name="CustomerLogin[email]" value="<?php echo $_POST['email']; ?>" />
        <input type="hidden" id="customerpassword" name="CustomerLogin[password]" value="<?php echo $_POST['password']; ?>" />
    </form>
    <script>
        document.getElementById('TheForm').submit();
    </script>
    <?php
    exit;
}
//error_reporting(E_ALL);
//check mode
$uuid=$_GET['uuid'];
//database
$mysqli = new mysqli("localhost","root","kJUjhNkK5r7Fd5gQ","mailer");
if ($mysqli -> connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
}

if(isset($_GET['mode']) && $_GET['mode']=="createList"){
    //create api key
    //https://mktg.booostr.co/frontend/uploadLogocustomer-18052022.php?uuid=ck656az8n11af&mode=createList
    $uuid=$_GET['uuid'];
    $sql="select * from `mw_customer` where customer_uid='".$uuid."'";
    $result=$mysqli->query($sql);
    if($result){
        $row = $result -> fetch_assoc();
        if(!empty($row)){
            $customer_id=$row['customer_id'];
            //
            $metadata='a:2:{s:38:"is_select_all_at_action_when_subscribe";i:0;s:40:"is_select_all_at_action_when_unsubscribe";i:0;}';
            $sqlinsert="Insert into `mw_list` (list_uid,customer_id, name, display_name,description,visibility,opt_in,opt_out,welcome_email,removable,subscriber_require_approval,meta_data,status,date_added,last_updated)
            values
            ('".uniqid()."',".$customer_id.",'Booster Club List','Booster Club List','Booster Club List','public','double','single','no','yes','no','".$metadata."','active','".date('Y-m-d h:i:s')."','".date('Y-m-d h:i:s')."');
            ";
            $mysqli->query($sqlinsert);
            echo $mysqli->insert_id;
            die;
        }
    }
    die;
}
else if(isset($_GET['mode']) && $_GET['mode']=="updatecomapny"){
    $sql="select * from `mw_customer` where customer_uid='".$uuid."'";
    $result=$mysqli->query($sql);
    if($result){
        $row = $result->fetch_assoc();
        if(!empty($row)){
            $customer_id=$row['customer_id'];
            //find the company
            $sql="select count(*) as count from `mw_customer_company` where customer_id=". $customer_id;
            $result=$mysqli->query($sql);
            $row = $result->fetch_assoc();
            if($row['count']==0)
            {
                //insert 
                $sqlinsert="insert into mw_customer_company (customer_id,country_id,name,address_1,address_2,zone_name,city,zip_code,date_added,last_updated) values
                ($customer_id,223,'".$_GET['name']."','".$_GET['address_1']."','".$_GET['address_2']."','".$_GET['zone']."','".$_GET['city']."','".$_GET['zip_code']."','".date('Y-m-d h:i:s')."','".date('Y-m-d h:i:s')."')";
                $mysqli->query($sqlinsert);
                echo "Inserted";
            }
            else{
                //update 
                //leave it for now
                $sqlinsert="update mw_customer_company set 
                name='".urldecode(str_replace("–","-",$_GET['name']))."',address_1='".urldecode($_GET['address_2'])."', address_2='".urldecode($_GET['address_2'])."',zone_name='".urldecode($_GET['zone'])."',city='".urldecode($_GET['city'])."',zip_code='".urldecode($_GET['zip_code'])."'
                where customer_id=".$customer_id;
                $mysqli->query($sqlinsert);
                echo "Leave it for now";
            }
            //
            /*$key=uniqid();
            $sqlinsert="Insert into `mw_customer_api_key` (`customer_id`,`key`,`date_added`)
            values
            (".$customer_id.",'".$key."','".date('Y-m-d h:i:s')."');
            ";
            $mysqli->query($sqlinsert);
            if($mysqli->insert_id){
                echo $key;
            }*/
            die;
        }
    }
    die;
    die;
}
else if(isset($_GET['mode']) && $_GET['mode']=="contact" && isset($_GET['list_id']) && isset($_GET['email'])){
    //echo "Existing list";
    $lid=$_GET['list_id'];
    $email=$_GET['email'];
    //check if email and list id is already there 
    $sqlcheck="select count(*) as count from `mw_list_subscriber` where list_id=".$lid." and email='".$email."'";
    $result=$mysqli->query($sqlcheck);
    $row = $result -> fetch_assoc();
    if($row['count']==0)
    {
        $sqlinsert="Insert into `mw_list_subscriber` (subscriber_uid,list_id,email,source,status,date_added,last_updated)
        values
        ('".uniqid()."',".$lid.",'".$_GET['email']."','web','confirmed','".date('Y-m-d h:i:s')."','".date('Y-m-d h:i:s')."');
        ";
        $mysqli->query($sqlinsert);
    }
    die;
}
else if(isset($_GET['mode']) && $_GET['mode']=="createAPIKEY"){

    $sql="select * from `mw_customer` where customer_uid='".$uuid."'";
    $result=$mysqli->query($sql);
    if($result){
        $row = $result -> fetch_assoc();
        if(!empty($row)){
            $customer_id=$row['customer_id'];
            //
            $key=uniqid();
            $sqlinsert="Insert into `mw_customer_api_key` (`customer_id`,`key`,`date_added`)
            values
            (".$customer_id.",'".$key."','".date('Y-m-d h:i:s')."');
            ";
            $mysqli->query($sqlinsert);
            if($mysqli->insert_id){
                echo $key;
            }
            die;
        }
    }
    die;
}
else{
    //upload image
    $imageurl = $_GET['url'];
    //'https://staging3.booostr.co/wp-content/uploads/2022/01/Capture001.png';
    $filename=end(explode('/',$imageurl));
    $imagePath=$_SERVER['DOCUMENT_ROOT'].'/frontend/assets/files/avatars/'.$filename;
    $newPath=$_SERVER['DOCUMENT_ROOT'].'/frontend/assets/files/resized/90x90/'.$filename;
    copy($imageurl,$imagePath);
    //update logo in database
    //resize the image
    //resized
    createResizedImage($imagePath,$newPath,90,90,'DEFAULT');

    //update the database


    // Check connection


    $sql="update `mw_customer` set avatar='/frontend/assets/files/avatars/".$filename."' where customer_uid='".$uuid."'";
    $mysqli->query($sql);
    ///
    
}

function createResizedImage(
    string $imagePath = '',
    string $newPath = '',
    int $newWidth = 0,
    int $newHeight = 0,
    string $outExt = 'DEFAULT'
) : ?string
{
    if (!$newPath or !file_exists ($imagePath)) {
        return null;
    }

    $types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_BMP, IMAGETYPE_WEBP];
    $type = exif_imagetype ($imagePath);

    if (!in_array ($type, $types)) {
        return null;
    }

    list ($width, $height) = getimagesize ($imagePath);

    $outBool = in_array ($outExt, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg ($imagePath);
            if (!$outBool) $outExt = 'jpg';
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng ($imagePath);
            if (!$outBool) $outExt = 'png';
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif ($imagePath);
            if (!$outBool) $outExt = 'gif';
            break;
        case IMAGETYPE_BMP:
            $image = imagecreatefrombmp ($imagePath);
            if (!$outBool) $outExt = 'bmp';
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp ($imagePath);
            if (!$outBool) $outExt = 'webp';
    }

    $newImage = imagecreatetruecolor ($newWidth, $newHeight);

    //TRANSPARENT BACKGROUND
    $color = imagecolorallocatealpha ($newImage, 0, 0, 0, 127); //fill transparent back
    imagefill ($newImage, 0, 0, $color);
    imagesavealpha ($newImage, true);

    //ROUTINE
    imagecopyresampled ($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Rotate image on iOS
    if(function_exists('exif_read_data') && $exif = exif_read_data($imagePath, 'IFD0'))
    {
        if(isset($exif['Orientation']) && isset($exif['Make']) && !empty($exif['Orientation']) && preg_match('/(apple|ios|iphone)/i', $exif['Make'])) {
            switch($exif['Orientation']) {
                case 8:
                    if ($width > $height) $newImage = imagerotate($newImage,90,0);
                    break;
                case 3:
                    $newImage = imagerotate($newImage,180,0);
                    break;
                case 6:
                    $newImage = imagerotate($newImage,-90,0);
                    break;
            }
        }
    }

    switch (true) {
        case in_array ($outExt, ['jpg', 'jpeg']): $success = imagejpeg ($newImage, $newPath);
            break;
        case $outExt === 'png': $success = imagepng ($newImage, $newPath);
            break;
        case $outExt === 'gif': $success = imagegif ($newImage, $newPath);
            break;
        case  $outExt === 'bmp': $success = imagebmp ($newImage, $newPath);
            break;
        case  $outExt === 'webp': $success = imagewebp ($newImage, $newPath);
    }

    if (!$success) {
        return null;
    }

    return $newPath;
}

?>
