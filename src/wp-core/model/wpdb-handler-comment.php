<?php
namespace WP_Core\Model;

use Wnd\WPDB\WPDB_Row;
use WP_Core\Utility\Singleton_Trait;

/**
 *
 */
class WPDB_Handler_Comment extends WPDB_Row {

	protected $table_name          = 'comments';
	protected $object_name         = 'comment';
	protected $primary_id_column   = 'comment_ID';
	protected $required_columns    = ['comment_post_ID', 'comment_author', 'comment_content'];
	protected $object_cache_fields = ['comment_ID'];

	use Singleton_Trait;

	private function __construct() {
		parent::__construct();
	}

	protected function check_insert_data(array $data): array{
		return $data;
	}

	protected function check_update_data(array $data): array{
		return $data;
	}
}
