<?php
	set_time_limit(0);
	class MusicSearch{
		/**
		* Attribute to store the url
		*/
		private $url = "http://player.radiorock.com.br/cron/integrador/results.json?_=1426437442704";

		/**
		* Attribute to set time interval
		*/
		private $time_interval = 60;

		/**
		* File to store list
		*/
		private $file = "./data/musics.txt";

		/**
		* Error curl log file
		*/
		private $error = "./logs/errors.txt";

		/**
		* Musics directory
		*/
		private $musics = "./mp3";

		/**
		* Instance of this class
		*/
		public static $instance;

		/**
        * Method that returns an instance
        */
        public static function getInstance(){
            if (!isset(self::$instance) && is_null(self::$instance)) {
                $c = __CLASS__;
                self::$instance = new $c;
            }
            return self::$instance;
        }

        /**
        * Private constructor to prevent direct criation
        */
        private function __construct(){}

        /**
        * Musics to download
        */
        public function toDownload($startAt=1, $endAt=INF){
			$lines = file($this->file);
			$toDownload = [];
        	foreach ($lines as $line_num => $line){
        		if($line_num>$startAt && $line_num<$endAt){
	        		$explode = explode("#", $line);
	        		$music_line = str_replace("\n", "", $explode[1]);
	        		$artist_line = $explode[0];
	        		$file = $this->musics . "/" . ucwords(strtolower($artist_line . " - " . $music_line)). ".mp3";
	        		if(!is_file($file)){
	        			$toDownload[] = $artist_line . " - " . $music_line;
	        		}
        		}
            }
            return $toDownload;
        }

        /**
        * Function to start music search
        */
        public function go(){
        	while(true){
        		$curl   = $this->_curl($this->url);
        		if($curl != false){
					$json   = json_decode($curl);
					$music  = strtoupper($json->musicas[0]->tocando[0]->song);
					$artist = strtoupper($json->musicas[0]->tocando[0]->singer);
					if(!$this->search($artist, $music)){
						echo "Nova música encontrada: " . $artist . "#" . $music . "\n";
						$this->insert($artist, $music);
					}
	        	}
	        	else{
	        		echo "Conexão falhou. Tentando novamente em " . $this->time_interval . " segundos." . "\n";
	        	}
	        	sleep($this->time_interval);
	        }
        }

        /**
        * Method to inser register in a file
        */
        public function insert($artist, $music){
        	fwrite(fopen($this->file, 'a'), $artist . "#" . $music . "\n");
        }

        /**
        * Method to search if a music already is on database
        */
        public function search($artist, $music){
        	$lines = file($this->file);
        	foreach ($lines as $line_num => $line){
        		$explode = explode("#", $line);
        		$music_line = str_replace("\n", "", $explode[1]);
        		$artist_line = $explode[0];
        		if($music == $music_line && $artist == $artist_line){
        			echo "Música já adicionada. Nova busca em " . $this->time_interval . " segundos." . "\n";
        			return true;
        		}
            }
            return false;
        }

        /**
		* Curl
		*/
		public function _curl($url){
			$ch      = curl_init();
			$options = array(
			    CURLOPT_URL            => $url,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_HEADER         => false,
			    CURLOPT_FOLLOWLOCATION => true,
			    CURLOPT_ENCODING       => "",
			    CURLOPT_AUTOREFERER    => true,
			    CURLOPT_CONNECTTIMEOUT => 120,
			    CURLOPT_TIMEOUT        => 120,
			    CURLOPT_MAXREDIRS      => 10,
			    CURLOPT_SSL_VERIFYPEER => false
			);
			curl_setopt_array($ch, $options);
			$query = curl_exec($ch); 
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_status != 200){
				$filename = $this->error;
				$file = fopen($filename, "a+");
				fwrite($file, "\n(". date("d-m-Y H:i:s") . ") -> ERRO AO ACESSAR A URL: " . $url); 
				fclose($file);
				$query = false;
			}
			curl_close($ch);
			return $query;
		}
	}
?>