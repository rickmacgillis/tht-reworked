<link type="text/css" href="<URL>includes/codemirror/codemirror.css" rel="stylesheet"/>
<script type="text/javascript" src="<URL>includes/codemirror/codemirror.js"></script>
<link type="text/css" href="<URL>includes/codemirror/css/css.css" rel="stylesheet"/>
<script type="text/javascript" src="<URL>includes/codemirror/css/css.js"></script>
<style>.CodeMirror {background: #f8f8f8;}</style> 
<script>
$(document).ready(function() {
    CodeMirror.fromTextArea(document.getElementById("cssArea"), {mode: "css"});
});
</script>
<strong>Editing your Cascading Style Sheet</strong><br />
<p>Want to edit your style in an web based interface? Here it is!</p>
<p>Variables:<br /> &lt;IMG&gt; tag links to /themes/your_style/images/ directory.</p><br /><br />
<ERRORS>
<form method = "POST">
    <textarea name="contents" id="cssArea" cols="45" rows="5" style="width:99%; height:300px;">%CONTENT%</textarea><br>
    <input type = "submit" id="editcss" value = "Edit CSS" name = "editcss" class = "button"> %NOTICE%
</form>
