# Plugin JSON (Jeedom)

Ce plugin pour **Jeedom** (développé par [scanab](https://github.com/scanab)) permet de récupérer des données depuis une source distante au format JSON et d'extraire des informations spécifiques via des requêtes **JSONPath**.

Il est optimisé pour ne réaliser qu'une seule requête HTTP pour l'ensemble des commandes d'un même équipement, réduisant ainsi la charge réseau et processeur.

---

## 1. Présentation

Le plugin JSON est un outil de "parsing" (analyse) de flux. Il est particulièrement utile pour :
* Consommer des API REST (Météo, Domotique tierce, Pollen, etc.).
* Interroger des objets connectés locaux qui exposent un état en JSON.
* Centraliser la récupération de données complexes en une seule requête.

---

## 2. Installation

L'installation est standard pour Jeedom :

1.  Rendez-vous dans le menu **Plugins** > **Gestion des plugins**.
2.  Cliquez sur le bouton **Market**.
3.  Recherchez "JSON".
4.  Installez le plugin de l'auteur **scanab**.
5.  Une fois l'installation terminée, cliquez sur **Activer**.

> [!NOTE]
> Le plugin ne nécessite aucune dépendance système particulière ni de démon (service) en arrière-plan.

---

## 3. Configuration des équipements

Pour créer un nouvel équipement, rendez-vous dans le menu **Plugins** > **Programmation** > **JSON**.

### Onglet Équipement
Renseignez les paramètres de connexion à votre source de données :

* **Nom de l'équipement** : Identifiant de votre capteur/API.
* **Objet parent** : Pièce de destination dans Jeedom.
* **Auto-actualisation (cron)** : Fréquence de mise à jour (ex: `*/15 * * * *` pour 15 min). Laissez vide pour une actualisation manuelle uniquement.
* **URL** : L'adresse complète (http/https) du flux JSON.
* **Méthode** : `GET` (lecture standard) ou `POST` (envoi de données).
* **Headers** : Si l'API nécessite une authentification ou des paramètres spécifiques.
    * *Exemple :* `Authorization: Bearer VOTRE_TOKEN`
    * *Exemple :* `Content-Type: application/json`

### Onglet Commandes
C'est ici que vous définissez les informations que vous souhaitez extraire du flux global récupéré.

1.  Cliquez sur **Ajouter une commande info**.
2.  **Nom** : Nom de la commande (ex: "Température").
3.  **Sous-type** : Choisissez entre `Numérique`, `Binaire` ou `Autre` (pour du texte).
4.  **Expression JSONPath** : Le chemin d'accès à la donnée.

---

## 4. Aide JSONPath

Le plugin utilise la syntaxe standard **JSONPath**. Voici les opérateurs principaux pour construire vos requêtes :

| Symbole | Description | Exemple |
| :--- | :--- | :--- |
| `$` | Racine du document JSON | `$` |
| `.` | Accès à un membre enfant | `$.meteo.ville` |
| `[]` | Accès à un index de tableau (commence à 0) | `$.capteurs[0].valeur` |
| `..` | Recherche profonde (partout dans le JSON) | `$..temperature` |
| `*` | Joker (tous les éléments) | `$.maison.*` |

### Exemple de Parsing
Si le flux JSON reçu est :
```json
{
  "station": "Paris",
  "mesures": [
    {"type": "temp", "val": 22.5},
    {"type": "hum", "val": 45}
  ]
}
```
* Pour extraire la **ville** : $.station
* Pour extraire la **température** : $.mesures[0].val
* Pour extraire l'**humidité** : $.mesures[1].val

## 5. FAQ
**Pourquoi ma commande affiche-t-elle "Vide" ?**

Vérifiez que l'URL est correcte et accessible depuis votre box Jeedom.

**Assurez-vous que l'équipement est Activé.**

Testez votre expression JSONPath sur un testeur en ligne avec le contenu de votre flux.

**Peut-on envoyer des données en POST ?**
Oui, en sélectionnant la méthode POST dans la configuration de l'équipement. Vous pouvez alors passer les paramètres nécessaires via l'URL ou les Headers selon l'API.

**Comment forcer la mise à jour des données ?**
Utilisez la commande Rafraîchir générée automatiquement sur l'équipement ou via un scénario.

## 6. Changelog
Les notes de version et l'historique des modifications sont disponibles sur le [dépôt GitHub officiel](changelog.md).
