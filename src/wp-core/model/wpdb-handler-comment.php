<?php
namespace WP_Core\Model;

/**
 *
 */
class WPDB_Handler_Comment extends WPDB_Handler_Abstract {

	protected $table_name          = 'comments';
	protected $object_name         = 'comment';
	protected $primary_id_column   = 'comment_ID';
	protected $required_columns    = ['comment_post_ID', 'comment_author', 'comment_content'];
	protected $object_cache_fields = ['comment_ID'];

	protected function check_insert_data(array $data) {}

	protected function check_update_data(array $data) {}
}