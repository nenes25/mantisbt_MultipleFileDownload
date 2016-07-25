<?php
# MantisBT - A PHP based bugtracking system
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

#
#  Plugin MultipleFileDownload for Mantis BugTracker
#  © Hennes Hervé <contact@h-hennes.fr>
#    2015-2016
#  http://www.h-hennes.fr/blog/

require_once( dirname(__FILE__) . '/../../../core.php' );
require_once( dirname(__FILE__) . '/../../../core/file_api.php' );

#Données envoyées par le script
$t_bug_id = gpc_get_int('bug_id');
$t_files_download = gpc_get_int_array('files');

#Donées de configuration mantis
$t_upload_method = config_get('file_upload_method');
$t_upload_directory = config_get('absolute_path_default_upload_folder');
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

        #Ajout des donnes à l'archive
        if ( $t_upload_method == DISK || $t_upload_method == FTP )
          $archive->addFile($t_upload_directory  . $row['diskfile'], $row['filename']);
        #Sinon si on est en mode DB ajout du contenu à l'archive
        else 
            $archive->addFromString($row['filename'], $row['content']);
    }
    $archive->close();
    
    #Téléchargement du zip genéré
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($t_file_name_with_path)) . ' GMT');
    header('Content-type: application/zip');
    header('Content-Disposition: inline; filename="'.date('Ymd').'files-bug-'.$t_bug_id.'.zip"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($t_file_name_with_path));
    header('Connection: close');
    readfile($t_file_name_with_path);
    #supression du fichier
    unlink($t_file_name_with_path);
    #Fin du script
    exit();
}
else {
    echo plugin_lang_get( 'error_unable_to_create_zip_file' );
}
