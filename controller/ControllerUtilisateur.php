<?php
//CONTROLLER : recevoir le formulaire, demander les données au model pour les fournir à la view

//Importer les ressources
include_once '../model/ModelUtilisateur.php';
include_once '../view/ViewUtilisateur.php';

//Class Controller
class ControllerUtilisateur{
    //ATTRIBUTS
    private ModelUtilisateur $model;
    private ViewUtilisateur $view;

    //CONSTRUCTEUR
    public function __construct(ModelUtilisateur $newModel, ViewUtilisateur $newView){
        $this->model = $newModel;
        $this->view = $newView;
    }

    //GETTER ET SETTER
    public function getModel(): ModelUtilisateur {
        return $this->model;
    }

    public function setModel(ModelUtilisateur $model): self {
        $this->model = $model;
        return $this;
    }

    public function getView(): ViewUtilisateur {
        return $this->view;
    }

    public function setView(ViewUtilisateur $view): self {
        $this->view = $view;
        return $this;
    }

    //METHODS
    //Vérifier les données saisies à l'inscription
    private function verifierInscription(array $valeurs, string $mdp, string $confirm): array {
        $erreurs = [];

        // Nom : non vide 
        if (empty($valeurs['nom']) ) {
            $erreurs[] = 'Le nom est obligatoire .';
        }

        // Prénom 
        if (empty($valeurs['prenom'])  ) {
            $erreurs[] = 'Le prénom est obligatoire .';
        }

        // Date de naissance 
        $date = DateTime::createFromFormat('Y-m-d', $valeurs['naissance']);
        if (!$date || $date->format('Y-m-d') !== $valeurs['naissance'] || $date > new DateTime()) {
            $erreurs[] = 'Date de naissance invalide.';
        }

        // E-mail : non vide et format valide
        if (empty($valeurs['email']) || !filter_var($valeurs['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'Adresse e-mail invalide.';
        } elseif ($this->getModel()->findByEmail($valeurs['email']) !== null) {
            $erreurs[] = 'Cette adresse e-mail est déjà utilisée.';
        }

        // Mot de passe : 12 caractères minimum, et deux saisies identiques
        if (mb_strlen($mdp) < 12) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 12 caractères.';
        }
        if ($mdp !== $confirm) {
            $erreurs[] = 'Les deux mots de passe ne correspondent pas.';
        }

        // Conditions d'utilisation : acceptation obligatoire
        if (!isset($_POST['cgu'])) {
            $erreurs[] = 'Vous devez accepter les conditions d\'utilisation.';
        }

        return $erreurs;
    }

    //Lancer le rendu de la page
    public function render(): void {
        session_start();

        $erreurs = [];
        $message = '';
        $valeurs = [];

        //1. Déconnexion
        if (isset($_POST['deconnexion'])) {
            session_destroy();
            header('Location: ControllerUtilisateur.php');
            exit;
        }

        //2. Inscription
        if (isset($_POST['inscription'])) {
           
            $valeurs = [
                'nom'       => trim($_POST['nom'] ?? ''),
                'prenom'    => trim($_POST['prenom'] ?? ''),
                'naissance' => trim($_POST['naissance'] ?? ''),
                'email'     => strtolower(trim($_POST['email'] ?? '')),
                'adresse'   => trim($_POST['adresse'] ?? ''),
            ];
            $mdp     = $_POST['mot_de_passe'] ?? '';
            $confirm = $_POST['mot_de_passe_confirm'] ?? '';

            //verification des données
            $erreurs = $this->verifierInscription($valeurs, $mdp, $confirm);

            if (empty($erreurs)) {
                //Je demande au model l'identifiant du rôle "client"
                $idRole = $this->getModel()->findIdRole('client');

                if ($idRole === null) {
                    $erreurs[] = 'Le rôle "client" n\'existe pas dans la table role.';
                } else {
                    // demande au model d'enregistrer l'utilisateur 
                    $this->getModel()->inscription(
                        $valeurs['nom'],
                        $valeurs['prenom'],
                        $valeurs['naissance'],
                        $valeurs['email'],
                        $mdp,
                        $valeurs['adresse'],
                        $idRole
                    );
                    $message = 'Compte créé. Vous pouvez maintenant vous connecter.';
                    $valeurs = [];
                }
            }
        }

        //3. Connexion
        if (isset($_POST['connexion'])) {
            $email = trim($_POST['email'] ?? '');
            $mdp   = $_POST['mot_de_passe'] ?? '';

            //Je demande au model de vérifier l'e-mail et le mot de passe
            $utilisateur = $this->getModel()->connexion($email, $mdp);

            if ($utilisateur !== null) {
                // Connexion réussie sans régénération de session
                $_SESSION['utilisateur'] = $utilisateur;
                header('Location: ControllerUtilisateur.php');
                exit;
            }

            //Même message que l'e-mail existe ou non
            $erreurs[] = 'E-mail ou mot de passe incorrect.';
            $valeurs['email_connexion'] = $email;
        }

        //4. Je passe les données à afficher à la View, puis je demande de faire l'affichage
        $data = [
            'erreurs'     => $erreurs,
            'message'     => $message,
            'valeurs'     => $valeurs,
            'utilisateur' => $_SESSION['utilisateur'] ?? null,
        ];
        $this->getView()->setData($data)->display();
    }
}


//Exécution
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=projet;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die('Connexion à la base de données impossible.');
}

//Création du Model
$model = new ModelUtilisateur($pdo);

//Création de la View
$view = new ViewUtilisateur();

//Création du Controller
$controller = new ControllerUtilisateur($model, $view);

//Lancement du Rendu de la page à partir du model et de la view
$controller->render();