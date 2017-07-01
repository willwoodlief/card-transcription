<?php 
class  method {

function user_settings($uid){
include('connect.php');
$sql='SELECT * FROM wz_wpdatatable_4 WHERE user_id = '$uid' LIMIT 1';
$res=mysql_query($sql);
 return $res;
}
function user_details($uid){
include('connect.php');
$sql='SELECT * FROM dap_users WHERE user_id = '$uid' LIMIT 1';
$res=mysql_query($sql);
 return $res;
}
function user_membership($uid){
include('connect.php');
$sql='SELECT product_id FROM dap_users_products_jn WHERE user_id = '$uid' AND product_id REGEXP '74|75|76|77|79' AND CURDATE() <= access_end_date ORDER BY Product_id DESC LIMIT 1';
$res=mysql_query($sql);
 return $res;
}
function verify_cellphone(){



}
function enric_data($key,$email,$url){
$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'http://app.enri.ch/app.php',
  CURLOPT_POST => 1,
  CURLOPT_POSTFIELDS => ['key' => $key, 'email' => $email,'url'=>$url],
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;


}
function geocode_address($address,$key){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://api.ivr-platform.com/api/token-auth',
  CURLOPT_POST => 1,
  CURLOPT_POSTFIELDS => ['address' => $address, 'key' => $Key],
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function freeloader($proid,$key,$redirect){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://enri.ch/dap/dapconnect.php',
  CURLOPT_POST => 1,
  CURLOPT_POSTFIELDS => ['productId' => $proid, 'secretKey' => $Key, 'redirect' => $redirect],
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function directmail_postcard($key,$cust,$ordno,$accountemail,$dmmaddleadandmail,$fname,$lname,$address,$city,$state,$zip,$business_phone,$company){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://enri.ch/dap/dapconnect.php',
  CURLOPT_POST => 1,
  CURLOPT_POSTFIELDS => ['apikey'=>$key,'idcust'=$cust,'orderno'=$ordno,'customeremail'=>$accountemail,'action'=>$dmmaddleadandmail,'listname'=>$fname,'name'=>$fname.$lname,'address1'=>$address,'city'=>$city,'state'=>$state,'zip'=>$zip,'phone'=>$business_phone,'company'=>$company],
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function pdf_marged($userid,$profile_id,$delivery){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://app.enri.ch/processes/pdf/bcrpdf.php?userid=2802&profile_id=2&transcription_id=14&delivery=pdf&email=feedleadcalls@gmail.com&fname=Tom&mname=George&lname=Mack&suffix=JR&title=Owner&company=Feed%20Lead%20Corporation&address=777%20Piedmont%20Wekiva%20%20Road,%20Suite%20711&city=Apopka&state=FL&zip=32701&country=USA&work_phone=4072552266&work_phone_extension=22&home_phone=4073592220&cell_phone=4079497248&fax=4078987621&other_phone=4073592220&skype=tom.and.mary.mack&twitter=enrichautomation&website=feedlead.com&notes=somenotes&tag_string=sometags&latitude=&longitude=&images_org_side_a=https://s3.amazonaws.com/enrich-scanner/e_img43b_id2813_p2_20170105.jpg&images_org_side_b=https://s3.amazonaws.com/enrich-scanner/e_img43a_id2813_p2_20170105.jpg&images_edit_side_a=https://s3.amazonaws.com/enrich-scanner/e_img43b_id2813_p2_20170105.jpg&images_edit_side_b=https://s3.amazonaws.com/enrich-scanner/e_img43a_id2813_p2_20170105.jpg',
  CURLOPT_POST => 1,
  
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function fax($userid,$profile_id,$delivery){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://app.enri.ch/processes/pdf/bcrpdf.php?userid=2802&profile_id=2&transcription_id=14&delivery=fax&email=feedleadcalls@gmail.com&fname=Tom&mname=George&lname=Mack&suffix=JR&title=Owner&company=Feed%20Lead%20Corporation&address=777%20Piedmont%20Wekiva%20%20Road,%20Suite%20711&city=Apopka&state=FL&zip=32701&country=USA&work_phone=4072552266&work_phone_extension=22&home_phone=4073592220&cell_phone=4079497248&fax=4078987621&other_phone=4073592220&skype=tom.and.mary.mack&twitter=enrichautomation&website=feedlead.com&notes=somenotes&tag_string=sometags&latitude=&longitude=&images_org_side_a=https://s3.amazonaws.com/enrich-scanner/e_img43b_id2813_p2_20170105.jpg&images_org_side_b=https://s3.amazonaws.com/enrich-scanner/e_img43a_id2813_p2_20170105.jpg&images_edit_side_a=https://s3.amazonaws.com/enrich-scanner/e_img43b_id2813_p2_20170105.jpg&images_edit_side_b=https://s3.amazonaws.com/enrich-scanner/e_img43a_id2813_p2_20170105.jpg',
  CURLOPT_POST => 1,
  
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function email_drip($userid,$profile_id,$delivery){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://app.enri.ch/webforms/webform.php?user_id=2802&profile_id=1&transcription_id=14&email=feedleadcalls@gmail.com&fname=Tom&mname=George&lname=Mack&suffix=JR&title=Owner&company=Feed%20Lead%20Corporation&address=777%20Piedmont%20Wekiva%20%20Road,%20Suite%20711&city=Apopka&state=FL&zip=32701&country=USA&work_phone=4072552266&work_phone_extension=22&home_phone=4073592220&cell_phone=4079497248&fax=4078987621&other_phone=4073592220&skype=tom.and.mary.mack&twitter=enrichautomation&website=http://feedlead.com&notes=somenotes&tag_string=sometags&latitude=28.419480528153823&longitude=-81.58116012811661',
  CURLOPT_POST => 1,
  
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function team_communication($userid,$profile_id,$delivery){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'webhook_share_endpoint?user_id=2802&profile_id=1&transcription_id=14&email=feedleadcalls@gmail.com&fname=Tom&mname=George&lname=Mack&suffix=JR&title=Owner&company=Feed%20Lead%20Corporation&address=777%20Piedmont%20Wekiva%20%20Road,%20Suite%20711&city=Apopka&state=FL&zip=32701&country=USA&work_phone=4072552266&work_phone_extension=22&home_phone=4073592220&cell_phone=4079497248&fax=4078987621&other_phone=4073592220&skype=tom.and.mary.mack&twitter=enrichautomation&website=feedlead.com&notes=somenotes&tag_string=sometags&latitude=&longitude=&images_org_side_a=https://s3.amazonaws.com/enrich-scanner/e_img43b_id2813_p2_20170105.jpg&images_org_side_b=https://s3.amazonaws.com/enrich-scanner/e_img43a_id2813_p2_20170105.jpg',
  CURLOPT_POST => 1,
  
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function penny_api($userid,$profile_id,$delivery){

$ch = curl_init();
curl_setopt_array($ch, [
  CURLOPT_URL => 'https://enri.ch/spendapenny.php?userid=2802&productid=82&creditsspent=1&transcriptionid=-10398&comment=Richard%20Moelman%20-%20Meninium%20Electric',
  CURLOPT_POST => 1,
  
  CURLOPT_RETURNTRANSFER => true,
]);

$json = curl_exec($ch);
return $json;
}
function email_notification(){



}
}



?>