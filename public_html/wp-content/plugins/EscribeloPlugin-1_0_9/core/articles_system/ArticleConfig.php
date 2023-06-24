<?php

class ArticleConfig {
	public $categoryId, $language, $status, $time, $bankImagesType, $bankImagesApiKey;

	public function __construct(int $categoryId, string $language, string $status, int $time, string $bankImagesType, $bankImagesApiKey) {
		$this->categoryId = $categoryId;
		$this->language = $language;
		$this->status = $status;
		$this->time = $time;
		$this->bankImagesType = $bankImagesType;
		$this->bankImagesApiKey = $bankImagesApiKey;
	}

	public function getCategoryId(): int {
		return $this->categoryId;
	}

	public function getLanguage(): string {
		return $this->language;
	}

	public function getStatus(): string {
		return $this->status;
	}

	public function getTime(): int {
		return $this->time;
	}

	public function getBankImagesType() : string {
		return $this->bankImagesType;
	}

	public function getBankImagesApiKey() {
		return $this->bankImagesApiKey;
	}

	public function setTime(int $time) {
		$this->time = $time;
	}

}