<?php
	include('./app/Youtube/Finder/YoutubeFinder.php');
	include('./app/Youtube/Downloader/youtube-dl.class.php');
	include('./app/MusicSearch.php');
	$instance = MusicSearch::getInstance();
	$musics_list = $instance->toDownload();
	foreach ($musics_list as $music) {
		echo "Música: ". $music . "\n";
		$ytObject   = new YoutubeFinder();
		$videoLinks = array();
		echo "Buscando url do youtube da música.\n";
		if($ytObject->search($music, 5)){
			if(count($ytObject->RKT_requestResult->entry) > 0 ){
				foreach ($ytObject->RKT_requestResult->entry as $video) {
					$videoLinks[] = (string)$video->link->attributes()->href;
				}
			}
		}
		else{
			echo "A url do youtube da música não foi encontrada.\n";
			echo $ytObject->getRKT_errorMessage();
		}
		$video_count = 0;
		foreach ($videoLinks as $videoLink) {
			if($video_count == 0){
				$number = "";
			}
			else{
				$number = $video_count;
			}
			try {
				echo "Baixando a música.\n";
			    $mytube    = new yt_downloader($videoLink, TRUE, "audio");
		    	$audio     = $mytube->get_audio();
		    	$path_dl   = $mytube->get_downloads_dir();
		    	$thumb     = $mytube->get_thumb();
		    	$thumbfile = $path_dl . $thumb;
		    	$oldname   = $path_dl . $audio;
		    	$newname   = $path_dl . ucwords(strtolower($music . $number)).".mp3";
				if($audio !== FALSE && file_exists($oldname) !== FALSE)
				    rename ($oldname, $newname);
				if(file_exists($thumbfile))
					unlink($thumbfile);
			}
			catch (Exception $e) {
				echo "Não foi possível baixar a música desta vez.\n";
			    echo $e->getMessage();
			}
			$video_count++;
		}
	}
?>