<?php


abstract class SOEntity {
    /*
     * This class contains the logic that handles connecting with
     * StackExchange via the API. It also manages caching of the results.
     *
     * There are a number of derived classes representing different
     * stackexchange objects: user, answer, and so on.
     *
     */

    protected $url;
    private $cacheDir;
    private $flavor;
    private $id;
    private $cacheLife;
    protected $cacheFile;
    private $rawdata;
    private $object;

    abstract protected function getUrl();
    abstract public function getItem();

    function __construct($flavor, $cacheLife = 120, $id = null) {
        $this->cacheLife = $cacheLife;
        $this->flavor = $flavor;
        $this->id = $id;
    }

    function getCacheFile() {
        // important to set this value lazily.
        // The url may change...
        if (!isset($this->cacheFile)) {
            // the url is set implicitly by the base class
            $md5 = @md5($this->url);
            $filename = 'soprofile-' . $this->flavor . '-' ;
            if ($this->id != null)
                $filename .= $this->id . '-';

            $filename .= $md5 . '.json';
            $this->cacheFile = $this->getCacheDir() . $filename;
        }
        return $this->cacheFile;
    }

    function getCacheDir() {
        // This choose the cache dir to be a subdirectory of the wp-content
        // directory.  This is the recommended way of doing things in
        // WP plugins.
        if (!isset($this->cacheDir)) {
            $this->cacheDir = WP_CONTENT_DIR . '/cache/';
            $this->setupCacheDir();
        }
        return $this->cacheDir;
    }

    private function setupCacheDir() {
        $cacheDir = $this->cacheDir;
        if ( file_exists( $cacheDir )) {
            if (@is_dir( $cacheDir )) {
                return $cacheDir;
            }
            else {
                return null;
            }
        }

        if ( @mkdir( $cacheDir ) ) {
            $stat = @stat( dirname( $cacheDir ) );
            $dir_perms = $stat['mode'] & 0007777;
            @chmod( $cacheDir, $dir_perms );
            return $cacheDir;
        }
    }

    function cacheIsHot() {
        return (($this->cacheLife > 0) &&
                $this->getCacheFile() && file_exists($this->cacheFile) &&
                (filemtime($this->cacheFile) > (time() - 60 * $this->cacheLife)));
    }

    function cacheFileExists() {
        return (($this->cacheLife > 0) &&
                $this->getCacheFile() && file_exists($this->cacheFile));
    }

    protected function getData($logger = null) {
        if ($logger) {
            $logger->write('SoEntity->getData: url: ' . $this->url);
        }

        if ($this->object) {
            if ($logger) {
                $logger->write('SoEntity->getData: returning existing object');
            }
            return $this->object;
        }

        // do the remote call
        if ($logger) {
            $logger->write('SoEntity->getData: check cache');
            $logger->write('  cache file: ' . $this->getCacheFile());
        }
        if ($this->cacheFileExists()) {
            if ($this->cacheIsHot()) {
                $this->rawdata = @file_get_contents($this->cacheFile);
                if (!empty($this->rawdata)) {
                    if ($logger) {
                        $logger->write('SoEntity->getData: returning cached data');
                    }
                    $this->object = json_decode($this->rawdata, true);
                    return $this->object;
                }
            }
            else {
                if ($logger) {
                    $logger->write('SoEntity->getData: removing stale cache');
                }
                unlink($this->cacheFile);
            }
        }

        // do the remote call
        if ($logger) {
            $logger->write('SoEntity->getData: do remote call');
        }

        $this->curl_get_url();

        if ($this->cacheLife > 0 && $this->cacheFile) {
            file_put_contents($this->cacheFile, $this->rawdata);
        }

        return $this->object;
    }


    private function curl_get_url() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_PORT, 80);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 0);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        //curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        //curl_setopt($curl, CURLOPT_REFERER, $referer);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5184000);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/json"));
        curl_setopt($curl, CURLOPT_USERAGENT, 'SO Profile WP Widget 2012.06.18');
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

        $this->rawdata = curl_exec($curl);
        $this->object = array();

        if (!curl_errno($curl)) {
            $this->object = json_decode($this->rawdata, true);
        }

        curl_close($curl);
    }

    static function clearCacheFiles($age = 0) {
        $cacheDir = WP_CONTENT_DIR . '/cache/';
        if ( file_exists( $cacheDir )) {
            $files = glob($cacheDir . 'soprofile-*.json');
            if ($files && count($files) > 0) {
                $cutoffTime = time() - (60 * $age);
                foreach($files as $file) {
                    if (filemtime($file) < $cutoffTime) {
                        @unlink($file);
                    }
                }
            }
        }
    }
}

class SOAnswer extends SOEntity {
    private $id;
    private $item;
    private $appKey;

    function __construct($id, $cacheLife = 12, $appKey = null) {
        $this->id = $id;
        $this->appKey = $appKey;
        $this->getUrl();
        parent::__construct('answer', $cacheLife, $id);
    }

    protected function getUrl() {
        if (!isset($this->url)) {
            $this->url = 'http://api.stackexchange.com/2.0/answers/' .
                $this->id .
                '?site=stackoverflow' .
                '&filter=!mxHrtf9u8v' ;
        }
        return $this->url;
    }

    public function getItem() {
        if (!isset($this->item)) {
            $data = $this->getData();
            $this->item =  (isset($data) &&
                            isset($data['items']) &&
                            isset($data['items'][0]))
                ? $data['items'][0]
                : false;
        }
        return $this->item;
    }
}


class SOAnswerset extends SOEntity {
    private $idset;
    private $appKey;
    private $item;

    function __construct($idset, $cacheLife = 12, $appKey = null) {
        $this->idset = $idset; // array
        $this->appKey = $appKey;
        //$this->getUrl($idset);
        parent::__construct('answerset', $cacheLife);
    }

    protected function getUrl() {
        return $this->getCustomUrl($this->idset);
    }

    private function getCustomUrl($theseIds) {
        $this->url = 'http://api.stackexchange.com/2.0/answers/' .
            implode(';',$theseIds) .
            '?site=stackoverflow' .
            '&filter=!mxHrtf9u8v' ;

        return $this->url;
    }

    public function getItem() {
        if (isset($this->item)) {
            return $this->item;
        }

        $answersAvailable = array();
        $answersNeeded = array();

        foreach($this->idset as $id) {
            $a = new SOAnswer($id, 12, $this->appKey);
            if ($a->cacheIsHot()) {
                $answersAvailable[] = $a->getItem();
            }
            else {
                $answersNeeded[] = $id;
            }
        }

        if (count($answersNeeded) > 0) {
            // remote request the data not currently fresh
            $this->getCustomUrl($answersNeeded);
            $data = $this->getData();
            // extract data for individual answers to filesystem cache
            //
            // {
            //   "items": [
            //     {
            //       "question_id": 11040711,
            //       "answer_id": 11041267,
            //       "creation_date": 1339707621,
            //       "last_edit_date": 1339708077,
            //       "last_activity_date": 1339708077,
            //       "score": 2,
            //       "is_accepted": true,
            //       "title": "JQuery - Iterating JSON Response"
            //     },
            //     {
            //       "question_id": 630453,
            //       "answer_id": 2691891,
            //       "creation_date": 1271948154,
            //       "last_edit_date": 1287932852,
            //       "last_activity_date": 1287932852,
            //       "score": 251,
            //       "is_accepted": false,
            //       "title": "PUT vs POST in REST"
            //     }
            //   ],
            //   "quota_remaining": 9972,
            //   "quota_max": 10000,
            //   "has_more": false
            // }

            // Create cache files for the individual answer items
            $items = $data['items'];

            foreach($items as $answer) {
                $a = new SOAnswer($answer['answer_id'], 12, $this->appKey);
                $file = $a->getCacheFile();
                $slug = array('items'=> array( $answer ));
                file_put_contents($file, json_encode($slug));
                $answersAvailable[] = $answer;
            }
        }

        $this->item = array('items' => $answersAvailable);

        // cache the entire answerset, just for the halibut.
        $this->getUrl();
        file_put_contents($this->getCacheFile(), json_encode($this->item));

        return $this->item;
    }
}


class SOActivity extends SOEntity {
    private $userId;
    private $flavor;
    private $nItems;
    private $sort;
    private $appKey;
    private $entity;

    function __construct($userId, $flavor, $n, $sort, $cacheLife = 12, $appKey = null) {
        $this->userId = $userId;
        $this->flavor = $flavor;
        $this->nItems = (int) $n;
        $this->sort = $sort;
        $this->appKey = $appKey;
        $this->getUrl();
        parent::__construct($flavor, $cacheLife);
    }

    protected function getUrl() {
        if (!isset($this->url)) {
            $url = 'http://api.stackexchange.com/2.0/users/' .
                $this->userId .
                '/' . $this->flavor . '/' .
                '?site=stackoverflow' .
                '&filter=!mxHrtf9u8v'; // '!Sp0dF1GHfV9jb)omNz';

            if ( $this->flavor == 'answers') {
                $url .= '&pagesize=' . $this->nItems .
                    '&sort=';

                switch ($this->sort) {
                    case 'score-asc':
                        $url .= 'votes&order=desc';
                        break;

                    case 'newest':
                        $url .= 'creation&order=desc';
                        break;

                    case 'latest':
                    default:
                        $url .= 'activity&order=desc';
                        break;
                }
            }
            else {
                // reputation reports consolidate upvotes, but
                // include acceptances as distinct items. So if you ask
                // for 5 items, you may get updates on only 2 answers,
                // if two of them have both upvotes and acceptances, and
                // one of the items is an upvoted question.
                // Therefore don't limit the number of items requested.
                // '&pagesize=' . ($this->nItems * 2) .


                // $url .= '&sort=';
                // switch ($this->sort) {
                //     case 'score-asc':
                //         $url .= 'votes&order=desc';
                //         break;
                //
                //     case 'newest':
                //         $url .= 'creation&order=desc';
                //         break;
                //
                //     case 'latest':
                //     default:
                //         $url .= 'activity&order=desc';
                //         break;
                // }
            }

            if (!empty($this->appKey)) {
                $url .= '&key=' . $this->appKey;
            }

            $this->url = $url;
        }

        return $this->url;
    }

    public function getItem() {
        $data = $this->getData();
        // Create cache files for the individual answer items
        foreach($data['items'] as $answer) {
            if (isset($answer['answer_id'])) {
                $a = new SOAnswer($answer['answer_id'], 12, $this->appKey);
                $file = $a->getCacheFile();
                $slug = array('items'=> array( $answer ));
                file_put_contents($file, json_encode($slug));
            }
        }

        return $data;
    }
}


class SOUser extends SOEntity {
    private $userId;
    // private $url;
    private $appKey;
    private $entity;
    private $item;

    function __construct($userId, $cacheLife = 120, $appKey = null) {
        $this->userId = $userId;
        $this->appKey = $appKey;
        $this->getUrl();
        parent::__construct('user', $cacheLife);
    }

    protected function getUrl() {
        if (!isset($this->url)) {
            $url = 'http://api.stackexchange.com/2.0/users/' .
                $this->userId .
                '?site=stackoverflow' .
                '&filter=!-q2RcgPq' ;

            if (!empty($this->appKey)) {
                $url .= '&key=' . $this->appKey;
            }

            $this->url = $url;
        }
        return $this->url;
    }

    function getHtmlUrl() {
        return 'http://stackoverflow.com/users/' . $this->userId;
    }

    public function getItem() {
        if (!isset($this->item)) {
            $uInfo = $this->getData();
            $this->item =
                (isset($uInfo) && isset($uInfo['items']) && isset($uInfo['items'][0]))
                ? $uInfo['items'][0]
                : false;

            // Example:
            // {
            //   "user_id": 48082,
            //   "user_type": "registered",
            //   "creation_date": 1229831074,
            //   "display_name": "Cheeso",
            //   "profile_image": "http://www.gravatar.com/avatar/.......",
            //   "reputation": 52183,
            //   "reputation_change_day": 125,
            //   "reputation_change_week": 396,
            //   "reputation_change_month": 871,
            //   "reputation_change_quarter": 4388,
            //   "reputation_change_year": 8766,
            //   "last_access_date": 1339779236,
            //   "last_modified_date": 1339679499,
            //   "is_employee": false,
            //   "link": "http://stackoverflow.com/users/48082/cheeso",
            //   "website_url": "http://dinochiesa.net",
            //   "location": "Seattle, WA",
            //   "account_id": 20213,
            //   "badge_counts": {
            //     "gold": 14,
            //     "silver": 104,
            //     "bronze": 230
            //   },
            //   "answer_count": 1581,
            //   "accept_rate": 96
            // }
        }

        return $this->item;
    }

    /*
     * The following functions support the display.php module
     * that renders the widget.
     ****/

    function getReputation() {
        $user = $this->getItem();
        if ($user) {
            return $user['reputation'];
        }
        return 101;
    }
    function getAnswerCount() {
        $user = $this->getItem();
        if ($user) {
            return $user['answer_count'];
        }
        return 0;
    }
    function getDisplayName() {
        $user = $this->getItem();
        if ($user) {
            return $user['display_name'];
        }
        return '???';
    }
    function getBadgeCounts() {
        $user = $this->getItem();
        if ($user) {
            return $user['badge_counts'];
        }
        return array('gold'=>0,'silver'=>0,'bronze'=>0);
    }
}

?>