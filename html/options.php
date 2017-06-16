<div class="wrap">
<h2>WP MARKTPLAATS</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">

<tr valign="top">
<th scope="row">Marktplaats userid?</th>
<td><input type="text" name="userid" value="<?php echo get_option('userid'); ?>" /></td>
</tr>


<tr valign="top">
<th scope="row">Hoeveel advertenties tonen?</th>
<td><input type="text" name="ad_amount" value="<?php echo get_option('ad_amount'); ?>" /></td>
</tr>

</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="userid, ad_amount" />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>