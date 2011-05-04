<?php
/*
Plugin Name: MoviesForMyBlog
Plugin URI: http://moviesformyblog.com
Description: Displays the most recent instant play movies and/or DVD's and Blu-Ray's shipped out on the netflix account
and ratings.

Version: 1.2
Author: Jason Deatherage
Author URI: http://racheljason.com
License: GPL2

Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA


 */

//adds the script to the header section
add_action( 'init', 'mfmb_slider_scripts' );


/*MoviesForMyBlog Class*/

class MoviesForMyBlog extends WP_Widget {

    /*Constructor*/
    function MoviesForMyBlog() {
        parent::WP_Widget(false,$name = 'MoviesForMyBlog');

        // widget actual processes
    }

    function form($instance) {
         $title = esc_attr($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>">
            <?php _e('Title:'); ?>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text"
                   value="<?php echo $title; ?>" />
                </label>
            </p>


        <?php


        // outputs the options form on admin
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);

        return $instance;
        // processes widget options to be saved
    }

    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);

               /* Before widget (defined by themes). */
               echo $before_widget;

                   if ( $title )
                       echo $before_title . $title . $after_title; ?>



    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery("#mfmbslider").easySlider({
                prevText: 'Previous',
                nextText: 'Next',
                controlsFade: true,
                speed: 300
            });
            //jQuery("li:before").addClass("mfmbcustomlist");
        });
    </script>



    <?php //if (get_option('mfmb_instant_data') == true || get_option('mfmb_dvd_data') == true )
        //This section needs to be update for some additional logic once filtering is put in.
          if (get_option('mfmb_dvd_check') != false || get_option('mfmb_instant_check') != false)
          {
              if (get_option('mfmb_dvd_check') == "FALSE" && get_option('mfmb_instant_check') == "FALSE")
              {?>
                <table>
                <tr>
                    <td colspan="2">
                    <b>Please login as an Administrator and configure the MoviesForMyBlog plugin filters.</b>
                    </td>
                </tr>
              </table>

        <?php }
              else
              {
                  //figures out what movie types to display by default it is instant play
                  if (isset($_GET['mfmb']))
                  {
                      $mfmb_query_type = $_GET['mfmb'];
                  }
                  else
                  {
                      if (get_option('mfmb_instant_check') == 'TRUE')
                      {
                        $mfmb_query_type = 'instant';
                      }
                      else
                      {
                          $mfmb_query_type = 'dvd';
                      }

                  }

                  //Checks to see if the data needs to be refreshed
                  if ((time() - get_option('mfmb_data_refresh_time')) > (get_option('mfmb_data_refresh') * 3600))
                  {

                        if (get_option('mfmb_instant_check') == 'TRUE')
                        {
                            mfmb_RefreshInstantPlayData();
                            mfmb_RefreshInstantPlayRatings();
                        }
                        else
                        {
                            update_option('mfmb_instant_data','');
                            update_option('mfmb_instant_data_ratings','');
                        }

                        if (get_option('mfmb_dvd_check') == 'TRUE')
                        {
                            mfmb_RefreshDVDMovieData();
                            mfmb_RefreshDVDMovieRatings();
                        }
                        else
                        {
                            update_option('mfmb_dvd_data','');
                            update_option('mfmb_dvd_data_ratings','');
                        }

                  }
                  if ($mfmb_query_type == 'instant')
                  {



                      $mfmb_query_data_name = 'mfmb_instant_data';
                      $mfmb_query_ratings_name = 'mfmb_instant_data_ratings';
                  }
                  elseif ($mfmb_query_type == 'dvd')
                  {
                      $mfmb_query_data_name = 'mfmb_dvd_data';
                      $mfmb_query_ratings_name = 'mfmb_dvd_data_ratings';
                  }

                     //Loads the movie data
                     $mfmb_dom_data = new DOMDocument("1.0");
                     $mfmb_dom_data->loadXML(get_option($mfmb_query_data_name));
                     $mfmb_dom_data->formatOutput=true;

                     //Loads the instant play movie data into a DOMXPath object
                    $mfmb_xpath = new DOMXPath($mfmb_dom_data);
                    $mfmb_xpath_query = '//rental_history_item';
                    $mfmb_total =  $mfmb_xpath->query($mfmb_xpath_query)->length;

                    //Loads the instant play ratings data
                     $mfmb_dom_data_ratings = new DOMDocument("1.0");
                     $mfmb_dom_data_ratings->loadXML(get_option($mfmb_query_ratings_name));
                     $mfmb_dom_data_ratings->formatOutput=true;

                      //Loads the instant play movie ratings into a DOMXPath object
                    $mfmb_ratings_xpath = new DOMXPath($mfmb_dom_data_ratings);
                    $mfmb_ratings_xpath_query = '//ratings_item';
                    $mfmb_ratings_total =  $mfmb_ratings_xpath->query($mfmb_ratings_xpath_query)->length;

                     $mfmb_queryTitle = '/rental_history/rental_history_item/title';
                     $mfmb_queryMovieArt = '/rental_history/rental_history_item/box_art';
                     $mfmb_querySynopsis = '/rental_history/rental_history_item/link/synopsis';
                     $mfmb_queryRatings = '//ratings_item';

                     $mfmb_Title_entries = $mfmb_xpath->query($mfmb_queryTitle);
                     $mfmb_MovieArt_entries = $mfmb_xpath->query($mfmb_queryMovieArt);
                     $mfmb_Synopsis_entries = $mfmb_xpath->query($mfmb_querySynopsis);

                     $mfmb_Ratings_entries = $mfmb_ratings_xpath->query($mfmb_queryRatings);

                     $i = 0;
                     $mfmb_rating = 0;
                     $mfmb_rating_pos = 0;
                  ?>
                    <div id="mfmbslider">
                        <ul>

                        <?php while ($i < $mfmb_total) { ?>
                            <li>
                                <table id="mfmb_table" class="mfmb_movie_display">
                                    <tr><td id="mfmb_movie_display_title" colspan="2" align="center"><?php echo $mfmb_Title_entries->item($i)->getAttribute('regular'); ?></td></tr>
                                    <tr>
                                        <td valign="top" rowspan="2"><img alt="" src="<?php echo $mfmb_MovieArt_entries->item($i)->getAttribute('medium'); ?>"></td>
                                        <td style="vertical-align: top; padding-left: 2px;">
                                            <?php
                                                 //checks if the films has been rated
                                                 if ($mfmb_Ratings_entries->item($i)->childNodes->item(1)->nodeName == 'user_rating')
                                                 {
                                                     if ($mfmb_Ratings_entries->item($i)->childNodes->item(1)->nodeValue == '')
                                                     {

                                                         $mfmb_rating = 0;
                                                         $mfmb_newrating = $mfmb_rating / 5 * 80;
                                                         $mfmb_rating_pos = 0;
                                                         ?>
                                                             <div><b>Not Interested:</b></div>
                                               <?php }
                                                     else
                                                     {

                                                         $mfmb_rating = (int)$mfmb_Ratings_entries->item($i)->childNodes->item(1)->nodeValue;
                                                         $mfmb_newrating = $mfmb_rating / 5 * 80;
                                                         $mfmb_rating_pos = -32;
                                                         ?>
                                                            <div><b>I rated this:</b></div>
                                              <?php   } ?>

                                            <?php
                                                     //echo $mfmb_newrating;
                                                 }
                                                 else
                                                 {
                                                      $mfmb_rating = (float)$mfmb_Ratings_entries->item($i)->childNodes->item(1)->nodeValue;
                                                      $mfmb_newrating = $mfmb_rating / 5 * 80;
                                                      $mfmb_rating_pos = -16;
                                                     ?>
                                                     <div><b>Predicted Rating:</b></div>
                                              <?php   }



                                                ?>
                                            <div id="mfmb_ratingdiv"  title="<?php echo $mfmb_rating; ?> out of 5 stars">
                                                <div id="mfmb_ratingdiv1" style="width:<?php echo $mfmb_newrating?>px; background-position: 0px <?php echo $mfmb_rating_pos; ?>px; ">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td id="titlesynopsis" class="titlepad">
                                            <span title="<?php echo strip_tags($mfmb_Synopsis_entries->item($i)->nodeValue); ?>"><?php echo string_limit_words($mfmb_Synopsis_entries->item($i)->nodeValue,15); ?></span></td>
                                    </tr>
                                </table>
                            </li>
                        <?php
                             $i++;
                             } ?>
                        </ul>
                    </div>

            <table class="mfmb_movie_display">
                        <?php if (get_option('mfmb_dvd_check') == "TRUE" && get_option('mfmb_instant_check') == "TRUE")
                         { ?>

                        <tr>
                            <td class="viewtitle">
                                <a href="<?php echo get_option('siteurl') ?>?mfmb=instant">Instant</a> |
                                <a href="<?php echo get_option('siteurl') ?>?mfmb=dvd">DVD</a>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td id="deliveredby">
                            <div id="myMovieLocation">
                             <a href="http://www.netflix.com" Target="_blank">delivered by Netflix</a><br />
                            </div>
                    </td></tr>
                    </table>
              <?php }
        }

      else
      { ?>

        <table>
        <tr><td colspan="2">
                <b>Please login as an Administrator and setup the MoviesForMyBlog plugin.</b>
            </td>
        </tr>
      </table>

     <?php }


         ?>

                       <?php

                   /*if ( $jason )
                       echo '<p>' . $jason . '</p>';
                       $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestMovieGenres';
                       $response = file_get_contents($request);
                       echo $response;*/

               /* After widget (defined by themes). */
               echo $after_widget;


     }


}

// register MoviesForMyBlog widget
add_action('widgets_init', create_function('', 'return register_widget("MoviesForMyBlog");'));


//Admin Menu
add_action('admin_menu', 'MoviesForMyBlog_plugin_menu');

function MoviesForMyBlog_plugin_menu() {


    add_options_page('MoviesForMyBlog Settings', 'MFMB Settings','manage_options', 'mfmb_handle', 'mfmb_settings' );
    // Add a submenu to the custom top-level menu:
    //add_submenu_page('mfmb_handle','MFMB Filters','MFMB Filters', 'manage_options', 'mfmb_sub', 'mfmb_filters');



}
//This is the function that displays all of the main settings for MoviesForMyBlog
function mfmb_settings() {

  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  // variables for the field and option names
  //************Oauth Tokens*******************************
  //All of the Oauth security codes for your account
  //$mfmb_consumerkey DO NOT CHANGE otherwise no queries can be made to the webservice.
  $mfmb_rt_consumerkey = 'qdgmvxswkxqqhxhdnhantdst';
  $mfmb_rt_consumerkey_name = 'mfmb_rt_consumerkey';
  update_option( $mfmb_rt_consumerkey_name, $mfmb_rt_consumerkey );


  //Login tokens allow you to request your login tokens securely and verfiy who you are with netflix
  $mfmb_lt_oauth_token = '';
  $mfmb_lt_oauth_token_name = 'mfmb_lt_oauth_token';
  $mfmb_lt_oauth_token_secret = '';
  $mfmb_lt_oauth_token_secret_name = 'mfmb_lt_oauth_token_secret';
  //$mfmb_lt_application_name DO NOT CHANGE otherwise authorization won't work
  $mfmb_lt_application = 'Movies+For+My+Blog';
  $mfmb_lt_application_name = 'mfmb_lt_application';
  update_option($mfmb_lt_application_name, $mfmb_lt_application);

  //Authorized Token
  $mfmb_auth_oauth_token = '';
  $mfmb_auth_oauth_token_name = 'mfmb_auth_oauth_token';


  //Access Tokens
  $mfmb_access_oauth_token = '';
  $mfmb_access_user_id = '';
  $mfmb_access_oauth_token_secret = '';

  $mfmb_access_oauth_token_name = 'mfmb_access_oauth_token';
  $mfmb_access_user_id_name = 'mfmb_access_user_id';
  $mfmb_access_oauth_token_secret_name = 'mfmb_access_oauth_token_secret';
 //************Oauth Tokens*******************************

  //***********Display/Quantity Settings**********************
  $mfmb_dvd_check = '';
  $mfmb_dvd_check_name = 'mfmb_dvd_check';

  $mfmb_instant_check = '';
  $mfmb_instant_check_name = 'mfmb_instant_check';

  $mfmb_dvd_quantity = '';
  $mfmb_dvd_quantity_name = 'mfmb_dvd_quantity';

  $mfmb_instant_quantity = '';
  $mfmb_instant_quantity_name = 'mfmb_instant_quantity';
  //how often new data should be pulled from the web service
  $mfmb_data_refresh = '';
  $mfmb_data_refresh_name = 'mfmb_data_refresh';

  $mfmb_data_refresh_time_name = 'mfmb_data_refresh_time';
  //***********Display/Quantity Settings**********************


  $hidden_field_name = 'mt_submit_hidden';


    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    $mfmb_rt_consumerkey = get_option($mfmb_rt_consumerkey_name);
    $mfmb_lt_oauth_token = get_option($mfmb_lt_oauth_token_name);
    $mfmb_lt_oauth_token_secret = get_option($mfmb_lt_oauth_token_secret_name);
    $mfmb_auth_oauth_token = get_option($mfmb_auth_oauth_token_name);
    $mfmb_lt_application = get_option($mfmb_lt_application_name);
    $mfmb_access_oauth_token = get_option($mfmb_access_oauth_token_name);
    $mfmb_access_user_id = get_option($mfmb_access_user_id_name);
    $mfmb_access_oauth_token_secret = get_option($mfmb_access_oauth_token_secret_name);

    $mfmb_dvd_check = get_option($mfmb_dvd_check_name);
    $mfmb_instant_check = get_option($mfmb_instant_check_name);

    $mfmb_dvd_quantity = get_option($mfmb_dvd_quantity_name);
    $mfmb_instant_quantity = get_option($mfmb_instant_quantity_name);

    $mfmb_data_refresh = get_option($mfmb_data_refresh_name);

    //This processes if the get login tokens link was clicked
    if (isset($_GET["mfmbrun"]) && $_GET["mfmbrun"] == 'logintokens') {

        //queries mfmb web service to get the login tokens
        $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestLoginToken?AppKey=' . $mfmb_rt_consumerkey;

        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
         // grab URL and pass it to the browser
        $response = curl_exec($ch);

        $xml = simplexml_load_string($response);
        $mfmb_lt_strings = explode('&',$xml[0]);

        //Sets the Login Token Variable on a request
        $mfmb_lt_strings1 = explode('=',$mfmb_lt_strings[0]);
        $mfmb_lt_oauth_token = $mfmb_lt_strings1[1];
        update_option($mfmb_lt_oauth_token_name, $mfmb_lt_oauth_token);

        //Sets the Authorized Token variable which is the same as the Login OAuth Token
        $mfmb_auth_oauth_token = $mfmb_lt_strings1[1];
        update_option($mfmb_auth_oauth_token_name,$mfmb_auth_oauth_token);

        //Sets the Login Token Secret Variable on a request
        $mfmb_lt_strings1 = explode('=',$mfmb_lt_strings[1]);
        $mfmb_lt_oauth_token_secret = $mfmb_lt_strings1[1];
        update_option($mfmb_lt_oauth_token_secret_name,$mfmb_lt_oauth_token_secret);

    }

    //This processes if the get access tokens link was pressed.
    if ((isset($_GET["mfmbrun"]) && $_GET["mfmbrun"] == 'accesstokens') &&
            ($_POST[ $hidden_field_name ] != 'Y' )) {
            //queries the mfmb web service to get access tokens
            $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestAccessToken?AppKey=' . $mfmb_rt_consumerkey . '&webTokens=' . $mfmb_lt_oauth_token . '=' . $mfmb_lt_oauth_token_secret;

            $ch = curl_init();
            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
             // grab URL and pass it to the browser
            $response = curl_exec($ch);

            //loads the xml response to be able to querie it
            $xml = simplexml_load_string($response);
            $mfmb_lt_strings = explode('&',$xml[0]);
            //sets the oauth access token variable
            $mfmb_lt_strings1 = explode('=',$mfmb_lt_strings[0]);
            $mfmb_access_oauth_token = $mfmb_lt_strings1[1];
            update_option($mfmb_access_oauth_token_name,$mfmb_access_oauth_token);
            //sets the oauth access user id variable
            $mfmb_lt_strings1 = explode('=',$mfmb_lt_strings[1]);
            $mfmb_access_user_id = $mfmb_lt_strings1[1];
            update_option($mfmb_access_user_id_name,$mfmb_access_user_id);
            //sets the oauth access token secret variable
            $mfmb_lt_strings1 = explode('=',$mfmb_lt_strings[2]);
            $mfmb_access_oauth_token_secret = $mfmb_lt_strings1[1];
            update_option($mfmb_access_oauth_token_secret_name,$mfmb_access_oauth_token_secret);

    }

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        //$opt_val = $_POST[ $data_field_name ];

        //Checks to see if the DVD/Blu-Ray option is checked
        if(isset($_POST[ $mfmb_dvd_check_name ]))
         {
             $mfmb_dvd_check = 'TRUE';
         }
         else {
             $mfmb_dvd_check = 'FALSE';
         }

         //Checks to see if the Instant Play option is checked
         if(isset($_POST[ $mfmb_instant_check_name ]))
             {
             $mfmb_instant_check = 'TRUE';
         }
         else
             {
             $mfmb_instant_check = 'FALSE';
         }

        // Save the posted value in the database
        //update_option( $opt_name, $opt_val );
        update_option($mfmb_dvd_check_name,$mfmb_dvd_check);
        update_option($mfmb_instant_check_name,$mfmb_instant_check);

        $mfmb_dvd_quantity = $_POST[$mfmb_dvd_quantity_name];
        update_option($mfmb_dvd_quantity_name,$mfmb_dvd_quantity);

        $mfmb_instant_quantity = $_POST[$mfmb_instant_quantity_name];
        update_option($mfmb_instant_quantity_name,$mfmb_instant_quantity);

        $mfmb_data_refresh = $_POST[$mfmb_data_refresh_name];
        update_option($mfmb_data_refresh_name,$mfmb_data_refresh);

        if (get_option('mfmb_instant_check') == 'TRUE')
        {
            mfmb_RefreshInstantPlayData();
            mfmb_RefreshInstantPlayRatings();
        }
        else
        {
            update_option('mfmb_instant_data','');
            update_option('mfmb_instant_data_ratings','');
        }

        if (get_option('mfmb_dvd_check') == 'TRUE')
        {
            mfmb_RefreshDVDMovieData();
            mfmb_RefreshDVDMovieRatings();
        }
        else
        {
            update_option('mfmb_dvd_data','');
            update_option('mfmb_dvd_data_ratings','');
        }

        //sets time when the last data refress occured
        update_option($mfmb_data_refresh_time_name,time());
        // Put an settings updated message on the screen

        ?>
        <div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
        <?php

    }

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __('MoviesForMyBlog Plugin Settings', 'mfmb-settings') . "</h2>";
    // settings form
    ?>





<div>
<form name="form1" method="post" action="">

<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<table width="810px">
    <tr><td>
            <table class="mfmbtable">
    <tr><td class="mfmb_tblcolright"><b>Request Token</b></td></tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("Consumer Key:", 'mfmb-settings' ); _e ?></td>
        <td><input type="text" name="<?php echo $mfmb_rt_consumerkey_name; ?>" value="<?php echo $mfmb_rt_consumerkey; ?>" size="40" readonly></td>
    </tr>
    <tr><td class="mfmb_tblcolright"><b>Login Tokens</b></td></tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("OAuth Token:", 'mfmb-settings'); _e ?></td>
        <td><input type="text" id="<?php echo $mfmb_lt_oauth_token_name; ?>" value="<?php echo $mfmb_lt_oauth_token; ?>" size="40"></td>
    </tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("OAuth Token Secret:", 'mfmb-settings'); _e ?></td>
        <td><input type="text" id="<?php echo $mfmb_lt_oauth_token_secret_name; ?>" value="<?php echo $mfmb_lt_oauth_token_secret; ?>" size="40"></td>
        <td><a href="admin.php?page=mfmb_handle&mfmbrun=logintokens">Get Login Tokens</a></td>
    </tr>
    <tr><td class="mfmb_tblcolright"><b>Authorized Token</b></td></tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("OAuth Token:", 'mfmb-settings'); _e ?></td>
        <td><input type="text" id="<?php echo $mfmb_auth_oauth_token_name; ?>" value="<?php echo $mfmb_auth_oauth_token; ?>" size="40"></td>
        <?php if( $mfmb_auth_oauth_token != '') {?>
        <td><a href="<?php echo 'https://api-user.netflix.com/oauth/login?application_name=' . $mfmb_lt_application . '&oauth_callback=' . get_option("siteurl") . '&oauth_consumer_key=' . $mfmb_rt_consumerkey . '&oauth_token=' . $mfmb_lt_oauth_token;?>">Authorize Tokens</a></td>
        <?php }?>
    </tr>
    <tr><td class="mfmb_tblcolright"><b>Access Token</b></td></tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("OAuth Token:", 'mfmb-settings'); _e ?></td>
        <td><input type="text" id="<?php echo $mfmb_access_oauth_token_name; ?>" value="<?php echo $mfmb_access_oauth_token; ?>" size="40"></td>
    </tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("User ID:", 'mfmb-settings'); _e ?></td>
         <td><input type="text" id="<?php echo $mfmb_access_user_id_name; ?>" value="<?php echo $mfmb_access_user_id; ?>" size="40"></td>
    </tr>
    <tr>
        <td class="mfmb_tblcolright"><?php _e("OAuth Token Secret:", 'mfmb-settings'); _e ?></td>
         <td><input type="text" id="<?php echo $mfmb_access_oauth_token_secret_name; ?>" value="<?php echo $mfmb_access_oauth_token_secret; ?>" size="40"></td>
        <td><a href="admin.php?page=mfmb_handle&mfmbrun=accesstokens">Get Access Tokens</a></td>
    </tr>
    </table>
        </td>
        <td valign="top" style="padding-left: 5px;">
            <table class="mfmbtable" width="250px">
                <tr>
                    <td class="mfmbsettingtitle" colspan="2">What to Display/Quantity</td>
                </tr>
                <tr><?php if($mfmb_dvd_check == "TRUE") {?>
                        <td><input type="checkbox" name="<?php echo $mfmb_dvd_check_name; ?>" value="<?php echo $mfmb_dvd_check; ?>" checked />DVD/Blu-Ray</td>
                    <?php }
                    else
                        {?>
                    <td><input type="checkbox" name="<?php echo $mfmb_dvd_check_name; ?>" value="<?php echo $mfmb_dvd_check; ?>" />DVD/Blu-Ray</td>
                    <?php }
                    if ($mfmb_instant_check == "TRUE") {
                    ?>
                    <td><input type="checkbox" name="<?php echo $mfmb_instant_check_name; ?>" value="<?php echo $mfmb_instant_check; ?>" checked />Instant Play</td>
                    <?php }
                    else {?>
                    <td><input type="checkbox" name="<?php echo $mfmb_instant_check_name; ?>" value="<?php echo $mfmb_instant_check; ?>" />Instant Play</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td><input type="text" name="<?php echo $mfmb_dvd_quantity_name; ?>" value="<?php echo $mfmb_dvd_quantity; ?>" size="4"></td>
                    <td><input type="text" name="<?php echo $mfmb_instant_quantity_name; ?>" value="<?php echo $mfmb_instant_quantity; ?>" size="4"></td>
                </tr>
                <tr>
                    <td class="mfmbsettingtitle" colspan="2">Data Refresh</td>
                </tr>
                <tr>
                    <td><input type="text" name="<?php echo $mfmb_data_refresh_name; ?>" value="<?php echo $mfmb_data_refresh; ?>" size="4"> Hour(s)</td>

                </tr>
                <tr><td style="font-size: 9px;" colspan="2">*Maximum requests of 4/sec and 5000/day</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="U6XN67BLCPNZE">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

</div>

<?php
}
//This is the function that displays all of the filters for MoviesForMyBlog
function mfmb_filters() {

  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  echo '<div class="wrap">';
  echo '<p>Here is where the form would go if I actually had options for MoviesForMyBlog Filters.</p>';
  echo '</div>';
}

//Queries the Instant Play Data and ratings
function mfmb_RefreshInstantPlayData()
{
    //queries mfmb web service to get the last instant play movies
   $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestLastMoviesWatched?AppKey=' . get_option('mfmb_rt_consumerkey') .
    '&webClientTokens=' . get_option('mfmb_access_oauth_token') . '=' . get_option('mfmb_access_user_id') . '=' .
    get_option('mfmb_access_oauth_token_secret') . '&intInstantQty=' . get_option('mfmb_instant_quantity');

    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
     // grab URL and pass it to the browser
    $response = curl_exec($ch);

    $dom = new DOMDocument("1.0");
    $dom->loadXML($response);
    $dom->formatOutput=true;

    $items = $dom->getElementsByTagName('string');

    update_option('mfmb_instant_data',$items->item(0)->nodeValue);

}
function mfmb_RefreshInstantPlayRatings()
{
    $dom = new DOMDocument("1.0");
    $dom->loadXML(get_option('mfmb_instant_data'));
    $dom->formatOutput=true;

    $mfmb_instant_xpath = new DOMXPath($dom);
    $mfmb_queryInstantMovies = '//rental_history_item/link[1]';
    //$mfmb_instant_total =  $mfmb_instant_xpath->query($mfmb_queryInstantMovies)->length;

    $mfmb_entries = $mfmb_instant_xpath->query($mfmb_queryInstantMovies);

     foreach ($mfmb_entries as $entry) {
        $mfmb_InstantMovieURL = $mfmb_InstantMovieURL . $entry->getAttribute('href') . ',';

     }

    //queries mfmb web service to get the last instant play movies ratings
    $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestMovieRatings?AppKey=' . get_option('mfmb_rt_consumerkey') .
    '&webClientTokens=' . get_option('mfmb_access_oauth_token') . '=' . get_option('mfmb_access_user_id') . '=' .
    get_option('mfmb_access_oauth_token_secret') . '&MovieTitles=' . $mfmb_InstantMovieURL;

    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
     // grab URL and pass it to the browser
    $response = curl_exec($ch);

    $dom1 = new DOMDocument("1.0");
    $dom1->loadXML($response);
    $dom1->formatOutput=true;

    $items = $dom1->getElementsByTagName('string');

    update_option('mfmb_instant_data_ratings',$items->item(0)->nodeValue);

}

//Queries the DVD/Blu-Ray rentals and the ratings of the items
function mfmb_RefreshDVDMovieData()
{

     //queries mfmb web service to get the last DVD/Blu-Ray movies rented
   $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestLastDiscsRented?AppKey=' . get_option('mfmb_rt_consumerkey') .
    '&webClientTokens=' . get_option('mfmb_access_oauth_token') . '=' . get_option('mfmb_access_user_id') . '=' .
    get_option('mfmb_access_oauth_token_secret') . '&intDiscsQty=' . get_option('mfmb_dvd_quantity');

    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
     // grab URL and pass it to the browser
    $response = curl_exec($ch);

    $dom = new DOMDocument("1.0");
    $dom->loadXML($response);
    $dom->formatOutput=true;

    $items = $dom->getElementsByTagName('string');

    update_option('mfmb_dvd_data',$items->item(0)->nodeValue);
}

//Queries the DVD Movies Ratings
function mfmb_RefreshDVDMovieRatings()
{
    $dom = new DOMDocument("1.0");
    $dom->loadXML(get_option('mfmb_dvd_data'));
    $dom->formatOutput=true;

    $mfmb_dvd_xpath = new DOMXPath($dom);
    $mfmb_queryDVDMovies = '//rental_history_item/link[1]';

    $mfmb_dvd_entries =  $mfmb_dvd_xpath->query($mfmb_queryDVDMovies);

     foreach ($mfmb_dvd_entries as $dvd_entry) {
        $mfmb_DVDMovieURL = $mfmb_DVDMovieURL . $dvd_entry->getAttribute('href') . ',';

     }


      //queries mfmb web service to get the movie ratings for DVD/Blu-Ray
     $request = 'http://moviesformyblog.com/netflixv1/service.asmx/RequestMovieRatings?AppKey=' . get_option('mfmb_rt_consumerkey') .
     '&webClientTokens=' . get_option('mfmb_access_oauth_token') . '=' . get_option('mfmb_access_user_id') . '=' .
     get_option('mfmb_access_oauth_token_secret') . '&MovieTitles=' . $mfmb_DVDMovieURL;

     $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $request);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
     // grab URL and pass it to the browser
    $response = curl_exec($ch);

    $dom1 = new DOMDocument("1.0");
    $dom1->loadXML($response);
    $dom1->formatOutput=true;

    $items = $dom1->getElementsByTagName('string');

    update_option('mfmb_dvd_data_ratings',$items->item(0)->nodeValue);

}


//function to add the easy slider script
function mfmb_slider_scripts() {
	wp_enqueue_script( 'easyslider', plugins_url( 'easyslider.js', __FILE__ ),
		array('jquery'), false, false);
        wp_enqueue_style(mfmbstyles,  plugins_url( 'mfmbstyles.css', __FILE__ ));
}

function string_limit_words($string, $word_limit)
{
  $words = explode(' ', $string, ($word_limit + 1));
  if(count($words) > $word_limit)
  array_pop($words);
  return implode(' ', $words) . '...';
}
?>