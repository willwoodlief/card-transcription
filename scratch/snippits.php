
<script>
    /*
    To get the base64 data, you can just use the snapshotImage() method.
        For instance, open a web console on http://mattketmo.github.io/darkroomjs/ and call dkrm.snapshotImage();

        see http://stackoverflow.com/questions/39104814/how-do-i-apply-an-imagedata-pixel-transformation-in-darkroom-js
        see https://github.com/MattKetmo/darkroomjs/issues/8
        */
</script>
<?php
function is_connected()
{
    //http://stackoverflow.com/questions/4860365/determine-in-php-script-if-connected-to-internet
    $connected = @fsockopen("www.some_domain.com", 80);
    //website, port  (try 80 or 443)
    if ($connected){
        $is_conn = true; //action when connected
        fclose($connected);
    }else{
        $is_conn = false; //action in connection failure
    }
    return $is_conn;

}

function base64_to_jpeg($base64_string, $output_file) {
    //http://stackoverflow.com/questions/15153776/convert-base64-string-to-an-image-file
    $ifp = fopen($output_file, "wb");

    $data = explode(',', $base64_string);

    fwrite($ifp, base64_decode($data[1]));
    fclose($ifp);

    return $output_file;
}

?>




<?php
print '<pre>'.var_dump($user->data()).'</pre>';
$perms = fetchUserPermissions($user->data()->id);
print '<hr><pre>'.var_dump($perms).'</pre>';
print '<hr><pre>'.var_dump($user->roles()).'</pre>';
?>

