<?php

$errors = [];
$uploadSuccess = false;
$filePath = '';
$userInfo = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["avatar"])) {
    $target_dir = "public/uploads/";
    $uniquePrefix = time() . '_' . uniqid('', true); // Génère un préfixe unique
    $target_file = $target_dir . $uniquePrefix . basename($_FILES["avatar"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Vérifier si le fichier est une image
    if (isset($_POST["send"])) {
        $check = getimagesize($_FILES["avatar"]["tmp_name"]);
        if ($check === false) {
            $errors[] = "Le fichier n'est pas une image.";
            $uploadOk = 0;
        }
    }

    // Vérifier si le fichier existe déjà
    if (file_exists($target_file)) {
        $errors[] = "Désolé, le fichier existe déjà.";
        $uploadOk = 0;
    }

    // Vérifier la taille du fichier (1 Mo max)
    if ($_FILES["avatar"]["size"] > 1000000) {
        $errors[] = "Désolé, votre fichier doit être inférieur a 1Mo.";
        $uploadOk = 0;
    }

    // Autoriser certains formats de fichier
    if ($imageFileType !== "jpg" && $imageFileType !== "png" && $imageFileType !== "gif" && $imageFileType !== "webp") {
        $errors[] = "Désolé, seuls les fichiers JPG, PNG, GIF & WEBP sont autorisés.";
        $uploadOk = 0;
    }

    // Tentative d'upload
    if ($uploadOk === 1 && move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
        $uploadSuccess = true;
        $filePath = $target_file;
        $userInfo = [
            'firstName' => $_POST['firstName'],
            'lastName' => $_POST['lastName'],
            'age' => $_POST['age']
        ];
    } else {
        $errors[] = "Désolé, il y a eu une erreur lors du téléchargement de votre fichier.";
    }
}

// Gestion de la suppression du fichier
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete'])) {
    $fileToDelete = $_POST['filePath'];
    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
        $uploadSuccess = false; // Réinitialiser l'état de succès
        $filePath = ''; // Réinitialiser le chemin du fichier
    } else {
        $errors[] = "Le fichier n'existe pas.";
    }
}

if (!empty($errors)): ?>
    <div>
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label for="imageUpload">Upload a profile image:</label>
    <input type="file" name="avatar" id="imageUpload" />
    <label for="firstName">Prénom:</label>
    <input type="text" name="firstName" id="firstName" required>
    <label for="lastName">Nom:</label>
    <input type="text" name="lastName" id="lastName" required>
    <label for="age">Âge:</label>
    <input type="number" name="age" id="age" required>
    <button name="send">Send</button>
</form>

<?php if ($uploadSuccess && file_exists($filePath)): ?>
    <div class="profile-info">
        <h1>Permis de Springfield</h1>
        <p><strong>Nom:</strong> <?php echo htmlspecialchars($userInfo['firstName'] . " " . $userInfo['lastName']); ?></p>
        <p><strong>Âge:</strong> <?php echo htmlspecialchars($userInfo['age']); ?></p>
        <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Profile Image">
        <form method="post">
            <input type="hidden" name="filePath" value="<?php echo htmlspecialchars($filePath); ?>">
            <button type="submit" name="delete">Supprimer</button>
        </form>
    </div>
<?php endif; ?>