<link type="text/css" href="<URL>includes/codemirror/codemirror.css" rel="stylesheet"/>
<script type="text/javascript" src="<URL>includes/codemirror/codemirror.js"></script>
<link type="text/css" href="<URL>includes/codemirror/xml/xml.css" rel="stylesheet"/>
<script type="text/javascript" src="<URL>includes/codemirror/xml/xml.js"></script>
<link type="text/css" href="<URL>includes/codemirror/javascript/javascript.css" rel="stylesheet"/>
<script type="text/javascript" src="<URL>includes/codemirror/javascript/javascript.js"></script>
<link type="text/css" href="<URL>includes/codemirror/css/css.css" rel="stylesheet"/>
<script type="text/javascript" src="<URL>includes/codemirror/css/css.js"></script>
<script type="text/javascript" src="<URL>includes/codemirror/htmlmixed/htmlmixed.js"></script>
<style>.CodeMirror {background: #f8f8f8;}</style> 
<script>
$(document).ready(function() {
    CodeMirror.fromTextArea(document.getElementById("tplCode"), {mode: "htmlmixed"});
});
</script>
<strong>Editing your Footer Template</strong><br />
<p>Want to edit your style in an web based interface? Here it is!<br /><br />
<em>Variables:</em><br /> &lt;PAGEGEN&gt; tag shows the debug stuff.<br />
&lt;COPYRIGHT&gt; the "Powered by" notice.<br><br>
<ERRORS>
<form method = "POST">
    <textarea cols="75" style="width:99%; height:300px;" id="tplCode" rows="25" wrap="no" name="contents">%CONTENT%</textarea><br>
    <input type = "submit" name = "editTheTplNao" class = "button" value = "Edit Footer" id="editTheTplNao"> %NOTICE%
</form>
