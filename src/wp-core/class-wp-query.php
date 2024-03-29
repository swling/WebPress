<?php
/**
 * Query API: WP_Query class
 *
 */
class WP_Query extends WP_Query_Abstract {

	protected $table_name        = 'posts';
	protected $primary_id_column = 'ID';
	protected $date_column       = 'post_date';
	protected $meta_type         = 'post';
	protected $int_column        = ['ID', 'post_author', 'post_parent'];
	protected $str_column        = ['post_name', 'post_type', 'post_status', 'post_mime_type'];
	protected $search_column     = ['post_title'];
	protected $default_order_by  = 'post_date';

	/**
	 * Index of the current item in the loop.
	 *
	 * @since 1.5.0
	 * @var int
	 */
	public $current_post = -1;

	/**
	 * Whether the loop has started and the caller is in the loop.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	public $in_the_loop = false;

	/**
	 * The current post.
	 *
	 * This property does not get populated when the `fields` argument is set to
	 * `ids` or `id=>parent`.
	 *
	 * @since 1.5.0
	 * @var WP_Post|null
	 */
	public $post;

	/**
	 * Holds the data for a single object that is queried.
	 *
	 * Holds the contents of a post, page, category, attachment.
	 *
	 * @since 1.5.0
	 * @var WP_Term|WP_Post_Type|WP_Post|WP_User|null
	 */
	public $queried_object;

	/**
	 * The ID of the queried object.
	 *
	 * @since 1.5.0
	 * @var int
	 */
	public $queried_object_id;

	/**
	 * Default values for query vars.
	 *
	 * @var array
	 */
	protected static $query_var_defaults = [
		'ID'            => '',
		'post_name'     => '',
		'post_parent'   => '',
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'tax_query'     => [],
		'meta_query'    => [],
		'date_query'    => [],
		'fields'        => '',
		'number'        => 10,
		'search'        => '',
		'no_found_rows' => false,
		'orderby'       => '',
		'order'         => 'DESC',
	];

	/**
	 * Parse a query string and set query type booleans.
	 *
	 * @since 1.5.0
	 * @since 4.2.0 Introduced the ability to order by specific clauses of a `$meta_query`, by passing the clause's
	 *              array key to `$orderby`.
	 * @since 4.4.0 Introduced `$post_name__in` and `$title` parameters. `$s` was updated to support excluded
	 *              search terms, by prepending a hyphen.
	 * @since 4.5.0 Removed the `$comments_popup` parameter.
	 *              Introduced the `$comment_status` and `$ping_status` parameters.
	 *              Introduced `RAND(x)` syntax for `$orderby`, which allows an integer seed value to random sorts.
	 * @since 4.6.0 Added 'post_name__in' support for `$orderby`. Introduced the `$lazy_load_term_meta` argument.
	 * @since 4.9.0 Introduced the `$comment_count` parameter.
	 * @since 5.1.0 Introduced the `$meta_compare_key` parameter.
	 * @since 5.3.0 Introduced the `$meta_type_key` parameter.
	 *
	 * @param string|array $query {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type int             $attachment_id           Attachment post ID. Used for 'attachment' post_type.
	 *     @type int|string      $author                  Author ID, or comma-separated list of IDs.
	 *     @type string          $author_name             User 'user_nicename'.
	 *     @type int[]           $author__in              An array of author IDs to query from.
	 *     @type int[]           $author__not_in          An array of author IDs not to query from.
	 *     @type bool            $cache_results           Whether to cache post information. Default true.
	 *     @type int|string      $cat                     Category ID or comma-separated list of IDs (this or any children).
	 *     @type int[]           $category__and           An array of category IDs (AND in).
	 *     @type int[]           $category__in            An array of category IDs (OR in, no children).
	 *     @type int[]           $category__not_in        An array of category IDs (NOT in).
	 *     @type string          $category_name           Use category slug (not name, this or any children).
	 *     @type array|int       $comment_count           Filter results by comment count. Provide an integer to match
	 *                                                    comment count exactly. Provide an array with integer 'value'
	 *                                                    and 'compare' operator ('=', '!=', '>', '>=', '<', '<=' ) to
	 *                                                    compare against comment_count in a specific way.
	 *     @type string          $comment_status          Comment status.
	 *     @type int             $comments_per_page       The number of comments to return per page.
	 *                                                    Default 'comments_per_page' option.
	 *     @type array           $date_query              An associative array of WP_Date_Query arguments.
	 *                                                    See WP_Date_Query::__construct().
	 *     @type int             $day                     Day of the month. Default empty. Accepts numbers 1-31.
	 *     @type bool            $exact                   Whether to search by exact keyword. Default false.
	 *     @type string          $fields                  Post fields to query for. Accepts:
	 *                                                    - '' Returns an array of complete post objects (`WP_Post[]`).
	 *                                                    - 'ids' Returns an array of post IDs (`int[]`).
	 *                                                    - 'id=>parent' Returns an associative array of parent post IDs,
	 *                                                      keyed by post ID (`int[]`).
	 *                                                    Default ''.
	 *     @type int             $hour                    Hour of the day. Default empty. Accepts numbers 0-23.
	 *     @type int|bool        $ignore_sticky_posts     Whether to ignore sticky posts or not. Setting this to false
	 *                                                    excludes stickies from 'post__in'. Accepts 1|true, 0|false.
	 *                                                    Default false.
	 *     @type int             $m                       Combination YearMonth. Accepts any four-digit year and month
	 *                                                    numbers 1-12. Default empty.
	 *     @type string|string[] $meta_key                Meta key or keys to filter by.
	 *     @type string|string[] $meta_value              Meta value or values to filter by.
	 *     @type string          $meta_compare            MySQL operator used for comparing the meta value.
	 *                                                    See WP_Meta_Query::__construct for accepted values and default value.
	 *     @type string          $meta_compare_key        MySQL operator used for comparing the meta key.
	 *                                                    See WP_Meta_Query::__construct for accepted values and default value.
	 *     @type string          $meta_type               MySQL data type that the meta_value column will be CAST to for comparisons.
	 *                                                    See WP_Meta_Query::__construct for accepted values and default value.
	 *     @type string          $meta_type_key           MySQL data type that the meta_key column will be CAST to for comparisons.
	 *                                                    See WP_Meta_Query::__construct for accepted values and default value.
	 *     @type array           $meta_query              An associative array of WP_Meta_Query arguments.
	 *                                                    See WP_Meta_Query::__construct for accepted values.
	 *     @type int             $menu_order              The menu order of the posts.
	 *     @type int             $minute                  Minute of the hour. Default empty. Accepts numbers 0-59.
	 *     @type int             $monthnum                The two-digit month. Default empty. Accepts numbers 1-12.
	 *     @type string          $name                    Post slug.
	 *     @type bool            $nopaging                Show all posts (true) or paginate (false). Default false.
	 *     @type bool            $no_found_rows           Whether to skip counting the total rows found. Enabling can improve
	 *                                                    performance. Default false.
	 *     @type int             $offset                  The number of posts to offset before retrieval.
	 *     @type string          $order                   Designates ascending or descending order of posts. Default 'DESC'.
	 *                                                    Accepts 'ASC', 'DESC'.
	 *     @type string|array    $orderby                 Sort retrieved posts by parameter. One or more options may be passed.
	 *                                                    To use 'meta_value', or 'meta_value_num', 'meta_key=keyname' must be
	 *                                                    also be defined. To sort by a specific `$meta_query` clause, use that
	 *                                                    clause's array key. Accepts:
	 *                                                    - 'none'
	 *                                                    - 'name'
	 *                                                    - 'author'
	 *                                                    - 'date'
	 *                                                    - 'title'
	 *                                                    - 'modified'
	 *                                                    - 'menu_order'
	 *                                                    - 'parent'
	 *                                                    - 'ID'
	 *                                                    - 'rand'
	 *                                                    - 'relevance'
	 *                                                    - 'RAND(x)' (where 'x' is an integer seed value)
	 *                                                    - 'comment_count'
	 *                                                    - 'meta_value'
	 *                                                    - 'meta_value_num'
	 *                                                    - 'post__in'
	 *                                                    - 'post_name__in'
	 *                                                    - 'post_parent__in'
	 *                                                    - The array keys of `$meta_query`.
	 *                                                    Default is 'date', except when a search is being performed, when
	 *                                                    the default is 'relevance'.
	 *     @type int             $p                       Post ID.
	 *     @type int             $page                    Show the number of posts that would show up on page X of a
	 *                                                    static front page.
	 *     @type int             $paged                   The number of the current page.
	 *     @type int             $page_id                 Page ID.
	 *     @type string          $pagename                Page slug.
	 *     @type string          $perm                    Show posts if user has the appropriate capability.
	 *     @type string          $ping_status             Ping status.
	 *     @type int[]           $post__in                An array of post IDs to retrieve, sticky posts will be included.
	 *     @type int[]           $post__not_in            An array of post IDs not to retrieve. Note: a string of comma-
	 *                                                    separated IDs will NOT work.
	 *     @type string          $post_mime_type          The mime type of the post. Used for 'attachment' post_type.
	 *     @type string[]        $post_name__in           An array of post slugs that results must match.
	 *     @type int             $post_parent             Page ID to retrieve child pages for. Use 0 to only retrieve
	 *                                                    top-level pages.
	 *     @type int[]           $post_parent__in         An array containing parent page IDs to query child pages from.
	 *     @type int[]           $post_parent__not_in     An array containing parent page IDs not to query child pages from.
	 *     @type string|string[] $post_type               A post type slug (string) or array of post type slugs.
	 *                                                    Default 'any' if using 'tax_query'.
	 *     @type string|string[] $post_status             A post status (string) or array of post statuses.
	 *     @type int             $posts_per_page          The number of posts to query for. Use -1 to request all posts.
	 *     @type int             $posts_per_archive_page  The number of posts to query for by archive page. Overrides
	 *                                                    'posts_per_page' when is_archive(), or is_search() are true.
	 *     @type string          $s                       Search keyword(s). Prepending a term with a hyphen will
	 *                                                    exclude posts matching that term. Eg, 'pillow -sofa' will
	 *                                                    return posts containing 'pillow' but not 'sofa'. The
	 *                                                    character used for exclusion can be modified using the
	 *                                                    the 'wp_query_search_exclusion_prefix' filter.
	 *     @type int             $second                  Second of the minute. Default empty. Accepts numbers 0-59.
	 *     @type bool            $sentence                Whether to search by phrase. Default false.
	 *     @type bool            $suppress_filters        Whether to suppress filters. Default false.
	 *     @type string          $tag                     Tag slug. Comma-separated (either), Plus-separated (all).
	 *     @type int[]           $tag__and                An array of tag IDs (AND in).
	 *     @type int[]           $tag__in                 An array of tag IDs (OR in).
	 *     @type int[]           $tag__not_in             An array of tag IDs (NOT in).
	 *     @type int             $tag_id                  Tag id or comma-separated list of IDs.
	 *     @type string[]        $tag_slug__and           An array of tag slugs (AND in).
	 *     @type string[]        $tag_slug__in            An array of tag slugs (OR in). unless 'ignore_sticky_posts' is
	 *                                                    true. Note: a string of comma-separated IDs will NOT work.
	 *     @type array           $tax_query               An associative array of WP_Tax_Query arguments.
	 *                                                    See WP_Tax_Query->__construct().
	 *     @type string          $title                   Post title.
	 *     @type bool            $update_post_meta_cache  Whether to update the post meta cache. Default true.
	 *     @type bool            $update_post_term_cache  Whether to update the post term cache. Default true.
	 *     @type bool            $lazy_load_term_meta     Whether to lazy-load term meta. Setting to false will
	 *                                                    disable cache priming for term meta, so that each
	 *                                                    get_term_meta() call will hit the database.
	 *                                                    Defaults to the value of `$update_post_term_cache`.
	 *     @type int             $w                       The week number of the year. Default empty. Accepts numbers 0-53.
	 *     @type int             $year                    The four-digit year. Default empty. Accepts any four-digit year.
	 * }
	 */
	public function parse_query(array $query) {
		// Resets query flags to false.
		$this->init_query_flags();

		// 兼容WP参数
		if (isset($query['posts_per_page'])) {
			$query['number'] = $query['posts_per_page'];
			unset($query['posts_per_page']);
		}

		$this->query      = $query;
		$this->query_vars = array_merge(static::$query_var_defaults, $query);
		$qv               = $this->query_vars;

		if ($qv['tax_query']) {
			$this->is_archive = true;
			$this->is_tax     = true;
		} elseif ('' !== $qv['post_name'] or $qv['ID']) {
			if ('page' == $qv['post_type']) {
				$this->is_page = true;
			} else {
				$this->is_single = true;
			}
		} elseif (!empty($qv['post_type']) && !is_array($qv['post_type'])) {
			$post_type_obj = get_post_type_object($qv['post_type']);
			if (!empty($post_type_obj->has_archive)) {
				$this->is_post_type_archive = true;
				$this->is_archive           = true;
			}
		}

		$this->is_singular = $this->is_single || $this->is_page;

		if (!$this->is_singular and !$this->is_archive and !$this->is_search and !$this->is_404) {
			$this->is_home = true;
		}
	}

	/**
	 * Resets query flags to false.
	 *
	 * The query flags are what page info WordPress was able to figure out.
	 *
	 * @since 2.0.0
	 */
	protected function init_query_flags() {
		$this->is_single            = false;
		$this->is_preview           = false;
		$this->is_page              = false;
		$this->is_archive           = false;
		$this->is_date              = false;
		$this->is_year              = false;
		$this->is_month             = false;
		$this->is_day               = false;
		$this->is_time              = false;
		$this->is_author            = false;
		$this->is_category          = false;
		$this->is_tag               = false;
		$this->is_tax               = false;
		$this->is_search            = false;
		$this->is_feed              = false;
		$this->is_comment_feed      = false;
		$this->is_home              = false;
		$this->is_404               = false;
		$this->is_paged             = false;
		$this->is_admin             = false;
		$this->is_attachment        = false;
		$this->is_singular          = false;
		$this->is_posts_page        = false;
		$this->is_post_type_archive = false;
	}

	public function get_results() {
		// excute sql query
		parent::get_results($this->request);
		if ($this->results) {
			$this->post = reset($this->results);
		}

		return $this->results;
	}

	protected static function instantiate_item(object $item): object {
		return new WP_Post($item);
	}

	/**
	 * Retrieves the currently queried object.
	 *
	 * If queried object is not set, then the queried object will be set from
	 * the category, tag, taxonomy, posts page, single post, page, or author
	 * query variable. After it is set up, it will be returned.
	 *
	 * @since 1.5.0
	 *
	 * @return WP_Term|WP_Post_Type|WP_Post|WP_User|null The queried object.
	 */
	public function get_queried_object() {
		if ($this->queried_object) {
			return $this->queried_object;
		}

		$this->queried_object    = null;
		$this->queried_object_id = null;

		if ($this->is_tax) {
			// For other tax queries, grab the first term from the first clause.
			if (!empty($this->tax_query->queried_terms)) {
				$queried_taxonomies = array_keys($this->tax_query->queried_terms);
				$matched_taxonomy   = reset($queried_taxonomies);
				$query              = $this->tax_query->queried_terms[$matched_taxonomy];

				if (!empty($query['terms'])) {
					if ('term_id' === $query['field']) {
						$term = get_term(reset($query['terms']));
					} else {
						$term = get_term_by($query['field'], reset($query['terms']), $matched_taxonomy);
					}
				}
			}

			if (!empty($term) && !is_wp_error($term)) {
				$this->queried_object    = $term;
				$this->queried_object_id = (int) $term->term_id;
			}
		} elseif ($this->is_post_type_archive) {
			$post_type = $this->get('post_type');
			if (is_array($post_type)) {
				$post_type = reset($post_type);
			}
			$this->queried_object = get_post_type_object($post_type);
		} elseif ($this->is_singular && !empty($this->post)) {
			$this->queried_object    = $this->post;
			$this->queried_object_id = (int) $this->post->ID;
		} elseif ($this->is_author) {
			$this->queried_object_id = (int) $this->get('author');
			$this->queried_object    = get_userdata($this->queried_object_id);
		}

		return $this->queried_object;
	}

	/**
	 * Retrieves the ID of the currently queried object.
	 *
	 * @since 1.5.0
	 *
	 * @return int
	 */
	public function get_queried_object_id() {
		$this->get_queried_object();

		if (isset($this->queried_object_id)) {
			return $this->queried_object_id;
		}

		return 0;
	}

	/**
	 * Set up the next post and iterate current post index.
	 *
	 * @since 1.5.0
	 *
	 * @return WP_Post Next post.
	 */
	public function next_post(): object{
		$this->current_post++;

		/** @var WP_Post */
		$this->post = $this->results[$this->current_post];
		return $this->post;
	}

	/**
	 * Sets up the current post.
	 *
	 * Retrieves the next post, sets up the post, sets the 'in the loop'
	 * property to true.
	 *
	 * @since 1.5.0
	 *
	 * @global WP_Post $post Global post object.
	 */
	public function the_post() {
		global $post;
		$this->in_the_loop = true;

		if (-1 == $this->current_post) {
			// Loop has just started.
			/**
			 * Fires once the loop is started.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_Query $query The WP_Query instance (passed by reference).
			 */
			do_action_ref_array('loop_start', [ & $this]);
		}

		$post = $this->next_post();
		$this->setup_postdata($post);
	}

	/**
	 * Determines whether there are more posts available in the loop.
	 *
	 * Calls the {@see 'loop_end'} action when the loop is complete.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if posts are available, false if end of the loop.
	 */
	public function have_posts() {
		if ($this->current_post + 1 < $this->result_count) {
			return true;
		} elseif ($this->current_post + 1 == $this->result_count && $this->result_count > 0) {
			/**
			 * Fires once the loop has ended.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_Query $query The WP_Query instance (passed by reference).
			 */
			do_action_ref_array('loop_end', [ & $this]);
			// Do some cleaning up after the loop.
			$this->rewind_posts();
		} elseif (0 === $this->result_count) {
			/**
			 * Fires if no results are found in a post query.
			 *
			 * @since 4.9.0
			 *
			 * @param WP_Query $query The WP_Query instance.
			 */
			do_action('loop_no_results', $this);
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the posts and reset post index.
	 *
	 * @since 1.5.0
	 */
	public function rewind_posts() {
		$this->current_post = -1;
		if ($this->result_count > 0) {
			$this->post = $this->results[0];
		}
	}

	/**
	 * Set up global post data.
	 *
	 * @since 4.1.0
	 * @since 4.4.0 Added the ability to pass a post ID to `$post`.
	 *
	 * @global int     $id
	 * @global int     $page
	 * @global array   $pages
	 * @global int     $multipage
	 * @global int     $more
	 * @global int     $numpages
	 *
	 * @param WP_Post|object|int $post WP_Post instance or Post ID/object.
	 * @return true True when finished.
	 */
	public function setup_postdata(object $post) {
		global $id, $page, $pages, $multipage, $more, $numpages;

		if (!get_object_vars($post)) {
			return false;
		}

		if (!($post instanceof WP_Post)) {
			$post = new WP_Post($post);
		}

		$elements = $this->generate_postdata($post);
		if (false === $elements) {
			return;
		}

		$id        = $elements['id'];
		$page      = $elements['page'];
		$pages     = $elements['pages'];
		$multipage = $elements['multipage'];
		$more      = $elements['more'];
		$numpages  = $elements['numpages'];

		/**
		 * Fires once the post data has been set up.
		 *
		 * @since 2.8.0
		 * @since 4.1.0 Introduced `$query` parameter.
		 *
		 * @param WP_Post  $post  The Post object (passed by reference).
		 * @param WP_Query $query The current Query object (passed by reference).
		 */
		do_action_ref_array('the_post', [ & $post, &$this]);

		return true;
	}

	/**
	 * Generate post data.
	 *
	 * @since 5.2.0
	 *
	 * @param WP_Post|object $post WP_Post instance or Post ID/object.
	 * @return array|false Elements of post or false on failure.
	 */
	public function generate_postdata(object $post) {
		if (!get_object_vars($post)) {
			return false;
		}

		if (!($post instanceof WP_Post)) {
			$post = new WP_Post($post);
		}

		$id        = (int) $post->ID;
		$numpages  = 1;
		$multipage = 0;
		$page      = $this->get('page');
		if (!$page) {
			$page = 1;
		}

		$content = $post->post_content;
		if (false !== strpos($content, '<!--nextpage-->')) {
			$content = str_replace("\n<!--nextpage-->\n", '<!--nextpage-->', $content);
			$content = str_replace("\n<!--nextpage-->", '<!--nextpage-->', $content);
			$content = str_replace("<!--nextpage-->\n", '<!--nextpage-->', $content);

			// Remove the nextpage block delimiters, to avoid invalid block structures in the split content.
			$content = str_replace('<!-- wp:nextpage -->', '', $content);
			$content = str_replace('<!-- /wp:nextpage -->', '', $content);

			// Ignore nextpage at the beginning of the content.
			if (0 === strpos($content, '<!--nextpage-->')) {
				$content = substr($content, 15);
			}

			$pages = explode('<!--nextpage-->', $content);
		} else {
			$pages = [$post->post_content];
		}

		/**
		 * Filters the "pages" derived from splitting the post content.
		 *
		 * "Pages" are determined by splitting the post content based on the presence
		 * of `<!-- nextpage -->` tags.
		 *
		 * @since 4.4.0
		 *
		 * @param string[] $pages Array of "pages" from the post content split by `<!-- nextpage -->` tags.
		 * @param WP_Post  $post  Current post object.
		 */
		$pages = apply_filters('content_pagination', $pages, $post);

		$numpages = count($pages);

		if ($numpages > 1) {
			if ($page > 1) {
				$more = 1;
			}
			$multipage = 1;
		} else {
			$multipage = 0;
			$more      = 0;
		}

		$elements = compact('id', 'page', 'pages', 'multipage', 'more', 'numpages');

		return $elements;
	}

	/**
	 * After looping through a nested query, this function
	 * restores the $post global to the current post in this query.
	 *
	 * @since 3.7.0
	 *
	 * @global WP_Post $post Global post object.
	 */
	public function reset_postdata() {
		if (!empty($this->post)) {
			$GLOBALS['post'] = $this->post;
			$this->setup_postdata($this->post);
		}
	}

	/**
	 * Is the query the main query?
	 *
	 * @since 3.3.0
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 *
	 * @return bool Whether the query is the main query.
	 */
	public function is_main_query() {
		global $wp_query;
		return $wp_query === $this;
	}

	/**
	 * Is the query for the blog homepage?
	 *
	 * This is the page which shows the time based blog content of your site.
	 *
	 * Depends on the site's "Front page displays" Reading Settings 'show_on_front' and 'page_for_posts'.
	 *
	 * If you set a static page for the front page of your site, this function will return
	 * true only on the page you set as the "Posts page".
	 *
	 * @since 3.1.0
	 *
	 * @see WP_Query::is_front_page()
	 *
	 * @return bool Whether the query is for the blog homepage.
	 */
	public function is_home() {
		return (bool) $this->is_home;
	}

	/**
	 * Is the query for an existing custom taxonomy archive page?
	 *
	 * If the $taxonomy parameter is specified, this function will additionally
	 * check if the query is for that specific $taxonomy.
	 *
	 * If the $term parameter is specified in addition to the $taxonomy parameter,
	 * this function will additionally check if the query is for one of the terms
	 * specified.
	 *
	 * @since 3.1.0
	 *
	 * @global WP_Taxonomy[] $wp_taxonomies Registered taxonomies.
	 *
	 * @param string|string[]           $taxonomy Optional. Taxonomy slug or slugs to check against.
	 *                                            Default empty.
	 * @param int|string|int[]|string[] $term     Optional. Term ID, name, slug, or array of such
	 *                                            to check against. Default empty.
	 * @return bool Whether the query is for an existing custom taxonomy archive page.
	 *              True for custom taxonomy archive pages, false for built-in taxonomies
	 *              (category and tag archives).
	 */
	public function is_tax($taxonomy = '', $term = '') {
		global $wp_taxonomies;

		if (!$this->is_tax) {
			return false;
		}

		if (empty($taxonomy)) {
			return true;
		}

		$queried_object = $this->get_queried_object();
		$tax_array      = array_intersect(array_keys($wp_taxonomies), (array) $taxonomy);
		$term_array     = (array) $term;

		// Check that the taxonomy matches.
		if (!(isset($queried_object->taxonomy) && count($tax_array) && in_array($queried_object->taxonomy, $tax_array, true))) {
			return false;
		}

		// Only a taxonomy provided.
		if (empty($term)) {
			return true;
		}

		return isset($queried_object->term_id) &&
		count(
			array_intersect(
				[$queried_object->term_id, $queried_object->name, $queried_object->slug],
				$term_array
			)
		);
	}

	/**
	 * Is the query for an existing archive page?
	 *
	 * Archive pages include category, tag, author, date, custom post type,
	 * and custom taxonomy based archives.
	 *
	 * @since 3.1.0
	 *
	 * @see WP_Query::is_category()
	 * @see WP_Query::is_tag()
	 * @see WP_Query::is_author()
	 * @see WP_Query::is_date()
	 * @see WP_Query::is_post_type_archive()
	 * @see WP_Query::is_tax()
	 *
	 * @return bool Whether the query is for an existing archive page.
	 */
	public function is_archive() {
		return (bool) $this->is_archive;
	}

	/**
	 * Is the query for an existing post type archive page?
	 *
	 * @since 3.1.0
	 *
	 * @param string|string[] $post_types Optional. Post type or array of posts types
	 *                                    to check against. Default empty.
	 * @return bool Whether the query is for an existing post type archive page.
	 */
	public function is_post_type_archive($post_types = '') {
		if (empty($post_types) || !$this->is_post_type_archive) {
			return (bool) $this->is_post_type_archive;
		}

		$post_type = $this->get('post_type');
		if (is_array($post_type)) {
			$post_type = reset($post_type);
		}
		$post_type_object = get_post_type_object($post_type);

		return in_array($post_type_object->name, (array) $post_types, true);
	}

	/**
	 * Is the query for an existing author archive page?
	 *
	 * If the $author parameter is specified, this function will additionally
	 * check if the query is for one of the authors specified.
	 *
	 * @since 3.1.0
	 *
	 * @param int|string|int[]|string[] $author Optional. User ID, nickname, nicename, or array of such
	 *                                          to check against. Default empty.
	 * @return bool Whether the query is for an existing author archive page.
	 */
	public function is_author($author = '') {
		if (!$this->is_author) {
			return false;
		}

		if (empty($author)) {
			return true;
		}

		$author_obj = $this->get_queried_object();

		$author = array_map('strval', (array) $author);

		if (in_array((string) $author_obj->ID, $author, true)) {
			return true;
		} elseif (in_array($author_obj->nickname, $author, true)) {
			return true;
		} elseif (in_array($author_obj->user_nicename, $author, true)) {
			return true;
		}

		return false;
	}

	/**
	 * Is the query for an existing single page?
	 *
	 * If the $page parameter is specified, this function will additionally
	 * check if the query is for one of the pages specified.
	 *
	 * @since 3.1.0
	 *
	 * @see WP_Query::is_single()
	 * @see WP_Query::is_singular()
	 *
	 * @param int|string|int[]|string[] $page Optional. Page ID, title, slug, path, or array of such
	 *                                        to check against. Default empty.
	 * @return bool Whether the query is for an existing single page.
	 */
	public function is_page($page = '') {
		if (!$this->is_page) {
			return false;
		}

		if (empty($page)) {
			return true;
		}

		$page_obj = $this->get_queried_object();

		$page = array_map('strval', (array) $page);

		if (in_array((string) $page_obj->ID, $page, true)) {
			return true;
		} elseif (in_array($page_obj->post_title, $page, true)) {
			return true;
		} elseif (in_array($page_obj->post_name, $page, true)) {
			return true;
		} else {
			foreach ($page as $pagepath) {
				if (!strpos($pagepath, '/')) {
					continue;
				}
				$pagepath_obj = get_page_by_path($pagepath);

				if ($pagepath_obj && ($pagepath_obj->ID == $page_obj->ID)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is the query for a paged result and not for the first page?
	 *
	 * @since 3.1.0
	 *
	 * @return bool Whether the query is for a paged result.
	 */
	public function is_paged() {
		return (bool) $this->is_paged;
	}

	/**
	 * Is the query for a post or page preview?
	 *
	 * @since 3.1.0
	 *
	 * @return bool Whether the query is for a post or page preview.
	 */
	public function is_preview() {
		return (bool) $this->is_preview;
	}

	/**
	 * Is the query for a search?
	 *
	 * @since 3.1.0
	 *
	 * @return bool Whether the query is for a search.
	 */
	public function is_search() {
		return (bool) $this->is_search;
	}

	/**
	 * Is the query for an existing single post?
	 *
	 * Works for any post type excluding pages.
	 *
	 * If the $post parameter is specified, this function will additionally
	 * check if the query is for one of the Posts specified.
	 *
	 * @since 3.1.0
	 *
	 * @see WP_Query::is_page()
	 * @see WP_Query::is_singular()
	 *
	 * @param int|string|int[]|string[] $post Optional. Post ID, title, slug, path, or array of such
	 *                                        to check against. Default empty.
	 * @return bool Whether the query is for an existing single post.
	 */
	public function is_single($post = '') {
		if (!$this->is_single) {
			return false;
		}

		if (empty($post)) {
			return true;
		}

		$post_obj = $this->get_queried_object();

		$post = array_map('strval', (array) $post);

		if (in_array((string) $post_obj->ID, $post, true)) {
			return true;
		} elseif (in_array($post_obj->post_title, $post, true)) {
			return true;
		} elseif (in_array($post_obj->post_name, $post, true)) {
			return true;
		} else {
			foreach ($post as $postpath) {
				if (!strpos($postpath, '/')) {
					continue;
				}
				$postpath_obj = get_page_by_path($postpath, OBJECT, $post_obj->post_type);

				if ($postpath_obj && ($postpath_obj->ID == $post_obj->ID)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Is the query for an existing single post of any post type (post, attachment, page,
	 * custom post types)?
	 *
	 * If the $post_types parameter is specified, this function will additionally
	 * check if the query is for one of the Posts Types specified.
	 *
	 * @since 3.1.0
	 *
	 * @see WP_Query::is_page()
	 * @see WP_Query::is_single()
	 *
	 * @param string|string[] $post_types Optional. Post type or array of post types
	 *                                    to check against. Default empty.
	 * @return bool Whether the query is for an existing single post
	 *              or any of the given post types.
	 */
	public function is_singular($post_types = '') {
		if (empty($post_types) || !$this->is_singular) {
			return (bool) $this->is_singular;
		}

		$post_obj = $this->get_queried_object();

		return in_array($post_obj->post_type, (array) $post_types, true);
	}

	/**
	 * Is the query a 404 (returns no results)?
	 *
	 * @since 3.1.0
	 *
	 * @return bool Whether the query is a 404 error.
	 */
	public function is_404() {
		return (bool) $this->is_404;
	}

}
