<?php
    session_start();
    if(!isset($_SESSION["idUtente"])){
        header("Location: index.php");
    }
    else{
        setcookie("idUtente", $_SESSION["idUtente"]);
    }

    if(!isset($_SESSION["mode"])){
        $_SESSION["mode"] = "dark-mode";
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <link rel="stylesheet" href="assets/playerStyle.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/styleMain.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body style="padding-top: 100px !important;" class="<?php echo $_SESSION["mode"] ?>">
    <?php include 'shared/navbar.php'?>
    <div class="container-md" id="mainContainer">
        <h1 class="mainTitle" style="text-align: center;">Il tuo account</h1>
        <div class="formCenter" style="padding-bottom: 20px; margin-bottom: 100px;">
            <div class="mb-3">
                <label for="usernameInput" class="form-label">Username</label>
                <input type="text" class="form-control" id="usernameInput" name="username" placeholder="<?php echo($_SESSION["username"]); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="emailInput" class="form-label">Email</label>
                <input type="email" class="form-control" id="emailInput" name="email" placeholder="<?php echo($_SESSION["email"]); ?>" disabled>
            </div>
            <button class="btn btn-danger" onclick="logoutClick()">Logout</button>
            <button class="btn btn-primary" onclick="toggleLightModeClick()">Attiva dark mode</a>
            <button class="btn btn-primary" onclick="loginSpotify()">loginSpotify</button>
            <button type="button" class="btn btn-delete-account" data-bs-toggle="modal" data-bs-target="#modalDelete">
                Elimina account
            </button>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="modalDelete" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Elimina account<h5>
                </div>
                <div class="modal-body">
                    Sei veramente sicuro di voler eliminare l'account? L'azione è <span class="fw-bold">irreversibile.</span><br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger" onclick="deleteAccountClick()">Elimina</button>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/scripts/accountScript.js"></script>
    <?php include 'shared/footer.php'?>
    <script src="assets/scripts/scriptMain.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>