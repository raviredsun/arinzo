<?php
/**
 * Available variables
 * - string $id
 * - string $actionUrl Action URL for search form
 * - string $checkInDate
 * - string $checkOutDate
 * - int $roomTypeId
 * - int $adults
 * - int $children
 * - array $adultsList
 * - array $childrenList
 * - array $roomsList The list of accommodation types
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$jsDateFormat = MPHB()->settings()->dateTime()->getDateFormatJS();

/** @hooked None */
do_action( 'mphb_cb_search_form_before_start' );

?>

    <form method="GET" class="mphb_cb_search_form mphb-search-form" action="<?php echo esc_url( $actionUrl ) ?>">

        <?php
        /**
         * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printQueryHiddenFields - 10
         */
        do_action( 'mphb_cb_search_form_after_start' );
        ?>

        <p class="mphb-check-in-date">
            <label for="<?php echo esc_attr( 'mphb_check_in_date-' . $id ); ?>">
                <?php _e( 'Date', 'motopress-hotel-booking' ); ?>
                <abbr title="<?php printf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), $jsDateFormat ); ?>">*</abbr>
            </label>
            <br />
            <?php // Skip name in date input (see [MB-397]) ?>
            <input
                    id="<?php echo esc_attr( 'mphb_check_in_date-' . $id ); ?>"
                    class="mphb-datepick"
                    type="text"
                    value="<?php echo esc_attr( $checkInDate ); ?>"
                    placeholder="<?php _e( 'Date', 'motopress-hotel-booking' ); ?>"
                    required="required"
                    autocomplete="off"
                    data-datepick-group="<?php echo esc_attr( $id ); ?>"
            />
        </p>
        <?php
        /*
         * Qadisha - Add style="display:none;"
         * This is to remove the check-out date from the backend
         */
        ?>
        <p class="mphb-check-out-date" style="display:none;">
            <label for="<?php echo esc_attr( 'mphb_check_out_date-' . $id ); ?>">
                <?php _e( 'Check-out', 'motopress-hotel-booking' ); ?>
                <abbr title="<?php printf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), $jsDateFormat ); ?>">*</abbr>
            </label>
            <br />
            <?php // Skip name in date input (see [MB-397]) ?>
            <input
                    id="<?php echo esc_attr( 'mphb_check_out_date-' . $id ); ?>"
                    class="mphb-datepick"
                    type="text"
                    value="<?php echo esc_attr( $checkOutDate ); ?>"
                    placeholder="<?php _e( 'Check-out Date', 'motopress-hotel-booking' ); ?>"
                    required="required"
                    autocomplete="off"
                    data-datepick-group="<?php echo esc_attr( $id ); ?>"
            />
        </p>
        <input type="hidden" name="mphb_room_type_id" value="507">

        <p class="mphb-adults">
            <label for="<?php echo esc_attr( 'mphb_adults-' . $id ); ?>">
                <?php _e( 'Adults', 'motopress-hotel-booking' ); ?>
            </label>
            <br />
            <select id="<?php echo esc_attr( 'mphb_adults-' . $id ); ?>" name="mphb_adults">
                <?php foreach ( $adultsList as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $adults, $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </p>

        <p class="mphb-children">
            <label for="<?php echo esc_attr( 'mphb_children-' . $id ); ?>">
                <?php
                $childrenAgeText = MPHB()->settings()->main()->getChildrenAgeText();
                if ( empty( $childrenAgeText ) ) {
                    _e( 'Children', 'motopress-hotel-booking' );
                } else {
                    printf( __( 'Children %s', 'motopress-hotel-booking' ), esc_html( $childrenAgeText ) );
                }
                ?>
            </label>
            <br />
            <select id="<?php echo esc_attr( 'mphb_children-' . $id ); ?>" name="mphb_children">
                <?php foreach ( $childrenList as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $children, $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </p>

        <?php
        /**
         * @hooked \MPHB\Admin\MenuPages\CreateBooking\Step::printDateHiddenFields - 10
         */
        do_action( 'mphb_cb_search_form_before_submit_button' );
        ?>

        <p class="mphb-submit-button-wrapper">
            <input type="submit" class="button" value="<?php _e( 'Search', 'motopress-hotel-booking' ); ?>" />
        </p>

        <?php
        /** @hooked None */
        do_action( 'mphb_cb_search_form_before_end' );
        ?>

    </form>

<?php

/** @hooked None */
do_action( 'mphb_cb_search_form_after_end' );
