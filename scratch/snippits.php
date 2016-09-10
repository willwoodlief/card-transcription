
<script>
    /*
    To get the base64 data, you can just use the snapshotImage() method.
        For instance, open a web console on http://mattketmo.github.io/darkroomjs/ and call dkrm.snapshotImage();

        see http://stackoverflow.com/questions/39104814/how-do-i-apply-an-imagedata-pixel-transformation-in-darkroom-js
        see https://github.com/MattKetmo/darkroomjs/issues/8
        */
</script>
<?php


?>




<?php
print '<pre>'.var_dump($user->data()).'</pre>';
$perms = fetchUserPermissions($user->data()->id);
print '<hr><pre>'.var_dump($perms).'</pre>';
print '<hr><pre>'.var_dump($user->roles()).'</pre>';




foreach (
    rest_helper('https://api.github.com/users/willwoodlief/repos')
        ->repositories as $repo) {
    echo $repo->name, "<br>\n";
    echo htmlentities($repo->description), "<br>\n";
    echo "<hr>\n";
}

?>






