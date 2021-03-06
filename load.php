<?php
// 自动加载器
require ABSPATH . 'autoloader.php';

// init
require ABSPATH . WPINC . '/default-constants.php';
require ABSPATH . WPINC . '/load.php';
require ABSPATH . WPINC . '/repair.php';
require ABSPATH . WPINC . '/functions.php';
require ABSPATH . WPINC . '/class-wpdb.php';
require ABSPATH . WPINC . '/plugin.php';
require ABSPATH . WPINC . '/theme.php';
require ABSPATH . WPINC . '/default-hook.php';
require ABSPATH . WPINC . '/query.php';

require ABSPATH . WPINC . '/formatting.php';
require ABSPATH . WPINC . '/option.php';
require ABSPATH . WPINC . '/meta.php';
require ABSPATH . WPINC . '/taxonomy.php';
require ABSPATH . WPINC . '/user.php';
require ABSPATH . WPINC . '/post.php';
require ABSPATH . WPINC . '/term.php';
require ABSPATH . WPINC . '/comment.php';

require ABSPATH . WPINC . '/template.php';
require ABSPATH . WPINC . '/script.php';
require ABSPATH . WPINC . '/styles.php';
require ABSPATH . WPINC . '/link-template.php';

// pluggable
require ABSPATH . WPINC . '/pluggable.php';

// 计时
timer_start();

// shutdown
register_shutdown_function('shutdown_action_hook');

// 实例化数据库连接
$wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
$wpdb->set_prefix($table_prefix);

// 创建数据库依赖 wpdb
if (isset($_GET['install'])) {
	require ABSPATH . 'src/wp-core/admin/schema.php';
	require ABSPATH . 'src/wp-core/admin/dbDelta.php';

	dbDelta('all');
}

// Start the WordPress object cache, or an external object cache if the drop-in is present.
wp_start_object_cache();

// Taxonomy
create_initial_taxonomies();

// Post Type and Status
create_initial_post_types();

// Core Frontend（Wnd Frontend）
Wnd\Model\Wnd_Init::get_instance();

// current theme functions.php
if (file_exists(TEMPLATEPATH . '/functions.php')) {
	require TEMPLATEPATH . '/functions.php';
}

/**
 * Fires after WordPress has finished loading but before any headers are sent.
 *
 * Most of WP is loaded at this stage, and the user is authenticated. WP continues
 * to load on the {@see 'init'} hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once WP is loaded, use the {@see 'wp_loaded'} hook below.
 *
 * @since 1.5.0
 */
do_action('init');
