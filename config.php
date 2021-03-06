<?php

ini_set('display_errors', 'On');

// ######################################### 站点配置

/**
 * 适配域名
 */
define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST']);
define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST']);

// current theme
define('TEMPLATE_DIR', 'default');

// ######################################### 数据库配置

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
define('DB_NAME', 'sanks_wndwp');
// define('DB_NAME', 'wordpress_dev');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix = 'wp_';

/**
 * 数据库整理类型。如不确定请勿更改
 */
define('DB_COLLATE', '');

// #########################################

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/
 * WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'aaa-bbb-ccc-ddd');
define('SECURE_AUTH_KEY', 'aaa-bbb-ccc-ddd');
define('LOGGED_IN_KEY', 'aaa-bbb-ccc-ddd');
define('NONCE_KEY', 'aaa-bbb-ccc-ddd');
define('AUTH_SALT', 'aaa-bbb-ccc-ddd');
define('SECURE_AUTH_SALT', 'aaa-bbb-ccc-ddd');
define('LOGGED_IN_SALT', 'aaa-bbb-ccc-ddd');
define('NONCE_SALT', 'aaa-bbb-ccc-ddd');

/**#@-*/

// ######################################### Debug 配置
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', null);
// define('WP_DEBUG_LOG', true);
define('SAVEQUERIES', true);

// ######################################### 以下信息如无特殊需求，请勿修改
// 定义插件文件路径
define('ABSPATH', __DIR__ . DIRECTORY_SEPARATOR);

// 安装目录
define('DIR_NAME', basename(__DIR__));

define('WPINC', 'src/wp-core');

// 临时性代码：兼容插件
define('WND_PATH', ABSPATH . 'src');
define('WND_LANG_KEY', 'lang');

require ABSPATH . 'load.php';
