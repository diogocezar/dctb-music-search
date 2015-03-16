<?php
	include('./app/Youtube/Finder/YoutubeFinder.php');
	include('./app/MusicSearch.php');
	$instance = MusicSearch::getInstance();
	$musics_list = $instance->toDownload(200);
	foreach ($musics_list as $music) {
		$ytObject  = new YoutubeFinder();
		$videoLink = array();
		if($ytObject->search($music, 5)){
			if(count($ytObject->RKT_requestResult->entry) > 0 ){
				foreach ($ytObject->RKT_requestResult->entry as $video) {
					$videoLink[] = (string)$video->link->attributes()->href;
				}
			}
		}
		foreach ($videoLink as $link) {
			echo $link . "<br/>" . "\n";
		}
		echo "<br/>" . "\n";
	}
?>