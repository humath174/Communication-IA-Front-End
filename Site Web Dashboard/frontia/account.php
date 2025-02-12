<?php
session_start();
require 'config.php'; // Connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les infos de l'utilisateur
$stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vérification et mise à jour des informations
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
        $update->bind_param("sssi", $full_name, $email, $hashed_password, $user_id);
    } else {
        $update = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $update->bind_param("ssi", $full_name, $email, $user_id);
    }

    if ($update->execute()) {
        $_SESSION['success'] = "Informations mises à jour avec succès.";
        header("Location: account.php");
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body >
<?php

include "navbar.php";

?>

<div class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white shadow-lg rounded-lg p-8 max-w-md w-full">
    <h2 class="text-2xl font-semibold text-gray-800 text-center mb-4">Mon compte</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 p-2 text-green-700 bg-green-100 border border-green-300 rounded">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 p-2 text-red-700 bg-red-100 border border-red-300 rounded">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="account.php" method="POST" class="space-y-4">
        <div>
            <label for="full_name" class="block text-gray-700 font-medium">Nom complet</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required
                   class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-blue-300">
        </div>

        <div>
            <label for="email" class="block text-gray-700 font-medium">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                   class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-blue-300">
        </div>

        <div>
            <label for="password" class="block text-gray-700 font-medium">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" id="password" name="password"
                   class="w-full p-2 border border-gray-300 rounded focus:ring focus:ring-blue-300">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
            Mettre à jour
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="logout.php" class="text-red-600 hover:underline">Se déconnecter</a>
    </div>
</div>
</div>
</body>
</html>
