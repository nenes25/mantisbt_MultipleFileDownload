<?php
/*
  Plugin MultipleFileDownload pour Mantis BugTracker :

  - Téléchargement de fichiers multiples

  © Hennes Hervé - 2015
  http://www.h-hennes.fr
 */
require_once( config_get('class_path') . 'MantisPlugin.class.php' );

class MultipleFileDownloadPlugin extends MantisPlugin {

    /**
     * Enregistrement du module
     */
    function register() {
        
        $this->name = plugin_lang_get( 'plugin_title' ); //'Mutiple Filed Download';
        $this->description = plugin_lang_get( 'plugin_description' ); 'Mutiple Filed Download'; 
        $this->version = '0.1.0';
        $this->requires = array(
            'MantisCore' => '1.2.0',
            //'jQuery' => '1.11'
            );
        $this->author = 'Hennes Hervé';
        $this->url = 'http://www.h-hennes.fr/blog/';
    
    }

    /**
     * Initialisation du module
     */
    function init() {
        plugin_event_hook('EVENT_VIEW_BUG_EXTRA', 'prepareDownloadDatas');
    }

    /**
     * Affichage du code dans la page de visualisation d'un bug
     * Placement dans le DOM via jquery
     */
    function prepareDownloadDatas() {

        $t_bug_id = gpc_get_int('id', -1);

        if ($t_bug_id > 0) {

            $t_bug_files = file_get_visible_attachments($t_bug_id);
            if (sizeof($t_bug_files)) {
                
                #Affichage du bloc de téléchargement
                echo '<div id="plugin_multiplefiledownload_block">'
                . '<form name="plugin_multiplefiledownload_form" method="post" action="'.plugin_page('download_script.php').'">'
                . '<input type="hidden" name="bug_id" value="'.$t_bug_id.'" />'        
                . '<table class="width100">'
                . '<tr colspan="2"><td class="form-title">'.plugin_lang_get( 'multiple_file_download').'</td></tr>'
                . '<tr class="row-2"><td class="category">'.plugin_lang_get( 'files').'</td>'
                . '<td width="75%">';
                echo '<ul style="list-style:none;">';
                echo '<p>'.plugin_lang_get( 'check_files_download' ).'</p>';
                foreach ($t_bug_files as $t_bug_file) {
                    if ($t_bug_file['can_download'])
                        echo '<li><input type="checkbox" class="plugin_multiplefiledownload_files" name="files[]" value="' . $t_bug_file['id'] . '">' . $t_bug_file['display_name'] . '</li>';
                }
                echo '</ul></td></tr>';
                echo '<tr class="row-1"><td class="category">&nbsp;</td><td><input type="submit" value="'.plugin_lang_get( 'download_files' ).'" id="plugin_multiplefiledownload_submit"/></td></tr>';
                echo '</table></form></div>';
                
                #Javascript de gestion @ToDO : le rajouter dans un js spécifique
                echo '<script type="text/javascript">
                    jQuery(document).ready(function($){
                    
                        $("#plugin_multiplefiledownload_submit").on("click",function(){
                            
                            var files_id = []
                            $(".plugin_multiplefiledownload_files").each(function(){
                                if ( $(this).is(":checked"))
                                 files_id.push($(this).val());
                            });
                            
                            if ( ! files_id.length ) {
                                alert("'.plugin_lang_get( 'no_selected_files_for_download' ).'");
                                return false;
                            }
                        });
                        
                    });
                </script>';
            }
        }
    }

}