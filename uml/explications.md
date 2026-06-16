# Explications des diagrammes UML

## 1. Diagramme de cas d'utilisation

Le diagramme de cas d'utilisation presente les interactions principales entre les acteurs et le systeme.
Les acteurs retenus sont l'administrateur, l'utilisateur simple, le capteur IoT et le systeme lui-meme.

L'administrateur gere les salles, les cours, les promotions, les departements, les horaires et lance l'attribution optimale.
Il peut aussi forcer l'etat d'une salle ou simuler une occupation depuis l'interface de simulation IoT.
L'utilisateur consulte principalement l'etat des salles et le planning hebdomadaire.
Le capteur IoT transmet l'etat d'occupation au systeme, qui met ensuite a jour la base de donnees et conserve un historique.

## 2. Diagramme de classes

Le diagramme de classes decrit le modele metier du systeme.
Les principales classes persistantes sont `Salle`, `Cours`, `Promotion`, `Departement`, `Horaire`, `EtatSalle`, `EtatSalleHistory` et `Admin`.

Une promotion appartient a un departement, un cours peut etre rattache a un departement, et un horaire relie une salle, un cours et une promotion.
La classe `EtatSalle` represente l'etat courant d'une salle, tandis que `EtatSalleHistory` garde la trace des changements d'etat.

Des classes de service sont ajoutees pour representer la logique applicative :
`OptimisationService`, `PlanningService` et `EtatSalleService`.
Elles correspondent aux traitements implementes dans les endpoints PHP comme `optimize.php`, `addHoraire.php`, `update_etat_salle.php` et `iot_update.php`.

## 3. Diagramme d'activite - Attribution optimale

Ce diagramme montre le processus suivi lorsqu'un administrateur veut programmer un cours.
Le systeme verifie d'abord les donnees saisies, puis recherche une salle disponible avec une capacite suffisante.
S'il n'y a pas de salle libre, il analyse les conflits existants.

Deux regles importantes sont prises en compte :
- un cours d'ensemble de type "Toutes/Tous" peut remplacer un horaire deja place ;
- sinon, les promotions de niveau superieur sont prioritaires selon l'ordre Bac4 > Bac3 > Bac2 > Bac1.

Lorsqu'un remplacement est autorise, les anciens horaires sont marques comme annules et le nouvel horaire est enregistre.

## 4. Diagramme d'activite - Mise a jour IoT

Ce diagramme decrit le flux de mise a jour d'une salle a partir d'un capteur IoT.
Le capteur detecte une presence ou une absence, l'information est transmise au systeme, puis l'API met a jour l'etat de la salle.
Chaque changement est conserve dans l'historique afin de garder une tracabilite.

## 5. Diagramme de composants

Le diagramme de composants presente l'architecture logicielle.
Le systeme est organise autour de pages PHP, scripts JavaScript, endpoints API et base de donnees MySQL.
La partie IoT est representee par les capteurs, l'Arduino et le bridge Python qui communique avec l'API.

## 6. Diagramme de deploiement

Le diagramme de deploiement montre les noeuds physiques ou logiques :
le navigateur utilisateur, le serveur XAMPP, la base MySQL, le dispositif IoT et le bridge Python.
Les communications se font principalement en HTTP/AJAX entre le navigateur et Apache/PHP, puis en PDO/MySQL vers la base.

## 7. Diagrammes de sequence importants

### 7.1 Flux IoT vers systeme

Ce scenario montre comment l'etat detecte par un capteur arrive dans le systeme.
L'Arduino transmet l'information au bridge Python, qui appelle l'API `iot_update.php`.
L'API verifie la salle, met a jour son etat et cree une ligne d'historique.

### 7.2 Attribution optimale

Ce scenario decrit l'interaction entre l'administrateur, l'interface de planning et les APIs `optimize.php` et `addHoraire.php`.
Il montre la selection d'une salle optimale, la verification des conflits, l'annulation des anciens horaires si necessaire et l'enregistrement du nouveau planning.

### 7.3 Liberation d'une salle

Ce scenario montre ce qui se passe lorsqu'une salle est marquee comme libre depuis la simulation.
Le systeme met a jour l'etat courant et peut annuler des horaires actifs lies a la salle.

### 7.4 Consultation temps reel

Ce scenario montre comment la page de consultation recharge periodiquement les donnees des salles et des horaires afin d'afficher un planning presque temps reel.
