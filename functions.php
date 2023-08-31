<?php 
 
require get_theme_file_path('/inc/like-route.php');
require get_theme_file_path('/inc/search-route.php');

    //This function registers two custom REST fields for the `post` and `note` post types.
    //This allows us to return additional data about the posts in the REST API, such as the author's name and the user's note count.
    function university_custom_rest(){
        register_rest_field('post', 'authorName', array(
          'get_callback' => function() { return get_the_author();}
        ));

        register_rest_field('note', 'userNoteCount', array(
          'get_callback' => function() { return count_user_posts(get_current_user_id(), 'note');}
        ));
    }

    add_action('rest_api_init', 'university_custom_rest');

    //This function creates a page banner.
    //This function is used to create a banner at the top of pages. The banner can be customized with a title, subtitle, and background image.
    function pageBanner($args = NULL) {
  
        if (!isset($args['title'])) {
          $args['title'] = get_the_title();
        }
       
        if (!isset($args['subtitle'])) {
          $args['subtitle'] = get_field('page_banner_subtitle');
        }
       
        if (!isset($args['photo'])) {
          if (get_field('page_banner_background_image') AND !is_archive() AND !is_home() ) {
            $args['photo'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
          } else {
            $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
          }
        }
        ?>
        <div class="page-banner">
            <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
            <div class="page-banner__content container container--narrow">
                <h1 class="page-banner__title"><?php echo $args['title']?></h1>
                <div class="page-banner__intro">
                <p><?php echo $args['subtitle'];?></p>
                </div>
            </div>
            </div>
      <?php  }
      //This function registers the necessary JavaScript and CSS files for the theme.
      //This ensures that the theme's JavaScript and CSS files are loaded on all pages.
      function university_files() {
        wp_enqueue_script('googleMap', '//maps.googleapis.com/maps/api/js?key=AIzaSyDin3iGCdZ7RPomFLyb2yqFERhs55dmfTI', NULL, '1.0', true);
        wp_enqueue_script('main-university-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
        wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
        wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
      
        wp_localize_script('main-university-js', 'universityData', array(
          'root_url' => get_site_url(),
          'nonce' => wp_create_nonce('wp_rest')
        ));
      
      }
 
      add_action("wp_enqueue_scripts", "university_files");

      //This function registers the theme's features.
      //This ensures that the theme supports features such as
      function university_features() {
          // register_nav_menu('headerMenuLocation', 'Header Menu Location');
          // register_nav_menu('footerLocationOne', 'footer Location One');
          // register_nav_menu('footerLocationTwo', 'footer Location Two');

          add_theme_support('title-tag');
          add_theme_support('post-thumbnails');
          add_image_size('professorLandscape', 400, 260, true);
          add_image_size('professorPortrait', 480, 650, true);
          add_image_size('pageBanner', 1500, 350, true);
          
      }
      add_action("after_setup_theme", "university_features");

      //This function is used to adjust the queries for the `campus`, `program`, and `event` post types.
      //This is done to change the default settings for these post types, such as the number of posts per page and the order in which they are displayed.
      function university_adjust_queries($query){

        if(!is_admin() AND is_post_type_archive('campus') AND $query->is_main_query()){
              $query->set('posts_per_page', -1);
          }

          if(!is_admin() AND is_post_type_archive('program') AND $query->is_main_query()){
              $query->set('orderby','title');
              $query->set('order', 'ASC');
              $query->set('posts_per_page', -1);
          }

          if(!is_admin()  AND is_post_type_archive('event') AND $query->is_main_query()){
              $today = date('Ymd');
              $query->set( 'meta_key', 'event_date');
              $query->set( 'orderby', 'meta_value_num');
              $query->set( 'order', 'ASC');
              $query->set( 'meta_query', array(
                  array(
                    
                    'key'=> 'event_date',
                    'compare' => '>=',
                    'value' => $today,
                    'type' => 'numeric'
                  )
                  ));
          }

      }
      add_action("pre_get_posts", "university_adjust_queries");

        //This function is used to change the default Google Maps API key for the acf/fields/google_map field.
        //This is done to use a custom Google Maps API key.
        function universityMapKey($api){
          $api['key'] ='AIzaSyCYITYn3QWr7BCkJxrwF8hVWC2cGht4eXU';
          return $api;
        }

        add_filter('acf/fields/google_map/api', 'universityMapKey');




      //This function is used to redirect subscriber accounts out of the WordPress admin dashboard and to the homepage.
      //This is done because subscribers should not have access to the admin dashboard.
      add_action('admin_init', 'redirectSubsToFrontend');
      function redirectSubsTOFrontend(){
        $ourCurrentUser=wp_get_current_user();
        
        if(count($ourCurrentUser->roles) == 1 AND $ourCurrentUser -> roles[0] == 'subscriber'){
          wp_redirect(site_url('/'));
          exit;
        }
      }



        add_action('wp_loaded', 'noSubsAdminBar');
        //This function prevents subscribers from seeing the admin bar.
        //This function gets the current user and checks if they are a subscriber. If they are, it calls the `show_admin_bar()` function with the parameter `false`, which hides the admin bar.
        //This is useful for preventing subscribers from accessing the admin area of the website.
        function noSubsAdminBar(){
          $ourCurrentUser=wp_get_current_user();
          
          if(count($ourCurrentUser->roles) == 1 AND $ourCurrentUser -> roles[0] == 'subscriber'){
            show_admin_bar(false);
          }
        }


        // Customize Login Screen
        add_filter('login_headerurl', 'ourHeaderUrl');

        //This code customizes the login screen by changing the logo, the title, and the CSS.
        //This is useful for making the login screen look more professional and branded.
        function ourHeaderUrl() {
          return esc_url(site_url('/'));
        }

        add_action('login_enqueue_scripts', 'ourLoginCSS');
        //The `add_action()` function registers a function to be called when a specific event occurs.
        //This is used to add custom CSS to the WordPress login page.
        function ourLoginCSS() {
          wp_enqueue_style('custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
          wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
          wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
          wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
        }

        add_filter('login_headertitle', 'ourLoginTitle');
        //The `add_filter()` function registers a function to be called when a specific filter is applied.
        //This is used to change the title of the WordPress login page.
        function ourLoginTitle() {
          return get_bloginfo('name');
        }


        //force note posts to be private
        add_filter('wp_insert_post_data','makeNotePrivate', 10, 2);
        //This function forces all note posts to be private.
        //This is useful for preventing users from publishing public notes.
        function makeNotePrivate($data, $postarr){
          if($data['post_type'] == 'note'){
            if(count_user_posts(get_current_user_id(), 'note') > 3 AND !$postarr['ID']){
              die('You have reached your note limit!');
            }
            
            $data['post_content'] = sanitize_textarea_field($data['post_content']);
            $data['post_title'] = sanitize_textarea_field($data['post_title']);
          }
          if($data['post_type']=='note' AND $data['post_status'] != 'trash'){
              $data['post_status'] = "private";
          }
          return $data;
        }
        ?>