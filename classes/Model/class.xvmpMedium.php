<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class xvmpMedium
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpMedium extends xvmpObject {

	public static function getSelectedAsArray($obj_id) {
		$selected = xvmpSelectedMedia::getSelected($obj_id);
		$videos = array();
		foreach ($selected as $rec) {
			try {
				$item = self::getObjectAsArray($rec->getMid());
			} catch (xvmpException $e) {
				continue;
			}
			$item['visible'] = $rec->getVisible();
			$videos[] = $item;
		}
		return $videos;
	}

	public static function getFilteredAsArray(array $filter) {
		$response = xvmpRequest::getMedia($filter)->getResponseArray();
		if ($response['media']['count'] <= 1) {
			return array($response['media']['medium']);
		}
		return $response['media']['medium'];
	}


	public static function getObjectAsArray($id) {
		$response = xvmpRequest::getMedium($id)->getResponseArray();
		return $response['medium'];
	}

	public static function getAllAsArray() {
		$response = xvmpRequest::getMedia()->getResponseArray();
		return $response['media']['medium'];
	}

	public function update() {
		$params = array(
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'categories' => implode(',', $this->getCategories())
		);
		xvmpRequest::editMedium($this->getId(), $params);
	}

	public static function upload($video, $obj_id, $add_automatically, $notification) {
		global $ilUser;
		$response = xvmpRequest::uploadMedium($video);
		$medium = $response->getResponseArray()['medium'];

		if ($add_automatically) {
			xvmpSelectedMedia::addVideo($medium['mid'],$obj_id);
		}

		$uploaded_media = new xvmpUploadedMedia();
		$uploaded_media->setMid($medium['mid']);
		$uploaded_media->setNotification($notification);
		$uploaded_media->setUserId($ilUser->getId());
		$uploaded_media->create();
	}

	public static function deleteObject($mid) {
		xvmpRequest::deleteMedium($mid);
		xvmpSelectedMedia::deleteVideo($mid);
		if ($uploaded_media = xvmpUploadedMedia::find($mid)) {
			$uploaded_media->delete();
		}
	}

	/**
	 * @var int
	 */
	protected $mid;
	/**
	 * @var int
	 */
	protected $uid;
	/**
	 * @var String
	 */
	protected $username;
	/**
	 * @var String
	 */
	protected $mediakey;
	/**
	 * @var String
	 */
	protected $mediatype;
	/**
	 * @var String
	 */
	protected $mediasubtype;
	/**
	 * @var String
	 */
	protected $published;
	/**
	 * @var String
	 */
	protected $status;
	/**
	 * @var bool
	 */
	protected $featured;
	/**
	 * @var String
	 */
	protected $culture;
	/**
	 * @var array
	 */
	protected $properties;
	/**
	 * @var String
	 */
	protected $title;
	/**
	 * @var String
	 */
	protected $description;
	/**
	 * @var int
	 */
	protected $duration;
	/**
	 * @var String
	 */
	protected $thumbnail;
	/**
	 * @var String
	 */
	protected $embed_code;
	/**
	 * @var array
	 */
	protected $medium;
	/**
	 * @var String
	 */
	protected $source;
	/**
	 * @var String
	 */
	protected $meta_title;
	/**
	 * @var String
	 */
	protected $meta_description;
	/**
	 * @var String
	 */
	protected $meta_keywords;
	/**
	 * @var String
	 */
	protected $meta_author;
	/**
	 * @var String
	 */
	protected $meta_copyright;
	/**
	 * @var int
	 */
	protected $sum_rating;
	/**
	 * @var int
	 */
	protected $count_views;
	/**
	 * @var int
	 */
	protected $count_rating;
	/**
	 * @var int
	 */
	protected $count_favorites;
	/**
	 * @var int
	 */
	protected $count_comments;
	/**
	 * @var int
	 */
	protected $count_flags;
	/**
	 * @var String
	 */
	protected $created_at;
	/**
	 * @var String
	 */
	protected $updated_at;
	/**
	 * @var array
	 */
	protected $categories;
	/**
	 * @var array
	 */
	protected $tags;


	/**
	 * @return int
	 */
	public function getMid() {
		return $this->mid;
	}


	/**
	 * @param int $mid
	 */
	public function setMid($mid) {
		$this->mid = $mid;
	}


	/**
	 * @return int
	 */
	public function getUid() {
		return $this->uid;
	}


	/**
	 * @param int $uid
	 */
	public function setUid($uid) {
		$this->uid = $uid;
	}


	/**
	 * @return String
	 */
	public function getUsername() {
		return $this->username;
	}


	/**
	 * @param String $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}


	/**
	 * @return String
	 */
	public function getMediakey() {
		return $this->mediakey;
	}


	/**
	 * @param String $mediakey
	 */
	public function setMediakey($mediakey) {
		$this->mediakey = $mediakey;
	}


	/**
	 * @return String
	 */
	public function getMediatype() {
		return $this->mediatype;
	}


	/**
	 * @param String $mediatype
	 */
	public function setMediatype($mediatype) {
		$this->mediatype = $mediatype;
	}


	/**
	 * @return String
	 */
	public function getMediasubtype() {
		return $this->mediasubtype;
	}


	/**
	 * @param String $mediasubtype
	 */
	public function setMediasubtype($mediasubtype) {
		$this->mediasubtype = $mediasubtype;
	}


	/**
	 * @return String
	 */
	public function getPublished() {
		return $this->published;
	}


	/**
	 * @param String $published
	 */
	public function setPublished($published) {
		$this->published = $published;
	}


	/**
	 * @return String
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @param String $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}


	/**
	 * @return bool
	 */
	public function isFeatured() {
		return $this->featured;
	}


	/**
	 * @param bool $featured
	 */
	public function setFeatured($featured) {
		$this->featured = $featured;
	}


	/**
	 * @return String
	 */
	public function getCulture() {
		return $this->culture;
	}


	/**
	 * @param String $culture
	 */
	public function setCulture($culture) {
		$this->culture = $culture;
	}


	/**
	 * @return array
	 */
	public function getProperties() {
		return $this->properties;
	}


	/**
	 * @param array $properties
	 */
	public function setProperties($properties) {
		$this->properties = $properties;
	}


	/**
	 * @return String
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param String $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return String
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param String $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return int
	 */
	public function getDuration() {
		return $this->duration;
	}


	/**
	 * @param int $duration
	 */
	public function setDuration($duration) {
		$this->duration = $duration;
	}


	/**
	 * @return String
	 */
	public function getThumbnail() {
		return $this->thumbnail;
	}


	/**
	 * @param String $thumbnail
	 */
	public function setThumbnail($thumbnail) {
		$this->thumbnail = $thumbnail;
	}


	/**
	 * @return String
	 */
	public function getEmbedCode() {
		return $this->embed_code;
	}


	/**
	 * @param String $embed_code
	 */
	public function setEmbedCode($embed_code) {
		$this->embed_code = $embed_code;
	}


	/**
	 * @return array
	 */
	public function getMedium() {
		return $this->medium;
	}


	/**
	 * @param array $medium
	 */
	public function setMedium($medium) {
		$this->medium = $medium;
	}


	/**
	 * @return String
	 */
	public function getSource() {
		return $this->source;
	}


	/**
	 * @param String $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}


	/**
	 * @return String
	 */
	public function getMetaTitle() {
		return $this->meta_title;
	}


	/**
	 * @param String $meta_title
	 */
	public function setMetaTitle($meta_title) {
		$this->meta_title = $meta_title;
	}


	/**
	 * @return String
	 */
	public function getMetaDescription() {
		return $this->meta_description;
	}


	/**
	 * @param String $meta_description
	 */
	public function setMetaDescription($meta_description) {
		$this->meta_description = $meta_description;
	}


	/**
	 * @return String
	 */
	public function getMetaKeywords() {
		return $this->meta_keywords;
	}


	/**
	 * @param String $meta_keywords
	 */
	public function setMetaKeywords($meta_keywords) {
		$this->meta_keywords = $meta_keywords;
	}


	/**
	 * @return String
	 */
	public function getMetaAuthor() {
		return $this->meta_author;
	}


	/**
	 * @param String $meta_author
	 */
	public function setMetaAuthor($meta_author) {
		$this->meta_author = $meta_author;
	}


	/**
	 * @return String
	 */
	public function getMetaCopyright() {
		return $this->meta_copyright;
	}


	/**
	 * @param String $meta_copyright
	 */
	public function setMetaCopyright($meta_copyright) {
		$this->meta_copyright = $meta_copyright;
	}


	/**
	 * @return int
	 */
	public function getSumRating() {
		return $this->sum_rating;
	}


	/**
	 * @param int $sum_rating
	 */
	public function setSumRating($sum_rating) {
		$this->sum_rating = $sum_rating;
	}


	/**
	 * @return int
	 */
	public function getCountViews() {
		return $this->count_views;
	}


	/**
	 * @param int $count_views
	 */
	public function setCountViews($count_views) {
		$this->count_views = $count_views;
	}


	/**
	 * @return int
	 */
	public function getCountRating() {
		return $this->count_rating;
	}


	/**
	 * @param int $count_rating
	 */
	public function setCountRating($count_rating) {
		$this->count_rating = $count_rating;
	}


	/**
	 * @return int
	 */
	public function getCountFavorites() {
		return $this->count_favorites;
	}


	/**
	 * @param int $count_favorites
	 */
	public function setCountFavorites($count_favorites) {
		$this->count_favorites = $count_favorites;
	}


	/**
	 * @return int
	 */
	public function getCountComments() {
		return $this->count_comments;
	}


	/**
	 * @param int $count_comments
	 */
	public function setCountComments($count_comments) {
		$this->count_comments = $count_comments;
	}


	/**
	 * @return int
	 */
	public function getCountFlags() {
		return $this->count_flags;
	}


	/**
	 * @param int $count_flags
	 */
	public function setCountFlags($count_flags) {
		$this->count_flags = $count_flags;
	}


	/**
	 * @return String
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}


	/**
	 * @param String $created_at
	 */
	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}


	/**
	 * @return String
	 */
	public function getUpdatedAt() {
		return $this->updated_at;
	}


	/**
	 * @param String $updated_at
	 */
	public function setUpdatedAt($updated_at) {
		$this->updated_at = $updated_at;
	}


	/**
	 * @return array
	 */
	public function getCategories() {
		return $this->categories;
	}


	/**
	 * @param array $categories
	 */
	public function setCategories($categories) {
		$this->categories = $categories;
	}


	/**
	 * @return array
	 */
	public function getTags() {
		return $this->tags;
	}


	/**
	 * @param array $tags
	 */
	public function setTags($tags) {
		$this->tags = $tags;
	}
}