# Speed Reporting

Speed Reporting est une application web conçue pour simplifier la gestion du temps de travail. Elle permet aux salariés de saisir rapidement leurs heures hebdomadaires et offre aux managers des outils pour suivre et analyser la charge de travail de leur équipe grâce à des indicateurs de performance personnalisés.

## Fonctionnalités principales

- **Saisie rapide des heures** : Les employés peuvent enregistrer leurs heures travaillées en quelques clics.
- **Indicateurs de performance** : Les managers disposent de tableaux de bord pour visualiser la productivité et la charge de travail.
- **Gestion d'équipe** : Suivi des projets et répartition des tâches pour une meilleure organisation.
- **Interface intuitive** : Une application facile à utiliser, adaptée à tous les utilisateurs.

## Technologies utilisées

- Frontend : `HTML-CSS-JS`
- Backend : `PHP`
- Base de données : `MySQL`

## Installation

### En développement

1. Installer WAMP ou LAMP sur votre machine locale.
2. Cloner le dépôt :

```bash
git clone  https://github.com/Miche1-Pierre/speed-reporting.git
```

3. Créer un VirtualHost vers `/public` pour le projet dans votre serveur local (WAMP ou LAMP).
4. Renommer le fichier `.env.example` en `.env` et configurer les informations de connexion à la base de données.
5. Initialiser la base de données en éxecutant le fichier PHP `reset_db.php`.

```bash
# Depuis le répertoire du projet
php ./dev/reset_db.php
```

> Admin :
> admin@example.com | password123

> User :
> user@example.com | password123

### Prérequis

- PHP 7.4 ou supérieur
- Composer (pour la gestion des dépendances PHP)

## Configuration

Crée un fichier `.env` à la racine :

```ini
DB_HOST=url_de_la_base_de_donnees
DB_PORT=port_de_la_base_de_donnees
DB_DATABASE=nom_de_la_base_de_donnees
DB_USERNAME=utilisateur_de_la_base_de_donnees
DB_PASSWORD=mot_de_passe_de_la_base_de_donnees
```

## Structure du projet

```bash
speedreporting/
├── public/                 # Contient les fichiers accessibles publiquement (HTML, CSS, JS)
│   ├── assets/             # Ressources statiques (images, styles, scripts)
│   ├── index.php           # Point d'entrée principal de l'application
│   ├── .htaccess           # Configuration du serveur web (Apache)
├── src/                    # Code source de l'application
│   ├── Controllers/        # Contrôleurs pour gérer la logique métier
│   ├── Core/               # Classes de base pour l'application
│   ├── Managers/           # Gestionnaires pour la logique de l'application
│   ├── Models/             # Modèles pour interagir avec la base de données
│   ├── Services/           # Services pour la logique métier
│   ├── Utils/              # Fonctions utilitaires
│   ├── Views/              # Vues pour le rendu HTML
│       ├── partials/       # Composants partiels
│       ├── mails/          # Modèles d'e-mails
├── dev/                    # Fichiers de configuration
│   ├── reset_db.php        # Configuration de la base de données de développement
│   ├── db.sql              # Script SQL pour réinitialiser la base de données
│   ├── data.sql            # Script SQL pour insérer des données de test
├── .env                    # Fichier d'environnement pour les variables sensibles
├── composer.json           # Dépendances PHP
├── composer.lock           # Verrou des dépendances
├── README.md               # Documentation du projet
```

## Fonctionnement technique de l'application

### Strucuture MVC

#### Classes et fichiers

L'application est construite selon le modèle MVC (Modèle-Vue-Contrôleur) pour séparer la logique métier, la présentation et la gestion des données. Voici un aperçu de chaque composant :

- **Modèle** : Contient des classes qui représentent les entités de l'application (ex. : Utilisateur, Projet, Log...).
- **Vue** : Fichiers HTML qui définissent la présentation de l'application. Ils utilisent des composants pour réutiliser le code.
- **Contrôleur** : Gère les requêtes HTTP, interagit avec le modèle et renvoie la vue appropriée. Il contient la logique métier de l'application.
- **Manager** : Gère les modèles et les interactions avec la base de données. Il contient des méthodes pour récupérer, insérer ou mettre à jour des données.
- **Service** : Contient la logique métier de l'application. Il est utilisé par les contrôleurs pour effectuer des opérations spécifiques.
- **Utils** : Contient des fonctions utilitaires pour des tâches courantes (ex. : validation, formatage de données).

#### Point d'entrée principal

Le point d'entrée principal de l'application est le fichier `index.php` situé dans le répertoire `/public`. Ce fichier gère les requêtes entrantes et les redirige vers le contrôleur approprié.
Il inclut également les fichiers de configuration et d'autoloading nécessaires pour charger les classes de l'application.

### Sécurité

#### Gestion des sessions

Les sessions sont gérées à l'aide de la classe `AuthManager`. Cette classe permet de démarrer une session, de stocker des données dans la session et de les récupérer. Elle est utilisée pour gérer l'authentification des utilisateurs et stocker des informations temporaires.
La classe `AuthManager` est initialisée depuis la page de login et est accessible depuis n'importe quel contrôleur ou service de l'application.

La classe `AuthManager` utilise les sessions PHP pour stocker les informations de l'utilisateur connecté. Elle fournit des méthodes pour vérifier si un utilisateur est connecté, récupérer les informations de l'utilisateur et déconnecter l'utilisateur.

#### Données sensibles

Tous les données sensibles (comme les mots de passe) sont stockées de manière sécurisée. Les mots de passe sont hachés à l'aide de la fonction `password_hash()` de PHP avant d'être stockés dans la base de données. Lors de la connexion, le mot de passe fourni par l'utilisateur est comparé au mot de passe haché à l'aide de la fonction `password_verify()`.

#### Validation des formulaires

Les formulaires de l'application sont protégés contre les attaques CSRF (Cross-Site Request Forgery) en utilisant des jetons CSRF. Chaque formulaire contient un champ caché avec un jeton unique qui est vérifié lors de la soumission du formulaire. Cela garantit que seules les requêtes légitimes provenant de l'application sont acceptées.

## Points d'accès de l'application

### Accès : Tous

| URL              | Méthode | Description                               | Paramètres         | Contrôleur |
| ---------------- | -------- | ----------------------------------------- | ------------------- | ----------- |
| `/connexion`   | GET/POST | Page de connexion                         | error,<br />message | Auth        |
| `/deconnexion` | GET      | Déconnexion                              |                     | Auth        |
| `/mdp-oublie`  | GET/POST | Page de réinitialisation de mot de passe |                     | Auth        |

### Accès : Authentifié

| URL            | Méthode | Description                      | Paramètres | Contrôleur |
| -------------- | -------- | -------------------------------- | ----------- | ----------- |
| `/dashboard` | GET      | Tableau de bord de l'utilisateur |             | Dashboard   |

### Accès : User

| URL                     | Méthode | Description                     | Paramètres      | Contrôleur |
| ----------------------- | -------- | ------------------------------- | ---------------- | ----------- |
| `/mes-heures`         | GET/POST | Heures de l'utilisateur         | year,<br />month | Work        |
| `/update/mes-heures`  | POST     | Mettre une entrée à jour      |                  | Work        |
| `/delete/mes-heures`  | POST     | Supprimer une entrée           |                  | Work        |
| `/valider/mes-heures` | POST     | Valider les heures en brouillon |                  | Work        |

### Accès : Admin

| URL                      | Méthode | Description                                  | Paramètres                              | Contrôleur |
| ------------------------ | -------- | -------------------------------------------- | ---------------------------------------- | ----------- |
| `/heures`              | GET      | Liste des heures enregistrées               | year,<br />month,<br />page,<br />search | Work        |
| `/update/heures`       | POST     | Mettre une entrée à jour                   |                                          | Work        |
| `/delete/heures`       | POST     | Supprimer une entrée                        |                                          | Work        |
| `/heures/valider`      | GET/POST | Gestion et validation des heures confirmées | search,<br />page                        | Work        |
| `/utilisateurs`        | GET/POST | Liste des utilisateurs                       | search,<br />page                        | User        |
| `/update/utilisateurs` | POST     | Mettre une entrée à jour                   |                                          | User        |
| `/delete/utilisateurs` | POST     | Supprimer une entrée                        |                                          | User        |
| `/projets`             | GET/POST | Liste des projets                            | search,<br />page                        | Project     |
| `/update/projets`      | POST     | Mettre une entrée à jour                   |                                          | Project     |
| `/delete/projets`      | POST     | Supprimer une entrée                        |                                          | Project     |
| `/clients`             | GET/POST | Liste des clients                            | search,<br />page                        | Client      |
| `/update/clients`      | POST     | Mettre une entrée à jour                   |                                          | Client      |
| `/delete/clients`      | POST     | Supprimer une entrée                        |                                          | Client      |

## Auteurs

- Pierre Michel - [Miche1-Pierre](https://github.com/Miche1-Pierre)
- Schmitt Arthur - [SchArthur](https://github.com/SchArthur)
- Rémy Deiber - [Remy0011](https://github.com/Remy0011)

Stage réalisé dans le cadre d'un stage de 3 mois chez [Synapsia](https://synapsia.fr).
