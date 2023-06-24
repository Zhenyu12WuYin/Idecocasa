<?php

function generateArticle(Article $article, ArticleConfig $articleConfig) {
	$curl = curl_init();
	$contentRaw = 'id='.($article->getType() == 'Largo' ? 'd6244efc-a545-4611-8acc-e46ccc7a8543' : '10c9bfc4-6dab-4f17-a426-ee684a8c8b96').'&tone=Informal&value1='.$article->getTitle().
	              '&value2='.($article->getKeywords() != null ? $article->getKeywords() : '').'&fromLanguage=ES&toLanguage='.$articleConfig->getLanguage();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://app.escribelo.ai/api/v1/tools/?origin=plugin&v=1.0.7',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $contentRaw,
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '.get_option('escribelo_session'),
			'Content-Type: application/x-www-form-urlencoded'
		),
	));
	$response = curl_exec($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if($httpCode == 524) return trackArticle($article, $articleConfig);

	if(strpos($response, 'The server is currently overloaded with other requests. Sorry about that! You can retry your request') !== false) {
		return generateArticle($article, $articleConfig);
	}

	if(strpos($response, 'You\'ve reached the words of your plan') !== false) {
		return -1;
	}

	$json = json_decode($response, true);

	if($json['error']) return null;

	return $json['choices'][0]['text'];
}

function trackArticle(Article $article, ArticleConfig $articleConfig) {
	$actualTime = floor(time() / 1000);
	for($i = 0; $i < 44; $i++) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://app.escribelo.ai/api/v1/tools/latest?title='.$article->getTitle());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer '.get_option('escribelo_session')
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($response, true);

		if (strpos($response, 'Too many requests! Please try later') !== false || $json['time'] < $actualTime) {
			sleep(16);
			continue;
		}

		return $json['text'];
	}
	return generateArticle($article, $articleConfig);
}

function verifyLogin() : bool {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://app.escribelo.ai/api/v1/accounts/");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, [
		"Authorization: Bearer ".get_option('escribelo_session')
	]);

	$response = curl_exec($curl);
	curl_close($curl);

	$json = json_decode($response, true);

	if($json['error'] || strpos($json['plan'], 'Infinity') || strpos($json['plan'], 'Premium20k') || strpos($json['plan'], 'LifeTime')) {
		delete_option('escribelo_session');
		return false;
	}
	return true;
}

function retrieveNotifications(string $version) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://app.escribelo.ai/api/v1/accounts/plugin");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, [
		"Authorization: Bearer ".get_option('escribelo_session')
	]);

	$response = curl_exec($curl);
	curl_close($curl);

	$json = json_decode($response, true);
	if(!isset($json[$version])) return null;

	return $json[$version];
}

function generateImage($type, $api_key, $title) {
	switch(strtolower($type)) {
		case 'pexels': return connectPexels($api_key, $title);
		case 'pixabay': return connectPixabay($api_key, $title);
		case 'unsplash': return connectUnsplash($api_key, $title);
		default: return null;
	}
}

function connectPexels($apikey, $title) {
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.pexels.com/v1/search?per_page=1&'.http_build_query(['query' => $title]),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => ['Authorization: '.$apikey]
	));

	$response = curl_exec($curl);
	curl_close($curl);

	$json = json_decode($response, true);

	if(!isset($json['photos'])) {
		return null;
	}

	return $json['photos'][0]['src']['large'];
}

function connectPixabay($apikey, $title) {
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://pixabay.com/api/?client_id=$apikey&".http_build_query(['q' => $title])."&page=1",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
	));

	$response = curl_exec($curl);
	curl_close($curl);

	$json = json_decode($response, true);

	if(!isset($json['results'])) {
		return null;
	}

	return $json['results'][0]['urls']['thumb'];
}

function connectUnsplash($apikey, $title) {
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://pixabay.com/api/?key=$apikey&".http_build_query(['query' => $title])."&image_type=photo",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
	));

	$response = curl_exec($curl);
	curl_close($curl);

	$json = json_decode($response, true);

	if(!isset($json['hits'])) {
		return null;
	}

	return $json['hits'][0]['largeImageURL'];
}

function translateText($text) {
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://app.escribelo.ai/api/v1/tools/?origin=plugin&v=1.0.7',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => 'id=523c63b3-89da-4da3-bb6a-9f1df12fe44a&tone=Informal&q=1&fromLanguage=ES&toLanguage=EN&value1='.$text,
		CURLOPT_HTTPHEADER => ['Authorization: Bearer '.get_option('escribelo_session')],
	));

	$response = curl_exec($curl);
	curl_close($curl);

	if(!isset(json_decode($response)->choices)) {
		error_log('retried');
		sleep(10);
		return translateText($text);
	}

	return json_decode($response)->choices[0]->text;
}

function uploadImage($img_url) {
	require_once(ABSPATH.'wp-admin/includes/media.php');
	require_once(ABSPATH.'wp-admin/includes/file.php');
	require_once(ABSPATH.'wp-admin/includes/image.php');

	$html = media_sideload_image($img_url);
	$doc = new DOMDocument();
	$doc->loadHTML($html);
	$xpath = new DOMXPath($doc);
	return attachment_url_to_postid($xpath->evaluate("string(//img/@src)"));
}

function fixArticle($html) {
	$html = preg_replace('/<h1\b[^>]*>(.*?)<\/h1>/i', '', $html);
	$html = preg_replace('/<h1\b[^>]*>(.*?)<\/ h1>/i', '', $html);
	return $html;
}


function isJson($string) {
	json_decode($string);
	return json_last_error() === JSON_ERROR_NONE;
}