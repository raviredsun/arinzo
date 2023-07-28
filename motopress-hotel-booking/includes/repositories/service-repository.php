<?php
namespace MPHB\Repositories;
use \MPHB\Entities;
use \MPHB\Utils\ValidateUtils;
class ServiceRepository extends AbstractPostRepository {
	protected $type = 'service';
	/**
	 *
	 * @param int $id
	 * @param bool $force Optional. FALSE by defautl.
	 * @return Entities\Service
	 */
	public function findById( $id, $force = false ){
		return parent::findById( $id, $force );
	}
	public function mapPostToEntity( $post ){
		if ( is_a( $post, '\WP_Post' ) ) {
			$id = $post->ID;
		} else {
			$id		 = absint( $post );
			$post	 = get_post( $id );
		}

		
		$price = get_post_meta( $id, 'mphb_price', true );
		$child_price = get_post_meta( $id, 'mphb_child_price', true );
		$price_group = get_post_meta($id, 'customdata_group', true);
		if($price_group){
			if(isset($_POST['check_in_date']) && $_POST['check_in_date']){
			    $check_in_date = str_replace('/', '-', $_POST['check_in_date']);
				$post_date = strtotime(date("Y-m-d",strtotime($check_in_date)));
				foreach ($price_group as $key => $value) {
					if($post_date >= strtotime($value['startdate']) && $post_date <= strtotime($value['enddate'])){
						if($value['rate']){
							$price = $value['rate'];
						}
						if($value['child_rate']){
							$child_price = $value['child_rate'];
						}
					}
				}
			}
			if(isset($_GET['formValues']['mphb_check_in_date']) && $_GET['formValues']['mphb_check_in_date']){
			    $check_in_date = str_replace('/', '-', $_GET['formValues']['mphb_check_in_date']);
				$post_date = strtotime(date("Y-m-d",strtotime($check_in_date)));
				foreach ($price_group as $key => $value) {
					if($post_date >= strtotime($value['startdate']) && $post_date <= strtotime($value['enddate'])){
						if($value['rate']){
							$price = $value['rate'];
						}
						if($value['child_rate']){
							$child_price = $value['child_rate'];
						}
					}
				}
			}
		}
		
		/*if(isset($_GET['test'])){
		    echo "<pre>";print_r($price);die;    
		}*/
		if(!$child_price){
			$child_price = $price;
		}
		$periodicity = get_post_meta( $id, 'mphb_price_periodicity', true );
		if ( empty( $periodicity ) ) {
			$periodicity = 'once';
		}
        $minQuantity = get_post_meta($id, 'mphb_min_quantity', true);
        $minQuantity = ValidateUtils::parseInt($minQuantity, 1);
        $maxQuantity = get_post_meta($id, 'mphb_max_quantity', true);
        if ($maxQuantity !== '') {
            $maxQuantity = ValidateUtils::parseInt($maxQuantity, 0);
        }
        $isAutoLimit = get_post_meta($id, 'mphb_is_auto_limit', true);
        $isAutoLimit = ValidateUtils::validateBool($isAutoLimit);
		$repeat = get_post_meta( $id, 'mphb_price_quantity', true );
		if ( empty( $repeat ) ) {
			$repeat = 'once';
		}
		$show_child = get_post_meta( $id, 'mphb_show_child', true );
		$couple_package = get_post_meta( $id, 'mphb_couple_package', true );
		$atts = array(
			'id'			 => $id,
			'original_id'	 => MPHB()->translation()->getOriginalId( $id, MPHB()->postTypes()->service()->getPostType() ),
			'title'			 => get_the_title( $id ),
			'description'	 => get_post_field( 'post_content', $id ),
			'price'			 => $price ? floatval( $price ) : 0.0,
			'child_price'	 => $child_price ? floatval( $child_price ) : 0.0,
			'periodicity'	 => $periodicity,
			'show_child'	 => $show_child,
			'couple_package'	 => $couple_package,
            'min_quantity'   => $minQuantity,
            'max_quantity'   => $maxQuantity,
            'is_auto_limit'  => $isAutoLimit,
			'repeat'		 => $repeat
		);
		return Entities\Service::create( $atts );
	}
	/**
	 *
	 * @param Entities\Service $entity
	 * @return \MPHB\Entities\WPPostData
	 */
	public function mapEntityToPostData( $entity ){
		$postAtts = array(
			'ID'		 => $entity->getId(),
			'post_metas' => array(),
			'post_type'	 => MPHB()->postTypes()->service()->getPostType(),
		);
		$postAtts['post_metas'] = array(
			'mphb_price'                => $entity->getPrice(),
			'mphb_price_periodicity'    => $entity->getPeriodicity(),
            'mphb_min_quantity'         => $entity->getMinQuantity(),
            'mphb_max_quantity'         => $entity->getMaxQuantity(),
            'mphb_is_auto_limit'        => $entity->isAutoLimit(),
			'mphb_price_quantity'       => $entity->getRepeatability()
		);
		return new Entities\WPPostData( $postAtts );
	}
}
