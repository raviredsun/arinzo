<?php

namespace MPHB\Admin\MenuPages;

use MPHB\Admin\MenuPages\EditBooking;
use MPHB\Entities\Booking;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class EditBookingMenuPage extends AbstractMenuPage
{
    /** @var string */
    protected $currentStep = '';

    /** @var string */
    protected $nextStep = '';

    /** @var EditBooking\StepControl|null */
    protected $stepControl = null;

    /** @var Booking|null */
    protected $editBooking = null;

    /** @var string[] */
    protected $errors = array();

    public function onLoad()
    {
        if (!$this->isCurrentPage()) {
            return;
        }

        try {
            $this->editBooking = $this->findBooking();
            $this->stepControl = $this->detectStep();

            $this->stepControl->setup();

        } catch (\Exception $e) {
            $this->errors = explode(PHP_EOL, $e->getMessage());
        }
    }

    /**
     * @return Booking
     * @throws Error If the booking is not set or not found. 
     */
    protected function findBooking()
    {
        if (!isset($_GET['booking_id'])) {
            throw new Error(__('The booking is not set.', 'motopress-hotel-booking'));
        }

        $bookingId = mphb_posint($_GET['booking_id']);
        $booking = mphb_get_booking($bookingId);

        if (is_null($booking)) {
            throw new Error(__('The booking not found.', 'motopress-hotel-booking'));
        }

        return $booking;
    }

    /**
     * @return EditBooking\StepControl
     */
    protected function detectStep()
    {
        $stepsSequence = array(
            // Current step => next step
            'edit'     => 'summary',
            'summary'  => 'checkout',
            'checkout' => 'booking',
            'booking'  => '' // No matter
        );

        $currentStep = 'edit';
        $currentStep = 'checkout';
        if(isset($_GET['edit_accomodation']) && $_GET['edit_accomodation']){
            $currentStep = 'edit';
        }
        if (isset($_POST['step']) && in_array($_POST['step'], array_keys($stepsSequence))) {
            $currentStep = $_POST['step'];
        }

        $this->currentStep = $currentStep;
        $this->nextStep = $stepsSequence[$currentStep];

        switch ($currentStep) {
            case 'edit': return new EditBooking\EditControl($this->editBooking); break;
            case 'summary': return new EditBooking\SummaryControl($this->editBooking); break;
            case 'checkout': return new EditBooking\CheckoutControl($this->editBooking); break;
            case 'booking': return new EditBooking\BookingControl($this->editBooking); break;
            default: return new EditBooking\StepControl($this->editBooking); break;
        }
    }

    public function render()
    {
        $backUrl = $this->getBackUrl();

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php printf(__('Edit Booking #%d', 'motopress-hotel-booking'), $this->editBooking->getId()); ?></h1>

            <?php if (!empty($backUrl)) { ?>
                <a href="<?php echo esc_url($backUrl); ?>" class="page-title-action"><?php $this->currentStep == 'edit' ? _e('Cancel', 'motopress-hotel-booking') : _e('Back', 'motopress-hotel-booking'); ?></a>
            <?php } ?>

            <hr class="wp-header-end">

            <div class="mphb-edit-booking <?php echo esc_attr($this->currentStep); ?>">
                <?php
                if (empty($this->errors)) {
                    $this->renderValid();
                    if($this->currentStep == "checkout"){
                        $return = "";
    
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
                                        min-width: 10px;
                                 }
                                 .tbl-map td{
                                        border: none;
                                            text-align: center;
                                            padding: 0.5em;
                                 }
                                 .tbl-map{
                                     margin: 10px 0 1rem;
                                    border-collapse: collapse;
                                    width: 100%;
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
                                            /*$return .= '<td colspan="{total_rows}" class="tbl-bg-yellow">';
                                            $return .= '</td>';
                        		            $return .= '<td colspan="'.($total ? $total : 1).'">';
                                                $return .= '<lable class="my_lbl" for="mphb_place_'.$value->ID.'">'.$value->post_title.'</lable>';
                                            $return .= '</td>';*/
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
                        echo $return;
                    }
                } else {
                    $this->renderInvalid();
                }
                ?>
            </div>
        </div>
        <?php
    }

    protected function renderValid()
    {
        do_action('mphb_edit_booking_before_valid_step', $this->editBooking, $this->currentStep);

        // See MPHB\Admin\MenuPages\EditBooking\*Control
        do_action('mphb_edit_booking_form', $this->editBooking, array(
            'current_step' => $this->currentStep,
            'next_step'    => $this->nextStep,
            'action_url'   => $this->getUrl()
        ));

        do_action('mphb_edit_booking_after_valid_step', $this->editBooking, $this->currentStep);
    }

    protected function renderInvalid()
    {
        do_action('mphb_edit_booking_before_invalid_step', $this->errors, $this->currentStep);

        mphb_get_template_part('edit-booking/errors', array('errors' => $this->errors));

        do_action('mphb_edit_booking_after_invalid_step', $this->errors, $this->currentStep);
    }

    /**
     * @return string Back URL or empty string "".
     */
    protected function getBackUrl()
    {
        if (is_null($this->editBooking)) {
            return '';
        }

        switch ($this->currentStep) {
            case 'edit':
                return get_edit_post_link($this->editBooking->getId());
                break;

            case 'summary':
            case 'checkout':
            /*case 'booking':
                return $this->getUrl(); break;*/
            case 'booking':
                return get_edit_post_link($this->editBooking->getId());
                break;

            default: return ''; break;
        }
    }

    public function getUrl($moreArgs = array())
    {
        if (!is_null($this->editBooking)) {
            $moreArgs['booking_id'] = $this->editBooking->getId();
        }

        if (isset($_GET['lang'])) {
            $moreArgs['lang'] = sanitize_text_field($_GET['lang']);
        }

        return parent::getUrl($moreArgs);
    }

    /**
     * @return string
     */
    protected function getPageTitle()
    {
        return __('Edit Booking', 'motopress-hotel-booking');
    }

    /**
     * @return string
     */
    protected function getMenuTitle()
    {
        return __('Edit Booking', 'motopress-hotel-booking');
    }
}
