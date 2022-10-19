<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test</title>
</head>
<body>
<form method="POST" action="/test">
    <?= $this->getProperty('csrfField') ?>
    <input type="text" name="test" required>
    <button type="submit">Submit</button>
</form>
</body>
</html>