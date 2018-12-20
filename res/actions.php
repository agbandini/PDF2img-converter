<?php

include('functions.php');

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$outPut = false;

switch ($action) {
    case 'pdf2img':
        $post = file_get_contents('php://input');
        if (isset($post)) {
            $imageData = $post;
            $filteredData = substr($imageData, strpos($imageData, ",") + 1);
            $unencodedData = base64_decode($filteredData);
            $filename = buildCheckFile($_GET['page'], $_GET['ext']);
            //var_dump($filename);
            $fp = fopen(dirname(__DIR__) . '/images/' . $filename, 'wb');
            fwrite($fp, $unencodedData);
            fclose($fp);
        }
        break;
    case "getGalleryTmp":
        $di = new DirectoryIterator(dirname(__DIR__) . '/images/');
        //var_dump($di);
        $out = array();
        foreach ($di as $entry) {
            if ($entry->isFile()) {
                //echo $entry->getFilename().'('.$entry->getSize().')<br>';
                $ext = strtolower(substr(strrchr($entry->getFilename(), '.'), 1));
                if ($ext == 'jpg' || $ext == 'png') {
                    array_push($out, strtolower($entry->getFilename()));
                }
            }
        }
        $outPut = json_encode($out);
        break;
    case "clearGalleryTmp":
        $di = new DirectoryIterator(dirname(__DIR__) . '/images/');
        $out = array();
        foreach ($di as $entry) {
            if ($entry->isFile()) {
                unlink(dirname(__DIR__) . '/images/' . $entry);
            }
        }
        $outPut = json_encode($out);
        break;
    case "deleteImg":
        $di = new DirectoryIterator(dirname(__DIR__) . '/images/');
        $out = array();
        foreach ($di as $entry) {
            if ($entry->isFile()) {
                if ($entry->getFilename() == $_GET['img']) {
                    unlink(dirname(__DIR__) . '/images/' . $entry);
                }
            }
        }
        $outPut = json_encode($out);
        break;
    case 'zippaescarica':
        // Get real path for our folder
        $rootPath = realpath(dirname(__DIR__) . '/images/');
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open('gallery.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }
        // Zip archive will be created only after closing object
        $zip->close();
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $actual_link = removeFilename($actual_link);
        $actual_link .= 'gallery.zip';
        $outPut = json_encode(array('zip' => $actual_link));
        break;
}

echo $outPut;
