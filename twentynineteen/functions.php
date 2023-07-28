<?php 
/**
 * Twenty Nineteen functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since Twenty Nineteen 1.0
 */

use MPHB\Utils\ParseUtils;
use MPHB\Utils\BookingDetailsUtil;

use MPHB\Entities\Booking;
use MPHB\Entities\ReservedRoom;
use MPHB\Entities\RoomType;
use MPHB\Admin\MenuPages\EditBooking;

use \MPHB\Views;
/**
 * Twenty Nineteen only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}

if ( ! function_exists( 'twentynineteen_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function twentynineteen_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Twenty Nineteen, use a find and replace
		 * to change 'twentynineteen' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'twentynineteen', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 1568, 9999 );

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'menu-1' => __( 'Primary', 'twentynineteen' ),
				'footer' => __( 'Footer Menu', 'twentynineteen' ),
				'social' => __( 'Social Links Menu', 'twentynineteen' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			)
		);

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 190,
				'width'       => 190,
				'flex-width'  => false,
				'flex-height' => false,
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Add custom editor font sizes.
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name'      => __( 'Small', 'twentynineteen' ),
					'shortName' => __( 'S', 'twentynineteen' ),
					'size'      => 19.5,
					'slug'      => 'small',
				),
				array(
					'name'      => __( 'Normal', 'twentynineteen' ),
					'shortName' => __( 'M', 'twentynineteen' ),
					'size'      => 22,
					'slug'      => 'normal',
				),
				array(
					'name'      => __( 'Large', 'twentynineteen' ),
					'shortName' => __( 'L', 'twentynineteen' ),
					'size'      => 36.5,
					'slug'      => 'large',
				),
				array(
					'name'      => __( 'Huge', 'twentynineteen' ),
					'shortName' => __( 'XL', 'twentynineteen' ),
					'size'      => 49.5,
					'slug'      => 'huge',
				),
			)
		);

		// Editor color palette.
		add_theme_support(
			'editor-color-palette',
			array(
				array(
					'name'  => 'default' === get_theme_mod( 'primary_color' ) ? __( 'Blue', 'twentynineteen' ) : null,
					'slug'  => 'primary',
					'color' => twentynineteen_hsl_hex( 'default' === get_theme_mod( 'primary_color' ) ? 199 : get_theme_mod( 'primary_color_hue', 199 ), 100, 33 ),
				),
				array(
					'name'  => 'default' === get_theme_mod( 'primary_color' ) ? __( 'Dark Blue', 'twentynineteen' ) : null,
					'slug'  => 'secondary',
					'color' => twentynineteen_hsl_hex( 'default' === get_theme_mod( 'primary_color' ) ? 199 : get_theme_mod( 'primary_color_hue', 199 ), 100, 23 ),
				),
				array(
					'name'  => __( 'Dark Gray', 'twentynineteen' ),
					'slug'  => 'dark-gray',
					'color' => '#111',
				),
				array(
					'name'  => __( 'Light Gray', 'twentynineteen' ),
					'slug'  => 'light-gray',
					'color' => '#767676',
				),
				array(
					'name'  => __( 'White', 'twentynineteen' ),
					'slug'  => 'white',
					'color' => '#FFF',
				),
			)
		);

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );
	}
endif;
add_action( 'after_setup_theme', 'twentynineteen_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function twentynineteen_widgets_init() {

	register_sidebar(
		array(
			'name'          => __( 'Footer', 'twentynineteen' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your footer.', 'twentynineteen' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

}
add_action( 'widgets_init', 'twentynineteen_widgets_init' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width Content width.
 */
function twentynineteen_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'twentynineteen_content_width', 640 );
}
add_action( 'after_setup_theme', 'twentynineteen_content_width', 0 );

/**
 * Enqueue scripts and styles.
 */
function twentynineteen_scripts() {
	wp_enqueue_style( 'twentynineteen-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	wp_style_add_data( 'twentynineteen-style', 'rtl', 'replace' );

	if ( has_nav_menu( 'menu-1' ) ) {
		wp_enqueue_script( 'twentynineteen-priority-menu', get_theme_file_uri( '/js/priority-menu.js' ), array(), '20181214', true );
		wp_enqueue_script( 'twentynineteen-touch-navigation', get_theme_file_uri( '/js/touch-keyboard-navigation.js' ), array(), '20181231', true );
	}

	wp_enqueue_style( 'twentynineteen-print-style', get_template_directory_uri() . '/print.css', array(), wp_get_theme()->get( 'Version' ), 'print' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'twentynineteen_scripts' );

/**
 * Fix skip link focus in IE11.
 *
 * This does not enqueue the script because it is tiny and because it is only for IE11,
 * thus it does not warrant having an entire dedicated blocking script being loaded.
 *
 * @link https://git.io/vWdr2
 */
function twentynineteen_skip_link_focus_fix() {
	// The following is minified via `terser --compress --mangle -- js/skip-link-focus-fix.js`.
	?>
	<script>
	/(trident|msie)/i.test(navigator.userAgent)&&document.getElementById&&window.addEventListener&&window.addEventListener("hashchange",function(){var t,e=location.hash.substring(1);/^[A-z0-9_-]+$/.test(e)&&(t=document.getElementById(e))&&(/^(?:a|select|input|button|textarea)$/i.test(t.tagName)||(t.tabIndex=-1),t.focus())},!1);
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'twentynineteen_skip_link_focus_fix' );

/**
 * Enqueue supplemental block editor styles.
 */
function twentynineteen_editor_customizer_styles() {

	wp_enqueue_style( 'twentynineteen-editor-customizer-styles', get_theme_file_uri( '/style-editor-customizer.css' ), false, '1.1', 'all' );

	if ( 'custom' === get_theme_mod( 'primary_color' ) ) {
		// Include color patterns.
		require_once get_parent_theme_file_path( '/inc/color-patterns.php' );
		wp_add_inline_style( 'twentynineteen-editor-customizer-styles', twentynineteen_custom_colors_css() );
	}
}
add_action( 'enqueue_block_editor_assets', 'twentynineteen_editor_customizer_styles' );

/**
 * Display custom color CSS in customizer and on frontend.
 */
function twentynineteen_colors_css_wrap() {

	// Only include custom colors in customizer or frontend.
	if ( ( ! is_customize_preview() && 'default' === get_theme_mod( 'primary_color', 'default' ) ) || is_admin() ) {
		return;
	}

	require_once get_parent_theme_file_path( '/inc/color-patterns.php' );

	$primary_color = 199;
	if ( 'default' !== get_theme_mod( 'primary_color', 'default' ) ) {
		$primary_color = get_theme_mod( 'primary_color_hue', 199 );
	}
	?>

	<style type="text/css" id="custom-theme-colors" <?php echo is_customize_preview() ? 'data-hue="' . absint( $primary_color ) . '"' : ''; ?>>
		<?php echo twentynineteen_custom_colors_css(); ?>
	</style>
	<?php
}
add_action( 'wp_head', 'twentynineteen_colors_css_wrap' );

/**
 * SVG Icons class.
 */
require get_template_directory() . '/classes/class-twentynineteen-svg-icons.php';

/**
 * Custom Comment Walker template.
 */
require get_template_directory() . '/classes/class-twentynineteen-walker-comment.php';

/**
 * Common theme functions.
 */
require get_template_directory() . '/inc/helper-functions.php';

/**
 * SVG Icons related functions.
 */
require get_template_directory() . '/inc/icon-functions.php';

/**
 * Enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Custom template tags for the theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';
add_action( 'wp_loaded', 'disable_wp_theme_update_loaded' );
function disable_wp_theme_update_loaded() {
    remove_action( 'load-update-core.php', 'wp_update_themes' );
    add_filter( 'pre_site_transient_update_themes', '__return_null' );
	
	
}

add_action('wp_head', 'global_site_tag_script');
add_action('wp_head', 'global_site_tag_scriptsss');
function global_site_tag_script(){
?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-44808677-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-44808677-3');
</script>
<?php
};

function global_site_tag_scriptsss(){
	if(in_array(get_the_ID(), [13789, 13792, 13943, 14194, 14038, 14045, 14198, 14048])) {
?>
<button onclick="topFunction()" id="go-to-top" title="<?php _e("Go to top", "twentynineteen") ?>"><?php _e("Top", "twentynineteen") ?></button>
<style>
#go-to-top {
  display: none;
  position: fixed;
  bottom: 80px;
  right: 30px;
  z-index: 99;
  font-size: 18px;
  border: none;
  outline: none;
  background-color: red;
  color: white;
  cursor: pointer;
  padding: 15px;
  border-radius: 4px;
}

#go-to-top:hover {
  background-color: #555;
}

.vce-google-fonts-heading-inner {
	margin-top: 10px !important;
	margin-bottom: 10px
	line-height: 20px;
}
.vce-google-fonts-heading-wrapper {
    line-height: 20px;
}

@media screen and (max-width: 960px) {
.vce {
	margin-bottom: 20px !important;
}
}

</style>
<script>
//Get the button
var mybutton = document.getElementById("go-to-top");
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    mybutton.style.display = "block";
  } else {
    mybutton.style.display = "none";
  }
}

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
}
</script>
<?php
	}
};



function custom_login_css_admin_page() {
    ?>
    <style type="text/css">
    	body.login {
		    background-color: #ffffff;
		}

		body.login div#login h1 a {
		    background-image: none,url(https://booking.arienzobeachclub.com/wp-content/uploads/2020/06/arienzo_logo.png);
		    width: 150px;
		    height: px;
		    background-size: 150px px;
		}

		#login form#loginform, #login form#registerform, #login form#lostpasswordform {
			border: 0;
			box-shadow: 0 1px 3px rgb(0 0 0 / 18%);
		}

		#login form#loginform .input, #login form#registerform .input, #login form#lostpasswordform .input {
		    border-color: #4d8fcc;
		    box-shadow: unset;
		    color: #616161;
		    border-radius: 0;
		}

		#login form#loginform label, #login form#registerform label, #login form#lostpasswordform label {
		    color: #616161;
		}

		#login form#loginform .forgetmenot label, #login form#registerform .forgetmenot label, #login form#lostpasswordform .forgetmenot label {
		}

		#login form .submit .button {
		    height: auto;
		    background-color: #4d8fcc;
		    border-color: #4d8fcc;
		    text-shadow: 0 -1px 1px #4d8fcc,1px 0 1px #4d8fcc,0 1px 1px #4d8fcc,-1px 0 1px #4d8fcc;
		}

		#login form .submit .button:hover, #login form .submit .button:focus {
		    background-color: #4d8fcc;
		    border-color: #4d8fcc;
		}

		.login #nav, .login #nav a, .login #backtoblog a {
		    color: #616161;
		}

		.login #backtoblog a:hover, .login #nav a:hover {
		    color: #4d8fcc;
		}
    </style>
    <?php
}
add_action('login_head', 'custom_login_css_admin_page');

function custom_login_footer_admin_page(){
	?>
	<script type="text/javascript">
		(function(){
			document.getElementsByClassName("login")[0].getElementsByTagName("h1")[0].getElementsByTagName("a")[0].href = "<?php echo home_url(); ?>";
		}())
	</script>
	<?php
}

add_action('login_footer', 'custom_login_footer_admin_page');

function my_custom_post_location() {
	if(!empty($_GET['change_booking_dashboard_pagi'])){
		update_option("booking_dashboard_pagi",$_GET['change_booking_dashboard_pagi']);
		wp_redirect(admin_url(""));die;
	}
	if(isset($_POST['make_check_in']) && !empty($_POST['check_in_ids'])){
		foreach ($_POST['check_in_ids'] as $key => $value) {
			update_post_meta($value, 'arf_qr_code_status', "checked");
		}
		$url = array();
		if(!empty($_POST['frdate'])){
			$url[] = "frdate=".$_POST['frdate'];
		}
		if(!empty($_POST['frname'])){
			$url[] = "frname=".$_POST['frname'];
		}
		if(!empty($_POST['frid'])){
			$url[] = "frid=".$_POST['frid'];
		}
		wp_redirect(admin_url("?".implode("&", $url)));die;
	}

    $labels = array(
    	'name' => _x( 'Lunch Time', 'post type general name' ),
    	'singular_name' => _x( 'Lunch Time', 'post type singular name' ),
    	'add_new' => _x( 'Add New', 'Lunch Time' ),
    	'add_new_item' => __( 'Add New Lunch Time' ),
    	'edit_item' => __( 'Edit Lunch Time' ),
    	'new_item' => __( 'New Lunch Time' ),
    	'all_items' => __( 'All Lunch Time' ),
    	'view_item' => __( 'View Lunch Time' ),
    	'search_items' => __( 'Search Lunch Time' ),
    	'not_found' => __( 'No Lunch Time found' ),
    	'not_found_in_trash' => __( 'No Lunch Time found in the Trash' ),
    	'parent_item_colon' => '',
    	'menu_name' => 'Lunch Time'
    );
    $args = array(
    'labels' => $labels,
    'description' => 'Displays Lunch Time',
    'public' => true,
    'menu_position' => 2,
    'supports' => array( 'title',),
    'has_archive' => true,
    );
    register_post_type( 'lunch_time', $args );

    $labels = array(
    	'name' => _x( 'Places', 'post type general name' ),
    	'singular_name' => _x( 'Place', 'post type singular name' ),
    	'add_new' => _x( 'Add New', 'Place' ),
    	'add_new_item' => __( 'Add New Place' ),
    	'edit_item' => __( 'Edit Place' ),
    	'new_item' => __( 'New Place' ),
    	'all_items' => __( 'All Places' ),
    	'view_item' => __( 'View Place' ),
    	'search_items' => __( 'Search Place' ),
    	'not_found' => __( 'No Place found' ),
    	'not_found_in_trash' => __( 'No Place found in the Trash' ),
    	'parent_item_colon' => '',
    	'menu_name' => 'Places'
    );
    $args = array(
    'labels' => $labels,
    'description' => 'Displays city Place and their ratings',
    'public' => true,
    'menu_position' => 2,
    'supports' => array( 'title',),
    'has_archive' => true,
    );
    register_post_type( 'location', $args );

}
add_action( 'init', 'my_custom_post_location' );


function my_custom_post_lunch_time() {
    
}
add_action( 'init', 'my_custom_post_lunch_time' );

// Add the custom columns to the lunch_time post type:
add_filter( 'manage_lunch_time_posts_columns', 'set_custom_edit_lunch_time_columns' );
function set_custom_edit_lunch_time_columns($columns) {
		if(isset($columns['date'])){
			unset($columns['date']);
    	$columns['description'] = 'Description';
    	$columns['lunch_time_type'] = 'Type';
    	$columns['date'] = 'Date';
		}else{
    	$columns['description'] = 'Description';
    	$columns['lunch_time_type'] = 'Type';
		}
    return $columns;
}

// Add the data to the custom columns for the lunch_time post type:
add_action( 'manage_lunch_time_posts_custom_column' , 'custom_lunch_time_column', 10, 2 );
function custom_lunch_time_column( $column, $post_id ) {
    switch ( $column ) {
        case 'description' :
            $description = get_post_meta( $post_id , 'description',true);
            echo $description;
        break;
        case 'lunch_time_type' :
            $terms = get_post_meta( $post_id , 'lunch_time_type',true);
            if ( $terms == "lunch_at_your_sunbed" )
                echo "Sunbed";
            else
                echo 'Table';
        break;

    }
}


function booking_confiration_link_custom_meta(){
    
	add_meta_box(
        'confiration_link_meta',
        'Booking Confirmation Link',
        'confiration_link_meta',
        '',
        'side',
        'low'
    );
}
function confiration_link_meta(){
    global $post;
    $url = home_url( '/booking-confirmation/booking-confirmed/?booking_id=' . $post->ID ); ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery(".copy_clip_board").click(function (e) {
               e.preventDefault();
               const textarea = document.createElement('textarea')
          
                // Set the value of the text
                textarea.value = jQuery("#"+jQuery(this).data("id")).val();
                
                // Make sure we cant change the text of the textarea
                textarea.setAttribute('readonly', '');
                
                // Hide the textarea off the screnn
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                
                // Add the textarea to the page
                document.body.appendChild(textarea);
                // Copy the textarea
                textarea.select()
                try {
                  var successful = document.execCommand('copy');
                  this.copied = true
                } catch(err) {
                  this.copied = false
                }
                textarea.remove()
        
                var copyText = document.getElementById(jQuery(this).data("select"));
                /* Select the text field */
                copyText.select();
                copyText.setSelectionRange(0, 99999); /* For mobile devices */
                /* Copy the text inside the text field */
             
              /* Alert the copied text */
              jQuery(this).text("<?php echo  "Copied" ?>");
              jQuery(this).attr("title","<?php echo  "Copied" ?>");
              $this = jQuery(this);
              setTimeout(function(){ 
                $this.text("<?php echo  "Copy" ?>");
                $this.attr("title","<?php echo  "Copy" ?>");
              }, 2000);
            });        
        })
    
    </script>
    <input id="confirmation_url" style="width:100%" class="confirmation_url" type="text" value="<?php echo $url ?>" readonly>
    <button style="margin-top:10px;" name="save" type="button"  data-id="confirmation_url" data-select="confirmation_url"  class="button button-primary button-large copy_clip_board" value="Copy">Copy</button>
    <?php
}

add_action( 'admin_head', 'custom_dashboard_widget' );
function custom_dashboard_widget() {

	// Bail if not viewing the main dashboard page
	if ( get_current_screen()->base !== 'dashboard' ) {
		return;
	}

	?>
    <style type="text/css">
        #custom-id{background:none;}#custom-id:before{display:none}
    </style>
	<div id="custom-id" class="welcome-panel" style="display: none;">
		<div class="welcome-panel-content-2">
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
                 span.place_dot.active_green {
                        background: #4caf50;
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

				.edit_td{
				    cursor: pointer;
				}
				.w-100{
					width: 100%;
					max-width: 25rem;
				}
				.editPop{
					overflow: hidden;
				    padding: 25px;
				    position: fixed;
				    width: 50%;
				    min-width: 400px;
				    top: 10%;
				    left: 25%;
				    display: none;
				    background: #fff;
				    z-index: 10000;
				    transition: 0.5s;
				    height: 500px;
				    overflow-y: scroll;
				    box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
				    border: 1px solid #ccd0d4;
				}
				.mphb_checkout-services-list label{
					display: inline-block;
				}
				.main_loader{
				    position: fixed;
				    background: #fff;
				    width: 100%;
				    height: 100%;
				    left: 0;
				    top: 0;
				    opacity: 0.5;
				    z-index: 100000;
				    transition: 0.5s;
				}

				.loader4 {
				   position: absolute;
				   top: calc(50% - 22px);
				   left: calc(50% - 22px);
				   width:45px;
				   height:45px;
				   display:inline-block;
				   padding:0px;
				   border-radius:100%;
				   border:5px solid;
				   border-top-color:rgba(246, 36, 89, 1);
				   border-bottom-color:rgba(255,255,255, 0.3);
				   border-left-color:rgba(246, 36, 89, 1);
				   border-right-color:rgba(255,255,255, 0.3);
				   -webkit-animation: loader4 1s ease-in-out infinite;
				   animation: loader4 1s ease-in-out infinite;
				}
				@keyframes loader4 {
				   from {transform: rotate(0deg);}
				   to {transform: rotate(360deg);}
				}
				@-webkit-keyframes loader4 {
				   from {-webkit-transform: rotate(0deg);}
				   to {-webkit-transform: rotate(360deg);}
				}
				.page-numbers li{
					display: inline-block;
				}
				.page-numbers li a{
					padding: 10px;
					color: #2271b1;
				    border: 1px solid #2271b1;
				    background: #f6f7f7;
				    display: inline-block;
				    vertical-align: baseline;
				    min-width: 30px;
				    min-height: 30px;
				    margin: 0;
				    padding: 0 4px;
				    font-size: 16px;
				    line-height: 1.7;
				    text-align: center;
				}
				.page-numbers li .page-numbers.current{
					padding: 10px;
					color: #ffffff;
				    border: 1px solid #2271b1;
				    background: #2271b1;
				    display: inline-block;
				    vertical-align: baseline;
				    min-width: 30px;
				    min-height: 30px;
				    margin: 0;
				    padding: 0 4px;
				    font-size: 16px;
				    line-height: 1.7;
				    text-align: center;
				}
            </style>
            <?php
            $adultsTotalCount = 0; 
            $childTotalCount = 0; 
            $childTotalCountArray = array();
			$adultsTotalCountArray = array();
            $lunch_time = "12:00";
            global $wpdb;
            if(isset($_GET['frdate']) && $_GET['frdate']){
                $mphb_check_in_date = $_GET['frdate'];
            }else{
                $mphb_check_in_date =  date( 'Y-m-d' );   
            }
        
            $table_selected_ids = [];
            $booking_ids = $wpdb->get_results ("
                SELECT post_id 
                FROM  $wpdb->postmeta
                LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id)
                    WHERE `meta_key` = 'mphb_check_in_date'
                    AND `meta_value` = '$mphb_check_in_date' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')
            ");
             
            $mphb_place = array();
            $mphb_place_green = array();
            $booking_info = array();
            foreach ( $booking_ids as $booking_id )
            {
                if($booking_id->post_id != $_POST['id']){
                    $mmm = get_post_meta($booking_id->post_id, 'mphb_place', true);
                    $qr_code_status = get_post_meta($booking_id->post_id, 'arf_qr_code_status', true);
                    if($mmm){
                    	if($qr_code_status){
	                        foreach($mmm AS $kk => $vv){
	                            if(isset($mphb_place_green[$kk])){
	                                $mphb_place_green[$kk] = array_merge($mphb_place_green[$kk],$vv);
	                            }else{
	                                $mphb_place_green[$kk] = $vv;
	                            }

		                        foreach ($vv as $kkkk => $vvvv) {
		                            $booking = MPHB()->getBookingRepository()->findById($booking_id->post_id);
		                            if($booking){
		                            	 //echo "<pre>"; print_r($booking); echo "</pre>";die; 
		                            	$customer = $booking->getCustomer();
		                            	$guest = "";
		                            	$reservedRooms = $booking->getReservedRooms();
								        if (!empty($reservedRooms) && !$booking->isImported()) {
								            $adultsTotal = 0;
								            $childrenTotal = 0;
								            foreach ($reservedRooms as $reservedRoom) {
								                $adultsTotal += $reservedRoom->getAdults();
								                $childrenTotal += $reservedRoom->getChildren();
								            }

								            $guest .= 'Adults: ';
								            $guest .= $adultsTotal;
								            if ($childrenTotal > 0) {
								                $guest .= ' - ';
								                $guest .= 'Children: ';
								                $guest .= $childrenTotal;
												$childTotalCountArray[$booking_id->post_id] = $childrenTotal;
								            }
								            $adultsTotalCountArray[$booking_id->post_id] = $adultsTotal;
								        }
								        $obj = get_post_status_object($booking->getStatus());
			                            $booking_info[$kk][$vvvv][] = array(
			                            	"id" => $booking_id->post_id,
			                            	"status" => isset($obj->label) ? $obj->label : "N/A",
			                            	"name" =>  $customer->getName(),
			                            	"telephone" => $customer->getPhone(),
			                            	"guests" => $guest,
			                            	"arrival_time" => get_post_meta($booking_id->post_id,"beach_arrival_time",1)
			                            );
		                            }
		                        }
		                        	
	                        }
                    	}else{
	                        foreach($mmm AS $kk => $vv){
	                            if(isset($mphb_place[$kk])){
	                                $mphb_place[$kk] = array_merge($mphb_place[$kk],$vv);
	                            }else{
	                                $mphb_place[$kk] = $vv;
	                            }

		                        foreach ($vv as $kkkk => $vvvv) {
		                            $booking = MPHB()->getBookingRepository()->findById($booking_id->post_id);
		                            if($booking){
		                            	 //echo "<pre>"; print_r($booking); echo "</pre>";die; 
		                            	$customer = $booking->getCustomer();
		                            	$guest = "";
		                            	$reservedRooms = $booking->getReservedRooms();
								        if (!empty($reservedRooms) && !$booking->isImported()) {
								            $adultsTotal = 0;
								            $childrenTotal = 0;
								            foreach ($reservedRooms as $reservedRoom) {
								                $adultsTotal += $reservedRoom->getAdults();
								                $childrenTotal += $reservedRoom->getChildren();
								            }

								            $guest .= 'Adults: ';
								            $guest .= $adultsTotal;
								            if ($childrenTotal > 0) {
								                $guest .= ' - ';
								                $guest .= 'Children: ';
								                $guest .= $childrenTotal;
												$childTotalCountArray[$booking_id->post_id] = $childrenTotal;
								            }
								            $adultsTotalCountArray[$booking_id->post_id] = $adultsTotal;
								        }
								        $obj = get_post_status_object($booking->getStatus());
			                            $booking_info[$kk][$vvvv][] = array(
			                            	"id" => $booking_id->post_id,
			                            	"status" => isset($obj->label) ? $obj->label : "N/A",
			                            	"name" =>  $customer->getName(),
			                            	"telephone" => $customer->getPhone(),
			                            	"guests" => $guest,
			                            	"arrival_time" => get_post_meta($booking_id->post_id,"beach_arrival_time",1)
			                            );
		                            }
		                        }
		                        	
	                        }
                    	}
                    	/*if(isset($_GET['a'])){
                    	 	echo "<pre>"; print_r($mmm); echo "</pre>";die; 
                    	}*/
                    	
                    }else{
                    	$booking = MPHB()->getBookingRepository()->findById($booking_id->post_id);
                        if($booking){
                        	$reservedRooms = $booking->getReservedRooms();
					        if (!empty($reservedRooms) && !$booking->isImported()) {
					        	$adultsTotal = 0;
					            $childrenTotal = 0;
					            foreach ($reservedRooms as $reservedRoom) {
					                $adultsTotal += $reservedRoom->getAdults();
					                $childrenTotal += $reservedRoom->getChildren();
					            }
					            if ($childrenTotal > 0) {
									$childTotalCountArray[$booking_id->post_id] = $childrenTotal;
					            }
					            $adultsTotalCountArray[$booking_id->post_id] = $adultsTotal;
					        }
					    }
                    }
                }
                
            }
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
                $url = get_dashboard_url().'?frdate=';
                //childTotalCount
				//adultsTotalCount
				$childTotalCount = array_sum($childTotalCountArray);
				$adultsTotalCount = array_sum($adultsTotalCountArray);
                $return = '<input type="date" placeholder="Date" title="Date" class="fdate" value="'.$mphb_check_in_date.'" /> Adults : '.$adultsTotalCount." - Child : ".$childTotalCount;
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
                                	$count_person = "";
                                	$title = "";
                                	if(isset($booking_info[$value->ID][$vv])){
                                		$count_person = count($booking_info[$value->ID][$vv]);
                                		foreach ($booking_info[$value->ID][$vv] as $booking_info_key => $booking_info_value) {
	                                		$title .= "Id : ".$booking_info_value["id"]."&#013;";
	                                		$title .= "Name : ".$booking_info_value["name"]."&#013;";
	                                		$title .= "Telephone : ".$booking_info_value["telephone"]."&#013;";
	                                		$title .= "Guests : ".$booking_info_value["guests"]."&#013;";
	                                		$title .= "Arrival Time : ".$booking_info_value["arrival_time"]."&#013;";
	                                		$title .= "Status : ".$booking_info_value["status"]."&#013;";

	                                		if($booking_info_key+1 < $count_person){
	                                			$title .= "&#013;&#013;";
	                                		}

                                		}
                                	}
                                    $colls .= "<td>";
                                        $colls .= '<span title="'.$title.'" class="place_dot '.(isset($mphb_place[$value->ID]) && in_array( $vv, $mphb_place[$value->ID]) ? "active" : "").' '.(isset($mphb_place_green[$value->ID]) && in_array( $vv, $mphb_place_green[$value->ID]) ? "active_green" : "").'">'.(($count_person && $count_person > 1) ? $count_person : "").'</span>';        
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

                                	$count_person = "";
                                	$title = "";
                                	if(isset($booking_info[$value->ID][$vv])){
                                		$count_person = count($booking_info[$value->ID][$vv]);
                                		foreach ($booking_info[$value->ID][$vv] as $booking_info_key => $booking_info_value) {
	                                		$title .= "Id : ".$booking_info_value["id"]."&#013;";
	                                		$title .= "Name : ".$booking_info_value["name"]."&#013;";
	                                		$title .= "Telephone : ".$booking_info_value["telephone"]."&#013;";
	                                		$title .= "Guests : ".$booking_info_value["guests"]."&#013;";
	                                		$title .= "Arrival Time : ".$booking_info_value["arrival_time"]."&#013;";
	                                		$title .= "Status : ".$booking_info_value["status"]."&#013;";

	                                		if($booking_info_key+1 < $count_person){
	                                			$title .= "&#013;&#013;";
	                                		}

                                		}
                                	}
                                    $colls .= "<td>";
                                        $colls .= '<span title="'.$title.'" class="place_dot '.(isset($mphb_place[$value->ID]) && in_array( $vv, $mphb_place[$value->ID]) ? "active" : "").' '.(isset($mphb_place_green[$value->ID]) && in_array( $vv, $mphb_place_green[$value->ID]) ? "active_green" : "").'">'.(($count_person && $count_person > 1) ? $count_person : "").'</span>';        
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
                echo $return;
                if(isset($_GET['frdate']) && $_GET['frdate']){
	                $frdate = $_GET['frdate'];
	            }else{
	                $frdate =  date( 'Y-m-d' );   
	            }
	            $frname = isset($_GET['frname']) ? $_GET['frname'] : "";
				$frid = isset($_GET['frid']) ? $_GET['frid'] : "";
				if(!$frid){
			        $args = array(
			            'start_date' => $frdate,
			            'end_date' => $frdate,
			            "room" => "-1",
			            "status" => "all",
			            "search_by" => "reserved-rooms",
			            "columns" => array(
			                "booking-id",
			                "booking-status",
			                "check-in",
			                "check-out",
			                "room-type",
			                "room-type-id",
			                "room",
			                "rate",
			                "adults",
			                "children",
			                "services",
			                "first-name",
			                "last-name",
			                "email",
			                "phone",
			                "country",
			                "address",
			                "city",
			                "state",
			                "postcode",
			                "customer-note",
			                "guest-name",
			                "coupon",
			                "price",
			                "paid",
			                "payments",
			                "date",
			                "lunch_time",
			                "beach_arrival_time",
			            )
			        );

			        // /$query = new \MPHB\CSV\Bookings\BookingsQuery($args);
		            $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;

		            $pagi = get_option("booking_dashboard_pagi");
		            $pagi = $pagi ? $pagi : 20;

			        $attr = array(
			            'posts_per_page' => $pagi,
			            'paged' => $paged, 
			            'post_type' => 'mphb_booking',
			            'post_status' => array('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge'),
			            'fields' => 'ids',
			            'meta_query' => array()
			        );

		            $attr['meta_query'][] = array(
		                'key' => 'mphb_check_in_date',
		                'value' => $args['start_date'],
		                'compare' => '=',
		            );
		            if($frname){
		            	$frname_v = explode(" ", trim($frname));
			            $attr['meta_query'][] = array(
			                'key' => 'mphb_first_name',
			                'value' => $frname_v[0],
			                'compare' => 'like',
			            );
			            /*unset($frname_v[0]);
			            if($frname_v){
				            $attr['meta_query'][] = array(
				                'key' => 'mphb_last_name',
				                'value' => implode(" ", $frname_v),
				                'compare' => '=',
				            );
			            }*/
		            }

			        $query = new WP_Query($attr);
			        $ids = $query->posts;
			    }else{
			        $ids[] = $frid;
			    }
		        ?>

            <div style="width:100%">
            	<div style="width:100%;margin: 20px 0;">
            		<form action="" method="GET">
	            		<table style="width:100%;">
	            			<tr>
	            				<td>
	            					<input type="date" placeholder="Date" title="Date" name="frdate" value="<?php echo $frdate ?>"  style="width:100%;"/>
	            				</td>
	            				<td>
	            					<input type="text" placeholder="Name" title="Name" name="frname" value="<?php echo $frname ?>"  style="width:100%;"/>
	            				</td>
	            				<td>
	            					<input type="text" placeholder="ID" title="ID" name="frid" value="<?php echo $frid ?>"  style="width:100%;"/>
	            				</td>
	            				<td width="70px">
	            					<input type="submit" value="Submit" class="button" />
	            				</td>
	            			</tr>
	            		</table>
            		</form>
            	</div>
            	<form action="" class="checkInForm" method="POST">
            		<div style="display: flex;flex-wrap: wrap;">
	            		<div style="width:50%;margin: 20px 0;">
	            			<input type="hidden" name="frdate" value="<?php echo $frdate ?>">
	            			<input type="hidden" name="frname" value="<?php echo $frname ?>">
	            			<input type="hidden" name="frid" value="<?php echo $frid ?>">
	            			<input type="submit" value="Make Check In" class="button" name="make_check_in">
	            		</div>
	            		<div style="width:50%;margin: 20px 0;text-align: right;">
	            			<select onchange="location = '<?php echo get_dashboard_url()."?change_booking_dashboard_pagi=" ?>'+jQuery(this).val()">
	            				<option <?php echo $pagi == 20 ? "selected" : "" ?> value="20">20</option>
	            				<option <?php echo $pagi == 40 ? "selected" : "" ?> value="40">40</option>
	            				<option <?php echo $pagi == 60 ? "selected" : "" ?> value="60">60</option>
	            				<option <?php echo $pagi == 80 ? "selected" : "" ?> value="80">80</option>
	            				<option <?php echo $pagi == 100 ? "selected" : "" ?> value="100">100</option>
	            			</select>
	            		</div>
            		</div>
		        	<table class="widefat">
			            <thead>
				            <tr>
				                <th><input type="checkbox" name="checkall" onclick="if(jQuery(this).prop('checked')){jQuery('.check_in_ids').prop('checked',true);}else{jQuery('.check_in_ids').prop('checked',false);}"></th>
				                <th>Booking Id</th>
				                <th>Full Name</th>
				                <th>Phone</th>
				                <th>Guests</th>
				                <th>Price</th>
				                <th>Arrival time</th>
				                <th>Lunch time</th>
				                <th>Services</th>
				                <th>Location</th>
				                <th>Table</th>
				                <th>Notes</th>
				                <th width="120">Status</th>
				            </tr>
			            </thead>
			            <tbody>
					        <?php
					        if($ids){
					        	$bookings = MPHB()->getBookingRepository()->findAll(array('post__in' => $ids));
					        	?>

						            <?php foreach ($bookings as $booking) {
						                $id = $booking->getId();
						                $customer = $booking->getCustomer();


								        $reservedRooms = $booking->getReservedRooms();
								        $guest = "";
								        if (!empty($reservedRooms) && !$booking->isImported()) {
								            $adultsTotal = 0;
								            $childrenTotal = 0;
								            foreach ($reservedRooms as $reservedRoom) {
								                $adultsTotal += $reservedRoom->getAdults();
								                $childrenTotal += $reservedRoom->getChildren();
								            }

								            $guest .= 'Adults: ';
								            $guest .= "<span class='countAdult'>".$adultsTotal."</span>";
								            if ($childrenTotal > 0) {
								                $guest .= '<br/>';
								                $guest .= 'Children: ';
								                $guest .= $childrenTotal;
								            }
								        }

						                
						                $metas = get_post_meta($id);
						                $mphb_place = get_post_meta($id,"mphb_place",1);
						                $places = array();
						                if($mphb_place){
							                foreach ($mphb_place as $key => $value) {
							                    foreach ($value as $key => $value) {
							                        $places[] = $value;
							                    }
							                }
						                }
						                 //echo "<pre>"; print_r($mphb_place); echo "</pre>";die; 
						                $beach_arrival_time = $metas['beach_arrival_time'][0];
						                $lunch_time = $metas['lunch_time'][0];
						                

						                $mphb_table_id = get_post_meta($id, 'arf_cp_table_id', true);

						                $table_selected_ids = [];
						                $tables = [];

						                $ids = get_post_meta($id, 'arf_cp_table_id', true);
						                if(is_array($ids)){
						                    $table_selected_ids = $ids;
						                }else{
						                    $table_selected_ids[] = $ids;
						                }

						                if($table_selected_ids) {
						                    $args = array(
						                        'post_type' => 'arf_pt_table',
						                        'posts_per_page' => -1,
						                        'post_status' => 'publish',
						                        'orderby' => 'post_title',
						                        'order' => 'ASC'
						                    );
						                    $args['post__in'] = $table_selected_ids;
						                    $arf_pt_tables = get_posts($args);
						                    foreach ($arf_pt_tables as $key => $value) {
						                        $tables[] = $value->post_title;
						                    }
						                }
						                $qr_code_status = get_post_meta($id, 'arf_qr_code_status', true);
						                
						                //$price = $booking->getTotalPrice();
						                $price_breakdown = get_post_meta( $id, '_mphb_booking_price_breakdown', true); 
						                $price = 0;
								        if($price_breakdown){
											$ddd = json_decode(strip_tags($price_breakdown),true);
											//echo "<pre>";print_r($ddd);die;
											if(isset($ddd['rooms'])){
												foreach ($ddd['rooms'] as $kk => $value) {
													$adults += $value['room']['adults']; 
							            			$child += $value['room']['children']; 
													if(isset($value['services']['list'])){
														foreach ($value['services']['list'] as $key => $vv) {
															$service_arr[] = $vv['title']." (".$vv['details'].")";
															$sub_total = $vv['details'];
														}	
													}
													if(isset($value['services']['total']) && $value['services']['total']){
													    $price += $value['services']['total'];
													}
													
												}
											}
								        }

						                ?>
						                <tr>
						                	<th><input type="checkbox" class="check_in_ids" name="check_in_ids[]" value="<?php echo $id ?>"></th>
						                    <!-- <td><a href="<?php echo admin_url("admin.php?page=mphb_edit_booking&booking_id=".$id) ?>" target="_blank"><?php echo $id ?></a></td> -->
						                    <td><a href="#" class="show_pop" data-id="<?php echo $id ?>"><?php echo $id ?></a></td>

						                    <td class="edit_td td_data" style="text-align: left;">
						                    	<span class="data_show"><?php echo $customer->getName() ?></span>
			                                    <input type="text" data-field="name" data-id="<?php echo $id ?>" class="form-control data_input" value="<?php echo $customer->getName() ?>" style="display: none;">
						                    </td>
						                    <td class="edit_td td_data">
						                    	<span class="data_show"><?php echo $customer->getPhone() ?></span>
			                                    <input type="text" data-field="phone" data-id="<?php echo $id ?>" class="form-control data_input" value="<?php echo $customer->getPhone() ?>" style="display: none;">
						                    	
						                    </td>
						                    <td><?php echo $guest ?></td>
						                    <td><?php Views\BookingView::renderTotalPriceHTML( $booking ); ?></td>

						                    <td><?php echo $beach_arrival_time ?></td>
						                    <td><?php echo get_lunch_text($lunch_time) ?></td>
						                    <td><?php 

										        $reservedRooms = $booking->getReservedRooms();
										        foreach ($reservedRooms as $reservedRoom) {
										            $reservedServices = $reservedRoom->getReservedServices();
										            $placeholder = ' &#8212;';
										            if (!empty($reservedServices)) {
										                echo '<ol>';
										                foreach ($reservedServices as $reservedService) {
										                    echo '<li>';
										                    echo '<a target="_blank" href="' . esc_url(get_edit_post_link($booking->getId())) . '">' . esc_html($reservedService->getTitle()) . '</a>';
										                    if ($reservedService->isPayPerAdult()) {
										                        echo ' <em>' . sprintf(_n('x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking'), $reservedService->getAdults()) . '</em>';
										                    }
										                    if ($reservedService->isFlexiblePay()) {
										                        echo ' <em>' . sprintf(_n('x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking'), $reservedService->getQuantity()) . '</em>';
										                    }
										                    echo '</li>';
										                }
										                echo '</ol>';
										            } else {
										                echo $placeholder;
										            }
										        }
						                     ?></td>
						                    <td><?php echo implode(",", $places) ?></td>
						                    <td><?php echo implode(",", $tables) ?></td>
						                    <td><?php echo isset($metas['mphb_note'][0]) ? $metas['mphb_note'][0] : "" ?></td>
						                    <td>
						                    	<?php if (empty($qr_code_status)) { ?>
					                    			NOT CHECKED IN
					                    		<?php }else{ ?>
					                    			CHECKED IN
					                    		<?php } ?>
						                    </td>
						                </tr>
						            <?php } ?>
					        	<?php
					        }else{ ?>
					        	<tr>
					        		<td colspan="10" align="center">No Record Found</td>
					        	</tr>
				        	<?php } ?>
			            </tbody>
			        </table>
			        <div class="pagination">
					    <?php 
					        echo paginate_links( array(
					            'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
					            'total'        => $query->max_num_pages,
					            'current'      => (isset($_GET['paged']) ? $_GET['paged'] : 1),
					            'format'       => '?paged=%#%',
					            'show_all'     => false,
					            'type'         => 'list',
					            'end_size'     => 2,
					            'mid_size'     => 1,
					            'prev_next'    => true,
					            'prev_text'    => sprintf( '<i></i> %1$s', __( '<<', 'text-domain' ) ),
					            'next_text'    => sprintf( '%1$s <i></i>', __( '>>', 'text-domain' ) ),
					            'add_args'     => false,
					            'add_fragment' => '',
					        ) );
					    ?>
					</div>
				</form>
            </div>
		</div>
	</div>
	<div style="" class="editPop">
		<div class="headerPop" style="text-align:right"><a href="#" class="closePop" style="margin:10px 10px 0 0;">X</a></div>
		<div class="bodyPop"></div>
	</div>
	<div class="main_loader" style="display:none"><div class="loader4"></div></div>
	<script>
		jQuery(document).ready(function($) {
			$('#welcome-panel').after($('#custom-id').show());
			$('.fdate').change(function(){
			    location = "<?php echo get_dashboard_url() ?>?frdate="+$(this).val();
			})
			/*$('.frdate').change(function(){
			    location = "<?php echo get_dashboard_url() ?>?frdate="+$(this).val();
			})*/
			$('.edit_td').dblclick(function(){
	            /*if($(this).data("edit") == "0"){*/
	                /*$(this).data("edit",1);*/
	                $(".data_show").show();
	                $(".data_input").hide();

	                $(this).find(".data_show").hide();
	                $(this).find(".data_input").show().focus();
	            /*}else{

	            }*/
	        });

            $('.data_input').on('blur change',function(){
                $this = $(this);
                fun_change_val($this);
            })

            var fun_change_val = function($this){   
                var field = $this.data("field");
                var id = $this.data("id");
                var data_val = $this.val();
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    dataType:"json",
                    data:{
                        action : "change_booking_details",
                        id : id,
                        field : field,
                        data_val : data_val,
                    },
                    success: function(data) {
                        $(".data_show").show();
                        $(".data_input").hide();
                        
                        $this.parent(".td_data").find(".data_show").text($this.val());
                    }
                });
            }

            $(".closePop").click(function (e) {
            	e.preventDefault();
            	$(".editPop").hide();
            })
            $(".show_pop").click(function (e) {
            	e.preventDefault();
            	$this = $(this);
                var id = $this.data("id");
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    dataType:"html",
                    beforeSend:function(){
                		$(".main_loader").show()
                	},
                	complete:function(){
                		$(".main_loader").hide()
                	},
                    data:{
                        action : "get_booking_details",
                        id : id,
                        frdate : "<?php echo $frdate ?>",
                        frname : "<?php echo $frname ?>",
                        frid : "<?php echo $frid ?>",
                        debug : "debug",
                    },
                    success: function(data) {
                    	$(".editPop .bodyPop").html(data);
                    	$(".editPop").show();
                    }
                });
            });
            $(document).delegate(".postEditBook",'submit',function (e) {
            	e.preventDefault();
            	if($("#mphb_place_switch_location").val() && $("#mphb_place_switch").data("count") < $("#mphb_place_switch_location").val().length){
            		alert("Select "+$("#mphb_place_switch").data("count")+" Switch Location");
            		return false;
            	}
            	$this = $(this);
                var id = $this.data("id");
                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: "POST",
                    dataType: "JSON",
                    data:$this.serialize(),
                    success: function(data) {
                    	location.reload();
                    	/*$(".editPop .bodyPop").html("");
                    	$(".editPop").hide();*/
                    }
                });
            });

            $(document).delegate("#mphb_place_switch",'change',function (e) {
            	html = "";
            	
            	if($("#mphb_location_data"+$(this).val()).length){
            		$ddd =  JSON.parse($("#mphb_location_data"+$(this).val()).text());
            		$.each($ddd,function(i,jj){
            			$.each(jj,function(ii,j){
            				html += "<option value='"+i+"-"+j+"'>"+j+"</option>";
            			})
            		})
            	}
				$("#mphb_place_switch_location").html(html);
            });

            $(".checkInForm").submit(function(e){
            	if(jQuery(".check_in_ids:checked").length == 0){
            		e.preventDefault();
            		alert("Please Select Atleast One Record.")
            	}
            })
		});
	</script>

<?php }
add_action('wp_ajax_nopriv_update_booking_details', 'update_booking_details_fun');
add_action('wp_ajax_update_booking_details', 'update_booking_details_fun');

function update_booking_details_fun(){
	$json = array();
	if(!empty($_POST['booking_id'])){
        $checkInDate = ParseUtils::parseCheckInDate($_POST['check_in_date'], array('allow_past_dates' => true));
        $checkOutDate = ParseUtils::parseCheckOutDate($_POST['check_out_date'], array('check_booking_rules' => false, 'check_in_date' => $checkInDate));
        $booking = mphb_get_booking($_POST['booking_id'], true);
		
        if(isset($_POST['mphb_place_switch']) && isset($_POST['mphb_place_switch_location']) && $_POST['mphb_place_switch'] && $_POST['mphb_place_switch_location']) {

        	$mphb_place0 = get_post_meta($_POST['booking_id'], 'mphb_place', true);
        	$mphb_place_booking = array();

        	$mphb_place1 = get_post_meta($_POST['mphb_place_switch'], 'mphb_place', true);
        	$mphb_place_switecher = array();

        	foreach ($mphb_place0 as $key => $value) {
        		foreach ($value as $kk => $vv) {
        			$mphb_place_booking[$key."-".$vv] = $vv;
        		}
        	}
        	
        	foreach ($mphb_place1 as $key => $value) {
        		foreach ($value as $kk => $vv) {
        			$mphb_place_switecher[$key."-".$vv] = $vv;
        		}
        	}
        	/*echo "<pre>"; print_r($mphb_place_booking); echo "</pre>";
        	echo "<pre>"; print_r($mphb_place_switecher); echo "</pre>";die; 

			$mphb_place_booking_update = array();
			$mphb_place_switecher_update = array();*/

			$i = 0;
			$count = count($_POST['mphb_place_switch_location']);
			foreach ($mphb_place_booking as $key => $value) {
				$mphb_place_switecher[$key] = $value;
				unset($mphb_place_booking[$key]);
				$i++;
				if($i == $count){
					break;
				}
			}
			foreach ($_POST['mphb_place_switch_location'] as $key => $value) {
				$mphb_place_booking[$value] = explode("-", $value)[1];
				unset($mphb_place_switecher[$value]);
			}
			$mphb_place = array();
			foreach ($mphb_place_booking as $key => $value) {
				$vvv = explode("-", $key);
				$mphb_place[$vvv[0]][] = $value;
			}
            update_post_meta($booking->getId(), "mphb_place", $mphb_place);

			$mphb_place = array();
			foreach ($mphb_place_switecher as $key => $value) {
				$vvv = explode("-", $key);
				$mphb_place[$vvv[0]][] = $value;
			}
            update_post_meta($_POST['mphb_place_switch'], "mphb_place", $mphb_place);
        }
        $roomDetails = ParseUtils::parseRooms($_POST['mphb_room_details'], array(
            'check_in_date'  => $checkInDate,
            'check_out_date' => $checkOutDate,
            'edit_booking'   => $booking
        ));

        $oldRooms = $booking->getReservedRooms();

        $uids = array();

        foreach ($oldRooms as $reservedRoom) {
            $uids[$reservedRoom->getRoomId()] = $reservedRoom->getUid();
        }

        // Create new list of reserved rooms
        $newRooms = array();

        foreach ($roomDetails as $room) {
            $services = array_map(array('\MPHB\Entities\ReservedService', 'create'), $room['services']);
            $services = array_filter($services); // Filter NULLs

            $uid = isset($uids[$room['room_id']]) ? $uids[$room['room_id']] : mphb_generate_uid();

            $newRooms[] = new ReservedRoom(array(
                'room_id'           => $room['room_id'],
                'rate_id'           => $room['rate_id'],
                'adults'            => $room['adults'],
                'children'          => $room['children'],
                'guest_name'        => $room['guest_name'],
                'reserved_services' => $services,
                'uid'               => $uid
            ));
        }


        // Update booking with new data
        $booking->setDates($checkInDate, $checkOutDate);
        $booking->setRooms($newRooms);
        $booking->updateTotal();

        // Update booking
        $saved = MPHB()->getBookingRepository()->save($booking);

        if ($saved) {
            MPHB()->getBookingRepository()->updateReservedRooms($booking->getId());
        } else {
            throw new Error(__('Unable to update booking. Please try again.', 'motopress-hotel-booking'));
        }


				if($_POST['mphb_beach_arrival_time']) {
            update_post_meta($booking->getId(), "beach_arrival_time", $_POST['mphb_beach_arrival_time']);
        }

        if($_POST['mphb_lunch_time']) {
            update_post_meta($booking->getId(), "lunch_time", $_POST['mphb_lunch_time']);
        }
		
				if($_POST['mphb_table_id']) {
		            update_post_meta($booking->getId(), "arf_cp_table_id", $_POST['mphb_table_id']);
		    }
        if(isset($_POST['mphb_place_switch']) && isset($_POST['mphb_place_switch_location']) && $_POST['mphb_place_switch'] && $_POST['mphb_place_switch_location']) {
        	$mphb_place = get_post_meta($_POST['booking_id'], 'mphb_place', true);

            update_post_meta($_POST['mphb_place_switch'], "mphb_place", $mphb_place);
						$mphb_place = array();        	
						foreach ($_POST['mphb_place_switch_location'] as $key => $value) {
							$vvv = implode("-", $value);
							if(count($vvv) == 2 ){
								$mphb_place[$vvv[0]][] = $vvv[1];
							}
						}
            update_post_meta($booking->getId(), "mphb_place", $mphb_place);
        }
        /*if(isset($_POST['mphb_place'])) {
            update_post_meta($booking->getId(), "mphb_place", $_POST['mphb_place']);
        }*/

        if(isset($_POST['mphb_place_2'])) {
            update_post_meta($booking->getId(), "mphb_place_2", $_POST['mphb_place_2']);
        }

        if(isset($_POST['mphb_place_3'])) {
            update_post_meta($booking->getId(), "mphb_place_3", $_POST['mphb_place_3']);
        }

        if(isset($_POST['mphb_place_4'])) {
            update_post_meta($booking->getId(), "mphb_place_4", $_POST['mphb_place_4']);
        }

        if(isset($_POST['mphb_place_5'])) {
            update_post_meta($booking->getId(), "mphb_place_5", $_POST['mphb_place_5']);
        }

        if(isset($_POST['mphb_place_6'])) {
            update_post_meta($booking->getId(), "mphb_place_6", $_POST['mphb_place_6']);
        }

        if(isset($_POST['mphb_place_7'])) {
            update_post_meta($booking->getId(), "mphb_place_7", $_POST['mphb_place_7']);
        }


        $booking->addLog(__('Booking was edited.', 'motopress-hotel-booking'));


        if (!empty($_POST['mphb_room_details'])) {
            foreach ( $_POST['mphb_room_details'] as $value ) {
                if (!empty($value['services'])) {
                    foreach ( $value['services'] as $reservedService ) {
                        if(empty($reservedService['id'])) continue;
                        $service_price = get_post_meta($reservedService['id'], 'service_price', true);
                        $min_pax = get_post_meta($reservedService['id'], 'min_pax', true);
                        $max_pax = get_post_meta($reservedService['id'], 'max_pax', true);

                        $featured_img_url = array();
                        $min = array();
                        $max = array();
                        foreach ($max_pax as $key => $value) {
                            $max[$key] = $value;
                        }
                        foreach ($min_pax as $key => $value) {
                            $min[$key] = $value;
                        }
                    }
                }
            }
        }



        if (!empty($_POST['products'])) {
            $products_qty_old = get_post_meta($booking->getId(),"products_qty",1);

            $product_change = array();



            $products = array();
            $products_qty = array();
            $products_title = array();
            $products_title2 = array();
            $price_total = 0;
            foreach ($_POST['products'] as $key => $value) {
                $price = 0;
                $qty = 1;
                if(!empty($service_price[$value])){
                    $products[] = ($value);
                    $price = $service_price[$value];
                    if(isset($_POST['products_qty'][$value])){
                        $qty = $_POST['products_qty'][$value];
                        $price = $qty * $price;
                    }else if(isset($max[$value]) && $max[$value] < $adults_total){
                        $qty = ceil($adults_total / $max[$value]);
                        $price = $qty * $price;
                    }
                    $products_qty[$value] = $qty;
                    $price_total += $price;
                    $price = " - €".$price;
                    $products_title[] = get_the_title($value)." x ".$qty.$price;
                    $products_title2[] = get_the_title($value)." x ".$qty;
                }
            }
            update_post_meta($booking->getId(), 'products_qty', $products_qty);
            update_post_meta($booking->getId(), 'products_price_total', $price_total);
            update_post_meta($booking->getId(), 'products', $products);
            update_post_meta($booking->getId(), 'products_title', implode(" , ", $products_title));
            update_post_meta($booking->getId(), 'products_title2', implode(" , ", $products_title2));


            if($products_qty){
                foreach ($products_qty as $key => $value) {
                    if(!isset($products_qty_old[$key])){
                        $product_change["add"][] = array(
                            "key" => $key,
                            "oldqty" => 0,
                            "qty" => $value,
                        );
                    }else if($products_qty_old[$key] != $value){
                        if($products_qty_old[$key] > $value){
                            $product_change["add"][] = array(
                                "key" => $key,
                                "oldqty" => $products_qty_old[$key],
                                "qty" => $value,
                            );  
                        }else{
                            $product_change["minus"][] = array(
                                "key" => $key,
                                "oldqty" => $products_qty_old[$key],
                                "qty" => $value,
                            );  
                        }
                        unset($products_qty_old[$key]);
                    }else{
                        unset($products_qty_old[$key]);
                    }
                }
            }
            if(!empty($product_change['add'])){
                foreach ($product_change['add'] as $key => $value) {
                    $oldstock = $stock = get_post_meta($value['key'],"stock",1);
                    $stock = $stock - ($value['qty'] - $value['oldqty']);
                    update_post_meta($value['key'],"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$booking->getId().".Product ".get_the_title($value['key'])." Old Quantity ".$value['oldqty'].", New Quantity ".$value['qty'].". Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
            if(!empty($product_change['minus'])){
                foreach ($product_change['minus'] as $key => $value) {
                    $oldstock = $stock = get_post_meta($value['key'],"stock",1);
                    $stock = $stock + ($value['oldqty'] - $value['qty']);
                    update_post_meta($value['key'],"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$booking->getId().".Product ".get_the_title($value['key'])." Old Quantity ".$value['oldqty'].", New Quantity ".$value['qty'].". Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
            if(!empty($products_qty_old)){
                foreach ($products_qty_old as $key => $value) {
                    $oldstock = $stock = get_post_meta($key,"stock",1);
                    $stock = $stock + ($value);
                    update_post_meta($key,"stock",$stock);
                    if(file_exists(ABSPATH."product_log.txt")){
                        $text = 'Booking ID '.$booking->getId().".Product ".get_the_title($key)." Old Quantity ".$value.", New Quantity 0. Old Stock ".$oldstock.", New Stock ".$stock.PHP_EOL;
                        $fp = fopen(ABSPATH."product_log.txt", 'a');
                        fwrite($fp, $text);
                    }
                }
            }
        }

        $priceBreakdown = $booking->getPriceBreakdown($booking->getId());
        if($booking){
            update_post_meta($booking->getId(), '_mphb_booking_price_breakdown', json_encode($priceBreakdown));
            update_post_meta($booking->getId(), 'mphb_total_price', $priceBreakdown["total"]);
        }
        // Reload booking after update. Refresh its data, such as reserved rooms
        // and their IDs
        $booking = mphb_get_booking($booking->getId(), true);
        do_action('mphb_update_edited_booking', $booking, $oldRooms);

				$json = array("success"=>1);
	}
	echo json_encode($json);wp_die();
}
add_action('wp_ajax_nopriv_get_booking_detail_by_id', 'get_booking_detail_by_id_fun');
add_action('wp_ajax_get_booking_detail_by_id', 'get_booking_detail_by_id_fun');

function get_booking_detail_by_id_fun(){
	$json = array();
	if(isset($_POST['id']) && $_POST['id']){
		$booking = mphb_get_booking($_POST['id'], true);

		$reservedRooms = $booking->getReservedRooms();

		$guest = "";
        if (!empty($reservedRooms) && !$booking->isImported()) {
            $adultsTotal = 0;
            $childrenTotal = 0;
            foreach ($reservedRooms as $reservedRoom) {
                $adultsTotal += $reservedRoom->getAdults();
                $childrenTotal += $reservedRoom->getChildren();
            }

            $guest .= 'Adults: ';
            $guest .= $adultsTotal;
            if ($childrenTotal > 0) {
                $guest .= '<br/>';
                $guest .= 'Children: ';
                $guest .= $childrenTotal;
            }
        }
        $services_html = "";
        $reservedRooms = $booking->getReservedRooms();

        foreach ($reservedRooms as $reservedRoom) {
            $reservedServices = $reservedRoom->getReservedServices();
            $placeholder = ' &#8212;';
            if (!empty($reservedServices)) {
                $services_html .= '<ol style="margin-left: 1em;">';
                foreach ($reservedServices as $reservedService) {
                    $services_html .= '<li>';

                    $services_html .= '<a target="_blank" href="' . esc_url(get_edit_post_link($booking->getId())) . '">' . esc_html($reservedService->getTitle()) . '</a>';
                    if ($reservedService->isPayPerAdult()) {
                        $services_html .= ' <em>' . sprintf(_n('x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking'), $reservedService->getAdults()) . '</em>';
                    }
                    if ($reservedService->isFlexiblePay()) {
                        $services_html .= ' <em>' . sprintf(_n('x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking'), $reservedService->getQuantity()) . '</em>';
                    }
                    $services_html .= '</li>';
                }
                $services_html .= '</ol>';
            } else {
                $services_html .= "";
            }
        }

        $json = array(
        	"services" => $services_html,
        	"guest" => $guest,
        );
	}	
    echo json_encode($json);wp_die();
}
add_action('wp_ajax_nopriv_get_booking_details', 'get_booking_details_fun');
add_action('wp_ajax_get_booking_details', 'get_booking_details_fun');

function get_booking_details_fun()
{
	if(isset($_POST['id']) && $_POST['id']){
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$_GET['booking_id'] = $_POST['id'];
		$booking = mphb_get_booking($_POST['id'], true);
		//$checkInDate = get_post_meta($_POST['id'],"mphb_check_in_date",true);
		//$checkOutDate = get_post_meta($_POST['id'],"mphb_check_out_date",true);
		$checkInDateStr = $booking->getCheckInDate()->format("d/m/Y");
		$checkInDateStr2 = $booking->getCheckInDate()->format("Y-m-d");
        $checkOutDateStr =  $booking->getCheckOutDate()->format("d/m/Y");

		$checkInDate = ParseUtils::parseCheckInDate($checkInDateStr, array('allow_past_dates' => true));
        $checkOutDate = ParseUtils::parseCheckOutDate($checkOutDateStr, array('check_booking_rules' => false, 'check_in_date' => $checkInDate));

		$map_rooms = array();
        $availableRooms = mphb_get_available_rooms($checkInDate, $checkOutDate, array('exclude_bookings' => $booking->getId()));
        $roomsUtil = BookingDetailsUtil::createFromAvailableRooms($availableRooms);

        $availableRooms = $roomsUtil->addTitles()->getValues();
        
        // Prepare reserved rooms list
        $roomsUtil = BookingDetailsUtil::createFromBooking($booking);

        $reservedRooms = $roomsUtil->addTitles()->addCapacities()->getValues();
        foreach($reservedRooms AS $key => $value){
            $map_rooms[] = array(
                "room_id" =>  $value['room_id'],
                "reserved_room_id" => $value['room_id'],
            );
        }

        $bookedRooms = $booking->getRoomIds();
        $rooms = array();
        $roomDetails = array();

    
        foreach ($map_rooms as $mapInfo) {
            if (!isset($mapInfo['room_id'], $mapInfo['reserved_room_id'])) {
                continue;
            }

            $roomId = mphb_posint($mapInfo['room_id']);
            $reservedRoomId = mphb_posint($mapInfo['reserved_room_id']);

            if (!in_array($reservedRoomId, $bookedRooms)) {
                $reservedRoomId = 0;
            }

            if ($roomId > 0) {
                $rooms[$roomId] = $reservedRoomId;
            }
        }

        $roomsMap = mphb_array_flip_duplicates($rooms); // [Reserved room ID => Room ID or IDs]

        // Build checkout room details
        $roomsUtil = BookingDetailsUtil::createFromRooms(array_keys($rooms));
        $roomsUtil->addCapacities()->addRates($checkInDate, $checkOutDate)->addPresets($booking, $roomsMap);

        // Use room IDs as keys to simplify the search in filter functions. But
        // don't forget that CheckoutView will only work with default indexes
        // and fail on custom ones
        $roomDetails = $roomsUtil->getValues();

        // Add "allowed_rate_ids"
        foreach ($roomDetails as $roomId => $room) {
            $rateIds = array_map(function ($rate) { return $rate->getId(); }, $room['allowed_rates']);
            $roomDetails[$roomId]['allowed_rate_ids'] = $rateIds;
        }
        $rooms = $roomDetails;

        new EditBooking\CheckoutControl($booking);
	?>
    
    <!-- <form class="" action="<?php echo esc_attr(admin_url('admin.php?page=mphb_edit_booking&booking_id='.$_POST['id'])); ?>" method="POST"> -->
    <form class="postEditBook form-wrap" >
        <!-- <input type="hidden" name="debug" value="debug"> -->
        <input type="hidden" name="action" value="update_booking_details">
        <input type="hidden" name="booking_id" value="<?php echo esc_html($_POST['id']); ?>">
        <input type="hidden" name="step" value="<?php echo esc_html("booking"); ?>">
        <input type="hidden" name="mphb_check_in_date" value="<?php echo esc_html($checkInDateStr2); ?>">
        <input type="hidden" name="check_in_date" value="<?php echo esc_html($checkInDateStr); ?>">
        <input type="hidden" name="check_out_date" value="<?php echo esc_html($checkOutDateStr); ?>">
        <input type="hidden" name="redirect" value="<?php echo esc_url(admin_url("?frdate=".$_POST['frdate']."&frname=".$_POST['frname']."&frid=".$_POST['frid'])); ?>">
        <?php wp_nonce_field('edit-booking', 'checkout_nonce'); ?>
        
        
        <section id="mphb-booking-details" class="mphb-booking-details mphb-checkout-section">
            <h3 class="mphb-booking-details-title">
                <?php _e('New Booking Details', 'motopress-hotel-booking'); ?>
            </h3>

			<p class="mphb-check-in-date">
				<span><?php _e( 'Check-in:', 'motopress-hotel-booking' ); ?></span>
				<time datetime="<?php echo $booking->getCheckInDate()->format( 'Y-m-d' ); ?>">
					<strong>
						<?php echo \MPHB\Utils\DateUtils::formatDateWPFront( $booking->getCheckInDate() ); ?>
					</strong>
				</time>,
				<span>
					<?php _ex( 'from', 'from 10:00 am', 'motopress-hotel-booking' ); ?>
				</span>
				<time datetime="<?php echo MPHB()->settings()->dateTime()->getCheckInTime(); ?>">
					<?php echo MPHB()->settings()->dateTime()->getCheckInTimeWPFormatted(); ?>
				</time>
			</p>

			<p class="mphb-check-out-date" style="display: none;">
				<span><?php _e( 'Check-out:', 'motopress-hotel-booking' ); ?></span>
				<time datetime="<?php echo $booking->getCheckOutDate()->format( 'Y-m-d' ); ?>">
					<strong>
						<?php echo \MPHB\Utils\DateUtils::formatDateWPFront( $booking->getCheckOutDate() ); ?>
					</strong>
				</time>,
				<span>
					<?php _ex( 'until', 'until 10:00 am', 'motopress-hotel-booking' ); ?>
				</span>
				<time datetime="<?php echo MPHB()->settings()->dateTime()->getCheckOutTime(); ?>">
					<?php echo MPHB()->settings()->dateTime()->getCheckOutTimeWPFormatted(); ?>
				</time>
			</p>
            
	        <div class="mphb-reserve-rooms-details">
	            <?php
	            foreach ($booking->getReservedRooms() as $index => $reservedRoom) {
	            	$roomIndex = $index;
	                $roomTypeId = apply_filters('_mphb_translate_post_id', $reservedRoom->getRoomTypeId());
	                $roomType = MPHB()->getRoomTypeRepository()->findById($roomTypeId);
	                ?>

			        <p class="mphb-room-title">
			            <span>
			                <?php _e('Accommodation:', 'motopress-hotel-booking'); ?>
			            </span>
			            <a href="<?php echo esc_url(get_edit_post_link($roomId)); ?>" target="_blank">
			                <?php echo get_the_title($roomId); ?>
			            </a>
			        </p>

	                <div class="mphb-room-details" data-index="<?php echo esc_attr($index); ?>">
	                    <input type="hidden" name="mphb_room_details[<?php echo esc_attr($index); ?>][room_type_id]" value="<?php echo esc_attr($roomType->getOriginalId()); ?>">
	                    <input type="hidden" name="mphb_room_details[<?php echo esc_attr($index); ?>][room_id]" value="<?php echo esc_attr($reservedRoom->getRoomId()); ?>">

	                    
						<h3 class="mphb-room-number">
							<?php printf( __( 'Accommodation #%d', 'motopress-hotel-booking' ), $roomIndex + 1 ); ?>
						</h3>
						<p class="mphb-room-type-title">
							<span>
								<?php _e( 'Accommodation Type:', 'motopress-hotel-booking' ); ?>
							</span>
							<a href="<?php echo esc_url( $roomType->getLink() ); ?>" target="_blank">
								<?php echo $roomType->getTitle(); ?>
							</a>
						</p>

	                </div>
	                <?php
						global $wpdb;
						$namePrefix = 'mphb_room_details[' . esc_attr($roomIndex) . ']';
				        $idPrefix = 'mphb_room_details-' . esc_attr($roomIndex);

				        // Value -1 means that nothing is selected ("— Select —" option active)
				        $adultsCapacity = $roomType->getAdultsCapacity();
				        $minAdults = mphb_get_min_adults();
				        $maxAdults = $adultsCapacity;
				        $presetAdults = -1;


				        $roomId = $reservedRoom->getRoomId();
				         
				        if (isset($rooms[$roomId]['presets']['adults'])) {
				            $presetAdults = $rooms[$roomId]['presets']['adults'];
				        }

				        $childrenCapacity = $roomType->getChildrenCapacity();
				        $minChildren = mphb_get_min_children();
				        $maxChildren = $childrenCapacity;
				        $presetChildren = -1;

				        if (isset($rooms[$roomId]['presets']['children'])) {
				            $presetChildren = $rooms[$roomId]['presets']['children'];
				        }


				        $totalCapacity = $roomType->getTotalCapacity();

				        if (!empty($totalCapacity)) {
				            $maxAdults = max($minAdults, min($adultsCapacity, $totalCapacity));
				            $maxChildren = max($minChildren, min($childrenCapacity, $totalCapacity));

				            if ($presetAdults + $presetChildren > $totalCapacity) {
				                // Someone misused the filters? Reset values
				                $presetAdults = $maxAdults;
				                $presetChildren = -1;
				            }
				        } else {
				            $totalCapacity = $roomType->calcTotalCapacity();
				        }

				        $childrenAllowed = $maxChildren > 0 && MPHB()->settings()->main()->isChildrenAllowed();

				        $presetGuestName = "";


				        if (isset($rooms[$roomId]['presets']['guest_name'])) {
				            $presetGuestName = $rooms[$roomId]['presets']['guest_name'];
				        }


				        $beacharrivaltime = get_post_meta($_GET["booking_id"], 'beach_arrival_time', true); 
				        $lunchtime = get_post_meta($_GET["booking_id"], 'lunch_time', true);
				        // $lunch = get_post_meta($_GET["booking_id"], 'lunch_time', true); 
						$mphb_place = get_post_meta($_GET["booking_id"], 'mphb_place', true);
						//echo "<pre>";print_r($mphb_place);
						$mphb_place_1 = get_post_meta($_GET["booking_id"], 'mphb_place_1', true);
				        $mphb_place_2 = get_post_meta($_GET["booking_id"], 'mphb_place_2', true);
				        $mphb_place_3 = get_post_meta($_GET["booking_id"], 'mphb_place_3', true);
				        $mphb_place_4 = get_post_meta($_GET["booking_id"], 'mphb_place_4', true);
				        $mphb_place_5 = get_post_meta($_GET["booking_id"], 'mphb_place_5', true);
				        $mphb_place_6 = get_post_meta($_GET["booking_id"], 'mphb_place_6', true);
				        $mphb_place_7 = get_post_meta($_GET["booking_id"], 'mphb_place_7', true);
				        $mphb_table_id = get_post_meta($_GET["booking_id"], 'arf_cp_table_id', true);
				        $lunch_time_list = get_posts([
						        'numberposts'       => -1,
						        'post_type'     => 'lunch_time',
						        'post_status'   => 'publish',
						        'suppress_filters' => 0
						    ]);
				        ?>
				        <?php if (MPHB()->settings()->main()->isAdultsAllowed()) { ?>
				            <p class="mphb-adults-chooser">
				                <label for="<?php echo esc_attr($idPrefix); ?>-adults">
				                    <?php
				                    if (MPHB()->settings()->main()->isChildrenAllowed()) {
				                        _e('Adults', 'motopress-hotel-booking');
				                    } else {
				                        _e('Guests', 'motopress-hotel-booking');
				                    }
				                    ?>
				                    <abbr title="<?php _e('Required', 'motopress-hotel-booking'); ?>">*</abbr>
				                </label>
				                <select name="<?php echo esc_attr($namePrefix); ?>[adults]" id="<?php echo esc_attr($idPrefix); ?>-adults" class="mphb_sc_checkout-guests-chooser w-100 mphb_checkout-guests-chooser" required="required" data-max-allowed="<?php echo esc_attr($adultsCapacity); ?>" data-max-total="<?php echo esc_attr($totalCapacity); ?>">
				                    <option value=""><?php _e('— Select —', 'motopress-hotel-booking'); ?></option>
				                    <?php for ($i = 1; $i <= $maxAdults; $i++) { ?>
				                        <option value="<?php echo $i; ?>" <?php selected($i, $presetAdults); ?>>
				                            <?php echo $i; ?>
				                        </option>
				                    <?php } ?>
				                </select>
				            </p>
				        <?php } else { ?>
				            <input type="hidden" id="<?php echo esc_attr($idPrefix); ?>-adults" name="<?php echo esc_attr($namePrefix); ?>[adults]" value="<?php echo esc_attr($minAdults); ?>">
				        <?php } ?>

				        <?php if ($childrenAllowed) { ?>
				            <p class="mphb-children-chooser">
				                <label for="<?php echo esc_attr($idPrefix); ?>-children">
				                    <?php printf(__('Children %s', 'motopress-hotel-booking'), MPHB()->settings()->main()->getChildrenAgeText()); ?>
				                    <abbr title="<?php _e('Required', 'motopress-hotel-booking'); ?>">*</abbr>
				                </label>
				                <select name="<?php echo esc_attr($namePrefix); ?>[children]" id="<?php echo esc_attr($idPrefix); ?>-children" class="mphb_sc_checkout-guests-chooser w-100 mphb_checkout-guests-chooser" required="required" data-max-allowed="<?php echo esc_attr($childrenCapacity); ?>" data-max-total="<?php echo esc_attr($totalCapacity); ?>">
				                    <option value=""><?php _e('— Select —', 'motopress-hotel-booking'); ?></option>
				                    <?php for ($i = 0; $i <= $maxChildren; $i++) { ?>
				                        <option value="<?php echo $i; ?>" <?php selected($i, $presetChildren); ?>>
				                            <?php echo $i; ?>
				                        </option>
				                    <?php } ?>
				                </select>
				            </p>
				        <?php } else { ?>
				            <input type="hidden" id="<?php echo esc_attr($idPrefix); ?>-children" name="<?php echo esc_attr($namePrefix); ?>[children]" value="<?php echo esc_attr($minChildren); ?>">
				        <?php } ?>

				        <p class="mphb-guest-name-wrapper">
				            <label for="<?php echo esc_attr($idPrefix); ?>-guest-name">
				                <?php _e('Full Guest Name', 'motopress-hotel-booking'); ?>
				            </label>
				            <input type="text" name="<?php echo esc_attr($namePrefix); ?>[guest_name]" id="<?php echo esc_attr($idPrefix); ?>-guest-name" value="<?php echo esc_attr($presetGuestName); ?>" class="w-100">
				        </p>
				        <p>
				            <label for="mphb_beach_arrival_time">
				                <?php _e('Beach arrival time', 'motopress-hotel-booking'); echo $beacharrivaltime; ?>
				            </label><br>
				            <select name="mphb_beach_arrival_time" id="mphb_beach_arrival_time" class="w-100">
				            <?php
				            	if ($beacharrivaltime == '9:50'){
				                    echo '<option selected value="9:50">9:50</option>';    
				                } else {
				                    echo '<option value="9:50">9:50</option>';    
				                }
								if ($beacharrivaltime == '10:10'){
				                    echo '<option selected value="10:10">10:10</option>';    
				                } else {
				                    echo '<option value="10:10">10:10</option>';    
				                }
								if ($beacharrivaltime == '10:30'){
				                    echo '<option selected value="10:30">10:30</option>';    
				                } else {
				                    echo '<option value="10:30">10:30</option>';    
				                }
								if ($beacharrivaltime == '10:50'){
				                    echo '<option selected value="10:50">10:50</option>';    
				                } else {
				                    echo '<option value="10:50">10:50</option>';    
				                }
								if ($beacharrivaltime == '11:10'){
				                    echo '<option selected value="11:10">11:10</option>';    
				                } else {
				                    echo '<option value="11:10">11:10</option>';    
				                }
								if ($beacharrivaltime == '11:30'){
				                    echo '<option selected value="11:30">11:30</option>';    
				                } else {
				                    echo '<option value="11:30">11:30</option>';    
				                }
								if ($beacharrivaltime == '11:50'){
				                    echo '<option selected value="11:50">11:50</option>';    
				                } else {
				                    echo '<option value="11:50">11:50</option>';    
				                }
								if ($beacharrivaltime == '12:10'){
				                    echo '<option selected value="12:10">12:10</option>';    
				                } else {
				                    echo '<option value="12:10">12:10</option>';    
				                }
								if ($beacharrivaltime == '12:30'){
				                    echo '<option selected value="12:30">12:30</option>';    
				                } else {
				                    echo '<option value="12:30">12:30</option>';    
				                }
								if ($beacharrivaltime == '13:00'){
				                    echo '<option selected value="13:00">13:00</option>';    
				                } else {
				                    echo '<option value="13:00">13:00</option>';    
				                }
								if ($beacharrivaltime == '13:15'){
				                    echo '<option selected value="13:15">13:15</option>';    
				                } else {
				                    echo '<option value="13:15">13:15</option>';    
				                }             
				            ?>
				            </select>
				        </p>
				        <p>
				            <label for="mphb_lunch_time">
				                <?php _e('Lunch time', 'motopress-hotel-booking'); ?>
				            </label><br>
				            <select name="mphb_lunch_time" id="mphb_lunch_time" class="w-100">
				            	<?php foreach ($lunch_time_list as $key => $value) { ?>
						            <?php 
						                if ($lunchtime == $value->ID || $lunchtime == $value->post_title){
						                    echo '<option selected value="'.$value->ID.'">'.$value->post_title.'</option>';    
						                } else {
						                    echo '<option value="'.$value->ID.'">'.$value->post_title.'</option>';    
						                }
						            ?>
				            	<?php } ?>
				            </select>
				        </p>
						<?php
				        $lunch_time = get_post_meta($booking->getId(), 'lunch_time', true);
				        $lunch_time_text = get_lunch_text($lunch_time);
				        if(!$lunch_time){
				        	$lunch_time = "12:00";
				        }
				        $mphb_check_in_date = $booking->getCheckInDate()->format( 'Y-m-d' );

				        $table_selected_ids = [];
				        $booking_ids = $wpdb->get_results ("
				    SELECT post_id 
				    FROM  $wpdb->postmeta
				        LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id)
				        WHERE `meta_key` = 'mphb_check_in_date' AND ".$wpdb->prefix."posts.post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')
				        AND `meta_value` = '$mphb_check_in_date' AND ".$wpdb->prefix."posts.ID != '".$_GET["booking_id"]."'
				"); 
				        /*WHERE `meta_key` = 'mphb_check_in_date' AND ".$wpdb->prefix."posts.post_status != 'cancelled' AND ".$wpdb->prefix."posts.post_status != 'trash' AND ".$wpdb->prefix."posts.post_status != 'expired-reservation' AND ".$wpdb->prefix."posts.post_status != 'special-cancellations'*/
				        $booking_slot = array();
				        $booking_slot_switch = array();
				        foreach ( $booking_ids as $booking_id )
				        {
				            $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
				            if($item_lunch_time == $lunch_time || $item_lunch_time == $lunch_time_text) {
				                $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
				                if(is_array($ids)){
				                    $table_selected_ids = array_merge($table_selected_ids,$ids);
				                }else{
				                    $table_selected_ids[] = $ids;
				                }
				            }
				            if($booking_id->post_id != $_GET["booking_id"]){
				                $mphb_first_name = get_post_meta($booking_id->post_id, 'mphb_first_name', true);
				                $mphb_last_name = get_post_meta($booking_id->post_id, 'mphb_last_name', true);
				            	$booking_slot_switch[$booking_id->post_id]['name'] = $mphb_first_name." ".$mphb_last_name;
				                $mmm = get_post_meta($booking_id->post_id, 'mphb_place', true);
				                if($mmm){
				                    foreach($mmm AS $kk => $vv){
				                        if(isset($booking_slot_switch[$booking_id->post_id]['location'][$kk])){
				                			$booking_slot_switch[$booking_id->post_id]['location'][$kk] = array_merge($booking_slot_switch[$booking_id->post_id]['location'][$kk],$vv);
				                		}else{
				                			$booking_slot_switch[$booking_id->post_id]['location'][$kk] = $vv;	
				                		}
				                        if(isset($booking_slot[$kk])){
				                            $booking_slot[$kk] = array_merge($booking_slot[$kk],$vv);
				                        }else{
				                            $booking_slot[$kk] = $vv;
				                        }
				                    }
				                }    
				            }
				            
				        }
				        $count_locations = 0;
				        if($mphb_place){
					        foreach ($mphb_place as $key => $value) {
					        	$count_locations += count($value);
				        	}
				        }
				        $args = array(
				            'post_type' => 'arf_pt_table',
				            'posts_per_page' => -1,
				            'post_status' => 'publish',
				            'orderby' => 'post_title',
				            'order' => 'ASC'
				        );

				        if($table_selected_ids) {
				            $args['post__not_in'] = $table_selected_ids;
				        }
				        $arf_pt_tables = get_posts($args); 
				        ?>
				        <p>
				            <lable for="mphb_table_id"><?php _e('Table', 'motopress-hotel-booking'); ?></lable>
				            <br>
				            <select name="mphb_table_id[]" multiple="multiple" id="mphb_table_id" class="w-100">
				                <!-- <option value=""><?php _e('Select', 'arienzo_reservation_form') ?></option> -->
				                <?php foreach ( $arf_pt_tables as $table ) { ?>
				                    <option value="<?php echo $table->ID; ?>" <?php if(!is_array($mphb_table_id) && $mphb_table_id == $table->ID){echo "selected";}else if(is_array($mphb_table_id) && in_array($table->ID,$mphb_table_id)){echo "selected";} ?>><?php echo $table->post_title; ?></option>
				                <?php } ?>
				            </select>
				        </p>
				        <p>
			                <lable for="mphb_place_switch">Switch Slot</lable>
			                <br>
			                <select name="mphb_place_switch" id="mphb_place_switch" class="w-100" data-count="<?php echo $count_locations ?>">
			                	<option value="">Select</option>
			                    <?php foreach($booking_slot_switch AS $kk => $vv){ ?>
			                    <option  value="<?php _e($kk, 'arienzo_reservation_form') ?>"><?php _e($vv['name'], 'arienzo_reservation_form') ?></option>
			                    <?php } ?>
			                </select>
			                    <?php foreach($booking_slot_switch AS $kk => $vv){ ?>
			                    <div style="display:none;" id="mphb_location_data<?php echo $kk ?>"><?php echo json_encode($vv['location']) ?></div>
			                    <?php } ?>
			            </p>
				        <p>
			                <lable for="mphb_place_switch_location">Slot Location</lable>
			                <br>
			                <select name="mphb_place_switch_location[]" id="mphb_place_switch_location" class="w-100" multiple="multiple">
			                </select>
			            </p>
						<?php 
				    		    
				            $args = array(
				                'post_type' => 'location',
				                'posts_per_page' => -1,
				                'post_status' => 'publish',
				                'orderby' => 'post_title',
				                'order' => 'ASC'
				            );
				            $locations = get_posts($args);
						?>
						<?php foreach($locations AS $key => $value){ $ddd = get_field("location_names",$value->ID); $arr = array(); $ddd = explode("|",$ddd); if($ddd){ foreach($ddd AS $kk => $vv){ $arr = array_merge($arr,explode(",",$vv)); } } ?>
						    <!-- <p>
				                <lable for="mphb_place_<?php echo $value->ID; ?>"><?php echo $value->post_title; ?></lable>
				                <br>
				                <select name="mphb_place[<?php echo $value->ID; ?>][]" multiple="multiple" id="mphb_place_<?php echo $value->ID; ?>" class="w-100">
				                    <?php foreach($arr AS $kk => $vv){ ?>
				                    <option  <?php echo isset($booking_slot[$value->ID]) && in_array( $vv, $booking_slot[$value->ID]) ? "disabled" : "" ?>  <?php echo isset($mphb_place[$value->ID]) && in_array( $vv, $mphb_place[$value->ID]) ? "selected" : "" ?> value="<?php _e($vv, 'arienzo_reservation_form') ?>"><?php _e($vv, 'arienzo_reservation_form') ?></option>
				                    <?php } ?>
				                </select>
				            </p> -->
						<?php } ?>
				    <?php
			        if ($roomType->hasServices()) {
			            

			        $services = MPHB()->getServiceRepository()->findAll(array(
			            'post__in'         => $roomType->getServices(),
			            'suppress_filters' => true
			        ));

			        if (!empty($services)) {
			        ?>
			        <section id="mphb-services-details-<?php echo esc_attr($roomIndex); ?>" class="mphb-services-details mphb-checkout-item-section">
			            <h4 class="mphb-services-details-title">
			                <?php _e('Choose Additional Services', 'motopress-hotel-booking'); ?>
			            </h4>

			            <ul class="mphb_sc_checkout-services-list mphb_checkout-services-list">
			                <?php foreach ($services as $index => $service) {
			                    $serviceId = $service->getOriginalId();
			                    if ($service->isPayPerAdult() && $roomType->getAdultsCapacity() > 1) {
			                        $presetAdults = $roomType->getAdultsCapacity();
			                    }else{
			                        $presetAdults = $reservedRoom->getAdults();
			                    }
						        $serviceId = $service->getOriginalId();
						        

						        if (isset($rooms[$roomId]['presets']['services'][$serviceId])) {
						            $presetAdults = $rooms[$roomId]['presets']['services'][$serviceId]['adults'];
						            $adultsCapacity = $rooms[$roomId]['adults'];
						            if ($adultsCapacity != mphb_get_min_adults()) {
						                $presetAdults = min((int)$presetAdults, (int)$adultsCapacity);
						            }
						        }
			                    $presetChild = $roomType->getChildrenCapacity();

			                    
						        if (isset($rooms[$roomId]['presets']['children'])) {
						            $presetChild = $rooms[$roomId]['presets']['children'];
						        }

			                    $namePrefix = 'mphb_room_details[' . esc_attr($roomIndex) . '][services][' . esc_attr($index) . ']';
			                    $idPrefix = 'mphb_room_details-' . esc_attr($roomIndex) . '-service-' . $serviceId;
			                     //echo "<pre>"; print_r(); echo "</pre>";die; 
			                    $service = apply_filters('_mphb_translate_service', $service);
			                    $isSelected = false;


						        if (isset($rooms[$roomId]['presets']['services'][$serviceId])) {
						            $isSelected = true;
						        }


			                    $show_child = get_post_meta( $service->getId(), 'mphb_show_child', true );
			                    $mphb_couple_package = get_post_meta( $service->getId(), 'mphb_couple_package', true );
			                    ?>
			                    <li>
			                        <label for="<?php echo $idPrefix; ?>-id" class="mphb-checkbox-label">
			                            <input type="checkbox" id="<?php echo $idPrefix; ?>-id" name="<?php echo $namePrefix; ?>[id]" class="mphb_sc_checkout-service mphb_checkout-service" value="<?php echo $serviceId; ?>" <?php checked($isSelected); ?>>
			                            <?php echo $service->getTitle(); ?>
			                            <em>(<?php echo $service->getPriceWithConditions(false); ?>)</em>
			                        </label>

			                        <?php if ($service->isPayPerAdult() && $roomType->getAdultsCapacity() > 1) { ?>
			                            <label for="<?php echo $idPrefix; ?>-adults">
			                                <?php _e('for ', 'motopress-hotel-booking'); ?>
			                                <select name="<?php echo $namePrefix; ?>[adults]" id="<?php echo $idPrefix; ?>-adults" class="mphb_sc_checkout-service-adults mphb_checkout-service-adults">
			                                    <?php for ($i = 1; $i <= $roomType->getAdultsCapacity(); $i++) { ?>
			                                        <option value="<?php echo $i; ?>" <?php selected($presetAdults, $i); ?>>
			                                            <?php echo $i; ?>
			                                        </option>
			                                    <?php } ?>
			                                </select>
			                                <?php echo _x(' guest(s)', 'Example: Breakfast for X guest(s)', 'motopress-hotel-booking'); ?>
			                            </label>
			                            
			                            <?php if ($show_child && $roomType->getChildrenCapacity() > 1) { ?>
			                            <label for="<?php echo $idPrefix; ?>-child">
			                                <?php _e('for ', 'motopress-hotel-booking'); ?>
			                                <select name="<?php echo $namePrefix; ?>[child]" id="<?php echo $idPrefix; ?>-child" class="mphb_sc_checkout-service-child mphb_checkout-service-child">
			                                    <?php for ($i = 0; $i <= $roomType->getChildrenCapacity(); $i++) { ?>
			                                        <option value="<?php echo $i; ?>" <?php ($i) ? selected($presetChild, $i) : ""; ?>>
			                                            <?php echo $i; ?>
			                                        </option>
			                                    <?php } ?>
			                                </select>
			                                <?php echo _x(' Child(s)', 'Example: Breakfast for X Child(s)', 'motopress-hotel-booking'); ?>
			                            </label>
			                            <?php }else{ ?>
			                            <input type="hidden" name="<?php echo $namePrefix; ?>[child]" value="0">
			                            <?php } ?>
			                        <?php } else if(isset($_GET['page']) && $_GET['page'] == "mphb_add_new_booking"){ ?>
			                            <input type="hidden" name="<?php echo $namePrefix; ?>[adults]" value="1">
			                            <input type="hidden" name="<?php echo $namePrefix; ?>[child]" value="0">
			                        <?php }else{ ?>
			                            <?php if($mphb_couple_package){ ?>
			                                <select name="<?php echo $namePrefix; ?>[adults]" id="<?php echo $idPrefix; ?>-adults" class="mphb_sc_checkout-service-adults mphb_checkout-service-adults">
			                                    <?php for ($i = 0; $i <= $roomType->getAdultsCapacity(); $i++) { ?>
			                                        <option value="<?php echo $i; ?>" <?php ($i) ? selected($presetAdults, $i) : ""; ?>>
			                                            <?php echo $i; ?>
			                                        </option>
			                                    <?php } ?>
			                                </select>
			                                <?php echo _x(' Couples(s)', 'Example: Breakfast for X Couples(s)', 'motopress-hotel-booking'); ?>
			                            <?php }else{ ?>
			                                <input type="hidden" name="<?php echo $namePrefix; ?>[adults]" value="<?php echo  $presetAdults ?>">
			                                
			                            <?php } ?>
			                        <?php } ?>

			                        <?php if ($service->isFlexiblePay()) { ?>
			                            <?php
			                                $minQuantity = $service->getMinQuantity();
			                                $maxQuantity = $service->getMaxQuantityNumber();

			                                if ($service->isAutoLimit()) {
			                                    $maxQuantity = DateUtils::calcNights($booking->getCheckInDate(), $booking->getCheckOutDate());
			                                }

			                                $maxQuantity = max($minQuantity, $maxQuantity);

			                                $presetQuantity = apply_filters('mphb_sc_checkout_preset_service_quantity', $minQuantity, $service, $reservedRoom, $roomType);
			                                $presetQuantity = mphb_limit($presetQuantity, $minQuantity, $maxQuantity);
			                            ?>
			                            &#215; <input type="number" name="<?php echo $namePrefix; ?>[quantity]" class="mphb_sc_checkout-service-quantity mphb_checkout-service-quantity" value="<?php echo esc_attr($presetQuantity); ?>" min="<?php echo esc_attr($minQuantity); ?>" <?php echo !$service->isUnlimited() ? 'max="' . esc_attr($maxQuantity) . '"' : ''; ?> step="1"> <?php _e('time(s)', 'motopress-hotel-booking'); ?>
			                        <?php } // Is flexible pay? ?>
			                    </li>
			                <?php } ?>
			            </ul>
			        </section>
			        <?php } ?>
			      <?php } ?>

			      <?php
			       	$products_qty = get_post_meta($booking->getId(),"products_qty",true);
        			$products = get_post_meta($booking->getId(),"products",true);

        			
        			foreach ($booking->getReservedRooms() as $index => $reservedRoom) {
        					$s_services = $reservedRoom->getReservedServices();
        		?>	
				      <?php if ($s_services) { ?>

				        <section id="mphb-product-details-<?php echo esc_attr($roomIndex); ?>" class="mphb-product-details mphb-checkout-item-section">
				            <h4 class="mphb-product-details-title">
				                <?php _e('Choose Products', 'motopress-hotel-booking'); ?>
				            </h4>
				            <?php foreach ($s_services as $index => $service) {
				                $features_image_type = get_post_meta($service->getId(), 'features_image_type', true);
				                $service_price = get_post_meta($service->getId(), 'service_price', true);
				                $min_pax = get_post_meta($service->getId(), 'min_pax', true);
				                $max_pax = get_post_meta($service->getId(), 'max_pax', true);
				                $featured_img_url = array();
				                $min = array();
				                $max = array();
				                foreach ($max_pax as $key => $value) {
				                    $max[$key] = $value;
				                }
				                foreach ($min_pax as $key => $value) {
				                    $min[$key] = $value;
				                }
				            ?>
				            <div class="product_container">
				                <h4><?= $service->getTitle() ?></h4>
				                <?php if($features_image_type){ ?>

				                    <?php foreach ($features_image_type as $key => $value) { if($value == "service") continue; 
				                        $qty_stock = (int)get_post_meta($value,"stock",1);
				                        if(!$qty_stock) continue;
				                        ?>
				                        <?php $title = get_the_title( $value ); ?>
				                        <?php if($title) {
				                            /*$stock = get_post_meta($value,"stock",1);*/
				                            $min_qty = (isset($min[$value]) ? $min[$value] : 0);
				                            $max_qty = (isset($max[$value]) ? $max[$value] : 0);
				                            ?>
				                            <div class="product_div_<?php echo $value; ?> product_main_div" data-stock="<?= $qty_stock ?>" data-min="<?php echo $min_qty ?>" data-max="<?php echo $max_qty ?>">
				                                <input type="hidden" name="product[<?php echo $value ?>]" value="0" id="product_<?php echo $value ?>" data-value="<?= isset($service_price[$value]) ? $service_price[$value] : 0 ?>">
				                                <table style="background: #ffffff;">
				                                    <tbody>
				                                        <tr>
				                                            <td style="font-size: 12px;" class="product_title">
				                                                <input type="radio" name="products[]" value="<?= $value ?>" <?= $products && in_array($value, $products) ? "checked" : "" ?>>
				                                            </td>
				                                            <td style="font-size: 12px;" class="product_title"><?= $title ?></td>
				                                            <td style="font-size: 12px;    width: 40px;"><?= isset($service_price[$value]) ? "€".$service_price[$value] : "-" ?></td>
				                                            <td>
				                                                <select name="products_qty[<?= $value ?>]">
				                                                    <?php for ($i= 1; $i <= min(10,$qty_stock); $i++) { ?>
				                                                        <option  <?= isset($products_qty[$value]) && $products_qty[$value] == $i  ? "selected" : "" ?> value="<?= $i ?>"><?= $i ?></option>
				                                                    <?php } ?>
				                                                    <!-- <?php for ($i= $min_qty; $i <= $max_qty; $i++) { ?>
				                                                        <option  <?= isset($products_qty[$value]) && $products_qty[$value] == $i  ? "selected" : "" ?> value="<?= $i ?>"><?= $i ?></option>
				                                                    <?php } ?> -->
				                                                </select>
				                                            </td>
				                                        </tr>
				                                    </tbody>
				                                </table>
				                            </div>
				                        <?php } ?>
				                    <?php } ?>
				                <?php } ?>
				            </div>
				            <?php } ?>
				        </section>
				        <script type="text/javascript">
				            jQuery(document).ready(function(){
				                jQuery(".mphb_sc_checkout-service.mphb_checkout-service").click(function(){
				                    jQuery(".mphb_sc_checkout-service.mphb_checkout-service").prop("checked",false);
				                    jQuery(this).prop("checked",true);
				                    $this = jQuery(this);
				                    jQuery.ajax({
				                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
				                        type:'POST',
				                        data:{
				                            action : "get_product_list_by_service",
				                            id : $this.val(),
				                        },
				                        beforeSend:function(){
				                            jQuery(".product_container").html("Loading... <i style='animation-name: spin;animation-duration: 5000ms;animation-iteration-count: infinite;animation-timing-function: linear; ' class='dashicons dashicons-update-alt'></i>");
				                        },
				                        success:function(html){
				                            jQuery(".product_container").html(html);
				                        },
				                    })
				                })
				            })
				        </script>
				      <?php } ?>
			      
				    <?php } ?>
			        <?php
			        $namePrefix = 'mphb_room_details[' . esc_attr($roomIndex) . ']';
			        $idPrefix = 'mphb_room_details-' . esc_attr($roomIndex);
			      
							$allowedRates   = $roomDetails[$reservedRoom->getRoomId()]['allowed_rates'];

					        $defaultRate    = reset($allowedRates);
					       
							$adults         = $roomDetails[$reservedRoom->getRoomId()]['adults'];
							$children       = $roomDetails[$reservedRoom->getRoomId()]['children'];
			        $selectedRateId = "";

			        if($defaultRate){
			            $selectedRateId = $defaultRate->getOriginalId();     
			            
			            $roomId = $reservedRoom->getRoomId();

				        if (isset($rooms[$roomId]['presets']['rate_id'])) {
				            $presetId = $rooms[$roomId]['presets']['rate_id'];

				            // Don't set the unallowed rate
				            if (in_array($presetId, $rooms[$roomId]['allowed_rate_ids'])) {
				                $selectedRateId = $presetId;
				            }
				        }    
			        }
			        

			        if (count($allowedRates) > 1) {
			        	
			            ?>
			            <section class="mphb-rate-chooser mphb-checkout-item-section">
			                <h4 class="mphb-room-rate-chooser-title">
			                    <?php _e('Choose Rate', 'motopress-hotel-booking'); ?>
			                </h4>

			                <?php
			                foreach ($allowedRates as $rate) {
			                    $rate = apply_filters('_mphb_translate_rate', $rate);
			                    $rateId = $rate->getOriginalId();

			                    MPHB()->reservationRequest()->setupParameters(array(
			                        'adults'         => $adults,
			                        'children'       => $children,
			                        'check_in_date'  => $booking->getCheckInDate(),
			                        'check_out_date' => $booking->getCheckOutDate()
			                    ));

			                    $ratePrice = mphb_format_price($rate->calcPrice($booking->getCheckInDate(), $booking->getCheckOutDate()));

			                    $inputId = $idPrefix . '-rate-id-' . $rateId;
			                    $inputName = $namePrefix . '[rate_id]';

			                    ?>
			                    <p class="mphb-room-rate-variant">
			                        <label for="<?php echo esc_attr($inputId); ?>">
			                            <input type="radio" id="<?php echo esc_attr($inputId); ?>" name="<?php echo esc_attr($inputName); ?>" class="mphb_sc_checkout-rate mphb_checkout-rate mphb-radio-label" value="<?php echo esc_attr($rateId); ?>" <?php checked($selectedRateId, $rateId); ?>>
			                            <strong>
			                                <?php echo esc_html($rate->getTitle()) . ', ' . $ratePrice; ?>
			                            </strong>
			                        </label>
			                        <br>
			                        <?php echo esc_html($rate->getDescription()); ?>
			                    </p>
			                <?php } // For each allowed rate ?>
			            </section>
			        <?php } else { ?>
			            <input type="hidden" name="<?php echo esc_attr($namePrefix); ?>[rate_id]" value="<?php echo esc_attr($selectedRateId); ?>">
			        <?php } ?>
	            <?php } ?>
	        </div>
        </section>

        <p class="mphb-submit-button-wrapper">
            <input type="submit" name="edit-booking" class="button button-primary button-hero" value="<?php _e('Save', 'motopress-hotel-booking'); ?>">
        </p>
    </form>

	<?php
	}
	die;
}
add_action('wp_ajax_nopriv_change_booking_details', 'change_booking_details_fun');
add_action('wp_ajax_change_booking_details', 'change_booking_details_fun');

function change_booking_details_fun()
{
	if(isset($_POST['id']) && $_POST['id'] && isset($_POST['field']) && $_POST['field']){
		if($_POST['field'] == "name"){
			$fname= "";
			$lname= "";
			if(isset($_POST['data_val'])){
				$frname_v = explode(" ", $_POST['data_val']);
				$fname = $frname_v[0];
				unset($frname_v[0]);
			    if($frname_v){
			    	$lname = implode(" ", $frname_v);
			    }
			}

			update_post_meta($_POST['id'],"mphb_first_name",$fname);
			update_post_meta($_POST['id'],"mphb_last_name",$lname);
		}else if($_POST['field'] == "phone"){
			update_post_meta($_POST['id'],"mphb_phone",(isset($_POST['data_val']) ? $_POST['data_val'] : ""));
		}
	}
	echo json_encode(array());wp_die();
}
add_action('add_meta_boxes', 'booking_confiration_link_custom_meta');


/*
function wpdocs_add_dashboard_widgets() {
    wp_add_dashboard_widget( 'dashboard_widget', 'Booking', 'dashboard_widget_function' );
}
add_action( 'wp_dashboard_setup', 'wpdocs_add_dashboard_widgets' );

function dashboard_widget_function( $post, $callback_args ) {
    esc_html_e( "Hello World, this is my first Dashboard Widget!", "textdomain" );
}
*/

/*
add_action( 'admin_menu', 'wptw_default_published_post' );
function wptw_default_published_post(){
    global $submenu;
    //print_r($submenu);
    // POSTS
    foreach( $submenu['mphb_booking_menu'] as $key => $value )
    {
       
        if( in_array( 'edit.php?post_type=mphb_booking', $value ) )
        {
            $submenu['edit.php'][ $key ][2] = 'edit.php?post_status=pending&post_type=mphb_booking';
        }
    }
}
*/
// Add inline CSS in the admin head with the style tag
function my_custom_admin_head() {
	global $current_user;

    $user_roles = $current_user->roles;
    if(in_array("owner", $user_roles)){ ?>
	?>
    <style>
    	#menu-comments,#menu-posts-popup,#toplevel_page_wpcf7,#menu-appearance,#menu-tools,#toplevel_page_vcv-settings,#toplevel_page_edit-post_type-acf-field-group,#toplevel_page_sitepress-multilingual-cms-menu-languages,#menu-posts{
    		display: none;
    	}
    </style>
    <?php
	}
	?>
	<style type="text/css">
		.arf_dashboard_box_float_left {
		    margin: 0 !important;
		    margin-right: 10;
		    padding: 0;
		    background-position: top center;
		    background-repeat: no-repeat;
		    background-size: 100% 100%;
		    width: 24% !important;
		    /* padding: 0 10px; */
		}
		.arf_dashboard_box_big_green,.arf_dashboard_box_big_red{
			width: 49% !important;
			margin-top: 20px !important;
		}
		#arf_dashboard_booking_box{
    		display: flex;
        	justify-content: space-between;
			flex-direction: initial;
    		flex-wrap: wrap;
		}
		.welcome-panel-content{
			display: none;
		}
		.welcome-panel-content-2{
			min-height: 400px;
    		display: flex;
        	justify-content: space-between;
			flex-direction: initial;
    		flex-wrap: wrap;
		}
	</style>
	<?php

	if(isset($_GET['tab']) && ($_GET['tab'] == "customer_emails" || $_GET['tab'] == "admin_emails")){
		?>
			<style type="text/css">
				.settings_div_inner > p,.settings_div_inner > .form-table{
					display: none;
				}
				.settings_div_inner > h2{
					cursor: pointer;
					background: #ddd;
					padding: 10px;
				}
			</style>
		<?php
	}
}
add_action( 'admin_head', 'my_custom_admin_head' );



// First, this will disable support for comments and trackbacks in post types
function disable_comments_post_types_support() {
	/*if(isset($_GET['a'])){
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
		      if(post_type_supports($post_type, 'comments')) {
		         remove_post_type_support($post_type, 'comments');
		         remove_post_type_support($post_type, 'trackbacks');
		      }
		}	
	}*/
		
}
# https://keithgreer.uk/wordpress-code-completely-disable-comments-using-functions-php

add_action('admin_init', 'disable_comments_post_types_support');

// Then close any comments open comments on the front-end just in case
function disable_comments_status() {
   return false;
}
add_filter('comments_open', 'disable_comments_status', 20, 2);
add_filter('pings_open', 'disable_comments_status', 20, 2);

// Finally, hide any existing comments that are on the site. 
function disable_comments_hide_existing_comments($comments) {
   $comments = array();
   return $comments;
}
add_filter('comments_array', 'disable_comments_hide_existing_comments', 10, 2);

/* Qadisha - QD - 20221106 Remove annoyng error from error_log */
if( isset($mphb_check_in_date) ){

$booking_ids = $wpdb->get_results ("
                SELECT post_id 
                FROM  $wpdb->postmeta
                LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id)
                    WHERE `meta_key` = 'mphb_check_in_date'
                    AND `meta_value` = '$mphb_check_in_date' AND post_status IN ('confirmed','confirmed-archived','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')
            ");

}

?>
<?php
	/*register_activation_hook( __FILE__, 'my_activation_cron' );
	  
	function my_activation_cron() {
	    if (! wp_next_scheduled ( 'my_hourly_event' )) {
	        wp_schedule_event( time(), 'hourly', 'my_hourly_event' );
	    }
	}
	add_action( 'my_hourly_event', 'do_this_hourly', 10 );
	function do_this_hourly() {
		expire_pending_booking();
	}*/

	/*function mmmmmmmmmm(){
		if(isset($_GET['aaaaa'])){
			expire_pending_booking();
		}
			
	}
	add_action( 'init', 'mmmmmmmmmm' );*/

	function myprefix_custom_cron_schedule( $schedules ) {
	    $schedules['every_secound'] = array(
	        'interval' => 5, // Every 6 hours
	        'display'  => __( 'Every 6 hours' ),
	    );
	    return $schedules;
	}
	add_filter( 'cron_schedules', 'myprefix_custom_cron_schedule' );
	
	function myprefix_cron_function() {
		global $wpdb;
		$booking_ids = $wpdb->get_results ("
            SELECT ID 
            FROM ".$wpdb->prefix."posts WHERE `post_date` <= '".date("Y-m-d H:i:s",strtotime("-48 Hours"))."'
                AND post_status IN ('pending')
        ",ARRAY_A);
        foreach ($booking_ids as $key => $value) {
			  $my_post = array(
			      'ID'           => $value['ID'],
			      'post_status'   => 'expired-reservation',
			  );
			  wp_update_post( $my_post );
        }
		//file_put_contents("cron_log.txt", json_encode($booking_ids), FILE_APPEND);
	}
	add_action( 'myprefix_cron_hook_6', 'myprefix_cron_function' );
	if ( ! wp_next_scheduled( 'myprefix_cron_hook_6' ) ) {
    	wp_schedule_event( time(), 'hourly', 'myprefix_cron_hook_6' );
	}

	///Hook into that action that'll fire every six hours

	
	
	add_action('init', function() {
		 /*if(isset($_GET['aaaaaaaaaa'])){
			global $wpdb;
			$booking_ids = $wpdb->get_results ("
	            SELECT ID 
	            FROM ".$wpdb->prefix."posts WHERE post_status IN ('pending')
	        ",ARRAY_A);
	        //echo "<pre>"; print_r($booking_ids); echo "</pre>";die; 
	        foreach ($booking_ids as $key => $value) {
				 
	        }	
		}*/
			
	});
	
	/*

	add_action('init', function() {
	    add_action( 'my_hourly_event', 'do_this_hourly' );
	    if (! wp_next_scheduled ( 'my_hourly_event' )) {
	    	 echo "<pre>"; print_r(111); echo "</pre>";die; 
	        wp_schedule_event( time(), 'hourly', 'my_hourly_event' );
	    }
	});
	 
	function do_this_hourly() {
	    expire_pending_booking();
	}*/
?>
<?php

add_action("init","add_post_type_food_beverage");
function add_post_type_food_beverage(){
	$labels = array(
		'name'					 => __( 'Food & Beverage', 'motopress-hotel-booking' ),
		'singular_name'			 => __( 'Food & Beverage', 'motopress-hotel-booking' ),
		'add_new'				 => _x( 'Add New', 'Add New Food & Beverage', 'motopress-hotel-booking' ),
		'add_new_item'			 => __( 'Add New Food & Beverage', 'motopress-hotel-booking' ),
		'edit_item'				 => __( 'Edit Food & Beverage', 'motopress-hotel-booking' ),
		'new_item'				 => __( 'New Food & Beverage', 'motopress-hotel-booking' ),
		'view_item'				 => __( 'View Food & Beverage', 'motopress-hotel-booking' ),
		'search_items'			 => __( 'Search Food & Beverage', 'motopress-hotel-booking' ),
		'not_found'				 => __( 'No Food & Beverage found', 'motopress-hotel-booking' ),
		'not_found_in_trash'	 => __( 'No Food & Beverage found in Trash', 'motopress-hotel-booking' ),
		'all_items'				 => __( 'Food & Beverage', 'motopress-hotel-booking' ),
		'insert_into_item'		 => __( 'Insert into Food & Beverage description', 'motopress-hotel-booking' ),
		'uploaded_to_this_item'	 => __( 'Uploaded to this Food & Beverage', 'motopress-hotel-booking' )
	);

	$args = array(
		'labels'				 => $labels,
		'public'				 => true,
		'publicly_queryable'	 => true,
		'show_ui'				 => true,
		'capability_type'		 => 'post',
		//'has_archive'			 => true,
		'hierarchical'			 => false,
		'show_in_menu'			 => MPHB()->postTypes()->roomType()->getMenuSlug(),
		'supports'				 => array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
		//'register_meta_box_cb'	 => array( 'registerMetaBoxes' ),
		'rewrite'				 => array(
			//translators: do not translate
			'slug'		 => _x( 'food_beverage', 'slug', 'motopress-hotel-booking' ),
			'with_front' => false,
			'feeds'		 => true
		),
		'query_var'				 => true,
	    'show_in_rest'           => true
	);
	register_post_type( "mphb_room_food", $args );
	add_filter( 'manage_mphb_room_food_posts_columns', 'set_custom_edit_mphb_room_food_columns' );
	function set_custom_edit_mphb_room_food_columns($columns) {
	    $columns['quantity'] = __( 'In Stock', 'your_text_domain' );
	    $columns['sold'] = __( 'Sold', 'your_text_domain' );
	    return $columns;
	}
	add_action( 'manage_mphb_room_food_posts_custom_column' , 'custom_mphb_room_food_column', 10, 2 );
	function custom_mphb_room_food_column( $column, $post_id ) {
    	switch ( $column ) {
	        case 'quantity' :
	            echo get_post_meta( $post_id , 'stock' , true ); 
	        break;
	        case 'sold' :
				global $wpdb;
				$total_array = array();
				$total = 0;
				$result = $wpdb->get_results("SELECT ID,{$wpdb->prefix}posts.post_status FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}postmeta ON ({$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID) WHERE {$wpdb->prefix}posts.post_type='mphb_booking' AND meta_key = 'products' AND  (meta_value LIKE '%".sprintf(':"%s";', $post_id)."%' OR meta_value LIKE '%".sprintf(':%s;', $post_id)."%') AND {$wpdb->prefix}posts.post_status IN ('confirmed','confirmed-archived','paid_not_refundable','paid_refundable','last_minute','pending_late_charge','cancelled')");
				foreach ($result as $key => $value) {
					$products_qty = get_post_meta($value->ID,"products_qty",true);
					if($products_qty){
						$sum = array_sum($products_qty);
						if($value->post_status != "cancelled"){
							$total += $sum;
						}
						if(!isset($total_array[$value->post_status])) $total_array[$value->post_status] = 0;
						$total_array[$value->post_status] += $sum;
					}
				}
	            echo '<a href="'.admin_url('?page=mphb_product_reports&product_id='.$post_id).'">Total - '.$total.'</a><br/>';
	            foreach ($total_array as $key => $value) {
	            	echo '<a href="'.admin_url('?page=mphb_product_reports&product_id='.$post_id).'&post_status='.$key.'">'.esc_html( mphb_get_status_label( $key ) ).' - '.$value.'</a><br/>';
	            }
	        break;

	    }
	}
	add_submenu_page( 
        null,
        'Product Report',
        'Product Report',
        'manage_options',
        'mphb_product_reports',
        'mphb_product_reports_callback',
    );
    function mphb_product_reports_callback(){
    	$products = array();
    	$product_id = 0;
    	if(!empty($_GET['product_id'])){
    		$product_id = $_GET['product_id'];
			global $wpdb;

			//$paged = isset($_GET['paged']) ? ($_GET['paged']-1)*20 : 0;

    		/*$result = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}postmeta ON ({$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID) WHERE {$wpdb->prefix}posts.post_type='mphb_booking' AND meta_key = 'products' AND  meta_value LIKE '%".sprintf(':"%s";', $_GET['product_id'])."%' AND {$wpdb->prefix}posts.post_status IN ('confirmed','confirmed-archived','paid_not_refundable','paid_refundable','last_minute','pending_late_charge') LIMIT ".$paged.",20 ",ARRAY_A);*/

    		$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;

            //$pagi = get_option("booking_dashboard_pagi");
            $pagi = 20;
            $post_status = array('confirmed','confirmed-archived','paid_not_refundable','paid_refundable','last_minute','pending_late_charge');

            if(!empty($_GET['post_status'])){
            	$post_status = $_GET['post_status'];
            }

	        $attr = array(
	            'posts_per_page' => $pagi,
	            'paged' => $paged, 
	            'post_type' => 'mphb_booking',
	            'post_status' => $post_status,
	            'fields' => 'ids',
	            'meta_query' => array('relation' => 'OR')
	        );

            $attr['meta_query'][] = array(
                'key' => 'products',
                //'value' => "%".sprintf(':"%s";', $_GET['product_id'])."%",
                'value' => sprintf(':"%s";', $_GET['product_id']),
                'compare' => 'LIKE',
            );
            $attr['meta_query'][] = array(
                'key' => 'products',
                //'value' => "%".sprintf(':"%s";', $_GET['product_id'])."%",
                'value' => sprintf(':%s;', $_GET['product_id']),
                'compare' => 'LIKE',
            );
            $query = new WP_Query($attr);
			$ids = $query->posts;
    	}
    	?>
    	<table class="widefat" style="margin-top: 20px;">
            <thead>
	            <tr>
	                <th><input type="checkbox" name="checkall" onclick="if(jQuery(this).prop('checked')){jQuery('.check_in_ids').prop('checked',true);}else{jQuery('.check_in_ids').prop('checked',false);}"></th>
	                <th>Booking Id</th>
	                <th>Status</th>
	                <th>Full Name</th>
	                <th>Phone</th>
	                <th>Guests</th>
	                <th>Price</th>
	                <th>Arrival time</th>
	                <th>Lunch time</th>
	                <th>Services</th>
	                <th>Quantity</th>
	                <th width="120">Status</th>
	            </tr>
            </thead>
            <tbody>
		        <?php
		        if($ids){
		        	$bookings = MPHB()->getBookingRepository()->findAll(array('post__in' => $ids));
		        	?>

			            <?php foreach ($bookings as $booking) {
			                $id = $booking->getId();
			                $customer = $booking->getCustomer();


					        $reservedRooms = $booking->getReservedRooms();
					        $guest = "";
					        if (!empty($reservedRooms) && !$booking->isImported()) {
					            $adultsTotal = 0;
					            $childrenTotal = 0;
					            foreach ($reservedRooms as $reservedRoom) {
					                $adultsTotal += $reservedRoom->getAdults();
					                $childrenTotal += $reservedRoom->getChildren();
					            }

					            $guest .= 'Adults: ';
					            $guest .= "<span class='countAdult'>".$adultsTotal."</span>";
					            if ($childrenTotal > 0) {
					                $guest .= '<br/>';
					                $guest .= 'Children: ';
					                $guest .= $childrenTotal;
					            }
					        }

			                
			                $metas = get_post_meta($id);
			                $mphb_place = get_post_meta($id,"mphb_place",1);
			                $places = array();
			                if($mphb_place){
				                foreach ($mphb_place as $key => $value) {
				                    foreach ($value as $key => $value) {
				                        $places[] = $value;
				                    }
				                }
			                }
			                 //echo "<pre>"; print_r($mphb_place); echo "</pre>";die; 
			                $beach_arrival_time = $metas['beach_arrival_time'][0];
			                $lunch_time = $metas['lunch_time'][0];
			                

			                $mphb_table_id = get_post_meta($id, 'arf_cp_table_id', true);

			                $table_selected_ids = [];
			                $tables = [];

			                $ids = get_post_meta($id, 'arf_cp_table_id', true);
			                if(is_array($ids)){
			                    $table_selected_ids = $ids;
			                }else{
			                    $table_selected_ids[] = $ids;
			                }

			                if($table_selected_ids) {
			                    $args = array(
			                        'post_type' => 'arf_pt_table',
			                        'posts_per_page' => -1,
			                        'post_status' => 'publish',
			                        'orderby' => 'post_title',
			                        'order' => 'ASC'
			                    );
			                    $args['post__in'] = $table_selected_ids;
			                    $arf_pt_tables = get_posts($args);
			                    foreach ($arf_pt_tables as $key => $value) {
			                        $tables[] = $value->post_title;
			                    }
			                }
			                $qr_code_status = get_post_meta($id, 'arf_qr_code_status', true);
			                
			                //$price = $booking->getTotalPrice();
			                $price_breakdown = get_post_meta( $id, '_mphb_booking_price_breakdown', true); 
			                $price = 0;
					        if($price_breakdown){
								$ddd = json_decode(strip_tags($price_breakdown),true);
								//echo "<pre>";print_r($ddd);die;
								if(isset($ddd['rooms'])){
									foreach ($ddd['rooms'] as $kk => $value) {
										$adults += $value['room']['adults']; 
				            			$child += $value['room']['children']; 
										if(isset($value['services']['list'])){
											foreach ($value['services']['list'] as $key => $vv) {
												$service_arr[] = $vv['title']." (".$vv['details'].")";
												$sub_total = $vv['details'];
											}	
										}
										if(isset($value['services']['total']) && $value['services']['total']){
										    $price += $value['services']['total'];
										}
										
									}
								}
					        }
					        $status = $booking->getStatus();
			                ?>
			                <tr>
			                	<th><input type="checkbox" class="check_in_ids" name="check_in_ids[]" value="<?php echo $id ?>"></th>
			                    <td><a href="<?php echo admin_url("admin.php?page=mphb_edit_booking&booking_id=".$id) ?>" target="_blank"><?php echo $id ?></a></td>
			                    <td><?php echo esc_html( mphb_get_status_label( $status ) ); ?></td>
			                    <!-- <td><a href="#" class="show_pop" data-id="<?php echo $id ?>"><?php echo $id ?></a></td> -->

			                    <td class="edit_td td_data" style="text-align: left;">
			                    	<span class="data_show"><?php echo $customer->getName() ?></span>
                                    <input type="text" data-field="name" data-id="<?php echo $id ?>" class="form-control data_input" value="<?php echo $customer->getName() ?>" style="display: none;">
			                    </td>
			                    <td class="edit_td td_data">
			                    	<span class="data_show"><?php echo $customer->getPhone() ?></span>
                                    <input type="text" data-field="phone" data-id="<?php echo $id ?>" class="form-control data_input" value="<?php echo $customer->getPhone() ?>" style="display: none;">
			                    	
			                    </td>
			                    <td><?php echo $guest ?></td>
			                    <td><?php Views\BookingView::renderTotalPriceHTML( $booking ); ?></td>

			                    <td><?php echo $beach_arrival_time ?></td>
			                    <td><?php echo get_lunch_text($lunch_time) ?></td>
			                    <td><?php 

							        $reservedRooms = $booking->getReservedRooms();
							        foreach ($reservedRooms as $reservedRoom) {
							            $reservedServices = $reservedRoom->getReservedServices();
							            $placeholder = ' &#8212;';
							            if (!empty($reservedServices)) {
							                echo '<ol>';
							                foreach ($reservedServices as $reservedService) {
							                    echo '<li>';
							                    echo '<a target="_blank" href="' . esc_url(get_edit_post_link($booking->getId())) . '">' . esc_html($reservedService->getTitle()) . '</a>';
							                    if ($reservedService->isPayPerAdult()) {
							                        echo ' <em>' . sprintf(_n('x %d guest', 'x %d guests', $reservedService->getAdults(), 'motopress-hotel-booking'), $reservedService->getAdults()) . '</em>';
							                    }
							                    if ($reservedService->isFlexiblePay()) {
							                        echo ' <em>' . sprintf(_n('x %d time', 'x %d times', $reservedService->getQuantity(), 'motopress-hotel-booking'), $reservedService->getQuantity()) . '</em>';
							                    }
							                    echo '</li>';
							                }
							                echo '</ol>';
							            } else {
							                echo $placeholder;
							            }
							        }
			                     ?></td>
			                    <td>
			                    	<?php 
			                    	$products_qty = get_post_meta($id,"products_qty",1);
			                    	$qty = 0;
									if($products_qty){
										if(isset($products_qty[$product_id])){
											$qty = $products_qty[$product_id];
										}
									}
									echo $qty;
			                    	?>
			                    </td>
			                    <td>
			                    	<?php if (empty($qr_code_status)) { ?>
		                    			NOT CHECKED IN
		                    		<?php }else{ ?>
		                    			CHECKED IN
		                    		<?php } ?>
			                    </td>
			                </tr>
			            <?php } ?>
		        	<?php
		        }else{ ?>
		        	<tr>
		        		<td colspan="8" align="center">No Record Found</td>
		        	</tr>
	        	<?php } ?>
            </tbody>
        </table>
        <div class="pagination">
		    <?php 
		        echo paginate_links( array(
		            'base'         => str_replace( 999999999, '%#%', html_entity_decode( get_pagenum_link( 999999999 ) ) ),
		            'total'        => $query->max_num_pages,
		            'current'      => (isset($_GET['paged']) ? $_GET['paged'] : 1),
		            'format'       => '&paged=%#%',
		            'show_all'     => false,
		            'type'         => 'list',
		            'end_size'     => 2,
		            'mid_size'     => 1,
		            'prev_next'    => true,
		            'prev_text'    => sprintf( '<i></i> %1$s', __( '<<', 'text-domain' ) ),
		            'next_text'    => sprintf( '%1$s <i></i>', __( '>>', 'text-domain' ) ),
		            'add_args'     => false,
		        ) );
		    ?>
		</div>
	<?php
    }
	/*if(isset($_GET['aaa'])){
		add_meta_box(
			'mphb_price',                 // Unique ID
			'Data',      // Box title
			'wporg_custom_box_html',  // Content callback, must be of type callable
			"mphb_room_food"                            // Post type
		);
		$priceGroup			 = new Groups\MetaBoxGroup( 'mphb_price', __( 'Price', 'motopress-hotel-booking' ), "mphb_room_food" );
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
	}*/
		

	// if(isset($_GET['a'])){
	// 	echo "<pre>"; print_r($args); echo "</pre>";die; 
	// }
}
add_action('admin_init', 'fb_add_meta_boxes', 2);

function fb_add_meta_boxes() {
add_meta_box( 'fb-group', 'Availability', 'fb_repeatable_meta_box_display', 'mphb_room_food', 'normal', 'default');
}

function fb_repeatable_meta_box_display() {
    global $post;
    $gpminvoice_group = get_post_meta($post->ID, 'availability_range', true);
     wp_nonce_field( 'fb_repeatable_meta_box_nonce', 'fb_repeatable_meta_box_nonce' );
     $lang ="en";
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		  $lang = ICL_LANGUAGE_CODE;
		}
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text-date' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text-date screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			<?php if($lang == "en"){ ?>
		        row.find(".new_datepicker_input").datepicker({
				  dateFormat: "mm-dd-yy"
				});
			<?php }else{ ?>
		        row.find(".new_datepicker_input").datepicker({
				  dateFormat: "dd-mm-yy"
				});
			<?php } ?>
			row.find(".new_datepicker").removeClass("new_datepicker_input");
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
        <?php  
		if($lang == "en"){ ?>
	        $(".datepicker_input").datepicker({
			  dateFormat: "mm-dd-yy"
			});
		<?php }else{ ?>
	        $(".datepicker_input").datepicker({
			  dateFormat: "dd-mm-yy"
			});
		<?php } ?>
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
        <input type="text"  placeholder="Start Date" class="datepicker_input" name="startdate[]" value="<?php if($field['startdate'] != '') echo esc_attr( $field['startdate'] ); ?>" /></td> 
      <td width="25%">End Date
        <input type="text"  placeholder="End Date" name="enddate[]" class="datepicker_input" value="<?php if($field['enddate'] != '') echo esc_attr( $field['enddate'] ); ?>" /></td> 
      <td width="25%"><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> Start Date
        <input type="text" placeholder="Start Date" class="datepicker_input" title="Start Date" name="startdate[]" /></td>
      <td> End Date
        <input type="text" placeholder="End Date" title="End Date" class="datepicker_input" name="enddate[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text-date screen-reader-text">
      <td> Start Date
        <input type="text" placeholder="Start Date" class="new_datepicker_input new_datepicker" title="Start Date" name="startdate[]" /></td>
      <td> End Date
        <input type="text" placeholder="End Date" class="new_datepicker_input new_datepicker"  title="End Date" name="enddate[]" /></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row" class="button" href="#">Add another</a></p>
 <?php
}
add_action('save_post', 'fb_custom_repeatable_meta_box_save');
function fb_custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['fb_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['fb_repeatable_meta_box_nonce'], 'fb_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $old = get_post_meta($post_id, 'availability_range', true);
    $new = array();
    $startdate = $_POST['startdate'];
    $endDate = $_POST['enddate'];
     $count = count( $startdate );
     for ( $i = 0; $i < $count; $i++ ) {
        if ( $startdate[$i] != '' ) :
            $new[$i]['startdate'] = stripslashes( strip_tags( $startdate[$i] ) );
             $new[$i]['enddate'] = stripslashes( $endDate[$i] ); // and however you want to sanitize
        endif;
    }
    if ( !empty( $new ) && $new != $old )
        update_post_meta( $post_id, 'availability_range', $new );
    elseif ( empty($new) && $old )
        delete_post_meta( $post_id, 'availability_range', $old );
}

add_action('admin_init', 'fb_packages_add_meta_boxes', 2);

function fb_packages_add_meta_boxes() {
add_meta_box( 'fb-packages-group', 'Packages', 'fb_packages_repeatable_meta_box_display', 'mphb_room_food', 'normal', 'default');
}

function fb_packages_repeatable_meta_box_display() {
    global $post;
    $packages = get_post_meta($post->ID, 'packages', true);
    wp_nonce_field( 'fb_packages_repeatable_meta_box_nonce', 'fb_packages_repeatable_meta_box_nonce' );
    ?>
    <div class="categorydiv" id="packages" >
      <div class="tabs-panel">
        <ul class="categorychecklist form-no-clear">
          <?php foreach ( MPHB()->getServiceRepository()->findAll() as $service ) { ?>
            <li class="popular-category">
              <?php $labelHtml = ( $service->getTitle() . ' (' . $service->getPriceWithConditions() . ')'); ?>
              <label class="selectit">
                <input value="<?php echo esc_attr( $service->getId() ); ?>"
                     type="checkbox"
                     name="packages[]"
                     <?php echo $packages && in_array( $service->getId(), $packages ) ? 'checked="checked"' : ''; ?>
                     style="margin-top: 0;"
                     />
                     <?php echo $labelHtml; ?>
              </label>
            </li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <?php
    
      $servicePostTypeObj = get_post_type_object( MPHB()->postTypes()->service()->getPostType() );
      ?>
      <a  href="<?php echo esc_attr( MPHB()->postTypes()->service()->getEditPage()->getUrl( array(), true ) ); ?>"
        target="_blank"
        class="taxonomy-add-new"
        >+ <?php echo $servicePostTypeObj->labels->add_new_item; ?></a>
      <?php
    
}
add_action('save_post', 'fb_packages_custom_repeatable_meta_box_save');
function fb_packages_custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['fb_packages_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['fb_packages_repeatable_meta_box_nonce'], 'fb_packages_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $old = get_post_meta( $post_id, 'packages', true );
    foreach ($old as $key => $value) {
    	$features_image_type = get_post_meta($value, 'features_image_type', true);
    	if($features_image_type && in_array($post_id, $features_image_type)){
    		$key = array_search($post_id, $features_image_type);
    		unset($features_image_type[$key]);
    		update_post_meta($value,"features_image_type",$features_image_type);
    	}
    }

    $new = isset($_POST['packages']) ? $_POST['packages'] : array();
    update_post_meta( $post_id, 'packages', $new );

    foreach ($new as $key => $value) {
    	$features_image_type = get_post_meta($value, 'features_image_type', true);
    	if($features_image_type && !in_array($post_id, $features_image_type)){
    		$features_image_type[] = $post_id;
    		update_post_meta($value,"features_image_type",$features_image_type);
    	}
    }


}


// First register resources with init 
function add_slider_assets() {

	wp_enqueue_script("slider-assets-script", get_template_directory_uri()."/js/owl.carousel.min.js", '', '', true);
	wp_enqueue_style("slider-assets-style", get_template_directory_uri()."/js/owl.carousel.min.css");
}
add_action( 'init', 'add_slider_assets' );

add_action('admin_init', 'service_packages_add_meta_boxes', 2);

function service_packages_add_meta_boxes() {
	add_meta_box( 'service-packages-group', 'Products', 'service_packages_repeatable_meta_box_display', 'mphb_room_service', 'normal', 'default');
}

function service_packages_repeatable_meta_box_display() {
		global $wpdb;
		$posts = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}postmeta ON ({$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID) WHERE meta_key = 'packages' AND  meta_value LIKE '%".sprintf(':"%s";', $_GET['post'])."%' AND {$wpdb->prefix}posts.post_status NOT IN ('trash')", ARRAY_A);

		$default = get_post_meta($_GET['post'], 'default', true);

		$service_price = get_post_meta($_GET['post'], 'service_price', true);
		$min_pax = get_post_meta($_GET['post'], 'min_pax', true);
		$max_pax = get_post_meta($_GET['post'], 'max_pax', true);
		// The Loop
		if ( $posts ) {
			?>
			<table class="widefat">
				<thead>
					<tr>
						<td>Title</td>
						<td>Quantity</td>
						<td>Default</td>
						<td>Price</td>
						<td>Min Pax</td>
						<td>Max Pax</td>
					</tr>
				</thead>
			<?php foreach ($posts as $key => $value) { $price = get_post_meta( $value['ID'] , 'price' , true ); ?>
				<tr>
					<td><?php echo $value['post_title'] ?></td>
					<td><?php echo get_post_meta( $value['ID'] , 'stock' , true ); ?></td>
					<td>
                		<input type="checkbox" name="default[]" value="<?= $value['ID'] ?>" <?= $default && in_array($value['ID'], $default) ? "checked='checked'" : "" ?> />
					</td>
					<td>
                		<input type="text" name="service_price[<?= $value['ID'] ?>]" value="<?= isset($service_price[$value['ID']]) ? $service_price[$value['ID']] : "" ?>" />
					</td>
					<td>
                		<input type="text" name="min_pax[<?= $value['ID'] ?>]" value="<?= isset($min_pax[$value['ID']]) ? $min_pax[$value['ID']] : "" ?>" />
					</td>
					<td>
                		<input type="text" name="max_pax[<?= $value['ID'] ?>]" value="<?= isset($max_pax[$value['ID']]) ? $max_pax[$value['ID']] : "" ?>" />
					</td>
				</tr>
			<?php } ?>
			</table>
			<?php
		} else {
			echo  " NO Products Found";
		}
}
?>
<?php
add_action('admin_init', 'service_packages_feture_image_add_meta_boxes', 2);

function service_packages_feture_image_add_meta_boxes() {
	add_meta_box( 'service-feture_image-group', 'Feture Image', 'service_packages_feture_image_repeatable_meta_box_display', 'mphb_room_service', 'normal', 'default');
}

function service_packages_feture_image_repeatable_meta_box_display() {
	wp_nonce_field( 'service_packages_feture_image_repeatable_meta_box_nonce', 'service_packages_feture_image_repeatable_meta_box_nonce' );
	$features_image_type = get_post_meta($_GET['post'], 'features_image_type', true);
	?>
    <div class="categorydiv" id="packages" >
      <div class="tabs-panel">
        <ul class="categorychecklist form-no-clear">
        	<li>
              <label class="selectit">
                <input value="service" type="checkbox" name="features_image_type[]" <?php echo !$features_image_type || (is_array($features_image_type) && in_array("service", $features_image_type)) ? 'checked="checked"' : ''; ?> style="margin-top: 0;"/> From Service</label>
        	</li>
			<?php
				global $wpdb;
				$posts = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}postmeta ON ({$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID) WHERE meta_key = 'packages' AND  meta_value LIKE '%".sprintf(':"%s";', $_GET['post'])."%'  AND {$wpdb->prefix}posts.post_status NOT IN ('trash')", ARRAY_A);
				 
				// The Loop
				if ( $posts ) { ?>
					<?php foreach ($posts as $key => $value) { ?>
		        	<li>
		              <label class="selectit">
		                <input value="<?php echo $value['ID'] ?>"
		                     type="checkbox"
		                     name="features_image_type[]"
		                     <?php echo is_array($features_image_type) && in_array($value['ID'], $features_image_type) ? 'checked="checked"' : ''; ?>
		                     style="margin-top: 0;"
		                     />
		                     <?php echo $value['post_title']; ?>
		              </label>
		        	</li>
					<?php
					}
				}
				/* Restore original Post Data */
			?>
        </ul>
      </div>
    </div>
    
	<?php
}

add_action('save_post', 'service_packages_feture_image_repeatable_meta_box_save');
function service_packages_feture_image_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['service_packages_feture_image_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['service_packages_feture_image_repeatable_meta_box_nonce'], 'service_packages_feture_image_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    $new = isset($_POST['features_image_type']) ? $_POST['features_image_type'] : array();
    update_post_meta( $post_id, 'features_image_type', $new );

    $new = isset($_POST['service_price']) ? $_POST['service_price'] : array();
    update_post_meta( $post_id, 'service_price', $new );

    $new = isset($_POST['default']) ? $_POST['default'] : array();
    update_post_meta( $post_id, 'default', $new );


    $new = isset($_POST['min_pax']) ? $_POST['min_pax'] : array();
    update_post_meta( $post_id, 'min_pax', $new );


    $new = isset($_POST['max_pax']) ? $_POST['max_pax'] : array();
    update_post_meta( $post_id, 'max_pax', $new );


}
?>

<?php

add_shortcode( 'mphb_room_service_detail', 'mphb_room_service_detail_func' );
function mphb_room_service_detail_func($atts){
	$return = "";
	if(isset($atts['post_id'])){
		$title = get_the_title($atts['post_id']);
		$post_content = get_post_field('post_content', $atts['post_id']);
		$return .= '<div class="vce-google-fonts-heading vce-google-fonts-heading--align-left vce-google-fonts-heading--color-b-248-135-73--45--5C00FF--FF7200 vce-google-fonts-heading--font-family-Lato">';
			$return .= '<div class="vce-google-fonts-heading-wrapper">';
				$return .= '<div class="vce-google-fonts-heading--background vce" id="el-9e96ffe2" data-vce-do-apply="border background  padding margin el-9e96ffe2">';
					$return .= '<h3 class="vce-google-fonts-heading-inner" style="font-weight: 400;text-transform: uppercase;">'.$title;
					$return .= '</h3>';
				$return .= '</div>';
			$return .= '</div>';
		$return .= '</div>';
		$return .= '<div class="vce vce-separator-container vce-separator--align-left vce-separator--style-solid" id="el-a01920d2" data-vce-do-apply="margin el-a01920d2">';
			$return .= '<div class="vce-separator vce-separator--color-b-248-135-73 vce-separator--width-65 vce-separator--thickness-1" data-vce-do-apply="border padding background  el-a01920d2">';
			$return .= '</div>';
		$return .= '</div>';
		$return .= '<div class="vce-text-block">';
			$return .= '<div class="vce-text-block-wrapper vce">';
				$return .= apply_filters('the_content', $post_content);;
			$return .= '</div>';
		$return .= '</div>';
	}
	return $return;
}
?>
<?php

add_shortcode( 'mphb_room_service_image', 'mphb_room_service_image_func' );
function mphb_room_service_image_func($atts){
	$return = "";
	if(isset($atts['post_id'])){
		$features_image_type = get_post_meta($atts['post_id'], 'features_image_type', true);
		$featured_img_url = "";
		if($features_image_type){
			$featured_img_url = get_the_post_thumbnail_url($features_image_type,'full');
		}
		if(!$featured_img_url){
			$featured_img_url = get_the_post_thumbnail_url($atts['post_id'],'full');
		}

		if($featured_img_url){
			$return .= '<div class="vce-single-image-container custom_hw_100 vce-single-image--align-center">';
				$return .= '<div class="vce vce-single-image-wrapper" id="el-66a16121" data-vce-do-apply="all el-66a16121">';
					$return .= '<figure>';
						$return .= '<div class="vce-single-image-inner vce-single-image--absolute" style="padding-bottom: 125.031%; width: 819px;">';
							$return .= '<img loading="lazy" class="vce-single-image vcv-lozad" data-src="'.$featured_img_url.'" width="819" height="1024" src="'.$featured_img_url.'" data-img-src="'.$featured_img_url.'" alt="" title="single1-scaled-min" data-loaded="true">';
							$return .= '<noscript>';
								$return .= '<img loading="lazy" class="vce-single-image" src="'.$featured_img_url.'" width="819" height="1024" alt="" title="single1-scaled-min" />';
							$return .= '</noscript>';
						$return .= '</div>';
						$return .= '<figcaption hidden="">';
						$return .= '</figcaption>';
					$return .= '</figure>';
				$return .= '</div>';
			$return .= '</div>';
		}
	}
	return $return;
}
?>
<?php


add_shortcode( 'mphb_room_service_detail_full', 'mphb_room_service_detail_full_func' );
function mphb_room_service_detail_full_func($atts){
	$return = "";
	if(isset($atts['post_id'])){
		$title = get_the_title($atts['post_id']);
		$post_content = get_post_field('post_content', $atts['post_id']);
		$return .= "<div class='d-flex flex-wrap h-100'>";
		$return .= "<div class='col-6 mobile-12 mobilep-0'>";
		$return .= '<div class="vce-google-fonts-heading vce-google-fonts-heading--align-left vce-google-fonts-heading--color-b-248-135-73--45--5C00FF--FF7200 vce-google-fonts-heading--font-family-Lato">';
			$return .= '<div class="vce-google-fonts-heading-wrapper">';
				$return .= '<div class="vce-google-fonts-heading--background vce" id="el-9e96ffe2" data-vce-do-apply="border background  padding margin el-9e96ffe2">';
					$return .= '<h3 class="vce-google-fonts-heading-inner" style="font-weight: 400;text-transform: uppercase;">'.$title;
					$return .= '</h3>';
				$return .= '</div>';
			$return .= '</div>';
		$return .= '</div>';
		$return .= '<div class="vce vce-separator-container vce-separator--align-left vce-separator--style-solid" id="el-a01920d2" data-vce-do-apply="margin el-a01920d2">';
			$return .= '<div class="vce-separator vce-separator--color-b-248-135-73 vce-separator--width-65 vce-separator--thickness-1" data-vce-do-apply="border padding background  el-a01920d2">';
			$return .= '</div>';
		$return .= '</div>';
		$return .= '<div class="vce-text-block">';
			$return .= '<div class="vce-text-block-wrapper vce my-accommodation">';
				$return .= apply_filters('the_content', $post_content);;
			$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';

		$service_price = get_post_meta($atts['post_id'], 'service_price', true);
		$default = get_post_meta($atts['post_id'], 'default', true);
		$features_image_type = get_post_meta($atts['post_id'], 'features_image_type', true);
		$min_pax = get_post_meta($atts['post_id'], 'min_pax', true);
		$max_pax = get_post_meta($atts['post_id'], 'max_pax', true);
		$featured_img_url = array();
		$min = array();
		$max = array();
		foreach ($max_pax as $key => $value) {
			$max[$key] = $value;
		}
		foreach ($min_pax as $key => $value) {
			$min[$key] = $value;
		}
		if(!$features_image_type){
			//$features_image_type[] = "service";
			$featured_img_url["service"] = get_the_post_thumbnail_url($atts['post_id'],'full');
		}
		$defaults = array();
		$na_defaults = array();

		$defaults_sort_order = array();
		$na_defaults_sort_order = array();
		
		


		if($features_image_type){
			foreach ($features_image_type as $key => $value) {
				if($value == "service"){
					$url = get_the_post_thumbnail_url($atts['post_id'],'full');
					if($url){
						$featured_img_url["service"]  = $url;
					}
				}else{
                    if ( get_post_status ( $value ) == 'trash' ) {
                        continue;
                    }
                    $qty_stock = (int)get_post_meta($value,"stock",1);
                    if(!$qty_stock) continue;
					$url = get_the_post_thumbnail_url($value,'full');
					if($url){
						if($default && in_array($value, $default)){
							$defaults[$value] = array(
								"id" => $value,
								"url" => $url,
							);
							$defaults_sort_order[$value] = isset($service_price[$value]) ? $service_price[$value] : 0;
						}else{
							$na_defaults[$value] = array(
								"id" => $value,
								"url" => $url,
							);
							$na_defaults_sort_order[$value] = isset($service_price[$value]) ? $service_price[$value] : 0;
						}
					}
				}
			}
		}

		array_multisort($defaults_sort_order, SORT_DESC, $defaults);	
		array_multisort($na_defaults_sort_order, SORT_DESC, $na_defaults);	

		$featured_img_url = array_merge($featured_img_url,$defaults,$na_defaults);
		if($featured_img_url){
			$return .= "<div class='col-6 mobilep-0 mobile-12 h-100-owl'>";

			$return .= '<div class="my-slider-list owl-carousel owl-theme owl-slider-'.$atts['post_id'].'">';
				if(isset($featured_img_url["service"])){
					$return .= '<div  class="item main">';
							$return .= '<img src="'.$featured_img_url["service"].'">';
					$return .= '</div>';
					unset($featured_img_url["service"]);
				}
			$return .= '</div>';
			$default_selected = "";

			$adult = !empty($_GET['adult']) ? (int)$_GET['adult'] : 1;
			$return .= '<div style="display:none;" class="owl-content-'.$atts['post_id'].'">';
				foreach ($featured_img_url as $key => $value) {
					if((isset($min[$value['id']]) && $min[$value['id']] != '' && $min[$value['id']] != '0')){
						$bottal_attribute = get_post_meta($value['id'],"bottal_attribute",1);
						$return .= '<div  class="item "  data-bottal_attribute="'.$bottal_attribute.'" data-key="'.$value['id'].'" data-min="'.(isset($min[$value['id']]) ? $min[$value['id']] : 0).'" data-max="'.(isset($max[$value['id']]) ? $max[$value['id']] : 0).'">';
							$return .= '<img src="'.$value['url'].'">';
							if($value['id'] != "service"){

								$availability_range = get_post_meta($value['id'],"availability_range",1);

								$text = '<i class="icon icon-plus"></i> <span>Add</span>';
								$attrdata = '';
								if($default && in_array($value['id'], $default) && !$default_selected){
									if($availability_range){
                                        foreach ($availability_range as $kk => $vvv) {
                                            $startdate = $vvv['startdate'];
                                            $parts = explode('-',$startdate);
                                            $startdate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                             
                                            $enddate = $vvv['enddate'];
                                            $parts = explode('-',$enddate);
                                            $enddate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                            if(!empty($_GET['mphb_check_in_date']) && strtotime($startdate) <= strtotime($_GET['mphb_check_in_date']) && strtotime($enddate) >= strtotime($_GET['mphb_check_in_date']) && isset($min[$value['id']]) && $adult >= $min[$value['id']]){
                                                $default_selected = 1;
												$attrdata = " data-added='1' ";
												$text = '<i class="icon icon-check"></i> <span>Added</span>';
                                            }
                                        }
                                    }else{
										$default_selected = 1;
										$attrdata = " data-added='1' ";
										$text = '<i class="icon icon-check"></i> <span>Added</span>';
                                    }
								}
								$return .= '<a href="#" '.$attrdata.' data-id="'.$value['id'].'"  data-min="'.(isset($min[$value['id']]) ? $min[$value['id']] : 0).'" data-max="'.(isset($max[$value['id']]) ? $max[$value['id']] : 0).'" class="add_product">'.$text.'</a>';
							}
						$return .= '</div>';
					}
				}
			$return .= '</div>';
			foreach ($featured_img_url as $key => $value) {
				if((isset($min[$value['id']]) && $min[$value['id']] != '' && $min[$value['id']] != '0')){
					$availability_range = get_post_meta($value['id'],"availability_range",1);
					if($availability_range){
						$return .= '<div style="display:none" class="available_product_'.$value['id'].'">';
						$return .= json_encode($availability_range);
						$return .= '</div>';
					}
				}
			}
			$return .= '</div>';
		}
		$return .= '</div>';
	}
	return $return;
}


add_shortcode( 'mphb_service_avail_result', 'mphb_service_avail_result_func' );
function mphb_service_avail_result_func(){
	$service = array();
	if(!empty($_GET['mphb_check_in_date'])){
		$checkInDateFormatted			 = isset($_GET['mphb_check_in_date']) ? $_GET['mphb_check_in_date'] : date("Y-m-d");
		$date_str = strtotime($checkInDateFormatted);
		$blocked_all = "";
		
		/*$args = array(
			'post_type'=> 'mphb_season',
			'orderby'    => 'ID',
			'post_status' => 'publish',
			'order'    => 'DESC',
			'posts_per_page' => -1 // this will retrive all the post that is published 
		);
		$result = new WP_Query( $args );
		

		if ( $result-> have_posts() ) : 
			$blocked_all = 1;
			while ( $result->have_posts() ) : 
				$result->the_post();
				$mphb_start_date = get_post_meta($result->post->ID, 'mphb_start_date', true);
				$mphb_end_date = get_post_meta($result->post->ID, 'mphb_end_date', true);
				if(strtotime($mphb_start_date) <= $date_str && strtotime($mphb_end_date) >= $date_str){
					$blocked_all = 0;
				}
		 	endwhile; 
		endif; wp_reset_postdata(); */
		if(!$blocked_all){
			$dates = getBookingRules();
			if(in_array($checkInDateFormatted, $dates)){
				$blocked_all = 1;
			}
			/*$mphb_booking_rules_custom = get_option("mphb_booking_rules_custom");
			
			foreach ($mphb_booking_rules_custom as $key => $value) {
				if(strtotime($value['date_from']) >= $date_str && strtotime($value['date_to']) <= $date_str){
					$blocked_all = 1;
				}
			}*/
		}
		if($date_str < strtotime(date("Y-m-d"))){
			$blocked_all = 1;
		}
		if(!$blocked_all){
			$args = array(
				'post_type'=> 'mphb_room_type',
				'orderby'    => 'ID',
				'post_status' => 'publish',
				'order'    => 'DESC',
				'posts_per_page' => -1 // this will retrive all the post that is published 
			);
			$mphb_room_type = array();
			$result = new WP_Query( $args );
			if ( $result-> have_posts() ) : 
				while ( $result->have_posts() ) : 
					$result->the_post(); 
					
					$mphb_services = get_post_meta($result->post->ID, 'mphb_services', true);
					
					$mphb_room_type[] = array(
						"id" => $result->post->ID,
						"title" => get_the_title( $result->post->ID ),
						"mphb_services" => $mphb_services,
					);
                
			 	endwhile; 
			endif; wp_reset_postdata(); 
			foreach ($mphb_room_type as $key => $value) {
				if($value['mphb_services']){
					foreach ($value['mphb_services'] as $key => $value) {
						$dates = get_post_meta($value, 'mphb_block_dates', true);
						$blocked_dates = array();
	                    if($dates){
	                        foreach (explode(",", $dates) as $key => $vvv) {
	                            $blocked_dates[] = date("Y-m-d",strtotime($vvv));
	                        }
	                    }
	                    if(!in_array($checkInDateFormatted, $blocked_dates)){
							$service[] = array(
								"id" => $value,
								"title" => get_the_title( $value ),
								"sort_order" => get_post_meta($value,"sort_order")
							);
	                    }
					}
				}
			}

			$sort_order = array();

			foreach ($service as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $service);	
			
			/*$args = array(
				'post_type'=> 'mphb_room_service',
				'orderby'    => 'ID',
				'post_status' => 'publish',
				'order'    => 'DESC',
				'posts_per_page' => -1 // this will retrive all the post that is published 
			);
			$result = new WP_Query( $args );
			if ( $result-> have_posts() ) : 
				while ( $result->have_posts() ) : 
					$result->the_post(); 
					
					$dates = get_post_meta($result->post->ID, 'mphb_block_dates', true);
					$blocked_dates = array();
                    if($dates){
                        foreach (explode(",", $dates) as $key => $value) {
                            $blocked_dates[] = date("Y-m-d",strtotime($value));
                        }
                    }
                    if(!in_array($checkInDateFormatted, $blocked_dates)){
						$service[] = array(
							"id" => $result->post->ID,
							"title" => get_the_title( $result->post->ID ),
						);
                    }
			 	endwhile; 
			endif; wp_reset_postdata(); */

		}
	}
	ob_start();
	?>
	<style type="text/css">
		@media (max-width: 768px){
			.mobile-12{
				flex: 0 0 100% !important;
	 		    max-width: 100% !important;
			}
		}
	</style>
	<!-- <style type="text/css">
		.btn.btn-sm {
		    padding: 10px 15px;
		    text-decoration: none !important;
		    margin-right: 4px;
		    font-size: 15px;
		}
		.btn-info{
			background: #00bcd4 !important;
		    color: #fff !important;
		}
		.btn-info:hover{
		    text-decoration: none !important;
			opacity: .8;
		}
		.btn-primary{
			background: #2196f3 !important;
		    color: #fff !important;
		}
		.btn-primary:hover{
		    text-decoration: none !important;
			opacity: .8;
		}
		.table-white{
			background: #fff;
			color: #000000;
			border-color: #000000;
		}
		.table-white td,.table-white tr{
			border-color: #000000;
			color: #000000;
		}
		.my_model{
		    position: fixed;
		    top: 0;
		    left: 0;
		    width: 100%;
		    height: 100%;
		    background: #000000ab;
		    z-index: 1000;
		    overflow-y: scroll;
		}
		.my_model-content{
			position: absolute;
		    top: 15%;
		    right: 25%;
		    width: 50%;
		    background: #ffffff;
		    opacity: 1;
		    z-index: 1000000000;
		    padding: 10px;
		}
		button.close_model {
		    background: transparent;
		    color: #000000;
		    position: absolute;
		    right: 15px;
		    top: 15px;
		    padding: 0;
		    z-index: 100;
		}
	</style> -->
	<!-- <?php if(!empty($_GET['mphb_check_in_date'])){ ?>
		<table class="table table-bordered table-white">
			<tbody>
				<?php if(!empty($service)){ ?>
					<?php foreach ($service as $key => $value) { ?>
						<tr>
							<td><?= $value['title'] ?></td>
							<td align="right"><a class="btn btn-sm btn-info view_service_info" data-id="<?= $value['id'] ?>" href="#">Show Details</a><a class="btn btn-sm btn-primary" href="#">Book Now</a></td>
						</tr>
					<?php } ?>
				<?php }else{ ?>
					<tr>
						<td>Services Not Available</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<div class="my_model" style="display: none;">
			<div class="my_model-content">
				<button type="button" class="close_model" onclick="jQuery('.my_model').hide()">X</button>
				<div class="service_img">
					<img src="">
				</div>
				<div class="service_content"></div>
			</div>
		</div>
	<?php } ?> -->
	<?php if(!empty($_GET['mphb_check_in_date'])){ 
		$default_adult = !empty($_GET['adult']) ? $_GET['adult'] : 1;
		$default_childs = !empty($_GET['childs']) ? $_GET['childs'] : 0;
		?>
		<?php if(!empty($service)){ ?>
			<?php foreach ($service as $key => $value) { 
				$background_color = get_post_meta($value['id'],"background_color",1);
				$mphb_show_child = get_post_meta($value['id'],"mphb_show_child",1);

				if($default_childs && !$mphb_show_child){
					continue;
				}
				$mphb_show_child = $mphb_show_child ? 0 : 1;
				$title = get_the_title($value['id']);

				?>
				<div class="d-flex flex-wrap my_main_div">
					<div class="col-8  mobile-12 mobilep-0">
						<?= do_shortcode('[mphb_room_service_detail_full post_id='.$value['id'].']'); ?>
					</div>
					<div class="col-4 mobile-12" style="<?= $background_color ? "background: ".$background_color : ""; ?>">
						<?= do_shortcode('[arf_form_search service_id="'.$value['id'].'" child_hidden='.$mphb_show_child.' default_adult='.$default_adult.' default_childs='.$default_childs.' mphb_check_in_date="'.$_GET['mphb_check_in_date'].'" service_title="'.$title.'"]'); ?>
					</div>
				</div>
			<?php } ?>
		<?php }else{ ?>
			<table class="table table-bordered table-white">
				<tr>
					<td>Services Not Available</td>
				</tr>
			</table>
		<?php } ?>
	<?php } ?>
	<?php
	$content = ob_get_clean();
	return $content;
}
add_shortcode( 'mphb_service_avail', 'mphb_service_avail_func' );

function mphb_service_avail_func($attr){
	$checkInDateFormatted			 = isset($_GET['mphb_check_in_date']) ? $_GET['mphb_check_in_date'] : "";

	if(!$checkInDateFormatted){
		$dates = getBookingRules();
		$i = 0;
		if($dates){
			while($checkInDateFormatted == ""){
				$date = date("Y-m-d",strtotime("+".$i." days"));
				if(!in_array($date,$dates)){
					$checkInDateFormatted = $date;
					break;
				}
				$i++;
			}
		}else{
			$checkInDateFormatted = date("Y-m-d");
		}

	}
	
	//$checkInDateFormatted	 = \MPHB\Utils\DateUtils::convertDateFormat( $checkInDate, MPHB()->settings()->dateTime()->getDateTransferFormat(), MPHB()->settings()->dateTime()->getDateFormat() );
	
	 
		
	ob_start();
	?>
	<style type="text/css">
		.btn.btn-sm {
		    padding: 10px 15px;
		    text-decoration: none !important;
		    margin-right: 4px;
		    font-size: 15px;
		}
		.btn-info{
			background: #00bcd4 !important;
		    color: #fff !important;
		}
		.btn-info:hover{
		    text-decoration: none !important;
			opacity: .8;
		}
		.btn-primary{
			background: #2196f3 !important;
		    color: #fff !important;
		}
		.btn-primary:hover{
		    text-decoration: none !important;
			opacity: .8;
		}
		.table-white{
			background: #fff;
			color: #000000;
			border-color: #000000;
		}
		.table-white td,.table-white tr{
			border-color: #000000;
			color: #000000;
		}
		.my_model{
		    position: fixed;
		    top: 0;
		    left: 0;
		    width: 100%;
		    height: 100%;
		    background: #000000ab;
		    z-index: 1000;
		    overflow-y: scroll;
		}
		.my_model-content{
			position: absolute;
		    top: 15%;
		    right: 25%;
		    width: 50%;
		    background: #ffffff;
		    opacity: 1;
		    z-index: 1000000000;
		    padding: 10px;
		}
		button.close_model {
		    background: transparent;
		    color: #000000;
		    position: absolute;
		    right: 15px;
		    top: 15px;
		    padding: 0;
		    z-index: 100;
		}


		input.qtyminus, input.qtyplus {
		    top: 3px;
		    text-indent: -9999px;
		    box-shadow: none
		}

		@media (max-width: 767px) {
		    #contact_info h4, #reach_us h4 {
		        margin-bottom: 10px
		    }

		    #reach_us {
		        text-align: center
		    }

		    #reach_us ul li {
		        padding-left: 0
		    }

		    #reach_us ul li i {
		        display: none
		    }
		}

		.qty-buttons-2 {
		    position: relative;
		    width: 30%;
		    height: 38px;
		    display: inline-block;
		    margin-bottom: 10px;
		}

		input.qty-2 {
		    width: 100%;
		    text-align: center;
		    height: 43px;
		    border:0 !important;
		}

		input.qty-2:focus {
			outline: 0 !important
		}

		input.qtyminus-2, input.qtyplus-2 {
		    position: absolute;
		    width: 32px;
		    height: 38px;
		    border: 0;
		    outline: 0;
		    cursor: pointer;
		    -webkit-appearance: none;
		    border-radius: 0
		}

		input.qtyplus-2 {
		    background: url(https://booking.arienzobeachclub.com/wp-content/plugins/arienzo-reservation-form/assets/img/plus.svg) center center no-repeat #fff !important;
		    right: 10px;
		    z-index: 1;
		    top: 1px;
		}

		input.qtyminus-2 {
		    background: url(https://booking.arienzobeachclub.com/wp-content/plugins/arienzo-reservation-form/assets/img/minus.svg) center center no-repeat #fff !important;
		    left: 10px
		}
		@media (max-width: 500px) {

			.qty-buttons-2 {
			    width: 100%;
			}
			input.qty-2 {
			    width: 100%;
			}
		}
	</style>
	<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
	<form data-action="<?= isset($attr['action']) ? $attr['action'] : "" ?>"  data-action2="<?= isset($attr['action2']) ? $attr['action2'] : "" ?>" action="<?= isset($attr['action']) ? $attr['action'] : "" ?>" method="GET"  target="_blank">
		<p class="mphb-check-in-date-wrapper">
			<label for="<?php echo esc_attr( 'mphb_check_in_date' ); ?>">
				<?php _e( 'Check-in Date', 'motopress-hotel-booking' ); ?>
				<abbr title="<?php printf( _x( 'Formatted as %s', 'Date format tip', 'motopress-hotel-booking' ), MPHB()->settings()->dateTime()->getDateFormatJS() ); ?>">*</abbr>
			</label>
			<br />
			<input id="<?php echo esc_attr( 'mphb_check_in_date' ); ?>" type="text" class="mphb-datepick" name="mphb_check_in_date" value="<?php echo esc_attr( $checkInDateFormatted ); ?>" required="required" autocomplete="off" placeholder="<?php _e( 'Check-in Date', 'motopress-hotel-booking' ); ?>" />
		</p>
		<div class="qty-buttons-2">
		    <input type="button" value="+" class="qtyplus-2" name="adult">
		    <input type="number" name="adult" id="adult" min="1" value="" class="qty-2 form-control" placeholder="Adults..." readonly="">
		    <input type="button" value="-" class="qtyminus-2" name="adult">
		</div>

		<div class="qty-buttons-2">
		    <input type="button" value="+" class="qtyplus-2" name="childs">
		    <input type="number" name="childs" id="childs" value="" class="qty-2 form-control" placeholder="Child" readonly="">
		    <input type="button" value="-" class="qtyminus-2" name="childs">
		</div>

		<p class="mphb-reserve-btn-wrapper">
			<input class="mphb-reserve-btn button" type="submit" value="<?php _e( 'Check Availability', 'motopress-hotel-booking' ); ?>" />
			<span class="mphb-preloader mphb-hide"></span>
		</p>
	</form>
	<script src="<?= plugin_dir_url('').'arienzo-reservation-form/assets/js/common_scripts.min.js' ?>"></script>
	<?php
	$content = ob_get_clean();
	return $content;
}



add_shortcode( 'mphb_service_avail2', 'mphb_service_avail2_func' );

function mphb_service_avail2_func($attr){
	$checkInDateFormatted			 = isset($_GET['mphb_check_in_date']) ? $_GET['mphb_check_in_date'] : "";

	if(!$checkInDateFormatted){
		$dates = getBookingRules();
		$i = 0;
		if($dates){
			while($checkInDateFormatted == ""){
				$date = date("Y-m-d",strtotime("+".$i." days"));
				if(!in_array($date,$dates)){
					$checkInDateFormatted = $date;
					break;
				}
				$i++;
			}
		}else{
			$checkInDateFormatted = date("Y-m-d");
		}

	}
		
	ob_start();
	?>
	<style type="text/css">
    .btn.btn-sm {
        padding: 10px 15px;
        text-decoration: none !important;
        margin-right: 4px;
        font-size: 15px;
    }

    .btn-info {
        background: #00bcd4 !important;
        color: #fff !important;
    }

    .btn-info:hover {
        text-decoration: none !important;
        opacity: .8;
    }

    .btn-primary {
        background: #2196f3 !important;
        color: #fff !important;
    }

    .btn-primary:hover {
        text-decoration: none !important;
        opacity: .8;
    }

    .table-white {
        background: #fff;
        color: #000000;
        border-color: #000000;
    }

    .table-white td,.table-white tr {
        border-color: #000000;
        color: #000000;
    }

    .my_model {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #000000ab;
        z-index: 1000;
        overflow-y: scroll;
    }

    .my_model-content {
        position: absolute;
        top: 15%;
        right: 25%;
        width: 50%;
        background: #ffffff;
        opacity: 1;
        z-index: 1000000000;
        padding: 10px;
    }

    button.close_model {
        background: transparent;
        color: #000000;
        position: absolute;
        right: 15px;
        top: 15px;
        padding: 0;
        z-index: 100;
    }

    input.qtyminus, input.qtyplus {
        top: 3px;
        text-indent: -9999px;
        box-shadow: none
    }

    @media (max-width: 767px) {
        #contact_info h4, #reach_us h4 {
            margin-bottom: 10px
        }

        #reach_us {
            text-align: center
        }

        #reach_us ul li {
            padding-left: 0
        }

        #reach_us ul li i {
            display: none
        }
    }

    .qty-buttons-2 {
        position: relative;
        width: 60%;
        height: 38px;
        display: inline-block;
        margin-bottom: 0;
    }

    input.qty-2 {
        width: 100%;
        text-align: center;
        height: 43px;
        border: 0 !important;
        background: transparent;
        padding: 0;
        font-family: Arial;
        font-weight: 400;
    }

    input.qty-2:focus {
        outline: 0 !important
    }

    input.qtyminus-2, input.qtyplus-2 {
        position: absolute;
        width: 32px;
        height: 38px;
        border: 0;
        outline: 0;
        cursor: pointer;
        -webkit-appearance: none;
        border-radius: 0
    }

    input.qtyplus-2 {
        background: url(https://booking.arienzobeachclub.com/wp-content/plugins/arienzo-reservation-form/assets/img/plus.svg) center center no-repeat #fff !important;
        right: 5px;
        z-index: 1;
        top: 4px;
        border: 1px solid #ddd;
        border-radius: 50%;
        padding: 15px;
        width: 30px;
        height: 30px;
    }

    input.qtyminus-2 {
        background: url(https://booking.arienzobeachclub.com/wp-content/plugins/arienzo-reservation-form/assets/img/minus.svg) center center no-repeat #fff !important;
        left: 5px;
        border: 1px solid #ddd;
        border-radius: 50%;
        padding: 0;
        width: 30px;
        height: 30px;
        padding: 15px;
        top: 4px;
    }

    @media (max-width: 500px) {
        .qty-buttons-2 {
            width: 100%;
        }

        input.qty-2 {
            width: 100%;
        }
    }

    .booking-form-1 {
        justify-content: space-between;
        width: 80%;
        margin: auto;
        background: #ebebeb;
    }

    .booking-form-1 > div {
        width: 34%;
    }

    .date_viewer .month_selected {
        margin: 0;
    }

    .date_viewer .day_selected {
        margin: 0;
    }

    .booking-form-1 .mphb-reserve-btn {
        width: 100%;
        height: 100%;
        background: #2d8a90;
        border-radius: 0;
    }

    .wp-date-selctor {
    		cursor: pointer;
        padding-left: 20px;
        padding-right: 20px;
        border-right: 1px solid #ddd;
        width: 18% !important;
    }

    .wp-mr-1 {
        margin-right: 10px;
    }

    .wp-text-center {
        text-align: center;
    }

    .date_selected {
        font-size: 35px;
        font-weight: 700;
        margin-right: 7px;
    }

    .month_selected {
        font-size: 22px;
        font-weight: normal;
    }

    .day_selected {
        font-size: 12px;
        font-weight: normal;
        font-weight: normal;
    }
    .wp_adults_selector{
        padding-left: 20px;
        padding-right: 20px;
        border-right: 1px solid #ddd;
    }
		.wp_child_selector{
			padding-left: 20px;
      padding-right: 20px;
		}
		.wp_avail_btn{
			width: 20%;
		}
		.wp_text_ac span{
			font-weight: 400;
			color: #727272;
		}


		.booking-form-1 h6{
			font-weight: 500;
		    font-size: 0.75em;
		    margin: 0;
		}
		.booking-form-1 .date_selected{
			font-family: Arial;
			font-size: 37px;
		  font-weight: 500;
		}
		.booking-form-1 .month_selected{
			text-transform: uppercase;
		}
		.booking-form-1 .day_selected{
			text-transform: uppercase;
			font-weight: 400;
			color: #777;
			font-size: 0.65em;
		    margin-left: -4px;
		}
		.booking-form-1 .mphb-reserve-btn{
			text-transform: uppercase;
			font-weight: 400;
			letter-spacing: 1px
		}
		@media (max-width: 1024px){
					.wp-date-selctor,.wp_adults_selector {
						border-bottom: 1px solid #b5b2b2;
					}
			 .booking-form-1 {
			 	flex-wrap: wrap;
			 }
				.mmw-100{
					width: 50% !important;
				}
		}
		@media (max-width: 800px){

    	.qty-buttons-2 {
        width: 100%;
      }
			 .booking-form-1 {
			 	flex-wrap: wrap;
			 }
			.mmw-100{
				width: 100% !important;
			}
			.booking-form-1 img{
				width: 40px;
			}
			.m-justify-content-space-between{
				    justify-content: space-between;
				    border-bottom: 1px solid #b5b2b2;
    				margin-bottom: 10px;
			}
			.no-border-margin{
				border-bottom: 0;
    				margin-bottom: 0;	
			}
			.m-flex-wrap{
				width: 30%;
				flex-wrap: wrap;

		    margin-bottom: 7px;

			}.wp_text_ac{
				margin: 0;
				width: 100%;
				text-align: center;
				margin-bottom: 10px;
			}
			.mmr-25{
				    width: 30%;
		    justify-content: center;
		    margin: 0;
			}
		}

		@media (max-width: 500px){
			.mmr-25{
				    width: 60%;
		    justify-content: center;
		    margin: 0;
			}
			.m-flex-wrap{
				width: 60%;
			}
		}

		footer{
			padding-top: 0px
		}
	</style>
	<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
	<form data-action="<?= isset($attr['action']) ? $attr['action'] : "" ?>"  data-action2="<?= isset($attr['action2']) ? $attr['action2'] : "" ?>" action="<?= isset($attr['action']) ? $attr['action'] : "" ?>" method="GET"  target="_blank">
		<div class="d-flex booking-form-1">
			<div class="d-flex align-items-center wp-date-selctor mmw-100 m-justify-content-space-between">
					<div class="d-flex wp-mr-1">
						<img src="<?php echo get_template_directory_uri(); ?>/logos/calendar.png" width="30">
						<input style="    visibility: hidden;    width: 0;    padding: 0; height: 0;" id="<?php echo esc_attr( 'mphb_check_in_date' ); ?>" type="text" class="mphb-datepick" name="mphb_check_in_date" value="<?php echo esc_attr( $checkInDateFormatted ); ?>" required="required" autocomplete="off" placeholder="<?php _e( 'Check-in Date', 'motopress-hotel-booking' ); ?>" />					
					</div>
					<div class="align-content-center d-flex date_viewer align-items-center mmr-25">
						<div class="date_selected"><?php echo date("d") ?></div>
						<div class="wp-text-center">
							<h4  class="month_selected"><?php echo date("M") ?></h4>
							<h6  class="day_selected"><?php echo strtoupper(date("D"))  ?></h6>
						</div>
					</div>
			</div>
			
			<div class="d-flex align-items-center wp_adults_selector mmw-100 m-justify-content-space-between">
				<div class="d-flex wp-mr-1">
					<img src="<?php echo get_template_directory_uri(); ?>/logos/user.png" width="30">
				</div>
				<div class="d-flex align-items-center m-flex-wrap">
				<h6 class="wp-mr-1 wp_text_ac">Adults <span>(18+)</span></h6>
				<div class="qty-buttons-2 ">
				    <input type="button" value="+" class="qtyplus-2" name="adult">
				    <input type="number" name="adult" id="adult" min="1" value="1" class="qty-2 form-control" readonly="">
				    <input type="button" value="-" class="qtyminus-2" name="adult">
				</div>
				</div>
			</div>
		
			<div class="d-flex align-items-center wp_child_selector mmw-100 m-justify-content-space-between no-border-margin">
				<div class="d-flex wp-mr-1">
					<img src="<?php echo get_template_directory_uri(); ?>/logos/children.png" width="30">
				</div>
				<div class="d-flex align-items-center m-flex-wrap">
				<h6 class="wp-mr-1 wp_text_ac">Kids <span>(2-17)</span></h6>
				<div class="qty-buttons-2">
				    <input type="button" value="+" class="qtyplus-2" name="childs">
				    <input type="number" name="childs" id="childs"  value="0" class="qty-2 form-control" readonly="">
				    <input type="button" value="-" class="qtyminus-2" name="childs">
				</div>
				</div>
			</div>
	
			<div class="wp_avail_btn mmw-100">
					<input class="mphb-reserve-btn button" type="submit" value="<?php _e( 'Check Availability', 'motopress-hotel-booking' ); ?>" />
					<span class="mphb-preloader mphb-hide"></span>
			</div>
		</div>


	</form>
	<script src="<?= plugin_dir_url('').'arienzo-reservation-form/assets/js/common_scripts.min.js' ?>"></script>
	<?php
	$content = ob_get_clean();
	return $content;
}


function footer_script() {
?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(document).delegate(".my-accommodation .vce-classic-accordion-panel-heading","click",function(e){
			e.preventDefault();
			console.log($(this).parents(".vce-classic-accordion-panel").length)
			if($(this).parents(".vce-classic-accordion-panel").attr("data-vcv-active")  !== undefined){
				$(this).parents(".vce-classic-accordion-panel").removeAttr("data-vcv-active");
				$(this).parents(".vce-classic-accordion-panel").find(".vce-classic-accordion-panel-body").attr("hidden","hidden");
				$(this).parents(".vce-classic-accordion-panel").find(".vce-classic-accordion-panel-body").css("height","0px");
			}else{
				$(this).parents(".vce-classic-accordion-panel").attr("data-vcv-active",true);
				$(this).parents(".vce-classic-accordion-panel").find(".vce-classic-accordion-panel-body").removeAttr("hidden");
				$(this).parents(".vce-classic-accordion-panel").find(".vce-classic-accordion-panel-body").css("height","");
			}
		})
		if(jQuery(".my-slider-list").length){
			jQuery(".my-slider-list").owlCarousel({
			    loop:false,
			    singleItem: true,
			    pagination: true,
			    dots: true,
			    nav:true,
			    margin:5,
				navText: ["<i class='icon icon-arrow-left'></i>","<i class='icon icon-arrow-right'></i>"],
			    responsive:{
			        0:{
			            items:1
			        },
			        600:{
			            items:1
			        },
			        1000:{
			            items:1
			        }
			    }
			})
		}
		if(jQuery(".my-slider-list2").length){
			jQuery(".my-slider-list2").owlCarousel({
			    loop:false,
			    singleItem: true,
			    pagination: true,
			    dots: false,
			    nav:true,
			    margin:5,
				navText: ["<i class='icon icon-arrow-left'></i>","<i class='icon icon-arrow-right'></i>"],
			    responsive:{
			        0:{
			            items:1
			        },
			        600:{
			            items:1
			        },
			        1000:{
			            items:1
			        }
			    }
			})
		}
		jQuery(document).delegate(".qtyminus,.qtyplus","click",function(){
			product_action(jQuery(this));
			lunch_time_check(jQuery(this));
			/*var val = $(this).parents(".qty-buttons").find("input").val();
			service_id = $(this).parents(".wizard_container").data("service_id");
			if(service_id){
				$.each($(".owl-slider-"+service_id+" .owl-item:not(.main)"),function(){
					//$(this).remove();
					if($(this).find(".main").length == 0){
						
						if($(this).find(".item").data("min") > val || $(this).find(".item").data("max") < val){
							$(this).addClass("removeItem");
							//console.log($(".owl-slider-"+service_id+" .owl-stage .removeItem").index())
							$(".owl-slider-"+service_id+"").trigger('remove.owl.carousel', [$(".owl-slider-"+service_id+" .owl-stage .removeItem").index()]).trigger('refresh.owl.carousel');
							$(".product_div_"+$(this).find(".item").data("key")).hide();
							$("#product_"+$(this).find(".item").data("key")).val(0);
						}
					}
				})
				$.each($(".owl-content-"+service_id+" .item"),function(){
					if($(this).data("min") <= val && $(this).data("max") >= val && $(".owl-slider-27402 .item[data-key='"+$(this).data('key')+"']").length == 0){
						$(".owl-slider-"+service_id+"").owlCarousel('add', $(this)[0].outerHTML).owlCarousel('update');
					}
				})
			}*/
		})
		jQuery("input[name^='dates']").on('apply.daterangepicker', function (ev, picker) {
			product_action($(this));
			lunch_time_check($(this));
			calc_price($(this));

		
			/*var val = $(this).val();
			service_id = $(this).parents(".wizard_container").data("service_id");
			if(service_id){
				$.each($(".owl-slider-"+service_id+" .owl-item:not(.main)"),function(){
					//$(this).remove();
					if($(this).find(".main").length == 0){
						var remove = "1";
						if($(".available_product_"+$(this).data('key')).length){
							remove = "";
							available_product = JSON.parse($(".available_product_"+$(this).data('key')).text());
			                $.each(available_product,function(i,j){
			                    currentDate = new Date(j['startdate']);
			                    end = new Date(j['enddate']);
			                    while (currentDate <= end) {
			                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
			                        if(currentDate2 == val){
			                        	remove = "1";
			                        	return false;
			                        }
			                        currentDate.setDate(currentDate.getDate() + 1);
			                    }
			                    if(remove == "1"){
			                    	return false;
			                    }
			                })
						}
						if(remove == ""){
							$(this).addClass("removeItem");
							//console.log($(".owl-slider-"+service_id+" .owl-stage .removeItem").index())
							$(".owl-slider-"+service_id+"").trigger('remove.owl.carousel', [$(".owl-slider-"+service_id+" .owl-stage .removeItem").index()]).trigger('refresh.owl.carousel');
							$(".product_div_"+$(this).find(".item").data("key")).hide();
							$("#product_"+$(this).find(".item").data("key")).val(0);
						}
					}
				})
				$.each($(".owl-content-"+service_id+" .item"),function(){
					var add = "1";
					if($(".available_product_"+$(this).data('key')).length){
						add = "";
						available_product = JSON.parse($(".available_product_"+$(this).data('key')).text());
		                $.each(available_product,function(i,j){
		                    currentDate = new Date(j['startdate']);
		                    end = new Date(j['enddate']);
		                    while (currentDate <= end) {
		                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
		                        if(currentDate2 == val){
		                        	add = "1";
		                        	return false;
		                        }
		                        currentDate.setDate(currentDate.getDate() + 1);
		                    }
		                    if(add == "1"){
		                    	return false;
		                    }
		                })
					}
					if(add == "1"){
						$(".owl-slider-"+service_id+"").owlCarousel('add', $(this)[0].outerHTML).owlCarousel('update');
					}
				})
			}*/
		})

		var product_action = function($this){

			var val_price = $this.parents(".wizard_container").find(".qty-buttons input[name='people']").val();
			
			var val_date = $this.parents(".wizard_container").find("input[name^='dates']").val();


			$this.parents(".wizard-form-main").find(".submit .container_radio2").hide();

			var timestamp = new Date("<?= date("Y-m-d") ?>").getTime() + (10 * 24 * 60 * 60 * 1000)
			var timestamp2 = new Date(val_date).getTime()

			if(timestamp2 <= timestamp){
				$this.parents(".wizard-form-main").find(".submit .paytype[value='last_minute']").prop("checked",true);
				$this.parents(".wizard-form-main").find(".submit .container_radio2.last_minute_payment").show();
			}else{
				$this.parents(".wizard-form-main").find(".submit .paytype[value='not_refundable']").prop("checked",true);
				$this.parents(".wizard-form-main").find(".submit .container_radio2:not(.last_minute_payment)").show();
			}

			var service_id = $this.parents(".wizard_container").data("service_id");
			if(service_id){
				//console.log(service_id)

				jQuery.each($(".owl-slider-"+service_id+" .owl-item:not(.main)"),function(){
					//$(this).remove();
					if($(this).find(".main").length == 0){
						var remove = "1";
						if($this.parents(".my_main_div").find(".available_product_"+$(this).find(".item").data('key')).length){
							remove = "";
							var available_product = [];
							str = $this.parents(".my_main_div").find(".available_product_"+$(this).find(".item").data('key')).text();
							if(str){
								available_product = JSON.parse(jQuery.trim(str));
							}
							console.log($(this).find(".item").data('key'))
              $.each(available_product,function(i,j){
                  currentDate = new Date(j['startdate'].replace(/-/g, "/"));
                  end = new Date(j['enddate'].replace(/-/g, "/"));
                  while (currentDate <= end) {
                      currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
                      if(currentDate2 == val_date){
                      	remove = "1";
                      	return false;
                      }
                      currentDate.setDate(currentDate.getDate() + 1);
                  }
                  /*if(remove == "1"){
                  	return false;
                  }*/
              })
						}
						if(val_price > 1 && $(this).find(".item").data('bottal_attribute') == "0.375"){
							remove = "";
						}

						if(($(this).find(".item").data("min") > val_price) || remove == ""){
							$(this).addClass("removeItem");
							//console.log($(".owl-slider-"+service_id+" .owl-stage .removeItem").index())
							$(".owl-slider-"+service_id+"").trigger('remove.owl.carousel', [$(".owl-slider-"+service_id+" .owl-stage .removeItem").index()]).trigger('refresh.owl.carousel');
										$this.parents(".wizard_container").find(".product_div_"+$(this).find(".item").data("key")).hide();
										$this.parents(".wizard_container").find("#product_"+$(this).find(".item").data("key")).val(0);


				            $this.parents(".my_main_div").find(".add_product[data-id="+$(this).find(".item").data("key")+"]").removeAttr("data-added");
				            $this.parents(".my_main_div").find(".add_product[data-id="+$(this).find(".item").data("key")+"]").find("span").text("Add");
				            $this.parents(".my_main_div").find(".add_product[data-id="+$(this).find(".item").data("key")+"]").find("i").addClass("icon-plus").removeClass("icon-check");

				            $this.parents(".my_main_div").find(".product_div_"+$(this).find(".item").data("key")).find("input").val(0);

				            /*$(".owl-content-"+service_id+"").find(".add_product[data-id="+$(this).find(".item").data("key")+"]").data("added","");
				            $(".owl-content-"+service_id+"").find(".add_product[data-id="+$(this).find(".item").data("key")+"]").find("span").text("Add");
				            $(".owl-content-"+service_id+"").find(".add_product[data-id="+$(this).find(".item").data("key")+"]").find("i").addClass("icon-plus").removeClass("icon-check");*/
						}
					}
				})
				$.each($(".owl-content-"+service_id+" .item"),function(){
					var add = "1";
					if($this.parents(".my_main_div").find(".available_product_"+$(this).data('key')).length){
						
						add = "";
						var available_product = [];
						str = $this.parents(".my_main_div").find(".available_product_"+$(this).data('key')).text()
						if(str){
							available_product = JSON.parse(jQuery.trim(str));
						}

		                $.each(available_product,function(i,j){
		                    currentDate = new Date(j['startdate'].replace(/-/g, "/"));
		                    end = new Date(j['enddate'].replace(/-/g, "/"));
		                    while (currentDate <= end) {
		                        currentDate2 = $.datepicker.formatDate('yy-mm-dd', currentDate)
		                        if(currentDate2 == val_date){
		                        	add = "1";
		                        	return false;
		                        }
		                        currentDate.setDate(currentDate.getDate() + 1);
		                    }
		                    if(add == "1"){
		                    	return false;
		                    }
		                })
					}

					if(val_price > 1 && $(this).data('bottal_attribute') == "0.375"){
						add = "";
					}
					if(($(this).data("min") <= val_price && $(".owl-slider-"+service_id+" .item[data-key='"+$(this).data('key')+"']").length == 0) && add == "1"){
						$(".owl-slider-"+service_id+"").owlCarousel('add', $(this)[0].outerHTML).owlCarousel('update').trigger('refresh.owl.carousel');
					}
				})
			}
		}
		var ajax_request = [];
		var lunch_time_check = function($this){
			var val_people = $this.parents(".wizard_container").find(".qty-buttons input[type='number'][name='people']").val();
			var val_childs = $this.parents(".wizard_container").find(".qty-buttons input[type='number'][name='child']").val();
			var val_date = $this.parents(".wizard_container").find("input[name^='dates']").val();

			var wizard_step_dd = $this.parents(".wizard_container");

			wizard_step_dd.find(".dd-options a.not_avail_time").removeClass('disable-item');
      wizard_step_dd.find("#lunch_time .dd-options a.not_avail_time .dd-option-text-na").remove();
			wizard_step_dd.find(".dd-options a").removeClass('not_avail_time');

			var service_id = $this.parents(".wizard_container").data("service_id");
			ajax_request[service_id];
			if (ajax_request[service_id] != null){ 
			    ajax_request[service_id].abort();
			    ajax_request[service_id] = null;
			}

			if(val_people && val_date){
				ajax_request[service_id] = jQuery.ajax({
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          type: "POST",
          dataType:"json",
          data:{
              action : "check_lunch_time_avail",
              people:val_people,
              childs:val_childs,
              date:val_date,
              service_id:service_id,
          },
      		success:function(json){
      			ajax_request[service_id] = null;
      			if(json['location']){
      				$this.parents(".wizard_container").find(".custom_error_message").text(json['location']);
      			}else if(json['error']){
      				$this.parents(".wizard_container").find(".custom_error_message").text(json['error']);
      			}else{
      				$this.parents(".wizard_container").find(".custom_error_message").text("");
      			}
      			if(json['lunch_time']){
      				var unset_lunchtime = "";
      				jQuery.each(json['lunch_time'],function(i,j){
      					parenttt = wizard_step_dd.find("#lunch_time .dd-options .dd-option-value[value='"+j+"']").parent('a');
      					if(j == wizard_step_dd.find("input[name='lunch_time']").val()){
      						unset_lunchtime = 1;
      					}
      					if(parenttt){
		      				parenttt.addClass('disable-item');
		      				parenttt.addClass('not_avail_time');
		      				parenttt.find('.dd-option-text-na').remove();
		              parenttt.find('.dd-option-text').after('<label class="dd-option-text-na"> - Not Available</label>');
      					}
      				})
      				if(unset_lunchtime != ""){
			         	wizard_step_dd.find("input[name='lunch_time']").val("");
			         	console.log(11)
			         	wizard_step_dd.find("#lunch_time .dd-select .dd-selected-text").text(wizard_step_dd.find("#lunch_time .dd-options li").eq(0).find(".dd-option-text").text());
      				}
      			}
      		},
      	})
			}
		}
		var cal_lunch_time_check = function(total){
			for (var i = 0; i < total; i++) {
				lunch_time_check($("input[name^='dates']").eq(i))
			}
		}
		jQuery(document).ready(function(){
			cal_lunch_time_check(jQuery("input[name^='dates']").length);
		})
		/*$(document).delegate(".qtyminus","click",function(){

		})*/
		if(jQuery("input[name=people]").length){
			jQuery.each(jQuery("input[name=people]"),function(i,j){
				jQuery(this).trigger("change");
				product_action(jQuery(this));
			})
		}
		jQuery(".daterangepicker").trigger("apply");


		jQuery('[data-toggle="popover"]').popover()
		

	})
</script>
<?php $blocked_dates = js_array(getBookingRules()); ?>
<script type="text/javascript">
	jQuery.browser = {};
	(function () {
	    jQuery.browser.msie = false;
	    jQuery.browser.version = 0;
	    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
	        jQuery.browser.msie = true;
	        jQuery.browser.version = RegExp.$1;
	    }
	})();
	jQuery(document).ready(function(){
		jQuery.browser = {};
		(function () {
		    jQuery.browser.msie = false;
		    jQuery.browser.version = 0;
		    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
		        jQuery.browser.msie = true;
		        jQuery.browser.version = RegExp.$1;
		    }
		})();
		if(jQuery("#mphb_check_in_date").length){

      var month_names_short = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      var weekday = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
			var blocked_dates = <?php echo $blocked_dates; ?>;
			var nowDate = new Date();
			var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
			jQuery("#mphb_check_in_date").datepicker({
				dateFormat: 'yy-mm-dd',
	      "minDate": today,
	      onSelect: function(dateText, inst) {

 	        const d = new Date(dateText);
 	        jQuery(".month_selected").text(month_names_short[d.getMonth()])
 	        jQuery(".date_selected").text(("0"+d.getDate()).slice(-2))
 	        jQuery(".day_selected").text(weekday[d.getDay()].toUpperCase())
		    },
		    beforeShowDay: function(date){
		        var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
		        return [ blocked_dates.indexOf(string) == -1 ]
		    }
	    });


      const d = new Date(jQuery("#mphb_check_in_date").val());
      jQuery(".month_selected").text(month_names_short[d.getMonth()])
      jQuery(".date_selected").text(("0"+d.getDate()).slice(-2))
      jQuery(".day_selected").text(weekday[d.getDay()].toUpperCase())
	    
	    
	    jQuery(".view_service_info").click(function(e){
	    	e.preventDefault();
	    	$this = jQuery(this);
	    	jQuery.ajax({
	              url: "<?php echo admin_url('admin-ajax.php'); ?>",
	              type: "POST",
	              dataType:"json",
	              data:{
	                  action : "view_service_info",
	                  id:$this.data("id")
	              },
	    		beforeSend:function(){
	    			$this.text("Loading..");
	    		},
	    		complete:function(){
	    			$this.text("Show Details");
	    		},
	    		success:function(json){
	    			jQuery(".my_model").show()
	    			if(json['image']){
	    				jQuery(".service_img").find("img").attr("src",json['image']);
	    				jQuery(".service_img").show();
	    			}else{
	    				jQuery(".service_img").hide();
	    			}
	    			jQuery(".service_content").html(json['content'] ? json['content'] : "N/A");
	    		},
	    	})
	    })
		}
	})
</script>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(".qtyminus-2").click(function(){
			input = jQuery(this).parents(".qty-buttons-2").find("input[type='number']");
			val = input.val() ? input.val() : 0;

				
			if(input.parents(".qty-buttons-2").find("input[type='number'][name='adult']").length){
				if(val -1 > 0){
					input.val( val-1 );
				}else{
                    var vall = 0;
                    min = jQuery(this).parents(".qty-buttons-2").find('input[type="number"]').attr("min");
                    if(min){
                        vall = min;
                    }
    				input.val(vall)
					console.log('null3');
				}
			}

            if(input.parents(".qty-buttons-2").find("input[type='number'][name='childs']").length){
				if(val > 0){
					input.val(val-1)
				}else{
					input.val(0)
				}

                        }
			val = input.val() ? parseInt(input.val()) : 0;

			
			var action_change = "";
			if(input.parents("form").find("input[type='number'][name='adult']")){
				val = input.parents("form").find("input[type='number'][name='adult']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					action_change = "1";
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
			if(action_change == "" && input.parents("form").find("input[type='number'][name='childs']")){
				val = input.parents("form").find("input[type='number'][name='childs']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
		})
		jQuery(".qtyplus-2").click(function(){
			input = jQuery(this).parents(".qty-buttons-2").find("input[type='number']");
			val = input.val() ? parseInt(input.val()) : 0;
			input.val(val+1)
			val = input.val() ? parseInt(input.val()) : 0;
			var action_change = "";
			if(input.parents("form").find("input[type='number'][name='adult']")){
				val = input.parents("form").find("input[type='number'][name='adult']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					action_change = "1";
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
			if(action_change == "" && input.parents("form").find("input[type='number'][name='childs']")){
				val = input.parents("form").find("input[type='number'][name='childs']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
		})
	})
</script>

<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(".qtyminus-3").click(function(){
			input = jQuery(this).parents(".qty-buttons-3").find("input[type='number']");
			val = input.val() ? input.val() : 0;
			if(val-1 > 0){
				input.val(val-1)
			}else{
			    
                var vall = 0;
                min = $(this).parents(".qty-buttons-3").find('input[type="number"]').attr("min");
                if(min){
                    vall = min;
                }
				input.val(vall)
			}
			val = input.val() ? parseInt(input.val()) : 0;
			var action_change = "";
			if(input.parents("form").find("input[type='number'][name='adult']")){
				val = input.parents("form").find("input[type='number'][name='adult']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					action_change = "1";
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
			if(action_change == "" && input.parents("form").find("input[type='number'][name='childs']")){
				val = input.parents("form").find("input[type='number'][name='childs']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
		})
		jQuery(".qtyplus-3").click(function(){
			input = jQuery(this).parents(".qty-buttons-3").find("input[type='number']");
			val = input.val() ? parseInt(input.val()) : 0;
			input.val(val+1)
			val = input.val() ? parseInt(input.val()) : 0;
			var action_change = "";
			if(input.parents("form").find("input[type='number'][name='adult']")){
				val = input.parents("form").find("input[type='number'][name='adult']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					action_change = "1";
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
			if(action_change == "" && input.parents("form").find("input[type='number'][name='childs']")){
				val = input.parents("form").find("input[type='number'][name='childs']").val()
				val = val ? parseInt(val) : 0;
				if(val > 10){
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action2"))
				}else{
					form = jQuery(this).parents("form");
					form.attr("action",form.data("action"))
				}
			}
		})
	})
</script>
<?php
}
add_action('wp_ajax_nopriv_check_product_qty', 'check_product_qty_fun');
add_action('wp_ajax_check_product_qty', 'check_product_qty_fun');

function check_product_qty_fun(){
	$json['success'] = 1;
	if(!empty($_POST['products']) && !empty($_POST['people'])){
		foreach ($_POST['products'] as $key => $value) {
			$stock = (int)get_post_meta($value['id'],"stock",1);
			if($stock < $value['val']){
				$json['success'] = 0;
			}
		}
	}else{
		$json['success'] = 0;
	}
	echo json_encode($json);wp_die();
}

add_action('wp_ajax_nopriv_view_service_info', 'view_service_info_fun');
add_action('wp_ajax_view_service_info', 'view_service_info_fun');

function view_service_info_fun()
{
	$json = array();
	if(isset($_POST['id']) && $_POST['id'] ){
		$json['image'] = get_the_post_thumbnail_url($_POST['id'],'full');
		$json['content'] = get_post_field('post_content', $_POST['id']);
	}
	echo json_encode($json);wp_die();
}
add_action( 'wp_footer', 'footer_script' );
function head_script() {
?>
<style type="text/css">


    .vce-classic-accordion-panels,.vce-classic-accordion-panels-container {
        box-sizing: border-box;
        position: relative
    }

    .vce-classic-accordion-panel {
        border: 1px solid;
        display: block;
        margin-bottom: 2px;
        transition: border .2s ease-in-out
    }

    .vce-classic-accordion-panel:last-of-type {
        margin-bottom: 0
    }

    .vce-classic-accordion-panel-heading {
        box-sizing: border-box;
        transition: border .2s ease-in-out
    }

    .vce-classic-accordion .vce-classic-accordion-panel-title {
        background: transparent;
        border: none;
        box-shadow: none;
        box-sizing: border-box;
        color: inherit;
        display: block;
        line-height: 1;
        margin: 0;
        padding: 15px 20px;
        position: relative;
        text-decoration: none;
        transition: color .2s ease-in-out
    }

    .vce-classic-accordion .vce-classic-accordion-panel-title:focus,.vce-classic-accordion .vce-classic-accordion-panel-title:hover {
        box-shadow: none;
        outline: none;
        text-decoration: none
    }

    .vce-classic-accordion-panel-body {
        box-sizing: content-box;
        display: none;
        overflow: hidden;
        padding: 20px 20px 0;
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
        transition: padding .2s ease-in-out
    }

    .vce-classic-accordion-panel-body>:last-child {
        margin-bottom: 0
    }

    .vce-classic-accordion-panel[data-vcv-active=true] {
        display: block
    }

    .vce-classic-accordion-panel[data-vcv-active=true] .vce-classic-accordion-panel-heading {
        border-bottom: 1px solid
    }

    .vce-classic-accordion-panel[data-vcv-active=true]>.vce-classic-accordion-section-inner>.vce-classic-accordion-panel-body {
        display: block
    }

    .vce-classic-accordion-panel[data-vcv-active=true]>.vce-classic-accordion-section-inner>.vce-classic-accordion-panel-heading .vce-classic-accordion-panel-title>a:hover {
        cursor: default
    }

    .vce-classic-accordion-panel[data-vcv-active=true]>.vce-classic-accordion-section-inner>.vce-classic-accordion-panel-heading .vce-classic-accordion-panel-title>span:before {
        left: 0;
        opacity: 1;
        right: 0;
        visibility: visible
    }

    .vce-classic-accordion-panel[data-vcv-position-to-active=after]>.vce-classic-accordion-section-inner>.vce-classic-accordion-panel-heading>.vce-classic-accordion-panel-title>span:before {
        left: -100vw;
        right: 100vw
    }

    .vce-classic-accordion-panel[data-vcv-animating=true]>.vce-classic-accordion-section-inner>.vce-classic-accordion-panel-body {
        display: block;
        min-height: 0
    }

    .vce-classic-accordion-o-all-clickable .vce-classic-accordion-panel .vce-classic-accordion-panel-title>a:hover {
        cursor: pointer
    }

    .vce-classic-accordion-container {
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        position: relative;
        text-align: center;
        z-index: 3
    }

    .vce-classic-accordion {
        position: relative
    }

    .vce-classic-accordion.vce-classic-accordion-align--left .vce-classic-accordion-container,.vce-classic-accordion.vce-classic-accordion-align--left .vce-classic-accordion-panel-heading {
        text-align: left
    }

    .vce-classic-accordion.vce-classic-accordion-align--center .vce-classic-accordion-container,.vce-classic-accordion.vce-classic-accordion-align--center .vce-classic-accordion-panel-heading {
        text-align: center
    }

    .vce-classic-accordion.vce-classic-accordion-align--right .vce-classic-accordion-container,.vce-classic-accordion.vce-classic-accordion-align--right .vce-classic-accordion-panel-heading {
        text-align: right
    }

    .vce-classic-accordion-icon {
        display: inline;
        font-size: 1.15em;
        line-height: 0
    }

    .vce-classic-accordion-icon:before {
        display: inline
    }

    .vce-classic-accordion-title-text:not(:empty):not(:first-child),.vce-classic-accordion-title-text:not(:empty)~* {
        margin-left: 15px
    }

    .vce-classic-accordion-title-text:empty {
        display: inline-block
    }

    .vce-classic-accordion-icon.fa,.vce-classic-accordion-icon.vc_li {
        vertical-align: middle
    }

    .vce-classic-accordion.vce-classic-accordion--empty>.vce-classic-accordion-inner>.vce-classic-accordion-container {
        height: 0
    }

    .vce-classic-accordion.vce-classic-accordion--empty .vce-classic-accordion-panels {
        padding: 0 0 20px
    }

    .vce-classic-accordion.vce-classic-accordion--empty .vcv-row-control-container {
        margin: 0
    }

    .vce-row--col-gap-30>.vce-row-content>.vce-col {
        margin-right: 30px
    }

    .vce-row--col-gap-30>.vce-row-content>.vce-column-resizer .vce-column-resizer-handler {
        width: 30px
    }

    .rtl .vce-row--col-gap-30>.vce-row-content>.vce-col,.rtl.vce-row--col-gap-30>.vce-row-content>.vce-col {
        margin-left: 30px;
        margin-right: 0
    }

    .vce-classic-accordion-border-color--D8D8D8.vce-classic-accordion .vce-classic-accordion-panel,.vce-classic-accordion-border-color--D8D8D8.vce-classic-accordion .vce-classic-accordion-panel-heading {
        border-color: #d8d8d8
    }

    .vce-classic-accordion-border-color--D8D8D8.vce-classic-accordion .vce-classic-accordion-panel:hover,.vce-classic-accordion-border-color--D8D8D8.vce-classic-accordion .vce-classic-accordion-panel:hover .vce-classic-accordion-panel-heading {
        border-color: #adadad
    }
	.error-text {
	    color: #e34f4f;
	    font-size: 14px;
	    background: #ffffff;
	    padding: 10px;
	    border : 1px solid #d2d8dd !important;
	}
	.service_detail_text {
	    font-size: 16px;
	    background: #ffffff;
	    padding: 10px;
	    margin-bottom: 16px; 
	    border : 1px solid #d2d8dd !important;
	}
	.error-outofstock-text{
		color: #e34f4f;
		font-size: 14px;
	}
	.my-slider-list{
		position: relative;
		background: #ffffff;
	}
	.my-slider-list .owl-nav{
		position: absolute;
	    top: 0;
	    left: 15px;
	}
	
	.my-slider-list .owl-dots{
		position: absolute;
	    bottom: 0;
	    right: 10px;
	}
	.my-slider-list .owl-next{
		line-height: 1 !important;
	    padding: 5px 5px !important;
	    background: #2abfaa !important;
	}
	.my-slider-list .owl-prev{
		line-height: 1 !important;
	    padding: 5px 5px !important;
	    background: #2abfaa !important;
	}



	.my-slider-list2{
		position: relative;
		background: #ffffff;
	}
	.my-slider-list2 .owl-prev{
		position: absolute;
	    top: 50%;
	    left: 0;
	}
	.my-slider-list2 .owl-next{
		position: absolute;
	    top: 50%;
	    right: 0;
	}
	
	.my-slider-list2 .owl-dots{
		position: absolute;
	    bottom: 0;
	    right: 10px;
	}
	.my-slider-list2 .owl-next{
		line-height: 1 !important;
	    padding: 1px !important;
	    background: transparent !important;
	    color: #000 !important;
	    font-size: 25px !important;
	}
	.my-slider-list2 .owl-prev{
		line-height: 1 !important;
	    padding: 1px !important;
	    background: transparent !important;
	    color: #000 !important;
	    font-size: 25px !important;
	}
	@media (min-width: 769px){
		.h-100-owl .my-slider-list{
			height: 100%;
		}
		.h-100-owl .my-slider-list .owl-stage-outer{
			height: 100%;
		}
		.h-100-owl .my-slider-list .owl-stage-outer .owl-stage{
			height: 100%;
		}
		.h-100-owl .my-slider-list .owl-stage-outer .owl-stage .owl-item{
			height: 100%;
		}
		.h-100-owl .my-slider-list .owl-stage-outer .owl-stage .owl-item .item{
			height: 100%;
			position: relative;
		}
		.h-100-owl .my-slider-list .owl-stage-outer .owl-stage .owl-item .item img{
			position: absolute;
			margin: auto;
		    top: 0;
		    left: 0;
		    right: 0;
		    width: auto;
		    max-height: 100%;
		}
	}

	.attribute-label-carousel{
		display: inline-block;
    background: #2abfaa;
    color: #fff;
    font-size: 13px;
    position: absolute;
    top: 10px;
    left: 0px;
    padding: 2px 10px;
	}
</style>
<?php
}
add_action( 'wp_head', 'head_script' );
?>
<?php

	add_action('wp_ajax_nopriv_get_product_list_by_service', 'get_product_list_by_service_fun');
	add_action('wp_ajax_get_product_list_by_service', 'get_product_list_by_service_fun');

	function get_product_list_by_service_fun(){
		if(!empty($_POST['id'])){
			$features_image_type = get_post_meta($_POST['id'], 'features_image_type', true);
	        $service_price = get_post_meta($_POST['id'], 'service_price', true);
	        $min_pax = get_post_meta($_POST['id'], 'min_pax', true);
	        $max_pax = get_post_meta($_POST['id'], 'max_pax', true);
	        $featured_img_url = array();
	        $min = array();
	        $max = array();
	        foreach ($max_pax as $key => $value) {
	            $max[$key] = $value;
	        }
	        foreach ($min_pax as $key => $value) {
	            $min[$key] = $value;
	        }
	        ?>
	        <h4><?= get_the_title($_POST['id']) ?></h4>
            <?php if($features_image_type){ ?>

                <?php foreach ($features_image_type as $key => $value) { if($value == "service") continue; 
                    $qty_stock = (int)get_post_meta($value,"stock",1);
                    if(!$qty_stock) continue;
                    ?>
                    <?php $title = get_the_title( $value ); ?>
                    <?php if($title) {
                        $min_qty = (isset($min[$value]) ? $min[$value] : 0);
                        $max_qty = (isset($max[$value]) ? $max[$value] : 0);
                        ?>
                        <div class="product_div_<?php echo $value; ?> product_main_div" data-stock="<?= $qty_stock ?>" data-min="<?php echo $min_qty ?>" data-max="<?php echo $max_qty ?>">
                            <input type="hidden" name="product[<?php echo $value ?>]" value="0" id="product_<?php echo $value ?>" data-value="<?= isset($service_price[$value]) ? $service_price[$value] : 0 ?>">
                            <table style="background: #ffffff;">
                                <tbody>
                                    <tr>
                                        <td style="font-size: 12px;" class="product_title">
                                            <input type="radio" name="products[]" value="<?= $value ?>" <?= $products && in_array($value, $products) ? "checked" : "" ?>>
                                        </td>
                                        <td style="font-size: 12px;" class="product_title"><?= $title ?></td>
                                        <td style="font-size: 12px;    width: 40px;"><?= isset($service_price[$value]) ? "€".$service_price[$value] : "-" ?></td>
                                        <td>
                                            <select name="products_qty[<?= $value ?>]">
                                                <?php for ($i= $min_qty; $i <= min(10,$qty_stock); $i++) { ?>
                                                    <option  <?= isset($products_qty[$value]) && $products_qty[$value] == $i  ? "selected" : "" ?> value="<?= $i ?>"><?= $i ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
	        <?php
	        wp_die();
		}
	}
?>

<?php

add_shortcode( 'mphb_service_avail_result2', 'mphb_service_avail_result2_func' );
function mphb_service_avail_result2_func($attr){
	wp_enqueue_style('arf_google_fonts');
  wp_enqueue_style('arf_bootstrap_css');
  wp_enqueue_style('arf_style_css');
  wp_enqueue_style('arf_vendors_css');
  wp_enqueue_style('arf_intTelInput_css');
  wp_enqueue_script('modernizr_js');
  wp_deregister_script('jquery');
  wp_enqueue_script('arf_jquery');
  wp_enqueue_script('arf_common_scripts_js');
  wp_enqueue_script('arf_velocity_js');
  wp_enqueue_script('arf_script_js');

	$service = array();
	$checkInDateFormatted = "";
	$adult = !empty($_GET['adult']) ? $_GET['adult'] : 1;
	$childs = isset($_GET['childs']) ? $_GET['childs'] : "";
	if(!empty($_GET['mphb_check_in_date'])){
		$checkInDateFormatted			 = isset($_GET['mphb_check_in_date']) ? $_GET['mphb_check_in_date'] : date("Y-m-d");
		$date_str = strtotime($checkInDateFormatted);
		$blocked_all = "";

		if(!$blocked_all){
			$dates = getBookingRules();
			if(in_array($checkInDateFormatted, $dates)){
				$blocked_all = 1;
			}

		}
		if($date_str < strtotime(date("Y-m-d"))){
			$blocked_all = 1;
		}
		if(!$blocked_all){
			$total_person_count = 0;
			if($adult){
				$total_person_count += $adult;
			}
			if($childs){
				$total_person_count += $childs;
			}


      global $wpdb;
      $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '".$checkInDateFormatted."' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");
      $booked_places = array();
      $table_selected_ids = array();
      foreach ($booking_ids as $booking_id) {

          $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
          if($item_lunch_time == $lunch_time || $item_lunch_time == $lunch_time_text) {
              $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
              if(is_array($ids)){
                  $table_selected_ids = array_merge($table_selected_ids,$ids);
              }else{
                  $table_selected_ids[] = $ids;
              }
          }

          $mmm = get_post_meta($booking_id->post_id, 'mphb_place', true);
          foreach ($mmm as $key => $value) {
              if(isset($booked_places[$key])){
                  $booked_places[$key] = array_merge($booked_places[$key],$value);
              }else{
                  $booked_places[$key] = $value;
              }
          }
      }
      
      
			$loop_location = 0;
      if($total_person_count <= 3){
          $loop_location = 1;
      }else if($total_person_count <= 5){
          $loop_location = 2;
      }else if($total_person_count <= 7){
          $loop_location = 3;
      }else if($total_person_count <= 9){
          $loop_location = 4;
      }else if($total_person_count <= 11){
          $loop_location = 5;
      }else if($total_person_count <= 13){
          $loop_location = 6;
      }else if($total_person_count <= 15){
          $loop_location = 7;
      }else if($total_person_count <= 17){
          $loop_location = 8;
      }else if($total_person_count <= 19){
          $loop_location = 9;
      }else{
          $loop_location = 10;
      }


		$args = array(
			'post_type'=> 'mphb_room_type',
			'orderby'    => 'ID',
			'post_status' => 'publish',
			'order'    => 'DESC',
			'posts_per_page' => -1 // this will retrive all the post that is published 
		);
		$mphb_room_type = array();
		$result = new WP_Query( $args );
		if ( $result-> have_posts() ) : 
			while ( $result->have_posts() ) : 
				$result->the_post(); 
				
				$mphb_services = get_post_meta($result->post->ID, 'mphb_services', true);
				
				$mphb_room_type[] = array(
					"id" => $result->post->ID,
					"title" => get_the_title( $result->post->ID ),
					"mphb_services" => $mphb_services,
				);
              
		 	endwhile; 
		endif; wp_reset_postdata(); 
		foreach ($mphb_room_type as $key => $value) {
			if($value['mphb_services']){
				foreach ($value['mphb_services'] as $key => $value) {
					$dates = get_post_meta($value, 'mphb_block_dates', true);
					$blocked_dates = array();
          if($dates){
              foreach (explode(",", $dates) as $key => $vvv) {
                  $blocked_dates[] = date("Y-m-d",strtotime($vvv));
              }
          }
          if(!in_array($checkInDateFormatted, $blocked_dates)){
	       		$location = get_post_meta($value,"location_on_order",1);
	       		$location_count = 0;
	       		$total_locations = array();
	          foreach ($location as $location_value) {
	              $block_locations = array();
	              if(!empty($booked_places[$location_value])){
	                  $block_locations = $booked_places[$location_value];
	              }

	              $block_location_names = get_field("block_location_names",$location_value);

	              $block_location_names = explode(",", $block_location_names);

	              $ddd = get_field("location_names",$location_value);
	              $arr = array();
	              $ddd = explode("|",$ddd);
	              if($ddd){ foreach($ddd AS $kk => $vv){ $arr = array_merge($arr,explode(",",$vv)); } }
	              if($block_locations){
	                  foreach ($arr as $arr_value) {
	                  		if(in_array($arr_value, $block_location_names)) continue;
	                      if(!in_array($arr_value, $block_locations)){
	                          $total_locations[] = $arr_value;
	                          $location_count++;
	                      }
	                      if($location_count == $loop_location){
	                          break;
	                      }
	                  }
	              }else{
	                  foreach ($arr as $arr_value) {
	                  		if(in_array($arr_value, $block_location_names)) continue;
	                      $total_locations[] = $arr_value;
	                      $location_count++;
	                      if($location_count == $loop_location){
	                          break;
	                      }
	                  }
	              }
	              if($location_count == $loop_location){
	                  break;
	              }
	          }


          	$errorslocation = "";
			      if(count($total_locations) < $loop_location){
			          $errorslocation = "Booking not available for selected date please select diffrent date";
			      }

             if(!$errorslocation){
							$service[] = array(
								"id" => $value,
								"title" => get_the_title( $value ),
								"sort_order" => get_post_meta($value,"sort_order")
							);
            }
          }
				}
			}
		}

		$sort_order = array();

		foreach ($service as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $service);	
			
		

		}
	}
	ob_start();
	?>
	<style type="text/css">
		@media (max-width: 768px){
			.mobile-12{
				flex: 0 0 100% !important;
	 		    max-width: 100% !important;
			}
		}
	</style>

	<style type="text/css">
		.btn.btn-sm {
		    padding: 10px 15px;
		    text-decoration: none !important;
		    margin-right: 4px;
		    font-size: 15px;
		}
		.btn-info{
			background: #00bcd4 !important;
		    color: #fff !important;
		}
		.btn-info:hover{
		    text-decoration: none !important;
			opacity: .8;
		}
		.btn-primary{
			background: #2196f3 !important;
		    color: #fff !important;
		}
		.btn-primary:hover{
		    text-decoration: none !important;
			opacity: .8;
		}
		.table-white{
			background: #fff;
			color: #000000;
			border-color: #000000;
		}
		.table-white td,.table-white tr{
			border-color: #000000;
			color: #000000;
		}
		.my_model{
		    position: fixed;
		    top: 0;
		    left: 0;
		    width: 100%;
		    height: 100%;
		    background: #000000ab;
		    z-index: 1000;
		    overflow-y: scroll;
		}
		.my_model-content{
			position: absolute;
		    top: 15%;
		    right: 25%;
		    width: 50%;
		    background: #ffffff;
		    opacity: 1;
		    z-index: 1000000000;
		    padding: 10px;
		}
		button.close_model {
		    background: transparent;
		    color: #000000;
		    position: absolute;
		    right: 15px;
		    top: 15px;
		    padding: 0;
		    z-index: 100;
		}


		input.qtyminus, input.qtyplus {
		    top: 3px;
		    text-indent: -9999px;
		    box-shadow: none
		}

		@media (max-width: 767px) {
		    #contact_info h4, #reach_us h4 {
		        margin-bottom: 10px
		    }

		    #reach_us {
		        text-align: center
		    }

		    #reach_us ul li {
		        padding-left: 0
		    }

		    #reach_us ul li i {
		        display: none
		    }
		}

		.qty-buttons-3 {
		    position: relative;
		    width: auto;
		    height: 38px;
		    display: inline-block;
		}

		input.qty-3.form-control {
		    width: 100%;
		    text-align: center;
		    height: 43px;
		    border:0 !important;
		}

		input.qty-3.form-control:focus {
			outline: 0 !important
		}

		input.qtyminus-3, input.qtyplus-3 {
		    position: absolute;
		    width: 32px;
		    height: 38px;
		    border: 0;
		    outline: 0;
		    cursor: pointer;
		    -webkit-appearance: none;
		    border-radius: 0;
		    top:2px;
		}

		input.qtyplus-3 {
		    background: url(https://booking.arienzobeachclub.com/wp-content/plugins/arienzo-reservation-form/assets/img/plus.svg) center center no-repeat #fff !important;
		    right: 10px;
		    z-index: 1;
		    top: 0;
		}

		input.qtyminus-3 {
		    background: url(https://booking.arienzobeachclub.com/wp-content/plugins/arienzo-reservation-form/assets/img/minus.svg) center center no-repeat #fff !important;
		    left: 10px;
		    top:0;
		}
		.m-0{
			margin: 0;
		}
		.mr-2{
			margin-right: 20px;
		}
		.search-form{
			background: #e5e5e5;
			padding: 10px;
		}
		.search-form #mphb_check_in_date{
			color: #717a82;
			padding: 6px 10px;
    		border: unset;
		}
		.mphb-reserve-btn.button{
			background: #fbbb00;
		    color: #000;
		    padding: 11px 15px;
		        border-radius: 0 !important;
		}
		.mphb-reserve-btn.button:hover,.mphb-reserve-btn.button:active{
			background: #fbbb00 !important;
		    color: #000 !important;
		    opacity: .8 !important;
		}
		input.qty-3.form-control{
			padding: 9px 10px;
    		height: auto;
    		border-radius: 0 !important;
		}
		.m-0{
			margin: 0;
		}
		.mb-20{
			margin-bottom: 20px;
		}
		.align-items-center {
		    -ms-flex-align: center!important;
		    align-items: center!important;
		}
		.new_add_product{
			bottom: 10px !important;
		    top: unset !important;
		    right: unset !important;
		    position: relative !important;
		    left: calc(50% - 42px);
		}
		.new_form_code h3.main_question{
			color: #fbbb00;
			text-align: center;
		}
		.new_form_code h3.main_question strong{
			display: inline-block;
			color: #fbbb00;
		}
		.new_form_code .service_total_price_view {
			background: transparent;
		}
		.new_form_code .service_detail_text {
		    font-size: 16px;
		    background: transparent;
		    padding: 5px 0;
		    margin-bottom: 16px;
		    border: unset !important;
		}
		.new_form_code .content-right{
			padding: 20px;
		}
		.new_form_code #wizard_container{
			width: 100%;
			background: #fafafa;
		    padding: 15px;
		    border-radius: 15px;
		}
		.new_my_main_div{
			border: 2px solid #ddd;
		    border-radius: 20px;
		    margin: 10px 20px;
		    padding: 15px 10px;
		    background: #ffffff;
		}
		.w-49{
			width: 49%
		}
		.pll-30{
			padding-left: 40px !important; 
		}
		.br-blue{
			border-right: 1px solid #2abfab
		}
		.new_form_code .backward {
		    color: #333;
		    background: transparent;
		    text-transform: uppercase;
		    font-size: 15px;
		}
		.new_form_code .backward i.icon{
			position: relative;
    		top: 2px;
		}
		.new_form_code .backward:hover {
			color: #333;
		    background: transparent;
		}
		.container_radio2{
			width: 100%;
			margin-bottom:10px;
		}
		.container_radio2 input{
			display: none;
		}
		.container_radio_label{
			padding: 10px;
			width: 100%;
			display: flex;
			cursor: pointer;
    		justify-content: space-between;
    		background: #fff;
    		border: 2px solid #fff;
		}
		.container_radio2 input:checked ~ .container_radio_label{
			border: 2px solid #2abfaa;	
			color: #2abfaa
		}
		.checked-icon{
    		
    	}
		.checked-icon:before{
			content: "\4e";
    		font-family: ElegantIcons;
    		margin-right: 10px;
		    position: relative;
		    top: 2px;
		}
		.my-slider-list2.owl-carousel .owl-item img{
			margin-bottom: 30px;
		}
		.desc-head {
		    color: #2abfaa;
		    margin-bottom: 20px;
		    font-weight: 400;
		    font-size: 24px;
		}
		.include-head{
			color: #ffbe00;
		    font-weight: 400;
		    font-size: 24px;
		}
		.view_more_options {
		    text-align: right;
		    margin: 0;
		}
		.view_more_options li {
		    display: inline-block;
		    font-size: 15px;
		    color: #b1afaf;
		    padding-right: 10px;
		    border-right: 1px solid;
		    line-height: 1;
		    cursor: pointer;
		}
		.view_more_options li:last-child {
		    padding-right: 0;
		    border: none;
		}
		.m-0{
			margin: 0;
		}
		.include-list{
			font-size: 16px;
		}
		.ui-datepicker td span, .ui-datepicker td a{
			text-align: center !important;
		}
		.ui-widget-content {
		     background-color: #ffffff !important; 
		}
		.ui-widget-header{
			background: #e9e9e9 !important;
		}
        .search-form .qty-buttons-3{
            margin-right: 0.5rem;
        }
		@media (max-width: 768px) {
            .new_form_code{
                margin-top: 15px;
                padding: 0 !important
            }
            .service_total_price_view{
                font-size: 16px;
            }
            .new_form_code #wrapped .field_wrapper a{
                right: -15px;
            }
			.br-blue{
				border-right: unset;
			}
			.pll-30{
				padding-left: 0 !important;
			}
            .search-form > .d-flex{
                flex-wrap: wrap;
                text-align: center;
            }
            .search-form > .d-flex > span{
                width: 100%;
                text-align: left;
            }
            .search-form #mphb_check_in_date{
                width: 100%;
            }
            .search-form .mphb-check-in-date-wrapper{
                width: 100%;
                margin-bottom: 10px !important;
                margin-right: 0 !important;
            }
            .qty-buttons-3{
                width: 100%;
                margin-bottom: 10px !important;
                margin-right: 0 !important;
            }
		}
		@media (max-width: 500px) {

			.qty-buttons-3 {
			    width: 100%;
			}
			input.qty-3.form-control {
			    width: 100%;
			}
		}
	</style>
	<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
	<form  action="<?= ($adult && $adult > 10) || ($childs && $childs > 10) ? (isset($attr['action2']) ? $attr['action2'] : "") : ""  ?>" data-action="" method="GET"  class="mb-20 search-form"  data-action2="<?= isset($attr['action2']) ? $attr['action2'] : "" ?>" >
		<div class="d-flex align-items-center ">
			<span class="mr-2">SEARCH</span>
			<p class="mphb-check-in-date-wrapper m-0 mr-2">
				<input id="<?php echo esc_attr( 'mphb_check_in_date' ); ?>" type="text" class="mphb-datepick" name="mphb_check_in_date" value="<?php echo esc_attr( $checkInDateFormatted ); ?>" required="required" autocomplete="off" placeholder="<?php _e( 'Check-in Date', 'motopress-hotel-booking' ); ?>"  />
			</p>
			<div class="qty-buttons-3">
			    <input type="button" value="+" class="qtyplus-3" name="adult">
			    <input type="number" name="adult" id="adult" value="<?= $adult ?>" class="qty-3 form-control" placeholder="Adults" readonly=""  min="1">
			    <input type="button" value="-" class="qtyminus-3" name="adult">
			</div>

			<div class="qty-buttons-3">
			    <input type="button" value="+" class="qtyplus-3" name="childs">
			    <input type="number" name="childs" id="childs" value="<?= $childs ?>" class="qty-3 form-control" placeholder="Child" readonly="">
			    <input type="button" value="-" class="qtyminus-3" name="childs">
			</div>

			<p class="mphb-reserve-btn-wrapper  m-0">
				<input class="mphb-reserve-btn button" type="submit" value="<?php _e( 'Check Availability', 'motopress-hotel-booking' ); ?>" />
				<span class="mphb-preloader mphb-hide"></span>
			</p>
		</div>
	</form>
	
	<?php if(!empty($_GET['mphb_check_in_date'])){ 
		$default_adult = !empty($_GET['adult']) ? $_GET['adult'] : 1;
		$default_childs = !empty($_GET['childs']) ? $_GET['childs'] : 0;
		?>
		<?php if(!empty($service)){ ?>
			<?php foreach ($service as $key => $value) { 
				$background_color = get_post_meta($value['id'],"background_color",1);
				$mphb_show_child = get_post_meta($value['id'],"mphb_show_child",1);

				if($default_childs && !$mphb_show_child){
					continue;
				}
				$mphb_show_child = $mphb_show_child ? 0 : 1;
				$title = get_the_title($value['id']);

				$welcome_bottle_quantities_title = get_post_meta($value['id'],"welcome_bottle_quantities_title",true);
				$welcome_bottle_quantities = get_post_meta($value['id'],"welcome_bottle_quantities",true);
				$terms_conditions_title = get_post_meta($value['id'],"terms_conditions_title",true);
				$terms_conditions = get_post_meta($value['id'],"terms_conditions",true);
				$beach_club_policy_title = get_post_meta($value['id'],"beach_club_policy_title",true);
				$beach_club_policy = get_post_meta($value['id'],"beach_club_policy",true);
				$kids_adolescents_ages_2_17_years_rates_title = get_post_meta($value['id'],"kids_adolescents_ages_2_17_years_rates_title",true);
				$kids_adolescents_ages_2_17_years_rates = get_post_meta($value['id'],"kids_adolescents_ages_2_17_years_rates",true);


				?>
				<div class="d-flex flex-wrap my_main_div new_my_main_div">
					<div class="col-7  mobile-12 mobilep-0">
						<?= do_shortcode('[mphb_room_service_detail_full2 post_id='.$value['id'].']'); ?>
					</div>
					<div class="col-5 mobile-12 new_form_code" >
						<?= do_shortcode('[arf_form_search2 service_id="'.$value['id'].'" child_hidden='.$mphb_show_child.' default_adult='.$default_adult.' default_childs='.$default_childs.' mphb_check_in_date="'.$_GET['mphb_check_in_date'].'" service_title="'.$title.'"  action2="'.(isset($attr['action2']) ? $attr['action2'] : "").'"]'); ?>
					</div>
					<div class="col-12 mobile-12" >
						<ul class="view_more_options">
							<?php if($welcome_bottle_quantities_title || $welcome_bottle_quantities) { ?>
								<li onclick="jQuery('#welcome_bottle_quantities_<?= $value['id'] ?>').modal('show')">Welcome Bottle quantities</li>
							<?php } ?>
							<?php if($terms_conditions_title || $terms_conditions) { ?>
								<li onclick="jQuery('#terms_conditions_<?= $value['id'] ?>').modal('show')">Terms & Conditions</li>
							<?php } ?>
							<?php if($beach_club_policy_title || $beach_club_policy) { ?>
								<li onclick="jQuery('#beach_club_policy_<?= $value['id'] ?>').modal('show')">Beach Club Policy</li>
							<?php } ?>
							<?php if($kids_adolescents_ages_2_17_years_rates_title || $kids_adolescents_ages_2_17_years_rates) { ?>
								<li onclick="jQuery('#kids_adolescents_ages_2_17_years_rates_<?= $value['id'] ?>').modal('show')">Kids & Adolescents (ages 2-17 years) rates</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php if($welcome_bottle_quantities_title || $welcome_bottle_quantities) { ?>
				<div class="modal" style="z-index: 9999;" id="welcome_bottle_quantities_<?= $value['id'] ?>" role="dialog">
				  <div class="modal-dialog modal-lg" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h6 class="modal-title">Welcome Bottle quantities</h6>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				      	<?php if($welcome_bottle_quantities_title){ ?>
				        	<h6><?= $welcome_bottle_quantities_title ?></h6>
				      	<?php } ?>

						<ul class="m-0">
							<?php foreach ($welcome_bottle_quantities as $kk => $vv) { if(!$vv['welcome_bottle_quantities']) continue; ?>
								<li>	
									<?= $vv['welcome_bottle_quantities'] ?>
								</li>	
							<?php } ?>
						</ul>	
				      </div>
				    </div>
				  </div>
				</div>
				<?php } ?>
				<?php if($terms_conditions_title || $terms_conditions) { ?>
				<div class="modal" style="z-index: 9999;" id="terms_conditions_<?= $value['id'] ?>" role="dialog">
				  <div class="modal-dialog modal-lg" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h6 class="modal-title">Terms & Conditions</h6>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				      	<?php if($terms_conditions_title){ ?>
				        	<h6><?= $terms_conditions_title ?></h6>
				      	<?php } ?>

						<ul class="m-0">
							<?php foreach ($terms_conditions as $kk => $vv) { if(!$vv['terms_conditions']) continue; ?>
								<li>	
									<?= $vv['terms_conditions'] ?>
								</li>	
							<?php } ?>
						</ul>	
				      </div>
				    </div>
				  </div>
				</div>
				<?php } ?>
				<?php if($beach_club_policy_title || $beach_club_policy) { ?>
				<div class="modal" style="z-index: 9999;" id="beach_club_policy_<?= $value['id'] ?>" role="dialog">
				  <div class="modal-dialog modal-lg" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h6 class="modal-title">Beach Club Policy</h6>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				      	<?php if($beach_club_policy_title){ ?>
				        	<h6><?= $beach_club_policy_title ?></h6>
				      	<?php } ?>

						<ul class="m-0">
							<?php foreach ($beach_club_policy as $kk => $vv) { if(!$vv['beach_club_policy']) continue; ?>
								<li>	
									<?= $vv['beach_club_policy'] ?>
								</li>	
							<?php } ?>
						</ul>	
				      </div>
				    </div>
				  </div>
				</div>
				<?php } ?>
				<?php if($kids_adolescents_ages_2_17_years_rates_title || $kids_adolescents_ages_2_17_years_rates) { ?>
				<div class="modal" style="z-index: 9999;" id="kids_adolescents_ages_2_17_years_rates_<?= $value['id'] ?>" role="dialog">
				  <div class="modal-dialog modal-lg" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h6 class="modal-title">Kids & Adolescents (ages 2-17 years) rates</h6>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				      	<?php if($kids_adolescents_ages_2_17_years_rates_title){ ?>
				        	<h6><?= $kids_adolescents_ages_2_17_years_rates_title ?></h6>
				      	<?php } ?>

						<ul class="m-0">
							<?php foreach ($kids_adolescents_ages_2_17_years_rates as $kk => $vv) { if(!$vv['kids_adolescents_ages_2_17_years_rates']) continue; ?>
								<li>	
									<?= $vv['kids_adolescents_ages_2_17_years_rates'] ?>
								</li>	
							<?php } ?>
						</ul>	
				      </div>
				    </div>
				  </div>
				</div>
				<?php } ?>
			<?php } ?>
		<?php }else{ ?>
			<table class="table table-bordered table-white">
				<tr>
					<td>Services Not Available</td>
				</tr>
			</table>
		<?php } ?>

		<script type="text/javascript">
			/*jQuery(document).ready(function(){
				jQuery(".qtyminus-3").click(function(){
					input = jQuery(this).parents(".qty-buttons-3").find("input[type='number']");
					val = input.val() ? input.val() : 0;
					if(val > 0){
						input.val(val-1)
					}else{
						input.val(0)
					}
					val = input.val() ? parseInt(input.val()) : 0;
					var action_change = "";
					if(input.parents("form").find("input[type='number'][name='adult']")){
						val = input.parents("form").find("input[type='number'][name='adult']").val()
						val = val ? parseInt(val) : 0;
						if(val > 10){
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action2"))
							action_change = "1";
						}else{
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action"))
						}
					}
					if(action_change == "" && input.parents("form").find("input[type='number'][name='childs']")){
						val = input.parents("form").find("input[type='number'][name='childs']").val()
						val = val ? parseInt(val) : 0;
						if(val > 10){
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action2"))
						}else{
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action"))
						}
					}
				})
				jQuery(".qtyplus-3").click(function(){
					input = jQuery(this).parents(".qty-buttons-3").find("input[type='number']");
					val = input.val() ? parseInt(input.val()) : 0;
					input.val(val+1)
					val = input.val() ? parseInt(input.val()) : 0;
					var action_change = "";
					if(input.parents("form").find("input[type='number'][name='adult']")){
						val = input.parents("form").find("input[type='number'][name='adult']").val()
						val = val ? parseInt(val) : 0;
						if(val > 10){
							action_change = "1";
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action2"))
						}else{
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action"))
						}
					}
					if(action_change == "" && input.parents("form").find("input[type='number'][name='childs']")){
						val = input.parents("form").find("input[type='number'][name='childs']").val()
						val = val ? parseInt(val) : 0;
						if(val > 10){
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action2"))
						}else{
							form = jQuery(this).parents("form");
							form.attr("action",form.data("action"))
						}
					}
				})
			})*/
		</script>
	<?php }else{ ?>
		<script src="<?= plugin_dir_url('').'arienzo-reservation-form/assets/js/common_scripts.min.js' ?>"></script>
	<?php } ?>
	<?php
	$content = ob_get_clean();
	return $content;
}


add_shortcode( 'mphb_room_service_detail_full2', 'mphb_room_service_detail_full2_func' );
function mphb_room_service_detail_full2_func($atts){
	$return = "";
	if(isset($atts['post_id'])){
		$title = get_the_title($atts['post_id']);
		$post_content = get_post_field('post_content', $atts['post_id']);
		$return .= "<div class='d-flex flex-wrap h-100 align-items-center'>";
		

		$service_price = get_post_meta($atts['post_id'], 'service_price', true);
		$default = get_post_meta($atts['post_id'], 'default', true);
		$features_image_type = get_post_meta($atts['post_id'], 'features_image_type', true);
		$min_pax = get_post_meta($atts['post_id'], 'min_pax', true);
		$max_pax = get_post_meta($atts['post_id'], 'max_pax', true);
		$featured_img_url = array();
		$min = array();
		$max = array();
		foreach ($max_pax as $key => $value) {
			$max[$key] = $value;
		}
		foreach ($min_pax as $key => $value) {
			$min[$key] = $value;
		}
		if(!$features_image_type){
			//$features_image_type[] = "service";
			$featured_img_url["service"] = get_the_post_thumbnail_url($atts['post_id'],'full');
		}
		$defaults = array();
		$na_defaults = array();

		$defaults_sort_order = array();
		$na_defaults_sort_order = array();
		
		


		if($features_image_type){
			foreach ($features_image_type as $key => $value) {
				if($value == "service"){
					$url = get_the_post_thumbnail_url($atts['post_id'],'full');
					if($url){
						$featured_img_url["service"]  = $url;
					}
				}else{
                    if ( get_post_status ( $value ) == 'trash' ) {
                        continue;
                    }
                    $qty_stock = (int)get_post_meta($value,"stock",1);
                    if(!$qty_stock) continue;
					$url = get_the_post_thumbnail_url($value,'full');
					if($url){
						if($default && in_array($value, $default)){
							$defaults[$value] = array(
								"id" => $value,
								"url" => $url,
							);
							$defaults_sort_order[$value] = isset($service_price[$value]) ? $service_price[$value] : 0;
						}else{
							$na_defaults[$value] = array(
								"id" => $value,
								"url" => $url,
							);
							$na_defaults_sort_order[$value] = isset($service_price[$value]) ? $service_price[$value] : 0;
						}
					}
				}
			}
		}

		array_multisort($defaults_sort_order, SORT_DESC, $defaults);	
		array_multisort($na_defaults_sort_order, SORT_DESC, $na_defaults);	

		$featured_img_url = array_merge($featured_img_url,$defaults,$na_defaults);
		if($featured_img_url){
			$return .= "<div class='col-4 mobilep-0 br-blue mobile-12 h-100-owl'>";

			$return .= '<div class="my-slider-list2 owl-carousel owl-theme owl-slider-'.$atts['post_id'].'">';
				if(isset($featured_img_url["service"])){
					$return .= '<div  class="item main">';
							$return .= '<img src="'.$featured_img_url["service"].'">';
					$return .= '</div>';
					unset($featured_img_url["service"]);
				}
			$return .= '</div>';
			$default_selected = "";

			$adult = !empty($_GET['adult']) ? (int)$_GET['adult'] : 1;
			$return .= '<div style="display:none;" class="owl-content-'.$atts['post_id'].'">';
				foreach ($featured_img_url as $key => $value) {
					if((isset($min[$value['id']]) && $min[$value['id']] != '' && $min[$value['id']] != '0')){
						$bottal_attribute = get_post_meta($value['id'],"bottal_attribute",1);
						$return .= '<div  class="item " data-bottal_attribute="'.$bottal_attribute.'" data-key="'.$value['id'].'" data-min="'.(isset($min[$value['id']]) ? $min[$value['id']] : 0).'" data-max="'.(isset($max[$value['id']]) ? $max[$value['id']] : 0).'">';
							$return .= '<img src="'.$value['url'].'">';
							if($bottal_attribute){
								$text = "";
								if($bottal_attribute == "0.375"){
									$text = "375ml";
								}else if($bottal_attribute == "0.75"){
									$text = "750ml";
								}else if($bottal_attribute == "Magnum"){
									$text = "Magnum";
								}
								if($text){
									$return .= '<span class="attribute-label-carousel">'.$text.'</span>';
								}
							}
							if($value['id'] != "service"){

								$availability_range = get_post_meta($value['id'],"availability_range",1);

								$text = '<i class="icon icon-plus"></i> <span>Add</span>';
								$attrdata = '';
								if($default && in_array($value['id'], $default) && !$default_selected){
									if($availability_range){
                                        foreach ($availability_range as $kk => $vvv) {
                                            $startdate = $vvv['startdate'];
                                            $parts = explode('-',$startdate);
                                            $startdate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                             
                                            $enddate = $vvv['enddate'];
                                            $parts = explode('-',$enddate);
                                            $enddate = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
                                            if(!empty($_GET['mphb_check_in_date']) && strtotime($startdate) <= strtotime($_GET['mphb_check_in_date']) && strtotime($enddate) >= strtotime($_GET['mphb_check_in_date']) && isset($min[$value['id']]) && $adult >= $min[$value['id']]){
                                                $default_selected = 1;
												$attrdata = " data-added='1' ";
												$text = '<i class="icon icon-check"></i> <span>Added</span>';
                                            }
                                        }
                                    }else{
										$default_selected = 1;
										$attrdata = " data-added='1' ";
										$text = '<i class="icon icon-check"></i> <span>Added</span>';
                                    }
								}
								$return .= '<a href="#" '.$attrdata.' data-id="'.$value['id'].'"  data-min="'.(isset($min[$value['id']]) ? $min[$value['id']] : 0).'" data-max="'.(isset($max[$value['id']]) ? $max[$value['id']] : 0).'" class="add_product new_add_product">'.$text.'</a>';
							}
						$return .= '</div>';
					}
				}
			$return .= '</div>';
			foreach ($featured_img_url as $key => $value) {
				if((isset($min[$value['id']]) && $min[$value['id']] != '' && $min[$value['id']] != '0')){
					$availability_range = get_post_meta($value['id'],"availability_range",1);
					if($availability_range){
						$return .= '<div style="display:none" class="available_product_'.$value['id'].'">';
						$return .= json_encode($availability_range);
						$return .= '</div>';
					}
				}
			}
			$return .= '</div>';
		}

		$description_title = get_post_meta($atts['post_id'],"description_title",true);
		$desc_includes = get_post_meta($atts['post_id'],"desc_includes",true);
		$desc_includes2 = get_post_meta($atts['post_id'],"desc_includes2",true);
		
		$return .= "<div class='col-8 mobile-12 mobilep-0 pll-30'>";
		$return .= '<div class="vce-google-fonts-heading vce-google-fonts-heading--align-left vce-google-fonts-heading--color-b-248-135-73--45--5C00FF--FF7200 vce-google-fonts-heading--font-family-Lato">';
			$return .= '<div class="vce-google-fonts-heading-wrapper">';
				$return .= '<div class="vce-google-fonts-heading--background m-0 vce" id="el-9e96ffe2" data-vce-do-apply="border background  padding margin el-9e96ffe2">';
					$return .= '<h4 class="vce-google-fonts-heading-inner" style="font-size: 30px;font-weight: 700;text-transform: uppercase;margin-bottom:5px;">'.$title;
					$return .= '</h4>';
				$return .= '</div>';
			$return .= '</div>';
		$return .= '</div>';
		$return .= '<div class="vce-text-block">';
			$return .= '<div class="vce-text-block-wrapper vce my-accommodation m-0">';
				if($description_title){
					$return .= '<h5 class="desc-head">';
						$return .= $description_title;
					$return .= '</h5>';
				}
				if($desc_includes && $desc_includes2){
					$return .= '<h5 class="include-head">';
						$return .= "Includes:";
					$return .= '</h5>';	
					if($desc_includes){
						$return .= '<strong>Per person/to share:</strong>';	
						$return .= '<ul class="m-0 include-list">';	
							foreach ($desc_includes as $key => $value) { if(!$value['desc_includes']) continue;
								$return .= '<li>';	
									$return .= $value['desc_includes'];	
								$return .= '</li>';	
							}
						$return .= '</ul>';	

					}
					if($desc_includes2){
						$return .= '<strong>Per person:</strong>';	
						$return .= '<ul class="m-0 include-list">';
							foreach ($desc_includes2 as $key => $value) { if(!$value['desc_includes2']) continue;
								$return .= '<li>';	
									$return .= $value['desc_includes2'];	
								$return .= '</li>';	
							}
						$return .= '</ul>';	

					}
				}
			$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';

		
	}
	return $return;
}
function get_lunch_text($lunch_id = ""){
	if(is_numeric($lunch_id)){
	    
		$title = get_the_title($lunch_id);
		if(get_post_meta($lunch_id,"lunch_time_type",true) == "lunch_at_your_sunbed"){
		    $title .= " - Sunbed";
		}
	}else{
		$title = $lunch_id;
	}
	return $title;
}

function get_lunch_text2($lunch_id = ""){
	if(is_numeric($lunch_id)){
		$title = get_the_title($lunch_id);
	}else{
		$title = $lunch_id;
	}
	return $title;
}

add_action('wp_ajax_nopriv_check_lunch_time_avail', 'check_lunch_time_avail_fun');
add_action('wp_ajax_check_lunch_time_avail', 'check_lunch_time_avail_fun');

function check_lunch_time_avail_fun(){
	$json['lunch_time'] = array();
	if(!empty($_POST['people']) && !empty($_POST['date']) && !empty($_POST['service_id'])){



      $args = array(
          'post_type' => 'arf_pt_table',
          'posts_per_page' => -1,
          'post_status' => 'publish',
          'orderby' => 'post_title',
          'order' => 'ASC'
      );
      
      $args['meta_query'] = array(
          array(
              'key' => 'is_subed',
              'value' => "1"
          ),
          array(
              'key' => 'auto_booking',
              'value' => "1"
          )
      );

      $sunbed_tables = get_posts($args);

      $args = array(
          'post_type' => 'arf_pt_table',
          'posts_per_page' => -1,
          'post_status' => 'publish',
          'orderby' => 'post_title',
          'order' => 'ASC'
      );
  
      $args['meta_query'] = array(
          'relation' => 'and',
          array(
          	'relation' => 'or',
	          array(
	              'key' => 'is_subed',
	              'value' => "0"
	          ),
	          array(
	              'key' => 'is_subed',
	              'compare' => "NOT EXISTS"
	          )
          ),
          array(
          	'key' => 'auto_booking',
            'value' => "1"
          )
      );

      $tables = get_posts($args);

      $avail_sunbed_tables = count($sunbed_tables);
			$avail_tables = count($tables);
      

			global $wpdb;
      $booking_ids = $wpdb->get_results("SELECT post_id FROM  $wpdb->postmeta LEFT JOIN ".$wpdb->prefix."posts ON (".$wpdb->prefix."posts.ID = ".$wpdb->postmeta.".post_id) WHERE `meta_key` = 'mphb_check_in_date' AND `meta_value` = '".$_POST['date']."' AND post_status IN ('confirmed','paid_not_refundable','paid_refundable','last_minute','pending_late_charge')");

      $total_pax = 0;
      $table_selected_ids = array();
      $booked_places = array();
      $total_locations = array();

      if($booking_ids){
	      foreach ($booking_ids as $booking_id) {
	      		$price_breakdown = get_post_meta( $booking_id->post_id, '_mphb_booking_price_breakdown', true); 
	      		if($price_breakdown){
      				$ddd = json_decode(strip_tags($price_breakdown),true);
							if(isset($ddd['rooms'])){
								foreach ($ddd['rooms'] as $kk => $value) {
									$total_pax += !empty($value['room']['adults']) ? $value['room']['adults'] : 0; 
			           	$total_pax += !empty($value['room']['children']) ? $value['room']['children'] : 0; 
								}
							}
	      		}
	          $item_lunch_time = get_post_meta($booking_id->post_id, 'lunch_time', true);
	          $lunch_id = "";
	          if(is_numeric($item_lunch_time)){
	          	$lunch_id = $item_lunch_time;
	          }else{
	          	$iddd = get_post_by_title($item_lunch_time);
	          	if($iddd){
	          		$lunch_id = $iddd;
	          	}
	          }
	          if($lunch_id) {
			          if(!isset($table_selected_ids[$lunch_id])){
			          	$table_selected_ids[$lunch_id] = array();
			          }
	              $ids = get_post_meta($booking_id->post_id, 'arf_cp_table_id', true);
	              if(is_array($ids)){
	                  $table_selected_ids[$lunch_id] = array_merge($table_selected_ids[$lunch_id],$ids);
	              }else{
	                  $table_selected_ids[$lunch_id][] = $ids;
	              }
	          }


	          $mmm = get_post_meta($booking_id->post_id, 'mphb_place', true);
	          foreach ($mmm as $key => $value) {
	              if(isset($booked_places[$key])){
	                  $booked_places[$key] = array_merge($booked_places[$key],$value);
	              }else{
	                  $booked_places[$key] = $value;
	              }
	          }
	      }
      }

      $location = get_post_meta($_POST['service_id'],"location_on_order",1);

      foreach ($location as $location_value) {
          $block_locations = array();
          if(!empty($booked_places[$location_value])){
              $block_locations = $booked_places[$location_value];
          }

          $block_location_names = get_field("block_location_names",$location_value);


	        $block_location_names = explode(",", $block_location_names);

          $ddd = get_field("location_names",$location_value);
          $arr = array();
          $ddd = explode("|",$ddd);
          if($ddd){ foreach($ddd AS $kk => $vv){ $arr = array_merge($arr,explode(",",$vv)); } }

          if($block_locations){
              foreach ($arr as $arr_value) {
              		if(!in_array($arr_value, $block_location_names)){
	                  if(!in_array($arr_value, $block_locations)){
	                      $total_locations[] = $arr_value;
	                      $location_count++;
	                  }
              		}
              }
          }else{
              foreach ($arr as $arr_value) {
          				if(!in_array($arr_value, $block_location_names)){
	                  $total_locations[] = $arr_value;
	                  $location_count++;
          				}
              }
          }
      }


      $location_count = 0;

      $loop_location = 0;
      $total_person_count = $_POST['people'] + (!empty($_POST['childs']) ? $_POST['childs'] : 0);
      
      if($total_person_count <= 3){
          $loop_location = 1;
      }else if($total_person_count <= 5){
          $loop_location = 2;
      }else if($total_person_count <= 7){
          $loop_location = 3;
      }else if($total_person_count <= 9){
          $loop_location = 4;
      }else if($total_person_count <= 11){
          $loop_location = 5;
      }else if($total_person_count <= 13){
          $loop_location = 6;
      }else if($total_person_count <= 15){
          $loop_location = 7;
      }else if($total_person_count <= 17){
          $loop_location = 8;
      }else if($total_person_count <= 19){
          $loop_location = 9;
      }else{
          $loop_location = 10;
      }

      foreach ($table_selected_ids as $key => $value) {
      	$lunch_time_type = get_post_meta($key,"lunch_time_type",true);
      	if($lunch_time_type == 'lunch_at_your_sunbed'){
      		if(count($value) + $loop_location > $avail_sunbed_tables){
      			$json['lunch_time'][] = $key;
      		}
      	}else{
      		if(count($value) + $loop_location > $avail_tables){
      			$json['lunch_time'][] = $key;
      		}
      	}
      }
    	if($avail_sunbed_tables < $loop_location){
    		$lunch_time_list_sunbed = get_posts([
		        'numberposts'       => -1,
		        'post_type'     => 'lunch_time',
		        'post_status'   => 'publish',
		        'suppress_filters' => 0,
		        'order'=> "asc",
		        'orderby'=> "post_title",
		        'meta_query' => array(
		            array(
		                'key' => 'lunch_time_type',
		                'value' => "lunch_at_your_sunbed"
		            )
		        ),
		    ]);

		    foreach ($lunch_time_list_sunbed as $key => $value) {
		    	$json['lunch_time'][] = $value->ID;
		    }
    	}
			if($avail_tables < $loop_location){
				$lunch_time_list_table = get_posts([
	        'numberposts'       => -1,
	        'post_type'     => 'lunch_time',
	        'post_status'   => 'publish',
	        'suppress_filters' => 0,
	        'order'=> "asc",
	        'orderby'=> "post_title",
	        'meta_query' => array(
	            'relation' => 'OR', /* <-- here */
	            array(
	                'key' => 'lunch_time_type',
	                'value' => "lunch_time"
	            ),
	            array(
	                'key' => 'lunch_time_type',
	                'value'   => '',
	                'compare' => '='
	            ),
	            array(
	                'key' => 'lunch_time_type',
	                'compare'   => 'NOT EXISTS',
	            )
	        ),
		    ]);
		    foreach ($lunch_time_list_table as $key => $value) {
						$json['lunch_time'][] = $value->ID;
		    }
			}
    
			array_unique($json['lunch_time']);

      if(count($total_locations) < $loop_location){
          $json['location'] = "We regret to inform you that we are unable to accommodate your requested reservation; please consider selecting an alternative date or reducing the number of individuals in order for us to be able to serve you.";
          //$json['location'] = "Booking not available for selected persons and date please decrese person or select diffrent date" . count($total_locations);
      }
      $mphb_daily_limit = get_option("mphb_daily_limit");
       
      if(($total_pax + $total_person_count) > $mphb_daily_limit){
      	$json['error'] = "Booking full for selected date please select diffrent date.";
      }
	}
	echo json_encode($json);wp_die();
}

function get_post_by_title($page_title) {
    global $wpdb;
    $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='lunch_time'", $page_title ));
    if ( $post )
       return $post;

    return "";
}
 function ww_load_dashicons(){
     wp_enqueue_style('dashicons');
 }
 add_action('wp_enqueue_scripts', 'ww_load_dashicons');

 

add_filter("admin_footer","admin_footer_fun");
function admin_footer_fun(){
	if(isset($_GET['tab']) && ($_GET['tab'] == "customer_emails" || $_GET['tab'] == "admin_emails")){
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery(".settings_div_inner > h2").click(function(){
					jQuery(this).parent(".settings_div_inner").children("p").toggle();
					jQuery(this).parent(".settings_div_inner").children(".form-table").toggle();
				})
			})
		</script>
		<?php
	}
}
