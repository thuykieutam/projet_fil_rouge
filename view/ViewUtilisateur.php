<?php
//Class ViewUtilisateur

class ViewUtilisateur {
    //ATTRIBUT
    private array $data;

    //CONSTRUCTEUR

    //GETTER ET SETTER
    public function getData():array{
        return $this->data;
    }
    public function setData(array $newData):self{
        $this->data = $newData;
        return $this;
    }

    //METHODS
    public function display(){
        
        $erreurs     = $this->getData()['erreurs'] ?? [];
        $message     = $this->getData()['message'] ?? '';
        $valeurs     = $this->getData()['valeurs'] ?? [];
        $utilisateur = $this->getData()['utilisateur'] ?? null;
?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Connexion et inscription</title>
        </head>
        <body>
            <main>
                <?php
                    

                    //Affichage du message de réussite
                    if ($message !== '') {
                        echo("<p>" . htmlspecialchars($message) . "</p>");
                    }

                    //Affichage des erreurs
                    foreach ($erreurs as $erreur) {
                        echo("<p>" . htmlspecialchars($erreur) . "</p>");
                    }

                    //Si l'utilisateur est connecté : message de bienvenue et bouton de déconnexion
                    if ($utilisateur !== null) {
                        echo("<p>Bonjour " . htmlspecialchars($utilisateur['prenom']) . ", vous êtes connecté(e).</p>");
                ?>
                        <form method="post">
                            <button type="submit" name="deconnexion">Se déconnecter</button>
                        </form>
                <?php
                    //Sinon : formulaires de connexion et d'inscription
                    } else {
                ?>
                        <h1>Connexion</h1>
                        <form method="post">
                            <label for="login_email">E-mail</label>
                            <input type="email" id="login_email" name="email" value="<?php echo(htmlspecialchars($valeurs['email_connexion'] ?? '')); ?>" required>

                            <label for="login_mdp">Mot de passe</label>
                            <input type="password" id="login_mdp" name="mot_de_passe" required>

                            <button type="submit" name="connexion">Se connecter</button>
                        </form>

                        <h1>Créer un compte</h1>
                        <form method="post">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?php echo(htmlspecialchars($valeurs['nom'] ?? '')); ?>" required>

                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?php echo(htmlspecialchars($valeurs['prenom'] ?? '')); ?>" required>

                            <label for="naissance">Date de naissance</label>
                            <input type="date" id="naissance" name="naissance" value="<?php echo(htmlspecialchars($valeurs['naissance'] ?? '')); ?>" required>

                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" value="<?php echo(htmlspecialchars($valeurs['email'] ?? '')); ?>" required>

                            <label for="mdp">Mot de passe (12 caractères minimum)</label>
                            <input type="password" id="mdp" name="mot_de_passe" required>

                            <label for="mdp_confirm">Confirmer le mot de passe</label>
                            <input type="password" id="mdp_confirm" name="mot_de_passe_confirm" required>

                            <label>
                                <input type="checkbox" name="cgu" value="1" required>
                                J'accepte les conditions d'utilisation
                            </label>

                            <button type="submit" name="inscription">Créer mon compte</button>
                        </form>
                <?php
                    }
                ?>
            </main>
        </body>
        </html>
<?php
    }
}