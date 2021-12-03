<?php
$panel_vars = get_panel_vars();
?>

<hr/>

<h2>Call to Action default template</h2>

<pre>
  <?php echo htmlentities( print_r( $panel_vars, TRUE ) ); ?>
</pre>

<a class="call-to-action" href="<?php echo $panel_vars['link']['url']; ?>"><?php echo $panel_vars['link']['label']; ?></a>

<hr/>
