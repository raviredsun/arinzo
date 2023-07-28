<?php
/**
 * Template Name: Registration Page
 */

get_header();
global $error_output;
?>
<section class="irf_registerForm">
    <div class="container-login100">
        <div class="wrap-login100 p-l-85 p-r-85 p-t-55 p-b-55">
            <form class="login100-form validate-form flex-sb flex-w" action="" method="post">
                <?php wp_nonce_field('irf_register_action'); ?>
                <span class="login100-form-title p-b-32">
						Register Page
					</span>

                <span class="txt1 p-b-11">
						Username
					</span>
                <div class="wrap-input100 validate-input m-b-36" data-validate = "Username is required">
                    <input class="input100" type="text" name="u_name" >
                    <span class="focus-input100"></span>
                </div>
                <span class="txt1 p-b-11">
						Email
					</span>
                <div class="wrap-input100 validate-input m-b-36" data-validate = "Email is required">
                    <input class="input100" type="email" name="u_email" >
                    <span class="focus-input100"></span>
                </div>

                <span class="txt1 p-b-11">
						Password
					</span>
                <div class="wrap-input100 validate-input m-b-12" data-validate = "Password is required">
                    <input class="input100" type="password" name="u_pass" >
                    <span class="focus-input100"></span>
                </div>
                <span class="txt1 p-b-11">
						Confirm Password
					</span>
                <div class="wrap-input100 validate-input m-b-12" data-validate = "Password is required">
                    <input class="input100" type="password" name="u_c_pass" >
                    <span class="focus-input100"></span>
                </div>

                <div class="container-login100-form-btn">
                    <button class="login100-form-btn">
                        Register
                    </button>
                </div>
                <div class="flex-sb-m w-full p-b-48">
                    <div>
                        <a href="<?php echo get_page_link( get_page_by_path( 'profile-page' )->ID ); ?>" class="txt3">Login</a>
                    </div>
                </div>
                <?php display_message($error_output); ?>
            </form>
        </div>
    </div>
</section>
<?php get_footer(); ?>
