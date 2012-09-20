<?php
include_once 'src/facebook.php';

class FacebokAlbums {
    /*
     * Insert your facebook application data
     * You can create new application on https://developers.facebook.com/apps
     */
    const USER_ID    = '314854078551721';
    const APP_ID     = '271765292933365';
    const APP_SECRET = '6d982a58710810d3dd0355634d579fff';
    const STATIC_DIR_NAME_IMAGES_PATTERN = 'http://wp.my/static_images/';

    public function __construct(){
        $config = array(
            'appId'  => self::APP_ID,
            'secret' => self::APP_SECRET,
            'cookie' => true
        );
        $this->_facebook = new Facebook($config);
    }

    public function __destruct(){
        unset($_SESSION['instance']);
    }

    private function _getInstance(){
        if($_SESSION['instance'] != true){
            $_SESSION['instance'] = true;
            return '<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
                    <script type="text/javascript" src="http://fancyapps.com/fancybox/source/jquery.fancybox.pack.js?v=2.1.0"></script>
                    <link href="http://fancyapps.com/fancybox/source/jquery.fancybox.css?v=2.1.0" media="screen" rel="stylesheet" type="text/css" >';
        } else {
            return '';
        }
    }
    public function getAllLatest($numberOfImages = 10,$thumbsSize = 100, $useStaticImages = true){
        $queries   = array (
            'query1' => "SELECT aid FROM album WHERE owner in (313946888618991, 314854078551721,251801401546303, 72013347105)",
            'query2' => " SELECT pid, src,src_big, caption FROM photo WHERE aid IN (SELECT aid FROM #query1) ORDER BY created DESC LIMIT ".$numberOfImages

        );
        $param = array(
            'method' => 'fql.multiquery',
            'queries' => $queries,
            'callback' => ''
        );
        $imagesResult = $this->_facebook->api($param);
        echo $this->_generateHtmlAlbum($imagesResult[1]['fql_result_set'],$thumbsSize, $useStaticImages);

    }
    public function getLatestImagesForAll($pageId = self::USER_ID, $numberOfImages = 10,$thumbsSize = 100, $useStaticImages = true){
        $queries   = array (
            'query1' => "SELECT aid FROM album WHERE owner = ".$pageId,
            'query2' => " SELECT pid, src,src_big, caption FROM photo WHERE aid IN (SELECT aid FROM #query1) ORDER BY created DESC LIMIT ".$numberOfImages

        );
        $param = array(
            'method' => 'fql.multiquery',
            'queries' => $queries,
            'callback' => ''
        );
        $imagesResult = $this->_facebook->api($param);
        echo $this->_generateHtmlAlbum($imagesResult[1]['fql_result_set'],$thumbsSize, $useStaticImages);

    }
    /*
     * Function returt list of user's public albums
     */

    public function getUserAlbumList($userId = self::USER_ID, $thumbsSize = 50){
        $queries   = array (
            'query1' => "SELECT aid, cover_pid, name FROM album WHERE owner=".$userId,
            'query2' => "SELECT src FROM photo WHERE pid IN (SELECT cover_pid FROM #query1)"

            );
        $param = array(
            'method' => 'fql.multiquery',
            'queries' => $queries,
            'callback' => ''
        );
        echo $this->_generateHtmlAlbumList($this->_facebook->api($param),$thumbsSize);
    }
    /*
     * Return images from album
     * @numberofImages - use to limit numbers of foto you want to get
     */
    public function getAlbumImages($albumId = '314854078551721_71623', $numberOfImages = 10, $thumbsSize = 50, $useStaticImages = true){
        $fql = "SELECT pid, src, src_small, src_big, caption FROM photo WHERE aid = '" . $albumId ."' ORDER BY created DESC limit ".$numberOfImages;
        $param = array(
            'method' => 'fql.query',
            'query' => $fql,
            'callback' => ''
        );
        echo $this->_generateHtmlAlbum($this->_facebook->api($param),$thumbsSize, $useStaticImages);
    }
    /*
     * Generate html wrapper for album
     */
    private function _generateHtmlAlbum($albumsData, $thumbSize, $useStaticImages){
        if($useStaticImages != false) $albumsData = $this->_getStaticImages($albumsData);
        $timestamp = uniqid('gall');
        $html  = $this->_getInstance();
        $html .= '<div class="fb_album_'.$timestamp.'" style="clear:both;">';
        if(is_array($albumsData)){
            $html .= '<ul class="img_gallery_'.$timestamp.'">';
                foreach($albumsData as $key => $imageData){
                    $html .= '<li class="gallery_image_'.$timestamp.'"  style="float:left;list-style-type: none;padding:10px;">';
                        $html .= '<a class="gall_'.$timestamp.'" href="'.$imageData['src_big'].'" rel="'.$timestamp.'">';
                            $html .= '<img style="width:'.$thumbSize.'px;height:'.$thumbSize.'px;" src="'.$imageData['src'].'">';
                        $html .= '</a>';
                    $html .= '</li>';
                }
            $html .= '</ul>';
        $html .= "<script type='text/javascript'>
                $('a[rel=".$timestamp."]').fancybox({
                    'transitionIn'	: 'none',
                    'transitionOut'	: 'none',
                    'titlePosition' : 'over'
                });
            </script>";
        }
        return $html;
    }

    private function _generateHtmlAlbumList($albums, $thumbSize){
        $html  = $this->_getInstance();
        if(is_array($albums[0]['fql_result_set'])){
            $html .= '<ul class="img_gallery">';
                foreach($albums[0]['fql_result_set'] as $key => $albumData){
                    $html .= '<li class="gallery_image">';
                        $html .= '<a class="gall" href="#">';
                            $html .= '<img style="width:'.$thumbSize.'px;height:'.$thumbSize.'px;" src="'.$albums[1]['fql_result_set'][$key]['src'].'">';
                        $html .= '</a>';
                        $html .= '</li>';
                }
            $html .= '</ul>';
        }
        return $html;
    }
    /*
    * To use static images set const STATIC_DIR_NAME_IMAGES_PATTERN value to folder with images
    * and images must be 1.jpg, 2.jpg, etc
    */
    private function _getStaticImages($facebookArray){
        for($i = 10; $i > 0; $i--){
            $tempVar['src_big'] = self::STATIC_DIR_NAME_IMAGES_PATTERN.$i.'.jpg';
            $tempVar['src'] = self::STATIC_DIR_NAME_IMAGES_PATTERN.$i.'.jpg';
            array_unshift($facebookArray, $tempVar);
        }
        return $facebookArray;
    }
}