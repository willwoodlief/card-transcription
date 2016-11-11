var editor = null;
$(function() {
    //add stuff here
     editor = ace.edit("editor");
    editor.setTheme("ace/theme/twilight");
    editor.session.setMode("ace/mode/json");

    editor.getSession().on('change', function(e) {
        // e.type, etc
      //
    });

});

function getEditorData(){
    $('#json_in').val(editor.getValue());
    return true;
}