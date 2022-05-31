# Contribuer à Todo List

Bienvenue dans le Guide des contributeurs du projet DocTodo List. Cette documentation vise à expliquer comment les contributeurs et les mainteneurs doivent travailler lors de l'utilisation de git, pendant le processus de développement, la gestion des dépendances, etc... 

* * *

## Table des matières

[:zap: Contributeurs vs mainteneurs](#contributeurs-vs-mainteneurs)

[:memo: Conventions du projet](#conventions-du-projet)

-   [Langue](#langue)
-   [Messages de commits](#messages-de-commits)
-   [Branches](#branches)
-   [Qualité de code](#qualité-de-code)
-   [Issues](#issues)
-   [Pull request](#pull-request)
-   [documentation](#documentation)
-   [Dépendences tierces](#dépendences-tierces)

[:rocket: Workflow](#workflow)

* * *

## :zap: Contributeurs vs mainteneurs

Avant d'aller plus en avant dans la consultation de ce document, il est primordial de distinguer la différence entre un contributeur et un mainteneur.

-   **Contributeur** : 
    Il s'agit d'une personne extérieure à l'équipe de développement du projet, et qui souhaite apporter une modifications au projet.
    Un contributeur peut être n'importe qui ! _Ça pourrait être vous_. Continuez à lire cette section si vous souhaitez vous impliquer et contribuer au projet Todo List. 

-   **Mainteneur** : Il s'agit d'une personne faisant partie de l'équipe de développement du projet et disposant d'un accès de validation au dépot officiel du projet. 

* * *

## :memo: Conventions du projet

Afin de faciliter le développement de Todo List, et l'analyse d l'historique, il est primordial d'uniformiser certains proccess.

Il sera donc requis de respecter les règles qui suivent.

### Langue

L'ensemble des opérations réalisées via `git` ou sur `gitlab` devront être écritent en anglais.

### Commits

-   **Thématique**
    Les commits doivent être thématiques.
    Squashez l'ensemble des commits traitant d'un thème en un seul.

-   **Messages de commits**

    Ils doivent se composer de la manière suivante:
    `émoticone` + `action` + `sujet` (ex: :memo: ADD documentation)

    -   _Emoticones_
        vous trouverez une liste d'émoticone, ainsi que la description du type de commits auquels les rattacher sur [gitemoji.dev](https://gitmoji.dev/).
    -   _Action_
        -   elle doit être rédigée en majuscules.
        -   utilisez un verbe à l'infinitif (ex: CREATE, ADD, REMOVE, UPDATE, FIX, REPLACE).
    -   _Sujet_
        -   il doit être rédigé en minuscules.
        -   il doit être le plus concis possible.

### Branches

-   **Création** :
    Créez les branches à partir du dernier _`merge`_ sur la branche main.

-   **Branches thématiques** :
    Les branches doivent être thématiques.

-   **Nommage des branches** :
    -   Soyez le plus concis possible.
    -   utilisez des minuscules et des underscores.
        ex: _italian_translation_

### Qualité de code

-   **Liste des standards à respecter**

    -   A minima, il vous est demané de respecter les standards suivants :
        -   `PSR-1` : conventions minimales de codage.
        -   `PSR-12` : style et organisation du code.
        -   `PSR-4` : chargement des classes PHP.

    -   Dans l'idéal, vous suivrez la recommandation :
        -   `Symfony Coding Standard` (voir [documentation officielle](#https://symfony.com/doc/5.4/contributing/code/standards.html))

-   **git hook**
    -   [Installez grumphp](#https://github.com/phpro/grumphp)
    -   Configurez un [git:pre-commit](#https://github.com/phpro/grumphp/blob/master/doc/commands.md#git-hooks).

-   **Codacy**
    L'analyse de votre branche devra obtenir une note A sur la plateforme Codacy.

### Issues

Les issues doivent être correctement commentées et documentées.

### Pull Request

-   **Nommage des pull requests**
    `action` + `sujet` (ex: CREATE _my contribution_)
-   **Commentaires**
    Listez l'ensemble des commits en respectant la convention sur les [messages de commits](#commits).

### Documentation

Elle devra être produite au format Markdown.

### Dépendences tierces

L'installation de dépendences tierces se fera obligatoirement via composer.

```bash
composer require <package><version>
```

* * *

## :rocket: Workflow

##### 1. _**Duplication du projet**_.

Reportez-vous à la rubrique [installation](#https://gitlab.com/phil-all/todolist/-/tree/main/README.md#installation) du projet.

##### 2. _**Créez une issue**_.

##### 3. _**Synchronisez**_.

Avant toute session de travail, synchronisez votre dépôt local avec le dépot officiel.

```bash
git pull upstream main
```

##### 4. _**Créez votre branche de travail**_.

##### 5. _**Réalisez vos modifictations**_.

##### 6. _**Testez vos modifications**_.

Reportez-vous aux sections [test](#https://gitlab.com/phil-all/todolist/-/tree/main/README.md#test-environment-for-docker-bash-use) du projet.

##### 7. _**Réalisez un commit de vos modifications**_.

##### 8. _**Pushez vos modifications**_.

##### 9. _**Soumettez une pull request**_.

##### 10. _**Traitement de la pull request**_.

Un mainteneur fusionnera ou fermera la PR.

##### 11. _**Synchronisation**_

Synchronisez la branche main mise à jour avec celle de votre dépot local.
