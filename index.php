<?php 
    $tagName = 'rock';
    $limitePaginasDownload = 2;
    $baseUrl = 'https://www.instagram.com/explore/tags/'.$tagName.'/?__a=1';
    $json = json_decode(file_get_contents($baseUrl));

  
    function baixaFile($src,$video,$code){
        $fp = fopen("zipfile/log.txt", "a"); 
        if($video){
            $instalink = 'http://instagram.com/p/'.$code.'/';            
            $response = @ file_get_contents( $instalink );
            $response = explode('_sharedData =', $response);
            $response = explode('{}}}', $response[1]);
            $response = $response[0].'{}}}}';
            $response = explode('video_url', $response);
            $response = explode('video_view_count', $response[1]);  
            $response = explode(' ', $response[0]);  
            $response = explode(',', $response[1]);  
            $response = explode('"', $response[0]);  
            $linkCopia = ($response[1]);
            $novoNome = $code.'.mp4';      
        }else{
            $nomeFile = explode('/', $src);
            $totalNomeFile = count($nomeFile);
            $novoNome = $nomeFile[$totalNomeFile - 1];
            $linkCopia = $src;
        }
        if (!copy($linkCopia, 'zipfile/'.$novoNome)) {
            $escreve = fwrite($fp, $src.PHP_EOL."-Succes\r\n"); 
        }else{
             $escreve = fwrite($fp, $src.PHP_EOL."-Error\r\n"); 
        }
        $escreve = fwrite($fp, $src.PHP_EOL."\r\n");   
        move_uploaded_file($src, 'zipfile/');
        fclose($fp); 
    }

    
    foreach ($json->tag->media->nodes as $key => $value) { 
        baixaFile($value->display_src,$value->is_video,$value->code);        
    }
    if($json->tag->media->page_info->has_next_page){
        $nextPage = true;
    }else{
        $nextPage = false;
    }
    if($nextPage){
        $baseUrl = 'https://www.instagram.com/graphql/query/?query_id=17875800862117404&variables=%7B%22tag_name%22%3A%22'.$tagName.'%22%2C%22first%22%3A9%2C%22after%22%3A%22'.$json->tag->media->page_info->end_cursor.'%22%7D';
        $json = json_decode(file_get_contents($baseUrl));
        foreach ($json->data->hashtag->edge_hashtag_to_media->edges as $key => $value) { 
            baixaFile($value->node->display_url,$value->node->is_video,$value->node->shortcode);
        }
        $whileNovaPg = $json->data->hashtag->edge_hashtag_to_media->page_info->has_next_page;
        $totalPaginasFeitos = 0;
        while ($whileNovaPg && $totalPaginasFeitos < $limitePaginasDownload) {
            $baseUrl = 'https://www.instagram.com/graphql/query/?query_id=17875800862117404&variables=%7B%22tag_name%22%3A%22'.$tagName.'%22%2C%22first%22%3A9%2C%22after%22%3A%22'.$json->data->hashtag->edge_hashtag_to_media->page_info->end_cursor.'%22%7D';
            $json = json_decode(file_get_contents($baseUrl));
            foreach ($json->data->hashtag->edge_hashtag_to_media->edges as $key => $value) { 
                baixaFile($value->node->display_url,$value->node->is_video,$value->node->shortcode);
            }
            if($json->data->hashtag->edge_hashtag_to_media->page_info->has_next_page){
                $whileNovaPg = true;
            }else{
                $whileNovaPg = false;
            }            
            $totalPaginasFeitos++;
        }

    }

?>