<?php

$connection = new PDO('mysql:host=localhost; dbname=academy; charset=utf8', 'root', '' );

if (isset($_POST['submit'])) {
    $argum = $_FILES['file']['name'];
    if (count($argum) <= 3) {
        foreach ($argum as $key => $elm) {

            $fileName = $_FILES['file']['name'][$key];
            $fileTmpName = $_FILES['file']['tmp_name'][$key];
            $fileType = $_FILES['file']['type'][$key];
            $fileError = $_FILES['file']['error'][$key];
            $fileSize = $_FILES['file']['size'][$key];

            $fileExtension = strtolower(end(explode('.', $fileName)));

            // Если в имени файла несколько точек (например, abc.xyz.jpg), то имя файла сохраняется не до первой точки, а до последней (имя на выходе - abc.xyz).
            // Реализация ниже в коде.
            $fileName = explode('.', $fileName);
            unset($fileName[array_search($fileExtension,$fileName)]);
            $newtext = implode(".",$fileName);
            $fileName = preg_replace('/[0-9]/', '', $newtext);

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'js'];

            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize < 5000000) {
                    if ($fileError === 0) {
                        $connection->query("INSERT INTO `images` (`imagname`,`extension`) VALUES ('$fileName', '$fileExtension')");

                        $lastId = $connection->query("SELECT MAX(id) FROM `images` ");
                        $lastId = $lastId->fetchAll();
                        $lastId = $lastId[0][0];

                        $fileNameNew = $lastId . $fileName . '.' . $fileExtension;
                        $fileDestination = 'uploads/' . $fileNameNew;

                        move_uploaded_file($fileTmpName, $fileDestination);
                        echo "Файл успешно загружен!";
                    header("Location: index.php");
                    } else {
                        echo "Что то пошло не так";
                    }
                } else {
                    echo "Слишком большой размер файла!";
                }
            } else {
                echo "Неверный тип файла!";
            }

        }
    }
    else {
        echo "Превышено максимальное колличество загружаемых фотографи";
    }
}

$data = $connection->query("SELECT * FROM `images` ");
echo "<div style='display: flex;align-items: flex-end; flex-wrap:wrap;'>";
foreach ($data as $img) {
    $delete = "delete".$img['id'];
    $image = "uploads/" . $img['id'] . $img['imagname'] . '.' . $img['extension'];
    if (isset($_POST[$delete])) {
        $imageId = $img['id'];
        $connection->query("DELETE FROM `academy` . `images` WHERE id='$imageId'");
        if (file_exists($image)) {
            unlink($image);
        }
    }

    if (file_exists($image)) {
        echo "<div>";
        echo "<img width='150' height='150' src=$image>";
        echo "<form method='POST'><button name='delete".$img['id']."'style='display:block; margin:auto; margin-top:10px'> 
        Удалить </button></form></div>";
        }
}
echo "</div>";


?>

<style>
    body {
        margin: 50px 100px;
        font-size: 25px;
    }
    input,button {
        outline: none;
        font-size: 25px;
    }
</style>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file[]" multiple>
    <button name="submit">Отправить</button>
</form>

</body>
</html>
