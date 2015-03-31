<?php

/*
  Plugin MultipleFileDownload pour Mantis BugTracker :

  - Téléchargement de fichiers multiples

  © Hennes Hervé - 2015
  http://www.h-hennes.fr
 */
require_once( dirname(__FILE__) . '/../../../core.php' );
require_once( dirname(__FILE__) . '/../../../core/file_api.php' );

#Données envoyées par le script
$t_bug_id = gpc_get_int('bug_id');
$t_files_download = gpc_get_int_array('files');

#Donées de configuration mantis
$t_upload_method = config_get('file_upload_method');
$t_bug_file_table = db_get_table('mantis_bug_file_table');

#Nom de l'archive qui va être créée
$t_archive_name = md5($t_bug_id . implode('', $t_files_download) . time()) . '.zip';
$t_file_name_with_path = dirname(__FILE__) . '/../files/'.$t_archive_name;

$archive = new ZipArchive();
if ($archive->open($t_file_name_with_path, ZipArchive::CREATE) === TRUE) {
    
    foreach ($t_files_download as $t_file_download) {

        $query = "SELECT *
	      FROM $t_bug_file_table
	      WHERE id=" . db_param();

        $result = db_query_bound($query, array($t_file_download));
        $row = db_fetch_array($result);

        #Ajout des donnes à l'archive(@ToDO gérer en fonction de la méthode de stockage des fichiers)
        //echo 'Ajout du fichier '. $row['filename'] . ' a l\archive' . "\n";
        $archive->addFromString($row['filename'], $row['content']);
        //$archive->addFile($t_upload_directory . '/' . $row['diskfile'], $row['filename']);
    }
    $archive->close();
    
    #Téléchargement du zip genéré
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime('test.zip')) . ' GMT');
    header('Content-Type: application/force-download');
    header('Content-Disposition: inline; filename="'.date('Ymd').'files-bug-'.$t_bug_id.'.zip"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($t_file_name_with_path));
    header('Connection: close');
    readfile($t_file_name_with_path);
    exit();
    
    #Suppression du fichier
    //unlink($t_file_name_with_path);
}
else {
    echo 'Erreur durant la creation de l\'archive de téléchargement';
}
