<?php
$db = new SQLite3('tempdata.db');
$results = $db->query('SELECT * FROM data ORDER BY rowid DESC limit 1441'); #get the last 24 hours worth of data from the data base
$first_row = 1; #used for flagging
$x_offset = 35; #used to offset the graph from the page.
$y_offset = 35;
$x = 1440; #The size of the graph, not the total image
$y = 500;
$img = imagecreatetruecolor($x + 2*$x_offset,$y + 2*$y_offset + 40); #plus 40 to add additional space to insert info bellow the graph.
$black = imagecolorallocate($img, 0, 0, 0);
$white = imagecolorallocate($img, 255, 255, 255);
$grey = imagecolorallocate($img, 100, 100, 100);
$green = imagecolorallocate($img, 0, 255, 0);
$red = imagecolorallocate($img, 255, 0, 0);
$aqua =  imagecolorallocate($img, 0, 255, 255);
imagefill($img, 0, 0, $white); #background of the entire image.
imagefilledrectangle($img, $x_offset, $y_offset, $x+$x_offset, $y+$y_offset, $black); #Background of graph.

header('Content-Type: image/png');
imagestring($img, 5, (($x - 5*$x_offset))/2, $y_offset/2, "31 P ST. Apt A", $black);

for($i=0;$i<=20;$i++){ #print the horizontal dashed lines and the temperature ticks.
	imagedashedline($img,$x_offset,$i*-25 + ($y+$y_offset),$x+$x_offset,$i*-25 + ($y+$y_offset),$grey);
	
	imagestring($img, 3, $x_offset/2, $i*25+8+$y_offset/2, 100-($i*5),$black); #left
	imagestring($img, 3, $x+$x_offset+2, $i*25+8+$y_offset/2, 100-($i*5),$black); #right
}
imageline($img,$x_offset,$y_offset+5*(100 - 32),$x+$x_offset,$y_offset+5*(100 - 32),$aqua); #line to represent freezing temps
$i=$x; #Creates an index for the following while loop based on the width of the image.

while ($row = $results->fetchArray(SQLITE3_ASSOC)) { #keep getting data until we get the last 24 hours worth of data.
	$x1 = $i;
	$y1 = round($row['temp']);
	if($first_row == 1){
		imagestring($img,5,$x_offset,$y+$y_offset+25,"Current Date and Time: ".$row['date']." ".$row['time'],$black);
		imagestring($img,5,$x_offset,$y+$y_offset+40,"Current Temperature: ".$row['temp'],$black);
	}
	if($first_row == 0){ #Only do this when we have 2 data points to plot
		imageline($img,$x1+$x_offset,$y1*-5 + $y+$y_offset,$x2+$x_offset,$y2*-5 + $y+$y_offset,$green); #draws a line given two points
	}
	$x2 = $x1; #will be the old values on the next iteration
 	$y2 = $y1;
	$i = $i-1;
	$first_row = 0; #we're not on the first row anymore
	if ($i <= $x && ($i+1) % 60 == 0){ #prints a vertical dashed line and the time on the x-axis
		imagedashedline($img,$i+$x_offset,$y_offset,$i+$x_offset,$y+$y_offset,$grey);
		imagestring($img,3,$i+$x_offset/2,$y+$y_offset,$row['time'],$black);
	}
	if($row['time'] == "00:00" && $x1 != 1){ #don't want to print two dates when two 0:00 show up on the graph.
		imageline($img,$i+$x_offset,$y_offset,$i+$x_offset,$y+$y_offset,$red);
		imagestring($img,3,$i+($x_offset/2) - 10,$y+$y_offset+10,$row['date'],$black);

	}
}
imagestring($img, 5, ($x-2*$x_offset)/2, $y+$y_offset+25, 'Time' ,$black); #xlabel
imagestringup($img, 5, 0 ,($y+2*$y_offset+40)/2, 'Temperature (F)', $black); #ylabel
imagepng($img);
?>
