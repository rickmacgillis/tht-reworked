<?PHP
//////////////////////////////
// The Hosting Tool
// Captcha Image
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

session_start();
header("Content-Type: image/jpeg");

$md5  = md5(rand(0, 9999));
$pass = substr($md5, 10, 5);

$_SESSION["pass"] = $pass;

$image     = ImageCreatetruecolor(100, 20);
$clr_white = ImageColorAllocate($image, 255, 255, 255);
$clr_black = ImageColorAllocate($image, 0, 0, 0);

imagefill($image, 0, 0, $clr_black);

imagefontheight(15);
imagefontwidth(15);

imagestring($image, 5, 30, 3, $pass, $clr_white);

imageline($image, 5, 1, 50, 20, $clr_white);
imageline($image, 60, 1, 96, 20, $clr_white);

echo imagejpeg($image);

imagedestroy($image);

?>