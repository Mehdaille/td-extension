<?php 
/** -------------------------------------------------------
*  Détail d'une carte dans l'admin (onglet 'nom de la carte) 
* + Formulaires de modification et de suppression d'une carte
* ----------------------------------------------------------
*/
// Carte courante
if (isset($_GET['map'])) 
{    
    // On s'assure que l'id est numérique
    // cf. https://stackoverflow.com/questions/236406/is-there-a-difference-between-is-int-and-ctype-digit
    if (is_numeric($_GET['map'])) 
    {
        $map = $this->osm_getMap($_GET['map']);
    } 
    else 
    {
        $bDisplay = FALSE;     
    }    
} 
else 
{     
    $bDisplay = FALSE;     
}

// Liste des cartes (pour les onglets de navigation)
$mapList = $this->osm_getMapList(); 

// S'il y a des erreurs, on affiche/redirige sur le template 'admin_home.php'
if ( (isset($bDisplay) && $bDisplay === FALSE) || !isset($map) || empty($map) || !isset($mapList) || empty($mapList)) 
{
    $sUrl = $this->url."&msg=tech_ko";   
    echo"<script type='text/javascript'>window.location.replace('".$sUrl."');</script>\n";
    exit;
}

// Tableau des erreurs 
$aErrors = ["empty_ko" => "Tous les champs sont obligatoires.",
            "upd_ok" => "La carte a été mise à jour.",
            "upd_ko" => "La modification de la carte a échoué.",
            "del_ko" => "La suppression de la carte a échoué.",
           ];
?>
<div class="wrap">   
    <h2>My OpenStreetMap</h2>
</div>
<!-- Menu à onglets de l'admin du plugin -->
<div id="menumap">
    <ul>
        <li><a href="<?php echo $this->url; ?>">Créer une carte</a></li>
        <?php  
	    // Une carte = un onglet dans le menu 
        if ($mapList) 
        {
            foreach ($mapList as $m) 
            {       
                if ($_GET['map'] == $m->id) 
                { 
                    $active = " id='active'";
		        }
		        else 
		        {
		          $active = "";
		        }
                   
		    $href = $this->url."&map=".$m->id;
		    echo "<li ".$active."><a href='".$href."'>".$m->titre."</a></li>\n";
           }
      }
      else 
      {
          echo"<div class='msg-ko'>Une erreur est survenue</div>";
          exit;
      }
      ?>
      </ul>
</div> <!--fin #menumap -->  
<div id="contentmap2">
    <h2 class="title" >Carte : <?php echo $map->titre; ?></h2>        
    <?php 
    // Affichage des erreurs + vérif. qu'elles existent bien dans le tableau
    if (isset($_GET["msg"]) && array_key_exists($_GET["msg"], $aErrors)) 
    { 
        // si finit par 'ok' = fond vert, si finit par 'ko' = fond rouge    
        // substr(chaine, -2) extrait les 2 derniers caractères, ici soit 'ok' soit 'ko'
        $color = substr($_GET["msg"], -2);
        echo"<div class='msg-".$color."'>".$aErrors[$_GET["msg"]]."</div>\n";   
    }    
    ?>        
	<!-- Génération et affichage du shortcode --> 
    <div id="placecode">
        Copiez (ctrl+C) le code et collez (ctrl+V) dans la page ou l'article où vous voulez voir apparaître votre carte : 
        <input id="codemap" type="text" value="[osm id=<?php echo $map->id ?>]" readonly>
    </div>
       	 
    <div class="left">
        <h3 class="title">Paramètres :</h3>
        
		<!-- Formulaire de modification -->
        <form action="<?php echo $this->url; ?>&action=updatemap" method="post">        
            <p id="Mg-title-error" style="color:red;display:none;">Entrez un titre, svp</p> 
            <p>Titre* :<br><input type="text" id="Mg-title" name="Mg-title" value="<?php echo $map->titre; ?>"></p>
           
            <p id="Mg-latitude-error" style="color:red;display:none;">Entrez une latitude, svp</p>    
            <p>Latitude* :<br><input type="text" id="Mg-latitude" name="Mg-latitude" value="<?php echo $map->latitude ?>"></p>
              
            <p id="Mg-longitude-error" style="color:red;display:none;">Entrez une longitude, svp</p>     
            <p>Longitude* :<br><input type="text" id="Mg-longitude" name="Mg-longitude" value="<?php echo $map->longitude ?>"></p>
                            
            <input type="hidden" name="Mg-id" id="Mg-id" value="<?php echo $map->id; ?>">
              			 
            <p><input type="submit" name="btn-update" id="bt-map" class="button button-primary" value="Mettre à jour"></p>             
            <small>* champs obligatoires</small>    
        </form>
        
		<!-- Formulaire de suppression -->
        <form action="<?php echo $this->url; ?>&action=deletemap" method="post">
            <input type="hidden" name="Mg-id" value="<?php echo $map->id; ?>">
            <p><input type="submit" name="btn-delete" id="bt-delete" class="button button-primary" value="Supprimer la carte"></p>  
         </form> 
    </div> <!--fin .left -->
    
    <!-- Aperçu de la carte -->
    <div class="left">        
      <h3  class="title" >Aperçu :</h3>
      <!-- class 'map-display' : les cartes doivent avoir une hauteur minimum de 400px pour être affichées -->
      <div id="map" class="map-display"></div>          
         <script type="text/javascript">
         // On initialise la latitude et la longitude de Paris
         // La carte sera centrée sur ce point
         var lat = <?php echo $map->latitude; ?>;
         var lon = <?php echo $map->longitude; ?>;
         var macarte = null;
        
         // Fonction d'initialisation de la carte
         function initMap() {
            // Créer l'objet "macarte" et l'insèrer dans l'élément HTML qui a l'ID "map"
            macarte = L.map('map').setView([lat, lon], 11);
        
            // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
            L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', 
        	{
        		// Lien vers la source des données
        		attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
        		minZoom: 1,
        		maxZoom: 20
            }).addTo(macarte);
        	
        	// Nous ajoutons un marqueur (= punaise)
        	var marker = L.marker([lat, lon]).addTo(macarte);
        }
        
        // Fonction d'initialisation qui s'exécute lorsque le DOM est chargé
        window.onload = function() {
        	initMap(); 
        };
        </script>  
                   
    </div> <!--fin .left -->
</div><!--fin #contentmap2-->   