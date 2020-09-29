<?php
/*
Créez une bdd : « team »

Créez une table : « player » 

	avec les champs suivants :
		- id_player  
		- nom
		- prenom
		- age
		- pays
		- poste (attaque/defenseur) input type="radio" OU un select
		- photo
		- presentation

CREATE DATABASE team;
USE team;

CREATE TABLE player(
	id_player INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
	prenom VARCHAR(25) NOT NULL,
	nom VARCHAR(25) NOT NULL,
	age INT NOT NULL,
	pays VARCHAR(25) NOT NULL,
	poste ENUM('attaque', 'defense') NOT NULL,
	photo VARCHAR(255) NOT NULL,
	presentation TEXT NOT NULL
) ENGINE=InnoDB;

-----------------------------------------------------------------
Enregistrement des données (formulaire)
	=> pensez aux controles des saisies !!
		-> l'age doit etre un ENTIER et avoir 2 chiffres
		-> nom, prenom OBLIGATOIRE 
	Bonus : Pour la photo gérer que l'extension doit etre .jpg ou .png

Affichage des données sous forme de tableau
	=> 'photo', afficher la photo
	=> 'presentation', couper le texte si trop long substr()
*/
//----------------------------------------------------------------
//----------------------------------------------------------------
//Connexion à la BDD :
$pdo = new PDO('mysql:host=localhost;dbname=team', 'root', '');
	//var_dump( $pdo );
//Création d'une variable pour recevoir les messages d'erreurs 
$error = '';
// $error_photo =' ';
//----------------------------------------------------------------
//Controle + Insertion en bdd :
// print '<pre>';
// 	print_r( $_POST );
// print '</pre>';

if( isset($_POST) && !empty($_POST) ){ //Si il a eu validation du formulaire et que le post n'est pas vide

	//première controle des saisies
	foreach( $_POST as $indice => $valeur){

		$_POST[$indice] = htmlentities( addslashes( $valeur ) );
	}

	//--------------------------------------------------
	//Champs 'nom' et 'prenom' obligatoires :
	if( empty( $_POST['nom'] ) ){ //SI l'input 'nom' est vide

		$error .= "<div style='color:red;'>Vous devez saisir un nom</div>";
	}
	if( empty( $_POST['prenom'] ) ){

		$error .= "<div style='color:red;'>Vous devez saisir un prénom</div>";		
	}

	//--------------------------------------------------
	//l'age doit etre un ENTIER et avoir 2 chiffres
	if( !empty($_POST['age']) ){

		//l'age doit contenir 2 chiffres:
		if( strlen( $_POST['age'] ) != 2 ){ //Si la taille de l'age est différente de 2

			$error .= "<div style='color:red;'>Vous devez avoir un age contenant 2 chiffres</div>";		
		}
		//l'age doit etre un ENTIER
		if( !is_numeric( $_POST['age'] ) ){ //si l'age est différent d'un type INT

			$error .= "<div style='color:red;'>Vous devez renseigner des chiffres</div>";		
		}
	}
	//--------------------------------------------------
	// print '<pre>';
	// 	print_r( $_FILES );
	// print '</pre>';

	if( !empty( $_FILES['photo']['name'] ) ){ //SI on a essayé d'uploader un fichier

		//récupération du nom de la photo
		$photo = $_FILES['photo']['name'];

		//--------------------------------------------------
		//gestion de l'extension du fichier :
			//récupération des infos sur la photo
			$info_photo = pathinfo( $photo );

			// print '<pre>';
			// 	print_r( $info_photo );
			// print '</pre>';

			//récupération de l'extension du fichier
			$extension = $info_photo['extension'];
				//var_dump($extension);

			$extension_autorisee = array('jpg', 'jpeg', 'png');
			// print '<pre>';
			// 	print_r( $extension_autorisee );
			// print '</pre>';		

			if( !in_array( $extension , $extension_autorisee) ){
			//in_array(arg1, arg2) : permet de savoir si une information appartient au tableau
				//arg1 : l'information que l'on cherche a savoir si il appartient au tableau
				//arg2 : le tableau dans lequel on recherche
				$error_photo = "<div style='color:red;'>Votre fichier n'est pas valide (.jpg, .jpeg ou .png)</div>";	
			}
			else{
			//chemin a insérer en bdd
			$photo_bdd = "http://localhost/team/photo/$photo";

			//chemin pour stocker la photo (ici, sur le pc)
			$photo_dossier = "C:/xampp/htdocs/team/photo/$photo";
			// $photo_dossier	="C:\xampp\htdocs\team\photo\$photo";

			copy( $_FILES['photo']['tmp_name'], $photo_dossier );
 
		}
	}

	if( empty( $error ) && empty( $error_photo) ){ //si il n'y a pas de messages d'erreur

		$pdo->query(" INSERT INTO player(nom, prenom, age, pays, poste, photo, presentation) 
			VALUES( '$_POST[nom]',
					'$_POST[prenom]',
					'$_POST[age]',
					'$_POST[pays]',
					'$_POST[poste]',
					'$photo_bdd',
					'$_POST[presentation]'
				)");
	}
}

//------------------------------------------------------------------
//Affichage des données :
$pdostatement = $pdo->query(" SELECT * FROM player ");

echo '<table border="1" cellpadding="5">';
	echo "<tr>";
		for( $i=0; $i < $pdostatement->columnCount(); $i++ ){

			$colonne = $pdostatement->getColumnMeta( $i );

			echo "<th>$colonne[name]</th>";
		}
	echo "</tr>";
	while( $ligne = $pdostatement->fetch(PDO::FETCH_ASSOC ) ){
		echo "<tr>";

			// print '<pre>';
			// 	print_r( $ligne );
			// print '</pre>';

			foreach( $ligne as $key => $value ){

				if( $key == 'photo'){

					echo "<td><img src='$value' width='80'></td>";
					//$content .= "<td><img src='$value' width='80'></td>";

				}
				elseif( $key == 'presentation'){

						if( strlen( $value )  > 20 ){

							echo "<td>". substr($value, 0, 20) ."...<a href=''>Lire la suite</a></td>";
						}
						else{
							echo "<td>$value</td>";
						}
				}
				else{

						echo "<td>$value</td>";
				}
			}
		echo "</tr>";
	}
echo '</table>';

//-----------------------------------------------------
//Affichage des messages d'erreurs :
echo $error;
// echo $error_photo;

//----------------------------------------------------------------
//formulaire
?>
<form method="post" enctype="multipart/form-data">
<!-- enctype="multipart/form-data" INDISPENSABLE pour uploader des fichiers ( fonction avec <input type="file"> ) -->
	
	<label>Nom</label><br>
	<input type="text" name="nom"><br><br>
	
	<label>Prenom</label><br>
	<input type="text" name="prenom"><br><br>

	<label>Age</label><br>
	<input type="text" name="age"><br><br>
	
	<label>Pays</label><br>
	<input type="text" name="pays" ><br><br>
	
	<label>Poste</label><br>
	<input type="radio" name="poste" value="attaque" checked>Attaquant<br>
	<input type="radio" name="poste" value="defense">Défenseur<br><br>

	<label>Photo</label><br>
	<input type="file" name="photo"><br><br>

	<label>Présentation</label><br>
	<textarea name="presentation" cols='15'></textarea><br><br>

	<input type="submit" value="Inscription">

</form>