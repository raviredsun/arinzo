<?php

namespace MPHB\PostTypes;

use \MPHB\Admin\Fields;
use \MPHB\Admin\Groups;
use \MPHB\Admin\ManageCPTPages;
use \MPHB\Entities;

class ServiceCPT extends EditableCPT {

	protected $postType = 'mphb_room_service';

	protected function addActions(){
		parent::addActions();
		add_action( 'after_setup_theme', array( $this, 'addFeaturedImageSupport' ), 11 );

		add_filter( 'post_class', array( $this, 'filterPostClass' ), 20, 3 );
		add_action( 'loop_start', array( $this, 'setupPseudoTemplate' ) );

        add_filter( 'use_block_editor_for_post_type', array( $this, 'useBlockEditor' ), 10, 2 );
	}

    public function useBlockEditor($useBlockEditor, $postType)
    {
        if ($postType == $this->postType) {
            $useBlockEditor = MPHB()->settings()->main()->useBlockEditorForServices();
        }

        return $useBlockEditor;
    }

	protected function createManagePage(){
		return new ManageCPTPages\ServiceManageCPTPage( $this->postType );
	}

	/**
	 *
	 * @param \WP_Query $query
	 * @return null
	 */
	public function setupPseudoTemplate( $query ){
		// append meta to single service page's query & service listing queries except our shortcodes output
		if ( $query->is_main_query() &&
			$query->get( 'post_type' ) === $this->postType
		) {
			$query->set( 'mphb_append_meta', true );
			add_filter( 'the_content', array( $this, 'appendMetas' ) );
			remove_action( 'loop_start', array( $this, 'setupPseudoTemplate' ) );
			add_action( 'loop_end', array( $this, 'stopAppendMetas' ) );
		}
	}

	/**
	 * Append metas to service content.
	 *
	 * @param string $content
	 * @return string
	 */
	public function appendMetas( $content ){

		if ( is_main_query() &&
			get_query_var( 'mphb_append_meta' ) &&
			get_post_type() === $this->postType
		) {
			ob_start();
			\MPHB\Views\SingleServiceView::_renderMetas();
			$content .= ob_get_clean();
		}

		return $content;
	}

	public function stopAppendMetas( $query ){
		if ( $query->is_main_query() &&
			$query->get( 'mphb_append_meta' )
		) {
			remove_filter( 'the_content', array( $this, 'appendMetas' ) );
			remove_filter( 'loop_end', array( $this, 'stopAppendMetas' ) );
		}
	}

	public function register(){

		$labels = array(
			'name'					 => __( 'Services', 'motopress-hotel-booking' ),
			'singular_name'			 => __( 'Service', 'motopress-hotel-booking' ),
			'add_new'				 => _x( 'Add New', 'Add New Service', 'motopress-hotel-booking' ),
			'add_new_item'			 => __( 'Add New Service', 'motopress-hotel-booking' ),
			'edit_item'				 => __( 'Edit Service', 'motopress-hotel-booking' ),
			'new_item'				 => __( 'New Service', 'motopress-hotel-booking' ),
			'view_item'				 => __( 'View Service', 'motopress-hotel-booking' ),
			'search_items'			 => __( 'Search Service', 'motopress-hotel-booking' ),
			'not_found'				 => __( 'No services found', 'motopress-hotel-booking' ),
			'not_found_in_trash'	 => __( 'No services found in Trash', 'motopress-hotel-booking' ),
			'all_items'				 => __( 'Services', 'motopress-hotel-booking' ),
			'insert_into_item'		 => __( 'Insert into service description', 'motopress-hotel-booking' ),
			'uploaded_to_this_item'	 => __( 'Uploaded to this service', 'motopress-hotel-booking' )
		);

		$args = array(
			'labels'				 => $labels,
			'public'				 => true,
			'publicly_queryable'	 => true,
			'show_ui'				 => true,
			'capability_type'		 => 'post',
			'has_archive'			 => true,
			'hierarchical'			 => false,
			'show_in_menu'			 => MPHB()->postTypes()->roomType()->getMenuSlug(),
			'supports'				 => array( 'title', 'editor', 'page-attributes', 'thumbnail', 'comments' ),
			'register_meta_box_cb'	 => array( $this, 'registerMetaBoxes' ),
			'rewrite'				 => array(
				//translators: do not translate
				'slug'		 => _x( 'service', 'slug', 'motopress-hotel-booking' ),
				'with_front' => false,
				'feeds'		 => true
			),
			'query_var'				 => true,
            'show_in_rest'           => true
		);

		register_post_type( $this->postType, $args );
	}

	public function getFieldGroups(){
		$priceGroup			 = new Groups\MetaBoxGroup( 'mphb_price', __( 'Price', 'motopress-hotel-booking' ), $this->postType );
		$regularPriceField	 = Fields\FieldFactory::create(
				'mphb_price', array(
				'type'		 => 'number',
				'label'		 => __( 'Price (Adult)', 'motopress-hotel-booking' ),
				'default'	 => 0,
				'step'		 => 0.01,
				'min'		 => 0,
				'size'		 => 'price',
				)
		);
		$priceGroup->addField( $regularPriceField );

		$childPriceField	 = Fields\FieldFactory::create(
				'mphb_child_price', array(
				'type'		 => 'number',
				'label'		 => __( 'Price (Child)', 'motopress-hotel-booking' ),
				'default'	 => 0,
				'step'		 => 0.01,
				'min'		 => 0,
				'size'		 => 'price',
				)
		);
		$priceGroup->addField( $childPriceField );


		$blockDateField	 = Fields\FieldFactory::create(
				'mphb_block_dates', array(
				'type'		 => 'textarea',
				'default'	 => 0,
				'rows' => "10",
				'label'		 => __( 'Block Dates', 'motopress-hotel-booking' ),
				'default'	 => "",
				'description'	 => __( 'Enter Muptiple Date Seprated By "," Date Formate is YYYY-mm-dd', 'motopress-hotel-booking' ),
				)
		);
		$priceGroup->addField( $blockDateField );

		$pricePeriodicityField = Fields\FieldFactory::create(
				'mphb_show_child', array(
				'type'			 => 'select',
				'label'			 => __( 'Show Child', 'motopress-hotel-booking' ),
				'list'			 => array(
					''		 => __( 'No', 'motopress-hotel-booking' ),
					'1'	 => __( 'Yes', 'motopress-hotel-booking' ),
				),
				'description'	 => __( 'Show Child Selection.', 'motopress-hotel-booking' ),
				'default'		 => '',
				)
		);
		$priceGroup->addField( $pricePeriodicityField );

		$couplePackageField = Fields\FieldFactory::create(
				'mphb_couple_package', array(
				'type'			 => 'select',
				'label'			 => __( 'Is Couple Package', 'motopress-hotel-booking' ),
				'list'			 => array(
					''		 => __( 'No', 'motopress-hotel-booking' ),
					'1'	 => __( 'Yes', 'motopress-hotel-booking' ),
				),
				'default'		 => '',
				)
		);
		$priceGroup->addField( $couplePackageField );

		$pricePeriodicityField = Fields\FieldFactory::create(
				'mphb_price_periodicity', array(
				'type'			 => 'select',
				'label'			 => __( 'Periodicity', 'motopress-hotel-booking' ),
				'list'			 => array(
					'once'		 => __( 'Once', 'motopress-hotel-booking' ),
					'per_night'	 => __( 'Per Day', 'motopress-hotel-booking' ),
					'flexible'   => __( 'Guest Choice', 'motopress-hotel-booking' )
				),
				'description'	 => __( 'How many times the customer will be charged.', 'motopress-hotel-booking' ),
				'default'		 => 'once',
				)
		);
		$priceGroup->addField( $pricePeriodicityField );

        $priceGroup->addField(Fields\FieldFactory::create(
            'mphb_min_quantity',
            array(
                'type'      => 'number',
                'label'     => __('Minimum', 'motopress-hotel-booking'),
                'default'   => 1,
                'min'       => 1,
                'step'      => 1,
                'size'      => 'price'
            )
        ));

        $priceGroup->addField(Fields\FieldFactory::create(
            'mphb_is_auto_limit',
            array(
                'type'          => 'checkbox',
                'label'         => __('Maximum', 'motopress-hotel-booking'),
                'inner_label'   => __('Use the length of stay as the maximum value.'),
                'default'       => false
            )
        ));

        $priceGroup->addField(Fields\FieldFactory::create(
            'mphb_max_quantity',
            array(
                'type'          => 'number',
                'description'   => __('Empty means unlimited', 'motopress-hotel-booking'),
                'default'       => '',
                'min'           => 0,
                'step'          => 1,
                'size'          => 'price'
            )
        ));

		$priceQuantityField = Fields\FieldFactory::create(
				'mphb_price_quantity', array(
				'type'		 => 'select',
				'label'		 => __( 'Charge', 'motopress-hotel-booking' ),
				'list'		 => array(
					'once'		 => __( 'Per Accommodation', 'motopress-hotel-booking' ),
					'per_adult'	 => __( 'Per Guest', 'motopress-hotel-booking' )
				),
				'default'	 => 'once',
				)
		);




		$priceGroup->addField( $priceQuantityField );

		return array( $priceGroup );
	}

	public function addFeaturedImageSupport(){
		$supportedTypes = get_theme_support( 'post-thumbnails' );
		if ( $supportedTypes === false ) {
			add_theme_support( 'post-thumbnails', array( $this->postType ) );
		} elseif ( is_array( $supportedTypes ) ) {
			$supportedTypes[0][] = $this->postType;
			add_theme_support( 'post-thumbnails', $supportedTypes[0] );
		}
	}

	public function filterPostClass( $classes, $class = '', $postId = '' ){

		if ( $postId !== '' && get_post_type( $postId ) === $this->getPostType() ) {

			$service = MPHB()->getServiceRepository()->findById( $postId );

			if ( !$service ) {
				return $classes;
			}

			if ( $service->isFree() ) {
				$classes[] = 'mphb-service-free';
			}

			if ( $service->isPayPerAdult() ) {
				$classes[] = 'mphb-service-pay-per-adult';
			}

			if ( $service->isPayPerNight() ) {
				$classes[] = 'mphb-service-pay-per-night';
			} else if ($service->isFlexiblePay()) {
                $classes[] = 'mphb-service-pay-by-guest-choise';
            }

			if ( !is_single() && !is_search() && !is_archive() && false !== ( $key = array_search( 'hentry', $classes ) ) ) {
				unset( $classes[$key] );
			}
		}

		return $classes;
	}

}
