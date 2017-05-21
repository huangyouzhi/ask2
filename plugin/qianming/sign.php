<?php

function signature(){

	 $urls = array(
   
);
	$file = fopen(ASK2_ROOT."/plugin/qianming/qianming.txt", "r") or exit("Unable to open file!");
	while(!feof($file))
{


 
 array_push($urls,str_replace('ccjz、', '', fgets($file)));
}
fclose($file);

  return $urls;
}

