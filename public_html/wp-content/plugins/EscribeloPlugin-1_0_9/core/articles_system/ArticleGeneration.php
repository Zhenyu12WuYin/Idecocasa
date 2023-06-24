<?php

require_once('Article.php');
require_once('ArticleConfig.php');

function startGenerationTasks(array $items) {
	$array = get_option('escribelo_tasks') ?? array();
	if(!is_array($array)) $array = array();

	foreach ($items as $item) {
		$title = $item['article']->getTitle();
		if(strlen($title) == 0) continue;

		$duplicate = null;
		foreach($array as $existingItem) {
			if ($existingItem['article']->getTitle() == $title) {
				$duplicate = true;
				break;
			}
		}

		if($duplicate == null) $array[] = $item;
	}

	update_option('escribelo_tasks', $array);
}

function createArticleTask() {
	if(get_option('escribelo_session') == null) return;
	$array = get_option('escribelo_tasks');

	if(count($array) == 0) return;

	require_once(plugin_dir_path(__FILE__).'../utils.php');
	require_once(plugin_dir_path(__FILE__).'Article.php');
	require_once(plugin_dir_path(__FILE__).'ArticleConfig.php');
	require_once(plugin_dir_path(__FILE__).'ArticleGeneration.php');

	$itemSelected = null;
	foreach($array as $item) {
		$articleConfig = $item['config'];
		if(time() > $articleConfig->getTime()) {
			$itemSelected = $item;
			break;
		}
	}

	if($itemSelected == null) {
		return;
	}

	if(!verifyLogin()) {
		update_option('escribelo_tasks', []);
		return;
	}

	$array_updated = [];
	foreach($array as $item) {
		if($item != $itemSelected) $array_updated[] = $item;
	}
	update_option('escribelo_tasks', $array_updated); // Remove the item selected

	$article = $itemSelected['article'];
	$articleConfig = $itemSelected['config'];

	$articleHTML = generateArticle($article, $articleConfig);

	if($articleHTML != null && $articleHTML != -1) {
		$articleHTML = fixArticle($articleHTML);
		$title = $article->getTitle();
		$category = $articleConfig->getCategoryId();
		$contentHTML = $articleHTML;
		$status = $articleConfig->getStatus();

		$my_post = array(
			'post_title'    => $title,
			'post_content'  => $contentHTML,
			'post_status'   => $status,
			'post_author'   => get_users(array('fields' => 'ID'))[0],
			'post_category' => array($category)
		);

		$post = wp_insert_post($my_post);

		if($articleConfig->getBankImagesApiKey() != null) {
			$img = generateImage($articleConfig->getBankImagesType(), $articleConfig->getBankImagesApiKey(), translateText($title));
			set_post_thumbnail($post, uploadImage($img));
		}
	} else if($articleHTML == -1) { //in case there is an error generating, move all the articles for be generated to the next day, so there is not 32197823179823198231 connections
		$array_updated = [];
		foreach($array as $item) {
			$articleConfig = $item['config'];
			if(time() > $articleConfig->getTime()) {
				$articleConfig->setTime(time() + 86400);
				$array_updated[] = $item;
			} else $array_updated[] = $item;
		}
		update_option('escribelo_tasks', $array_updated);
	}
}