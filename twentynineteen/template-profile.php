<?php
/**
 * Template Name: Login Page and Profile page
 */

get_header(); ?>

    <!-- section -->
    <section class="irf_loginForm">
        <?php
        global $user_login;

        // In case of a login error.
        if (isset($_GET['login']) && $_GET['login'] == 'failed') : ?>
            <div class="irf_error">
                <p><?php _e('FAILED: Try again!', 'innova_reservation_form'); ?></p>
            </div>
        <?php
        endif;

        // If user is already logged in.
        if (is_user_logged_in()) : ?>

            <div class="irf_logout">
                <div class="container-login100">
                    <div class="wrap-profile100 p-l-85 p-r-85 p-t-55 p-b-55">
                        <?php
                        _e('Hello', 'innova_reservation_form');
                        echo $user_login;
                        ?>

                        </br>

                        <?php _e('You are already logged in.', 'innova_reservation_form'); ?>
                        <br>
                        <div>
                            <a href="<?php echo wp_logout_url(); ?>" class="txt3"><?php _e('Logout', 'innova_reservation_form'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        // If user is not logged in.
        else: ?>
            <div class="container-login100">
                <div class="wrap-login100 p-l-85 p-r-85 p-t-55 p-b-55">
                    <form class="login100-form validate-form flex-sb flex-w" action="" method="post">
                        <input type="hidden" name="action" value="irf_login_action"/>
                        <span class="login100-form-title p-b-32">
						Login Page
					</span>

                        <span class="txt1 p-b-11">
						Username or Email Address
					</span>
                        <div class="wrap-input100 validate-input m-b-36" data-validate="Username is required">
                            <input class="input100" type="text" name="log">
                            <span class="focus-input100"></span>
                        </div>

                        <span class="txt1 p-b-11">
						Password
					</span>
                        <div class="wrap-input100 validate-input m-b-12" data-validate="Password is required">
                            <input class="input100" type="password" name="pwd">
                            <span class="focus-input100"></span>
                        </div>

                        <div class="flex-sb-m w-full p-b-48">
                            <div class="contact100-form-checkbox">
                                <input class="input-checkbox100" id="ckb1" type="checkbox" name="rememberme">
                                <label class="label-checkbox100" for="ckb1">
                                    Remember me
                                </label>
                            </div>

                            <div>
                                <a href="#" class="txt3">
                                    Forgot Password?
                                </a>
                            </div>
                            <div>
                                <a href="<?php echo get_page_link(get_page_by_title('register')->ID); ?>" class="txt3">Register</a>
                            </div>
                        </div>

                        <div class="container-login100-form-btn">
                            <button class="login100-form-btn">
                                Login
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        <?php
        endif;
        ?>


    </section>
    <!-- /section -->

<?php get_footer(); ?>