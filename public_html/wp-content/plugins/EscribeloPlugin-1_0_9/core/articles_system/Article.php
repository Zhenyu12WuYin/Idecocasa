<?php

class Article {
	public $title, $keywords, $type = null;

	public function __construct(string $title, string $keywords, string $type) {
		$this->title = $title;
		$this->keywords = $keywords;
		$this->type = $type;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getKeywords(): string {
		return $this->keywords;
	}

	public function getType(): string {
		return $this->type;
	}

}