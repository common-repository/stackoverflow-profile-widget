<?php
/**
Plugin Name: StackOverflow Profile
Plugin URI: http://wordpress.org/plugins/stackoverflow-profile-widget/
Description: A widget that displays the StackOverflow Profile and selected answers for a particular user.
Version: 2014.07.03
Author: dpchiesa
Author URI: http://www.dinochiesa.net
Donate URI: http://dinochiesa.github.io/SOProfile-Donate.html
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

// prevent direct access
function soprofile_safeRedirect($location, $replace = 1, $Int_HRC = NULL) {
    if(!headers_sent()) {
        header('location: ' . urldecode($location), $replace, $Int_HRC);
        exit;
    }
    exit('<meta http-equiv="refresh" content="4; url=' .
         urldecode($location) . '"/>');
    return;
}

if(!defined('WPINC')){
    soprofile_safeRedirect("http://" . $_SERVER["HTTP_HOST"]);
}

require_once 'lib/soClient.php';
require_once 'lib/onDateSorter.php';
//require_once 'lib/Logger.php';


class SOProfileWidget extends WP_Widget {
    private $idBase = 'soprofile-widget';
    private $translateDomain = 'soprofile-widget';
    private $basePath;
    private $widgetSettings;
    private $sortOptions;
    private $stackUser;
    private $stackActivity;

    public function __construct() {
        $this->basePath = plugin_dir_path( __FILE__ );
        $this->sortOptions = array(
            'score-asc' => $this->translate('Hightest score'),
            'latest' => $this->translate('Latest'),
            'newest' => $this->translate('Newest'),
        );
        $this->whatOptions = array(
            'answers' => $this->translate('Answers'),
            'reputation' => $this->translate('Upvotes')
        );

        // Widget settings
        $widgetOps = array(
            'classname' => $this->translateDomain,
            'description' => $this->translate('StackOverflow Profile'));

        // Widget control settings
        $controlOps = array('id_base' => $this->idBase, 'width' => 251);

        // Create the widget
        $this->WP_Widget($this->idBase, $this->translate('StackOverflow Profile Widget'), $widgetOps, $controlOps);

        // add css only when necessary
        if ( is_active_widget(false, false, $this->id_base) ) {
            wp_enqueue_style( 'soprofile' );
        }
    }

    /**
     *
     * @param <type> $args
     * @param <type> $instance
     * @see WP_Widget::widget
     */
    function widget($args, $instance) {
        extract($args);
        $this->widgetSettings = $instance;

        $maxAge = @max( (int) $this->widgetSettings['cacheActivity'],
                        (int) $this->widgetSettings['cacheUser']);

        SOEntity::clearCacheFiles($maxAge);

        $soUser      = $this->getStackUser();         // possibly a remote request
        $badgeCounts = $soUser->getBadgeCounts();
        $activity    = $this->getStackActivity()->getItem(); // possibly a remote request
        $answers     = $activity['items'];
        $total       = count($answers);
        $maxAnswers  = (int) $instance['totalAnswers'];
        $title       = esc_html($instance['title']);

        if ($this->widgetSettings['what'] == 'reputation') {
            // When retrieving reputation, you don't get
            // title, last_updated_date, and other metadata.
            // Therefore, we need to retrieve metadata on all those questions.

            $repEntries = $answers;
            $ids = array();
            foreach($repEntries as $entry) {
                // there can be duplicates in the list of reputation entries.
                $id = (int) $entry['post_id'];
                if (($entry['post_type'] == 'answer') && !in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }

            $set = new SOAnswerset($ids,
                                   (int) $this->widgetSettings['cacheActivity'],
                                   $this->widgetSettings['appKey'] );

            $activity = $set->getItem();
            $answers  = $activity['items'];

            $sorter = new OnDateSorter($repEntries);
            usort($answers, array($sorter, 'compare_on_date'));
        }

        // Before widget (defined by themes)
        echo $before_widget;

        include $this->basePath . 'views/display.php';

        // After widget (defined by themes)
        echo $after_widget;
    }

    /**
     *
     * @see WP_Widget::update
     * @return <type>
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title']         = esc_html($new_instance['title']);
        $instance['userid']        = strip_tags($new_instance['userid']);
        $instance['what']          = strip_tags($new_instance['what']);
        $instance['totalAnswers']  = strip_tags($new_instance['totalAnswers']);
        $instance['appKey']        = strip_tags($new_instance['appKey']);
        $instance['sort']          = esc_html($new_instance['sort']);
        $instance['cacheUser']     = strip_tags($new_instance['cacheUser']);
        $instance['cacheActivity'] = strip_tags($new_instance['cacheActivity']);

        SOEntity::clearCacheFiles(0); // 0=all

        return $instance;
    }

    function update_callback($widget_args = 1) {
        if (isset($_POST['delete_widget']) && $_POST['delete_widget']) {
            // Delete the settings for this instance of the widget
            if (isset($_POST['the-widget-id'])) {
                $this->removeCacheFiles();
            }
        }

        return parent::update_callback($widget_args);
    }

    /*
     * @see WP_Widget::form
     */
    function form($instance) {
        $instance = wp_parse_args((array) $instance,
                                  array('title'         => '',
                                        'userid'        => '',
                                        'totalAnswers'  => '5',
                                        'what'          => 'reputation',
                                        'sort'          => '',
                                        'cacheActivity' => '12',
                                        'cacheUser'     => '120',
                                        'appKey'        => ''));

        $title         = esc_html($instance['title']);
        $userId        = esc_html($instance['userid']);
        $total         = esc_html($instance['totalAnswers']);
        $what          = esc_html($instance['what']);
        $sort          = esc_html($instance['sort']);
        $cacheActivity = esc_html($instance['cacheActivity']);
        $cacheUser     = esc_html($instance['cacheUser']);
        $appKey        = esc_html($instance['appKey']);

        $sortOptions = $this->sortOptions;
        $whatOptions = $this->whatOptions;

        include $this->basePath . 'views/form.php';
    }


    private function getAnswerUrl($answer) {
        return 'http://stackoverflow.com/questions/' .
            $answer['question_id'] .
            '/' .
            $answer['answer_id'] .
            '#' .
            $answer['answer_id'];
    }

    private function getStackActivity() {
        if (!isset($this->stackActivity)) {
            $this->stackActivity =
            new SOActivity( $this->widgetSettings['userid'],
                            $this->widgetSettings['what'],
                            $this->widgetSettings['totalAnswers'],
                            $this->widgetSettings['sort'],
                            (int) $this->widgetSettings['cacheActivity'],
                            $this->widgetSettings['appKey'] );
        }

        return $this->stackActivity;
    }

    private function getStackUser() {
        if (!isset($this->stackUser)) {
            $this->stackUser = new SOUser( $this->widgetSettings['userid'],
                                           (int) $this->widgetSettings['cacheUser'],
                                           $this->widgetSettings['appKey'] );
        }
        return $this->stackUser;
    }


    private function translate($string) {
        return __($string, $this->translateDomain);
    }
}


add_action( 'wp_enqueue_scripts', 'soprofile_load_css' );

function soprofile_load_css() {
  wp_register_style('soprofile', plugins_url('styles.css', __FILE__) );
}

add_action('widgets_init', 'soprofile_init_widget');

function soprofile_init_widget() {
    register_widget('SOProfileWidget');
}
