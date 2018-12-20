<?php

function buildCheckFile($page, $ext, $i = 0){
    $filename = 'ktm';
    if ($i == 0) {
        $filename = 'out_' . $page . '.' .$ext;
    } else {
        $filename = 'out_'.$page.'_'.$i.'.'.$ext;
    }
    //var_dump($filename);
    $path = str_replace('\\','/',dirname(__DIR__).'/images/'.$filename);
    //var_dump(file_exists($path));
    if (file_exists($path)){
        $i++;
        $out = buildCheckFile($page, $ext, $i);
    } else {
        $out = $filename;
    }
    return $out;
}

function removeFilename($url) {
    $file_info = pathinfo($url);
    return isset($file_info['extension'])
        ? str_replace($file_info['filename'] . "." . $file_info['extension'], "", $url)
        : $url;
}
