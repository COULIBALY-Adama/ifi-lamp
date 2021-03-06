<?php
	include ("./common.php");
	include ("./styles.inc");
	// Database Initialize
	$db1 = new DB_Sql();
	$db1->Database = DATABASE_NAME;
	$db1->User     = DATABASE_USER;
	$db1->Password = DATABASE_PASSWORD;
	$db1->Host     = DATABASE_HOST;

	session_start();
	check_security(3, "../index.php");

	$emploiID = get_param("EmploiID");
	$formName = get_param("FormName");
	$formAction = get_param("FormAction");
	$formType = get_param("FormType");
	$etatselected = get_param("EtatSelected");
	$error = "";
	$fldEmploiID = $emploiID;
	$fldNomDeEmploi;
	$nouveauEmploiID;
	$nouveauNomDeLEmploi;

	form1_get_info($formAction);
	switch (strtolower($formName)) {
		case "form1":
			form1_Action($formAction);
			break;
		case "form2":
			// r�serv�
			break;
	}
?>
<html>
<head>
<title>Doubler un Emploi du Temps</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body bgcolor="#CCFFFF" marginwidth="0" marginheight="0">
<form method="POST" action="adminEmploiCopier.php" name="form1">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" height="65">
    <tr bordercolor="#FFFFFF" bgcolor="#000099"> 
      <td colspan="2" height="37"> 
        <div align="center"><font color="#FFFFFF"><b>Doubler Emploi du Temps</b></font></div>
      </td>
    </tr>
    <tr> 
      <td width="46%" height="14">&nbsp;</td>
      <td width="54%" height="14">&nbsp;</td>
    </tr>
    <tr> 
      <td width="46%" height="14"> 
        <div align="right">Source ID : </div>
      </td>
      <td width="54%" height="14">&nbsp;<?php echo  $fldEmploiID ?></td>
    </tr>
    <tr> 
      <td width="46%" height="14"> 
        <div align="right">Nom de Source : </div>
      </td>
      <td width="54%" height="14">&nbsp;<?php echo  $fldNomDeLEmploi ?></td>
    </tr>
    <tr> 
      <td width="46%" height="14">&nbsp;</td>
      <td width="54%" height="14">&nbsp;</td>
    </tr>
    <tr> 
      <td width="46%" height="14"> 
        <div align="right">Nouveau ID : </div>
      </td>
      <td width="54%" height="14">&nbsp;
        <input type="text" name="NouveauEmploiID">
      </td>
    </tr>
    <tr> 
      <td width="46%" height="14">
        <div align="right">Nom de nouveau Emploi du Temps : </div>
      </td>
      <td width="54%" height="14">
        <input type="text" name="NouveauNomDeLEmploi" size="40" maxlength="40">
      </td>
    </tr>
    <tr> 
      <td colspan="2" height="50"> 
        <div align="center"> 
		  <input type="hidden" name="FormName" value="form1">
		  <input type="hidden" name="FormAction" value="effectuer">
		  <input type="hidden" name="FormType" value="<?php echo  $formType?>">
		  <input type="hidden" name="EmploiID" value="<?php echo  $emploiID?>">
		  <input type="hidden" name="EtatSelected " value="<?php echo  $etatselected?>">
          <input type="submit" name="submit" value="Effectuer"
			onclick="document.form1.FormAction.value='effectuer'; ">
          <input type="submit" name="submit" value="Annuler"
			onclick="document.form1.FormAction.value='annuler'; ">
        </div>
      </td>
    </tr>
  </table>
  <p>&nbsp;</p>
</form>
<p>&nbsp;</p></body>
</html>

<?php
function form1_get_info($action) {
	global $db;
	global $formType;
	global $fldEmploiID;
	global $fldNomDeLEmploi;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;

	if (strtolower($action) == "effectuer") {
		$nouveauNomDeLEmploi = strip(get_param("NouveauNomDeLEmploi"));
		$nouveauEmploiID = strip(get_param("NouveauEmploiID"));
	}
	else {
		$sWhere .= "emploi_id=" . tosql($fldEmploiID, "Text");
		$sSQL = "SELECT * FROM Emploi WHERE " . $sWhere;
		$db->query($sSQL);
		$db->next_record();
		$fldNomDeLEmploi = $db->f("nom_de_lemploi");
	}
}

function test_error() {
	global $db;
	global $error;
	global $formAction;
	global $formType;
	global $emploiID;
	global $fldEmploiID;
	global $fldNomDeLEmploi;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;

	if (!strlen($nouveauEmploiID))
	{
		$error = $error . "Il faut avoir un ID pour le nouveau Emploi.<br>" ;
	}
	else {
		$sSQL = "SELECT * FROM Emploi WHERE emploi_id=" . tosql($nouveauEmploiID, "Text");
		$db->query($sSQL);
		$nextdb = $db->next_record();
		if ($nextdb) 
			$error = $error . "Le nouveau ID existe d�j�. Choisissez un autre!<br>" ;
	}
	if (!strlen($nouveauNomDeLEmploi))
	{
		$error = $error . "Il faut entrer un Nom.<br>" ;
	}
}

function copy_emploi($emploiID)
{	global $db;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;
	
	$sWhere = " WHERE emploi_id=" . tosql($emploiID, "Text");
	$sSQL = "SELECT * FROM Emploi " . $sWhere;
	$db->query($sSQL);
	$db->next_record();
	$fldNomDeLEmploi = $db->f("nom_de_lemploi");
	$fldNombreDeSemaine = $db->f("nombre_de_semaine");
	$fldPremierJour  = $db->f("premier_jour");
	$fldNombreSeance = $db->f("nombre_seance");
	$fldDuree = $db->f("duree");
	$fldDebutMatin = $db->f("debut_matin");
	$fldNombreHeureMatin = $db->f("nombre_heure_matin");
	$fldDebutMidi = $db->f("debut_midi");
	$fldNombreHeureMidi = $db->f("nombre_heure_midi");
	$fldEtat = $db->f("etat");
	$fldSalleID = $db->f("salle_id");
	$sSQL = "INSERT INTO Emploi (" .
			"emploi_id, " . 
			"nom_de_lemploi, " . 
			"nombre_de_semaine, " . 
			"premier_jour, " . 
			"nombre_seance, " . 
			"duree, " . 
			"debut_matin, " . 
			"nombre_heure_matin, " . 
			"debut_midi, " . 
			"nombre_heure_midi, " . 
			"etat, " . 
			"salle_id) " . 
		"VALUES (" .
			tosql($nouveauEmploiID, "Text") . "," . 
			tosql($nouveauNomDeLEmploi, "Text") . "," . 
			tosql($fldNombreDeSemaine, "Number") . "," . 
			tosql($fldPremierJour, "Date") . "," . 
			tosql($fldNombreSeance, "Number") . "," . 
			tosql($fldDuree, "Number") . "," . 
			tosql($fldDebutMatin, "Time") . "," . 
			tosql($fldNombreHeureMatin, "Number") . "," . 
			tosql($fldDebutMidi, "Time") . "," . 
			tosql($fldNombreHeureMidi, "Number") . "," . 
			tosql("creer", "Text") . "," . 
			tosql($fldSalleID, "Number") . 
			")";
	$db->query($sSQL);
}
function copy_emploi_horaire($emploiID)
{
	global $db;
	global $db1;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;
	$sed = "SELECT eh.horaire_id, eh.debut, eh.fin, eh.seance FROM Emploi_horaire AS eh WHERE emploi_id=" . tosql($emploiID, "Text");
	$db1->query($sed);
	while ($db1->next_record()) {
		$fldHoraireID = $db1->f("horaire_id");
		$fldDebut = $db1->f("debut");
		$fldFin = $db1->f("fin");
		$fldSeance = $db1->f("seance");
		$sSQL = "INSERT INTO Emploi_horaire (horaire_id, emploi_id, debut, fin, seance) VALUES (" .
			tosql($fldHoraireID, "Number") . ", " .
			tosql($nouveauEmploiID, "Text") . ", " .
			tosql($fldDebut, "Time") . ", " .
			tosql($fldFin, "Time") . ", " .
			tosql($fldSeance, "Number") . ")";
		$db->query($sSQL);
	}
}
function copy_emploi_cours($emploiID)
{
	global $db;
	global $db1;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;
	$sed = "SELECT cours_id FROM Emploi_cours WHERE emploi_id=" . tosql($emploiID, "Text");
	$db1->query($sed);
	while ($db1->next_record()) {
		$fldCoursID = $db1->f("cours_id");
		$sSQL = "INSERT INTO Emploi_cours (emploi_id, cours_id) VALUES (" .
			tosql($nouveauEmploiID, "Text") . ", " .
			tosql($fldCoursID, "Text") . ")";
		$db->query($sSQL);
	}
}
function copy_emploi_cours_professuer($emploiID)
{
	global $db;
	global $db1;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;
	$sed = "SELECT cours_id, personnel_id FROM Emploi_cours_professeur WHERE emploi_id=" . tosql($emploiID, "Text");
	$db1->query($sed);
	while ($db1->next_record()) {
		$fldCoursID = $db1->f("cours_id");
		$fldPersonnelID = $db1->f("personnel_id");
		$sSQL = "INSERT INTO Emploi_cours_professeur (emploi_id, cours_id, personnel_id) VALUES (" .
			tosql($nouveauEmploiID, "Text") . ", " .
			tosql($fldCoursID, "Text") . ", " .
			tosql($fldPersonnelID, "Text") . ")";
		$db->query($sSQL);
	}
}
function copy_emploi_jours($emploiID)
{
	global $db;
	global $db1;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;
	$sed = "SELECT * FROM Emploi_jour WHERE emploi_id=" . tosql($emploiID, "Text");
	$db1->query($sed);
	while ($db1->next_record()) {
		$fldJourID = $db1->f("jour_id");
		$fldHoraireID = $db1->f("horaire_id");
		$fldSalleID = $db1->f("salle_id");
		$fldCoursID = $db1->f("cours_id");
		$sSQLadd = "INSERT INTO Emploi_jour (" .
				"emploi_id, " . 
				"cours_id, " . 
				"salle_id, " . 
				"horaire_id, " . 
				"jour_id) " . 
			"VALUES (" .
				tosql($nouveauEmploiID, "Text") . "," . 
				tosql($fldCoursID, "Text") . "," . 
				tosql($fldSalleID, "Number") . "," . 
				tosql($fldHoraireID, "Number") . "," . 
				tosql($fldJourID, "Number") .")";
		$db->query($sSQLadd);
	}
}


function form1_Action($action) {
	global $db;
	global $error;
	global $formType;
	global $emploiID;
	global $fldEmploiID;
	global $fldNomDeLEmploi;
	global $nouveauEmploiID;
	global $nouveauNomDeLEmploi;
	
	switch (strtolower($action)) {
		case "effectuer":
			test_error();
			if(strlen($error)) return;
			copy_emploi($emploiID);
			copy_emploi_horaire($emploiID);
			copy_emploi_cours($emploiID);
			copy_emploi_cours_professuer($emploiID);
			copy_emploi_jours($emploiID);
			header("Location: adminListeEmploi.php?EtatSelected=tous");
			return;
		case "annuler":
			header("Location: adminListeEmploi.php?EtatSelected=" . $etatselected);
			return;
	}
}
	
?>

