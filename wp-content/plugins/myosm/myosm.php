<?php
/*
Plugin Name: My OpenStreetMap
Description: Créer des cartes OpenStreetMap.
Version: 2.0
*/
if (!class_exists("MyOsm")) {
    class MyOsm
    {
        private $table;
        private $url;

        function __construct()
        {
            /* objet $wpdb de WordPress permet de se connecter à la base de données
            * Il s'agit d'une variable globale, il faut donc la récupérer dans la
            * fonction avec la mention 'global'
            *
            * https://developer.wordpress.org/reference/classes/wpdb
            * https://apical.xyz/fiches/base_de_donnees_wordpress/La_classe_wpdb
            */
            global $wpdb;

            // $table vaudra 'wp_osm' si le préfixe de table configuré à l'installation est 'wp_' (celui par défaut)
            $this->table = $wpdb->prefix . 'osm';

            // Définit l'url vers le fichier de classe du plugin
            $this->url = get_bloginfo("url") . "/wp-admin/options-general.php?page=myosm/myosm.php";
        } // -- __construct()

        // Fonction déclenchée à l'activation du plugin
        function osm_install(): void
        {
            global $wpdb;

            /* fonction get_var() :
            * exécute une requête SQL et retourne une variable
            * https://developer.wordpress.org/reference/classes/wpdb/get_var
            *
            * SHOW TABLES ne fonctionne pas avec des quotes obliques ``, il faut des droites ''
            * ici get_var() retourne NULL car la table n'existe pas
            */

            // On s'assure que la table n'existe pas déjà ('!=')
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $this->table . "'") != $this->table) {
                /*
                * - Longitude: 11 chiffres max dont 8 max après la virgule (exemple: -180.00000001)
                * - Latitude max : 10 chiffres max dont 8 max après la virgule (exemple: -90.00000001)
                *
                * On devrait donc les stocker en type DECIMAL mais cela pose des problèmes de formlatage dans les requêtes préparées,
                * pour simplifier on les stocke comme chaînes en VARCHAR.
                *
                * https://qastack.fr/programming/15965166/what-is-the-maximum-length-of-latitude-and-longitude
                */
                $sql = "CREATE TABLE " . $this->table . "
                 (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                 `titre` VARCHAR(100) NOT NULL,
                 `longitude` VARCHAR(11) NOT NULL,  
                 `latitude` VARCHAR(10) NOT NULL                          
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

                /* Inclusion du fichier 'upgrade.php' nécessaire car c'est lui qui contient le code
                * de la fonction dbDelta utilisée à la ligne suivante
                * ABSPATH = chemin absolu vers le répertoire du projet = 'C:\wamp\www\wordpress/'
                */
                if (require_once(ABSPATH . "wp-admin/includes/upgrade.php")) {
                    /*
                    * La fonction dbDelta() applique les changements de structure sur les objets de la base (tables, colonnes...)
                    * https://developer.wordpress.org/reference/functions/dbdelta/
                    * https://codex.wordpress.org/Creating_Tables_with_Plugins
                    * https://apical.xyz/fiches/donnees_personnalisees_wordpress/Ajouter_des_tables_personnalisees_dbDelta
                    */
                    dbDelta($sql);
                }
            }
        } // -- osm_install()

        // Fonction déclenchée lors de la désactivation du plugin
        function osm_uninstall(): void
        {
            global $wpdb;

            // On s'assure que la table existe
            // ici, get_var() retourne le nom de la table, par exemple 'wp_osm'
            if ($wpdb->get_var("SHOW TABLES LIKE '" . $this->table . "'") == $this->table) {
                // On la supprime
                // ATTENTION : pensez à sauvegarder les données au préalable si nécessaire
                $wpdb->query("DROP TABLE `" . $this->table . "`");
            }
        } // -- osm_uninstall()

        function osm_init(): void
        {
            if (function_exists('add_menu_page')) {
                /* fonction add_menu_page() : ajout d'un lien dans le menu de l'administration
                * + fonction qui doit être lancée quand on clique sur ce lien, ici osm_admin_page()
                *
                * https://developer.wordpress.org/reference/functions/add_menu_page
                *
                * add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', int $position = null)
                *
                * - $page_title : balise <title> de la page (= aussi dans l'onglet du navigateur)
                * - $menu_title : libellé du lien dans le menu de l'administration
                * - $capability : rôle pour lequel la page d'admin est disponible
                * - $menu_slug : nom technique du lien dans le menu de l'administration
                * - $function : fonction à exécuter pour l'affichage des pages du plugins
                *               (attention l'argument passé est un tableau indiquant le plugin/l'instance - et la méthode)
                * - $position : position dans le menu d'admin, placé à la fin si non précisé.
                *
                * Ici $sPage vaut 'settings_page_myosm/myosm'
                */
                $sPage = add_menu_page('My OpenStreetMap', 'My Osm', 'administrator', __FILE__, array($this, 'osm_admin_page'));

                /* Créer un hook 'load-settings_page_myosm/myosm'
                 * qui appelle la fonction osm_ admin_header()
                 */
                add_action("load-" . $sPage, array($this, "osm_admin_header"));
            }
        } // -- osm_init()

        // Charge les CSS et JS nécessaires au plugin côté admin
        function osm_admin_header(): void
        {
            // plugin_dir_url('css/admin-osm.css', __FILE__)) = 'http://localhost/wordpress/wp-content/plugins/css/'
            // plugins_url('css/admin-osm.css', __FILE__)) = 'http://localhost/wordpress/wp-content/plugins/myosm/css/admin-osm.css'

            wp_register_style('my_osm_css', plugins_url('css/admin-osm.css', __FILE__));
            wp_enqueue_style('my_osm_css');
            wp_enqueue_script('my_osm_js', plugins_url('js/admin-osm.js', __FILE__), array('jquery'));

            // Leafleft JS et CSS
            wp_enqueue_script('leaflet_js', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.js");
            wp_enqueue_style('leaflet_css', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.css");
        } // -- osm_admin_header()

        // Gestion des pages/formulaires dans l'administration
        function osm_admin_page(): void
        {
            // map = id d'une carte
            if (isset($_GET["map"])) {
                require_once("templates/admin-map-detail.php");
            } else {
                require_once("templates/admin-home.php");
            }

            if (isset($_GET['action']) && $_GET['action'] == 'createmap') {
                // +++ TODO : Sécuriser davantage les données provenant du formulaire : filter_var, type de données etc.) +++
                if (!empty(trim($_POST['Mg-title'])) && (!empty(trim($_POST['Mg-latitude']))) && (!empty(trim($_POST['Mg-longitude'])))) {
                    if ($this->osm_insertMap($_POST['Mg-title'], $_POST['Mg-latitude'], $_POST['Mg-longitude'])) {
                        /*
                         * https://www.w3schools.com/howto/howto_js_redirect_webpage.asp
                         * https://christianelagace.com/wordpress/la-redirection-avec-wordpress
                         */
                        $sUrl = $this->url . "&msg=cre_ok";
                        echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                        exit;
                    } else {
                        $sUrl = $this->url . "&msg=cre_ko";
                        echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                        exit;
                    }
                } else {
                    $sUrl = $this->url . "&msg=empty_ko";
                    echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                    exit;
                }
            } else if (isset($_GET['action']) && $_GET['action'] == 'updatemap') {
                if ((trim($_POST['Mg-title']) != '') && (trim($_POST['Mg-latitude']) != '') && (trim($_POST['Mg-longitude']) != "") && (trim($_POST['Mg-id']) != '')) {
                    if ($this->osm_updateMap($_POST['Mg-id'], $_POST['Mg-title'], $_POST['Mg-latitude'], $_POST['Mg-longitude'])) {
                        $sUrl = $this->url . "&msg=upd_ok&map=" . $_POST["Mg-id"];
                        echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                        exit;
                    } else {
                        $sUrl = $this->url . "&msg=upd_ko&map=" . $_POST["Mg-id"];
                        echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                        exit;
                    }
                } else {
                    $sUrl = $this->url . "&msg=empty_ko&map=" . $_POST["Mg-id"];
                    echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                    exit;
                }
            } elseif (isset($_GET['action']) && $_GET['action'] == 'deletemap') {
                if (trim($_POST['Mg-id']) != '') {
                    if ($this->osm_deleteMap($_POST['Mg-id'])) {
                        $sUrl = $this->url . "&msg=del_ok";
                        echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                        exit;
                    } else {
                        $sUrl = $this->url . "&msg=del_ko&map=" . $_POST["Mg-id"];
                        echo "<script>window.location.replace('" . $sUrl . "');</script>\n";
                        exit;
                    }
                }
            }
        } // -- osm_admin_page()

        // Liste des cartes en base
        function osm_getMapList()
        {
            global $wpdb;

            // +++ TODO : prepare() vraiment nécessaire ??? +++
            $sql = $wpdb->prepare("SELECT * FROM " . $this->table, "");

            // https://developer.wordpress.org/reference/classes/wpdb/get_results
            return $wpdb->get_results($sql);
        } // -- osm_getMapList()

        // Sélection d'une carte (via son id) en base
        function osm_getMap($id)
        {
            global $wpdb;

            /* https://developer.wordpress.org/reference/classes/wpdb/prepare
             * %d = nombre entier (digit)
             */
            $sql = $wpdb->prepare("SELECT * FROM " . $this->table . " WHERE id = %d LIMIT 1", $id);

            // https://developer.wordpress.org/reference/classes/wpdb/get_row
            $map = $wpdb->get_row($sql);

            return $map;
        } // -- osm_getMap()

        // Insertion d'une carte en base
        function osm_insertMap($title, $lat, $long): bool
        {
            global $wpdb;

            /* https://developer.wordpress.org/reference/classes/wpdb/prepare
             *
             * - %s = chaîne (string)
             *
             * Marqueur pour un décimal :
             * https://wordpress.stackexchange.com/questions/385581/how-to-insert-a-value-to-decimal-type-field-using-wpdb-prepare
             */
            $sql = $wpdb->prepare("INSERT INTO " . $this->table . " (titre, latitude, longitude) VALUES (%s, %s, %s)", $title, $lat, $long);

            if ($wpdb->query($sql)) {
                return true;
            }

            return false;
        } // -- osm_insertMap()

        // Modification d'une carte en base
        function osm_updateMap($id, $title, $lat, $long): bool
        {
            global $wpdb;

            $sql = $wpdb->prepare("UPDATE " . $this->table . "
                           SET
                           titre = %s,
                           latitude = %s,
                           longitude = %s
                           WHERE id = %d",
                $title,
                $lat,
                $long,
                $id);

            if ($wpdb->query($sql)) {
                return true;
            }

            return false;
        } // -- osm_updateMap()

        // Suppression d'une carte en base
        function osm_deleteMap($id): bool
        {
            global $wpdb;

            $sql = $wpdb->prepare("DELETE FROM " . $this->table . " WHERE id=%d LIMIT 1", $id);

            if ($wpdb->query($sql)) {
                return true;
            }

            return false;
        } // -- osm_deleteMap()

        // Ajout des fichiers CSS et JS du front
        function osm_front_header(): void
        {
            wp_enqueue_script('leaflet_js', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.js");
            wp_enqueue_style('leaflet_css', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.css");
        } // -- osm_front_header()

        // Création d'un shortcode
        function osm_shortcode($att)
        {
            // On récupère les infos de la carte en base
            $map = $this->osm_getMap($att['id']);

            // Il faut une hauteur min de 400px pour voir la carte
            $html = "<div id='map' style='height:400px;'></div>\n";

            $html .= "<script type='text/javascript'>\n";

            // Chemin vers le fichier 'map.js.replace'
            $jsFile = plugin_dir_path( __FILE__ )."/map.js.replace";

            if (file_exists($jsFile))
            {
                // On récupère le contenu du fichier 'map.js.replace'
                $jsFileContent = file_get_contents($jsFile);

                // On remplace les balises [LATITUDE_PHP] et [LONGITUDE_PHP] par leurs valeurs en BDD
                $jsFileContent = str_replace("[LATITUDE_PHP]", $map->latitude, $jsFileContent);
                $jsFileContent = str_replace("[LONGITUDE_PHP]", $map->longitude, $jsFileContent);

                // On ajoute le code du script JS au HTML à retourner
                $html .= $jsFileContent;

                $html .= "</script>\n";

                // On retourne le HTML
                return $html;
            }

            /* Note : si le fichier 'map.js.replace' n'existe pas, rien n'est retourné (le return se trouvant dans la condition),
            * la balise de shortcode ne sera pas remplacée, elle apparaîtra brute dans le post ([osm id=1])
            */

        } // -- osm_shortcode()

    } // -- classe
} // -- class_exists()

// Instanciation
if (class_exists("MyOsm")) {
    $oMap = new MyOsm();
}

// Si instance créée
if (isset($oMap)) {
    // Sur l'action 'Activer le plugin', exécution de la fonction osm_install()
    // register_activation_hook()
    register_activation_hook(__FILE__, array($oMap, 'osm_install'));

    // Sur l'action 'Désinstaller le plugin', exécution de la fonction osm_uninstall()
    register_deactivation_hook(__FILE__, array($oMap, 'osm_uninstall'));

    // Sur l'action 'Afficher le menu d'admin', exécution de la fonction `osm_init()` de ce fichier
    add_action('admin_menu', array($oMap, 'osm_init'));

    // Ajout du chargement des scripts définis dans la fonction gmap_front_header()
    add_action('wp_enqueue_scripts', array($oMap, 'osm_front_header'));

    // Création d'un shortcode
    add_shortcode('osm', array($oMap, 'osm_shortcode'));
} // -- fin si objet créé