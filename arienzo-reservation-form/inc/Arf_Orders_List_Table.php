<?php
if (!defined('WPINC')) {
    die;
}
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Arf_Orders_List_Table extends WP_List_Table
{
    /**
     * @return array
     */
    public function get_columns()
    {
        return array(
            'id' => 'Order ID',
            'user' => 'User',
            'reservation_start_date' => 'Reservation Date',
            'payment_status' => 'Status',
            'action'	=> 'Action'
        );
    }

    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    /**
     * @return array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    private function table_data()
    {
        global $wpdb;

        $arf = apply_filters('arf_database', $wpdb);
        $table_name = $arf->prefix . 'arf_orders';
        $data = array();

        $query = "SELECT * FROM $table_name";
        $data = $arf->get_results($query, ARRAY_A);
        return $data;
    }

    public function prepare_items()
    {
        global $wpdb;
        $arf = apply_filters('arf_database', $wpdb);
        $table_name = $arf->prefix . 'arf_orders';

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $data = $this->table_data();
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $count_forms = wp_count_posts('wpcf7_contact_form');

        $totalItems = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));

        $this->_column_headers = array($columns, $hidden);
        $this->items = $data;
    }

    protected function single_row_columns( $item ) {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
        foreach ( $columns as $column_name => $column_display_name ) {
            $classes = "$column_name column-$column_name";
            if ( $primary === $column_name ) {
                $classes .= ' has-row-actions column-primary';
            }

            if ( in_array( $column_name, $hidden ) ) {
                $classes .= ' hidden';
            }
            $data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';
            $attributes = "class='$classes' $data";

            if ( 'cb' === $column_name ) {
                echo '<th scope="row" class="check-column">';
                echo $this->column_cb( $item );
                echo '</th>';
            } elseif('action' === $column_name) {
                $itemAction = sprintf("<a href=admin.php?page=arf-orders-list.php&id=%d>%s</a>", $item['id'], "View");
                echo "<td $attributes>";
                echo $itemAction;
                echo '</td>';
            } elseif ('user' === $column_name) {
                $user = $item['user_firstname'] . " " . $item['user_lastname'];
                echo "<td $attributes>";
                echo $user;
                echo '</td>';
            }

            else {
                echo "<td $attributes>";
                echo $this->column_default( $item, $column_name );
                echo $this->handle_row_actions( $item, $column_name, $primary );
                echo '</td>';
            }
        }
    }

}