<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="public/debug.css" type="text/css"/>
    <title>Error!</title></head>
  <body>
    <main class="container">
      <h1>Oops, something went wrong!</h1>
      <ul>
        <?php foreach ($view_data as $k => [$error, $snippet, $trace]): ?>
          <li>
            <p><?= $error->getFile() ?></p>
            <p><code><?= $snippet ?></code></p>
            <p>Message:</p>
            <p><code><?= $error ?></code></p>
            <p>
              <a href="#trace-<?=$k?>"><button class="btn btn-primary">Show Trace</button></a>
              <a href="#"><button class="btn btn-secondary">Hide Trace</button></a>
            </p>
            <ul id="trace-<?=$k?>" class="trace">
              <?php foreach ($trace as [$function, $tsnippet, $file]): ?>
                <li>
                  <p><?=$file?> @ <?=$function?></p>
                  <p></p>
                  <p><code><?= $tsnippet ?></code></p>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
          <hr>
        <?php endforeach; ?>
      </ul>
    </main>
  </body>
</html>
