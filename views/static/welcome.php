<?php
  /** @var View $this */

  use Neu\View;

  $this->extend('base');
  $this['title'] = 'Welcome!';
  $this->begin_section('content');
?>

<h2>Hello, and welcome to this project!</h2>
<p>Here are some numbers:</p>
<ul>
  <?php foreach ($numbers as $num): ?>
    <li><?=$num?></li>
  <?php endforeach; ?>
</ul>

  <?php $this->begin_section('scripts'); ?>
  <script src="http://joern-neumeyer.de"></script>
  <?php $this->end_section(); ?>

<?php
  $this->end_section();
?>
