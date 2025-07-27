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
    <title>Home page</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">

    <link rel="stylesheet" href="assets/cardStyle.css">
    <link rel="stylesheet" href="assets/playerStyle.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/styleMain.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body style="padding-bottom: 150px !important;" class="<?php echo $_SESSION["mode"] ?>">
    <?php include 'shared/navbar.php'?>
    <div class="container-md" id="mainContainer">
        <h1 class="mainTitle" style="text-align: center;">Suggeriti</h1>
        <div class="row g-1 justify-content-evenly">
            <!-- cards go here -->
        </div>
        <!--
        <div class="row g-1 justify-content-evenly">
            
        </div>
        -->
    </div>
    <h1 class="mainTitle" style="text-align: center;">Preferiti da spotify</h1>
    <table class="table" <?php if($_SESSION["mode"] == "dark-mode"){echo 'data-bs-theme="dark"';}?>>
        <tbody id="braniSaved">
        </tbody>
    </table>
    <script>
        var content;
        var par = document.getElementById('response');
        if(window.localStorage.getItem("auth_code")){
            url = 'https://api.spotify.com/v1/me/tracks';
            (async () => {
                const rawResponse = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + window.localStorage.getItem("auth_code")
                    },
                });
                content = await rawResponse.json();

                console.log(content);
                content.items.forEach(element => {
                    document.getElementById("braniSaved").innerHTML += '<tr><td><img style="width: 50px;" src="' + element.track.album.images[0].url + '"></td><td>' + capitalizeFirstLetter(element.track.name) + '</td><td>' + element.track.artists[0].name + '</td><td class="col-durata">' + millisToMinutesAndSeconds(element.track.duration_ms) + '</td><td><button class="btn btn-primary btn-play-song" onclick="playTrack(\'\')"><i class="bi bi-play-fill h4"></i></button></td></tr>';
                });
            })();
    }
    </script>
    <script src="assets/scripts/homeScript.js"></script>
    <?php include 'shared/footer.php'?>
    <script src="assets/scripts/scriptMain.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>