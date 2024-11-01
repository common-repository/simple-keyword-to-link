<p align="center">
	<?=esc_html_e( 'Author', plugin_slug );?>: <a href="https://www.invernomuto.net">Alessandro Piconi</a><br />
	<?=esc_html_e( 'Email', plugin_slug );?>: <a href="mailto:attesor@gmail.com">attesor@gmail.com</a><br />
	<br />
	<p><i><?=esc_html_e( 'Please, before a bad review, contact me to solve any problem', plugin_slug );?></i></p>
	<p><i><?=esc_html_e( 'The statistics functionality is still experimental. If the links don\'t work on the site, you need to delete the URL rewrite cache, enabling a different type of permalink and then putting the chosen one back.', plugin_slug );?></i></p>
	<p><a href="<?php echo plugins_url('../stats.csv', __FILE__);?>">Stat Csv File</a></p>
</p>