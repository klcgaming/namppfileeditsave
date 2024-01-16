<?php
function listFiles($dir) {
    $files = scandir($dir);
    echo '<ul>';
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo '<li>';
            if (is_dir($dir . '/' . $file)) {
                echo "<span class='folder'>$file ▼</span>";
                listFiles($dir . '/' . $file);
            } else {
                echo "<span class='file' data-file='$file'>$file</span>";
            }
            echo '</li>';
        }
    }
    echo '</ul>';
}

function readFileContent($file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        echo file_get_contents($filePath);
    }
}

function saveFileContent($file, $content) {
    $filePath = __DIR__ . '/' . $file;
    file_put_contents($filePath, $content);
}

function createFolder($folderName) {
    $folderPath = __DIR__ . '/' . $folderName;
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
}

function createFile($fileName) {
    $filePath = __DIR__ . '/' . $fileName;
    if (!file_exists($filePath)) {
        file_put_contents($filePath, '');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'read') {
        $file = $_POST['file'];
        readFileContent($file);
    } elseif ($action === 'save') {
        $file = $_POST['file'];
        $content = $_POST['content'];
        saveFileContent($file, $content);
    } elseif ($action === 'createFolder') {
        $folderName = $_POST['folderName'];
        createFolder($folderName);
    } elseif ($action === 'createFile') {
        $fileName = $_POST['fileName'];
        createFile($fileName);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Explorer</title>
    <style>
        ul {
            list-style: none;
            padding-left: 20px;
        }

        li {
            cursor: pointer;
        }

        .file, .folder {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="file-content"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileContentDiv = document.getElementById('file-content');

            fileContentDiv.addEventListener('click', function (event) {
                if (event.target.classList.contains('file')) {
                    const fileName = event.target.dataset.file;
                    readFileContent(fileName);
                } else if (event.target.classList.contains('folder')) {
                    const folderName = prompt('Enter folder name:');
                    if (folderName) {
                        createFolder(folderName);
                        location.reload();
                    }
                }
            });

            function readFileContent(file) {
                const formData = new FormData();
                formData.append('action', 'read');
                formData.append('file', file);

                fetch('', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(content => {
                    fileContentDiv.innerHTML = `<textarea id="editor" rows="10" cols="30">${content}</textarea>
                                             <button onclick="saveFile('${file}')">Save</button>`;
                });
            }

            function saveFile(file) {
                const content = document.getElementById('editor').value;
                const formData = new FormData();
                formData.append('action', 'save');
                formData.append('file', file);
                formData.append('content', content);

                fetch('', {
                    method: 'POST',
                    body: formData,
                })
                .then(() => {
                    fileContentDiv.innerHTML = '';
                });
            }
        });
    </script>
    <?php listFiles(__DIR__); ?>
</body>
</html>

