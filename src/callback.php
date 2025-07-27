<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CALLBACK</title>
</head>
<body>
    CALLBACK
    <?php
        print_r($_GET);
    ?>
    <script>
        (async () => {
            const rawResponse = await fetch('https://accounts.spotify.com/api/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Basic ' + btoa('7830ddbbbf3043a6a3ebab4273d26205:c5f048c8ae3c491e90d445b178d6274a')
                },
                body: 'code=<?php echo($_GET["code"]) ?>&grant_type=authorization_code&redirect_uri=http://127.0.0.1/synthax/src/callback.php'
            });
            const content = await rawResponse.json();

            console.log(content);
            window.localStorage.setItem("auth_code", content.access_token)
            window.location.replace("home.php")
        })();
    </script>
</body>
</html>