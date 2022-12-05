<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

//https://code.google.com/archive/p/jsonpath/wikis/PHP.wiki

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__  . '/../../vendor/autoload.php';

class json extends eqLogic {
  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom */
  public static function cron() {
    foreach (eqLogic::byType('json', true) as $eqLogic) {
      $autorefresh = $eqLogic->getConfiguration('autorefresh');
      if ($eqLogic->getIsEnable() == 1 && $autorefresh != '') {
        try {
          $c = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
          if ($c->isDue()) {
            $eqLogic->calculate();
            /*foreach (($eqLogic->getCmd()) as $cmd) {
              if ($cmd->getType() == 'info') {
                $cmd->execute();
              }
            }*/   
          }
        } catch (Exception $exc) {
          log::add('json', 'error', __('Expression cron non valide pour', __FILE__) . ' ' . $eqLogic->getHumanName() . ' : ' . $autorefresh);
        }
      }
    }
  }
    
  private static function headersTab2String($_headers = array()) {
    $result = "";
    foreach ($_headers as $key => $value) {
      $result = "$key: $value\n\r";
    }
    return $result;
  }

  private static function headersString2Tab($_headers = "") {
    $result = array();
    foreach (explode("\n", trim(str_replace("\r\n", "\n", $_headers))) as $header) {
      $h = explode(":", $header, 2);
      $result[trim($h[0])] = trim($h[1]);
    }
    log::add('json', 'debug', "headersString2Tab($_headers) = " . json_encode($result));
    return $result;
  }

  public function calculate($_options = array()) {
      log::add('json', 'debug', "calculate " . $this->getHumanName());
      
      $url = $this->getConfiguration('jsonUrl');

      $headers = json::headersString2Tab($this->getConfiguration('headers'));
      if ($this->getConfiguration('authentication-type') == 'http-basic-authentication') {
        $username = $this->getConfiguration('authentication-username');
        $password = $this->getConfiguration('authentication-password');
        $headers["Authorization"] = "Basic " . base64_encode("$username:$password");
      }

      $opts = array(
        'http'=>array(
          'method'=> "GET",
          'header'=> json::headersTab2String($headers),
          'protocol_version' => 1.1
        )
      );

      log::add('json', 'debug', "Appel de $url");
      log::add('json', 'debug', "Options : " . json_encode($opts));
      
      $context = stream_context_create($opts);
      $data = json_decode(file_get_contents($url, false, $context));
      
      foreach (($this->getCmd()) as $cmd) {
        if ($cmd->getType() == 'info') {
          $path = $cmd->getLogicalId();
          log::add('json', 'debug', "JsonPath : $path");
          $res = (new \Flow\JSONPath\JSONPath($data))->find($path)->getData();
          if (is_array($res) && count($res) == 1 && !(is_object($res[0]) || is_array($res[0]))) {
            $res = $res[0];
          }
          if (is_object($res) || is_array($res)) {
            $res = json_encode($res);
          }
          try {
            $cmd->event($res);
          } catch (Exception $exc) {
            log::add('json', 'error', __('Problème event. Résultat trop long ?', __FILE__) . ' ' . $this->getHumanName() . ' : ' . $autorefresh);
          }
          log::add('json', 'debug', "Res : $res");
          log::add('json', 'info', "$path : $res");
        }
      }
  }

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {}
  */

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*     * **********************Getteur Setteur*************************** */

}

class jsonCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
      log::add('json', 'debug', "Execute " . $this->getLogicalId() . ' on ' . $this->getEqLogic()->getHumanName());
  }

  /*     * **********************Getteur Setteur*************************** */

}
