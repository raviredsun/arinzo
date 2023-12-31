<?php

namespace MPHB\PostTypes\BookingCPT;

use \MPHB\Utils;

class Logs {

	protected $postType;
	protected $commentType;

	public function __construct( $postType ){

		$this->postType		 = $postType;
		$this->commentType	 = $postType . '_log';
		// Hide Logs
		add_action( 'mphb_booking_before_get_logs', array( $this, 'removeHideFromCommentsActions' ) );
		add_action( 'mphb_booking_after_get_logs', array( $this, 'addHideFromCommentsActions' ) );
		add_filter( 'comment_feed_where', array( $this, 'hideFromFeed' ), 10, 2 );
		add_filter( 'wp_count_comments', array( $this, 'fixCommentsCount' ), 11, 2 );

		$this->addHideFromCommentsActions();
	}

	public function addHideFromCommentsActions(){
		add_action( 'pre_get_comments', array( $this, 'hideFromComments' ), 10 );
		add_filter( 'comments_clauses', array( $this, 'hideFromComments_pre41' ), 10, 2 );
	}

	public function removeHideFromCommentsActions(){
		remove_action( 'pre_get_comments', array( $this, 'hideFromComments' ), 10 );
		remove_filter( 'comments_clauses', array( $this, 'hideFromComments_pre41' ), 10, 2 );
	}

	/**
	 * Exclude logs from comments
	 *
	 * @param \WP_Comment_Query $query
	 */
	function hideFromComments( $query ){
		global $wp_version;

		if ( MPHB()->isWPVersion( '4.1', '>=' ) ) {
			$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
			if ( !is_array( $types ) ) {
				$types = array( $types );
			}
			$types[] = $this->commentType;

			$query->query_vars['type__not_in'] = $types;
		}
	}

	/**
	 * Exclude logs from comments
	 *
	 * @param array $clauses Comment clauses for comment query
	 * @param \WP_Comment_Query $wp_comment_query
	 * @return array $clauses Updated comment clauses
	 */
	function hideFromComments_pre41( $clauses, $wp_comment_query ){
		global $wp_version;
		if ( MPHB()->isWPVersion( '4.1', '<' ) ) {
			$clauses['where'] .= sprintf( ' AND comment_type != "%s"', $this->commentType );
		}
		return $clauses;
	}

	/**
	 * Exclude logs from comment feeds
	 *
	 * @param array $where
	 * @param \WP_Comment_Query $wp_comment_query
	 * @return array $where
	 */
	function hideFromFeed( $where, $wp_comment_query ){
		global $wpdb;

		$where .= $wpdb->prepare( " AND comment_type != %s", $this->postType );
		return $where;
	}

	/**
	 * Remove logs from the wp_count_comments function
	 *
	 * @param array $stats
	 * @param int $postId Post ID
	 * @return array Array of comment counts
	 */
	function fixCommentsCount( $stats, $postId ){
		global $wpdb, $pagenow;

		if ( 0 === $postId ) {

			$stats = wp_cache_get( "comments-{$postId}", 'counts' );

			if ( $stats === false ) {

				$hiddenCommentTypes = array( $this->commentType );

				if ( Utils\ThirdPartyPluginsUtils::isActiveWoocommerce() ) {
					$hiddenCommentTypes[]	 = 'order_note';
					$hiddenCommentTypes[]	 = 'webhook_delivery';
				}

				if ( Utils\ThirdPartyPluginsUtils::isActiveEDD() ) {
					$hiddenCommentTypes[] = 'edd_payment_note';
				}

				if ( count( $hiddenCommentTypes ) == 1 ) {
					$where = sprintf( 'WHERE comment_type != "%s"', reset( $hiddenCommentTypes ) );
				} else {
					$where = sprintf( 'WHERE comment_type NOT IN ( "%s" )', join( '","', $hiddenCommentTypes ) );
				}

                /*
                
                    Qadisha - QD - Remove slow count
                
				$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS total FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );
                */
                $count = 0;
                
                
				$stats = array(
					'approved'		 => 0,
					'moderated'		 => 0,
					'spam'			 => 0,
					'trash'			 => 0,
					'post-trashed'	 => 0,
					'total_comments' => 0,
					'all'			 => 0
				);

				foreach ( (array) $count as $row ) {
					switch ( $row['comment_approved'] ) {
						case 'trash':
							$stats['trash']			 = $row['total'];
							break;
						case 'post-trashed':
							$stats['post-trashed']	 = $row['total'];
							break;
						case 'spam':
							$stats['spam']			 = $row['total'];
							$stats['total_comments'] += $row['total'];
							break;
						case '1':
							$stats['approved']		 = $row['total'];
							$stats['total_comments'] += $row['total'];
							$stats['all'] += $row['total'];
							break;
						case '0':
							$stats['moderated']		 = $row['total'];
							$stats['total_comments'] += $row['total'];
							$stats['all'] += $row['total'];
							break;
						default:
							break;
					}
				}

				$stats = (object) $stats;
				wp_cache_set( "comments-{$postId}", $stats, 'counts' );
			}
		}
		return $stats;
	}

}
