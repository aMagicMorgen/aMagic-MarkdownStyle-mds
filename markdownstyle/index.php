<?php
// Пример использования: пока без MarkdownExtra
require_once 'MarkdownStyle.php';

$nom = '321';
$data = date('d.m.Y');

echo '
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<!--link rel="stylesheet" href="md-page.css"-->
<script src="https://cdn.rawgit.com/oscarmorrison/md-page/5833d6d1/md-page.js"></script><noscript>
<!--script src="md-page.js"></script><noscript-->
<!--link href="https://cdn.jsdelivr.net/gh/aMagicMorgen/aMagic-MDreader-AMMDr@main/ammdr-4.0/assets/css/ammdr.css" rel="stylesheet"-->
<!--script type="module" src="https://cdn.jsdelivr.net/gh/zerodevx/zero-md@2/dist/zero-md.min.js"></script-->
<!---script src="https://cdn.rawgit.com/webcomponents/webcomponentsjs/v0.7.15/webcomponents-lite.min.js"></script-->`
<!--link rel="import" href="https://cdn.rawgit.com/zerodevx/zero-md/v0.2.0/build/zero-md.html"-->

<!-- Themes -->
<!--link rel="stylesheet" href="https://cdn.rawgit.com/zerodevx/zero-md/v0.2.0/markdown-themes/default.css"-->
<!--link rel="stylesheet" href="https://cdn.rawgit.com/zerodevx/zero-md/v0.2.0/highlight-themes/default.css"-->
<style>
body{
max-width: 960px;	
margin: 0 auto;
padding-bottom: 107px;	
}
section {
	max-width: 960px; 
    margin: 0 auto;
    padding-bottom: 107px;
    width: 100%;
    background: #fff;
    padding: 2em;
    border-radius: 0.75rem;
    line-height: 1.6;
    overflow: hidden;
    margin-bottom: 2rem;
    position: relative;
    font-size: .875rem;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 10%), 0 4px 6px -2px rgb(0 0 0 / 5%);
}
</style>
</head>
<body>	
<main>
';

ob_start();
	include 'mds.mds';
    include 'mdh.mdh';
	include 'mthl.mthl';
    $mdh = ob_get_clean();
echo MarkdownStyle::parse($mdh);
/*
echo "<zero-md>
<script type='text/markdown'>
$md

</script>
</zero-md>";
*/
/*
echo "
<zero-md text='$md'>
  <div class='md-html'></div>
</zero-md>
";
*/
echo "</main>
  </body>
</body></html>";

