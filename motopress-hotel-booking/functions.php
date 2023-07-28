<?php
/**
 * Get template part.
 *
 * @param string $slug
 * @param string $name Optional. Default ''.
 */
function mphb_get_template_part( $slug, $atts = array() ){

	$template = '';

	// Look in %theme_dir%/%template_path%/slug.php
	$template = locate_template( MPHB()->getTemplatePath() . "{$slug}.php" );

	// Get default template from plugin
	if ( empty( $template ) && file_exists( MPHB()->getPluginPath( "templates/{$slug}.php" ) ) ) {
		$template = MPHB()->getPluginPath( "templates/{$slug}.php" );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'mphb_get_template_part', $template, $slug, $atts );

	if ( !empty( $template ) ) {
		mphb_load_template( $template, $atts );
	}
}

function mphb_load_template( $template, $templateArgs = array() ){
	if ( $templateArgs && is_array( $templateArgs ) ) {
		extract( $templateArgs );
	}
	require $template;
}

/**
 *
 * @global string $wp_version
 * @param string $type
 * @param bool $gmt
 * @return string
 */
function mphb_current_time( $type, $gmt = 0 ){
	global $wp_version;
	if ( version_compare( $wp_version, '3.9', '<=' ) && !in_array( $type, array( 'timestmap',
			'mysql' ) ) ) {
		$timestamp = current_time( 'timestamp', $gmt );
		return date( $type, $timestamp );
	} else {
		return current_time( $type, $gmt );
	}
}

/**
 * Retrieve a post status label by name
 *
 * @param string $status
 * @return string
 */
function mphb_get_status_label( $status ){
	switch ( $status ) {
		case 'new':
			$label		 = _x( 'New', 'Post Status', 'motopress-hotel-booking' );
			break;
		case 'auto-draft':
			$label		 = _x( 'Auto Draft', 'Post Status', 'motopress-hotel-booking' );
			break;
		default:
			$statusObj	 = get_post_status_object( $status );
			$label		 = !is_null( $statusObj ) && property_exists( $statusObj, 'label' ) ? $statusObj->label : '';
			break;
	}

	return $label;
}

/**
 *
 * @param string $name
 * @param string $value
 * @param int $expire
 */
function mphb_set_cookie( $name, $value, $expire = 0 ){
	setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN );
	if ( COOKIEPATH != SITECOOKIEPATH ) {
		setcookie( $name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
	}
}

/**
 *
 * @param string $name
 * @return mixed|null Cookie value or null if not exists.
 */
function mphb_get_cookie( $name ){
	return ( mphb_has_cookie( $name ) ) ? $_COOKIE[$name] : null;
}

/**
 *
 * @param string $name
 * @return bool
 */
function mphb_has_cookie( $name ){
	return isset( $_COOKIE[$name] );
}

function mphb_is_checkout_page(){
	$checkoutPageId = MPHB()->settings()->pages()->getCheckoutPageId();
	return $checkoutPageId && is_page( $checkoutPageId );
}

function mphb_is_search_results_page(){
	$searchResultsPageId = MPHB()->settings()->pages()->getSearchResultsPageId();
	return $searchResultsPageId && is_page( $searchResultsPageId );
}

function mphb_is_single_room_type_page(){
	return is_singular( MPHB()->postTypes()->roomType()->getPostType() );
}

function mphb_is_create_booking_page(){
	return MPHB()->getCreateBookingMenuPage()->isCurrentPage();
}

function mphb_get_thumbnail_width(){
	$width = 150;

	$imageSizes = get_intermediate_image_sizes();
	if ( in_array( 'thumbnail', $imageSizes ) ) {
		$width = (int) get_option( "thumbnail_size_w", $width );
	}

	return $width;
}

/**
 *
 * @param float $price
 * @param array $atts
 * @param string $atts['decimal_separator']
 * @param string $atts['thousand_separator']
 * @param int $atts['decimals'] Number of decimals
 * @param string $atts['currency_position'] Possible values: after, before, after_space, before_space
 * @param string $atts['currency_symbol']
 * @param bool $atts['literal_free'] Use "Free" text instead of 0 price.
 * @param bool $atts['trim_zeros'] Trim decimals zeros.
 * @return string
 */
function mphb_format_price( $price, $atts = array() ){

	$defaultAtts = array(
		'decimal_separator'	 => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
		'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
		'decimals'			 => MPHB()->settings()->currency()->getPriceDecimalsCount(),
		'currency_position'	 => MPHB()->settings()->currency()->getCurrencyPosition(),
		'currency_symbol'	 => MPHB()->settings()->currency()->getCurrencySymbol(),
		'literal_free'		 => false,
		'trim_zeros'		 => true,
		'period'			 => false,
		'period_title'		 => '',
		'period_nights'		 => 1,
		'as_html'			 => true
	);

	$atts = wp_parse_args( $atts, $defaultAtts );

	$priceFormat = MPHB()->settings()->currency()->getPriceFormat( $atts['currency_symbol'], $atts['currency_position'], $atts['as_html'] );

	$priceClasses = array( 'mphb-price' );

	if ( $atts['literal_free'] && $price == 0 ) {
		$formattedPrice	 = apply_filters( 'mphb_free_literal', _x( 'Free', 'Zero price', 'motopress-hotel-booking' ) );
		$priceClasses[]	 = 'mphb-price-free';
	} else {
		$negative	 = $price < 0;
		$price		 = abs( $price );
		$price		 = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );
		if ( $atts['trim_zeros'] ) {
			$price = mphb_trim_zeros( $price );
		}
		$formattedPrice = ( $negative ? '-' : '' ) . sprintf( $priceFormat, $price );
	}

    if ( $atts['as_html'] ) {
        $priceClassesStr = join( ' ', $priceClasses );
        $price = sprintf( '<span class="%s">%s</span>', esc_attr( $priceClassesStr ), $formattedPrice );
    } else {
        $price = $formattedPrice;
    }

	if ( $atts['period'] ) {

		$priceDescription	 = _nx( 'per night', 'for %d nights', $atts['period_nights'], 'Ex: $99 for 2 nights', 'motopress-hotel-booking' );
		$priceDescription	 = sprintf( $priceDescription, $atts['period_nights'] );
		$priceDescription	 = apply_filters( 'mphb_price_period_description', $priceDescription, $atts['period_nights'] );

        if ( $atts['as_html'] ) {
            $priceDescription = sprintf( '<span class="mphb-price-period" title="%1$s">%2$s</span>', esc_attr( $atts['period_title'] ), $priceDescription );
        }

		$price = sprintf( '%1$s %2$s', $price, $priceDescription );
	}

	return $price;
}

/**
 *
 * @param float $price
 * @param array $atts
 * @param string $atts['decimal_separator']
 * @param string $atts['thousand_separator']
 * @param int $atts['decimals'] Number of decimals
 * @return string
 */
function mphb_format_percentage( $price, $atts = array() ){

	$defaultAtts = array(
		'decimal_separator'	 => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
		'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
		'decimals'			 => MPHB()->settings()->currency()->getPriceDecimalsCount()
	);

	$atts = wp_parse_args( $atts, $defaultAtts );

	$isNegative		 = $price < 0;
	$price			 = abs( $price );
	$price			 = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );
	$formattedPrice	 = ( $isNegative ? '-' : '' ) . $price;

	return '<span class="mphb-percentage">' . $formattedPrice . '%</span>';
}

/**
 * Trim trailing zeros off prices.
 *
 * @param mixed $price
 * @return string
 */
function mphb_trim_zeros( $price ){
	return preg_replace( '/' . preg_quote( MPHB()->settings()->currency()->getPriceDecimalsSeparator(), '/' ) . '0++$/', '', $price );
}

/**
 * @since 3.2.0
 */
function mphb_trim_decimal_zeros($price)
{
    $separator = preg_quote(MPHB()->settings()->currency()->getPriceDecimalsSeparator());

    $price = preg_replace("/{$separator}0++$/", '', $price);
    $price = preg_replace("/({$separator}[^0]++)0++$/", '$1', $price);

    return $price;
}

/**
 * Get WP Query paged var
 *
 * @return int
 */
function mphb_get_paged_query_var(){
	if ( get_query_var( 'paged' ) ) {
		$paged = absint( get_query_var( 'paged' ) );
	} else if ( get_query_var( 'page' ) ) {
		$paged = absint( get_query_var( 'page' ) );
	} else {
		$paged = 1;
	}
	return $paged;
}

/**
 *
 * @param array $queryPart
 * @param array|null $metaQuery
 * @return array
 */
function mphb_add_to_meta_query( $queryPart, $metaQuery ){

	if ( is_null( $metaQuery ) ) {

		if ( mphb_meta_query_is_first_order_clause( $queryPart ) ) {
			$metaQuery = array( $queryPart );
		} else {
			$metaQuery = $queryPart;
		}

		return $metaQuery;
	}

	if ( !empty( $metaQuery ) && !isset( $metaQuery['relation'] ) ) {
		$metaQuery['relation'] = 'AND';
	}

	if ( isset( $metaQuery['relation'] ) && strtoupper( $metaQuery['relation'] ) === 'AND' ) {

		if ( mphb_meta_query_is_first_order_clause( $queryPart ) ||
			( isset( $queryPart['relation'] ) && strtoupper( $queryPart['relation'] ) === 'OR' )
		) {
			$metaQuery[] = $queryPart;
		} else {
			$metaQuery = array_merge( $metaQuery, $queryPart );
		}
	} else {
		$metaQuery = array(
			'relation' => 'AND',
			$queryPart,
			$metaQuery
		);
	}

	return $metaQuery;
}

/**
 *
 * @param array $query
 * @return bool
 */
function mphb_meta_query_is_first_order_clause( $query ){
	return isset( $query['key'] ) || isset( $query['value'] );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function mphb_clean( $var ){
	if ( is_array( $var ) ) {
		return array_map( 'mphb_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * @see https://github.com/symfony/polyfill-php56
 *
 * @param string $knownString
 * @param string $userInput
 * @return boolean
 */
function mphb_hash_equals( $knownString, $userInput ){

	if ( !is_string( $knownString ) ) {
		return false;
	}

	if ( !is_string( $userInput ) ) {
		return false;
	}

	$knownLen	 = mphb_strlen( $knownString );
	$userLen	 = mphb_strlen( $userInput );

	if ( $knownLen !== $userLen ) {
		return false;
	}

	$result = 0;

	for ( $i = 0; $i < $knownLen; ++$i ) {
		$result |= ord( $knownString[$i] ) ^ ord( $userInput[$i] );
	}

	return 0 === $result;
}

/**
 *
 * @param string $s
 * @return string
 */
function mphb_strlen( $s ){
	return ( extension_loaded( 'mbstring' ) ) ? mb_strlen( $s, '8bit' ) : strlen( $s );
}

/**
 * @todo add support for arrays
 *
 * @param string $url
 * @return array
 */
function mphb_get_query_args( $url ){

	$queryArgs = array();

	$queryStr = parse_url( $url, PHP_URL_QUERY );

	if ( $queryStr ) {
		parse_str( $queryStr, $queryArgs );
	}

	return $queryArgs;
}

/**
 * Wrapper function for wp_dropdown_pages
 *
 * @see wp_dropdown_pages
 *
 * @param array $atts
 * @return string
 */
function mphb_wp_dropdown_pages( $atts = array() ){

	do_action( '_mphb_before_dropdown_pages' );

	$dropdown = wp_dropdown_pages( $atts );

	do_action( '_mphb_after_dropdown_pages' );

	return $dropdown;
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @param int $limit The maximum execution time, in seconds. If set to zero, no time limit is imposed.
 */
function mphb_set_time_limit( $limit = 0 ){
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

function mphb_error_log( $message ){
	if ( !is_string( $message ) ) {
		$message = print_r( $message, true );
	}
	error_log( $message );
}

/**
 *
 * @return string
 */
function mphb_current_domain(){
	$homeHost = parse_url( home_url(), PHP_URL_HOST ); // www.booking.coms
	return preg_replace( '/^www\./', '', $homeHost );  // booking.com
}

/**
 * For local usage only. For global IDs it's better to use function
 * mphb_generate_uid().
 *
 * @return string
 */
function mphb_generate_uuid4(){
	// Source: http://php.net/manual/ru/function.uniqid.php#94959
	$uuid4 = sprintf(
		'%04x%04x%04x%04x%04x%04x%04x%04x'
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0x0fff ) | 0x4000
		, mt_rand( 0, 0x3fff ) | 0x8000
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
	);
	return $uuid4;
}

function mphb_generate_uid(){
	return mphb_generate_uuid4() . '@' . mphb_current_domain();
}

/**
 * Retrieves the edit post link for post regardless current user capabilities
 *
 * @param int|string $id
 * @return string
 */
function mphb_get_edit_post_link_for_everyone( $id, $context = 'display' ){

	if ( !$post = get_post( $id ) ) {
		return '';
	}

	if ( 'revision' === $post->post_type ) {
		$action = '';
	} elseif ( 'display' == $context ) {
		$action = '&amp;action=edit';
	} else {
		$action = '&action=edit';
	}

	$post_type_object = get_post_type_object( $post->post_type );
	if ( !$post_type_object ) {
		return '';
	}

	if ( $post_type_object->_edit_link ) {
		$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
	} else {
		$link = '';
	}

	/**
	 * Filters the post edit link.
	 *
	 * @since 2.3.0
	 *
	 * @param string $link The edit link.
	 * @param int $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands
	 * are encoded.
	 */
	return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );

	return $link;
}

/**
 *
 * @param int $typeId Room type ID.
 *
 * @return array [%Room ID% => %Room Number%].
 */
function mphb_get_rooms_select_list( $typeId ){
	$rooms = MPHB()->getRoomPersistence()->getIdTitleList( array(
		'room_type_id'	 => $typeId,
		'post_status'	 => 'all'
	) );

	$roomType	 = MPHB()->getRoomTypeRepository()->findById( $typeId );
	$typeTitle	 = ( $roomType ? $roomType->getTitle() : '' );

	if ( !empty( $typeTitle ) ) {
		foreach ( $rooms as &$room ) {
			$room	 = str_replace( $typeTitle, '', $room );
			$room	 = trim( $room );
		}
		unset( $room );
	}

	return $rooms;
}

function mphb_show_multiple_instances_notice(){
	/* translators: %s: URL to plugins.php page */
	$message = __( 'You are using two instances of Hotel Booking plugin at the same time, please <a href="%s">deactivate one of them</a>.', 'motopress-hotel-booking' );
	$message = sprintf( $message, esc_url( admin_url( 'plugins.php' ) ) );

	$html_message = sprintf( '<div class="notice notice-warning is-dismissible">%s</div>', wpautop( $message ) );

	echo wp_kses_post( $html_message );
}

/**
 * @param string $wrapper Optional. Wrapper tag - "span" or "div". "span" by
 *     default. Pass the empty value to remove the wrapper
 * @param string $wrapperClass Optional. "description" by default.
 * @return string "Upgrade to Premium..." HTML.
 *
 * @since 3.5.1 parameters $before and $after was replaced with $wrapper and $wrapperClass.
 */
function mphb_upgrade_to_premium_message($wrapper = 'span', $wrapperClass = 'description')
{
	$message = __('<a href="%s">Upgrade to Premium</a> to enable this feature.', 'motopress-hotel-booking');
	$message = sprintf($message, esc_url(admin_url('admin.php?page=mphb_premium')));

    if (!empty($wrapper)) {
        if ($wrapper === 'div') {
            $message = '<div class="' . esc_attr($wrapperClass) . '">' . $message . '</div>';
        } else {
            $message = '<span class="' . esc_attr($wrapperClass) . '">' . $message . '</span>';
        }
    }

	return $message;
}

/**
 * Season price format history:
 * v2.6.0- - single number.
 * v2.7.1- - ["base", "enable_variations" => "0"|"1", "variations" => ""|[["adults", "children", "price"]]].
 * v2.7.2+ - ["periods", "prices", "enable_variations" => true/false, "variations" => [["adults", "children", "prices"]]].
 *
 * @param mixed $price Price in any format.
 *
 * @return array Price in format 2.7.2+.
 */
function mphb_normilize_season_price( $price ){
	$value = array(
		'periods' => array( 1 ),
		'prices'  => array( 0 ),
		'enable_variations' => false,
		'variations' => array()
	);

	if ( !is_numeric( $price ) && !is_array( $price ) ) {
		return $value;
	}

	if ( is_numeric( $price ) ) {
		// Convert v2.6.0- into v2.7.2+
		$value['prices'][0] = $price;

	} else if ( isset( $price['base'] ) ) {
		// Convert v2.7.1- into v2.7.2+
		$value['prices'][0] = $price['base'];
		$value['enable_variations'] = \MPHB\Utils\ValidateUtils::validateBool( $price['enable_variations'] );

	} else {
		// Merge values from v2.7.2+
		$value['periods'] = $price['periods'];
		$value['prices'] = $price['prices'];
		$value['enable_variations'] = $price['enable_variations'];
	}

	// Merge variations
	if ( isset( $price['variations'] ) && is_array( $price['variations'] ) ) {
		foreach ( $price['variations'] as $variation ) {
			if ( isset( $variation['price'] ) ) {
				// Convert v2.7.1- into v2.7.2+
				$prices = array( $variation['price'] );
			} else {
				// Copy prices from v2.7.2+
				$prices = $variation['prices'];
			}

			$value['variations'][] = array(
				'adults'	 => intval( $variation['adults'] ),
				'children'	 => intval( $variation['children'] ),
				'prices'	 => $prices
			);
		}
	}

	return $value;
}

/**
 * Check if term name is reserved.
 *
 * @param  string $termName Term name.
 *
 * @return bool
 *
 * @see https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
 */
function mphb_is_reserved_term( $termName ){
	$reservedTerms = array(
		'attachment',
		'attachment_id',
		'author',
		'author_name',
		'calendar',
		'cat',
		'category',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comments_per_page',
		'comments_popup',
		'customize_messenger_channel',
		'customized',
		'cpage',
		'day',
		'debug',
		'error',
		'exact',
		'feed',
		'fields',
		'hour',
		'link_category',
		'm',
		'minute',
		'monthnum',
		'more',
		'name',
		'nav_menu',
		'nonce',
		'nopaging',
		'offset',
		'order',
		'orderby',
		'p',
		'page',
		'page_id',
		'paged',
		'pagename',
		'pb',
		'perm',
		'post',
		'post__in',
		'post__not_in',
		'post_format',
		'post_mime_type',
		'post_status',
		'post_tag',
		'post_type',
		'posts',
		'posts_per_archive_page',
		'posts_per_page',
		'preview',
		'robots',
		's',
		'search',
		'second',
		'sentence',
		'showposts',
		'static',
		'subpost',
		'subpost_id',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'taxonomy',
		'tb',
		'term',
		'theme',
		'type',
		'w',
		'withcomments',
		'withoutcomments',
		'year'
	);

	return in_array( $termName, $reservedTerms, true );
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 *
 * @since 3.7.0
 *
 * @author MrHus
 * @link http://stackoverflow.com/a/834355/3918377
 */
function mphb_string_starts_with($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 *
 * @author MrHus
 *
 * @see https://stackoverflow.com/a/834355/3918377
 */
function mphb_string_ends_with( $haystack, $needle ){
    $length = strlen( $needle );

    if ( $length == 0 ) {
        return true;
    }

    return ( substr( $haystack, -$length ) === $needle );
}

/**
 * @since 3.0
 */
function mphb_array_disjunction( $a, $b ){
    return array_merge( array_diff( $a, $b ), array_diff( $b, $a ) );
}

/**
 * @return array "publish", and maybe "private", if current user can read
 * private posts.
 *
 * @since 3.0.1
 */
function mphb_readable_post_statuses(){
	if ( current_user_can( 'read_private_posts' ) ) {
		return array( 'publish', 'private' );
	} else {
		return array( 'publish' );
	}
}

/**
 * @since 3.0.2
 */
function mphb_db_version()
{
    // Min version "1.0.1" can be found in the upgrader constants
    return get_option('mphb_db_version', '1.0.1');
}

/**
 * @since 3.0.2
 */
function mphb_db_version_at_least($requiredVersion)
{
    $dbVersion = mphb_db_version();
    return version_compare($dbVersion, $requiredVersion, '>=');
}

/**
 * @since 3.0.3
 */
function mphb_version_at_least($requiredVersion)
{
    $actualVersion = MPHB()->getVersion();
    return version_compare($actualVersion, $requiredVersion, '>=');
}

/**
 * @param string $requiredVersion
 * @return bool
 *
 * @global string $wp_version
 *
 * @since 3.7.4
 */
function mphb_wordpress_at_least($requiredVersion)
{
    global $wp_version;

    return version_compare($wp_version, $requiredVersion, '>=');
}

/**
 * @see Issue ticket in WordPress Trac: https://core.trac.wordpress.org/ticket/45495
 *
 * @since 3.3.0
 */
function mphb_fix_blocks_autop()
{
    if (mphb_wordpress_at_least('5.2')) {
        // The bug was fixed since WP 5.2
        return;

    } else if (mphb_wordpress_at_least('5.0') && has_filter('the_content', 'wpautop') !== false) {
        remove_filter('the_content', 'wpautop');
        add_filter('the_content', function ($content) {
            if (has_blocks()) {
                return $content;
            }

            return wpautop($content);
        });
    }
}

/**
 * @param string $json JSON string with possibly escaped Unicode symbols (\uXXXX).
 * @return string JSON string with escaped Unicode symbols (\\uXXXX).
 *
 * @since 3.5.0
 */
function mphb_escape_json_unicodes($json)
{
    return preg_replace('/(\\\\u[0-9a-f]{4})/i', '\\\\$1', $json);
}

/**
 * @return string "/path/to/wordpress/wp-content/uploads/mphb/"
 *
 * @since 3.5.0
 */
function mphb_uploads_dir()
{
    $uploads = wp_upload_dir();
    return trailingslashit($uploads['basedir']) . 'mphb/';
}

/**
 * @since 3.5.0
 */
function mphb_create_uploads_dir()
{
    $dir = mphb_uploads_dir();

    if (file_exists($dir)) {
        return;
    }

    // Create .../uploads/mphb/
    wp_mkdir_p($dir);

    // Create .../uploads/mphb/index.php
    @file_put_contents($dir . 'index.php', '<?php' . PHP_EOL);

    // Create .../uploads/mphb/.htaccess
    $htaccess = "Options -Indexes\n"
        . "deny from all\n"
        . "<FilesMatch '\.(jpg|jpeg|png|gif|mp3|ogg)$'>\n"
        . "Order Allow,Deny\n"
        . "Allow from all\n"
        . "</FilesMatch>\n";

    @file_put_contents($dir . '.htaccess', $htaccess);
}

/**
 * @since 3.6.0
 */
function mphb_verify_nonce($action, $nonceName = 'mphb_nonce')
{
    if (!isset($_REQUEST[$nonceName])) {
        return false;
    }

    $nonce = $_REQUEST[$nonceName];

    return wp_verify_nonce($nonce, $action);
}

/**
 * @since 3.7.1
 */
function mphb_get_polyfill_for($function)
{
    switch ($function) {
        case 'mb_convert_encoding': require_once MPHB()->getPluginPath('includes/polyfills/mbstring.php'); break;
    }
}

/**
 * @return int
 *
 * @since 3.7.2
 */
function mphb_current_year()
{
    return intval(strftime('%Y'));
}

/**
 * @param int $month
 * @param int $year
 * @return int
 *
 * @since 3.7.2
 */
function mphb_days_in_month($month, $year)
{
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_customer_fields()
{
    return MPHB()->settings()->main()->getCustomerBundle()->getCustomerFields();
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_default_customer_fields()
{
    return MPHB()->settings()->main()->getCustomerBundle()->getDefaultFields();
}

/**
 * @param string $fieldName
 * @return bool
 *
 * @since 3.7.2
 */
function mphb_is_default_customer_field($fieldName)
{
    return MPHB()->settings()->main()->getCustomerBundle()->isDefaultField($fieldName);
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_custom_customer_fields()
{
    return MPHB()->settings()->main()->getCustomerBundle()->getCustomFields();
}

/**
 * @return array
 *
 * @since 3.7.2
 */
function mphb_get_admin_checkout_customer_fields()
{
    return MPHB()->settings()->main()->getCustomerBundle()->getAdminCheckoutFields();
}

/**
 * @return int
 *
 * @since 3.7.2
 */
function mphb_get_editing_post_id()
{
    $postId = 0;

    if (is_admin()) {
        if (isset($_REQUEST['post_ID']) && is_numeric($_REQUEST['post_ID'])) {
            $postId = intval($_REQUEST['post_ID']); // On post update ($_POST)
        } else if (isset($_REQUEST['post']) && is_numeric($_REQUEST['post'])) {
            $postId = intval($_REQUEST['post']); // On post edit page ($_GET)
        }
    }

    return $postId;
}

/**
 * @param int|float $value
 * @param int|float $min
 * @param int|float $max
 * @return int|float
 *
 * @since 3.7.2
 */
function mphb_limit($value, $min, $max)
{
    return max($min, min($value, $max));
}

/**
 * Add an array after the specified position.
 *
 * @param array $array Subject array.
 * @param int $position
 * @param array $insert Array to insert.
 * @return array Result array with inserted items.
 *
 * @since 3.7.2
 */
function mphb_array_insert_after($array, $position, $insert)
{
    if ($position < 0) {
        return array_merge($insert, $array);
    } else if ($position >= count($array)) {
        return array_merge($array, $insert);
    } else {
        return array_merge(
            array_slice($array, 0, $position + 1, true),
            $insert,
            array_slice($array, $position + 1, count($array), true)
        );
    }
}

/**
 * Add an array after the specified key in the associative array.
 *
 * @param array $array Subject array.
 * @param mixed $searchKey
 * @param array $insert Array to insert.
 * @return array Result array with inserted items.
 *
 * @since 3.7.2
 */
function mphb_array_insert_after_key($array, $searchKey, $insert)
{
    $position = array_search($searchKey, array_keys($array));

    if ($position !== false) {
        return mphb_array_insert_after($array, $position, $insert);
    } else {
        return mphb_array_insert_after($array, count($array), $insert);
    }
}

/**
 * @param array $haystack
 * @param callable $checkCallback The callback check function. The function must
 *     return TRUE if the proper element was found or FALSE otherwise. Gets the
 *     value of the element as the first argument and the key as second.
 * @return mixed The key for searched element or FALSE.
 *
 * @since 3.7.2
 */
function mphb_array_usearch(array $haystack, callable $checkCallback)
{
    foreach ($haystack as $key => $value) {
        if ($checkCallback($value, $key)) {
            return $key;
        }
    }

    return false;
}

/**
 * @param string $str
 * @param string $separator Optional. "_" by default.
 * @return string
 *
 * @since 3.7.3
 */
function mphb_prefix($str, $separator = '_')
{
    return MPHB()->addPrefix($str, $separator);
}

/**
 * @param string $str
 * @param string $separator Optional. "_" by default.
 * @return string
 *
 * @since 3.7.3
 */
function mphb_unprefix($str, $separator = '_')
{
    $prefix = MPHB()->getPrefix() . $separator;
    return str_replace($prefix, '', $str);
}

/**
 * @param int $bookingId
 * @param bool $force Optional. FALSE by default.
 * @return \MPHB\Entities\Booking|null
 *
 * @since 3.7.3
 */
function mphb_get_booking($bookingId, $force = false)
{
    return MPHB()->getBookingRepository()->findById($bookingId, $force);
}

/**
 * @param int $bookingId
 * @return \MPHB\Entities\Customer|null
 *
 * @since 3.7.3
 */
function mphb_get_customer($bookingId)
{
    $booking = mphb_get_booking($bookingId);

    if (!is_null($booking)) {
        return $booking->getCustomer();
    } else {
        return null;
    }
}

/**
 * @param int $roomTypeId
 * @param bool $force Optional. FALSE by default.
 * @return \MPHB\Entities\RoomType|null
 *
 * @since 3.8
 */
function mphb_get_room_type($roomTypeId, $force = false)
{
    return MPHB()->getRoomTypeRepository()->findById($roomTypeId, $force);
}

/**
 * Determine if the current view is the "All" view.
 *
 * @see \WP_Posts_List_Table::is_base_request()
 *
 * @param string|null $postType Optional. NULL by default.
 * @return bool
 *
 * @global string $typenow
 *
 * @since 3.7.3
 */
function mphb_is_base_request($postType = null)
{
    global $typenow;

    $allowedVars = [
        'post_type' => true,
        'paged'     => true,
        'all_posts' => true
    ];

    $unallowedVars = array_diff_key($_GET, $allowedVars);

    $isBase = count($unallowedVars) == 0;

    // Add additional check of the post type
    if (!is_null($postType) && $isBase) {
        $isBase = $postType === $typenow;
    }

    return $isBase;
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_complete_booking($booking)
{
    $bookedStatuses = MPHB()->postTypes()->booking()->statuses()->getBookedRoomStatuses();
    return in_array($booking->getStatus(), $bookedStatuses);
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_pending_booking($booking)
{
    $pendingStatuses = MPHB()->postTypes()->booking()->statuses()->getPendingRoomStatuses();
    return in_array($booking->getStatus(), $pendingStatuses);
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_locking_booking($booking)
{
    $lockingStatuses = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
    return in_array($booking->getStatus(), $lockingStatuses);
}

/**
 * @param \MPHB\Entities\Booking $booking
 * @return bool
 *
 * @since 3.7.6
 */
function mphb_is_failed_booking($booking)
{
    $failedStatuses = MPHB()->postTypes()->booking()->statuses()->getFailedStatuses();
    return in_array($booking->getStatus(), $failedStatuses);
}

/**
 * @param \DateTime $from Start date, like check-in date.
 * @param \DateTime $to End date, like check-out date.
 * @param array $atts Optional.
 *     @param int $atts['room_type_id'] Optional. 0 by default (any room type).
 *     @param int|int[] $atts['exclude_bookings'] Optional. One or more booking IDs.
 * @return array [Room type ID => [Rooms IDs]] (all IDs - original)
 *
 * @since 3.8
 */
function mphb_get_available_rooms($from, $to, $atts = array())
{
    $roomTypeId = isset($atts['room_type_id']) ? $atts['room_type_id'] : 0;
    $searchAtts = array();

    if (isset($atts['exclude_bookings'])) {
        $searchAtts['exclude_bookings'] = $atts['exclude_bookings'];
    }

    return MPHB()->getRoomRepository()->getAvailableRooms($from, $to, $roomTypeId, $searchAtts);
}

/**
 * @param int|string $value
 * @return int The number in range [0; oo)
 *
 * @since 3.8
 */
function mphb_posint($value)
{
    return max(0, intval($value));
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_min_adults()
{
    return MPHB()->settings()->main()->getMinAdults();
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_min_children()
{
    return MPHB()->settings()->main()->getMinChildren();
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_max_adults()
{
    return MPHB()->settings()->main()->getSearchMaxAdults();
}

/**
 * @return int
 *
 * @since 3.8
 */
function mphb_get_max_children()
{
    return MPHB()->settings()->main()->getSearchMaxChildren();
}

/**
 * @param array $array Array to flip.
 * @param bool $arraySingle Optional. Convert single value into array. FALSE by default.
 * @return array
 *
 * @since 3.8
 */
function mphb_array_flip_duplicates($array, $arraySingle = false)
{
    $values = array_unique($array);
    $flip = array();

    foreach ($values as $value) {
        $keys = array_keys($array, $value);

        if ($arraySingle || count($keys) > 1) {
            $flip[$value] = $keys;
        } else {
            $flip[$value] = reset($keys);
        }
    }

    return $flip;
}



function mphb_booking_info_manual_func( $atts ){
	$return = "";
	//echo "<pre>"; print_r($_SERVER); echo "</pre>";
	
	//
	if(!isset($_GET['booking_id'])){
	    $parts = parse_url($_SERVER['REQUEST_URI']);
        parse_str($parts['query'], $query);
        if(isset($query['booking_id'])){
            $_GET['booking_id'] = $query['booking_id'];
        }
	}
    if(isset($_GET['booking_id']) && $_GET['booking_id']){
        
		$booking = mphb_get_booking($_GET['booking_id'], true);
        
        if ($booking) {
            $check_in = date_i18n( MPHB()->settings()->dateTime()->getDateFormatWP(), $booking->getCheckInDate()->getTimestamp() );
            ?>
		<script async defer src="https://apis.google.com/js/api.js"
          onload="this.onload=function(){};handleClientLoad()"
          onreadystatechange="if (this.readyState === 'complete') this.onload()">
        </script>
        
        <style>
            #authorize-button{
                background: #ededed;
            color: #262626;
            border: 1px solid #cdcdcd;
            border-radius: 0;
            line-height: 1;    
            }
            
        </style>
<script type="text/javascript">
      var now = new Date("<?= date("Y-m-d 00:00:00",$booking->getCheckInDate()->getTimestamp()) ?>");
      today = now.toISOString();


      var now = new Date("<?= date("Y-m-d 23:59:59",$booking->getCheckInDate()->getTimestamp()) ?>");
      enddate = now.toISOString();
    

      var resource = {
        "summary": "Arienzo Beach Club Booking",
        "start": {
          "dateTime": today
        },
        "end": {
          "dateTime": enddate
        }
      };

      // Client ID and API key from the Developer Console
      var CLIENT_ID = '707294692731-guc4lkkk6pihupvu15d3e0vffqnnaame.apps.googleusercontent.com';
      var API_KEY = 'AIzaSyCS9EsAmLgKtsrJxvW-d7abaMMWTtpsqbo';

      // Array of API discovery doc URLs for APIs used by the quickstart
      

      // Authorization scopes required by the API; multiple scopes can be
      // included, separated by spaces.
      var SCOPES = "https://www.googleapis.com/auth/calendar";

      
      

      /**
       *  On load, called to load the auth2 library and API client library.
       */
      function handleClientLoad() {
        gapi.load('client:auth2', initClient);
      }

      /**
       *  Initializes the API client library and sets up sign-in state
       *  listeners.
       */
      function initClient() {
        gapi.client.init({
          apiKey: API_KEY,
          clientId: CLIENT_ID,
          scope: SCOPES
        }).then(function () {
          // Listen for sign-in state changes.
          gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

          // Handle the initial sign-in state.
          updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
          
          
        }, function(error) {
          
        });
      }

      /**
       *  Called when the signed in status changes, to update the UI
       *  appropriately. After a sign-in, the API is called.
       */
      var handleAuthClick = function(event) {
        gapi.auth2.getAuthInstance().signIn();
      }

       var makeApiCall = function() {
        gapi.client.load('calendar', 'v3', function() {         // load the calendar api (version 3)
          var request = gapi.client.calendar.events.insert({
            'calendarId':   'primary',  // calendar ID
            "resource":     resource              // pass event details with api call
          });
          
          // handle the response from our api call
          request.execute(function(resp) {
            /*if(resp.status=='confirmed') {
              document.getElementById('event-response').innerHTML = "Event created successfully. View it <a href='" + resp.htmlLink + "'>online here</a>.";
            } else {
              document.getElementById('event-response').innerHTML = "There was a problem. Reload page and try again.";
            }*/
            /* for (var i = 0; i < resp.items.length; i++) {    // loop through events and write them out to a list
              var li = document.createElement('li');
              var eventInfo = resp.items[i].summary + ' ' +resp.items[i].start.dateTime;
              li.appendChild(document.createTextNode(eventInfo));
              document.getElementById('events').appendChild(li);
            } */
            //console.log(resp);
            alert("Event add To calendar successfully");
          });
        });
      }
      var auth_already = "";
      function updateSigninStatus(isSignedIn) {
        if (isSignedIn) {
          jQuery(document).delegate("#authorize-button","click",makeApiCall);
          if(auth_already){
            auth_already = "";
            jQuery("#authorize-button").click();
          }
        }else{
          jQuery(document).delegate("#authorize-button","click",handleAuthClick);
          auth_already = 1;
        }
      }

      /**
       *  Sign in the user upon button click.
       */
      
     
     
    </script>
        <?php
            
            $lunchtime = get_post_meta($_GET["booking_id"], 'lunch_time', true);
            $beacharrivaltime = get_post_meta($_GET["booking_id"], 'beach_arrival_time', true); 
            $products_title = get_post_meta($_GET["booking_id"], 'products_title', true); 
            $products_title2 = get_post_meta($_GET["booking_id"], 'products_title2', true); 
            $price_breakdown = get_post_meta($_GET["booking_id"], '_mphb_booking_price_breakdown', true); 

            $is_gift=get_post_meta($_GET["booking_id"],'arf_is_gift',true);  

            $adults = 0; 
            $child = 0; 
            $guest_name = ""; 
            $guests = array();

            $reservedRooms = $booking->getReservedRooms();
	        $roomTypes = array();
	        $presetGuestName = array();
	        $service = "";
	        $service_arr = array();

	        $ddd = array();
            
	        $total = 0;
	        $sub_total = ""; 
	        
	        if($price_breakdown){
				$ddd = json_decode(strip_tags($price_breakdown),true);
				//echo "<pre>";print_r($ddd);die;
				if(isset($ddd['rooms'])){
					foreach ($ddd['rooms'] as $kk => $value) {
						$adults += $value['room']['adults']; 
            			$child += $value['room']['children']; 
						if(isset($value['services']['list'])){
							foreach ($value['services']['list'] as $key => $vv) {
								$service_arr[] = $vv['title'];
								$sub_total = $vv['details'];
							}	
						}
						if(isset($value['services']['total']) && $value['services']['total']){
						    //$total += $value['services']['total'];
						}
						
					}
				}
				if(isset($ddd['total']) && $ddd['total']){
				    $total = $ddd['total'];
				}
				/*if($total && $adults){
				    $sub_total = $total / $adults;
				}else{
				    $sub_total = $total;
				}*/
	        }

	        $service = implode(" , ", $service_arr);
	        	
	        foreach ($reservedRooms as $reservedRoom) {
	            $saveId = $roomTypeId = $reservedRoom->getRoomTypeId();
	            $guests[] = $reservedRoom->getGuestName();
	            if ($language !== 'original') {
	                $roomTypeId = MPHB()->translation()->translateId($roomTypeId, MPHB()->postTypes()->roomType()->getPostType(), $language);

	                if ($translateIds) {
	                    $saveId = $roomTypeId;
	                }
	            }

	            if (!array_key_exists($saveId, $roomTypes)) {
	                $roomTypes[$saveId] = get_the_title($roomTypeId);
	            }

	        }
            $reservedTypes = $roomTypes;
            $guest_name = implode(" , ",$guests);
            if (empty($reservedTypes)) {
                $accommodations = '&#8212;';
            } else {
                $links = array_map(function ($roomTypeId, $title) {
                    return '<a href="' . esc_url(get_permalink($roomTypeId)) . '">' . esc_html($title) . '</a>';
                }, array_keys($reservedTypes), $reservedTypes);
                $accommodations = implode(', ', $links);
            }
			/*if ($lunchtime == '11:59'){
				$lunchtime = "Lunch at your sunbed";
			}*/

            $return .= '<h3>Details of booking</h3>';
            $return .= '<table class="table">';
            	$return .= '<tr>';
            		$return .= '<td>ID:</td>';
            		$return .= '<td>#'.$_GET['booking_id'].'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
            		$return .= '<td>Arrival time:</td>';
            		$return .= '<td>'.$beacharrivaltime.'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
            		$return .= '<td>Lunch Time:</td>';
            		$return .= '<td>'.get_lunch_text($lunchtime).'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
                    $return .= '<td>Check-in:</td>';
                    $return .= '<td>'.$check_in.'</td>';
                $return .= '</tr>';
            	/*$return .= '<tr>';
            		$return .= '<td>Accommodation Type:</td>';
            		$return .= '<td>'.$accommodations.'</td>';
            	$return .= '</tr>';*/
            	$return .= '<tr>';
            		$return .= '<td>Adults:</td>';
            		$return .= '<td>'.$adults.'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
            		$return .= '<td>Children:</td>';
            		$return .= '<td>'.$child.'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
            		$return .= '<td>Full Guest Name:</td>';
            		$return .= '<td>'.$guest_name.'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
            		$return .= '<td>Purchased services:</td>';
            		$return .= '<td>'.$service.'</td>';
            	$return .= '</tr>';
            	$return .= '<tr>';
            		$return .= '<td>Product:</td>';
            		$return .= '<td>'.($products_title2 ? $products_title2 : ($products_title ? $products_title : "N/A")).'</td>';
            	$return .= '</tr>';
            	/*$return .= '<tr>';
            		$return .= '<td>Sub Total:</td>';
            		$return .= '<td>'.substr(str_replace(MPHB()->settings()->currency()->getCurrencySymbol(), "<br/>".MPHB()->settings()->currency()->getCurrencySymbol(), $sub_total), 5).'</td>';
            	$return .= '<///tr>';*/
            	////echo $is_gift;
            	if($is_gift=="Yes")
            		{

            		}
            		else
            			{
			            	$return .= '<tr>';
			            		$return .= '<td>Total: </td>';
			            		$return .= '<td>'.MPHB()->settings()->currency()->getCurrencySymbol().number_format($total,2).'</td>';
			            	$return .= '</tr>'; 
            	 		}

            $return .= '</table>';
            
            $return .= '<button id="authorize-button" class="btn btn-primary"><img src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/12/google.png"  /> <span style="    vertical-align: text-top;">Add To Google Calender</span></button>';
        }
    }
    return $return;
}


add_shortcode( 'mphb_booking_info_manual', 'mphb_booking_info_manual_func' );


add_shortcode( 'mphb_booking_info_place', 'mphb_booking_info_place_func' );
function mphb_booking_info_place_func(){
    $return = "";
    if(current_user_can('administrator')){
		if(!isset($_GET['booking_id'])){
		    $parts = parse_url($_SERVER['REQUEST_URI']);
	        parse_str($parts['query'], $query);
	        if(isset($query['booking_id'])){
	            $_GET['booking_id'] = $query['booking_id'];
	        }
		}
	    if(isset($_GET['booking_id']) && $_GET['booking_id']){
	        ?>
	        <style type="text/css">
	             span.place_dot {
	                   width: 20px;
	                   height: 20px;
	                   border: 1px solid #000000;
	                   background: #dddddd;
	                   display: inline-block;
	                   margin-right: 10px;
	                   border-radius: 10px;
	             }
	             span.place_dot.active {
	                    background: #ffa9c6;
	             }
	             .tbl-bg-yellow{
	                    background: #d9a407;
	             }
	             .tbl-map td{
	                    border: none;
	                        text-align: center;
	             }
	             .my_lbl{
	                   font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
	             }
	             @media only screen and (max-width: 768px) {
	                  span.place_dot {
	                           width: 13px;
	                           height: 13px;
	                           border: 1px solid #000000;
	                           background: #dddddd;
	                           display: inline-block;
	                           margin-right: 0;
	                           border-radius: 10px;
	                  }
	                  .tbl-map td {
	                      border: none;
	                      padding: 2px;
	                  }
	                  .my_lbl{
	                       font-size: 15px;
	                  }
	                  .tbl-bg-yellow{
	                       padding: 5px 7px !important
	                  }
	             }
	        </style>
	        <?php
	        $mphb_place = get_post_meta($_GET["booking_id"], 'mphb_place', true);
			$booking = mphb_get_booking($_GET['booking_id'], true);
	        if ($booking) {
	            $args = array(
	                'post_type' => 'location',
	                'posts_per_page' => -1,
	                'post_status' => 'publish',
	                'orderby' => 'post_title',
	                'meta_key' => 'location_priority',
	                'orderby'   => 'meta_value',
	                'meta_query' => array(
	                    array(
	                        'key' => 'location_type',
	                        'value' => "Basic"
	                    )
	                ),
	                'order' => 'DESC'
	            );
	    
	            $basic_locations = get_posts($args);
	            
	            $args = array(
	                'post_type' => 'location',
	                'posts_per_page' => -1,
	                'post_status' => 'publish',
	                //'orderby' => 'post_title',
	                'meta_key' => 'location_priority',
	                'orderby'   => 'meta_value',
	                'meta_query' => array(
	                    array(
	                        'key' => 'location_type',
	                        'value' => "Main"
	                    )
	                ),
	                'order' => 'DESC'
	            );
	    
	            $main_locations = get_posts($args);
	            $total_row = 0;
	            $return .= '<table class="tbl-map">';
	            $basics_row = array();
	            $main_row = array();
	            if($basic_locations){
	                foreach($basic_locations AS $key => $value){ 
	                    $return .= '<tr>';
	                    $arr = get_field("location_names",$value->ID); 
	                    $arr = explode("|",$arr);
	                    $total = 0;
	                    $colls = "";
	                    $count = 0;
	                    if($arr){
	                        $count = count($arr);
	                        $colls .= "<tr>";
	                        foreach($arr AS $key => $vvv){
	                            $rows = explode(",",$vvv);
	                            $total += count($rows);
	                            
	                            foreach($rows AS $kk => $vv){
	                                $colls .= "<td>";
	                                    $colls .= '<span class="place_dot '.(isset($mphb_place[$value->ID]) && in_array( $vv, $mphb_place[$value->ID]) ? "active" : "").'"></span>';        
	                                $colls .= "</td>";
	                            }
	                            
	                            if($count > 1 && $key != $count - 1){
	                                $colls .= "<td class='tbl-bg-yellow'>";
	                                    $colls .= '<span></span>';        
	                                $colls .= "</td>";    
	                                $total = $total+1;
	                            }
	                            $colls .= '<td colspan="{total_rows}"  class="tbl-bg-yellow">';
	                            $colls .= '</td>';
	                        }
	                        $colls .= "</tr>";
	                    }
	                        /*---$return .= '<td colspan="{total_rows}" class="tbl-bg-yellow">';
	                        $return .= '</td>';
	    		            $return .= '<td colspan="'.($total ? $total : 1).'">';
	                            $return .= '<lable class="my_lbl" for="mphb_place_'.$value->ID.'">'.$value->post_title.'</lable>';
	                        $return .= '</td>';---*/
	                    $return .= '</tr>';    
	                    $return .= $colls;
	                    $basics_row[] = ($total ? $total : 1);
	                } 
	            }else{
	                $return .= '<tr>';
	                    $return .= '<td colspan="{total_rows}"  class="tbl-bg-yellow">';
	                    $return .= '</td>';
	                $return .= '</tr>';
	            }
	            if($main_locations){
	                
	                foreach($main_locations AS $key => $value){ 
	                    $return .= '<tr>';
	                    $arr = get_field("location_names",$value->ID); 
	                    $arr = explode("|",$arr);
	                    $total = 0;
	                    $colls = "";
	                    $count = 0;
	                    $first_count = 1;
	                    $td_space = "";
	                    $td_row = array();
	                    if($arr){
	                        $count = count($arr);
	                        $colls .= "<tr>";
	                        foreach($arr AS $key => $vvv){
	                            
	                            $rows = explode(",",$vvv);
	                            $total += count($rows);
	                            if(!$key){
	                                $first_count = count($rows);
	                            }else{
	                                $td_row[] =  count($rows);
	                            }
	                            foreach($rows AS $kk => $vv){
	                                $colls .= "<td>";
	                                    $colls .= '<span class="place_dot '.(isset($mphb_place[$value->ID]) && in_array( $vv, $mphb_place[$value->ID]) ? "active" : "").'"></span>';        
	                                $colls .= "</td>";
	                            }
	                            
	                            if($count > 1 && $key != $count - 1){
	                                $colls .= "<td class='tbl-bg-yellow'>";
	                                    $colls .= '<span></span>';        
	                                $colls .= "</td>";    
	                                $td_space .= "<td class='tbl-bg-yellow'>";
	                                    $td_space .= '<span></span>';        
	                                $td_space .= "</td>";    
	                                $total = $total+1;
	                            }
	                            
	                        }
	                        $colls .= "</tr>";
	                    }
	    		            $return .= '<td colspan="'.($first_count ? $first_count : 1).'">';
	                            //$return .= '<lable class="my_lbl" for="mphb_place_'.$value->ID.'">'.$value->post_title.'</lable>';
	                        $return .= '</td>';
	                        if($arr){
	                            $return .= $td_space;
	                            $count = count($td_row);
	                            foreach($td_row AS $kk => $vv){
	                                $return .= '<td colspan="'.($vv ? $vv : 1).'">';
	                                $return .= '</td>';
	                                if($count > 1 && $kk != $count - 1){
	                                    $return .= "<td class='tbl-bg-yellow'>";
	                                        $return .= '<span></span>';        
	                                    $return .= "</td>";   
	                                }
	                            }
	                        }
	                    $return .= '</tr>';    
	                    $return .= $colls;
	                    $main_row[] = ($total ? $total : 1);
	                } 
	            }
	            $return .= '</table>';
	            $return .= '<img width="100%" src="https://booking.arienzobeachclub.com/wp-content/uploads/2021/12/imgpsh_fullsize_anim-1.jpg">';
	            
	            if($main_row && $basics_row){
	                $total_row = max($main_row) - max($basics_row);    
	            }else if($main_row){
	                $total_row = max($main_row);    
	            }else if($basics_row){
	                $total_row = max($basics_row);    
	            }else{
	                $total_row = 1;
	            }
	            $return = str_replace('{total_rows}', ($total_row ? $total_row : 1), $return);
	        }
	    }
    }
    return $return;
}


add_action('admin_init', 'cp_add_meta_boxes', 2);

function cp_add_meta_boxes() {
add_meta_box( 'gpminvoice-group', 'Custom Price', 'Repeatable_meta_box_display', 'mphb_room_service', 'normal', 'default');
}

function Repeatable_meta_box_display() {
    global $post;
    $gpminvoice_group = get_post_meta($post->ID, 'customdata_group', true);
     wp_nonce_field( 'cp_repeatable_meta_box_nonce', 'cp_repeatable_meta_box_nonce' );
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-date' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text screen-reader-text-date' );
            row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table id="repeatable-fieldset-one" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Start Date
        <input type="date"  placeholder="Start Date" name="startdate[]" value="<?php if($field['startdate'] != '') echo esc_attr( $field['startdate'] ); ?>" /></td> 
      <td width="25%">End Date
        <input type="date"  placeholder="End Date" name="enddate[]" value="<?php if($field['enddate'] != '') echo esc_attr( $field['enddate'] ); ?>" /></td> 
      <td width="25%">Rate"
        <input type="number"  placeholder="Rate" name="rate[]" value="<?php if($field['rate'] != '') echo esc_attr( $field['rate'] ); ?>" /></td> 
      <td width="25%">Child Rate
        <input type="number"  placeholder="Child Rate" name="child_rate[]" value="<?php if(isset($field['child_rate']) && $field['child_rate'] != '') echo esc_attr( $field['child_rate'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Start Date
        <input type="date" placeholder="Start Date" title="Start Date" name="startdate[]" /></td>
      <td> End Date
        <input type="date" placeholder="End Date" title="End Date" name="enddate[]" /></td>
      <td> Rate"
        <input type="number" placeholder="Rate" title="Rate" name="rate[]" /></td>
       <td> Child Rate
        <input type="number" placeholder="Child Rate" title="Child Rate" name="child_rate[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text screen-reader-text-date">
      <td> Start Date
        <input type="date" placeholder="Start Date" title="Start Date" name="startdate[]" /></td>
      <td> End Date
        <input type="date" placeholder="End Date" title="End Date" name="enddate[]" /></td>
      <td> Rate"
        <input type="number" placeholder="Rate" title="Rate" name="rate[]" /></td>
      <td> Child Rate
        <input type="number" placeholder="Child Rate" title="Child Rate" name="child_rate[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row" class="button" href="#">Add another</a></p>
 <?php
}
add_action('save_post', 'custom_repeatable_meta_box_save');
function custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['cp_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['cp_repeatable_meta_box_nonce'], 'cp_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $old = get_post_meta($post_id, 'customdata_group', true);
    $new = array();
    $startdate = $_POST['startdate'];
    $endDate = $_POST['enddate'];
    $rate = $_POST['rate'];
    $child_rate = $_POST['child_rate'];
     $count = count( $startdate );
     for ( $i = 0; $i < $count; $i++ ) {
        if ( $startdate[$i] != '' ) :
            $new[$i]['startdate'] = stripslashes( strip_tags( $startdate[$i] ) );
             $new[$i]['enddate'] = stripslashes( $endDate[$i] ); // and however you want to sanitize
             $new[$i]['rate'] = stripslashes( $rate[$i] ); // and however you want to sanitize
             $new[$i]['child_rate'] = stripslashes( $child_rate[$i] ); // and however you want to sanitize
        endif;
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'customdata_group', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'customdata_group', $old );


}

add_filter("admin_head","admin_head_fun");
function admin_head_fun(){
	?>
	<style type="text/css">
		.no-border:focus,.no-border:active{
			border: 0 !important;
		    outline: unset !important;
		    box-shadow: unset;
		}
	</style>
	<?php
}
add_filter("admin_footer","whatsapp_init");
function whatsapp_init(){
	global $post;
	if(is_admin()  && isset($_GET['post'])){
	 	$type = get_post_type($_GET['post']);
	 	if($type == "mphb_booking"){
	 		/*wp_deregister_script('jquery');
 			wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);*/

	 		?>
	 		<script type="text/javascript">
	 			jQuery(document).ready(function(){
	 				$('#mphb-mphb_phone').after('<a href="https://wa.me/' +$('#mphb-mphb_phone').val()+ '" target="_blank" class="no-border"><img src="https://booking.arienzobeachclub.com/wp-content/uploads/2022/11/whatsapp-logo-light-green-png-0.png" width="30" style="border: 0;" ></a>');
	 			})
	 		</script>
	 		<?php 
	 	}
	}

}

add_action('admin_init', 'mrs_add_meta_boxes', 2);

function mrs_add_meta_boxes() {
add_meta_box( 'mrs-group', 'Includes Per person/to share', 'mrs_repeatable_meta_box_display', 'mphb_room_service', 'normal', 'default');
add_meta_box( 'mrs-group-2', 'Includes Per person', 'mrs_repeatable_meta_box_display2', 'mphb_room_service', 'normal', 'default');
add_meta_box( 'mrs-group-3', 'Welcome Bottle quantities', 'mrs_repeatable_meta_box_display3', 'mphb_room_service', 'normal', 'default');
add_meta_box( 'mrs-group-4', 'Terms & Conditions', 'mrs_repeatable_meta_box_display4', 'mphb_room_service', 'normal', 'default');
add_meta_box( 'mrs-group-5', 'Beach Club Policy', 'mrs_repeatable_meta_box_display5', 'mphb_room_service', 'normal', 'default');
add_meta_box( 'mrs-group-6', 'Kids & Adolescents (ages 2-17 years) rates', 'mrs_repeatable_meta_box_display6', 'mphb_room_service', 'normal', 'default');
}

function mrs_repeatable_meta_box_display() {
    global $post;
    $gpminvoice_group = get_post_meta($post->ID, 'desc_includes', true);
     wp_nonce_field( 'mrs_repeatable_meta_box_nonce', 'mrs_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row-pps' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-pps' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-pps screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one-pps tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table id="repeatable-fieldset-one-pps" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Label
        <input type="text"  placeholder="Label"  name="desc_includes[]" value="<?php if($field['desc_includes'] != '') echo esc_attr( $field['desc_includes'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Label
        <input type="text" placeholder="Label"  title="Label" name="desc_includes[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-pps screen-reader-text">
      <td> Label
        <input type="text" placeholder="Label" class="" title="Label" name="desc_includes[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row-pps" class="button" href="#">Add another</a></p>
 <?php
}
function mrs_repeatable_meta_box_display2() {
    global $post;
    $gpminvoice_group = get_post_meta($post->ID, 'desc_includes2', true);
     wp_nonce_field( 'mrs_repeatable_meta_box_nonce', 'mrs_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row-pp' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-pp' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-pp screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one-pp tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table id="repeatable-fieldset-one-pp" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Label
        <input type="text"  placeholder="Label"  name="desc_includes2[]" value="<?php if($field['desc_includes2'] != '') echo esc_attr( $field['desc_includes2'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Label
        <input type="text" placeholder="Label"  title="Label" name="desc_includes2[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-pp screen-reader-text" >
      <td> Label
        <input type="text" placeholder="Label" class="" title="Label" name="desc_includes2[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row-pp" class="button" href="#">Add another</a></p>
 <?php
}
add_action('save_post', 'mrs_custom_repeatable_meta_box_save');
function mrs_custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['mrs_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['mrs_repeatable_meta_box_nonce'], 'mrs_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $old = get_post_meta($post_id, 'desc_includes', true);
    $new = array();
    $desc_includes = $_POST['desc_includes'];
    if($desc_includes){
	    $count = count( $desc_includes );
	    for ( $i = 0; $i < $count; $i++ ) {
	        if ( $desc_includes[$i] != '' ) :
	            $new[$i]['desc_includes'] = stripslashes( strip_tags( $desc_includes[$i] ) );
	        endif;
	    }
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'desc_includes', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'desc_includes', $old );

    $old = get_post_meta($post_id, 'desc_includes2', true);
    $new = array();
    $desc_includes2 = $_POST['desc_includes2'];
    if($desc_includes2){
	    $count = count( $desc_includes2 );
	    for ( $i = 0; $i < $count; $i++ ) {
	        if ( $desc_includes2[$i] != '' ) :
	            $new[$i]['desc_includes2'] = stripslashes( strip_tags( $desc_includes2[$i] ) );
	        endif;
	    }
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'desc_includes2', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'desc_includes2', $old );


    	$old = get_post_meta($post_id, 'welcome_bottle_quantities', true);
    $new = array();
    $welcome_bottle_quantities = $_POST['welcome_bottle_quantities'];
    if($welcome_bottle_quantities){
	    $count = count( $welcome_bottle_quantities );
	    for ( $i = 0; $i < $count; $i++ ) {
	        if ( $welcome_bottle_quantities[$i] != '' ) :
	            $new[$i]['welcome_bottle_quantities'] = stripslashes( strip_tags( $welcome_bottle_quantities[$i] ) );
	        endif;
	    }
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'welcome_bottle_quantities', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'welcome_bottle_quantities', $old );
	$old = get_post_meta($post_id, 'terms_conditions', true);
    $new = array();
    $terms_conditions = $_POST['terms_conditions'];
    if($terms_conditions){
	    $count = count( $terms_conditions );
	    for ( $i = 0; $i < $count; $i++ ) {
	        if ( $terms_conditions[$i] != '' ) :
	            $new[$i]['terms_conditions'] = stripslashes( strip_tags( $terms_conditions[$i] ) );
	        endif;
	    }
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'terms_conditions', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'terms_conditions', $old );
	$old = get_post_meta($post_id, 'beach_club_policy', true);
    $new = array();
    $beach_club_policy = $_POST['beach_club_policy'];
    if($beach_club_policy){
	    $count = count( $beach_club_policy );
	    for ( $i = 0; $i < $count; $i++ ) {
	        if ( $beach_club_policy[$i] != '' ) :
	            $new[$i]['beach_club_policy'] = stripslashes( strip_tags( $beach_club_policy[$i] ) );
	        endif;
	    }
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'beach_club_policy', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'beach_club_policy', $old );
	$old = get_post_meta($post_id, 'kids_adolescents_ages_2_17_years_rates', true);
    $new = array();
    $kids_adolescents_ages_2_17_years_rates = $_POST['kids_adolescents_ages_2_17_years_rates'];
    if($kids_adolescents_ages_2_17_years_rates){
	    $count = count( $kids_adolescents_ages_2_17_years_rates );
	    for ( $i = 0; $i < $count; $i++ ) {
	        if ( $kids_adolescents_ages_2_17_years_rates[$i] != '' ) :
	            $new[$i]['kids_adolescents_ages_2_17_years_rates'] = stripslashes( strip_tags( $kids_adolescents_ages_2_17_years_rates[$i] ) );
	        endif;
	    }
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'kids_adolescents_ages_2_17_years_rates', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'kids_adolescents_ages_2_17_years_rates', $old );


    update_post_meta( $post_id, 'welcome_bottle_quantities_title', isset($_POST['welcome_bottle_quantities_title']) ? $_POST['welcome_bottle_quantities_title'] : "" );
	update_post_meta( $post_id, 'terms_conditions_title', isset($_POST['terms_conditions_title']) ? $_POST['terms_conditions_title'] : "" );
	update_post_meta( $post_id, 'beach_club_policy_title', isset($_POST['beach_club_policy_title']) ? $_POST['beach_club_policy_title'] : "" );
	update_post_meta( $post_id, 'kids_adolescents_ages_2_17_years_rates_title', isset($_POST['kids_adolescents_ages_2_17_years_rates_title']) ? $_POST['kids_adolescents_ages_2_17_years_rates_title'] : "" );
}

function mrs_repeatable_meta_box_display3() {
    global $post;
    $welcome_bottle_quantities_title = get_post_meta($post->ID, 'welcome_bottle_quantities_title', true);
    $gpminvoice_group = get_post_meta($post->ID, 'welcome_bottle_quantities', true);
     wp_nonce_field( 'mrs_repeatable_meta_box_nonce', 'mrs_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row-welcome_bottle_quantities' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-welcome_bottle_quantities' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-welcome_bottle_quantities screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one-welcome_bottle_quantities tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table  width="100%">
  <tbody>
    <tr>
      <td colspan="2">
        <label>Title</label>
        <br/>
        <input type="text" placeholder="Title"  name="welcome_bottle_quantities_title" value="<?php  echo esc_attr( $welcome_bottle_quantities_title ); ?>">
      </td>
    </tr>
  </tbody>
  </table>
  <h4>Data</h4>
  <table id="repeatable-fieldset-one-welcome_bottle_quantities" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Label
        <input type="text"  placeholder="Label"  name="welcome_bottle_quantities[]" value="<?php if($field['welcome_bottle_quantities'] != '') echo esc_attr( $field['welcome_bottle_quantities'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Label
        <input type="text" placeholder="Label"  title="Label" name="welcome_bottle_quantities[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-welcome_bottle_quantities screen-reader-text" >
      <td> Label
        <input type="text" placeholder="Label" class="" title="Label" name="welcome_bottle_quantities[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row-welcome_bottle_quantities" class="button" href="#">Add another</a></p>
 <?php
}

function mrs_repeatable_meta_box_display4() {
    global $post;
    $terms_conditions_title = get_post_meta($post->ID, 'terms_conditions_title', true);
    $gpminvoice_group = get_post_meta($post->ID, 'terms_conditions', true);
     wp_nonce_field( 'mrs_repeatable_meta_box_nonce', 'mrs_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row-terms_conditions' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-terms_conditions' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-terms_conditions screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one-terms_conditions tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table  width="100%">
  <tbody>
    <tr>
      <td colspan="2">
        <label>Title</label>
        <br/>
        <input type="text" placeholder="Title"  name="terms_conditions_title" value="<?php  echo esc_attr( $terms_conditions_title ); ?>">
      </td>
    </tr>
  </tbody>
  </table>
  <h4>Data</h4>
  <table id="repeatable-fieldset-one-terms_conditions" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Label
        <input type="text"  placeholder="Label"  name="terms_conditions[]" value="<?php if($field['terms_conditions'] != '') echo esc_attr( $field['terms_conditions'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Label
        <input type="text" placeholder="Label"  title="Label" name="terms_conditions[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-terms_conditions screen-reader-text" >
      <td> Label
        <input type="text" placeholder="Label" class="" title="Label" name="terms_conditions[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row-terms_conditions" class="button" href="#">Add another</a></p>
 <?php
}


function mrs_repeatable_meta_box_display5() {
    global $post;
    $beach_club_policy_title = get_post_meta($post->ID, 'beach_club_policy_title', true);
    $gpminvoice_group = get_post_meta($post->ID, 'beach_club_policy', true);
     wp_nonce_field( 'mrs_repeatable_meta_box_nonce', 'mrs_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row-beach_club_policy' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-beach_club_policy' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-beach_club_policy screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one-beach_club_policy tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table  width="100%">
  <tbody>
    <tr>
      <td colspan="2">
        <label>Title</label>
        <br/>
        <input type="text" placeholder="Title"  name="beach_club_policy_title" value="<?php  echo esc_attr( $beach_club_policy_title ); ?>">
      </td>
    </tr>
  </tbody>
  </table>
  <h4>Data</h4>
  <table id="repeatable-fieldset-one-beach_club_policy" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Label
        <input type="text"  placeholder="Label"  name="beach_club_policy[]" value="<?php if($field['beach_club_policy'] != '') echo esc_attr( $field['beach_club_policy'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Label
        <input type="text" placeholder="Label"  title="Label" name="beach_club_policy[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-beach_club_policy screen-reader-text" >
      <td> Label
        <input type="text" placeholder="Label" class="" title="Label" name="beach_club_policy[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row-beach_club_policy" class="button" href="#">Add another</a></p>
 <?php
}

function mrs_repeatable_meta_box_display6() {
    global $post;
    $kids_adolescents_ages_2_17_years_rates_title = get_post_meta($post->ID, 'kids_adolescents_ages_2_17_years_rates_title', true);
    $gpminvoice_group = get_post_meta($post->ID, 'kids_adolescents_ages_2_17_years_rates', true);
     wp_nonce_field( 'mrs_repeatable_meta_box_nonce', 'mrs_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row-kids_adolescents_ages_2_17_years_rates' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-kids_adolescents_ages_2_17_years_rates' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-kids_adolescents_ages_2_17_years_rates screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one-kids_adolescents_ages_2_17_years_rates tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table  width="100%">
  <tbody>
    <tr>
      <td colspan="2">
        <label>Title</label>
        <br/>
        <input type="text" placeholder="Title"  name="kids_adolescents_ages_2_17_years_rates_title" value="<?php  echo esc_attr( $kids_adolescents_ages_2_17_years_rates_title ); ?>">
      </td>
    </tr>
  </tbody>
  </table>
  <h4>Data</h4>
  <table id="repeatable-fieldset-one-kids_adolescents_ages_2_17_years_rates" width="100%">
  <tbody>
    <?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
    <tr>
      <td width="25%">Label
        <input type="text"  placeholder="Label"  name="kids_adolescents_ages_2_17_years_rates[]" value="<?php if($field['kids_adolescents_ages_2_17_years_rates'] != '') echo esc_attr( $field['kids_adolescents_ages_2_17_years_rates'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Label
        <input type="text" placeholder="Label"  title="Label" name="kids_adolescents_ages_2_17_years_rates[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-kids_adolescents_ages_2_17_years_rates screen-reader-text" >
      <td> Label
        <input type="text" placeholder="Label" class="" title="Label" name="kids_adolescents_ages_2_17_years_rates[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row-kids_adolescents_ages_2_17_years_rates" class="button" href="#">Add another</a></p>
 <?php
}
