<?php
$panel_vars  = get_panel_vars();
$show_title  = ( $panel_vars[ 'show_title' ]   == 'yes' ) ? TRUE : FALSE;
$title_class = ( $panel_vars[ 'center_title' ] == 'yes' ) ? 'class="center"' : '';
?>

<hr/>

<h2>WYSIWYG default template</h2>

<pre>
  <?php echo htmlentities( print_r( $panel_vars, TRUE ) ); ?>
</pre>

<?php if ( $show_title ) echo "<h2 {$title_class}>{$panel_vars[ 'title' ]}</h2>\n"; ?>

<?php echo $panel_vars[ 'content' ]; ?>

<hr/>
