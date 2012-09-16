<?php
include_once 'src/facebook.php';

class FacebokAlbums {
    /*
     * Insert your facebook application data
     * You can create new application on https://developers.facebook.com/apps
     */
    const APP_ID     = '366577446747492';
    const APP_SECRET = 'c2a98549e69c7b8ec5a19ce1a0f0fed3';
    public function __construct(){
        $config = array(
            'appId'  => self::APP_ID,
            'secret' => self::APP_SECRET,
            'cookie' => true
        );
        $this->_facebook = new Facebook($config);
    }
    /*
     * Function returt list of user's public albums
     */
    public function getUserAlbumList($userId = '314854078551721'){
        $queries   = array (
            'query1' => "SELECT aid, cover_pid, name FROM album WHERE owner=".$userId,
            'query2' => "SELECT src FROM photo WHERE pid IN (SELECT cover_pid FROM #query1)"

            );
        $param = array(
            'method' => 'fql.multiquery',
            'queries' => $queries,
            'callback' => ''
        );
        return $this->_generateHtmlAlbumList($this->_facebook->api($param));
    }
    /*
     * Return images from album
     * @numberofImages - use to limit numbers of foto you want to get
     */
    public function getAlbumImages($albumId = '314854078551721_71623', $numberOfImages = 10){
        $fql = "SELECT pid, src, src_small, src_big, caption FROM photo WHERE aid = '" . $albumId ."' ORDER BY created DESC limit ".$numberOfImages;
        $param = array(
            'method' => 'fql.query',
            'query' => $fql,
            'callback' => ''
        );
        return $this->_generateHtmlAlbum($this->_facebook->api($param));
    }
    /*
     * Generate html wrapper for album
     */
    private function _generateHtmlAlbum($albumsData, $thumbSize = 50){
        $html = '<div class="fb_album">';
        if(is_array($albumsData)){
            $html .= '<ul class="fb_gallery">';
                foreach($albumsData as $key => $imageData){
                    $html .= '<li class="fb_gallery_image">';
                        $html .= '<a class="gall" href="'.$imageData['src_big'].'" rel="gall">';
                            $html .= '<img style="width:'.$thumbSize.'px;height:'.$thumbSize.'px;" src="'.$imageData['src'].'">';
                        $html .= '</a>';
                    $html .= '</li>';
                }
            $html .= '</ul>';
        }
        return $html;
    }

    private function _generateHtmlAlbumList($albums, $thumbSize = 50){
        if(is_array($albums[0]['fql_result_set'])){
            $html = '<ul class="img_gallery">';
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
}