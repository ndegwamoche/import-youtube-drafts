<?php

/*
Plugin Name: Import Youtube Drafts
Description: A plugin that imports YouTube videos as draft posts on your website
Version: 1.0
Author: Moche
Author URI: https://www.linkedin.com/in/ndegwer/
*/

if (!defined('ABSPATH')) exit;

class ImportYoutubeDrafts
{
    function __construct()
    {
        add_action("admin_menu", array($this, "adminLink"));
        add_action("admin_init", array($this, "adminSettings"));
        add_action('admin_enqueue_scripts', array($this, "enqueue_progress_bar_script"));
    }

    function enqueue_progress_bar_script()
    {
        wp_enqueue_script('import-progress-bar', plugin_dir_url(__FILE__) . 'js/import-progress-bar.js', array(), '1.0', true);
    }

    function adminSettings()
    {
        add_settings_section("iyd_first_section", null, null, "import-youtube-drafts");

        add_settings_field("iyd_categories", "Category", array($this, "categoryHTML"), "import-youtube-drafts", "iyd_first_section");
        register_setting("importyoutubedrafts", "iyd_categories", array("sanitize_callback" => array($this, "sanitizeCategory"), "default" => "0"));

        add_settings_field("iyd_searchterm", "Search Term", array($this, "searchTermHTML"), "import-youtube-drafts", "iyd_first_section");
        register_setting("importyoutubedrafts", "iyd_searchterm", array("sanitize_callback" => "sanitize_text_field", "default" => "Rechargeable Gadgets"));

        add_settings_field("iyd_apikey", "API Key", array($this, "apiKeyHTML"), "import-youtube-drafts", "iyd_first_section");
        register_setting("importyoutubedrafts", "iyd_apikey", array("sanitize_callback" => "sanitize_text_field", "default" => "0"));

        add_settings_field("iyd_maxresults", "Max Results", array($this, "maxResultsHTML"), "import-youtube-drafts", "iyd_first_section");
        register_setting("importyoutubedrafts", "iyd_maxresults", array("sanitize_callback" => "sanitize_text_field", "default" => "20"));

        add_settings_field("iyd_dateafter", "Date After", array($this, "dateAfterHTML"), "import-youtube-drafts", "iyd_first_section");
        register_setting("importyoutubedrafts", "iyd_dateafter", array("sanitize_callback" => "sanitize_text_field", "default" => ""));

        add_settings_field("iyd_publish", "Publish", array($this, "publishHTML"), "import-youtube-drafts", "iyd_first_section");
        register_setting("importyoutubedrafts", "iyd_publish", array("sanitize_callback" => "sanitize_text_field", "default" => "draft"));
    }

    function adminLink()
    {
        add_menu_page("Import YouTube Drafts Settings", "Import YouTube", "manage_options", "import-youtube-drafts", array($this, "runImportHTML"), "dashicons-database-import", 100);
    }

    function runImportHTML()
    { ?>
        <div class="wrap">

            <h1>Import YouTube Drafts</h1>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_POST["runsubmit"] == "true") {
                    //$this->enqueue_progress_bar_script();
                    $this->runImport();
                } else {
                    echo "error";
                };
            }
            ?>

            <form action="options.php" method="post">
                <?php
                settings_fields("importyoutubedrafts");
                do_settings_sections("import-youtube-drafts");
                submit_button();
                ?>
                <input type="hidden" name="runsubmit" value="true" />
                <button type="submit" formaction="#" class="button button-primary">
                    Start Importing...
                </button>
            </form>
        </div>

    <?php }

    function dateAfterHTML()
    { ?>
        <input type="date" name="iyd_dateafter" value="<?php echo get_option("iyd_dateafter") ?>" />
    <?php }

    function maxResultsHTML()
    { ?>
        <input type="text" name="iyd_maxresults" value="<?php echo get_option("iyd_maxresults") ?>" />
    <?php }

    function apiKeyHTML()
    { ?>
        <input type="radio" name="iyd_apikey" value="AIzaSyBSoEcnGGu_1kc4Wz-p0mpVGIml0H5nOow" <?php checked(get_option("iyd_apikey"), "AIzaSyBSoEcnGGu_1kc4Wz-p0mpVGIml0H5nOow") ?>>
        <label>AIzaSyBSoEcnGGu_1kc4Wz-p0mpVGIml0H5nOow</label><br>
        <input type="radio" name="iyd_apikey" value="AIzaSyC74JNu-uvreWzUiCjXhQvuLvCn8Zk-IkU" <?php checked(get_option("iyd_apikey"), "AIzaSyC74JNu-uvreWzUiCjXhQvuLvCn8Zk-IkU") ?>>
        <label>AIzaSyC74JNu-uvreWzUiCjXhQvuLvCn8Zk-IkU</label><br>
        <input type="radio" name="iyd_apikey" value="AIzaSyD7aNTwEtq-ku7qNEIRtnnd3mA1Yp6dMnI" <?php checked(get_option("iyd_apikey"), "AIzaSyD7aNTwEtq-ku7qNEIRtnnd3mA1Yp6dMnI") ?>>
        <label>AIzaSyD7aNTwEtq-ku7qNEIRtnnd3mA1Yp6dMnI</label><br>
        <input type="radio" name="iyd_apikey" value="AIzaSyCdUBfmfNy-5HiKptAG3UlYWdTa0Mo3Tak" <?php checked(get_option("iyd_apikey"), "AIzaSyCdUBfmfNy-5HiKptAG3UlYWdTa0Mo3Tak") ?>>
        <label>AIzaSyCdUBfmfNy-5HiKptAG3UlYWdTa0Mo3Tak</label><br>
    <?php }

    function searchTermHTML()
    { ?>
        <input type="text" name="iyd_searchterm" value="<?php echo esc_attr(get_option("iyd_searchterm")) ?>" size="100" />
    <?php }

    function categoryHTML()
    { ?>
        <select name="iyd_categories">
            <option value="0">Please Select a Category</option>

            <?php
            $categories = get_categories(array(
                'taxonomy' => 'category',
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => false,
                'parent' => 0
            ));

            foreach ($categories as $category) {
            ?>
                <option value="<?php echo $category->term_id; ?>" <?php selected(get_option("iyd_categories"), $category->term_id) ?>><?php echo $category->name; ?></option>
            <?php } ?>
        </select>
    <?php }

    function publishHTML()
    { ?>
        <select name="iyd_publish">
            <option value="draft" <?php selected(get_option("iyd_publish"), "draft") ?>>Draft</option>
            <option value="publish" <?php selected(get_option("iyd_publish"), "publish") ?>>Publish</option>
        </select>
    <?php }

    function create_blocks($blocks = array())
    {
        $block_contents = '';
        foreach ($blocks as $block) {
            $block_contents .= create_block($block['name'], $block['attributes'], $block['content']);
        }
        return $block_contents;
    }

    function runImport()
    {
    ?>

        <style type="text/css">
            .outter {
                height: 19px;
                width: 360px;
                font-size: 11px;
                font-family: calibri;
                border: solid 1px #000;
                font-weight: bold;
                padding: 1px;
                line-height: 19px;
                margin-bottom: 15px;
                margin-top: 15px;
            }

            .inner {
                height: 19px;
                width: 0px;
                font-size: 10px;
                font-family: calibri;
                background-color: lightblue;
                line-height: 19px;
            }
        </style>

        <div class="outter" id="progress">
            <div class="inner"></div>
        </div>

<?php

        $max_results = get_option("iyd_maxresults");
        $api_key = get_option("iyd_apikey");
        $search_query = get_option("iyd_searchterm");
        $save_category = get_option("iyd_categories");
        $date_after = get_option("iyd_dateafter");
        $publish = get_option("iyd_publish");

        $option = array(
            'part' => 'snippet',
            'maxResults' => $max_results,
            'q' => $search_query,
            'key' => $api_key,
            // 'publishedAfter' => '2024-01-01T14:47:50.000Z',
            // 'regionCode' => 'TZ'
        );

        $url = "https://youtube.googleapis.com/youtube/v3/search?" . http_build_query($option, 'a', '&');
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "https://mixzote.com/");

        $json_response = curl_exec($curl);

        curl_close($curl);

        $obj = json_decode($json_response);

        $posts = 0;

        if (!$obj->error->message) {
            $totalItems = count($obj->items);
            $currentItem = 1;

            foreach ($obj->items as $playlist) {

                $percent = intval($currentItem / $max_results * 100);

                $trending_video_title = $playlist->snippet->title;
                $postDescription = $playlist->snippet->description;
                $postUrl = $playlist->snippet->thumbnails->default->url;
                $postCode = get_string_between($postUrl, "https://i.ytimg.com/vi/", "/hqdefault.jpg");
                $postDate = date("Y-m-d H:i:s", strtotime($playlist->snippet->publishedAt));

                $trending_video_url = $playlist->snippet->thumbnails->default->url;
                $trending_video_hqdefault_url = $playlist->snippet->thumbnails->sddefault->url;

                $trending_video_code = get_string_between($trending_video_url, 'i.ytimg.com/vi/', '/default.jpg');
                $trending_video_channel_title = $playlist->snippet->channelTitle;

                $content = '<!-- wp:embed {"url":"https://www.youtube.com/watch?v=' . $trending_video_code . '","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} --><figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">https://www.youtube.com/watch?v=' . $trending_video_code . '</div></figure><!-- /wp:embed -->';

                $content .= '<!-- wp:heading --><strong><h2 class="wp-block-heading">Watch Online, Listen, Share and Download Free <a href="https://mixzote.com/">' . $trending_video_title . '</a>  Videos in HD & Audio MP3</h2></strong><!-- /wp:heading -->';

                $content .= '<!-- wp:paragraph --><p>Enjoy and stay connected with us for the latest videos like <a href="https://www.youtube.com/watch?v=' . $trending_video_code . '" target="_blank" rel="dofollow"> ' . $trending_video_title . '</a> and remember to subscribe to their YouTube channel.</p><!-- /wp:paragraph -->';

                $content .= '<!-- wp:heading {"level":3} --><strong><h3 class="wp-block-heading">Which video MP4 mix or Full HD Movie & Audio MP3 is trending for <a href="https://mixzote.com/?s=' . $trending_video_channel_title . '">' . $trending_video_channel_title . '</a>  that is free to download ?</h3></strong><!-- /wp:heading -->';

                $content .= '<!-- wp:image {"align":"center","sizeSlug":"large"} --><figure class="wp-block-image aligncenter size-large"><img src="https://img.youtube.com/vi/' . $trending_video_code . '/sddefault.jpg" alt=""/></figure><!-- /wp:image -->';

                $content .= '<!-- wp:paragraph --><p>Our website is tested regularly to keep it as secure as possible. We work hard so you can download HD videos and audio MP3 from mixzote.com with no risk at all. We guarantee you that the last thing you will download when using our tool is malware.</p><!-- /wp:paragraph -->';

                // echo "<textarea cols='100'>" . $content . "</textarea>";
                // exit;

                if (!empty($trending_video_code)) {

                    echo $currentItem . " - " . $trending_video_title . $num . "<br/>";

                    if (get_page_by_title($trending_video_title) == null) {
                        $posts++;

                        kses_remove_filters();
                        $new_post = array(
                            'post_title' => $trending_video_title,
                            'post_content' => $content,
                            'post_status' => $publish,
                            'post_date' => date('Y-m-d H:i:s'),
                            'post_modified' => date('Y-m-d H:i:s'),
                            'post_author' => 1,
                            'post_type' => 'post',
                            'post_category' => array($save_category),
                            'tags_input' => array('Video')
                        );

                        $post_id = wp_insert_post($new_post);
                        add_post_meta($post_id, 'times', '1');

                        echo '<script language="javascript">document.getElementById("progress").innerHTML="<div style=\"width:' . $percent . ';background-color:lightblue;height: 19px;\">&nbsp;' . $percent . '%</div>";</script>';
                    }
                }

                echo str_repeat(' ', 1024 * 64);
                ob_flush();
                sleep(1);

                $currentItem++;
            }
        } else {
            echo json_encode(['error' => $obj->error->message]);
        }
    }

    function sanitizeCategory($input)
    {
        $categories = get_categories(array(
            'taxonomy' => 'category',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
            'parent' => 0
        ));

        $test = "1";

        foreach ($categories as $category) {
            if ($category->term_id == $input) {
                $test = "1";
                break;
            } else {
                $test = "0";
            }
        }

        if ($test == "0") {
            add_settings_error("iyd_categories", "iyd_categories_errors", "Category must be chosen from the drop-down.");
            return get_option("iyd_categories");
        } else {
            return $input;
        }
    }
}

$importYoutubeDrafts = new ImportYoutubeDrafts();
