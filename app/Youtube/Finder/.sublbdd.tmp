<?php
class YoutubeFinder{
	private $RKT_resultsPerRequest = 10;
	private $RKT_startOffset = 1;
	private $RKT_orderResultsBy = 'relevance';
	private $RKT_searchQuery = '';
	private $RKT_videoId = '';
	private $RKT_curlTimeOut = 25;
	private $RKT_errorCode = 0;
	private $RKT_errorMessage = '';
	public  $RKT_rawXML = '';
	public  $RKT_requestResult;

	const ERROR_NO_SEARCH_TERMS  = 1;
	const ERROR_RETURNED_BLANK   = 2;
	const ERROR_XML_EXCEPTION    = 3;
	const ERROR_CURL_INIT        = 4;
	const ERROR_CURL_EXEC        = 5;
	const ERROR_FOPEN_FAIL       = 6;

	public function search($keywords, $recordWant = '')
	{
		if(intval($recordWant) > 1) {
			$this->RKT_resultsPerRequest = $recordWant;
		}
		return $this->sendRequest('search', $keywords);
	}

	private function sendRequest ($method, $searchTerms='')
	{
		if(!empty($searchTerms)) {
			$this->RKT_searchQuery = $searchTerms;
		}

		if($method == 'search') {
			$this->RKT_searchQuery = trim($this->RKT_searchQuery);

			if(empty($this->RKT_searchQuery)) {
				$this->setError(self::ERROR_NO_SEARCH_TERMS);
				return false;
			}
		}

		$requestUri = $this->getRequestUri($method);
		$resultXML = trim($this->sendGetRequest($requestUri));

		$this->RKT_rawXML = $resultXML;		
		if($resultXML === false || $this->RKT_errorCode > 0) {
			return false;
		}

		if(empty($resultXML)) {
			$this->setError(self::ERROR_RETURNED_BLANK);
			return false;
		}

		try {
			$this->RKT_requestResult = new SimpleXMLElement($resultXML);
		} catch(Exception $exception) {
			$this->setError(self::ERROR_XML_EXCEPTION, $exception->getMessage());
			return false;
		}

		return true;
	}

	private function sendGetRequest ($uri)
	{
		if(function_exists('curl_init')) {
			return $this->requestUsingCurl($uri);
		}

		return $this->requestUsingFopen($uri);
	}

	private function requestUsingCurl($url)
	{
		$connection = @curl_init();

		if(!$connection) {
			$this->setError(self::ERROR_CURL_INIT);
			return false;
		}

		curl_setopt($connection, CURLOPT_URL, $url);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($connection, CURLOPT_FAILONERROR, true);
		curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $this->RKT_curlTimeOut);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);

		$data = curl_exec($connection);
		if ($data === false) {
			$this->setError(self::ERROR_CURL_EXEC, 'Error ' . curl_errno($connection) . ': ' .curl_error($connection));
			return false;
		}

		curl_close($connection);

		return $data;
	}

	private function requestUsingFopen($url)
	{
		$data = '';
		$connection = @fopen($url, 'rb');

		if (!$connection) {
			$this->setError(self::ERROR_FOPEN_FAIL);
			return false;
		}

		while (!@feof($connection)) {
			$data .= @fgets($connection, 4096);
		}

		@fclose($connection);

		return $data;
	}

	private function getRequestUri ($type='search')
	{
		switch($type) {
			case 'search':
				return 'http://gdata.youtube.com/feeds/api/videos?q=' . urlencode($this->RKT_searchQuery) . '&orderby=' . $this->RKT_orderResultsBy . '&start-index='. $this->RKT_startOffset . '&max-results=' . $this->RKT_resultsPerRequest . '&v=2';

				//We Can Here Extand Many Cases as required.
		}
		return;
	}
	private function setError ($RKT_errorCode, $RKT_errorMessage='')
	{	
		switch($RKT_errorCode) {
			case self::ERROR_NO_SEARCH_TERMS:
				$this->RKT_errorCode = $RKT_errorCode;
				$this->RKT_errorMessage = 'No search terms entered.';
				break;
			case self::ERROR_RETURNED_BLANK:
				$this->RKT_errorCode = $RKT_errorCode;
				$this->RKT_errorMessage = 'YouTube returned a blank response.';
				break;
			case self::ERROR_CURL_INIT:
				$this->RKT_errorCode = $RKT_errorCode;
				$this->RKT_errorMessage = 'Unable to initialise cURL.';
				break;
			case self::ERROR_FOPEN_FAIL:
				$this->RKT_errorCode = $RKT_errorCode;
				$this->RKT_errorMessage = 'Unable to open the remote URL using fopen.';
				break;
			case self::ERROR_XML_EXCEPTION:
			case self::ERROR_CURL_EXEC:
				$this->RKT_errorCode = $RKT_errorCode;
				$this->RKT_errorMessage = $RKT_errorMessage;
				break;
		}
	}
	public function getRKT_errorCode()
	{
		return $this->RKT_errorCode;
	}


	public function getRKT_errorMessage()
	{
		return $this->RKT_errorMessage;
	}

	public function parseVideoRow($video)
	{
		$RKT_rating      = '';
		$RKT_ratingStars = '';
		$RKT_viewCount   = '0';

		$RKT_namespaces = $video->getNameSpaces(true);
		$RKT_media = $video->children($RKT_namespaces['media']);
		$RKT_ratings = $video->children($RKT_namespaces['gd']);
		$RKT_stats = $video->children($RKT_namespaces['yt']);

		$RKT_thumbnail = $RKT_media->group->thumbnail->attributes();
		$RKT_length    = $RKT_media->group->content->attributes();
		$RKT_videoInfo = $RKT_media->group->children($RKT_namespaces['yt']);

		$RKT_duration  = $RKT_videoInfo->duration->attributes();
		
		$RKT_videoId = trim((string)@$RKT_videoInfo->RKT_videoId);

		if(empty($RKT_videoId)) {
			$RKT_videoId = @$video->id;
			if(!empty($RKT_videoId)) {
				$RKT_videoId = str_replace('http://gdata.youtube.com/feeds/api/videos/', '', $RKT_videoId);
			}
		}
		$RKT_length = date('G:i:s', (int)$RKT_duration['seconds']);

		if(substr($RKT_length,0, 2) == '0:') {
			$RKT_length = substr($RKT_length, 2);
		}
		if(isset($RKT_stats->statistics)) {
			$RKT_statsInfo = $RKT_stats->statistics->attributes();
			$RKT_viewCount = $RKT_statsInfo['viewCount'];
		}
		$summary = (string)$RKT_media->group->description;
		if(strlen($summary) > 85) {
			$summary = substr($summary, 0, 85) . "...";
		}

		$title = (string)$video->title;
		if(strlen($title) > 25) {
			$title = substr($title, 0, 23) . "...";
		}
		
		$videoLink =  (string)$video->link->attributes()->href;
		$RKT_videoLength      = $RKT_length;
		$RKT_videoRating      = $RKT_ratingStars;
		$RKT_RKT_videoId      = $RKT_videoId;
		$RKT_videoViews       = number_format($RKT_viewCount);
		$RKT_videoTitle       = $title;
		$RKT_videoTitleFull   = (string)$video->title;
		$RKT_videoImage       = (string)$RKT_thumbnail['url'];
		$RKT_videoSummary     = $summary;
		$RKT_videoSummaryFull = (string)$RKT_media->group->description;

		$html .= '<li>';
			$html .= '<a href="'.$videoLink.'" class="viewYouTubeVideo" target="_blank"><img src="'.$RKT_videoImage.'" class="ytVideoImage" id="'.$RKT_videoId.'" title="'.$RKT_videoTitleFull.'"/></a>';
			$html .= '<a href="'.$videoLink.'" target="_blank"><div class="ytVideoTitle"><span class="videoTitleText" title="'.$RKT_videoTitleFull.'">'.$RKT_videoTitle.'</span> (<span class="ytVideoLength">'.$RKT_videoLength.'</span>)</div></a>';
			$html .= '<div class="ytVideoDetails">'.$RKT_videoViews.' views '.$RKT_videoRating.'<br /><span title="'.$RKT_videoSummaryFull.'">"'.$RKT_videoSummary.'"</span></div>';
		$html .= '</li>';

		$html = str_replace(array("\r", "\n"), '', $html);
		return $html;

	}
}