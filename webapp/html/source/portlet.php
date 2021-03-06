<?php
/**
 * This file is part of GrottoCenter.
 *
 * GrottoCenter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GrottoCenter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with GrottoCenter.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright (c) 2009-2012 Clément Ronzon
 * @license http://www.gnu.org/licenses/agpl.txt
 */
include("../conf/config.php");
include("../func/function.php");
include("declaration.php");
?>
<?php echo getDoctype(false)."\n"; ?>
<html <?php echo getHTMLTagContent(); ?>>
  <head>
    <script type="text/javascript" src="<?php echo getScriptJS(__FILE__); ?>"></script>
    <script type="text/javascript" src="../scripts/calendar.js"></script>
<?php
  	include("application_".$_SESSION['language'].".php");
  	include("mailfunctions_".$_SESSION['language'].".php");

    // EQUATION SQL POUR LE CALCUL DES ENTREES DANS UN RAYON R AUTOUR DU POINT (A,B) :
    // SELECT ROUND(6378*(PI()/2-ASIN(SIN(Latitude*2*PI()/360)*SIN(A*2*PI()/360)+COS(Longitude*2*PI()/360-B*2*PI()/360)*COS(Latitude*2*PI()/360)*COS(A*2*PI()/360))),2) radius FROM T_entry HAVING radius <= R ORDER BY radius 
		// FUNCTION : XTD_GET_RADIUS_FCT(centerLat DOUBLE, centerLng DOUBLE, varLat DOUBLE, varLng DOUBLE)
    
    //SYNTAXE : SELECT field1_for_select AS `field1_for_where|field1_label[|field1_list_querry]`, ...
    //in field1_for_where, please replace . by * and spaces by @
    
    $type = (isset($_GET['type'])) ? $_GET['type'] : '';
    
    //Set the filter for overview and switch to map view. 
    if (isset($_POST['overview_filter'])) { //Save the selected filter, then close the window
      $_SESSION[$type.'_load_conditions'] = getWhereClause($_POST);
      $_SESSION[$type.'_filter_set'] = $_POST;
      $_SESSION['advanced_filter'] = "true";
      $_SESSION[$type.'_filter'] = "true";
      $switch_to_overview = true;
    } else {
      $switch_to_overview = false;
    }
?>
<?php if ($switch_to_overview) { ?>
    <script type="text/javascript">
    <?php echo getCDataTag(true); ?>
    window.top.location = "../index.php?home_page=overview&advanced=true&<?php echo $type; ?>=true";
    <?php echo getCDataTag(false); ?>
    </script>
  </head>
  <body>
  </body>
</html>
<?php   exit();
      } ?>
<?php
    switch ($type) {
      case "entry":
        $sql = "SELECT DISTINCT ";
        $sql .= "T_entry.Id AS `0`, ";
        $sql .= "T_entry.Name AS `1`, ";//Nom de la cavitÃ©
//        $sql .= "IF((T_topography.Id IS NOT NULL),'<convert>#label=626<convert>','<convert>#label=627<convert>') AS `2`, ";//Topographies
        $sql .= "IF(T_entry.Has_contributions='YES','<convert>#label=626<convert>','<convert>#label=627<convert>') AS `3`, ";//Fiche dÃ©taillÃ©e
        $sql .= "IFNULL(T_cave.Depth,T_single_entry.Depth) AS `4`, ";//DÃ©nivellation
        $sql .= "IFNULL(T_cave.Length,T_single_entry.Length) AS `5`, ";//DÃ©veloppement
        $sql .= "ROUND(T_entry.Latitude,5) AS `6`, ";//Latitude
        $sql .= "ROUND(T_entry.Longitude,5) AS `7`, ";//Longitude
				$sql .= "T_country.".$_SESSION['language']."_name AS `8`, ";//Pays
        $sql .= "T_entry.Region AS `9`, ";//Etat/RÃ©gion
        $sql .= "T_entry.City AS `10`, ";//Commune
//CRO 2011-10-12
//        $sql .= "T_massif.Name AS `11`, ";//Massif
        $sql .= "T_cave.Name AS `12`, ";//RÃ©seau
        $sql .= "T_type.".$_SESSION['language']."_type AS `13`, ";//Type de cavitÃ©
        $sql .= "IF(IFNULL(T_cave.Is_diving,T_single_entry.Is_diving)='YES','<convert>#label=626<convert>','<convert>#label=627<convert>') AS `14`, ";//PlongÃ©e sout.
        $sql .= "ROUND(V_entry_avg.Aestheticism,1) AS `15`, ";//EsthÃ©tisme (moy. sur 10)
        $sql .= "ROUND(V_entry_avg.Caving,1) AS `16`, ";//FacilitÃ© de progression (moy. sur 10)
        $sql .= "ROUND(V_entry_avg.Approach,1) AS `17`, ";//FacilitÃ© d'accÃ¨s (moy. sur 10)
        $sql .= "T_entry.Year_discovery AS `18` ";//AnnÃ©e de dÃ©couverte
        $sql .= "FROM `".$_SESSION['Application_host']."`.`T_entry` ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`J_cave_entry` ON J_cave_entry.Id_entry = T_entry.Id ";
//CRO 2011-10-12
//        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`J_massif_cave` ON (J_massif_cave.Id_cave = J_cave_entry.Id_cave OR J_massif_cave.Id_entry = T_entry.Id) ";
//        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_massif` ON T_massif.Id = J_massif_cave.Id_massif ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_cave` ON T_cave.Id = J_cave_entry.Id_cave ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_type` on T_type.Id = T_entry.Id_type ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_country` ON T_country.Iso = T_entry.Country ";
/*        $sql .= "LEFT OUTER JOIN (SELECT T_topography.*, J_topo_cave.Id_cave, J_topo_cave.Id_entry ";
        $sql .= "FROM `".$_SESSION['Application_host']."`.`T_topography` ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`J_topo_cave` ON (T_topography.Id = J_topo_cave.Id_topography AND T_topography.Enabled = 'YES')) T_topography ";
        $sql .= "ON ((T_topography.Id_cave = J_cave_entry.Id_cave OR T_topography.Id_entry = T_entry.Id) ";
        if (USER_IS_CONNECTED) {
        } else {
          $sql .= "AND T_topography.Is_public = 'YES' ";
        }
        $sql .= ") ";*/
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_single_entry` ON T_single_entry.Id = T_entry.Id ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`V_entry_avg` ON T_entry.Id = V_entry_avg.Id_entry ";
        $category = $type;
        //$entry_file_link = $_SESSION['Application_url']."/html/file_".$_SESSION['language'].".php?lang=".$_SESSION['language']."&amp;check_lang_auto=false&amp;category=".$category."&amp;id=<Id>";
        $entry_file_link = "javascript:detailMarker(event,'entry','<Id>','".$_SESSION['language']."',true);";
        $links = array (
                1 => array(
                    'conditions' =>  array(),
                    'parameters' => array(
                                    '<Id>' => 0),
                    'link' => $entry_file_link,
                    'target' => 'onclick')/*,
                13 => array(
                    'conditions' =>  array(
                                    13 => '<convert>#label=626<convert>'),
                    'parameters' => array(
                                    '<Id>' => 0),
                    'link' => $entry_file_link,
                    'target' => 'onclick')*/);
				$columns_params = array(
					0 => "[hidden]|[hidden]Id",
					1 => "T_entry*Name|<convert>#label=613<convert>",
//					2 => "IF((T_topography*Id@IS@NOT@NULL),'<convert>#label=626<convert>','<convert>#label=627<convert>')|<convert>#label=815<convert>|<convert>#label=626<convert>;<convert>#label=627<convert>",
					2 => "[hidden]|[hidden]Topography",
					3 => "IF(T_entry*Has_contributions='YES','<convert>#label=626<convert>','<convert>#label=627<convert>')|<convert>#label=614<convert>|<convert>#label=626<convert>;<convert>#label=627<convert>",
					4 => "IFNULL(T_cave*Depth,T_single_entry*Depth)|<convert>#label=64<convert>",
					5 => "IFNULL(T_cave*Length,T_single_entry*Length)|<convert>#label=68<convert>",
					6 => "T_entry*Latitude|<convert>#label=103<convert>",
					7 => "T_entry*Longitude|<convert>#label=105<convert>",
					8 => "T_entry*Country|<convert>#label=98<convert>|SELECT Iso AS value,".$_SESSION['language']."_name AS text FROM ".$_SESSION['Application_host'].".T_country ORDER BY text",
					9 => "T_entry*Region|<convert>#label=100<convert>",
					10 => "T_entry*City|<convert>#label=101<convert>",
//CRO 2011-10-12
//					11 => "T_massif*Name|<convert>#label=555<convert>",
					11 => "[hidden]|[hidden]Massif_name",
					12 => "T_cave*Name|<convert>#label=119<convert>",
					13 => "T_entry*Id_type|<convert>#label=114<convert>|SELECT Id AS value,".$_SESSION['language']."_type AS text FROM ".$_SESSION['Application_host'].".T_type ORDER BY text",
					14 => "IF(IFNULL(T_cave*Is_diving,T_single_entry*Is_diving)='YES','<convert>#label=626<convert>','<convert>#label=627<convert>')|<convert>#label=71<convert>|<convert>#label=626<convert>;<convert>#label=627<convert>",
					15 => "V_entry_avg*Aestheticism|<convert>#label=615<convert>",
					16 => "V_entry_avg*Caving|<convert>#label=616<convert>",
					17 => "V_entry_avg*Approach|<convert>#label=617<convert>",
					18 => "T_entry*Year_discovery|<convert>#label=109<convert>"
				);
      break;
      case "grotto":
        $sql = "SELECT DISTINCT ";
        $sql .= "T_grotto.Id AS `0`, ";
        $sql .= "T_grotto.Name AS `1`, ";//Nom du club
        $sql .= "T_country.".$_SESSION['language']."_name AS `2`, "; //Pays
        $sql .= "T_grotto.Region AS `3`, ";//Etat/RÃ©gion
        $sql .= "T_grotto.City AS `4`, ";//Commune
        $sql .= "COUNT(J_grotto_caver.Id_caver) AS `5` ";//Nombre de spÃ©lÃ©ologues
        $sql .= "FROM `".$_SESSION['Application_host']."`.`T_grotto` ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_country` ON T_country.Iso = T_grotto.Country ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`J_grotto_caver` ON J_grotto_caver.Id_grotto = T_grotto.Id ";
        $sql .= "GROUP BY T_grotto.Id ";
        $links = array ();
				$columns_params = array(
					0 => "[hidden]|[hidden]Id",
					1 => "T_grotto*Name|<convert>#label=144<convert>",
					2 => "T_grotto*Country|<convert>#label=98<convert>|SELECT Iso AS value,".$_SESSION['language']."_name AS text FROM ".$_SESSION['Application_host'].".T_country ORDER BY text",
					3 => "T_grotto*Region|<convert>#label=100<convert>",
					4 => "T_grotto*City|<convert>#label=101<convert>",
					5 => "[hidden]|<convert>#label=663<convert>"
				);
      break;
      case "caver":
				$columns_params = array(
					0 => "[hidden]|[hidden]Id",
					1 => "T_caver*Nickname|<convert>#label=578<convert>",
					2 => "T_caver*Surname|<convert>#label=200<convert>",
					3 => "T_caver*Name|<convert>#label=199<convert>",
					5 => "T_caver*Country|<convert>#label=98<convert>|SELECT Iso AS value,".$_SESSION['language']."_name AS text FROM ".$_SESSION['Application_host'].".T_country ORDER BY text",
					11 => "T_grotto*Name|<convert>#label=386<convert>"
				);
        $sql = "SELECT DISTINCT ";
        $sql .= "T_caver.Id AS `0`, ";
        $sql .= "T_caver.Nickname AS `1`, ";//Pseudo
        $sql .= "T_caver.Surname AS `2`, ";//PrÃ©nom
        $sql .= "T_caver.Name AS `3`, ";//Nom
        if (USER_IS_CONNECTED) {
					$columns_params[4] = "IF(T_caver*Contact_is_public@in@(1,2),T_caver*Contact,NULL)|<convert>#label=146<convert>";
          $sql .= "IF(T_caver.Contact_is_public in (1,2),T_caver.Contact,NULL) AS `4`, ";//E-mail
        } else {
					$columns_params[4] = "IF(T_caver*Contact_is_public=1,T_caver*Contact,NULL)|<convert>#label=146<convert>";
          $sql .= "IF(T_caver.Contact_is_public in (2),T_caver.Contact,NULL) AS `4`, ";//E-mail
        }
        $sql .= "T_country.".$_SESSION['language']."_name AS `5`, "; //Pays
        if (USER_IS_CONNECTED) {
					$columns_params[6] = "IF(T_caver*Contact_is_public@in@(1,2),T_caver*Region,NULL)|<convert>#label=100<convert>";
          $sql .= "IF(T_caver.Contact_is_public in (1,2),T_caver.Region,NULL) AS `6`, ";//Etat/RÃ©gion
        } else {
					$columns_params[6] = "IF(T_caver*Contact_is_public=1,T_caver*Region,NULL)|<convert>#label=100<convert>";
          $sql .= "IF(T_caver.Contact_is_public in (2),T_caver.Region,NULL) AS `6`, ";//Etat/RÃ©gion
        }
        if (USER_IS_CONNECTED) {
					$columns_params[7] = "IF(T_caver*Contact_is_public@in@(1,2),T_caver*Postal_code,NULL)|<convert>#label=145<convert>[hidden]";
					$columns_params[8] = "IF(T_caver*Contact_is_public@in@(1,2),T_caver*City,NULL)|<convert>#label=101<convert>[hidden]";
					$columns_params[9] = "[hidden]|<convert>#label=145<convert> - <convert>#label=101<convert>";
					$columns_params[10] = "IF(T_caver*Contact_is_public@in@(1,2),T_caver*Address,NULL)|<convert>#label=102<convert>";
          $sql .= "T_caver.Postal_code AS `7`, ";//CP
          $sql .= "T_caver.City AS `8`, ";//Commune
          $sql .= "IF(T_caver.Contact_is_public in (1,2),CONCAT_WS(' - ',T_caver.Postal_code,T_caver.City),NULL) AS `9`, ";//CP - Commune
          $sql .= "IF(T_caver.Contact_is_public in (1,2),T_caver.Address,NULL) AS `10`, ";//Adresse
        } else {
					$columns_params[7] = "IF(T_caver*Contact_is_public=1,T_caver*Postal_code,NULL)|<convert>#label=145<convert>[hidden]";
					$columns_params[8] = "IF(T_caver*Contact_is_public=1,T_caver*City,NULL)|<convert>#label=101<convert>[hidden]";
					$columns_params[9] = "[hidden]|<convert>#label=145<convert> - <convert>#label=101<convert>";
					$columns_params[10] = "IF(T_caver*Contact_is_public=1,T_caver*Address,NULL)|<convert>#label=102<convert>";
          $sql .= "T_caver.Postal_code AS `7`, ";//CP
          $sql .= "T_caver.City AS `8`, ";//Commune
          $sql .= "IF(T_caver.Contact_is_public in (2),CONCAT_WS(' - ',T_caver.Postal_code,T_caver.City),NULL) AS `9`, ";//CP - Commune
          $sql .= "IF(T_caver.Contact_is_public in (2),T_caver.Address,NULL) AS `10`, ";//Adresse
        }
        $sql .= "GROUP_CONCAT(DISTINCT T_grotto.Name ORDER BY T_grotto.Name SEPARATOR ', ') AS `11` ";//Clubs
        $sql .= "FROM `".$_SESSION['Application_host']."`.`T_caver` ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_country` ON T_country.Iso = T_caver.Country ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`J_grotto_caver` ON J_grotto_caver.Id_caver = T_caver.Id ";
        $sql .= "LEFT OUTER JOIN `".$_SESSION['Application_host']."`.`T_grotto` ON T_grotto.Id = J_grotto_caver.Id_grotto ";
        $sql .= "GROUP BY T_caver.Id ";
        $links = array (
                4 => array(
                    'conditions' =>  array(),
                    'parameters' => array(
                                    '<email>' => 4),
                    'link' => "mailto:<email>",
                    'target' => ''));
      break;
      case "url":
        $sql = "SELECT DISTINCT ";
        $sql .= "T_url.Id AS `0`, ";
        $sql .= "T_url.Name AS `1`, "; //Nom du site
        $sql .= "T_url.Url AS `2`, "; //Url
        $sql .= "T_url.Comments AS `3` "; //Commentaires
        $sql .= "FROM `".$_SESSION['Application_host']."`.`T_url` ";
        $links = array (
                1 => array(
                    'conditions' =>  array(),
                    'parameters' => array(
                                    '<Url>' => 2),
                    'link' => "<Url>",
                    'target' => '_blank'));
				$columns_params = array(
					0 => "[hidden]|[hidden]Id",
					1 => "T_url*Name|<convert>#label=671<convert>",
					2 => "T_url*Url|<convert>#label=672<convert>",
					3 => "T_url*Comments|<convert>#label=638<convert>"
				);
      break;
		  default:
		    exit();
		  break;
    }
?>

<?php
    $length_page = 10;
    $records_by_page_array = array(5, 10, 15, 20, 25, 30, 40, 50);
    $records_by_page = (isset($_POST['records_by_page'])) ? $_POST['records_by_page'] : 15;
    $filter_form = "automatic_form";
    $list_form = "result_form";
    $input_type = array(
            'type' => '',
            'conditions' => array());
    $style = array();
    $default_order = 2;
    $result = getRowsFromSQL($sql, $columns_params, $links, $records_by_page, $filter_form, $list_form, $_POST, $input_type, $style, $default_order, true, true, "");
    $resource_id = $result['resource_id'];
    $filter_fields = getFilterFields($sql,$columns_params,$_POST,$filter_form,"<convert>#label=542<convert>", false, $resource_id);//Tous
    $rows = $result['rows'];
    $total_count = $result['total_count'];
    $local_count = $result['local_count'];
    $count_page = ceil($total_count/$records_by_page);
    $current_page = (isset($_POST['current'])) ? $_POST['current'] : 1;
    $order = (isset($_POST['order'])) ? $_POST['order'] : '';
    $by = (isset($_POST['by'])) ? $_POST['by'] : $default_order;
    if ($total_count > 0) {
      $navigator = getPageNavigator($length_page, $current_page, $count_page, $filter_form);//$base_url);
    } else {
      $navigator = "";
    }
    $mapCategories = array("entry","grotto","caver");
?>
    <?php echo getMetaTags(); ?>
    <title><?php echo $_SESSION['Application_title']; ?> <convert>#label=618<convert></title>
    <link rel="stylesheet" type="text/css" href="../css/global.css" />
    <link rel="stylesheet" type="text/css" href="../css/global_p.css" media="print" />
    <link rel="stylesheet" type="text/css" href="../css/portlet.css" />
    <link rel="stylesheet" type="text/css" href="../css/tab.css" />
    <!--[if IE]><link rel="stylesheet" type="text/css" href="../css/tab_ie.css" /><![endif]-->
    <!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="../css/tab_ie6.css" /><![endif]-->
    <script type="text/javascript">
    <?php echo getCDataTag(true); ?>
      
    function load() {
      mySite.setSessionTimer("<?php echo USER_IS_CONNECTED; ?>");
      mySite.details.switchDetails(true);
    }
    
    function unload() {
      mySite.details.switchDetails(false);
    }
      
    <?php echo getCDataTag(false); ?>
    </script>
  </head>
  <body onload="JavaScript:load();" onunload="JavaScript:unload();" >
    <?php echo getTopFrame(); ?>
    <?php echo getNoScript("<convert>#label=22<convert>","<convert>#label=23<convert>"); ?>
		<?php echo getCloseBtn("home_".$_SESSION['language'].".php","<convert>#label=371<convert>"); ?>
		<div class="frame_title"><?php echo setTitle("portlet_".$_SESSION['language'].".php?type=".$type, "home", "<convert>#label=607<convert>", 2); ?></div><!--Listes des Ã©lÃ©ments-->
		<?php if (in_array($type,$mapCategories)) { ?>
    <div class="tab">
		  <ul>
			  <li <?php if($type == "entry") { ?>id="actif"<?php } ?>><?php if($type != "entry") { ?><a href="?type=entry"><?php } ?><span><img src="../images/icons/entry2.png" alt="" style="height:9pt;margin:0px 3px;border:0px none;" /> <convert>#label=48<convert><!--CavitÃ©s--></span><?php if($type != "entry") { ?></a><?php } ?></li>
        <li <?php if($type == "grotto") { ?>id="actif"<?php } ?>><?php if($type != "grotto") { ?><a href="?type=grotto"><?php } ?><span><img src="../images/icons/grotto1.png" alt="" style="height:9pt;border:0px none;" /> <convert>#label=386<convert><!--Clubs--></span><?php if($type != "grotto") { ?></a><?php } ?></li>
        <li <?php if($type == "caver") { ?>id="actif"<?php } ?>><?php if($type != "caver") { ?><a href="?type=caver"><?php } ?><span><img src="../images/icons/caver2.png" alt="" style="height:9pt;border:0px none;" /> <convert>#label=385<convert><!--SpÃ©lÃ©os--></span><?php if($type != "caver") { ?></a><?php } ?></li>
      </ul>
    </div>
    <?php } ?>
    <div>
      <form id="<?php echo $filter_form; ?>" name="<?php echo $filter_form; ?>" method="post" action="">
        <table border="0" cellspacing="0" cellpadding="0" id="filter_set">
          <tr><td colspan="2"><convert>#label=601<convert><!--Pour rechercher une partie d'un mot utiliser le caractÃ¨re *, ex: *erre pourrais retourner Pierre ou Terre etc.--></td></tr>
          <?php echo $filter_fields; ?>
        </table>
        <input type="hidden" name="current" value="" />
        <input type="hidden" name="order" value="<?php echo $order; ?>" />
        <input type="hidden" name="by" value="<?php echo $by; ?>" />
        <input type="submit" name="submit_filter" class="button1" value="<convert>#label=602<convert>" /><!--Filtrer-->
        <input type="submit" name="reset_filter" class="button1" value="<convert>#label=603<convert>" onclick="JavaScript:resetForm(this.form);" /><!--Tout afficher-->
        <input type="button" name="reset" class="button1" value="<convert>#label=604<convert>" onclick="JavaScript:resetForm(this.form);" /><!--Effacer-->
        <?php if (in_array($type,$mapCategories)) { ?>
        <input type="submit" name="overview_filter" class="button1" value="<convert>#label=646<convert>" /><!--Utiliser ces critÃ¨res-->
        <?php } ?>
        <br /><select class="select2" name="records_by_page" id="records_by_page" onchange="JavaScript:this.form.submit();">
          <?php echo getOptionsFromArray($records_by_page_array,"",$records_by_page); ?>
        </select> <convert>#label=664<convert><!--Lignes par page-->.
      </form>
    </div>
    <?php if ($local_count >= $records_by_page) { ?>
    <div class="navigator">
      <?php echo $navigator; ?>
    </div>
    <?php } ?>
    <div>
      <form id="<?php echo $list_form; ?>" name="<?php echo $list_form; ?>" method="post" action="">
        <table border="0" cellspacing="1" cellpadding="0" id="result_table">
          <?php if ($total_count > 0) { echo $rows; } else { ?><convert>#label=622<convert><!--Aucun rÃ©sultat n'est disponible--><?php } ?>
        </table>
      </form>
    </div>
    <div class="navigator">
      <?php echo $navigator; ?>
    </div>
    <convert>#label=605<convert><!--Nb total de rÃ©sultats--> : <?php echo $total_count; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<convert>#label=606<convert><!--Nb total de pages--> : <?php echo $count_page; ?><br />
    <?php echo getBotFrame(); ?>
<?php
    $virtual_page = "home/".$_SESSION['language'];
    include_once "../func/suivianalytics.php";
?>
  </body>
</html>
