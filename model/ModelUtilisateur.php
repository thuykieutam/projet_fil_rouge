<?php
//MODEL : class ModelUtilisateur (accès à la base de données)
class ModelUtilisateur{
    //ATTRIBUT
    private int $idUtilisateur;
    private string $nomUtilisateur;
    private string $prenomUtilisateur;
    private string $dateNaissanceUtilisateur;
    private string $mailUtilisateur;
    private string $motDePasseUtilisateur;
    private int $idRole;
    private PDO $pdo;

    //CONSTRUCTEUR
    public function __construct(PDO $newPDO){
        $this->pdo = $newPDO;
    }

    //GETTER ET SETTER
    //Get the value of idUtilisateur
    public function getIdUtilisateur(): int {
        return $this->idUtilisateur;
    }

    //Set the value of idUtilisateur
    public function setIdUtilisateur(int $idUtilisateur): self {
        $this->idUtilisateur = $idUtilisateur;
        return $this;
    }

    //Get the value of nomUtilisateur
    public function getNomUtilisateur(): string {
        return $this->nomUtilisateur;
    }

    //Set the value of nomUtilisateur
    public function setNomUtilisateur(string $nomUtilisateur): self {
        $this->nomUtilisateur = $nomUtilisateur;
        return $this;
    }

    //Get the value of prenomUtilisateur
    public function getPrenomUtilisateur(): string {
        return $this->prenomUtilisateur;
    }

    //Set the value of prenomUtilisateur
    public function setPrenomUtilisateur(string $prenomUtilisateur): self {
        $this->prenomUtilisateur = $prenomUtilisateur;
        return $this;
    }

    //Get the value of dateNaissanceUtilisateur
    public function getDateNaissanceUtilisateur(): string {
        return $this->dateNaissanceUtilisateur;
    }

    //Set the value of dateNaissanceUtilisateur
    public function setDateNaissanceUtilisateur(string $dateNaissanceUtilisateur): self {
        $this->dateNaissanceUtilisateur = $dateNaissanceUtilisateur;
        return $this;
    }

    //Get the value of mailUtilisateur
    public function getMailUtilisateur(): string {
        return $this->mailUtilisateur;
    }

    //Set the value of mailUtilisateur
    public function setMailUtilisateur(string $mailUtilisateur): self {
        $this->mailUtilisateur = $mailUtilisateur;
        return $this;
    }

    //Get the value of motDePasseUtilisateur
    public function getMotDePasseUtilisateur(): string {
        return $this->motDePasseUtilisateur;
    }

    //Set the value of motDePasseUtilisateur
    public function setMotDePasseUtilisateur(string $motDePasseUtilisateur): self {
        $this->motDePasseUtilisateur = $motDePasseUtilisateur;
        return $this;
    }

    //Get the value of idRole
    public function getIdRole(): int {
        return $this->idRole;
    }

    //Set the value of idRole
    public function setIdRole(int $idRole): self {
        $this->idRole = $idRole;
        return $this;
    }

    //Get the value of pdo
    public function getPdo(): PDO {
        return $this->pdo;
    }

    //Set the value of pdo
    public function setPdo(PDO $pdo): self {
        $this->pdo = $pdo;
        return $this;
    }

    //METHODS
    //Retrouver l'identifiant d'un rôle à partir de son nom 
    public function findIdRole(string $nomRole): ?int {
        try{
            //1) requête SELECT
           
            $request = $this->getPdo()->prepare('SELECT Id_role FROM role WHERE role = :role;');

            //2) Envoyer la requête à la BDD
            $request->execute([':role' => $nomRole]);

            //3) Récupérer la réponse de la BDD
            $idRole = $request->fetchColumn();
            if ($idRole === false) {
                return null;
            }
            return (int) $idRole;

        }catch(Exception $error){
            //On n'affiche pas le message de l'erreur : il pourrait donner des informations sur la base
            die('Erreur technique, réessayez plus tard.');
        }
    }

    //Chercher un utilisateur grâce à son e-mail (renvoie null s'il n'existe pas)
    public function findByEmail(string $email): ?array {
        try{
            //1) Préparer la requête SELECT
            $request = $this->getPdo()->prepare('SELECT Id_utilisateur, prenom_utilisateur, mot_de_passe_utilisateur FROM utilisateur WHERE mail_utilisateur = :email;');

            //2) Envoyer la requête à la BDD
            $request->execute([':email' => $email]);

            //3) Récupérer la réponse de la BDD
            // PDO::FETCH_ASSOC : permet de récupérer la réponse sous forme de tableau associatif
            $utilisateur = $request->fetch(PDO::FETCH_ASSOC);
            if ($utilisateur === false) {
                return null;
            }
            return $utilisateur;

        }catch(Exception $error){
            die('Erreur technique, réessayez plus tard.');
        }
    }

    //Enregistrer un nouvel utilisateur
    public function inscription(string $nom, string $prenom, string $naissance, string $email, string $mdp, string $adresse, int $idRole): void {
    try {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);

        $request = $this->getPdo()->prepare('INSERT INTO utilisateur (nom_utilisateur, prenom_utilisateur, date_naissance_utilisateur, mail_utilisateur, mot_de_passe_utilisateur, adresse_utilisateur, Id_role) VALUES (:nom, :prenom, :naissance, :email, :mdp, :adresse, :role);');

        $request->execute([
            ':nom'       => $nom,
            ':prenom'    => $prenom,
            ':naissance' => $naissance,
            ':email'     => $email,
            ':mdp'       => $hash,
            ':adresse'   => $adresse,
            ':role'      => $idRole,
        ]);
    } catch(Exception $error) {
        die('Erreur technique, réessayez plus tard.');
    }
}

    //Connexion : vérifier l'e-mail et le mot de passe 
    public function connexion(string $email, string $mdp): ?array {
        $utilisateur = $this->findByEmail($email);

        //le password_verify compare le mot de passe que le client entre avec le mdp deja stocké
        if ($utilisateur !== null && password_verify($mdp, $utilisateur['mot_de_passe_utilisateur'])) {
          
            return [
                'id'     => $utilisateur['Id_utilisateur'],
                'prenom' => $utilisateur['prenom_utilisateur'],
            ];
        }
        return null;
    }
}