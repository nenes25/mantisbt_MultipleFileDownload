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
        
        $this->name = 'Mutiple Filed Download';//lang_get( 'plugin_multiplefiledownload_title' );
        $this->description = 'Mutiple Filed Download'; //lang_get( 'plugin_multiplefiledownload_description' );
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
                . '<tr colspan="2"><td class="form-title">Multiple-File Download</td></tr>'
                . '<tr class="row-2"><td class="category">Files</td>'
                . '<td width="75%">';
                echo '<ul>';
                echo '<p>Cocher les pièces jointes que vous souhaitez télécharger</p>';
                foreach ($t_bug_files as $t_bug_file) {
                    if ($t_bug_file['can_download'])
                        echo '<li><input type="checkbox" class="plugin_multiplefiledownload_files" name="files[]" value="' . $t_bug_file['id'] . '">' . $t_bug_file['display_name'] . '</li>';
                }
                echo '</ul></td></tr>';
                echo '<tr class="row-1"><td class="category">&nbsp;</td><td><input type="submit" value="Envoyer" id="plugin_multiplefiledownload_submit"/></td></tr>';
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
                                alert("Vous n\'avez pas sélectionné de de pièces jointes à télécharger");
                                return false;
                            }
                            
                            /*$.ajax({
                                method : "post",
                                url : "'.plugin_page('download_script.php').'",
                                dataType: "json",    
                                data : {"bug_id" :'.$t_bug_id.',"files" :files_id},
                                success: function(msg){
                                    if ( msg == 0 ) {
                                        alert("Unable to download files");
                                    }
                                    else {
                                        
                                    }
                                }
                            });*/
                                
                            
                        });
                        
                    });
                </script>';
            }
        }
    }

}