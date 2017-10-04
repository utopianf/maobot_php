<?php
/**
 *
 *
 * ニコニコ検索クラス
 *
 * @author Yuki-Yamamoto
 * 
 * $nico = new NicoSearch('アプリケーション名');
 * $response = query(
 * 	【対象サービス：NicoSearch::VIDEO】,
 * 	【検索キーワード：'初音ミク'】,
 * 	【検索対象：'array(NicoSearch::TAG)'】,
 * 	【並べ替えフィールド名：NicoSearch::UPLOAD_TIME】,
 * 	【並べ替え順序：NicoSearch::DESC】,
 * 	【データ取得の起点：0〜1600】,
 * 	【データ取得件数：1〜100】
 * );
 *
 */
class NicoSearch {
	/**
	 * 定数
	 */
	// エンドポイント
    const ENDPOINT = 'http://api.search.nicovideo.jp/api/';
 	
    //　対象サービス
    const VIDEO = 'video';
    const LIVE = 'live';
    const ILLUST = 'illust';
    const MANGA = 'manga';
    const BOOK = 'book';
    const CHANNEl = 'channel';
    const CHANNELARTICLE = 'channelarticle';
    const NEWS = 'news';

    // 検索対象
    const TITLE = 'title';
    const TAG = 'tags';
    const DESCRIPTION = 'description';
    const BODY = 'body'; // ニュース専用
    const CAPTION = 'caption'; //ニュース専用

    // 並べ替えフィールド名
    const COMMENT_TIME = 'last_comment_time';
    const VIEW_COUNTER = 'view_counter';
    const UPLOAD_TIME = 'start_time';
    const MYLIST_COUNTER = 'mylist_counter';
    const COMMENT_COUNTER = 'comment_counter';
    const MOVIE_LENGTH = 'length_seconds';

    // 並べ替え順序
    const DESC = 'desc';
    const ASC = 'asc';

    // 返却される項目
    const JOIN = array(
		"cmsid", 
		"title", 
		"tags",
		"thumbnail_url",
		"start_time",
		"view_counter",
		"comment_counter",
		"mylist_counter",
		"length_seconds"
	);

	/**
	 * プロパティ
	 */
	// 対象サービス
	private $service;

	// 検索キーワード
	private $query;

	// 検索対象
	private $feild;

	// 並べ替えフィールド名
	private $sort;

	// 並べ替え順序
	private $order;

	// レスポンス取得開始位置
	private $res_start;

	// レスポンスの数
	private $res_size;

	// アプリケーション名
	private $app_name;

	/**
	 * メソッド
	 */
	// コンストラクタ
	function __construct($app_name = 'NicoSearch') {
		$this->app_name = $app_name;
	}

	// 送信用クエリの設定
	public function query($service, $query, $feild, 
		$sort, $order, $from, $size) {
		// クエリをプロパティに格納
		$this->service = $service;
		$this->query = $query;
		$this->feild = $feild;
		$this->sort = $sort;
		$this->order = $order;
		$this->res_start = $from;
		$this->res_size = $size;

		// APIデータ取得
		$response = $this->get_api();
		return $response;
	}

	// APIデータの取得
	private function get_api() {
		// POSTデータ
		$post_data = array(
			"query" => $this->query,
			"service" => array($this->service),
			"search" => $this->feild,
			"join" => NicoSearch::JOIN,
			"sort_by" => $this->sort,
			"order" => $this->order,
			"from" => $this->res_start,
			"size" => $this->res_size,
			"issuer" => $this->app_name,
			"reason" =>"ma10"
		);

		// コンテキストの作成
		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-type: application/json; charset=UTF-8',
					'content' => json_encode($post_data)
				)
			)
		);

		$api_data = file_get_contents(NicoSearch::ENDPOINT, false, $context);
		$json = $this->json_parse($api_data);
		return $json;
	}

	// 複数のJSONを単一化
	private function json_parse($api_data) {
		$data = explode("\n", $api_data);

		if(!strpos($data[0], 'errid')) {
			// 複数のJSONをまとめてJSONにする
			$json = array();
			$num = 0;
			for($i = 2; $i < count($data)-2; $i++) {
				$obj = json_decode($data[$i]);
				$val = $obj->values;
				for($n = 0; $n < count($val); $n++) {
					$json[] = array(
						"cmsid" => $val[$n]->cmsid,
						"title" => $val[$n]->title,
						"view_counter" => $val[$n]->view_counter,
						"tags" => $val[$n]->tags,
						"start_time" => $val[$n]->start_time,
						"thumbnail_url" => $val[$n]->thumbnail_url,
						"comment_counter" => $val[$n]->comment_counter,
						"mylist_counter" => $val[$n]->mylist_counter,
						"length_seconds" => $val[$n]->length_seconds
					);
				}
			}
			return json_encode($json);
		} else {
			return false;
		}
	}
}
