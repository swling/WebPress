<?php
namespace Wnd\Query;

/**
 * 插件管理菜单
 * @since 0.9.11
 */
class Wnd_Menus extends Wnd_Query {

	protected static function query(array $args = []): array{
		$defaults = [
			'in_side' => false, // 是否为侧边栏
			'expand'  => true, // 是否展开首个子菜单
		];
		$args = wp_parse_args($args, $defaults);

		// 拓展菜单 API
		if (!is_user_logged_in()) {
			$default_menus = [];
		} elseif (wnd_is_manager()) {
			$default_menus = static::build_manager_menus();
		} else {
			$default_menus = static::build_user_menus();
		}

		$menus[]            = $default_menus;
		$menus              = apply_filters('wnd_menus', $menus, $args);
		$menus[0]['expand'] = $args['expand'];
		return $menus;
	}

	protected static function build_user_menus(): array{

		$menus = [
			'label'  => __('控制板', 'wnd'),
			'expand' => false, // 是否强制展开
			'items'  => [
				['title' => __('概览', 'wnd'), 'href' => static::get_front_page_url() . '#'],
				['title' => __('内容', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_user_posts_panel'],
				['title' => __('财务', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_user_finance_panel'],
				['title' => __('资料', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_profile_form'],
				['title' => __('账户', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_account_form'],
				['title' => __('消息', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_mail_box'],
			],
		];

		return $menus;
	}

	protected static function build_manager_menus(): array{
		$menus = [
			'label'  => __('控制板', 'wnd'),
			'expand' => false, // 是否强制展开
			'items'  => [
				['title' => __('概览', 'wnd'), 'href' => static::get_front_page_url() . '#'],
				['title' => __('审核', 'wnd'), 'href' => static::get_front_page_url() . '#admin/wnd_admin_posts_panel'],
				['title' => __('统计', 'wnd'), 'href' => static::get_front_page_url() . '#admin/wnd_finance_stats'],
				['title' => __('订单', 'wnd'), 'href' => static::get_front_page_url() . '#admin/wnd_finance_list'],
				['title' => __('用户', 'wnd'), 'href' => static::get_front_page_url() . '#admin/wnd_users_list'],
				['title' => __('内容', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_user_posts_panel'],
				['title' => __('财务', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_user_finance_panel'],
				['title' => __('资料', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_profile_form'],
				['title' => __('账户', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_account_form'],
				['title' => __('消息', 'wnd'), 'href' => static::get_front_page_url() . '#user/wnd_mail_box'],
			],
		];

		return $menus;
	}

	/**
	 * 前端页面 URL
	 */
	protected static function get_front_page_url(): string {
		return wnd_get_front_page_url();
	}
}
